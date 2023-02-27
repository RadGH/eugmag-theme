<?php
/* 
This template creates the tabbed navigation on the ad section. The two mandatory links are "Manage My Ads" and "Buy New Ad". Additional links can be added in the options menu.
*/


// Count the number of ads that have been purchased by this user.
// Note: We just need the "found_posts" value.
$args = array(
	'post_type' => 'ld_ad',
	'post_author' => get_current_user_id(),
	'post_status' => array('publish', 'future', 'draft', 'pending', 'private'),
	'posts_per_page' => 0,
	'meta_query' => array(
		array(
			'key' => 'customer',
			'value' => get_current_user_id(),
			'compare' => '=',
		),
	),
	'fields' => 'ids',
);

$i = new WP_Query($args);
$purchased_ad_count = $i->found_posts;

$menu = array(
	'dashboard' => array(
		'id' => ldadstore_get_dashboard_page_id(),
		'title' => 'Manage My Ads',
		'url' => get_permalink( ldadstore_get_dashboard_page_id() ),
		'disabled' => ($purchased_ad_count < 1),
	),
	'store' => array(
		'id' => ldadstore_get_store_page_id(),
		'title' => 'Buy New Ad',
		'url' => get_permalink( ldadstore_get_store_page_id() ),
		'disabled' => !is_user_logged_in()
	),
);

$menu = apply_filters( 'ld_ad_store_menu', $menu );
// if you add menu items, make sure to call the navigation before the content filter using:
// include( LDAdStore_PATH . '/advertisements-store/templates/parts/navigation.php' );
?>
<div class="ad-store-navigation">
	<?php
	foreach( $menu as $key => $item ) {
		$html_attrs = array();

		$classes = array('ad-menu-item');
		$classes[] = 'ad-menu-' . $key;

		if ( $item['disabled'] ) {
			$classes[] = 'disabled';
			$html_attrs['disabled'] = false;
			$html_attrs['onclick'] = "return false;";
			$item['url'] = "#";
		}

		if ( $item['id'] && $item['id'] === get_the_ID() ) {
			$classes[] = 'current-menu-item';
		}

		$html_attrs['class'] = implode(' ', $classes);
		$attrs = '';

		foreach( $html_attrs as $k => $v ) {
			if ( $v !== false ) $attrs.= " " . $k . '="' . esc_attr($v) . '"';
			else $attrs.= " " . $k;
		}

		printf(
			'<a href="%s"%s>%s</a>',
			esc_attr($item['url']),
			$attrs,
			esc_html($item['title'])
		);
	}
	?>
</div>