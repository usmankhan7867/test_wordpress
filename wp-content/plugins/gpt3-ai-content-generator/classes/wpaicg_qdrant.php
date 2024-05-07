<?php
namespace WPAICG;

if (!defined('ABSPATH')) exit;

if (!class_exists('\\WPAICG\\WPAICG_Qdrant')) {
    class WPAICG_Qdrant
    {
        private static $instance = null;
        private $api_key;
        private $endpoint;
        private $collection;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('wp_ajax_wpaicg_add_vector_qdrant', [$this, 'add_vector']);
            add_action('wp_ajax_wpaicg_delete_vector_qdrant', [$this, 'delete_vector']);
            add_action('wp_ajax_wpaicg_create_collection', [$this, 'create_collection']);
            add_action('wp_ajax_wpaicg_show_collections', [$this, 'show_collections']);
            add_action('wp_ajax_wpaicg_troubleshoot_connect_qdrant', [$this, 'connect_to_qdrant']);
            add_action('wp_ajax_wpaicg_save_qdrant_collections', [$this, 'save_qdrant_collections']);
            $this->api_key = get_option('wpaicg_qdrant_api_key', '');
            $this->endpoint = rtrim(get_option('wpaicg_qdrant_endpoint', ''), '/') . '/collections';
            $this->collection = get_option('wpaicg_qdrant_default_collection', '');
        }

        public function save_qdrant_collections() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                wp_send_json_error('Nonce verification failed');
            }
        
            $collections = isset($_POST['collections']) ? $_POST['collections'] : [];
            update_option('wpaicg_qdrant_collections', $collections);
            wp_send_json_success('Collections saved successfully');
        }
        

        public function show_collections() {
            if ( ! wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce') ) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
    
            $apiKey = sanitize_text_field($_POST['api_key']);
            // update option 
            update_option('wpaicg_qdrant_api_key', $apiKey);

            $endpoint = sanitize_text_field($_POST['endpoint']);
            update_option('wpaicg_qdrant_endpoint', $endpoint);

            // add /collections to endpoint
            $endpoint = rtrim($endpoint, '/') . '/collections';
    
            $response = wp_remote_get($endpoint, [
                'headers' => ['api-key' => $apiKey]
            ]);

            if (is_wp_error($response)) {
                wp_send_json_error(['error' => $response->get_error_message()]);
            } else {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($body['error'])) {
                    wp_send_json_error(['error' => $body['error']]);
                }
                $collections = array_column($body['result']['collections'], 'name');
                wp_send_json_success($collections);
            }
    
            die();
        }

        public function connect_to_qdrant()
        {
            // nonce verification
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }

            $this->api_key = sanitize_text_field($_POST['api_key']);
            $this->endpoint = sanitize_text_field($_POST['endpoint']);

            // Save Qdrant API key and endpoint
            update_option('wpaicg_qdrant_api_key', $this->api_key);
            update_option('wpaicg_qdrant_endpoint', $this->endpoint);

            $response = wp_remote_get($this->endpoint, [
                'headers' => ['api-key' => $this->api_key]
            ]);

            if (is_wp_error($response)) {
                echo 'Error: ' . $response->get_error_message();
            } else {
                echo 'Response: ' . wp_remote_retrieve_body($response);
            }

            die();
        }

        public function create_collection() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }

            $collectionName = sanitize_text_field($_POST['collection_name']);
            $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
            $dimension = 1536;

            if ($wpaicg_provider === 'Google') {
                $dimension = 768;
            } 
            $apiKey = sanitize_text_field($_POST['api_key']); 
            $endpoint = rtrim(sanitize_text_field($_POST['endpoint']), '/') . '/collections/' . $collectionName; 

            $response = wp_remote_request($endpoint, [
                'method' => 'PUT',
                'headers' => [
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'vectors' => [
                        'distance' => 'Cosine',
                        'size' => $dimension
                    ],
                ])
            ]);

            if (is_wp_error($response)) {
                echo json_encode(['error' => $response->get_error_message()]);
            } else {
                echo wp_remote_retrieve_body($response);
            }

            die();
        }

        public function add_vector() {

            $collection = $this->collection;
            $query_params = '/points?wait=true';
            $endpoint = $this->endpoint . '/collections/' . $collection . $query_params;
            $api_key = $this->api_key;
            $vectors = str_replace("\\", '', sanitize_text_field($_REQUEST['data']));

            $response = wp_remote_request($endpoint, array(
                'method'    => 'PUT',
                'headers' => ['api-key' => $api_key, 'Content-Type' => 'application/json'],
                'body'      => $vectors,
            ));

            if (is_wp_error($response)) {
                die($response->get_error_message());
            } else {
                echo wp_remote_retrieve_body($response);
                die();
            }
        }

        public function delete_vector() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }

            $collection_name = sanitize_text_field($_POST['collection_name']);
            $points = str_replace("\\", '', sanitize_text_field($_POST['data']));

            $full_endpoint = $this->endpoint . '/' . $collection_name . '/points/delete?wait=true';

            $response = wp_remote_request($full_endpoint, array(
                'method' => 'POST',
                'headers' => ['api-key' => $this->api_key, 'Content-Type' => 'application/json'],
                'body' => $points,
            ));

            if (is_wp_error($response)) {
                die($response->get_error_message());
            } else {
                echo wp_remote_retrieve_body($response);
                die();
            }
        }

        public function search($query_params)
        {
            // Implement your search logic using Qdrant
        }
    }

    WPAICG_Qdrant::get_instance();
}
