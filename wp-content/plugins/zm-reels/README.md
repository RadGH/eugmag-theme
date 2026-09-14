# ZM: Reels

A "Reels" post type (Facebook Reels / YouTube Shorts / TikTok style) with a full-screen video
viewer. Uses ACF for the custom fields and the classic editor (Gutenberg is disabled for reels).

## Endpoints

- **`/reels/`** — the viewer, starting at the **latest** reel.
- **`/reels/{slug}/`** — the viewer, starting at that specific reel (deep-linkable).

Both load `assets/reels.js`, which renders a full-screen viewer (not a real modal — it's a real page).

## Editing reels

**Reels → Add New.** Each reel has:

| Field | Source | Notes |
|-------|--------|-------|
| Title | Post title | Shown in the sidebar. |
| Description | Classic editor content | Shown in the sidebar (HTML). |
| Category | `Reel Categories` taxonomy | Shown in the sidebar; also filterable at `/reel-category/{term}/`. |
| Date Released | ACF `reel_date_released` | Optional. Defaults to publish date. **Controls order — newest first.** |
| Video File | ACF `reel_video` | Upload an mp4 (portrait 9:16 looks best). |
| Video URL | ACF `reel_video_url` | Optional. A **YouTube**, **Vimeo**, or direct (.mp4) URL. If set, used instead of the uploaded file. |
| Poster | Featured Image | Optional. Used as the video poster / fallback image. |
| Business Type | ACF `business_type` | e.g. `Restaurant`, `Magazine`. Shown as "Type: Name". |
| Business Name | ACF `business_name` | e.g. `City Life Monthly`. |
| Actions | ACF `reel_actions` (repeater) | Text + URL rows, shown as buttons (e.g. "Visit our website"). |

**Video source:** each reel plays either an uploaded/direct file (in a `<video>` element with a
mute toggle) **or** a YouTube/Vimeo link (in an `<iframe>` using the platform's own controls). The
plugin auto-detects which from the URL — YouTube `watch`/`youtu.be`/`shorts`/`embed` links and Vimeo
`vimeo.com/ID` (incl. unlisted `/HASH`) links are recognized; anything else is treated as a direct
file.

### Business info & action links

Shown in the sidebar **below the category/date and above the description**: `Business Type: Business
Name`, followed by the action buttons. Action URLs that point to another domain automatically open in
a new tab with `target="_blank" rel="nofollow noopener noreferrer external"`; internal links open
normally.

## Likes, comments & settings

**Reels → Settings** (an ACF options page) controls the social features:

- **Enable Likes** + **Allow guests to like**
- **Enable Comments** + **Allow guests to comment**
- **Cloudflare Turnstile** site key + secret key

When guests are **not** allowed for a feature, the viewer shows **Log in** / **Register** buttons
instead (Register only appears when *Settings → General → Anyone can register* is on).

**Likes** are stored in a custom table (`{prefix}zm_reel_likes`). Logged-in likes are de-duplicated
by user ID; guest likes by **IP address** (the IP is logged), so one guest per IP can like a reel
once (clicking again un-likes).

**Comments & replies** use native WordPress comments, so they appear in the normal **Comments** admin
screen and obey your **Settings → Discussion** rules (moderation, "must have a previously approved
comment", etc.). Guest comments record the author IP and run through `wp_new_comment()` — meaning any
anti-spam plugin hooked to `preprocess_comment` applies. Logged-in comments use the default flow.

**Cloudflare Turnstile:** when both keys are filled in, **guests** must pass a Turnstile check before
a like or comment is accepted (verified server-side against Cloudflare). Logged-in users are not
challenged. Leave the keys blank to disable Turnstile. Get keys at *dash.cloudflare.com → Turnstile*.

### Recommended anti-spam plugins (besides Akismet)

Because comments go through `wp_new_comment()`, standard comment anti-spam plugins work with no code
changes:

- **Antispam Bee** — free, no account, GDPR-friendly; hooks `preprocess_comment`. Works out of the box.
- **Titan Anti-spam & Security** — heuristic comment filtering, no external service.
- **WP Armour – Honeypot** — adds an invisible honeypot field to comment/other forms.

Turnstile (built in here) already blocks most bots at the point of submission, so Antispam Bee +
Turnstile is a solid, Akismet-free combo. **Note:** honeypot plugins that inject a hidden field into
the *theme's* comment form won't automatically appear in the reels viewer (the viewer builds its own
form via JS); they still catch bots that POST directly, but for in-viewer coverage prefer plugins
that filter on `preprocess_comment` (Antispam Bee, Titan) rather than ones that rely on a form field.

## Viewer controls

- **Right arrow / Down arrow / scroll down / swipe up** → next reel
- **Left arrow / Up arrow / scroll up / swipe down** → previous reel
- **Close (✕) / Escape** → returns to the site front page
- **Tap the video** → play / pause. **Speaker button** → mute / unmute (starts muted so it autoplays).

The URL updates to `/reels/{slug}/` as you navigate (via `history.pushState`), so browser
back/forward moves between reels and any reel is shareable.

**On mobile**, the video fills the screen and the sidebar (business info, likes, comments) sits
**below** it — scroll down to reach it. A brief "Scroll for more" hint with a bouncing chevron shows
on each reel (the text fades after a few seconds; the hint disappears once you scroll). Because a
vertical swipe scrolls the page there, use the on-screen **‹ ›** arrows to move between reels on
mobile.

## Notes for developers

- Code is split into `includes/` — `settings.php` (options page + getters), `likes.php` (table +
  AJAX), `comments.php` (AJAX), `turnstile.php` (verify).
- The ordered reel list is built in `zm_reels_get_data()` and handed to JS via `wp_localize_script`
  as `window.ZM_REELS` (`{ reels, start, home, base, social }`). Per-reel it includes
  `businessType`, `businessName`, `actions`, `likeCount`, `liked`, `commentCount`.
- The viewer template is `templates/reels-viewer.php` (loaded via `template_include`) — a minimal
  full-screen page with `wp_head()` / `wp_footer()` but no theme header/footer.
- Likes/comments post to `admin-ajax.php` (actions `zm_reels_like`, `zm_reels_get_comments`,
  `zm_reels_post_comment`) with a `zm_reels` nonce.
- The client IP used for guest likes is filterable via `zm_reels_client_ip` (handy behind Cloudflare,
  where you may want `CF-Connecting-IP`). It defaults to `REMOTE_ADDR`.
- Rewrite rules are flushed on activation/deactivation. If `/reels/` ever 404s after code changes,
  re-save **Settings → Permalinks**.
- Comments rely on WordPress commenting being enabled site-wide. If a "disable comments" feature is
  active (e.g. A+A Security Manager's comment toggle), enable comments there or reels can't accept new
  ones — the plugin no longer forces comments open on its own.

## Requirements

- **ACF Pro** — the settings page (options page) and the Actions repeater require ACF Pro. The
  video/business/date fields work with ACF free; likes/comments toggles fall back to sensible
  defaults (likes on, guest likes on, comments on, guest comments off) if ACF is inactive.
- Reactivate the plugin (or visit any admin page) once after updating so the `zm_reel_likes` table is
  created.
