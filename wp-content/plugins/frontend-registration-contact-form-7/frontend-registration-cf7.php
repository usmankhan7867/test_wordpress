<?php
/**
 * Plugin Name: Frontend Registration - Contact Form 7
 * Plugin URL: http://www.wpbuilderweb.com/frontend-registration-contact-form-7/
 * Description:  This plugin will convert your Contact form 7 in to registration form for WordPress. PRO Plugin available now with New Features. <strong>PRO Version is also available with New Features.</strong>. 
 * Version: 5.1
 * Author: David Pokorny
 * Author URI: http://www.wpbuilderweb.com
 * Developer: Pokorny David
 * Developer E-Mail: parmarcrish@gmail.com
 * Text Domain: contact-form-7-freg
 * Domain Path: /languages
 * 
 * Copyright: © 2009-2015 izept.com.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
/**
 * 
 * @access      public
 * @since       1.1
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

define( 'FRCF7_VERSION', '5.1' );

define( 'FRCF7_REQUIRED_WP_VERSION', '4.0' );

define( 'FRCF7_PLUGIN', __FILE__ );

define( 'FRCF7_PLUGIN_BASENAME', plugin_basename( FRCF7_PLUGIN ) );

define( 'FRCF7_PLUGIN_NAME', trim( dirname( FRCF7_PLUGIN_BASENAME ), '/' ) );

define( 'FRCF7_PLUGIN_DIR', untrailingslashit( dirname( FRCF7_PLUGIN ) ) );

define( 'FRCF7_PLUGIN_CSS_DIR', FRCF7_PLUGIN_DIR . '/css' );

add_action('plugins_loaded', function () {
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
    if (!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
          deactivate_plugins('frontend-registration-contact-form-7/frontend-registration-cf7.php');
          add_action('admin_notices', 'cf7fr_admin_notice');
    } else {
		require_once (dirname(__FILE__) . '/frontend-registration-opt-cf7.php');
    }
});

/**
 * cf7fr_admin_notice
 *
 * @return void
 */
function cf7fr_admin_notice()
{
    echo '<div class="error"><p>Plugin deactivated. Please activate contact form 7 plugin!</p></div>';
	return false;
}

function cf7fr_editor_panels_reg ( $panels ) {
		
		$new_page = array(
			'Error' => array(
				'title' => __( 'Registration Settings', 'contact-form-7' ),
				'callback' => 'cf7fr_admin_reg_additional_settings'
			)
		);
		
		$panels = array_merge($panels, $new_page);
		
		return $panels;
		
	}
	add_filter( 'wpcf7_editor_panels', 'cf7fr_editor_panels_reg' );

add_filter('plugin_row_meta',  'my_register_plugins_link', 10, 2);
function my_register_plugins_link ($links, $file) {
   $base = plugin_basename(__FILE__);
   if ($file == $base) {
       $links[] = '<a href="http://www.wpbuilderweb.com/frontend-registration-contact-form-7/">' . __('PRO Version') . '</a>';
       $links[] = '<a href="http://www.wpbuilderweb.com/shop">' . __('More Plugins by David Pokorny') . '</a>';
       $links[] = '<a href="https://www.wpbuilderweb.com/donation-free-plugins/">' . __('Donate') . '</a>';
   }
   return $links;
}
function cf7fr_admin_reg_additional_settings( $cf7 )
{
	
	$post_id = sanitize_text_field($_GET['post']);
	$tags = $cf7->scan_form_tags();
	$cf7frenable = get_post_meta($post_id, "_cf7fr_enable_registration", true);
	$cf7fru = get_post_meta($post_id, "_cf7fru_", true);
	$cf7fre = get_post_meta($post_id, "_cf7fre_", true);
	$cf7frr = get_post_meta($post_id, "_cf7frr_", true);
	$enablemail = get_post_meta($post_id, "_cf7fr_enablemail_registration", true);
	$autologinfield = get_post_meta($post_id, "_cf7fr_autologinfield_reg", true);
	$loginurlmail = get_post_meta($post_id, "_cf7fr_loginurlmail_reg", true);
	$loginurlformail = get_post_meta($post_id, "_cf7fr_loginurlformail_reg", true);
	$selectedrole = $cf7frr;
	if(!$selectedrole)
	{
		$selectedrole = 'subscriber';
	}
	if ($cf7frenable == "1") { $cf7frechecked = "CHECKED"; } else { $cf7frechecked = ""; }
	if ($enablemail == "1") { $checkedmail = "CHECKED"; } else { $checkedmail = ""; }
	if ($autologinfield == "1") { $autologinfield = "CHECKED"; } else { $autologinfield = ""; }
	if ($loginurlmail == "1") { $loginurlmail = "CHECKED"; } else { $loginurlmail = ""; }
	if ($loginurlformail != "") { $loginurlformail = $loginurlformail; } else { $loginurlformail = ""; }
	
	$selected = "";
	$admin_cm_output = "";
	
		$admin_cm_output .= "<div id='additional_settings-sortables' class='meta-box'><div id='additionalsettingsdiv'>";
			$admin_cm_output .= "<h2 class='hndle ui-sortable-handle'><span>Frontend Registration Settings:</span></h2>";
			$admin_cm_output .= "<div class='inside'>";
			
			$admin_cm_output .= "<div class='mail-field pretty p-switch p-fill'>";
			$admin_cm_output .= "<input name='cf7frenable' value='1' type='checkbox' $cf7frechecked>";
			$admin_cm_output .= "<div class='state'><label>Enable Registration on this form</label></div>";
			$admin_cm_output .= "</div>";

			$admin_cm_output .= "<div class='mail-field pretty p-switch p-fill'>";
			$admin_cm_output .= "<input name='enablemail' value='' type='checkbox' $checkedmail>";
			$admin_cm_output .= "<div class='state'><label>Skip Contact Form 7 Mails ?</label></div>";
			$admin_cm_output .= "</div>";

			$admin_cm_output .= "<div class='mail-field pretty p-switch p-fill'>";
            $admin_cm_output .= "<input name='autologinfield' value='' type='checkbox' $autologinfield>";
            $admin_cm_output .= "<div class='state'><label>Enable auto login after registration? </label></div>";
            $admin_cm_output .= "</div>";

            $admin_cm_output .= "<div class='mail-field pretty p-switch p-fill'>";
            $admin_cm_output .= "<input name='loginurlmail' value='' type='checkbox' $loginurlmail>";
            $admin_cm_output .= "<div class='state'><label>Enable sent Login URL in Mail. </label></div>";
            $admin_cm_output .= "</div>";

            $admin_cm_output .= "<div class='mail-field'>";
            $admin_cm_output .= "<br/><div class='state'><label>Set Custom Login URL for email :</label></div>";
            $admin_cm_output .= "<input name='loginurlformail' value='".$loginurlformail."' type='text' ><br/>";
            $admin_cm_output .= "</div>";

			$admin_cm_output .= "<table>";

			$admin_cm_output .= "<div class='handlediv' title='Click to toggle'><br></div><h2 class='hndle ui-sortable-handle'><span>Frontend Fields Settings:</span></h2>";

			$admin_cm_output .= "<tr><td>Selected Field Name For User Name :</td></tr>";
			$admin_cm_output .= "<tr><td><select name='_cf7fru_'>";
			$admin_cm_output .= "<option value=''>Select Field</option>";
			foreach ($tags as $key => $value) {
				if($cf7fru==$value['name']){$selected='selected=selected';}else{$selected = "";}			
				$admin_cm_output .= "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
			}
			$admin_cm_output .= "</select>";
			$admin_cm_output .= "</td></tr>";

			$admin_cm_output .= "<tr><td>Selected Field Name For Email :</td></tr>";
			$admin_cm_output .= "<tr><td><select name='_cf7fre_'>";
			$admin_cm_output .= "<option value=''>Select Field</option>";
			foreach ($tags as $key => $value) {
				if($cf7fre==$value['name']){$selected='selected=selected';}else{$selected = "";}
				$admin_cm_output .= "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
			}
			$admin_cm_output .= "</select>";
			$admin_cm_output .= "</td></tr><tr><td>";
			$admin_cm_output .= "<input type='hidden' name='email' value='2'>";
			$admin_cm_output .= "<input type='hidden' name='post' value='$post_id'>";
			$admin_cm_output .= "</td></tr>";
			$admin_cm_output .= "<tr><td>Selected User Role:</td></tr>";
			$admin_cm_output .= "<tr><td>";
			$admin_cm_output .= "<select name='_cf7frr_'>";
			$editable_roles = get_editable_roles();
		    foreach ( $editable_roles as $role => $details ) {
		     $name = translate_user_role($details['name'] );
		         if ( $selectedrole == $role ) // preselect specified role
		             $admin_cm_output .= "<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
		         else
		             $admin_cm_output .= "<option value='" . esc_attr($role) . "'>$name</option>";
		    }
		    $admin_cm_output .="</select>";
			$admin_cm_output .= "</td></tr>";
			$admin_cm_output .="</table>";
			$admin_cm_output .= "</div>";
			$admin_cm_output .= "</div>";
		$admin_cm_output .= "</div>";

	echo $admin_cm_output;
	
}
// hook into contact form 7 admin form save
add_action('wpcf7_save_contact_form', 'cf7_save_reg_contact_form');


function cf7_save_reg_contact_form( $cf7 ) 
{

		$tags = $cf7->scan_form_tags();

		
		$post_id = sanitize_text_field($_POST['post_ID']);
		
		if (!empty($_POST['cf7frenable'])) {
			$enable = sanitize_text_field($_POST['cf7frenable']);
			update_post_meta($post_id, "_cf7fr_enable_registration", $enable);
		} else {
			update_post_meta($post_id, "_cf7fr_enable_registration", 0);
		}
		if (isset($_POST['enablemail'])) {
			update_post_meta($post_id, "_cf7fr_enablemail_registration", 1);
		} else {
			update_post_meta($post_id, "_cf7fr_enablemail_registration", 0);
		}

		if (isset($_POST['autologinfield'])) {
            update_post_meta($post_id, "_cf7fr_autologinfield_reg", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_autologinfield_reg", 0);
        }

        if (isset($_POST['loginurlmail'])) {
            update_post_meta($post_id, "_cf7fr_loginurlmail_reg", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_loginurlmail_reg", 0);
        }

        if (isset($_POST['loginurlformail'])) {
            update_post_meta($post_id, "_cf7fr_loginurlformail_reg", sanitize_text_field($_POST['loginurlformail']));
        } else {
            update_post_meta($post_id, "_cf7fr_loginurlformail_reg", "");
        }

		$key = "_cf7fru_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);

		$key = "_cf7fre_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);	

		$key = "_cf7frr_";
		$vals = sanitize_text_field($_POST[$key]);
		update_post_meta($post_id, $key, $vals);	
}
?>