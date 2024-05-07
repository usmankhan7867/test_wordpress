<?php
namespace WpAssetCleanUp;

/**
 * Class AssetsPagesManager
 * @package WpAssetCleanUp
 *
 * Actions taken within the Dashboard, inside the plugin area: "CSS & JS MANAGER" (main top menu) -- "MANAGE CSS/JS" (main tab)
 */
class AssetsPagesManager
{
    /**
     * @var array
     */
    public $data = array();

	/**
	 * AssetsPagesManager constructor.
	 */
	public function __construct()
    {
		if ( Misc::getVar('get', 'page') !== WPACU_PLUGIN_ID . '_assets_manager' ) {
			return;
		}

	    $wpacuSubPage = (isset($_GET['wpacu_sub_page']) && $_GET['wpacu_sub_page']) ? $_GET['wpacu_sub_page'] : 'manage_css_js';

	    $this->data = array(
		    'for'          => 'homepage', // default
		    'nonce_action' => WPACU_PLUGIN_ID . '_dash_assets_page_update_nonce_action',
		    'nonce_name'   => WPACU_PLUGIN_ID . '_dash_assets_page_update_nonce_name'
	    );

	    $this->data['site_url'] = get_site_url();

	    if (isset($_GET['wpacu_for']) && $_GET['wpacu_for'] !== '') {
		    $this->data['for'] = sanitize_text_field($_GET['wpacu_for']);
	    }

	    $this->data['wpacu_post_id'] = (isset($_GET['wpacu_post_id']) && $_GET['wpacu_post_id']) ? (int)$_GET['wpacu_post_id'] : false;

		if ($this->data['wpacu_post_id'] && $this->data['for'] === 'homepage') {
			// URI is like: /wp-admin/admin.php?page=wpassetcleanup_assets_manager&wpacu_post_id=POST_ID_HERE (without any "wpacu_for")
			// Proceed to detect the post type
			global $wpdb;
			$query = $wpdb->prepare("SELECT `post_type` FROM `{$wpdb->posts}` WHERE `ID`='%d'", $this->data['wpacu_post_id']);
			$requestedPostType = $wpdb->get_var($query);

			if ($requestedPostType === 'post') {
				$this->data['for'] = 'posts';
			} elseif ($requestedPostType === 'page') {
				$this->data['for'] = 'posts';
			} elseif ($requestedPostType === 'attachment') {
				$this->data['for'] = 'media-attachment';
			} elseif ($requestedPostType !== '') {
				$this->data['for'] = 'custom-post-types';
			}
		}

	    if (Menu::isPluginPage()) {
		    $this->data['page'] = sanitize_text_field($_GET['page']);
	    }

	    $wpacuSettings = new Settings;
	    $this->data['wpacu_settings'] = $wpacuSettings->getAll();
	    $this->data['show_on_front'] = Misc::getShowOnFront();

	    if ($wpacuSubPage === 'manage_css_js' && in_array($this->data['for'], array('homepage', 'pages', 'posts', 'custom-post-types', 'media-attachment'))) {
		    Misc::w3TotalCacheFlushObjectCache();

		    // Front page displays: A Static Page
		    if ($this->data['for'] === 'homepage' && $this->data['show_on_front'] === 'page') {
			    $this->data['page_on_front'] = get_option('page_on_front');

			    if ($this->data['page_on_front']) {
				    $this->data['page_on_front_title'] = get_the_title($this->data['page_on_front']);
			    }

			    $this->data['page_for_posts'] = get_option('page_for_posts');

			    if ($this->data['page_for_posts']) {
				    $this->data['page_for_posts_title'] = get_the_title($this->data['page_for_posts']);
			    }
		    }

		    // e.g. It could be the homepage tab loading a singular page set as the homepage in "Settings" -> "Reading"
		    $anyPostId = (int)Misc::getVar('post', 'wpacu_manage_singular_page_id');

		    if ($this->data['for'] === 'homepage' && ! $anyPostId) {
			    // "CSS & JS MANAGER" -- "Homepage" (e.g. "Your homepage displays" set as "Your latest posts")
			    $this->homepageActions();
		    } else {
			    // "CSS & JS MANAGER" --> "MANAGE CSS/JS"
			    // Case 1: "Homepage", if singular page set as the homepage in "Settings" -> "Reading")
			    // Case 2: "Posts"
			    // Case 3: "Pages"
			    // Case 4: "Custom Post Types" (e.g. WooCommerce product)
			    // Case 5: "Media" (attachment pages, rarely used)
			    $this->singularPageActions();
		    }
	    }
    }

	/**
	 *
	 */
    public function homepageActions()
    {
        // Only continue if we are on the plugin's homepage edit mode
        if ( ! ( $this->data['for'] === 'homepage' && Misc::getVar('get', 'page') === WPACU_PLUGIN_ID . '_assets_manager' ) ) {
            return;
        }

        // Update action?
        if (! empty($_POST) && Misc::getVar( 'post', 'wpacu_manage_home_page_assets', false ) ) {
	        $wpacuNoLoadAssets = Misc::getVar( 'post', WPACU_PLUGIN_ID, array() );

	        $wpacuUpdate = new Update;

	        if ( ! (isset($_REQUEST[$this->data['nonce_name']])
                && wp_verify_nonce($_REQUEST[$this->data['nonce_name']], $this->data['nonce_action'])) ) {
		        add_action('wpacu_admin_notices', array($wpacuUpdate, 'changesNotMadeInvalidNonce'));
		        return;
	        }

	        // All good with the nonce? Do the changes!
	        $wpacuUpdate->updateFrontPage( $wpacuNoLoadAssets );
        }
    }

	/**
	 * Any post type, including the custom ones
	 */
	public function singularPageActions()
    {
	    $postId = (int)Misc::getVar('post', 'wpacu_manage_singular_page_id');

	    $isSingularPageEdit = $postId > 0 &&
			( Misc::getVar('get', 'page') === WPACU_PLUGIN_ID . '_assets_manager' &&
			in_array( $this->data['for'], array('homepage', 'pages', 'posts', 'custom-post-types', 'media-attachment' ) ) );

	    // Only continue if the form was submitted for a singular page
	    // e.g. a post, a page (could be the homepage), a WooCommerce product page, any public custom post type
	    if (! $isSingularPageEdit) {
		    return;
	    }

	    if (! empty($_POST)) {
		    // Update action?
		    $wpacuNoLoadAssets   = Misc::getVar( 'post', WPACU_PLUGIN_ID, array() );
		    $wpacuSingularPageUpdate = Misc::getVar( 'post', 'wpacu_manage_singular_page_assets', false );

		    // Could Be an Empty Array as Well so just is_array() is enough to use
		    if ( is_array( $wpacuNoLoadAssets ) && $wpacuSingularPageUpdate ) {
			    $wpacuUpdate = new Update;

			    if ( ! (isset($_REQUEST[$this->data['nonce_name']])
			            && wp_verify_nonce($_REQUEST[$this->data['nonce_name']], $this->data['nonce_action'])) ) {
				    add_action('wpacu_admin_notices', array($wpacuUpdate, 'changesNotMadeInvalidNonce'));
				    return;
			    }

			    if ($postId > 0) {
				    $wpacuUpdate = new Update;
				    $wpacuUpdate->savePosts($postId);
			    }
		    }
	    }
    }

	/**
	 * Called in Menu.php (within "admin_menu" hook via "activeMenu" method)
	 */
	public function renderPage()
    {
	    Main::instance()->parseTemplate('admin-page-assets-manager', $this->data, true);
    }
}
