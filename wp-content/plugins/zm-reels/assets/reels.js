/**
 * ZM Reels — full-screen viewer.
 *
 * Renders one reel at a time from the data localized as window.ZM_REELS.
 * Navigation: left/right arrows + up/down keys + scroll + touch swipe.
 * Sidebar: business info + action links, likes, and threaded comments.
 * Close returns to the site front page (it is a real page, not a modal).
 */
( function () {
	'use strict';

	var data = window.ZM_REELS;
	var app  = document.getElementById( 'zm-reels-app' );
	if ( ! app || ! data || ! data.reels || ! data.reels.length ) {
		return; // leave the "No reels" message in place.
	}

	var reels   = data.reels;
	var social  = data.social || {};
	var idx     = Math.max( 0, Math.min( reels.length - 1, parseInt( data.start, 10 ) || 0 ) );
	var muted   = true;          // start muted so autoplay is allowed.
	var lastNav = 0;             // wheel cooldown timestamp.
	var currentVideo = null;

	// ---- helpers -------------------------------------------------

	function escapeHtml( str ) {
		return String( str == null ? '' : str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function el( tag, className, html ) {
		var e = document.createElement( tag );
		if ( className ) { e.className = className; }
		if ( html != null ) { e.innerHTML = html; }
		return e;
	}

	function reelUrl( reel ) {
		return data.base + reel.slug + '/';
	}

	function isMobile() {
		return window.matchMedia && window.matchMedia( '(max-width: 768px)' ).matches;
	}

	// A "Scroll for more" hint on mobile: the chevron stays until the first
	// scroll; the text label fades on its own after a few seconds.
	function addScrollHint() {
		if ( ! isMobile() ) { return; }
		var hint = el( 'div', 'zm-scroll-hint' );
		hint.innerHTML =
			'<span class="zm-scroll-hint__text">Scroll for more</span>' +
			'<span class="zm-scroll-hint__chev"><i></i></span>';
		app.appendChild( hint );

		var textTimer = setTimeout( function () { hint.classList.add( 'zm-scroll-hint--text-hidden' ); }, 3500 );

		function dismiss() {
			app.removeEventListener( 'scroll', onScroll );
			clearTimeout( textTimer );
			hint.classList.add( 'zm-scroll-hint--gone' );
			setTimeout( function () { if ( hint.parentNode ) { hint.parentNode.removeChild( hint ); } }, 400 );
		}
		function onScroll() {
			if ( app.scrollTop > 12 ) { dismiss(); }
		}
		app.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	function makeButton( className, html, onClick ) {
		var b = document.createElement( 'button' );
		b.type = 'button';
		b.className = className;
		b.innerHTML = html;
		b.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			onClick( e );
		} );
		return b;
	}

	// POST to admin-ajax; resolves with the parsed WP JSON response.
	function ajax( action, params ) {
		var body = new FormData();
		body.append( 'action', action );
		body.append( 'nonce', social.nonce );
		for ( var k in params ) {
			if ( params.hasOwnProperty( k ) && params[ k ] != null ) {
				body.append( k, params[ k ] );
			}
		}
		return fetch( social.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( r ) { return r.json(); } )
			.catch( function () { return { success: false, data: { message: 'Network error.' } }; } );
	}

	// Resolve a Turnstile token for guests (empty string when not needed).
	function getTurnstileToken( container, invisible ) {
		return new Promise( function ( resolve ) {
			var ts = social.turnstile || {};
			if ( ! ts.enabled || social.loggedIn || ! window.turnstile ) {
				resolve( '' );
				return;
			}
			function render() {
				if ( container._wid === undefined ) {
					container._wid = window.turnstile.render( container, {
						sitekey: ts.siteKey,
						appearance: invisible ? 'interaction-only' : 'always',
						callback: function ( token ) {
							if ( container._res ) { var r = container._res; container._res = null; r( token ); }
						},
						'error-callback': function () {
							if ( container._res ) { var r = container._res; container._res = null; r( '' ); }
						}
					} );
				} else {
					window.turnstile.reset( container._wid );
				}
				var existing = '';
				try { existing = window.turnstile.getResponse( container._wid ); } catch ( e ) {}
				if ( existing ) { resolve( existing ); return; }
				container._res = resolve;
				// Safety: never hang if Turnstile can't produce a token.
				setTimeout( function () {
					if ( container._res ) { var r = container._res; container._res = null; r( '' ); }
				}, 8000 );
			}
			// Wait briefly for the Turnstile script if it hasn't loaded yet.
			if ( window.turnstile && window.turnstile.render ) {
				render();
			} else {
				var tries = 0;
				var iv = setInterval( function () {
					if ( window.turnstile && window.turnstile.render ) { clearInterval( iv ); render(); }
					else if ( ++tries > 50 ) { clearInterval( iv ); resolve( '' ); }
				}, 100 );
			}
		} );
	}

	function authGate( message ) {
		var d = el( 'div', 'zm-gate' );
		d.appendChild( el( 'p', 'zm-gate__msg', escapeHtml( message ) ) );
		var links = el( 'div', 'zm-gate__links' );
		var login = el( 'a', 'zm-btn' );
		login.href = social.loginUrl;
		login.textContent = 'Log in';
		links.appendChild( login );
		if ( social.canRegister && social.registerUrl ) {
			var reg = el( 'a', 'zm-btn zm-btn--ghost' );
			reg.href = social.registerUrl;
			reg.textContent = 'Register';
			links.appendChild( reg );
		}
		d.appendChild( links );
		return d;
	}

	// ---- sidebar (title, meta, business, description) ------------

	// True when the Business Name is effectively the same as the reel title.
	// In that case we skip the redundant "Type: Name" line and instead link
	// the reel title itself to the Business URL (see sidebarHtml).
	function businessIsTitle( reel ) {
		return !! reel.businessName && !! reel.title &&
			reel.businessName.trim().toLowerCase() === reel.title.trim().toLowerCase();
	}

	// Attributes for a Business URL link (external links open in a new tab).
	function businessUrlAttrs( reel ) {
		return reel.businessUrlExternal ? ' target="_blank" rel="nofollow noopener noreferrer external"' : '';
	}

	function businessHtml( reel ) {
		// Suppress the "Type: Name" line when the name just repeats the title.
		var hasBiz = ( reel.businessType || reel.businessName ) && ! businessIsTitle( reel );
		var hasContact = reel.businessPhone || reel.businessAddress;
		var hasActions = reel.actions && reel.actions.length;
		if ( ! hasBiz && ! hasContact && ! hasActions ) { return ''; }

		var h = '<div class="zm-reel__business">';
		if ( hasBiz ) {
			h += '<div class="zm-reel__biz-line">';
			if ( reel.businessType ) {
				h += '<span class="zm-reel__biz-type">' + escapeHtml( reel.businessType ) + ':</span> ';
			}
			if ( reel.businessName ) {
				if ( reel.businessUrl ) {
					h += '<a class="zm-reel__biz-name zm-reel__biz-link" href="' + escapeHtml( reel.businessUrl ) + '"' + businessUrlAttrs( reel ) + '>' + escapeHtml( reel.businessName ) + '</a>';
				} else {
					h += '<span class="zm-reel__biz-name">' + escapeHtml( reel.businessName ) + '</span>';
				}
			}
			h += '</div>';
		}

		// Phone (tap-to-call) + address (Google Maps), where the business type used to sit.
		if ( hasContact ) {
			h += '<div class="zm-reel__contact">';
			if ( reel.businessPhone ) {
				var tel = reel.businessPhone.replace( /[^\d+]/g, '' );
				h += '<a class="zm-reel__phone" href="tel:' + escapeHtml( tel ) + '">' + escapeHtml( reel.businessPhone ) + '</a>';
			}
			if ( reel.businessAddress ) {
				var mapUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent( reel.businessAddress );
				h += '<a class="zm-reel__address" href="' + escapeHtml( mapUrl ) + '" target="_blank" rel="noopener noreferrer external">' + escapeHtml( reel.businessAddress ) + '</a>';
			}
			h += '</div>';
		}

		if ( hasActions ) {
			h += '<div class="zm-reel__actions">';
			reel.actions.forEach( function ( a ) {
				var attrs = a.external ? ' target="_blank" rel="nofollow noopener noreferrer external"' : '';
				h += '<a class="zm-reel__action" href="' + escapeHtml( a.url ) + '"' + attrs + '>' + escapeHtml( a.text ) + '</a>';
			} );
			h += '</div>';
		}
		h += '</div>';
		return h;
	}

	function sidebarHtml( reel ) {
		// When the reel is named after the business, link the title to the Business URL
		// instead of showing a redundant "Type: Name" row below it.
		var titleInner = escapeHtml( reel.title );
		if ( businessIsTitle( reel ) && reel.businessUrl ) {
			titleInner = '<a class="zm-reel__title-link" href="' + escapeHtml( reel.businessUrl ) + '"' + businessUrlAttrs( reel ) + '>' + titleInner + '</a>';
		}
		var html = '<h1 class="zm-reel__title">' + titleInner + '</h1>';
		var meta = '';
		if ( reel.category ) { meta += '<span class="zm-reel__cat">' + escapeHtml( reel.category ) + '</span>'; }
		if ( reel.date ) { meta += '<span class="zm-reel__date">' + escapeHtml( reel.date ) + '</span>'; }
		if ( meta ) { html += '<div class="zm-reel__meta">' + meta + '</div>'; }

		// Business info sits below the category/date, above the post content.
		html += businessHtml( reel );

		if ( reel.description ) {
			html += '<div class="zm-reel__desc">' + reel.description + '</div>';
		}
		return html;
	}

	// ---- likes ---------------------------------------------------

	function buildLikes( reel ) {
		if ( ! social.likes || ! social.likes.enabled ) { return null; }

		var wrap = el( 'div', 'zm-likes' );
		var btn = makeButton( 'zm-like' + ( reel.liked ? ' is-liked' : '' ), '', function () { onLike( reel, btn, count, gateHost ); } );
		btn.setAttribute( 'aria-label', 'Like' );
		btn.innerHTML = '<span class="zm-like__heart">&#10084;</span>';
		var count = el( 'span', 'zm-like__count' );
		count.textContent = reel.likeCount || 0;
		wrap.appendChild( btn );
		wrap.appendChild( count );

		// Hidden container for the (interaction-only) like Turnstile.
		var ts = el( 'div', 'zm-ts zm-ts--like' );
		wrap.appendChild( ts );
		wrap._ts = ts;

		var gateHost = el( 'div', 'zm-likes__gate' );
		wrap.appendChild( gateHost );

		return wrap;
	}

	function onLike( reel, btn, countEl, gateHost ) {
		if ( ! social.loggedIn && ! social.likes.guests ) {
			gateHost.innerHTML = '';
			gateHost.appendChild( authGate( 'Please log in to like this reel.' ) );
			return;
		}
		btn.disabled = true;
		var tsHost = btn.parentNode._ts;
		getTurnstileToken( tsHost, true ).then( function ( token ) {
			ajax( 'zm_reels_like', { reel: reel.id, turnstile: token } ).then( function ( res ) {
				btn.disabled = false;
				if ( res && res.success ) {
					reel.liked = res.data.liked;
					reel.likeCount = res.data.count;
					btn.className = 'zm-like' + ( reel.liked ? ' is-liked' : '' );
					countEl.textContent = reel.likeCount;
				} else {
					var d = ( res && res.data ) || {};
					if ( d.requireAuth ) {
						gateHost.innerHTML = '';
						gateHost.appendChild( authGate( d.message || 'Please log in to like.' ) );
					} else {
						gateHost.innerHTML = '<div class="zm-note">' + escapeHtml( d.message || 'Could not like.' ) + '</div>';
					}
				}
			} );
		} );
	}

	// ---- comments ------------------------------------------------

	function buildComments( reel ) {
		if ( ! social.comments || ! social.comments.enabled ) { return null; }

		var wrap = el( 'div', 'zm-comments' );
		var head = el( 'h2', 'zm-comments__head' );
		head.textContent = 'Comments';
		var counter = el( 'span', 'zm-comments__count' );
		counter.textContent = reel.commentCount ? ' (' + reel.commentCount + ')' : '';
		head.appendChild( counter );
		wrap.appendChild( head );

		var list = el( 'div', 'zm-comments__list' );
		list.innerHTML = '<div class="zm-note">Loading…</div>';
		wrap.appendChild( list );

		// Top-level comment form or auth gate.
		if ( ! social.loggedIn && ! social.comments.guests ) {
			wrap.appendChild( authGate( 'Please log in to join the conversation.' ) );
		} else {
			wrap.appendChild( buildCommentForm( reel, 0, function ( total ) {
				counter.textContent = total ? ' (' + total + ')' : '';
			} ) );
		}

		// Lazy-load the thread for this reel.
		ajax( 'zm_reels_get_comments', { reel: reel.id } ).then( function ( res ) {
			list.innerHTML = '';
			var comments = ( res && res.success && res.data.comments ) ? res.data.comments : [];
			reel.commentCount = comments.length;
			counter.textContent = comments.length ? ' (' + comments.length + ')' : '';
			if ( ! comments.length ) {
				list.appendChild( el( 'div', 'zm-note', 'Be the first to comment.' ) );
				return;
			}
			renderThread( list, buildTree( comments ), 0, reel, counter );
		} );

		wrap._list = list;
		wrap._counter = counter;
		return wrap;
	}

	function buildTree( flat ) {
		var byId = {}, roots = [];
		flat.forEach( function ( c ) { c.children = []; byId[ c.id ] = c; } );
		flat.forEach( function ( c ) {
			if ( c.parent && byId[ c.parent ] ) { byId[ c.parent ].children.push( c ); }
			else { roots.push( c ); }
		} );
		return roots;
	}

	function renderThread( container, nodes, depth, reel, counter ) {
		nodes.forEach( function ( c ) {
			container.appendChild( renderComment( c, depth, reel, counter ) );
			if ( c.children.length ) { renderThread( container, c.children, depth + 1, reel, counter ); }
		} );
	}

	function renderComment( c, depth, reel, counter ) {
		var item = el( 'div', 'zm-cmt' );
		item.style.marginLeft = ( Math.min( depth, 4 ) * 18 ) + 'px';

		var head = el( 'div', 'zm-cmt__head' );
		if ( c.avatar ) {
			var av = document.createElement( 'img' );
			av.className = 'zm-cmt__avatar';
			av.src = c.avatar;
			av.alt = '';
			head.appendChild( av );
		}
		head.appendChild( el( 'span', 'zm-cmt__author', escapeHtml( c.author ) ) );
		head.appendChild( el( 'span', 'zm-cmt__date', escapeHtml( c.date ) ) );
		item.appendChild( head );

		item.appendChild( el( 'div', 'zm-cmt__body', c.content ) );

		// Reply control (respects the same login/guest rules as the main form).
		var canReply = social.loggedIn || social.comments.guests;
		var replyRow = el( 'div', 'zm-cmt__foot' );
		var replyBtn = makeButton( 'zm-cmt__reply', 'Reply', function () { toggleReply( item, c, reel, counter ); } );
		replyRow.appendChild( replyBtn );
		if ( ! canReply ) { replyBtn.style.display = 'none'; }
		item.appendChild( replyRow );

		return item;
	}

	function toggleReply( afterItem, parentComment, reel, counter ) {
		var existing = afterItem.querySelector( ':scope > .zm-reply-host' );
		if ( existing ) { existing.parentNode.removeChild( existing ); return; }
		var host = el( 'div', 'zm-reply-host' );
		host.appendChild( buildCommentForm( reel, parentComment.id, function ( total ) {
			if ( counter ) { counter.textContent = total ? ' (' + total + ')' : ''; }
		}, function () { host.parentNode && host.parentNode.removeChild( host ); } ) );
		afterItem.appendChild( host );
	}

	// Build a comment/reply form. onPosted(totalCount) fires after an approved comment.
	function buildCommentForm( reel, parentId, onPosted, onDone ) {
		var form = el( 'div', 'zm-cform' + ( parentId ? ' zm-cform--reply' : '' ) );

		var nameInput, emailInput, honeypotInput;
		if ( ! social.loggedIn ) {
			var row = el( 'div', 'zm-cform__row' );
			nameInput = document.createElement( 'input' );
			nameInput.type = 'text';
			nameInput.placeholder = 'Name';
			nameInput.className = 'zm-cform__name';
			nameInput.autocomplete = 'name';
			row.appendChild( nameInput );

			emailInput = document.createElement( 'input' );
			emailInput.type = 'email';
			emailInput.placeholder = social.comments.requireEmail ? 'Email' : 'Email (optional)';
			emailInput.className = 'zm-cform__email';
			emailInput.autocomplete = 'email';
			row.appendChild( emailInput );

			form.appendChild( row );

			// Honeypot: a hidden "Website" field. Real visitors never see or fill it,
			// so any value means a bot — the server flags those submissions as spam.
			var hp = el( 'div', 'zm-hp' );
			hp.setAttribute( 'aria-hidden', 'true' );
			var hpLabel = document.createElement( 'label' );
			hpLabel.textContent = 'Website';
			honeypotInput = document.createElement( 'input' );
			honeypotInput.type = 'text';
			honeypotInput.name = 'url';
			honeypotInput.className = 'zm-cform__hp';
			honeypotInput.tabIndex = -1;
			honeypotInput.setAttribute( 'autocomplete', 'off' );
			hpLabel.appendChild( honeypotInput );
			hp.appendChild( hpLabel );
			form.appendChild( hp );
		}

		var textarea = document.createElement( 'textarea' );
		textarea.className = 'zm-cform__text';
		textarea.rows = parentId ? 2 : 3;
		textarea.placeholder = parentId ? 'Write a reply…' : ( social.loggedIn ? 'Comment as ' + social.userName + '…' : 'Add a comment…' );
		form.appendChild( textarea );

		// Turnstile for guests.
		var tsHost = el( 'div', 'zm-ts zm-ts--comment' );
		if ( social.turnstile && social.turnstile.enabled && ! social.loggedIn ) {
			form.appendChild( tsHost );
			// Pre-render so a token is ready by submit time.
			getTurnstileToken( tsHost, false );
		}

		var msg = el( 'div', 'zm-cform__msg' );
		var actions = el( 'div', 'zm-cform__actions' );
		var submit = makeButton( 'zm-btn zm-cform__submit', parentId ? 'Reply' : 'Post', function () {
			doSubmit();
		} );
		actions.appendChild( submit );
		if ( onDone ) {
			actions.appendChild( makeButton( 'zm-btn zm-btn--ghost', 'Cancel', function () { onDone(); } ) );
		}
		form.appendChild( actions );
		form.appendChild( msg );

		function doSubmit() {
			var content = textarea.value.trim();
			if ( ! content ) { msg.textContent = 'Please enter a comment.'; return; }
			if ( nameInput && ! nameInput.value.trim() ) { msg.textContent = 'Please enter your name.'; return; }
			if ( emailInput && social.comments.requireEmail ) {
				var em = emailInput.value.trim();
				if ( ! em || ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( em ) ) {
					msg.textContent = 'Please enter a valid email.';
					return;
				}
			}
			submit.disabled = true;
			msg.textContent = '';

			getTurnstileToken( tsHost, false ).then( function ( token ) {
				if ( social.turnstile && social.turnstile.enabled && ! social.loggedIn && ! token ) {
					submit.disabled = false;
					msg.textContent = 'Please complete the verification.';
					return;
				}
				ajax( 'zm_reels_post_comment', {
					reel: reel.id,
					parent: parentId,
					content: content,
					author: nameInput ? nameInput.value.trim() : '',
					email: emailInput ? emailInput.value.trim() : '',
					website: honeypotInput ? honeypotInput.value.trim() : '',
					turnstile: token
				} ).then( function ( res ) {
					submit.disabled = false;
					var d = ( res && res.data ) || {};
					if ( res && res.success ) {
						if ( d.status === 'approved' && d.comment ) {
							appendApproved( reel, d.comment, parentId, onPosted );
							textarea.value = '';
							if ( onDone ) { onDone(); }
						} else {
							msg.textContent = d.message || 'Thanks! Your comment is awaiting moderation.';
							textarea.value = '';
						}
					} else {
						if ( d.requireAuth ) {
							form.innerHTML = '';
							form.appendChild( authGate( d.message || 'Please log in to comment.' ) );
						} else {
							msg.textContent = d.message || 'Could not post your comment.';
							if ( d.turnstile ) { getTurnstileToken( tsHost, false ); }
						}
					}
				} );
			} );
		}

		return form;
	}

	// Insert a newly-approved comment into the visible list.
	function appendApproved( reel, comment, parentId, onPosted ) {
		var side = app.querySelector( '.zm-reel__side' );
		var commentsWrap = side ? side.querySelector( '.zm-comments' ) : null;
		if ( ! commentsWrap ) { return; }
		var list = commentsWrap._list;
		var counter = commentsWrap._counter;

		var placeholder = list.querySelector( '.zm-note' );
		if ( placeholder ) { placeholder.parentNode.removeChild( placeholder ); }

		reel.commentCount = ( reel.commentCount || 0 ) + 1;
		if ( onPosted ) { onPosted( reel.commentCount ); }
		else if ( counter ) { counter.textContent = ' (' + reel.commentCount + ')'; }

		comment.children = [];
		if ( parentId ) {
			// Place the reply after its parent node.
			var nodes = list.querySelectorAll( '.zm-cmt' );
			var parentEl = null, parentDepth = 0;
			for ( var i = 0; i < nodes.length; i++ ) {
				if ( nodes[ i ].__cid === parentId ) { parentEl = nodes[ i ]; parentDepth = nodes[ i ].__depth || 0; break; }
			}
			var node = renderComment( comment, parentDepth + 1, reel, counter );
			node.__cid = comment.id; node.__depth = parentDepth + 1;
			if ( parentEl && parentEl.nextSibling ) { list.insertBefore( node, parentEl.nextSibling ); }
			else { list.appendChild( node ); }
		} else {
			var top = renderComment( comment, 0, reel, counter );
			top.__cid = comment.id; top.__depth = 0;
			list.appendChild( top );
		}
	}

	// ---- render one reel ----------------------------------------

	function updateMuteButton( btn ) {
		btn.innerHTML = muted ? '&#128263;' : '&#128266;';
		btn.setAttribute( 'aria-label', muted ? 'Unmute' : 'Mute' );
	}

	function render() {
		var reel = reels[ idx ];
		app.innerHTML = '';

		var wrap = el( 'div', 'zm-reel' );
		var stage = el( 'div', 'zm-reel__stage' );
		currentVideo = null;

		if ( 'file' === reel.type && reel.src ) {
			var video = document.createElement( 'video' );
			video.className = 'zm-reel__video';
			video.src = reel.src;
			if ( reel.poster ) { video.poster = reel.poster; }
			video.loop = true;
			video.muted = muted;
			video.autoplay = true;
			video.setAttribute( 'playsinline', '' );
			video.setAttribute( 'webkit-playsinline', '' );
			video.preload = 'auto';
			video.addEventListener( 'click', function () { if ( video.paused ) { video.play(); } else { video.pause(); } } );
			stage.appendChild( video );
			currentVideo = video;
			var playAttempt = video.play();
			if ( playAttempt && playAttempt.catch ) { playAttempt.catch( function () {} ); }
		} else if ( 'embed' === reel.type && reel.src ) {
			var frame = document.createElement( 'iframe' );
			frame.className = 'zm-reel__embed';
			frame.src = reel.src;
			frame.setAttribute( 'allow', 'autoplay; fullscreen; encrypted-media; picture-in-picture' );
			frame.setAttribute( 'allowfullscreen', '' );
			frame.setAttribute( 'title', reel.title || 'Reel' );
			stage.appendChild( frame );
		} else if ( reel.poster ) {
			var img = document.createElement( 'img' );
			img.className = 'zm-reel__poster-fallback';
			img.src = reel.poster;
			img.alt = reel.title || '';
			stage.appendChild( img );
		}

		var prev = makeButton( 'zm-reel__nav zm-reel__nav--prev', '&#8249;', function () { go( -1 ); } );
		var next = makeButton( 'zm-reel__nav zm-reel__nav--next', '&#8250;', function () { go( 1 ); } );
		prev.disabled = idx <= 0;
		next.disabled = idx >= reels.length - 1;
		prev.setAttribute( 'aria-label', 'Previous reel' );
		next.setAttribute( 'aria-label', 'Next reel' );
		stage.appendChild( prev );
		stage.appendChild( next );

		if ( currentVideo ) {
			var mute = makeButton( 'zm-reel__mute', '', function () {
				muted = ! muted;
				if ( currentVideo ) {
					currentVideo.muted = muted;
					if ( ! muted && currentVideo.paused ) { currentVideo.play(); }
				}
				updateMuteButton( mute );
			} );
			updateMuteButton( mute );
			stage.appendChild( mute );
		}

		// Sidebar.
		var side = el( 'div', 'zm-reel__side' );
		side.innerHTML = sidebarHtml( reel );

		var likes = buildLikes( reel );
		if ( likes ) { side.appendChild( likes ); }

		var comments = buildComments( reel );
		if ( comments ) { side.appendChild( comments ); }

		var close = makeButton( 'zm-reel__close', '&#10005;', closeViewer );
		close.setAttribute( 'aria-label', 'Close' );

		var count = el( 'div', 'zm-reel__count' );
		count.textContent = ( idx + 1 ) + ' / ' + reels.length;

		wrap.appendChild( stage );
		wrap.appendChild( side );
		app.appendChild( wrap );
		app.appendChild( close );
		app.appendChild( count );

		// Each reel starts at the video; on mobile show the scroll hint.
		app.scrollTop = 0;
		addScrollHint();
	}

	function go( dir ) {
		var target = idx + dir;
		if ( target < 0 || target >= reels.length ) { return; }
		idx = target;
		render();
		var reel = reels[ idx ];
		try { history.pushState( { zmReel: idx }, '', reelUrl( reel ) ); } catch ( e ) {}
		document.title = reel.title;
	}

	function closeViewer() {
		window.location.href = data.home;
	}

	// ---- input handlers ------------------------------------------

	// Ignore navigation keys while typing in a comment field.
	function inField( e ) {
		var t = e.target;
		return t && ( t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( inField( e ) ) { if ( e.key === 'Escape' ) { e.target.blur(); } return; }
		if ( e.key === 'ArrowRight' || e.key === 'ArrowDown' ) { e.preventDefault(); go( 1 ); }
		else if ( e.key === 'ArrowLeft' || e.key === 'ArrowUp' ) { e.preventDefault(); go( -1 ); }
		else if ( e.key === 'Escape' ) { closeViewer(); }
	} );

	// Desktop scroll: navigate between reels (the sidebar scrolls normally).
	// On mobile the page scrolls to reveal the sidebar, so wheel/swipe do NOT
	// navigate there — the on-screen arrows handle that.
	window.addEventListener( 'wheel', function ( e ) {
		if ( isMobile() ) { return; }
		if ( e.target && e.target.closest && e.target.closest( '.zm-reel__side' ) ) { return; }
		if ( Math.abs( e.deltaY ) < 20 ) { return; }
		var now = Date.now();
		if ( now - lastNav < 650 ) { return; }
		lastNav = now;
		go( e.deltaY > 0 ? 1 : -1 );
	}, { passive: true } );

	var touchY = null;
	window.addEventListener( 'touchstart', function ( e ) {
		if ( isMobile() ) { touchY = null; return; }
		if ( e.target && e.target.closest && e.target.closest( '.zm-reel__side' ) ) { touchY = null; return; }
		touchY = e.touches[ 0 ].clientY;
	}, { passive: true } );
	window.addEventListener( 'touchend', function ( e ) {
		if ( touchY === null ) { return; }
		var dy = e.changedTouches[ 0 ].clientY - touchY;
		if ( Math.abs( dy ) > 50 ) { go( dy < 0 ? 1 : -1 ); }
		touchY = null;
	}, { passive: true } );

	window.addEventListener( 'popstate', function () {
		var path = window.location.pathname.replace( /\/+$/, '' );
		var slug = path.split( '/' ).pop();
		for ( var i = 0; i < reels.length; i++ ) {
			if ( reels[ i ].slug === slug ) {
				idx = i;
				render();
				document.title = reels[ i ].title;
				return;
			}
		}
	} );

	render();
} )();
