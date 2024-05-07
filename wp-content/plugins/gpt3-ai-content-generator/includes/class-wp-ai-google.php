<?php
namespace WPAICG;

if (!defined('ABSPATH')) exit;

if (!class_exists('\\WPAICG\\WPAICG_Google')) {
    class WPAICG_Google {
        private static $instance = null;
        private $apiKey;
        private $model;
        private $stream_method;
        private $timeout = 300; // Timeout for Google API requests
        // Declare the properties
        public $temperature;
        public $max_tokens;
        public $top_p;
        public $best_of;
        public $frequency_penalty;
        public $presence_penalty;
        public $wpai_language;
        public $img_size;
        public $wpai_add_img;
        public $wpai_add_intro;
        public $wpai_add_conclusion;
        public $wpai_add_tagline;
        public $wpai_add_faq;
        public $wpai_add_keywords_bold;
        public $wpai_number_of_heading;
        public $wpai_modify_headings;
        public $wpai_heading_tag;
        public $wpai_writing_style;
        public $wpai_writing_tone;
        public $wpai_target_url;
        public $wpai_target_url_cta;
        public $wpai_cta_pos;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // Fetch settings from the database
            $this->initialize_settings();
        }

        private function initialize_settings() {
            global $wpdb;
            $wpaicgTable = $wpdb->prefix . 'wpaicg';
            $sql = $wpdb->prepare( 'SELECT * FROM ' . $wpaicgTable . ' WHERE name=%s', 'wpaicg_settings' );
            $wpaicg_settings = $wpdb->get_row( $sql, ARRAY_A );
            if ($wpaicg_settings) {
                // Assign the values
                $this->apiKey = get_option('wpaicg_google_model_api_key', '');
                $this->model = get_option('wpaicg_google_model', 'gemini-pro');
                
                foreach($wpaicg_settings as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
            }
        }

        public function image($opts)
        {
            // return error message
            return ['status' => 'error', 'msg' => 'Your default AI provider is set to Google. Google Image API is not publicly available yet. Choose another AI provider from Settings - AI Engine tab.'];
        }

        public function chat($opts, $stream = null)
        {
            if ($stream != null && array_key_exists('stream', $opts)) {
                if (! $opts['stream']) {
                    throw new \Exception(
                        'Please provide a stream function.'
                    );
                }
                $this->stream_method = $stream;
            }

            $opts['model'] = $opts['model'] ?? $this->model;

            // opts sourceModule 
            $opts['sourceModule'] = $opts['sourceModule'] ?? null;

            // Concatenate all messages if sourceModule is chat
            if ($opts['sourceModule'] === 'chat') {
                $messagesContent = array_reduce($opts['messages'], function ($carry, $item) {
                    return $carry . (empty($carry) ? '' : "\n") . $item['content'];
                }, '');

                return $this->send_google_request($messagesContent, $opts['model'], $opts['temperature'], $opts['top_p'], $opts['max_tokens'], 'chat');
            } 
            // If source module is form, handle it accordingly
            else if ($opts['sourceModule'] === 'form') {
                return $this->send_google_request($opts['messages'][0]['content'], $opts['model'], $opts['temperature'], $opts['top_p'], $opts['max_tokens'], 'form');
            }
            // Handle the case where provider is Google
            else if (array_key_exists('provider', $opts) && $opts['provider'] === 'google') {
                $opts['top_p'] = $this->top_p;
                return $this->send_google_request($opts['messages'][0]['content'], $opts['model'], $opts['temperature'], $opts['top_p'], $opts['max_tokens'], 'content');
            }
        }

        public function send_google_stream_request($content, $model, $temperature, $top_p, $max_tokens, $sourceModule) {


            $url = "https://generativelanguage.googleapis.com/v1/models/" . $model . ":streamGenerateContent?key=" . $this->apiKey . "&alt=sse";

            $postData = json_encode(array(
                "contents" => [
                    ["role" => "user", "parts" => [["text" => $content]]]
                ],
                "generationConfig" => [
                    "temperature" => $temperature, 
                    "topK" => 1, 
                    "topP" => $top_p, 
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ]
            ));
        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
            // Enable streaming
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Initialize a variable to track the presence of 'promptFeedback' in the current stream.
            $promptFeedbackReceived = false;

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) use (&$promptFeedbackReceived) {

                // Check if 'promptFeedback' is present in the received data.
                if (strpos($data, '"promptFeedback":') !== false) {
                    if ($promptFeedbackReceived) {
                        $finaldata = 'Error: Received multiple promptFeedback in the same stream.';
                        return call_user_func($this->stream_method, $this, $finaldata);
                    } else {
                        // Mark that 'promptFeedback' has been received for the current stream.
                        $promptFeedbackReceived = true;
                        return call_user_func($this->stream_method, $this, $data);
                    }
                } else {
                    return call_user_func($this->stream_method, $this, $data);
                }
                
            });

            // Execute the request
            curl_exec($ch);
            if(curl_errno($ch)){
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);
        }

        public function send_google_request($title, $model, $temperature, $top_p, $max_tokens, $sourceModule = null) {
            if (empty($this->apiKey)) {
                return 'Error: Google API key is not set';
            }

            $args = array(
                'headers' => array('Content-Type' => 'application/json'),
                'method' => 'POST',
                'timeout' => $this->timeout
            );

            // Construct the request URL and body based on the model
            switch ($model) {
                case 'text-bison-001':
                    $url = "https://generativelanguage.googleapis.com/v1beta3/models/text-bison-001:generateText?key={$this->apiKey}";
                    $args['body'] = json_encode(array("prompt" => array("text" => $title)));
                    break;
                case 'chat-bison-001':
                    $url = "https://generativelanguage.googleapis.com/v1beta2/models/chat-bison-001:generateMessage?key={$this->apiKey}";
                    $args['body'] = json_encode(array("prompt" => array("messages" => array(array("content" => $title)))));
                    break;
                case 'gemini-pro':

                    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key={$this->apiKey}";

                    $args['body'] = json_encode(array(
                        "contents" => [
                            ["role" => "user", "parts" => [["text" => $title]]]
                        ],
                        "generationConfig" => [
                            "temperature" => $temperature, 
                            "topK" => 1, 
                            "topP" => $top_p, 
                            "maxOutputTokens" => $max_tokens, 
                            "stopSequences" => []
                        ],
                        "safetySettings" => [
                            ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                        ]
                    ));
                    break;
                default:
                    return 'Error: Invalid model selected';
            }

            $response = wp_remote_post($url, $args);
            $processedResponse = $this->handle_response($response, $sourceModule);
            
            // Modification for sourceModule = "content" or chat and status = success
            // if ($sourceModule === 'content' && isset($processedResponse['status']) && $processedResponse['status'] === 'success') {
            if ($sourceModule === 'content'|| $sourceModule === 'chat' && isset($processedResponse['status']) && $processedResponse['status'] === 'success') {
                // Generate random ID with prefix
                $id = 'chatcmpl-' . bin2hex(random_bytes(16));
                
                // Current time as created
                $created = time();
                
                // Calculate tokens
                $promptLength = mb_strlen($title, 'UTF-8');
                $dataLength = 0;
                $processedResponse['data'] = $processedResponse['data'] ?? '';
                // Check if "data" key exists and is not empty, otherwise use a default length
                if (isset($processedResponse['data']) && !empty($processedResponse['data'])) {
                    $dataLength = mb_strlen($processedResponse['data'], 'UTF-8');
                } else {
                    $dataLength = 0; // Default length, adjust as needed
                    // {"headers":{},"body":"{\n  \"candidates\": [\n    {\n      \"finishReason\": \"RECITATION\",\n      \"index\": 0\n    }\n  ],\n  \"promptFeedback\": {\n    \"safetyRatings\": [\n      {\n        \"category\": \"HARM_CATEGORY_SEXUALLY_EXPLICIT\",\n        \"probability\": \"NEGLIGIBLE\"\n      },\n      {\n        \"category\": \"HARM_CATEGORY_HATE_SPEECH\",\n        \"probability\": \"NEGLIGIBLE\"\n      },\n      {\n        \"category\": \"HARM_CATEGORY_HARASSMENT\",\n        \"probability\": \"NEGLIGIBLE\"\n      },\n      {\n        \"category\": \"HARM_CATEGORY_DANGEROUS_CONTENT\",\n        \"probability\": \"NEGLIGIBLE\"\n      }\n    ]\n  }\n}\n","response":{"code":200,"message":"OK"},"cookies":[],"filename":null,"http_response":{"data":null,"headers":null,"status":null}}
                    $firstCandidate = json_decode($response['body'], true)['candidates'][0];
                    $finishReason = '';
                    if (isset($firstCandidate['finishReason'])) {
                        $finishReason = $firstCandidate['finishReason'];
                        error_log('Finish reason: ' . $finishReason); // Log the finish reason for debugging
                    }
                    $processedResponse['data'] = '.';
                }
                $prompt_tokens = ceil(($promptLength / 1000) * 250); 
                $completion_tokens = ceil(($dataLength / 1000) * 250); 
                $total_tokens = $prompt_tokens + $completion_tokens;

                // Construct new response format
                $processedResponse = json_encode([
                    "id" => $id,
                    "object" => "chat.completion",
                    "created" => $created,
                    "model" => $model,
                    "choices" => [
                        [
                            "index" => 0,
                            "message" => [
                                "role" => "assistant",
                                "content" => $processedResponse['data']
                            ],
                            "logprobs" => null,
                            "finish_reason" => "stop"
                        ]
                    ],
                    "usage" => [
                        "prompt_tokens" => $prompt_tokens,
                        "completion_tokens" => $completion_tokens,
                        "total_tokens" => $total_tokens
                    ],
                    "system_fingerprint" => "fp_" . bin2hex(random_bytes(8))
                ]);
            }
            return $processedResponse;
        }

        private function handle_response($response, $sourceModule = null) {
            if (is_wp_error($response)) {
                return ['error' => 'HTTP request error: ' . $response->get_error_message()];
            }
        
            $body = wp_remote_retrieve_body($response);
            $decodedResponse = json_decode($body, true);
        
            if (isset($decodedResponse['error'])) {
                $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'Unknown error from Google API';
                error_log('Parsed error message: ' . $errorMsg);
                // if source module is form then return this: return ['error' => 'Response from Google: ' . $errorMsg];
                if ($sourceModule === 'form') {
                    return ['error' => 'Response from Google: ' . $errorMsg];
                } else {
                    return ['status' => 'error', 'msg' => 'Response from Google: ' . $errorMsg];
                }
            } elseif (empty($decodedResponse)) {
                error_log('No data found in the response');
                return ['error' => 'No valid content found in the response'];
            }
        
            // Logic to handle candidates and extract text
            if (isset($decodedResponse['candidates']) && is_array($decodedResponse['candidates'])) {
                $firstCandidate = $decodedResponse['candidates'][0];
                if (isset($firstCandidate['content']['parts'][0]['text'])) {
                    $completeText = $firstCandidate['content']['parts'][0]['text'];
                    // Parse the markdown. if sourceModule is template or editor or content then dont parse markdown
                    if ($sourceModule !== 'template' && $sourceModule !== 'editor' && $sourceModule !== 'content' && $sourceModule !== 'form' && $sourceModule !== 'chat') {
                        $completeText = $this->parse_markdown($completeText);
                    }
                    $completeText = $completeText;
                    return ['status' => 'success', 'data' => $completeText];
                } else {
                    // catch the actual error message from the response
                    $errorMsg = isset($firstCandidate['error']['message']) ? $firstCandidate['error']['message'] : 'Unknown error from Google API';
                    return ['error' => 'Response from Google: ' . $errorMsg];
                }
            } else {
                return ['error' => 'Invalid response from Google API'];
            }
        }
        
        public function parse_markdown($text) {
            // Bold
            $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
            $text = preg_replace('/__(.*?)__/s', '<strong>$1</strong>', $text);
        
            // Italic
            $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
            $text = preg_replace('/_(.*?)_/s', '<em>$1</em>', $text);
        
            // Strikethrough
            $text = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $text);
        
            // Code
            $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
        
            // Link
            $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
        
            // Lists
            // First, we'll split the text into lines to process lists correctly.
            $lines = explode("\n", $text);
            $inList = false;
            $text = '';
        
            foreach ($lines as $line) {
                if (preg_match('/^- (.*?)$/', $line)) { // Check if line is a list item.
                    if (!$inList) { // If we're not already in a list, start one.
                        $inList = true;
                        $text .= "<ul>\n";
                    }
                    $text .= '<li>' . preg_replace('/^- (.*?)$/', '$1', $line) . "</li>\n"; // Add the list item.
                } else {
                    if ($inList) { // If we were in a list but current line is not a list item, close the list.
                        $inList = false;
                        $text .= "</ul>\n";
                    }
                    $text .= $line . "\n"; // Add the non-list line.
                }
            }
        
            if ($inList) { // Close the list if the text ends while still in a list.
                $text .= "</ul>\n";
            }

            // Blockquote
            $text = preg_replace('/^> (.*?)$/m', '<blockquote>$1</blockquote>', $text);

            return $text;
        }


        public function embeddings($apiParams) {
            // Ensure the API key is set
            if (empty($this->apiKey)) {
                return ['error' => 'Google API key is not set'];
            }

            $model = $apiParams['model'];
            $content = $apiParams['input'];

            // Construct the request URL
            $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:embedContent?key={$this->apiKey}";

            // Prepare the request body
            $postData = [
                "model" => "models/{$model}",
                "content" => [
                    "parts" => [
                        ["text" => $content]
                    ]
                ]
            ];

            // Setup arguments for the POST request
            $args = [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($postData),
                'timeout' => $this->timeout,
                'data_format' => 'body'
            ];

            // Send the request
            $response = wp_remote_post($url, $args);

            // Check for errors in the response
            if (is_wp_error($response)) {
                return ['error' => $response->get_error_message()];
            }

            // Decode and process the response
            $body = wp_remote_retrieve_body($response);

            // Convert the Google response to an associative array
            $googleResponse = json_decode($body, true);   
            if (isset($googleResponse['error'])) {
                return json_encode($googleResponse);
            }

            // Extract embedding values from Google response
            $googleEmbeddings = $googleResponse['embedding']['values'];

            // Calculate prompt_tokens based on $content character count
            $contentLength = strlen($apiParams['input']);
            $promptTokens = ceil($contentLength / 4);

            // Convert to OpenAI format
            $openAiResponse = [
                "object" => "list",
                "data" => [
                    [
                        "object" => "embedding",
                        "index" => 0,
                        "embedding" => $googleEmbeddings
                    ]
                ],
                "model" => $model,
                "usage" => [
                    "prompt_tokens" => $promptTokens,
                    "total_tokens" => $promptTokens // Assuming total_tokens is equal to prompt_tokens
                ]
            ];

            // return json response
            $decodedResponse = json_encode($openAiResponse);

            return $decodedResponse;


        }
        
        
    }
}
