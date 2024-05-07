<?php
/**
 * Header menu template part.
 *
 * @package OceanWP WordPress theme
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

static $ocean_nav_plugin = false;

// Retunr if disabled.
if ( ! oceanwp_display_navigation() ) {
	return;
}

// Header style.
$header_style = oceanwp_header_style();

// Get ID.
$template = oceanwp_custom_nav_template();

// Check if page is Elementor page.
$elementor = get_post_meta( $template, '_elementor_edit_mode', true );

// Get content.
$get_content = oceanwp_nav_template_content();

// Get classes for the header menu.
$wrap_classes  = oceanwp_header_menu_classes( 'wrapper' );
$inner_classes = oceanwp_header_menu_classes( 'inner' );

// Nav attributes.
$owp_nav_attrs = apply_filters( 'oceanwp_attrs_main_nav', '' );

if ( ! empty( $template ) && ! defined( 'OCEANWP_NAV_SHORTCODE_DONE' ) ) {
	do_action( 'ocean_before_nav' );

	if ( preg_match( '(oceanwp_nav|ocean_wp)', $get_content ) === 1 ) {
		define( 'OCEANWP_NAV_SHORTCODE_DONE', true );
	}

	// If is not full screen header style.
	if ( 'full_screen' !== $header_style ) { ?>
		<div id="site-navigation-wrap" class="<?php echo esc_attr( $wrap_classes ); ?>">
	<?php } ?>

		<?php do_action( 'ocean_before_nav_inner' ); ?>

		<?php
		if ( OCEANWP_ELEMENTOR_ACTIVE && $elementor ) {

			// If Elementor.
			OceanWP_Elementor::get_nav_content();

		} elseif ( OCEANWP_BEAVER_BUILDER_ACTIVE && ! empty( $template ) ) {

			// If Beaver Builder.
			echo do_shortcode( '[fl_builder_insert_layout id="' . $template . '"]' );

		}  else if ( class_exists( 'SiteOrigin_Panels' ) && get_post_meta( $template, 'panels_data', true ) ) {

			echo SiteOrigin_Panels::renderer()->render( $template );

		} else {

			// If Gutenberg.
			if ( ocean_is_block_template( $template ) ) {
				$get_content = apply_filters( 'oceanwp_nav_template_content', do_blocks( $get_content ) );
			}

			// Display template content.
			echo do_shortcode( $get_content );

		}
		?>

		<?php do_action( 'ocean_after_nav_inner' ); ?>

	<?php
	// If is not full screen header style.
	if ( 'full_screen' !== $header_style ) {
		?>
		</div><!-- #site-navigation-wrap -->
		<?php
	}
	?>

	<?php do_action( 'ocean_after_nav' ); ?>

	<?php

} else {

	// Menu Location.
	$menu_location = apply_filters( 'ocean_main_menu_location', 'main_menu' );

	// Multisite global menu.
	$ms_global_menu = apply_filters( 'ocean_ms_global_menu', false );

	// Display menu if defined.
	if ( has_nav_menu( $menu_location ) || $ms_global_menu ) :

		// Get menu classes.
		$menu_classes = array( 'main-menu' );

		// If full screen header style.
		if ( 'full_screen' === $header_style ) {
			$menu_classes[] = 'fs-dropdown-menu';
		} else {
			$menu_classes[] = 'dropdown-menu';
		}

		// If is not full screen or vertical header style.
		if ( 'full_screen' !== $header_style
			&& 'vertical' !== $header_style ) {
			$menu_classes[] = 'sf-menu';
		}

		// Turn menu classes into space seperated string.
		$menu_classes = implode( ' ', $menu_classes );

		// Menu arguments.
		$menu_args = array(
			'theme_location' => $menu_location,
			'menu_class'     => $menu_classes,
			'container'      => false,
			'fallback_cb'    => false,
			'link_before'    => '<span class="text-wrap">',
			'link_after'     => '</span>',
			'walker'         => new OceanWP_Custom_Nav_Walker(),
		);

		// Check if custom menu.
		if ( $menu = oceanwp_header_custom_menu() ) {
			$menu_args['menu'] = $menu;
		}

		do_action( 'ocean_before_nav' );

		// If is not full screen header style.
		if ( 'full_screen' !== $header_style ) {
			?>
			<div id="site-navigation-wrap" class="<?php echo esc_attr( $wrap_classes ); ?>">
			<?php
		}
		?>

			<?php do_action( 'ocean_before_nav_inner' ); ?>
				
			<?php
			// Add container if is medium header style.
			if ( 'medium' === $header_style ) {
				?>
				<div class="container clr">
				<?php
			}
			?>
			<nav id="site-navigation" style="display:flex;align-items:center;justify-content:space-between;" class="<?php echo esc_attr( $inner_classes ); ?>"<?php oceanwp_schema_markup( 'site_navigation' ); ?> role="navigation" <?php echo $owp_nav_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				
				<?php
				// Display global multisite menu.
				if ( is_multisite() && $ms_global_menu ) :

					switch_to_blog( 1 );
					wp_nav_menu( $menu_args );
					restore_current_blog();

				else :

					// Display this site's menu.
					wp_nav_menu( $menu_args );

				endif;

				// If is not top menu header style.
				if ( 'top' !== $header_style
					&& 'full_screen' !== $header_style
					&& 'vertical' !== $header_style ) {

					// Header search.
					if ( 'drop_down' === oceanwp_menu_search_style() ) {
						get_template_part( 'partials/header/search-dropdown' );
					} elseif ( 'header_replace' === oceanwp_menu_search_style() ) {
						get_template_part( 'partials/header/search-replace' );
					}
				}

				// Social links if full screen header style.
				if ( 'full_screen' === $header_style
					&& true === get_theme_mod( 'ocean_menu_social', false ) ) {
					get_template_part( 'partials/header/social' );
				}
				?>
		
			<?php if ( ! is_user_logged_in() ) : ?>
<div style="padding:8px;box-shadow: 0px 0px 3px 0px #eee;margin-left:2rem;border-radius:5px">
   <a href="https://aihomeworkhelper.co/login/" style="display:flex;align-items:center;">
	<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#FACC15" class="bi bi-pencil-square" viewBox="0 0 16 16">
  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
</svg>
    <span style="font-size:16px;font-weight:600;padding-left:8px">Start Writing - It's Free</span>
	</a>
</div>
<?php endif; ?>
				
			
			</nav><!-- #site-navigation -->

			<?php
			// Add container if is medium header style.
			if ( 'medium' === $header_style ) {
				?>
				</div>
				<?php
			}
			?>

			<?php do_action( 'ocean_after_nav_inner' ); ?>

		<?php
		// If is not full screen header style.
		if ( 'full_screen' !== $header_style ) {
			?>
			</div><!-- #site-navigation-wrap -->
			<?php
		}
		?>

		<?php do_action( 'ocean_after_nav' ); ?>

	<?php endif;

}
?>
