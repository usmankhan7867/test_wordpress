<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$wpaicg_embeddings_settings_updated = false;
if(isset($_POST['wpaicg_save_builder_settings'])){
    check_admin_referer('wpaicg_embeddings_settings');
    if(isset($_POST['wpaicg_pinecone_api']) && !empty($_POST['wpaicg_pinecone_api'])) {
        update_option('wpaicg_pinecone_api', sanitize_text_field($_POST['wpaicg_pinecone_api']));
    }
    else{
        delete_option('wpaicg_pinecone_api');
    }
    if(isset($_POST['wpaicg_pinecone_environment']) && !empty($_POST['wpaicg_pinecone_environment'])) {
        update_option('wpaicg_pinecone_environment', sanitize_text_field($_POST['wpaicg_pinecone_environment']));
    }
    else{
        delete_option('wpaicg_pinecone_environment');
    }

    if(isset($_POST['wpaicg_builder_enable']) && !empty($_POST['wpaicg_builder_enable'])){
        update_option('wpaicg_builder_enable','yes');
    }
    else{
        delete_option('wpaicg_builder_enable');
    }
    if(isset($_POST['wpaicg_builder_types']) && is_array($_POST['wpaicg_builder_types']) && count($_POST['wpaicg_builder_types'])){
        update_option('wpaicg_builder_types',\WPAICG\wpaicg_util_core()->sanitize_text_or_array_field($_POST['wpaicg_builder_types']));
    }
    else{
        delete_option('wpaicg_builder_types');
    }
    if(isset($_POST['wpaicg_instant_embedding']) && !empty($_POST['wpaicg_instant_embedding'])){
        update_option('wpaicg_instant_embedding',\WPAICG\wpaicg_util_core()->sanitize_text_or_array_field($_POST['wpaicg_instant_embedding']));
    }
    else{
        update_option('wpaicg_instant_embedding','no');
    }
    if(isset($_POST['wpaicg_vector_db_provider'])) {
        update_option('wpaicg_vector_db_provider', sanitize_text_field($_POST['wpaicg_vector_db_provider']));
    }
    if(isset($_POST['wpaicg_qdrant_api_key'])) {
        update_option('wpaicg_qdrant_api_key', sanitize_text_field($_POST['wpaicg_qdrant_api_key']));
    }
    if(isset($_POST['wpaicg_qdrant_endpoint'])) {

        $qdrantEndpoint = sanitize_text_field($_POST['wpaicg_qdrant_endpoint']);
        $usePort = isset($_POST['wpaicg_qdrant_use_port']) && $_POST['wpaicg_qdrant_use_port'] === 'yes';
        // Remove any existing port from the endpoint
        $qdrantEndpoint = preg_replace('/:\d+$/', '', $qdrantEndpoint);
        // Append ':6333' if "Use Port" is checked
        if ($usePort) {
            $qdrantEndpoint .= ':6333';
        }
        update_option('wpaicg_qdrant_endpoint', $qdrantEndpoint);
        update_option('wpaicg_qdrant_use_port', $usePort ? 'yes' : 'no');
    }
    
    // Save the currently selected Qdrant collection as the default
    $selected_qdrant_collection = isset($_POST['wpaicg_qdrant_collections']) ? sanitize_text_field($_POST['wpaicg_qdrant_collections']) : '';
    update_option('wpaicg_qdrant_default_collection', $selected_qdrant_collection);
    
    // Saving the Embeddings Model option
    $wpaicg_openai_embeddings_value = isset($_POST['wpaicg_openai_embeddings']) ? sanitize_text_field($_POST['wpaicg_openai_embeddings']) : 'text-embedding-ada-002';
    update_option('wpaicg_openai_embeddings', $wpaicg_openai_embeddings_value);
    // Saving the Google Embeddings Model option
    $wpaicg_google_embeddings_value = isset($_POST['wpaicg_google_embeddings']) ? sanitize_text_field($_POST['wpaicg_google_embeddings']) : 'embedding-001';
    update_option('wpaicg_google_embeddings', $wpaicg_google_embeddings_value);
    // Update the variable to reflect the new setting immediately
    $wpaicg_openai_embeddings = get_option('wpaicg_openai_embeddings', 'text-embedding-ada-002');
    $wpaicg_google_embeddings = get_option('wpaicg_google_embeddings', 'embedding-001');
    
    $wpaicg_embeddings_settings_updated = true;
}
$table_name = $wpdb->prefix . 'wpaicg';
$ai_provider_api_key = ''; // Initialize the variable to store the API key

// Retrieve the vector db provider option
$wpaicg_vector_db_provider = get_option('wpaicg_vector_db_provider', '');
$wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
$wpaicg_qdrant_api_key = get_option('wpaicg_qdrant_api_key', '');
$wpaicg_pinecone_api = get_option('wpaicg_pinecone_api', '');
$wpaicg_azure_api_key = get_option('wpaicg_azure_api_key', '');
$wpaicg_google_model_api_key = get_option('wpaicg_google_model_api_key', '');
$svg_check_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0BDA51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>';
$svg_alert_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF3131" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';

$embedding_model = '';
$embedding_model_reason = '';
// Retrieve the embedding model based on the provider
switch ($wpaicg_provider) {
    case 'OpenAI':
        $api_key_row = $wpdb->get_row("SELECT api_key FROM {$table_name} WHERE name = 'wpaicg_settings'", ARRAY_A);
        $ai_provider_api_key = $api_key_row ? $api_key_row['api_key'] : '';
        $embedding_model = get_option('wpaicg_openai_embeddings', 'text-embedding-ada-002');
        break;
    case 'Azure':
        $ai_provider_api_key = get_option('wpaicg_azure_api_key', '');
        $embedding_model = get_option('wpaicg_azure_embeddings', '');
        break;
    case 'Google':
        $ai_provider_api_key = get_option('wpaicg_google_model_api_key', '');
        $embedding_model = get_option('wpaicg_google_embeddings', 'embedding-001');
        break;
}

// Check if the embedding model is set
if (empty($embedding_model)) {
    $embedding_model_reason = 'Embedding model not set.';
}


$index_name = '';
$db_alert_reason = '';
$index_alert_reason = '';
$ai_provider_alert_reason = '';


// Check for AI provider API key status
if ($wpaicg_provider == 'OpenAI' && (empty($ai_provider_api_key) || strlen($ai_provider_api_key) < 5)) {
    $ai_provider_alert_reason = 'OpenAI API key missing or invalid.';
} elseif (empty($ai_provider_api_key)) {
    $ai_provider_alert_reason = $wpaicg_provider . ' API key missing.';
}

if (!empty($wpaicg_vector_db_provider)) {
    if ($wpaicg_vector_db_provider === 'qdrant') {
        $index_name = get_option('wpaicg_qdrant_default_collection');
        $db_alert_reason .= empty($wpaicg_qdrant_api_key) ? 'Qdrant API missing. ' : '';
        $index_alert_reason .= empty($index_name) ? 'Collection missing. ' : '';
    } elseif ($wpaicg_vector_db_provider === 'pinecone') {
        $index_name_parts = explode('-', get_option('wpaicg_pinecone_environment'), 2);
        $index_name = $index_name_parts[0];
        $db_alert_reason .= empty($wpaicg_pinecone_api) ? 'Pinecone API missing. ' : '';
        $index_alert_reason .= empty($index_name) ? 'Index missing. ' : '';
    }
}

?>
<div class="content-writer-master">
  <div class="content-writer-master-navigation">
    <nav>
      <ul>
        <li>
          <a href="#knowledge">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tool">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
            </svg>
            Knowledge Builder</a>
        </li>
        <li>
          <a href="#settings">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers">
              <polygon points="12 2 2 7 12 12 22 7 12 2" />
              <polyline points="2 17 12 22 22 17" />
              <polyline points="2 12 12 17 22 12" />
            </svg>
            Settings</a>
        </li>
        <li>
          <a href="#troubleshoot">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-align-justify">
              <line x1="21" y1="10" x2="3" y2="10" />
              <line x1="21" y1="6" x2="3" y2="6" />
              <line x1="21" y1="14" x2="3" y2="14" />
              <line x1="21" y1="18" x2="3" y2="18" />
            </svg>
            Troubleshoot</a>
        </li>
        <li>
        <a href="#finetuning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square">
            <polyline points="9 11 12 14 22 4" />
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
            </svg>
            Fine-tuning</a>
        </li>
      </ul>
    </nav>
  </div>
  <main class="content-writer-master-content">
    <!-- CONTENT BUILDER -->
    <section>
      <div class="href-target" id="knowledge"></div>
      <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tool">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
        </svg>
        Knowledge Builder
      </h1>
        <!-- Data Source Selector -->
        <div class="nice-form-group">
        <label for="data-source-selector"><?php echo esc_html__('Data Source','gpt3-ai-content-generator')?></label>
            <select id="data-source-selector" class="data-source-select" style="width: 140px;">
                <option value="manual"><?php echo esc_html__('Manual Entry','gpt3-ai-content-generator')?></option>
                <option value="pdf"><?php echo esc_html__('PDF Upload','gpt3-ai-content-generator')?></option>
                <option value="scan"><?php echo esc_html__('Auto-Scan','gpt3-ai-content-generator')?></option>
            </select>
        </div>
        <!-- Manual Entry -->
        <div id="manual-container" class="data-source-container">
            <div class="nice-form-group">
                <?php
                include __DIR__.'/manual.php';
                ?>
            </div>
        </div>
        <!-- PDF Upload -->
        <div id="pdf-container" class="data-source-container" style="display: none;">
            <div class="nice-form-group">
                <?php
                    if(\WPAICG\wpaicg_util_core()->wpaicg_is_pro()) {
                        include WPAICG_PLUGIN_DIR . 'lib/views/pdf/index.php';
                    }
                    else{
                        echo '<a href="'.esc_url(admin_url('admin.php?page=wpaicg-pricing')).'"><img src="'.esc_url(WPAICG_PLUGIN_URL).'admin/images/compress_pro_pdf.png" width="100%"></a>';
                    }
                    ?>
            </div>
        </div>
        <!-- Auto-Scan -->
        <div id="scan-container" class="data-source-container" style="display: none;">
            <div class="nice-form-group">
                <?php
                include __DIR__.'/autoscan.php';
                ?>
            </div>
        </div>
        <?php
            include WPAICG_PLUGIN_DIR.'admin/views/embeddings/entries.php';
            ?>
    </section>
    
    <!-- SETTINGS -->
    <section>
      <div class="href-target" id="settings"></div>
      <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers">
          <polygon points="12 2 2 7 12 12 22 7 12 2" />
          <polyline points="2 17 12 22 22 17" />
          <polyline points="2 12 12 17 22 12" />
        </svg>
        Settings
      </h1>
      <div class="nice-form-group">
        <?php
            include WPAICG_PLUGIN_DIR.'admin/views/embeddings/settings.php';
            ?>
      </div>
    </section>
    <!-- TROUBLESHOOT -->
    <section>
      <div class="href-target" id="troubleshoot"></div>
      <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-align-justify">
          <line x1="21" y1="10" x2="3" y2="10" />
          <line x1="21" y1="6" x2="3" y2="6" />
          <line x1="21" y1="14" x2="3" y2="14" />
          <line x1="21" y1="18" x2="3" y2="18" />
        </svg>
        Troubleshoot
      </h1>

      <div class="nice-form-group">
        <?php
            include WPAICG_PLUGIN_DIR.'admin/views/embeddings/troubleshoot.php';
            ?>
      </div>
    </section>
    <!-- FINE-TUNING -->
    <section>
        <div class="href-target" id="finetuning"></div>
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square">
            <polyline points="9 11 12 14 22 4" />
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
            </svg>
            Fine-tuning
        </h1>
        <div class="nice-form-group">
            <?php include WPAICG_PLUGIN_DIR.'admin/views/finetune/manual.php';?>
            <?php include WPAICG_PLUGIN_DIR.'admin/views/finetune/files.php';?>
        </div>
    </section>
  </main>
      <!-- Right Navigation for Express Mode -->
      <div class="content-writer-right-navigation" id="right-nav-express">
        <nav>
            <ul>
                <li>
                    <a href="javascript:void(0)" class="advanced-settings" ><?php echo esc_html__('STATUS','gpt3-ai-content-generator')?></a>
                    <!-- Submenu for status -->
                    <div class="submenu">
                        <table class="wp-list-table widefat striped table-view-list comments" style="white-space: break-spaces;">
                            <tbody>
                                <tr>
                                    <th><?php echo esc_html__('AI', 'gpt3-ai-content-generator'); ?></th>
                                    <td><strong><?php echo esc_html($wpaicg_provider); ?></strong></td>
                                    <td><span class="wpaicg_alert_container"><?php echo empty($ai_provider_alert_reason) ? $svg_check_icon : $svg_alert_icon; ?> <?php echo esc_html($ai_provider_alert_reason); ?></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></th>
                                    <td><strong><?php echo esc_html($embedding_model); ?></strong></td>
                                    <td><span class="wpaicg_alert_container"><?php echo empty($embedding_model_reason) ? $svg_check_icon : $svg_alert_icon; ?> <?php echo esc_html($embedding_model_reason); ?></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__('DB', 'gpt3-ai-content-generator'); ?></th>
                                    <td><strong><?php echo esc_html($wpaicg_vector_db_provider); ?></strong></td>
                                    <td><span class="wpaicg_alert_container"><?php echo empty($db_alert_reason) ? $svg_check_icon : $svg_alert_icon; ?> <?php echo esc_html($db_alert_reason); ?></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__('Index', 'gpt3-ai-content-generator'); ?></th>
                                    <td><strong style="word-break: break-all;"><?php echo !empty($index_name) ? esc_html($index_name) : esc_html__('N/A', 'gpt3-ai-content-generator'); ?></strong></td>
                                    <td><span class="wpaicg_alert_container"><?php echo empty($index_alert_reason) ? $svg_check_icon : $svg_alert_icon; ?> <?php echo esc_html($index_alert_reason); ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</div>
<script>
    // Right navigation menu
    document.addEventListener("DOMContentLoaded", function () {
        // Get all left navigation links
        var leftNavLinks = document.querySelectorAll('.content-writer-master-navigation a');

        // Function to hide all right navigation menus
        function hideAllRightNavs() {
            document.querySelectorAll('.content-writer-right-navigation').forEach(function (nav) {
            nav.style.display = 'none';
            });
        }

        // Function to show a right navigation menu
        function showRightNav(navId) {
            var rightNav = document.getElementById(navId);
            if (rightNav) {
            rightNav.style.display = 'block';
            }
        }

        // Initialize by hiding all right navs and showing the first one
        hideAllRightNavs();
        showRightNav('right-nav-express'); // Replace with the ID of your first right nav

        // Add click event to all left navigation links
        leftNavLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
            e.preventDefault();
            var targetId = this.getAttribute('href').replace('#', '');
            // Hide all right navs
            hideAllRightNavs();
            // Show the right nav associated with the clicked left nav item
            showRightNav('right-nav-' + targetId);
            });
        });
    });

</script>
<script>
    // Submenu functionality
    document.addEventListener("DOMContentLoaded", function () {
        const menuItems = document.querySelectorAll('.content-writer-right-navigation > nav > ul > li > a');

        function closeAllSubmenus() {
            document.querySelectorAll('.submenu').forEach(function (submenu) {
                submenu.style.display = 'none';
            });
        }

        // Open the first submenu by default
        const firstSubmenu = document.querySelector('.content-writer-right-navigation .submenu');
        if (firstSubmenu) {
            firstSubmenu.style.display = 'block';
        }

        menuItems.forEach(function (menuItem) {
            menuItem.addEventListener('click', function () {
                // If the clicked submenu is already open, do nothing
                var submenu = this.nextElementSibling;
                if (submenu.style.display === 'block') {
                    return;
                }

                // Close all submenus
                closeAllSubmenus();

                // Open the clicked submenu
                submenu.style.display = 'block';
            });
        });
    });
</script>
<script>
    // Tab navigation
    document.addEventListener("DOMContentLoaded", function () {

        // 1. TAB NAVIGATION
        const tabs = document.querySelectorAll('.content-writer-master-navigation ul li a');
        const contentSections = document.querySelectorAll('.content-writer-master-content section');
        const rightNav = document.getElementById('right-nav-express');

        // Initially hide all sections (This part is handled by CSS now, you can choose to keep or remove these lines)
        contentSections.forEach((section, index) => {
            section.style.display = 'none';
        });

        // Explicitly show the first tab content and set the first tab as active
        if (contentSections.length > 0) {
            contentSections[0].style.display = 'block';
        }
        if (tabs.length > 0) {
            tabs[0].parentElement.classList.add('active');
        }

        // Tab click event
        tabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('href').replace('#', '');
                const targetContent = document.getElementById(targetId);

                if (targetContent) {
                    contentSections.forEach(section => {
                        section.style.display = 'none';
                    });

                    targetContent.parentElement.style.display = 'block';

                    tabs.forEach(t => {
                        t.parentElement.classList.remove('active');
                    });
                    this.parentElement.classList.add('active');
                }

                // Hide the right navigation if the "Fine-Tuning" tab is active, show it otherwise
                if (targetId === 'finetuning') {
                    rightNav.style.display = 'none';
                } else {
                    rightNav.style.display = 'block';
                }
            });
        });
    });
</script>
<!-- DATA SOURCE -->
<script>
    document.getElementById('data-source-selector').addEventListener('change', function() {
        // Hide all containers
        document.querySelectorAll('.data-source-container').forEach(function(container) {
            container.style.display = 'none';
        });
        
        // Show the selected container
        var selectedValue = this.value;
        if (selectedValue === 'manual') {
            document.getElementById('manual-container').style.display = 'block';
        } else if (selectedValue === 'pdf') {
            document.getElementById('pdf-container').style.display = 'block';
            document.getElementById('delete-all-posts').setAttribute('data-action', 'pdf_upload_action');
        } else if (selectedValue === 'scan') {
            document.getElementById('scan-container').style.display = 'block';
        }
    });
</script>