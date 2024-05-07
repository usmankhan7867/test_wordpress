<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;

// Check if the promptbase log table and prompt feedback tables exist
$wpaicgPromptbaseLogTable = $wpdb->prefix . 'wpaicg_promptbase_logs';
$wpaicgPromptFeedbackTable = $wpdb->prefix . 'wpaicg_prompt_feedback';
$promptbaseLogTableExists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgPromptbaseLogTable)) == $wpaicgPromptbaseLogTable;
$promptFeedbackTableExists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgPromptFeedbackTable)) == $wpaicgPromptFeedbackTable;

if (!$promptbaseLogTableExists && !$promptFeedbackTableExists) {
    echo '<div class="notice notice-info is-dismissible">
        <p>'. esc_html__('Both the log table and feedback table do not exist. Please deactivate and then reactivate the plugin to trigger the table creation.', 'gpt3-ai-content-generator') .'</p>
    </div>';
    return;
} elseif (!$promptbaseLogTableExists) {
    echo '<div class="notice notice-info is-dismissible">
        <p>'. esc_html__('The log table does not exist. Please deactivate and then reactivate the plugin to trigger the table creation.', 'gpt3-ai-content-generator') .'</p>
    </div>';
    return;
} elseif (!$promptFeedbackTableExists) {
    echo '<div class="notice notice-info is-dismissible">
        <p>'. esc_html__('The feedback table does not exist. Please deactivate and then reactivate the plugin to trigger the table creation.', 'gpt3-ai-content-generator') .'</p>
    </div>';
    return;
}

if (isset($_GET['search']) && !empty($_GET['search']) && !wp_verify_nonce($_GET['wpaicg_nonce'], 'wpaicg_promptbaselog_search_nonce')) {
    die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
}
$wpaicg_log_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$search = isset($_GET['search']) && !empty($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$where = '';

if(!empty($search)) {
    $where .= $wpdb->prepare(" AND (`data` LIKE %s", '%' . $wpdb->esc_like($search) . '%');
    $where .= $wpdb->prepare(" OR `prompt` LIKE %s", '%' . $wpdb->esc_like($search) . '%');
    $where .= $wpdb->prepare(" OR `comment` LIKE %s)", '%' . $wpdb->esc_like($search) . '%');
}
$query = "SELECT logs.*, 
                 feedback.feedback, 
                 feedback.comment
          FROM ".$wpdb->prefix."wpaicg_promptbase_logs AS logs 
          LEFT JOIN ".$wpdb->prefix."wpaicg_prompt_feedback AS feedback ON logs.eventID = feedback.eventID 
          WHERE 1=1".$where;
$total_query = "SELECT COUNT(1) FROM ({$query}) AS combined_table";
$total = $wpdb->get_var( $total_query );
$items_per_page = 10;
$offset = ( $wpaicg_log_page * $items_per_page ) - $items_per_page;
$wpaicg_logs = $wpdb->get_results($query . " ORDER BY created_at DESC LIMIT {$offset}, {$items_per_page}");
$totalPage         = ceil($total / $items_per_page);
?>
<style>
    .wpaicg_modal{
        top: 5%;
        height: 90%;
        position: relative;
    }
    .wpaicg_modal_content{
        max-height: calc(100% - 103px);
        overflow-y: auto;
    }
</style>
<form action="" method="get">
    <?php wp_nonce_field('wpaicg_promptbaselog_search_nonce', 'wpaicg_nonce'); ?>
    <input type="hidden" name="page" value="wpaicg_promptbase">
    <input type="hidden" name="action" value="logs">
    <div class="wpaicg-d-flex mb-5">
        <input style="width: 100%" value="<?php echo esc_html($search)?>" class="regular-text" name="search" type="text" placeholder="<?php echo esc_html__('Type for search','gpt3-ai-content-generator')?>">
        <button class="button button-primary"><?php echo esc_html__('Search','gpt3-ai-content-generator')?></button>
    </div>
</form>
<table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
            <tr>
                <?php
                $headers = array(
                    'ID',
                    'Form',
                    'Prompt',
                    'Page',
                    'Model',
                    'Duration',
                    'Token',
                    'Estimated',
                    'User',
                    'Feedback',
                    'Comment',
                    'Created'
                );

                foreach ($headers as $header) {
                    echo '<th>' . esc_html__($header, 'gpt3-ai-content-generator') . '</th>';
                }
                ?>
            </tr>
    </thead>
    <tbody class="wpaicg-builder-list">
    <?php
    if($wpaicg_logs && is_array($wpaicg_logs) && count($wpaicg_logs)){
        foreach ($wpaicg_logs as $wpaicg_log) {
            $source = '';
            $wpaicg_ai_model = $wpaicg_log->model;
            $wpaicg_usage_token = $wpaicg_log->tokens;
            if($wpaicg_log->source > 0){
                $source = get_the_title($wpaicg_log->source);
            }
            if($wpaicg_ai_model === 'gpt-3.5-turbo' || $wpaicg_ai_model === 'gpt-3.5-turbo-16k') {
                $wpaicg_estimated = 0.002 * $wpaicg_usage_token / 1000;
            }
            if($wpaicg_ai_model === 'gpt-4') {
                $wpaicg_estimated = 0.06 * $wpaicg_usage_token / 1000;
            }
            if($wpaicg_ai_model === 'gpt-4-32k') {
                $wpaicg_estimated = 0.12 * $wpaicg_usage_token / 1000;
            }
            else{
                $wpaicg_estimated = 0.02 * $wpaicg_usage_token / 1000;
            }
            ?>
            <tr>
                <td><?php echo esc_html($wpaicg_log->prompt_id)?></td>
                <td><?php echo esc_html($wpaicg_log->name)?></td>
                <td>
                    <a class="wpaicg-view-log" 
                    href="javascript:void(0)"
                    data-content="<?php echo esc_attr($wpaicg_log->data)?>"
                    data-prompt="<?php echo esc_attr($wpaicg_log->prompt)?>"
                    data-feedback="<?php echo esc_attr($wpaicg_log->feedback)?>" 
                    data-comment="<?php echo esc_attr($wpaicg_log->comment)?>">
                    <?php 
                        echo esc_html(strlen($wpaicg_log->prompt) > 100 ? substr($wpaicg_log->prompt, 0, 100) . '..' : $wpaicg_log->prompt); 
                    ?>
                    </a>
                </td>
                <td><?php echo esc_html($source)?></td>
                <td><?php echo esc_html($wpaicg_ai_model)?></td>
                <td><?php echo esc_html(WPAICG\WPAICG_Content::get_instance()->wpaicg_seconds_to_time((int)$wpaicg_log->duration))?></td>
                <td><?php echo esc_html($wpaicg_usage_token)?></td>
                <td><?php 
                        // Check if NumberFormatter class exists
                        if (class_exists('NumberFormatter')) {
                            $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
                            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 4);
                            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 4);
                            $formattedNumber = $formatter->format($wpaicg_estimated);
                        } else {
                            // Fallback method if NumberFormatter is not available
                            // Using number_format() function for formatting
                            $formattedNumber = '$' . number_format($wpaicg_estimated, 4, '.', ',');
                        }

                        // Output the formatted number, escaped for safety
                        echo esc_html($formattedNumber);
                    ?>
                </td>
                <?php 
                $user_info = get_userdata($wpaicg_log->userID);
                $username = ($user_info) ? $user_info->user_login : esc_html__('Guest', 'gpt3-ai-content-generator');
                ?>
                <td><?php echo $username; ?></td>
                <td>
                    <?php 
                    if ($wpaicg_log->feedback == 'thumbs_up') {
                        echo esc_html('👍');
                    } elseif ($wpaicg_log->feedback == 'thumbs_down') {
                        echo esc_html('👎');
                    } else {
                        echo esc_html(' ');
                    }
                    ?>
                </td>
                <td>
                    <?php if (!is_null($wpaicg_log->comment) && trim($wpaicg_log->comment) !== '') : ?>
                        <a class="wpaicg-view-log" 
                        href="javascript:void(0)" 
                        data-content="<?php echo esc_attr($wpaicg_log->data)?>" 
                        data-prompt="<?php echo esc_attr($wpaicg_log->prompt)?>" 
                        data-feedback="<?php echo esc_attr($wpaicg_log->feedback)?>" 
                        data-comment="<?php echo esc_attr($wpaicg_log->comment)?>">
                        <?php 
                            echo esc_html(strlen($wpaicg_log->comment) > 100 ? substr($wpaicg_log->comment, 0, 100) . '..' : $wpaicg_log->comment); 
                        ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html(' '); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html(gmdate('d.m.Y H:i',$wpaicg_log->created_at))?></td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>
<div class="wpaicg-paginate">
    <?php
    if($totalPage > 1){
        echo paginate_links( array(
            'base'         => admin_url('admin.php?page=wpaicg_promptbase&action=logs&wpage=%#%'),
            'total'        => $totalPage,
            'current'      => $wpaicg_log_page,
            'format'       => '?wpage=%#%',
            'show_all'     => false,
            'prev_next'    => false,
            'add_args'     => false,
        ));
    }
    ?>
</div>
<script>
    jQuery(document).ready(function ($) {
        // Use event delegation for '.wpaicg_modal_close' click event
        $(document).on('click', '.wpaicg_modal_close', function () {
            $(this).closest('.wpaicg_modal').hide();
            $('.wpaicg-overlay').hide();
        });

        // Use event delegation for '.wpaicg-view-log' click event
        $(document).on('click', '.wpaicg-view-log', function () {
            let content = $(this).attr('data-content').trim()
                .replace(/\n/g, "<br />")
                .replace(/\\/g, '');

            let modalTitle = `<?php echo esc_html__('View Form Log', 'gpt3-ai-content-generator')?>`;
            let promptLabel = `<p><strong><?php echo esc_html__('Prompt', 'gpt3-ai-content-generator')?>:</strong> </p>`;
            let promptText = $(this).attr('data-prompt');
            let responseLabel = `<p><strong><?php echo esc_html__('Response', 'gpt3-ai-content-generator')?>:</strong></p>`;
            let responseContent = `<div>${content}</div>`;
            // Convert feedback data value to respective icon
            let feedbackIcon;
            let feedback = $(this).attr('data-feedback');
            switch (feedback) {
                case 'thumbs_up':
                    feedbackIcon = '👍';
                    break;
                case 'thumbs_down':
                    feedbackIcon = '👎';
                    break;
                default:
                    feedbackIcon = '';
                    break;
            }
            
            let feedbackLabel = feedbackIcon ? `<p><strong><?php echo esc_html__('Feedback', 'gpt3-ai-content-generator')?>:</strong> ${feedbackIcon}</p>` : '';

            let comment = $(this).attr('data-comment');
            let commentLabel = comment ? `<p><strong><?php echo esc_html__('Comment', 'gpt3-ai-content-generator')?>:</strong> ${comment}</p>` : '';

            $('.wpaicg_modal_title').html(modalTitle);
            $('.wpaicg_modal_content').html(promptLabel)
                .append($('<div>').text(promptText))
                .append(responseLabel)
                .append(responseContent);
                // Only append feedbackLabel and commentLabel if they are not empty
                if (feedbackLabel) {
                    $('.wpaicg_modal_content').append(feedbackLabel);
                }
                if (commentLabel) {
                    $('.wpaicg_modal_content').append(commentLabel);
                }
            $('.wpaicg-overlay, .wpaicg_modal').show();
        });
    });
</script>
