<?php
defined('ABSPATH') or die();
use AiomaticOpenAI\OpenAi\OpenAi;
$aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
if (isset($aiomatic_Spinner_Settings['process_event']) && $aiomatic_Spinner_Settings['process_event'] === 'draft')
{
    add_action('save_post', 'aiomatic_draft_process', 10, 3);
}
elseif (isset($aiomatic_Spinner_Settings['process_event']) && $aiomatic_Spinner_Settings['process_event'] === 'pending')
{
    add_action('save_post', 'aiomatic_pending_process', 10, 3);
}
else
{
    add_action('aiomatic_new_post_cron', 'aiomatic_do_post_wrapper', 10, 2);
    add_action('transition_post_status', 'aiomatic_new_post', 10, 3);
}
add_action('init', 'aiomatic_register_my_custom_cron_event');

function aiomatic_do_post_wrapper($post, $skip_if_edited = false)
{
    if($skip_if_edited == true)
    {
        $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
        if (isset($aiomatic_Spinner_Settings['no_twice']) && $aiomatic_Spinner_Settings['no_twice'] == 'on') 
        {
            if (isset($aiomatic_Spinner_Settings['custom_name']) && trim($aiomatic_Spinner_Settings['custom_name']) != '')
            {
                $custom_name = trim($aiomatic_Spinner_Settings['custom_name']);
                $custom_name = str_replace('%%current_date%%', date("Y-m-d"), $custom_name);
            }
            else
            {
                $custom_name = 'aiomatic_published';
            }
            $meta = get_post_meta($post->ID, $custom_name, true);
            if ($meta == 'pub')
            {
                return;
            }
        }
    }

    aiomatic_do_post($post, false, false, false);
    $editors = get_option('aiomatic_Editor_Rules');
    if (!empty($editors)) 
    {
        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
        foreach($editors as $current_editor)
        {
            if(is_array($current_editor) && $current_editor[5] == '1')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Running content editing rule: ' . $current_editor[8]);
                }
                aiomatic_do_post($post, false, false, $current_editor);
            }
        }
    }
}
function aiomatic_register_my_custom_cron_event() 
{
    add_action('aiomatic_handle_delayed_post', 'aiomatic_process_delayed_post', 10, 1);
}
function aiomatic_schedule_post($post_id, $delay) 
{
    $execution_time = time() + $delay;
    wp_schedule_single_event($execution_time, 'aiomatic_handle_delayed_post', [$post_id]);
}
function aiomatic_process_delayed_post($post_id) 
{
    $post = get_post($post_id);
    if ($post === null) 
    {
        aiomatic_log_to_file('Post ID no longer found! ID is: ' . $post_id);
        return;
    }
    if ($post->post_status === 'draft') 
    {
        $is_draft_added = get_post_meta($post_id, 'aiomatic_draft_processed', true);
        if (!$is_draft_added) 
        {
            update_post_meta($post_id, 'aiomatic_draft_processed', '1');
            aiomatic_do_post_wrapper($post, true);
        }
    } 
    else 
    {
        delete_post_meta($post_id, 'aiomatic_draft_processed');
    }
}
function aiomatic_draft_process($post_id, $post, $update) 
{
    if (wp_is_post_autosave($post_id)) 
    {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if ($post->post_status === 'draft') 
    {
        $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
        if (isset($aiomatic_Spinner_Settings['delay_post']) && $aiomatic_Spinner_Settings['delay_post'] != '' && is_numeric($aiomatic_Spinner_Settings['delay_post'])) 
        {
            aiomatic_schedule_post($post_id, intval($aiomatic_Spinner_Settings['delay_post']));
            return;
        }
        $is_draft_added = get_post_meta($post_id, 'aiomatic_draft_processed', true);
        if (!$is_draft_added) 
        {
            update_post_meta($post_id, 'aiomatic_draft_processed', '1');
            aiomatic_do_post_wrapper($post, true);
        }
    } 
    else 
    {
        delete_post_meta($post_id, 'aiomatic_draft_processed');
    }
}
function aiomatic_pending_process($post_id, $post, $update) 
{
    if (wp_is_post_autosave($post_id)) 
    {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if ($post->post_status === 'pending') 
    {
        $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
        if (isset($aiomatic_Spinner_Settings['delay_post']) && $aiomatic_Spinner_Settings['delay_post'] != '' && is_numeric($aiomatic_Spinner_Settings['delay_post'])) 
        {
            sleep(intval($aiomatic_Spinner_Settings['delay_post']));
            $post = get_post($post_id);
            if($post === null)
            {
                aiomatic_log_to_file('Post ID no longer found! ID is: ' . $post_id);
                return;
            }
        }
        $is_draft_added = get_post_meta($post_id, 'aiomatic_pending_processed', true);
        if (!$is_draft_added) 
        {
            update_post_meta($post_id, 'aiomatic_pending_processed', '1');
            aiomatic_do_post_wrapper($post, true);
        }
    } 
    else 
    {
        delete_post_meta($post_id, 'aiomatic_pending_processed');
    }
}
function aiomatic_new_post($new_status, $old_status, $post)
{
    if ('publish' !== $new_status or 'publish' === $old_status)
    {
        return;
    }
    else
    {
        if($old_status == 'auto-draft' && $new_status == 'publish' && !has_post_thumbnail($post->ID) && ((function_exists('has_blocks') && has_blocks($post)) || ($post->post_content == '' && function_exists('has_blocks') && !class_exists('Classic_Editor'))))
        {
            $delay_it_is_gutenberg = true;
        }
        else
        {
            $delay_it_is_gutenberg = false;
        }
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
        if (isset($aiomatic_Spinner_Settings['aiomatic_spinning']) && $aiomatic_Spinner_Settings['aiomatic_spinning'] == 'on') {
            if (isset($aiomatic_Spinner_Settings['delay_post']) && $aiomatic_Spinner_Settings['delay_post'] != '' && is_numeric($aiomatic_Spinner_Settings['delay_post'])) {
                if(wp_next_scheduled('aiomatic_new_post_cron', array($post, true)) === false)
                {
                    if($delay_it_is_gutenberg && $aiomatic_Spinner_Settings['delay_post'] < 2)
                    {
                        $aiomatic_Spinner_Settings['delay_post'] = 2;
                    }
                    wp_schedule_single_event(time() + $aiomatic_Spinner_Settings['delay_post'], 'aiomatic_new_post_cron', array($post, true));
                }
            }
            else
            {
                if (isset($aiomatic_Spinner_Settings['run_background']) && $aiomatic_Spinner_Settings['run_background'] == 'on') {
                    if($delay_it_is_gutenberg)
                    {
                        if(wp_next_scheduled('aiomatic_new_post_cron', array($post, true)) === false)
                        {
                            wp_schedule_single_event(time() + 2, 'aiomatic_new_post_cron', array($post, true));
                        }
                    }
                    else
                    {
                        $unique_id = uniqid();
                        aiomatic_update_option('aiomatic_do_post_uniqid', $unique_id);
                        $xcron_url = site_url( '?aiomatic_do_post_cronjob=1&post_id=' . $post->ID . '&aiomatic_do_post_key=' . $unique_id);
                        wp_remote_post( $xcron_url, array( 'timeout' => 1, 'blocking' => false, 'sslverify' => false ) );
                    }
                }
                else
                {
                    if($delay_it_is_gutenberg)
                    {
                        if(wp_next_scheduled('aiomatic_new_post_cron', array($post, true)) === false)
                        {
                            wp_schedule_single_event( time() + 2, 'aiomatic_new_post_cron', array($post, true) );
                        }
                    }
                    else
                    {
                        aiomatic_do_post_wrapper($post, true);
                    }
                }
            }
        }
    }
}

function aiomatic_process_elementor_widget(&$element, $allowed_widgets, &$edits_made, $max_tokens, $instructions_token_count, $model, $completionmodels, $exclude_count_before, $ai_instruction, $token, $assistant_id, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, $finish_reason, $error, $vision_file, $thread_id, $store_data, $post) {
    if (!isset($element['elType']) || $element['elType'] !== 'widget') {
        if (isset($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as &$sub_element) {
                aiomatic_process_elementor_widget($sub_element, $allowed_widgets, $edits_made, $max_tokens, $instructions_token_count, $model, $completionmodels, $exclude_count_before, $ai_instruction, $token, $assistant_id, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, $finish_reason, $error, $vision_file, $thread_id, $store_data, $post);
            }
        }
        return;
    }
    if (!isset($element['widgetType']) || !in_array($element['widgetType'], $allowed_widgets)) {
        return;
    }
    if (isset($element['settings']['editor']) && !empty($element['settings']['editor'])) {
        $content_to_edit = $element['settings']['editor'];
        if ($content_to_edit != '') 
        {
            $final_gut_content_ai = '';
            $tokens = aiomatic_encode($content_to_edit);
            $content_token_count = count($tokens);
            $available_tokens = $max_tokens - ($instructions_token_count + $content_token_count + 2);
            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
            {
                if (isset($aiomatic_Spinner_Settings['max_char_chunks']) && $aiomatic_Spinner_Settings['max_char_chunks'] != '' && intval($aiomatic_Spinner_Settings['max_char_chunks']) / 4 < $max_tokens)
                {
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                        aiomatic_log_to_file('Splitting text into chunks of ' . $aiomatic_Spinner_Settings['max_char_chunks'] . ' characters.');
                    }
                    $chunk_split = str_split($content_to_edit, intval($aiomatic_Spinner_Settings['max_char_chunks']));
                }
                else
                {
                    $chunk_split = aiomatic_split_to_token_len($tokens, intval($max_tokens / 2));
                }
            }
            else
            {
                if (isset($aiomatic_Spinner_Settings['max_char_chunks']) && $aiomatic_Spinner_Settings['max_char_chunks'] != '' && intval($aiomatic_Spinner_Settings['max_char_chunks']) / 4 < $max_tokens && (!isset($aiomatic_Spinner_Settings['no_add_content']) || $aiomatic_Spinner_Settings['no_add_content'] != 'on'))
                {
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                        aiomatic_log_to_file('Splitting text into chunks of ' . $aiomatic_Spinner_Settings['max_char_chunks'] . ' characters.');
                    }
                    $chunk_split = str_split($content_to_edit, intval($aiomatic_Spinner_Settings['max_char_chunks']));
                }
                else
                {
                    $chunk_split = array($content_to_edit);
                }
            }
            if (isset($aiomatic_Spinner_Settings['no_add_content']) && $aiomatic_Spinner_Settings['no_add_content'] == 'on')
            {
                $chunk_split = array($content_to_edit);
            }
            foreach($chunk_split as $my_little_chunk)
            {
                if(!in_array($model, $completionmodels))
                {
                    $exclude_count_before += aiomatic_countExcludes($my_little_chunk);
                }
                if(in_array($model, $completionmodels))
                {
                    if (!isset($aiomatic_Spinner_Settings['no_add_content']) || $aiomatic_Spinner_Settings['no_add_content'] != 'on')
                    {
                        $prompt = $ai_instruction . ':\r\n\r\n ' . $my_little_chunk . ' \r\n\r\n';
                    }
                    else
                    {
                        $prompt = $ai_instruction;
                    }
                    $error = '';
                    $finish_reason = '';
                    $max_tokens = aiomatic_get_max_tokens($model);
                    $query_token_count = count(aiomatic_encode($prompt));
                    $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                    {
                        $string_len = strlen($prompt);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $prompt = aiomatic_substr($prompt, 0, $string_len);
                        $prompt = trim($prompt);
                        if(empty($prompt))
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('Empty API seed expression provided (after processing)');
                            }
                        }
                        else
                        {
                            $query_token_count = count(aiomatic_encode($prompt));
                            $available_tokens = $max_tokens - $query_token_count;
                        }
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                    {
                        $api_service = aiomatic_get_api_service($token, $model);
                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $model . ')(' . $available_tokens . ') Content Editor with seed command: ' . $prompt);
                    }
                    $response_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, false, 'contentCEditor', 0, $finish_reason, $error, false, false, false, $vision_file, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                    if($response_text === false)
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit the post chunk using AI: ' . $error . ' !-! ' . $ai_instruction . ' !-! ' . $my_little_chunk);
                        }
                        $final_gut_content_ai .= $my_little_chunk;
                    }
                    else
                    {
                        $response_text = trim($response_text);
                        $final_gut_content_ai .= $response_text;
                    }
                }
                else
                {
                    $aierror = '';
                    $edited_content = aiomatic_edit_text($token, $model, $ai_instruction, $my_little_chunk, $edit_temperature, $edit_top_p, 'contentEditor', 0, $aierror);
                    if($edited_content !== false)
                    {
                        $final_gut_content_ai .= $edited_content;
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post chunk using AI: ' . $aierror . ' !-! ' . $ai_instruction . ' !-! ' . $my_little_chunk);
                        }
                        $final_gut_content_ai .= $my_little_chunk;
                    }
                }
            }
            if(!empty($final_gut_content_ai))
            {
                $processed_content = $final_gut_content_ai;
                if ($processed_content !== $content_to_edit) 
                {
                    $element['settings']['editor'] = $processed_content;
                    $edits_made = true;
                }
            }
        } 
    }
}

add_action('init', 'aiomatic_do_post_callback', 0);
function aiomatic_do_post_callback()
{
    $secretp_key = get_option('aiomatic_do_post_uniqid', false);
    if (isset($_GET['aiomatic_do_post_cronjob']) && $_GET['aiomatic_do_post_cronjob'] == '1' && isset($_GET['post_id']) && is_numeric($_GET['post_id']) && $_GET['aiomatic_do_post_key'] === $secretp_key)
    {
        $post = get_post($_GET['post_id']);
        if($post !== null)
        {
            aiomatic_do_post_wrapper($post, false);
            exit();
        }
    }
}
function aiomatic_do_post($post, $manual = false, $template = false, $editor_rule = false)
{
    $plugin = plugin_basename(__FILE__);
    $plugin_slug = explode('/', $plugin);
    $plugin_slug = $plugin_slug[0]; 
    $uoptions = array();
    $is_activated = aiomatic_is_activated($plugin_slug, $uoptions);
    if($is_activated !== true && $is_activated !== 2)
    {
        aiomatic_log_to_file('The plugin is not activated using a valid purchase code. You need to activate the plugin for this feature to work.');
        return;
    }
    $raw_img_list = array();
    $full_result_list = array();
    $post_link = '';
    $post_title = '';
    $blog_title = '';
    $post_excerpt = '';
    $final_content = '';
    $user_name = '';
    $featured_image = '';
    $post_cats = '';
    $post_tagz = '';
    $postID = '';
    $img_attr = '';
    $thread_id = '';
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['rule_timeout']) && $aiomatic_Main_Settings['rule_timeout'] != '') 
    {
        $timeout = intval($aiomatic_Main_Settings['rule_timeout']);
    } 
    else 
    {
        $timeout = 36000;
    }
    ini_set('safe_mode', 'Off');
    ini_set('max_execution_time', $timeout);
    ini_set('ignore_user_abort', 1);
    ini_set('user_agent', aiomatic_get_random_user_agent());
    if(function_exists('ignore_user_abort'))
    {
        ignore_user_abort(true);
    }
    if(function_exists('set_time_limit'))
    {
        set_time_limit($timeout);
    }
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        require_once(dirname(__FILE__) . "/res/aiomatic-chars.php");
        wp_cache_delete('aiomatic_Spinner_Settings', 'options');
        $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
        $editor_rule_applied = false;
        if($editor_rule !== false && is_array($editor_rule) && $editor_rule[5] == '1')
        {
            if($editor_rule[0] == 'default')
            {
                $editor_rule[0] = '';
            }
            $aiomatic_Spinner_Settings['use_template_auto'] = $editor_rule[0];
            $aiomatic_Spinner_Settings['post_posts'] = $editor_rule[1];
            $aiomatic_Spinner_Settings['post_pages'] = $editor_rule[2];
            $aiomatic_Spinner_Settings['post_custom'] = $editor_rule[3];
            $aiomatic_Spinner_Settings['except_type'] = $editor_rule[4];
            $aiomatic_Spinner_Settings['only_type'] = $editor_rule[6];
            $aiomatic_Spinner_Settings['disabled_categories'] = $editor_rule[7];
            $aiomatic_Spinner_Settings['disable_tags'] = $editor_rule[9];
            $aiomatic_Spinner_Settings['disable_users'] = $editor_rule[10];
            $aiomatic_Spinner_Settings['enable_users'] = $editor_rule[11];
            $aiomatic_Spinner_Settings['featured_status'] = $editor_rule[12];
            $aiomatic_Spinner_Settings['enable_field'] = $editor_rule[13];
            $aiomatic_Spinner_Settings['disable_field'] = $editor_rule[14];
            $aiomatic_Spinner_Settings['enable_tags'] = $editor_rule[15];
            $aiomatic_Spinner_Settings['seo_status'] = $editor_rule[16];
            $aiomatic_Spinner_Settings['enable_default'] = 'yes';
            $editor_rule_applied = true;
        }
        if($editor_rule_applied == false && $manual == false && isset($aiomatic_Spinner_Settings['enable_default']) && $aiomatic_Spinner_Settings['enable_default'] == 'on')
        {
            return;
        }
        if(isset($aiomatic_Spinner_Settings['disabled_categories']) && is_array($aiomatic_Spinner_Settings['disabled_categories']) && in_array('aiomatic_no_category_12345678', $aiomatic_Spinner_Settings['disabled_categories']))
        {
            if (($key = array_search('aiomatic_no_category_12345678', $aiomatic_Spinner_Settings['disabled_categories'])) !== false) 
            {
                unset($aiomatic_Spinner_Settings['disabled_categories'][$key]);
            }
        }
        if(isset($aiomatic_Spinner_Settings['store_data']) && $aiomatic_Spinner_Settings['store_data'] == 'on')
        {
            $store_data = 'on';
        }
        else
        {
            $store_data = 'off';
        }
        $pid = $post->ID;
        $post = get_post($post->ID);
        if($post === null)
        {
            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
            {
                aiomatic_log_to_file('Post ID no longer found: ' . $pid);
            }
            return;
        }
        if(in_array($post->post_type, AIOMATIC_EXCEPTED_POST_TYPES_FROM_EDITING))
        {
            return;
        }
        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
        {
            aiomatic_log_exec_time('Edit Posts');
        }
        $added_img_list = array();
        $added_images = 0;
        $heading_results = array();
        if ($manual)
        {
            if($template !== 'skip')
            {
                if(!empty($template) && is_numeric($template))
                {
                    $template_name = '';
                    $post_data = false;
                    $args = array(
                        'post_type' => 'aiomatic_editor_temp',
                        'p' => intval($template),
                    );
                    $the_query = new WP_Query( $args );
                    if ( $the_query->have_posts() ) 
                    {
                        while ( $the_query->have_posts() ) 
                        {
                            $the_query->the_post();
                            $post_id = get_the_ID();
                            $template_name = get_the_title();
                            $post_data = get_post_meta($post_id, 'aiomatic_json', true);
                        }
                    }
                    else
                    {
                        wp_reset_postdata();
                        aiomatic_log_to_file('Failed to parse AI Content Editor template with ID: ' . $template);
                        return;
                    }
                    wp_reset_postdata();
                    if(!empty($post_data))
                    {
                        if(is_string($post_data))
                        {
                            $post_data = str_replace("\\'", "'", $post_data); 
                            $post_data = str_replace("'", "\\\\'", $post_data); 
                            $post_data_decode = json_decode($post_data);
                        }
                        else
                        {
                            $post_data_decode = $post_data;
                        }
                        if($post_data_decode === null)
                        {
                            $json_last_error = json_last_error();
                            $json_last_error_msg = json_last_error_msg();
                            $error_message = 'Failed to parse Post Editor template with ID: ' . $post_data . "\n";
                            $error_message .= 'JSON Error: ' . $json_last_error . ' - ' . $json_last_error_msg;
                            aiomatic_log_to_file($error_message);
                            return;
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                        {
                            aiomatic_log_to_file('Loading Manual AI Content Editor Template: ' . $template_name);
                        }
                        foreach($post_data_decode as $theindex => $thevalue)
                        {
                            $aiomatic_Spinner_Settings[$theindex] = $thevalue;
                        }
                    }
                    else
                    {
                        aiomatic_log_to_file('Cannot find the AI Content Editor template with ID: ' . $template);
                        return;
                    }
                }
                else
                {
                    if(isset($aiomatic_Spinner_Settings['use_template_manual']) && $aiomatic_Spinner_Settings['use_template_manual'] != '')
                    {
                        $template_name = '';
                        $post_data = false;
                        $args = array(
                            'post_type' => 'aiomatic_editor_temp',
                            'p' => intval($aiomatic_Spinner_Settings['use_template_manual']),
                        );
                        $the_query = new WP_Query( $args );
                        if ( $the_query->have_posts() ) 
                        {
                            while ( $the_query->have_posts() ) 
                            {
                                $the_query->the_post();
                                $post_id = get_the_ID();
                                $template_name = get_the_title();
                                $post_data = get_post_meta($post_id, 'aiomatic_json', true);
                            }
                        }
                        else
                        {
                            wp_reset_postdata();
                            aiomatic_log_to_file('Failed to parse AI Content Editor template with ID: ' . $aiomatic_Spinner_Settings['use_template_manual']);
                            return;
                        }
                        wp_reset_postdata();
                        if(!empty($post_data))
                        {
                            if(is_string($post_data))
                            {
                                $post_data = str_replace("\\'", "'", $post_data); 
                                $post_data = str_replace("'", "\\\\'", $post_data); 
                                $post_data_decode = json_decode($post_data);
                            }
                            else
                            {
                                $post_data_decode = $post_data;
                            }
                            if($post_data_decode === null)
                            {
                                $json_last_error = json_last_error();
                                $json_last_error_msg = json_last_error_msg();
                                $error_message = 'Failed to parse Post Editor template with ID: ' . $post_data . "\n";
                                $error_message .= 'JSON Error: ' . $json_last_error . ' - ' . $json_last_error_msg;
                                aiomatic_log_to_file($error_message);
                                return;
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Loading Manual AI Content Editor Template: ' . $template_name);
                            }
                            foreach($post_data_decode as $theindex => $thevalue)
                            {
                                $aiomatic_Spinner_Settings[$theindex] = $thevalue;
                            }
                        }
                        else
                        {
                            aiomatic_log_to_file('Cannot find the AI Content Editor template with ID: ' . $aiomatic_Spinner_Settings['use_template_manual']);
                            return;
                        }
                    }
                }
            }
        }
        else
        {
            if(isset($aiomatic_Spinner_Settings['use_template_auto']) && $aiomatic_Spinner_Settings['use_template_auto'] != '')
            {
                $template_name = '';
                $post_data = false;
                $args = array(
                    'post_type' => 'aiomatic_editor_temp',
                    'p' => intval($aiomatic_Spinner_Settings['use_template_auto']),
                );
                $the_query = new WP_Query( $args );
                if ( $the_query->have_posts() ) 
                {
                    while ( $the_query->have_posts() ) 
                    {
                        $the_query->the_post();
                        $post_id = get_the_ID();
                        $template_name = get_the_title();
                        $post_data = get_post_meta($post_id, 'aiomatic_json', true);
                    }
                }
                else
                {
                    wp_reset_postdata();
                    aiomatic_log_to_file('Failed to process AI Content Editor template with ID: ' . $aiomatic_Spinner_Settings['use_template_auto']);
                    return;
                }
                wp_reset_postdata();
                if(!empty($post_data))
                {
                    if(is_string($post_data))
                    {
                        $post_data = str_replace("\\'", "'", $post_data); 
                        $post_data = str_replace("'", "\\\\'", $post_data); 
                        $post_data_decode = json_decode($post_data);
                    }
                    else
                    {
                        $post_data_decode = $post_data;
                    }
                    if($post_data_decode === null)
                    {
                        $json_last_error = json_last_error();
                        $json_last_error_msg = json_last_error_msg();
                        $error_message = 'Failed to parse Post Editor template with ID: ' . $post_data . "\n";
                        $error_message .= 'JSON Error: ' . $json_last_error . ' - ' . $json_last_error_msg;
                        aiomatic_log_to_file($error_message);
                        return;
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                    {
                        aiomatic_log_to_file('Loading Automatic AI Content Editor Template: ' . $template_name);
                    }
                    foreach($post_data_decode as $theindex => $thevalue)
                    {
                        $aiomatic_Spinner_Settings[$theindex] = $thevalue;
                    }
                }
                else
                {
                    aiomatic_log_to_file('Cannot find the AI Content Editor template with ID: ' . $aiomatic_Spinner_Settings['use_template_auto']);
                    return;
                }
            }
        }
        if ($manual || isset($aiomatic_Spinner_Settings['aiomatic_spinning']) && $aiomatic_Spinner_Settings['aiomatic_spinning'] == 'on') 
        {
            if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
            {
                aiomatic_log_to_file('You need to insert a valid OpenAI/AiomaticAPI API Key for this to work!');
                return;
            }
            $vision_file = '';
            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
            $appids = array_filter($appids);
            $token = $appids[array_rand($appids)];
            if (!$manual && isset($aiomatic_Spinner_Settings['post_posts'])) {
                if ($aiomatic_Spinner_Settings['post_posts'] == 'on' && 'post' === $post->post_type) {
                    return;
                }
            }
            if (!$manual && isset($aiomatic_Spinner_Settings['post_pages'])) {
                if ($aiomatic_Spinner_Settings['post_pages'] == 'on' && 'page' === $post->post_type) {
                    return;
                }
            }
            if (!$manual && isset($aiomatic_Spinner_Settings['post_custom'])) {
                if ($aiomatic_Spinner_Settings['post_custom'] == 'on' && 'page' !== $post->post_type && 'post' !== $post->post_type) 
                {
                    if (isset($aiomatic_Spinner_Settings['except_type']) && $aiomatic_Spinner_Settings['except_type'] != '') 
                    {
                        $excepted_types = explode(',', $aiomatic_Spinner_Settings['except_type']);
                        $excepted_types = array_map('trim', $excepted_types);
                        if(!in_array($post->post_type, $excepted_types))
                        {
                            return;
                        }
                    }
                    else
                    {
                        return;
                    }
                }
            }
            if (!$manual && (!isset($aiomatic_Spinner_Settings['post_custom']) || $aiomatic_Spinner_Settings['post_custom'] != 'on'))
            {
                if (isset($aiomatic_Spinner_Settings['only_type']) && $aiomatic_Spinner_Settings['only_type'] != '') 
                {
                    $only_types = explode(',', $aiomatic_Spinner_Settings['only_type']);
                    $only_types = array_map('trim', $only_types);
                    if(!in_array($post->post_type, $only_types))
                    {
                        return;
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['custom_name']) && trim($aiomatic_Spinner_Settings['custom_name']) != '')
            {
                $custom_name = trim($aiomatic_Spinner_Settings['custom_name']);
                $custom_name = str_replace('%%current_date%%', date("Y-m-d"), $custom_name);
            }
            else
            {
                $custom_name = 'aiomatic_published';
            }
            $meta = get_post_meta($post->ID, $custom_name, true);
            if (!$manual && $meta == 'pub' && $editor_rule === false)
            {
                return;
            }
            $meta = get_post_meta($post->ID, "aiomatic_auto_post_spinned", true);
            if ($meta === '1') 
            {
                return;
            }
            if (isset($aiomatic_Spinner_Settings['max_seed_tokens']) && $aiomatic_Spinner_Settings['max_seed_tokens'] != '')
            {
                $max_seed_tokens = intval($aiomatic_Spinner_Settings['max_seed_tokens']);
            }
            else
            {
                $max_seed_tokens = '';
            }
            if (isset($aiomatic_Spinner_Settings['temperature']) && $aiomatic_Spinner_Settings['temperature'] != '')
            {
                $temperature = floatval($aiomatic_Spinner_Settings['temperature']);
            }
            else
            {
                $temperature = 1;
            }
            if (isset($aiomatic_Spinner_Settings['top_p']) && $aiomatic_Spinner_Settings['top_p'] != '')
            {
                $top_p = floatval($aiomatic_Spinner_Settings['top_p']);
            }
            else
            {
                $top_p = 1;
            }
            if (isset($aiomatic_Spinner_Settings['presence_penalty']) && $aiomatic_Spinner_Settings['presence_penalty'] != '')
            {
                $presence_penalty = floatval($aiomatic_Spinner_Settings['presence_penalty']);
            }
            else
            {
                $presence_penalty = 0;
            }
            if (isset($aiomatic_Spinner_Settings['frequency_penalty']) && $aiomatic_Spinner_Settings['frequency_penalty'] != '')
            {
                $frequency_penalty = floatval($aiomatic_Spinner_Settings['frequency_penalty']);
            }
            else
            {
                $frequency_penalty = 0;
            }
            if (isset($aiomatic_Spinner_Settings['model']) && $aiomatic_Spinner_Settings['model'] != '')
            {
                $completion_model = $aiomatic_Spinner_Settings['model'];
            }
            else
            {
                $completion_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
            }
            if (isset($aiomatic_Spinner_Settings['append_assistant_id']) && $aiomatic_Spinner_Settings['append_assistant_id'] != '')
            {
                $assistant_id = $aiomatic_Spinner_Settings['append_assistant_id'];
            }
            else
            {
                $assistant_id = '';
            }
            if (isset($aiomatic_Spinner_Settings['headings_model']) && $aiomatic_Spinner_Settings['headings_model'] != '')
            {
                $headings_model = $aiomatic_Spinner_Settings['headings_model'];
            }
            else
            {
                $headings_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
            }
            if (isset($aiomatic_Spinner_Settings['headings_assistant_id']) && $aiomatic_Spinner_Settings['headings_assistant_id'] != '')
            {
                $headings_assistant_id = $aiomatic_Spinner_Settings['headings_assistant_id'];
            }
            else
            {
                $headings_assistant_id = '';
            }
            if (isset($aiomatic_Spinner_Settings['headings_ai_command']) && $aiomatic_Spinner_Settings['headings_ai_command'] != '')
            {
                $headings_ai_command = $aiomatic_Spinner_Settings['headings_ai_command'];
            }
            else
            {
                $headings_ai_command = 'Write %%needed_heading_count%% PAA related questions, each on a new line, for the title: %%post_title%%';
            }
            if (isset($aiomatic_Spinner_Settings['max_tokens']) && $aiomatic_Spinner_Settings['max_tokens'] != '')
            {
                $max_tokens_completion = intval($aiomatic_Spinner_Settings['max_tokens']);
            }
            else
            {
                $max_tokens_completion = aiomatic_get_max_tokens($completion_model);
            }
            if($max_tokens_completion <= 0)
            {
                $max_tokens_completion = AIOMATIC_DEFAULT_MAX_TOKENS;
            }
            if($max_tokens_completion > aiomatic_get_max_tokens($completion_model) && aiomatic_get_max_tokens($completion_model) > 0)
            {
                $max_tokens_completion = aiomatic_get_max_tokens($completion_model);
            }
            $max_tokens = $max_tokens_completion;
            $updated = false;
            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
            {
                aiomatic_log_to_file('Starting editing post ID: ' . $pid);
            }
            $post_title = $post->post_title;
            $post_excerpt = $post->post_excerpt;
            $final_content = $post->post_content;
            if (isset($aiomatic_Spinner_Settings['ai_duplication']) && $aiomatic_Spinner_Settings['ai_duplication'] != '' && $aiomatic_Spinner_Settings['ai_duplication'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting AI duplication checking...');
                }
                $post_type_to_check = $post->post_type;
                $duplicate_post_id = false;
                $duplicate_post_title = false;
                if (isset($aiomatic_Spinner_Settings['similarity_threshold']) && $aiomatic_Spinner_Settings['similarity_threshold'] != '' && is_numeric($aiomatic_Spinner_Settings['similarity_threshold']))
                {
                    $similarity_threshold = floatval($aiomatic_Spinner_Settings['similarity_threshold']);
                }
                else
                {
                    $similarity_threshold = 0.9;
                }
                if (isset($aiomatic_Spinner_Settings['max_to_process_in_one_go']) && $aiomatic_Spinner_Settings['max_to_process_in_one_go'] != '' && is_numeric($aiomatic_Spinner_Settings['max_to_process_in_one_go']))
                {
                    $max_to_process_in_one_go = intval($aiomatic_Spinner_Settings['max_to_process_in_one_go']);
                }
                else
                {
                    $max_to_process_in_one_go = PHP_INT_MAX;
                }
                if (isset($aiomatic_Spinner_Settings['dup_titles']) && $aiomatic_Spinner_Settings['dup_titles'] == 'on')
                {
                    $dup_titles = 1;
                }
                else
                {
                    $dup_titles = 0;
                }
                if (isset($aiomatic_Spinner_Settings['enhance_articles']) && $aiomatic_Spinner_Settings['enhance_articles'] == 'on')
                {
                    $enhance_articles = 1;
                }
                else
                {
                    $enhance_articles = 0;
                }
                if (isset($aiomatic_Spinner_Settings['enhance_model']) && $aiomatic_Spinner_Settings['enhance_model'] != '')
                {
                    $enhance_model = $aiomatic_Spinner_Settings['enhance_model'];
                }
                else
                {
                    $enhance_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                }
                if (isset($aiomatic_Spinner_Settings['enhance_assistant_id']) && $aiomatic_Spinner_Settings['enhance_assistant_id'] != '')
                {
                    $enhance_assistant_id = $aiomatic_Spinner_Settings['enhance_assistant_id'];
                }
                else
                {
                    $enhance_assistant_id = '';
                }
                if (isset($aiomatic_Spinner_Settings['ai_instruction_enhance']) && $aiomatic_Spinner_Settings['ai_instruction_enhance'] != '')
                {
                    $ai_instruction_enhance = $aiomatic_Spinner_Settings['ai_instruction_enhance'];
                }
                else
                {
                    $ai_instruction_enhance = '';
                }
                if (isset($aiomatic_Spinner_Settings['duplication_template']) && $aiomatic_Spinner_Settings['duplication_template'] != '')
                {
                    $duplication_template = $aiomatic_Spinner_Settings['duplication_template'];
                }
                else
                {
                    $duplication_template = '%%post_content_plain_text%%';
                }
                if (isset($aiomatic_Spinner_Settings['duplication_index']) && $aiomatic_Spinner_Settings['duplication_index'] != '')
                {
                    $duplication_index = $aiomatic_Spinner_Settings['duplication_index'];
                }
                else
                {
                    $duplication_index = '_aiomatic_index';
                }
                if (isset($aiomatic_Spinner_Settings['duplicate_action']) && $aiomatic_Spinner_Settings['duplicate_action'] != '')
                {
                    $duplicate_action = $aiomatic_Spinner_Settings['duplicate_action'];
                }
                else
                {
                    $duplicate_action = 'delete';
                }
                if (isset($aiomatic_Spinner_Settings['duplicate_handle']) && $aiomatic_Spinner_Settings['duplicate_handle'] != '')
                {
                    $duplicate_handle = $aiomatic_Spinner_Settings['duplicate_handle'];
                }
                else
                {
                    $duplicate_handle = 'keep_relevant';
                }
                $best_similarity = 0;
                $new_embedding = aiomatic_check_duplicate_or_replace($post, $aiomatic_Main_Settings, $post_type_to_check, $duplicate_post_id, $duplicate_post_title, $similarity_threshold, $max_to_process_in_one_go, $dup_titles, $duplication_template, $duplication_index, $best_similarity);
                if (!$new_embedding) 
                {
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to create embedding in AI duplication checking');
                    }
                }
                else
                {
                    if($duplicate_handle == 'keep_old' && $new_embedding === 'REPLACE_OLD')
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                        {
                            aiomatic_log_to_file('Enforcing to keep old post...');
                        }
                        $new_embedding = 'DUPLICATE_KEEP_OLD';
                    }
                    elseif($duplicate_handle == 'keep_new' && $new_embedding === 'DUPLICATE_KEEP_OLD')
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                        {
                            aiomatic_log_to_file('Enforcing to keep new post...');
                        }
                        $new_embedding = 'REPLACE_OLD';
                    }
                    $kept_source = '';
                    $rem_source = '';
                    if($new_embedding === 'REPLACE_OLD' || $new_embedding === 'DUPLICATE_KEEP_OLD')
                    {
                        if($enhance_articles == 1 && $ai_instruction_enhance != '')
                        {
                            $go_process = false;
                            $dupli_post = null;
                            if($new_embedding === 'REPLACE_OLD')
                            {
                                $go_process = true;
                                $ai_instruction_enhance = str_replace('%%post_title_to_be_kept%%', $post->post_title, $ai_instruction_enhance);
                                $ai_instruction_enhance = str_replace('%%post_content_to_be_kept%%', $post->post_content, $ai_instruction_enhance);
                                $ai_instruction_enhance = str_replace('%%post_plain_text_content_to_be_kept%%', wp_strip_all_tags(strip_shortcodes($post->post_content)), $ai_instruction_enhance);
                                $all_meta = get_post_meta($post->ID);
                                if ($all_meta && !empty($all_meta)) 
                                {
                                    foreach ($all_meta as $key => $value) 
                                    {
                                        if (stripos($key, 'post_url') !== false) 
                                        {
                                            $kept_source = is_array($value) ? $value[0] : $value;
                                            break;
                                        }
                                    }
                                }
                                $ai_instruction_enhance = str_replace('%%post_source_to_be_kept%%', $kept_source, $ai_instruction_enhance);

                                $dupli_post = get_post($duplicate_post_id);
                                if ($dupli_post === null)
                                {
                                    $ai_instruction_enhance = str_replace('%%post_title_to_be_removed%%', $duplicate_post_title, $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_content_to_be_removed%%', '', $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_plain_text_content_to_be_removed%%', '', $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_source_to_be_removed%%', '', $ai_instruction_enhance);
                                }
                                else
                                {
                                    $ai_instruction_enhance = str_replace('%%post_title_to_be_removed%%', $duplicate_post_title, $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_content_to_be_removed%%', $dupli_post->post_content, $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_plain_text_content_to_be_removed%%', wp_strip_all_tags(strip_shortcodes($dupli_post->post_content)), $ai_instruction_enhance);
                                    $all_meta = get_post_meta($duplicate_post_id);
                                    if ($all_meta && !empty($all_meta)) 
                                    {
                                        foreach ($all_meta as $key => $value) 
                                        {
                                            if (stripos($key, 'post_url') !== false) 
                                            {
                                                $rem_source = is_array($value) ? $value[0] : $value;
                                                break;
                                            }
                                        }
                                    }
                                    $ai_instruction_enhance = str_replace('%%post_source_to_be_removed%%', $rem_source, $ai_instruction_enhance);
                                }
                                $ai_instruction_enhance = aiomatic_replaceSynergyShortcodes($ai_instruction_enhance);
                                $ai_instruction_enhance = trim($ai_instruction_enhance);
                                if(!empty($rem_source))
                                {
                                    $existing_sources = get_post_meta($post->ID, 'aiomatic_duplicate_sources', true);
                                    if(!is_array($existing_sources))
                                    {
                                        $existing_sources = array();
                                    }
                                    $existing_sources[] = $rem_source;
                                    update_post_meta($post->ID, 'aiomatic_duplicate_sources', $existing_sources);
                                }
                            }
                            elseif($new_embedding === 'DUPLICATE_KEEP_OLD')
                            {
                                $go_process = true;
                                $ai_instruction_enhance = str_replace('%%post_title_to_be_removed%%', $post->post_title, $ai_instruction_enhance);
                                $ai_instruction_enhance = str_replace('%%post_content_to_be_removed%%', $post->post_content, $ai_instruction_enhance);
                                $ai_instruction_enhance = str_replace('%%post_plain_text_content_to_be_removed%%', wp_strip_all_tags(strip_shortcodes($post->post_content)), $ai_instruction_enhance);
                                $all_meta = get_post_meta($post->ID);
                                if ($all_meta && !empty($all_meta)) 
                                {
                                    foreach ($all_meta as $key => $value) 
                                    {
                                        if (stripos($key, 'post_url') !== false) 
                                        {
                                            $kept_source = is_array($value) ? $value[0] : $value;
                                            break;
                                        }
                                    }
                                }
                                $ai_instruction_enhance = str_replace('%%post_source_to_be_removed%%', $kept_source, $ai_instruction_enhance);
                                if(!empty($kept_source))
                                {
                                    $existing_sources = get_post_meta($duplicate_post_id, 'aiomatic_duplicate_sources', true);
                                    if(!is_array($existing_sources))
                                    {
                                        $existing_sources = array();
                                    }
                                    $existing_sources[] = $kept_source;
                                    update_post_meta($duplicate_post_id, 'aiomatic_duplicate_sources', $existing_sources);
                                }
                                $dupli_post = get_post($duplicate_post_id);
                                if ($dupli_post === null)
                                {
                                    $ai_instruction_enhance = str_replace('%%post_title_to_be_kept%%', $duplicate_post_title, $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_content_to_be_kept%%', '', $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_plain_text_content_to_be_kept%%', '', $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_source_to_be_kept%%', '', $ai_instruction_enhance);
                                }
                                else
                                {
                                    $ai_instruction_enhance = str_replace('%%post_title_to_be_kept%%', $duplicate_post_title, $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_content_to_be_kept%%', $dupli_post->post_content, $ai_instruction_enhance);
                                    $ai_instruction_enhance = str_replace('%%post_plain_text_content_to_be_kept%%', wp_strip_all_tags(strip_shortcodes($dupli_post->post_content)), $ai_instruction_enhance);
                                    $all_meta = get_post_meta($duplicate_post_id);
                                    if ($all_meta && !empty($all_meta)) 
                                    {
                                        foreach ($all_meta as $key => $value) 
                                        {
                                            if (stripos($key, 'post_url') !== false) 
                                            {
                                                $rem_source = is_array($value) ? $value[0] : $value;
                                                break;
                                            }
                                        }
                                    }
                                    $ai_instruction_enhance = str_replace('%%post_source_to_be_kept%%', $rem_source, $ai_instruction_enhance);
                                }
                                $ai_instruction_enhance = aiomatic_replaceSynergyShortcodes($ai_instruction_enhance);
                                $ai_instruction_enhance = trim($ai_instruction_enhance);
                            }
                            if($go_process === true && $ai_instruction_enhance != '')
                            {
                                $query_token_count = count(aiomatic_encode($ai_instruction_enhance));
                                if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                                {
                                    $ai_instruction_enhance = aiomatic_substr($ai_instruction_enhance, 0, (0-($max_seed_tokens * 4)));
                                    $query_token_count = count(aiomatic_encode($ai_instruction_enhance));
                                }
                                $max_tokens_enhance = aiomatic_get_max_tokens($enhance_model);
                                $available_tokens = aiomatic_compute_available_tokens($enhance_model, $max_tokens_enhance, $ai_instruction_enhance, $query_token_count);
                                $max_result_tokens = aiomatic_get_max_tokens($enhance_model);
                                if($available_tokens > $max_result_tokens)
                                {
                                    $available_tokens = $max_result_tokens;
                                }
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($ai_instruction_enhance);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $ai_instruction_enhance = aiomatic_substr($ai_instruction_enhance, 0, $string_len);
                                    $ai_instruction_enhance = trim($ai_instruction_enhance);
                                    if(empty($ai_instruction_enhance))
                                    {
                                        aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_instruction_enhance, true));
                                        return;
                                    }
                                    $query_token_count = count(aiomatic_encode($ai_instruction_enhance));
                                    $available_tokens = $max_tokens_enhance - $query_token_count;
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $enhance_model);
                                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $enhance_assistant_id . '\\' . $enhance_model . ')(' . $available_tokens . ') Duplicate Post Editor with seed command: ' . $ai_instruction_enhance);
                                }
                                $aierror = '';
                                $aiwriter = '';
                                $finish_reason = '';
                                $generated_text = aiomatic_generate_text($token, $enhance_model, $ai_instruction_enhance, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'duplicateEditing', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', $enhance_assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                                if($generated_text === false)
                                {
                                    aiomatic_log_to_file($aierror);
                                    return;
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $enhance_model);
                                    aiomatic_log_to_file($api_service . ' responded successfully, ID: ' . $post->ID);
                                }
                                $args = array();
                                if($new_embedding === 'REPLACE_OLD')
                                {
                                    $args['ID'] = $post->ID;
                                }
                                elseif($new_embedding === 'DUPLICATE_KEEP_OLD' && isset($dupli_post->ID))
                                {
                                    $args['ID'] = $dupli_post->ID;
                                }
                                if(isset($args['ID']))
                                {
                                    $args['post_content'] = $generated_text;
                                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    remove_filter('title_save_pre', 'wp_filter_kses');
                                    update_post_meta($args['ID'], $custom_name, "pub");
                                    $post_updated = wp_update_post($args);
                                    add_filter('content_save_pre', 'wp_filter_post_kses');
                                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    add_filter('title_save_pre', 'wp_filter_kses');
                                    if (is_wp_error($post_updated)) {
                                        $errors = $post_updated->get_error_messages();
                                        foreach ($errors as $error) {
                                            aiomatic_log_to_file('Error occured while updating post for AI enhanced content "' . $post->post_title . '": ' . $error);
                                        }
                                    }
                                    else
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated enhanced content.');
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($new_embedding === 'REPLACE_OLD')
                    {
                        if($duplicate_action === 'delete')
                        {
                            wp_delete_post($duplicate_post_id, true);
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Duplicate found for post ID: ' . $pid . ' ("' . $post->post_title . '"), deleting old post: ' . $duplicate_post_id . ' ("' . $duplicate_post_title . '"), similarity score: ' . $best_similarity);
                            }
                        }
                        elseif($duplicate_action === 'thrash')
                        {
                            wp_delete_post($duplicate_post_id, false);
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Duplicate found for post ID: ' . $pid . ' ("' . $post->post_title . '"), thrashing old post: ' . $duplicate_post_id . ' ("' . $duplicate_post_title . '"), similarity score: ' . $best_similarity);
                            }
                        }
                        elseif($duplicate_action === 'nothing')
                        {
                            aiomatic_log_to_file('Duplicate found for post ID: ' . $pid . ' ("' . $post->post_title . '"), old post: ' . $duplicate_post_id . ' ("' . $duplicate_post_title . '"), similarity score: ' . $best_similarity);
                        }
                        else
                        {
                            aiomatic_log_to_file('Invalid duplicate action: ' . $duplicate_action);
                        }
                    }
                    elseif($new_embedding === 'DUPLICATE_KEEP_OLD')
                    {
                        if($duplicate_action === 'delete')
                        {
                            wp_delete_post($pid, true);
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Duplicate found for post ID, deleting newly published post: ' . $pid . ' ("' . $post->post_title . '"), keeping old post: ' . $duplicate_post_id . ' ("' . $duplicate_post_title . '"), similarity score: ' . $best_similarity);
                            }
                            return;
                        }
                        elseif($duplicate_action === 'thrash')
                        {
                            wp_delete_post($pid, false);
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Duplicate found for post ID, thrashing newly published post: ' . $pid . ' ("' . $post->post_title . '"), keeping old post: ' . $duplicate_post_id . ' ("' . $duplicate_post_title . '"), similarity score: ' . $best_similarity);
                            }
                            return;
                        }
                        elseif($duplicate_action === 'nothing')
                        {
                            aiomatic_log_to_file('Duplicate found for post ID, newly published post: ' . $pid . ' ("' . $post->post_title . '"), keeping old post: ' . $duplicate_post_id . ' ("' . $duplicate_post_title . '"), similarity score: ' . $best_similarity);
                        }
                        else
                        {
                            aiomatic_log_to_file('Invalid duplicate action: ' . $duplicate_action);
                        }
                    }
                    elseif($new_embedding === 'NO_MATCH')
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                        {
                            aiomatic_log_to_file('No duplication matches found for: ' . $pid . ' ("' . $post->post_title . '"), max similarity score: ' . $best_similarity);
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                        {
                            aiomatic_log_to_file('Unknown return value from duplicate checking for post ID: ' . $pid . ', return value: ' . $new_embedding);
                        }

                    }
                    if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                    {
                        $args = array();
                        $args['ID'] = $post->ID;
                        $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                        remove_filter('content_save_pre', 'wp_filter_post_kses');
                        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                        remove_filter('title_save_pre', 'wp_filter_kses');
                        update_post_meta($post->ID, $custom_name, "pub");
                        $post_updated = wp_update_post($args);
                        add_filter('content_save_pre', 'wp_filter_post_kses');
                        add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                        add_filter('title_save_pre', 'wp_filter_kses');
                        if (is_wp_error($post_updated)) {
                            $errors = $post_updated->get_error_messages();
                            foreach ($errors as $error) {
                                aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                            }
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                    {
                        $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                        if($date_stamp)
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            $postdate = date("Y-m-d H:i:s", $date_stamp);
                            $args['post_date'] = $postdate;
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['ai_rewriter']) && $aiomatic_Spinner_Settings['ai_rewriter'] != '' && $aiomatic_Spinner_Settings['ai_rewriter'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post rewriter...');
                }
                $vision_file = '';
                $vision_file_title = '';
                $vision_file_excerpt = '';
                $vision_file_slug = '';
                if (isset($aiomatic_Spinner_Settings['edit_temperature']) && $aiomatic_Spinner_Settings['edit_temperature'] != '')
                {
                    $edit_temperature = floatval($aiomatic_Spinner_Settings['edit_temperature']);
                }
                else
                {
                    $edit_temperature = 0;
                }
                if (isset($aiomatic_Spinner_Settings['edit_model']) && $aiomatic_Spinner_Settings['edit_model'] != '')
                {
                    $model = trim($aiomatic_Spinner_Settings['edit_model']);
                }
                else
                {
                    $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                }
                if (isset($aiomatic_Spinner_Settings['title_model']) && $aiomatic_Spinner_Settings['title_model'] != '')
                {
                    $title_model = trim($aiomatic_Spinner_Settings['title_model']);
                }
                else
                {
                    $title_model = $model;
                }
                if (isset($aiomatic_Spinner_Settings['excerpt_model']) && $aiomatic_Spinner_Settings['excerpt_model'] != '')
                {
                    $excerpt_model = trim($aiomatic_Spinner_Settings['excerpt_model']);
                }
                else
                {
                    $excerpt_model = $model;
                }
                if (isset($aiomatic_Spinner_Settings['slug_model']) && $aiomatic_Spinner_Settings['slug_model'] != '')
                {
                    $slug_model = trim($aiomatic_Spinner_Settings['slug_model']);
                }
                else
                {
                    $slug_model = $model;
                }
                if (isset($aiomatic_Spinner_Settings['ai_vision']) && $aiomatic_Spinner_Settings['ai_vision'] == 'on')
                {
                    $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                    if($avatar !== false)
                    {
                        $vision_file = $avatar;
                    }
                }
                if (isset($aiomatic_Spinner_Settings['ai_title_vision']) && $aiomatic_Spinner_Settings['ai_title_vision'] == 'on')
                {
                    $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                    if($avatar !== false)
                    {
                        $vision_file_title = $avatar;
                    }
                }
                if (isset($aiomatic_Spinner_Settings['ai_excerpt_vision']) && $aiomatic_Spinner_Settings['ai_excerpt_vision'] == 'on')
                {
                    $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                    if($avatar !== false)
                    {
                        $vision_file_excerpt = $avatar;
                    }
                }
                if (isset($aiomatic_Spinner_Settings['ai_slug_vision']) && $aiomatic_Spinner_Settings['ai_slug_vision'] == 'on')
                {
                    $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                    if($avatar !== false)
                    {
                        $vision_file_slug = $avatar;
                    }
                }
                if (isset($aiomatic_Spinner_Settings['edit_assistant_id']) && $aiomatic_Spinner_Settings['edit_assistant_id'] != '')
                {
                    $assistant_id = trim($aiomatic_Spinner_Settings['edit_assistant_id']);
                }
                else
                {
                    $assistant_id = '';
                }
                if (isset($aiomatic_Spinner_Settings['edit_top_p']) && $aiomatic_Spinner_Settings['edit_top_p'] != '')
                {
                    $edit_top_p = floatval($aiomatic_Spinner_Settings['edit_top_p']);
                }
                else
                {
                    $edit_top_p = 1;
                }
                if (isset($aiomatic_Spinner_Settings['edit_presence_penalty']) && $aiomatic_Spinner_Settings['edit_presence_penalty'] != '')
                {
                    $edit_presence_penalty = floatval($aiomatic_Spinner_Settings['edit_presence_penalty']);
                }
                else
                {
                    $edit_presence_penalty = 0;
                }
                if (isset($aiomatic_Spinner_Settings['edit_frequency_penalty']) && $aiomatic_Spinner_Settings['edit_frequency_penalty'] != '')
                {
                    $edit_frequency_penalty = floatval($aiomatic_Spinner_Settings['edit_frequency_penalty']);
                }
                else
                {
                    $edit_frequency_penalty = 1;
                }
                $completionmodels = aiomatic_get_all_models(true);
                if ((isset($aiomatic_Spinner_Settings['ai_instruction']) && $aiomatic_Spinner_Settings['ai_instruction'] != '') || (isset($aiomatic_Spinner_Settings['ai_instruction_title']) && $aiomatic_Spinner_Settings['ai_instruction_title'] != '') || (isset($aiomatic_Spinner_Settings['ai_instruction_excerpt']) && $aiomatic_Spinner_Settings['ai_instruction_excerpt'] != '') || (isset($aiomatic_Spinner_Settings['ai_instruction_slug']) && $aiomatic_Spinner_Settings['ai_instruction_slug'] != ''))
                {
                    $ai_instruction = trim($aiomatic_Spinner_Settings['ai_instruction']);
                    $ai_instruction = aiomatic_replaceSynergyShortcodes($ai_instruction);
                    $ai_instruction_title = trim($aiomatic_Spinner_Settings['ai_instruction_title']);
                    $ai_instruction_title = aiomatic_replaceSynergyShortcodes($ai_instruction_title);
                    $ai_instruction_excerpt = trim($aiomatic_Spinner_Settings['ai_instruction_excerpt']);
                    $ai_instruction_excerpt = aiomatic_replaceSynergyShortcodes($ai_instruction_excerpt);
                    $ai_instruction_slug = trim($aiomatic_Spinner_Settings['ai_instruction_slug']);
                    $ai_instruction_slug = aiomatic_replaceSynergyShortcodes($ai_instruction_slug);
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                    {
                        aiomatic_log_to_file('Now editing post ID: ' . $post->ID);
                    }
                    $ai_instruction = aiomatic_replaceAIPostShortcodes($ai_instruction, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_instruction = trim($ai_instruction);
                    $ai_instruction_title = aiomatic_replaceAIPostShortcodes($ai_instruction_title, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_instruction_title = trim($ai_instruction_title);
                    $ai_instruction_excerpt = aiomatic_replaceAIPostShortcodes($ai_instruction_excerpt, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_instruction_excerpt = trim($ai_instruction_excerpt);
                    $ai_instruction_slug = aiomatic_replaceAIPostShortcodes($ai_instruction_slug, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_instruction_slug = str_replace('%%post_slug%%', $post->post_name, $ai_instruction_slug);
                    $ai_instruction_slug = trim($ai_instruction_slug);
                    if(isset($aiomatic_Spinner_Settings['protect_html']) && $aiomatic_Spinner_Settings['protect_html'] == 'on')
                    {
                        if(!in_array($model, $completionmodels))
                        {
                            if(!empty($ai_instruction))
                            {
                                $ai_instruction .= ", numbers in brackets are protected terms, keep them unchanged in the returned text.";
                            }
                        }
                        else
                        {
                            $ai_instruction .= ", don't edit HTML tags, only text.";
                        }
                    }
                    $pre_tags_matches = array();
                    $pre_tags_matches_s = array();
                    $conseqMatchs = array();
                    $htmlfounds = array();
                    if(!in_array($model, $completionmodels))
                    {
                        $final_content_pre = aiomatic_replaceExcludes($final_content, $htmlfounds, $pre_tags_matches, $pre_tags_matches_s, $conseqMatchs);
                    }
                    else
                    {
                        $final_content_pre = $final_content;
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['max_char']) && $aiomatic_Spinner_Settings['max_char'] != '') 
                    {
                        if(strlen($ai_instruction_title) > $aiomatic_Spinner_Settings['max_char'])
                        {
                            aiomatic_log_to_file('Skipping post, title too long, max is: ' . $aiomatic_Spinner_Settings['max_char']);
                        }
                        if(strlen($ai_instruction) > $aiomatic_Spinner_Settings['max_char'])
                        {
                            aiomatic_log_to_file('Skipping post, content too long, max is: ' . $aiomatic_Spinner_Settings['max_char']);
                        }
                        if(strlen($ai_instruction_excerpt) > $aiomatic_Spinner_Settings['max_char'])
                        {
                            aiomatic_log_to_file('Skipping post, excerpt too long, max is: ' . $aiomatic_Spinner_Settings['max_char']);
                        }
                    }
                    $instructions_token_count = count(aiomatic_encode($ai_instruction));
                    $instructions_token_count_title = count(aiomatic_encode($ai_instruction_title));
                    $instructions_token_count_excerpt = count(aiomatic_encode($ai_instruction_excerpt));
                    $instructions_token_count_slug = count(aiomatic_encode($ai_instruction_slug));
                    $title_token_count = count(aiomatic_encode($post_title));
                    $excerpt_token_count = count(aiomatic_encode($post->post_excerpt));
                    $slug_token_count = count(aiomatic_encode($post->post_name));
                    $max_tokens = aiomatic_get_max_tokens($model);
                    $max_title_tokens = aiomatic_get_max_tokens($title_model);
                    $max_excerpt_tokens = aiomatic_get_max_tokens($excerpt_model);
                    $max_slug_tokens = aiomatic_get_max_tokens($slug_model);
                    $available_title_tokens = $max_title_tokens - ($instructions_token_count_title + $title_token_count);
                    $available_excerpt_tokens = $max_excerpt_tokens - ($instructions_token_count_excerpt + $excerpt_token_count);
                    $available_slug_tokens = $max_slug_tokens - ($instructions_token_count_slug + $slug_token_count);
                    $title_ai_edited = '';
                    $excerpt_ai_edited = '';
                    if ((!isset($aiomatic_Spinner_Settings['no_title']) || $aiomatic_Spinner_Settings['no_title'] != 'on') && !empty($ai_instruction_title))
                    {
                        if($available_title_tokens < 0)
                        {
                            aiomatic_log_to_file('Skipping editing title, it is too long: ' . $post->post_title);
                        }
                        else
                        {
                            if(in_array($title_model, $completionmodels))
                            {
                                if (!isset($aiomatic_Spinner_Settings['no_add_title']) || $aiomatic_Spinner_Settings['no_add_title'] != 'on')
                                {
                                    $prompt = $ai_instruction_title . ': ' . $post_title;
                                }
                                else
                                {
                                    $prompt = $ai_instruction_title;
                                }
                                $error = '';
                                $finish_reason = '';
                                $max_tokens = aiomatic_get_max_tokens($title_model);
                                $query_token_count = count(aiomatic_encode($prompt));
                                $available_tokens = aiomatic_compute_available_tokens($title_model, $max_tokens, $prompt, $query_token_count);
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($prompt);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $prompt = aiomatic_substr($prompt, 0, $string_len);
                                    $prompt = trim($prompt);
                                    if(empty($prompt))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Empty API seed expression provided (after processing)');
                                        }
                                    }
                                    else
                                    {
                                        $query_token_count = count(aiomatic_encode($prompt));
                                        $available_tokens = $max_tokens - $query_token_count;
                                    }
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $title_model);
                                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $title_model . ')(' . $available_tokens . ') Title Editor with seed command: ' . $prompt);
                                }
                                $response_text = aiomatic_generate_text($token, $title_model, $prompt, $available_tokens, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, false, 'titleCEditor', 0, $finish_reason, $error, true, false, false, $vision_file_title, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                                if($response_text === false)
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post title using AI: ' . $error);
                                    }
                                }
                                else
                                {
                                    $response_text = ucfirst(trim(trim(trim(trim($response_text), '.'), ' "\'')));
                                    $title_ai_edited = $response_text;
                                    $post_title = $response_text;
                                }
                            }
                            else
                            {
                                $aierror = '';
                                $edited_content = aiomatic_edit_text($token, $title_model, $ai_instruction_title, $post_title, $edit_temperature, $edit_top_p, 'titleEditor', 0, $aierror);
                                if($edited_content !== false)
                                {
                                    $edited_content = ucfirst(trim(trim(trim(trim($edited_content), '.'), ' "\'')));
                                    $title_ai_edited = $edited_content;
                                    $post_title = $edited_content;
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post title using AI: ' . $aierror);
                                    }
                                }
                            }
                        }
                    }
                    if ((!isset($aiomatic_Spinner_Settings['no_excerpt']) || $aiomatic_Spinner_Settings['no_excerpt'] != 'on') && !empty($ai_instruction_excerpt))
                    {
                        if($available_excerpt_tokens < 0)
                        {
                            aiomatic_log_to_file('Skipping editing excerpt, it is too long: ' . $post->post_excerpt);
                        }
                        else
                        {
                            if(in_array($excerpt_model, $completionmodels))
                            {
                                if (!isset($aiomatic_Spinner_Settings['no_add_excerpt']) || $aiomatic_Spinner_Settings['no_add_excerpt'] != 'on')
                                {
                                    $prompt = $ai_instruction_excerpt . ': ' . $post_excerpt;
                                }
                                else
                                {
                                    $prompt = $ai_instruction_excerpt;
                                }
                                $error = '';
                                $finish_reason = '';
                                $max_tokens = aiomatic_get_max_tokens($excerpt_model);
                                $query_token_count = count(aiomatic_encode($prompt));
                                $available_tokens = aiomatic_compute_available_tokens($excerpt_model, $max_tokens, $prompt, $query_token_count);
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($prompt);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $prompt = aiomatic_substr($prompt, 0, $string_len);
                                    $prompt = trim($prompt);
                                    if(empty($prompt))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Empty API seed expression provided (after processing)');
                                        }
                                    }
                                    else
                                    {
                                        $query_token_count = count(aiomatic_encode($prompt));
                                        $available_tokens = $max_tokens - $query_token_count;
                                    }
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $excerpt_model);
                                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $excerpt_model . ')(' . $available_tokens . ') Excerpt Editor with seed command: ' . $prompt);
                                }
                                $response_text = aiomatic_generate_text($token, $excerpt_model, $prompt, $available_tokens, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, false, 'excerptCEditor', 0, $finish_reason, $error, true, false, false, $vision_file_excerpt, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                                if($response_text === false)
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post excerpt using AI: ' . $error);
                                    }
                                }
                                else
                                {
                                    $response_text = trim($response_text);
                                    $response_text = ucfirst(trim(trim(trim(trim($response_text), '.'), ' "\'')));
                                    $excerpt_ai_edited = $response_text;
                                    $post_excerpt = $response_text;
                                }
                            }
                            else
                            {
                                $aierror = '';
                                $edited_content = aiomatic_edit_text($token, $excerpt_model, $ai_instruction_excerpt, $post_excerpt, $edit_temperature, $edit_top_p, 'excerptEditor', 0, $aierror);
                                if($edited_content !== false)
                                {
                                    $edited_content = ucfirst(trim(trim(trim(trim($edited_content), '.'), ' "\'')));
                                    $excerpt_ai_edited = $edited_content;
                                    $post_excerpt = $edited_content;
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post excerpt using AI: ' . $aierror);
                                    }
                                }
                            }
                        }
                    }
                    $edited_slug = '';
                    if ((!isset($aiomatic_Spinner_Settings['no_slug']) || $aiomatic_Spinner_Settings['no_slug'] != 'on') && !empty($ai_instruction_slug))
                    {
                        if($available_slug_tokens < 0)
                        {
                            aiomatic_log_to_file('Skipping editing slug, it is too long: ' . $post->post_excerpt);
                        }
                        else
                        {
                            if(in_array($slug_model, $completionmodels))
                            {
                                if (!isset($aiomatic_Spinner_Settings['no_add_slug']) || $aiomatic_Spinner_Settings['no_add_slug'] != 'on')
                                {
                                    $prompt = $ai_instruction_slug . ': ' . $post->post_name;
                                }
                                else
                                {
                                    $prompt = $ai_instruction_slug;
                                }
                                $error = '';
                                $finish_reason = '';
                                $max_tokens = aiomatic_get_max_tokens($slug_model);
                                $query_token_count = count(aiomatic_encode($prompt));
                                $available_tokens = aiomatic_compute_available_tokens($slug_model, $max_tokens, $prompt, $query_token_count);
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($prompt);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $prompt = aiomatic_substr($prompt, 0, $string_len);
                                    $prompt = trim($prompt);
                                    if(empty($prompt))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Empty API seed expression provided (after processing)');
                                        }
                                    }
                                    else
                                    {
                                        $query_token_count = count(aiomatic_encode($prompt));
                                        $available_tokens = $max_tokens - $query_token_count;
                                    }
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $slug_model);
                                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $slug_model . ')(' . $available_tokens . ') Slug Editor with seed command: ' . $prompt);
                                }
                                $response_text = aiomatic_generate_text($token, $slug_model, $prompt, $available_tokens, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, false, 'slugCEditor', 0, $finish_reason, $error, true, false, false, $vision_file_slug, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                                if($response_text === false)
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post slug using AI: ' . $error);
                                    }
                                }
                                else
                                {
                                    $response_text = trim($response_text);
                                    $response_text = ucfirst(trim(trim(trim(trim($response_text), '.'), ' "\'')));
                                    $edited_slug = sanitize_title($response_text);
                                    if(!empty($max_slug_len))
                                    {
                                        $edited_slug = substr($edited_slug, 0, intval($max_slug_len));
                                        $edited_slug = trim($edited_slug, '-');
                                    }
                                }
                            }
                            else
                            {
                                $aierror = '';
                                $edited_content = aiomatic_edit_text($token, $slug_model, $ai_instruction_slug, $post->post_name, $edit_temperature, $edit_top_p, 'slugEditor', 0, $aierror);
                                if($edited_content !== false)
                                {
                                    $edited_content = ucfirst(trim(trim(trim(trim($edited_content), '.'), ' "\'')));
                                    $edited_slug = sanitize_title($response_text);
                                    if(!empty($max_slug_len))
                                    {
                                        $edited_slug = substr($edited_slug, 0, intval($max_slug_len));
                                        $edited_slug = trim($edited_slug, '-');
                                    }
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post slug using AI: ' . $aierror);
                                    }
                                }
                            }
                        }
                    }
                    $one_success = false;
                    $final_content_ai = '';
                    $exclude_count_before = 0;
                    $elementor_updated = false;
                    if ((!isset($aiomatic_Spinner_Settings['no_content']) || $aiomatic_Spinner_Settings['no_content'] != 'on') && !empty($ai_instruction))
                    {
                        $elementor_found = false;
                        if ((!isset($aiomatic_Spinner_Settings['enable_elementor']) || $aiomatic_Spinner_Settings['enable_elementor'] != '0'))
                        {
                            if(!function_exists('is_plugin_active'))
                            {
                                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                            }
                            if ( is_plugin_active( 'elementor/elementor.php' ) || is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) 
                            {
                                $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                                if ($elementor_data) {
                                    $elementor_json = json_decode($elementor_data, true);
                                    if ($elementor_json && is_array($elementor_json)) 
                                    {
                                        $elementor_found = true;
                                        $elementor_widgets = ['text-editor', 'heading', 'accordion', 'toggle', 'tabs', 'alert', 'call-to-action', 'testimonial', 'html'];
                                        $edits_made = false;
                                        $max_tokens = aiomatic_get_max_tokens($model);
                                        foreach ($elementor_json as &$element) 
                                        {
                                            $finish_reason = '';
                                            $error = '';
                                            aiomatic_process_elementor_widget($element, $elementor_widgets, $edits_made, $max_tokens, $instructions_token_count, $model, $completionmodels, $exclude_count_before, $ai_instruction, $token, $assistant_id, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, $finish_reason, $error, $vision_file, $thread_id, $store_data, $post);
                                        }
                                        if ($edits_made) 
                                        {
                                            update_post_meta($post->ID, '_elementor_data', wp_json_encode($elementor_json));
                                            $elementor_updated = true;
                                            if (class_exists('\Elementor\Plugin')) {
                                                if(defined('ELEMENTOR_VERSION'))
                                                {
                                                    update_post_meta($post->ID, '_elementor_version', ELEMENTOR_VERSION);
                                                }
                                                update_post_meta($post->ID, '_elementor_edit_mode', 'builder');
                                                if (
                                                    isset(\Elementor\Plugin::$instance) &&
                                                    isset(\Elementor\Plugin::$instance->posts_css_manager) &&
                                                    method_exists(\Elementor\Plugin::$instance->posts_css_manager, 'clear_cache') &&
                                                    isset(\Elementor\Plugin::$instance->files_manager) &&
                                                    method_exists(\Elementor\Plugin::$instance->files_manager, 'clear_cache')
                                                ) {
                                                    \Elementor\Plugin::$instance->posts_css_manager->clear_cache($post->ID);
                                                    \Elementor\Plugin::$instance->files_manager->clear_cache();
                                                }
                                                if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) && method_exists('\Elementor\Core\Files\CSS\Post', 'clear_cache') ) 
                                                {
                                                    \Elementor\Core\Files\CSS\Post::clear_cache( $post->ID );
                                                }
                                                if ( class_exists( '\ElementorPro\Plugin' ) ) {
                                                    \ElementorPro\Plugin::instance()
                                                        ->modules_manager
                                                        ->get_modules( 'theme-builder' )
                                                        ->update_location_data( $post->ID );
                                                }
                                                wp_update_post( [
                                                    'ID'                => $post->ID,
                                                    'post_modified'     => current_time( 'mysql' ),
                                                    'post_modified_gmt' => current_time( 'mysql', 1 ),
                                                ] );
                                                delete_transient('elementor_css_cache');
                                                do_action('save_post', $post->ID, get_post($post->ID), true);
                                                do_action('elementor/editor/after_save', $post->ID);
                                                do_action('elementor/core/files/clear_cache');
                                            }
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                aiomatic_log_to_file('Post ID ' . $post->ID . ' had its Elementor blocks edited successfully');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if($elementor_found === false)
                        {
                            if ((isset($aiomatic_Spinner_Settings['enable_gutenberg']) && $aiomatic_Spinner_Settings['enable_gutenberg'] == '1') && has_blocks($final_content_pre))
                            {
                                $blocks = parse_blocks($final_content_pre);
                                if (isset($aiomatic_Spinner_Settings['gutenberg_list']) && is_array($aiomatic_Spinner_Settings['gutenberg_list']) && !empty($aiomatic_Spinner_Settings['gutenberg_list']))
                                {
                                    $gutenberg_block_types = $aiomatic_Spinner_Settings['gutenberg_list'];
                                }
                                else
                                {
                                    $gutenberg_block_types = ['core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/code', 'core/preformatted', 'core/pullquote', 'core/table', 'core/verse'];
                                }
                                $edits_made = false;
                                foreach ($blocks as &$block)
                                {
                                    if (isset($block['blockName']) && in_array($block['blockName'], $gutenberg_block_types)) 
                                    {
                                        $content_to_edit = $block['innerHTML'] ?? '';
                                        if ($content_to_edit != '') 
                                        {
                                            $final_gut_content_ai = '';
                                            $tokens = aiomatic_encode($content_to_edit);
                                            $content_token_count = count($tokens);
                                            $available_tokens = $max_tokens - ($instructions_token_count + $content_token_count + 2);
                                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                            {
                                                if (isset($aiomatic_Spinner_Settings['max_char_chunks']) && $aiomatic_Spinner_Settings['max_char_chunks'] != '' && intval($aiomatic_Spinner_Settings['max_char_chunks']) / 4 < $max_tokens)
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                        aiomatic_log_to_file('Splitting text into chunks of ' . $aiomatic_Spinner_Settings['max_char_chunks'] . ' characters.');
                                                    }
                                                    $chunk_split = str_split($content_to_edit, intval($aiomatic_Spinner_Settings['max_char_chunks']));
                                                }
                                                else
                                                {
                                                    $chunk_split = aiomatic_split_to_token_len($tokens, intval($max_tokens / 2));
                                                }
                                            }
                                            else
                                            {
                                                if (isset($aiomatic_Spinner_Settings['max_char_chunks']) && $aiomatic_Spinner_Settings['max_char_chunks'] != '' && intval($aiomatic_Spinner_Settings['max_char_chunks']) / 4 < $max_tokens && (!isset($aiomatic_Spinner_Settings['no_add_content']) || $aiomatic_Spinner_Settings['no_add_content'] != 'on'))
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                        aiomatic_log_to_file('Splitting text into chunks of ' . $aiomatic_Spinner_Settings['max_char_chunks'] . ' characters.');
                                                    }
                                                    $chunk_split = str_split($content_to_edit, intval($aiomatic_Spinner_Settings['max_char_chunks']));
                                                }
                                                else
                                                {
                                                    $chunk_split = array($content_to_edit);
                                                }
                                            }
                                            if (isset($aiomatic_Spinner_Settings['no_add_content']) && $aiomatic_Spinner_Settings['no_add_content'] == 'on')
                                            {
                                                $chunk_split = array($content_to_edit);
                                            }
                                            foreach($chunk_split as $my_little_chunk)
                                            {
                                                if(!in_array($model, $completionmodels))
                                                {
                                                    $exclude_count_before += aiomatic_countExcludes($my_little_chunk);
                                                }
                                                if(in_array($model, $completionmodels))
                                                {
                                                    if (!isset($aiomatic_Spinner_Settings['no_add_content']) || $aiomatic_Spinner_Settings['no_add_content'] != 'on')
                                                    {
                                                        $prompt = $ai_instruction . ':\r\n\r\n ' . $my_little_chunk . ' \r\n\r\n';
                                                    }
                                                    else
                                                    {
                                                        $prompt = $ai_instruction;
                                                    }
                                                    $error = '';
                                                    $finish_reason = '';
                                                    $max_tokens = aiomatic_get_max_tokens($model);
                                                    $query_token_count = count(aiomatic_encode($prompt));
                                                    $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                                                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                                    {
                                                        $string_len = strlen($prompt);
                                                        $string_len = $string_len / 2;
                                                        $string_len = intval(0 - $string_len);
                                                        $prompt = aiomatic_substr($prompt, 0, $string_len);
                                                        $prompt = trim($prompt);
                                                        if(empty($prompt))
                                                        {
                                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                                aiomatic_log_to_file('Empty API seed expression provided (after processing)');
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $query_token_count = count(aiomatic_encode($prompt));
                                                            $available_tokens = $max_tokens - $query_token_count;
                                                        }
                                                    }
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        $api_service = aiomatic_get_api_service($token, $model);
                                                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $model . ')(' . $available_tokens . ') Content Editor with seed command set: ' . $prompt);
                                                    }
                                                    $response_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, false, 'contentCEditor', 0, $finish_reason, $error, false, false, false, $vision_file, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                                                    if($response_text === false)
                                                    {
                                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit the post chunk using AI: ' . $error . ' !-! ' . $ai_instruction . ' !-! ' . $my_little_chunk);
                                                        }
                                                        $final_gut_content_ai .= $my_little_chunk;
                                                    }
                                                    else
                                                    {
                                                        $response_text = trim($response_text);
                                                        $final_gut_content_ai .= $response_text;
                                                    }
                                                }
                                                else
                                                {
                                                    $aierror = '';
                                                    $edited_content = aiomatic_edit_text($token, $model, $ai_instruction, $my_little_chunk, $edit_temperature, $edit_top_p, 'contentEditor', 0, $aierror);
                                                    if($edited_content !== false)
                                                    {
                                                        $final_gut_content_ai .= $edited_content;
                                                    }
                                                    else
                                                    {
                                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post chunk using AI: ' . $aierror . ' !-! ' . $ai_instruction . ' !-! ' . $my_little_chunk);
                                                        }
                                                        $final_gut_content_ai .= $my_little_chunk;
                                                    }
                                                }
                                            }
                                            if(!empty($final_gut_content_ai))
                                            {
                                                $block['innerHTML'] = $final_gut_content_ai;
                                                $edits_made = true;
                                            }
                                        }
                                    }
                                }
                                if($edits_made === true)
                                {
                                    $gutenberg_post_content = aiomatic_reconstruct_post_content($blocks);
                                    if(!empty($gutenberg_post_content))
                                    {
                                        $final_content_ai = $gutenberg_post_content;
                                        $one_success = true;
                                    }
                                }
                            }
                            else
                            {
                                $tokens = aiomatic_encode($final_content_pre);
                                $content_token_count = count($tokens);
                                $available_tokens = $max_tokens - ($instructions_token_count + $content_token_count + 2);
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    if (isset($aiomatic_Spinner_Settings['max_char_chunks']) && $aiomatic_Spinner_Settings['max_char_chunks'] != '' && intval($aiomatic_Spinner_Settings['max_char_chunks']) / 4 < $max_tokens)
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Splitting text into chunks of ' . $aiomatic_Spinner_Settings['max_char_chunks'] . ' characters.');
                                        }
                                        $chunk_split = str_split($final_content_pre, intval($aiomatic_Spinner_Settings['max_char_chunks']));
                                    }
                                    else
                                    {
                                        $chunk_split = aiomatic_split_to_token_len($tokens, intval($max_tokens / 2));
                                    }
                                }
                                else
                                {
                                    if (isset($aiomatic_Spinner_Settings['max_char_chunks']) && $aiomatic_Spinner_Settings['max_char_chunks'] != '' && intval($aiomatic_Spinner_Settings['max_char_chunks']) / 4 < $max_tokens)
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Splitting text into chunks of ' . $aiomatic_Spinner_Settings['max_char_chunks'] . ' characters.');
                                        }
                                        $chunk_split = str_split($final_content_pre, intval($aiomatic_Spinner_Settings['max_char_chunks']));
                                    }
                                    else
                                    {
                                        $chunk_split = array($final_content_pre);
                                    }
                                }
                                if (isset($aiomatic_Spinner_Settings['no_add_content']) && $aiomatic_Spinner_Settings['no_add_content'] == 'on')
                                {
                                    $chunk_split = array($final_content_pre);
                                }
                                foreach($chunk_split as $my_little_chunk)
                                {
                                    if(!in_array($model, $completionmodels))
                                    {
                                        $exclude_count_before += aiomatic_countExcludes($my_little_chunk);
                                    }
                                    if(in_array($model, $completionmodels))
                                    {
                                        if (!isset($aiomatic_Spinner_Settings['no_add_content']) || $aiomatic_Spinner_Settings['no_add_content'] != 'on')
                                        {
                                            $prompt = $ai_instruction . ':\r\n\r\n ' . $my_little_chunk . ' \r\n\r\n';
                                        }
                                        else
                                        {
                                            $prompt = $ai_instruction;
                                        }
                                        $error = '';
                                        $finish_reason = '';
                                        $max_tokens = aiomatic_get_max_tokens($model);
                                        $query_token_count = count(aiomatic_encode($prompt));
                                        $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                                        if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                        {
                                            $string_len = strlen($prompt);
                                            $string_len = $string_len / 2;
                                            $string_len = intval(0 - $string_len);
                                            $prompt = aiomatic_substr($prompt, 0, $string_len);
                                            $prompt = trim($prompt);
                                            if(empty($prompt))
                                            {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                    aiomatic_log_to_file('Empty API seed expression provided (after processing)');
                                                }
                                            }
                                            else
                                            {
                                                $query_token_count = count(aiomatic_encode($prompt));
                                                $available_tokens = $max_tokens - $query_token_count;
                                            }
                                        }
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                        {
                                            $api_service = aiomatic_get_api_service($token, $model);
                                            aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $model . ')(' . $available_tokens . ') Content Editor with seed command prompt: ' . $prompt);
                                        }
                                        $response_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, $edit_temperature, $edit_top_p, $edit_presence_penalty, $edit_frequency_penalty, false, 'contentCEditor', 0, $finish_reason, $error, false, false, false, $vision_file, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                                        if($response_text === false)
                                        {
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit the post chunk using AI: ' . $error . ' !-! ' . $ai_instruction . ' !-! ' . $my_little_chunk);
                                            }
                                            $final_content_ai .= $my_little_chunk;
                                        }
                                        else
                                        {
                                            $response_text = trim($response_text);
                                            $final_content_ai .= $response_text;
                                            $one_success = true;
                                        }
                                    }
                                    else
                                    {
                                        $aierror = '';
                                        $edited_content = aiomatic_edit_text($token, $model, $ai_instruction, $my_little_chunk, $edit_temperature, $edit_top_p, 'contentEditor', 0, $aierror);
                                        if($edited_content !== false)
                                        {
                                            $final_content_ai .= $edited_content;
                                            $one_success = true;
                                        }
                                        else
                                        {
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit post chunk using AI: ' . $aierror . ' !-! ' . $ai_instruction . ' !-! ' . $my_little_chunk);
                                            }
                                            $final_content_ai .= $my_little_chunk;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($one_success === false)
                    {
                        $final_content_ai = '';
                    }
                    if($final_content_ai != '')
                    {
                        if(!in_array($model, $completionmodels))
                        {
                            $exclude_count_after = aiomatic_countExcludes($final_content_ai);
                        }
                        else
                        {
                            $exclude_count_after = 0;
                        }
                        if((!isset($aiomatic_Spinner_Settings['no_html_check']) || $aiomatic_Spinner_Settings['no_html_check'] != 'on') && $exclude_count_before != $exclude_count_after)
                        {
                            aiomatic_log_to_file('Post edit failed, as HTML tags were removed by the AI editor. Because of this, edits are not saved. Count of HTML tags missing: ' . ($exclude_count_before - $exclude_count_after));
                        }
                        else
                        {
                            if(!in_array($model, $completionmodels))
                            {
                                $final_content_ai = aiomatic_restoreExcludes($final_content_ai, $htmlfounds, $pre_tags_matches, $pre_tags_matches_s, $conseqMatchs);
                            }
                            $final_content = $final_content_ai;
                            $args = array();
                            $args['ID'] = $post->ID;
                            if (!isset($aiomatic_Main_Settings['no_undetectibility']) || $aiomatic_Main_Settings['no_undetectibility'] != 'on' && !aiomatic_stringContainsArrayChars($final_content, $xchars)) 
                            {
                                if (!isset($aiomatic_Main_Settings['no_undetectibility_editor']) || $aiomatic_Main_Settings['no_undetectibility_editor'] != 'on') 
                                {
                                    if(!isset($xchars))
                                    {
                                        $xchars = array();
                                    }
                                    $rand_percentage = rand(10, 20);
                                    $final_content = aiomatic_make_unique($final_content, $xchars, $rand_percentage);
                                }
                            }
                            $args['post_content'] = $final_content;
                            if($title_ai_edited != '')
                            {
                                if ((isset($aiomatic_Spinner_Settings['rewrite_url']) && $aiomatic_Spinner_Settings['rewrite_url'] == 'on'))
                                {
                                    if(!empty(sanitize_title($title_ai_edited)))
                                    {
                                        $args['post_name'] = sanitize_title($title_ai_edited);
                                    }
                                }
                                $args['post_title'] = aiomatic_truncate_title($title_ai_edited);
                            }
                            if(!empty($edited_slug))
                            {
                                $args['post_name'] = trim($edited_slug);
                            }
                            if($excerpt_ai_edited != '')
                            {
                                $args['post_excerpt'] = trim($excerpt_ai_edited);
                            }
                            if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                            {
                                $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            }
                            if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                            {
                                $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                if($date_stamp)
                                {
                                    $postdate = date("Y-m-d H:i:s", $date_stamp);
                                    $args['post_date'] = $postdate;
                                }
                            }
                            update_post_meta($post->ID, $custom_name, "pub");
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post for content rewriting "' . $post->post_title . '": ' . $error);
                                }
                            }
                            else
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI rewritten content.');
                                }
                            }
                        }
                    }
                    else
                    {
                        if($title_ai_edited != '')
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            if ((isset($aiomatic_Spinner_Settings['rewrite_url']) && $aiomatic_Spinner_Settings['rewrite_url'] == 'on'))
                            {
                                if(!empty(sanitize_title($title_ai_edited)))
                                {
                                    $args['post_name'] = sanitize_title($title_ai_edited);
                                }
                            }
                            $args['post_title'] = aiomatic_truncate_title($title_ai_edited);
                            if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                            {
                                $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            }
                            if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                            {
                                $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                if($date_stamp)
                                {
                                    $postdate = date("Y-m-d H:i:s", $date_stamp);
                                    $args['post_date'] = $postdate;
                                }
                            }
                            if($excerpt_ai_edited != '')
                            {
                                $args['post_excerpt'] = trim($excerpt_ai_edited);
                            }
                            if(!empty($edited_slug))
                            {
                                $args['post_name'] = trim($edited_slug);
                            }
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                            else
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated title.');
                                }
                            }
                        }
                        else
                        {
                            if($excerpt_ai_edited != '')
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                                {
                                    $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                                }
                                if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                                {
                                    $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                    if($date_stamp)
                                    {
                                        $postdate = date("Y-m-d H:i:s", $date_stamp);
                                        $args['post_date'] = $postdate;
                                    }
                                }
                                if(!empty($edited_slug))
                                {
                                    $args['post_name'] = trim($edited_slug);
                                }
                                $args['post_excerpt'] = trim($excerpt_ai_edited);
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating excerpt post for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated excerpt.');
                                    }
                                }
                            }
                            else
                            {
                                if($edited_slug != '')
                                {
                                    $args = array();
                                    $args['ID'] = $post->ID;
                                    if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                                    {
                                        $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                                    }
                                    if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                                    {
                                        $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                        if($date_stamp)
                                        {
                                            $postdate = date("Y-m-d H:i:s", $date_stamp);
                                            $args['post_date'] = $postdate;
                                        }
                                    }
                                    $args['post_name'] = trim($edited_slug);
                                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    remove_filter('title_save_pre', 'wp_filter_kses');
                                    update_post_meta($post->ID, $custom_name, "pub");
                                    $post_updated = wp_update_post($args);
                                    add_filter('content_save_pre', 'wp_filter_post_kses');
                                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    add_filter('title_save_pre', 'wp_filter_kses');
                                    if (is_wp_error($post_updated)) {
                                        $errors = $post_updated->get_error_messages();
                                        foreach ($errors as $error) {
                                            aiomatic_log_to_file('Error occured while updating post slug for title "' . $post->post_title . '": ' . $error);
                                        }
                                    }
                                    else
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated slug.');
                                        }
                                    }
                                }
                                else
                                {
                                    if(!$elementor_updated)
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to be editted, nothing returned from AI editor');
                                        }
                                    }
                                    else
                                    {
                                        if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                                        {
                                            $args = array();
                                            $args['ID'] = $post->ID;
                                            $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                            remove_filter('title_save_pre', 'wp_filter_kses');
                                            update_post_meta($post->ID, $custom_name, "pub");
                                            $post_updated = wp_update_post($args);
                                            add_filter('content_save_pre', 'wp_filter_post_kses');
                                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                            add_filter('title_save_pre', 'wp_filter_kses');
                                            if (is_wp_error($post_updated)) {
                                                $errors = $post_updated->get_error_messages();
                                                foreach ($errors as $error) {
                                                    aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                                }
                                            }
                                        }
                                        if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                                        {
                                            $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                            if($date_stamp)
                                            {
                                                $args = array();
                                                $args['ID'] = $post->ID;
                                                $postdate = date("Y-m-d H:i:s", $date_stamp);
                                                $args['post_date'] = $postdate;
                                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                                remove_filter('title_save_pre', 'wp_filter_kses');
                                                update_post_meta($post->ID, $custom_name, "pub");
                                                $post_updated = wp_update_post($args);
                                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                                add_filter('title_save_pre', 'wp_filter_kses');
                                                if (is_wp_error($post_updated)) {
                                                    $errors = $post_updated->get_error_messages();
                                                    foreach ($errors as $error) {
                                                        aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['append_spintax']) && $aiomatic_Spinner_Settings['append_spintax'] != '' && $aiomatic_Spinner_Settings['append_spintax'] != 'disabled')
            {

                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post content completion module...');
                }
                if(isset($aiomatic_Spinner_Settings['social_list']) && !empty($aiomatic_Spinner_Settings['social_list']) && is_array($aiomatic_Spinner_Settings['social_list']))
                {
                    $social_list = $aiomatic_Spinner_Settings['social_list'];
                }
                else
                {
                    $social_list = array();
                }
                if(isset($aiomatic_Spinner_Settings['rich_embed_location']) && !empty($aiomatic_Spinner_Settings['rich_embed_location']))
                {
                    $rich_embed_location = $aiomatic_Spinner_Settings['rich_embed_location'];
                }
                else
                {
                    $rich_embed_location = 'bottom';
                }
                $vision_file = '';
                if (isset($aiomatic_Spinner_Settings['headings']) && $aiomatic_Spinner_Settings['headings'] != '')
                {
                    $headings = intval($aiomatic_Spinner_Settings['headings']);
                }
                else
                {
                    $headings = '';
                }
                if (isset($aiomatic_Spinner_Settings['ai_vision_add']) && $aiomatic_Spinner_Settings['ai_vision_add'] == 'on')
                {
                    $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                    if($avatar !== false)
                    {
                        $vision_file = $avatar;
                    }
                }
                if (isset($aiomatic_Spinner_Settings['images']) && $aiomatic_Spinner_Settings['images'] != '')
                {
                    $images = intval($aiomatic_Spinner_Settings['images']);
                }
                else
                {
                    $images = '';
                }
                if (isset($aiomatic_Spinner_Settings['videos']) && $aiomatic_Spinner_Settings['videos'] != '')
                {
                    $videos = $aiomatic_Spinner_Settings['videos'];
                }
                else
                {
                    $videos = '';
                }
                if (isset($aiomatic_Spinner_Settings['max_result_tokens']) && $aiomatic_Spinner_Settings['max_result_tokens'] != '')
                {
                    $max_result_tokens = intval($aiomatic_Spinner_Settings['max_result_tokens']);
                }
                else
                {
                    $max_result_tokens = aiomatic_get_max_tokens($completion_model);
                }
                $max_tokens = aiomatic_get_max_tokens($completion_model);
                if (isset($aiomatic_Spinner_Settings['ai_command']) && $aiomatic_Spinner_Settings['ai_command'] != '')
                {
                    $aicontent = trim(strip_tags($aiomatic_Spinner_Settings['ai_command']));
                    $aicontent = aiomatic_replaceSynergyShortcodes($aicontent);
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $aicontent = aiomatic_replaceAIPostShortcodes($aicontent, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                }
                else
                {
                    $aicontent = trim(strip_tags($final_content));
                    if(empty($aicontent))
                    {
                        $aicontent = trim(strip_tags($post->post_excerpt));
                    }
                    if(empty($aicontent))
                    {
                        $aicontent = trim(strip_tags($post_title));
                        $last_char = aiomatic_substr($aicontent, -1, null);
                        if(!ctype_punct($last_char))
                        {
                            $aicontent .= '.';
                        }
                    }
                }
                $aicontent = str_replace('%%first_content_paragraph_plain_text%%', aiomatic_extract_paragraph($post->post_content, false, 500), $aicontent);
                $aicontent = str_replace('%%last_content_paragraph_plain_text%%', aiomatic_extract_paragraph($post->post_content, true, 500), $aicontent);
                $aicontent = str_replace('%%first_content_paragraph%%', aiomatic_extract_text_chars($post->post_content, false, 500), $aicontent);
                $aicontent = str_replace('%%last_content_paragraph%%', aiomatic_extract_text_chars($post->post_content, true, 500), $aicontent);
                $aicontent = trim($aicontent);
                $query_token_count = count(aiomatic_encode($aicontent));
                if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                {
                    $aicontent = aiomatic_substr($aicontent, 0, (0-($max_seed_tokens * 4)));
                    $query_token_count = count(aiomatic_encode($aicontent));
                }
                $available_tokens = aiomatic_compute_available_tokens($completion_model, $max_tokens, $aicontent, $query_token_count);
                if($available_tokens > $max_result_tokens)
                {
                    $available_tokens = $max_result_tokens;
                }
                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                {
                    $string_len = strlen($aicontent);
                    $string_len = $string_len / 2;
                    $string_len = intval(0 - $string_len);
                    $aicontent = aiomatic_substr($aicontent, 0, $string_len);
                    $aicontent = trim($aicontent);
                    if(empty($aicontent))
                    {
                        aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($aicontent, true));
                        return;
                    }
                    $query_token_count = count(aiomatic_encode($aicontent));
                    $available_tokens = $max_tokens - $query_token_count;
                }
                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                {
                    $api_service = aiomatic_get_api_service($token, $completion_model);
                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $completion_model . ')(' . $available_tokens . ') Post Editor with seed command: ' . $aicontent);
                }
                $aierror = '';
                $aiwriter = '';
                $finish_reason = '';
                $generated_text = aiomatic_generate_text($token, $completion_model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'contentCompletion', 0, $finish_reason, $aierror, false, false, false, $vision_file, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                if($generated_text === false)
                {
                    aiomatic_log_to_file($aierror);
                    return;
                }
                else
                {
                    $aiwriter = ucfirst(trim(nl2br(trim($generated_text))));
                }
                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                {
                    $api_service = aiomatic_get_api_service($token, $completion_model);
                    aiomatic_log_to_file($api_service . ' responded successfully, ID: ' . $post->ID);
                }
                $ai_created_data = '';
                $prepp = ucfirst(trim(nl2br($aiwriter)));
                if($prepp != false && $prepp != '')
                {
                    $ai_created_data = $prepp;
                }
                $image_query = '';
                $heading_val = '';
                if(!empty($ai_created_data))
                {
                    if($headings != '' && is_numeric($headings))
                    {
                        $heading_results = aiomatic_scrape_related_questions($ai_created_data, $headings, $headings_model, $temperature, $top_p, $presence_penalty, $frequency_penalty, $max_tokens, $headings_ai_command, $headings_assistant_id);
                    }
                    $need_more = true;
                    if (isset($aiomatic_Spinner_Settings['min_char']) && $aiomatic_Spinner_Settings['min_char'] != '') 
                    {
                        $min_char = intval($aiomatic_Spinner_Settings['min_char']);
                        $cnt = 1;
                        $max_fails = 10;
                        $failed_calls = 0;
                        if (isset($aiomatic_Spinner_Settings['max_continue_tokens']) && $aiomatic_Spinner_Settings['max_continue_tokens'] != '')
                        {
                            $max_continue_tokens = intval($aiomatic_Spinner_Settings['max_continue_tokens']);
                        }
                        else
                        {
                            $max_continue_tokens = 1000;
                        }
                        $ai_retry = false;
                        $ai_continue_title = $post_title;
                        while(strlen(strip_tags($ai_created_data)) < $min_char)
                        {
                            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) < $cnt)
                            {
                                break;
                            }
                            $need_more = false;
                            $just_set_fallback = false;
                            $image_query = '';
                            $heading_val = '';
                            if(count($heading_results) > 0)
                            {
                                $rand_heading = '';
                                $saverand = array_rand($heading_results);
                                $rand_heading = $heading_results[$saverand];
                                unset($heading_results[$saverand]);
                                if(isset($rand_heading['q']))
                                {
                                    $rand_heading['q'] = preg_replace('#^\d+\.([\s\S]*)#i', '$1', $rand_heading['q']);
                                    $heading_val = '<h2>' . $rand_heading['q'] . '</h2>' . '<span>' . $rand_heading['a'];
                                    $image_query = $rand_heading['q'];
                                }
                            }
                            if($heading_val == '')
                            {
                                $temp_post = trim($ai_created_data);
                            }
                            else
                            {
                                $temp_post = trim($heading_val);
                            }
                            if(strlen($temp_post) > $max_continue_tokens * 4)
                            {
                                $negative_contiue_tokens = 0 - ($max_continue_tokens * 4);
                                $newaicontent = aiomatic_substr($temp_post, $negative_contiue_tokens, null);
                            }
                            else
                            {
                                $newaicontent = $temp_post;
                            }
                            $add_me_to_text = '';
                            if($ai_retry == true)
                            {
                                $just_set_fallback = true;
                                if (isset($aiomatic_Main_Settings['alternate_continue']) && $aiomatic_Main_Settings['alternate_continue'] == 'on')
                                {
                                    $newaicontent = $newaicontent . ' ' . $ai_continue_title;
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                    {
                                        $api_service = aiomatic_get_api_service($token, $completion_model);
                                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $assistant_id . '\\' . $completion_model . ')(' . aiomatic_get_max_tokens($completion_model) . ') PAA writer with seed command: ' . 'Write a People Also Asked question related to "' . $ai_continue_title . '"');
                                    }
                                    $aierror = '';
                                    $finish_reason = '';
                                    $generated_text = aiomatic_generate_text($token, $completion_model, 'Write a People Also Asked question related to "' . $ai_continue_title . '"', aiomatic_get_max_tokens($completion_model), $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'headingCompletion', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                                    if($generated_text === false)
                                    {
                                        aiomatic_log_to_file('Similarity finding failed: ' . $aierror);
                                        $newaicontent = $aicontent;
                                    }
                                    else
                                    {
                                        $newaicontent = ucfirst(trim(nl2br(trim($generated_text))));
                                        if(empty($newaicontent))
                                        {
                                            $newaicontent = $aicontent;
                                        }
                                        else
                                        {
                                            $newaicontent = preg_replace('#^\d+\.([\s\S]*)#i', '$1', $newaicontent);
                                            $add_me_to_text = '<h3>' . $newaicontent . '</h3> ';
                                            $ai_continue_title = $newaicontent;
                                        }
                                    }
                                }
                            }
                            $ai_retry = false;
                            $newaicontent = trim($newaicontent);
                            $query_token_count = count(aiomatic_encode($newaicontent));
                            $available_tokens = aiomatic_compute_available_tokens($completion_model, $max_tokens, $newaicontent, $query_token_count);
                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                            {
                                $string_len = strlen($newaicontent);
                                $string_len = $string_len / 2;
                                $string_len = intval(0 - $string_len);
                                $newaicontent = aiomatic_substr($newaicontent, 0, $string_len);
                                $newaicontent = trim($newaicontent);
                                if(empty($newaicontent))
                                {
                                    aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($temp_post, true));
                                    break;
                                }
                                $query_token_count = count(aiomatic_encode($newaicontent));
                                $available_tokens = $max_tokens - $query_token_count;
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                $api_service = aiomatic_get_api_service($token, $completion_model);
                                aiomatic_log_to_file('Calling ' . $api_service . ' again (' . $cnt . ')(' . $available_tokens . ') from text editor, to meet minimum character limit: ' . $min_char . ' - current char count: ' . strlen(strip_tags($ai_created_data)));
                            }
                            $aierror = '';
                            $aiwriter = '';
                            $finish_reason = '';
                            $generated_text = aiomatic_generate_text($token, $completion_model, $newaicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'contentCompletion', 0, $finish_reason, $aierror, false, false, false, $vision_file, '', 'user', $assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                            if($generated_text === false)
                            {
                                aiomatic_log_to_file($aierror);
                                break;
                            }
                            else
                            {
                                $aiwriter = $add_me_to_text . ucfirst(trim(nl2br(trim($generated_text))));
                            }
                            
                            if($aiwriter == '')
                            {
                                $ai_retry = true;
                                if($just_set_fallback == true)
                                {
                                    aiomatic_log_to_file('Ending execution, already retried once');
                                    break;
                                }
                                continue;
                            }
                            $add_my_image = '';

                            $temp_get_img = '';
                            $temp_get_video = '';
                            if($images != '' && is_numeric($images) && $images > $added_images)
                            {
                                if ($videos == 'on' && $rich_embed_location == 'inside')
                                {
                                    if(rand(1, 100) <= 50)
                                    {
                                        $temp_get_video = aiomatic_get_video(trim(stripslashes(str_replace('&quot;', '"', $post_title))), '', $social_list);
                                    }
                                }
                                if(empty($temp_get_video))
                                {
                                    $query_words = '';
                                    if($image_query == '')
                                    {
                                        $image_query = $temp_post;
                                    }
                                    if (isset($aiomatic_Spinner_Settings['enable_ai_images']) && ($aiomatic_Spinner_Settings['enable_ai_images'] == '1' || $aiomatic_Spinner_Settings['enable_ai_images'] == 'on')) 
                                    {
                                        if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                        {
                                            $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                        }
                                        else
                                        {
                                            $image_size = '1024x1024';
                                        }
                                        if (isset($aiomatic_Spinner_Settings['image_model']) && trim($aiomatic_Spinner_Settings['image_model']) != '')
                                        {
                                            $image_model = trim($aiomatic_Spinner_Settings['image_model']);
                                        }
                                        else
                                        {
                                            $image_model = 'dalle2';
                                        }
                                        $get_img = '';
                                        $query_words = $post_title;
                                        if($image_query == '')
                                        {
                                            $image_query = $temp_post;
                                        }
                                        $orig_ai_command_image = '';
                                        if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                        {
                                            $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                        }
                                        if($orig_ai_command_image == '')
                                        {
                                            $orig_ai_command_image = $image_query;
                                        }
                                        if($orig_ai_command_image != '')
                                        {
                                            $ai_command_image = $orig_ai_command_image;
                                            $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                            $ai_command_image = array_filter($ai_command_image);
                                            if(count($ai_command_image) > 0)
                                            {
                                                $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                            }
                                            else
                                            {
                                                $ai_command_image = '';
                                            }
                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                            if(!empty($ai_command_image))
                                            {
                                                $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                            }
                                            else
                                            {
                                                $ai_command_image = trim(strip_tags($post_title));
                                            }
                                            $ai_command_image = trim($ai_command_image);
                                            if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                            {
                                                $txt_content = aiomatic_get_web_page($ai_command_image);
                                                if ($txt_content !== FALSE) 
                                                {
                                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                    $txt_content = array_filter($txt_content);
                                                    if(count($txt_content) > 0)
                                                    {
                                                        $txt_content = $txt_content[array_rand($txt_content)];
                                                        if(trim($txt_content) != '') 
                                                        {
                                                            $ai_command_image = $txt_content;
                                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($ai_command_image))
                                            {
                                                aiomatic_log_to_file('Empty API image seed expression provided!');
                                            }
                                            else
                                            {
                                                if(strlen($ai_command_image) > 400)
                                                {
                                                    $ai_command_image = aiomatic_substr($ai_command_image, 0, 400);
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = aiomatic_get_api_service($token, $image_model);
                                                    aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                                }
                                                $aierror = '';
                                                $get_img = aiomatic_generate_ai_image($token, 1, $ai_command_image, $image_size, 'editContentImage', false, 0, $aierror, $image_model);
                                                if($get_img !== false)
                                                {
                                                    foreach($get_img as $tmpimg)
                                                    {
                                                        $added_images++;
                                                        $added_img_list[] = $tmpimg;
                                                        $temp_get_img = $tmpimg;
                                                    }
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        aiomatic_log_to_file('AI generated image returned: ' . $tmpimg);
                                                    }
                                                }
                                                else
                                                {
                                                    aiomatic_log_to_file('Failed to generate AI image: ' . $aierror);
                                                    $get_img = '';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Empty AI image query entered.');
                                        }
                                    }
                                    elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '2') 
                                    {
                                        if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                        {
                                            $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                        }
                                        else
                                        {
                                            $image_size = '1024x1024';
                                        }
                                        $get_img = '';
                                        $query_words = $post_title;
                                        if($image_query == '')
                                        {
                                            $image_query = $temp_post;
                                        }
                                        $orig_ai_command_image = '';
                                        if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                        {
                                            $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                        }
                                        if($orig_ai_command_image == '')
                                        {
                                            $orig_ai_command_image = $image_query;
                                        }
                                        if($orig_ai_command_image != '')
                                        {
                                            $ai_command_image = $orig_ai_command_image;
                                            $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                            $ai_command_image = array_filter($ai_command_image);
                                            if(count($ai_command_image) > 0)
                                            {
                                                $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                            }
                                            else
                                            {
                                                $ai_command_image = '';
                                            }
                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                            if(!empty($ai_command_image))
                                            {
                                                $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                            }
                                            else
                                            {
                                                $ai_command_image = trim(strip_tags($post_title));
                                            }
                                            $ai_command_image = trim($ai_command_image);
                                            if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                            {
                                                $txt_content = aiomatic_get_web_page($ai_command_image);
                                                if ($txt_content !== FALSE) 
                                                {
                                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                    $txt_content = array_filter($txt_content);
                                                    if(count($txt_content) > 0)
                                                    {
                                                        $txt_content = $txt_content[array_rand($txt_content)];
                                                        if(trim($txt_content) != '') 
                                                        {
                                                            $ai_command_image = $txt_content;
                                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($ai_command_image))
                                            {
                                                aiomatic_log_to_file('Empty API image seed expression provided!');
                                            }
                                            else
                                            {
                                                if(strlen($ai_command_image) > 2000)
                                                {
                                                    $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = 'Stability.AI';
                                                    aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                                }
                                                if($image_size == '256x256')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '512x512')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '1024x1024')
                                                {
                                                    $width = '1024';
                                                    $height = '1024';
                                                }
                                                else
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                $ierror = '';
                                                $temp_get_imgs = aiomatic_generate_stability_image($ai_command_image, $height, $width, 'editorContentStableImage', 0, false, $ierror, false, false, '', 0);
                                                if($temp_get_imgs !== false)
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs[1]);
                                                    }
                                                    $added_images++;
                                                    $added_img_list[] = $temp_get_imgs[1];
                                                    $temp_get_img = $temp_get_imgs[1];
                                                }
                                                else
                                                {
                                                    aiomatic_log_to_file('Failed to generate Stability.AI image: ' . $ierror);
                                                    $temp_get_img = '';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Empty AI image query entered.');
                                        }
                                    }
                                    elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '6') 
                                    {
                                        $get_img = '';
                                        $query_words = $post_title;
                                        if($image_query == '')
                                        {
                                            $image_query = $temp_post;
                                        }
                                        $orig_ai_command_image = '';
                                        if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                        {
                                            $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                        }
                                        if($orig_ai_command_image == '')
                                        {
                                            $orig_ai_command_image = $image_query;
                                        }
                                        if($orig_ai_command_image != '')
                                        {
                                            $ai_command_image = $orig_ai_command_image;
                                            $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                            $ai_command_image = array_filter($ai_command_image);
                                            if(count($ai_command_image) > 0)
                                            {
                                                $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                            }
                                            else
                                            {
                                                $ai_command_image = '';
                                            }
                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                            if(!empty($ai_command_image))
                                            {
                                                $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                            }
                                            else
                                            {
                                                $ai_command_image = trim(strip_tags($post_title));
                                            }
                                            $ai_command_image = trim($ai_command_image);
                                            if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                            {
                                                $txt_content = aiomatic_get_web_page($ai_command_image);
                                                if ($txt_content !== FALSE) 
                                                {
                                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                    $txt_content = array_filter($txt_content);
                                                    if(count($txt_content) > 0)
                                                    {
                                                        $txt_content = $txt_content[array_rand($txt_content)];
                                                        if(trim($txt_content) != '') 
                                                        {
                                                            $ai_command_image = $txt_content;
                                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($ai_command_image))
                                            {
                                                aiomatic_log_to_file('Empty API image seed expression provided!');
                                            }
                                            else
                                            {
                                                if(strlen($ai_command_image) > 2000)
                                                {
                                                    $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = 'Google';
                                                    aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                                }
                                                if (isset($aiomatic_Main_Settings['google_ai_image_model']) && trim($aiomatic_Main_Settings['google_ai_image_model']) != '') 
                                                {
                                                    $model = trim($aiomatic_Main_Settings['google_ai_image_model']);
                                                }
                                                else
                                                {
                                                    $model = AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL;
                                                }
                                                $ierror = '';
                                                
                                                $temp_get_imgs = aiomatic_generate_image_google($ai_command_image, $model, 'editorContentGoogleImage', false, $ierror, false);
                                                if($temp_get_imgs !== false)
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs[1]);
                                                    }
                                                    $added_images++;
                                                    $added_img_list[] = $temp_get_imgs[1];
                                                    $temp_get_img = $temp_get_imgs[1];
                                                }
                                                else
                                                {
                                                    aiomatic_log_to_file('Failed to generate Stability.AI image: ' . $ierror);
                                                    $temp_get_img = '';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Empty AI image query entered.');
                                        }
                                    }
                                    elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '3') 
                                    {
                                        if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                        {
                                            $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                        }
                                        else
                                        {
                                            $image_size = '1024x1024';
                                        }
                                        $get_img = '';
                                        $query_words = $post_title;
                                        if($image_query == '')
                                        {
                                            $image_query = $temp_post;
                                        }
                                        $orig_ai_command_image = '';
                                        if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                        {
                                            $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                        }
                                        if($orig_ai_command_image == '')
                                        {
                                            $orig_ai_command_image = $image_query;
                                        }
                                        if($orig_ai_command_image != '')
                                        {
                                            $ai_command_image = $orig_ai_command_image;
                                            $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                            $ai_command_image = array_filter($ai_command_image);
                                            if(count($ai_command_image) > 0)
                                            {
                                                $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                            }
                                            else
                                            {
                                                $ai_command_image = '';
                                            }
                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                            if(!empty($ai_command_image))
                                            {
                                                $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                            }
                                            else
                                            {
                                                $ai_command_image = trim(strip_tags($post_title));
                                            }
                                            $ai_command_image = trim($ai_command_image);
                                            if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                            {
                                                $txt_content = aiomatic_get_web_page($ai_command_image);
                                                if ($txt_content !== FALSE) 
                                                {
                                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                    $txt_content = array_filter($txt_content);
                                                    if(count($txt_content) > 0)
                                                    {
                                                        $txt_content = $txt_content[array_rand($txt_content)];
                                                        if(trim($txt_content) != '') 
                                                        {
                                                            $ai_command_image = $txt_content;
                                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($ai_command_image))
                                            {
                                                aiomatic_log_to_file('Empty API image seed expression provided!');
                                            }
                                            else
                                            {
                                                if(strlen($ai_command_image) > 2000)
                                                {
                                                    $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = 'GoAPI (Midjourney)';
                                                    aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                                }
                                                if($image_size == '256x256')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '512x512')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '1024x1024')
                                                {
                                                    $width = '1024';
                                                    $height = '1024';
                                                }
                                                elseif($image_size == '1792x1024')
                                                {
                                                    $width = '1792';
                                                    $height = '1024';
                                                }
                                                elseif($image_size == '1024x1792')
                                                {
                                                    $width = '1024';
                                                    $height = '1792';
                                                }
                                                else
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                $ierror = '';
                                                $temp_get_imgs = aiomatic_generate_ai_image_midjourney($ai_command_image, $width, $height, 'editorContentMidjourneyImage', false, $ierror);
                                                if($temp_get_imgs !== false)
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs);
                                                    }
                                                    $added_images++;
                                                    $added_img_list[] = $temp_get_imgs;
                                                    $temp_get_img = $temp_get_imgs;
                                                }
                                                else
                                                {
                                                    aiomatic_log_to_file('Failed to generate Midjourney image: ' . $ierror);
                                                    $temp_get_img = '';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Empty AI image query entered.');
                                        }
                                    }
                                    elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '4') 
                                    {
                                        if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                        {
                                            $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                        }
                                        else
                                        {
                                            $image_size = '1024x1024';
                                        }
                                        $get_img = '';
                                        $query_words = $post_title;
                                        if($image_query == '')
                                        {
                                            $image_query = $temp_post;
                                        }
                                        $orig_ai_command_image = '';
                                        if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                        {
                                            $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                        }
                                        if($orig_ai_command_image == '')
                                        {
                                            $orig_ai_command_image = $image_query;
                                        }
                                        if($orig_ai_command_image != '')
                                        {
                                            $ai_command_image = $orig_ai_command_image;
                                            $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                            $ai_command_image = array_filter($ai_command_image);
                                            if(count($ai_command_image) > 0)
                                            {
                                                $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                            }
                                            else
                                            {
                                                $ai_command_image = '';
                                            }
                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                            if(!empty($ai_command_image))
                                            {
                                                $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                            }
                                            else
                                            {
                                                $ai_command_image = trim(strip_tags($post_title));
                                            }
                                            $ai_command_image = trim($ai_command_image);
                                            if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                            {
                                                $txt_content = aiomatic_get_web_page($ai_command_image);
                                                if ($txt_content !== FALSE) 
                                                {
                                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                    $txt_content = array_filter($txt_content);
                                                    if(count($txt_content) > 0)
                                                    {
                                                        $txt_content = $txt_content[array_rand($txt_content)];
                                                        if(trim($txt_content) != '') 
                                                        {
                                                            $ai_command_image = $txt_content;
                                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($ai_command_image))
                                            {
                                                aiomatic_log_to_file('Empty API image seed expression provided!');
                                            }
                                            else
                                            {
                                                if(strlen($ai_command_image) > 2000)
                                                {
                                                    $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = 'Replicate';
                                                    aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                                }
                                                if($image_size == '256x256')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '512x512')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '1024x1024')
                                                {
                                                    $width = '1024';
                                                    $height = '1024';
                                                }
                                                elseif($image_size == '1792x1024')
                                                {
                                                    $width = '1792';
                                                    $height = '1024';
                                                }
                                                elseif($image_size == '1024x1792')
                                                {
                                                    $width = '1024';
                                                    $height = '1792';
                                                }
                                                else
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                $ierror = '';
                                                $temp_get_imgs = aiomatic_generate_replicate_image($ai_command_image, $width, $height, 'editorContentReplicateImage', false, $ierror);
                                                if($temp_get_imgs !== false)
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs);
                                                    }
                                                    $added_images++;
                                                    $added_img_list[] = $temp_get_imgs;
                                                    $temp_get_img = $temp_get_imgs;
                                                }
                                                else
                                                {
                                                    aiomatic_log_to_file('Failed to generate Replicate image: ' . $ierror);
                                                    $temp_get_img = '';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Empty AI image query entered.');
                                        }
                                    }
                                    elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '5') 
                                    {
                                        if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                        {
                                            $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                        }
                                        else
                                        {
                                            $image_size = '1024x1024';
                                        }
                                        $get_img = '';
                                        $query_words = $post_title;
                                        if($image_query == '')
                                        {
                                            $image_query = $temp_post;
                                        }
                                        $orig_ai_command_image = '';
                                        if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                        {
                                            $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                        }
                                        if($orig_ai_command_image == '')
                                        {
                                            $orig_ai_command_image = $image_query;
                                        }
                                        if($orig_ai_command_image != '')
                                        {
                                            $ai_command_image = $orig_ai_command_image;
                                            $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                            $ai_command_image = array_filter($ai_command_image);
                                            if(count($ai_command_image) > 0)
                                            {
                                                $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                            }
                                            else
                                            {
                                                $ai_command_image = '';
                                            }
                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                            if(!empty($ai_command_image))
                                            {
                                                $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                            }
                                            else
                                            {
                                                $ai_command_image = trim(strip_tags($post_title));
                                            }
                                            $ai_command_image = trim($ai_command_image);
                                            if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                            {
                                                $txt_content = aiomatic_get_web_page($ai_command_image);
                                                if ($txt_content !== FALSE) 
                                                {
                                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                    $txt_content = array_filter($txt_content);
                                                    if(count($txt_content) > 0)
                                                    {
                                                        $txt_content = $txt_content[array_rand($txt_content)];
                                                        if(trim($txt_content) != '') 
                                                        {
                                                            $ai_command_image = $txt_content;
                                                            $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($ai_command_image))
                                            {
                                                aiomatic_log_to_file('Empty API image seed expression provided!');
                                            }
                                            else
                                            {
                                                if(strlen($ai_command_image) > 2000)
                                                {
                                                    $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = 'Ideogram';
                                                    aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                                }
                                                if($image_size == '256x256')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '512x512')
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                elseif($image_size == '1024x1024')
                                                {
                                                    $width = '1024';
                                                    $height = '1024';
                                                }
                                                elseif($image_size == '1792x1024')
                                                {
                                                    $width = '1792';
                                                    $height = '1024';
                                                }
                                                elseif($image_size == '1024x1792')
                                                {
                                                    $width = '1024';
                                                    $height = '1792';
                                                }
                                                else
                                                {
                                                    $width = '512';
                                                    $height = '512';
                                                }
                                                $ierror = '';
                                                $temp_get_imgs = aiomatic_generate_ideogram_image($ai_command_image, $width, $height, 'editorContentIdeogramImage', false, $ierror);
                                                if($temp_get_imgs !== false)
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs);
                                                    }
                                                    $added_images++;
                                                    $added_img_list[] = $temp_get_imgs;
                                                    $temp_get_img = $temp_get_imgs;
                                                }
                                                else
                                                {
                                                    aiomatic_log_to_file('Failed to generate Ideogram image: ' . $ierror);
                                                    $temp_get_img = '';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Empty AI image query entered.');
                                        }
                                    }
                                    elseif (!isset($aiomatic_Spinner_Settings['enable_ai_images']) || $aiomatic_Spinner_Settings['enable_ai_images'] == '0') 
                                    {
                                        if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                        {
                                            $image_query = $aiomatic_Spinner_Settings['ai_image_command'];
                                        }
                                        if(isset($aiomatic_Main_Settings['improve_keywords']) && trim($aiomatic_Main_Settings['improve_keywords']) == 'textrazor')
                                        {
                                            if(isset($aiomatic_Main_Settings['textrazor_key']) && trim($aiomatic_Main_Settings['textrazor_key']) != '')
                                            {
                                                try
                                                {
                                                    if(!class_exists('TextRazor'))
                                                    {
                                                        require_once(dirname(__FILE__) . "/res/TextRazor.php");
                                                    }
                                                    TextRazorSettings::setApiKey(trim($aiomatic_Main_Settings['textrazor_key']));
                                                    $textrazor = new TextRazor();
                                                    $textrazor->addExtractor('entities');
                                                    $response = $textrazor->analyze($image_query);
                                                    if (isset($response['response']['entities'])) 
                                                    {
                                                        foreach ($response['response']['entities'] as $entity) 
                                                        {
                                                            $query_words = '';
                                                            if(isset($entity['entityEnglishId']))
                                                            {
                                                                $query_words = $entity['entityEnglishId'];
                                                            }
                                                            else
                                                            {
                                                                $query_words = $entity['entityId'];
                                                            }
                                                            if($query_words != '')
                                                            {
                                                                $attached_id = '';
                                                                $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                                                                if(!empty($z_img))
                                                                {
                                                                    $added_images++;
                                                                    $added_img_list[] = $z_img;
                                                                    $temp_get_img = $z_img;
                                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                                        aiomatic_log_to_file('Royalty Free Image Generated with help of TextRazor (kw: "' . $query_words . '"): ' . $z_img);
                                                                    }
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                catch(Exception $e)
                                                {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                        aiomatic_log_to_file('Failed to search for keywords using TextRazor (2): ' . $e->getMessage());
                                                    }
                                                }
                                            }
                                        }
                                        elseif(isset($aiomatic_Main_Settings['improve_keywords']) && trim($aiomatic_Main_Settings['improve_keywords']) == 'openai')
                                        {
                                            if(isset($aiomatic_Main_Settings['keyword_prompts']) && trim($aiomatic_Main_Settings['keyword_prompts']) != '')
                                            {
                                                if(isset($aiomatic_Main_Settings['keyword_model']) && $aiomatic_Main_Settings['keyword_model'] != '')
                                                {
                                                    $kw_model = $aiomatic_Main_Settings['keyword_model'];
                                                }
                                                else
                                                {
                                                    $kw_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                                                }
                                                if(isset($aiomatic_Main_Settings['keyword_assistant_id']) && $aiomatic_Main_Settings['keyword_assistant_id'] != '')
                                                {
                                                    $keyword_assistant_id = $aiomatic_Main_Settings['keyword_assistant_id'];
                                                }
                                                else
                                                {
                                                    $keyword_assistant_id = '';
                                                }
                                                $title_ai_command = trim($aiomatic_Main_Settings['keyword_prompts']);
                                                $title_ai_command = str_replace('%%default_post_cats%%', '', $title_ai_command);
                                                $title_ai_command = str_replace('%%original_post_title%%', $post_title, $title_ai_command);
                                                $title_ai_command = preg_split('/\r\n|\r|\n/', $title_ai_command);
                                                $title_ai_command = array_filter($title_ai_command);
                                                if(count($title_ai_command) > 0)
                                                {
                                                    $title_ai_command = $title_ai_command[array_rand($title_ai_command)];
                                                }
                                                else
                                                {
                                                    $title_ai_command = '';
                                                }
                                                $title_ai_command = aiomatic_replaceSynergyShortcodes($title_ai_command);
                                                if(!empty($title_ai_command))
                                                {
                                                    $title_ai_command = aiomatic_replaceAIPostShortcodes($title_ai_command, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                }
                                                $title_ai_command = trim($title_ai_command);
                                                if (filter_var($title_ai_command, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($title_ai_command, '.txt'))
                                                {
                                                    $txt_content = aiomatic_get_web_page($title_ai_command);
                                                    if ($txt_content !== FALSE) 
                                                    {
                                                        $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                        $txt_content = array_filter($txt_content);
                                                        if(count($txt_content) > 0)
                                                        {
                                                            $txt_content = $txt_content[array_rand($txt_content)];
                                                            if(trim($txt_content) != '') 
                                                            {
                                                                $title_ai_command = $txt_content;
                                                                $title_ai_command = aiomatic_replaceSynergyShortcodes($title_ai_command);
                                                                $title_ai_command = aiomatic_replaceAIPostShortcodes($title_ai_command, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                            }
                                                        }
                                                    }
                                                }
                                                if(empty($title_ai_command))
                                                {
                                                    aiomatic_log_to_file('Empty API keyword extractor seed expression provided!');
                                                    $title_ai_command = 'Extract a comma separated list of relevant keywords from the text: ' . trim(strip_tags($post_title));
                                                }
                                                if(!empty($max_seed_tokens) && strlen($title_ai_command) > $max_seed_tokens * 4)
                                                {
                                                    $title_ai_command = aiomatic_substr($title_ai_command, 0, (0 - ($max_seed_tokens * 4)));
                                                }
                                                $title_ai_command = trim($title_ai_command);
                                                if(empty($title_ai_command))
                                                {
                                                    aiomatic_log_to_file('Empty API title seed expression provided(6)! ' . print_r($title_ai_command, true));
                                                }
                                                else
                                                {
                                                    $query_token_count = count(aiomatic_encode($title_ai_command));
                                                    $max_tokens = aiomatic_get_max_tokens($kw_model);
                                                    $available_tokens = aiomatic_compute_available_tokens($kw_model, $max_tokens, $title_ai_command, $query_token_count);
                                                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                                    {
                                                        $string_len = strlen($title_ai_command);
                                                        $string_len = $string_len / 2;
                                                        $string_len = intval(0 - $string_len);
                                                        $title_ai_command = aiomatic_substr($title_ai_command, 0, $string_len);
                                                        $title_ai_command = trim($title_ai_command);
                                                        $query_token_count = count(aiomatic_encode($title_ai_command));
                                                        $available_tokens = $max_tokens - $query_token_count;
                                                    }
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        $api_service = aiomatic_get_api_service($token, $kw_model);
                                                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $keyword_assistant_id . '\\' . $kw_model . ')(' . $available_tokens . ') for title text1: ' . $title_ai_command);
                                                    }
                                                    $aierror = '';
                                                    $finish_reason = '';
                                                    $generated_text = aiomatic_generate_text($token, $kw_model, $title_ai_command, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'keywordCompletion', 0, $finish_reason, $aierror, true, false, false, '', '', 'user', $keyword_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                                                    if($generated_text === false)
                                                    {
                                                        aiomatic_log_to_file('Keyword generator error: ' . $aierror);
                                                        $ai_title = '';
                                                    }
                                                    else
                                                    {
                                                        $ai_title = trim(trim(trim(trim($generated_text), '.'), ' "\''));
                                                        $ai_titles = explode(',', $ai_title);
                                                        foreach($ai_titles as $query_words)
                                                        {
                                                            $attached_id = '';
                                                            $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, trim($query_words), $img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                                                            if(!empty($z_img))
                                                            {
                                                                $added_images++;
                                                                $added_img_list[] = $z_img;
                                                                $temp_get_img = $z_img;
                                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                                    aiomatic_log_to_file('Royalty Free Image Generated with help of AI (kw: "' . $query_words . '"): ' . $z_img);
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                    {
                                                        $api_service = aiomatic_get_api_service($token, $kw_model);
                                                        aiomatic_log_to_file('Successfully got API keyword result from ' . $api_service . ': ' . $ai_title);
                                                    }
                                                }
                                            }
                                        }
                                        if(empty($temp_get_img))
                                        {
                                            $keyword_class = new Aiomatic_keywords();
                                            $query_words = $keyword_class->keywords($image_query, 2);
                                            $temp_img_attr = '';
                                            $attached_id = '';
                                            $temp_get_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $temp_img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                                            if($temp_get_img == '' || $temp_get_img === false)
                                            {
                                                $query_words = $keyword_class->keywords($image_query, 1);
                                                $attached_id = '';
                                                $temp_get_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $temp_img_attr, $attached_id, 20, false, $raw_img_list, array(), $full_result_list);
                                                if($temp_get_img == '' || $temp_get_img === false)
                                                {
                                                    $temp_get_img = '';
                                                }
                                                else
                                                {
                                                    if(!in_array($temp_get_img, $added_img_list))
                                                    {
                                                        $added_images++;
                                                        $added_img_list[] = $temp_get_img;
                                                    }
                                                    else
                                                    {
                                                        $temp_get_img = '';
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                if(!in_array($temp_get_img, $added_img_list))
                                                {
                                                    $added_images++;
                                                    $added_img_list[] = $temp_get_img;
                                                }
                                                else
                                                {
                                                    $temp_get_img = '';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if($temp_get_video != '')
                            {
                                $add_my_image = $temp_get_video . '<br/>';
                            }
                            elseif($temp_get_img != '')
                            {
                                if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled')
                                {
                                    $attached_id = '';
                                    $localpath = aiomatic_copy_image_locally($temp_get_img, $attached_id, '', false, '');
                                    if($localpath !== false)
                                    {
                                        $temp_get_img = $localpath[0];
                                    }
                                }
                                $add_my_image = '<img class="ximage_class" src="' . $temp_get_img . '" alt="' . $query_words . '"><br/>';
                            }
                            if($heading_val == '')
                            {
                                if($add_my_image == '')
                                {
                                    $add_my_image = ' ';
                                }
                                $ai_created_data .= $add_my_image . trim(nl2br($aiwriter));
                            }
                            else
                            {
                                $ai_created_data .= $add_my_image . $heading_val . ' ' . trim(nl2br($aiwriter)) . '</span>';
                            }
                            sleep(1);
                            $cnt++;
                        }
                    }
                    if($need_more === true)
                    {
                        $add_my_image = '';
                        $temp_get_img = '';
                        $temp_get_video = '';
                        if(count($heading_results) > 0)
                        {
                            $rand_heading = '';
                            $saverand = array_rand($heading_results);
                            $rand_heading = $heading_results[$saverand];
                            unset($heading_results[$saverand]);
                            if(isset($rand_heading['q']))
                            {
                                $rand_heading['q'] = preg_replace('#^\d+\.([\s\S]*)#i', '$1', $rand_heading['q']);
                                $heading_val = '<h2>' . $rand_heading['q'] . '</h2>' . '<span>' . $rand_heading['a'];
                                $image_query = $rand_heading['q'];
                            }
                        }
                        if($images != '' && is_numeric($images) && $images > $added_images)
                        {
                            if ($videos == 'on' && $rich_embed_location == 'inside')
                            {
                                if(rand(1, 100) <= 50)
                                {
                                    $temp_get_video = aiomatic_get_video(trim(stripslashes(str_replace('&quot;', '"', $post_title))), '', $social_list);
                                }
                            }
                            if(empty($temp_get_video))
                            {
                                if($heading_val == '')
                                {
                                    $temp_post = trim($ai_created_data);
                                }
                                else
                                {
                                    $temp_post = trim($heading_val);
                                }
                                $query_words = '';
                                if($image_query == '')
                                {
                                    $image_query = $temp_post;
                                }
                                if (isset($aiomatic_Spinner_Settings['enable_ai_images']) && ($aiomatic_Spinner_Settings['enable_ai_images'] == '1' || $aiomatic_Spinner_Settings['enable_ai_images'] == 'on')) 
                                {
                                    if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                    {
                                        $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                    }
                                    else
                                    {
                                        $image_size = '1024x1024';
                                    }
                                    if (isset($aiomatic_Spinner_Settings['image_model']) && trim($aiomatic_Spinner_Settings['image_model']) != '')
                                    {
                                        $image_model = trim($aiomatic_Spinner_Settings['image_model']);
                                    }
                                    else
                                    {
                                        $image_model = 'dalle2';
                                    }
                                    $get_img = '';
                                    $query_words = $post_title;
                                    $orig_ai_command_image = '';
                                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                    {
                                        $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                    }
                                    if($orig_ai_command_image == '')
                                    {
                                        $orig_ai_command_image = $image_query;
                                    }
                                    if($orig_ai_command_image != '')
                                    {
                                        $ai_command_image = $orig_ai_command_image;
                                        $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                        $ai_command_image = array_filter($ai_command_image);
                                        if(count($ai_command_image) > 0)
                                        {
                                            $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                        }
                                        else
                                        {
                                            $ai_command_image = '';
                                        }
                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                        if(!empty($ai_command_image))
                                        {
                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        }
                                        else
                                        {
                                            $ai_command_image = trim(strip_tags($post_title));
                                        }
                                        $ai_command_image = trim($ai_command_image);
                                        if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                        {
                                            $txt_content = aiomatic_get_web_page($ai_command_image);
                                            if ($txt_content !== FALSE) 
                                            {
                                                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                $txt_content = array_filter($txt_content);
                                                if(count($txt_content) > 0)
                                                {
                                                    $txt_content = $txt_content[array_rand($txt_content)];
                                                    if(trim($txt_content) != '') 
                                                    {
                                                        $ai_command_image = $txt_content;
                                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                        $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                    }
                                                }
                                            }
                                        }
                                        if(empty($ai_command_image))
                                        {
                                            aiomatic_log_to_file('Empty API image seed expression provided!');
                                        }
                                        else
                                        {
                                            if(strlen($ai_command_image) > 400)
                                            {
                                                $ai_command_image = aiomatic_substr($ai_command_image, 0, 400);
                                            }
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                            {
                                                $api_service = aiomatic_get_api_service($token, $image_model);
                                                aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                            }
                                            $aierror = '';
                                            $get_img = aiomatic_generate_ai_image($token, 1, $ai_command_image, $image_size, 'editContentImage', false, 0, $aierror, $image_model);
                                            if($get_img !== false)
                                            {
                                                foreach($get_img as $tmpimg)
                                                {
                                                    $added_images++;
                                                    $added_img_list[] = $tmpimg;
                                                    $temp_get_img = $tmpimg;
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    aiomatic_log_to_file('AI generated image returned: ' . $tmpimg);
                                                }
                                            }
                                            else
                                            {
                                                aiomatic_log_to_file('Failed to generate AI image: ' . $aierror);
                                                $get_img = '';
                                            }
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Empty AI image query entered.');
                                    }
                                }
                                elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '2') 
                                {
                                    if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                    {
                                        $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                    }
                                    else
                                    {
                                        $image_size = '1024x1024';
                                    }
                                    $get_img = '';
                                    $query_words = $post_title;
                                    if($image_query == '')
                                    {
                                        $image_query = $temp_post;
                                    }
                                    $orig_ai_command_image = '';
                                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                    {
                                        $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                    }
                                    if($orig_ai_command_image == '')
                                    {
                                        $orig_ai_command_image = $image_query;
                                    }
                                    if($orig_ai_command_image != '')
                                    {
                                        $ai_command_image = $orig_ai_command_image;
                                        $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                        $ai_command_image = array_filter($ai_command_image);
                                        if(count($ai_command_image) > 0)
                                        {
                                            $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                        }
                                        else
                                        {
                                            $ai_command_image = '';
                                        }
                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                        if(!empty($ai_command_image))
                                        {
                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        }
                                        else
                                        {
                                            $ai_command_image = trim(strip_tags($post_title));
                                        }
                                        $ai_command_image = trim($ai_command_image);
                                        if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                        {
                                            $txt_content = aiomatic_get_web_page($ai_command_image);
                                            if ($txt_content !== FALSE) 
                                            {
                                                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                $txt_content = array_filter($txt_content);
                                                if(count($txt_content) > 0)
                                                {
                                                    $txt_content = $txt_content[array_rand($txt_content)];
                                                    if(trim($txt_content) != '') 
                                                    {
                                                        $ai_command_image = $txt_content;
                                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                        $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                    }
                                                }
                                            }
                                        }
                                        if(empty($ai_command_image))
                                        {
                                            aiomatic_log_to_file('Empty API image seed expression provided!');
                                        }
                                        else
                                        {
                                            if(strlen($ai_command_image) > 2000)
                                            {
                                                $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                            }
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                            {
                                                $api_service = 'Stability.AI';
                                                aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                            }
                                            if($image_size == '256x256')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '512x512')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '1024x1024')
                                            {
                                                $width = '1024';
                                                $height = '1024';
                                            }
                                            else
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            $ierror = '';
                                            $temp_get_imgs = aiomatic_generate_stability_image($ai_command_image, $height, $width, 'editorContentStableImage', 0, false, $ierror, false, false, '', 0);
                                            if($temp_get_imgs !== false)
                                            {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs[1]);
                                                }
                                                $added_images++;
                                                $added_img_list[] = $temp_get_imgs[1];
                                                $temp_get_img = $temp_get_imgs[1];
                                            }
                                            else
                                            {
                                                aiomatic_log_to_file('Failed to generate Stability.AI image: ' . $ierror);
                                                $temp_get_img = '';
                                            }
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Empty AI image query entered.');
                                    }
                                }
                                elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '3') 
                                {
                                    if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                    {
                                        $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                    }
                                    else
                                    {
                                        $image_size = '1024x1024';
                                    }
                                    $get_img = '';
                                    $query_words = $post_title;
                                    if($image_query == '')
                                    {
                                        $image_query = $temp_post;
                                    }
                                    $orig_ai_command_image = '';
                                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                    {
                                        $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                    }
                                    if($orig_ai_command_image == '')
                                    {
                                        $orig_ai_command_image = $image_query;
                                    }
                                    if($orig_ai_command_image != '')
                                    {
                                        $ai_command_image = $orig_ai_command_image;
                                        $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                        $ai_command_image = array_filter($ai_command_image);
                                        if(count($ai_command_image) > 0)
                                        {
                                            $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                        }
                                        else
                                        {
                                            $ai_command_image = '';
                                        }
                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                        if(!empty($ai_command_image))
                                        {
                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        }
                                        else
                                        {
                                            $ai_command_image = trim(strip_tags($post_title));
                                        }
                                        $ai_command_image = trim($ai_command_image);
                                        if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                        {
                                            $txt_content = aiomatic_get_web_page($ai_command_image);
                                            if ($txt_content !== FALSE) 
                                            {
                                                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                $txt_content = array_filter($txt_content);
                                                if(count($txt_content) > 0)
                                                {
                                                    $txt_content = $txt_content[array_rand($txt_content)];
                                                    if(trim($txt_content) != '') 
                                                    {
                                                        $ai_command_image = $txt_content;
                                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                        $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                    }
                                                }
                                            }
                                        }
                                        if(empty($ai_command_image))
                                        {
                                            aiomatic_log_to_file('Empty API image seed expression provided!');
                                        }
                                        else
                                        {
                                            if(strlen($ai_command_image) > 2000)
                                            {
                                                $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                            }
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                            {
                                                $api_service = 'GoAPI (Midjourney)';
                                                aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                            }
                                            if($image_size == '256x256')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '512x512')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '1024x1024')
                                            {
                                                $width = '1024';
                                                $height = '1024';
                                            }
                                            elseif($image_size == '1792x1024')
                                            {
                                                $width = '1792';
                                                $height = '1024';
                                            }
                                            elseif($image_size == '1024x1792')
                                            {
                                                $width = '1024';
                                                $height = '1792';
                                            }
                                            else
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            $ierror = '';
                                            $temp_get_imgs = aiomatic_generate_ai_image_midjourney($ai_command_image, $width, $height, 'editorContentMidjourneyImage', false, $ierror);
                                            if($temp_get_imgs !== false)
                                            {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs);
                                                }
                                                $added_images++;
                                                $added_img_list[] = $temp_get_imgs;
                                                $temp_get_img = $temp_get_imgs;
                                            }
                                            else
                                            {
                                                aiomatic_log_to_file('Failed to generate Midjourney image: ' . $ierror);
                                                $temp_get_img = '';
                                            }
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Empty AI image query entered.');
                                    }
                                }
                                elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '6') 
                                {
                                    $get_img = '';
                                    $query_words = $post_title;
                                    if($image_query == '')
                                    {
                                        $image_query = $temp_post;
                                    }
                                    $orig_ai_command_image = '';
                                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                    {
                                        $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                    }
                                    if($orig_ai_command_image == '')
                                    {
                                        $orig_ai_command_image = $image_query;
                                    }
                                    if($orig_ai_command_image != '')
                                    {
                                        $ai_command_image = $orig_ai_command_image;
                                        $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                        $ai_command_image = array_filter($ai_command_image);
                                        if(count($ai_command_image) > 0)
                                        {
                                            $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                        }
                                        else
                                        {
                                            $ai_command_image = '';
                                        }
                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                        if(!empty($ai_command_image))
                                        {
                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        }
                                        else
                                        {
                                            $ai_command_image = trim(strip_tags($post_title));
                                        }
                                        $ai_command_image = trim($ai_command_image);
                                        if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                        {
                                            $txt_content = aiomatic_get_web_page($ai_command_image);
                                            if ($txt_content !== FALSE) 
                                            {
                                                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                $txt_content = array_filter($txt_content);
                                                if(count($txt_content) > 0)
                                                {
                                                    $txt_content = $txt_content[array_rand($txt_content)];
                                                    if(trim($txt_content) != '') 
                                                    {
                                                        $ai_command_image = $txt_content;
                                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                        $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                    }
                                                }
                                            }
                                        }
                                        if(empty($ai_command_image))
                                        {
                                            aiomatic_log_to_file('Empty API image seed expression provided!');
                                        }
                                        else
                                        {
                                            if(strlen($ai_command_image) > 2000)
                                            {
                                                $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                            }
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                            {
                                                $api_service = 'Google';
                                                aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                            }
                                            if (isset($aiomatic_Main_Settings['google_ai_image_model']) && trim($aiomatic_Main_Settings['google_ai_image_model']) != '') 
                                            {
                                                $model = trim($aiomatic_Main_Settings['google_ai_image_model']);
                                            }
                                            else
                                            {
                                                $model = AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL;
                                            }
                                            $ierror = '';
                                            
                                            $temp_get_imgs = aiomatic_generate_image_google($ai_command_image, $model, 'editorContentGoogleImage', false, $ierror, false);
                                            if($temp_get_imgs !== false)
                                            {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs[1]);
                                                }
                                                $added_images++;
                                                $added_img_list[] = $temp_get_imgs[1];
                                                $temp_get_img = $temp_get_imgs[1];
                                            }
                                            else
                                            {
                                                aiomatic_log_to_file('Failed to generate Stability.AI image: ' . $ierror);
                                                $temp_get_img = '';
                                            }
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Empty AI image query entered.');
                                    }
                                }
                                elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '4') 
                                {
                                    if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                    {
                                        $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                    }
                                    else
                                    {
                                        $image_size = '1024x1024';
                                    }
                                    $get_img = '';
                                    $query_words = $post_title;
                                    if($image_query == '')
                                    {
                                        $image_query = $temp_post;
                                    }
                                    $orig_ai_command_image = '';
                                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                    {
                                        $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                    }
                                    if($orig_ai_command_image == '')
                                    {
                                        $orig_ai_command_image = $image_query;
                                    }
                                    if($orig_ai_command_image != '')
                                    {
                                        $ai_command_image = $orig_ai_command_image;
                                        $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                        $ai_command_image = array_filter($ai_command_image);
                                        if(count($ai_command_image) > 0)
                                        {
                                            $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                        }
                                        else
                                        {
                                            $ai_command_image = '';
                                        }
                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                        if(!empty($ai_command_image))
                                        {
                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        }
                                        else
                                        {
                                            $ai_command_image = trim(strip_tags($post_title));
                                        }
                                        $ai_command_image = trim($ai_command_image);
                                        if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                        {
                                            $txt_content = aiomatic_get_web_page($ai_command_image);
                                            if ($txt_content !== FALSE) 
                                            {
                                                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                $txt_content = array_filter($txt_content);
                                                if(count($txt_content) > 0)
                                                {
                                                    $txt_content = $txt_content[array_rand($txt_content)];
                                                    if(trim($txt_content) != '') 
                                                    {
                                                        $ai_command_image = $txt_content;
                                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                        $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                    }
                                                }
                                            }
                                        }
                                        if(empty($ai_command_image))
                                        {
                                            aiomatic_log_to_file('Empty API image seed expression provided!');
                                        }
                                        else
                                        {
                                            if(strlen($ai_command_image) > 2000)
                                            {
                                                $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                            }
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                            {
                                                $api_service = 'Replicate';
                                                aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                            }
                                            if($image_size == '256x256')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '512x512')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '1024x1024')
                                            {
                                                $width = '1024';
                                                $height = '1024';
                                            }
                                            elseif($image_size == '1792x1024')
                                            {
                                                $width = '1792';
                                                $height = '1024';
                                            }
                                            elseif($image_size == '1024x1792')
                                            {
                                                $width = '1024';
                                                $height = '1792';
                                            }
                                            else
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            $ierror = '';
                                            $temp_get_imgs = aiomatic_generate_replicate_image($ai_command_image, $width, $height, 'editorContentReplicateImage', false, $ierror);
                                            if($temp_get_imgs !== false)
                                            {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs);
                                                }
                                                $added_images++;
                                                $added_img_list[] = $temp_get_imgs;
                                                $temp_get_img = $temp_get_imgs;
                                            }
                                            else
                                            {
                                                aiomatic_log_to_file('Failed to generate Replicate image: ' . $ierror);
                                                $temp_get_img = '';
                                            }
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Empty AI image query entered.');
                                    }
                                }
                                elseif (isset($aiomatic_Spinner_Settings['enable_ai_images']) && $aiomatic_Spinner_Settings['enable_ai_images'] == '5') 
                                {
                                    if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                    {
                                        $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                    }
                                    else
                                    {
                                        $image_size = '1024x1024';
                                    }
                                    $get_img = '';
                                    $query_words = $post_title;
                                    if($image_query == '')
                                    {
                                        $image_query = $temp_post;
                                    }
                                    $orig_ai_command_image = '';
                                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                    {
                                        $orig_ai_command_image = $aiomatic_Spinner_Settings['ai_image_command'];
                                    }
                                    if($orig_ai_command_image == '')
                                    {
                                        $orig_ai_command_image = $image_query;
                                    }
                                    if($orig_ai_command_image != '')
                                    {
                                        $ai_command_image = $orig_ai_command_image;
                                        $ai_command_image = preg_split('/\r\n|\r|\n/', $ai_command_image);
                                        $ai_command_image = array_filter($ai_command_image);
                                        if(count($ai_command_image) > 0)
                                        {
                                            $ai_command_image = $ai_command_image[array_rand($ai_command_image)];
                                        }
                                        else
                                        {
                                            $ai_command_image = '';
                                        }
                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                        if(!empty($ai_command_image))
                                        {
                                            $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        }
                                        else
                                        {
                                            $ai_command_image = trim(strip_tags($post_title));
                                        }
                                        $ai_command_image = trim($ai_command_image);
                                        if (filter_var($ai_command_image, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_command_image, '.txt'))
                                        {
                                            $txt_content = aiomatic_get_web_page($ai_command_image);
                                            if ($txt_content !== FALSE) 
                                            {
                                                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                $txt_content = array_filter($txt_content);
                                                if(count($txt_content) > 0)
                                                {
                                                    $txt_content = $txt_content[array_rand($txt_content)];
                                                    if(trim($txt_content) != '') 
                                                    {
                                                        $ai_command_image = $txt_content;
                                                        $ai_command_image = aiomatic_replaceSynergyShortcodes($ai_command_image);
                                                        $ai_command_image = aiomatic_replaceAIPostShortcodes($ai_command_image, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                    }
                                                }
                                            }
                                        }
                                        if(empty($ai_command_image))
                                        {
                                            aiomatic_log_to_file('Empty API image seed expression provided!');
                                        }
                                        else
                                        {
                                            if(strlen($ai_command_image) > 2000)
                                            {
                                                $ai_command_image = aiomatic_substr($ai_command_image, 0, 2000);
                                            }
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                            {
                                                $api_service = 'Ideogram';
                                                aiomatic_log_to_file('Calling ' . $api_service . ' for image: ' . $ai_command_image);
                                            }
                                            if($image_size == '256x256')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '512x512')
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            elseif($image_size == '1024x1024')
                                            {
                                                $width = '1024';
                                                $height = '1024';
                                            }
                                            elseif($image_size == '1792x1024')
                                            {
                                                $width = '1792';
                                                $height = '1024';
                                            }
                                            elseif($image_size == '1024x1792')
                                            {
                                                $width = '1024';
                                                $height = '1792';
                                            }
                                            else
                                            {
                                                $width = '512';
                                                $height = '512';
                                            }
                                            $ierror = '';
                                            $temp_get_imgs = aiomatic_generate_ideogram_image($ai_command_image, $width, $height, 'editorContentIdeogramImage', false, $ierror);
                                            if($temp_get_imgs !== false)
                                            {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    aiomatic_log_to_file('AI generated image returned: ' . $temp_get_imgs);
                                                }
                                                $added_images++;
                                                $added_img_list[] = $temp_get_imgs;
                                                $temp_get_img = $temp_get_imgs;
                                            }
                                            else
                                            {
                                                aiomatic_log_to_file('Failed to generate Ideogram image: ' . $ierror);
                                                $temp_get_img = '';
                                            }
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Empty AI image query entered.');
                                    }
                                }
                                elseif (!isset($aiomatic_Spinner_Settings['enable_ai_images']) || $aiomatic_Spinner_Settings['enable_ai_images'] == '0') 
                                {
                                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                                    {
                                        $image_query = $aiomatic_Spinner_Settings['ai_image_command'];
                                    }
                                    if(isset($aiomatic_Main_Settings['improve_keywords']) && trim($aiomatic_Main_Settings['improve_keywords']) == 'textrazor')
                                    {
                                        if(isset($aiomatic_Main_Settings['textrazor_key']) && trim($aiomatic_Main_Settings['textrazor_key']) != '')
                                        {
                                            try
                                            {
                                                if(!class_exists('TextRazor'))
                                                {
                                                    require_once(dirname(__FILE__) . "/res/TextRazor.php");
                                                }
                                                TextRazorSettings::setApiKey(trim($aiomatic_Main_Settings['textrazor_key']));
                                                $textrazor = new TextRazor();
                                                $textrazor->addExtractor('entities');
                                                $response = $textrazor->analyze($image_query);
                                                if (isset($response['response']['entities'])) 
                                                {
                                                    foreach ($response['response']['entities'] as $entity) 
                                                    {
                                                        $query_words = '';
                                                        if(isset($entity['entityEnglishId']))
                                                        {
                                                            $query_words = $entity['entityEnglishId'];
                                                        }
                                                        else
                                                        {
                                                            $query_words = $entity['entityId'];
                                                        }
                                                        if($query_words != '')
                                                        {
                                                            $attached_id = '';
                                                            $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                                                            if(!empty($z_img))
                                                            {
                                                                $added_images++;
                                                                $added_img_list[] = $z_img;
                                                                $temp_get_img = $z_img;
                                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                                    aiomatic_log_to_file('Royalty Free Image Generated with help of TextRazor (kw: "' . $query_words . '"): ' . $z_img);
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            catch(Exception $e)
                                            {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                    aiomatic_log_to_file('Failed to search for keywords using TextRazor (2): ' . $e->getMessage());
                                                }
                                            }
                                        }
                                    }
                                    elseif(isset($aiomatic_Main_Settings['improve_keywords']) && trim($aiomatic_Main_Settings['improve_keywords']) == 'openai')
                                    {
                                        if(isset($aiomatic_Main_Settings['keyword_prompts']) && trim($aiomatic_Main_Settings['keyword_prompts']) != '')
                                        {
                                            if(isset($aiomatic_Main_Settings['keyword_model']) && $aiomatic_Main_Settings['keyword_model'] != '')
                                            {
                                                $kw_model = $aiomatic_Main_Settings['keyword_model'];
                                            }
                                            else
                                            {
                                                $kw_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                                            }
                                            if(isset($aiomatic_Main_Settings['keyword_assistant_id']) && $aiomatic_Main_Settings['keyword_assistant_id'] != '')
                                            {
                                                $keyword_assistant_id = $aiomatic_Main_Settings['keyword_assistant_id'];
                                            }
                                            else
                                            {
                                                $keyword_assistant_id = '';
                                            }
                                            $title_ai_command = trim($aiomatic_Main_Settings['keyword_prompts']);
                                            $title_ai_command = str_replace('%%default_post_cats%%', '', $title_ai_command);
                                            $title_ai_command = str_replace('%%original_post_title%%', $post_title, $title_ai_command);
                                            $title_ai_command = preg_split('/\r\n|\r|\n/', $title_ai_command);
                                            $title_ai_command = array_filter($title_ai_command);
                                            if(count($title_ai_command) > 0)
                                            {
                                                $title_ai_command = $title_ai_command[array_rand($title_ai_command)];
                                            }
                                            else
                                            {
                                                $title_ai_command = '';
                                            }
                                            $title_ai_command = aiomatic_replaceSynergyShortcodes($title_ai_command);
                                            if(!empty($title_ai_command))
                                            {
                                                $title_ai_command = aiomatic_replaceAIPostShortcodes($title_ai_command, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                            }
                                            $title_ai_command = trim($title_ai_command);
                                            if (filter_var($title_ai_command, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($title_ai_command, '.txt'))
                                            {
                                                $txt_content = aiomatic_get_web_page($title_ai_command);
                                                if ($txt_content !== FALSE) 
                                                {
                                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                                    $txt_content = array_filter($txt_content);
                                                    if(count($txt_content) > 0)
                                                    {
                                                        $txt_content = $txt_content[array_rand($txt_content)];
                                                        if(trim($txt_content) != '') 
                                                        {
                                                            $title_ai_command = $txt_content;
                                                            $title_ai_command = aiomatic_replaceSynergyShortcodes($title_ai_command);
                                                            $title_ai_command = aiomatic_replaceAIPostShortcodes($title_ai_command, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                        }
                                                    }
                                                }
                                            }
                                            if(empty($title_ai_command))
                                            {
                                                aiomatic_log_to_file('Empty API keyword extractor seed expression provided!');
                                                $title_ai_command = 'Extract a comma separated list of relevant keywords from the text: ' . trim(strip_tags($post_title));
                                            }
                                            if(!empty($max_seed_tokens) && strlen($title_ai_command) > $max_seed_tokens * 4)
                                            {
                                                $title_ai_command = aiomatic_substr($title_ai_command, 0, (0 - ($max_seed_tokens * 4)));
                                            }
                                            $title_ai_command = trim($title_ai_command);
                                            if(empty($title_ai_command))
                                            {
                                                aiomatic_log_to_file('Empty API title seed expression provided(7)! ' . print_r($title_ai_command, true));
                                            }
                                            else
                                            {
                                                $query_token_count = count(aiomatic_encode($title_ai_command));
                                                $max_tokens = aiomatic_get_max_tokens($kw_model);
                                                $available_tokens = aiomatic_compute_available_tokens($kw_model, $max_tokens, $title_ai_command, $query_token_count);
                                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                                {
                                                    $string_len = strlen($title_ai_command);
                                                    $string_len = $string_len / 2;
                                                    $string_len = intval(0 - $string_len);
                                                    $title_ai_command = aiomatic_substr($title_ai_command, 0, $string_len);
                                                    $title_ai_command = trim($title_ai_command);
                                                    $query_token_count = count(aiomatic_encode($title_ai_command));
                                                    $available_tokens = $max_tokens - $query_token_count;
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = aiomatic_get_api_service($token, $kw_model);
                                                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $keyword_assistant_id . '\\' . $kw_model . ')(' . $available_tokens . ') for title text2: ' . $title_ai_command);
                                                }
                                                $aierror = '';
                                                $finish_reason = '';
                                                $generated_text = aiomatic_generate_text($token, $kw_model, $title_ai_command, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'keywordCompletion', 0, $finish_reason, $aierror, true, false, false, '', '', 'user', $keyword_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                                                if($generated_text === false)
                                                {
                                                    aiomatic_log_to_file('Keyword generator error: ' . $aierror);
                                                    $ai_title = '';
                                                }
                                                else
                                                {
                                                    $ai_title = trim(trim(trim(trim($generated_text), '.'), ' "\''));
                                                    $ai_titles = explode(',', $ai_title);
                                                    foreach($ai_titles as $query_words)
                                                    {
                                                        $attached_id = '';
                                                        $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, trim($query_words), $img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                                                        if(!empty($z_img))
                                                        {
                                                            $added_images++;
                                                            $added_img_list[] = $z_img;
                                                            $temp_get_img = $z_img;
                                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                                aiomatic_log_to_file('Royalty Free Image Generated with help of AI (kw: "' . $query_words . '"): ' . $z_img);
                                                            }
                                                            break;
                                                        }
                                                    }
                                                }
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                                {
                                                    $api_service = aiomatic_get_api_service($token, $kw_model);
                                                    aiomatic_log_to_file('Successfully got API keyword result from ' . $api_service . ': ' . $ai_title);
                                                }
                                            }
                                        }
                                    }
                                    if(empty($temp_get_img))
                                    {
                                        $keyword_class = new Aiomatic_keywords();
                                        $query_words = $keyword_class->keywords($image_query, 2);
                                        $temp_img_attr = '';
                                        $attached_id = '';
                                        $temp_get_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $temp_img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                                        if($temp_get_img == '' || $temp_get_img === false)
                                        {
                                            $query_words = $keyword_class->keywords($image_query, 1);
                                            $attached_id = '';
                                            $temp_get_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $temp_img_attr, $attached_id, 20, false, $raw_img_list, array(), $full_result_list);
                                            if($temp_get_img == '' || $temp_get_img === false)
                                            {
                                                $temp_get_img = '';
                                            }
                                            else
                                            {
                                                if(!in_array($temp_get_img, $added_img_list))
                                                {
                                                    $added_images++;
                                                    $added_img_list[] = $temp_get_img;
                                                }
                                                else
                                                {
                                                    $temp_get_img = '';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if(!in_array($temp_get_img, $added_img_list))
                                            {
                                                $added_images++;
                                                $added_img_list[] = $temp_get_img;
                                            }
                                            else
                                            {
                                                $temp_get_img = '';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if($heading_val != '')
                        {
                            $ai_created_data = $heading_val . ' ' . $ai_created_data;
                        }
                        if($temp_get_video != '')
                        {
                            $ai_created_data = $temp_get_video . ' ' . $ai_created_data;
                        }
                        elseif($temp_get_img != '')
                        {
                            if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled')
                            {
                                $attached_id = '';
                                $localpath = aiomatic_copy_image_locally($temp_get_img, $attached_id, '', false, '');
                                if($localpath !== false)
                                {
                                    $temp_get_img = $localpath[0];
                                }
                            }
                            $ai_created_data = '<img class="ximage_class" src="' . $temp_get_img . '" alt="' . $query_words . '">' . ' ' . $ai_created_data;
                        }
                    }
                }
                if ($videos == 'on' && $rich_embed_location != 'inside' && !empty($rich_embed_location) && $ai_created_data != false) 
                {
                    $new_vid = aiomatic_get_video(trim(stripslashes(str_replace('&quot;', '"', $post_title))), '', $social_list);
                    if($new_vid !== false)
                    {
                        if($rich_embed_location == 'bottom' || empty($rich_embed_location))
                        {
                            $ai_created_data .= $new_vid;
                        }
                        elseif($rich_embed_location == 'top')
                        {
                            $ai_created_data = $new_vid . ' ' . $ai_created_data;
                        }
                    }
                }
                $final_excerpt = $post->post_excerpt;
                $final_title = $post->post_title;
                if($ai_created_data != false && $ai_created_data != '')
                {
                    if (!isset($aiomatic_Main_Settings['no_undetectibility']) || $aiomatic_Main_Settings['no_undetectibility'] != 'on') 
                    {
                        if (!isset($aiomatic_Main_Settings['no_undetectibility_editor']) || $aiomatic_Main_Settings['no_undetectibility_editor'] != 'on') 
                        {
                            $rand_percentage = rand(10, 20);
                            if(!isset($xchars))
                            {
                                $xchars = array();
                            }
                            $ai_created_data = aiomatic_make_unique($ai_created_data, $xchars, $rand_percentage);
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['preppend_add']) && $aiomatic_Spinner_Settings['preppend_add'] != '')
                    {
                        $preppend_add = aiomatic_replaceAIPostShortcodes($aiomatic_Spinner_Settings['preppend_add'], $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                        $ai_created_data = $preppend_add . $ai_created_data;
                    }
                    if (isset($aiomatic_Spinner_Settings['append_add']) && $aiomatic_Spinner_Settings['append_add'] != '')
                    {
                        $append_add = aiomatic_replaceAIPostShortcodes($aiomatic_Spinner_Settings['append_add'], $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                        $ai_created_data = $ai_created_data . $append_add;
                    }
                    if (isset($aiomatic_Spinner_Settings['append_spintax']) && $aiomatic_Spinner_Settings['append_spintax'] == 'append') 
                    {
                        if (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'title')
                        {
                            $final_title = $final_title . ' ' . $ai_created_data;
                        }
                        elseif (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'excerpt')
                        {
                            $final_excerpt = $final_excerpt . ' ' . $ai_created_data;
                        }
                        else
                        {
                            $final_content = $final_content . ' <br/> ' . $ai_created_data;
                        }
                        $updated = true;
                    }
                    elseif (isset($aiomatic_Spinner_Settings['append_spintax']) && $aiomatic_Spinner_Settings['append_spintax'] == 'preppend')
                    {
                        if (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'title')
                        {
                            $final_title = $ai_created_data . ' ' . $final_title;
                        }
                        elseif (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'excerpt')
                        {
                            $final_excerpt = $ai_created_data . ' ' . $final_excerpt;
                        }
                        else
                        {
                            $final_content = $ai_created_data . ' <br/> ' . $final_content;
                        }
                        $updated = true;
                    }
                    elseif (isset($aiomatic_Spinner_Settings['append_spintax']) && $aiomatic_Spinner_Settings['append_spintax'] == 'inside')
                    {
                        if (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'title')
                        {
                            $final_title = aiomatic_insert_ai_content($final_title, $ai_created_data);
                        }
                        elseif (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'excerpt')
                        {
                            $final_excerpt = aiomatic_insert_ai_content($final_excerpt, $ai_created_data);
                        }
                        else
                        {
                            $final_content = aiomatic_insert_ai_content($final_content, $ai_created_data);
                        }
                        $updated = true;
                    }
                }
                if($updated == true)
                {
                    $args = array();
                    $args['ID'] = $post->ID;
                    if (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'title')
                    {
                        if (isset($aiomatic_Main_Settings['swear_filter']) && $aiomatic_Main_Settings['swear_filter'] == 'on') 
                        {
                            require_once(dirname(__FILE__) . "/res/swear.php");
                            $final_title = aiomatic_filterwords($final_title);
                        }
                        $args['post_title'] = aiomatic_truncate_title($final_title);
                    }
                    elseif (isset($aiomatic_Spinner_Settings['append_location']) && $aiomatic_Spinner_Settings['append_location'] == 'excerpt')
                    {
                        if (isset($aiomatic_Main_Settings['swear_filter']) && $aiomatic_Main_Settings['swear_filter'] == 'on') 
                        {
                            require_once(dirname(__FILE__) . "/res/swear.php");
                            $final_excerpt = aiomatic_filterwords($final_excerpt);
                        }
                        $args['post_excerpt'] = $final_excerpt;
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['swear_filter']) && $aiomatic_Main_Settings['swear_filter'] == 'on') 
                        {
                            require_once(dirname(__FILE__) . "/res/swear.php");
                            $final_content = aiomatic_filterwords($final_content);
                        }
                        $args['post_content'] = $final_content;
                    }
                    if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                    {
                        $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                    }
                    if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                    {
                        $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                        if($date_stamp)
                        {
                            $postdate = date("Y-m-d H:i:s", $date_stamp);
                            $args['post_date'] = $postdate;
                        }
                    }
                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    remove_filter('title_save_pre', 'wp_filter_kses');
                    update_post_meta($post->ID, $custom_name, "pub");
                    $post_updated = wp_update_post($args);
                    add_filter('content_save_pre', 'wp_filter_post_kses');
                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    add_filter('title_save_pre', 'wp_filter_kses');
                    if (is_wp_error($post_updated)) {
                        $errors = $post_updated->get_error_messages();
                        foreach ($errors as $error) {
                            aiomatic_log_to_file('Error occured while updating post for AI content "' . $post->post_title . '": ' . $error);
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated content.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['add_links']) && $aiomatic_Spinner_Settings['add_links'] != '' && $aiomatic_Spinner_Settings['add_links'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post auto linker...');
                }
                if(!function_exists('is_plugin_active'))
                {
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }
                if (isset($aiomatic_Spinner_Settings['link_method']) && $aiomatic_Spinner_Settings['link_method'] == 'linkjuicer' && (is_plugin_active('internal-links/wp-internal-linkjuicer.php') || is_plugin_active('internal-links-premium/wp-internal-linkjuicer.php')))
                {
                    $vision_file = '';
                    if (isset($aiomatic_Spinner_Settings['ai_vision_link_juicer']) && $aiomatic_Spinner_Settings['ai_vision_link_juicer'] == 'on')
                    {
                        $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                        if($avatar !== false)
                        {
                            $vision_file = $avatar;
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['link_juicer_prompt']) && $aiomatic_Spinner_Settings['link_juicer_prompt'] != '')
                    {
                        $link_juicer_prompt = $aiomatic_Spinner_Settings['link_juicer_prompt'];
                    }
                    else
                    {
                        $link_juicer_prompt = 'Generate a comma-separated list of relevant keywords for the post title (for use in the Link Juicer plugin): "%%post_title%%".';
                    }
                    $link_juicer_prompt = aiomatic_replaceSynergyShortcodes($link_juicer_prompt);
                    
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $link_juicer_prompt = aiomatic_replaceAIPostShortcodes($link_juicer_prompt, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $link_juicer_prompt = trim($link_juicer_prompt);
                    if (isset($aiomatic_Spinner_Settings['link_juicer_model']) && $aiomatic_Spinner_Settings['link_juicer_model'] != '') {
                        $cmodel = $aiomatic_Spinner_Settings['link_juicer_model'];
                    }
                    else
                    {
                        $cmodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                    }
                    if (isset($aiomatic_Spinner_Settings['link_juicer_assistant_id']) && $aiomatic_Spinner_Settings['link_juicer_assistant_id'] != '') {
                        $link_juicer_assistant_id = $aiomatic_Spinner_Settings['link_juicer_assistant_id'];
                    }
                    else
                    {
                        $link_juicer_assistant_id = '';
                    }
                    preg_match_all('#\s*(\d+)\s*-\s*(\d+)\s*#', $max_cats, $mxatches);
                    if(isset($mxatches[2][0]))
                    {
                        $minx = $mxatches[1][0];
                        $maxx = $mxatches[2][0];
                        $max_cats = rand(intval($minx), intval($maxx));
                    }
                    else
                    {
                        $max_cats = intval($max_cats);
                    }
                    $author_obj       = get_user_by('id', $post->post_author);
                    if(isset($author_obj->user_nicename))
                    {
                        $xuser_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $xuser_name        = get_bloginfo( 'name' );
                    }
                    $xpost_link        = get_permalink($post->ID);
                    $link_juicer_prompt_new = $link_juicer_prompt;
                    $userid = false;
                    $date       = date('Y-m-d H:i:s', strtotime(current_time('mysql')));
                    $query_token_count = count(aiomatic_encode($link_juicer_prompt_new));
                    if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                    {
                        $link_juicer_prompt_new = aiomatic_substr($link_juicer_prompt_new, 0, (0-($max_seed_tokens * 4)));
                        $query_token_count = count(aiomatic_encode($link_juicer_prompt_new));
                    }
                    $max_tokens = aiomatic_get_max_tokens($cmodel);
                    $available_tokens = aiomatic_compute_available_tokens($cmodel, $max_tokens, $link_juicer_prompt_new, $query_token_count);
                    $max_result_tokens = aiomatic_get_max_tokens($cmodel);
                    if($available_tokens > $max_result_tokens)
                    {
                        $available_tokens = $max_result_tokens;
                    }
                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                    {
                        $string_len = strlen($link_juicer_prompt_new);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $link_juicer_prompt_new = aiomatic_substr($link_juicer_prompt_new, 0, $string_len);
                        $link_juicer_prompt_new = trim(link_juicer_prompt_new);
                        if(empty(link_juicer_prompt_new))
                        {
                            aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($link_juicer_prompt_new, true));
                        }
                        $query_token_count = count(aiomatic_encode($link_juicer_prompt_new));
                        $available_tokens = $max_tokens - $query_token_count;
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                    {
                        $api_service = aiomatic_get_api_service($token, $cmodel);
                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $link_juicer_assistant_id . '\\' . $cmodel . ')(' . $available_tokens . ') post Link Juicer keyword generator, with seed command: ' . $link_juicer_prompt_new);
                    }
                    $aierror = '';
                    $extra_kws = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $cmodel, $link_juicer_prompt_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'linkJuicerKeywordGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $link_juicer_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('Link Juicer keyword generator error: ' . $aierror);
                    }
                    else
                    {
                        $extra_kws = ucfirst(trim(nl2br(trim($generated_text))));
                        $extra_kws = str_replace('//', '', $extra_kws);
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($extra_kws) !== '') 
                    {
                        $api_service = aiomatic_get_api_service($token, $cmodel);
                        aiomatic_log_to_file($api_service . ' responded successfully, Link Juicer keyword generated for post ID ' . $post->ID . ': ' . $extra_kws, true);
                    }
                    if($extra_kws != '')
                    {
                        if (isset($aiomatic_Spinner_Settings['force_keywords']) && $aiomatic_Spinner_Settings['force_keywords'] != '') 
                        {
                            $force_keywords = $aiomatic_Spinner_Settings['force_keywords'];
                            $force_keywords = explode(',', $force_keywords);
                            $force_keywords = array_map('trim', $force_keywords);
                            $force_keywords = array_filter($force_keywords);
                        }
                        else
                        {
                            $force_keywords = array();
                        }
                        $link_juicer_arr = explode(',', $extra_kws);
                        $link_juicer_arr = array_merge($link_juicer_arr, $force_keywords);
                        update_post_meta( $post->ID, 'ilj_linkdefinition', $link_juicer_arr);
                        do_action(
                            'ilj_after_keywords_update',
                            $post->ID,
                            'post',
                            $post->post_status
                        );
                    }
                }
                else
                {
                    if (isset($aiomatic_Spinner_Settings['max_links']) && $aiomatic_Spinner_Settings['max_links'] != '')
                    {
                        $max_links = trim($aiomatic_Spinner_Settings['max_links']);
                    }
                    else
                    {
                        $max_links = '3-5';
                    }
                    if (isset($aiomatic_Spinner_Settings['link_post_types']) && $aiomatic_Spinner_Settings['link_post_types'] != '')
                    {
                        $link_post_types = trim($aiomatic_Spinner_Settings['link_post_types']);
                    }
                    else
                    {
                        $link_post_types = 'post';
                    }
                    if (isset($aiomatic_Spinner_Settings['link_post_not_ids']) && $aiomatic_Spinner_Settings['link_post_not_ids'] != '')
                    {
                        $link_post_not_ids = trim($aiomatic_Spinner_Settings['link_post_not_ids']);
                    }
                    else
                    {
                        $link_post_not_ids = '';
                    }
                    if (isset($aiomatic_Spinner_Settings['link_post_ids']) && $aiomatic_Spinner_Settings['link_post_ids'] != '')
                    {
                        $link_post_ids = trim($aiomatic_Spinner_Settings['link_post_ids']);
                    }
                    else
                    {
                        $link_post_ids = '';
                    }
                    if (isset($aiomatic_Spinner_Settings['link_nofollow']) && $aiomatic_Spinner_Settings['link_nofollow'] != '')
                    {
                        $link_nofollow = trim($aiomatic_Spinner_Settings['link_nofollow']);
                    }
                    else
                    {
                        $link_nofollow = 'post';
                    }
                    if (isset($aiomatic_Spinner_Settings['link_type']) && $aiomatic_Spinner_Settings['link_type'] != '') 
                    {
                        $link_type = $aiomatic_Spinner_Settings['link_type'];
                    }
                    else
                    {
                        $link_type = 'internal';
                    }
                    if (isset($aiomatic_Spinner_Settings['link_list']) && !empty($aiomatic_Spinner_Settings['link_list'])) 
                    {
                        $link_list = $aiomatic_Spinner_Settings['link_list'];
                    }
                    else
                    {
                        $link_list = '';
                    }
                    $zlang = 'en_US';
                    if (isset($aiomatic_Main_Settings['kw_lang']) && !empty($aiomatic_Main_Settings['kw_lang'])) 
                    {
                        $zlang = $aiomatic_Main_Settings['kw_lang'];
                    }
                    $rel_search = array('post_title', 'post_content');
                    if (isset($aiomatic_Main_Settings['rel_search']) && is_array($aiomatic_Main_Settings['rel_search'])) 
                    {
                        $rel_search = $aiomatic_Main_Settings['rel_search'];
                    }
                    if($max_links !== '')
                    {
                        preg_match_all('#\s*(\d+)\s*-\s*(\d+)\s*#', $max_links, $mxatches);
                        if(isset($mxatches[2][0]))
                        {
                            $min = $mxatches[1][0];
                            $max = $mxatches[2][0];
                            $max_links = rand(intval($min), intval($max));
                        }
                        else
                        {
                            $max_links = intval($max_links);
                        }
                        if (isset($aiomatic_Spinner_Settings['force_keywords']) && $aiomatic_Spinner_Settings['force_keywords'] != '') 
                        {
                            $force_keywords = $aiomatic_Spinner_Settings['force_keywords'];
                            $force_keywords = explode(',', $force_keywords);
                            $force_keywords = array_map('trim', $force_keywords);
                            $force_keywords = array_filter($force_keywords);
                        }
                        else
                        {
                            $force_keywords = array();
                        }
                        require_once(dirname(__FILE__) . "/res/InboundLinks.php");
                        $inboundlinker = new AiomaticAutoInboundLinks();
                        try
                        {
                            $final_content_links = $inboundlinker->add_inbound_links($final_content, $max_links, $link_post_types, $zlang, $rel_search, $post->ID, $link_type, $link_list, $link_nofollow, $link_post_ids, $link_post_not_ids, $force_keywords);
                            if(!empty($final_content_links) && $final_content_links != $final_content)
                            {
                                $final_content = $final_content_links;
                                $args = array();
                                $args['ID'] = $post->ID;
                                $args['post_content'] = $final_content_links;
                                if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                                {
                                    $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                                }
                                if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                                {
                                    $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                    if($date_stamp)
                                    {
                                        $postdate = date("Y-m-d H:i:s", $date_stamp);
                                        $args['post_date'] = $postdate;
                                    }
                                }
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post for internal links "' . $post->post_title . '": ' . $error);
                                    }
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with internal links.');
                                    }
                                }
                            }
                        }
                        catch(Exception $ex)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('Failed to automatically add new inbound links to content: ' . $ex->getMessage());
                            }
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['append_toc']) && $aiomatic_Spinner_Settings['append_toc'] != '' && $aiomatic_Spinner_Settings['append_toc'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post ToC generator...');
                }
                $updated = false;
                $items               = '';
                $find                = [];
                $replace             = [];
                $css_classes = '';
                $items = aiomatic_extract_headings( $find, $replace, $final_content );
                if ( $items ) 
                {
                    if (isset($aiomatic_Spinner_Settings['float_toc']) && $aiomatic_Spinner_Settings['float_toc'] != '' && $aiomatic_Spinner_Settings['float_toc'] != 'none')
                    {
                        switch ( $aiomatic_Spinner_Settings['float_toc'] ) 
                        {
                            case 'left':
                                $css_classes .= ' aiomatic_toc_wrap_left';
                                break;

                            case 'right':
                                $css_classes .= ' aiomatic_toc_wrap_right';
                                break;

                            case 'none':
                            default:
                        }
                    }

                    if (isset($aiomatic_Spinner_Settings['color_toc']) && $aiomatic_Spinner_Settings['color_toc'] != '' && $aiomatic_Spinner_Settings['color_toc'] != 'gray')
                    {
                        switch ( $aiomatic_Spinner_Settings['color_toc'] ) 
                        {
                            case 'blue':
                                $css_classes .= ' aiomatic_toc_light_blue';
                                break;

                            case 'white':
                                $css_classes .= ' aiomatic_toc_white';
                                break;

                            case 'black':
                                $css_classes .= ' aiomatic_toc_black';
                                break;

                            case 'transparent':
                                $css_classes .= ' aiomatic_toc_transparent';
                                break;

                            case 'gray':
                            default:
                        }
                    }
                    $css_classes = trim( $css_classes );
                    if ( ! $css_classes ) 
                    {
                        $css_classes = ' ';
                    }
                    $html = '<div id="aiomatic_toc_container" class="' . htmlentities( $css_classes, ENT_COMPAT, 'UTF-8' ) . '">';
                    if (isset($aiomatic_Spinner_Settings['title_toc']) && trim($aiomatic_Spinner_Settings['title_toc']) != '')
                    {
                        $toc_title = trim($aiomatic_Spinner_Settings['title_toc']);
                    }
                    else
                    {
                        $toc_title = 'Table of Contents';
                    }
                    $toc_title = htmlentities( $toc_title, ENT_COMPAT, 'UTF-8' );
                    $html .= '<p class="aiomatic_toc_title">' . $toc_title . '</p>';
                    $html .= '<ul class="aiomatic_toc_list">' . $items . '</ul></div>' . "\n";
                    $toc_location = $aiomatic_Spinner_Settings['append_toc'];
                    if ( count( $find ) > 0 ) 
                    {
                        switch ( $toc_location ) 
                        {
                            case 'preppend':
                                $final_content = $html . aiomatic_mb_find_replace( $find, $replace, $final_content );
                                $updated = true;
                                break;

                            case 'append':
                                $final_content = aiomatic_mb_find_replace( $find, $replace, $final_content ) . $html;
                                $updated = true;
                                break;

                            case 'heading2':
                                $replace[0] = $replace[0] . $html;
                                $final_content    = aiomatic_mb_find_replace( $find, $replace, $final_content );
                                $updated = true;
                                break;

                            case 'heading':
                            default:
                                $replace[0] = $html . $replace[0];
                                $final_content    = aiomatic_mb_find_replace( $find, $replace, $final_content );
                                $updated = true;
                        }
                    }
				}
                if($updated == true)
                {
                    $args = array();
                    $args['ID'] = $post->ID;
                    $args['post_content'] = $final_content;
                    if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                    {
                        $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                    }
                    if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                    {
                        $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                        if($date_stamp)
                        {
                            $postdate = date("Y-m-d H:i:s", $date_stamp);
                            $args['post_date'] = $postdate;
                        }
                    }
                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    remove_filter('title_save_pre', 'wp_filter_kses');
                    update_post_meta($post->ID, $custom_name, "pub");
                    $post_updated = wp_update_post($args);
                    add_filter('content_save_pre', 'wp_filter_post_kses');
                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    add_filter('title_save_pre', 'wp_filter_kses');
                    if (is_wp_error($post_updated)) {
                        $errors = $post_updated->get_error_messages();
                        foreach ($errors as $error) {
                            aiomatic_log_to_file('Error occured while updating post for AI content "' . $post->post_title . '": ' . $error);
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated content.');
                        }
                        update_post_meta($post->ID, 'aiomatic_toc', '1');
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['add_cats']) && $aiomatic_Spinner_Settings['add_cats'] != '' && $aiomatic_Spinner_Settings['add_cats'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post category generator...');
                }
                $vision_file = '';
                if (isset($aiomatic_Spinner_Settings['max_cats']) && $aiomatic_Spinner_Settings['max_cats'] != '')
                {
                    $max_cats = trim($aiomatic_Spinner_Settings['max_cats']);
                }
                else
                {
                    $max_cats = '1';
                }
                if($max_cats !== '')
                {
                    if (isset($aiomatic_Spinner_Settings['cats_tax']) && trim($aiomatic_Spinner_Settings['cats_tax']) != '')
                    {
                        $cat_slug = trim($aiomatic_Spinner_Settings['cats_tax']);
                    }
                    else
                    {
                        $cat_slug = 'category';
                        if($post->post_type == 'product')
                        {
                            $cat_slug = 'product_cat';
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['ai_vision_cat']) && $aiomatic_Spinner_Settings['ai_vision_cat'] == 'on')
                    {
                        $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                        if($avatar !== false)
                        {
                            $vision_file = $avatar;
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['ai_cats']) && $aiomatic_Spinner_Settings['ai_cats'] != '')
                    {
                        $ai_cats = $aiomatic_Spinner_Settings['ai_cats'];
                    }
                    else
                    {
                        $ai_cats = 'Write a comma separated list of 5 categories for post title: %%post_title%%';
                    }
                    $ai_cats = aiomatic_replaceSynergyShortcodes($ai_cats);
                    
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $caxcategories = get_terms(array(
                        'taxonomy'   => array($cat_slug)
                    ));
                    $all_categories = '';
                    if (!is_wp_error($caxcategories) && !empty($caxcategories)) 
                    {
                        foreach ($caxcategories as $category) 
                        {
                            $all_categories .= $category->slug . ',';
                        }
                        $all_categories = rtrim($all_categories, ',');
                    }
                    $ai_cats = str_replace('%%all_categories%%', $all_categories, $ai_cats);
                    $ai_cats = aiomatic_replaceAIPostShortcodes($ai_cats, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_cats = trim($ai_cats);
                    if (isset($aiomatic_Spinner_Settings['cats_model']) && $aiomatic_Spinner_Settings['cats_model'] != '') {
                        $cmodel = $aiomatic_Spinner_Settings['cats_model'];
                    }
                    else
                    {
                        $cmodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                    }
                    if (isset($aiomatic_Spinner_Settings['categories_assistant_id']) && $aiomatic_Spinner_Settings['categories_assistant_id'] != '') {
                        $categories_assistant_id = $aiomatic_Spinner_Settings['categories_assistant_id'];
                    }
                    else
                    {
                        $categories_assistant_id = '';
                    }
                    preg_match_all('#\s*(\d+)\s*-\s*(\d+)\s*#', $max_cats, $mxatches);
                    if(isset($mxatches[2][0]))
                    {
                        $minx = $mxatches[1][0];
                        $maxx = $mxatches[2][0];
                        $max_cats = rand(intval($minx), intval($maxx));
                    }
                    else
                    {
                        $max_cats = intval($max_cats);
                    }
                    $author_obj       = get_user_by('id', $post->post_author);
                    if(isset($author_obj->user_nicename))
                    {
                        $xuser_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $xuser_name        = get_bloginfo( 'name' );
                    }
                    $xpost_link        = get_permalink($post->ID);
                    $ai_cats_new = $ai_cats;
                    $userid = false;
                    $date       = date('Y-m-d H:i:s', strtotime(current_time('mysql')));
                    $query_token_count = count(aiomatic_encode($ai_cats_new));
                    if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                    {
                        $ai_cats_new = aiomatic_substr($ai_cats_new, 0, (0-($max_seed_tokens * 4)));
                        $query_token_count = count(aiomatic_encode($ai_cats_new));
                    }
                    $max_tokens = aiomatic_get_max_tokens($cmodel);
                    $available_tokens = aiomatic_compute_available_tokens($cmodel, $max_tokens, $ai_cats_new, $query_token_count);
                    $max_result_tokens = aiomatic_get_max_tokens($cmodel);
                    if($available_tokens > $max_result_tokens)
                    {
                        $available_tokens = $max_result_tokens;
                    }
                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                    {
                        $string_len = strlen($ai_cats_new);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $ai_cats_new = aiomatic_substr($ai_cats_new, 0, $string_len);
                        $ai_cats_new = trim($ai_cats_new);
                        if(empty($ai_cats_new))
                        {
                            aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_cats_new, true));
                        }
                        $query_token_count = count(aiomatic_encode($ai_cats_new));
                        $available_tokens = $max_tokens - $query_token_count;
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                    {
                        $api_service = aiomatic_get_api_service($token, $cmodel);
                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $categories_assistant_id . '\\' . $cmodel . ')(' . $available_tokens . ') post category generator, with seed command: ' . $ai_cats_new);
                    }
                    $aierror = '';
                    $extra_categories = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $cmodel, $ai_cats_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'categoryGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $categories_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('Category generator error: ' . $aierror);
                    }
                    else
                    {
                        $extra_categories = ucfirst(trim(nl2br(trim($generated_text))));
                        $extra_categories = str_replace('//', '', $extra_categories);
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($extra_categories) !== '') 
                    {
                        $api_service = aiomatic_get_api_service($token, $cmodel);
                        aiomatic_log_to_file($api_service . ' responded successfully, category generated for post ID ' . $post->ID . ': ' . $extra_categories, true);
                    }
                    if($extra_categories != '')
                    {
                        $extra_cats = explode(',', $extra_categories);
                        $added_cats = 0;
                        foreach($extra_cats as $extra_cat)
                        {
                            if($added_cats >= $max_cats)
                            {
                                break;
                            }
                            $extra_cat = trim($extra_cat);
                            $extra_cat = strip_tags($extra_cat);
                            $extra_cat = preg_replace('#^\d+\.\s*#', '', $extra_cat);
                            if(empty($extra_cat))
                            {
                                continue;
                            }
                            if (isset($aiomatic_Spinner_Settings['skip_inexist']) && $aiomatic_Spinner_Settings['skip_inexist'] == 'on') 
                            {
                                if(!term_exists($extra_cat, $cat_slug))
                                {
                                    aiomatic_log_to_file('Skipping category, as it is not found on this site: ' . $extra_cat);
                                    continue;
                                }
                            }
                            $added_cats++;
                            $termid = aiomatic_create_terms($cat_slug, null, trim($extra_cat));
                            wp_set_post_terms($post->ID, $termid, $cat_slug, true);
                        }
                        if($added_cats > 0)
                        {
                            if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                            if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                            {
                                $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                if($date_stamp)
                                {
                                    $args = array();
                                    $args['ID'] = $post->ID;
                                    $postdate = date("Y-m-d H:i:s", $date_stamp);
                                    $args['post_date'] = $postdate;
                                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    remove_filter('title_save_pre', 'wp_filter_kses');
                                    update_post_meta($post->ID, $custom_name, "pub");
                                    $post_updated = wp_update_post($args);
                                    add_filter('content_save_pre', 'wp_filter_post_kses');
                                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    add_filter('title_save_pre', 'wp_filter_kses');
                                    if (is_wp_error($post_updated)) {
                                        $errors = $post_updated->get_error_messages();
                                        foreach ($errors as $error) {
                                            aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                        }
                                    }
                                }
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated categories.');
                            }
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['add_tags']) && $aiomatic_Spinner_Settings['add_tags'] != '' && $aiomatic_Spinner_Settings['add_tags'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post tag generator...');
                }
                if (isset($aiomatic_Spinner_Settings['max_tags']) && $aiomatic_Spinner_Settings['max_tags'] != '')
                {
                    $max_tags = trim($aiomatic_Spinner_Settings['max_tags']);
                }
                else
                {
                    $max_tags = '1';
                }
                if($max_tags !== '')
                {
                    if (isset($aiomatic_Spinner_Settings['tags_tax']) && trim($aiomatic_Spinner_Settings['tags_tax']) != '')
                    {
                        $tag_slug = trim($aiomatic_Spinner_Settings['tags_tax']);
                    }
                    else
                    {
                        $tag_slug = 'post_tag';
                        if($post->post_type == 'product')
                        {
                            $tag_slug = 'product_tag';
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['ai_vision_tag']) && $aiomatic_Spinner_Settings['ai_vision_tag'] == 'on')
                    {
                        $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                        if($avatar !== false)
                        {
                            $vision_file = $avatar;
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['ai_tags']) && $aiomatic_Spinner_Settings['ai_tags'] != '')
                    {
                        $ai_tags = $aiomatic_Spinner_Settings['ai_tags'];
                    }
                    else
                    {
                        $ai_tags = 'Write a comma separated list of 5 tags for post title: %%post_title%%';
                    }
                    $ai_tags = aiomatic_replaceSynergyShortcodes($ai_tags);
                    
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, $tag_slug );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $ai_tags = aiomatic_replaceAIPostShortcodes($ai_tags, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_tags = trim($ai_tags);
                    if (isset($aiomatic_Spinner_Settings['tags_model']) && $aiomatic_Spinner_Settings['tags_model'] != '') {
                        $cmodel = $aiomatic_Spinner_Settings['tags_model'];
                    }
                    else
                    {
                        $cmodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                    }
                    if (isset($aiomatic_Spinner_Settings['tags_assistant_id']) && $aiomatic_Spinner_Settings['tags_assistant_id'] != '') {
                        $tags_assistant_id = $aiomatic_Spinner_Settings['tags_assistant_id'];
                    }
                    else
                    {
                        $tags_assistant_id = '';
                    }
                    preg_match_all('#\s*(\d+)\s*-\s*(\d+)\s*#', $max_tags, $mxatches);
                    if(isset($mxatches[2][0]))
                    {
                        $minx = $mxatches[1][0];
                        $maxx = $mxatches[2][0];
                        $max_tags = rand(intval($minx), intval($maxx));
                    }
                    else
                    {
                        $max_tags = intval($max_tags);
                    }
                    $author_obj       = get_user_by('id', $post->post_author);
                    if(isset($author_obj->user_nicename))
                    {
                        $xuser_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $xuser_name        = get_bloginfo( 'name' );
                    }
                    $xpost_link        = get_permalink($post->ID);
                    $ai_tags_new = $ai_tags;
                    $userid = false;
                    $date       = date('Y-m-d H:i:s', strtotime(current_time('mysql')));
                    $query_token_count = count(aiomatic_encode($ai_tags_new));
                    if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                    {
                        $ai_tags_new = aiomatic_substr($ai_tags_new, 0, (0-($max_seed_tokens * 4)));
                        $query_token_count = count(aiomatic_encode($ai_tags_new));
                    }
                    $max_tokens = aiomatic_get_max_tokens($cmodel);
                    $available_tokens = aiomatic_compute_available_tokens($cmodel, $max_tokens, $ai_tags_new, $query_token_count);
                    $max_result_tokens = aiomatic_get_max_tokens($cmodel);
                    if($available_tokens > $max_result_tokens)
                    {
                        $available_tokens = $max_result_tokens;
                    }
                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                    {
                        $string_len = strlen($ai_tags_new);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $ai_tags_new = aiomatic_substr($ai_tags_new, 0, $string_len);
                        $ai_tags_new = trim($ai_tags_new);
                        if(empty($ai_tags_new))
                        {
                            aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_tags_new, true));
                        }
                        $query_token_count = count(aiomatic_encode($ai_tags_new));
                        $available_tokens = $max_tokens - $query_token_count;
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                    {
                        $api_service = aiomatic_get_api_service($token, $cmodel);
                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $tags_assistant_id . '\\' . $cmodel . ')(' . $available_tokens . ') post tags generator, with seed command: ' . $ai_tags_new);
                    }
                    $aierror = '';
                    $extra_tags = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $cmodel, $ai_tags_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'tagsGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $tags_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('Tags generator error: ' . $aierror);
                    }
                    else
                    {
                        $extra_tags = ucfirst(trim(nl2br(trim($generated_text))));
                        $extra_tags = str_replace('//', '', $extra_tags);
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($extra_tags) !== '') 
                    {
                        $api_service = aiomatic_get_api_service($token, $cmodel);
                        aiomatic_log_to_file($api_service . ' responded successfully, tag generated for post ID ' . $post->ID . ': ' . $extra_tags);
                    }
                    if($extra_tags != '')
                    {
                        $extra_tagsz = explode(',', $extra_tags);
                        $added_tags = 0;
                        foreach($extra_tagsz as $extra_tag)
                        {
                            if($added_tags >= $max_tags)
                            {
                                break;
                            }
                            $extra_tag = trim($extra_tag);
                            $extra_tag = strip_tags($extra_tag);
                            $extra_tag = preg_replace('#^\d+\.\s*#', '', $extra_tag);
                            $extra_tag = str_replace('#', '', $extra_tag);
                            if(empty($extra_tag))
                            {
                                continue;
                            }
                            if (isset($aiomatic_Spinner_Settings['skip_inexist_tags']) && $aiomatic_Spinner_Settings['skip_inexist_tags'] == 'on') 
                            {
                                if(!term_exists($extra_tag, $tag_slug))
                                {
                                    aiomatic_log_to_file('Skipping tag, as it is not found on this site: ' . $extra_tag);
                                    continue;
                                }
                            }
                            $added_tags++;
                            $term = get_term_by('name', trim($extra_tag), $tag_slug);
                            $term_id = '';
                            if (!$term) {
                                $term = wp_insert_term(trim($extra_tag), $tag_slug);
                                if (is_wp_error($term)) {
                                    aiomatic_log_to_file('Failed to insert new term: ' . $extra_tag . ' - error: ' . $term->get_error_message());
                                    continue;
                                }
                                $term_id = $term['term_id'];
                            }
                            else
                            {
                                $term_id = $term->term_id;
                            }
                            if ($term_id) {
                                wp_set_object_terms($post->ID, array($term_id), $tag_slug, true);
                            }
                        }
                        if($added_tags > 0)
                        {
                            if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                            if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                            {
                                $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                if($date_stamp)
                                {
                                    $args = array();
                                    $args['ID'] = $post->ID;
                                    $postdate = date("Y-m-d H:i:s", $date_stamp);
                                    $args['post_date'] = $postdate;
                                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    remove_filter('title_save_pre', 'wp_filter_kses');
                                    update_post_meta($post->ID, $custom_name, "pub");
                                    $post_updated = wp_update_post($args);
                                    add_filter('content_save_pre', 'wp_filter_post_kses');
                                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    add_filter('title_save_pre', 'wp_filter_kses');
                                    if (is_wp_error($post_updated)) {
                                        $errors = $post_updated->get_error_messages();
                                        foreach ($errors as $error) {
                                            aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                        }
                                    }
                                }
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated tags.');
                            }
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['add_custom']) && $aiomatic_Spinner_Settings['add_custom'] != '' && $aiomatic_Spinner_Settings['add_custom'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post custom taxonomy creator...');
                }
                if (isset($aiomatic_Spinner_Settings['max_custom']) && $aiomatic_Spinner_Settings['max_custom'] != '')
                {
                    $max_custom = trim($aiomatic_Spinner_Settings['max_custom']);
                }
                else
                {
                    $max_custom = '1';
                }
                if($max_custom !== '')
                {
                    if (isset($aiomatic_Spinner_Settings['ai_vision_custom']) && $aiomatic_Spinner_Settings['ai_vision_custom'] == 'on')
                    {
                        $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                        if($avatar !== false)
                        {
                            $vision_file = $avatar;
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['ai_custom_tax']) && $aiomatic_Spinner_Settings['ai_custom_tax'] != '')
                    {
                        $ai_custom_tax_arr = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['ai_custom_tax']));
                        $added_tax = 0;
                        foreach($ai_custom_tax_arr as $ai_custom_tax_full)
                        {
                            $ai_custom_tax_full = trim($ai_custom_tax_full);
                            if($ai_custom_tax_full == '')
                            {
                                continue;
                            }
                            if(strstr($ai_custom_tax_full, '=>') === false)
                            {
                                aiomatic_log_to_file('Invalid custom taxonomy creator format sent. Correct format is: taxonomy_slug => AI prompt. Sent: ' . $ai_custom_tax_full);
                                continue;
                            }
                            $ai_custom_tax_parts = explode('=>', $ai_custom_tax_full);
                            $ai_custom_tax = trim($ai_custom_tax_parts[1]);
                            $ai_custom_tax_slug = trim($ai_custom_tax_parts[0]);
                            if(empty($ai_custom_tax))
                            {
                                aiomatic_log_to_file('Invalid custom taxonomy creator prompt sent. Correct format is: taxonomy_slug => AI prompt. Sent: ' . $ai_custom_tax_full);
                                continue;
                            }
                            if(empty($ai_custom_tax_slug))
                            {
                                aiomatic_log_to_file('Invalid custom taxonomy creator slug sent. Correct format is: taxonomy_slug => AI prompt. Sent: ' . $ai_custom_tax_full);
                                continue;
                            }
                            $ai_custom_tax = aiomatic_replaceSynergyShortcodes($ai_custom_tax);
                            $post_link        = get_permalink($post->ID);
                            $blog_title       = html_entity_decode(get_bloginfo('title'));
                            $author_obj       = get_user_by('id', $post->post_author);
                            if($author_obj !== false && isset($author_obj->user_nicename))
                            {
                                $user_name        = $author_obj->user_nicename;
                            }
                            else
                            {
                                $user_name        = '';
                            }
                            $featured_image   = '';
                            wp_suspend_cache_addition(true);
                            $metas = get_post_custom($post->ID);
                            wp_suspend_cache_addition(false);
                            if(is_array($metas))
                            {
                                $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                            }
                            else
                            {
                                $rez_meta = array();
                            }
                            if(count($rez_meta) > 0)
                            {
                                foreach($rez_meta as $rm)
                                {
                                    if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                                    {
                                        $featured_image = $rm[0];
                                        break;
                                    }
                                }
                            }
                            if($featured_image == '')
                            {
                                $featured_image = aiomatic_generate_thumbmail($post->ID);
                            }
                            if($featured_image == '' && $final_content != '')
                            {
                                $dom     = new DOMDocument();
                                $internalErrors = libxml_use_internal_errors(true);
                                $dom->loadHTML($final_content);
                                libxml_use_internal_errors($internalErrors);
                                $tags      = $dom->getElementsByTagName('img');
                                foreach ($tags as $tag) {
                                    $temp_get_img = $tag->getAttribute('src');
                                    if ($temp_get_img != '') {
                                        $temp_get_img = strtok($temp_get_img, '?');
                                        $featured_image = rtrim($temp_get_img, '/');
                                    }
                                }
                            }
                            $post_cats = '';
                            $post_categories = wp_get_post_categories( $post->ID );
                            foreach($post_categories as $c){
                                $cat = get_category( $c );
                                $post_cats .= $cat->name . ',';
                            }
                            $post_cats = trim($post_cats, ',');
                            if($post_cats != '')
                            {
                                $post_categories = explode(',', $post_cats);
                            }
                            else
                            {
                                $post_categories = array();
                            }
                            if(count($post_categories) == 0)
                            {
                                $terms = get_the_terms( $post->ID, 'product_cat' );
                                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                    foreach ( $terms as $term ) {
                                        $post_categories[] = $term->slug;
                                    }
                                    $post_cats = implode(',', $post_categories);
                                }
                                
                            }
                            foreach($post_categories as $pc)
                            {
                                if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                                    foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                                    {
                                        if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                        {
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                            {
                                                aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                            }
                                            return;
                                        }
                                    }
                                }
                            }
                            $post_tagz = '';
                            $post_tags = wp_get_post_tags( $post->ID );
                            foreach($post_tags as $t){
                                $post_tagz .= $t->name . ',';
                            }
                            $post_tagz = trim($post_tagz, ',');
                            if($post_tagz != '')
                            {
                                $post_tags = explode(',', $post_tagz);
                            }
                            else
                            {
                                $post_tags = array();
                            }
                            if(count($post_tags) == 0)
                            {
                                $terms = get_the_terms( $post->ID, 'product_tag' );
                                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                    foreach ( $terms as $term ) {
                                        $post_tags[] = $term->slug;
                                    }
                                    $post_tagz = implode(',', $post_tags);
                                }
                                
                            }
                            if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                                
                                $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                                foreach($disable_users as $disable_user)
                                {
                                    if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                        }
                                        return;
                                    }
                                }
                            }
                            if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                                
                                $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                                $found = false;
                                foreach($enable_users as $disable_user)
                                {
                                    if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                    {
                                        $found = true;
                                    }
                                }
                                if($found === false)
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                                    }
                                    return;
                                }
                            }
                            if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                                if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                                {
                                    if ( !has_post_thumbnail($post->ID) ) 
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                        }
                                        return;
                                    }
                                }
                                elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                                {
                                    if ( has_post_thumbnail($post->ID) ) 
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                        }
                                        return;
                                    }
                                }
                            }
                            
                            if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                                if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                                {
                                    if ( !aiomatic_check_seo_description($post->ID) ) 
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                        }
                                        return;
                                    }
                                }
                                elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                                {
                                    if ( aiomatic_check_seo_description($post->ID) ) 
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                        }
                                        return;
                                    }
                                }
                            }
                            if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                                $skip_me = true;
                                $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                                foreach($enable_field as $fieldx)
                                {
                                    if(strstr($fieldx, '=>') !== false)
                                    {
                                        $fna = explode('=>', $fieldx);
                                        if(isset($fna[1]))
                                        {
                                            $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                            if ($custom_val) 
                                            {
                                                if(trim($fna[1])  == '*')
                                                {
                                                    $skip_me = false;
                                                }
                                                if($custom_val == trim($fna[1]))
                                                {
                                                    $skip_me = false;
                                                }
                                            }
                                        }
                                    }
                                }
                                if($skip_me === true)
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                                $skip_me = false;
                                $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                                foreach($disable_field as $fieldx)
                                {
                                    if(strstr($fieldx, '=>') !== false)
                                    {
                                        $fna = explode('=>', $fieldx);
                                        if(isset($fna[1]))
                                        {
                                            $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                            if ($custom_val) 
                                            {
                                                if(trim($fna[1])  == '*')
                                                {
                                                    $skip_me = true;
                                                }
                                                if($custom_val == trim($fna[1]))
                                                {
                                                    $skip_me = true;
                                                }
                                            }
                                        }
                                    }
                                }
                                if($skip_me === true)
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            foreach($post_tags as $pt)
                            {
                                if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                                    
                                    $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                                    foreach($disable_tags as $disabled_tag)
                                    {
                                        if($manual != true && trim($pt) == trim($disabled_tag))
                                        {
                                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                            {
                                                aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                            }
                                            return;
                                        }
                                    }
                                }
                            }
                            if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                                $skip = true;
                                $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                                foreach($post_tags as $pt)
                                {
                                    foreach($enable_tags as $enable_tag)
                                    {
                                        if(trim($pt) == trim($enable_tag))
                                        {
                                            $skip = false;
                                            break;
                                        }
                                    }
                                }
                                if($skip == true)
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                            $ai_custom_tax = aiomatic_replaceAIPostShortcodes($ai_custom_tax, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                            $ai_custom_tax = trim($ai_custom_tax);
                            if (isset($aiomatic_Spinner_Settings['custom_model']) && $aiomatic_Spinner_Settings['custom_model'] != '') {
                                $cmodel = $aiomatic_Spinner_Settings['custom_model'];
                            }
                            else
                            {
                                $cmodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                            }
                            if (isset($aiomatic_Spinner_Settings['custom_assistant_id']) && $aiomatic_Spinner_Settings['custom_assistant_id'] != '') {
                                $custom_assistant_id = $aiomatic_Spinner_Settings['custom_assistant_id'];
                            }
                            else
                            {
                                $custom_assistant_id = '';
                            }
                            preg_match_all('#\s*(\d+)\s*-\s*(\d+)\s*#', $max_custom, $mxatches);
                            if(isset($mxatches[2][0]))
                            {
                                $minx = $mxatches[1][0];
                                $maxx = $mxatches[2][0];
                                $max_custom = rand(intval($minx), intval($maxx));
                            }
                            else
                            {
                                $max_custom = intval($max_custom);
                            }
                            $author_obj       = get_user_by('id', $post->post_author);
                            if(isset($author_obj->user_nicename))
                            {
                                $xuser_name        = $author_obj->user_nicename;
                            }
                            else
                            {
                                $xuser_name        = get_bloginfo( 'name' );
                            }
                            $xpost_link        = get_permalink($post->ID);
                            $ai_custom_tax_new = $ai_custom_tax;
                            $userid = false;
                            $date       = date('Y-m-d H:i:s', strtotime(current_time('mysql')));
                            if (isset($aiomatic_Spinner_Settings['no_custom_tax_prompt']) && $aiomatic_Spinner_Settings['no_custom_tax_prompt'] == 'on') 
                            {
                                $extra_tax = $ai_custom_field_new;
                            }
                            else
                            {
                                $query_token_count = count(aiomatic_encode($ai_custom_tax_new));
                                if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                                {
                                    $ai_custom_tax_new = aiomatic_substr($ai_custom_tax_new, 0, (0-($max_seed_tokens * 4)));
                                    $query_token_count = count(aiomatic_encode($ai_custom_tax_new));
                                }
                                $max_tokens = aiomatic_get_max_tokens($cmodel);
                                $available_tokens = aiomatic_compute_available_tokens($cmodel, $max_tokens, $ai_custom_tax_new, $query_token_count);
                                $max_result_tokens = aiomatic_get_max_tokens($cmodel);
                                if($available_tokens > $max_result_tokens)
                                {
                                    $available_tokens = $max_result_tokens;
                                }
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($ai_custom_tax_new);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $ai_custom_tax_new = aiomatic_substr($ai_custom_tax_new, 0, $string_len);
                                    $ai_custom_tax_new = trim($ai_custom_tax_new);
                                    if(empty($ai_custom_tax_new))
                                    {
                                        aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_custom_tax_new, true));
                                    }
                                    $query_token_count = count(aiomatic_encode($ai_custom_tax_new));
                                    $available_tokens = $max_tokens - $query_token_count;
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $cmodel);
                                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $custom_assistant_id . '\\' . $cmodel . ')(' . $available_tokens . ') post custom taxonomy generator, with seed command: ' . $ai_custom_tax_new);
                                }
                                $aierror = '';
                                $extra_tax = '';
                                $finish_reason = '';
                                $generated_text = aiomatic_generate_text($token, $cmodel, $ai_custom_tax_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'customTaxonomyGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $custom_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                                if($generated_text === false)
                                {
                                    aiomatic_log_to_file('Custom Taxonomy generator error: ' . $aierror);
                                }
                                else
                                {
                                    $extra_tax = ucfirst(trim(nl2br(trim($generated_text))));
                                    $extra_tax = str_replace('//', '', $extra_tax);
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($extra_tax) !== '') 
                                {
                                    $api_service = aiomatic_get_api_service($token, $cmodel);
                                    aiomatic_log_to_file($api_service . ' responded successfully, custom taxonomy generated for post ID ' . $post->ID . ': ' . $extra_tax);
                                }
                            }
                            if($extra_tax != '')
                            {
                                $extra_taxes = explode(',', $extra_tax);
                                foreach($extra_taxes as $my_extra_tax)
                                {
                                    if($added_tax >= $max_custom)
                                    {
                                        break;
                                    }
                                    $my_extra_tax = trim($my_extra_tax);
                                    $my_extra_tax = strip_tags($my_extra_tax);
                                    $my_extra_tax = preg_replace('#^\d+\.\s*#', '', $my_extra_tax);
                                    if(empty($my_extra_tax))
                                    {
                                        continue;
                                    }
                                    if (isset($aiomatic_Spinner_Settings['skip_inexist_custom']) && $aiomatic_Spinner_Settings['skip_inexist_custom'] == 'on') 
                                    {
                                        if(!term_exists($my_extra_tax, $ai_custom_tax_slug))
                                        {
                                            aiomatic_log_to_file('Skipping tag, as it is not found on this site: ' . $my_extra_tax);
                                            continue;
                                        }
                                    }
                                    $added_tax++;
                                    $termid = aiomatic_create_terms($ai_custom_tax_slug, null, trim($my_extra_tax));
                                    wp_set_post_terms($post->ID, $termid, $ai_custom_tax_slug, true);
                                }
                            }
                        }
                        if($added_tax > 0)
                        {
                            if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                            if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                            {
                                $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                if($date_stamp)
                                {
                                    $args = array();
                                    $args['ID'] = $post->ID;
                                    $postdate = date("Y-m-d H:i:s", $date_stamp);
                                    $args['post_date'] = $postdate;
                                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    remove_filter('title_save_pre', 'wp_filter_kses');
                                    update_post_meta($post->ID, $custom_name, "pub");
                                    $post_updated = wp_update_post($args);
                                    add_filter('content_save_pre', 'wp_filter_post_kses');
                                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                    add_filter('title_save_pre', 'wp_filter_kses');
                                    if (is_wp_error($post_updated)) {
                                        $errors = $post_updated->get_error_messages();
                                        foreach ($errors as $error) {
                                            aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                        }
                                    }
                                }
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated custom taxonomies.');
                            }
                        }
                    }
                }
                if (isset($aiomatic_Spinner_Settings['ai_custom_field']) && $aiomatic_Spinner_Settings['ai_custom_field'] != '')
                {
                    $ai_custom_field_arr = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['ai_custom_field']));
                    $added_field = 0;
                    foreach($ai_custom_field_arr as $ai_custom_field_full)
                    {
                        $ai_custom_field_full = trim($ai_custom_field_full);
                        if($ai_custom_field_full == '')
                        {
                            continue;
                        }
                        if(strstr($ai_custom_field_full, '=>') === false)
                        {
                            aiomatic_log_to_file('Invalid custom field creator format sent. Correct format is: custom_field_slug => AI prompt. Sent: ' . $ai_custom_field_full);
                            continue;
                        }
                        $ai_custom_field_parts = explode('=>', $ai_custom_field_full);
                        $ai_custom_field = trim($ai_custom_field_parts[1]);
                        $ai_custom_field_slug = trim($ai_custom_field_parts[0]);
                        if(empty($ai_custom_field))
                        {
                            aiomatic_log_to_file('Invalid custom field creator prompt sent. Correct format is: custom_field_slug => AI prompt. Sent: ' . $ai_custom_field_full);
                            continue;
                        }
                        if(empty($ai_custom_field_slug))
                        {
                            aiomatic_log_to_file('Invalid custom field creator slug sent. Correct format is: custom_field_slug => AI prompt. Sent: ' . $ai_custom_field_full);
                            continue;
                        }
                        $ai_custom_field = aiomatic_replaceSynergyShortcodes($ai_custom_field);
                        $post_link        = get_permalink($post->ID);
                        $blog_title       = html_entity_decode(get_bloginfo('title'));
                        $author_obj       = get_user_by('id', $post->post_author);
                        if($author_obj !== false && isset($author_obj->user_nicename))
                        {
                            $user_name        = $author_obj->user_nicename;
                        }
                        else
                        {
                            $user_name        = '';
                        }
                        $featured_image   = '';
                        wp_suspend_cache_addition(true);
                        $metas = get_post_custom($post->ID);
                        wp_suspend_cache_addition(false);
                        if(is_array($metas))
                        {
                            $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                        }
                        else
                        {
                            $rez_meta = array();
                        }
                        if(count($rez_meta) > 0)
                        {
                            foreach($rez_meta as $rm)
                            {
                                if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                                {
                                    $featured_image = $rm[0];
                                    break;
                                }
                            }
                        }
                        if($featured_image == '')
                        {
                            $featured_image = aiomatic_generate_thumbmail($post->ID);
                        }
                        if($featured_image == '' && $final_content != '')
                        {
                            $dom     = new DOMDocument();
                            $internalErrors = libxml_use_internal_errors(true);
                            $dom->loadHTML($final_content);
                            libxml_use_internal_errors($internalErrors);
                            $tags      = $dom->getElementsByTagName('img');
                            foreach ($tags as $tag) {
                                $temp_get_img = $tag->getAttribute('src');
                                if ($temp_get_img != '') {
                                    $temp_get_img = strtok($temp_get_img, '?');
                                    $featured_image = rtrim($temp_get_img, '/');
                                }
                            }
                        }
                        $post_cats = '';
                        $post_categories = wp_get_post_categories( $post->ID );
                        foreach($post_categories as $c){
                            $cat = get_category( $c );
                            $post_cats .= $cat->name . ',';
                        }
                        $post_cats = trim($post_cats, ',');
                        if($post_cats != '')
                        {
                            $post_categories = explode(',', $post_cats);
                        }
                        else
                        {
                            $post_categories = array();
                        }
                        if(count($post_categories) == 0)
                        {
                            $terms = get_the_terms( $post->ID, 'product_cat' );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                foreach ( $terms as $term ) {
                                    $post_categories[] = $term->slug;
                                }
                                $post_cats = implode(',', $post_categories);
                            }
                            
                        }
                        foreach($post_categories as $pc)
                        {
                            if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                                foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                                {
                                    if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                        }
                                        return;
                                    }
                                }
                            }
                        }
                        $post_tagz = '';
                        $post_tags = wp_get_post_tags( $post->ID );
                        foreach($post_tags as $t){
                            $post_tagz .= $t->name . ',';
                        }
                        $post_tagz = trim($post_tagz, ',');
                        if($post_tagz != '')
                        {
                            $post_tags = explode(',', $post_tagz);
                        }
                        else
                        {
                            $post_tags = array();
                        }
                        if(count($post_tags) == 0)
                        {
                            $terms = get_the_terms( $post->ID, 'product_tag' );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                foreach ( $terms as $term ) {
                                    $post_tags[] = $term->slug;
                                }
                                $post_tagz = implode(',', $post_tags);
                            }
                            
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                            
                            $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                            foreach($disable_users as $disable_user)
                            {
                                if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                    }
                                    return;
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                            
                            $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                            $found = false;
                            foreach($enable_users as $disable_user)
                            {
                                if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                {
                                    $found = true;
                                }
                            }
                            if($found === false)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                            if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                            {
                                if ( !has_post_thumbnail($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                            {
                                if ( has_post_thumbnail($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                        }
                        
                        if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                            if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                            {
                                if ( !aiomatic_check_seo_description($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                            {
                                if ( aiomatic_check_seo_description($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                            $skip_me = true;
                            $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                            foreach($enable_field as $fieldx)
                            {
                                if(strstr($fieldx, '=>') !== false)
                                {
                                    $fna = explode('=>', $fieldx);
                                    if(isset($fna[1]))
                                    {
                                        $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                        if ($custom_val) 
                                        {
                                            if(trim($fna[1])  == '*')
                                            {
                                                $skip_me = false;
                                            }
                                            if($custom_val == trim($fna[1]))
                                            {
                                                $skip_me = false;
                                            }
                                        }
                                    }
                                }
                            }
                            if($skip_me === true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                            $skip_me = false;
                            $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                            foreach($disable_field as $fieldx)
                            {
                                if(strstr($fieldx, '=>') !== false)
                                {
                                    $fna = explode('=>', $fieldx);
                                    if(isset($fna[1]))
                                    {
                                        $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                        if ($custom_val) 
                                        {
                                            if(trim($fna[1])  == '*')
                                            {
                                                $skip_me = true;
                                            }
                                            if($custom_val == trim($fna[1]))
                                            {
                                                $skip_me = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if($skip_me === true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        foreach($post_tags as $pt)
                        {
                            if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                                
                                $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                                foreach($disable_tags as $disabled_tag)
                                {
                                    if($manual != true && trim($pt) == trim($disabled_tag))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                        }
                                        return;
                                    }
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                            $skip = true;
                            $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                            foreach($post_tags as $pt)
                            {
                                foreach($enable_tags as $enable_tag)
                                {
                                    if(trim($pt) == trim($enable_tag))
                                    {
                                        $skip = false;
                                        break;
                                    }
                                }
                            }
                            if($skip == true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                                }
                                return;
                            }
                        }
                        $ai_custom_field = aiomatic_replaceAIPostShortcodes($ai_custom_field, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                        $ai_custom_field = trim($ai_custom_field);
                        if (isset($aiomatic_Spinner_Settings['custom_model']) && $aiomatic_Spinner_Settings['custom_model'] != '') {
                            $cmodel = $aiomatic_Spinner_Settings['custom_model'];
                        }
                        else
                        {
                            $cmodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                        }
                        if (isset($aiomatic_Spinner_Settings['custom_assistant_id']) && $aiomatic_Spinner_Settings['custom_assistant_id'] != '') {
                            $custom_assistant_id = $aiomatic_Spinner_Settings['custom_assistant_id'];
                        }
                        else
                        {
                            $custom_assistant_id = '';
                        }
                        preg_match_all('#\s*(\d+)\s*-\s*(\d+)\s*#', $max_custom, $mxatches);
                        if(isset($mxatches[2][0]))
                        {
                            $minx = $mxatches[1][0];
                            $maxx = $mxatches[2][0];
                            $max_custom = rand(intval($minx), intval($maxx));
                        }
                        else
                        {
                            $max_custom = intval($max_custom);
                        }
                        $author_obj       = get_user_by('id', $post->post_author);
                        if(isset($author_obj->user_nicename))
                        {
                            $xuser_name        = $author_obj->user_nicename;
                        }
                        else
                        {
                            $xuser_name        = get_bloginfo( 'name' );
                        }
                        $xpost_link        = get_permalink($post->ID);
                        $ai_custom_field_new = $ai_custom_field;
                        $userid = false;
                        $date       = date('Y-m-d H:i:s', strtotime(current_time('mysql')));
                        if (isset($aiomatic_Spinner_Settings['no_custom_field_prompt']) && $aiomatic_Spinner_Settings['no_custom_field_prompt'] == 'on') 
                        {
                            $extra_field = $ai_custom_field_new;
                        }
                        else
                        {
                            $query_token_count = count(aiomatic_encode($ai_custom_field_new));
                            if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                            {
                                $ai_custom_field_new = aiomatic_substr($ai_custom_field_new, 0, (0-($max_seed_tokens * 4)));
                                $query_token_count = count(aiomatic_encode($ai_custom_field_new));
                            }
                            $max_tokens = aiomatic_get_max_tokens($cmodel);
                            $available_tokens = aiomatic_compute_available_tokens($cmodel, $max_tokens, $ai_custom_field_new, $query_token_count);
                            $max_result_tokens = aiomatic_get_max_tokens($cmodel);
                            if($available_tokens > $max_result_tokens)
                            {
                                $available_tokens = $max_result_tokens;
                            }
                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                            {
                                $string_len = strlen($ai_custom_field_new);
                                $string_len = $string_len / 2;
                                $string_len = intval(0 - $string_len);
                                $ai_custom_field_new = aiomatic_substr($ai_custom_field_new, 0, $string_len);
                                $ai_custom_field_new = trim($ai_custom_field_new);
                                if(empty($ai_custom_field_new))
                                {
                                    aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_custom_field_new, true));
                                }
                                $query_token_count = count(aiomatic_encode($ai_custom_field_new));
                                $available_tokens = $max_tokens - $query_token_count;
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                            {
                                $api_service = aiomatic_get_api_service($token, $cmodel);
                                aiomatic_log_to_file('Calling ' . $api_service . ' (' . $custom_assistant_id . '\\' . $cmodel . ')(' . $available_tokens . ') post custom field generator, with seed command: ' . $ai_custom_field_new);
                            }
                            $aierror = '';
                            $extra_field = '';
                            $finish_reason = '';
                            $generated_text = aiomatic_generate_text($token, $cmodel, $ai_custom_field_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'customFieldGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $custom_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                            if($generated_text === false)
                            {
                                aiomatic_log_to_file('Custom Field generator error: ' . $aierror);
                            }
                            else
                            {
                                $extra_field = ucfirst(trim(nl2br(trim($generated_text))));
                                $extra_field = str_replace('//', '', $extra_field);
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($extra_field) !== '') 
                            {
                                $api_service = aiomatic_get_api_service($token, $cmodel);
                                aiomatic_log_to_file($api_service . ' responded successfully, custom field generated for post ID ' . $post->ID . ': ' . $extra_field);
                            }
                        }
                        if($extra_field != '')
                        {
                            $extra_field = trim($extra_field);
                            if(empty($extra_field))
                            {
                                continue;
                            }
                            $added_field++;
                            update_post_meta($post->ID, $ai_custom_field_slug, $extra_field);
                        }
                    }
                    if($added_field > 0)
                    {
                        if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                        }
                        if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                        {
                            $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                            if($date_stamp)
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $postdate = date("Y-m-d H:i:s", $date_stamp);
                                $args['post_date'] = $postdate;
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated custom fields.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['add_comments']) && $aiomatic_Spinner_Settings['add_comments'] != '' && $aiomatic_Spinner_Settings['add_comments'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post auto commenter...');
                }
                if (isset($aiomatic_Spinner_Settings['max_comments']) && $aiomatic_Spinner_Settings['max_comments'] != '')
                {
                    $max_comments = trim($aiomatic_Spinner_Settings['max_comments']);
                }
                else
                {
                    $max_comments = '1-2';
                }
                if($max_comments !== '')
                {
                    $vision_file = '';
                    if (isset($aiomatic_Spinner_Settings['ai_comments']) && $aiomatic_Spinner_Settings['ai_comments'] != '')
                    {
                        $ai_comments = $aiomatic_Spinner_Settings['ai_comments'];
                    }
                    else
                    {
                        $ai_comments = `Write a single comment (don't start a new line) for the post title: %%post_title%%
Previous comments are:
%%previous_comments%%
%%comment_author_name%%:`;
                    }
                    if (isset($aiomatic_Spinner_Settings['prev_comms']) && $aiomatic_Spinner_Settings['prev_comms'] != '')
                    {
                        $prev_comms = intval($aiomatic_Spinner_Settings['prev_comms']);
                    }
                    else
                    {
                        $prev_comms = 5;
                    }
                    $ai_comments = aiomatic_replaceSynergyShortcodes($ai_comments);
                    
                    if (isset($aiomatic_Spinner_Settings['ai_vision_com']) && $aiomatic_Spinner_Settings['ai_vision_com'] == 'on')
                    {
                        $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                        if($avatar !== false)
                        {
                            $vision_file = $avatar;
                        }
                    }
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $ai_comments = aiomatic_replaceAIPostShortcodes($ai_comments, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_comments = trim($ai_comments);
                    if (isset($aiomatic_Spinner_Settings['url_list']) && $aiomatic_Spinner_Settings['url_list'] != '') {
                        $blog_url_list = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['url_list']));
                    }
                    else
                    {
                        $blog_url_list = array('');
                    }
                    if (isset($aiomatic_Spinner_Settings['user_list']) && $aiomatic_Spinner_Settings['user_list'] != '') {
                        $blog_user_list = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['user_list']));
                    }
                    else
                    {
                        $blog_user_list = array('%%random_user%%');
                    }
                    if (isset($aiomatic_Spinner_Settings['email_list']) && $aiomatic_Spinner_Settings['email_list'] != '') {
                        $blog_email_list = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['email_list']));
                    }
                    else
                    {
                        $blog_email_list = array(aiomatic_get_random_word(4, 10) . '@' . aiomatic_get_random_word(4, 8) . '.com');
                    }
                    if (isset($aiomatic_Spinner_Settings['comments_model']) && $aiomatic_Spinner_Settings['comments_model'] != '') {
                        $cmodel = $aiomatic_Spinner_Settings['comments_model'];
                    }
                    else
                    {
                        $cmodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                    }
                    if (isset($aiomatic_Spinner_Settings['comments_assistant_id']) && $aiomatic_Spinner_Settings['comments_assistant_id'] != '') {
                        $comments_assistant_id = $aiomatic_Spinner_Settings['comments_assistant_id'];
                    }
                    else
                    {
                        $comments_assistant_id = '';
                    }
                    preg_match_all('#\s*(\d+)\s*-\s*(\d+)\s*#', $max_comments, $mxatches);
                    if(isset($mxatches[2][0]))
                    {
                        $minx = $mxatches[1][0];
                        $maxx = $mxatches[2][0];
                        $max_comments = rand(intval($minx), intval($maxx));
                    }
                    else
                    {
                        $max_comments = intval($max_comments);
                    }
                    $author_obj       = get_user_by('id', $post->post_author);
                    if(isset($author_obj->user_login))
                    {
                        $xuser_name        = $author_obj->user_login;
                    }
                    else
                    {
                        $xuser_name        = get_bloginfo( 'name' );
                    }
                    $xpost_link        = get_permalink($post->ID);
                    $comms_add = false;
                    for($i = 0; $i < $max_comments; $i++)
                    {
                        $star_count = '';
                        if (isset($aiomatic_Spinner_Settings['star_count']) && $aiomatic_Spinner_Settings['star_count'] != '') 
                        {
                            $star_count = $aiomatic_Spinner_Settings['star_count'];
                        }
                        if(strstr($star_count, '-') !== false)
                        {
                            list($min, $max) = explode('-', $star_count);
                            if(is_numeric($min) && is_numeric($max) && intval($min) <= intval($max))
                            {
                                $min = (int)trim($min);
                                $max = (int)trim($max);
                                $star_count = rand($min, $max);
                            }
                            else
                            {
                                $star_count = '';
                            }
                        }
                        else
                        {
                            if(is_numeric($star_count))
                            {
                                $star_count = intval($star_count);
                            }
                            else
                            {
                                $star_count = '';
                            }
                        }
                        $comms_add = true;
                        $userid = 0;
                        $comm_url   = trim($blog_url_list[array_rand($blog_url_list)]);
                        $cauthor     = trim($blog_user_list[array_rand($blog_user_list)]);
                        $cmail     = trim($blog_email_list[array_rand($blog_email_list)]);
                        $cauthor = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $cauthor);
                        $cauthor = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $cauthor);
                        $cauthor = str_replace('%%author_name%%', $xuser_name, $cauthor);
                        if(strstr($cauthor, '%%random_new_name%%') !== false)
                        {
                            $cauthor = aiomatic_generateComplexRandomName();
                        }
                        elseif(strstr($cauthor, '%%random_user%%') !== false)
                        {
                            global $wpdb;
                            $random_user_id = $wpdb->get_var( "
                                SELECT ID 
                                FROM {$wpdb->users} 
                                ORDER BY RAND() 
                                LIMIT 1
                            " );
                            if ( $random_user_id ) 
                            {
                                $userid = $random_user_id;
                                $xuser = get_user_by( 'id', $userid );
                                if($xuser !== false)
                                {
                                    $cauthor = str_replace('%%random_user%%', $xuser->display_name, $cauthor);
                                }
                                else
                                {
                                    $cauthor = str_replace('%%random_user%%', '', $cauthor);
                                }
                            }
                        }
                        else
                        {
                            $user = get_user_by('login', $cauthor);
                            if($user !== false)
                            {
                                $userid = $user->ID;
                            }
                        }
                        $cmail = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $cmail);
                        $cmail = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $cmail);
                        $comm_url = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $comm_url);
                        $comm_url = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $comm_url);
                        $comm_url = str_replace('%%post_link%%', $xpost_link, $comm_url);
                        if (isset($aiomatic_Spinner_Settings['max_time']) && $aiomatic_Spinner_Settings['max_time'] != '' && isset($aiomatic_Spinner_Settings['min_time']) && $aiomatic_Spinner_Settings['min_time'] != '') 
                        {
                            $t1 = strtotime($aiomatic_Spinner_Settings['min_time']);
                            $t2 = strtotime($aiomatic_Spinner_Settings['max_time']);
                            if($t1 !== false && $t2 !== false)
                            {
                                $int = rand($t1, $t2);
                                $date = date('Y-m-d H:i:s', $int);
                                $gdate = gmdate('Y-m-d H:i:s', $int);
                            }
                            else
                            {
                                $date = date('Y-m-d H:i:s');
                                $gdate = gmdate('Y-m-d H:i:s');
                            }
                        }
                        else
                        {
                            $date = date('Y-m-d H:i:s');
                            $gdate = gmdate('Y-m-d H:i:s');
                        }
                        $ai_comments_new = str_replace('%%comment_author_name%%', $cauthor, $ai_comments);
                        $ai_comments_new = str_replace('%%comment_author_email%%', $cmail, $ai_comments_new);
                        $ai_comments_new = str_replace('%%comment_author_url%%', $comm_url, $ai_comments_new);
                        $ai_comments_new = str_replace('%%product_star_rating%%', $star_count, $ai_comments_new);
                        if(strstr($ai_comments_new, '%%previous_comments%%') !== false)
                        {
                            $comments = get_comments(array(
                                'post_id' => $post->ID,
                                'number' => $prev_comms,
                                'orderby' => 'comment_date',
                                'order' => 'DESC'
                            ));
                            $older_comms = '';
                            foreach($comments as $comment) {
                                $older_comms .= $comment->comment_author . ': ' . $comment->comment_content . '\r\n';
                            }
                            $ai_comments_new = str_replace('%%previous_comments%%', $older_comms, $ai_comments_new);
                        }
                        $query_token_count = count(aiomatic_encode($ai_comments_new));
                        if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                        {
                            $ai_comments_new = aiomatic_substr($ai_comments_new, 0, (0-($max_seed_tokens * 4)));
                            $query_token_count = count(aiomatic_encode($ai_comments_new));
                        }
                        $max_tokens = aiomatic_get_max_tokens($cmodel);
                        $available_tokens = aiomatic_compute_available_tokens($cmodel, $max_tokens, $ai_comments_new, $query_token_count);
                        $max_result_tokens = aiomatic_get_max_tokens($cmodel);
                        if($available_tokens > $max_result_tokens)
                        {
                            $available_tokens = $max_result_tokens;
                        }
                        if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                        {
                            $string_len = strlen($ai_comments_new);
                            $string_len = $string_len / 2;
                            $string_len = intval(0 - $string_len);
                            $ai_comments_new = aiomatic_substr($ai_comments_new, 0, $string_len);
                            $ai_comments_new = trim($ai_comments_new);
                            if(empty($ai_comments_new))
                            {
                                aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_comments_new, true));
                            }
                            $query_token_count = count(aiomatic_encode($ai_comments_new));
                            $available_tokens = $max_tokens - $query_token_count;
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                        {
                            $api_service = aiomatic_get_api_service($token, $cmodel);
                            aiomatic_log_to_file('Calling ' . $api_service . ' (' . $comments_assistant_id , '/' . $cmodel . ')(' . $available_tokens . ') post comment generator, with seed command: ' . $ai_comments_new);
                        }
                        $aierror = '';
                        $comm = '';
                        $finish_reason = '';
                        $generated_text = aiomatic_generate_text($token, $cmodel, $ai_comments_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'commentGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $comments_assistant_id, $thread_id, '', 'disabled', '', true, $store_data, array());
                        if($generated_text === false)
                        {
                            aiomatic_log_to_file('Comment generator error: ' . $aierror);
                        }
                        else
                        {
                            $comm = ucfirst(trim(nl2br(trim($generated_text))));
                            $comm = str_replace('//', '', $comm);
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($comm) !== '') 
                        {
                            $api_service = aiomatic_get_api_service($token, $cmodel);
                            aiomatic_log_to_file($api_service . ' responded successfully, comment generated for post ID: ' . $post->ID);
                        }
                        $comment_type = '';
                        if(!empty($star_count) && $post->post_type == 'product')
                        {
                            $comment_type = 'review';
                        }
                        if (trim($comm) != '') 
                        {
                            $data = array(
                                'comment_post_ID' => $post->ID,
                                'comment_author' => $cauthor,
                                'comment_author_email' => $cmail,
                                'comment_author_url' => $comm_url,
                                'comment_content' => $comm,
                                'comment_type' => $comment_type,
                                'comment_parent' => 0,
                                'user_id' => $userid,
                                'comment_author_IP' => aiomatic_generateRandomIP(),
                                'comment_agent' => aiomatic_get_random_user_agent(),
                                'comment_date' => $date,
                                'comment_date_gmt' => $gdate
                            );
                            if (isset($aiomatic_Spinner_Settings['no_approve']) && $aiomatic_Spinner_Settings['no_approve'] == 'on') 
                            {
                                $data['comment_approved'] = 0;
                            }
                            else
                            {
                                $data['comment_approved'] = 1;
                            }
                            $comment_id = wp_insert_comment($data);
                            if($comment_id === false)
                            {
                                aiomatic_log_to_file('Failed to insert comment to post ID: ' . $post->ID);
                            }
                            else
                            {
                                update_post_meta($post->ID, $custom_name, "pub");
                                if(!empty($star_count) && $post->post_type == 'product')
                                {
                                    update_comment_meta($comment_id, 'rating', $star_count);
                                }
                            }
                        }
                    }
                    if($comms_add == true)
                    {
                        if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                        }
                        if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                        {
                            $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                            if($date_stamp)
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $postdate = date("Y-m-d H:i:s", $date_stamp);
                                $args['post_date'] = $postdate;
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated comments.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['add_seo']) && $aiomatic_Spinner_Settings['add_seo'] != '' && $aiomatic_Spinner_Settings['add_seo'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post SEO writer...');
                }
                if (isset($aiomatic_Spinner_Settings['seo_copy_excerpt']) && $aiomatic_Spinner_Settings['seo_copy_excerpt'] == 'on')
                {
                    if(!empty($post->post_excerpt))
                    {
                        $comm = $post->post_excerpt;
                        if (trim($comm) != '') {
                            if (isset($aiomatic_Spinner_Settings['seo_max_char']) && $aiomatic_Spinner_Settings['seo_max_char'] != '')
                            {
                                $comm = substr($comm, 0, intval($aiomatic_Spinner_Settings['seo_max_char']));
                            } 
                            aiomatic_save_seo_description($post->ID, trim($comm), '');
                            update_post_meta($post->ID, $custom_name, "pub");
                        }
                    }
                }
                else
                {
                    $vision_file = '';
                    if (isset($aiomatic_Spinner_Settings['ai_seo']) && $aiomatic_Spinner_Settings['ai_seo'] != '')
                    {
                        $ai_seo = $aiomatic_Spinner_Settings['ai_seo'];
                    }
                    else
                    {
                        $ai_seo = 'Write a SEO meta description for the post title: %%post_title%%';
                    }
                    if (isset($aiomatic_Spinner_Settings['ai_vision_seo']) && $aiomatic_Spinner_Settings['ai_vision_seo'] == 'on')
                    {
                        $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                        if($avatar !== false)
                        {
                            $vision_file = $avatar;
                        }
                    }
                    $ai_seo = aiomatic_replaceSynergyShortcodes($ai_seo);
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $ai_seo = aiomatic_replaceAIPostShortcodes($ai_seo, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_seo = trim($ai_seo);
                    if (isset($aiomatic_Spinner_Settings['seo_model']) && $aiomatic_Spinner_Settings['seo_model'] != '') {
                        $seo_model = $aiomatic_Spinner_Settings['seo_model'];
                    }
                    else
                    {
                        $seo_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                    }
                    if (isset($aiomatic_Spinner_Settings['meta_assistant_id']) && $aiomatic_Spinner_Settings['meta_assistant_id'] != '') {
                        $meta_assistant_id = $aiomatic_Spinner_Settings['meta_assistant_id'];
                    }
                    else
                    {
                        $meta_assistant_id = '';
                    }
                    $xpost_link        = get_permalink($post->ID);
                    $date       = date('Y-m-d H:i:s', strtotime(current_time('mysql')));
                    $ai_seo_new = $ai_seo;
                    $query_token_count = count(aiomatic_encode($ai_seo_new));
                    if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                    {
                        $ai_seo_new = aiomatic_substr($ai_seo_new, 0, (0-($max_seed_tokens * 4)));
                        $query_token_count = count(aiomatic_encode($ai_seo_new));
                    }
                    $max_tokens = aiomatic_get_max_tokens($seo_model);
                    $available_tokens = aiomatic_compute_available_tokens($seo_model, $max_tokens, $ai_seo_new, $query_token_count);
                    $max_result_tokens = aiomatic_get_max_tokens($seo_model);
                    if($available_tokens > $max_result_tokens)
                    {
                        $available_tokens = $max_result_tokens;
                    }
                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                    {
                        $string_len = strlen($ai_seo_new);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $ai_seo_new = aiomatic_substr($ai_seo_new, 0, $string_len);
                        $ai_seo_new = trim($ai_seo_new);
                        if(empty($ai_seo_new))
                        {
                            aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_seo_new, true));
                        }
                        $query_token_count = count(aiomatic_encode($ai_seo_new));
                        $available_tokens = $max_tokens - $query_token_count;
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                    {
                        $api_service = aiomatic_get_api_service($token, $seo_model);
                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $seo_model . ')(' . $available_tokens . ') post SEO meta generator, with seed command: ' . $ai_seo_new);
                    }
                    $aierror = '';
                    $comm = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $seo_model, $ai_seo_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'seoMetaGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $meta_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('SEO meta generator error: ' . $aierror);
                    }
                    else
                    {
                        $comm = ucfirst(trim(nl2br(trim($generated_text))));
                        $comm = str_replace('//', '', $comm);
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($comm) !== '') 
                    {
                        $api_service = aiomatic_get_api_service($token, $seo_model);
                        aiomatic_log_to_file($api_service . ' responded successfully, SEO meta description generated for post ID: ' . $post->ID);
                    }
                    if (trim($comm) != '') {
                        if (isset($aiomatic_Spinner_Settings['seo_max_char']) && $aiomatic_Spinner_Settings['seo_max_char'] != '')
                        {
                            $comm = substr($comm, 0, intval($aiomatic_Spinner_Settings['seo_max_char']));
                        } 
                        aiomatic_save_seo_description($post->ID, trim($comm), '');
                        update_post_meta($post->ID, $custom_name, "pub");
                        if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                        }
                        if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                        {
                            $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                            if($date_stamp)
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $postdate = date("Y-m-d H:i:s", $date_stamp);
                                $args['post_date'] = $postdate;
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated SEO meta description.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['add_seo_title']) && $aiomatic_Spinner_Settings['add_seo_title'] != '' && $aiomatic_Spinner_Settings['add_seo_title'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post SEO title writer...');
                }
                {
                    $vision_file = '';
                    if (isset($aiomatic_Spinner_Settings['ai_seo_title']) && $aiomatic_Spinner_Settings['ai_seo_title'] != '')
                    {
                        $ai_seo = $aiomatic_Spinner_Settings['ai_seo_title'];
                    }
                    else
                    {
                        $ai_seo = 'Write a SEO meta title for the post title: %%post_title%%';
                    }
                    if (isset($aiomatic_Spinner_Settings['ai_vision_seo_title']) && $aiomatic_Spinner_Settings['ai_vision_seo_title'] == 'on')
                    {
                        $avatar = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
                        if($avatar !== false)
                        {
                            $vision_file = $avatar;
                        }
                    }
                    $ai_seo = aiomatic_replaceSynergyShortcodes($ai_seo);
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $ai_seo = aiomatic_replaceAIPostShortcodes($ai_seo, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    $ai_seo = trim($ai_seo);
                    if (isset($aiomatic_Spinner_Settings['seo_model_title']) && $aiomatic_Spinner_Settings['seo_model_title'] != '') {
                        $seo_model = $aiomatic_Spinner_Settings['seo_model_title'];
                    }
                    else
                    {
                        $seo_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                    }
                    if (isset($aiomatic_Spinner_Settings['meta_assistant_id_title']) && $aiomatic_Spinner_Settings['meta_assistant_id_title'] != '') {
                        $meta_assistant_id = $aiomatic_Spinner_Settings['meta_assistant_id_title'];
                    }
                    else
                    {
                        $meta_assistant_id = '';
                    }
                    $xpost_link        = get_permalink($post->ID);
                    $date       = date('Y-m-d H:i:s', strtotime(current_time('mysql')));
                    $ai_seo_new = $ai_seo;
                    $query_token_count = count(aiomatic_encode($ai_seo_new));
                    if(!empty($max_seed_tokens) && $query_token_count > $max_seed_tokens)
                    {
                        $ai_seo_new = aiomatic_substr($ai_seo_new, 0, (0-($max_seed_tokens * 4)));
                        $query_token_count = count(aiomatic_encode($ai_seo_new));
                    }
                    $max_tokens = aiomatic_get_max_tokens($seo_model);
                    $available_tokens = aiomatic_compute_available_tokens($seo_model, $max_tokens, $ai_seo_new, $query_token_count);
                    $max_result_tokens = aiomatic_get_max_tokens($seo_model);
                    if($available_tokens > $max_result_tokens)
                    {
                        $available_tokens = $max_result_tokens;
                    }
                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                    {
                        $string_len = strlen($ai_seo_new);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $ai_seo_new = aiomatic_substr($ai_seo_new, 0, $string_len);
                        $ai_seo_new = trim($ai_seo_new);
                        if(empty($ai_seo_new))
                        {
                            aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($ai_seo_new, true));
                        }
                        $query_token_count = count(aiomatic_encode($ai_seo_new));
                        $available_tokens = $max_tokens - $query_token_count;
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                    {
                        $api_service = aiomatic_get_api_service($token, $seo_model);
                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $seo_model . ')(' . $available_tokens . ') post SEO meta generator, with seed command: ' . $ai_seo_new);
                    }
                    $aierror = '';
                    $comm = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $seo_model, $ai_seo_new, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'seoMetaGenerator', 0, $finish_reason, $aierror, true, false, false, $vision_file, '', 'user', $meta_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('SEO meta generator error: ' . $aierror);
                    }
                    else
                    {
                        $comm = ucfirst(trim(nl2br(trim($generated_text))));
                        $comm = str_replace('//', '', $comm);
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && trim($comm) !== '') 
                    {
                        $api_service = aiomatic_get_api_service($token, $seo_model);
                        aiomatic_log_to_file($api_service . ' responded successfully, SEO meta title generated for post ID: ' . $post->ID);
                    }
                    if (trim($comm) != '') {
                        if (isset($aiomatic_Spinner_Settings['seo_max_char_title']) && $aiomatic_Spinner_Settings['seo_max_char_title'] != '')
                        {
                            $comm = substr($comm, 0, intval($aiomatic_Spinner_Settings['seo_max_char_title']));
                        } 
                        aiomatic_save_seo_title($post->ID, trim($comm));
                        update_post_meta($post->ID, $custom_name, "pub");
                        if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                        }
                        if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                        {
                            $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                            if($date_stamp)
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $postdate = date("Y-m-d H:i:s", $date_stamp);
                                $args['post_date'] = $postdate;
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated SEO meta title.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['ai_featured_image']) && $aiomatic_Spinner_Settings['ai_featured_image'] != '' && $aiomatic_Spinner_Settings['ai_featured_image'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post featured image generator...');
                }
                update_post_meta($post->ID, $custom_name, "pub");
                if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                {
                    $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                }
                else
                {
                    $image_size = '1024x1024';
                }
                if (isset($aiomatic_Spinner_Settings['image_model']) && trim($aiomatic_Spinner_Settings['image_model']) != '')
                {
                    $image_model = trim($aiomatic_Spinner_Settings['image_model']);
                }
                else
                {
                    $image_model = 'dalle2';
                }
                if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                {
                    $aicontent = trim(strip_tags($aiomatic_Spinner_Settings['ai_image_command']));
                    $aicontent = aiomatic_replaceSynergyShortcodes($aicontent);
                    $post_link = get_permalink($post->ID);
                    $blog_title       = html_entity_decode(get_bloginfo('title'));
                    $author_obj       = get_user_by('id', $post->post_author);
                    if($author_obj !== false && isset($author_obj->user_nicename))
                    {
                        $user_name        = $author_obj->user_nicename;
                    }
                    else
                    {
                        $user_name        = '';
                    }
                    $featured_image   = '';
                    wp_suspend_cache_addition(true);
                    $metas = get_post_custom($post->ID);
                    wp_suspend_cache_addition(false);
                    if(is_array($metas))
                    {
                        $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                    }
                    else
                    {
                        $rez_meta = array();
                    }
                    if(count($rez_meta) > 0)
                    {
                        foreach($rez_meta as $rm)
                        {
                            if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                            {
                                $featured_image = $rm[0];
                                break;
                            }
                        }
                    }
                    if($featured_image == '')
                    {
                        $featured_image = aiomatic_generate_thumbmail($post->ID);
                    }
                    if($featured_image == '' && $final_content != '')
                    {
                        $dom     = new DOMDocument();
                        $internalErrors = libxml_use_internal_errors(true);
                        $dom->loadHTML($final_content);
                        libxml_use_internal_errors($internalErrors);
                        $tags      = $dom->getElementsByTagName('img');
                        foreach ($tags as $tag) {
                            $temp_get_img = $tag->getAttribute('src');
                            if ($temp_get_img != '') {
                                $temp_get_img = strtok($temp_get_img, '?');
                                $featured_image = rtrim($temp_get_img, '/');
                            }
                        }
                    }
                    $post_cats = '';
                    $post_categories = wp_get_post_categories( $post->ID );
                    foreach($post_categories as $c){
                        $cat = get_category( $c );
                        $post_cats .= $cat->name . ',';
                    }
                    $post_cats = trim($post_cats, ',');
                    if($post_cats != '')
                    {
                        $post_categories = explode(',', $post_cats);
                    }
                    else
                    {
                        $post_categories = array();
                    }
                    if(count($post_categories) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_cat' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_categories[] = $term->slug;
                            }
                            $post_cats = implode(',', $post_categories);
                        }
                        
                    }
                    foreach($post_categories as $pc)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                            foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                            {
                                if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    $post_tagz = '';
                    $post_tags = wp_get_post_tags( $post->ID );
                    foreach($post_tags as $t){
                        $post_tagz .= $t->name . ',';
                    }
                    $post_tagz = trim($post_tagz, ',');
                    if($post_tagz != '')
                    {
                        $post_tags = explode(',', $post_tagz);
                    }
                    else
                    {
                        $post_tags = array();
                    }
                    if(count($post_tags) == 0)
                    {
                        $terms = get_the_terms( $post->ID, 'product_tag' );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                            foreach ( $terms as $term ) {
                                $post_tags[] = $term->slug;
                            }
                            $post_tagz = implode(',', $post_tags);
                        }
                        
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                        
                        $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                        foreach($disable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                        
                        $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                        $found = false;
                        foreach($enable_users as $disable_user)
                        {
                            if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                            {
                                $found = true;
                            }
                        }
                        if($found === false)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                        {
                            if ( !has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                        {
                            if ( has_post_thumbnail($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    
                    if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                        if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                        {
                            if ( !aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                        {
                            if ( aiomatic_check_seo_description($post->ID) ) 
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                }
                                return;
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                        $skip_me = true;
                        $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                        foreach($enable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = false;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = false;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                        $skip_me = false;
                        $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                        foreach($disable_field as $fieldx)
                        {
                            if(strstr($fieldx, '=>') !== false)
                            {
                                $fna = explode('=>', $fieldx);
                                if(isset($fna[1]))
                                {
                                    $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                    if ($custom_val) 
                                    {
                                        if(trim($fna[1])  == '*')
                                        {
                                            $skip_me = true;
                                        }
                                        if($custom_val == trim($fna[1]))
                                        {
                                            $skip_me = true;
                                        }
                                    }
                                }
                            }
                        }
                        if($skip_me === true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                            }
                            return;
                        }
                    }
                    foreach($post_tags as $pt)
                    {
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                            
                            $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                            foreach($disable_tags as $disabled_tag)
                            {
                                if($manual != true && trim($pt) == trim($disabled_tag))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                    }
                                    return;
                                }
                            }
                        }
                    }
                    if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                        $skip = true;
                        $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                        foreach($post_tags as $pt)
                        {
                            foreach($enable_tags as $enable_tag)
                            {
                                if(trim($pt) == trim($enable_tag))
                                {
                                    $skip = false;
                                    break;
                                }
                            }
                        }
                        if($skip == true)
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                            }
                            return;
                        }
                    }
                    $aicontent = aiomatic_replaceAIPostShortcodes($aicontent, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                }
                else
                {
                    $aicontent = trim(strip_tags($final_content));
                    if(empty($aicontent))
                    {
                        $aicontent = trim(strip_tags($post->post_excerpt));
                    }
                    if(empty($aicontent))
                    {
                        $aicontent = trim(strip_tags($post_title));
                        $last_char = aiomatic_substr($aicontent, -1, null);
                        if(!ctype_punct($last_char))
                        {
                            $aicontent .= '.';
                        }
                    }
                }
                if(isset($aiomatic_Spinner_Settings['ai_featured_image_source']) && $aiomatic_Spinner_Settings['ai_featured_image_source'] != '')
                {
                    $fisource = $aiomatic_Spinner_Settings['ai_featured_image_source'];
                }
                else
                {
                    $fisource = '1';
                }
                $img_saved = false;
                if($fisource == '1')
                {
                    $aicontent = trim($aicontent);
                    if(strlen($aicontent) > 400)
                    {
                        $aicontent = aiomatic_substr($aicontent, 0, 400);
                    }
                    $skip_this_copy = true;
                    if(!function_exists('is_plugin_active'))
                    {
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    }
                    if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                    {
                        $skip_this_copy = false;
                    }
                    $aierror = '';
                    $temp_get_imgs = aiomatic_generate_ai_image($token, 1, $aicontent, $image_size, 'editFeaturedImage', $skip_this_copy, 0, $aierror, $image_model);
                    if($temp_get_imgs !== false)
                    {
                        foreach($temp_get_imgs as $tmpimg)
                        {
                            if (!aiomatic_generate_featured_image($tmpimg, $post->ID)) {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('aiomatic_generate_featured_image failed using OpenAI/AiomaticAPI for ' . $tmpimg);
                                }
                            }
                            else
                            {
                                $img_saved = true;
                            }
                            break;
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to generated a featured image using OpenAI/AiomaticAPI: ' . $aierror);
                        }
                    }
                }
                elseif($fisource == '2')
                {
                    $aicontent = trim($aicontent);
                    if(strlen($aicontent) > 2000)
                    {
                        $aicontent = aiomatic_substr($aicontent, 0, 2000);
                    }
                    if($image_size == '256x256')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '512x512')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '1024x1024')
                    {
                        $width = '1024';
                        $height = '1024';
                    }
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    $skip_this_copy = true;
                    if(!function_exists('is_plugin_active'))
                    {
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    }
                    if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                    {
                        $skip_this_copy = false;
                    }
                    $ierror = '';
                    $temp_get_imgs = aiomatic_generate_stability_image($aicontent, $height, $width, 'editorFeaturedStableImage', 0, false, $ierror, $skip_this_copy, false, '', 0);
                    if($temp_get_imgs !== false)
                    {
                        $temp_get_img_local = $temp_get_imgs[0];
                        if (!aiomatic_assign_featured_image_path($temp_get_img_local, $post->ID)) {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('aiomatic_assign_featured_image_path failed using Stability.AI for ' .$temp_get_imgs[1]);
                            }
                        }
                        else
                        {
                            $img_saved = true;
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to generated a featured image using Stability.AI: ' . $ierror);
                        }
                    }
                }
                elseif($fisource == '7')
                {
                    $aicontent = trim($aicontent);
                    if(strlen($aicontent) > 2000)
                    {
                        $aicontent = aiomatic_substr($aicontent, 0, 2000);
                    }
                    $skip_this_copy = true;
                    if(!function_exists('is_plugin_active'))
                    {
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    }
                    if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                    {
                        $skip_this_copy = false;
                    }
                    if (isset($aiomatic_Main_Settings['google_ai_image_model']) && trim($aiomatic_Main_Settings['google_ai_image_model']) != '') 
                    {
                        $model = trim($aiomatic_Main_Settings['google_ai_image_model']);
                    }
                    else
                    {
                        $model = AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL;
                    }
                    $ierror = '';
                    $temp_get_imgs = aiomatic_generate_image_google($aicontent, $model, 'editorFeaturedGoogleImage', false, $ierror, false);
                    if($temp_get_imgs !== false)
                    {
                        $temp_get_img_local = $temp_get_imgs[0];
                        if (!aiomatic_assign_featured_image_path($temp_get_img_local, $post->ID)) {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('aiomatic_assign_featured_image_path failed using Stability.AI for ' .$temp_get_imgs[1]);
                            }
                        }
                        else
                        {
                            $img_saved = true;
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to generated a featured image using Stability.AI: ' . $ierror);
                        }
                    }
                }
                elseif($fisource == '4')
                {
                    $aicontent = trim($aicontent);
                    if(strlen($aicontent) > 2000)
                    {
                        $aicontent = aiomatic_substr($aicontent, 0, 2000);
                    }
                    if($image_size == '256x256')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '512x512')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '1024x1024')
                    {
                        $width = '1024';
                        $height = '1024';
                    }
                    elseif($image_size == '1792x1024')
                    {
                        $width = '1792';
                        $height = '1024';
                    }
                    elseif($image_size == '1024x1792')
                    {
                        $width = '1024';
                        $height = '1792';
                    }
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    $skip_this_copy = true;
                    if(!function_exists('is_plugin_active'))
                    {
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    }
                    if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                    {
                        $skip_this_copy = false;
                    }
                    $ierror = '';
                    $temp_get_imgs = aiomatic_generate_ai_image_midjourney($aicontent, $width, $height, 'editorFeaturedMidjourneyImage', $skip_this_copy, $ierror);
                    if($temp_get_imgs !== false)
                    {
                        $temp_get_img_local = $temp_get_imgs;
                        if (!aiomatic_assign_featured_image_path($temp_get_img_local, $post->ID)) {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('aiomatic_assign_featured_image_path failed using Midjourney for ' .$temp_get_imgs);
                            }
                        }
                        else
                        {
                            $img_saved = true;
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to generated a featured image using Midjourney: ' . $ierror);
                        }
                    }
                }
                elseif($fisource == '5')
                {
                    $aicontent = trim($aicontent);
                    if(strlen($aicontent) > 2000)
                    {
                        $aicontent = aiomatic_substr($aicontent, 0, 2000);
                    }
                    if($image_size == '256x256')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '512x512')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '1024x1024')
                    {
                        $width = '1024';
                        $height = '1024';
                    }
                    elseif($image_size == '1792x1024')
                    {
                        $width = '1792';
                        $height = '1024';
                    }
                    elseif($image_size == '1024x1792')
                    {
                        $width = '1024';
                        $height = '1792';
                    }
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    $skip_this_copy = true;
                    if(!function_exists('is_plugin_active'))
                    {
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    }
                    if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                    {
                        $skip_this_copy = false;
                    }
                    $ierror = '';
                    $temp_get_imgs = aiomatic_generate_replicate_image($aicontent, $width, $height, 'editorFeaturedReplicateImage', $skip_this_copy, $ierror);
                    if($temp_get_imgs !== false)
                    {
                        $temp_get_img_local = $temp_get_imgs;
                        if (!aiomatic_assign_featured_image_path($temp_get_img_local, $post->ID)) {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('aiomatic_assign_featured_image_path failed using Replicate for ' .$temp_get_imgs);
                            }
                        }
                        else
                        {
                            $img_saved = true;
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to generated a featured image using Replicate: ' . $ierror);
                        }
                    }
                }
                elseif($fisource == '6')
                {
                    $aicontent = trim($aicontent);
                    if(strlen($aicontent) > 2000)
                    {
                        $aicontent = aiomatic_substr($aicontent, 0, 2000);
                    }
                    if($image_size == '256x256')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '512x512')
                    {
                        $width = '512';
                        $height = '512';
                    }
                    elseif($image_size == '1024x1024')
                    {
                        $width = '1024';
                        $height = '1024';
                    }
                    elseif($image_size == '1792x1024')
                    {
                        $width = '1792';
                        $height = '1024';
                    }
                    elseif($image_size == '1024x1792')
                    {
                        $width = '1024';
                        $height = '1792';
                    }
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    $skip_this_copy = true;
                    if(!function_exists('is_plugin_active'))
                    {
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    }
                    if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                    {
                        $skip_this_copy = false;
                    }
                    $ierror = '';
                    $temp_get_imgs = aiomatic_generate_ideogram_image($aicontent, $width, $height, 'editorFeaturedIdeogramImage', $skip_this_copy, $ierror);
                    if($temp_get_imgs !== false)
                    {
                        $temp_get_img_local = $temp_get_imgs;
                        if (!aiomatic_assign_featured_image_path($temp_get_img_local, $post->ID)) {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('aiomatic_assign_featured_image_path failed using Ideogram for ' .$temp_get_imgs);
                            }
                        }
                        else
                        {
                            $img_saved = true;
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to generated a featured image using Ideogram: ' . $ierror);
                        }
                    }
                }
                elseif($fisource == '3')
                {
                    if(isset($aiomatic_Spinner_Settings['url_image_list']) && $aiomatic_Spinner_Settings['url_image_list'] != '')
                    {
                        $url_image_list = $aiomatic_Spinner_Settings['url_image_list'];
                    }
                    else
                    {
                        $url_image_list = '';
                    }
                    if ($url_image_list != '') 
                    {
                        $zget_img = '';
                        $zreplacement = str_replace(array('[', ']'), '', $post->post_title);
                        $image_url_temp = str_replace('%%item_title%%', $zreplacement, $url_image_list);
                        $image_url_temp = preg_replace_callback('#%%random_image\[([^\]]*?)\](\[\d+\])?%%#', function ($matches) {
                            if(isset($matches[2]))
                            {
                                $chance = trim($matches[2], '[]');
                            }
                            else
                            {
                                $chance = '';
                            }
                            $arv = array();
                            $my_img = aiomatic_get_random_image_google($matches[1], 0, 0, $chance, $arv);
                            return $my_img;
                        }, $image_url_temp);
                        $spintax = new Aiomatic_Spintax();
                        $img_rulx = $spintax->Parse(trim($image_url_temp));
                        $selected_img = aiomatic_select_ai_image($post->post_title, $img_rulx);
                        if($selected_img === false)
                        {
                            $img_rulx = explode(',', $img_rulx);
                            $img_rulx = trim($img_rulx[array_rand($img_rulx)]);
                            if($img_rulx != '')
                            {
                                $zget_img = $img_rulx;
                            }
                        }
                        else
                        {
                            $zget_img = $selected_img;
                        }
                        if($zget_img != '')
                        {
                            if (!aiomatic_generate_featured_image($zget_img, $post->ID)) {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('aiomatic_generate_featured_image failed using manual image: ' . $zget_img);
                                }
                            }
                            else
                            {
                                $img_saved = true;
                            }
                        }
                    }
                }
                elseif($fisource == '0')
                {
                    $img_set = false;
                    $img_attr = '';
                    $postID = $post->ID;
                    $post_excerpt = $post->post_excerpt;
                    $query_words = '';
                    $image_query = $post_title;
                    if (isset($aiomatic_Spinner_Settings['ai_image_command']) && $aiomatic_Spinner_Settings['ai_image_command'] != '')
                    {
                        $image_query = $aiomatic_Spinner_Settings['ai_image_command'];
                    }
                    if(isset($aiomatic_Main_Settings['improve_keywords']) && trim($aiomatic_Main_Settings['improve_keywords']) == 'textrazor')
                    {
                        if(isset($aiomatic_Main_Settings['textrazor_key']) && trim($aiomatic_Main_Settings['textrazor_key']) != '')
                        {
                            try
                            {
                                if(!class_exists('TextRazor'))
                                {
                                    require_once(dirname(__FILE__) . "/res/TextRazor.php");
                                }
                                TextRazorSettings::setApiKey(trim($aiomatic_Main_Settings['textrazor_key']));
                                $textrazor = new TextRazor();
                                $textrazor->addExtractor('entities');
                                $response = $textrazor->analyze($aicontent);
                                if (isset($response['response']['entities'])) 
                                {
                                    foreach ($response['response']['entities'] as $entity) 
                                    {
                                        $query_words = '';
                                        if(isset($entity['entityEnglishId']))
                                        {
                                            $query_words = $entity['entityEnglishId'];
                                        }
                                        else
                                        {
                                            $query_words = $entity['entityId'];
                                        }
                                        if($query_words != '')
                                        {
                                            $attached_id = '';
                                            $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $img_attr, $attached_id, 10, true, $raw_img_list, array(), $full_result_list);
                                            if(!empty($z_img))
                                            {
                                                if (!aiomatic_generate_featured_image($z_img, $post->ID)) {
                                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                        aiomatic_log_to_file('aiomatic_generate_featured_image failed using royalty free image: ' . $z_img);
                                                    }
                                                }
                                                else
                                                {
                                                    $img_saved = true;
                                                    $img_set = true;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            catch(Exception $e)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('Failed to search for keywords using TextRazor (2): ' . $e->getMessage());
                                }
                            }
                        }
                    }
                    elseif(isset($aiomatic_Main_Settings['improve_keywords']) && trim($aiomatic_Main_Settings['improve_keywords']) == 'openai')
                    {
                        if(isset($aiomatic_Main_Settings['keyword_prompts']) && trim($aiomatic_Main_Settings['keyword_prompts']) != '')
                        {
                            if(isset($aiomatic_Main_Settings['keyword_model']) && $aiomatic_Main_Settings['keyword_model'] != '')
                            {
                                $kw_model = $aiomatic_Main_Settings['keyword_model'];
                            }
                            else
                            {
                                $kw_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                            }
                            if(isset($aiomatic_Main_Settings['keyword_assistant_id']) && $aiomatic_Main_Settings['keyword_assistant_id'] != '')
                            {
                                $keyword_assistant_id = $aiomatic_Main_Settings['keyword_assistant_id'];
                            }
                            else
                            {
                                $keyword_assistant_id = '';
                            }
                            $title_ai_command = trim($aiomatic_Main_Settings['keyword_prompts']);
                            $title_ai_command = str_replace('%%default_post_cats%%', '', $title_ai_command);
                            $title_ai_command = str_replace('%%original_post_title%%', $post_title, $title_ai_command);
                            $title_ai_command = preg_split('/\r\n|\r|\n/', $title_ai_command);
                            $title_ai_command = array_filter($title_ai_command);
                            if(count($title_ai_command) > 0)
                            {
                                $title_ai_command = $title_ai_command[array_rand($title_ai_command)];
                            }
                            else
                            {
                                $title_ai_command = '';
                            }
                            $title_ai_command = aiomatic_replaceSynergyShortcodes($title_ai_command);
                            if(!empty($title_ai_command))
                            {
                                $title_ai_command = aiomatic_replaceAIPostShortcodes($title_ai_command, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                            }
                            $title_ai_command = trim($title_ai_command);
                            if (filter_var($title_ai_command, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($title_ai_command, '.txt'))
                            {
                                $txt_content = aiomatic_get_web_page($title_ai_command);
                                if ($txt_content !== FALSE) 
                                {
                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                    $txt_content = array_filter($txt_content);
                                    if(count($txt_content) > 0)
                                    {
                                        $txt_content = $txt_content[array_rand($txt_content)];
                                        if(trim($txt_content) != '') 
                                        {
                                            $title_ai_command = $txt_content;
                                            $title_ai_command = aiomatic_replaceSynergyShortcodes($title_ai_command);
                                            $title_ai_command = aiomatic_replaceAIPostShortcodes($title_ai_command, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        }
                                    }
                                }
                            }
                            if(empty($title_ai_command))
                            {
                                aiomatic_log_to_file('Empty API keyword extractor seed expression provided!');
                                $title_ai_command = 'Extract a comma separated list of relevant keywords from the text: ' . trim(strip_tags($post_title));
                            }
                            if(!empty($max_seed_tokens) && strlen($title_ai_command) > $max_seed_tokens * 4)
                            {
                                $title_ai_command = aiomatic_substr($title_ai_command, 0, (0 - ($max_seed_tokens * 4)));
                            }
                            $title_ai_command = trim($title_ai_command);
                            if(empty($title_ai_command))
                            {
                                aiomatic_log_to_file('Empty API title seed expression provided(8)! ' . print_r($title_ai_command, true));
                            }
                            else
                            {
                                $query_token_count = count(aiomatic_encode($title_ai_command));
                                $max_tokens = aiomatic_get_max_tokens($kw_model);
                                $available_tokens = aiomatic_compute_available_tokens($kw_model, $max_tokens, $title_ai_command, $query_token_count);
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($title_ai_command);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $title_ai_command = aiomatic_substr($title_ai_command, 0, $string_len);
                                    $title_ai_command = trim($title_ai_command);
                                    $query_token_count = count(aiomatic_encode($title_ai_command));
                                    $available_tokens = $max_tokens - $query_token_count;
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $kw_model);
                                    aiomatic_log_to_file('Calling ' . $api_service . ' (' . $kw_model . ')(' . $available_tokens . ') for title text3: ' . $title_ai_command);
                                }
                                $aierror = '';
                                $finish_reason = '';
                                $generated_text = aiomatic_generate_text($token, $kw_model, $title_ai_command, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'keywordCompletion', 0, $finish_reason, $aierror, true, false, false, '', '', 'user', $keyword_assistant_id, $thread_id, '', 'disabled', '', false, $store_data, array());
                                if($generated_text === false)
                                {
                                    aiomatic_log_to_file('Keyword generator error: ' . $aierror);
                                    $ai_title = '';
                                }
                                else
                                {
                                    $ai_title = trim(trim(trim(trim($generated_text), '.'), ' "\''));
                                    $ai_titles = explode(',', $ai_title);
                                    foreach($ai_titles as $query_words)
                                    {
                                        $attached_id = '';
                                        $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, trim($query_words), $img_attr, $attached_id, 10, true, $raw_img_list, array(), $full_result_list);
                                        if(!empty($z_img))
                                        {
                                            if (!aiomatic_generate_featured_image($z_img, $post->ID)) {
                                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                                    aiomatic_log_to_file('aiomatic_generate_featured_image failed using royalty free image: ' . $z_img);
                                                }
                                            }
                                            else
                                            {
                                                $img_saved = true;
                                                $img_set = true;
                                            }
                                            break;
                                        }
                                    }
                                }
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                                {
                                    $api_service = aiomatic_get_api_service($token, $kw_model);
                                    aiomatic_log_to_file('Successfully got API keyword result from ' . $api_service . ': ' . $ai_title);
                                }
                            }
                        }
                    }
                    if($img_set == false)
                    {
                        $keyword_class = new Aiomatic_keywords();
                        $query_words = $keyword_class->keywords($image_query, 2);
                        $attached_id = '';
                        $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $img_attr, $attached_id, 10, true, $raw_img_list, array(), $full_result_list);
                        if($z_img == '' || $z_img === false)
                        {
                            if(isset($aiomatic_Main_Settings['bimage']) && $aiomatic_Main_Settings['bimage'] == 'on')
                            {
                                $query_words = $keyword_class->keywords($image_query, 1);
                                $attached_id = '';
                                $z_img = aiomatic_get_free_image($aiomatic_Main_Settings, $query_words, $img_attr, $attached_id, 20, true, $raw_img_list, array(), $full_result_list);
                            }
                        }
                        if(!empty($z_img))
                        {
                            if (!aiomatic_generate_featured_image($z_img, $post->ID)) {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('aiomatic_generate_featured_image failed using royalty free image: ' . $z_img);
                                }
                            }
                            else
                            {
                                $img_saved = true;
                            }
                        }
                    }
                }
                if($img_saved == true)
                {
                    if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                    {
                        $args = array();
                        $args['ID'] = $post->ID;
                        $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                        remove_filter('content_save_pre', 'wp_filter_post_kses');
                        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                        remove_filter('title_save_pre', 'wp_filter_kses');
                        update_post_meta($post->ID, $custom_name, "pub");
                        $post_updated = wp_update_post($args);
                        add_filter('content_save_pre', 'wp_filter_post_kses');
                        add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                        add_filter('title_save_pre', 'wp_filter_kses');
                        if (is_wp_error($post_updated)) {
                            $errors = $post_updated->get_error_messages();
                            foreach ($errors as $error) {
                                aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                            }
                        }
                    }
                    if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                    {
                        $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                        if($date_stamp)
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            $postdate = date("Y-m-d H:i:s", $date_stamp);
                            $args['post_date'] = $postdate;
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                        }
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                        aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with royalty free image.');
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['ai_featured_image_edit']) && $aiomatic_Spinner_Settings['ai_featured_image_edit'] != '' && $aiomatic_Spinner_Settings['ai_featured_image_edit'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post featured image editor...');
                }
                update_post_meta($post->ID, $custom_name, "pub");
                if (isset($aiomatic_Spinner_Settings['image_strength']) && trim($aiomatic_Spinner_Settings['image_strength']) != '')
                {
                    $image_strength = trim($aiomatic_Spinner_Settings['image_strength']);
                }
                else
                {
                    $image_strength = '0.90';
                }
                wp_suspend_cache_addition(true);
                $metas = get_post_custom($post->ID);
                wp_suspend_cache_addition(false);
                if(is_array($metas))
                {
                    $rez_meta = aiomatic_preg_grep_keys('#.+?_featured_ima?ge?#i', $metas);
                }
                else
                {
                    $rez_meta = array();
                }
                if(count($rez_meta) > 0)
                {
                    foreach($rez_meta as $rm)
                    {
                        if(isset($rm[0]) && filter_var($rm[0], FILTER_VALIDATE_URL))
                        {
                            $featured_image = $rm[0];
                            break;
                        }
                    }
                }
                if(empty($featured_image))
                {
                    $featured_image   = get_the_post_thumbnail_url($post->ID, 'full');
                }
                if(empty($featured_image))
                {
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                        aiomatic_log_to_file('Post ID ' . $post->ID . ' does not have a featured image assigned, nothing to edit!');
                    }
                }
                else
                {
                    $img_saved = false;
                    if (isset($aiomatic_Spinner_Settings['ai_image_command_edit']) && $aiomatic_Spinner_Settings['ai_image_command_edit'] != '')
                    {
                        $aicontent = trim(strip_tags($aiomatic_Spinner_Settings['ai_image_command_edit']));
                        $aicontent = aiomatic_replaceSynergyShortcodes($aicontent);
                        $post_link = get_permalink($post->ID);
                        $blog_title       = html_entity_decode(get_bloginfo('title'));
                        $author_obj       = get_user_by('id', $post->post_author);
                        if($author_obj !== false && isset($author_obj->user_nicename))
                        {
                            $user_name        = $author_obj->user_nicename;
                        }
                        else
                        {
                            $user_name        = '';
                        }
                        $post_cats = '';
                        $post_categories = wp_get_post_categories( $post->ID );
                        foreach($post_categories as $c){
                            $cat = get_category( $c );
                            $post_cats .= $cat->name . ',';
                        }
                        $post_cats = trim($post_cats, ',');
                        if($post_cats != '')
                        {
                            $post_categories = explode(',', $post_cats);
                        }
                        else
                        {
                            $post_categories = array();
                        }
                        if(count($post_categories) == 0)
                        {
                            $terms = get_the_terms( $post->ID, 'product_cat' );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                foreach ( $terms as $term ) {
                                    $post_categories[] = $term->slug;
                                }
                                $post_cats = implode(',', $post_categories);
                            }
                            
                        }
                        foreach($post_categories as $pc)
                        {
                            if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                                foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                                {
                                    if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                        }
                                        return;
                                    }
                                }
                            }
                        }
                        $post_tagz = '';
                        $post_tags = wp_get_post_tags( $post->ID );
                        foreach($post_tags as $t){
                            $post_tagz .= $t->name . ',';
                        }
                        $post_tagz = trim($post_tagz, ',');
                        if($post_tagz != '')
                        {
                            $post_tags = explode(',', $post_tagz);
                        }
                        else
                        {
                            $post_tags = array();
                        }
                        if(count($post_tags) == 0)
                        {
                            $terms = get_the_terms( $post->ID, 'product_tag' );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                foreach ( $terms as $term ) {
                                    $post_tags[] = $term->slug;
                                }
                                $post_tagz = implode(',', $post_tags);
                            }
                            
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                            
                            $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                            foreach($disable_users as $disable_user)
                            {
                                if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                    }
                                    return;
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                            
                            $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                            $found = false;
                            foreach($enable_users as $disable_user)
                            {
                                if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                {
                                    $found = true;
                                }
                            }
                            if($found === false)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                            if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                            {
                                if ( !has_post_thumbnail($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                            {
                                if ( has_post_thumbnail($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                        }
                        
                        if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                            if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                            {
                                if ( !aiomatic_check_seo_description($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                            {
                                if ( aiomatic_check_seo_description($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                            $skip_me = true;
                            $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                            foreach($enable_field as $fieldx)
                            {
                                if(strstr($fieldx, '=>') !== false)
                                {
                                    $fna = explode('=>', $fieldx);
                                    if(isset($fna[1]))
                                    {
                                        $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                        if ($custom_val) 
                                        {
                                            if(trim($fna[1])  == '*')
                                            {
                                                $skip_me = false;
                                            }
                                            if($custom_val == trim($fna[1]))
                                            {
                                                $skip_me = false;
                                            }
                                        }
                                    }
                                }
                            }
                            if($skip_me === true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                            $skip_me = false;
                            $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                            foreach($disable_field as $fieldx)
                            {
                                if(strstr($fieldx, '=>') !== false)
                                {
                                    $fna = explode('=>', $fieldx);
                                    if(isset($fna[1]))
                                    {
                                        $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                        if ($custom_val) 
                                        {
                                            if(trim($fna[1])  == '*')
                                            {
                                                $skip_me = true;
                                            }
                                            if($custom_val == trim($fna[1]))
                                            {
                                                $skip_me = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if($skip_me === true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        foreach($post_tags as $pt)
                        {
                            if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                                
                                $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                                foreach($disable_tags as $disabled_tag)
                                {
                                    if($manual != true && trim($pt) == trim($disabled_tag))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                        }
                                        return;
                                    }
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                            $skip = true;
                            $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                            foreach($post_tags as $pt)
                            {
                                foreach($enable_tags as $enable_tag)
                                {
                                    if(trim($pt) == trim($enable_tag))
                                    {
                                        $skip = false;
                                        break;
                                    }
                                }
                            }
                            if($skip == true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                                }
                                return;
                            }
                        }
                        $aicontent = aiomatic_replaceAIPostShortcodes($aicontent, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    }
                    else
                    {
                        $aicontent = trim(strip_tags($final_content));
                        if(empty($aicontent))
                        {
                            $aicontent = trim(strip_tags($post->post_excerpt));
                        }
                        if(empty($aicontent))
                        {
                            $aicontent = trim(strip_tags($post_title));
                            $last_char = aiomatic_substr($aicontent, -1, null);
                            if(!ctype_punct($last_char))
                            {
                                $aicontent .= '.';
                            }
                        }
                    }
                    if(isset($aiomatic_Spinner_Settings['ai_featured_image_engine']) && $aiomatic_Spinner_Settings['ai_featured_image_engine'] != '')
                    {
                        $fisource = $aiomatic_Spinner_Settings['ai_featured_image_engine'];
                    }
                    else
                    {
                        $fisource = '2';
                    }
                    if($fisource == '2')
                    {
                        
                        if (!isset($aiomatic_Main_Settings['stability_app_id']) || trim($aiomatic_Main_Settings['stability_app_id']) == '') 
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('You need to enter a Stability.AI API key in the plugin\'s settings for this feature to work.');
                            }
                        }
                        else
                        {
                            $aicontent = trim($aicontent);
                            if(strlen($aicontent) > 2000)
                            {
                                $aicontent = aiomatic_substr($aicontent, 0, 2000);
                            }
                            $skip_this_copy = true;
                            if(!function_exists('is_plugin_active'))
                            {
                                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                            }
                            if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                            {
                                $skip_this_copy = false;
                            }
                            $ierror = '';
                            if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                            {
                                $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                            }
                            else
                            {
                                $image_size = '1024x1024';
                            }
                            if($image_size == '256x256')
                            {
                                $width = '512';
                                $height = '512';
                            }
                            elseif($image_size == '512x512')
                            {
                                $width = '512';
                                $height = '512';
                            }
                            elseif($image_size == '1024x1024')
                            {
                                $width = '1024';
                                $height = '1024';
                            }
                            elseif($image_size == '1792x1024')
                            {
                                $width = '1792';
                                $height = '1024';
                            }
                            elseif($image_size == '1024x1792')
                            {
                                $width = '1024';
                                $height = '1792';
                            }
                            else
                            {
                                $width = '512';
                                $height = '512';
                            }
                            $temp_get_imgs = aiomatic_generate_stability_image($aicontent, $height, $width, 'editorAIFeaturedStableImage', 0, false, $ierror, $skip_this_copy, false, $featured_image, $image_strength);
                            if($temp_get_imgs !== false)
                            {
                                $temp_get_img_local = $temp_get_imgs[0];
                                if (!aiomatic_assign_featured_image_path($temp_get_img_local, $post->ID)) {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('aiomatic_assign_featured_image_path failed using Stability.AI image editor for ' .$temp_get_imgs[1]);
                                    }
                                }
                                else
                                {
                                    $img_saved = true;
                                }
                            }
                            else
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit the featured image using Stability.AI: ' . $ierror);
                                }
                            }
                        }
                    }
                    if($img_saved == true)
                    {
                        if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating post for title "' . $post->post_title . '": ' . $error);
                                }
                            }
                        }
                        if (isset($aiomatic_Spinner_Settings['change_date']) && $aiomatic_Spinner_Settings['change_date'] != '') 
                        {
                            $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                            if($date_stamp)
                            {
                                $args = array();
                                $args['ID'] = $post->ID;
                                $postdate = date("Y-m-d H:i:s", $date_stamp);
                                $args['post_date'] = $postdate;
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                update_post_meta($post->ID, $custom_name, "pub");
                                $post_updated = wp_update_post($args);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                                if (is_wp_error($post_updated)) {
                                    $errors = $post_updated->get_error_messages();
                                    foreach ($errors as $error) {
                                        aiomatic_log_to_file('Error occured while updating post status for title "' . $post->post_title . '": ' . $error);
                                    }
                                }
                            }
                        }
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated image.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['ai_featured_image_edit_content']) && $aiomatic_Spinner_Settings['ai_featured_image_edit_content'] != '' && $aiomatic_Spinner_Settings['ai_featured_image_edit_content'] != 'disabled')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post content image editor...');
                }
                update_post_meta($post->ID, $custom_name, "pub");
                if (isset($aiomatic_Spinner_Settings['image_strength_content']) && trim($aiomatic_Spinner_Settings['image_strength_content']) != '')
                {
                    $image_strength = trim($aiomatic_Spinner_Settings['image_strength_content']);
                }
                else
                {
                    $image_strength = '0.90';
                }
                $pattern = '/<img[^>]+src="([^"]+)"/i';
                $srcs = [];
                if (preg_match_all($pattern, $post->post_content, $matches) && isset($matches[1])) 
                {
                    $srcs = $matches[1];
                }
                if(count($srcs) > 0)
                {
                    if (isset($aiomatic_Spinner_Settings['ai_image_command_edit_content']) && $aiomatic_Spinner_Settings['ai_image_command_edit_content'] != '')
                    {
                        $aicontent = trim(strip_tags($aiomatic_Spinner_Settings['ai_image_command_edit_content']));
                        $aicontent = aiomatic_replaceSynergyShortcodes($aicontent);
                        $post_link = get_permalink($post->ID);
                        $blog_title       = html_entity_decode(get_bloginfo('title'));
                        $author_obj       = get_user_by('id', $post->post_author);
                        if($author_obj !== false && isset($author_obj->user_nicename))
                        {
                            $user_name        = $author_obj->user_nicename;
                        }
                        else
                        {
                            $user_name        = '';
                        }
                        $post_cats = '';
                        $post_categories = wp_get_post_categories( $post->ID );
                        foreach($post_categories as $c){
                            $cat = get_category( $c );
                            $post_cats .= $cat->name . ',';
                        }
                        $post_cats = trim($post_cats, ',');
                        if($post_cats != '')
                        {
                            $post_categories = explode(',', $post_cats);
                        }
                        else
                        {
                            $post_categories = array();
                        }
                        if(count($post_categories) == 0)
                        {
                            $terms = get_the_terms( $post->ID, 'product_cat' );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                foreach ( $terms as $term ) {
                                    $post_categories[] = $term->slug;
                                }
                                $post_cats = implode(',', $post_categories);
                            }
                            
                        }
                        foreach($post_categories as $pc)
                        {
                            if (!$manual && isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
                                foreach($aiomatic_Spinner_Settings['disabled_categories'] as $disabled_cat)
                                {
                                    if($manual != true && trim($pc) == get_cat_name($disabled_cat))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, has a disabled category (' . get_cat_name($disabled_cat) . '): ' . $post->post_title);
                                        }
                                        return;
                                    }
                                }
                            }
                        }
                        $post_tagz = '';
                        $post_tags = wp_get_post_tags( $post->ID );
                        foreach($post_tags as $t){
                            $post_tagz .= $t->name . ',';
                        }
                        $post_tagz = trim($post_tagz, ',');
                        if($post_tagz != '')
                        {
                            $post_tags = explode(',', $post_tagz);
                        }
                        else
                        {
                            $post_tags = array();
                        }
                        if(count($post_tags) == 0)
                        {
                            $terms = get_the_terms( $post->ID, 'product_tag' );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                                foreach ( $terms as $term ) {
                                    $post_tags[] = $term->slug;
                                }
                                $post_tagz = implode(',', $post_tags);
                            }
                            
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_users']) && $aiomatic_Spinner_Settings['disable_users'] != '') {
                            
                            $disable_users = explode(",", $aiomatic_Spinner_Settings['disable_users']);
                            foreach($disable_users as $disable_user)
                            {
                                if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, has a disabled author user ID: ' . $post->post_author);
                                    }
                                    return;
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_users']) && $aiomatic_Spinner_Settings['enable_users'] != '') {
                            
                            $enable_users = explode(",", $aiomatic_Spinner_Settings['enable_users']);
                            $found = false;
                            foreach($enable_users as $disable_user)
                            {
                                if(!empty(trim($disable_user)) && $manual != true && $post->post_author == intval(trim($disable_user)))
                                {
                                    $found = true;
                                }
                            }
                            if($found === false)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have enabled author user ID: ' . $post->post_author);
                                }
                                return;
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['featured_status']) && $aiomatic_Spinner_Settings['featured_status'] != '' && $aiomatic_Spinner_Settings['featured_status'] != 'any') {
                            if ($aiomatic_Spinner_Settings['featured_status'] == 'yes')
                            {
                                if ( !has_post_thumbnail($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have a featured image: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            elseif ($aiomatic_Spinner_Settings['featured_status'] == 'no')
                            {
                                if ( has_post_thumbnail($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, already has a featured image: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                        }
                        
                        if (!$manual && isset($aiomatic_Spinner_Settings['seo_status']) && $aiomatic_Spinner_Settings['seo_status'] != '' && $aiomatic_Spinner_Settings['seo_status'] != 'any') {
                            if ($aiomatic_Spinner_Settings['seo_status'] == 'yes')
                            {
                                if ( !aiomatic_check_seo_description($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, does not have a SEO meta description: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                            elseif ($aiomatic_Spinner_Settings['seo_status'] == 'no')
                            {
                                if ( aiomatic_check_seo_description($post->ID) ) 
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                    {
                                        aiomatic_log_to_file('Skipping post, already has a SEO meta description: ' . $post->ID);
                                    }
                                    return;
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_field']) && trim($aiomatic_Spinner_Settings['enable_field']) != '') {
                            $skip_me = true;
                            $enable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['enable_field']));
                            foreach($enable_field as $fieldx)
                            {
                                if(strstr($fieldx, '=>') !== false)
                                {
                                    $fna = explode('=>', $fieldx);
                                    if(isset($fna[1]))
                                    {
                                        $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                        if ($custom_val) 
                                        {
                                            if(trim($fna[1])  == '*')
                                            {
                                                $skip_me = false;
                                            }
                                            if($custom_val == trim($fna[1]))
                                            {
                                                $skip_me = false;
                                            }
                                        }
                                    }
                                }
                            }
                            if($skip_me === true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have required custom field set: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['disable_field']) && trim($aiomatic_Spinner_Settings['disable_field']) != '') {
                            $skip_me = false;
                            $disable_field = preg_split('/\r\n|\r|\n/', trim($aiomatic_Spinner_Settings['disable_field']));
                            foreach($disable_field as $fieldx)
                            {
                                if(strstr($fieldx, '=>') !== false)
                                {
                                    $fna = explode('=>', $fieldx);
                                    if(isset($fna[1]))
                                    {
                                        $custom_val = get_post_meta($post->ID, trim($fna[0]), true);
                                        if ($custom_val) 
                                        {
                                            if(trim($fna[1])  == '*')
                                            {
                                                $skip_me = true;
                                            }
                                            if($custom_val == trim($fna[1]))
                                            {
                                                $skip_me = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if($skip_me === true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, has skipped custom field set: ' . $post->ID);
                                }
                                return;
                            }
                        }
                        foreach($post_tags as $pt)
                        {
                            if (!$manual && isset($aiomatic_Spinner_Settings['disable_tags']) && $aiomatic_Spinner_Settings['disable_tags'] != '') {
                                
                                $disable_tags = explode(",", $aiomatic_Spinner_Settings['disable_tags']);
                                foreach($disable_tags as $disabled_tag)
                                {
                                    if($manual != true && trim($pt) == trim($disabled_tag))
                                    {
                                        if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                        {
                                            aiomatic_log_to_file('Skipping post, has a disabled tag: ' . $post->post_title);
                                        }
                                        return;
                                    }
                                }
                            }
                        }
                        if (!$manual && isset($aiomatic_Spinner_Settings['enable_tags']) && $aiomatic_Spinner_Settings['enable_tags'] != '') {
                            $skip = true;
                            $enable_tags = explode(",", $aiomatic_Spinner_Settings['enable_tags']);
                            foreach($post_tags as $pt)
                            {
                                foreach($enable_tags as $enable_tag)
                                {
                                    if(trim($pt) == trim($enable_tag))
                                    {
                                        $skip = false;
                                        break;
                                    }
                                }
                            }
                            if($skip == true)
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                                {
                                    aiomatic_log_to_file('Skipping post, does not have required tag: ' . $post->post_title);
                                }
                                return;
                            }
                        }
                        $aicontent = aiomatic_replaceAIPostShortcodes($aicontent, $post_link, $post_title, $blog_title, $post->post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $post->ID, '', '', '', '', '', '');
                    }
                    else
                    {
                        $aicontent = trim(strip_tags($final_content));
                        if(empty($aicontent))
                        {
                            $aicontent = trim(strip_tags($post->post_excerpt));
                        }
                        if(empty($aicontent))
                        {
                            $aicontent = trim(strip_tags($post_title));
                            $last_char = aiomatic_substr($aicontent, -1, null);
                            if(!ctype_punct($last_char))
                            {
                                $aicontent .= '.';
                            }
                        }
                    }
                    $aicontent = trim($aicontent);
                    if(strlen($aicontent) > 2000)
                    {
                        $aicontent = aiomatic_substr($aicontent, 0, 2000);
                    }
                    if(isset($aiomatic_Spinner_Settings['ai_featured_image_engine_content']) && $aiomatic_Spinner_Settings['ai_featured_image_engine_content'] != '')
                    {
                        $fisource = $aiomatic_Spinner_Settings['ai_featured_image_engine_content'];
                    }
                    else
                    {
                        $fisource = '2';
                    }
                    if($fisource == '2')
                    {
                        if (!isset($aiomatic_Main_Settings['stability_app_id']) || trim($aiomatic_Main_Settings['stability_app_id']) == '') 
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                            {
                                aiomatic_log_to_file('You need to enter a Stability.AI API key in the plugin\'s settings for this feature to work.');
                            }
                        }
                        if(!function_exists('is_plugin_active'))
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        }
                        $skip_this_copy = true;
                        if ((is_plugin_active('featured-image-from-url/featured-image-from-url.php') || is_plugin_active('fifu-premium/fifu-premium.php')) && isset($aiomatic_Main_Settings['url_image']) && trim($aiomatic_Main_Settings['url_image']) == 'on') 
                        {
                            $skip_this_copy = false;
                        }
                        $changemade = 0;
                        foreach($srcs as $fimg)
                        {
                            if($fisource == '2')
                            {
                                $ierror = '';
                                if (isset($aiomatic_Spinner_Settings['image_size']) && trim($aiomatic_Spinner_Settings['image_size']) != '')
                                {
                                    $image_size = trim($aiomatic_Spinner_Settings['image_size']);
                                }
                                else
                                {
                                    $image_size = '1024x1024';
                                }
                                if($image_size == '256x256')
                                {
                                    $width = '512';
                                    $height = '512';
                                }
                                elseif($image_size == '512x512')
                                {
                                    $width = '512';
                                    $height = '512';
                                }
                                elseif($image_size == '1024x1024')
                                {
                                    $width = '1024';
                                    $height = '1024';
                                }
                                elseif($image_size == '1792x1024')
                                {
                                    $width = '1792';
                                    $height = '1024';
                                }
                                elseif($image_size == '1024x1792')
                                {
                                    $width = '1024';
                                    $height = '1792';
                                }
                                else
                                {
                                    $width = '512';
                                    $height = '512';
                                }
                                $temp_get_imgs = aiomatic_generate_stability_image($aicontent, $height, $width, 'editorAIFeaturedStableImage', 0, false, $ierror, $skip_this_copy, false, $fimg, $image_strength);
                                if($temp_get_imgs !== false)
                                {
                                    $temp_get_img_remote = $temp_get_imgs[1];
                                    $post->post_content = str_replace($fimg, $temp_get_img_remote, $post->post_content);
                                    $changemade++;
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' failed to edit the content image (' . $fimg . ') using Stability.AI: ' . $ierror);
                                    }
                                }
                            }
                        }
                        if ($changemade > 0) 
                        {
                            $args = array();
                            $args['ID'] = $post->ID;
                            if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                            {
                                $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                            }
                            if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                            {
                                $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                                if($date_stamp)
                                {
                                    $postdate = date("Y-m-d H:i:s", $date_stamp);
                                    $args['post_date'] = $postdate;
                                }
                            }
                            $args['post_content'] = $post->post_content;
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            update_post_meta($post->ID, $custom_name, "pub");
                            $post_updated = wp_update_post($args);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                            if (is_wp_error($post_updated)) {
                                $errors = $post_updated->get_error_messages();
                                foreach ($errors as $error) {
                                    aiomatic_log_to_file('Error occured while updating AI generated images for post title "' . $post->post_title . '": ' . $error);
                                }
                            }
                            else
                            {
                                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                    aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with ' . $changemade . ' Stability AI edited images in post content.');
                                }
                            }
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['content_text_speech']) && $aiomatic_Spinner_Settings['content_text_speech'] != '' && $aiomatic_Spinner_Settings['content_text_speech'] != 'off')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post text-to-speech module...');
                }
                $copy_it = 'local';
                if(isset($aiomatic_Spinner_Settings['copy_location']) && !empty($aiomatic_Spinner_Settings['copy_location']))
                {
                    $copy_it = $aiomatic_Spinner_Settings['copy_location'];
                }
                $updated = false;
                if($aiomatic_Spinner_Settings['content_text_speech'] == 'openai')
                {
                    if(!isset($aiomatic_Spinner_Settings['text_to_audio']) || empty($aiomatic_Spinner_Settings['text_to_audio']))
                    {
                        aiomatic_log_to_file('No text to send to text-to-speech!');
                    }
                    else
                    {
                        if (!isset($aiomatic_Main_Settings['app_id'])) 
                        {
                            $aiomatic_Main_Settings['app_id'] = '';
                        }
                        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                        $appids = array_filter($appids);
                        $token = $appids[array_rand($appids)];
                        $token = apply_filters('aiomatic_openai_api_key', $token);
                        if (empty($token))
                        {
                            aiomatic_log_to_file('You need to enter an OpenAI API key for this to work!');
                        }
                        else
                        {
                            if(aiomatic_is_aiomaticapi_key($token) || (aiomatic_check_if_azure_or_others($aiomatic_Main_Settings)))
                            {
                                aiomatic_log_to_file('Only OpenAI API keys are supported at the moment.');
                            }
                            else
                            {
                                if(isset($aiomatic_Spinner_Settings['open_model_id']) && $aiomatic_Spinner_Settings['open_model_id'] != '')
                                {
                                    $open_model_id = $aiomatic_Spinner_Settings['open_model_id'];
                                }
                                else
                                {
                                    $open_model_id = 'tts-1';
                                }
                                if(isset($aiomatic_Spinner_Settings['open_voice']) && $aiomatic_Spinner_Settings['open_voice'] != '')
                                {
                                    $open_voice = $aiomatic_Spinner_Settings['open_voice'];
                                }
                                else
                                {
                                    $open_voice = 'alloy';
                                }
                                if(isset($aiomatic_Spinner_Settings['open_format']) && $aiomatic_Spinner_Settings['open_format'] != '')
                                {
                                    $open_format = $aiomatic_Spinner_Settings['open_format'];
                                }
                                else
                                {
                                    $open_format = 'mp3';
                                }
                                if(isset($aiomatic_Spinner_Settings['open_speed']) && $aiomatic_Spinner_Settings['open_speed'] != '')
                                {
                                    $open_speed = $aiomatic_Spinner_Settings['open_speed'];
                                }
                                else
                                {
                                    $open_speed = '1';
                                }
                                $message = trim($aiomatic_Spinner_Settings['text_to_audio']);
                                $message = aiomatic_replaceSynergyShortcodes($message);
                                if(!empty($message))
                                {
                                    $message = aiomatic_replaceAIPostShortcodes($message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                    $session = aiomatic_get_session_id();
                                    $message = wp_strip_all_tags($message);
                                    if(!empty($message))
                                    {
                                        $query = new Aiomatic_Query($message, 0, 'openai-' . $open_model_id, '0', '', 'text-to-speech', 'text-to-speech', $token, $session, 1, '', '');
                                        $result = aiomatic_openai_voice_stream($token, $open_model_id, $open_voice, $open_format, $open_speed, $message);
                                        if(is_array($result))
                                        {
                                            aiomatic_log_to_file('Error occurred in OpenAI audio processing: ' . print_r($result, true));
                                        }
                                        else
                                        {
                                            apply_filters( 'aiomatic_ai_reply_text', $query, $message );
                                            $localfile = aiomatic_copy_audio_stream_locally($result, 'audio_' . time() . '.' . $open_format, $copy_it);
                                            if($localfile === false)
                                            {
                                                aiomatic_log_to_file('Failed to save audio file locally to your server.');
                                            }
                                            else
                                            {
                                                $retpath = $localfile[0];
                                                $prep_txt = '';
                                                if (isset($aiomatic_Spinner_Settings['prep_audio']) && $aiomatic_Spinner_Settings['prep_audio'] != '')
                                                {
                                                    $prep_txt = $aiomatic_Spinner_Settings['prep_audio'];
                                                }
                                                if (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'append') 
                                                {
                                                    $final_content = $final_content . $prep_txt . ' <br/> [audio src="' . $retpath . '"]';
                                                    $updated = true;
                                                }
                                                elseif (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'preppend')
                                                {
                                                    $final_content = $prep_txt . '[audio src="' . $retpath . '"] <br/> ' . $final_content;
                                                    $updated = true;
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Empty input message after strippinig html tags');
                                    }
                                }
                                else
                                {
                                    aiomatic_log_to_file('Empty input message after audio processing');
                                }
                            }
                        }
                    }
                }
                elseif($aiomatic_Spinner_Settings['content_text_speech'] == 'elevenlabs')
                {
                    if(!isset($aiomatic_Spinner_Settings['text_to_audio']) || empty($aiomatic_Spinner_Settings['text_to_audio']))
                    {
                        aiomatic_log_to_file('No text to send to text-to-speech!');
                    }
                    else
                    {
                        if (!isset($aiomatic_Main_Settings['elevenlabs_app_id'])) 
                        {
                            $aiomatic_Main_Settings['elevenlabs_app_id'] = '';
                        }
                        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['elevenlabs_app_id']));
                        $appids = array_filter($appids);
                        $token = $appids[array_rand($appids)];
                        $token = apply_filters('aiomatic_elevenlabs_api_key', $token);
                        if (empty($token))
                        {
                            aiomatic_log_to_file('You need to enter an ElevenLabs API key for this to work!');
                        }
                        else
                        {
                            if(isset($aiomatic_Spinner_Settings['eleven_voice_custom']) && $aiomatic_Spinner_Settings['eleven_voice_custom'] != '')
                            {
                                $voice = $aiomatic_Spinner_Settings['eleven_voice_custom'];
                            }
                            else
                            {
                                if(isset($aiomatic_Spinner_Settings['eleven_voice']) && $aiomatic_Spinner_Settings['eleven_voice'] != '')
                                {
                                    $voice = $aiomatic_Spinner_Settings['eleven_voice'];
                                }
                                else
                                {
                                    $voice = '21m00Tcm4TlvDq8ikWAM';
                                }
                            }
                            $message = trim($aiomatic_Spinner_Settings['text_to_audio']);
                            $message = aiomatic_replaceSynergyShortcodes($message);
                            if(!empty($message))
                            {
                                $message = aiomatic_replaceAIPostShortcodes($message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                $session = aiomatic_get_session_id();
                                $message = wp_strip_all_tags($message);
                                if(!empty($message))
                                {
                                    $query = new Aiomatic_Query($message, 0, 'elevenlabs', '0', '', 'text-to-speech', 'text-to-speech', trim($aiomatic_Main_Settings['elevenlabs_app_id']), $session, 1, '', '');
                                    $result = aiomatic_elevenlabs_stream($voice, $message, 'aiomatic_Spinner_Settings');
                                    if(is_array($result)){
                                        aiomatic_log_to_file('Error occurred in ElevenLabs AI audio processing: ' . print_r($result, true));
                                    }
                                    else
                                    {
                                        apply_filters( 'aiomatic_ai_reply_text', $query, $message );
                                        $localfile = aiomatic_copy_audio_stream_locally($result, 'audio_' . time() . '.mp3', $copy_it);
                                        if($localfile === false)
                                        {
                                            aiomatic_log_to_file('Failed to save audio file locally to your server.');
                                        }
                                        else
                                        {
                                            $retpath = $localfile[0];
                                            $prep_txt = '';
                                            if (isset($aiomatic_Spinner_Settings['prep_audio']) && $aiomatic_Spinner_Settings['prep_audio'] != '')
                                            {
                                                $prep_txt = $aiomatic_Spinner_Settings['prep_audio'];
                                            }
                                            if (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'append') 
                                            {
                                                $final_content = $final_content . $prep_txt . ' <br/> [audio src="' . $retpath . '"]';
                                                $updated = true;
                                            }
                                            elseif (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'preppend')
                                            {
                                                $final_content = $prep_txt . '[audio src="' . $retpath . '"] <br/> ' . $final_content;
                                                $updated = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elseif($aiomatic_Spinner_Settings['content_text_speech'] == 'google')
                {
                    if(!isset($aiomatic_Spinner_Settings['text_to_audio']) || empty($aiomatic_Spinner_Settings['text_to_audio']))
                    {
                        aiomatic_log_to_file('No text to send to text-to-speech!');
                    }
                    else
                    {
                        if (!isset($aiomatic_Main_Settings['google_app_id'])) 
                        {
                            $aiomatic_Main_Settings['google_app_id'] = '';
                        }
                        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['google_app_id']));
                        $appids = array_filter($appids);
                        $token = $appids[array_rand($appids)];
                        $token = apply_filters('aiomatic_google_api_key', $token);
                        if (empty($token))
                        {
                            aiomatic_log_to_file('You need to enter an Google API key for this to work!');
                        }
                        else
                        {
                            if(isset($aiomatic_Spinner_Settings['google_voice']) && $aiomatic_Spinner_Settings['google_voice'] != '')
                            {
                                $voice = $aiomatic_Spinner_Settings['google_voice'];
                                if(isset($aiomatic_Spinner_Settings['audio_profile']) && $aiomatic_Spinner_Settings['audio_profile'] != '')
                                {
                                    $audio_profile = $aiomatic_Spinner_Settings['audio_profile'];
                                }
                                else
                                {
                                    $audio_profile = '';
                                }
                                if(isset($aiomatic_Spinner_Settings['voice_language']) && $aiomatic_Spinner_Settings['voice_language'] != '')
                                {
                                    $voice_language = $aiomatic_Spinner_Settings['voice_language'];
                                    if(isset($aiomatic_Spinner_Settings['voice_speed']) && $aiomatic_Spinner_Settings['voice_speed'] != '')
                                    {
                                        $voice_speed = $aiomatic_Spinner_Settings['voice_speed'];
                                    }
                                    else
                                    {
                                        $voice_speed = '';
                                    }
                                    if(isset($aiomatic_Spinner_Settings['voice_pitch']) && $aiomatic_Spinner_Settings['voice_pitch'] != '')
                                    {
                                        $voice_pitch = $aiomatic_Spinner_Settings['voice_pitch'];
                                    }
                                    else
                                    {
                                        $voice_pitch = '';
                                    }
                                    $message = trim($aiomatic_Spinner_Settings['text_to_audio']);
                                    $message = aiomatic_replaceSynergyShortcodes($message);
                                    if(!empty($message))
                                    {
                                        $message = aiomatic_replaceAIPostShortcodes($message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                        $session = aiomatic_get_session_id();
                                        $message = wp_strip_all_tags($message);
                                        if(!empty($message))
                                        {
                                            $query = new Aiomatic_Query($message, 0, 'google', '0', '', 'text-to-speech', 'text-to-speech', trim($aiomatic_Main_Settings['google_app_id']), $session, 1, '', '');
                                            $result = aiomatic_google_stream($voice, $voice_language, $audio_profile, $voice_speed, $voice_pitch, $message);
                                            if(is_array($result))
                                            {
                                                if(isset($result['status']) && $result['status'] == 'success')
                                                {
                                                    apply_filters( 'aiomatic_ai_reply_text', $query, $message );
                                                    $decodedAudio = base64_decode($result['audio']);
                                                    $localfile = aiomatic_copy_audio_stream_locally($decodedAudio, 'audio_' . time() . '.mp3', $copy_it);
                                                    if($localfile === false)
                                                    {
                                                        aiomatic_log_to_file('Failed to save audio file locally to your server.');
                                                    }
                                                    else
                                                    {
                                                        $retpath = $localfile[0];
                                                        $prep_txt = '';
                                                        if (isset($aiomatic_Spinner_Settings['prep_audio']) && $aiomatic_Spinner_Settings['prep_audio'] != '')
                                                        {
                                                            $prep_txt = $aiomatic_Spinner_Settings['prep_audio'];
                                                        }
                                                        if (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'append') 
                                                        {
                                                            $final_content = $final_content . $prep_txt . ' <br/> [audio src="' . $retpath . '"]';
                                                            $updated = true;
                                                        }
                                                        elseif (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'preppend')
                                                        {
                                                            $final_content = $prep_txt . '[audio src="' . $retpath . '"] <br/> ' . $final_content;
                                                            $updated = true;
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    aiomatic_log_to_file('Failed to generate Google Audio AI output: ' . print_r($result, true));
                                                }
                                            }
                                            else
                                            {
                                                aiomatic_log_to_file('Failed to generate Google AI Audio output: ' . print_r($result, true));
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    aiomatic_log_to_file('You need to select a Google Text-to-Speech Voice Language for this feature to work.');
                                }
                            }
                            else
                            {
                                aiomatic_log_to_file('You need to select a Google Text-to-Speech Voice Name for this feature to work.');
                            }
                        }
                    }
                }
                elseif($aiomatic_Spinner_Settings['content_text_speech'] == 'did')
                {
                    if(!isset($aiomatic_Spinner_Settings['text_to_audio']) || empty($aiomatic_Spinner_Settings['text_to_audio']))
                    {
                        aiomatic_log_to_file('No text to send to text-to-video!');
                    }
                    else
                    {
                        if (!isset($aiomatic_Main_Settings['did_app_id'])) 
                        {
                            $aiomatic_Main_Settings['did_app_id'] = '';
                        }
                        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['did_app_id']));
                        $appids = array_filter($appids);
                        $token = $appids[array_rand($appids)];
                        $token = apply_filters('aiomatic_did_api_key', $token);
                        if (empty($token))
                        {
                            aiomatic_log_to_file('You need to enter an D-ID API key for this to work!');
                        }
                        else
                        {
                            if(isset($aiomatic_Spinner_Settings['did_image']) && $aiomatic_Spinner_Settings['did_image'] != '')
                            {
                                $did_image = $aiomatic_Spinner_Settings['did_image'];
                            }
                            else
                            {
                                $did_image = 'https://create-images-results.d-id.com/api_docs/assets/noelle.jpeg';
                            }
                            if(isset($aiomatic_Spinner_Settings['did_voice']) && $aiomatic_Spinner_Settings['did_voice'] != '')
                            {
                                $did_voice = $aiomatic_Spinner_Settings['did_voice'];
                            }
                            else
                            {
                                $did_voice = 'microsoft:en-US-JennyNeural:Cheerful';
                            }
                            $message = trim($aiomatic_Spinner_Settings['text_to_audio']);
                            $message = aiomatic_replaceSynergyShortcodes($message);
                            if(!empty($message))
                            {
                                $message = aiomatic_replaceAIPostShortcodes($message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                $session = aiomatic_get_session_id();
                                $message = wp_strip_all_tags($message);
                                if(!empty($message))
                                {
                                    $query = new Aiomatic_Query($message, 0, 'd-id', '0', '', 'text-to-speech', 'text-to-speech', trim($aiomatic_Main_Settings['did_app_id']), $session, 1, '', '');
                                    $result = aiomatic_d_id_video($did_image, $message, $did_voice);
                                    if(is_array($result)){
                                        if(isset($result['status']) && $result['status'] == 'success')
                                        {
                                            apply_filters( 'aiomatic_ai_reply_text', $query, $message );
                                            $video_url = $result['video'];
                                            $localfile = aiomatic_copy_video_locally($video_url, 'video_' . time(), $copy_it);
                                            if($localfile === false)
                                            {
                                                aiomatic_log_to_file('Failed to save video file locally to your server.');
                                            }
                                            else
                                            {
                                                $retpath = $localfile[0];
                                                $prep_txt = '';
                                                if (isset($aiomatic_Spinner_Settings['prep_audio']) && $aiomatic_Spinner_Settings['prep_audio'] != '')
                                                {
                                                    $prep_txt = $aiomatic_Spinner_Settings['prep_audio'];
                                                }
                                                if (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'append') 
                                                {
                                                    $final_content = $final_content . $prep_txt . ' <br/> [video src="' . $retpath . '"]';
                                                    $updated = true;
                                                }
                                                elseif (isset($aiomatic_Spinner_Settings['audio_location']) && $aiomatic_Spinner_Settings['audio_location'] == 'preppend')
                                                {
                                                    $final_content = $prep_txt . '[video src="' . $retpath . '"] <br/> ' . $final_content;
                                                    $updated = true;
                                                }
                                            }
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('D-ID AI video failed: ' . print_r($result, true));
                                        }
                                    }
                                    else
                                    {
                                        aiomatic_log_to_file('Failed to generate D-ID AI video output: ' . print_r($result, true));
                                    }
                                }
                            }
                        }
                    }
                }
                if($updated == true)
                {
                    $args = array();
                    $args['ID'] = $post->ID;
                    $args['post_content'] = $final_content;
                    if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                    {
                        $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                    }
                    if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                    {
                        $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                        if($date_stamp)
                        {
                            $postdate = date("Y-m-d H:i:s", $date_stamp);
                            $args['post_date'] = $postdate;
                        }
                    }
                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    remove_filter('title_save_pre', 'wp_filter_kses');
                    update_post_meta($post->ID, $custom_name, "pub");
                    $post_updated = wp_update_post($args);
                    add_filter('content_save_pre', 'wp_filter_post_kses');
                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    add_filter('title_save_pre', 'wp_filter_kses');
                    if (is_wp_error($post_updated)) {
                        $errors = $post_updated->get_error_messages();
                        foreach ($errors as $error) {
                            aiomatic_log_to_file('Error occured while updating post for AI content "' . $post->post_title . '": ' . $error);
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated audio/video content.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Spinner_Settings['content_speech_text']) && $aiomatic_Spinner_Settings['content_speech_text'] != '' && $aiomatic_Spinner_Settings['content_speech_text'] != 'off')
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
                {
                    aiomatic_log_to_file('Starting post speech-to-text module...');
                }
                $updated = false;
                if($aiomatic_Spinner_Settings['content_speech_text'] == 'openai')
                {
                    if(!isset($aiomatic_Spinner_Settings['audio_to_text']) || empty($aiomatic_Spinner_Settings['audio_to_text']))
                    {
                        $audio_to_text = '%%audio_to_text%%';
                    }
                    else
                    {
                        $audio_to_text = $aiomatic_Spinner_Settings['audio_to_text'];
                    }
                    if (!isset($aiomatic_Main_Settings['app_id'])) 
                    {
                        $aiomatic_Main_Settings['app_id'] = '';
                    }
                    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                    $appids = array_filter($appids);
                    $token = $appids[array_rand($appids)];
                    $token = apply_filters('aiomatic_openai_api_key', $token);
                    if (empty($token))
                    {
                        aiomatic_log_to_file('You need to enter an OpenAI API key for this to work!');
                    }
                    else
                    {
                        if(aiomatic_is_aiomaticapi_key($token) || (aiomatic_check_if_azure_or_others($aiomatic_Main_Settings)))
                        {
                            aiomatic_log_to_file('Only OpenAI API keys are supported at the moment.');
                        }
                        else
                        {
                            require_once (dirname(__FILE__) . "/res/openai/Url.php"); 
                            require_once (dirname(__FILE__) . "/res/openai/OpenAi.php"); 
                            $open_ai = new OpenAi($token);
                            if(!$open_ai){
                                aiomatic_log_to_file('Failed to init speech-to-text OpenAI API');
                            }
                            else
                            {
                                if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
                                {
                                    $open_ai->setCustomURL(trim($aiomatic_Main_Settings['custom_endpoint'], ' /'));
                                }
                                if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
                                {
                                    $open_ai->setORG($aiomatic_Main_Settings['openai_organization']);
                                }
                                if(!isset($aiomatic_Spinner_Settings['audio_to_text_prompt']) || empty($aiomatic_Spinner_Settings['audio_to_text_prompt']))
                                {
                                    $audio_to_text_prompt = '';
                                }
                                else
                                {
                                    $audio_to_text_prompt = $aiomatic_Spinner_Settings['audio_to_text_prompt'];
                                    $audio_to_text_prompt = aiomatic_replaceSynergyShortcodes($audio_to_text_prompt);
                                    $audio_to_text_prompt = aiomatic_replaceAIPostShortcodes($audio_to_text_prompt, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                }
                                if(!isset($aiomatic_Spinner_Settings['speech_temperature']) || empty($aiomatic_Spinner_Settings['speech_temperature']))
                                {
                                    $speech_temperature = '';
                                }
                                else
                                {
                                    $speech_temperature = $aiomatic_Spinner_Settings['speech_temperature'];
                                }
                                if(!isset($aiomatic_Spinner_Settings['speech_model']) || empty($aiomatic_Spinner_Settings['speech_model']))
                                {
                                    $speech_model = 'whisper-1';
                                }
                                else
                                {
                                    $speech_model = $aiomatic_Spinner_Settings['speech_model'];
                                }
                                if(!isset($aiomatic_Spinner_Settings['max_speech']) || empty($aiomatic_Spinner_Settings['max_speech']))
                                {
                                    $max_speech = '';
                                }
                                else
                                {
                                    $max_speech = intval($aiomatic_Spinner_Settings['max_speech']);
                                }
                                $xpattern = '/https?:\/\/[^\s"]+?\.(mp3|mp4|mpeg|mpga|m4a|wav|webm)/i';
                                preg_match_all($xpattern, $final_content, $matches);
                                if(count($matches[0]) > 0)
                                {
                                    $processd = 0;
                                    foreach($matches[0] as $url)
                                    {
                                        if($max_speech !== '' && $processd >= $max_speech)
                                        {
                                            break;
                                        }
                                        if(!function_exists('download_url')){
                                            include_once( ABSPATH . 'wp-admin/includes/file.php' );
                                        }
                                        $tmp_file = download_url($url);
                                        if ( is_wp_error( $tmp_file ) ){
                                            aiomatic_log_to_file('Failed to read audio file in speech-to-text: ' . $tmp_file->get_error_message());
                                        }
                                        else
                                        {
                                            $response_format = 'text';
                                            global $wp_filesystem;
                                            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
                                                include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
                                                wp_filesystem($creds);
                                            }
                                            $file_name = 'speech-to-text.mp3';
                                            $data_request = array(
                                                'audio' => array(
                                                    'filename' => $file_name,
                                                    'data' => $wp_filesystem->get_contents($tmp_file)
                                                ),
                                                'model' => $speech_model,
                                                'temperature' => $speech_temperature,
                                                'response_format' => $response_format,
                                                'prompt' => $audio_to_text_prompt
                                            );
                                            if(!empty($language)){
                                                $data_request['language'] = $language;
                                            }
                                            $delay = '';
                                            if (isset($aiomatic_Main_Settings['request_delay']) && $aiomatic_Main_Settings['request_delay'] != '') 
                                            {
                                                if(stristr($aiomatic_Main_Settings['request_delay'], ',') !== false)
                                                {
                                                    $tempo = explode(',', $aiomatic_Main_Settings['request_delay']);
                                                    if(isset($tempo[1]) && is_numeric(trim($tempo[1])) && is_numeric(trim($tempo[0])))
                                                    {
                                                        $delay = rand(trim($tempo[0]), trim($tempo[1]));
                                                    }
                                                }
                                                else
                                                {
                                                    if(is_numeric(trim($aiomatic_Main_Settings['request_delay'])))
                                                    {
                                                        $delay = intval(trim($aiomatic_Main_Settings['request_delay']));
                                                    }
                                                }
                                            }
                                            if($delay != '' && is_numeric($delay))
                                            {
                                                usleep($delay);
                                            }
                                            $session = aiomatic_get_session_id();
                                            $query = new Aiomatic_Query($audio_to_text_prompt, 0, 'openai-' . $speech_model, '0', '', 'speech-to-text', 'speech-to-text', $token, $session, 1, '', '');
                                            $completion = $open_ai->transcribe($data_request);
                                            $result = json_decode($completion);
                                            if($result && isset($result->error))
                                            {
                                                aiomatic_log_to_file('Failed to transcribe audio to text using OpenAI: ' . $result->error->message);
                                            }
                                            else
                                            {
                                                apply_filters( 'aiomatic_ai_reply_text', $query, $audio_to_text );
                                                $audio_to_text = str_replace('%%audio_to_text%%', $completion, $audio_to_text);
                                                $audio_to_text = aiomatic_replaceSynergyShortcodes($audio_to_text);
                                                $audio_to_text = aiomatic_replaceAIPostShortcodes($audio_to_text, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, $img_attr, '', '', '', '', '');
                                                if (isset($aiomatic_Spinner_Settings['audio_text_location']) && $aiomatic_Spinner_Settings['audio_text_location'] == 'append') 
                                                {
                                                    $final_content = $final_content . ' ' . $audio_to_text;
                                                    $updated = true;
                                                }
                                                elseif (isset($aiomatic_Spinner_Settings['audio_text_location']) && $aiomatic_Spinner_Settings['audio_text_location'] == 'preppend')
                                                {
                                                    $final_content = $audio_to_text . ' ' . $final_content;
                                                    $updated = true;
                                                }
                                            }     
                                        }
                                    }
                                }
                                else
                                {
                                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                        aiomatic_log_to_file('Post ID ' . $post->ID . ' does not contain any audio files to transcribe.');
                                    }
                                }
                            }
                        }
                    }
                }
                if($updated == true)
                {
                    $args = array();
                    $args['ID'] = $post->ID;
                    $args['post_content'] = $final_content;
                    if (isset($aiomatic_Spinner_Settings['change_status']) && $aiomatic_Spinner_Settings['change_status'] != '' && $aiomatic_Spinner_Settings['change_status'] != 'no') 
                    {
                        $args['post_status'] = $aiomatic_Spinner_Settings['change_status'];
                    }
                    if (isset($aiomatic_Spinner_Settings['change_date']) && trim($aiomatic_Spinner_Settings['change_date']) != '') 
                    {
                        $date_stamp = strtotime(str_replace('%%post_date%%', $post->post_date, trim($aiomatic_Spinner_Settings['change_date'])));
                        if($date_stamp)
                        {
                            $postdate = date("Y-m-d H:i:s", $date_stamp);
                            $args['post_date'] = $postdate;
                        }
                    }
                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    remove_filter('title_save_pre', 'wp_filter_kses');
                    update_post_meta($post->ID, $custom_name, "pub");
                    $post_updated = wp_update_post($args);
                    add_filter('content_save_pre', 'wp_filter_post_kses');
                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    add_filter('title_save_pre', 'wp_filter_kses');
                    if (is_wp_error($post_updated)) {
                        $errors = $post_updated->get_error_messages();
                        foreach ($errors as $error) {
                            aiomatic_log_to_file('Error occured while updating post for AI content "' . $post->post_title . '": ' . $error);
                        }
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Post ID ' . $post->ID . ' "' . $post->post_title . '" was successfully updated with AI generated audio-to-text content.');
                        }
                    }
                }
            }
            if (isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on') 
            {
                aiomatic_log_to_file('Finished editing post ID: ' . $pid);
            }
        }
    }
}
?>