<?php
/**
 * Drop down mobile style template part.
 *
 * @package OceanWP WordPress theme
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'dropdown' !== oceanwp_mobile_menu_style()
	|| ! oceanwp_display_navigation() ) {
	return;
}

// Navigation classes.
$classes = array( 'clr' );

// If social.
if ( true === get_theme_mod( 'ocean_menu_social', false ) ) {
	$classes[] = 'has-social';
}

// Turn classes into space seperated string.
$classes = implode( ' ', $classes );

// Menu Location.
$menu_location = apply_filters( 'ocean_main_menu_location', 'main_menu' );

// Dropdown menu attributes.
$dropdown_menu_attrs = apply_filters( 'oceanwp_attrs_mobile_dropdown', '' );

// Menu arguments.
$menu_args = array(
	'theme_location' => $menu_location,
	'container'      => false,
	'fallback_cb'    => false,
);

// Check if custom menu.
if ( $menu = oceanwp_header_custom_menu() ) {
	$menu_args['menu'] = $menu;
}

// Left menu for the Center header style.
$left_menu = get_theme_mod( 'ocean_center_header_left_menu' );
$left_menu = '0' !== $left_menu ? $left_menu : '';
$left_menu = apply_filters( 'ocean_center_header_left_menu', $left_menu );

// Menu arguments.
$left_menu_args = array(
	'menu'        => $left_menu,
	'container'   => false,
	'fallback_cb' => false,
);

// Top bar menu Location.
$top_menu_location = 'topbar_menu';

// Menu arguments.
$top_menu_args = array(
	'theme_location' => $top_menu_location,
	'container'      => false,
	'fallback_cb'    => false,
);

// Get close menu text.
$close_text = get_theme_mod( 'ocean_mobile_menu_close_text' );
$close_text = oceanwp_tm_translation( 'ocean_mobile_menu_close_text', $close_text );
$close_text = $close_text ? $close_text : esc_html__( 'Close', 'oceanwp' );

?>

<div id="mobile-dropdown" class="clr" <?php echo $dropdown_menu_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<nav class="<?php echo esc_attr( $classes ); ?>"<?php oceanwp_schema_markup( 'site_navigation' ); ?>>

		<?php
		// If has mobile menu.
		if ( has_nav_menu( 'mobile_menu' ) ) {
			get_template_part( 'partials/mobile/mobile-nav' );
		} else {

			// If has center header style and left menu.
			if ( 'center' === oceanwp_header_style()
				&& $left_menu ) {
				wp_nav_menu( $left_menu_args );
			}

			// Navigation.
			wp_nav_menu( $menu_args );

			// If has top bar menu.
			if ( has_nav_menu( $top_menu_location ) ) {
				wp_nav_menu( $top_menu_args );
			}
		}

		// Social.
		if ( true === get_theme_mod( 'ocean_menu_social', false ) ) {
			get_template_part( 'partials/header/social' );
		}

		// Mobile search form.
		if ( get_theme_mod( 'ocean_mobile_menu_search', true ) ) {
			get_template_part( 'partials/mobile/mobile-search' );
		}
		?>
<div style="display:flex;align-items:center;justify-content:center;padding:20px 0;">
				<?php if ( ! is_user_logged_in() ) : ?>
<div style="display:flex;align-items:center;padding:8px;box-shadow: 0px 2px 10px 2px #5f5b8ec7;margin-left:2rem;border-radius:5px">
    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#FACC15" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>
        <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
    </svg>
    <a href="https://aihomeworkhelper.co/login/" style="font-size:16px;font-weight:600;padding-left:5px">LOGIN</a>
</div>
<?php endif; ?>
				<div style="margin-left:3rem;padding:8px 0;">
					<a href="https://wa.me/+16163145029">
					<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#FACC15" class="bi bi-whatsapp" viewBox="0 0 16 16">
  <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
</svg>
					</a>
				</div>
		</div>

	</nav>

</div>
