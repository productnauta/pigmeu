<?php
defined('ABSPATH') or die();

function aiomatic_realtime_chat_shortcode($atts, $cont = '') {
    if(!aiomatic_validate_activation())
    {
        return '';
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
    {
        return 'You need to add an OpenAI API key in plugin\'s settings to use this feature.';
    }
    if (aiomatic_check_if_azure_or_others($aiomatic_Main_Settings)) 
    {
        return 'Azure/Claude API is not currently supported for realtime chat API.';
    }
    extract( shortcode_atts( array (
        'model' => AIOMATIC_DEFAULT_REALTIME_MODEL,
        'voice' => AIOMATIC_DEFAULT_REALTIME_VOICE,
        'temperature' => 1,
        'max_tokens' => 4096,
        'instructions' => '',
        'autostart' => 'no',
        'show_chat_log' => 'show',
        'show_user_log' => 'show',
        'start_recording' => 'Start Recording', 
        'stop_recording' => 'Stop Recording',
        'not_recording' => 'Not Recording', 
        'recording' => 'Recording...',
        'record_button_color' => '#007bff',
        'record_text_color' => '#ffffff',
        'record_status_color' => '#f8f9fa',
        'record_status_text_color' => '#333333',
        'chat_container_color' => '#e4e8ec',
        'chat_log_container_color' => '#ffffff',
        'ai_bubble_text_color' => '#000000',
        'ai_bubble_color' => '#f5f5f5',
        'user_bubble_text_color' => '#ffffff',
        'enable_god_mode' => '',
        'user_token_cap_per_day' => '',
        'user_bubble_color' => '#0084ff'
    ), $atts ) );
    $name = md5(get_bloginfo());
    wp_enqueue_style($name . '-realtime-css', plugins_url('styles/realtime.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    
    $reg_css_code = '';
    if (trim($record_button_color) != '')
    {
        $reg_css_code .= '#aiomatic-realtime-toggle{background-color:' . trim($record_button_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($record_text_color) != '')
    {
        $reg_css_code .= '#aiomatic-realtime-toggle{color:' . trim($record_text_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($record_status_color) != '')
    {
        $reg_css_code .= '#aiomatic-realtime-status{background-color:' . trim($record_status_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($record_status_text_color) != '')
    {
        $reg_css_code .= '#aiomatic-realtime-status{color:' . trim($record_status_text_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($chat_container_color) != '')
    {
        $reg_css_code .= '#aiomatic-realtime-chat-container{background-color:' . trim($chat_container_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($chat_log_container_color) != '')
    {
        $reg_css_code .= '#aiomatic-realtime-log{background-color:' . trim($chat_log_container_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($ai_bubble_text_color) != '')
    {
        $reg_css_code .= '.ai-bubble.ai-transcript{color:' . trim($ai_bubble_text_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($ai_bubble_color) != '')
    {
        $reg_css_code .= '.ai-bubble.ai-transcript{background-color:' . trim($ai_bubble_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if (trim($user_bubble_text_color) != '')
    {
        $reg_css_code .= '.ai-bubble.ai-speech{color:' . trim($user_bubble_text_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    if (trim($user_bubble_color) != '')
    {
        $reg_css_code .= '.ai-bubble.ai-speech{background-color:' . trim($user_bubble_color) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    }
    if($reg_css_code != '')
    {
        wp_add_inline_style( $name . '-realtime-css', $reg_css_code );
    }
    wp_enqueue_script($name . '-realtime-chat', plugins_url('scripts/realtime-chat.js', __FILE__), false, AIOMATIC_MAJOR_VERSION );
    wp_localize_script($name . '-realtime-chat', 'aiomatic_realtime', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('openai-ajax-nonce'),
        'persistentnonce' => wp_create_nonce('openai-persistent-nonce'),
        'model' => $model,
        'voice' => $voice,
        'temperature' => $temperature, 
        'max_tokens' => $max_tokens,
        'instructions' => $instructions,
        'autostart' => $autostart,
        'show_chat_log' => $show_chat_log,
        'show_user_log' => $show_user_log,
        'start_recording' => $start_recording,
        'stop_recording' => $stop_recording,
        'not_recording' => $not_recording,
        'recording' => $recording,
        'enable_god_mode' => $enable_god_mode,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id
    ));
    ob_start();
    ?>
    <div class="aiomatic-realtime-chat-container" id="aiomatic-realtime-chat-container">
    <?php
    if($show_chat_log == 'show')
    {?>
    <div id="aiomatic-realtime-log">
    </div>
    <?php } ?>
    <form id="openai-ai-realtime-chat-form" method="post" class="openai-ai-form">
    <div id="aiomatic-realtime-status"><?php echo esc_html($not_recording);?></div>
        <button type="button" class="airealtimechatsubmitbut btn btn-primary" id="aiomatic-realtime-toggle"><?php echo esc_html($start_recording);?></button>
</form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aiomatic-realtime-chat', 'aiomatic_realtime_chat_shortcode');

add_shortcode('aicontent', 'aiomatic_resolve_aicontent');
function aiomatic_resolve_aicontent($atts, $cont, $tagx)
{
    $retme = '';
    global $post;
    extract( shortcode_atts( array (
        'model' => '',
        'type' => 'text',
        'image_size' => '1024x1024',
        'repeat_for_each_line' => '',
        'static_content' => '',
        'cache_seconds' => 2592000
    ), $atts ) );
    if(!aiomatic_validate_activation())
    {
        return;
    }
    if(isset($post->ID) && $static_content == 'on')
    {
        $md5v = md5($post->ID . $model . $type . $image_size . $repeat_for_each_line . $cont);
    }
    else
    {
        $md5v = '';
    }
    if($cont != '')
    {
        $all_models = aiomatic_get_all_models();
        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
        if (isset($aiomatic_Main_Settings['app_id']) && trim($aiomatic_Main_Settings['app_id']) != '') 
        {
            if(isset($post->ID) && $static_content == 'on')
            {
                $tranzi = get_transient('aiomatic_aicontent_transient' . $md5v);
                if(!empty($tranzi))
                {
                    return $tranzi;
                }
            }
            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
            $appids = array_filter($appids);
            $token = $appids[array_rand($appids)];
            $orig_cont = $cont;
            $repeat_for_each_line_arr = preg_split('/\r\n|\r|\n/', trim($repeat_for_each_line));
            foreach($repeat_for_each_line_arr as $current_line)
            {
                $cont = $orig_cont;
                $cont = str_replace('%%current_line%%', $current_line, $cont);
                if($type == 'text')
                {
                    if(empty($model) || !in_array($model, $all_models))
                    {
                        if (isset($aiomatic_Main_Settings['aicontent_model']) && trim($aiomatic_Main_Settings['aicontent_model']) != '') 
                        {
                            $model = trim($aiomatic_Main_Settings['aicontent_model']);
                        }
                        else
                        {
                            $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                        }
                    }
                    if (isset($aiomatic_Main_Settings['aicontent_assistant_id']) && trim($aiomatic_Main_Settings['aicontent_assistant_id']) != '') 
                    {
                        $aicontent_assistant_id = trim($aiomatic_Main_Settings['aicontent_assistant_id']);
                    }
                    else
                    {
                        $aicontent_assistant_id = '';
                    }
                    if (isset($aiomatic_Main_Settings['aicontent_temperature']) && trim($aiomatic_Main_Settings['aicontent_temperature']) != '') 
                    {
                        $temperature = floatval($aiomatic_Main_Settings['aicontent_temperature']);
                    }
                    else
                    {
                        $temperature = 1;
                    }
                    if (isset($aiomatic_Main_Settings['aicontent_top_p']) && trim($aiomatic_Main_Settings['aicontent_top_p']) != '') 
                    {
                        $top_p = floatval($aiomatic_Main_Settings['aicontent_top_p']);
                    }
                    else
                    {
                        $top_p = 1;
                    }
                    if (isset($aiomatic_Main_Settings['aicontent_presence_penalty']) && trim($aiomatic_Main_Settings['aicontent_presence_penalty']) != '') 
                    {
                        $presence_penalty = floatval($aiomatic_Main_Settings['aicontent_presence_penalty']);
                    }
                    else
                    {
                        $presence_penalty = 0;
                    }
                    if (isset($aiomatic_Main_Settings['aicontent_frequency_penalty']) && trim($aiomatic_Main_Settings['aicontent_frequency_penalty']) != '') 
                    {
                        $frequency_penalty = floatval($aiomatic_Main_Settings['aicontent_frequency_penalty']);
                    }
                    else
                    {
                        $frequency_penalty = 0;
                    }

                    $max_tokens = aiomatic_get_max_tokens($model);
                    $prompt = trim($cont);
                    $query_token_count = count(aiomatic_encode($prompt));
                    $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                    if($available_tokens < AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                    {
                        $string_len = strlen($prompt);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $prompt = aiomatic_substr($prompt, 0, $string_len);
                        $prompt = trim($prompt);
                        $query_token_count = count(aiomatic_encode($prompt));
                        $available_tokens = $max_tokens - $query_token_count;
                    }
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                    {
                        $api_service = aiomatic_get_api_service($token, $model);
                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $aicontent_assistant_id . '/' . $model . ')(' . $available_tokens . ') for aicontent WP shortcode text generator: ' . $prompt);
                    }
                    $thread_id = '';
                    $aierror = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'WPAicontentShortcode', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', $aicontent_assistant_id, $thread_id, '', 'disabled', '', true, false, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('Failed to generate WP AI content: ' . $aierror);
                    }
                    else
                    {
                        if($retme != '')
                        {
                            $retme .= ' ';
                        }
                        $retme .= trim(trim(trim(trim($generated_text), '.'), ' "\''));
                    }
                }
                elseif($type == 'image-openai')
                {
                    $prompt = trim($cont);
                    if(empty($image_size))
                    {
                        $image_size = '1024x1024';
                    }
                    if(empty($model))
                    {
                        $model = AIOMATIC_DEFAULT_IMAGE_MODEL;
                    }
                    $all_models = AIOMATIC_DALLE_IMAGE_MODELS;
                    if(!in_array($model, $all_models))
                    {
                        $model = AIOMATIC_DEFAULT_IMAGE_MODEL;
                    }
                    $aierror = '';
                    $result = aiomatic_generate_ai_image($token, 1, $prompt, $image_size, 'WPAicontentShortcodeImage', true, 0, $aierror, $model);
                    if($result === false)
                    {
                        aiomatic_log_to_file('Failed to generate WP AI image: ' . $aierror);
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled') 
                        {
                            $attached_id = '';
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally($result[0], $attached_id, $attached_id, '', false, $prompt);
                            if($localpath !== false)
                            {
                                if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] == 'on')
                                {
                                    if (isset($localpath[1]) && (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') || (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                    {
                                        try
                                        {
                                            if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
                                            $imageRes = new ImageResize($localpath[1]);
                                            if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
                                            {
                                                $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
                                            }
                                            else
                                            {
                                                $imageRes->quality_jpg = 100;
                                            }
                                            if ((isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') && (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                            {
                                                $imageRes->resizeToBestFit($aiomatic_Main_Settings['ai_resize_width'], $aiomatic_Main_Settings['ai_resize_height'], true);
                                            }
                                            elseif (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== '')
                                            {
                                                $imageRes->resizeToWidth($aiomatic_Main_Settings['ai_resize_width'], true);
                                            }
                                            elseif (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '')
                                            {
                                                $imageRes->resizeToHeight($aiomatic_Main_Settings['ai_resize_height'], true);
                                            }
                                            $imageRes->save($localpath[1]);
                                        }
                                        catch(Exception $e)
                                        {
                                            aiomatic_log_to_file('Failed to resize AI generated image: ' . $localpath[0] . ' to sizes ' . $aiomatic_Main_Settings['ai_resize_width'] . ' - ' . $aiomatic_Main_Settings['ai_resize_height'] . '. Exception thrown ' . esc_html($e->getMessage()) . '!');
                                        }
                                    }
                                }
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme = $localpath[0];
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to copy AI generated image locally: ' . $retme);
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme .= $result[0];
                            }
                        }
                        else
                        {
                            if($retme != '')
                            {
                                $retme .= ' ';
                            }
                            $retme .= $result[0];
                        }
                    }
                }
                elseif($type == 'image-stable')
                {
                    $prompt = trim($cont);
                    if(empty($image_size))
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
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    $aierror = '';
                    $result = aiomatic_generate_stability_image($prompt, $height, $width, 'WPAicontentShortcodeStableImage', 0, true, $aierror, false, false, '', 0);
                    if($result === false)
                    {
                        aiomatic_log_to_file('Failed to generate WP AI image: ' . $aierror);
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled') 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally('data:image/png;base64,' . $result, $attached_id, $aiomatic_Main_Settings['copy_locally'], false, $prompt);
                            if($localpath !== false)
                            {
                                if($localpath !== false)
                                {
                                    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] == 'on')
                                    {
                                        if (isset($localpath[1]) && (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') || (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                        {
                                            try
                                            {
                                                if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
                                                $imageRes = new ImageResize($localpath[1]);
                                                if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
                                                {
                                                    $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
                                                }
                                                else
                                                {
                                                    $imageRes->quality_jpg = 100;
                                                }
                                                if ((isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') && (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                                {
                                                    $imageRes->resizeToBestFit($aiomatic_Main_Settings['ai_resize_width'], $aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== '')
                                                {
                                                    $imageRes->resizeToWidth($aiomatic_Main_Settings['ai_resize_width'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '')
                                                {
                                                    $imageRes->resizeToHeight($aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                $imageRes->save($localpath[1]);
                                            }
                                            catch(Exception $e)
                                            {
                                                aiomatic_log_to_file('Failed to resize AI generated image: ' . $localpath[0] . ' to sizes ' . $aiomatic_Main_Settings['ai_resize_width'] . ' - ' . $aiomatic_Main_Settings['ai_resize_height'] . '. Exception thrown ' . esc_html($e->getMessage()) . '!');
                                            }
                                        }
                                    }
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to copy AI generated image locally: ' . $retme);
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                            }
                            else
                            {
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme .= $result;
                            }
                        }
                        else
                        {
                            if($retme != '')
                            {
                                $retme .= ' ';
                            }
                            $retme .= $result;
                        }
                    }
                }
                elseif($type == 'image-google')
                {
                    $prompt = trim($cont);
                    $aierror = '';
                    $result = aiomatic_generate_image_google($seed_expre, $model, 'WPAicontentShortcodeGoogleImage', true, $aierror, false);
                    if($result === false)
                    {
                        aiomatic_log_to_file('Failed to generate WP AI image: ' . $aierror);
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled') 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally('data:image/png;base64,' . $result, $attached_id, $aiomatic_Main_Settings['copy_locally'], false, $prompt);
                            if($localpath !== false)
                            {
                                if($localpath !== false)
                                {
                                    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] == 'on')
                                    {
                                        if (isset($localpath[1]) && (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') || (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                        {
                                            try
                                            {
                                                if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
                                                $imageRes = new ImageResize($localpath[1]);
                                                if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
                                                {
                                                    $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
                                                }
                                                else
                                                {
                                                    $imageRes->quality_jpg = 100;
                                                }
                                                if ((isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') && (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                                {
                                                    $imageRes->resizeToBestFit($aiomatic_Main_Settings['ai_resize_width'], $aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== '')
                                                {
                                                    $imageRes->resizeToWidth($aiomatic_Main_Settings['ai_resize_width'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '')
                                                {
                                                    $imageRes->resizeToHeight($aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                $imageRes->save($localpath[1]);
                                            }
                                            catch(Exception $e)
                                            {
                                                aiomatic_log_to_file('Failed to resize AI generated image: ' . $localpath[0] . ' to sizes ' . $aiomatic_Main_Settings['ai_resize_width'] . ' - ' . $aiomatic_Main_Settings['ai_resize_height'] . '. Exception thrown ' . esc_html($e->getMessage()) . '!');
                                            }
                                        }
                                    }
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to copy AI generated image locally: ' . $retme);
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                            }
                            else
                            {
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme .= $result;
                            }
                        }
                        else
                        {
                            if($retme != '')
                            {
                                $retme .= ' ';
                            }
                            $retme .= $result;
                        }
                    }
                }
                elseif($type == 'image-midjourney')
                {
                    $prompt = trim($cont);
                    if(empty($image_size))
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
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    if(strlen($prompt) > 2000)
                    {
                        $prompt = aiomatic_substr($prompt, 0, 2000);
                    }
                    $aierror = '';
                    $result = aiomatic_generate_ai_image_midjourney($prompt, $width, $height, 'WPAicontentShortcodeMidjourneyImage', true, $aierror);
                    if($result === false)
                    {
                        aiomatic_log_to_file('Failed to generate WP AI image: ' . $aierror);
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled') 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally('' . $result, $attached_id, $aiomatic_Main_Settings['copy_locally'], false, $prompt);
                            if($localpath !== false)
                            {
                                if($localpath !== false)
                                {
                                    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] == 'on')
                                    {
                                        if (isset($localpath[1]) && (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') || (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                        {
                                            try
                                            {
                                                if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
                                                $imageRes = new ImageResize($localpath[1]);
                                                if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
                                                {
                                                    $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
                                                }
                                                else
                                                {
                                                    $imageRes->quality_jpg = 100;
                                                }
                                                if ((isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') && (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                                {
                                                    $imageRes->resizeToBestFit($aiomatic_Main_Settings['ai_resize_width'], $aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== '')
                                                {
                                                    $imageRes->resizeToWidth($aiomatic_Main_Settings['ai_resize_width'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '')
                                                {
                                                    $imageRes->resizeToHeight($aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                $imageRes->save($localpath[1]);
                                            }
                                            catch(Exception $e)
                                            {
                                                aiomatic_log_to_file('Failed to resize AI generated image: ' . $localpath[0] . ' to sizes ' . $aiomatic_Main_Settings['ai_resize_width'] . ' - ' . $aiomatic_Main_Settings['ai_resize_height'] . '. Exception thrown ' . esc_html($e->getMessage()) . '!');
                                            }
                                        }
                                    }
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to copy AI generated image locally: ' . $retme);
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                            }
                            else
                            {
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme .= $result;
                            }
                        }
                        else
                        {
                            if($retme != '')
                            {
                                $retme .= ' ';
                            }
                            $retme .= $result;
                        }
                    }
                }
                elseif($type == 'image-replicate')
                {
                    $prompt = trim($cont);
                    if(empty($image_size))
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
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    if(strlen($prompt) > 2000)
                    {
                        $prompt = aiomatic_substr($prompt, 0, 2000);
                    }
                    $aierror = '';
                    $result = aiomatic_generate_replicate_image($prompt, $width, $height, 'WPAicontentShortcodeReplicateImage', true, $aierror);
                    if($result === false)
                    {
                        aiomatic_log_to_file('Failed to generate WP AI image: ' . $aierror);
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled') 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally('' . $result, $attached_id, $aiomatic_Main_Settings['copy_locally'], false, $prompt);
                            if($localpath !== false)
                            {
                                if($localpath !== false)
                                {
                                    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] == 'on')
                                    {
                                        if (isset($localpath[1]) && (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') || (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                        {
                                            try
                                            {
                                                if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
                                                $imageRes = new ImageResize($localpath[1]);
                                                if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
                                                {
                                                    $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
                                                }
                                                else
                                                {
                                                    $imageRes->quality_jpg = 100;
                                                }
                                                if ((isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') && (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                                {
                                                    $imageRes->resizeToBestFit($aiomatic_Main_Settings['ai_resize_width'], $aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== '')
                                                {
                                                    $imageRes->resizeToWidth($aiomatic_Main_Settings['ai_resize_width'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '')
                                                {
                                                    $imageRes->resizeToHeight($aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                $imageRes->save($localpath[1]);
                                            }
                                            catch(Exception $e)
                                            {
                                                aiomatic_log_to_file('Failed to resize AI generated image: ' . $localpath[0] . ' to sizes ' . $aiomatic_Main_Settings['ai_resize_width'] . ' - ' . $aiomatic_Main_Settings['ai_resize_height'] . '. Exception thrown ' . esc_html($e->getMessage()) . '!');
                                            }
                                        }
                                    }
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to copy AI generated image locally: ' . $retme);
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                            }
                            else
                            {
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme .= $result;
                            }
                        }
                        else
                        {
                            if($retme != '')
                            {
                                $retme .= ' ';
                            }
                            $retme .= $result;
                        }
                    }
                }
                elseif($type == 'image-ideogram')
                {
                    $prompt = trim($cont);
                    if(empty($image_size))
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
                    else
                    {
                        $width = '512';
                        $height = '512';
                    }
                    if(strlen($prompt) > 2000)
                    {
                        $prompt = aiomatic_substr($prompt, 0, 2000);
                    }
                    $aierror = '';
                    $result = aiomatic_generate_ideogram_image($prompt, $width, $height, 'WPAicontentShortcodeIdeogramImage', true, $aierror);
                    if($result === false)
                    {
                        aiomatic_log_to_file('Failed to generate WP AI image: ' . $aierror);
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled') 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally('' . $result, $attached_id, $aiomatic_Main_Settings['copy_locally'], false, $prompt);
                            if($localpath !== false)
                            {
                                if($localpath !== false)
                                {
                                    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] == 'on')
                                    {
                                        if (isset($localpath[1]) && (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') || (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                        {
                                            try
                                            {
                                                if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
                                                $imageRes = new ImageResize($localpath[1]);
                                                if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
                                                {
                                                    $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
                                                }
                                                else
                                                {
                                                    $imageRes->quality_jpg = 100;
                                                }
                                                if ((isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') && (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                                {
                                                    $imageRes->resizeToBestFit($aiomatic_Main_Settings['ai_resize_width'], $aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== '')
                                                {
                                                    $imageRes->resizeToWidth($aiomatic_Main_Settings['ai_resize_width'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '')
                                                {
                                                    $imageRes->resizeToHeight($aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                $imageRes->save($localpath[1]);
                                            }
                                            catch(Exception $e)
                                            {
                                                aiomatic_log_to_file('Failed to resize AI generated image: ' . $localpath[0] . ' to sizes ' . $aiomatic_Main_Settings['ai_resize_width'] . ' - ' . $aiomatic_Main_Settings['ai_resize_height'] . '. Exception thrown ' . esc_html($e->getMessage()) . '!');
                                            }
                                        }
                                    }
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to copy AI generated image locally: ' . $retme);
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                            }
                            else
                            {
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme .= $result;
                            }
                        }
                        else
                        {
                            if($retme != '')
                            {
                                $retme .= ' ';
                            }
                            $retme .= $result;
                        }
                    }
                }
                elseif($type == 'image-royaltyfree')
                {
                    $prompt = trim($cont);
                    $img_attr = '';
                    $raw_img_list = array();
                    $full_result_list = array();
                    $attached_id = '';
                    $result = aiomatic_get_free_image($aiomatic_Main_Settings, $prompt, $img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                    if(!empty($result))
                    {
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled') 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally('' . $result, $attached_id, $aiomatic_Main_Settings['copy_locally'], false, $prompt);
                            if($localpath !== false)
                            {
                                if($localpath !== false)
                                {
                                    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] == 'on')
                                    {
                                        if (isset($localpath[1]) && (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') || (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                        {
                                            try
                                            {
                                                if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
                                                $imageRes = new ImageResize($localpath[1]);
                                                if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
                                                {
                                                    $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
                                                }
                                                else
                                                {
                                                    $imageRes->quality_jpg = 100;
                                                }
                                                if ((isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '') && (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== ''))
                                                {
                                                    $imageRes->resizeToBestFit($aiomatic_Main_Settings['ai_resize_width'], $aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_width']) && $aiomatic_Main_Settings['ai_resize_width'] !== '')
                                                {
                                                    $imageRes->resizeToWidth($aiomatic_Main_Settings['ai_resize_width'], true);
                                                }
                                                elseif (isset($aiomatic_Main_Settings['ai_resize_height']) && $aiomatic_Main_Settings['ai_resize_height'] !== '')
                                                {
                                                    $imageRes->resizeToHeight($aiomatic_Main_Settings['ai_resize_height'], true);
                                                }
                                                $imageRes->save($localpath[1]);
                                            }
                                            catch(Exception $e)
                                            {
                                                aiomatic_log_to_file('Failed to resize AI generated image: ' . $localpath[0] . ' to sizes ' . $aiomatic_Main_Settings['ai_resize_width'] . ' - ' . $aiomatic_Main_Settings['ai_resize_height'] . '. Exception thrown ' . esc_html($e->getMessage()) . '!');
                                            }
                                        }
                                    }
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to copy AI generated image locally: ' . $retme);
                                    if($retme != '')
                                    {
                                        $retme .= ' ';
                                    }
                                    $retme .= $localpath[0];
                                }
                            }
                            else
                            {
                                if($retme != '')
                                {
                                    $retme .= ' ';
                                }
                                $retme .= $result;
                            }
                        }
                        else
                        {
                            if($retme != '')
                            {
                                $retme .= ' ';
                            }
                            $retme .= $result;
                        }
                    }
                }
                else
                {
                    aiomatic_log_to_file('Unknown type provided for aicontent shortcode: ' . $type);
                }
            }
        }
    }
    if(!empty($retme) && isset($post->ID) && $static_content == 'on')
    {
        set_transient('aiomatic_aicontent_transient' . $md5v, $retme, intval($cache_seconds/10));
    }
    return $retme;
}
function aiomatic_replace_shortcode_with_content($post_id) 
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    {
        return;
    }
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) 
    {
        if (!current_user_can('access_aiomatic_menu', $post_id))
        {
            return;
        }
    } 
    else 
    {
        if (!current_user_can('access_aiomatic_menu', $post_id))
        {
            return;
        }
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['disable_aicontent_saving']) || $aiomatic_Main_Settings['disable_aicontent_saving'] != 'on')
    {
        $post = get_post($post_id);
        if($post !== null)
        {
            $content = $post->post_content;
            if (has_shortcode($content, 'aicontent')) 
            {
                $content = do_shortcode($content);
                remove_action('save_post', 'aiomatic_replace_shortcode_with_content');
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $content
                ));
                add_action('save_post', 'aiomatic_replace_shortcode_with_content');
            }
        }
    }
}
add_action('save_post', 'aiomatic_replace_shortcode_with_content');

add_shortcode('aiomatic-user-remaining-credits-text', 'aiomatic_remaining_credits');
function aiomatic_remaining_credits($atts)
{
    $current_user = wp_get_current_user();
    if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
    {
        $returnme = esc_html__('Please log in to your account to see usage info.', 'aiomatic-automatic-ai-content-writer');
        return $returnme;
    }
    else
    {
        $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
        return $GLOBALS['aiomatic_stats']->get_limits( $aiomatic_Limit_Settings );
    }
}
add_shortcode('aiomatic-form-history', 'aiomatic_form_history_shortcode');
function aiomatic_form_history_shortcode($atts) 
{
    if(!isset($atts['id']) || empty($atts['id'])) {
        return esc_html__('You need to specify the id parameter for this shortcode to work!', 'aiomatic-automatic-ai-content-writer');
    }
    $form_id = sanitize_text_field($atts['id']);
    $user_id = get_current_user_id();
    if(is_admin())
    {
        if(isset($atts['userid']))
        {
            $user_id = intval($atts['userid']);
        }
    }
    if ($user_id == 0) {
        return esc_html__('You must be logged in to view your form history.', 'aiomatic-automatic-ai-content-writer');
    }
    $name = md5(get_bloginfo());
    wp_enqueue_script('jquery');
    wp_register_script($name . '-forms-history-front-script', plugins_url('scripts/forms-history.js', __FILE__), false, AIOMATIC_MAJOR_VERSION );
    wp_enqueue_script($name . '-forms-history-front-script'  );
    wp_localize_script($name . '-forms-history-front-script', 'aiomatic_history_ajax_object_forms', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('openai-ajax-nonce')
    ));
    wp_enqueue_style('aiomatic-history-styles', plugins_url('styles/forms-history.css', __FILE__));
    $history = get_user_meta($user_id, 'aiomatic_form_history_' . $form_id, true);
    if (empty($history) || !is_array($history)) {
        $history = [];
    }
    
    $sort_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'desc';
    if ($sort_order === 'asc') {
        $history = array_reverse($history);
    }
    
    $per_page = 10;
    $current_page = isset($_GET['history_page']) ? max(1, intval($_GET['history_page'])) : 1;
    $total_entries = count($history);
    $total_pages = ceil($total_entries / $per_page);
    $offset = ($current_page - 1) * $per_page;
    $paged_history = array_slice($history, $offset, $per_page);
    
    ob_start();
    ?>
    <div id="aiomatic-form-history-<?php echo esc_attr($form_id); ?>" class="aiomatic-form-history aiomatic-modern-container">
        <div class="aiomatic-history-controls">
            <select class="aiomatic-sort-select" onchange="window.location.href='?sort_order='+this.value+'&history_page=<?php echo esc_attr($current_page); ?>'">
                <option value="desc" <?php selected($sort_order, 'desc'); ?>><?php esc_html_e('Newest First', 'aiomatic-automatic-ai-content-writer'); ?></option>
                <option value="asc" <?php selected($sort_order, 'asc'); ?>><?php esc_html_e('Oldest First', 'aiomatic-automatic-ai-content-writer'); ?></option>
            </select>
        </div>
        <ul class="aiomatic-history-list">
            <?php foreach ($paged_history as $index => $entry) : ?>
                <li class="aiomatic-history-item">
                    <span class="aiomatic-history-snippet" data-index="<?php echo esc_attr($index); ?>" 
                          data-full-text="<?php echo esc_attr($entry); ?>" 
                          data-form-id="<?php echo esc_attr($form_id); ?>">
                        <?php echo esc_html(wp_trim_words($entry, 10, '...')); ?>
                    </span>
                    <button class="aiomatic-delete-entry" 
                            title="<?php esc_attr_e('Delete entry', 'aiomatic-automatic-ai-content-writer'); ?>" 
                            data-index="<?php echo esc_attr($index); ?>" 
                            data-form-id="<?php echo esc_attr($form_id); ?>">
                        ×
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($total_pages > 1) : ?>
        <div class="aiomatic-pagination">
            <?php if ($current_page > 1) : ?>
                <a href="?history_page=<?php echo esc_attr($current_page - 1); ?>&sort_order=<?php echo esc_attr($sort_order); ?>" 
                   class="aiomatic-pagination-link aiomatic-prev"><?php esc_html_e('Previous', 'aiomatic-automatic-ai-content-writer'); ?></a>
            <?php endif; ?>
            <span class="aiomatic-page-info"><?php printf(esc_html__('Page %d of %d', 'aiomatic-automatic-ai-content-writer'), esc_html($current_page), esc_html($total_pages)); ?></span>
            <?php if ($current_page < $total_pages) : ?>
                <a href="?history_page=<?php echo esc_attr($current_page + 1); ?>&sort_order=<?php echo esc_attr($sort_order); ?>" 
                   class="aiomatic-pagination-link aiomatic-next"><?php esc_html_e('Next', 'aiomatic-automatic-ai-content-writer'); ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div id="aiomatic-full-text-display-<?php echo esc_attr($form_id); ?>" class="aiomatic-full-text-display">
            <p class="description"><?php esc_html_e('Select an entry to view the full text.', 'aiomatic-automatic-ai-content-writer'); ?></p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('aiomatic-form', 'aiomatic_editable_form_shortcode');
function aiomatic_editable_form_shortcode($atts)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    if(!isset($atts) || !is_array($atts))
    {
        return;
    }
    $returnme = '';
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(!isset($atts['id']) || empty($atts['id']))
    {
        $latest_form = get_posts([
            'post_type'      => 'aiomatic_forms',
            'posts_per_page' => 1,
            'orderby'        => 'ID',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ]);

        if ( ! empty( $latest_form ) ) {
            $atts['id'] = $latest_form[0];
        }
    }
    if(isset($atts['id']) && !empty($atts['id']))
    {
        $aiomatic_item_id = sanitize_text_field($atts['id']);
        $aiomatic_original_item_id = $aiomatic_item_id;
        $my_post = array();
        $my_post['post__in'] = array($aiomatic_item_id);
        $my_post['post_type'] = 'aiomatic_forms';
        $aiomatic_item = get_posts($my_post);
        if($aiomatic_item === null || !isset($aiomatic_item[0]))
        {
            $returnme = esc_html__('Form ID not found in the database!', 'aiomatic-automatic-ai-content-writer');
            return $returnme;
        }
        else
        {
            $aiomatic_item_id .= aiomatic_gen_uid();
            $submit_location = '1';
            if (isset($aiomatic_Main_Settings['submit_location']) && $aiomatic_Main_Settings['submit_location'] != '')
            {
                $submit_location = $aiomatic_Main_Settings['submit_location'];
            }
            $submit_align = 'aiomatic-prompt-flex-center';
            if (isset($aiomatic_Main_Settings['submit_align']) && $aiomatic_Main_Settings['submit_align'] != '')
            {
                if($aiomatic_Main_Settings['submit_align'] == '2')
                {
                    $submit_align = 'aiomatic-plain-center';
                }
                elseif($aiomatic_Main_Settings['submit_align'] == '3')
                {
                    $submit_align = 'aiomatic-plain-right';
                }
            }
            if (isset($aiomatic_Main_Settings['max_len']) && $aiomatic_Main_Settings['max_len'] != '')
            {
                $max_len = trim($aiomatic_Main_Settings['max_len']);
            }
            else
            {
                $max_len = '';
            }
            if (isset($aiomatic_Main_Settings['min_len']) && $aiomatic_Main_Settings['min_len'] != '')
            {
                $min_len = trim($aiomatic_Main_Settings['min_len']);
            }
            else
            {
                $min_len = '';
            }
            $user_id = get_current_user_id();
            $name = md5(get_bloginfo());
            wp_register_style($name . '-form-end-style', plugins_url('styles/form-end.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
            wp_enqueue_style($name . '-form-end-style');
            $reg_css_code = '.aiomatic-prompt-item{padding:10px;}.aiomatic-hide {display:none!important;visibility:hidden}';
            if (isset($aiomatic_Main_Settings['back_color']) && $aiomatic_Main_Settings['back_color'] != '')
            {
                $reg_css_code .= '.aiomatic-prompt-item{background-color:' . trim($aiomatic_Main_Settings['back_color']) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
            }
            if (isset($aiomatic_Main_Settings['text_color']) && $aiomatic_Main_Settings['text_color'] != '')
            {
                $reg_css_code .= '.aiomatic-prompt-item{color:' . trim($aiomatic_Main_Settings['text_color']) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
            }
            if (isset($aiomatic_Main_Settings['but_color']) && $aiomatic_Main_Settings['but_color'] != '')
            {
                $reg_css_code .= '.aiomatic-generate-button{background:' . trim($aiomatic_Main_Settings['but_color']) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}.aiomatic-get-button{background:' . trim($aiomatic_Main_Settings['but_color']) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
            }
            if (isset($aiomatic_Main_Settings['btext_color']) && $aiomatic_Main_Settings['btext_color'] != '')
            {
                $reg_css_code .= '.aiomatic-generate-button{color:' . trim($aiomatic_Main_Settings['btext_color']) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}.aiomatic-get-button{color:' . trim($aiomatic_Main_Settings['btext_color']) . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
            }
            if($reg_css_code != '')
            {
                wp_add_inline_style( $name . '-form-end-style', $reg_css_code );
            }
            $markdown_parse = 'off';
            if (isset($aiomatic_Main_Settings['markdown_forms']) && $aiomatic_Main_Settings['markdown_forms'] == 'on')
            {
                $markdown_parse = 'on';
            }
            $katex_parse = 'off';
            if (isset($aiomatic_Main_Settings['katex_forms']) && $aiomatic_Main_Settings['katex_forms'] == 'on')
            {
                $katex_parse = 'on';
            }
            if($katex_parse == 'on')
            {
                wp_register_script( $name . '-katex', trailingslashit( plugins_url('', __FILE__) ) . 'js/katex.min.js', array(), AIOMATIC_MAJOR_VERSION );
                wp_enqueue_script( $name . '-katex' );
                wp_register_script( $name . '-katex-autorender', trailingslashit( plugins_url('', __FILE__) ) . 'js/auto-render.min.js', array(), AIOMATIC_MAJOR_VERSION );
                wp_enqueue_script( $name . '-katex-autorender' );
                wp_register_style($name . '-katex-css', plugins_url('styles/katex.min.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
                wp_enqueue_style( $name . '-katex-css' );
            }
            $aiomatic_item = $aiomatic_item[0];
            $title = $aiomatic_item->post_title;
            $description = $aiomatic_item->post_content;
            $type = get_post_meta($aiomatic_item->ID, 'type', true);
            if($type != 'text' && $type != 'image' && $type != 'image-new' && $type != 'gpt-image-1' && $type != 'image-mid' && $type != 'image-rep' && $type != 'image-goo' && $type != 'image-ide' && $type != 'image2' && $type != 'omniblock-form')
            {
                $type = 'text';
            }
            $prompt = get_post_meta($aiomatic_item->ID, 'prompt', true);
            $model = get_post_meta($aiomatic_item->ID, 'model', true);
            $assistant_id = get_post_meta($aiomatic_item->ID, 'assistant_id', true);
            $header = get_post_meta($aiomatic_item->ID, 'header', true);
            $editor = get_post_meta($aiomatic_item->ID, 'editor', true);
            $advanced = get_post_meta($aiomatic_item->ID, 'advanced', true);
            $submit = get_post_meta($aiomatic_item->ID, 'submit', true);
            $max = get_post_meta($aiomatic_item->ID, 'max', true);
            $temperature = get_post_meta($aiomatic_item->ID, 'temperature', true);
            $html = get_post_meta($aiomatic_item->ID, 'html', true);
            $streaming_enabled = get_post_meta($aiomatic_item->ID, 'streaming_enabled', true);
            $wait_omniblock = get_post_meta($aiomatic_item->ID, 'wait_omniblock', true);
            $stream_url_claude = esc_html(add_query_arg(array(
                'aiomatic_claude_stream' => 'yes',
                'nonce' => wp_create_nonce('aiomatic-streaming-nonce')
            ), site_url() . '/index.php'));
            $stream_url = esc_html(add_query_arg(array(
                'aiomatic_stream' => 'yes',
                'nonce' => wp_create_nonce('aiomatic-streaming-nonce')
            ), site_url() . '/index.php'));
            $image_placeholder = plugins_url('images/loading.gif', __FILE__);
            wp_enqueue_script('jquery');
            wp_register_script($name . '-forms-front-script', plugins_url('scripts/forms-front.js', __FILE__), false, AIOMATIC_MAJOR_VERSION );
            wp_enqueue_script($name . '-forms-front-script'  );
            wp_localize_script($name . '-forms-front-script', 'aiomatic_completition_ajax_object_forms', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('openai-ajax-nonce'),
                'persistentnonce' => wp_create_nonce('openai-persistent-nonce'),
                'user_id' => $user_id,
                'formid' => $aiomatic_original_item_id,
                'max_len' => $max_len,
                'min_len' => $min_len,
                'stream_url' => $stream_url,
                'stream_url_claude' => $stream_url_claude,
                'claude_models' => AIOMATIC_CLAUDE_MODELS,
                'google_models' => AIOMATIC_GOOGLE_MODELS,
                'huggingface_models' => aiomatic_get_huggingface_models(),
                'image_placeholder' => $image_placeholder,
                'secretkey' => 'NDUPPe+cr2Cs2AYiN+JaoBH60cbleu6c',
                'markdown_parse' => $markdown_parse,
                'katex_parse' => $katex_parse
            ));
            if($temperature === '')
            {
                $temperature = 0;
            }
            else
            {
                $temperature = floatval($temperature);
            }
            $topp = get_post_meta($aiomatic_item->ID, 'topp', true);
            if($topp === '')
            {
                $topp = 0;
            }
            else
            {
                $topp = floatval($topp);
            }
            $presence = get_post_meta($aiomatic_item->ID, 'presence', true);
            if($presence === '')
            {
                $presence = 0;
            }
            else
            {
                $presence = floatval($presence);
            }
            $frequency = get_post_meta($aiomatic_item->ID, 'frequency', true);
            if($frequency === '')
            {
                $frequency = 0;
            }
            else
            {
                $frequency = floatval($frequency);
            }
            $response = get_post_meta($aiomatic_item->ID, 'response', true);
            $aiomaticfields = get_post_meta($aiomatic_item->ID, '_aiomaticfields', true);
            if(!is_array($aiomaticfields))
            {
                $aiomaticfields = array();
            }
            $aiomaticfields = array_values($aiomaticfields);
            wp_enqueue_editor();
            ob_start();
            ?>
            <div id="aiomatic-prompt-item-<?php echo esc_html($aiomatic_item->ID);?>" class="aiomatic-prompt-item aiomatic-prompt-item<?php echo esc_html($aiomatic_item_id);?>">
                <div id="aiomatic-prompt-head-<?php echo esc_html($aiomatic_item->ID);?>" class="aiomatic-prompt-head aiomatic-prompt-head<?php echo esc_html($aiomatic_item_id); echo $header === 'hide' ? ' aiomatic-hidden-form':'';?>">
                    <div>
                        <strong id="aiomatic_title<?php echo esc_html($aiomatic_item_id);?>" class="aiomatic_title aiomatic_title<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html($title);?></strong>
                        <?php
                        if(!empty($description)){
                            echo '<p class="aiomatic_desc aiomatic_desc' . esc_html($aiomatic_item_id) . '">' . esc_html($description) . '</p>';
                        }
                        ?>
                    </div>
                </div>
                <?php
if($submit_location == '2')
{
?>
<br/>
                            <div id="aiomatic-button-wrap-<?php echo esc_html($aiomatic_item->ID);?>" class="<?php echo esc_html($submit_align);?>">
                                <button class="aiomatic-button aiomatic-generate-button aiomatic-generate-button<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-generate-button<?php echo esc_html($aiomatic_item_id);?>" data-id="<?php echo esc_html($aiomatic_item_id);?>"><span class="button__text button__text<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html($submit);?></span></button>
                            </div>
<?php
}
?>
                <div id="aiomatic-prompt-content-<?php echo esc_html($aiomatic_item->ID);?>" class="aiomatic-prompt-content aiomatic-prompt-content<?php echo esc_html($aiomatic_item_id);?>">
                    <form method="post" action="" class="aiomatic-prompt-form aiomatic-prompt-form<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-prompt-form<?php echo esc_html($aiomatic_item_id);?>">
                        <div id="aiomatic-info-holder-<?php echo esc_html($aiomatic_item->ID);?>" class="aiomatic-mb-10 aiomatic-mb-10<?php echo esc_html($aiomatic_item_id);?>">
<?php
$encryptedData = aiomatic_simpleEncryptWithKey($prompt, 'NDUPPe+cr2Cs2AYiN+JaoBH60cbleu6c');
?>
                            <input type="hidden" name="aiomatic-prompt" id="aiomatic-prompt<?php echo esc_html($aiomatic_item_id);?>" value="<?php echo esc_html($encryptedData);?>">
                            <input type="hidden" name="aiomatic-type" id="aiomatic-form-type<?php echo esc_html($aiomatic_item_id);?>" value="<?php echo esc_html($type);?>">
                            <?php
                            if($aiomaticfields && is_array($aiomaticfields) && count($aiomaticfields))
                            {
                                foreach($aiomaticfields as $key => $aiomatic_field)
                                {
?>
                                    <div class="aiomatic-form-field aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>">
                                        <?php if(isset($aiomatic_field['label']) && $aiomatic_field['label'] != '')
                                        {
                                            echo '<label for="aiomatic-form-field' . esc_html($aiomatic_item_id) . '-' . esc_html($key) . '"><strong>' . esc_html($aiomatic_field['label']) . '</strong></label><br/>';
                                        }?>
                                        <?php
                                        if(isset($aiomatic_field['type']))
                                        {
                                            $value = '';
                                            $accept_string = '';
                                            if(isset($aiomatic_field['value']) && !empty($aiomatic_field['value']))
                                            {
                                                $value = $aiomatic_field['value'];
                                            }
                                            else
                                            {
                                                if(isset($_GET[$aiomatic_field['id']]) && !empty($_GET[$aiomatic_field['id']]))
                                                {
                                                    $value = $_GET[$aiomatic_field['id']];
                                                }
                                            }
                                            if($aiomatic_field['type'] == 'select')
                                            {
                                                $aiomatic_field_options = [];
                                                if(isset($aiomatic_field['options']) && is_string($aiomatic_field['options'])){
                                                    $aiomatic_field_options = preg_split('/\r\n|\r|\n/', trim($aiomatic_field['options']));
                                                }
                                                else
                                                {
                                                    if(isset($aiomatic_field['options']) && is_array($aiomatic_field['options'])){
                                                        $aiomatic_field_options = $aiomatic_field['options'];
                                                    }
                                                }
                                                ?>
                                                <select class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" <?php if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?> id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" data-min="" data-max="">
                                                    <?php
                                                    foreach($aiomatic_field_options as $aiomatic_field_option){
                                                        echo '<option value="'.esc_html($aiomatic_field_option).'"';
                                                        if($value == $aiomatic_field_option)
                                                        {
                                                            echo ' selected';
                                                        }
                                                        echo '>'.esc_html($aiomatic_field_option).'</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'checkbox' || $aiomatic_field['type'] == 'radio')
                                            {
                                                $aiomatic_field_options = [];
                                                if(isset($aiomatic_field['options']) && is_string($aiomatic_field['options'])){
                                                    $aiomatic_field_options = preg_split('/\r\n|\r|\n/', trim($aiomatic_field['options']));
                                                }
                                                else
                                                {
                                                    if(isset($aiomatic_field['options']) && is_array($aiomatic_field['options'])){
                                                        $aiomatic_field_options = $aiomatic_field['options'];
                                                    }
                                                }
                                                ?>
                                                <div id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>">
                                                    <?php
                                                    $zind = 0;
                                                    foreach($aiomatic_field_options as $aiomatic_field_option):
                                                        $zind++;
                                                        ?><label for="aiomatic-fieldop<?php echo esc_html($zind);?>"><input id="aiomatic-fieldop<?php echo esc_html($zind);?> class="aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" name="<?php echo esc_html($aiomatic_field['id']).($aiomatic_field['type'] == 'checkbox' ? '[]':'')?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" value="<?php echo esc_html($aiomatic_field_option)?>" type="<?php echo esc_html($aiomatic_field['type'])?>"<?php if($value == $aiomatic_field_option){echo ' checked';}?>>&nbsp;<?php echo esc_html($aiomatic_field_option)?></label><br/>
                                                        <?php
                                                    endforeach;
                                                    ?>
                                                </div>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'textarea')
                                            {
                                            ?>
                                                <textarea <?php if(isset($aiomatic_field['limit']) && !empty($aiomatic_field['limit'])){echo 'data-limit="' . esc_html($aiomatic_field['limit']) . '"';}else{echo 'data-limit=""';} if(isset($aiomatic_field['placeholder']) && !empty($aiomatic_field['placeholder'])){echo 'placeholder="' . esc_html($aiomatic_field['placeholder']) . '"';} echo isset($aiomatic_field['rows']) && !empty($aiomatic_field['rows']) ? ' rows="'.esc_html($aiomatic_field['rows']).'"': '';?><?php echo isset($aiomatic_field['cols']) && !empty($aiomatic_field['cols']) ? ' rows="'.esc_html($aiomatic_field['cols']).'"': ''; if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?> id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>" data-min="" data-max=""><?php if(!empty($value)){echo esc_textarea($value);}?></textarea>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'number')
                                            {
                                                ?>
                                                <input <?php if(isset($aiomatic_field['limit']) && !empty($aiomatic_field['limit'])){echo 'data-limit="' . esc_html($aiomatic_field['limit']) . '"';}else{echo 'data-limit=""';} if(isset($aiomatic_field['placeholder']) && !empty($aiomatic_field['placeholder'])){echo 'placeholder="' . esc_html($aiomatic_field['placeholder']) . '"';} if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?> id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>" data-min="<?php echo isset($aiomatic_field['min']) ? esc_html($aiomatic_field['min']) : ''?>" min="<?php echo isset($aiomatic_field['min']) ? esc_html($aiomatic_field['min']) : ''?>" data-max="<?php echo isset($aiomatic_field['max']) ? esc_html($aiomatic_field['max']) : ''?>" max="<?php echo isset($aiomatic_field['max']) ? esc_html($aiomatic_field['max']) : ''?>"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'range')
                                            {
                                                ?>
                                                <input <?php if(isset($aiomatic_field['limit']) && !empty($aiomatic_field['limit'])){echo 'data-limit="' . esc_html($aiomatic_field['limit']) . '"';}else{echo 'data-limit=""';} if(isset($aiomatic_field['placeholder']) && !empty($aiomatic_field['placeholder'])){echo 'placeholder="' . esc_html($aiomatic_field['placeholder']) . '"';} if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?> id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>" data-min="<?php echo isset($aiomatic_field['min']) ? esc_html($aiomatic_field['min']) : ''?>" min="<?php echo isset($aiomatic_field['min']) ? esc_html($aiomatic_field['min']) : ''?>" data-max="<?php echo isset($aiomatic_field['max']) ? esc_html($aiomatic_field['max']) : ''?>" max="<?php echo isset($aiomatic_field['max']) ? esc_html($aiomatic_field['max']) : ''?>"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'color')
                                            {
                                                ?>
                                                <input <?php if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?> id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'date')
                                            {
                                                ?>
                                                <input <?php if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?>  id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'time')
                                            {
                                                ?>
                                                <input <?php if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?>  id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'datetime')
                                            {
                                                ?>
                                                <input <?php if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?>  id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="datetime-local"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'month')
                                            {
                                                ?>
                                                <input <?php if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?>  id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'week')
                                            {
                                                ?>
                                                <input <?php if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?>  id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>"<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'file')
                                            {
                                                if(isset($aiomatic_field['options']) && is_string($aiomatic_field['options']))
                                                {
                                                    $accept_string = ' data-accept="' . esc_html(trim($aiomatic_field['options'])) . '" accept="' . esc_html(trim($aiomatic_field['options'])) . '"';
                                                }
                                                ?>
                                                <input <?php if(isset($aiomatic_field['limit']) && !empty($aiomatic_field['limit'])){echo 'data-limit="' . esc_html($aiomatic_field['limit']) . '"';}else{echo 'data-limit=""';} if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?> id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php echo esc_html($aiomatic_field['type'])?>" <?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';} if(!empty($accept_string)){echo $accept_string;}?>>
                                                <?php
                                            }
                                            elseif($aiomatic_field['type'] == 'captcha')
                                            {
                                               $rand1 = rand(1, 9);
$rand2 = rand(1, 9);
$captcha_answer = $rand1 + $rand2;
$captcha_question = "$rand1 + $rand2 = ?";
?>
<div class="aiomatic-captcha-field">
    <label for="aiomatic-captcha-<?php echo esc_attr($aiomatic_item_id); ?>">
        <strong><?php echo esc_html($captcha_question); ?></strong>
    </label><br/>
    <input 
        type="text"
        id="aiomatic-captcha-<?php echo esc_attr($aiomatic_item_id); ?>"
        class="aiomatic-form-input aiomatic-form-input<?php echo esc_attr($aiomatic_item_id); ?>"
        name="aiomatic_captcha"
        required
        data-captcha-answer="<?php echo esc_attr($captcha_answer); ?>"
    >
</div>
<?php
                                            }
                                            elseif($aiomatic_field['type'] == 'html')
                                            {
                                                echo '<div class="aiomatic-html-type-holder" id="aiomatic-form-field' . esc_html($aiomatic_item_id) . '-' . esc_html($key) . '">';
                                                $aiomatic_field_options = [];
                                                if(isset($aiomatic_field['options']) && is_string($aiomatic_field['options'])){
                                                    $aiomatic_field_options = preg_split('/\r\n|\r|\n/', trim($aiomatic_field['options']));
                                                }
                                                else
                                                {
                                                    if(isset($aiomatic_field['options']) && is_array($aiomatic_field['options'])){
                                                        $aiomatic_field_options = $aiomatic_field['options'];
                                                    }
                                                }
                                                foreach($aiomatic_field_options as $aiomatic_field_option){
                                                    echo $aiomatic_field_option;
                                                }
                                                echo '</div>';
                                            }
                                            else
                                            {
                                                ?>
                                                <input <?php if(isset($aiomatic_field['limit']) && !empty($aiomatic_field['limit'])){echo 'data-limit="' . esc_html($aiomatic_field['limit']) . '"';}else{echo 'data-limit=""';} if(isset($aiomatic_field['placeholder']) && !empty($aiomatic_field['placeholder'])){echo 'placeholder="' . esc_html($aiomatic_field['placeholder']) . '"';} 
                                                if($aiomatic_field['required'] == 'yes'){echo ' required data-required="yes" ';}?> id="aiomatic-form-field<?php echo esc_html($aiomatic_item_id);?>-<?php echo esc_html($key)?>" name="<?php echo esc_html($aiomatic_field['id'])?>" class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" aiomatic-name="<?php echo esc_html($aiomatic_field['id'])?>" data-label="<?php echo esc_html($aiomatic_field['label'])?>" data-type="<?php echo esc_html($aiomatic_field['type'])?>" type="<?php 
                                                if($aiomatic_field['type'] == 'scrape'){echo 'url';}else{echo esc_html($aiomatic_field['type']);}?>" data-min="" data-max=""<?php if(!empty($value)){echo ' value="' . esc_attr($value) . '"';}?>>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php
                                }
                            }
                            ?>
                            <div id="openai-response<?php echo esc_html($aiomatic_item_id);?>"></div>
<?php
if($submit_location == '1')
{
?>
                            <div id="aiomatic-button-wrap-<?php echo esc_html($aiomatic_item->ID);?>" class="<?php echo esc_html($submit_align);?>">
                                <button class="aiomatic-button aiomatic-generate-button aiomatic-generate-button<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-generate-button<?php echo esc_html($aiomatic_item_id);?>" data-id="<?php echo esc_html($aiomatic_item_id);?>"><span class="button__text button__text<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html($submit);?></span></button>
                            </div>
<?php
}
?>
                        </div>
                        <div id="aiomatic-response-holder-<?php echo esc_html($aiomatic_item->ID);?>" class="mb-5">
<?php
if($type === 'text' || $type === 'omniblock-form')
{
    if($html == '1')
    {
        echo '<div class="aiomatic_html_output aiomatic-prompt-result aiomatic-prompt-result' . esc_html($aiomatic_item_id) . '" id="aiomatic_html_output' . esc_html($aiomatic_item_id) . '" contenteditable="false" role="textbox" aria-label="AI Output"></div>';
        if (isset($aiomatic_Main_Settings['enable_copy']) && $aiomatic_Main_Settings['enable_copy'] == 'on') {
            echo '<button type="button" data-id="' . esc_html($aiomatic_item_id) . '" class="aiomatic-get-button aiomatic_copy_btn_html" title="' . esc_html__("Copy to clipboard", 'aiomatic-automatic-ai-content-writer') . '" id="aiomatic_copy_btn' . esc_html($aiomatic_item_id) . '">' . esc_html__("Copy", 'aiomatic-automatic-ai-content-writer') . '</button>&nbsp;';
        }
        if (isset($aiomatic_Main_Settings['enable_pdf']) && $aiomatic_Main_Settings['enable_pdf'] == 'on') {
            echo '<button type="button" data-id="' . esc_html($aiomatic_item_id) . '" class="aiomatic-get-button aiomatic_pdf_btn_html" title="' . esc_html__("Download as PDF", 'aiomatic-automatic-ai-content-writer') . '" id="aiomatic_pdf_btn' . esc_html($aiomatic_item_id) . '">' . esc_html__("Download as PDF", 'aiomatic-automatic-ai-content-writer') . '</button>&nbsp;';
            wp_enqueue_script($name . 'pdf-download-script', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array());
            wp_enqueue_script($name . 'pdf-download-helper', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array());
        }
    }
    else
    {
        if ((isset($aiomatic_Main_Settings['show_rich_editor']) && $aiomatic_Main_Settings['show_rich_editor'] == 'on') || $editor == 'wpeditor')
        { 
            $settings = array(
                'textarea_name' => 'aiomatic-prompt-result' . esc_html($aiomatic_item_id),
                'media_buttons' => true,
                'quicktags' => true,
                'tabindex' => '4',
                'editor_class' => 'aiomatic-editor-' . esc_html($aiomatic_item_id)
            );
            echo '<div class="aiomatic_editor_wrapper" id="aiomatic_editor_wrapper' . esc_html($aiomatic_item_id) . '">';
            wp_editor( '', 'aiomatic-prompt-result' . esc_html($aiomatic_item_id), $settings );
            if (isset($aiomatic_Main_Settings['enable_copy']) && $aiomatic_Main_Settings['enable_copy'] == 'on')
            {
                echo '<button type="button" data-id="' . esc_html($aiomatic_item_id) . '" title="' . esc_html__("Copy to clipboard", 'aiomatic-automatic-ai-content-writer') . '" class="aiomatic-get-button aiomatic_copy_btn aiomatic_copy_btn_gut" id="aiomatic_copy_btn' . esc_html($aiomatic_item_id) . '">' . esc_html__("Copy", 'aiomatic-automatic-ai-content-writer') . '</button>&nbsp;';
            }
            if (isset($aiomatic_Main_Settings['enable_pdf']) && $aiomatic_Main_Settings['enable_pdf'] == 'on') {
                echo '<button type="button" data-id="' . esc_html($aiomatic_item_id) . '" class="aiomatic-get-button aiomatic_pdf_btn aiomatic_pdf_btn_gut" title="' . esc_html__("Download as PDF", 'aiomatic-automatic-ai-content-writer') . '" id="aiomatic_pdf_btn' . esc_html($aiomatic_item_id) . '">' . esc_html__("Download as PDF", 'aiomatic-automatic-ai-content-writer') . '</button>&nbsp;';
                wp_enqueue_script($name . 'pdf-download-script', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array());
                wp_enqueue_script($name . 'pdf-download-helper', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array());
            }
            echo '</div>';
            if (isset($aiomatic_Main_Settings['enable_char_count']) && $aiomatic_Main_Settings['enable_char_count'] == 'on')
            {
                echo '<div id="charCount_textarea' . esc_html($aiomatic_item_id) . '">' . esc_html__("Characters", 'aiomatic-automatic-ai-content-writer') . ': <span id="charCount_span' . esc_html($aiomatic_item_id) . '">0</span></div>';
            }
        }
        else
        {
            if (isset($aiomatic_Main_Settings['form_placeholder']) && $aiomatic_Main_Settings['form_placeholder'] != '')
            { 
                $placeholder_form = $aiomatic_Main_Settings['form_placeholder'];
            }
            else
            {
                $placeholder_form = 'AI Result';
            }
            echo '<div class="aiomatic_textarea_wrapper" id="aiomatic_textarea_wrapper' . esc_html($aiomatic_item_id) . '">';
    ?>                   
            <textarea readonly aria-label="AI Output" name="aiomatic-prompt-result<?php echo esc_html($aiomatic_item_id);?>" onFocus="aiomaticCountChars('aiomatic-prompt-result<?php echo esc_html($aiomatic_item_id);?>','charCount_span<?php echo esc_html($aiomatic_item_id);?>')" onKeyDown="aiomaticCountChars('aiomatic-prompt-result<?php echo esc_html($aiomatic_item_id);?>','charCount_span<?php echo esc_html($aiomatic_item_id);?>')" onKeyUp="aiomaticCountChars('aiomatic-prompt-result<?php echo esc_html($aiomatic_item_id);?>','charCount_span<?php echo esc_html($aiomatic_item_id);?>')" onchange="aiomaticCountChars('aiomatic-prompt-result<?php echo esc_html($aiomatic_item_id);?>','charCount_span<?php echo esc_html($aiomatic_item_id);?>')" class="aiomatic-prompt-result aiomatic-prompt-result<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-prompt-result<?php echo esc_html($aiomatic_item_id);?>" rows="12" placeholder="<?php echo esc_html($placeholder_form);?>"></textarea>
    <?php
            if (isset($aiomatic_Main_Settings['enable_copy']) && $aiomatic_Main_Settings['enable_copy'] == 'on')
            {
                echo '<button type="button" data-id="' . esc_html($aiomatic_item_id) . '" title="' . esc_html__("Copy to clipboard", 'aiomatic-automatic-ai-content-writer') . '" class="aiomatic-get-button aiomatic_copy_btn aiomatic_copy_btn_textarea" id="aiomatic_copy_btn' . esc_html($aiomatic_item_id) . '">' . esc_html__("Copy", 'aiomatic-automatic-ai-content-writer') . '</button>&nbsp;';
            }
            if (isset($aiomatic_Main_Settings['enable_pdf']) && $aiomatic_Main_Settings['enable_pdf'] == 'on') {
                echo '<button type="button" data-id="' . esc_html($aiomatic_item_id) . '" class="aiomatic-get-button aiomatic_pdf_btn aiomatic_pdf_btn_textarea" title="' . esc_html__("Download as PDF", 'aiomatic-automatic-ai-content-writer') . '" id="aiomatic_pdf_btn' . esc_html($aiomatic_item_id) . '">' . esc_html__("Download as PDF", 'aiomatic-automatic-ai-content-writer') . '</button>&nbsp;';
                wp_enqueue_script($name . 'pdf-download-script', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array());
                wp_enqueue_script($name . 'pdf-download-helper', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array());
            }
            echo '</div>';
            if (isset($aiomatic_Main_Settings['enable_char_count']) && $aiomatic_Main_Settings['enable_char_count'] == 'on')
            {
                echo '<div id="charCount_textarea' . esc_html($aiomatic_item_id) . '" class="charcount_textarea">' . esc_html__("Characters", 'aiomatic-automatic-ai-content-writer') . ': <span id="charCount_span' . esc_html($aiomatic_item_id) . '">0</span></div>';
            }
        }
    }
}
else
{
?>
<div class="aiomatic-image-results" id="aiomatic-image-results<?php echo esc_html($aiomatic_item_id);?>">
<div class="aiomatic_image_wrapper" id="aiomatic_wrapper<?php echo esc_html($aiomatic_item_id); ?>">
    <img id="aiomatic_form_response<?php echo esc_html($aiomatic_item_id); ?>" src="">
<?php
if (isset($aiomatic_Main_Settings['enable_download']) && $aiomatic_Main_Settings['enable_download'] == 'on')
{
?>
    <a href="#" id="download_button<?php echo esc_html($aiomatic_item_id); ?>" class="aiomatic-get-button aiomatic_download_btn aiomatic_download_btn<?php echo esc_html($aiomatic_item_id); ?>" download><?php echo esc_html__("Download", 'aiomatic-automatic-ai-content-writer');?></a>
<?php
}
?>
</div>
</div>
<?php
}
?>
<input type="hidden" id="aiomatic-streaming<?php echo esc_html($aiomatic_item_id);?>" value="<?php echo esc_attr($streaming_enabled);?>">
<input type="hidden" id="aiomatic-wait<?php echo esc_html($aiomatic_item_id);?>" value="<?php echo esc_attr($wait_omniblock);?>">
<input type="hidden" id="aiomatic-formid<?php echo esc_html($aiomatic_item_id);?>" value="<?php echo esc_attr($aiomatic_item->ID);?>">
                        </div>
                        <?php
if($submit_location == '3')
{
?>
<br/>
                            <div id="aiomatic-button-wrap-<?php echo esc_html($aiomatic_item->ID);?>" class="<?php echo esc_html($submit_align);?>">
                                <button class="aiomatic-button aiomatic-generate-button" id="aiomatic-generate-button<?php echo esc_html($aiomatic_item_id);?>" data-id="<?php echo esc_html($aiomatic_item_id);?>"><span class="button__text button__text<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html($submit);?></span></button>
                            </div>
<?php
}
?>
                        <div id="aiomatic-advanced-holder-<?php echo esc_html($aiomatic_item->ID);?>" class="aiomatic-mb-10 aiomatic-prompt-item aiomatic-prompt-item<?php echo esc_html($aiomatic_item_id); echo (((isset($aiomatic_Main_Settings['show_advanced']) && $aiomatic_Main_Settings['show_advanced'] == 'on') || $advanced == 'show') && $type == 'text') ? '' : ' aiomatic-hidden-form';?>">
                            <h4><?php echo esc_html__('AI Settings', 'aiomatic-automatic-ai-content-writer');?></h4>
                            <div class="aiomatic-prompt-field aiomatic-prompt-engine aiomatic-prompt-engine<?php echo esc_html($aiomatic_item_id);?>">
                                <label for="aiomatic-assistant-id<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('AI Asssitant ID', 'aiomatic-automatic-ai-content-writer');?>: </label>
                                <select class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-assistant-id<?php echo esc_html($aiomatic_item_id);?>" name="assistant-id">
                                    <?php
                                    $all_assistants = aiomatic_get_all_assistants(true);
                                    if($all_assistants === false)
                                    {
                                        echo '<option val="" selected disabled>' . esc_html__("Only OpenAI API is supported for Assistants API", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                    }
                                    else
                                    {
                                        if(count($all_assistants) == 0)
                                        {
                                            echo '<option val="" selected disabled>' . esc_html__("No Assistans added, go to the plugin's 'AI Assistans' menu to add new assistants!", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                        }
                                        else
                                        {
                                            echo '<option value=""';
                                            if($assistant_id == '')
                                            {
                                                echo ' selected';
                                            }
                                            echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                            foreach($all_assistants as $myassistant)
                                            {
                                                echo '<option value="' . esc_html($myassistant->ID) .'"';
                                                if($assistant_id == $myassistant->ID)
                                                {
                                                    echo ' selected';
                                                }
                                                echo '>' . esc_html($myassistant->post_title);
                                                echo '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="aiomatic-prompt-field aiomatic-prompt-engine aiomatic-prompt-engine<?php echo esc_html($aiomatic_item_id);?>">
                                <label for="aiomatic-engine<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('AI Model', 'aiomatic-automatic-ai-content-writer');?>: </label>
                                <select class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-engine<?php echo esc_html($aiomatic_item_id);?>" name="engine">
                                    <?php
                                    $all_models = aiomatic_get_all_models(true);
                                    foreach($all_models as $aiomatic_model){
                                        echo '<option'.($aiomatic_model == $model ? ' selected':'').' value="' . esc_html($aiomatic_model) . '">' . esc_html($aiomatic_model) . esc_html(aiomatic_get_model_provider($aiomatic_model)) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="aiomatic-prompt-field aiomatic-prompt-field<?php echo esc_html($aiomatic_item_id);?>"><label for="aiomatic-max<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('Max Token Count', 'aiomatic-automatic-ai-content-writer');?>: </label><input class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-max<?php echo esc_html($aiomatic_item_id);?>" name="max_tokens" type="number" min="0" step="1" placeholder="4000" value="<?php echo esc_html($max);?>"></div>
                            <div class="aiomatic-prompt-field aiomatic-prompt-field<?php echo esc_html($aiomatic_item_id);?>"><label for="aiomatic-temperature<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('Temperature', 'aiomatic-automatic-ai-content-writer');?>: </label><input class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-temperature<?php echo esc_html($aiomatic_item_id);?>" name="temperature" type="number" min="0" max="2" step="0.01" placeholder="0" value="<?php echo esc_html($temperature)?>"></div>
                            <div class="aiomatic-prompt-field aiomatic-prompt-field<?php echo esc_html($aiomatic_item_id);?>"><label for="aiomatic-top_p<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('Top_p', 'aiomatic-automatic-ai-content-writer');?>: </label><input class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-top_p<?php echo esc_html($aiomatic_item_id);?>" type="number" min="0" max="1" step="0.01" name="top_p" placeholder="0" value="<?php echo esc_html($topp)?>"></div>
                            <div class="aiomatic-prompt-field aiomatic-prompt-field<?php echo esc_html($aiomatic_item_id);?>"><label for="aiomatic-frequency_penalty<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('Frequency Penalty', 'aiomatic-automatic-ai-content-writer');?>: </label><input class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-frequency_penalty<?php echo esc_html($aiomatic_item_id);?>" placeholder="0" name="frequency_penalty" type="number" min="0" max="2" step="0.01" value="<?php echo esc_html($frequency)?>"></div>
                            <div class="aiomatic-prompt-field aiomatic-prompt-field<?php echo esc_html($aiomatic_item_id);?>"><label for="aiomatic-presence_penalty<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('Presence Penalty', 'aiomatic-automatic-ai-content-writer');?>: </label><input class="aiomatic-form-input aiomatic-form-input<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-presence_penalty<?php echo esc_html($aiomatic_item_id);?>" placeholder="0" name="presence_penalty" type="number" min="-2" max="2" step="0.01" value="<?php echo esc_html($presence)?>"></div>
                        </div>
                        <?php
if($submit_location == '4')
{
?>
<br/>
                            <div id="aiomatic-button-wrap-<?php echo esc_html($aiomatic_item->ID);?>" class="<?php echo esc_html($submit_align);?>">
                                <button class="aiomatic-button aiomatic-generate-button aiomatic-generate-button<?php echo esc_html($aiomatic_item_id);?>" id="aiomatic-generate-button<?php echo esc_html($aiomatic_item_id);?>" data-id="<?php echo esc_html($aiomatic_item_id);?>"><span class="button__text button__text<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html($submit);?></span></button>
                            </div>
<?php
}
?>
                        <?php
if(!empty($response))
{
?>
                            <div class="aiomatic-prompt-field aiomatic-prompt-sample aiomatic-prompt-sample<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html__('Sample Response', 'aiomatic-automatic-ai-content-writer');?><div class="aiomatic-prompt-response aiomatic-prompt-response<?php echo esc_html($aiomatic_item_id);?>"><?php echo esc_html($response);?></div></div>
<?php
}
?>
                    </form>
                </div>
            </div>
            <?php
            $returnme = ob_get_clean();
        }
    }
    else
    {
        $returnme = esc_html__('You need to specify the id parameter for this shortcode to work!', 'aiomatic-automatic-ai-content-writer');
    }
    return $returnme;
}
add_shortcode('aiomatic_chat_charts', function ($atts) {
    $atts = shortcode_atts([
        'type'   => 'bar',
        'title'  => 'My Chart',
        'labels' => 'Label1,Label2,Label3',
        'data'   => '10,20,30',
        'colors' => 'FF6384,36A2EB,FFCE56',
    ], $atts);
    $secret_key = '!FF6384,36A2EB,FFCE56xxxcreateyourownkeyxxx';
    $token = hash_hmac('sha256', json_encode($atts), $secret_key);
    $atts['nonce'] = $token;
    $queryParams = http_build_query($atts);
    $imageUrl = esc_url(get_site_url() . '/wp-json/aiomatic/v1/chart-image?' . $queryParams);

    return '<img src="' . $imageUrl . '" alt="AI Generated Chart" style="width:100%; height:auto;">';
});
add_shortcode( 'aiomatic_charts', 'aiomatic_chart_shortcode' );
function aiomatic_chart_shortcode( $atts ) 
{
    if ( isset($_GET['page']) ) 
    {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    extract( shortcode_atts(
        array(
            'type'             => 'Line',
            'title'            => 'aiUsageChart' . uniqid(),
            'canvaswidth'      => '625',
            'canvasheight'     => '625',
            'width'			   => '100%',
            'height'		   => 'auto',
            'margin'		   => '5px',
            'relativewidth'	   => '1',
            'align'            => '',
            'classn'           => '',
            'labels'           => '',
            'datalabels'       => '',
            'data'             => '',
            'datasets'         => '',
            'colors'           => '#69D2E7,#a0a48C,#F38630,#96CE7F,#CEBC17,#CE4264',
            'fillopacity'      => '0.7',
            'animation'		   => 'true',
            'scalefontsize'    => '12',
            'scalefontcolor'   => '#666',
            'scaleoverride'    => 'false',
            'scalesteps' 	   => 'null',
            'scalestepwidth'   => 'null',
            'scalestartvalue'  => 'null',
            'representing'     => ''
        ), $atts )
    );
    if(empty($datasets) && !empty($data))
    {
        $datasets = $data;
    }
    elseif(empty($data) && !empty($datasets))
    {
        $data = $datasets;
    }
    if(empty($datalabels) && !empty($representing))
    {
        $datalabels = $representing;
    }
    $title    = str_replace(' ', '', $title);
    if ( ! $title || ( empty( $data ) && empty( $datasets ) ) ) 
    {
        return '';
    }
    $name = md5(get_bloginfo());
    wp_register_script( $name . '-charts-js', trailingslashit( plugins_url('', __FILE__) ) . 'js/Chart.min.js', false, AIOMATIC_MAJOR_VERSION );
    wp_register_script( $name . '-charts-functions', trailingslashit( plugins_url('', __FILE__) ) . 'js/functions.js', array( 'jquery' ), AIOMATIC_MAJOR_VERSION, true );
    wp_enqueue_script( $name . '-charts-js' );
    wp_enqueue_script( $name . '-charts-functions' );
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $reg_css_code = '.aiomatic-hide {display:none!important;visibility:hidden}.cr_back_white{background-color:#fff}.aiomatic-table {
  overflow: visible' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
  text-align:center;
  font-size:14px;
}
.aiomatic-table td, .aiomatic-table th {
  border: 1px solid #ddd;
  padding: 8px;
}
.aiomatic-table tr:nth-child(even){background-color: #f2f2f2;}
.aiomatic-table tr:hover {background-color: #ddd;}
.aiomatic-table th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
  text-align:center;
}
table th.aiomatic-absorbing-column {
    min-width: 150px;
}.aiomatic-table{text-align:center;overflow-x:auto;overflow-y: auto;}.aiomatic-table table{table-layout: fixed;border-collapse: collapse;width: 100%;}.aiomatic-table td{overflow-x: auto;}
    @media 
only screen and (max-width: 760px)  {
    .aiomatic-table table, .aiomatic-table thead, .aiomatic-table tbody, .aiomatic-table th, .aiomatic-table td, .aiomatic-table tr { 
		display: block; 
	}
	.aiomatic-table thead tr { 
		position: absolute;
		top: -9999px;
		left: -9999px;
	}
	.aiomatic-table tr { border: 1px solid #ccc; }
	.aiomatic-table td { 
		border: none;
		border-bottom: 1px solid #eee; 
		position: relative;
		padding-left: 50%; 
	}
	.aiomatic-table td:before { 
		position: absolute;
		top: 6px;
		left: 6px;
		width: 45%; 
		padding-right: 10px; 
		white-space: nowrap;
	}
}
.aiomatic_charts_canvas {width:100%' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . 'max-width:100%;}@media screen and (max-width:480px) {div.aiomatic-chart-wrap {float: none' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . 'margin-left: auto' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . 'margin-right: auto' . 
    ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . 'text-align: center;}}';
    wp_register_style( $name . '-plugin-reg-style', false );
    wp_enqueue_style( $name . '-plugin-reg-style' );
    wp_add_inline_style( $name . '-plugin-reg-style', $reg_css_code );
    $data     = explode(',', str_replace(' ', '', $data));
    $datalabels     = array_map('trim', explode(',', $datalabels));
    $datasets = explode("next", str_replace(' ', '', $datasets));
    if ($colors != "") {
        $colors   = explode(',', str_replace(' ','',$colors));
    } else {
        $colors = array('#69D2E7','#E0E4CC','#F38630','#96CE7F','#CEBC17','#CE4264');
    }
    (strpos($type, 'lar') !== false ) ? $type = 'PolarArea' : $type = ucwords($type);
    $classes = 'aiomatic-chart-wrap';
    if(!empty($align))
    {
        $classes .= ' ' . $align;
    }
    if(!empty($classn))
    {
        $classes .= ' ' . $classn;
    }
    $script_var   = 'var '.$title.'Ops = {';
    if($animation == 'true')
    {
        $script_var .= 'animation: {
                    duration: 2000
        },';
    }
    if ($type == 'Line' || $type == 'Radar' || $type == 'Bar' || $type == 'PolarArea') {
        $script_var .=	'scaleFontSize: '.$scalefontsize.',';
        $script_var .=	'scaleFontColor: "'.$scalefontcolor.'",';
        $script_var .=    'scaleOverride:'   .$scaleoverride.',';
        $script_var .=    'scaleSteps:' 	   .$scalesteps.',';
        $script_var .=    'scaleStepWidth:'  .$scalestepwidth.',';
        $script_var .=    'scaleStartValue:' .$scalestartvalue;
    }

    $script_var .= '}; ';

    if ($type == 'Line' || $type == 'Radar' || $type == 'Bar' || $type === 'Pie' || $type === 'Doughnut' || $type === 'Bubble') {
        if($type === 'Doughnut')
        {
            $xcolors = $colors;
        }
        aiomatic_compare_fill($datasets, $colors);
        if($type === 'Doughnut')
        {
            $colors = $xcolors;
        }
        $total    = count($datasets);

        $script_var .= 'var '.$title.'Data = {';
        $script_var .= 'labels : [';
        $labelstrings = explode(',',$labels);
        for ($j = 0; $j < count($labelstrings); $j++ ) {
            $script_var .= '"'.$labelstrings[$j].'"';
            aiomatic_trailing_comma($j, count($labelstrings), $script_var);
        }
        $script_var .= 	'],';
        $script_var .= 'datasets : [';
    } else {
        aiomatic_compare_fill($data, $colors);
        $total = count($data);
        $script_var .= 'var '.$title.'Data = [';
    }
    for ($i = 0; $i < $total; $i++) 
    {
        if ($type === 'Pie' || $type === 'Doughnut' || $type === 'PolarArea' || $type === 'Bubble') 
        {
            if(isset($datasets[$i]))
            {
                $script_var .= '{
                        data 	: ['. $datasets[$i] .'],';
                $script_var .= 'backgroundColor : [';
                foreach($colors as $cc)
                {
                    $script_var .= '"rgba('. aiomatic_hex2rgb( $cc ) .','.$fillopacity.')",';
                }
                $script_var .= '],';   
                if(isset($colors[$i]))
                {     
                    $script_var .= 'borderColor : "rgba('. aiomatic_hex2rgb( $colors[$i] ) .','.$fillopacity.')"';
                }
                $script_var .= '}';
            }

        } 
        else if ($type === 'Bar') 
        {
            if(isset($datasets[$i]))
            {
                $script_var .= '{';
                if(isset($colors[$i]))
                {
                    $script_var .= 'backgroundColor : "rgba('. aiomatic_hex2rgb( $colors[$i] ) .','.$fillopacity.')",
                    borderColor : "rgba('. aiomatic_hex2rgb( $colors[$i] ) .',1)",';
                }
                $script_var .= 'data : ['.$datasets[$i].'],';
                if(isset($datalabels[$i]))
                {
                    $script_var .= 'label : "' . $datalabels[$i] . '"';
                }
                $script_var .= '}';
            }

        } 
        else if ($type === 'Line' || $type === 'Radar') 
        {
            if(isset($datasets[$i]))
            {
                $script_var .= '{';
                if(isset($colors[$i]))
                {
                    $script_var .= 'borderColor : "rgba('. aiomatic_hex2rgb( $colors[$i] ) .','.$fillopacity.')",
                    backgroundColor : "rgba('. aiomatic_hex2rgb( $colors[$i] ) .','.$fillopacity.')",
                    pointBackgroundColor : "rgba('. aiomatic_hex2rgb( $colors[$i] ) .',1)",';
                }
                $script_var .= 'data : [' . $datasets[$i] . '],';
                if(isset($datalabels[$i]))
                {
                    $script_var .= 'label : "' . $datalabels[$i] . '",';
                }
                $script_var .= 'order : ' . ($total - $i) . '
                }';
            }
        }
        aiomatic_trailing_comma($i, $total, $script_var);
    }
    
    if ($type == 'Line' || $type == 'Radar' || $type == 'Bar' || $type === 'Pie' || $type === 'Doughnut' || $type === 'Bubble') {
        $script_var .=	']};';
    } else {
        $script_var .=	'];';
    }
    $script_var .= '
         window.aiomatic_charts = window.aiomatic_charts || {};
	     window.aiomatic_charts["'.$title.'"] = { options: '.$title.'Ops, data: '.$title.'Data, type: "'.$type.'" };';
    wp_register_script( $name . '-dummy-handle-header', plugins_url('scripts/header.js', __FILE__), false, AIOMATIC_MAJOR_VERSION );
    wp_enqueue_script( $name . '-dummy-handle-header'  );
    wp_add_inline_script( $name . '-dummy-handle-header', $script_var );
    $reg_css_code_style = '.aiomatic-chart-wrap{max-width: 100%; width:'.$width.'; height:'.$height.';margin:'.$margin.';}';
    wp_register_style( $name . '-plugin-reg-style-local', false );
    wp_enqueue_style( $name . '-plugin-reg-style-local' );
    wp_add_inline_style( $name . '-plugin-reg-style-local', $reg_css_code_style );
    $currentchart = '<div class="' . $classes . '" data-proportion="' . $relativewidth . '">';
    $currentchart .= '<canvas id="'.$title.'" height="'.$canvasheight.'" width="'.$canvaswidth.'" class="aiomatic_charts_canvas" data-proportion="'.$relativewidth.'"></canvas></div>';
    return $currentchart;
}

function aiomatic_related_posts_shortcode($atts) {
    ob_start();

    $atts = shortcode_atts([
        'limit' => 5,
        'threshold' => 0.7
    ], $atts);

    if (!is_singular('post')) {
        return '';
    }

    $post_id = get_the_ID();
    $cache_key = 'aiomatic_related_' . $post_id . '_' . md5($atts['limit'] . '_' . $atts['threshold']);
    $related = get_transient($cache_key);

    if ($related === false) {
        $related = aiomatic_fetch_related_posts($post_id, intval($atts['limit']), floatval($atts['threshold']));
        if($related == [])
        {
            set_transient($cache_key, $related, intval(HOUR_IN_SECONDS/10)); 
        }
        else
        {
            set_transient($cache_key, $related, intval(HOUR_IN_SECONDS)); 
        }
    }

    if (empty($related)) {
        return '<div class="aiomatic-related-posts-empty">' . esc_html__('No related posts found.', 'aiomatic-automatic-ai-content-writer') . '</div>';
    }

    $count = count($related);
    $columns = min($count, 4);

    echo '<style>
    .aiomatic-related-posts-grid {
        display: grid;
        grid-template-columns: repeat(' . $columns . ', minmax(0, 1fr));
        gap: 24px;
        margin-top: 32px;
        align-items: stretch;
    }

    .aiomatic-related-post {
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none;
        height: 100%;
        min-height: 100%;
    }

    .aiomatic-related-post:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    .aiomatic-related-post img {
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        display: block;
        background: #f9f9f9;
    }

    .aiomatic-related-content {
        padding: 16px 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .aiomatic-related-post h4 {
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
        margin: 0 0 10px;
        color: #111;
    }

    .aiomatic-related-score {
        font-size: 13px;
        color: #666;
        margin-top: auto;
    }

    @media (max-width: 1024px) {
        .aiomatic-related-posts-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .aiomatic-related-posts-grid {
            grid-template-columns: repeat(1, 1fr);
        }
    }
    </style>';

    echo '<div class="aiomatic-related-posts-grid">';
    foreach ($related as $item) {
        $post = $item['post'];
        $thumbnail = get_the_post_thumbnail($post->ID, 'medium') ?: '<img src="' . esc_url(plugins_url('images/no-thumbnail.jpg', __FILE__)) . '" alt="No thumbnail">';

        echo '<a href="' . esc_url(get_permalink($post)) . '" class="aiomatic-related-post">';
        echo $thumbnail;
        echo '<div class="aiomatic-related-content">';
        echo '<h4>' . esc_html(get_the_title($post)) . '</h4>';
        echo '</div>';
        echo '</a>';
    }
    echo '</div>';

    return ob_get_clean();
}

add_shortcode('aiomatic-related-posts', 'aiomatic_related_posts_shortcode');

add_shortcode( 'aiomatic-display-posts', 'aiomatic_display_posts_shortcode' );
function aiomatic_display_posts_shortcode( $atts ) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
	$original_atts = $atts;
	$atts = shortcode_atts( array(
		'author'               => '',
		'category'             => '',
		'category_display'     => '',
		'category_label'       => 'Posted in: ',
		'content_class'        => 'content',
		'date_format'          => '(n/j/Y)',
		'date'                 => '',
		'date_column'          => 'post_date',
		'date_compare'         => '=',
		'date_query_before'    => '',
		'date_query_after'     => '',
		'date_query_column'    => '',
		'date_query_compare'   => '',
		'display_posts_off'    => false,
		'excerpt_length'       => false,
		'excerpt_more'         => false,
		'excerpt_more_link'    => false,
		'exclude_current'      => false,
		'id'                   => false,
		'ignore_sticky_posts'  => false,
		'image_size'           => false,
		'include_author'       => false,
		'include_content'      => false,
		'include_date'         => false,
		'include_excerpt'      => false,
		'include_link'         => true,
		'include_title'        => true,
		'meta_key'             => '',
		'meta_value'           => '',
		'no_posts_message'     => '',
		'offset'               => 0,
		'order'                => 'DESC',
		'orderby'              => 'date',
		'post_parent'          => false,
		'post_status'          => 'publish',
		'post_type'            => 'post',
		'posts_per_page'       => '10',
		'tag'                  => '',
		'tax_operator'         => 'IN',
		'tax_include_children' => true,
		'tax_term'             => false,
		'taxonomy'             => false,
		'time'                 => '',
		'title'                => '',
        'title_color'          => '#000000',
        'excerpt_color'        => '#000000',
        'link_to_source'       => '',
        'title_font_size'      => '100%',
        'excerpt_font_size'    => '100%',
        'read_more_text'       => '',
		'wrapper'              => 'ul',
		'wrapper_class'        => 'display-posts-listing',
		'wrapper_id'           => false,
        'ruleid'               => ''
	), $atts, 'display-posts' );
	if( $atts['display_posts_off'] )
		return;
	$author               = sanitize_text_field( $atts['author'] );
    $ruleid               = sanitize_text_field( $atts['ruleid'] );
	$category             = sanitize_text_field( $atts['category'] );
	$category_display     = 'true' == $atts['category_display'] ? 'category' : sanitize_text_field( $atts['category_display'] );
	$category_label       = sanitize_text_field( $atts['category_label'] );
	$content_class        = array_map( 'sanitize_html_class', ( explode( ' ', $atts['content_class'] ) ) );
	$date_format          = sanitize_text_field( $atts['date_format'] );
	$date                 = sanitize_text_field( $atts['date'] );
	$date_column          = sanitize_text_field( $atts['date_column'] );
	$date_compare         = sanitize_text_field( $atts['date_compare'] );
	$date_query_before    = sanitize_text_field( $atts['date_query_before'] );
	$date_query_after     = sanitize_text_field( $atts['date_query_after'] );
	$date_query_column    = sanitize_text_field( $atts['date_query_column'] );
	$date_query_compare   = sanitize_text_field( $atts['date_query_compare'] );
	$excerpt_length       = intval( $atts['excerpt_length'] );
	$excerpt_more         = sanitize_text_field( $atts['excerpt_more'] );
	$excerpt_more_link    = filter_var( $atts['excerpt_more_link'], FILTER_VALIDATE_BOOLEAN );
	$exclude_current      = filter_var( $atts['exclude_current'], FILTER_VALIDATE_BOOLEAN );
	$id                   = $atts['id'];
	$ignore_sticky_posts  = filter_var( $atts['ignore_sticky_posts'], FILTER_VALIDATE_BOOLEAN );
	$image_size           = sanitize_key( $atts['image_size'] );
	$include_title        = filter_var( $atts['include_title'], FILTER_VALIDATE_BOOLEAN );
	$include_author       = filter_var( $atts['include_author'], FILTER_VALIDATE_BOOLEAN );
	$include_content      = filter_var( $atts['include_content'], FILTER_VALIDATE_BOOLEAN );
	$include_date         = filter_var( $atts['include_date'], FILTER_VALIDATE_BOOLEAN );
	$include_excerpt      = filter_var( $atts['include_excerpt'], FILTER_VALIDATE_BOOLEAN );
	$include_link         = filter_var( $atts['include_link'], FILTER_VALIDATE_BOOLEAN );
	$meta_key             = sanitize_text_field( $atts['meta_key'] );
	$meta_value           = sanitize_text_field( $atts['meta_value'] );
	$no_posts_message     = sanitize_text_field( $atts['no_posts_message'] );
	$offset               = intval( $atts['offset'] );
	$order                = sanitize_key( $atts['order'] );
	$orderby              = sanitize_key( $atts['orderby'] );
	$post_parent          = $atts['post_parent'];
	$post_status          = $atts['post_status'];
	$post_type            = sanitize_text_field( $atts['post_type'] );
	$posts_per_page       = intval( $atts['posts_per_page'] );
	$tag                  = sanitize_text_field( $atts['tag'] );
	$tax_operator         = $atts['tax_operator'];
	$tax_include_children = filter_var( $atts['tax_include_children'], FILTER_VALIDATE_BOOLEAN );
	$tax_term             = sanitize_text_field( $atts['tax_term'] );
	$taxonomy             = sanitize_key( $atts['taxonomy'] );
	$time                 = sanitize_text_field( $atts['time'] );
	$shortcode_title      = sanitize_text_field( $atts['title'] );
    $title_color          = sanitize_text_field( $atts['title_color'] );
    $excerpt_color        = sanitize_text_field( $atts['excerpt_color'] );
    $link_to_source       = sanitize_text_field( $atts['link_to_source'] );
    $excerpt_font_size    = sanitize_text_field( $atts['excerpt_font_size'] );
    $title_font_size      = sanitize_text_field( $atts['title_font_size'] );
    $read_more_text       = sanitize_text_field( $atts['read_more_text'] );
	$wrapper              = sanitize_text_field( $atts['wrapper'] );
	$wrapper_class        = array_map( 'sanitize_html_class', ( explode( ' ', $atts['wrapper_class'] ) ) );
	if( !empty( $wrapper_class ) )
		$wrapper_class = ' class="' . implode( ' ', $wrapper_class ) . '"';
	$wrapper_id = sanitize_html_class( $atts['wrapper_id'] );
	if( !empty( $wrapper_id ) )
		$wrapper_id = ' id="' . esc_html($wrapper_id) . '"';
	$args = array(
		'category_name'       => $category,
		'order'               => $order,
		'orderby'             => $orderby,
		'post_type'           => explode( ',', $post_type ),
		'posts_per_page'      => $posts_per_page,
		'tag'                 => $tag,
	);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
	if ( ! empty( $date ) || ! empty( $time ) || ! empty( $date_query_after ) || ! empty( $date_query_before ) ) {
		$initial_date_query = $date_query_top_lvl = array();
		$valid_date_columns = array(
			'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt',
			'comment_date', 'comment_date_gmt'
		);
		$valid_compare_ops = array( '=', '!=', '>', '>=', '<', '<=', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' );
		$dates = aiomatic_sanitize_date_time( $date );
		if ( ! empty( $dates ) ) {
			if ( is_string( $dates ) ) {
				$timestamp = strtotime( $dates );
				$dates = array(
					'year'   => date( 'Y', $timestamp ),
					'month'  => date( 'm', $timestamp ),
					'day'    => date( 'd', $timestamp ),
				);
			}
			foreach ( $dates as $arg => $segment ) {
				$initial_date_query[ $arg ] = $segment;
			}
		}
		$times = aiomatic_sanitize_date_time( $time, 'time' );
		if ( ! empty( $times ) ) {
			foreach ( $times as $arg => $segment ) {
				$initial_date_query[ $arg ] = $segment;
			}
		}
		$before = aiomatic_sanitize_date_time( $date_query_before, 'date', true );
		if ( ! empty( $before ) ) {
			$initial_date_query['before'] = $before;
		}
		$after = aiomatic_sanitize_date_time( $date_query_after, 'date', true );
		if ( ! empty( $after ) ) {
			$initial_date_query['after'] = $after;
		}
		if ( ! empty( $date_query_column ) && in_array( $date_query_column, $valid_date_columns ) ) {
			$initial_date_query['column'] = $date_query_column;
		}
		if ( ! empty( $date_query_compare ) && in_array( $date_query_compare, $valid_compare_ops ) ) {
			$initial_date_query['compare'] = $date_query_compare;
		}
		if ( ! empty( $date_column ) && in_array( $date_column, $valid_date_columns ) ) {
			$date_query_top_lvl['column'] = $date_column;
		}
		if ( ! empty( $date_compare ) && in_array( $date_compare, $valid_compare_ops ) ) {
			$date_query_top_lvl['compare'] = $date_compare;
		}
		if ( ! empty( $initial_date_query ) ) {
			$date_query_top_lvl[] = $initial_date_query;
		}
		$args['date_query'] = $date_query_top_lvl;
	}
    $args['meta_key'] = 'aiomatic_parent_rule';
    if($ruleid != '')
    {
        $args['meta_value'] = $ruleid;
    }
	if( $ignore_sticky_posts )
		$args['ignore_sticky_posts'] = true;
	 
	if( $id ) {
		$posts_in = array_map( 'intval', explode( ',', $id ) );
		$args['post__in'] = $posts_in;
	}
	if( is_singular() && $exclude_current )
		$args['post__not_in'] = array( get_the_ID() );
	if( !empty( $author ) ) {
		if( 'current' == $author && is_user_logged_in() )
			$args['author_name'] = wp_get_current_user()->user_login;
		elseif( 'current' == $author )
            $unrelevar = false;
			 
		else
			$args['author_name'] = $author;
	}
	if( !empty( $offset ) )
		$args['offset'] = $offset;
	$post_status = explode( ', ', $post_status );
	$validated = array();
	$available = array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash', 'any' );
	foreach ( $post_status as $unvalidated )
		if ( in_array( $unvalidated, $available ) )
			$validated[] = $unvalidated;
	if( !empty( $validated ) )
		$args['post_status'] = $validated;
	if ( !empty( $taxonomy ) && !empty( $tax_term ) ) {
		if( 'current' == $tax_term ) {
			global $post;
			$terms = wp_get_post_terms(get_the_ID(), $taxonomy);
			$tax_term = array();
			foreach ($terms as $term) {
				$tax_term[] = $term->slug;
			}
		}else{
			$tax_term = explode( ', ', $tax_term );
		}
		if( !in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) )
			$tax_operator = 'IN';
		$tax_args = array(
			'tax_query' => array(
				array(
					'taxonomy'         => $taxonomy,
					'field'            => 'slug',
					'terms'            => $tax_term,
					'operator'         => $tax_operator,
					'include_children' => $tax_include_children,
				)
			)
		);
		$count = 2;
		$more_tax_queries = false;
		while(
			isset( $original_atts['taxonomy_' . $count] ) && !empty( $original_atts['taxonomy_' . $count] ) &&
			isset( $original_atts['tax_' . esc_html($count) . '_term'] ) && !empty( $original_atts['tax_' . esc_html($count) . '_term'] )
		):
			$more_tax_queries = true;
			$taxonomy = sanitize_key( $original_atts['taxonomy_' . $count] );
	 		$terms = explode( ', ', sanitize_text_field( $original_atts['tax_' . esc_html($count) . '_term'] ) );
	 		$tax_operator = isset( $original_atts['tax_' . esc_html($count) . '_operator'] ) ? $original_atts['tax_' . esc_html($count) . '_operator'] : 'IN';
	 		$tax_operator = in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) ? $tax_operator : 'IN';
	 		$tax_include_children = isset( $original_atts['tax_' . esc_html($count) . '_include_children'] ) ? filter_var( $atts['tax_' . esc_html($count) . '_include_children'], FILTER_VALIDATE_BOOLEAN ) : true;
	 		$tax_args['tax_query'][] = array(
	 			'taxonomy'         => $taxonomy,
	 			'field'            => 'slug',
	 			'terms'            => $terms,
	 			'operator'         => $tax_operator,
	 			'include_children' => $tax_include_children,
	 		);
			$count++;
		endwhile;
		if( $more_tax_queries ):
			$tax_relation = 'AND';
			if( isset( $original_atts['tax_relation'] ) && in_array( $original_atts['tax_relation'], array( 'AND', 'OR' ) ) )
				$tax_relation = $original_atts['tax_relation'];
			$args['tax_query']['relation'] = $tax_relation;
		endif;
		$args = array_merge_recursive( $args, $tax_args );
	}
	if( $post_parent !== false ) {
		if( 'current' == $post_parent ) {
			global $post;
			$post_parent = get_the_ID();
		}
		$args['post_parent'] = intval( $post_parent );
	}
	$wrapper_options = array( 'ul', 'ol', 'div' );
	if( ! in_array( $wrapper, $wrapper_options ) )
		$wrapper = 'ul';
	$inner_wrapper = 'div' == $wrapper ? 'div' : 'li';
	$listing = new WP_Query( apply_filters( 'display_posts_shortcode_args', $args, $original_atts ) );
	if ( ! $listing->have_posts() ) {
		return apply_filters( 'display_posts_shortcode_no_results', wpautop( $no_posts_message ) );
	}
	$inner = '';
    wp_suspend_cache_addition(true);
	while ( $listing->have_posts() ): $listing->the_post(); global $post;
		$image = $date = $author = $excerpt = $content = '';
		if ( $include_title && $include_link ) {
            if($link_to_source == 'yes')
            {
                $source_url = get_post_meta($post->ID, 'aiomatic_post_url', true);
                if(!empty($source_url))
                {
                    $title = '<a class="aiomatic_display_title" href="' . esc_url_raw($source_url) . '"><span class="cr_display_span" >' . get_the_title() . '</span></a>';
                }
                else
                {
                    $title = '<a class="aiomatic_display_title" href="' . apply_filters( 'the_permalink', get_permalink() ) . '"><span class="cr_display_span" >' . get_the_title() . '</span></a>';
                }
            }
            else
            {
                $title = '<a class="aiomatic_display_title" href="' . apply_filters( 'the_permalink', get_permalink() ) . '"><span class="cr_display_span" >' . get_the_title() . '</span></a>';
            }
		} elseif( $include_title ) {
			$title = '<span class="aiomatic_display_title" class="cr_display_span">' . get_the_title() . '</span>';
		} else {
			$title = '';
		}
		if ( $image_size && has_post_thumbnail() && $include_link ) {
            if($link_to_source == 'yes')
            {
                $source_url = get_post_meta($post->ID, 'aiomatic_post_url', true);
                if(!empty($source_url))
                {
                    $image = '<a class="aiomatic_display_image" href="' . esc_url_raw($source_url) . '">' . get_the_post_thumbnail( get_the_ID(), $image_size ) . '</a> <br/>';
                }
                else
                {
                    $image = '<a class="aiomatic_display_image" href="' . get_permalink() . '">' . get_the_post_thumbnail( get_the_ID(), $image_size ) . '</a> <br/>';
                }
            }
            else
            {
                $image = '<a class="aiomatic_display_image" href="' . get_permalink() . '">' . get_the_post_thumbnail( get_the_ID(), $image_size ) . '</a> <br/>';
            }
		} elseif( $image_size && has_post_thumbnail() ) {
			$image = '<span class="aiomatic_display_image">' . get_the_post_thumbnail( get_the_ID(), $image_size ) . '</span> <br/>';
		}
		if ( $include_date )
			$date = ' <span class="date">' . get_the_date( $date_format ) . '</span>';
		if( $include_author )
			$author = apply_filters( 'display_posts_shortcode_author', ' <span class="aiomatic_display_author">by ' . get_the_author() . '</span>', $original_atts );
		if ( $include_excerpt ) {
			if( $excerpt_length || $excerpt_more || $excerpt_more_link ) {
				$length = $excerpt_length ? $excerpt_length : apply_filters( 'excerpt_length', 55 );
				$more   = $excerpt_more ? $excerpt_more : apply_filters( 'excerpt_more', '' );
				$more   = $excerpt_more_link ? ' <a href="' . get_permalink() . '">' . esc_html($more) . '</a>' : ' ' . esc_html($more);
				if( has_excerpt() && apply_filters( 'display_posts_shortcode_full_manual_excerpt', false ) ) {
					$excerpt = $post->post_excerpt . $more;
				} elseif( has_excerpt() ) {
					$excerpt = wp_trim_words( strip_shortcodes( $post->post_excerpt ), $length, $more );
				} else {
					$excerpt = wp_trim_words( strip_shortcodes( $post->post_content ), $length, $more );
				}
			} else {
				$excerpt = get_the_excerpt();
			}
			$excerpt = ' <br/><br/> <span class="aiomatic_display_excerpt" class="cr_display_excerpt_adv">' . $excerpt . '</span>';
            if($read_more_text != '')
            {
                if($link_to_source == 'yes')
                {
                    $source_url = get_post_meta($post->ID, 'aiomatic_post_url', true);
                    if(!empty($source_url))
                    {
                        $excerpt .= '<br/><a href="' . esc_url_raw($source_url) . '"><span class="aiomatic_display_excerpt" class="cr_display_excerpt_adv">' . esc_html($read_more_text) . '</span></a>';
                    }
                    else
                    {
                        $excerpt .= '<br/><a href="' . get_permalink() . '"><span class="aiomatic_display_excerpt" class="cr_display_excerpt_adv">' . esc_html($read_more_text) . '</span></a>';
                    }
                }
                else
                {
                    $excerpt .= '<br/><a href="' . get_permalink() . '"><span class="aiomatic_display_excerpt" class="cr_display_excerpt_adv">' . esc_html($read_more_text) . '</span></a>';
                }
            }
		}
		if( $include_content ) {
			add_filter( 'shortcode_atts_display-posts', 'aiomatic_display_posts_off', 10, 3 );
			$content = '<div class="' . implode( ' ', $content_class ) . '">' . apply_filters( 'the_content', get_the_content() ) . '</div>';
			remove_filter( 'shortcode_atts_display-posts', 'aiomatic_display_posts_off', 10, 3 );
		}
		$category_display_text = '';
		if( $category_display && is_object_in_taxonomy( get_post_type(), $category_display ) ) {
			$terms = get_the_terms( get_the_ID(), $category_display );
			$term_output = array();
			foreach( $terms as $term )
				$term_output[] = '<a href="' . get_term_link( $term, $category_display ) . '">' . esc_html($term->name) . '</a>';
			$category_display_text = ' <span class="category-display"><span class="category-display-label">' . esc_html($category_label) . '</span> ' . trim(implode( ', ', $term_output ), ', ') . '</span>';
			$category_display_text = apply_filters( 'display_posts_shortcode_category_display', $category_display_text );
		}
		$class = array( 'listing-item' );
		$class = array_map( 'sanitize_html_class', apply_filters( 'display_posts_shortcode_post_class', $class, $post, $listing, $original_atts ) );
		$output = '<br/><' . esc_html($inner_wrapper) . ' class="' . implode( ' ', $class ) . '">' . $image . $title . $date . $author . $category_display_text . $excerpt . $content . '</' . esc_html($inner_wrapper) . '><br/><br/><hr class="cr_hr_dot"/>';		$inner .= apply_filters( 'display_posts_shortcode_output', $output, $original_atts, $image, $title, $date, $excerpt, $inner_wrapper, $content, $class );
	endwhile; wp_reset_postdata();
    wp_suspend_cache_addition(false);
	$open = apply_filters( 'display_posts_shortcode_wrapper_open', '<' . $wrapper . $wrapper_class . $wrapper_id . '>', $original_atts );
	$close = apply_filters( 'display_posts_shortcode_wrapper_close', '</' . esc_html($wrapper) . '>', $original_atts );
	$return = $open;
	if( $shortcode_title ) {
		$title_tag = apply_filters( 'display_posts_shortcode_title_tag', 'h2', $original_atts );
		$return .= '<' . esc_html($title_tag) . ' class="display-posts-title">' . esc_html($shortcode_title) . '</' . esc_html($title_tag) . '>' . "\n";
	}
	$return .= $inner . $close;
    $reg_css_code = '.aiomatic-hide {display:none!important;visibility:hidden}.cr_hr_dot{border-top: dotted 1px;}.cr_display_span{font-size:' . esc_html($title_font_size) . ';color:' . esc_html($title_color) . ' ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}.cr_display_excerpt_adv{font-size:' . esc_html($excerpt_font_size) . ';color:' . esc_html($excerpt_color) . ' ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
    $name = md5(get_bloginfo());
    wp_register_style( $name . '-display-style', false );
    wp_enqueue_style( $name . '-display-style' );
    wp_add_inline_style( $name . '-display-style', $reg_css_code );
	return $return;
}

add_shortcode( 'aiomatic-list-posts', 'aiomatic_list_posts' );
function aiomatic_list_posts( $atts ) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    ob_start();
    extract( shortcode_atts( array (
        'type' => 'any',
        'order' => 'ASC',
        'orderby' => 'title',
        'posts' => 50,
        'posts_per_page' => 50,
        'category' => '',
        'ruleid' => ''
    ), $atts ) );
    $options = array(
        'post_type' => $type,
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => $posts,
        'category_name' => $category,
        'meta_key' => 'aiomatic_parent_rule',
        'meta_value' => $ruleid
    );
    $query = new WP_Query( $options );
    if ( $query->have_posts() ) { ?>
        <ul class="clothes-listing">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <a href="<?php echo esc_url_raw(get_permalink()); ?>"><?php echo esc_html(get_the_title());?></a>
            </li>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </ul>
    <?php $myvariable = ob_get_clean();
    return $myvariable;
    }
    return '';
}

add_shortcode("aiomatic-image", "aiomatic_image");
function aiomatic_image($atts, $cont, $tagx)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $is_elementor = false;
    $seed_expre = isset( $atts['seed_expre'] )? esc_attr($atts['seed_expre']) : '';
    $image_model = isset( $atts['image_model'] )? esc_attr($atts['image_model']) : 'dalle2';
    $static_content = isset( $atts['static_content'] )? esc_attr($atts['static_content']) : '';
    $copy_locally = isset( $atts['copy_locally'] )? esc_attr($atts['copy_locally']) : '';
    $image_size = isset( $atts['image_size'] )? esc_attr($atts['image_size']) : '';
    $cache_seconds = isset( $atts['cache_seconds'] )? intval(esc_attr($atts['cache_seconds'])) : 2592000;
    $post = $GLOBALS['post'];
    if(empty($seed_expre))
    {
        $exc = get_the_excerpt();
        $exc = trim(strip_tags($exc));
        $cnt = get_the_content();
        $cnt = trim(strip_tags($cnt));
        $cnt = strip_shortcodes($cnt);
        if($cnt != false && !empty($cnt))
        {
            $seed_expre = aiomatic_substr($cnt, 0, 200);
        }
        elseif(!empty($exc) && $exc != false)
        {
            $seed_expre = $exc;
        }
        else
        {
            $seed_expre = get_the_title();
            $seed_expre = trim(strip_tags($seed_expre));
            if($seed_expre == '')
            {
                return '';
            }
        }
    }
    else
    {
        if(isset($post->ID))
        {
            if(aiomatic_check_is_elementor($post->ID))
            {
                $is_elementor = true;
            }
            $post_link = get_permalink($post->ID);
            $blog_title       = html_entity_decode(get_bloginfo('title'));
            $author_obj       = get_user_by('id', $post->post_author);
            if($author_obj !== false)
            {
                $user_name        = $author_obj->user_nicename;
            }
            $final_content = $post->post_content;
            $post_title    = $post->post_title;
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
            $post_excerpt = $post->post_excerpt;
            $postID = $post->ID;
        }
        else
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
        }
        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
        if (filter_var($seed_expre, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($seed_expre, '.txt'))
        {
            $txt_content = aiomatic_get_web_page($seed_expre);
            if ($txt_content !== FALSE) 
            {
                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                $txt_content = array_filter($txt_content);
                if(count($txt_content) > 0)
                {
                    $txt_content = $txt_content[array_rand($txt_content)];
                    if(trim($txt_content) != '') 
                    {
                        $seed_expre = $txt_content;
                        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    }
                }
            }
        }
    }
    $md5v = md5($seed_expre . $image_size);
    
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        if(isset($post->ID) && $static_content == 'on')
        {
            $tranzi = false;
        }
        else
        {
            $tranzi = get_transient('aiomatic_image_transient' . $md5v);
        }
        if($tranzi === false)
        {
            if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') {
                aiomatic_log_to_file('You need to add an API key in plugin settings for this shortcode to work.');
                set_transient('aiomatic_image_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            else
            {
                $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                $appids = array_filter($appids);
                $token = $appids[array_rand($appids)];
            }
            $tranzi = '';
            if(strlen($seed_expre) > 400)
            {
                $seed_expre = aiomatic_substr($seed_expre, 0, 400);
            }
            $aierror = '';
            $temp_get_imgs = aiomatic_generate_ai_image($token, 1, $seed_expre, $image_size, 'shortcodeImage', false, 0, $aierror, $image_model);
            if($temp_get_imgs !== false)
            {
                foreach($temp_get_imgs as $tmpimg)
                {
                    $tranzi = $tmpimg;
                }
                if(!empty($tranzi))
                {
                    if($copy_locally == 'on')
                    {
                        $attached_id = '';
                        $localpath = aiomatic_copy_image_locally($tranzi, $attached_id, $copy_locally, false, $seed_expre);
                        if($localpath !== false)
                        {
                            $tranzi = $localpath[0];
                        }
                    }
                    if(!isset($post->ID) || $static_content != 'on')
                    {
                        set_transient('aiomatic_image_transient' . $md5v, $tranzi, $cache_seconds);
                    }
                    else
                    {
                        $shortcode_reconstruction = '#\[\s*' . preg_quote($tagx) . '\s*';
                        foreach($atts as $atx => $vatx)
                        {
                            $shortcode_reconstruction .= ' ' . preg_quote($atx) . '\s*=\s*[\'"]?' . preg_quote($vatx) . '[\'"]?';
                        }
                        $shortcode_reconstruction .= '\s*\]#i';
                        preg_match_all($shortcode_reconstruction, $post->post_content, $initmatches);
                        if(isset($initmatches[0][0]) && $initmatches[0][0] != '')
                        {
                            $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            wp_update_post($post);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                        }
                        else
                        {
                            preg_match_all('#\[aiomatic-image([^\]]*?)\]#i', $post->post_content, $zamatches);
                            if(isset($zamatches[0][0]) && $zamatches[0][0] != '')
                            {
                                $post->post_content = preg_replace('#\[aiomatic-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                wp_update_post($post);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                            }
                            else
                            {
                                set_transient('aiomatic_image_transient' . $md5v, $tranzi, $cache_seconds);
                            }
                        }
                        if($is_elementor)
                        {
                            $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                            if(!empty($elementor_data))
                            {
                                $elementor_json = json_decode($elementor_data);
                                if(!empty($elementor_json))
                                {
                                    $changemade = false;
                                    for($i = 0; $i < count($elementor_json); $i++)
                                    {
                                        if($elementor_json[$i]->elType == 'section' || $elementor_json[$i]->elType == 'column')
                                        {
                                            for($j = 0; $j < count($elementor_json[$i]->elements); $j++)
                                            {
                                                if($elementor_json[$i]->elements[$j]->elType == 'section' || $elementor_json[$i]->elements[$j]->elType == 'column')
                                                {
                                                    for($k = 0; $k < count($elementor_json[$i]->elements[$j]->elements); $k++)
                                                    {
                                                        if($elementor_json[$i]->elements[$j]->elements[$k]->elType == 'widget' && $elementor_json[$i]->elements[$j]->elements[$k]->widgetType == 'shortcode')
                                                        {
                                                            if(isset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode))
                                                            {
                                                                $sc = $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode;
                                                                $sc = preg_replace('#\[aiomatic-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                if($sc != $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode)
                                                                {
                                                                    unset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode);
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->settings->html = $sc;
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->widgetType = 'html';
                                                                    $changemade = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if($elementor_json[$i]->elements[$j]->elType == 'widget' && $elementor_json[$i]->elements[$j]->widgetType == 'shortcode')
                                                    {
                                                        if(isset($elementor_json[$i]->elements[$j]->settings->shortcode))
                                                        {
                                                            $sc = $elementor_json[$i]->elements[$j]->settings->shortcode;
                                                            $sc = preg_replace('#\[aiomatic-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            if($sc != $elementor_json[$i]->elements[$j]->settings->shortcode)
                                                            {
                                                                unset($elementor_json[$i]->elements[$j]->settings->shortcode);
                                                                $elementor_json[$i]->elements[$j]->settings->html = $sc;
                                                                $elementor_json[$i]->elements[$j]->widgetType = 'html';
                                                                $changemade = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if($elementor_json[$i]->elType == 'widget' && $elementor_json[$i]->widgetType == 'shortcode')
                                            {
                                                if(isset($elementor_json[$i]->settings->shortcode))
                                                {
                                                    $sc = $elementor_json[$i]->settings->shortcode;
                                                    $sc = preg_replace('#\[aiomatic-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    if($sc != $elementor_json[$i]->settings->shortcode)
                                                    {
                                                        unset($elementor_json[$i]->settings->shortcode);
                                                        $elementor_json[$i]->settings->html = $sc;
                                                        $elementor_json[$i]->widgetType = 'html';
                                                        $changemade = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($changemade == true)
                                    {
                                        $elementor_data_new = wp_json_encode($elementor_json);
                                        $elementor_data_new = trim($elementor_data_new, '"');
                                        if(!empty($elementor_data_new))
                                        {
                                            update_post_meta($post->ID, '_elementor_data', $elementor_data_new);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                aiomatic_log_to_file('Failed to create an image: ' . $aierror);
                set_transient('aiomatic_image_transient' . $md5v, 'not_working', intval($cache_seconds/10));
            }
        }
    }
    if(!empty($tranzi))
    {
        return '<img src="' . $tranzi . '">';
    }
    return '';
}

add_shortcode("aiomatic-stable-image", "aiomatic_stable_image");
function aiomatic_stable_image($atts, $cont, $tagx)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $is_elementor = false;
    $seed_expre = isset( $atts['seed_expre'] )? esc_attr($atts['seed_expre']) : '';
    $static_content = isset( $atts['static_content'] )? esc_attr($atts['static_content']) : '';
    $copy_locally = isset( $atts['copy_locally'] )? esc_attr($atts['copy_locally']) : '';
    $image_size = isset( $atts['image_size'] )? esc_attr($atts['image_size']) : '';
    $source_image = isset( $atts['source_image'] )? esc_attr($atts['source_image']) : '';
    $image_strength = isset( $atts['image_strength'] )? intval(esc_attr($atts['image_strength'])) : 0.5;
    $cache_seconds = isset( $atts['cache_seconds'] )? intval(esc_attr($atts['cache_seconds'])) : 2592000;
    $post = $GLOBALS['post'];
    if(empty($seed_expre))
    {
        $exc = get_the_excerpt();
        $exc = trim(strip_tags($exc));
        $cnt = get_the_content();
        $cnt = trim(strip_tags($cnt));
        $cnt = strip_shortcodes($cnt);
        if($cnt != false && !empty($cnt))
        {
            $seed_expre = aiomatic_substr($cnt, 0, 200);
        }
        elseif(!empty($exc) && $exc != false)
        {
            $seed_expre = $exc;
        }
        else
        {
            $seed_expre = get_the_title();
            $seed_expre = trim(strip_tags($seed_expre));
            if($seed_expre == '')
            {
                return '';
            }
        }
    }
    else
    {
        if(isset($post->ID))
        {
            if(aiomatic_check_is_elementor($post->ID))
            {
                $is_elementor = true;
            }
            $post_link = get_permalink($post->ID);
            $blog_title       = html_entity_decode(get_bloginfo('title'));
            $author_obj       = get_user_by('id', $post->post_author);
            if($author_obj !== false)
            {
                $user_name        = $author_obj->user_nicename;
            }
            $final_content = $post->post_content;
            $post_title    = $post->post_title;
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
            $post_excerpt = $post->post_excerpt;
            $postID = $post->ID;
        }
        else
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
        }
        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
        if (filter_var($seed_expre, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($seed_expre, '.txt'))
        {
            $txt_content = aiomatic_get_web_page($seed_expre);
            if ($txt_content !== FALSE) 
            {
                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                $txt_content = array_filter($txt_content);
                if(count($txt_content) > 0)
                {
                    $txt_content = $txt_content[array_rand($txt_content)];
                    if(trim($txt_content) != '') 
                    {
                        $seed_expre = $txt_content;
                        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    }
                }
            }
        }
    }
    $md5v = md5($seed_expre . $image_size);
    $local_now = false;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        if(isset($post->ID) && $static_content == 'on')
        {
            $tranzi = false;
        }
        else
        {
            $tranzi = get_transient('aiomatic_stability_image_transient' . $md5v);
        }
        if($tranzi === false)
        {
            if (!isset($aiomatic_Main_Settings['stability_app_id']) || trim($aiomatic_Main_Settings['stability_app_id']) == '') {
                aiomatic_log_to_file('You need to add an API key in plugin settings for this shortcode to work.');
                set_transient('aiomatic_stability_image_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            $tranzi = '';
            if(strlen($seed_expre) > 2000)
            {
                $seed_expre = aiomatic_substr($seed_expre, 0, 2000);
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
            $aierror = '';
            $get_img = aiomatic_generate_stability_image($seed_expre, $height, $width, 'shortcodeStableImage', 0, true, $aierror, false, false, $source_image, $image_strength);
            if($get_img !== false)
            {
                $tranzi = $get_img;
                if(!empty($tranzi))
                {
                    if($copy_locally == 'on' || $copy_locally == 'wasabi' || $copy_locally == 'generics3' || $copy_locally == 'amazon' || $copy_locally == 'digital')
                    {
                        $attached_id = '';
                        $localpath = aiomatic_copy_image_locally('data:image/png;base64,' . $tranzi, $attached_id, $copy_locally, false, $seed_expre);
                        if($localpath !== false)
                        {
                            $tranzi = $localpath[0];
                            $local_now = true;
                        }
                    }
                    if(!isset($post->ID) || $static_content != 'on')
                    {
                        set_transient('aiomatic_stability_image_transient' . $md5v, $tranzi, $cache_seconds);
                    }
                    else
                    {
                        $shortcode_reconstruction = '#\[\s*' . preg_quote($tagx) . '\s*';
                        foreach($atts as $atx => $vatx)
                        {
                            $shortcode_reconstruction .= ' ' . preg_quote($atx) . '\s*=\s*[\'"]?' . preg_quote($vatx) . '[\'"]?';
                        }
                        $shortcode_reconstruction .= '\s*\]#i';
                        preg_match_all($shortcode_reconstruction, $post->post_content, $initmatches);
                        if(isset($initmatches[0][0]) && $initmatches[0][0] != '')
                        {
                            if($local_now == true)
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            else
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="data:image/png;base64,' . $tranzi . '">', $post->post_content);
                            }
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            wp_update_post($post);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                        }
                        else
                        {
                            preg_match_all('#\[aiomatic-stable-image([^\]]*?)\]#i', $post->post_content, $zamatches);
                            if(isset($zamatches[0][0]) && $zamatches[0][0] != '')
                            {
                                if($local_now == true)
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                else
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="data:image/png;base64,' . $tranzi . '">', $post->post_content);
                                }
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                wp_update_post($post);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                            }
                            else
                            {
                                set_transient('aiomatic_stability_image_transient' . $md5v, $tranzi, $cache_seconds);
                            }
                        }
                        if($is_elementor)
                        {
                            $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                            if(!empty($elementor_data))
                            {
                                $elementor_json = json_decode($elementor_data);
                                if(!empty($elementor_json))
                                {
                                    $changemade = false;
                                    for($i = 0; $i < count($elementor_json); $i++)
                                    {
                                        if($elementor_json[$i]->elType == 'section' || $elementor_json[$i]->elType == 'column')
                                        {
                                            for($j = 0; $j < count($elementor_json[$i]->elements); $j++)
                                            {
                                                if($elementor_json[$i]->elements[$j]->elType == 'section' || $elementor_json[$i]->elements[$j]->elType == 'column')
                                                {
                                                    for($k = 0; $k < count($elementor_json[$i]->elements[$j]->elements); $k++)
                                                    {
                                                        if($elementor_json[$i]->elements[$j]->elements[$k]->elType == 'widget' && $elementor_json[$i]->elements[$j]->elements[$k]->widgetType == 'shortcode')
                                                        {
                                                            if(isset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode))
                                                            {
                                                                $sc = $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode;
                                                                if($local_now == true)
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                else
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="data:image/png;base64,' . $tranzi . '">', $sc);
                                                                }
                                                                if($sc != $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode)
                                                                {
                                                                    unset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode);
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->settings->html = $sc;
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->widgetType = 'html';
                                                                    $changemade = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if($elementor_json[$i]->elements[$j]->elType == 'widget' && $elementor_json[$i]->elements[$j]->widgetType == 'shortcode')
                                                    {
                                                        if(isset($elementor_json[$i]->elements[$j]->settings->shortcode))
                                                        {
                                                            $sc = $elementor_json[$i]->elements[$j]->settings->shortcode;
                                                            if($local_now == true)
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            else
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="data:image/png;base64,' . $tranzi . '">', $sc);
                                                            }
                                                            if($sc != $elementor_json[$i]->elements[$j]->settings->shortcode)
                                                            {
                                                                unset($elementor_json[$i]->elements[$j]->settings->shortcode);
                                                                $elementor_json[$i]->elements[$j]->settings->html = $sc;
                                                                $elementor_json[$i]->elements[$j]->widgetType = 'html';
                                                                $changemade = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if($elementor_json[$i]->elType == 'widget' && $elementor_json[$i]->widgetType == 'shortcode')
                                            {
                                                if(isset($elementor_json[$i]->settings->shortcode))
                                                {
                                                    $sc = $elementor_json[$i]->settings->shortcode;
                                                    if($local_now == true)
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    else
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-stable-image([^\]]*?)\]#i', '<img src="data:image/png;base64,' . $tranzi . '">', $sc);
                                                    }
                                                    if($sc != $elementor_json[$i]->settings->shortcode)
                                                    {
                                                        unset($elementor_json[$i]->settings->shortcode);
                                                        $elementor_json[$i]->settings->html = $sc;
                                                        $elementor_json[$i]->widgetType = 'html';
                                                        $changemade = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($changemade == true)
                                    {
                                        $elementor_data_new = wp_json_encode($elementor_json);
                                        $elementor_data_new = trim($elementor_data_new, '"');
                                        if(!empty($elementor_data_new))
                                        {
                                            update_post_meta($post->ID, '_elementor_data', $elementor_data_new);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                aiomatic_log_to_file('Failed to generate Stability.AI image: ' . $aierror);
                $get_img = '';
            }
        }
    }
    if(!empty($tranzi))
    {
        if($local_now == true)
        {
            return '<img src="' . $tranzi . '">';
        }
        else
        {
            return '<img src="data:image/png;base64,' . $tranzi . '">';
        }
    }
    return '';
}

add_shortcode("aiomatic-midjourney-image", "aiomatic_midjourney_image");
function aiomatic_midjourney_image($atts, $cont, $tagx)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $is_elementor = false;
    $seed_expre = isset( $atts['seed_expre'] )? esc_attr($atts['seed_expre']) : '';
    $static_content = isset( $atts['static_content'] )? esc_attr($atts['static_content']) : '';
    $copy_locally = isset( $atts['copy_locally'] )? esc_attr($atts['copy_locally']) : '';
    $image_size = isset( $atts['image_size'] )? esc_attr($atts['image_size']) : '';
    $cache_seconds = isset( $atts['cache_seconds'] )? intval(esc_attr($atts['cache_seconds'])) : 2592000;
    $post = $GLOBALS['post'];
    if(empty($seed_expre))
    {
        $exc = get_the_excerpt();
        $exc = trim(strip_tags($exc));
        $cnt = get_the_content();
        $cnt = trim(strip_tags($cnt));
        $cnt = strip_shortcodes($cnt);
        if($cnt != false && !empty($cnt))
        {
            $seed_expre = aiomatic_substr($cnt, 0, 200);
        }
        elseif(!empty($exc) && $exc != false)
        {
            $seed_expre = $exc;
        }
        else
        {
            $seed_expre = get_the_title();
            $seed_expre = trim(strip_tags($seed_expre));
            if($seed_expre == '')
            {
                return '';
            }
        }
    }
    else
    {
        if(isset($post->ID))
        {
            if(aiomatic_check_is_elementor($post->ID))
            {
                $is_elementor = true;
            }
            $post_link = get_permalink($post->ID);
            $blog_title       = html_entity_decode(get_bloginfo('title'));
            $author_obj       = get_user_by('id', $post->post_author);
            if($author_obj !== false)
            {
                $user_name        = $author_obj->user_nicename;
            }
            $final_content = $post->post_content;
            $post_title    = $post->post_title;
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
            $post_excerpt = $post->post_excerpt;
            $postID = $post->ID;
        }
        else
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
        }
        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
        if (filter_var($seed_expre, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($seed_expre, '.txt'))
        {
            $txt_content = aiomatic_get_web_page($seed_expre);
            if ($txt_content !== FALSE) 
            {
                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                $txt_content = array_filter($txt_content);
                if(count($txt_content) > 0)
                {
                    $txt_content = $txt_content[array_rand($txt_content)];
                    if(trim($txt_content) != '') 
                    {
                        $seed_expre = $txt_content;
                        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    }
                }
            }
        }
    }
    $md5v = md5($seed_expre . $image_size);
    $local_now = false;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        if(isset($post->ID) && $static_content == 'on')
        {
            $tranzi = false;
        }
        else
        {
            $tranzi = get_transient('aiomatic_midjourney_image_transient' . $md5v);
        }
        if($tranzi === false)
        {
            if (!isset($aiomatic_Main_Settings['midjourney_app_id']) || trim($aiomatic_Main_Settings['midjourney_app_id']) == '') {
                aiomatic_log_to_file('You need to add a GoAPI API key in plugin settings for this shortcode to work.');
                set_transient('aiomatic_midjourney_image_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            $tranzi = '';
            if(strlen($seed_expre) > 2000)
            {
                $seed_expre = aiomatic_substr($seed_expre, 0, 2000);
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
            $aierror = '';
            $get_img = aiomatic_generate_ai_image_midjourney($seed_expre, $width, $height, 'shortcodeMidjourneyImage', true, $aierror);
            if($get_img !== false)
            {
                $tranzi = $get_img;
                if(!empty($tranzi))
                {
                    if($copy_locally == 'on' || $copy_locally == 'wasabi' || $copy_locally == 'generics3' || $copy_locally == 'amazon' || $copy_locally == 'digital')
                    {
                        $attached_id = '';
                        $localpath = aiomatic_copy_image_locally('' . $tranzi, $attached_id, $copy_locally, false, $seed_expre);
                        if($localpath !== false)
                        {
                            $tranzi = $localpath[0];
                            $local_now = true;
                        }
                    }
                    if(!isset($post->ID) || $static_content != 'on')
                    {
                        set_transient('aiomatic_midjourney_image_transient' . $md5v, $tranzi, $cache_seconds);
                    }
                    else
                    {
                        $shortcode_reconstruction = '#\[\s*' . preg_quote($tagx) . '\s*';
                        foreach($atts as $atx => $vatx)
                        {
                            $shortcode_reconstruction .= ' ' . preg_quote($atx) . '\s*=\s*[\'"]?' . preg_quote($vatx) . '[\'"]?';
                        }
                        $shortcode_reconstruction .= '\s*\]#i';
                        preg_match_all($shortcode_reconstruction, $post->post_content, $initmatches);
                        if(isset($initmatches[0][0]) && $initmatches[0][0] != '')
                        {
                            if($local_now == true)
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            else
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            wp_update_post($post);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                        }
                        else
                        {
                            preg_match_all('#\[aiomatic-midjourney-image([^\]]*?)\]#i', $post->post_content, $zamatches);
                            if(isset($zamatches[0][0]) && $zamatches[0][0] != '')
                            {
                                if($local_now == true)
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                else
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                wp_update_post($post);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                            }
                            else
                            {
                                set_transient('aiomatic_midjourney_image_transient' . $md5v, $tranzi, $cache_seconds);
                            }
                        }
                        if($is_elementor)
                        {
                            $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                            if(!empty($elementor_data))
                            {
                                $elementor_json = json_decode($elementor_data);
                                if(!empty($elementor_json))
                                {
                                    $changemade = false;
                                    for($i = 0; $i < count($elementor_json); $i++)
                                    {
                                        if($elementor_json[$i]->elType == 'section' || $elementor_json[$i]->elType == 'column')
                                        {
                                            for($j = 0; $j < count($elementor_json[$i]->elements); $j++)
                                            {
                                                if($elementor_json[$i]->elements[$j]->elType == 'section' || $elementor_json[$i]->elements[$j]->elType == 'column')
                                                {
                                                    for($k = 0; $k < count($elementor_json[$i]->elements[$j]->elements); $k++)
                                                    {
                                                        if($elementor_json[$i]->elements[$j]->elements[$k]->elType == 'widget' && $elementor_json[$i]->elements[$j]->elements[$k]->widgetType == 'shortcode')
                                                        {
                                                            if(isset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode))
                                                            {
                                                                $sc = $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode;
                                                                if($local_now == true)
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                else
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                if($sc != $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode)
                                                                {
                                                                    unset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode);
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->settings->html = $sc;
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->widgetType = 'html';
                                                                    $changemade = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if($elementor_json[$i]->elements[$j]->elType == 'widget' && $elementor_json[$i]->elements[$j]->widgetType == 'shortcode')
                                                    {
                                                        if(isset($elementor_json[$i]->elements[$j]->settings->shortcode))
                                                        {
                                                            $sc = $elementor_json[$i]->elements[$j]->settings->shortcode;
                                                            if($local_now == true)
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            else
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            if($sc != $elementor_json[$i]->elements[$j]->settings->shortcode)
                                                            {
                                                                unset($elementor_json[$i]->elements[$j]->settings->shortcode);
                                                                $elementor_json[$i]->elements[$j]->settings->html = $sc;
                                                                $elementor_json[$i]->elements[$j]->widgetType = 'html';
                                                                $changemade = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if($elementor_json[$i]->elType == 'widget' && $elementor_json[$i]->widgetType == 'shortcode')
                                            {
                                                if(isset($elementor_json[$i]->settings->shortcode))
                                                {
                                                    $sc = $elementor_json[$i]->settings->shortcode;
                                                    if($local_now == true)
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    else
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-midjourney-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    if($sc != $elementor_json[$i]->settings->shortcode)
                                                    {
                                                        unset($elementor_json[$i]->settings->shortcode);
                                                        $elementor_json[$i]->settings->html = $sc;
                                                        $elementor_json[$i]->widgetType = 'html';
                                                        $changemade = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($changemade == true)
                                    {
                                        $elementor_data_new = wp_json_encode($elementor_json);
                                        $elementor_data_new = trim($elementor_data_new, '"');
                                        if(!empty($elementor_data_new))
                                        {
                                            update_post_meta($post->ID, '_elementor_data', $elementor_data_new);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                aiomatic_log_to_file('Failed to generate GoAPI (Midjourney) image: ' . $aierror);
                $get_img = '';
            }
        }
    }
    if(!empty($tranzi))
    {
        if($local_now == true)
        {
            return '<img src="' . $tranzi . '">';
        }
        else
        {
            return '<img src="' . $tranzi . '">';
        }
    }
    return '';
}
add_shortcode('aiomatic-comparison-form', 'aiomatic_comparison_form_shortcode');
function aiomatic_comparison_form_shortcode() {
    ob_start();
    $aiomatic_defaults = array(
        'temperature' => 1,
        'max_tokens' => 500,
        'top_p' => 0,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
    );
    $aiomatic_prompts = array(
        esc_html__('Select Prompt', 'aiomatic-automatic-ai-content-writer') => "",
    
        // Basic content generation
        "Headline Generator" => "Generate a catchy headline for an article about: latest tech trends in 2024.",
        "Blog Post Intro" => "Write an engaging introduction for a blog post about sustainable fashion.",
        "Detailed Explanation" => "Provide a detailed explanation of the concept: blockchain technology.",
        "Summarize in One Sentence" => "Summarize this article in one sentence: [input article content].",
    
        // Niche-specific content
        "Recipe Generator" => "Create a recipe for a vegan-friendly dinner dish with a Mediterranean twist.",
        "Travel Guide" => "Write a travel guide for a first-time visitor to Kyoto, Japan.",
        "Workout Plan" => "Develop a weekly workout plan for a beginner focusing on strength and endurance.",
        "Financial Advice" => "Provide practical financial advice for someone planning to retire early.",
    
        // Analysis and summaries
        "Product Review Summary" => "Summarize the key points from a product review about the [product name].",
        "Sentiment Analysis" => "Analyze the sentiment of this text: [input text].",
        "Pros and Cons" => "List the pros and cons of electric vehicles in urban areas.",
        "Historical Context" => "Provide the historical background on the development of: artificial intelligence.",
        
        // Creative writing
        "Story Plot Idea" => "Generate an idea for a sci-fi short story set in a futuristic city.",
        "Character Backstory" => "Create a backstory for a character who is a detective in a small town.",
        "Poem Generator" => "Write a short poem about the changing seasons.",
        "Dialogue for Story" => "Create a dialogue between two friends discussing their travel plans to Europe.",
    
        // Informational and factual responses
        "Quick Fact Sheet" => "Provide a quick fact sheet on the topic: climate change impacts.",
        "Definition" => "Define the term: quantum computing.",
        "Step-by-Step Guide" => "Write a step-by-step guide for: setting up a new WordPress website.",
        "Comparison Summary" => "Compare the benefits of renting versus buying a home in 2024.",
        
        // Marketing and branding
        "Social Media Post" => "Create a social media post promoting a new eco-friendly skincare line.",
        "Email Newsletter Intro" => "Write an introduction for an email newsletter about upcoming product updates.",
        "Slogan Creator" => "Generate a catchy slogan for a sustainable fashion brand.",
        "SEO Meta Description" => "Write an SEO-friendly meta description for an article about: digital marketing strategies.",
    
        // Miscellaneous prompts for testing purposes
        "Fact-Check Information" => "Verify if this information is accurate: [input text].",
        "Joke Generator" => "Generate a clean, family-friendly joke about working from home.",
        "Tips and Tricks" => "List five practical tips for improving productivity while working remotely.",
        "List Generator" => "Create a list of essential tools for graphic designers in 2024.",
        "Future Trends Prediction" => "Predict three major trends in artificial intelligence by 2030.",
        
        // Specific tasks for testing longer and more complex outputs
        "Long-form Article Outline" => "Create an outline for a long-form article about renewable energy solutions.",
        "Technical Documentation" => "Write technical documentation on how to install and configure a VPN.",
        "Research Summary" => "Summarize the latest research findings on mental health in adolescents.",
        "White Paper Summary" => "Summarize the key points of a white paper on cybersecurity best practices.",
    
        // More elaborate and advanced testing
        "In-Depth Analysis" => "Provide an in-depth analysis of the impacts of AI on the global job market.",
        "Debate Points" => "List arguments for and against universal basic income.",
        "Complex Problem Explanation" => "Explain the traveling salesman problem and its importance in optimization.",
        "Creative Writing Prompt" => "Write a 500-word short story about a futuristic society where humans coexist with AI.",

        // Chatbot-related prompts
        "Customer Support Inquiry" => "Act as a customer support agent. Help a user troubleshoot a Wi-Fi connectivity issue with their laptop.",
        "Friendly Conversation Starter" => "Start a friendly conversation with someone who just joined a fitness program.",
        "Booking Assistance" => "Guide a user through the process of booking a flight to New York City, including date selection and seat preference.",
        "Personalized Greeting" => "Create a personalized greeting message for a user named Alex who just signed up for a productivity app.",
        "FAQ Assistant" => "Answer frequently asked questions about online payment security for a new fintech app.",
        "Chit-Chat Mode" => "Engage in casual conversation about the weather with someone from Tokyo.",
        "Motivational Chat" => "Motivate a user who's feeling down about their productivity today.",
        "Joke Bot" => "Tell a light-hearted, workplace-friendly joke.",
        "Appointment Reminder" => "Remind a user about their upcoming doctor's appointment and include a checklist of items to bring.",
        "Follow-Up Inquiry" => "Ask a follow-up question to someone who just read an article about time management.",
        "Personalized Recommendations" => "Recommend three science fiction books based on someone who likes space exploration themes.",
        "Product Feedback" => "Gather feedback from a user who recently purchased a digital marketing course.",
        
        // Enhanced interaction and detailed prompts
        "Scenario-Based Response" => "A user is lost while navigating a big city. Offer them guidance and tips to find their way.",
        "Roleplay as an Expert" => "Roleplay as a nutritionist giving advice to a client wanting to reduce sugar intake.",
        "Casual Catch-Up" => "Ask a returning user how their weekend was and suggest some activities they might enjoy.",
        "Career Advice" => "Provide career advice for someone looking to transition from teaching to instructional design.",
        "Product Upsell Suggestion" => "Suggest an upgrade to the premium version of a task management app and explain the added benefits.",
        "Onboarding Guidance" => "Help a new user navigate the initial steps of setting up their profile on a fitness tracking app.",
        "Custom Response Memory" => "Recall that a user mentioned they love hiking, and suggest three mountain trails for their next trip.",
        "Cultural Fact Share" => "Share a fun fact about the history of chocolate with a user who mentions they love sweets.",
        "Emotional Support" => "Offer empathetic support to someone who's feeling overwhelmed with a work deadline.",
        "Guided Reflection" => "Guide a user through a short reflection exercise on their recent achievements.",
        
        // More content generation prompts
        "Thought-Provoking Question" => "Pose a thought-provoking question about the future of AI in education.",
        "Book Summary" => "Summarize the main plot and themes of the novel 'To Kill a Mockingbird.'",
        "Mindfulness Tips" => "List five mindfulness tips for reducing stress.",
        "Beginner's Guide" => "Write a beginner's guide to budgeting for young adults.",
        "Career Transition Tips" => "List five tips for transitioning from a corporate job to freelance work.",
        "Tool Comparison" => "Compare two popular graphic design tools for beginners.",
        "SEO Content Ideas" => "Generate a list of content ideas around the keyword 'remote work productivity tips.'",
        "Tutorial Outline" => "Create an outline for a video tutorial on editing photos with Adobe Lightroom.",
        "Fact vs. Myth" => "Distinguish fact from myth about common beliefs regarding healthy eating.",
        "Quick Health Check" => "Suggest a quick 5-minute health check routine that can be done at home.",
        "Safety Tips" => "List essential safety tips for solo travelers.",
        "Monthly Goals" => "Help a user set achievable monthly goals for personal growth.",
        
        // Advanced prompts for testing complexity
        "Complex Problem Explanation" => "Explain how neural networks work in a way that a high school student could understand.",
        "Scientific Summary" => "Summarize the key findings of a recent study on climate change adaptation strategies.",
        "Debate on Technology" => "List arguments for and against the adoption of autonomous vehicles in urban areas.",
        "Philosophical Reflection" => "Reflect on the concept of happiness and its impact on personal well-being.",
        "Ethical Dilemma Discussion" => "Discuss the ethical implications of using AI in medical diagnostics.",
        "Political Perspective" => "Explain the significance of voting in democratic societies.",
        "Economic Impact Analysis" => "Analyze the economic impact of remote work on urban and rural areas.",
        "Social Media Strategy" => "Suggest a social media strategy for a small business launching a new product.",
        "Brand Identity Development" => "Help define the brand identity for a tech startup focused on sustainable innovations.",
        
        // Language and creative writing
        "Haiku Generator" => "Write a haiku about a peaceful morning by the sea.",
        "Historical Fiction Plot" => "Create a plot idea for a historical fiction novel set during the Renaissance.",
        "Product Tagline Generator" => "Generate a catchy tagline for a premium coffee brand.",
        "Slogan Ideas" => "Create three slogan ideas for a new eco-friendly cleaning product line.",
        "Letter Writing" => "Write a heartfelt thank-you letter to a teacher.",
        "Metaphor Explanation" => "Explain the metaphor: 'Life is a journey.'",
        "Descriptive Writing" => "Describe a bustling city street scene in the rain.",
        "Monologue" => "Write an inner monologue for a character preparing for a major life change.",
        "Imagery Exercise" => "Paint a vivid picture of a calm, quiet lake at sunrise.",
        "Song Lyric Ideas" => "Generate a few lines of lyrics for a song about resilience and hope.",
        
        // Specialized or industry-specific tasks
        "Healthcare FAQ" => "Answer common questions about managing diabetes.",
        "Real Estate Listing Description" => "Write a listing description for a cozy, two-bedroom apartment in a busy downtown area.",
        "Resume Summary" => "Craft a strong summary for a resume for a recent computer science graduate.",
        "Job Interview Question Prep" => "List common job interview questions for a marketing manager role.",
        "Legal Disclaimer" => "Write a legal disclaimer for a personal finance advice blog.",
        "Event Announcement" => "Write an announcement for a charity event raising funds for animal shelters.",
        "Investor Pitch" => "Summarize the key points for an investor pitch for a food delivery startup.",
        "Press Release" => "Create a press release announcing the launch of an AI-based educational app.",
        "Educational Outline" => "Develop an outline for a workshop on public speaking skills.",
        "Grant Proposal Summary" => "Summarize a grant proposal for funding a renewable energy project.",
        
        // Miscellaneous testing
        "Problem-Solving Tips" => "Provide five tips for improving problem-solving skills.",
        "Daily Affirmation" => "Write a positive affirmation to help someone start their day on a good note.",
        "Holiday Wishes" => "Create a warm holiday greeting for a customer base.",
        "Shopping List" => "Generate a shopping list for a barbecue party with 10 people.",
        "Life Hack" => "Share a useful life hack for staying organized.",
        "Memory Exercise" => "Suggest a memory exercise for improving focus.",
        "Simple Explanation" => "Explain how photosynthesis works in simple terms.",
        "Story Ending Ideas" => "Suggest three different endings for a mystery story involving a detective.",
        "Puns and Wordplay" => "Create a list of funny puns about cats.",
        "Quick Exercise" => "Suggest a quick 5-minute stretching routine for people working at a desk."
    );
    $model_list = aiomatic_get_all_models();
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-comparison-ajax', plugins_url('scripts/comparison.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-comparison-ajax', 'aiomatic_completition_ajax_object_comparison', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'add_comparison' => esc_html__('Add Comparison', 'aiomatic-automatic-ai-content-writer'),
        'valid_temp' => sprintf(esc_html__('Please enter a valid temperature value between %1$d and %2$d.', 'aiomatic-automatic-ai-content-writer'), 0, 2),
        'valid_topp' => sprintf(esc_html__('Please enter a valid top probability value between %1$d and %2$d.', 'aiomatic-automatic-ai-content-writer'), 0, 1),
        'valid_frequency' => sprintf(esc_html__('Please enter valid frequency penalty value between %1$d and %2$d.', 'aiomatic-automatic-ai-content-writer'), 0, 2),
        'valid_presense' => sprintf(esc_html__('Please enter valid presence penalty value between %1$d and %2$d.', 'aiomatic-automatic-ai-content-writer'), -2, 2)
	));
    wp_enqueue_style($name . 'comparison-front', plugins_url('styles/comparison.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    ?>
    <div class="aiomatic-comparison-default" style="display: none">
        <div class="aiomatic-comparison-item aiomatic-comparison-item-[ID]">
            <form action="" method="post" class="aiomatic-comparison-form">
                <?php wp_nonce_field('aiomatic_comparison_generator'); ?>
                <input type="hidden" name="action" value="aiomatic_comparison">
                <span class="aiomatic-comparison-close">&times;</span>

                <div class="aiomatic-mb-10">
                    <label for="aiomatic-comparison-prompt"><?php echo esc_html__('Prompt', 'aiomatic-automatic-ai-content-writer'); ?></label>
                    <select id="aiomatic-comparison-prompt" class="aiomatic-comparison-select-prompt">
                        <?php foreach($aiomatic_prompts as $key => $aiomatic_prompt): ?>
                            <option value="<?php echo esc_html($aiomatic_prompt); ?>"><?php echo esc_html($key); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="aiomatic-mb-10">
                    <label for="aiomatic-comparison-cprompt"><?php echo esc_html__('Custom Prompt', 'aiomatic-automatic-ai-content-writer'); ?></label>
                    <textarea id="aiomatic-comparison-cprompt" rows="8" placeholder="<?php echo esc_html__('Enter your AI prompt here', 'aiomatic-automatic-ai-content-writer'); ?>" name="prompt"></textarea>
                    <span class="character-counter">0/128000</span>
                </div>

                <div class="aiomatic-mb-10">
                    <label for="aiomatic-comparison-model"><?php echo esc_html__('Model', 'aiomatic-automatic-ai-content-writer'); ?></label>
                    <select id="aiomatic-comparison-model" name="model">
                        <?php foreach ($model_list as $model): ?>
                            <option value="<?php echo esc_attr($model); ?>"><?php echo esc_html($model) . esc_html(aiomatic_get_model_provider($model)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="advanced-settings-toggle"><?php echo esc_html__('Show Advanced Settings', 'aiomatic-automatic-ai-content-writer'); ?></div>
                <div class="advanced-settings">
                    <?php $conx = 0;
                    foreach ($aiomatic_defaults as $key => $aiomatic_default): 
                        $conx++;?>
                        <div class="aiomatic-mb-10">
                            <label for="aiomatic-comparison-settings<?php echo esc_html($conx);?>"><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></label>
                            <input id="aiomatic-comparison-settings<?php echo esc_html($conx);?>" type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_html($aiomatic_default); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="aiomatic-mb-10">
                    <button class="button button-primary aiomatic-comparison-submit"><?php echo esc_html__('Generate', 'aiomatic-automatic-ai-content-writer'); ?></button>
                    <span class="aiomatic-comparison-space" style="display: none">&nbsp;&nbsp;</span>
                    <button style="display: none" type="button" class="button button-link-delete aiomatic-comparison-cancel"><?php echo esc_html__('Cancel', 'aiomatic-automatic-ai-content-writer'); ?></button>
                </div>

                <div class="aiomatic-mb-10">
                    <label for="aiomatic-comparison-out"><?php echo esc_html__('Output', 'aiomatic-automatic-ai-content-writer'); ?></label>
                    <textarea id="aiomatic-comparison-out" rows="8" placeholder="<?php echo esc_html__('See the AI output here', 'aiomatic-automatic-ai-content-writer'); ?>" class="aiomatic-comparison-output"></textarea>
                </div>
                <div class="aiomatic-mb-10 aiomatic-comparison-height">
                    <label for="aiomatic-comparison-token"><?php echo esc_html__('Tokens','aiomatic-automatic-ai-content-writer')?>:</label>
                    <div id="aiomatic-comparison-token" class="aiomatic-comparison-tokens"></div>
                </div>
                <div class="aiomatic-mb-10 aiomatic-comparison-height">
                    <label for="aiomatic-comparison-cost"><?php echo esc_html__('Cost','aiomatic-automatic-ai-content-writer')?>:</label>
                    <div id="aiomatic-comparison-cost" class="aiomatic-comparison-cost"></div>
                </div>
                <div class="aiomatic-mb-10 aiomatic-comparison-height">
                    <label for="aiomatic-comparison-duration"><?php echo esc_html__('Duration','aiomatic-automatic-ai-content-writer')?>:</label>
                    <div id="aiomatic-comparison-duration" class="aiomatic-comparison-duration"></div>
                </div>

                <div class="aiomatic-mb-10 aiomatic-comparison-height">
                    <label for="aiomatic-comparison-words"><?php echo esc_html__('Words','aiomatic-automatic-ai-content-writer')?>:</label>
                    <div id="aiomatic-comparison-words" class="aiomatic-comparison-words"></div>
                </div>
            </form>
        </div>
    </div>
    <div class="comparison_tool">
    </div>
<?php
    $output = ob_get_clean();
    return $output;
}

add_shortcode("aiomatic-replicate-image", "aiomatic_replicate_image");
function aiomatic_replicate_image($atts, $cont, $tagx)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $is_elementor = false;
    $seed_expre = isset( $atts['seed_expre'] )? esc_attr($atts['seed_expre']) : '';
    $static_content = isset( $atts['static_content'] )? esc_attr($atts['static_content']) : '';
    $copy_locally = isset( $atts['copy_locally'] )? esc_attr($atts['copy_locally']) : '';
    $image_size = isset( $atts['image_size'] )? esc_attr($atts['image_size']) : '';
    $cache_seconds = isset( $atts['cache_seconds'] )? intval(esc_attr($atts['cache_seconds'])) : 2592000;
    $post = $GLOBALS['post'];
    if(empty($seed_expre))
    {
        $exc = get_the_excerpt();
        $exc = trim(strip_tags($exc));
        $cnt = get_the_content();
        $cnt = trim(strip_tags($cnt));
        $cnt = strip_shortcodes($cnt);
        if($cnt != false && !empty($cnt))
        {
            $seed_expre = aiomatic_substr($cnt, 0, 200);
        }
        elseif(!empty($exc) && $exc != false)
        {
            $seed_expre = $exc;
        }
        else
        {
            $seed_expre = get_the_title();
            $seed_expre = trim(strip_tags($seed_expre));
            if($seed_expre == '')
            {
                return '';
            }
        }
    }
    else
    {
        if(isset($post->ID))
        {
            if(aiomatic_check_is_elementor($post->ID))
            {
                $is_elementor = true;
            }
            $post_link = get_permalink($post->ID);
            $blog_title       = html_entity_decode(get_bloginfo('title'));
            $author_obj       = get_user_by('id', $post->post_author);
            if($author_obj !== false)
            {
                $user_name        = $author_obj->user_nicename;
            }
            $final_content = $post->post_content;
            $post_title    = $post->post_title;
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
            $post_excerpt = $post->post_excerpt;
            $postID = $post->ID;
        }
        else
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
        }
        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
        if (filter_var($seed_expre, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($seed_expre, '.txt'))
        {
            $txt_content = aiomatic_get_web_page($seed_expre);
            if ($txt_content !== FALSE) 
            {
                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                $txt_content = array_filter($txt_content);
                if(count($txt_content) > 0)
                {
                    $txt_content = $txt_content[array_rand($txt_content)];
                    if(trim($txt_content) != '') 
                    {
                        $seed_expre = $txt_content;
                        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    }
                }
            }
        }
    }
    $md5v = md5($seed_expre . $image_size);
    $local_now = false;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        if(isset($post->ID) && $static_content == 'on')
        {
            $tranzi = false;
        }
        else
        {
            $tranzi = get_transient('aiomatic_replicate_image_transient' . $md5v);
        }
        if($tranzi === false)
        {
            if (!isset($aiomatic_Main_Settings['replicate_app_id']) || trim($aiomatic_Main_Settings['replicate_app_id']) == '') {
                aiomatic_log_to_file('You need to add a Replicate API key in plugin settings for this shortcode to work.');
                set_transient('aiomatic_replicate_image_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            $tranzi = '';
            if(strlen($seed_expre) > 2000)
            {
                $seed_expre = aiomatic_substr($seed_expre, 0, 2000);
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
            $aierror = '';
            $get_img = aiomatic_generate_replicate_image($seed_expre, $width, $height, 'shortcodeReplicateImage', true, $aierror);
            if($get_img !== false)
            {
                $tranzi = $get_img;
                if(!empty($tranzi))
                {
                    if($copy_locally == 'on' || $copy_locally == 'wasabi' || $copy_locally == 'generics3' || $copy_locally == 'amazon' || $copy_locally == 'digital')
                    {
                        $attached_id = '';
                        $localpath = aiomatic_copy_image_locally('' . $tranzi, $attached_id, $copy_locally, false, $seed_expre);
                        if($localpath !== false)
                        {
                            $tranzi = $localpath[0];
                            $local_now = true;
                        }
                    }
                    if(!isset($post->ID) || $static_content != 'on')
                    {
                        set_transient('aiomatic_replicate_image_transient' . $md5v, $tranzi, $cache_seconds);
                    }
                    else
                    {
                        $shortcode_reconstruction = '#\[\s*' . preg_quote($tagx) . '\s*';
                        foreach($atts as $atx => $vatx)
                        {
                            $shortcode_reconstruction .= ' ' . preg_quote($atx) . '\s*=\s*[\'"]?' . preg_quote($vatx) . '[\'"]?';
                        }
                        $shortcode_reconstruction .= '\s*\]#i';
                        preg_match_all($shortcode_reconstruction, $post->post_content, $initmatches);
                        if(isset($initmatches[0][0]) && $initmatches[0][0] != '')
                        {
                            if($local_now == true)
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            else
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            wp_update_post($post);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                        }
                        else
                        {
                            preg_match_all('#\[aiomatic-replicate-image([^\]]*?)\]#i', $post->post_content, $zamatches);
                            if(isset($zamatches[0][0]) && $zamatches[0][0] != '')
                            {
                                if($local_now == true)
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                else
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                wp_update_post($post);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                            }
                            else
                            {
                                set_transient('aiomatic_replicate_image_transient' . $md5v, $tranzi, $cache_seconds);
                            }
                        }
                        if($is_elementor)
                        {
                            $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                            if(!empty($elementor_data))
                            {
                                $elementor_json = json_decode($elementor_data);
                                if(!empty($elementor_json))
                                {
                                    $changemade = false;
                                    for($i = 0; $i < count($elementor_json); $i++)
                                    {
                                        if($elementor_json[$i]->elType == 'section' || $elementor_json[$i]->elType == 'column')
                                        {
                                            for($j = 0; $j < count($elementor_json[$i]->elements); $j++)
                                            {
                                                if($elementor_json[$i]->elements[$j]->elType == 'section' || $elementor_json[$i]->elements[$j]->elType == 'column')
                                                {
                                                    for($k = 0; $k < count($elementor_json[$i]->elements[$j]->elements); $k++)
                                                    {
                                                        if($elementor_json[$i]->elements[$j]->elements[$k]->elType == 'widget' && $elementor_json[$i]->elements[$j]->elements[$k]->widgetType == 'shortcode')
                                                        {
                                                            if(isset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode))
                                                            {
                                                                $sc = $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode;
                                                                if($local_now == true)
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                else
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                if($sc != $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode)
                                                                {
                                                                    unset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode);
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->settings->html = $sc;
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->widgetType = 'html';
                                                                    $changemade = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if($elementor_json[$i]->elements[$j]->elType == 'widget' && $elementor_json[$i]->elements[$j]->widgetType == 'shortcode')
                                                    {
                                                        if(isset($elementor_json[$i]->elements[$j]->settings->shortcode))
                                                        {
                                                            $sc = $elementor_json[$i]->elements[$j]->settings->shortcode;
                                                            if($local_now == true)
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            else
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            if($sc != $elementor_json[$i]->elements[$j]->settings->shortcode)
                                                            {
                                                                unset($elementor_json[$i]->elements[$j]->settings->shortcode);
                                                                $elementor_json[$i]->elements[$j]->settings->html = $sc;
                                                                $elementor_json[$i]->elements[$j]->widgetType = 'html';
                                                                $changemade = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if($elementor_json[$i]->elType == 'widget' && $elementor_json[$i]->widgetType == 'shortcode')
                                            {
                                                if(isset($elementor_json[$i]->settings->shortcode))
                                                {
                                                    $sc = $elementor_json[$i]->settings->shortcode;
                                                    if($local_now == true)
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    else
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-replicate-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    if($sc != $elementor_json[$i]->settings->shortcode)
                                                    {
                                                        unset($elementor_json[$i]->settings->shortcode);
                                                        $elementor_json[$i]->settings->html = $sc;
                                                        $elementor_json[$i]->widgetType = 'html';
                                                        $changemade = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($changemade == true)
                                    {
                                        $elementor_data_new = wp_json_encode($elementor_json);
                                        $elementor_data_new = trim($elementor_data_new, '"');
                                        if(!empty($elementor_data_new))
                                        {
                                            update_post_meta($post->ID, '_elementor_data', $elementor_data_new);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                aiomatic_log_to_file('Failed to generate Replicate image: ' . $aierror);
                $get_img = '';
            }
        }
    }
    if(!empty($tranzi))
    {
        if($local_now == true)
        {
            return '<img src="' . $tranzi . '">';
        }
        else
        {
            return '<img src="' . $tranzi . '">';
        }
    }
    return '';
}

add_shortcode("aiomatic-google-image", "aiomatic_google_image");
function aiomatic_google_image($atts, $cont, $tagx)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $is_elementor = false;
    $seed_expre = isset( $atts['seed_expre'] )? esc_attr($atts['seed_expre']) : '';
    $model = isset( $atts['model'] )? esc_attr($atts['model']) : AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL;
    $static_content = isset( $atts['static_content'] )? esc_attr($atts['static_content']) : '';
    $copy_locally = isset( $atts['copy_locally'] )? esc_attr($atts['copy_locally']) : '';
    $cache_seconds = isset( $atts['cache_seconds'] )? intval(esc_attr($atts['cache_seconds'])) : 2592000;
    $post = $GLOBALS['post'];
    if(!in_array($model, AIOMATIC_GOOGLE_IMAGE_MODELS))
    {
        $model = AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL;
    }
    if(empty($seed_expre))
    {
        $exc = get_the_excerpt();
        $exc = trim(strip_tags($exc));
        $cnt = get_the_content();
        $cnt = trim(strip_tags($cnt));
        $cnt = strip_shortcodes($cnt);
        if($cnt != false && !empty($cnt))
        {
            $seed_expre = aiomatic_substr($cnt, 0, 200);
        }
        elseif(!empty($exc) && $exc != false)
        {
            $seed_expre = $exc;
        }
        else
        {
            $seed_expre = get_the_title();
            $seed_expre = trim(strip_tags($seed_expre));
            if($seed_expre == '')
            {
                return '';
            }
        }
    }
    else
    {
        if(isset($post->ID))
        {
            if(aiomatic_check_is_elementor($post->ID))
            {
                $is_elementor = true;
            }
            $post_link = get_permalink($post->ID);
            $blog_title       = html_entity_decode(get_bloginfo('title'));
            $author_obj       = get_user_by('id', $post->post_author);
            if($author_obj !== false)
            {
                $user_name        = $author_obj->user_nicename;
            }
            $final_content = $post->post_content;
            $post_title    = $post->post_title;
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
            $post_excerpt = $post->post_excerpt;
            $postID = $post->ID;
        }
        else
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
        }
        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
        if (filter_var($seed_expre, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($seed_expre, '.txt'))
        {
            $txt_content = aiomatic_get_web_page($seed_expre);
            if ($txt_content !== FALSE) 
            {
                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                $txt_content = array_filter($txt_content);
                if(count($txt_content) > 0)
                {
                    $txt_content = $txt_content[array_rand($txt_content)];
                    if(trim($txt_content) != '') 
                    {
                        $seed_expre = $txt_content;
                        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    }
                }
            }
        }
    }
    $md5v = md5($seed_expre);
    $local_now = false;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        if(isset($post->ID) && $static_content == 'on')
        {
            $tranzi = false;
        }
        else
        {
            $tranzi = get_transient('aiomatic_google_image_transient' . $md5v);
        }
        if($tranzi === false)
        {
            if (!isset($aiomatic_Main_Settings['app_id_google']) || trim($aiomatic_Main_Settings['app_id_google']) == '') {
                aiomatic_log_to_file('You need to add a Google API key in plugin settings for this shortcode to work.');
                set_transient('aiomatic_google_image_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            $tranzi = '';
            if(strlen($seed_expre) > 2000)
            {
                $seed_expre = aiomatic_substr($seed_expre, 0, 2000);
            }

            $aierror = '';
            $get_img = aiomatic_generate_image_google($seed_expre, $model, 'shortcodeGoogleImage', false, $aierror, true);
            if(is_array($get_img) && isset($get_img[1]))
            {
                $get_img = $get_img[1];
            }
            if($get_img !== false)
            {
                $tranzi = $get_img;
                if(!empty($tranzi))
                {
                    if($copy_locally == 'on' || $copy_locally == 'wasabi' || $copy_locally == 'generics3' || $copy_locally == 'amazon' || $copy_locally == 'digital')
                    {
                        $attached_id = '';
                        $localpath = aiomatic_copy_image_locally('' . $tranzi, $attached_id, $copy_locally, false, $seed_expre);
                        if($localpath !== false)
                        {
                            $tranzi = $localpath[0];
                            $local_now = true;
                        }
                    }
                    if(!isset($post->ID) || $static_content != 'on')
                    {
                        set_transient('aiomatic_google_image_transient' . $md5v, $tranzi, $cache_seconds);
                    }
                    else
                    {
                        $shortcode_reconstruction = '#\[\s*' . preg_quote($tagx) . '\s*';
                        foreach($atts as $atx => $vatx)
                        {
                            $shortcode_reconstruction .= ' ' . preg_quote($atx) . '\s*=\s*[\'"]?' . preg_quote($vatx) . '[\'"]?';
                        }
                        $shortcode_reconstruction .= '\s*\]#i';
                        preg_match_all($shortcode_reconstruction, $post->post_content, $initmatches);
                        if(isset($initmatches[0][0]) && $initmatches[0][0] != '')
                        {
                            if($local_now == true)
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            else
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            wp_update_post($post);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                        }
                        else
                        {
                            preg_match_all('#\[aiomatic-google-image([^\]]*?)\]#i', $post->post_content, $zamatches);
                            if(isset($zamatches[0][0]) && $zamatches[0][0] != '')
                            {
                                if($local_now == true)
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                else
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                wp_update_post($post);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                            }
                            else
                            {
                                set_transient('aiomatic_google_image_transient' . $md5v, $tranzi, $cache_seconds);
                            }
                        }
                        if($is_elementor)
                        {
                            $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                            if(!empty($elementor_data))
                            {
                                $elementor_json = json_decode($elementor_data);
                                if(!empty($elementor_json))
                                {
                                    $changemade = false;
                                    for($i = 0; $i < count($elementor_json); $i++)
                                    {
                                        if($elementor_json[$i]->elType == 'section' || $elementor_json[$i]->elType == 'column')
                                        {
                                            for($j = 0; $j < count($elementor_json[$i]->elements); $j++)
                                            {
                                                if($elementor_json[$i]->elements[$j]->elType == 'section' || $elementor_json[$i]->elements[$j]->elType == 'column')
                                                {
                                                    for($k = 0; $k < count($elementor_json[$i]->elements[$j]->elements); $k++)
                                                    {
                                                        if($elementor_json[$i]->elements[$j]->elements[$k]->elType == 'widget' && $elementor_json[$i]->elements[$j]->elements[$k]->widgetType == 'shortcode')
                                                        {
                                                            if(isset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode))
                                                            {
                                                                $sc = $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode;
                                                                if($local_now == true)
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                else
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                if($sc != $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode)
                                                                {
                                                                    unset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode);
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->settings->html = $sc;
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->widgetType = 'html';
                                                                    $changemade = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if($elementor_json[$i]->elements[$j]->elType == 'widget' && $elementor_json[$i]->elements[$j]->widgetType == 'shortcode')
                                                    {
                                                        if(isset($elementor_json[$i]->elements[$j]->settings->shortcode))
                                                        {
                                                            $sc = $elementor_json[$i]->elements[$j]->settings->shortcode;
                                                            if($local_now == true)
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            else
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            if($sc != $elementor_json[$i]->elements[$j]->settings->shortcode)
                                                            {
                                                                unset($elementor_json[$i]->elements[$j]->settings->shortcode);
                                                                $elementor_json[$i]->elements[$j]->settings->html = $sc;
                                                                $elementor_json[$i]->elements[$j]->widgetType = 'html';
                                                                $changemade = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if($elementor_json[$i]->elType == 'widget' && $elementor_json[$i]->widgetType == 'shortcode')
                                            {
                                                if(isset($elementor_json[$i]->settings->shortcode))
                                                {
                                                    $sc = $elementor_json[$i]->settings->shortcode;
                                                    if($local_now == true)
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    else
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-google-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    if($sc != $elementor_json[$i]->settings->shortcode)
                                                    {
                                                        unset($elementor_json[$i]->settings->shortcode);
                                                        $elementor_json[$i]->settings->html = $sc;
                                                        $elementor_json[$i]->widgetType = 'html';
                                                        $changemade = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($changemade == true)
                                    {
                                        $elementor_data_new = wp_json_encode($elementor_json);
                                        $elementor_data_new = trim($elementor_data_new, '"');
                                        if(!empty($elementor_data_new))
                                        {
                                            update_post_meta($post->ID, '_elementor_data', $elementor_data_new);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                aiomatic_log_to_file('Failed to generate Google image: ' . $aierror);
                $get_img = '';
            }
        }
    }
    if(!empty($tranzi))
    {
        if(is_array($tranzi) && isset($tranzi[1]))
        {
            $tranzi = $tranzi[1];
        }
        if($local_now == true)
        {
            return '<img src="' . $tranzi . '">';
        }
        else
        {
            return '<img src="' . $tranzi . '">';
        }
    }
    return '';
}
add_shortcode("aiomatic-ideogram-image", "aiomatic_ideogram_image");
function aiomatic_ideogram_image($atts, $cont, $tagx)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $is_elementor = false;
    $seed_expre = isset( $atts['seed_expre'] )? esc_attr($atts['seed_expre']) : '';
    $static_content = isset( $atts['static_content'] )? esc_attr($atts['static_content']) : '';
    $copy_locally = isset( $atts['copy_locally'] )? esc_attr($atts['copy_locally']) : '';
    $image_size = isset( $atts['image_size'] )? esc_attr($atts['image_size']) : '';
    $cache_seconds = isset( $atts['cache_seconds'] )? intval(esc_attr($atts['cache_seconds'])) : 2592000;
    $post = $GLOBALS['post'];
    if(empty($seed_expre))
    {
        $exc = get_the_excerpt();
        $exc = trim(strip_tags($exc));
        $cnt = get_the_content();
        $cnt = trim(strip_tags($cnt));
        $cnt = strip_shortcodes($cnt);
        if($cnt != false && !empty($cnt))
        {
            $seed_expre = aiomatic_substr($cnt, 0, 200);
        }
        elseif(!empty($exc) && $exc != false)
        {
            $seed_expre = $exc;
        }
        else
        {
            $seed_expre = get_the_title();
            $seed_expre = trim(strip_tags($seed_expre));
            if($seed_expre == '')
            {
                return '';
            }
        }
    }
    else
    {
        if(isset($post->ID))
        {
            if(aiomatic_check_is_elementor($post->ID))
            {
                $is_elementor = true;
            }
            $post_link = get_permalink($post->ID);
            $blog_title       = html_entity_decode(get_bloginfo('title'));
            $author_obj       = get_user_by('id', $post->post_author);
            if($author_obj !== false)
            {
                $user_name        = $author_obj->user_nicename;
            }
            $final_content = $post->post_content;
            $post_title    = $post->post_title;
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
            $post_excerpt = $post->post_excerpt;
            $postID = $post->ID;
        }
        else
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
        }
        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
        if (filter_var($seed_expre, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($seed_expre, '.txt'))
        {
            $txt_content = aiomatic_get_web_page($seed_expre);
            if ($txt_content !== FALSE) 
            {
                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                $txt_content = array_filter($txt_content);
                if(count($txt_content) > 0)
                {
                    $txt_content = $txt_content[array_rand($txt_content)];
                    if(trim($txt_content) != '') 
                    {
                        $seed_expre = $txt_content;
                        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    }
                }
            }
        }
    }
    $md5v = md5($seed_expre . $image_size);
    $local_now = false;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') 
    {
        if(isset($post->ID) && $static_content == 'on')
        {
            $tranzi = false;
        }
        else
        {
            $tranzi = get_transient('aiomatic_ideogram_image_transient' . $md5v);
        }
        if($tranzi === false)
        {
            if (!isset($aiomatic_Main_Settings['ideogram_app_id']) || trim($aiomatic_Main_Settings['ideogram_app_id']) == '') {
                aiomatic_log_to_file('You need to add a Ideogram API key in plugin settings for this shortcode to work.');
                set_transient('aiomatic_ideogram_image_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            $tranzi = '';
            if(strlen($seed_expre) > 2000)
            {
                $seed_expre = aiomatic_substr($seed_expre, 0, 2000);
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
            $aierror = '';
            $get_img = aiomatic_generate_ideogram_image($seed_expre, $width, $height, 'shortcodeIdeogramImage', true, $aierror);
            if($get_img !== false)
            {
                $tranzi = $get_img;
                if(!empty($tranzi))
                {
                    if($copy_locally == 'on' || $copy_locally == 'wasabi' || $copy_locally == 'generics3' || $copy_locally == 'amazon' || $copy_locally == 'digital')
                    {
                        $attached_id = '';
                        $localpath = aiomatic_copy_image_locally('' . $tranzi, $attached_id, $copy_locally, false, $seed_expre);
                        if($localpath !== false)
                        {
                            $tranzi = $localpath[0];
                            $local_now = true;
                        }
                    }
                    if(!isset($post->ID) || $static_content != 'on')
                    {
                        set_transient('aiomatic_ideogram_image_transient' . $md5v, $tranzi, $cache_seconds);
                    }
                    else
                    {
                        $shortcode_reconstruction = '#\[\s*' . preg_quote($tagx) . '\s*';
                        foreach($atts as $atx => $vatx)
                        {
                            $shortcode_reconstruction .= ' ' . preg_quote($atx) . '\s*=\s*[\'"]?' . preg_quote($vatx) . '[\'"]?';
                        }
                        $shortcode_reconstruction .= '\s*\]#i';
                        preg_match_all($shortcode_reconstruction, $post->post_content, $initmatches);
                        if(isset($initmatches[0][0]) && $initmatches[0][0] != '')
                        {
                            if($local_now == true)
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            else
                            {
                                $post->post_content = preg_replace($shortcode_reconstruction, '<img src="' . $tranzi . '">', $post->post_content);
                            }
                            remove_filter('content_save_pre', 'wp_filter_post_kses');
                            remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            remove_filter('title_save_pre', 'wp_filter_kses');
                            wp_update_post($post);
                            add_filter('content_save_pre', 'wp_filter_post_kses');
                            add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                            add_filter('title_save_pre', 'wp_filter_kses');
                        }
                        else
                        {
                            preg_match_all('#\[aiomatic-ideogram-image([^\]]*?)\]#i', $post->post_content, $zamatches);
                            if(isset($zamatches[0][0]) && $zamatches[0][0] != '')
                            {
                                if($local_now == true)
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                else
                                {
                                    $post->post_content = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $post->post_content);
                                }
                                remove_filter('content_save_pre', 'wp_filter_post_kses');
                                remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                remove_filter('title_save_pre', 'wp_filter_kses');
                                wp_update_post($post);
                                add_filter('content_save_pre', 'wp_filter_post_kses');
                                add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                                add_filter('title_save_pre', 'wp_filter_kses');
                            }
                            else
                            {
                                set_transient('aiomatic_ideogram_image_transient' . $md5v, $tranzi, $cache_seconds);
                            }
                        }
                        if($is_elementor)
                        {
                            $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                            if(!empty($elementor_data))
                            {
                                $elementor_json = json_decode($elementor_data);
                                if(!empty($elementor_json))
                                {
                                    $changemade = false;
                                    for($i = 0; $i < count($elementor_json); $i++)
                                    {
                                        if($elementor_json[$i]->elType == 'section' || $elementor_json[$i]->elType == 'column')
                                        {
                                            for($j = 0; $j < count($elementor_json[$i]->elements); $j++)
                                            {
                                                if($elementor_json[$i]->elements[$j]->elType == 'section' || $elementor_json[$i]->elements[$j]->elType == 'column')
                                                {
                                                    for($k = 0; $k < count($elementor_json[$i]->elements[$j]->elements); $k++)
                                                    {
                                                        if($elementor_json[$i]->elements[$j]->elements[$k]->elType == 'widget' && $elementor_json[$i]->elements[$j]->elements[$k]->widgetType == 'shortcode')
                                                        {
                                                            if(isset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode))
                                                            {
                                                                $sc = $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode;
                                                                if($local_now == true)
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                else
                                                                {
                                                                    $sc = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                                }
                                                                if($sc != $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode)
                                                                {
                                                                    unset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode);
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->settings->html = $sc;
                                                                    $elementor_json[$i]->elements[$j]->elements[$k]->widgetType = 'html';
                                                                    $changemade = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if($elementor_json[$i]->elements[$j]->elType == 'widget' && $elementor_json[$i]->elements[$j]->widgetType == 'shortcode')
                                                    {
                                                        if(isset($elementor_json[$i]->elements[$j]->settings->shortcode))
                                                        {
                                                            $sc = $elementor_json[$i]->elements[$j]->settings->shortcode;
                                                            if($local_now == true)
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            else
                                                            {
                                                                $sc = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                            }
                                                            if($sc != $elementor_json[$i]->elements[$j]->settings->shortcode)
                                                            {
                                                                unset($elementor_json[$i]->elements[$j]->settings->shortcode);
                                                                $elementor_json[$i]->elements[$j]->settings->html = $sc;
                                                                $elementor_json[$i]->elements[$j]->widgetType = 'html';
                                                                $changemade = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if($elementor_json[$i]->elType == 'widget' && $elementor_json[$i]->widgetType == 'shortcode')
                                            {
                                                if(isset($elementor_json[$i]->settings->shortcode))
                                                {
                                                    $sc = $elementor_json[$i]->settings->shortcode;
                                                    if($local_now == true)
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    else
                                                    {
                                                        $sc = preg_replace('#\[aiomatic-ideogram-image([^\]]*?)\]#i', '<img src="' . $tranzi . '">', $sc);
                                                    }
                                                    if($sc != $elementor_json[$i]->settings->shortcode)
                                                    {
                                                        unset($elementor_json[$i]->settings->shortcode);
                                                        $elementor_json[$i]->settings->html = $sc;
                                                        $elementor_json[$i]->widgetType = 'html';
                                                        $changemade = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if($changemade == true)
                                    {
                                        $elementor_data_new = wp_json_encode($elementor_json);
                                        $elementor_data_new = trim($elementor_data_new, '"');
                                        if(!empty($elementor_data_new))
                                        {
                                            update_post_meta($post->ID, '_elementor_data', $elementor_data_new);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                aiomatic_log_to_file('Failed to generate Ideogram image: ' . $aierror);
                $get_img = '';
            }
        }
    }
    if(!empty($tranzi))
    {
        if($local_now == true)
        {
            return '<img src="' . $tranzi . '">';
        }
        else
        {
            return '<img src="' . $tranzi . '">';
        }
    }
    return '';
}

add_shortcode("aiomatic-article", "aiomatic_article");
function aiomatic_article($atts, $cont, $tagx)
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $is_elementor = false;
    $post_link = '';
    $post_title = '';
    $blog_title = html_entity_decode(get_bloginfo('title'));
    $post_excerpt = '';
    $final_content = '';
    $user_name = '';
    $featured_image = '';
    $post_cats = '';
    $post_tagz = '';
    $postID = '';
    $id = '';
    $added_img_list = array();
    $raw_img_list = array();
    $full_result_list = array();
    $added_images = 0;
    $heading_results = array();
    $seed_expre = isset( $atts['seed_expre'] )? esc_attr($atts['seed_expre']) : '';
    $headings = isset( $atts['headings'] )? esc_attr($atts['headings']) : '';
    $images = isset( $atts['images'] )? esc_attr($atts['images']) : '';
    $videos = isset( $atts['videos'] )? esc_attr($atts['videos']) : '';
    $static_content = isset( $atts['static_content'] )? esc_attr($atts['static_content']) : '';
    $temperature = isset( $atts['temperature'] )? esc_attr($atts['temperature']) : '1';
    $top_p = isset( $atts['top_p'] )? esc_attr($atts['top_p']) : '1';
    $presence_penalty = isset( $atts['presence_penalty'] )? esc_attr($atts['presence_penalty']) : '0';
    $frequency_penalty = isset( $atts['frequency_penalty'] )? esc_attr($atts['frequency_penalty']) : '0';
    $min_char = isset( $atts['min_char'] )? esc_attr($atts['min_char']) : '';
    $max_tokens = isset( $atts['max_tokens'] )? esc_attr($atts['max_tokens']) : 0;
    $max_seed_tokens = isset( $atts['max_seed_tokens'] )? esc_attr($atts['max_seed_tokens']) : AIOMATIC_DEFAULT_COMPLETION_TOKENS;
    $max_continue_tokens = isset( $atts['max_continue_tokens'] )? esc_attr($atts['max_continue_tokens']) : '500';
    $model = isset( $atts['model'] )? esc_attr(trim($atts['model'])) : aiomatic_get_default_model_name($aiomatic_Main_Settings);
    $headings_model = isset( $atts['model'] )? esc_attr(trim($atts['model'])) : aiomatic_get_default_model_name($aiomatic_Main_Settings);
    $headings_assistant_id = isset( $atts['assistant_id'] )? esc_attr(trim($atts['assistant_id'])) : '';
    $assistant_id = isset( $atts['assistant_id'] )? esc_attr(trim($atts['assistant_id'])) : '';
    $headings_seed_expre = isset( $atts['headings_seed_expre'] )? esc_attr(trim($atts['headings_seed_expre'])) : 'Write %%needed_heading_count%% PAA related questions, each on a new line, for the title: %%post_title%%';
    $cache_seconds = isset( $atts['cache_seconds'] )? intval(esc_attr($atts['cache_seconds'])) : 2592000;
    $no_internet = isset( $atts['no_internet'] )? esc_attr(trim($atts['no_internet'])) : '0';
    if($no_internet == '1' || $no_internet == 'on' || $no_internet == 'yes')
    {
        $no_internet = true;
    }
    else
    {
        $no_internet = false;
    }
    $all_models = aiomatic_get_all_models(true);
    if(!in_array($model, $all_models))
    {
        $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
    }
    if(!in_array($headings_model, $all_models))
    {
        $headings_model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
    }
    $max_tokens = intval($max_tokens);
    if($max_tokens <= 0)
    {
        $max_tokens = aiomatic_get_max_tokens($model);
    }
    if($max_tokens > aiomatic_get_max_tokens($model))
    {
        $max_tokens = aiomatic_get_max_tokens($model);
    }
    $max_seed_tokens = intval($max_seed_tokens);
    $max_continue_tokens = intval($max_continue_tokens);
    $post = $GLOBALS['post'];
    if(empty($seed_expre))
    {
        $exc = get_the_excerpt();
        $exc = trim(strip_tags($exc));
        $cnt = get_the_content();
        $cnt = trim(strip_tags($cnt));
        $cnt = strip_shortcodes($cnt);
        if($cnt != false && !empty($cnt))
        {
            $id = $cnt;
        }
        elseif(!empty($exc) && $exc != false)
        {
            $id = $exc;
        }
        else
        {
            $id = get_the_title();
            $id = trim(strip_tags($id));
            if($id == '')
            {
                return '';
            }
        }
    }
    else
    {
        if(isset($post->ID))
        {
            $post_link = get_permalink($post->ID);
            if(aiomatic_check_is_elementor($post->ID))
            {
                $is_elementor = true;
            }
            $blog_title       = html_entity_decode(get_bloginfo('title'));
            $author_obj       = get_user_by('id', $post->post_author);
            if($author_obj !== false)
            {
                $user_name        = $author_obj->user_nicename;
            }
            $final_content = $post->post_content;
            $post_title    = $post->post_title;
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
            $post_excerpt = $post->post_excerpt;
            $postID = $post->ID;
            preg_match_all('{%%tax_([^%]+?)%%}', $seed_expre, $taxes);
            if(isset($taxes[1]))
            {
                foreach($taxes[1] as $zatax)
                {
                    $xterms = get_the_terms( $post->ID, $zatax );
                    if ( ! empty( $xterms ) && ! is_wp_error( $xterms ) ){
                        $xpost_cats = array();
                        foreach ( $xterms as $term ) {
                            $xpost_cats[] = $term->name;
                        }
                        $xtaxes = implode(',', $xpost_cats);
                        $seed_expre = str_replace('%%tax_' . $zatax . '%%', $xtaxes, $seed_expre);
                    }
                    else
                    {
                        $seed_expre = str_replace('%%tax_' . $zatax . '%%', '', $seed_expre);
                    }
                }
            }
            preg_match_all('{%%meta_([^%]+?)%%}', $seed_expre, $metas);
            if(isset($metas[1]))
            {
                foreach($metas[1] as $metasx)
                {
                    $xmetas = get_post_meta($post->ID, $metasx, true);
                    if ( ! empty( $xmetas ) ){
                        $seed_expre = str_replace('%%meta_' . $metasx . '%%', $xmetas, $seed_expre);
                    }
                    else
                    {
                        $seed_expre = str_replace('%%meta_' . $metasx . '%%', '', $seed_expre);
                    }
                }
            }
        }
        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
        if (filter_var($seed_expre, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($seed_expre, '.txt'))
        {
            $txt_content = aiomatic_get_web_page($seed_expre);
            if ($txt_content !== FALSE) 
            {
                $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                $txt_content = array_filter($txt_content);
                if(count($txt_content) > 0)
                {
                    $txt_content = $txt_content[array_rand($txt_content)];
                    if(trim($txt_content) != '') 
                    {
                        $seed_expre = $txt_content;
                        $seed_expre = aiomatic_replaceAIPostShortcodes($seed_expre, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    }
                }
            }
        }
        $id = $seed_expre;
    }
    $md5v = md5($id . $temperature . $top_p . $presence_penalty . $frequency_penalty . $min_char);
    if($temperature == '')
    {
        $temperature = 1;
    }
    else
    {
        $temperature = floatval($temperature);
    }
    if($top_p == '')
    {
        $top_p = 1;
    }
    else
    {
        $top_p = floatval($top_p);
    }
    if($frequency_penalty == '')
    {
        $frequency_penalty = 0;
    }
    else
    {
        $frequency_penalty = floatval($frequency_penalty);
    }
    if($presence_penalty == '')
    {
        $presence_penalty = 0;
    }
    else
    {
        $presence_penalty = floatval($presence_penalty);
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['aiomatic_enabled']) && $aiomatic_Main_Settings['aiomatic_enabled'] == 'on') {
        if(isset($post->ID) && $static_content == 'on')
        {
            $tranzi = false;
        }
        else
        {
            $tranzi = get_transient('aiomatic_article_transient' . $md5v);
        }
        $new_post_content = '';
        if($tranzi === false)
        {
            if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') {
                aiomatic_log_to_file('You need to add an API key in plugin settings for this shortcode to work.');
                set_transient('aiomatic_article_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            else
            {
                $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                $appids = array_filter($appids);
                $token = $appids[array_rand($appids)];
            }
            
            $aicontent = $id;
            if(empty($aicontent))
            {
                return '';
            }
            if(strlen($aicontent) > $max_seed_tokens * 4)
            {
                $aicontent = aiomatic_substr($aicontent, 0, (0-($max_seed_tokens * 4)));
            }
            $aicontent = trim($aicontent);
            $last_char = aiomatic_substr($aicontent, -1, null);
            if(!ctype_punct($last_char))
            {
                $aicontent .= '.';
            }
            $query_token_count = count(aiomatic_encode($aicontent));
            $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $aicontent, $query_token_count);
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
                    return '';
                }
                $query_token_count = count(aiomatic_encode($aicontent));
                $available_tokens = $max_tokens - $query_token_count;
            }
            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
            {
                $api_service = aiomatic_get_api_service($token, $model);
                aiomatic_log_to_file('Calling ' . $api_service . ' (' . $model . ')(' . $available_tokens . ') shortcode for text: ' . $aicontent);
            }
            $thread_id = '';
            $aierror = '';
            $finish_reason = '';
            $generated_text = aiomatic_generate_text($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'shortcodeContentArticle', 0, $finish_reason, $aierror, $no_internet, false, false, '', '', 'user', $assistant_id, $thread_id, '', 'disabled', '', false, false, array());
            if($generated_text === false)
            {
                aiomatic_log_to_file($aierror);
                set_transient('aiomatic_article_transient' . $md5v, 'not_working', intval($cache_seconds/10));
                return '';
            }
            else
            {
                $new_post_content = ucfirst(trim(nl2br(trim($generated_text))));
            }
            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
            {
                aiomatic_log_to_file('Successfully got API result for shortcode.');
            }
            if($min_char == '')
            {
                $min_char = 0;
            }
            else
            {
                $min_char = intval($min_char);
            }
            $cnt = 1;
            if(strlen($new_post_content) < $min_char)
            {
                if($headings != '' && is_numeric($headings))
                {
                    $heading_results = aiomatic_scrape_related_questions($id, $headings, $headings_model, $temperature, $top_p, $presence_penalty, $frequency_penalty, $max_tokens, $headings_seed_expre, $headings_assistant_id);
                }
            }
            $image_query = '';
            $heading_val = '';
            $temp_post = '';
            $ai_retry = false;
            $ai_continue_title = $post_title;
            $img_attr = '';
            $query_words = '';
            while(strlen(strip_tags($new_post_content)) < $min_char)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) < $cnt)
                {
                    break;
                }
                $query_words = '';
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
                    $temp_post = trim($new_post_content);
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
                        $aierror = '';
                        $finish_reason = '';
                        $generated_text = aiomatic_generate_text($token, $model, 'Write a People Also Asked question related to "' . $ai_continue_title . '"', aiomatic_get_max_tokens($model), $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'shortcodeHeadingArticle', 0, $finish_reason, $aierror, $no_internet, false, false, '', '', 'user', $assistant_id, $thread_id, '', 'disabled', '', false, false, array());
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
                $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $newaicontent, $query_token_count);
                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                {
                    $string_len = strlen($newaicontent);
                    $string_len = $string_len / 2;
                    $string_len = intval(0 - $string_len);
                    $newaicontent = aiomatic_substr($newaicontent, 0, $string_len);
                    $newaicontent = trim($newaicontent);
                    if(empty($newaicontent))
                    {
                        aiomatic_log_to_file('Empty API seed expression provided (after processing) ' . print_r($newaicontent, true));
                        break;
                    }
                    $query_token_count = count(aiomatic_encode($newaicontent));
                    $available_tokens = $max_tokens - $query_token_count;
                }
                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                    $api_service = aiomatic_get_api_service($token, $model);
                    aiomatic_log_to_file('Calling ' . $api_service . ' again (' . $cnt . ')(' . $available_tokens . ') from shortcode, to meet minimum character limit: ' . $min_char . ' - current char count: ' . strlen(strip_tags($new_post_content)));
                }
                $aiwriter = '';
                $aierror = '';
                $finish_reason = '';
                $generated_text = aiomatic_generate_text($token, $model, $newaicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'shortcodeContentArticle', 0, $finish_reason, $aierror, $no_internet, false, false, '', '', 'user', $assistant_id, $thread_id, '', 'disabled', '', false, false, array());
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
                if($images != '' && is_numeric($images) && $images > $added_images)
                {
                    if($image_query == '')
                    {
                        $image_query = $temp_post;
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
                            }
                            else
                            {
                                $title_ai_command = 'Extract a comma separated list of relevant keywords from the text: ' . trim(strip_tags($post_title));
                                if(strlen($title_ai_command) > $max_seed_tokens * 4)
                                {
                                    $title_ai_command = aiomatic_substr($title_ai_command, 0, (0 - ($max_seed_tokens * 4)));
                                }
                                $title_ai_command = trim($title_ai_command);
                                if(empty($title_ai_command))
                                {
                                    aiomatic_log_to_file('Empty API title seed expression provided(1)! ' . print_r($title_ai_command, true));
                                }
                                else
                                {
                                    $query_token_count = count(aiomatic_encode($title_ai_command));
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
                                        aiomatic_log_to_file('Calling ' . $api_service . ' (' . $kw_model . ')(' . $available_tokens . ') for title text: ' . $title_ai_command);
                                    }
                                    $aierror = '';
                                    $finish_reason = '';
                                    $generated_text = aiomatic_generate_text($token, $kw_model, $title_ai_command, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'shortcodeKeywordArticle', 0, $finish_reason, $aierror, $no_internet, false, false, '', '', 'user', $keyword_assistant_id, $thread_id, '', 'disabled', '', false, false, array());
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
                if($temp_get_img != '')
                {
                    $add_my_image = '<img class="ximage_class" src="' . $temp_get_img . '" alt="' . $query_words . '"><br/>';
                }
                if($heading_val == '')
                {
                    if($add_my_image == '')
                    {
                        $add_my_image = ' ';
                    }
                    $new_post_content .= $add_my_image . trim(nl2br($aiwriter));
                }
                else
                {
                    $new_post_content .= $add_my_image . $heading_val . ' ' . trim(nl2br($aiwriter)) . '</span>';
                }
                sleep(1);
                $cnt++;
            }
            if (isset($aiomatic_Main_Settings['swear_filter']) && $aiomatic_Main_Settings['swear_filter'] == 'on') 
            {
                require_once(dirname(__FILE__) . "/res/swear.php");
                $new_post_content = aiomatic_filterwords($new_post_content);
            }
            if ($videos == 'on') 
            {
                $image_query = $query_words;
                if($image_query == '')
                {
                    if($temp_post != '')
                    {
                        $image_query = $temp_post;
                    }
                    else
                    {
                        $image_query = $id;
                    }
                }
                $new_vid = aiomatic_get_video($image_query, '', array());
                if($new_vid !== false)
                {
                    $new_post_content .= $new_vid;
                }
            }
            if(!isset($post->ID) || $static_content != 'on')
            {
                set_transient('aiomatic_article_transient' . $md5v, $new_post_content, $cache_seconds);
                $tranzi = $new_post_content;
            }
            else
            {
                $shortcode_reconstruction = '#\[\s*' . preg_quote($tagx) . '\s*';
                foreach($atts as $atx => $vatx)
                {
                    $shortcode_reconstruction .= ' ' . preg_quote($atx) . '\s*=\s*[\'"]?' . preg_quote($vatx) . '[\'"]?';
                }
                $shortcode_reconstruction .= '\s*\]#i';
                preg_match_all($shortcode_reconstruction, $post->post_content, $initmatches);
                if(isset($initmatches[0][0]) && $initmatches[0][0] != '')
                {
                    $tranzi = '';
                    $post->post_content = preg_replace($shortcode_reconstruction, $new_post_content, $post->post_content);
                    remove_filter('content_save_pre', 'wp_filter_post_kses');
                    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    remove_filter('title_save_pre', 'wp_filter_kses');
                    $post_updated = wp_update_post($post);
                    add_filter('content_save_pre', 'wp_filter_post_kses');
                    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                    add_filter('title_save_pre', 'wp_filter_kses');
                }
                else
                {
                    preg_match_all('#\[aiomatic-article([^\]]*?)\]#i', $post->post_content, $zamatches);
                    if(isset($zamatches[0][0]) && $zamatches[0][0] != '')
                    {
                        $tranzi = '';
                        $post->post_content = preg_replace('#\[aiomatic-article([^\]]*?)\]#i', $new_post_content, $post->post_content);
                        remove_filter('content_save_pre', 'wp_filter_post_kses');
                        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                        remove_filter('title_save_pre', 'wp_filter_kses');
                        $post_updated = wp_update_post($post);
                        add_filter('content_save_pre', 'wp_filter_post_kses');
                        add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
                        add_filter('title_save_pre', 'wp_filter_kses');
                    }
                    else
                    {
                        set_transient('aiomatic_article_transient' . $md5v, $new_post_content, $cache_seconds);
                        $tranzi = $new_post_content;
                    }
                }
                if($is_elementor)
                {
                    $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
                    if(!empty($elementor_data))
                    {
                        $elementor_json = json_decode($elementor_data);
                        if(!empty($elementor_json))
                        {
                            $changemade = false;
                            for($i = 0; $i < count($elementor_json); $i++)
                            {
                                if($elementor_json[$i]->elType == 'section' || $elementor_json[$i]->elType == 'column')
                                {
                                    for($j = 0; $j < count($elementor_json[$i]->elements); $j++)
                                    {
                                        if($elementor_json[$i]->elements[$j]->elType == 'section' || $elementor_json[$i]->elements[$j]->elType == 'column')
                                        {
                                            for($k = 0; $k < count($elementor_json[$i]->elements[$j]->elements); $k++)
                                            {
                                                if($elementor_json[$i]->elements[$j]->elements[$k]->elType == 'widget' && $elementor_json[$i]->elements[$j]->elements[$k]->widgetType == 'shortcode')
                                                {
                                                    if(isset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode))
                                                    {
                                                        $sc = $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode;
                                                        $sc = preg_replace('#\[aiomatic-article([^\]]*?)\]#i', $new_post_content, $sc);
                                                        if($sc != $elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode)
                                                        {
                                                            unset($elementor_json[$i]->elements[$j]->elements[$k]->settings->shortcode);
                                                            $elementor_json[$i]->elements[$j]->elements[$k]->settings->html = $sc;
                                                            $elementor_json[$i]->elements[$j]->elements[$k]->widgetType = 'html';
                                                            $changemade = true;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if($elementor_json[$i]->elements[$j]->elType == 'widget' && $elementor_json[$i]->elements[$j]->widgetType == 'shortcode')
                                            {
                                                if(isset($elementor_json[$i]->elements[$j]->settings->shortcode))
                                                {
                                                    $sc = $elementor_json[$i]->elements[$j]->settings->shortcode;
                                                    $sc = preg_replace('#\[aiomatic-article([^\]]*?)\]#i', $new_post_content, $sc);
                                                    if($sc != $elementor_json[$i]->elements[$j]->settings->shortcode)
                                                    {
                                                        unset($elementor_json[$i]->elements[$j]->settings->shortcode);
                                                        $elementor_json[$i]->elements[$j]->settings->html = $sc;
                                                        $elementor_json[$i]->elements[$j]->widgetType = 'html';
                                                        $changemade = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    if($elementor_json[$i]->elType == 'widget' && $elementor_json[$i]->widgetType == 'shortcode')
                                    {
                                        if(isset($elementor_json[$i]->settings->shortcode))
                                        {
                                            $sc = $elementor_json[$i]->settings->shortcode;
                                            $sc = preg_replace('#\[aiomatic-article([^\]]*?)\]#i', $new_post_content, $sc);
                                            if($sc != $elementor_json[$i]->settings->shortcode)
                                            {
                                                unset($elementor_json[$i]->settings->shortcode);
                                                $elementor_json[$i]->settings->html = $sc;
                                                $elementor_json[$i]->widgetType = 'html';
                                                $changemade = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if($changemade == true)
                            {
                                $elementor_data_new = wp_json_encode($elementor_json);
                                $elementor_data_new = trim($elementor_data_new, '"');
                                if(!empty($elementor_data_new))
                                {
                                    update_post_meta($post->ID, '_elementor_data', $elementor_data_new);
                                }
                            }
                        }
                    }
                }
            }
        }
        elseif($tranzi == 'not_working')
        {
            return '';
        }
        return $tranzi;
    }
    else
    {
        return '';
    }
}

add_shortcode('aiomatic-internet-search', 'aiomatic_internet_shortcode');
function aiomatic_internet_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $atts = shortcode_atts( array(
        'keyword' => ''
    ), $atts );
    //accessing the parameters like this
    $keyword = $atts['keyword'];
    if(empty($keyword))
    {
        return 'A "keyword" parameter is required for this shortcode!';
    }
    $internet_prompt = '';
    if(isset($aiomatic_Main_Settings['internet_prompt']) && $aiomatic_Main_Settings['internet_prompt'] != '')
    {
        $internet_prompt = $aiomatic_Main_Settings['internet_prompt'];
    }
    if(stristr($internet_prompt, '%%web_results%%') === false)
    {
        $internet_prompt .= ' %%web_results%%';
    }
    $locale = '';
    if (isset($aiomatic_Main_Settings['internet_gl']) && $aiomatic_Main_Settings['internet_gl'] != '')
    {
        $locale = $aiomatic_Main_Settings['internet_gl'];
    }
    $internet_rez = aiomatic_internet_result($keyword, false, $locale);
    shuffle($internet_rez);
    if (isset($aiomatic_Main_Settings['results_num']) && trim($aiomatic_Main_Settings['results_num']) != '')
    {
        $results = intval(trim($aiomatic_Main_Settings['results_num']));
    }
    else
    {
        $results = 3;
    }
    $gotcnt = 0;
    $internet_results = '';
    foreach($internet_rez as $emb)
    {
        if($gotcnt >= $results)
        {
            break;
        }
        if (isset($aiomatic_Main_Settings['internet_single_template']) && trim($aiomatic_Main_Settings['internet_single_template']) != '')
        {
            $internet_single_template = $aiomatic_Main_Settings['internet_single_template'];
        }
        else
        {
            $internet_single_template = '[%%result_counter%%]: %%result_title%% %%result_snippet%% ' . PHP_EOL . 'URL: %%result_link%%';
        }
        $internet_single_template = str_replace('%%result_counter%%', $gotcnt + 1, $internet_single_template);
        $internet_single_template = str_replace('%%result_title%%', $emb['title'], $internet_single_template);
        $internet_single_template = str_replace('%%result_snippet%%', $emb['snippet'], $internet_single_template);
        $internet_single_template = str_replace('%%result_link%%', $emb['link'], $internet_single_template);
        $internet_results .= $internet_single_template . PHP_EOL;
        $gotcnt++;
    }
    if($internet_prompt != '')
    {
        $internet_prompt = str_ireplace('%%original_query%%', $keyword, $internet_prompt);
        $internet_prompt = str_ireplace('%%current_date%%', date('Y-m-d'), $internet_prompt);
        $internet_prompt = str_ireplace('%%web_results%%', $internet_results, $internet_prompt);
        if($internet_prompt != '')
        {
            $keyword = $internet_prompt . '\n' . $keyword;
        }
        return $internet_prompt;
    }
    return $internet_results;
}
add_shortcode('aiomatic-text-completion-form', 'aiomatic_form_shortcode');
function aiomatic_form_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $echome = '';
    $atts = shortcode_atts( array(
        'temperature' => '0.7',
        'top_p' => '1',
        'presence_penalty' => '0',
        'frequency_penalty' => '0',
        'model' => aiomatic_get_default_model_name($aiomatic_Main_Settings),
        'assistant_id' => '',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );

    //accessing the parameters like this
    $temp = $atts['temperature'];
    $top_p = $atts['top_p'];
    $presence = $atts['presence_penalty'];
    $frequency = $atts['frequency_penalty'];
    $model = $atts['model'];
    $assistant_id = $atts['assistant_id'];
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    if(aiomatic_is_claude_model($model))
    {
        $stream_url = esc_html(add_query_arg(array(
            'aiomatic_claude_stream' => 'yes',
            'nonce' => wp_create_nonce('aiomatic-streaming-nonce')
        ), site_url() . '/index.php'));
    }
    else
    {
        $stream_url = esc_html(add_query_arg(array(
            'aiomatic_stream' => 'yes',
            'nonce' => wp_create_nonce('aiomatic-streaming-nonce')
        ), site_url() . '/index.php'));
    }
    if(aiomatic_is_claude_model($model))
    {
        $model_type = 'claude';
    }
    else
    {
        if(aiomatic_is_google_model($model))
        {
            $model_type = 'google';
        }
        elseif(aiomatic_is_huggingface_model($model))
        {
            $model_type = 'huggingface';
        }
        elseif(aiomatic_is_ollama_model($model))
        {
            $model_type = 'ollama';
        }
        else
        {
            $model_type = 'gpt';
        }
    }
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-completion-ajax', plugins_url('scripts/openai-completion-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-completion-ajax', 'aiomatic_completition_ajax_object_cmp', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'model' => $model,
		'temp' => $temp,
		'top_p' => $top_p,
		'presence' => $presence,
		'frequency' => $frequency,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id,
        'stream_url' => $stream_url,
        'model_type' => $model_type,
        'secretkey' => 'NDUPPe+cr2Cs2AYiN+JaoBH60cbleu6c'
	));
    $name = md5(get_bloginfo());
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    $all_models = aiomatic_get_all_models(true);
    $models = $all_models; 
    if($model != 'default' && !in_array($model, $models))
    {
        $echome .= 'Invalid model provided!';
        return $echome;
    }
    if($temp != 'default' && floatval($temp) < 0 || floatval($temp) > 1)
    {
        $echome .= 'Invalid temperature provided!';
        return $echome;
    }
    if($top_p != 'default' && floatval($top_p) < 0 || floatval($top_p) > 1)
    {
        $echome .= 'Invalid top_p provided!';
        return $echome;
    }
    if($presence != 'default' && floatval($presence) < -2 || floatval($presence) > 2)
    {
        $echome .= 'Invalid presence_penalty provided!';
        return $echome;
    }
    if($frequency != 'default' && floatval($frequency) < -2 || floatval($frequency) > 2)
    {
        $echome .= 'Invalid frequency_penalty provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<div id="aiomatic_input" ';
    if(($prompt_editable !== 'no' && $prompt_editable !== '0' && $prompt_editable !== 'disabled' && $prompt_editable !== 'off' && $prompt_editable !== 'disable' && $prompt_editable !== 'false') || $prompt_templates === '')
    {
        $echome .= 'contenteditable="true" ';
    }
    $echome .= 'class="form-control" placeholder="Write your AI command here"></div>';
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_completion_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }
    if($model == 'default' || $model == '')
    {
        $echome .= '<label for="model-selector">Model:</label><select class="aiomatic-ai-input-form" id="model-selector">';
        foreach ($models as $model) {
            $echome .= "<option value='" . $model . "'>" . $model . "</option>";
        }
        $echome .= '</select>';
    }
    if($temp == 'default' || $temp == '')
    {
        $echome .= '<label for="temperature-input">Temperature:</label><input type="number" min="0" step="0.01" max="2" class="aiomatic-ai-input-form" id="temperature-input" name="temperature" value="1">';
    }
    if($top_p == 'default' || $top_p == '')
    {
        $echome .= '<label for="top_p-input">Top_p:</label><input type="number" min="0" step="0.01" max="1" class="aiomatic-ai-input-form" id="top_p-input" name="top_p" value="1">';
    }
    if($presence == 'default' || $presence == '')
    {
        $echome .= '<label for="presence-input">Presence Penalty:</label><input type="number" min="-2" step="0.01" max="2" class="aiomatic-ai-input-form" id="presence-input" name="presence" value="0">';
    }
    if($frequency == 'default' || $frequency == '')
    {
        $echome .= '<label for="frequency-input">Frequency Penalty:</label><input type="number" min="0" step="0.01" max="2" class="aiomatic-ai-input-form" id="frequency-input" name="frequency" value="0">';
    }
	$echome .= '</div>';
    $echome .= '<input type="hidden" id="aix-assistant-id" value="' . esc_html($assistant_id) . '"><button type="button" id="copy-button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Copy to clipboard">
    <img src="' . plugins_url('images/copy.ico', __FILE__) . '">
    </button>';

    if($prompt_templates == '')
    {
        $echome .= '<button type="button" id="openai-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
    $echome .= '<button type="button" id="aisubmitbut" onclick="openaifunct()" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-response"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-text-editing-form', 'aiomatic_edit_shortcode');
function aiomatic_edit_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $echome = '';
    $atts = shortcode_atts( array(
        'temperature' => '1',
        'top_p' => '1',
        'model' => AIOMATIC_DEFAULT_MODEL,
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => '',
        'prompt' => '',
        'edit_placeholder' => 'Write your text to be edited here',
        'instruction_placeholder' => 'Write your AI instruction here',
        'result_placeholder' => 'You will see the edited result here',
        'submit_text' => 'Submit',
        'enable_copy' => '1',
        'enable_speech' => '1'
    ), $atts );

    //accessing the parameters like this
    $temp = $atts['temperature'];
    $top_p = $atts['top_p'];
    $model = $atts['model'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $prompt = $atts['prompt'];
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $edit_placeholder = $atts['edit_placeholder'];
    $instruction_placeholder = $atts['instruction_placeholder'];
    $result_placeholder = $atts['result_placeholder'];
    $enable_copy = $atts['enable_copy'];
    $enable_speech = $atts['enable_speech'];
    $submit_text = $atts['submit_text'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-edit-ajax', plugins_url('scripts/openai-edit-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-edit-ajax', 'aiomatic_edit_ajax_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'temp' => $temp,
		'top_p' => $top_p,
		'model' => $model,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id,
        'prompt' => $prompt
	));
    $name = md5(get_bloginfo());
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    $models = aiomatic_get_all_models(true);
    if($model != 'default' && !in_array($model, $models))
    {
        $echome .= 'Invalid model provided! ' . $model;
        return $echome;
    }
    if($temp != 'default' && floatval($temp) < 0 || floatval($temp) > 1)
    {
        $echome .= 'Invalid temperature provided!';
        return $echome;
    }
    if($top_p != 'default' && floatval($top_p) < 0 || floatval($top_p) > 1)
    {
        $echome .= 'Invalid top_p provided!';
        return $echome;
    }
    if(empty($prompt))
    {
        // Display the form
        $echome .= '
            <form class="openai-ai-form-alt" method="post">
                <div class="form-group">
                <textarea class="aiomatic-edit-textarea aiomatic-edit-area" rows="8" id="aiomatic_edit_input" placeholder="' . esc_attr($edit_placeholder) . '"></textarea>';

        $echome .= '<textarea class="aiomatic-edit-textarea aiomatic-instruction-area" rows="8" id="aiomatic_edit_instruction" placeholder="' . esc_attr($instruction_placeholder) . '"';
        if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
        {
            $echome .= ' disabled';
        }
        $echome .= '></textarea>';
        $echome .= '<textarea class="aiomatic-edit-textarea aiomatic-response-area" rows="5" id="aiomatic_edit_response" disabled placeholder="' . esc_attr($result_placeholder) . '"></textarea>';
        
        if($model == 'default' || $model == '')
        {
            $echome .= '<label for="model-edit-selector">Model:</label><select class="aiomatic-ai-input-form" id="model-edit-selector">';
            foreach ($models as $model) {
                $echome .= "<option value='" . $model . "'>" . $model . "</option>";
            }
            $echome .= '</select>';
        }
        if($temp == 'default' || $temp == '')
        {
            $echome .= '<label for="temperature-edit-input">Temperature:</label><input type="number" min="0" step="0.01" max="2" class="aiomatic-ai-input-form" id="temperature-edit-input" name="temperature" value="0">';
        }
        if($top_p == 'default' || $top_p == '')
        {
            $echome .= '<label for="top_p-edit-input">Top_p:</label><input type="number" min="0" step="0.01" max="1" class="aiomatic-ai-input-form" id="top_p-edit-input" name="top_p" value="1">';
        }
        if($prompt_templates != '')
        {
            $predefined_prompts_arr = explode(';', $prompt_templates);
            $echome .= '<select id="aiomatic_edit_templates" class="cr_width_full">';
            $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
            foreach($predefined_prompts_arr as $sval)
            {
                $ppro = explode('|~|~|', $sval);
                if(isset($ppro[1]))
                {
                    $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
                }
                else
                {
                    $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
                }
            }
            $echome .= '</select>';
        }
        $echome .= '</div>'; 
        if($enable_copy == '1')
        {
            $echome .= '<button type="button" id="copy-edit-button" data-target="aiomatic_edit_response" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="' . esc_html__("Copy to clipboard", 'aiomatic-automatic-ai-content-writer') . '">
            <img src="' . plugins_url('images/copy.ico', __FILE__) . '">
            </button>';
        }
        
        if($prompt_templates == '' && $enable_speech == '1')
        {
            $echome .= '<button type="button" id="openai-edit-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
        }
        $echome .= '<button type="button" id="aieditsubmitbut" onclick="openaieditfunct()" class="btn btn-primary">' . esc_html($submit_text) . '</button>
                <div id="openai-edit-response"></div>
            </form> 
        ';
    }
    else
    {
        // Display the form
        $echome .= '
            <form class="openai-ai-form-alt" method="post">
                <div class="form-group">
                <textarea class="aiomatic-edit-textarea aiomatic-edit-area" rows="8" id="aiomatic_edit_input" placeholder="' . esc_attr($edit_placeholder) . '"></textarea>';

        $echome .= '<textarea class="aiomatic-edit-textarea aiomatic-response-area" rows="8" id="aiomatic_edit_result" disabled placeholder="' . esc_attr($result_placeholder) . '"></textarea>';
        
        if($model == 'default' || $model == '')
        {
            $echome .= '<label for="model-edit-selector">Model:</label><select class="aiomatic-ai-input-form" id="model-edit-selector">';
            foreach ($models as $model) {
                $echome .= "<option value='" . $model . "'>" . $model . "</option>";
            }
            $echome .= '</select>';
        }
        if($temp == 'default' || $temp == '')
        {
            $echome .= '<label for="temperature-edit-input">Temperature:</label><input type="number" min="0" step="0.01" max="2" class="aiomatic-ai-input-form" id="temperature-edit-input" name="temperature" value="0">';
        }
        if($top_p == 'default' || $top_p == '')
        {
            $echome .= '<label for="top_p-edit-input">Top_p:</label><input type="number" min="0" step="0.01" max="1" class="aiomatic-ai-input-form" id="top_p-edit-input" name="top_p" value="1">';
        }
        $echome .= '</div>'; 
        if($enable_copy == '1')
        {
            $echome .= '<button type="button" id="copy-edit-button" data-target="aiomatic_edit_result" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="' . esc_html__("Copy to clipboard", 'aiomatic-automatic-ai-content-writer') . '">
            <img src="' . plugins_url('images/copy.ico', __FILE__) . '">
            </button>';
        }
        
        if($prompt_templates == '' && $enable_speech == '1')
        {
            $echome .= '<button type="button" id="openai-edit-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
        }
        $echome .= '<button type="button" id="aieditsubmitbut" onclick="openaieditfunct()" class="btn btn-primary">' . esc_html($submit_text) . '</button>
                <div id="openai-edit-response"></div>
            </form> 
        ';
    }
    return $echome;
}

add_shortcode('aiomatic-image-generator-form', 'aiomatic_image_shortcode');
function aiomatic_image_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $uniqid = uniqid();
    $echome = '';
    $atts = shortcode_atts( array(
        'image_size' => 'default',
        'image_model' => 'dalle2',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    //accessing the parameters like this
    $image_size = $atts['image_size'];
    $image_model = $atts['image_model'];
    $image_placeholder = plugins_url('images/loading.gif', __FILE__);
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-image-ajax', plugins_url('scripts/openai-image-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-image-ajax', 'aiomatic_image_ajax_object' . $uniqid, array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'image_size' => $image_size,
		'image_placeholder' => $image_placeholder,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id,
        'image_model' => $image_model
	));
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    if($image_model == 'dalle2')
    {
        $sizes = array('1024x1024', '512x512', '256x256');
    }
    else
    {
        $sizes = array('1024x1024', '1792x1024', '1024x1792', '1536x1024', '1024x1536');
    }
    if($image_size != 'default' && !in_array($image_size, $sizes))
    {
        $echome .= 'Invalid image size provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<input type="hidden" value="' . esc_html($image_model). '" id="image_model' . $uniqid . '"><textarea class="aiomatic-image-textarea aiomatic-image-instruction-area" rows="8" id="aiomatic_image_instruction' . $uniqid . '" placeholder="Write your AI instruction here"';
    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
    {
        $echome .= ' disabled';
    }
    $echome .= '></textarea>';   
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_image_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }
    $echome .= '<br/>
            <div class="aiomatic-image-result cr_image_center" id="aiomatic_image_div"><img id="aiomatic_image_response' . $uniqid . '" class="aiomatic_image_response" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII="></div>';
    if($image_size == 'default' || empty($image_size))
    {
        $echome .= '<label for="ai-image-size-selector' . $uniqid . '">Image Size:</label><select class="aiomatic-ai-input-form" id="ai-image-size-selector' . $uniqid . '">';
        foreach ($sizes as $size) {
            $echome .= "<option value='" . $size . "'>" . $size . "</option>";
        }
        $echome .= '</select>';
    }
	$echome .= '</div>';
    if($prompt_templates == '')
    {
        $echome .= '<button type="button" id="openai-image-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
    $echome .= '<button type="button" id="aiimagesubmitbut' . $uniqid . '" onclick="openaiimagefunct(\'' . $uniqid . '\')" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-image-response' . $uniqid . '"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-image-editor-form', 'aiomatic_image_editor_shortcode');
function aiomatic_image_editor_shortcode($atts) 
{
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $uniqid = uniqid();
    $echome = '';
    $atts = shortcode_atts( array(
        'image_size' => 'default',
        'image_model' => 'dalle2',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    //accessing the parameters like this
    $image_size = $atts['image_size'];
    $image_model = $atts['image_model'];
    $image_placeholder = plugins_url('images/loading.gif', __FILE__);
    $name = md5(get_bloginfo());
    
    wp_enqueue_script($name . 'openai-image-ajax', plugins_url('scripts/openai-image-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-image-ajax', 'aiomatic_image_ajax_object' . $uniqid, array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'image_size' => $image_size,
		'image_placeholder' => $image_placeholder,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id,
        'image_model' => $image_model
	));
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    if($image_model == 'dalle2')
    {
        $sizes = array('1024x1024', '512x512', '256x256');
    }
    else
    {
        $sizes = array('1024x1024', '1792x1024', '1024x1792', '1536x1024', '1024x1536');
    }
    if($image_size != 'default' && !in_array($image_size, $sizes))
    {
        $echome .= 'Invalid image size provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<input type="hidden" value="' . esc_html($image_model). '" id="image_model' . $uniqid . '"><textarea class="aiomatic-image-textarea aiomatic-image-instruction-area" rows="8" id="aiomatic_image_instruction' . $uniqid . '" placeholder="Write your AI instruction here"';
    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
    {
        $echome .= ' disabled';
    }
    $echome .= '></textarea>';   
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_image_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }
    $echome .= '<br/>';
    $echome .= '<label for="aiomatic_image_upload">' . esc_html__("Select an image to edit:", 'aiomatic-automatic-ai-content-writer') . '</label><br/>';
    $echome .= '<input type="file" accept="image/*" id="aiomatic_image_upload" required>';
    $echome .= '<br/><br/>';
    $echome .= '<label for="aiomatic_image_strength">' . esc_html__("AI generated image's similarity with the original image:", 'aiomatic-automatic-ai-content-writer') . '</label><br/>';
    $echome .= '<input type="range" id="aiomatic_image_strength" name="aiomatic_image_strength" min="0" max="1" step="0.01" />';
    $echome .= '<br/>
            <div class="aiomatic-image-result cr_image_center" id="aiomatic_image_div"><img id="aiomatic_image_response' . $uniqid . '" class="aiomatic_image_response" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII="></div>';
    if($image_size == 'default' || empty($image_size))
    {
        $echome .= '<label for="ai-image-size-selector' . $uniqid . '">Image Size:</label><select class="aiomatic-ai-input-form" id="ai-image-size-selector' . $uniqid . '">';
        foreach ($sizes as $size) {
            $echome .= "<option value='" . $size . "'>" . $size . "</option>";
        }
        $echome .= '</select>';
    }
	$echome .= '</div>';
    if($prompt_templates == '')
    {
        $echome .= '<button type="button" id="openai-image-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
    $echome .= '<button type="button" id="aiimagesubmitbut' . $uniqid . '" onclick="submitAiomaticImageEditor(\'' . $uniqid . '\')" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-image-response' . $uniqid . '"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-stable-image-generator-form', 'aiomatic_stable_image_shortcode');
function aiomatic_stable_image_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $echome = '';
    $atts = shortcode_atts( array(
        'image_size' => 'default',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    //accessing the parameters like this
    $image_size = $atts['image_size'];
    $image_placeholder = plugins_url('images/loading.gif', __FILE__);
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-stable-image-ajax', plugins_url('scripts/openai-stable-image-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-stable-image-ajax', 'aiomatic_stable_image_ajax_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'image_size' => $image_size,
		'image_placeholder' => $image_placeholder,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id
	));
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    $sizes = array('1024x1024', '512x512');
    if($image_size != 'default' && !in_array($image_size, $sizes))
    {
        $echome .= 'Invalid image size provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<textarea class="aiomatic-image-textarea aiomatic-image-instruction-area" rows="8" id="aiomatic_stable_image_instruction" placeholder="Write your AI instruction here"';
    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
    {
        $echome .= ' disabled';
    }
    $echome .= '></textarea>';
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_stable_image_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }        
    $echome .= '<br/>
            <div class="aiomatic-image-result cr_image_center" id="aiomatic_image_div"><img id="aiomatic_stable_image_response" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII="></div>';
    if($image_size == 'default' || empty($image_size))
    {
        $echome .= '<label for="model-stable-size-selector">Image Size:</label><select class="aiomatic-ai-input-form" id="model-stable-size-selector">';
        foreach ($sizes as $size) {
            $echome .= "<option value='" . $size . "'>" . $size . "</option>";
        }
        $echome .= '</select>';
    }
	$echome .= '</div>';
    if($prompt_templates == '')
    {
            $echome .= '<button type="button" id="openai-stable-image-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
			$echome .= '<button type="button" id="aistableimagesubmitbut" onclick="stableimagefunct()" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-stable-image-response"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-google-image-generator-form', 'aiomatic_google_image_shortcode');
function aiomatic_google_image_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $echome = '';
    $atts = shortcode_atts( array(
        'image_size' => 'default',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    //accessing the parameters like this
    $image_size = $atts['image_size'];
    $image_placeholder = plugins_url('images/loading.gif', __FILE__);
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-google-image-ajax', plugins_url('scripts/openai-google-image-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-google-image-ajax', 'aiomatic_google_image_ajax_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'image_size' => $image_size,
		'image_placeholder' => $image_placeholder,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id
	));
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    $sizes = array('1024x1024', '512x512');
    if($image_size != 'default' && !in_array($image_size, $sizes))
    {
        $echome .= 'Invalid image size provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<textarea class="aiomatic-image-textarea aiomatic-image-instruction-area" rows="8" id="aiomatic_google_image_instruction" placeholder="Write your AI instruction here"';
    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
    {
        $echome .= ' disabled';
    }
    $echome .= '></textarea>';
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_google_image_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }        
    $echome .= '<br/>
            <div class="aiomatic-image-result cr_image_center" id="aiomatic_image_div"><img id="aiomatic_google_image_response" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII="></div>';
    if($image_size == 'default' || empty($image_size))
    {
        $echome .= '<label for="model-google-size-selector">Image Size:</label><select class="aiomatic-ai-input-form" id="model-google-size-selector">';
        foreach ($sizes as $size) {
            $echome .= "<option value='" . $size . "'>" . $size . "</option>";
        }
        $echome .= '</select>';
    }
	$echome .= '</div>';
    if($prompt_templates == '')
    {
            $echome .= '<button type="button" id="openai-google-image-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
			$echome .= '<button type="button" id="aigoogleimagesubmitbut" onclick="googleimagefunct()" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-google-image-response"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-midjourney-image-generator-form', 'aiomatic_midjourney_image_shortcode');
function aiomatic_midjourney_image_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $echome = '';
    $atts = shortcode_atts( array(
        'image_size' => 'default',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    //accessing the parameters like this
    $image_size = $atts['image_size'];
    $image_placeholder = plugins_url('images/loading.gif', __FILE__);
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-midjourney-image-ajax', plugins_url('scripts/openai-midjourney-image-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-midjourney-image-ajax', 'aiomatic_midjourney_image_ajax_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'image_size' => $image_size,
		'image_placeholder' => $image_placeholder,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id
	));
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    $sizes = array('1024x1024', '512x512', '1024x1792', '1792x1024', '1536x1024', '1024x1536');
    if($image_size != 'default' && !in_array($image_size, $sizes))
    {
        $echome .= 'Invalid image size provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<textarea class="aiomatic-image-textarea aiomatic-image-instruction-area" rows="8" id="aiomatic_midjourney_image_instruction" placeholder="Write your AI instruction here"';
    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
    {
        $echome .= ' disabled';
    }
    $echome .= '></textarea>';
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_midjourney_image_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }        
    $echome .= '<br/>
            <div class="aiomatic-image-result cr_image_center" id="aiomatic_image_div"><img id="aiomatic_midjourney_image_response" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII="></div>';
    if($image_size == 'default' || empty($image_size))
    {
        $echome .= '<label for="model-midjourney-size-selector">Image Size:</label><select class="aiomatic-ai-input-form" id="model-midjourney-size-selector">';
        foreach ($sizes as $size) {
            $echome .= "<option value='" . $size . "'>" . $size . "</option>";
        }
        $echome .= '</select>';
    }
	$echome .= '</div>';
    if($prompt_templates == '')
    {
            $echome .= '<button type="button" id="openai-midjourney-image-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
			$echome .= '<button type="button" id="aimidjourneyimagesubmitbut" onclick="midjourneyimagefunct()" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-midjourney-image-response"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-replicate-image-generator-form', 'aiomatic_replicate_image_shortcode');
function aiomatic_replicate_image_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $echome = '';
    $atts = shortcode_atts( array(
        'image_size' => 'default',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    //accessing the parameters like this
    $image_size = $atts['image_size'];
    $image_placeholder = plugins_url('images/loading.gif', __FILE__);
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-replicate-image-ajax', plugins_url('scripts/openai-replicate-image-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-replicate-image-ajax', 'aiomatic_replicate_image_ajax_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'image_size' => $image_size,
		'image_placeholder' => $image_placeholder,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id
	));
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    $sizes = array('1024x1024', '512x512', '1024x1792', '1792x1024', '1536x1024', '1024x1536');
    if($image_size != 'default' && !in_array($image_size, $sizes))
    {
        $echome .= 'Invalid image size provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<textarea class="aiomatic-image-textarea aiomatic-image-instruction-area" rows="8" id="aiomatic_replicate_image_instruction" placeholder="Write your AI instruction here"';
    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
    {
        $echome .= ' disabled';
    }
    $echome .= '></textarea>';
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_replicate_image_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }        
    $echome .= '<br/>
            <div class="aiomatic-image-result cr_image_center" id="aiomatic_image_div"><img id="aiomatic_replicate_image_response" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII="></div>';
    if($image_size == 'default' || empty($image_size))
    {
        $echome .= '<label for="model-replicate-size-selector">Image Size:</label><select class="aiomatic-ai-input-form" id="model-replicate-size-selector">';
        foreach ($sizes as $size) {
            $echome .= "<option value='" . $size . "'>" . $size . "</option>";
        }
        $echome .= '</select>';
    }
	$echome .= '</div>';
    if($prompt_templates == '')
    {
            $echome .= '<button type="button" id="openai-replicate-image-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
			$echome .= '<button type="button" id="aireplicateimagesubmitbut" onclick="replicateimagefunct()" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-replicate-image-response"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-ideogram-image-generator-form', 'aiomatic_ideogram_image_shortcode');
function aiomatic_ideogram_image_shortcode($atts) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $echome = '';
    $atts = shortcode_atts( array(
        'image_size' => 'default',
        'user_token_cap_per_day' => '',
        'prompt_templates' => '',
        'prompt_editable' => ''
    ), $atts );
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $user_id = '0';
    if(!empty($user_token_cap_per_day))
    {
        $user_id = get_current_user_id();
    }
    //accessing the parameters like this
    $image_size = $atts['image_size'];
    $image_placeholder = plugins_url('images/loading.gif', __FILE__);
    $name = md5(get_bloginfo());
    wp_enqueue_script($name . 'openai-reideogramplicate-image-ajax', plugins_url('scripts/openai-ideogram-image-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
	wp_localize_script($name . 'openai-reideogramplicate-image-ajax', 'aiomatic_ideogram_image_ajax_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-ajax-nonce'),
		'image_size' => $image_size,
		'image_placeholder' => $image_placeholder,
        'user_token_cap_per_day' => $user_token_cap_per_day,
        'user_id' => $user_id
	));
    wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
    $sizes = array('1024x1024', '512x512', '1024x1792', '1792x1024', '1536x1024', '1024x1536');
    if($image_size != 'default' && !in_array($image_size, $sizes))
    {
        $echome .= 'Invalid image size provided!';
        return $echome;
    }
	// Display the form
	$echome .= '
		<form class="openai-ai-form-alt" method="post">
			<div class="form-group">';
    $echome .= '<textarea class="aiomatic-image-textarea aiomatic-image-instruction-area" rows="8" id="aiomatic_ideogram_image_instruction" placeholder="Write your AI instruction here"';
    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'disabled' || $prompt_editable == 'off' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
    {
        $echome .= ' disabled';
    }
    $echome .= '></textarea>';
    if($prompt_templates != '')
    {
        $predefined_prompts_arr = explode(';', $prompt_templates);
        $echome .= '<select id="aiomatic_ideogram_image_templates" class="cr_width_full">';
        $echome .= '<option disabled selected>' . esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($predefined_prompts_arr as $sval)
        {
            $ppro = explode('|~|~|', $sval);
            if(isset($ppro[1]))
            {
                $echome .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
            }
            else
            {
                $echome .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
            }
        }
        $echome .= '</select>';
    }        
    $echome .= '<br/>
            <div class="aiomatic-image-result cr_image_center" id="aiomatic_image_div"><img id="aiomatic_ideogram_image_response" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII="></div>';
    if($image_size == 'default' || empty($image_size))
    {
        $echome .= '<label for="model-ideogram-size-selector">Image Size:</label><select class="aiomatic-ai-input-form" id="model-ideogram-size-selector">';
        foreach ($sizes as $size) {
            $echome .= "<option value='" . $size . "'>" . $size . "</option>";
        }
        $echome .= '</select>';
    }
	$echome .= '</div>';
    if($prompt_templates == '')
    {
            $echome .= '<button type="button" id="openai-ideogram-image-speech-button" class="btn btn-primary" title="Record your voice">
                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
            </button>';
    }
			$echome .= '<button type="button" id="aiideogramimagesubmitbut" onclick="ideogramimagefunct()" class="btn btn-primary">' . esc_html__('Submit', 'aiomatic-automatic-ai-content-writer') . '</button>
            <div id="openai-ideogram-image-response"></div>
		</form> 
	';
    return $echome;
}

add_shortcode('aiomatic-persona-selector', 'aiomatic_persona_shortcode');
function aiomatic_persona_shortcode($atts) 
{
    $atts = shortcode_atts( array(
        'temperature' => '',
        'top_p' => '',
        'presence_penalty' => '',
        'frequency_penalty' => '',
        'model' => '',
        'instant_response' => '',
        'enable_vision' => '',
        'assistant_id' => '',
        'user_message_preppend' => '',
        'ai_message_preppend' => '',
        'chat_preppend_text' => '',
        'chat_mode' => '',
        'user_token_cap_per_day' => '',
        'persistent' => '',
        'persistent_guests' => '',
        'internet_access' => 'enabled',
        'embeddings' => 'enabled',
        'embeddings_namespace' => '',
        'prompt_templates' => '',
        'prompt_editable' => '',
        'placeholder' => '',
        'submit' => '',
        'submit_image' => '',
        'submit_full' => '',
        'show_in_window' => '',
        'window_location' => '',
        'font_size' => '',
        'height' => '',
        'background' => '',
        'minheight' => '',
        'bubble_width' => '',
        'bubble_alignment' => '',
        'bubble_user_alignment' => '',
        'show_user_avatar' => '',
        'show_ai_avatar' => '',
        'general_background' => '',
        'width' => '',
        'user_font_color' => '',
        'user_background_color' => '',
        'ai_font_color' => '',
        'ai_background_color' => '',
        'input_text_color' => '',
        'persona_name_color' => '',
        'persona_role_color' => '',
        'input_placeholder_color' => '',
        'input_border_color' => '',
        'submit_color' => '',
        'voice_color' => '',
        'ai_role' => '',
        'ai_avatar' => '',
        'user_avatar' => '',
        'ai_name' => '',
        'voice_color_activated' => '',
        'submit_text_color' => '',
        'show_header' => '',
        'show_dltxt' => '',
        'show_mute' => '',
        'show_internet' => '',
        'overwrite_voice' => '',
        'overwrite_avatar_image' => '',
        'disable_streaming' => '',
        'show_clear' => '',
        'compliance' => '',
        'select_prompt' => '',
        'chatbot_text_speech' => '',
        'enable_god_mode' => '',
        'ai_personas' => '',
        'upload_pdf' => '',
        'file_uploads' => '',
        'custom_header' => '',
        'custom_footer' => '',
        'custom_css' => '',
        'send_message_sound' => '',
        'receive_message_sound' => '',
        'response_delay' => '',
        'no_padding' => '',
        'store_data' => '',
        'additional_bots' => '',
        'conversation_starters' => '',
        'bot_workflow' => ''
    ), $atts );
    if(!aiomatic_validate_activation())
    {
        return;
    }
    if(!isset($_GET['personaid']) || empty(trim($_GET['personaid'])) || !is_numeric(trim($_GET['personaid'])))
    {
        $ai_personas = trim($atts['ai_personas']);
        if(empty($ai_personas))
        {
            return esc_html__("You need to add a list of persona IDs, in the ai_personas shortcode parameter.", 'aiomatic-automatic-ai-content-writer');
        }
        else
        {
            $search_persona_arr = explode(',', $ai_personas);
            $search_persona_arr = array_map('trim', $search_persona_arr);
            $my_post = array();
            $my_post['post__in'] = $search_persona_arr;
            $my_post['post_type'] = 'aiomatic_personas';
            $persona_arr = get_posts($my_post);
            if($persona_arr === null || empty($persona_arr))
            {
                return esc_html__("Incorrect ai_personas parameter given.", 'aiomatic-automatic-ai-content-writer');
            }
            $name = md5(get_bloginfo());
            wp_register_style($name . '-custom-persona-style', plugins_url('styles/aiomatic-persona.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
            wp_enqueue_style($name . '-custom-persona-style');
            global $wp;
            $curr_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
            $return_me = '';
            foreach ($persona_arr as $aiomatic_persona)
            {
                $this_url = add_query_arg( array(
                    'personaid' => $aiomatic_persona->ID
                ), $curr_url );
                $return_me .= '<div class="aiomatic-col-lg-3"><div class="aiomatic-chat-boxes aiomatic-text-center"><a href="' . $this_url . '" title="' . esc_html($aiomatic_persona->post_title) . '"><div class="aiomatic-card"><div class="aiomatic-card-body">';
                $att_src = get_the_post_thumbnail_url( $aiomatic_persona->ID, 'thumbnail' );
                if ( $att_src )
                {
                    $return_me .= '<div class="aiomatic-widget-user-image"><img alt="User Avatar" class="ai-user-avatar aiomatic-rounded-circle" src="' . $att_src . '"></div>';
                }
                else
                {
                    $return_me .= '<div class="aiomatic-widget-user-image">' . esc_html__("No avatar added", 'aiomatic-automatic-ai-content-writer') . '</div>';
                }
                $return_me .= '<div class="aiomatic-template-title"><h6 class="aiomatic-number-font">' . esc_html($aiomatic_persona->post_title) . '</h6></div><div class="aiomatic-template-info"><p class="aiomatic-text-muted">' . esc_html($aiomatic_persona->post_excerpt) . '</p></div>';
                $return_me .= '</div></div></a></div></div>';
            }
            return $return_me;
        }
    }
    else
    {
        $name = md5(get_bloginfo());
        wp_register_style($name . '-custom-persona-style', plugins_url('styles/aiomatic-persona.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
        wp_enqueue_style($name . '-custom-persona-style');
        global $wp;
        $curr_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
        $atts['ai_persona'] = trim($_GET['personaid']);
        $atts['show_in_window'] = 'no';
        return '<div class="ai_chatbot_main_holder"><div>' . aiomatic_chat_shortcode($atts) . '</div><div class="aiomatic-text-center"><button type="button" id="aichatbackbut" onclick="window.location.replace(\'' . $curr_url . '\');" class="btn btn-primary back-but">' . esc_html__("Back", 'aiomatic-automatic-ai-content-writer') . '</button></div></div>';
    }
}

add_shortcode('aiomatic-chat-form', 'aiomatic_chat_shortcode');
function aiomatic_chat_shortcode($atts) {
    $atts = shortcode_atts( array(
        'temperature' => '',
        'top_p' => '',
        'presence_penalty' => '',
        'frequency_penalty' => '',
        'model' => '',
        'enable_vision' => '',
        'instant_response' => '',
        'assistant_id' => '',
        'chat_preppend_text' => '',
        'user_message_preppend' => '',
        'ai_message_preppend' => '',
        'internet_access' => 'enabled',
        'embeddings' => 'enabled',
        'embeddings_namespace' => '',
        'ai_first_message' => '',
        'chat_mode' => '',
        'user_token_cap_per_day' => '',
        'persistent' => '',
        'persistent_guests' => '',
        'prompt_templates' => '',
        'prompt_editable' => '',
        'placeholder' => '',
        'submit' => '',
        'submit_image' => '',
        'submit_full' => '',
        'show_in_window' => '',
        'window_location' => '',
        'font_size' => '',
        'height' => '',
        'background' => '',
        'minheight' => '',
        'bubble_width' => '',
        'bubble_alignment' => '',
        'bubble_user_alignment' => '',
        'show_user_avatar' => '',
        'show_ai_avatar' => '',
        'general_background' => '',
        'width' => '',
        'user_font_color' => '',
        'user_background_color' => '',
        'input_text_color' => '',
        'persona_name_color' => '',
        'persona_role_color' => '',
        'input_placeholder_color' => '',
        'ai_font_color' => '',
        'ai_background_color' => '',
        'input_border_color' => '',
        'submit_color' => '',
        'voice_color' => '',
        'voice_color_activated' => '',
        'submit_text_color' => '',
        'ai_role' => '',
        'ai_avatar' => '',
        'user_avatar' => '',
        'ai_name' => '',
        'show_header' => '',
        'show_dltxt' => '',
        'show_mute' => '',
        'show_internet' => '',
        'overwrite_voice' => '',
        'overwrite_avatar_image' => '',
        'disable_streaming' => '',
        'show_clear' => '',
        'ai_persona' => '',
        'live_preview' => '',
        'compliance' => '',
        'select_prompt' => '',
        'chatbot_text_speech' => '',
        'enable_god_mode' => '',
        'upload_pdf' => '',
        'file_uploads' => '',
        'custom_header' => '',
        'custom_footer' => '',
        'custom_css' => '',
        'send_message_sound' => '',
        'receive_message_sound' => '',
        'response_delay' => '',
        'disable_filters' => '',
        'no_padding' => '',
        'conversation_starters' => '',
        'bot_workflow' => '',
        'store_data' => '',
        'additional_bots' => ''
    ), $atts );
    if(!aiomatic_validate_activation())
    {
        return '';
    }
    $chat_unique_id = aiomatic_generateUniqueIdFromArray($atts);
    $chatid = uniqid();
    $return_me = '<div class="aiomatic-chat-holder" id="aiomatic-chat-holder' . esc_attr($chatid) . '" data-id="' . esc_attr($chat_unique_id) . '" instance="' . esc_attr($chatid) . '">';
    $temp = $atts['temperature'];
    $top_p = $atts['top_p'];
    $presence = $atts['presence_penalty'];
    $frequency = $atts['frequency_penalty'];
    $model = $atts['model'];
    $enable_vision = $atts['enable_vision'];
    $disable_streaming = $atts['disable_streaming'];
    $instant_response = $atts['instant_response'];
    $assistant_id = $atts['assistant_id'];
    $chat_preppend_text = $atts['chat_preppend_text'];
    $user_message_preppend = $atts['user_message_preppend'];
    $ai_message_preppend = $atts['ai_message_preppend'];
    $ai_name = $atts['ai_name'];
    $ai_first_message = $atts['ai_first_message'];
    $chat_mode = $atts['chat_mode'];
    $no_padding = $atts['no_padding'];
    $conversation_starters = $atts['conversation_starters'];
    $bot_workflow = $atts['bot_workflow'];
    $store_data = $atts['store_data'];
    $user_token_cap_per_day = $atts['user_token_cap_per_day'];
    $persistent = $atts['persistent'];
    $prompt_templates = $atts['prompt_templates'];
    $prompt_editable = $atts['prompt_editable'];
    $internet_access = $atts['internet_access'];
    $embeddings = $atts['embeddings'];
    $embeddings_namespace = $atts['embeddings_namespace'];
    $placeholder = $atts['placeholder'];
    $submit = $atts['submit'];
    $submit_image = $atts['submit_image'];
    $submit_full = $atts['submit_full'];
    $enable_front_end = $atts['show_in_window'];
    $window_location = $atts['window_location'];
    $font_size = $atts['font_size'];
    $height = $atts['height'];
    $background = $atts['background'];
    $minheight = $atts['minheight'];
    $bubble_width = $atts['bubble_width'];
    $bubble_alignment = $atts['bubble_alignment'];
    $bubble_user_alignment = $atts['bubble_user_alignment'];
    $show_user_avatar = $atts['show_user_avatar'];
    $show_ai_avatar = $atts['show_ai_avatar'];
    $general_background = $atts['general_background'];
    $user_font_color = $atts['user_font_color'];
    $user_background_color = $atts['user_background_color'];
    $ai_font_color = $atts['ai_font_color'];
    $ai_background_color = $atts['ai_background_color'];
    $input_border_color = $atts['input_border_color'];
    $input_text_color = $atts['input_text_color'];
    $persona_name_color = $atts['persona_name_color'];
    $persona_role_color = $atts['persona_role_color'];
    $input_placeholder_color = $atts['input_placeholder_color'];
    $submit_color = $atts['submit_color'];
    $voice_color = $atts['voice_color'];
    $voice_color_activated = $atts['voice_color_activated'];
    $submit_text_color = $atts['submit_text_color'];
    $ai_role = $atts['ai_role'];
    $ai_avatar = $atts['ai_avatar'];
    $user_avatar = $atts['user_avatar'];
    $width = $atts['width'];
    $show_header = $atts['show_header'];
    $show_dltxt = $atts['show_dltxt'];
    $show_mute = $atts['show_mute'];
    $show_internet = $atts['show_internet'];
    $overwrite_voice = $atts['overwrite_voice'];
    $overwrite_avatar_image = $atts['overwrite_avatar_image'];
    $show_clear = $atts['show_clear'];
    $persistent_guests = $atts['persistent_guests'];
    $ai_persona = $atts['ai_persona'];
    $compliance = $atts['compliance'];
    $select_prompt = $atts['select_prompt'];
    $chatbot_text_speech = $atts['chatbot_text_speech'];
    $enable_god_mode = $atts['enable_god_mode'];
    $upload_pdf = $atts['upload_pdf'];
    $file_uploads = $atts['file_uploads'];
    $custom_header = $atts['custom_header'];
    $custom_footer = $atts['custom_footer'];
    $custom_css = $atts['custom_css'];
    $send_message_sound = $atts['send_message_sound'];
    $receive_message_sound = $atts['receive_message_sound'];
    $response_delay = $atts['response_delay'];
    $live_preview = $atts['live_preview'];
    $disable_filters = $atts['disable_filters'];
    $additional_bots = $atts['additional_bots'];
    $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Chatbot_Settings['custom_footer']) && $aiomatic_Chatbot_Settings['custom_footer'] != '' && $custom_footer == '')
    {
        $custom_footer = $aiomatic_Chatbot_Settings['custom_footer'];
    }
    if (isset($aiomatic_Chatbot_Settings['custom_header']) && $aiomatic_Chatbot_Settings['custom_header'] != '' && $custom_header == '')
    {
        $custom_header = $aiomatic_Chatbot_Settings['custom_header'];
    }
    if (isset($aiomatic_Chatbot_Settings['custom_css']) && $aiomatic_Chatbot_Settings['custom_css'] != '' && $custom_css == '')
    {
        $custom_css = $aiomatic_Chatbot_Settings['custom_css'];
    }
    $strip_botname = '0';
    if (isset($aiomatic_Chatbot_Settings['strip_botname']) && $aiomatic_Chatbot_Settings['strip_botname'] == 'on')
    {
        $strip_botname = '1';
    }
    if (isset($aiomatic_Chatbot_Settings['additional_bots']) && $aiomatic_Chatbot_Settings['additional_bots'] != '' && $additional_bots == '') 
    {
        if(!empty($aiomatic_Chatbot_Settings['additional_bots']) && is_array($aiomatic_Chatbot_Settings['additional_bots']))
        {
            $additional_bots = implode(',', $aiomatic_Chatbot_Settings['additional_bots']);
        }
    }
    $custom_footer = html_entity_decode($custom_footer, ENT_QUOTES);
    $custom_header = html_entity_decode($custom_header, ENT_QUOTES);
    $custom_css = html_entity_decode($custom_css, ENT_QUOTES);
    if (isset($aiomatic_Chatbot_Settings['chat_model']) && $aiomatic_Chatbot_Settings['chat_model'] != '' && $model == '') 
    {
        $model = $aiomatic_Chatbot_Settings['chat_model'];
    }
    if (isset($aiomatic_Chatbot_Settings['temperature']) && $aiomatic_Chatbot_Settings['temperature'] != '' && $temp == '') 
    {
        $temp = $aiomatic_Chatbot_Settings['temperature'];
    }
    if (isset($aiomatic_Chatbot_Settings['top_p']) && $aiomatic_Chatbot_Settings['top_p'] != '' && $top_p == '') 
    {
        $top_p = $aiomatic_Chatbot_Settings['top_p'];
    }
    if (isset($aiomatic_Chatbot_Settings['presence_penalty']) && $aiomatic_Chatbot_Settings['presence_penalty'] != '' && $presence == '') 
    {
        $presence = $aiomatic_Chatbot_Settings['presence_penalty'];
    }
    if (isset($aiomatic_Chatbot_Settings['frequency_penalty']) && $aiomatic_Chatbot_Settings['frequency_penalty'] != '' && $frequency == '') 
    {
        $frequency = $aiomatic_Chatbot_Settings['frequency_penalty'];
    }
    if (isset($aiomatic_Chatbot_Settings['instant_response']) && $aiomatic_Chatbot_Settings['instant_response'] != '' && $instant_response == '') 
    {
        $instant_response = $aiomatic_Chatbot_Settings['instant_response'];
    }
    if (isset($aiomatic_Chatbot_Settings['assistant_id']) && $aiomatic_Chatbot_Settings['assistant_id'] != '' && $assistant_id == '') 
    {
        $assistant_id = $aiomatic_Chatbot_Settings['assistant_id'];
    }
    if (isset($aiomatic_Chatbot_Settings['enable_vision']) && $aiomatic_Chatbot_Settings['enable_vision'] != '' && $enable_vision == '') 
    {
        $enable_vision = $aiomatic_Chatbot_Settings['enable_vision'];
    }
    if (isset($aiomatic_Chatbot_Settings['chat_preppend_text']) && $aiomatic_Chatbot_Settings['chat_preppend_text'] != '' && $chat_preppend_text == '') 
    {
        $chat_preppend_text = $aiomatic_Chatbot_Settings['chat_preppend_text'];
    }
    if (isset($aiomatic_Chatbot_Settings['ai_message_preppend']) && $aiomatic_Chatbot_Settings['ai_message_preppend'] != '' && $ai_message_preppend == '') 
    {
        $ai_message_preppend = $aiomatic_Chatbot_Settings['ai_message_preppend'];
    }
    if (isset($aiomatic_Chatbot_Settings['user_message_preppend']) && $aiomatic_Chatbot_Settings['user_message_preppend'] != '' && $user_message_preppend == '') 
    {
        $user_message_preppend = $aiomatic_Chatbot_Settings['user_message_preppend'];
    }
    if (isset($aiomatic_Chatbot_Settings['ai_first_message']) && $aiomatic_Chatbot_Settings['ai_first_message'] != '' && $ai_first_message == '') 
    {
        $ai_first_message = $aiomatic_Chatbot_Settings['ai_first_message'];
    }
    if (isset($aiomatic_Chatbot_Settings['chat_mode']) && $aiomatic_Chatbot_Settings['chat_mode'] != '' && $chat_mode == '') 
    {
        $chat_mode = $aiomatic_Chatbot_Settings['chat_mode'];
    }
    if (isset($aiomatic_Chatbot_Settings['user_token_cap_per_day']) && $aiomatic_Chatbot_Settings['user_token_cap_per_day'] != '' && $user_token_cap_per_day == '') 
    {
        $user_token_cap_per_day = $aiomatic_Chatbot_Settings['user_token_cap_per_day'];
    }
    if (isset($aiomatic_Chatbot_Settings['persistent']) && $aiomatic_Chatbot_Settings['persistent'] != '' && $persistent == '') 
    {
        $persistent = $aiomatic_Chatbot_Settings['persistent'];
    }
    if (isset($aiomatic_Chatbot_Settings['persistent_guests']) && $aiomatic_Chatbot_Settings['persistent_guests'] != '' && $persistent_guests == '') 
    {
        $persistent_guests = $aiomatic_Chatbot_Settings['persistent_guests'];
    }
    if (isset($aiomatic_Chatbot_Settings['prompt_editable']) && $aiomatic_Chatbot_Settings['prompt_editable'] != '' && $prompt_editable == '') 
    {
        $prompt_editable = $aiomatic_Chatbot_Settings['prompt_editable'];
    }
    if (isset($aiomatic_Chatbot_Settings['prompt_templates']) && $aiomatic_Chatbot_Settings['prompt_templates'] != '' && $prompt_templates == '') 
    {
        $prompt_templates = $aiomatic_Chatbot_Settings['prompt_templates'];
    }
    if (isset($aiomatic_Chatbot_Settings['placeholder']) && $aiomatic_Chatbot_Settings['placeholder'] != '' && $placeholder == '') 
    {
        $placeholder = $aiomatic_Chatbot_Settings['placeholder'];
    }
    if (isset($aiomatic_Chatbot_Settings['submit']) && $aiomatic_Chatbot_Settings['submit'] != '' && $submit == '') 
    {
        $submit = $aiomatic_Chatbot_Settings['submit'];
    }
    if (isset($aiomatic_Chatbot_Settings['submit_image']) && $aiomatic_Chatbot_Settings['submit_image'] != '' && $submit_image == '') 
    {
        $submit_image = $aiomatic_Chatbot_Settings['submit_image'];
    }
    if (isset($aiomatic_Chatbot_Settings['submit_full']) && $aiomatic_Chatbot_Settings['submit_full'] != '' && $submit_full == '') 
    {
        $submit_full = $aiomatic_Chatbot_Settings['submit_full'];
    }
    if (isset($aiomatic_Chatbot_Settings['window_location']) && $aiomatic_Chatbot_Settings['window_location'] != '' && $window_location == '') 
    {
        $window_location = $aiomatic_Chatbot_Settings['window_location'];
    }
    if (isset($aiomatic_Chatbot_Settings['ai_role']) && $aiomatic_Chatbot_Settings['ai_role'] != '' && $ai_role == '')
    {
        $ai_role = $aiomatic_Chatbot_Settings['ai_role'];
    }
    if (isset($aiomatic_Chatbot_Settings['ai_avatar']) && $aiomatic_Chatbot_Settings['ai_avatar'] != '' && $ai_avatar == '')
    {
        $ai_avatar = $aiomatic_Chatbot_Settings['ai_avatar'];
    }
    if (isset($aiomatic_Chatbot_Settings['user_avatar']) && $aiomatic_Chatbot_Settings['user_avatar'] != '' && $user_avatar == '')
    {
        $user_avatar = $aiomatic_Chatbot_Settings['user_avatar'];
    }
    if (isset($aiomatic_Chatbot_Settings['compliance']) && $aiomatic_Chatbot_Settings['compliance'] != '' && $compliance == '')
    {
        $compliance = $aiomatic_Chatbot_Settings['compliance'];
    }
    if (isset($aiomatic_Chatbot_Settings['bubble_width']) && $aiomatic_Chatbot_Settings['bubble_width'] != '' && $bubble_width == '')
    {
        $bubble_width = $aiomatic_Chatbot_Settings['bubble_width'];
    }
    elseif ($bubble_width == '')
    {
        $bubble_width = 'full';
    }
    if (isset($aiomatic_Chatbot_Settings['bubble_alignment']) && $aiomatic_Chatbot_Settings['bubble_alignment'] != '' && $bubble_alignment == '')
    {
        $bubble_alignment = $aiomatic_Chatbot_Settings['bubble_alignment'];
    }
    elseif ($bubble_alignment == '')
    {
        $bubble_alignment = 'left';
    }
    if (isset($aiomatic_Chatbot_Settings['bubble_user_alignment']) && $aiomatic_Chatbot_Settings['bubble_user_alignment'] != '' && $bubble_user_alignment == '')
    {
        $bubble_user_alignment = $aiomatic_Chatbot_Settings['bubble_user_alignment'];
    }
    elseif ($bubble_user_alignment == '')
    {
        $bubble_user_alignment = 'right';
    }
    if (isset($aiomatic_Chatbot_Settings['show_ai_avatar']) && $aiomatic_Chatbot_Settings['show_ai_avatar'] != '' && $show_ai_avatar == '')
    {
        $show_ai_avatar = $aiomatic_Chatbot_Settings['show_ai_avatar'];
    }
    if (isset($aiomatic_Chatbot_Settings['show_user_avatar']) && $aiomatic_Chatbot_Settings['show_user_avatar'] != '' && $show_user_avatar == '')
    {
        $show_user_avatar = $aiomatic_Chatbot_Settings['show_user_avatar'];
    }
    if (isset($aiomatic_Chatbot_Settings['select_prompt']) && $aiomatic_Chatbot_Settings['select_prompt'] != '' && $select_prompt == '')
    {
        $select_prompt = $aiomatic_Chatbot_Settings['select_prompt'];
    }
    if(empty($select_prompt))
    {
        $select_prompt = esc_html__("Please select a prompt", 'aiomatic-automatic-ai-content-writer');
    }
    if($ai_message_preppend == '' && $ai_name != '')
    {
        $ai_message_preppend = $ai_name;
    }
    if(!empty($assistant_id) && is_numeric($assistant_id))
    {
        $my_post = array();
        $my_post['post__in'] = array($assistant_id);
        $my_post['post_type'] = 'aiomatic_assistants';
        $assistant = get_posts($my_post);
        if($assistant !== null && !empty($assistant))
        {
            $assistant = $assistant[0];
            $ai_message_preppend = $assistant->post_title;
            $chat_preppend_text = $assistant->post_content;
            $message = get_post_meta($assistant->ID, '_assistant_first_message', true);
            $ai_first_message = $message;
            $ai_role = $assistant->post_excerpt;
            $ai_avatar = get_post_thumbnail_id($assistant->ID);
        }
    }
    else
    {
        if(!empty($ai_persona) && is_numeric($ai_persona))
        {
            $my_post = array();
            $my_post['post__in'] = array($ai_persona);
            $my_post['post_type'] = 'aiomatic_personas';
            $persona = get_posts($my_post);
            if($persona !== null && !empty($persona))
            {
                $persona = $persona[0];
                $ai_message_preppend = $persona->post_title;
                $chat_preppend_text = $persona->post_content;
                $message = get_post_meta($persona->ID, '_persona_first_message', true);
                $newmodel = get_post_meta($persona->ID, '_persona_model', true);
                if(!empty($newmodel))
                {
                    $model = $newmodel;
                }
                $ai_first_message = $message;
                $ai_role = $persona->post_excerpt;
                $ai_avatar = get_post_thumbnail_id($persona->ID);
            }
        }
    }
    $avatar_src = '';
    $avatar_url = '';
    if(is_numeric($ai_avatar))
    {
        $att_src = wp_get_attachment_image_src( $ai_avatar, 'thumbnail', false );
        if ( $att_src )
        {
            $avatar_src = '<img alt="Avatar" class="openai-chat-avatar" src="' . esc_url($att_src[0]) . '">';
            $avatar_url = $att_src[0];
        }
    }
    if(empty($ai_avatar) && $show_ai_avatar == 'show')
    {
        $avatar_url = plugins_url('images/placeholder.jpg', __FILE__);
    }
    $avatar_url_user = '';
    if (isset($aiomatic_Chatbot_Settings['user_account_avatar']) && $aiomatic_Chatbot_Settings['user_account_avatar'] == 'on' && is_user_logged_in())
    {
        $current_user_id = get_current_user_id();
        $user_avatar_image = get_avatar_url($current_user_id);
        if ($user_avatar_image !== false) 
        {
            $avatar_url_user = $user_avatar_image;
        } 
        else 
        {
            if(is_numeric($user_avatar))
            {
                $att_src_user = wp_get_attachment_image_src( $user_avatar, 'thumbnail', false );
                if ( $att_src_user )
                {
                    $avatar_url_user = $att_src_user[0];
                }
            }
        }
    } 
    else 
    {
        if(is_numeric($user_avatar))
        {
            $att_src_user = wp_get_attachment_image_src( $user_avatar, 'thumbnail', false );
            if ( $att_src_user )
            {
                $avatar_url_user = $att_src_user[0];
            }
        }
    }
    if(aiomatic_endsWith(trim($user_message_preppend), ':'))
    {
        $user_message_preppend = trim(trim($user_message_preppend), ':');
    }
    if(aiomatic_endsWith(trim($ai_message_preppend), ':'))
    {
        $ai_message_preppend = trim(trim($ai_message_preppend), ':');
    }
    if(!empty($user_message_preppend))
    {
        $user_message_preppend .= ': ';
    }
    if(!empty($ai_message_preppend))
    {
        $ai_message_preppend .= ': ';
    }
    if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
    {
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['not_show_urls']) && trim($aiomatic_Chatbot_Settings['not_show_urls']) != '') 
        {
            $no_show_urls = preg_split('/\r\n|\r|\n/', trim($aiomatic_Chatbot_Settings['not_show_urls']));
            $no_show_urls = array_filter($no_show_urls);
            if(count($no_show_urls) > 0)
            {
                global $wp;
                $current_url = home_url( $wp->request );
                foreach($no_show_urls as $nsurl)
                {
                    if(rtrim($current_url, '/') == rtrim(trim($nsurl), '/'))
                    {
                        return '';
                    }
                }
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['only_show_urls']) && trim($aiomatic_Chatbot_Settings['only_show_urls']) != '') 
        {
            $only_show_urls = preg_split('/\r\n|\r|\n/', trim($aiomatic_Chatbot_Settings['only_show_urls']));
            $only_show_urls = array_filter($only_show_urls);
            if(count($only_show_urls) > 0)
            {
                $url_found = false;
                global $wp;
                $current_url = home_url( $wp->request );
                foreach($only_show_urls as $nsurl)
                {
                    if(rtrim($current_url, '/') == rtrim(trim($nsurl), '/'))
                    {
                        $url_found = true;
                    }
                }
                if($url_found === false)
                {
                    return '';
                }
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['never_show']) && is_array($aiomatic_Chatbot_Settings['never_show'])) 
        {
            $this_day = date('l');
            if(in_array($this_day, $aiomatic_Chatbot_Settings['never_show']))
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['show_content_wp']) && is_array($aiomatic_Chatbot_Settings['show_content_wp']) && !empty($aiomatic_Chatbot_Settings['show_content_wp'])) 
        {
            $post_chars = aiomatic_get_post_characteristics();
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['show_content_wp'] as $showme)
            {
                if(in_array($showme, $post_chars))
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == false)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['no_show_content_wp']) && is_array($aiomatic_Chatbot_Settings['no_show_content_wp']) && !empty($aiomatic_Chatbot_Settings['no_show_content_wp'])) 
        {
            $post_chars = aiomatic_get_post_characteristics();
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['no_show_content_wp'] as $showme)
            {
                if(in_array($showme, $post_chars))
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == true)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['no_show_locales']) && is_array($aiomatic_Chatbot_Settings['no_show_locales']) && !empty($aiomatic_Chatbot_Settings['no_show_locales'])) 
        {
            $locale   = get_user_locale();
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['no_show_locales'] as $showme)
            {
                if($showme == $locale)
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == true)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['show_locales']) && is_array($aiomatic_Chatbot_Settings['show_locales']) && !empty($aiomatic_Chatbot_Settings['show_locales'])) 
        {
            $locale   = get_user_locale();
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['show_locales'] as $showme)
            {
                if($showme == $locale)
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == false)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['no_show_roles']) && is_array($aiomatic_Chatbot_Settings['no_show_roles']) && !empty($aiomatic_Chatbot_Settings['no_show_roles'])) 
        {
            $user   = wp_get_current_user();
            $fnd = false;
            if ( null !== $user ) 
            { 
                foreach ( $user->roles as $role ) {
                    if ( in_array( $role, $aiomatic_Chatbot_Settings['no_show_roles'], true ) ) {
                        $fnd = true;
                        break;
                    }
                }
                if($fnd == true)
                {
                    return '';
                }
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['show_roles']) && is_array($aiomatic_Chatbot_Settings['show_roles']) && !empty($aiomatic_Chatbot_Settings['show_roles'])) 
        {
            $user   = wp_get_current_user();
            $fnd = false;
            if ( null !== $user ) 
            { 
                foreach ( $user->roles as $role ) {
                    if ( in_array( $role, $aiomatic_Chatbot_Settings['show_roles'], true ) ) {
                        $fnd = true;
                        break;
                    }
                }
            }
            if($fnd == false)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['no_show_devices']) && is_array($aiomatic_Chatbot_Settings['no_show_devices']) && !empty($aiomatic_Chatbot_Settings['no_show_devices'])) 
        {
            require_once(dirname(__FILE__) . "/res/mobile-detect.php");
            $fnd = false;
            $detect = new AiomaticMobileDetect;
            $device = 'desktop';
            if ( $detect->isTablet() ) {
                $device = 'tablet';
            }
            if ( $detect->isMobile() && ! $detect->isTablet() ) {
                $device = 'mobile';
            }
            if ( in_array( $device, $aiomatic_Chatbot_Settings['no_show_devices'], true ) ) 
            {
                $fnd = true;
            }
            if($fnd == true)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['show_devices']) && is_array($aiomatic_Chatbot_Settings['show_devices']) && !empty($aiomatic_Chatbot_Settings['show_devices'])) 
        {
            require_once(dirname(__FILE__) . "/res/mobile-detect.php");
            $fnd = false;
            $detect = new AiomaticMobileDetect;
            $device = 'desktop';
            if ( $detect->isTablet() ) {
                $device = 'tablet';
            }
            if ( $detect->isMobile() && ! $detect->isTablet() ) {
                $device = 'mobile';
            }
            if ( in_array( $device, $aiomatic_Chatbot_Settings['show_devices'], true ) ) 
            {
                $fnd = true;
            }
            if($fnd == false)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['no_show_oses']) && is_array($aiomatic_Chatbot_Settings['no_show_oses']) && !empty($aiomatic_Chatbot_Settings['no_show_oses'])) 
        {
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['no_show_oses'] as $showme)
            {
                if ( aiomatic_detectOS($showme) ) 
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == true)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['show_oses']) && is_array($aiomatic_Chatbot_Settings['show_oses']) && !empty($aiomatic_Chatbot_Settings['show_oses'])) 
        {
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['show_oses'] as $showme)
            {
                if ( aiomatic_detectOS($showme) ) 
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == false)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['no_show_browsers']) && is_array($aiomatic_Chatbot_Settings['no_show_browsers']) && !empty($aiomatic_Chatbot_Settings['no_show_browsers'])) 
        {
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['no_show_browsers'] as $showme)
            {
                if ( aiomatic_detectBrowser($showme) ) 
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == true)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['show_browsers']) && is_array($aiomatic_Chatbot_Settings['show_browsers']) && !empty($aiomatic_Chatbot_Settings['show_browsers'])) 
        {
            $fnd = false;
            foreach($aiomatic_Chatbot_Settings['show_browsers'] as $showme)
            {
                if ( aiomatic_detectBrowser($showme) ) 
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == false)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['show_ips']) && !empty($aiomatic_Chatbot_Settings['show_ips'])) 
        {
            $fnd = false;
            $sips = preg_split('/\r\n|\r|\n/', $aiomatic_Chatbot_Settings['show_ips']);
            foreach($sips as $showme)
            {
                if ( aiomatic_passIPs($showme) ) 
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == false)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['no_show_ips']) && !empty($aiomatic_Chatbot_Settings['no_show_ips'])) 
        {
            $fnd = false;
            $sips = preg_split('/\r\n|\r|\n/', $aiomatic_Chatbot_Settings['no_show_ips']);
            foreach($sips as $showme)
            {
                if ( aiomatic_passIPs($showme) ) 
                {
                    $fnd = true;
                    break;
                }
            }
            if($fnd == true)
            {
                return '';
            }
        }
        if ($disable_filters != '1' && isset($aiomatic_Chatbot_Settings['min_time']) && $aiomatic_Chatbot_Settings['min_time'] != '' && isset($aiomatic_Chatbot_Settings['max_time']) && $aiomatic_Chatbot_Settings['max_time'] != '') 
        {
            $always_show = false;
            if (isset($aiomatic_Chatbot_Settings['always_show']) && is_array($aiomatic_Chatbot_Settings['always_show'])) 
            {
                $this_day = date('l');
                if(in_array($this_day, $aiomatic_Chatbot_Settings['always_show']))
                {
                    $always_show = true;
                }
            }
            if($always_show === false)
            {
                $exit = true;
                $mytime = date("H:i");
                $min_time = $aiomatic_Chatbot_Settings['min_time'];
                $max_time = $aiomatic_Chatbot_Settings['max_time'];
                $date1 = DateTime::createFromFormat('H:i', $mytime);
                $date2 = DateTime::createFromFormat('H:i', $min_time);
                $date3 = DateTime::createFromFormat('H:i', $max_time);
                if ($date1 > $date2 && $date1 < $date3)
                {
                    $exit = false;
                }
                if($exit == true)
                {
                    return '';
                }
            }
        }
        if (isset($aiomatic_Chatbot_Settings['window_width']) && $aiomatic_Chatbot_Settings['window_width'] != '') 
        {
            $window_width = $aiomatic_Chatbot_Settings['window_width'];
        }
        else
        {
            $window_width = '460px';
        }
    }
    else
    {
        $window_width = '';
    }
    if(empty($window_location))
    {
        $window_location = 'bottom-right';
    }
    if($window_location != 'bottom-right' && $window_location != 'bottom-left' && $window_location != 'top-right' && $window_location != 'top-left')
    {
        $window_location = 'bottom-right';
    }
    if(empty($submit))
    {
        $submit = 'Submit';
    }
    if(empty($model))
    {
        $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
    }
    if(empty($instant_response))
    {
        $instant_response = 'false';
    }
    if($instant_response == 'true')
    {
        $instant_response = 'on';
    }
    if($instant_response == 'false')
    {
        $instant_response = 'off';
    }
    if(empty($frequency))
    {
        $frequency = '0';
    }
    if(empty($store_data))
    {
        $store_data = '';
    }
    if(empty($presence))
    {
        $presence = '0';
    }
    if(empty($top_p))
    {
        $top_p = '1';
    }
    if(empty($temp))
    {
        $temp = '0.8';
    }
    if (isset($aiomatic_Chatbot_Settings['page_load_chat']) && $aiomatic_Chatbot_Settings['page_load_chat'] == 'on' ) 
    {
        $autoload = '1';
    }
    else
    {
        $autoload = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['page_load_chat_delay']) && $aiomatic_Chatbot_Settings['page_load_chat_delay'] != '' ) 
    {
        $autoload_delay = intval($aiomatic_Chatbot_Settings['page_load_chat_delay']);
    }
    else
    {
        $autoload_delay = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_load_chat_urls']) && $aiomatic_Chatbot_Settings['page_load_chat_urls'] != '' ) 
    {
        $page_load_chat_urls = $aiomatic_Chatbot_Settings['page_load_chat_urls'];
    }
    else
    {
        $page_load_chat_urls = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_exit_chat_urls']) && $aiomatic_Chatbot_Settings['page_exit_chat_urls'] != '' ) 
    {
        $page_exit_chat_urls = $aiomatic_Chatbot_Settings['page_exit_chat_urls'];
    }
    else
    {
        $page_exit_chat_urls = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_exit_chat_message']) && $aiomatic_Chatbot_Settings['page_exit_chat_message'] != '' ) 
    {
        $page_exit_chat_message = $aiomatic_Chatbot_Settings['page_exit_chat_message'];
    }
    else
    {
        $page_exit_chat_message = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_scroll_chat_urls']) && $aiomatic_Chatbot_Settings['page_scroll_chat_urls'] != '' ) 
    {
        $page_scroll_chat_urls = $aiomatic_Chatbot_Settings['page_scroll_chat_urls'];
    }
    else
    {
        $page_scroll_chat_urls = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_scroll_chat_message']) && $aiomatic_Chatbot_Settings['page_scroll_chat_message'] != '' ) 
    {
        $page_scroll_chat_message = $aiomatic_Chatbot_Settings['page_scroll_chat_message'];
    }
    else
    {
        $page_scroll_chat_message = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_inactive_chat_urls']) && $aiomatic_Chatbot_Settings['page_inactive_chat_urls'] != '' ) 
    {
        $page_inactive_chat_urls = $aiomatic_Chatbot_Settings['page_inactive_chat_urls'];
    }
    else
    {
        $page_inactive_chat_urls = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_inactive_chat_message']) && $aiomatic_Chatbot_Settings['page_inactive_chat_message'] != '' ) 
    {
        $page_inactive_chat_message = $aiomatic_Chatbot_Settings['page_inactive_chat_message'];
    }
    else
    {
        $page_inactive_chat_message = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_load_chat_message']) && $aiomatic_Chatbot_Settings['page_load_chat_message'] != '' ) 
    {
        $page_load_chat_message = $aiomatic_Chatbot_Settings['page_load_chat_message'];
    }
    else
    {
        $page_load_chat_message = '';
    }
    if (isset($aiomatic_Chatbot_Settings['page_scroll_chat_percent']) && $aiomatic_Chatbot_Settings['page_scroll_chat_percent'] != '' ) 
    {
        $page_scroll_chat_percent = intval($aiomatic_Chatbot_Settings['page_scroll_chat_percent']);
    }
    else
    {
        $page_scroll_chat_percent = 50;
    }
    if (isset($aiomatic_Chatbot_Settings['page_inactive_chat_time']) && $aiomatic_Chatbot_Settings['page_inactive_chat_time'] != '' ) 
    {
        $page_inactive_chat_time = intval($aiomatic_Chatbot_Settings['page_inactive_chat_time']);
    }
    else
    {
        $page_inactive_chat_time = 30000;
    }
    if (isset($aiomatic_Chatbot_Settings['page_load_chat_once']) && $aiomatic_Chatbot_Settings['page_load_chat_once'] == 'on' )
    {
        $page_load_chat_once = '1';
    }
    else
    {
        $page_load_chat_once = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['page_exit_chat_once']) && $aiomatic_Chatbot_Settings['page_exit_chat_once'] == 'on' )
    {
        $page_exit_chat_once = '1';
    }
    else
    {
        $page_exit_chat_once = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['page_scroll_chat_once']) && $aiomatic_Chatbot_Settings['page_scroll_chat_once'] == 'on' )
    {
        $page_scroll_chat_once = '1';
    }
    else
    {
        $page_scroll_chat_once = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['page_inactive_chat_once']) && $aiomatic_Chatbot_Settings['page_inactive_chat_once'] == 'on' )
    {
        $page_inactive_chat_once = '1';
    }
    else
    {
        $page_inactive_chat_once = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['page_exit_chat']) && $aiomatic_Chatbot_Settings['page_exit_chat'] == 'on' )
    {
        $page_exit_chat = '1';
    }
    else
    {
        $page_exit_chat = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['page_scroll_chat']) && $aiomatic_Chatbot_Settings['page_scroll_chat'] == 'on' )
    {
        $page_scroll_chat = '1';
    }
    else
    {
        $page_scroll_chat = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['page_inactive_chat']) && $aiomatic_Chatbot_Settings['page_inactive_chat'] == 'on' )
    {
        $page_inactive_chat = '1';
    }
    else
    {
        $page_inactive_chat = '0';
    }
    if (isset($aiomatic_Chatbot_Settings['free_voice']) && $aiomatic_Chatbot_Settings['free_voice'] != '') 
    {
        $free_voice = $aiomatic_Chatbot_Settings['free_voice'];
    }
    else
    {
        $free_voice = 'Google US English;en-US';
    }
    if (isset($aiomatic_Chatbot_Settings['chatbot_text_speech']) && $aiomatic_Chatbot_Settings['chatbot_text_speech'] != 'off' && $aiomatic_Chatbot_Settings['chatbot_text_speech'] != '' ) 
    {
        if($chatbot_text_speech == '')
        {            
            $chatbot_text_speech = $aiomatic_Chatbot_Settings['chatbot_text_speech'];
        }
    }
    else
    {
        if($chatbot_text_speech == '')
        {
            $chatbot_text_speech = 'off';
        }
    }
    if (isset($aiomatic_Chatbot_Settings['store_data']) && trim($aiomatic_Chatbot_Settings['store_data']) == 'on' && empty($store_data))
    {
        $store_data = 'on';
    }
    if($chat_mode == 'images' || $chat_mode == 'image')
    {
        $user_id = '0';
        $chat_history = '';
        if(!empty($user_token_cap_per_day) || ($persistent != 'off' && $persistent != '0' && $persistent != ''))
        {
            $user_id = get_current_user_id();
            if($user_id == 0 && ($persistent_guests == 'on' || $persistent_guests == '1'))
            {
                $user_id = aiomatic_get_the_user_ip();
            }
            if(($persistent != 'off' && $persistent != 'logs' && $persistent != 'history' && $persistent != 'vector' && $persistent != '0' && $persistent != '') && $user_id != 0)
            {
                if(is_numeric($user_id))
                {
                    $chat_history = get_user_meta($user_id, 'aiomatic_chat_history_' . $persistent, true);
                    if(empty($chat_history))
                    {
                        $chat_history = '';
                    }
                }
                else
                {
                    $chat_history = get_transient('aiomatic_chat_history_' . $persistent . '_' . $user_id);
                    if(empty($chat_history))
                    {
                        $chat_history = '';
                    }
                }
            }
        }
        $enable_moderation = '0';
        if (isset($aiomatic_Chatbot_Settings['enable_moderation']) && $aiomatic_Chatbot_Settings['enable_moderation'] == 'on') 
        {
            $enable_moderation = '1';
        }
        if (isset($aiomatic_Chatbot_Settings['moderation_model']) && $aiomatic_Chatbot_Settings['moderation_model'] == 'on') 
        {
            $moderation_model = $aiomatic_Chatbot_Settings['moderation_model'];
        }
        else
        {
            $moderation_model = 'text-moderation-stable';
        }
        if (isset($aiomatic_Chatbot_Settings['flagged_message']) && $aiomatic_Chatbot_Settings['flagged_message'] == 'on') 
        {
            $flagged_message = $aiomatic_Chatbot_Settings['flagged_message'];
        }
        else
        {
            $flagged_message = 'Your message has been flagged as potentially harmful or inappropriate. Please review your language and content to ensure it aligns with our values of respect and sensitivity towards others. Thank you for your cooperation.';
        }
        if (isset($aiomatic_Chatbot_Settings['enable_copy']) && $aiomatic_Chatbot_Settings['enable_copy'] == 'on') 
        {
            $enable_copy = $aiomatic_Chatbot_Settings['enable_copy'];
        }
        else
        {
            $enable_copy = '0';
        }
        if (isset($aiomatic_Chatbot_Settings['scroll_bot']) && $aiomatic_Chatbot_Settings['scroll_bot'] == 'on') 
        {
            $scroll_bot = $aiomatic_Chatbot_Settings['scroll_bot'];
        }
        else
        {
            $scroll_bot = '0';
        }
        $no_empty = '';
        if (isset($aiomatic_Chatbot_Settings['no_empty']) && trim($aiomatic_Chatbot_Settings['no_empty']) == 'on' && empty($input_text))
        {
            $no_empty = '1';
        }
        if(!function_exists('is_plugin_active'))
        {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $all_ok = true;
        if (!is_plugin_active('aiomatic-extension-pdf-files/aiomatic-extension-pdf-files.php')) 
        {
            $all_ok = false;
        }
        if (isset($aiomatic_Chatbot_Settings['chat_download_format']) && $aiomatic_Chatbot_Settings['chat_download_format'] != '' && $all_ok === true) 
        {
            $chat_download_format = $aiomatic_Chatbot_Settings['chat_download_format'];
        }
        else
        {
            $chat_download_format = 'txt';
        }
        $name = md5(get_bloginfo());
        wp_enqueue_script($name . 'openai-chat-images-ajax', plugins_url('scripts/openai-chat-images-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
        wp_localize_script($name . 'openai-chat-images-ajax', 'aiomatic_chat_image_ajax_object' . $chatid, array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('openai-ajax-images-nonce'),
            'persistent' => $persistent,
            'persistentnonce' => wp_create_nonce('openai-persistent-nonce'),
            'user_token_cap_per_day' => $user_token_cap_per_day,
            'user_id' => $user_id,
		    'moderation_nonce' => wp_create_nonce('openai-moderation-nonce'),
            'enable_moderation' => $enable_moderation,
            'moderation_model' => $moderation_model,
            'flagged_message' => $flagged_message,
            'enable_copy' => $enable_copy,
            'scroll_bot' => $scroll_bot,
            'no_empty' => $no_empty,
            'chatid' => $chatid,
            'autoload' => $autoload,
            'autoload_delay' => $autoload_delay,
            'chat_download_format' => $chat_download_format,
            'bubble_alignment' => $bubble_alignment,
            'bubble_user_alignment' => $bubble_user_alignment,
            'avatar_url_user' => $avatar_url_user,
            'avatar_url' => $avatar_url,
            'show_user_avatar' => $show_user_avatar,
            'show_ai_avatar' => $show_ai_avatar
        ));
        if($chat_download_format == 'pdf')
        {
            wp_enqueue_script($name . 'pdf-downloader', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array(), AIOMATIC_MAJOR_VERSION);
            wp_enqueue_script($name . 'html-canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), AIOMATIC_MAJOR_VERSION);
        }
        wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
        $css_added = false;
        $reg_css_code = '.aiomatic_chat_history{';
        if($font_size != '')
        {
            $reg_css_code .= 'font-size:' . $font_size . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['font_size']) && $aiomatic_Chatbot_Settings['font_size'] != '') 
            {
                $reg_css_code .= 'font-size:' . $aiomatic_Chatbot_Settings['font_size'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($height != '')
        {
            $reg_css_code .= 'height:' . $height . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['height']) && $aiomatic_Chatbot_Settings['height'] != '') 
            {
                $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($minheight != '')
        {
            $reg_css_code .= 'min-height:' . $minheight . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['minheight']) && $aiomatic_Chatbot_Settings['minheight'] != '') 
            {
                $reg_css_code .= 'min-height:' . $aiomatic_Chatbot_Settings['minheight'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.openai-ai-form{border-radius: 30px;';
        if($general_background != '')
        {
            $reg_css_code .= 'background-color:' . $general_background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['general_background']) && $aiomatic_Chatbot_Settings['general_background'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['general_background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic_chat_history_log{';
        if($font_size != '')
        {
            $reg_css_code .= 'font-size:' . $font_size . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['font_size']) && $aiomatic_Chatbot_Settings['font_size'] != '') 
            {
                $reg_css_code .= 'font-size:' . $aiomatic_Chatbot_Settings['font_size'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($height != '')
        {
            $reg_css_code .= 'height:' . $height . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['height']) && $aiomatic_Chatbot_Settings['height'] != '') 
            {
                $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($background != '')
        {
            $reg_css_code .= 'background-color:' . $background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($minheight != '')
        {
            $reg_css_code .= 'min-height:' . $minheight . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['minheight']) && $aiomatic_Chatbot_Settings['minheight'] != '') 
            {
                $reg_css_code .= 'min-height:' . $aiomatic_Chatbot_Settings['minheight'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.openai-ai-image-form{
border-radius: 30px;
max-width: 800px;
margin: 0 auto;
width: 100% !important;';
if($no_padding != 'on')
{
$reg_css_code .= '
box-sizing: border-box;
padding-right: 20px;
padding-left: 20px;';
}
        if($general_background != '')
        {
            $reg_css_code .= 'background-color:' . $general_background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['general_background']) && $aiomatic_Chatbot_Settings['general_background'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['general_background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($width != '')
        {
            $reg_css_code .= 'width:' . $width . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['width']) && $aiomatic_Chatbot_Settings['width'] != '') 
            {
                $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        if($bubble_width == 'full' || empty($bubble_width))
        {
            $reg_css_code .= '.ai-bubble{width:100%!important;}';
            $css_added = true;
        }
        elseif($bubble_width == 'auto')
        {
            $reg_css_code .= '.ai-bubble{width:auto!important;}';
            $css_added = true;
        }
        if($bubble_alignment == 'left' || empty($bubble_alignment))
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-left:unset!important;margin-right:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_alignment == 'right')
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-right:unset!important;margin-left:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_alignment == 'center')
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-left:auto!important;margin-right:auto!important;}';
            $css_added = true;
        }
        if($bubble_user_alignment == 'left')
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-left:unset!important;margin-right:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_user_alignment == 'right' || empty($bubble_user_alignment))
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-right:unset!important;margin-left:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_user_alignment == 'center')
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-left:auto!important;margin-right:auto!important;}';
            $css_added = true;
        }
        $reg_css_code .= '.ai-mine{';
        if($user_font_color != '')
        {
            $reg_css_code .= 'color:' . $user_font_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['user_font_color']) && $aiomatic_Chatbot_Settings['user_font_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['user_font_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($user_background_color != '')
        {
            $reg_css_code .= 'background-color:' . $user_background_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['user_background_color']) && $aiomatic_Chatbot_Settings['user_background_color'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['user_background_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
if(!empty($avatar_url))
{
    $css_added = true;
    $reg_css_code .= '.ai-avatar.ai-other {
    background-image: url("' . esc_url($avatar_url) . '");
    background-size: cover;
    background-position: center;
    margin-left: 3px;
    margin-right: 3px;
}';
}
if($additional_bots != '')
{
    $bot_list = explode(',', $additional_bots);
    $bot_list = array_map('trim', $bot_list);
    $bot_list = array_filter($bot_list);
    foreach($bot_list as $my_bot)
    {
        if(is_numeric($my_bot))
        {
            $bot_post = get_post($my_bot);
            if($bot_post != null)
            {
                if($bot_post->post_type === 'aiomatic_personas' || $bot_post->post_type === 'aiomatic_assistants')
                {
                    $att_src = get_the_post_thumbnail_url( $bot_post->ID, 'thumbnail' );
                    if ( empty($att_src) )
                    {
                        $att_src = plugins_url('images/placeholder.jpg', __FILE__);
                    }
                    $css_added = true;
                    $reg_css_code .= '.ai-avatar.ai-other.aiomatic-ainum-' . esc_html($bot_post->ID) . ' {
background-image: url("' . esc_url($att_src) . '");
background-size: cover;
background-position: center;
margin-left: 3px;
margin-right: 3px;
}';
                }
            }
        }
    }
}
if(!empty($avatar_url_user))
{
    $css_added = true;
    $reg_css_code .= '.ai-avatar.ai-mine {
        background-image: url("' . esc_url($avatar_url_user) . '");
        background-size: cover;
        background-position: center;
        margin-left: 3px;
        margin-right: 3px;
}';
}
        $reg_css_code .= '.ai-other{';
        if($ai_font_color != '')
        {
            $reg_css_code .= 'color:' . $ai_font_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['ai_font_color']) && $aiomatic_Chatbot_Settings['ai_font_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['ai_font_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($ai_background_color != '')
        {
            $reg_css_code .= 'background-color:' . $ai_background_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['ai_background_color']) && $aiomatic_Chatbot_Settings['ai_background_color'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['ai_background_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#aiomatic_chat_input' . $chatid . '{';
        if($background != '')
        {
            $reg_css_code .= 'background-color:' . $background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($input_border_color != '')
        {
            $reg_css_code .= 'border-color:' . $input_border_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['input_border_color']) && $aiomatic_Chatbot_Settings['input_border_color'] != '') 
            {
                $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['input_border_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($input_text_color != '')
        {
            $reg_css_code .= 'color:' . $input_text_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic-ai-input{';
        if($background != '')
        {
            $reg_css_code .= 'background-color:' . $background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($input_border_color != '')
        {
            $reg_css_code .= 'border-color:' . $input_border_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['input_border_color']) && $aiomatic_Chatbot_Settings['input_border_color'] != '') 
            {
                $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['input_border_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($input_text_color != '')
        {
            $reg_css_code .= 'color:' . $input_text_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}
#openai-persona-name' . $chatid . '{';
            if($persona_name_color != '')
            {
                $reg_css_code .= 'color:' . $persona_name_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
            else
            {
                if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
                {
                    $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_name_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                    $css_added = true;
                }
            }
            $reg_css_code .= '}
#openai-persona-role' . $chatid . '{';
            if($persona_role_color != '')
            {
                $reg_css_code .= 'color:' . $persona_role_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
            else
            {
                if (isset($aiomatic_Chatbot_Settings['persona_role_color']) && $aiomatic_Chatbot_Settings['persona_role_color'] != '') 
                {
                    $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_role_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                    $css_added = true;
                }
            }
            $reg_css_code .= '}
#aiomatic_chat_input' . $chatid . '::placeholder
{';
        
        if($input_placeholder_color != '')
        {
            $reg_css_code .= 'color:' . $input_placeholder_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['input_placeholder_color']) && $aiomatic_Chatbot_Settings['input_placeholder_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_placeholder_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}
.aiomatic-close-button{';
        if($submit_color != '')
        {
            $reg_css_code .= 'color:' . $submit_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{margin-top: 5px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
        $reg_css_code .= '#aiimagechatsubmitbut' . $chatid . '{margin-top: 5px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        if($submit_color != '')
        {
            $reg_css_code .= 'background-color:' . $submit_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($submit_text_color != '')
        {
            $reg_css_code .= 'color:' . $submit_text_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        if($voice_color_activated == '')
        {
            if (isset($aiomatic_Chatbot_Settings['voice_color_activated']) && $aiomatic_Chatbot_Settings['voice_color_activated'] != '') 
            {
                $voice_color_activated = $aiomatic_Chatbot_Settings['voice_color_activated'];
            }
        }
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{';
        if($voice_color != '')
        {
            $reg_css_code .= 'background-color:' . $voice_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['voice_color']) && $aiomatic_Chatbot_Settings['voice_color'] != '') 
            {
                $voice_color = $aiomatic_Chatbot_Settings['voice_color'];
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['voice_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-image-speech-button' . $chatid . '{';
        if($voice_color != '')
        {
            $reg_css_code .= 'background-color:' . $voice_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['voice_color']) && $aiomatic_Chatbot_Settings['voice_color'] != '') 
            {
                $voice_color = $aiomatic_Chatbot_Settings['voice_color'];
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['voice_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        if (isset($aiomatic_Chatbot_Settings['open_width']) && $aiomatic_Chatbot_Settings['open_width'] != '') 
        {
            $reg_css_code .= '.aiomatic-open-image{width:' . $aiomatic_Chatbot_Settings['open_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
            $css_added = true;
        }
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{margin-left:20px;}
#aiimagechatsubmitbut' . $chatid . '{';
        if($submit_color != '')
        {
            $reg_css_code .= 'background-color:' . $submit_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if($submit_text_color != '')
        {
            $reg_css_code .= 'color:' . $submit_text_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '') 
            {
                $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        $reg_css_code .= '}';
        if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
        {
            $reg_css_code .= 'form.aiomatic-window{';
            $reg_css_code .= 'display:none;';
            $reg_css_code .= '}';
            $css_added = true;
        }
        if($window_width != '')
        {
            preg_match_all('#(\d+)\s*px#i', $window_width, $zamatches);
            if(isset($zamatches[1][0]))
            {
                $myw = intval($zamatches[1][0]) + 100;
                $wwidth = $myw . 'px';
            }
            else
            {
                $wwidth = $window_width;
            }
            $reg_css_code .= '@media only screen and (min-width: ' . $wwidth . ') {form.aiomatic-window{';
            $reg_css_code .= 'width:' . $window_width . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'max-width:' . $window_width . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= '}}';
            $reg_css_code .= '@media only screen and (max-width: ' . $wwidth . ') {form.aiomatic-window{';
            $reg_css_code .= 'width:75%' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'max-width:75%' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= '}}';
            $css_added = true;
        }
        if($no_padding != 'on')
        {
        $reg_css_code .= '#openai-ai-chat-form-' . $chatid . '
{
  box-sizing: border-box;
  padding-right: 20px;
  padding-left: 20px;
}';
        }
$reg_css_code .= '.openai-mr-4
{
    margin-right: 1rem ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
}
.openai-chat-avatar
{
    border-radius: 50%;';
    if (isset($aiomatic_Chatbot_Settings['topavatar_width']) && $aiomatic_Chatbot_Settings['topavatar_width'] != '') 
    {
        $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['topavatar_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $css_added = true;
    }
    else
    {
        $reg_css_code .= 'width: 44px;';
    }
    if (isset($aiomatic_Chatbot_Settings['topavatar_height']) && $aiomatic_Chatbot_Settings['topavatar_height'] != '') 
    {
        $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['topavatar_height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $css_added = true;
    }
    else
    {
        $reg_css_code .= 'height: 44px;';
    }
    $reg_css_code .= 'clear: both;
    display: block;
    background: #E1F0FF;
    position: relative;
}
.ai-avatar{';
if (isset($aiomatic_Chatbot_Settings['convavatar_width']) && $aiomatic_Chatbot_Settings['convavatar_width'] != '') 
{
    $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['convavatar_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $reg_css_code .= 'min-width:' . $aiomatic_Chatbot_Settings['convavatar_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $css_added = true;
}
else
{
    $reg_css_code .= 'width: 40px;';
    $reg_css_code .= 'min-width: 40px;';
}
if (isset($aiomatic_Chatbot_Settings['convavatar_height']) && $aiomatic_Chatbot_Settings['convavatar_height'] != '') 
{
    $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['convavatar_height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $reg_css_code .= 'min-height:' . $aiomatic_Chatbot_Settings['convavatar_height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $css_added = true;
}
else
{
    $reg_css_code .= 'height: 40px;';
    $reg_css_code .= 'min-height: 40px;';
}
$reg_css_code .= '}
.openai-font-weight-bold
{
    font-weight: bold ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
}
.openai-card-header{';
    if (isset($aiomatic_Chatbot_Settings['width']) && $aiomatic_Chatbot_Settings['width'] != '') 
    {
        $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $reg_css_code .= 'max-width:' . $aiomatic_Chatbot_Settings['width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $css_added = true;
    }
    $reg_css_code .= '
}
#openai-chat-response' . $chatid . ' {
    padding-bottom: 5px;
    text-align:center;';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '}
.openai-file-document {';
    
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '
}
.openai-file-document:hover {
    transform: scale(1.1);
    ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'background-color:#f0f0f0' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '
    color: #333;
}
.aiomatic-vision-image
{
    max-width:300px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
    max-height:300px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
    display:block' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
}
.aiomatic-pdf-image {
    user-select: none;
    position: absolute;
    display: block;
    transform: scale(var(--ggs,1));
    width: 25px;
    overflow: hidden;
    border-radius: 2px;
}
.aiomatic-pdf-image {
    cursor: pointer;';
if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
{
    $reg_css_code .= 'right: 38px;';
}
else
{
    $reg_css_code .= 'right: 10px;';
}
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%);
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-file-image {
    user-select: none;
    position: absolute;
    display: block;
    transform: scale(var(--ggs,1));
    width: 25px;
    overflow: hidden;
    border-radius: 2px;
}
.aiomatic-file-image {
    cursor: pointer;';
    if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
    {
        if($all_ok)
        {
            $reg_css_code .= 'right: 66px;';
        }
        else
        {
            $reg_css_code .= 'right: 38px;';
        }
    }
    else
    {
        if($all_ok)
        {
            $reg_css_code .= 'right: 38px;';
        }
        else
        {
            $reg_css_code .= 'right: 10px;';
        }
    }
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%);
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-stop-image {
    user-select: none;
    position: absolute;
    transform: scale(var(--ggs,1));
    width: 25px;
    overflow: hidden;
    border-radius: 2px;
}
.aiomatic-show-logs {
    user-select: none;
    position: absolute;
    transform: scale(var(--ggs,1));
    width: 25px;
    overflow: hidden;
    border-radius: 2px;
}
.aiomatic-show-logs {
    cursor: pointer;';
    $enable_file_uploads = false;
    if (isset($aiomatic_Chatbot_Settings['enable_file_uploads']) && $aiomatic_Chatbot_Settings['enable_file_uploads'] != '' && $file_uploads == '') 
    {
        $file_uploads = $aiomatic_Chatbot_Settings['enable_file_uploads'];
    }
    if(!empty($assistant_id) && ($file_uploads == 'on' || $file_uploads == 'yes' || $file_uploads == '1' || $file_uploads == 'enable' || $file_uploads == 'enabled'))
    {
        $is_file_search = get_post_meta($assistant_id, '_assistant_tools', true);
        if(is_array($is_file_search))
        {
            foreach($is_file_search as $isfs)
            {
                if($isfs['type'] == 'file_search')
                {
                    $enable_file_uploads = true;
                    break;
                }
            }
        }
    }
if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 94px;';
        }
        else
        {
            $reg_css_code .= 'right: 66px;';
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 66px;';
        }
        else
        {
            $reg_css_code .= 'right: 38px;';
        }
    }
}
else
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 66px;';
        }
        else
        {
            $reg_css_code .= 'right: 38px;';
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 38px;';
        }
        else
        {
            $reg_css_code .= 'right: 10px;';
        }
    }
}
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%)!important;
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-stop-image {
    cursor: pointer;';
    $allow_stream_stop = false;
    if (isset($aiomatic_Chatbot_Settings['allow_stream_stop']) && $aiomatic_Chatbot_Settings['allow_stream_stop'] == 'on' && $persistent === 'history' && $assistant_id == '')
    {
        $allow_stream_stop = true;
    }
    $enable_file_uploads = false;
    if (isset($aiomatic_Chatbot_Settings['enable_file_uploads']) && $aiomatic_Chatbot_Settings['enable_file_uploads'] != '' && $file_uploads == '') 
    {
        $file_uploads = $aiomatic_Chatbot_Settings['enable_file_uploads'];
    }
    if(!empty($assistant_id) && ($file_uploads == 'on' || $file_uploads == 'yes' || $file_uploads == '1' || $file_uploads == 'enable' || $file_uploads == 'enabled'))
    {
        $is_file_search = get_post_meta($assistant_id, '_assistant_tools', true);
        if(is_array($is_file_search))
        {
            foreach($is_file_search as $isfs)
            {
                if($isfs['type'] == 'file_search')
                {
                    $enable_file_uploads = true;
                    break;
                }
            }
        }
    }
if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 122px;';
            }
            else
            {
                $reg_css_code .= 'right: 94px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 94px;';
            }
            else
            {
                $reg_css_code .= 'right: 66px;';
            }
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 94px;';
            }
            else
            {
                $reg_css_code .= 'right: 66px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 66px;';
            }
            else
            {
                $reg_css_code .= 'right: 38px;';
            }
        }
    }
}
else
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 94px;';
            }
            else
            {
                $reg_css_code .= 'right: 66px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 66px;';
            }
            else
            {
                $reg_css_code .= 'right: 38px;';
            }
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 66px;';
            }
            else
            {
                $reg_css_code .= 'right: 38px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 38px;';
            }
            else
            {
                $reg_css_code .= 'right: 10px;';
            }
        }
    }
}
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%)!important;
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-gg-image {
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}';

$reg_css_code .= '.openai-relative
{
    position: relative;
}
.openai-widget-user-name
{
    line-height: 1.8;
}
.cr_none
{
    display:none;
}
.cr_visible 
{
    display: block !important;
}
.cr_abs{position: absolute;}
.aiomatic-gg-image::after,
.aiomatic-gg-image::before {
    content: "";
    display: block;
    box-sizing: border-box;
    position: absolute;
    border: 2px solid
}
.aiomatic-gg-mute:hover {
    i:before {
        transform: scale(.8) translate(-3px, 0) rotate(42deg);		
    }
}
.aiomatic-hide {display:none!important;visibility:hidden}
.image_max_w_ai{width:100%;max-width:100%;}
.aiomatic-pdf-remove, .aiomatic-file-remove{
    font-size: 30px;
    justify-content: center;
    align-items: center;
    width: 18px;
    height: 18px;
    line-height: unset;
    font-family: Arial, serif;
    border-radius: 50%;
    font-weight: normal;
    padding: 0;
    margin: 0;
}
.aiomatic-loading-indicator {
    position: absolute;
    top: 5px;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}
.aiomatic-gg-trash::after,
.aiomatic-gg-trash::before {
    content: "";
    display: block;
    box-sizing: border-box;
    position: absolute
}
.aiomatic-gg-trash::after {
    background: currentColor;
    border-radius: 3px;
    width: 17px;
    height: 2px;
    top: -4px;
    left: -5px
}
.aiomatic-gg-trash::before {
    width: 10px;
    height: 4px;
    border: 2px solid;
    border-bottom: transparent;
    border-top-left-radius: 2px;
    border-top-right-radius: 2px;
    top: -7px;
    left: -1px
}
.aiomatic-gg-trash {
    box-sizing: border-box;
    position: relative;
    display: block;
    transform: scale(var(--ggs,1));
    width: 10px;
    height: 12px;
    border: 2px solid transparent;
    box-shadow:
        0 0 0 1px,
        inset -2px 0 0,
        inset 2px 0 0;
    border-bottom-left-radius: 1px;
    border-bottom-right-radius: 1px;
    margin-top: 4px;
}
.aiomatic-gg-mute {
    margin-left:10px;
    cursor: pointer;
    top: -1px;
    right: -3px;
    height: 20px; 
    width: 20px;  
    position: relative;
    overflow: hidden;
    display: inline-block;
}
.aiomatic-gg-image::after {
    transform: rotate(45deg);
    border-radius: 3px;
    width: 16px;
    height: 16px;
    top: 9px;
    left: 6px
}
.aiomatic-gg-image::before {
    width: 6px;
    height: 6px;
    border-radius: 100%;
    top: 2px;
    left: 2px
}
.aiomatic-cursor
{
    cursor: pointer;
}
.aiomatic-left-padding
{
    padding-left: 10px;
}
.aiomatic-gg-globe {
    top:-1px;
    position: relative;
    transform: scale(var(--ggs,1));
    width: 18px;
    border-radius: 22px;
}
.aiomatic-gg-globe::after,
.aiomatic-gg-globe::before {
    content: "";
    position: absolute;
    width: 8px;
    border-radius: 100%;
    top: -2px;
    left: 3px;
}
.aiomatic-gg-unmute {
    cursor: pointer;
    margin-left:10px;
    top: -1px;
    right: -3px;
    height: 20px; 
    width: 20px; 
    position: relative;
    overflow: hidden;
    display: inline-block;
}
.aiomatic-gg-image {
    box-sizing: border-box;
    position: absolute;
    display: block;
    transform: scale(var(--ggs,1));
    width: 20px;
    height: 16px;
    overflow: hidden;
    box-shadow: 0 0 0 2px;
    border-radius: 2px;
    cursor: pointer;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    padding: 10px;
    cursor: pointer;
}
.openai-file-document::after,
.openai-file-document::before {
    content: "";
    display: block;
    box-sizing: border-box;
    position: absolute;
}
.openai-file-document::before {
    background: currentColor;
    box-shadow: 0 4px 0, -6px -4px 0;
    left: 0;
    width: 10px;
    height: 2px;
    top: 8px;
}
.openai-file-document::after {
    width: 6px;
    height: 6px;
    border-left: 2px solid;
    border-bottom: 2px solid;
    right: -1px;
    top: -1px;
}
.openai-file-document
{
    cursor: pointer;
    box-sizing: border-box;
    position: relative;
    display: block;
    transform: scale(var(--ggs,1));
    width: 14px;
    height: 16px;
    border: 2px solid transparent;
    border-right: 0;
    border-top: 0;
    box-shadow: 0 0 0 1.3px;
    border-radius: 1px;
    border-top-right-radius: 4px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}
.openai-card-header
{
    margin: 0 auto;
    position: absolute;
    left: 0px;
    padding: 3px;
    border-radius: 0 50px 50px 0;
    height: auto;
    background: transparent;
    padding-top: 10px;
    display: flex;
    min-height: 3.5rem;
    align-items: center;
    margin-bottom: 0;
    position: relative;
}
.ai-export-txt
{
    cursor: pointer;
    padding-left:10px;
}
.ai-clear-chat
{
    cursor: pointer;
    padding-left:10px;
}
.openai-text-right
{
    display: flex;
    text-align: right;
    margin-left: auto;
    margin-right: 10px
}
.openai-d-flex
{
    display: flex;
}';
$reg_css_code .= '.aiomatic-gg-unmute {
    i {
        display: block;
        width: 5.33px; 
        height: 5.33px;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'background:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        margin: 7px 0 0 1.33px;
    }
    i:after {
        content: "";
        position: absolute;
        width: 0;
        height: 0;
        border-style: solid;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'border-color: transparent' . $aiomatic_Chatbot_Settings['submit_color'] . ' transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'border-color:transparent #55a7e2 transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        border-width: 6.67px 9.33px 6.67px 10px; 
        left: -8.67px;
        top: 3.33px;
    }
}
.aiomatic-gg-globe,
.aiomatic-gg-globe::after,
.aiomatic-gg-globe::before {
    display: block;
    box-sizing: border-box;
    height: 18px;
    border: 2px solid ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= '
}
.aiomatic-gg-globe::after {
    width: 24px;
    height: 20px;
    border: 2px solid transparent;
    border-bottom: 2px solid ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= '
    top: -11px;
    left: -5px;
}
.aiomatic-gg-globe:hover {
    transform: scale(1.1);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
}
.ai-compare-button.active {
  background-color: ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= ';
  border: 1px solid ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= ';
}
.aiomatic-globe-bar {
    position: absolute;
    width: 22px; 
    height: 2px;
    background: ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= '
    top: 50%;
    left: 50%; 
    transform: translate(-50%, -50%) rotate(45deg);
}
.aiomatic-gg-mute {
    i {
        display: block;
        width: 5.33px; 
        height: 5.33px;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'background:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        margin: 7px 0 0 1.33px; 
    }
    i:after {
        content: "";
        position: absolute;
        width: 0;
        height: 0;
        border-style: solid;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'border-color: transparent' . $aiomatic_Chatbot_Settings['submit_color'] . ' transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'border-color:transparent #55a7e2 transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        border-width: 6.67px 9.33px 6.67px 10px; 
        left: -8.67px; 
        top: 3.33px; 
    }
    i:before {
        transform: rotate(45deg);
        border-radius: 0 33.33px 0 0; 
        content: "";
        position: absolute;
        width: 3.33px; 
        height: 3.33px; 
        border-style: double;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'border-color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        border-width: 4.67px 4.67px 0 0;
        left: 12px; 
        top: 6px;
        transition: all 0.2s ease-out;
    }
}
.aiomatic-gg-trash {';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '
}
.aiomatic-gg-trash:hover {
    transform: scale(1.1);
    ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'background-color:#f0f0f0' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $loader_color = '#fff';
    if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
    {
        $loader_color = $aiomatic_Chatbot_Settings['persona_name_color'];
    }
    $reg_css_code .= '
}
.aiomatic-loading-indicator::before {
    content: \'\';
    box-sizing: border-box;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border-top: 3px solid ' . $loader_color. ';
    border-right: 3px solid transparent;
    animation: keysspin 1s linear infinite;
}
@keyframes keysspin {
    to {
        transform: rotate(360deg);
    }
}
#aiomatic-video-wrapper' . $chatid . '
{
    position: relative;
    padding-top: 10px;
}
.aiomatic-hide {display:none!important;}';
        $reg_css_code .= '.openai-ai-form{
            border-radius: 30px;';
        if($general_background != '')
        {
            $reg_css_code .= 'background-color:' . $general_background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['general_background']) && $aiomatic_Chatbot_Settings['general_background'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['general_background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if (isset($aiomatic_Chatbot_Settings['width']) && $aiomatic_Chatbot_Settings['width'] != '') 
        {
            $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        if($bubble_width == 'full' || empty($bubble_width))
        {
            $reg_css_code .= '.ai-bubble{width:100%!important;}';
            $css_added = true;
        }
        elseif($bubble_width == 'auto')
        {
            $reg_css_code .= '.ai-bubble{width:auto!important;}';
            $css_added = true;
        }
        if($bubble_alignment == 'left' || empty($bubble_alignment))
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-left:unset!important;margin-right:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_alignment == 'right')
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-right:unset!important;margin-left:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_alignment == 'center')
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-left:auto!important;margin-right:auto!important;}';
            $css_added = true;
        }
        if($bubble_user_alignment == 'left')
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-left:unset!important;margin-right:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_user_alignment == 'right' || empty($bubble_user_alignment))
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-right:unset!important;margin-left:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_user_alignment == 'center')
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-left:auto!important;margin-right:auto!important;}';
            $css_added = true;
        }
        $reg_css_code .= '.ai-mine{';
        if (isset($aiomatic_Chatbot_Settings['user_font_color']) && $aiomatic_Chatbot_Settings['user_font_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['user_font_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['user_background_color']) && $aiomatic_Chatbot_Settings['user_background_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['user_background_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
if(!empty($avatar_url))
{
    $css_added = true;
    $reg_css_code .= '.ai-avatar.ai-other {
    background-image: url("' . esc_url($avatar_url) . '");
    background-size: cover;
    background-position: center;
    margin-left: 3px;
    margin-right: 3px;
}';
}
if($additional_bots != '')
{
    $bot_list = explode(',', $additional_bots);
    $bot_list = array_map('trim', $bot_list);
    $bot_list = array_filter($bot_list);
    foreach($bot_list as $my_bot)
    {
        if(is_numeric($my_bot))
        {
            $bot_post = get_post($my_bot);
            if($bot_post != null)
            {
                if($bot_post->post_type === 'aiomatic_personas' || $bot_post->post_type === 'aiomatic_assistants')
                {
                    $att_src = get_the_post_thumbnail_url( $bot_post->ID, 'thumbnail' );
                    if ( empty($att_src) )
                    {
                        $att_src = plugins_url('images/placeholder.jpg', __FILE__);
                    }
                    $css_added = true;
                    $reg_css_code .= '.ai-avatar.ai-other.aiomatic-ainum-' . esc_html($bot_post->ID) . ' {
background-image: url("' . esc_url($att_src) . '");
background-size: cover;
background-position: center;
margin-left: 3px;
margin-right: 3px;
}';
                }
            }
        }
    }
}
if(!empty($avatar_url_user))
{
    $css_added = true;
    $reg_css_code .= '.ai-avatar.ai-mine {
    background-image: url("' . esc_url($avatar_url_user) . '");
    background-size: cover;
    background-position: center;
    margin-left: 3px;
    margin-right: 3px;
}';
}
        $reg_css_code .= '.ai-other{';
        if (isset($aiomatic_Chatbot_Settings['ai_font_color']) && $aiomatic_Chatbot_Settings['ai_font_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['ai_font_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['ai_background_color']) && $aiomatic_Chatbot_Settings['ai_background_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['ai_background_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
        {
            $go_color = $aiomatic_Chatbot_Settings['input_text_color'];
        }
        else
        {
            $go_color = '#ffffff';
        }
        $reg_css_code .= '}
.aiomatic-pdf-loading, .aiomatic-file-loading{
    border: 2px solid ' . $go_color . ';
    border-bottom-color: transparent;
    border-radius: 50%;
    box-sizing: border-box;
    animation: aiomatic_rotation 1s linear infinite;
    display: inline-block;
    width: 18px;
    height: 18px;
}
@keyframes aiomatic_rotation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}';
        $reg_css_code .= '#aiomatic_chat_input' . $chatid . '{min-height:62px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_border_color']) && $aiomatic_Chatbot_Settings['input_border_color'] != '') 
        {
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['input_border_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-name' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_name_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-role' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_role_color']) && $aiomatic_Chatbot_Settings['persona_role_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_role_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic-ai-text{';
        if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_name_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic-ai-input{min-height:62px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_border_color']) && $aiomatic_Chatbot_Settings['input_border_color'] != '') 
        {
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['input_border_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-name' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_name_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-role' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_role_color']) && $aiomatic_Chatbot_Settings['persona_role_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_role_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#aiomatic_chat_input' . $chatid . '::placeholder{';
        if (isset($aiomatic_Chatbot_Settings['input_placeholder_color']) && $aiomatic_Chatbot_Settings['input_placeholder_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_placeholder_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic-close-button{';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{margin-top: 5px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{';
        if ($voice_color != '') 
        {
            $reg_css_code .= 'background-color:' . $voice_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-image-speech-button' . $chatid . '{';
        if ($voice_color != '') 
        {
            $reg_css_code .= 'background-color:' . $voice_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        if (isset($aiomatic_Chatbot_Settings['open_width']) && $aiomatic_Chatbot_Settings['open_width'] != '') 
        {
            $reg_css_code .= '.aiomatic-open-image{width:' . $aiomatic_Chatbot_Settings['open_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
            $css_added = true;
        }
        $reg_css_code .= '#aiimagechatsubmitbut' . $chatid . '{margin-top: 5px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{margin-left:20px;}
#aiimagechatsubmitbut' . $chatid . '{';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
        {
            $reg_css_code .= 'form.aiomatic-window{';
            $reg_css_code .= 'display:none;';
            $reg_css_code .= '}';
            $css_added = true;
        }
        if($window_width != '')
        {
            preg_match_all('#(\d+)\s*px#i', $window_width, $zamatches);
            if(isset($zamatches[1][0]))
            {
                $myw = intval($zamatches[1][0]) + 100;
                $wwidth = $myw . 'px';
            }
            else
            {
                $wwidth = $window_width;
            }
            $reg_css_code .= '@media only screen and (min-width: ' . $wwidth . ') {form.aiomatic-window{';
            $reg_css_code .= 'width:' . $window_width . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'max-width:' . $window_width . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= '}}';
            $reg_css_code .= '@media only screen and (max-width: ' . $wwidth . ') {form.aiomatic-window{';
            $reg_css_code .= 'width:75%' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'max-width:75%' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= '}}';
            $css_added = true;
        }
        if($css_added === true)
        {
            wp_add_inline_style( $name . 'css-ai-front', $reg_css_code );
        }
        // Display the form
        if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
        {
            $show_me = '<img class="aiomatic-open-image" src="' . plugins_url('res/icons/1.png', __FILE__) . '">';
            if (isset($aiomatic_Chatbot_Settings['chatbot_icon']) && $aiomatic_Chatbot_Settings['chatbot_icon'] != '') 
            {
                if($aiomatic_Chatbot_Settings['chatbot_icon'] != 'x')
                {
                    $show_me = '<img class="aiomatic-open-image" src="' . plugins_url('res/icons/' . $aiomatic_Chatbot_Settings['chatbot_icon'] . '.png', __FILE__) . '">';
                }
                elseif (isset($aiomatic_Chatbot_Settings['chatbot_icon_html']) && $aiomatic_Chatbot_Settings['chatbot_icon_html'] != '') 
                {
                    $show_me = $aiomatic_Chatbot_Settings['chatbot_icon_html'];
                    if(aiomatic_starts_with($show_me, 'http') === true)
                    {
                        $show_me = '<img class="aiomatic-open-image" src="' . esc_url_raw($show_me) . '" width="32" height="32">';
                    }
                }
            }
            $return_me .= '<span id="aiomatic-open-button' . $chatid . '" class="aiomatic-open-button aiomatic-window aiomatic-' . $window_location . '" onclick="document.getElementById(\'openai-ai-image-form-' . $chatid . '\').style.display = \'inherit\';document.getElementById(\'aiomatic-open-button' . $chatid . '\').style.display = \'none\';">' . $show_me . '</span>';
        }
        $return_me .= '
            <form id="openai-ai-image-form-' . $chatid . '" method="post" class="openai-ai-image-form';
            if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
            {
                $return_me .= ' aiomatic-window aiomatic-' . $window_location;
            }
            $return_me .= '">';
            if (isset($aiomatic_Chatbot_Settings['show_header']) && $aiomatic_Chatbot_Settings['show_header'] != '' && $show_header == '')
            {
                $show_header = $aiomatic_Chatbot_Settings['show_header'];
            }
            $hclass = '';
            if($show_header != 'show')
            {
                $hclass = ' aiomatic-hide';
            }
            if (isset($aiomatic_Chatbot_Settings['show_dltxt']) && $aiomatic_Chatbot_Settings['show_dltxt'] != '' && $show_dltxt == '')
            {
                $show_dltxt = $aiomatic_Chatbot_Settings['show_dltxt'];
            }
            if (isset($aiomatic_Chatbot_Settings['show_mute']) && $aiomatic_Chatbot_Settings['show_mute'] != '' && $show_mute == '')
            {
                $show_mute = $aiomatic_Chatbot_Settings['show_mute'];
            }
            if (isset($aiomatic_Chatbot_Settings['show_internet']) && $aiomatic_Chatbot_Settings['show_internet'] != '' && $show_internet == '')
            {
                $show_internet = $aiomatic_Chatbot_Settings['show_internet'];
            }
            if (isset($aiomatic_Chatbot_Settings['show_clear']) && $aiomatic_Chatbot_Settings['show_clear'] != '' && $show_clear == '')
            {
                $show_clear = $aiomatic_Chatbot_Settings['show_clear'];
            }
            $tclass = '';
            if($show_dltxt != 'show')
            {
                $tclass = ' aiomatic-hide';
            }
            $mclass = '';
            if($show_mute != 'show' || $chatbot_text_speech == 'off')
            {
                $mclass = ' aiomatic-hide';
            }
            $iclass = '';
            if(!isset($aiomatic_Main_Settings['internet_chat_short']) || $aiomatic_Main_Settings['internet_chat_short'] != 'on')
            {
                $iclass = ' aiomatic-hide';
            }
            else
            {
                if($show_internet != 'show' || $internet_access == 'off' || $internet_access == 'disable' || $internet_access == 'disabled' || $internet_access == 'Disabled' || $internet_access === '0' || $internet_access === '0')
                {
                    $iclass = ' aiomatic-hide';
                }
            }
            $dclass = '';
            if($show_clear != 'show')
            {
                $dclass = ' aiomatic-hide';
            }
            $ai_prep = trim($ai_message_preppend, ': ');
            if($ai_prep != '')
            {
                $ai_prep .= ': ';
            }
            $user_prep = trim($user_message_preppend, ': ');
            if($user_prep != '')
            {
                $user_prep .= ': ';
            }
            if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
            {
                $return_me .= ' <span class="aiomatic-close-button" onclick="document.getElementById(\'openai-ai-image-form-' . $chatid . '\').style.display = \'none\';document.getElementById(\'aiomatic-open-button' . $chatid . '\').style.display = \'inherit\';">&times;</span>';
            }
            if(!empty($custom_header))
            {
                $return_me .= '<div class="openai-custom-header">' . $custom_header . '</div>';
            }
            $return_me .= '<div class="openai-card-header' . $hclass . '">
            <div class="w-100">
                <div class="openai-d-flex">
                    <div class="overflow-hidden openai-mr-4">' . $avatar_src . '</div>
                    <div class="openai-widget-user-name"><span id="openai-persona-name' . $chatid . '" class="openai-persona-name openai-font-weight-bold">' . esc_html(trim($ai_message_preppend, ': ')) . '</span><br><span id="openai-persona-role' . $chatid . '" class="openai-persona-role">' . esc_html($ai_role) . '</span></div>
                </div>
            </div>
                <div class="openai-text-right">
                <a id="ai-mute-chat' . $chatid . '" class="aiomatic-gg-mute ai-mute-chat template-button mr-2 download-btn' . $mclass . '"><i title="' . esc_html__("Mute/Unmute", 'aiomatic-automatic-ai-content-writer') . '"></i></a>
                <a id="ai-internet' . $chatid . '" chatid="' . $chatid . '" class="aiomatic-gg-globalist aiomatic-left-padding aiomatic-cursor template-button mr-2 download-btn' . $iclass . '"><i id="aiomatic-globe-overlay-mother' . $chatid . '" class="aiomatic-gg-globe" title="' . esc_html__("Disable Chatbot Internet Access", 'aiomatic-automatic-ai-content-writer') . '"><i id="aiomatic-globe-overlay' . $chatid . '" class="aiomatic-globe-overlay';
            if (isset($aiomatic_Chatbot_Settings['show_internet_default']))
            {
                if($aiomatic_Chatbot_Settings['show_internet_default'] == 'on')
                {
                }
                else
                {
                    $return_me .= ' aiomatic-globe-bar';
                }
            }
            else
            {
                $return_me .= ' aiomatic-globe-bar';
            }
            $return_me .= '"></i></i></a>
                <a id="ai-export-txt' . $chatid . '" class="ai-export-txt template-button mr-2 download-btn' . $tclass . '"><i title="' . esc_html__("Export Chat Conversation To File", 'aiomatic-automatic-ai-content-writer') . '" class="openai-file-document"></i></a>
                <a id="ai-clear-chat' . $chatid . '" class="ai-clear-chat template-button mr-2 download-btn' . $dclass . '"><i title="' . esc_html__("Clear Chat Conversation", 'aiomatic-automatic-ai-content-writer') . '" class="aiomatic-gg-trash"></i></a>
                </div>
            </div>';
            $return_me .= '
                <div class="code-form-top-pad form-group">
                    <div id="aiomatic_chat_history' . $chatid . '" class="aiomatic_chat_history ai-chat form-control"';
                    if (isset($aiomatic_Chatbot_Settings['enable_copy']) && $aiomatic_Chatbot_Settings['enable_copy'] != '') 
                    {
                        $return_me .= ' title="' . esc_html__('Click on a bubble to copy its content!', 'aiomatic-automatic-ai-content-writer') . '"';
                    }
                    $return_me .= '>';
                    if(!empty($chat_history))
                    {
                        $return_me .= $chat_history;
                    }
                    $complete_me = '';
                    if($ai_first_message != '')
                    {
                        if(stristr($ai_first_message, '%%') !== false)
                        {
                            $post_link = '';
                            $post_title = '';
                            $blog_title = html_entity_decode(get_bloginfo('title'));
                            $post_excerpt = '';
                            $final_content = '';
                            $user_name = '';
                            $featured_image = '';
                            $post_cats = '';
                            $post_tagz = '';
                            $postID = '';
                            global $post;
                            if(isset($post->ID))
                            {
                                $post_link = get_permalink($post->ID);
                                $blog_title       = html_entity_decode(get_bloginfo('title'));
                                $author_obj       = get_user_by('id', $post->post_author);
                                if($author_obj !== false)
                                {
                                    $user_name        = $author_obj->user_nicename;
                                }
                                $final_content = $post->post_content;
                                $post_title    = $post->post_title;
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
                                $post_excerpt = $post->post_excerpt;
                                $postID = $post->ID;
                            }
                            $ai_first_message = aiomatic_replaceAIPostShortcodes($ai_first_message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                            if (filter_var($ai_first_message, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_first_message, '.txt'))
                            {
                                $txt_content = aiomatic_get_web_page($ai_first_message);
                                if ($txt_content !== FALSE) 
                                {
                                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                    $txt_content = array_filter($txt_content);
                                    if(count($txt_content) > 0)
                                    {
                                        $txt_content = $txt_content[array_rand($txt_content)];
                                        if(trim($txt_content) != '') 
                                        {
                                            $ai_first_message = $txt_content;
                                            $ai_first_message = aiomatic_replaceAIPostShortcodes($ai_first_message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                                        }
                                    }
                                }
                            }
                            $current_user = wp_get_current_user();
                            if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
                            {
                                $ai_first_message = str_replace('%%user_name%%', '', $ai_first_message);
                                $ai_first_message = str_replace('%%user_email%%', '' , $ai_first_message);
                                $ai_first_message = str_replace('%%user_display_name%%', '', $ai_first_message);
                                $ai_first_message = str_replace('%%user_role%%', '', $ai_first_message);
                                $ai_first_message = str_replace('%%user_id%%', '' , $ai_first_message);
                                $ai_first_message = str_replace('%%user_firstname%%', '' , $ai_first_message);
                                $ai_first_message = str_replace('%%user_lastname%%', '' , $ai_first_message);
                                $ai_first_message = str_replace('%%user_description%%', '' , $ai_first_message);
                                $ai_first_message = str_replace('%%user_url%%', '' , $ai_first_message);
                            }
                            else
                            {
                                $ai_first_message = str_replace('%%user_name%%', $current_user->user_login, $ai_first_message);
                                $ai_first_message = str_replace('%%user_email%%', $current_user->user_email , $ai_first_message);
                                $ai_first_message = str_replace('%%user_display_name%%', $current_user->display_name, $ai_first_message);
                                $ai_first_message = str_replace('%%user_role%%', implode(',', $current_user->roles), $ai_first_message);
                                $ai_first_message = str_replace('%%user_id%%', $current_user->ID , $ai_first_message);
                                $ai_first_message = str_replace('%%user_firstname%%', $current_user->user_firstname , $ai_first_message);
                                $ai_first_message = str_replace('%%user_lastname%%', $current_user->user_lastname , $ai_first_message);
                                $user_desc = get_the_author_meta( 'description', $current_user->ID );
                                $ai_first_message = str_replace('%%user_description%%', $user_desc , $ai_first_message);
                                $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                                $ai_first_message = str_replace('%%user_url%%', $user_url , $ai_first_message);
                            }
                        }
                        $fm = preg_split('/\r\n|\r|\n/', trim($ai_first_message));
                        $fm = array_filter($fm);
                        if(empty($chat_history))
                        {
                            $return_me .= '<div class="ai-wrapper">';
                            if($bubble_alignment != 'right' && !empty($avatar_url) && $show_ai_avatar == 'show')
                            {
                                $return_me .= '<div class="ai-avatar ai-other"></div>';
                            }
                            $return_me .= '<div class="ai-bubble ai-other">' . $fm[0] . '</div>';
                            if($bubble_alignment == 'right' && !empty($avatar_url) && $show_ai_avatar == 'show')
                            {
                                $return_me .= '<div class="ai-avatar ai-other"></div>';
                            }
                            $return_me .= '</div>';
                        }
                        array_shift($fm);
                        if(count($fm) > 0)
                        {
                            $complete_me .= '<input type="hidden" id="aiomatic_message_input' . $chatid . '" value="' . esc_attr(implode('\r\n', $fm)) . '">';
                        }
                    }
                    $return_me .= '</div>';
                    $return_me .= '<textarea aria-label="' . esc_attr($placeholder) . '" id="aiomatic_chat_input' . $chatid . '" rows="2" class="aiomatic_chat_input chat-form-control" placeholder="' . esc_attr($placeholder) . '"';
                    if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'off' || $prompt_editable == 'disabled' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
                    {
                        $return_me .= ' disabled';
                    }
                    $return_me .= '></textarea>';
                    if($prompt_templates != '')
                    {
                        $predefined_prompts_arr = explode(';', $prompt_templates);
                        $return_me .= '<select id="aiomatic_image_chat_templates' . $chatid . '" class="cr_width_full">';
                        $return_me .= '<option disabled selected>' . esc_html($select_prompt) . '</option>';
                        foreach($predefined_prompts_arr as $sval)
                        {
                            $ppro = explode('|~|~|', $sval);
                            if(isset($ppro[1]))
                            {
                                $return_me .= '<option value="' . esc_attr($ppro[1]) . '">' . esc_html($ppro[0]) . '</option>';
                            }
                            else
                            {
                                $return_me .= '<option value="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
                            }
                        }
                        $return_me .= '</select>';
                    }        
                    $return_me .= '</div>';
                    if (isset($aiomatic_Chatbot_Settings['voice_input']) && $aiomatic_Chatbot_Settings['voice_input'] == 'on')
                    {
                        if(!($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'off' || $prompt_editable == 'disabled' || $prompt_editable == 'disable' || $prompt_editable == "false") || $prompt_templates == '')
                        {
                            $return_me .= '<button type="button" id="openai-chat-speech-button' . $chatid . '" class="openai-chat-speech-button btn btn-primary"';
                            if($submit_full == 'on')
                            {
                                $return_me .= ' style="width: 100%!important;"';
                            }
                            $return_me .= ' title="Record your voice">
                                <img src="' . plugins_url('images/mic.ico', __FILE__) . '">
                            </button>&nbsp;';
                        }
                    }
                    $return_me .= $complete_me;
                    if($submit_image != '')
                    {
                        $submit = '<img src="' . esc_url_raw($submit_image) . '" title="' . esc_html($submit) . '" alt="' . esc_html($submit) . '" />';
                    }
                    $return_me .= '<button type="button" id="aiimagechatsubmitbut' . $chatid . '" class="aiimagechatsubmitbut btn btn-primary"';
                            if($submit_full == 'on')
                            {
                                $return_me .= ' style="width: 100%!important;"';
                            }
                            $return_me .= '>' . $submit . '</button>
                <div id="openai-image-chat-response' . $chatid . '">&nbsp;</div>
                <div id="compliance' . $chatid . '" class="aiomatic-text-center cr_fullw">' . $compliance . '</div>';
        if(!empty($custom_footer))
        {
            $return_me .= '<div class="openai-custom-footer">' . $custom_footer . '</div>';
        }
        if(!empty($custom_css))
        {
            $return_me .= '<style>' . $custom_css . '</style>';
        }
        if (isset($aiomatic_Chatbot_Settings['show_gdpr']) && $aiomatic_Chatbot_Settings['show_gdpr'] == 'on')
        {
            $privacy_url = '/privacy-policy';
            if(function_exists('get_privacy_policy_url'))
            {
                $privacy_url = get_privacy_policy_url();
                if(empty($privacy_url))
                {
                    $privacy_url = '/privacy-policy';
                }
            }
            $gdpr_button = 'Start chatting';
            if (isset($aiomatic_Chatbot_Settings['gdpr_button']) && $aiomatic_Chatbot_Settings['gdpr_button'] != '')
            {
                $gdpr_button = $aiomatic_Chatbot_Settings['gdpr_button'];
            }
            $gdpr_checkbox = 'I agree to the terms.';
            if (isset($aiomatic_Chatbot_Settings['gdpr_checkbox']) && $aiomatic_Chatbot_Settings['gdpr_checkbox'] != '')
            {
                $gdpr_checkbox = $aiomatic_Chatbot_Settings['gdpr_checkbox'];
            }
            $gdpr_notice = 'By using this chatbot, you consent to the collection and use of your data as outlined in our <a href=\'%%privacy_policy_url%%\' target=\'_blank\'>Privacy Policy</a>. Your data will only be used to assist with your inquiry.';
            if (isset($aiomatic_Chatbot_Settings['gdpr_notice']) && $aiomatic_Chatbot_Settings['gdpr_notice'] != '')
            {
                $gdpr_notice = $aiomatic_Chatbot_Settings['gdpr_notice'];
            }
            $gdpr_notice = str_replace('%%privacy_policy_url%%', $privacy_url, $gdpr_notice);
            $return_me .= '<div class="aiomatic-chatbot-overlay cr_none" id="aiomatic-chatbot-overlay' . $chatid . '">
<div class="aiomatic-overlay-content">
    <p>' . wp_kses_post($gdpr_notice) . '</p>
    <label for="aiomatic-consent-checkbox' . $chatid . '">
        <input type="checkbox" class="aiomatic-consent-checkbox" id="aiomatic-consent-checkbox' . $chatid . '">' . esc_html($gdpr_checkbox) . '</label>
    <button type="button" class="aiomatic-start-chatting-button" id="aiomatic-start-chatting-button' . $chatid . '" disabled>' . esc_html($gdpr_button) . '</button>
</div>
</div>';
        }
        $return_me .= '</form> 
        ';
    }
    else
    {
        $persistent_assistant = false;
        $user_id = '0';
        $chat_history = '';
        if(!empty($user_token_cap_per_day) || ($persistent != 'off' && $persistent != '0' && $persistent != ''))
        {
            $user_id = get_current_user_id();
            if($user_id == 0 && ($persistent_guests == 'on' || $persistent_guests == '1'))
            {
                $user_id = aiomatic_get_the_user_ip();
            }
            if(($persistent != 'off' && $persistent != 'logs' && $persistent != 'vector' && $persistent != 'history' && $persistent != '0' && $persistent != '') && $user_id != 0)
            {
                if(is_numeric($user_id))
                {
                    if($assistant_id != '')
                    {
                        $chat_history = get_user_meta($user_id, 'aiomatic_assistant_history_thread', true);
                        if(empty($chat_history))
                        {
                            $chat_history = '';
                        }
                        else
                        {
                            $persistent_assistant = true;
                        }
                    }
                    else
                    {
                        $chat_history = get_user_meta($user_id, 'aiomatic_chat_history_' . $persistent, true);
                        if(empty($chat_history))
                        {
                            $chat_history = '';
                        }
                    }
                }
                else
                {
                    if($assistant_id != '')
                    {
                        $chat_history = get_transient('aiomatic_assistant_history_thread_' . $user_id);
                        if(empty($chat_history))
                        {
                            $chat_history = '';
                        }
                        else
                        {
                            $persistent_assistant = true;
                        }
                    }
                    else
                    {
                        $chat_history = get_transient('aiomatic_chat_history_' . $persistent . '_' . $user_id);
                        if(empty($chat_history))
                        {
                            $chat_history = '';
                        }
                    }
                }
            }
        }
        $name = md5(get_bloginfo());
        wp_enqueue_script($name . 'openai-chat-ajax', plugins_url('scripts/openai-chat-ajax.js', __FILE__), array('jquery'), AIOMATIC_MAJOR_VERSION);
        $chat_preppend_text = do_shortcode($chat_preppend_text);
        if(stristr($chat_preppend_text, '%%') !== false)
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
            global $post;
            if(isset($post->ID))
            {
                $post_link = get_permalink($post->ID);
                $blog_title       = html_entity_decode(get_bloginfo('title'));
                $author_obj       = get_user_by('id', $post->post_author);
                if($author_obj !== false)
                {
                    $user_name        = $author_obj->user_nicename;
                }
                $final_content = $post->post_content;
                $post_title    = $post->post_title;
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
                $post_excerpt = $post->post_excerpt;
                $postID = $post->ID;
            }
            $chat_preppend_text = aiomatic_replaceAIPostShortcodes($chat_preppend_text, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
            if (filter_var($chat_preppend_text, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($chat_preppend_text, '.txt'))
            {
                $txt_content = aiomatic_get_web_page($chat_preppend_text);
                if ($txt_content !== FALSE) 
                {
                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                    $txt_content = array_filter($txt_content);
                    if(count($txt_content) > 0)
                    {
                        $txt_content = $txt_content[array_rand($txt_content)];
                        if(trim($txt_content) != '') 
                        {
                            $chat_preppend_text = $txt_content;
                            $chat_preppend_text = aiomatic_replaceAIPostShortcodes($chat_preppend_text, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                            $chat_preppend_text = do_shortcode($chat_preppend_text);
                        }
                    }
                }
            }
            $current_user = wp_get_current_user();
            if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
            {
                $chat_preppend_text = str_replace('%%user_name%%', '', $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_email%%', '' , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_display_name%%', '', $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_role%%', '', $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_id%%', '' , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_firstname%%', '' , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_lastname%%', '' , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_description%%', '' , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_url%%', '' , $chat_preppend_text);
            }
            else
            {
                $chat_preppend_text = str_replace('%%user_name%%', $current_user->user_login, $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_email%%', $current_user->user_email , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_display_name%%', $current_user->display_name , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_role%%', implode(',', $current_user->roles), $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_id%%', $current_user->ID , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_firstname%%', $current_user->user_firstname , $chat_preppend_text);
                $chat_preppend_text = str_replace('%%user_lastname%%', $current_user->user_lastname , $chat_preppend_text);
                $user_desc = get_the_author_meta( 'description', $current_user->ID );
                $chat_preppend_text = str_replace('%%user_description%%', $user_desc , $chat_preppend_text);
                $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                $chat_preppend_text = str_replace('%%user_url%%', $user_url , $chat_preppend_text);
            }
        }
        if(stristr($ai_message_preppend, '%%') !== false)
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
            global $post;
            if(isset($post->ID))
            {
                $post_link = get_permalink($post->ID);
                $blog_title       = html_entity_decode(get_bloginfo('title'));
                $author_obj       = get_user_by('id', $post->post_author);
                if($author_obj !== false)
                {
                    $user_name        = $author_obj->user_nicename;
                }
                $final_content = $post->post_content;
                $post_title    = $post->post_title;
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
                $post_excerpt = $post->post_excerpt;
                $postID = $post->ID;
            }
            $ai_message_preppend = aiomatic_replaceAIPostShortcodes($ai_message_preppend, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
            if (filter_var($ai_message_preppend, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_message_preppend, '.txt'))
            {
                $txt_content = aiomatic_get_web_page($ai_message_preppend);
                if ($txt_content !== FALSE) 
                {
                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                    $txt_content = array_filter($txt_content);
                    if(count($txt_content) > 0)
                    {
                        $txt_content = $txt_content[array_rand($txt_content)];
                        if(trim($txt_content) != '') 
                        {
                            $ai_message_preppend = $txt_content;
                            $ai_message_preppend = aiomatic_replaceAIPostShortcodes($ai_message_preppend, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                        }
                    }
                }
            }
            $current_user = wp_get_current_user();
            if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
            {
                $ai_message_preppend = str_replace('%%user_name%%', '', $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_email%%', '' , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_display_name%%', '', $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_role%%', '', $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_id%%', '' , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_firstname%%', '' , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_lastname%%', '' , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_description%%', '' , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_url%%', '' , $ai_message_preppend);
            }
            else
            {
                $ai_message_preppend = str_replace('%%user_name%%', $current_user->user_login, $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_email%%', $current_user->user_email , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_display_name%%', $current_user->display_name , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_role%%', implode(',', $current_user->roles), $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_id%%', $current_user->ID , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_firstname%%', $current_user->user_firstname , $ai_message_preppend);
                $ai_message_preppend = str_replace('%%user_lastname%%', $current_user->user_lastname , $ai_message_preppend);
                $user_desc = get_the_author_meta( 'description', $current_user->ID );
                $ai_message_preppend = str_replace('%%user_description%%', $user_desc , $ai_message_preppend);
                $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                $ai_message_preppend = str_replace('%%user_url%%', $user_url , $ai_message_preppend);
            }
        }
        if(stristr($user_message_preppend, '%%') !== false)
        {
            $post_link = '';
            $post_title = '';
            $blog_title = html_entity_decode(get_bloginfo('title'));
            $post_excerpt = '';
            $final_content = '';
            $user_name = '';
            $featured_image = '';
            $post_cats = '';
            $post_tagz = '';
            $postID = '';
            global $post;
            if(isset($post->ID))
            {
                $post_link = get_permalink($post->ID);
                $blog_title       = html_entity_decode(get_bloginfo('title'));
                $author_obj       = get_user_by('id', $post->post_author);
                if($author_obj !== false)
                {
                    $user_name        = $author_obj->user_nicename;
                }
                $final_content = $post->post_content;
                $post_title    = $post->post_title;
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
                $post_excerpt = $post->post_excerpt;
                $postID = $post->ID;
            }
            $user_message_preppend = aiomatic_replaceAIPostShortcodes($user_message_preppend, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
            if (filter_var($user_message_preppend, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($user_message_preppend, '.txt'))
            {
                $txt_content = aiomatic_get_web_page($user_message_preppend);
                if ($txt_content !== FALSE) 
                {
                    $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                    $txt_content = array_filter($txt_content);
                    if(count($txt_content) > 0)
                    {
                        $txt_content = $txt_content[array_rand($txt_content)];
                        if(trim($txt_content) != '') 
                        {
                            $user_message_preppend = $txt_content;
                            $user_message_preppend = aiomatic_replaceAIPostShortcodes($user_message_preppend, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                        }
                    }
                }
            }
            $current_user = wp_get_current_user();
            if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
            {
                $user_message_preppend = str_replace('%%user_name%%', '', $user_message_preppend);
                $user_message_preppend = str_replace('%%user_email%%', '' , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_display_name%%', '', $user_message_preppend);
                $user_message_preppend = str_replace('%%user_role%%', '', $user_message_preppend);
                $user_message_preppend = str_replace('%%user_id%%', '' , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_firstname%%', '' , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_lastname%%', '' , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_description%%', '' , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_url%%', '' , $user_message_preppend);
            }
            else
            {
                $user_message_preppend = str_replace('%%user_name%%', $current_user->user_login, $user_message_preppend);
                $user_message_preppend = str_replace('%%user_email%%', $current_user->user_email , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_display_name%%', $current_user->display_name , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_role%%', implode(',', $current_user->roles), $user_message_preppend);
                $user_message_preppend = str_replace('%%user_id%%', $current_user->ID , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_firstname%%', $current_user->user_firstname , $user_message_preppend);
                $user_message_preppend = str_replace('%%user_lastname%%', $current_user->user_lastname , $user_message_preppend);
                $user_desc = get_the_author_meta( 'description', $current_user->ID );
                $user_message_preppend = str_replace('%%user_description%%', $user_desc , $user_message_preppend);
                $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                $user_message_preppend = str_replace('%%user_url%%', $user_url , $user_message_preppend);
            }
        }
        $enable_moderation = '0';
        if (isset($aiomatic_Chatbot_Settings['enable_moderation']) && $aiomatic_Chatbot_Settings['enable_moderation'] == 'on') 
        {
            $enable_moderation = '1';
        }
        if (isset($aiomatic_Chatbot_Settings['moderation_model']) && $aiomatic_Chatbot_Settings['moderation_model'] == 'on') 
        {
            $moderation_model = $aiomatic_Chatbot_Settings['moderation_model'];
        }
        else
        {
            $moderation_model = 'text-moderation-stable';
        }
        if (isset($aiomatic_Chatbot_Settings['flagged_message']) && $aiomatic_Chatbot_Settings['flagged_message'] == 'on') 
        {
            $flagged_message = $aiomatic_Chatbot_Settings['flagged_message'];
        }
        else
        {
            $flagged_message = esc_html__('Your message has been flagged as potentially harmful or inappropriate. Please review your language and content to ensure it aligns with our values of respect and sensitivity towards others. Thank you for your cooperation.', 'aiomatic-automatic-ai-content-writer');
        }
        if (isset($aiomatic_Chatbot_Settings['enable_copy']) && $aiomatic_Chatbot_Settings['enable_copy'] == 'on') 
        {
            $enable_copy = $aiomatic_Chatbot_Settings['enable_copy'];
        }
        else
        {
            $enable_copy = '0';
        }
        if (isset($aiomatic_Chatbot_Settings['scroll_bot']) && $aiomatic_Chatbot_Settings['scroll_bot'] == 'on') 
        {
            $scroll_bot = $aiomatic_Chatbot_Settings['scroll_bot'];
        }
        else
        {
            $scroll_bot = '0';
        }
        if (isset($aiomatic_Chatbot_Settings['chat_waveform']) && $aiomatic_Chatbot_Settings['chat_waveform'] == 'on') 
        {
            $chat_waveform = $aiomatic_Chatbot_Settings['chat_waveform'];
            wp_enqueue_script($name . 'openai-waveform-script', 'https://unpkg.com/wavesurfer.js@7.8.6/dist/wavesurfer.min.js');
        }
        else
        {
            $chat_waveform = '0';
        }
        if (isset($aiomatic_Chatbot_Settings['waveform_color']) && $aiomatic_Chatbot_Settings['waveform_color'] != '') 
        {
            $waveform_color = $aiomatic_Chatbot_Settings['waveform_color'];
        }
        else
        {
            $waveform_color = 'purple';
        }
        $max_messages = '';
        if (isset($aiomatic_Chatbot_Settings['max_message_count']) && $aiomatic_Chatbot_Settings['max_message_count'] != '' && is_numeric($aiomatic_Chatbot_Settings['max_message_count'])) 
        {
            $max_messages = $aiomatic_Chatbot_Settings['max_message_count'];
        }
        $max_message_context = '';
        if (isset($aiomatic_Chatbot_Settings['max_message_context']) && $aiomatic_Chatbot_Settings['max_message_context'] != '' && is_numeric($aiomatic_Chatbot_Settings['max_message_context'])) 
        {
            $max_message_context = $aiomatic_Chatbot_Settings['max_message_context'];
        }
        $no_empty = '';
        if (isset($aiomatic_Chatbot_Settings['no_empty']) && trim($aiomatic_Chatbot_Settings['no_empty']) == 'on' && empty($input_text))
        {
            $no_empty = '1';
        }
        if($persistent_assistant == true)
        {
            $thread_id = $chat_history;
        }
        else
        {
            $thread_id = '';
        }
        if ((isset($aiomatic_Main_Settings['did_app_id']) && trim($aiomatic_Main_Settings['did_app_id']) != ''))
        {
            $did_app_id = trim($aiomatic_Main_Settings['did_app_id']);
        }
        else
        {
            $did_app_id = '';
        }
        if ((isset($aiomatic_Main_Settings['azure_speech_id']) && trim($aiomatic_Main_Settings['azure_speech_id']) != ''))
        {
            $azure_speech_id = trim($aiomatic_Main_Settings['azure_speech_id']);
        }
        else
        {
            $azure_speech_id = '';
        }
        if ((isset($aiomatic_Chatbot_Settings['azure_voice']) && trim($aiomatic_Chatbot_Settings['azure_voice']) != ''))
        {
            $azure_voice = trim($aiomatic_Chatbot_Settings['azure_voice']);
        }
        else
        {
            $azure_voice = 'en-US-AvaMultilingualNeural';
        }
        if ((isset($aiomatic_Chatbot_Settings['azure_private_endpoint']) && trim($aiomatic_Chatbot_Settings['azure_private_endpoint']) != ''))
        {
            $azure_private_endpoint = trim($aiomatic_Chatbot_Settings['azure_private_endpoint']);
        }
        else
        {
            $azure_private_endpoint = '';
        }
        if ((isset($aiomatic_Chatbot_Settings['azure_voice_endpoint']) && trim($aiomatic_Chatbot_Settings['azure_voice_endpoint']) != ''))
        {
            $azure_voice_endpoint = trim($aiomatic_Chatbot_Settings['azure_voice_endpoint']);
        }
        else
        {
            $azure_voice_endpoint = '';
        }
        if ((isset($aiomatic_Chatbot_Settings['azure_character']) && trim($aiomatic_Chatbot_Settings['azure_character']) != ''))
        {
            $azure_character = trim($aiomatic_Chatbot_Settings['azure_character']);
        }
        else
        {
            $azure_character = 'lisa';
        }
        if ((isset($aiomatic_Chatbot_Settings['azure_character_style']) && trim($aiomatic_Chatbot_Settings['azure_character_style']) != ''))
        {
            $azure_character_style = trim($aiomatic_Chatbot_Settings['azure_character_style']);
        }
        else
        {
            $azure_character_style = 'casual-sitting';
        }
        if ((isset($aiomatic_Chatbot_Settings['azure_region']) && trim($aiomatic_Chatbot_Settings['azure_region']) != ''))
        {
            $azure_region = trim($aiomatic_Chatbot_Settings['azure_region']);
        }
        else
        {
            $azure_region = 'westus2';
        }
        if ((isset($aiomatic_Chatbot_Settings['azure_voice_profile']) && trim($aiomatic_Chatbot_Settings['azure_voice_profile']) != ''))
        {
            $azure_voice_profile = trim($aiomatic_Chatbot_Settings['azure_voice_profile']);
        }
        else
        {
            $azure_voice_profile = '';
        }
        $did_image = '';
        if(!empty($avatar_url))
        {
            $did_image = $avatar_url;
        }
        if(isset($aiomatic_Chatbot_Settings['did_image']) && $aiomatic_Chatbot_Settings['did_image'] != '')
        {
            $did_image = $aiomatic_Chatbot_Settings['did_image'];
        }
        if(isset($overwrite_avatar_image) && !empty(trim($overwrite_avatar_image)))
        {
            $did_image = trim($overwrite_avatar_image);
        }
        if(isset($disable_streaming) && ($disable_streaming == 'on' || $disable_streaming == '1' || $disable_streaming == 'yes'))
        {
            $did_image = '';
        }
        if(isset($aiomatic_Chatbot_Settings['did_voice']) && $aiomatic_Chatbot_Settings['did_voice'] != '')
        {
            $did_voice = $aiomatic_Chatbot_Settings['did_voice'];
        }
        else
        {
            $did_voice = 'microsoft:en-US-JennyNeural:Cheerful';
        }
        if(aiomatic_is_claude_model($model))
        {
            $stream_url = esc_html(add_query_arg(array(
                'aiomatic_claude_stream' => 'yes',
                'nonce' => wp_create_nonce('aiomatic-streaming-nonce')
            ), site_url() . '/index.php'));
        }
        else
        {
            $stream_url = esc_html(add_query_arg(array(
                'aiomatic_stream' => 'yes',
                'nonce' => wp_create_nonce('aiomatic-streaming-nonce')
            ), site_url() . '/index.php'));
        }
        if(aiomatic_is_claude_model($model))
        {
            $model_type = 'claude';
        }
        else
        {
            if(aiomatic_is_google_model($model))
            {
                $model_type = 'google';
            }
            elseif(aiomatic_is_huggingface_model($model))
            {
                $model_type = 'huggingface';
            }
            elseif(aiomatic_is_ollama_model($model))
            {
                $model_type = 'ollama';
            }
            else
            {
                $model_type = 'gpt';
            }
        }
        if (isset($aiomatic_Main_Settings['pdf_ok']) && trim($aiomatic_Main_Settings['pdf_ok']) != '')
        {
            $pdf_ok = trim($aiomatic_Main_Settings['pdf_ok']);
        }
        else
        {
            $pdf_ok = esc_html__('PDF file uploaded successfully! You can ask questions about it.', 'aiomatic-automatic-ai-content-writer');
        }
        if (isset($aiomatic_Main_Settings['pdf_end']) && trim($aiomatic_Main_Settings['pdf_end']) != '')
        {
            $pdf_end = trim($aiomatic_Main_Settings['pdf_end']);
        }
        else
        {
            $pdf_end = esc_html__('PDF file session ended.', 'aiomatic-automatic-ai-content-writer');
        }
        if (isset($aiomatic_Main_Settings['pdf_fail']) && trim($aiomatic_Main_Settings['pdf_fail']) != '')
        {
            $pdf_fail = trim($aiomatic_Main_Settings['pdf_fail']);
        }
        else
        {
            $pdf_fail = esc_html__('Failed to upload the PDF file, please try again later.', 'aiomatic-automatic-ai-content-writer');
        }
        $is_modern_gpt = '0';
        if (!isset($aiomatic_Chatbot_Settings['disable_modern_chat']) || $aiomatic_Chatbot_Settings['disable_modern_chat'] != 'on')
        {
            if(empty(trim($assistant_id)))
            {
                $checkappids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                $checkappids = array_filter($checkappids);
                if(!empty($checkappids))
                {
                    $checktoken = $checkappids[array_rand($checkappids)];
                    if(!aiomatic_is_aiomaticapi_key($checktoken) && (!aiomatic_check_if_azure_or_others($aiomatic_Main_Settings, $model) || aiomatic_is_perplexity_model($model) || aiomatic_is_groq_model($model) || aiomatic_is_nvidia_model($model) || aiomatic_is_xai_model($model)))
                    {
                        if(aiomatic_is_chatgpt_model($model) || aiomatic_is_chatgpt_turbo_model($model) || aiomatic_is_perplexity_model($model) || aiomatic_is_groq_model($model) || aiomatic_is_nvidia_model($model) || aiomatic_is_xai_model($model) || aiomatic_is_chatgpt_o_model($model) || aiomatic_is_o1_model($model))
                        {
                            $is_modern_gpt = '1';
                        }
                    }
                }
            }
        }
        if($voice_color_activated == '')
        {
            if (isset($aiomatic_Chatbot_Settings['voice_color_activated']) && $aiomatic_Chatbot_Settings['voice_color_activated'] != '') 
            {
                $voice_color_activated = $aiomatic_Chatbot_Settings['voice_color_activated'];
            }
        }
        if($voice_color == '')
        {
            if (isset($aiomatic_Chatbot_Settings['voice_color']) && $aiomatic_Chatbot_Settings['voice_color'] != '') 
            {
                $voice_color = $aiomatic_Chatbot_Settings['voice_color'];
            }
        }
        if(!function_exists('is_plugin_active'))
        {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $all_ok = true;
        if (!is_plugin_active('aiomatic-extension-pdf-files/aiomatic-extension-pdf-files.php')) 
        {
            $all_ok = false;
        }
        if (isset($aiomatic_Chatbot_Settings['chat_download_format']) && $aiomatic_Chatbot_Settings['chat_download_format'] != '' && $all_ok === true) 
        {
            $chat_download_format = $aiomatic_Chatbot_Settings['chat_download_format'];
        }
        else
        {
            $chat_download_format = 'txt';
        }
        if (isset($aiomatic_Chatbot_Settings['auto_submit_voice']) && $aiomatic_Chatbot_Settings['auto_submit_voice'] == 'on')
        {
            $auto_submit_voice = 'on';
        }
        else
        {
            $auto_submit_voice = 'off';
        }
        if ($send_message_sound == '' && isset($aiomatic_Chatbot_Settings['send_message_sound']) && trim($aiomatic_Chatbot_Settings['send_message_sound']) != '')
        {
            $send_message_sound = trim($aiomatic_Chatbot_Settings['send_message_sound']);
        }
        if ($receive_message_sound == '' && isset($aiomatic_Chatbot_Settings['receive_message_sound']) && trim($aiomatic_Chatbot_Settings['receive_message_sound']) != '')
        {
            $receive_message_sound = trim($aiomatic_Chatbot_Settings['receive_message_sound']);
        }
        if ($response_delay == '' && isset($aiomatic_Chatbot_Settings['response_delay']) && trim($aiomatic_Chatbot_Settings['response_delay']) != '')
        {
            $response_delay = trim($aiomatic_Chatbot_Settings['response_delay']);
        }
        $markdown_parse = 'off';
        if ((isset($aiomatic_Main_Settings['markdown_parse']) && $aiomatic_Main_Settings['markdown_parse'] == 'on') || (isset($aiomatic_Chatbot_Settings['markdown_parse']) && $aiomatic_Chatbot_Settings['markdown_parse'] == 'on'))
        {
            $markdown_parse = 'on';
        }
        $katex_parse = 'off';
        if (isset($aiomatic_Chatbot_Settings['katex_parse']) && $aiomatic_Chatbot_Settings['katex_parse'] == 'on')
        {
            $katex_parse = 'on';
        }
        if($katex_parse == 'on')
        {
            wp_register_script( $name . '-katex', trailingslashit( plugins_url('', __FILE__) ) . 'js/katex.min.js', array(), AIOMATIC_MAJOR_VERSION );
            wp_enqueue_script( $name . '-katex' );
            wp_register_script( $name . '-katex-autorender', trailingslashit( plugins_url('', __FILE__) ) . 'js/auto-render.min.js', array(), AIOMATIC_MAJOR_VERSION );
            wp_enqueue_script( $name . '-katex-autorender' );
            wp_register_style($name . '-katex-css', plugins_url('styles/katex.min.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
            wp_enqueue_style( $name . '-katex-css' );
        }
        if(aiomatic_check_if_azure($aiomatic_Main_Settings))
        {
            $is_azure = '1';
        }
        else
        {
            $is_azure = '0';
        }
        if($chat_download_format == 'pdf')
        {
            wp_enqueue_script($name . 'pdf-downloader', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array(), AIOMATIC_MAJOR_VERSION);
            wp_enqueue_script($name . 'html-canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), AIOMATIC_MAJOR_VERSION);
        }
        wp_enqueue_style($name . 'css-ai-front', plugins_url('styles/form-front.css', __FILE__), false, AIOMATIC_MAJOR_VERSION);
        $css_added = false;
        $reg_css_code = '.aiomatic_chat_history{';
        if (isset($aiomatic_Chatbot_Settings['font_size']) && $aiomatic_Chatbot_Settings['font_size'] != '') 
        {
            $reg_css_code .= 'font-size:' . $aiomatic_Chatbot_Settings['font_size'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['height']) && $aiomatic_Chatbot_Settings['height'] != '') 
        {
            $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['minheight']) && $aiomatic_Chatbot_Settings['minheight'] != '') 
        {
            $reg_css_code .= 'min-height:' . $aiomatic_Chatbot_Settings['minheight'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic_chat_history_log{';
        if (isset($aiomatic_Chatbot_Settings['font_size']) && $aiomatic_Chatbot_Settings['font_size'] != '') 
        {
            $reg_css_code .= 'font-size:' . $aiomatic_Chatbot_Settings['font_size'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['height']) && $aiomatic_Chatbot_Settings['height'] != '') 
        {
            $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['minheight']) && $aiomatic_Chatbot_Settings['minheight'] != '') 
        {
            $reg_css_code .= 'min-height:' . $aiomatic_Chatbot_Settings['minheight'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';

        $all_ok = true;
        if ($upload_pdf == 'on' || $upload_pdf == 'enabled' || $upload_pdf == 'yes' || $upload_pdf == '1' || (isset($aiomatic_Chatbot_Settings['upload_pdf']) && $aiomatic_Chatbot_Settings['upload_pdf'] == 'on' && $upload_pdf != 'disabled' && $upload_pdf != 'no' && $upload_pdf != '0' && $upload_pdf != 'off'))
        {
            if(!function_exists('is_plugin_active'))
            {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            if (!is_plugin_active('aiomatic-extension-pdf-files/aiomatic-extension-pdf-files.php')) 
            {
                $all_ok = false;
            }
            if (isset($aiomatic_Main_Settings['pinecone_app_id'])) {
                $pinecone_app_id = $aiomatic_Main_Settings['pinecone_app_id'];
            } else {
                $pinecone_app_id = '';
            }
            if (isset($aiomatic_Main_Settings['qdrant_app_id'])) {
                $qdrant_app_id = $aiomatic_Main_Settings['qdrant_app_id'];
            } else {
                $qdrant_app_id = '';
            }
            if($pinecone_app_id == '' && $qdrant_app_id == '')
            {
                $all_ok = false;
            }
            if (!isset($aiomatic_Main_Settings['embeddings_chat_short']) || trim($aiomatic_Main_Settings['embeddings_chat_short']) != 'on')
            {
                $all_ok = false;
            }
        }
        else
        {
            $all_ok = false;
        }
        if($no_padding != 'on')
        {
        $reg_css_code .= '#openai-ai-chat-form-' . $chatid . '
{
  box-sizing: border-box;
  padding-right: 20px;
  padding-left: 20px;
}';
        }
$reg_css_code .= '.openai-mr-4
{
    margin-right: 1rem ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
}
.openai-chat-avatar
{
    border-radius: 50%;';
    if (isset($aiomatic_Chatbot_Settings['topavatar_width']) && $aiomatic_Chatbot_Settings['topavatar_width'] != '') 
    {
        $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['topavatar_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $css_added = true;
    }
    else
    {
        $reg_css_code .= 'width: 44px;';
    }
    if (isset($aiomatic_Chatbot_Settings['topavatar_height']) && $aiomatic_Chatbot_Settings['topavatar_height'] != '') 
    {
        $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['topavatar_height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $css_added = true;
    }
    else
    {
        $reg_css_code .= 'height: 44px;';
    }
    $reg_css_code .= 'clear: both;
    display: block;
    background: #E1F0FF;
    position: relative;
}
.ai-avatar{';
if (isset($aiomatic_Chatbot_Settings['convavatar_width']) && $aiomatic_Chatbot_Settings['convavatar_width'] != '') 
{
    $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['convavatar_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $reg_css_code .= 'min-width:' . $aiomatic_Chatbot_Settings['convavatar_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $css_added = true;
}
else
{
    $reg_css_code .= 'width: 40px;';
    $reg_css_code .= 'min-width: 40px;';
}
if (isset($aiomatic_Chatbot_Settings['convavatar_height']) && $aiomatic_Chatbot_Settings['convavatar_height'] != '') 
{
    $reg_css_code .= 'height:' . $aiomatic_Chatbot_Settings['convavatar_height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $reg_css_code .= 'min-height:' . $aiomatic_Chatbot_Settings['convavatar_height'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $css_added = true;
}
else
{
    $reg_css_code .= 'height: 40px;';
    $reg_css_code .= 'min-height: 40px;';
}
$reg_css_code .= '}
.openai-font-weight-bold
{
    font-weight: bold ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
}
.openai-card-header{';
    if (isset($aiomatic_Chatbot_Settings['width']) && $aiomatic_Chatbot_Settings['width'] != '') 
    {
        $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $reg_css_code .= 'max-width:' . $aiomatic_Chatbot_Settings['width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        $css_added = true;
    }
    $reg_css_code .= '
}
#openai-chat-response' . $chatid . ' {
    padding-bottom: 5px;
    text-align:center;';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '}
.openai-file-document {';
    
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '
}
.openai-file-document:hover {
    transform: scale(1.1);
    ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'background-color:#f0f0f0' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '
    color: #333;
}
.aiomatic-vision-image
{
    max-width:300px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
    max-height:300px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
    display:block' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '
}
.aiomatic-pdf-image {
user-select: none;
position: absolute;
display: block;
transform: scale(var(--ggs,1));
width: 25px;
overflow: hidden;
border-radius: 2px;
}
.aiomatic-pdf-image {
    cursor: pointer;';
if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
{
    $reg_css_code .= 'right: 38px;';
}
else
{
    $reg_css_code .= 'right: 10px;';
}
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%);
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-file-image {
user-select: none;
position: absolute;
display: block;
transform: scale(var(--ggs,1));
width: 25px;
overflow: hidden;
border-radius: 2px;
}
.aiomatic-file-image {
    cursor: pointer;';
    if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
    {
        if($all_ok)
        {
            $reg_css_code .= 'right: 66px;';
        }
        else
        {
            $reg_css_code .= 'right: 38px;';
        }
    }
    else
    {
        if($all_ok)
        {
            $reg_css_code .= 'right: 38px;';
        }
        else
        {
            $reg_css_code .= 'right: 10px;';
        }
    }
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%);
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-stop-image {
user-select: none;
position: absolute;
transform: scale(var(--ggs,1));
width: 25px;
overflow: hidden;
border-radius: 2px;
}
.aiomatic-show-logs {
    user-select: none;
    position: absolute;
    transform: scale(var(--ggs,1));
    width: 25px;
    overflow: hidden;
    border-radius: 2px;
}
.aiomatic-show-logs {
    cursor: pointer;';
    $enable_file_uploads = false;
    if (isset($aiomatic_Chatbot_Settings['enable_file_uploads']) && $aiomatic_Chatbot_Settings['enable_file_uploads'] != '' && $file_uploads == '') 
    {
        $file_uploads = $aiomatic_Chatbot_Settings['enable_file_uploads'];
    }
    if(!empty($assistant_id) && ($file_uploads == 'on' || $file_uploads == 'yes' || $file_uploads == '1' || $file_uploads == 'enable' || $file_uploads == 'enabled'))
    {
        $is_file_search = get_post_meta($assistant_id, '_assistant_tools', true);
        if(is_array($is_file_search))
        {
            foreach($is_file_search as $isfs)
            {
                if($isfs['type'] == 'file_search')
                {
                    $enable_file_uploads = true;
                    break;
                }
            }
        }
    }
if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 94px;';
        }
        else
        {
            $reg_css_code .= 'right: 66px;';
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 66px;';
        }
        else
        {
            $reg_css_code .= 'right: 38px;';
        }
    }
}
else
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 66px;';
        }
        else
        {
            $reg_css_code .= 'right: 38px;';
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            $reg_css_code .= 'right: 38px;';
        }
        else
        {
            $reg_css_code .= 'right: 10px;';
        }
    }
}
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%)!important;
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-stop-image {
    cursor: pointer;';
    $allow_stream_stop = false;
    if (isset($aiomatic_Chatbot_Settings['allow_stream_stop']) && $aiomatic_Chatbot_Settings['allow_stream_stop'] == 'on' && $persistent === 'history' && $assistant_id == '') 
    {
        $allow_stream_stop = true;
    }
    $enable_file_uploads = false;
    if (isset($aiomatic_Chatbot_Settings['enable_file_uploads']) && $aiomatic_Chatbot_Settings['enable_file_uploads'] != '' && $file_uploads == '') 
    {
        $file_uploads = $aiomatic_Chatbot_Settings['enable_file_uploads'];
    }
    if(!empty($assistant_id) && ($file_uploads == 'on' || $file_uploads == 'yes' || $file_uploads == '1' || $file_uploads == 'enable' || $file_uploads == 'enabled'))
    {
        $is_file_search = get_post_meta($assistant_id, '_assistant_tools', true);
        if(is_array($is_file_search))
        {
            foreach($is_file_search as $isfs)
            {
                if($isfs['type'] == 'file_search')
                {
                    $enable_file_uploads = true;
                    break;
                }
            }
        }
    }
if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 122px;';
            }
            else
            {
                $reg_css_code .= 'right: 94px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 94px;';
            }
            else
            {
                $reg_css_code .= 'right: 66px;';
            }
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 94px;';
            }
            else
            {
                $reg_css_code .= 'right: 66px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 66px;';
            }
            else
            {
                $reg_css_code .= 'right: 38px;';
            }
        }
    }
}
else
{
    if($all_ok)
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 94px;';
            }
            else
            {
                $reg_css_code .= 'right: 66px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 66px;';
            }
            else
            {
                $reg_css_code .= 'right: 38px;';
            }
        }
    }
    else
    {
        if($enable_file_uploads)
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 66px;';
            }
            else
            {
                $reg_css_code .= 'right: 38px;';
            }
        }
        else
        {
            if($allow_stream_stop == true)
            {
                $reg_css_code .= 'right: 38px;';
            }
            else
            {
                $reg_css_code .= 'right: 10px;';
            }
        }
    }
}
$reg_css_code .= 'top: 53%;
    transform: translateY(-50%)!important;
    cursor: pointer;
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}
.aiomatic-gg-image {
    color: white;';
    if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
$reg_css_code .= '}';

$reg_css_code .= '.openai-relative
{
position: relative;
}
.openai-widget-user-name
{
line-height: 1.8;
}
.cr_none
{
display:none;
}
.cr_visible 
{
display: block !important;
}
.cr_abs{position: absolute;}
.aiomatic-gg-image::after,
.aiomatic-gg-image::before {
content: "";
display: block;
box-sizing: border-box;
position: absolute;
border: 2px solid
}
.aiomatic-gg-mute:hover {
i:before {
    transform: scale(.8) translate(-3px, 0) rotate(42deg);		
}
}
.aiomatic-hide {display:none!important;visibility:hidden}
.image_max_w_ai{width:100%;max-width:100%;}
.aiomatic-pdf-remove, .aiomatic-file-remove{
font-size: 30px;
justify-content: center;
align-items: center;
width: 18px;
line-height: unset;
font-family: Arial, serif;
border-radius: 50%;
font-weight: normal;
padding: 0;
margin: 0;
}
.aiomatic-loading-indicator {
position: absolute;
top: 5px;
left: 0;
width: 100%;
height: 100%;
display: flex;
align-items: center;
justify-content: center;
z-index: 10;
}
.aiomatic-gg-trash::after,
.aiomatic-gg-trash::before {
content: "";
display: block;
box-sizing: border-box;
position: absolute
}
.aiomatic-gg-trash::after {
background: currentColor;
border-radius: 3px;
width: 17px;
height: 2px;
top: -4px;
left: -5px
}
.aiomatic-gg-trash::before {
width: 10px;
height: 4px;
border: 2px solid;
border-bottom: transparent;
border-top-left-radius: 2px;
border-top-right-radius: 2px;
top: -7px;
left: -1px
}
.aiomatic-gg-trash {
box-sizing: border-box;
position: relative;
display: block;
transform: scale(var(--ggs,1));
width: 10px;
height: 12px;
border: 2px solid transparent;
box-shadow:
    0 0 0 1px,
    inset -2px 0 0,
    inset 2px 0 0;
border-bottom-left-radius: 1px;
border-bottom-right-radius: 1px;
margin-top: 4px;
}
.aiomatic-gg-mute {
margin-left:10px;
cursor: pointer;
top: -1px;
right: -3px;
height: 20px; 
width: 20px;  
position: relative;
overflow: hidden;
display: inline-block;
}
.aiomatic-gg-image::after {
transform: rotate(45deg);
border-radius: 3px;
width: 16px;
height: 16px;
top: 9px;
left: 6px
}
.aiomatic-gg-image::before {
width: 6px;
height: 6px;
border-radius: 100%;
top: 2px;
left: 2px
}
.aiomatic-cursor
{
cursor: pointer;
}
.aiomatic-left-padding
{
padding-left: 10px;
}
.aiomatic-gg-globe {
top:-1px;
position: relative;
transform: scale(var(--ggs,1));
width: 18px;
border-radius: 22px;
}
.aiomatic-gg-globe::after,
.aiomatic-gg-globe::before {
content: "";
position: absolute;
width: 8px;
border-radius: 100%;
top: -2px;
left: 3px;
}
.aiomatic-gg-unmute {
cursor: pointer;
margin-left:10px;
top: -1px;
right: -3px;
height: 20px; 
width: 20px; 
position: relative;
overflow: hidden;
display: inline-block;
}
.aiomatic-gg-image {
box-sizing: border-box;
position: absolute;
display: block;
transform: scale(var(--ggs,1));
width: 20px;
height: 16px;
overflow: hidden;
box-shadow: 0 0 0 2px;
border-radius: 2px;
cursor: pointer;
right: 10px;
top: 50%;
transform: translateY(-50%);
padding: 10px;
cursor: pointer;
}
.openai-file-document::after,
.openai-file-document::before {
content: "";
display: block;
box-sizing: border-box;
position: absolute;
}
.openai-file-document::before {
background: currentColor;
box-shadow: 0 4px 0, -6px -4px 0;
left: 0;
width: 10px;
height: 2px;
top: 8px;
}
.openai-file-document::after {
width: 6px;
height: 6px;
border-left: 2px solid;
border-bottom: 2px solid;
right: -1px;
top: -1px;
}
.openai-file-document
{
cursor: pointer;
box-sizing: border-box;
position: relative;
display: block;
transform: scale(var(--ggs,1));
width: 14px;
height: 16px;
border: 2px solid transparent;
border-right: 0;
border-top: 0;
box-shadow: 0 0 0 1.3px;
border-radius: 1px;
border-top-right-radius: 4px;
overflow: hidden;
transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}
.openai-card-header
{
margin: 0 auto;
position: absolute;
left: 0px;
padding: 3px;
border-radius: 0 50px 50px 0;
height: auto;
background: transparent;
padding-top: 10px;
display: flex;
min-height: 3.5rem;
align-items: center;
margin-bottom: 0;
position: relative;
}
.ai-export-txt
{
cursor: pointer;
padding-left:10px;
}
.ai-clear-chat
{
cursor: pointer;
padding-left:10px;
}
.openai-text-right
{
display: flex;
text-align: right;
margin-left: auto;
margin-right: 10px
}
.openai-d-flex
{
display: flex;
}';
$reg_css_code .= '.aiomatic-gg-unmute {
    i {
        display: block;
        width: 5.33px; 
        height: 5.33px;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'background:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        margin: 7px 0 0 1.33px;
    }
    i:after {
        content: "";
        position: absolute;
        width: 0;
        height: 0;
        border-style: solid;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'border-color: transparent' . $aiomatic_Chatbot_Settings['submit_color'] . ' transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'border-color:transparent #55a7e2 transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        border-width: 6.67px 9.33px 6.67px 10px; 
        left: -8.67px;
        top: 3.33px;
    }
}
.aiomatic-gg-globe,
.aiomatic-gg-globe::after,
.aiomatic-gg-globe::before {
    display: block;
    box-sizing: border-box;
    height: 18px;
    border: 2px solid ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= '
}
.aiomatic-gg-globe::after {
    width: 24px;
    height: 20px;
    border: 2px solid transparent;
    border-bottom: 2px solid ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= '
    top: -11px;
    left: -5px;
}
.aiomatic-gg-globe:hover {
    transform: scale(1.1);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
}
.ai-compare-button.active {
  background-color: ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '')
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#ffffff' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= ';
  border: 1px solid ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= ';
}
.aiomatic-globe-bar {
    position: absolute;
    width: 22px; 
    height: 2px;
    background: ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= $aiomatic_Chatbot_Settings['submit_color'] . ';';
    }
    else
    {
        $reg_css_code .= '#55a7e2;';
    }
    $reg_css_code .= '
    top: 50%;
    left: 50%; 
    transform: translate(-50%, -50%) rotate(45deg);
}
.aiomatic-gg-mute {
    i {
        display: block;
        width: 5.33px; 
        height: 5.33px;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'background:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        margin: 7px 0 0 1.33px; 
    }
    i:after {
        content: "";
        position: absolute;
        width: 0;
        height: 0;
        border-style: solid;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'border-color: transparent' . $aiomatic_Chatbot_Settings['submit_color'] . ' transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'border-color:transparent #55a7e2 transparent transparent ' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        border-width: 6.67px 9.33px 6.67px 10px; 
        left: -8.67px; 
        top: 3.33px; 
    }
    i:before {
        transform: rotate(45deg);
        border-radius: 0 33.33px 0 0; 
        content: "";
        position: absolute;
        width: 3.33px; 
        height: 3.33px; 
        border-style: double;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        else
        {
            $reg_css_code .= 'border-color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        }
        $reg_css_code .= '
        border-width: 4.67px 4.67px 0 0;
        left: 12px; 
        top: 6px;
        transition: all 0.2s ease-out;
    }
}
.aiomatic-gg-trash {';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'color:#55a7e2' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $reg_css_code .= '
}
.aiomatic-gg-trash:hover {
    transform: scale(1.1);
    ';
    if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
    {
        $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    else
    {
        $reg_css_code .= 'background-color:#f0f0f0' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    }
    $loader_color = '#fff';
    if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
    {
        $loader_color = $aiomatic_Chatbot_Settings['persona_name_color'];
    }
    $reg_css_code .= '
}
.aiomatic-loading-indicator::before {
    content: \'\';
    box-sizing: border-box;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border-top: 3px solid ' . $loader_color. ';
    border-right: 3px solid transparent;
    animation: keysspin 1s linear infinite;
}
@keyframes keysspin {
    to {
        transform: rotate(360deg);
    }
}
#aiomatic-video-wrapper' . $chatid . '
{
    position: relative;
    padding-top: 10px;
}
.aiomatic-hide {display:none!important;}';
        $reg_css_code .= '.openai-ai-form{
            border-radius: 30px;';
        if($general_background != '')
        {
            $reg_css_code .= 'background-color:' . $general_background . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        else
        {
            if (isset($aiomatic_Chatbot_Settings['general_background']) && $aiomatic_Chatbot_Settings['general_background'] != '') 
            {
                $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['general_background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
                $css_added = true;
            }
        }
        if (isset($aiomatic_Chatbot_Settings['width']) && $aiomatic_Chatbot_Settings['width'] != '') 
        {
            $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        if($bubble_width == 'full' || empty($bubble_width))
        {
            $reg_css_code .= '.ai-bubble{width:100%!important;}';
            $css_added = true;
        }
        elseif($bubble_width == 'auto')
        {
            $reg_css_code .= '.ai-bubble{width:auto!important;}';
            $css_added = true;
        }
        if($bubble_alignment == 'left' || empty($bubble_alignment))
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-left:unset!important;margin-right:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_alignment == 'right')
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-right:unset!important;margin-left:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_alignment == 'center')
        {
            $reg_css_code .= '.ai-bubble.ai-other{margin-left:auto!important;margin-right:auto!important;}';
            $css_added = true;
        }
        if($bubble_user_alignment == 'left')
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-left:unset!important;margin-right:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_user_alignment == 'right' || empty($bubble_user_alignment))
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-right:unset!important;margin-left:auto!important;}';
            $css_added = true;
        }
        elseif($bubble_user_alignment == 'center')
        {
            $reg_css_code .= '.ai-bubble.ai-mine{margin-left:auto!important;margin-right:auto!important;}';
            $css_added = true;
        }
        $reg_css_code .= '.aiomatic-conversation-starter {';
if (isset($aiomatic_Chatbot_Settings['user_font_color']) && $aiomatic_Chatbot_Settings['user_font_color'] != '') 
{
    $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['user_font_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $css_added = true;
}
if (isset($aiomatic_Chatbot_Settings['user_background_color']) && $aiomatic_Chatbot_Settings['user_background_color'] != '') 
{
    $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['user_background_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
    $css_added = true;
}
    $reg_css_code .= 'padding: 10px 15px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    max-width: 300px;
    text-align: center;
}';
        $reg_css_code .= '.ai-mine{';
        if (isset($aiomatic_Chatbot_Settings['user_font_color']) && $aiomatic_Chatbot_Settings['user_font_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['user_font_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['user_background_color']) && $aiomatic_Chatbot_Settings['user_background_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['user_background_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
if(!empty($avatar_url))
{
    $css_added = true;
    $reg_css_code .= '.ai-avatar.ai-other {
    background-image: url("' . esc_url($avatar_url) . '");
    background-size: cover;
    background-position: center;
    margin-left: 3px;
    margin-right: 3px;
}';
}

if($additional_bots != '')
{
    $bot_list = explode(',', $additional_bots);
    $bot_list = array_map('trim', $bot_list);
    $bot_list = array_filter($bot_list);
    foreach($bot_list as $my_bot)
    {
        if(is_numeric($my_bot))
        {
            $bot_post = get_post($my_bot);
            if($bot_post != null)
            {
                if($bot_post->post_type === 'aiomatic_personas' || $bot_post->post_type === 'aiomatic_assistants')
                {
                    $att_src = get_the_post_thumbnail_url( $bot_post->ID, 'thumbnail' );
                    if ( empty($att_src) )
                    {
                        $att_src = plugins_url('images/placeholder.jpg', __FILE__);
                    }
                    $css_added = true;
                    $reg_css_code .= '.ai-avatar.ai-other.aiomatic-ainum-' . esc_html($bot_post->ID) . ' {
background-image: url("' . esc_url($att_src) . '");
background-size: cover;
background-position: center;
margin-left: 3px;
margin-right: 3px;
}';
                }
            }
        }
    }
}
if(!empty($avatar_url_user))
{
    $css_added = true;
    $reg_css_code .= '.ai-avatar.ai-mine {
        background-image: url("' . esc_url($avatar_url_user) . '");
        background-size: cover;
        background-position: center;
        margin-left: 3px;
        margin-right: 3px;
}';
}
        $reg_css_code .= '.ai-other{';
        if (isset($aiomatic_Chatbot_Settings['ai_font_color']) && $aiomatic_Chatbot_Settings['ai_font_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['ai_font_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['ai_background_color']) && $aiomatic_Chatbot_Settings['ai_background_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['ai_background_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '')
        {
            $go_color = $aiomatic_Chatbot_Settings['input_text_color'];
        }
        else
        {
            $go_color = '#ffffff';
        }
        $reg_css_code .= '}
.aiomatic-pdf-loading, .aiomatic-file-loading{
    border: 2px solid ' . $go_color . ';
    border-bottom-color: transparent;
    border-radius: 50%;
    box-sizing: border-box;
    animation: aiomatic_rotation 1s linear infinite;
    display: inline-block;
    width: 18px;
    height: 18px;
}
@keyframes aiomatic_rotation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}';
        $reg_css_code .= '#aiomatic_chat_input' . $chatid . '{min-height:62px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_border_color']) && $aiomatic_Chatbot_Settings['input_border_color'] != '') 
        {
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['input_border_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-name' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_name_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-role' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_role_color']) && $aiomatic_Chatbot_Settings['persona_role_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_role_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic-ai-text{';
        if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_name_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic-ai-input{min-height:62px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        if (isset($aiomatic_Chatbot_Settings['background']) && $aiomatic_Chatbot_Settings['background'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['background'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_border_color']) && $aiomatic_Chatbot_Settings['input_border_color'] != '') 
        {
            $reg_css_code .= 'border-color:' . $aiomatic_Chatbot_Settings['input_border_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['input_text_color']) && $aiomatic_Chatbot_Settings['input_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-name' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_name_color']) && $aiomatic_Chatbot_Settings['persona_name_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_name_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-persona-role' . $chatid . '{'; 
        if (isset($aiomatic_Chatbot_Settings['persona_role_color']) && $aiomatic_Chatbot_Settings['persona_role_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['persona_role_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#aiomatic_chat_input' . $chatid . '::placeholder{';
        if (isset($aiomatic_Chatbot_Settings['input_placeholder_color']) && $aiomatic_Chatbot_Settings['input_placeholder_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['input_placeholder_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '.aiomatic-close-button{';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{margin-top: 5px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
        $reg_css_code .= '#openai-chat-speech-button' . $chatid . '{';
        if ($voice_color != '') 
        {
            $reg_css_code .= 'background-color:' . $voice_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        $reg_css_code .= '#openai-image-speech-button' . $chatid . '{';
        if ($voice_color != '') 
        {
            $reg_css_code .= 'background-color:' . $voice_color . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        if (isset($aiomatic_Chatbot_Settings['open_width']) && $aiomatic_Chatbot_Settings['open_width'] != '') 
        {
            $reg_css_code .= '.aiomatic-open-image{width:' . $aiomatic_Chatbot_Settings['open_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;') . '}';
            $css_added = true;
        }
        $reg_css_code .= '#aichatsubmitbut' . $chatid . '{margin-top: 5px' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}
#aiimagechatsubmitbut' . $chatid . '{margin-left:20px;';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $reg_css_code .= 'background-color:' . $aiomatic_Chatbot_Settings['submit_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        if (isset($aiomatic_Chatbot_Settings['submit_text_color']) && $aiomatic_Chatbot_Settings['submit_text_color'] != '') 
        {
            $reg_css_code .= 'color:' . $aiomatic_Chatbot_Settings['submit_text_color'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}
        .cr_transp_canvas{background-color: transparent;';
        if (isset($aiomatic_Chatbot_Settings['canvas_avatar_width']) && $aiomatic_Chatbot_Settings['canvas_avatar_width'] != '') 
        {
            $reg_css_code .= 'width:' . $aiomatic_Chatbot_Settings['canvas_avatar_width'] . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $css_added = true;
        }
        $reg_css_code .= '}';
        if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
        {
            $reg_css_code .= 'form.aiomatic-window{';
            $reg_css_code .= 'display:none;';
            $reg_css_code .= '}';
            $css_added = true;
        }
        if($window_width != '')
        {
            preg_match_all('#(\d+)\s*px#i', $window_width, $zamatches);
            if(isset($zamatches[1][0]))
            {
                $myw = intval($zamatches[1][0]) + 100;
                $wwidth = $myw . 'px';
            }
            else
            {
                $wwidth = $window_width;
            }
            $reg_css_code .= '@media only screen and (min-width: ' . $wwidth . ') {form.aiomatic-window{';
            $reg_css_code .= 'width:' . $window_width . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'max-width:' . $window_width . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= '}}';
            $reg_css_code .= '@media only screen and (max-width: ' . $wwidth . ') {form.aiomatic-window{';
            $reg_css_code .= 'width:75%' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= 'max-width:75%' . ((isset($aiomatic_Main_Settings['not_important']) && $aiomatic_Main_Settings['not_important'] === 'on') ? ';' : '!important;');
            $reg_css_code .= '}}';
            $css_added = true;
        }
        if($css_added === true)
        {
            wp_add_inline_style( $name . 'css-ai-front', $reg_css_code );
        }
        $all_models = aiomatic_get_all_models();
        $models = $all_models; 
        if($model != 'default' && !in_array($model, $models))
        {
            $return_me .= 'Invalid model provided!';
            return $return_me;
        }
        if($temp != 'default' && floatval($temp) < 0 || floatval($temp) > 1)
        {
            $return_me .= 'Invalid temperature provided!';
            return $return_me;
        }
        if($top_p != 'default' && floatval($top_p) < 0 || floatval($top_p) > 1)
        {
            $return_me .= 'Invalid top_p provided!';
            return $return_me;
        }
        if($presence != 'default' && floatval($presence) < -2 || floatval($presence) > 2)
        {
            $return_me .= 'Invalid presence_penalty provided!';
            return $return_me;
        }
        if($frequency != 'default' && floatval($frequency) < -2 || floatval($frequency) > 2)
        {
            $return_me .= 'Invalid frequency_penalty provided!';
            return $return_me;
        }
        // Display the form
        if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
        {
            $show_me = '<img class="aiomatic-open-image" src="' . plugins_url('res/icons/1.png', __FILE__) . '">';
            if (isset($aiomatic_Chatbot_Settings['chatbot_icon']) && $aiomatic_Chatbot_Settings['chatbot_icon'] != '') 
            {
                if($aiomatic_Chatbot_Settings['chatbot_icon'] != 'x')
                {
                    $show_me = '<img class="aiomatic-open-image" src="' . plugins_url('res/icons/' . $aiomatic_Chatbot_Settings['chatbot_icon'] . '.png', __FILE__) . '">';
                }
                elseif (isset($aiomatic_Chatbot_Settings['chatbot_icon_html']) && $aiomatic_Chatbot_Settings['chatbot_icon_html'] != '') 
                {
                    $show_me = trim($aiomatic_Chatbot_Settings['chatbot_icon_html']);
                    if(aiomatic_starts_with($show_me, 'http') === true)
                    {
                        $show_me = '<img class="aiomatic-open-image" src="' . esc_url_raw($show_me) . '" width="32" height="32">';
                    }
                }
            }
            $return_me .= '<span id="aiomatic-open-button' . $chatid . '" class="aiomatic-open-button aiomatic-window aiomatic-' . $window_location . '" onclick="document.getElementById(\'openai-ai-chat-form-' . $chatid . '\').style.display = \'inherit\';document.getElementById(\'aiomatic-open-button' . $chatid . '\').style.display = \'none\';">' . $show_me . '</span>';
        }
        $return_me .= '
            <form id="openai-ai-chat-form-' . $chatid . '" method="post" class="openai-ai-form';
        if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
        {
            $return_me .= ' aiomatic-window aiomatic-' . $window_location;
        }
        $return_me .= '">';
        if (isset($aiomatic_Chatbot_Settings['show_header']) && $aiomatic_Chatbot_Settings['show_header'] != '' && $show_header == '')
        {
            $show_header = $aiomatic_Chatbot_Settings['show_header'];
        }
        $hclass = '';
        if($show_header != 'show')
        {
            $hclass = ' aiomatic-hide';
        }
        if (isset($aiomatic_Chatbot_Settings['show_dltxt']) && $aiomatic_Chatbot_Settings['show_dltxt'] != '' && $show_dltxt == '')
        {
            $show_dltxt = $aiomatic_Chatbot_Settings['show_dltxt'];
        }
        if (isset($aiomatic_Chatbot_Settings['show_mute']) && $aiomatic_Chatbot_Settings['show_mute'] != '' && $show_mute == '')
        {
            $show_mute = $aiomatic_Chatbot_Settings['show_mute'];
        }
        if (isset($aiomatic_Chatbot_Settings['show_internet']) && $aiomatic_Chatbot_Settings['show_internet'] != '' && $show_internet == '')
        {
            $show_internet = $aiomatic_Chatbot_Settings['show_internet'];
        }
        if (isset($aiomatic_Chatbot_Settings['show_clear']) && $aiomatic_Chatbot_Settings['show_clear'] != '' && $show_clear == '')
        {
            $show_clear = $aiomatic_Chatbot_Settings['show_clear'];
        }
        $tclass = '';
        if($show_dltxt != 'show')
        {
            $tclass = ' aiomatic-hide';
        }
        $mclass = '';
        if($show_mute != 'show' || $chatbot_text_speech == 'off')
        {
            $mclass = ' aiomatic-hide';
        }
        $iclass = '';
        if(!isset($aiomatic_Main_Settings['internet_chat_short']) || $aiomatic_Main_Settings['internet_chat_short'] != 'on')
        {
            $iclass = ' aiomatic-hide';
        }
        else
        {
            if($show_internet != 'show' || $internet_access == 'off' || $internet_access == 'disable' || $internet_access == 'disabled' || $internet_access == 'Disabled' || $internet_access === '0' || $internet_access === '0')
            {
                $iclass = ' aiomatic-hide';
            }
        }
        $dclass = '';
        if($show_clear != 'show')
        {
            $dclass = ' aiomatic-hide';
        }
        $ai_prep = trim($ai_message_preppend, ': ');
        if($ai_prep != '')
        {
            $ai_prep .= ': ';
        }
        $user_prep = trim($user_message_preppend, ': ');
        if($user_prep != '')
        {
            $user_prep .= ': ';
        }
        if($enable_front_end == 'on' || $enable_front_end == '1' || $enable_front_end == 'true' || $enable_front_end == 'yes' || $enable_front_end == 'front' || $enable_front_end == 'back' || $enable_front_end == 'both')
        {
            $return_me .= ' <span class="aiomatic-close-button" onclick="document.getElementById(\'openai-ai-chat-form-' . $chatid . '\').style.display = \'none\';document.getElementById(\'aiomatic-open-button' . $chatid . '\').style.display = \'inherit\';">&times;</span>';
        }
        if($chatbot_text_speech == 'didstream')
        {
            if (isset($aiomatic_Chatbot_Settings['did_height']) && $aiomatic_Chatbot_Settings['did_height'] != '') 
            {
                $did_height = $aiomatic_Chatbot_Settings['did_height'];
            }
            else
            {
                $did_height = '300';
            }
            if (isset($aiomatic_Chatbot_Settings['did_width']) && $aiomatic_Chatbot_Settings['did_width'] != '') 
            {
                $did_width = $aiomatic_Chatbot_Settings['did_width'];
            }
            else
            {
                $did_width = '300';
            }
            $return_me .= '<div id="aiomatic-video-wrapper' . $chatid . '">
<div class="aiomatic-text-center">
    <div id="aiomatic-loading-indicator' . $chatid . '" class="aiomatic-loading-indicator"></div>
    <video id="talk-video' . $chatid . '" width="' . $did_width . '" height="' . $did_height . '" autoplay="autoplay" muted="muted"></video>
</div>
</div>';
        }
        elseif($chatbot_text_speech == 'azure')
        {
            wp_register_script( $name . '-azure-speech-sdk', 'https://aka.ms/csspeech/jsbrowserpackageraw');
            wp_enqueue_script( $name . '-azure-speech-sdk' );
            $return_me .= '
            <div id="aiomatic-video-wrapper' . $chatid . '">
  <div id="overlayArea' . $chatid . '" class="cr_abs" hidden="hidden">
  </div>
  <div id="remoteVideo' . $chatid . '"></div>
  <canvas id="canvas' . $chatid . '" width="1920" height="1080" class="cr_transp_canvas" hidden="hidden"></canvas>
  <canvas id="tmpCanvas' . $chatid . '" width="1920" height="1080" hidden="hidden"></canvas>
</div>';
        }
        if(!empty($custom_header))
        {
            $return_me .= '<div class="openai-custom-header">' . $custom_header . '</div>';
        }
        $return_me .= '<div class="openai-card-header' . $hclass . '">
<div class="w-100">
    <div class="openai-d-flex">
        <div class="overflow-hidden openai-mr-4">' . $avatar_src . '</div>
        <div class="openai-widget-user-name"><span id="openai-persona-name' . $chatid . '" class="openai-persona-name openai-font-weight-bold">' . esc_html(trim($ai_message_preppend, ': ')) . '</span><br><span id="openai-persona-role' . $chatid . '" class="openai-persona-role">' . esc_html($ai_role) . '</span></div>
    </div>
</div>
<div class="openai-text-right">
<a id="ai-mute-chat' . $chatid . '" class="aiomatic-gg-mute ai-mute-chat template-button mr-2 download-btn' . $mclass . '"><i title="' . esc_html__("Mute/Unmute", 'aiomatic-automatic-ai-content-writer') . '"></i></a>
<a id="ai-internet' . $chatid . '" chatid="' . $chatid . '" class="aiomatic-gg-globalist aiomatic-left-padding aiomatic-cursor template-button mr-2 download-btn' . $iclass . '"><i id="aiomatic-globe-overlay-mother' . $chatid . '" class="aiomatic-gg-globe" title="' . esc_html__("Disable Chatbot Internet Access", 'aiomatic-automatic-ai-content-writer') . '"><i id="aiomatic-globe-overlay' . $chatid . '" class="aiomatic-globe-overlay';
            if (isset($aiomatic_Chatbot_Settings['show_internet_default']))
            {
                if($aiomatic_Chatbot_Settings['show_internet_default'] == 'on')
                {
                }
                else
                {
                    $return_me .= ' aiomatic-globe-bar';
                }
            }
            else
            {
                $return_me .= ' aiomatic-globe-bar';
            }
            $return_me .= '"></i></i></a>
<a id="ai-export-txt' . $chatid . '" class="ai-export-txt template-button mr-2 download-btn' . $tclass . '"><i title="' . esc_html__("Export Chat Conversation To File", 'aiomatic-automatic-ai-content-writer') . '" class="openai-file-document"></i></a>
<a id="ai-clear-chat' . $chatid . '" class="ai-clear-chat template-button mr-2 download-btn' . $dclass . '"><i title="' . esc_html__("Clear Chat Conversation", 'aiomatic-automatic-ai-content-writer') . '" class="aiomatic-gg-trash"></i></a>
</div>
</div>';
        $return_me .= '<div class="code-form-top-pad form-group">
<div id="aiomatic_chat_history' . $chatid . '" class="aiomatic_chat_history ai-chat form-control"';
        if (isset($aiomatic_Chatbot_Settings['enable_copy']) && $aiomatic_Chatbot_Settings['enable_copy'] != '') 
        {
            $return_me .= ' title="' . esc_html__('Click on a bubble to copy its content!', 'aiomatic-automatic-ai-content-writer') . '"';
        }
        $return_me .= '>';
        $complete_me = '';
        $chat_initial_messages = '';
        if($thread_id != '')
        {
            if($assistant_id != '')
            {
                require_once(dirname(__FILE__) . "/res/aiomatic-assistants-api.php");
                try
                {
                    if (isset($aiomatic_Main_Settings['app_id']) && trim($aiomatic_Main_Settings['app_id']) != '') 
                    {
                        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                        $appids = array_filter($appids);
                        $token = $appids[array_rand($appids)];
                        if(!aiomatic_is_aiomaticapi_key($token) && (!isset($aiomatic_Main_Settings['api_selector']) || (trim($aiomatic_Main_Settings['api_selector']) != 'azure' && trim($aiomatic_Main_Settings['api_selector']) != 'custom')))
                        {
                            $old_messages = aiomatic_openai_list_messages($token, $thread_id, 100, 'asc');
                            if(isset($old_messages['data']) && is_array($old_messages['data']))
                            {
                                foreach($old_messages['data'] as $om)
                                {
                                    if(isset($om['content'][0]['text']['value']))
                                    {
                                        if($om['role'] == 'user')
                                        {
                                            $chat_initial_messages .= '<div class="ai-wrapper">';
                                            if($bubble_user_alignment != 'right' && !empty($avatar_url_user) && $show_user_avatar == 'show')
                                            {
                                                $chat_initial_messages .= '<div class="ai-avatar ai-mine"></div>';
                                            }
                                            $chat_initial_messages .= '<div class="ai-bubble ai-mine">' . $om['content'][0]['text']['value'] . '</div>';
                                            if($bubble_user_alignment == 'right' && !empty($avatar_url_user) && $show_user_avatar == 'show')
                                            {
                                                $chat_initial_messages .= '<div class="ai-avatar ai-mine"></div>';
                                            }
                                            $chat_initial_messages .= '</div>';
                                        }
                                        elseif($om['role'] == 'assistant')
                                        {
                                            $chat_initial_messages .= '<div class="ai-wrapper">';
                                            if($bubble_alignment != 'right' && !empty($avatar_url) && $show_ai_avatar == 'show')
                                            {
                                                $chat_initial_messages .= '<div class="ai-avatar ai-other"></div>';
                                            }
                                            $chat_initial_messages .= '<div class="ai-bubble ai-other">' . $om['content'][0]['text']['value'] . '</div>';
                                            if($bubble_alignment == 'right' && !empty($avatar_url) && $show_ai_avatar == 'show')
                                            {
                                                $chat_initial_messages .= '<div class="ai-avatar ai-other"></div>';
                                            }
                                            $chat_initial_messages .= '</div>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                catch(Exception $e)
                {
                    aiomatic_log_to_file('Failed to list persistent messages for thread ID: ' . $thread_id);
                }
            }
        }
        else
        {
            if(!empty($chat_history))
            {
                $chat_initial_messages .= $chat_history;
            }
            if($ai_first_message != '')
            {
                if(stristr($ai_first_message, '%%') !== false)
                {
                    $post_link = '';
                    $post_title = '';
                    $blog_title = html_entity_decode(get_bloginfo('title'));
                    $post_excerpt = '';
                    $final_content = '';
                    $user_name = '';
                    $featured_image = '';
                    $post_cats = '';
                    $post_tagz = '';
                    $postID = '';
                    global $post;
                    if(isset($post->ID))
                    {
                        $post_link = get_permalink($post->ID);
                        $blog_title       = html_entity_decode(get_bloginfo('title'));
                        $author_obj       = get_user_by('id', $post->post_author);
                        if($author_obj !== false)
                        {
                            $user_name        = $author_obj->user_nicename;
                        }
                        $final_content = $post->post_content;
                        $post_title    = $post->post_title;
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
                        $post_excerpt = $post->post_excerpt;
                        $postID = $post->ID;
                    }
                    $ai_first_message = aiomatic_replaceAIPostShortcodes($ai_first_message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                    if (filter_var($ai_first_message, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($ai_first_message, '.txt'))
                    {
                        $txt_content = aiomatic_get_web_page($ai_first_message);
                        if ($txt_content !== FALSE) 
                        {
                            $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                            $txt_content = array_filter($txt_content);
                            if(count($txt_content) > 0)
                            {
                                $txt_content = $txt_content[array_rand($txt_content)];
                                if(trim($txt_content) != '') 
                                {
                                    $ai_first_message = $txt_content;
                                    $ai_first_message = aiomatic_replaceAIPostShortcodes($ai_first_message, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                                }
                            }
                        }
                    }
                    $current_user = wp_get_current_user();
                    if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
                    {
                        $ai_first_message = str_replace('%%user_name%%', '', $ai_first_message);
                        $ai_first_message = str_replace('%%user_email%%', '' , $ai_first_message);
                        $ai_first_message = str_replace('%%user_display_name%%', '', $ai_first_message);
                        $ai_first_message = str_replace('%%user_role%%', '', $ai_first_message);
                        $ai_first_message = str_replace('%%user_id%%', '' , $ai_first_message);
                        $ai_first_message = str_replace('%%user_firstname%%', '' , $ai_first_message);
                        $ai_first_message = str_replace('%%user_lastname%%', '' , $ai_first_message);
                        $ai_first_message = str_replace('%%user_description%%', '' , $ai_first_message);
                        $ai_first_message = str_replace('%%user_url%%', '' , $ai_first_message);
                    }
                    else
                    {
                        $ai_first_message = str_replace('%%user_name%%', $current_user->user_login, $ai_first_message);
                        $ai_first_message = str_replace('%%user_email%%', $current_user->user_email , $ai_first_message);
                        $ai_first_message = str_replace('%%user_display_name%%', $current_user->display_name , $ai_first_message);
                        $ai_first_message = str_replace('%%user_role%%', implode(',', $current_user->roles), $ai_first_message);
                        $ai_first_message = str_replace('%%user_id%%', $current_user->ID , $ai_first_message);
                        $ai_first_message = str_replace('%%user_firstname%%', $current_user->user_firstname , $ai_first_message);
                        $ai_first_message = str_replace('%%user_lastname%%', $current_user->user_lastname , $ai_first_message);
                        $user_desc = get_the_author_meta( 'description', $current_user->ID );
                        $ai_first_message = str_replace('%%user_description%%', $user_desc , $ai_first_message);
                        $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                        $ai_first_message = str_replace('%%user_url%%', $user_url , $ai_first_message);
                    }
                }
                $fm = preg_split('/\r\n|\r|\n/', trim($ai_first_message));
                $fm = array_filter($fm);
                if(empty($chat_history))
                {
                    $chat_initial_messages .= '<div class="ai-wrapper">';
                    if($bubble_alignment != 'right' && !empty($avatar_url) && $show_ai_avatar == 'show')
                    {
                        $chat_initial_messages .= '<div class="ai-avatar ai-other"></div>';
                    }
                    $chat_initial_messages .= '<div class="ai-bubble ai-other">' . $fm[0] . '</div>';
                    if($bubble_alignment == 'right' && !empty($avatar_url) && $show_ai_avatar == 'show')
                    {
                        $chat_initial_messages .= '<div class="ai-avatar ai-other"></div>';
                    }
                    $chat_initial_messages .= '</div>';
                }
                array_shift($fm);
                if(count($fm) > 0)
                {
                    $complete_me .= '<input type="hidden" id="aiomatic_message_input' . $chatid . '" value="' . esc_attr(implode('\r\n', $fm)) . '">';
                }
            }
        }
        $return_me .= $chat_initial_messages;
        if (isset($aiomatic_Chatbot_Settings['chat_editing']) && trim($aiomatic_Chatbot_Settings['chat_editing']) != '')
        {
            $chat_editing = trim($aiomatic_Chatbot_Settings['chat_editing']);
        }
        else
        {
            $chat_editing = '';
        }
        $show_gdpr = '0';
        if (isset($aiomatic_Chatbot_Settings['show_gdpr']) && $aiomatic_Chatbot_Settings['show_gdpr'] == 'on')
        {
            $show_gdpr = '1';
        }
        $bots_data = array();
        if($additional_bots != '')
        {
            $bot_list = explode(',', $additional_bots);
            $bot_list = array_map('trim', $bot_list);
            $bot_list = array_filter($bot_list);
            foreach($bot_list as $my_bot)
            {
                if(is_numeric($my_bot))
                {
                    $bot_post = get_post($my_bot);
                    if($bot_post != null)
                    {
                        if(!empty(trim($bot_post->post_title)))
                        {
                            if($bot_post->post_type === 'aiomatic_personas' || $bot_post->post_type === 'aiomatic_assistants')
                            {
                                $new_bot['name'] = $bot_post->post_title;
                                $new_bot['ID'] = $bot_post->ID;
                                $botprompt = $bot_post->post_content;
                                $botprompt = do_shortcode($botprompt);
                                if(stristr($botprompt, '%%') !== false)
                                {
                                    $post_link = '';
                                    $post_title = '';
                                    $blog_title = html_entity_decode(get_bloginfo('title'));
                                    $post_excerpt = '';
                                    $final_content = '';
                                    $user_name = '';
                                    $featured_image = '';
                                    $post_cats = '';
                                    $post_tagz = '';
                                    $postID = '';
                                    global $post;
                                    if(isset($post->ID))
                                    {
                                        $post_link = get_permalink($post->ID);
                                        $blog_title       = html_entity_decode(get_bloginfo('title'));
                                        $author_obj       = get_user_by('id', $post->post_author);
                                        if($author_obj !== false)
                                        {
                                            $user_name        = $author_obj->user_nicename;
                                        }
                                        $final_content = $post->post_content;
                                        $post_title    = $post->post_title;
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
                                        $post_excerpt = $post->post_excerpt;
                                        $postID = $post->ID;
                                    }
                                    $botprompt = aiomatic_replaceAIPostShortcodes($botprompt, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                                    if (filter_var($botprompt, FILTER_VALIDATE_URL) !== false && aiomatic_endsWith($botprompt, '.txt'))
                                    {
                                        $txt_content = aiomatic_get_web_page($botprompt);
                                        if ($txt_content !== FALSE) 
                                        {
                                            $txt_content = preg_split('/\r\n|\r|\n/', $txt_content);
                                            $txt_content = array_filter($txt_content);
                                            if(count($txt_content) > 0)
                                            {
                                                $txt_content = $txt_content[array_rand($txt_content)];
                                                if(trim($txt_content) != '') 
                                                {
                                                    $botprompt = $txt_content;
                                                    $botprompt = aiomatic_replaceAIPostShortcodes($botprompt, $post_link, $post_title, $blog_title, $post_excerpt, $final_content, $user_name, $featured_image, $post_cats, $post_tagz, $postID, '', '', '', '', '', '');
                                                    $botprompt = do_shortcode($botprompt);
                                                }
                                            }
                                        }
                                    }
                                    $current_user = wp_get_current_user();
                                    if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
                                    {
                                        $botprompt = str_replace('%%user_name%%', '', $botprompt);
                                        $botprompt = str_replace('%%user_email%%', '' , $botprompt);
                                        $botprompt = str_replace('%%user_display_name%%', '', $botprompt);
                                        $botprompt = str_replace('%%user_role%%', '', $botprompt);
                                        $botprompt = str_replace('%%user_id%%', '' , $botprompt);
                                        $botprompt = str_replace('%%user_firstname%%', '' , $botprompt);
                                        $botprompt = str_replace('%%user_lastname%%', '' , $botprompt);
                                        $botprompt = str_replace('%%user_description%%', '' , $botprompt);
                                        $botprompt = str_replace('%%user_url%%', '' , $botprompt);
                                    }
                                    else
                                    {
                                        $botprompt = str_replace('%%user_name%%', $current_user->user_login, $botprompt);
                                        $botprompt = str_replace('%%user_email%%', $current_user->user_email , $botprompt);
                                        $botprompt = str_replace('%%user_display_name%%', $current_user->display_name , $botprompt);
                                        $botprompt = str_replace('%%user_role%%', implode(',', $current_user->roles), $botprompt);
                                        $botprompt = str_replace('%%user_id%%', $current_user->ID , $botprompt);
                                        $botprompt = str_replace('%%user_firstname%%', $current_user->user_firstname , $botprompt);
                                        $botprompt = str_replace('%%user_lastname%%', $current_user->user_lastname , $botprompt);
                                        $user_desc = get_the_author_meta( 'description', $current_user->ID );
                                        $botprompt = str_replace('%%user_description%%', $user_desc , $botprompt);
                                        $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                                        $botprompt = str_replace('%%user_url%%', $user_url , $botprompt);
                                    }
                                }
                                if($bot_post->post_type === 'aiomatic_assistants')
                                {
                                    $botassistantid = $bot_post->ID;
                                    $botmodel = get_post_meta($bot_post->ID, '_assistant_model', true);
                                }
                                elseif($bot_post->post_type === 'aiomatic_personas')
                                {
                                    $botassistantid = '';
                                    $botmodel = get_post_meta($bot_post->ID, '_persona_model', true);
                                }
                                if(empty($botmodel))
                                {
                                    $botmodel = AIOMATIC_DEFAULT_MODEL;
                                }
                                $new_bot['assistentid'] = $botassistantid;
                                $new_bot['model'] = $botmodel;
                                $new_bot['prompt'] = $botprompt;
                                $bots_data[] = $new_bot;
                            }
                        }
                    }
                }
            }
        }
        if(strstr($embeddings_namespace, '%%') !== false)
        {
            $current_user = wp_get_current_user();
            if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
            {
                $embeddings_namespace = str_replace('%%user_name%%', '', $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_email%%', '' , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_display_name%%', '', $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_role%%', '', $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_id%%', '' , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_firstname%%', '' , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_lastname%%', '' , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_description%%', '' , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_url%%', '' , $embeddings_namespace);
            }
            else
            {
                $embeddings_namespace = str_replace('%%user_name%%', $current_user->user_login, $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_email%%', $current_user->user_email , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_display_name%%', $current_user->display_name, $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_role%%', implode(',', $current_user->roles), $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_id%%', $current_user->ID , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_firstname%%', $current_user->user_firstname , $embeddings_namespace);
                $embeddings_namespace = str_replace('%%user_lastname%%', $current_user->user_lastname , $embeddings_namespace);
                $user_desc = get_the_author_meta( 'description', $current_user->ID );
                $embeddings_namespace = str_replace('%%user_description%%', $user_desc , $embeddings_namespace);
                $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                $embeddings_namespace = str_replace('%%user_url%%', $user_url , $embeddings_namespace);
            }
        }
        $return_me .= '</div>';
        if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
        {
            $return_me .= '<input type="file" id="aiomatic_vision_input' . $chatid . '" accept="image/*" class="aiomatic-hide">';
        }
        if($enable_file_uploads)
        {
            $return_me .= '<input type="file" id="aiomatic_file_input' . $chatid . '" class="aiomatic-hide">';
        }
        if ($upload_pdf == 'on' || $upload_pdf == 'enabled' || $upload_pdf == 'yes' || $upload_pdf == '1' || (isset($aiomatic_Chatbot_Settings['upload_pdf']) && $aiomatic_Chatbot_Settings['upload_pdf'] == 'on' && $upload_pdf != 'disabled' && $upload_pdf != 'no' && $upload_pdf != '0' && $upload_pdf != 'off'))
        {
            if($all_ok === true)
            {
                $return_me .= '<input type="file" id="aiomatic_pdf_input' . $chatid . '" accept="application/pdf" class="aiomatic-hide">';
            }
        }
        if (!empty($bots_data)) 
        {
            $return_me .= '<div class="ai-model-listing-container">';
            $return_me .= '<input type="hidden" id="aiomatic_default_hidden_' . $chatid . '">';
            foreach($bots_data as $bd)
            {
                $thumbimg = get_the_post_thumbnail_url($bd['ID'], 'thumbnail');
                if(empty($thumbimg))
                {
                    $thumbimg = plugins_url('images/placeholder.jpg', __FILE__);
                }
                $return_me .= '<div onclick="document.getElementById(\'aiomatic_chat_input' . $chatid . '\').value += \' @' . esc_attr($bd['name']) . ' \';document.getElementById(\'aiomatic_chat_input' . $chatid . '\').focus();" class="ai-model-item">';
        $return_me .= '<img class="ai-model-avatar" src="' . esc_html($thumbimg) . '" alt="' . esc_attr($bd['name']) . '">
        <span class="ai-model-name">@' . esc_html(str_replace(' ', '', $bd['name'])) . '</span>
        <button title="' . esc_html__('Use this bot by default', 'aiomatic-automatic-ai-content-writer') . '" class="ai-compare-button" type="button" id="aiomatic_default_' . $chatid . '_' . esc_attr($bd['ID']) . '" onclick="event.stopPropagation();aiomaticSetDefaultChatbot(\'' . esc_attr($bd['ID']) . '\', \'' . esc_attr($chatid) . '\')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
            </svg>
        </button>&nbsp;
        <button title="' . esc_html__('Ask this bot', 'aiomatic-automatic-ai-content-writer') . '" class="ai-compare-button" type="button" onclick="event.stopPropagation();document.getElementById(\'aiomatic_chat_input' . $chatid . '\').value += \' @' . esc_attr($bd['name']) . ' \';document.getElementById(\'aiomatic_chat_input' . $chatid . '\').focus();"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M4 13h14.09l-6.79 6.79a.996.996 0 1 0 1.41 1.41l8.5-8.5c.06-.06.09-.13.13-.2.03-.04.06-.08.08-.13a.91.91 0 0 0 .08-.37c0-.03-.01-.05-.01-.07-.01-.1-.02-.21-.06-.31a.955.955 0 0 0-.22-.33L12.72 2.8c-.2-.2-.45-.29-.71-.29-.26 0-.51.1-.71.29a.996.996 0 0 0 0 1.41L18.08 11H4c-.55 0-1 .45-1 1s.45 1 1 1Z"></path>
            </svg>
        </button>
    </div>';
            }
            $return_me .= '</div>';
        }
        $blocked_words = '';
        if (isset($aiomatic_Chatbot_Settings['blocked_words']) && trim($aiomatic_Chatbot_Settings['blocked_words']) != '')
        {
            $blocked_words = trim($aiomatic_Chatbot_Settings['blocked_words']);
        }
        if (isset($aiomatic_Chatbot_Settings['updown_navigate']) && trim($aiomatic_Chatbot_Settings['updown_navigate']) == 'on')
        {
            $updown_navigate = '1';
        }
        else
        {
            $updown_navigate = '0';
        }
        if (isset($aiomatic_Chatbot_Settings['conversation_starters']) && $aiomatic_Chatbot_Settings['conversation_starters'] != '' && $conversation_starters == '')
        {
            $conversation_starters = $aiomatic_Chatbot_Settings['conversation_starters'];
        }
        if(!empty($bot_workflow))
        {
            $bot_workflow_decode = base64_decode($bot_workflow);
            if($bot_workflow_decode !== false)
            {
                $bot_workflow = $bot_workflow_decode;
            }
            else
            {
                $bot_workflow = '';
            }
        }
        if (isset($aiomatic_Chatbot_Settings['bot_workflow']) && $aiomatic_Chatbot_Settings['bot_workflow'] != '' && $bot_workflow == '')
        {
            $bot_workflow = $aiomatic_Chatbot_Settings['bot_workflow'];
        }
        $typewriter_delay = 50;
        if (isset($aiomatic_Chatbot_Settings['typewriter_delay']) && $aiomatic_Chatbot_Settings['typewriter_delay'] != '')
        {
            $typewriter_delay = intval($aiomatic_Chatbot_Settings['typewriter_delay']);
        }
        if(!empty($bot_workflow) && $bot_workflow != '[]')
        {
            $workflow = json_decode($bot_workflow, true);
            $errs = aiomatic_validate_workflows($workflow);
            if(!empty($errs))
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                {
                    aiomatic_log_to_file('Errors in chatbot workflows: ' . print_r($errs, true));
                }
                $bot_workflow = '';
            }
        }
        $u_is_logged_in = is_user_logged_in() ? 1 : 0;
        $u_current_user = wp_get_current_user();
        $u_user_name = $u_is_logged_in ? $u_current_user->user_login : '';
        $u_user_roles = $u_is_logged_in ? $u_current_user->roles : [];
        $current_post_id = is_singular() ? get_the_ID() : 0;
        $current_post_categories = array();
        if (is_singular('product')) {
            $terms = wp_get_post_terms(get_the_ID(), 'product_cat');
            if (!is_wp_error($terms) && !empty($terms)) {
                $current_post_categories = array_map(function($term) {
                    return array(
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                    );
                }, $terms);
            }
        } elseif (is_singular()) {
            $terms = wp_get_post_terms(get_the_ID(), 'category');
            if (!is_wp_error($terms) && !empty($terms)) {
                $current_post_categories = array_map(function($term) {
                    return array(
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                    );
                }, $terms);
            }
        }
        if (empty($current_post_categories)) {
            $current_post_categories = array();
        }
        $bg_color = '#6077e6';
        if (isset($aiomatic_Chatbot_Settings['submit_color']) && $aiomatic_Chatbot_Settings['submit_color'] != '') 
        {
            $bg_color = $aiomatic_Chatbot_Settings['submit_color'];
        }
        $enable_html = false;
        if (isset($aiomatic_Chatbot_Settings['enable_html']) && $aiomatic_Chatbot_Settings['enable_html'] == 'on') 
        {
            $enable_html = true;
        }
        $strip_js = false;
        if (isset($aiomatic_Chatbot_Settings['strip_js']) && $aiomatic_Chatbot_Settings['strip_js'] == 'on') 
        {
            $strip_js = true;
        }
        $current_post_type = isset($post) ? get_post_type($post) : '';
        $custom_vars = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('openai-ajax-nonce'),
            'stream_url' => $stream_url,
            'model_type' => $model_type,
            'model' => $model,
            'temp' => $temp,
            'enable_html' => $enable_html,
            'strip_js' => $strip_js,
            'top_p' => $top_p,
            'presence' => $presence,
            'frequency' => $frequency,
            'is_logged_in' => $u_is_logged_in,
            'current_post_id' => $current_post_id,
            'current_post_categories' => $current_post_categories,
            'current_post_type' => $current_post_type,
            'user_name' => $u_user_name,
            'user_roles' => $u_user_roles,
            'store_data' => $store_data,
            'instant_response' => $instant_response,
            'chat_preppend_text' => $chat_preppend_text,
            'user_message_preppend' => $user_message_preppend,
            'ai_message_preppend' => $ai_message_preppend,
            'user_token_cap_per_day' => $user_token_cap_per_day,
            'user_id' => $user_id,
            'persistent' => $persistent,
            'persistentnonce' => wp_create_nonce('openai-persistent-nonce'),
		    'moderation_nonce' => wp_create_nonce('openai-moderation-nonce'),
            'enable_moderation' => $enable_moderation,
            'moderation_model' => $moderation_model,
            'flagged_message' => $flagged_message,
            'enable_copy' => $enable_copy,
            'scroll_bot' => $scroll_bot,
            'chat_waveform' => $chat_waveform,
            'waveform_color' => $waveform_color,
            'text_speech' => $chatbot_text_speech,
            'free_voice' => $free_voice,
            'max_messages' => $max_messages,
            'max_message_context' => $max_message_context,
            'no_empty' => $no_empty,
            'overwrite_voice' => $overwrite_voice,
            'chatid' => $chatid,
            'did_image' => $did_image,
            'did_voice' => $did_voice,
            'did_app_id' => $did_app_id,
            'azure_speech_id' => $azure_speech_id,
            'azure_voice' => $azure_voice,
            'azure_voice_profile' => $azure_voice_profile,
            'azure_private_endpoint' => $azure_private_endpoint,
            'azure_voice_endpoint' => $azure_voice_endpoint,
            'azure_character' => $azure_character,
            'azure_character_style' => $azure_character_style,
            'azure_region' => $azure_region,
            'threadid' => $thread_id,
            'pdf_ok' => $pdf_ok,
            'pdf_end' => $pdf_end,
            'pdf_fail' => $pdf_fail,
            'conversation_end' => esc_html__('The AI considers this as the end of the text. Please try using a different text input', 'aiomatic-automatic-ai-content-writer'),
            'file_end' => esc_html__('File session ended', 'aiomatic-automatic-ai-content-writer'),
            'file_ok' => esc_html__('File upload successful', 'aiomatic-automatic-ai-content-writer'),
            'file_fail' => esc_html__('File upload failed', 'aiomatic-automatic-ai-content-writer'),
            'processing_issue' => esc_html__('There was an issue with processing, please try again later', 'aiomatic-automatic-ai-content-writer'),
            'message_required' => esc_html__('You need to enter a message to speak to the chatbot', 'aiomatic-automatic-ai-content-writer'),
            'text_copied' => esc_html__('Text copied!', 'aiomatic-automatic-ai-content-writer'),
            'conversation_ended' => esc_html__('Conversation ended', 'aiomatic-automatic-ai-content-writer'),
            'enable_god_mode' => $enable_god_mode,
            'is_modern_gpt' => $is_modern_gpt,
            'voice_color' => $voice_color,
            'voice_color_activated' => $voice_color_activated,
            'chat_download_format' => $chat_download_format,
            'internet_access' => $internet_access,
            'embeddings' => $embeddings,
            'embeddings_namespace' => $embeddings_namespace,
            'autoload' => $autoload,
            'autoload_delay' => $autoload_delay,
            'page_load_chat_once' => $page_load_chat_once,
            'page_load_chat_urls' => $page_load_chat_urls,
            'page_load_chat_message' => $page_load_chat_message,
            'page_exit_chat' => $page_exit_chat,
            'page_exit_chat_once' => $page_exit_chat_once,
            'page_exit_chat_urls' => $page_exit_chat_urls,
            'page_exit_chat_message' => $page_exit_chat_message,
            'page_scroll_chat' => $page_scroll_chat,
            'page_scroll_chat_percent' => $page_scroll_chat_percent,
            'page_scroll_chat_once' => $page_scroll_chat_once,
            'page_scroll_chat_urls' => $page_scroll_chat_urls,
            'page_scroll_chat_message' => $page_scroll_chat_message,
            'page_inactive_chat' => $page_inactive_chat,
            'page_inactive_chat_time' => $page_inactive_chat_time,
            'page_inactive_chat_once' => $page_inactive_chat_once,
            'page_inactive_chat_urls' => $page_inactive_chat_urls,
            'page_inactive_chat_message' => $page_inactive_chat_message,
            'auto_submit_voice' => $auto_submit_voice,
            'send_message_sound' => $send_message_sound,
            'receive_message_sound' => $receive_message_sound,
            'response_delay' => $response_delay,
            'bubble_alignment' => $bubble_alignment,
            'bubble_user_alignment' => $bubble_user_alignment,
            'avatar_url_user' => $avatar_url_user,
            'avatar_url' => $avatar_url,
            'show_user_avatar' => $show_user_avatar,
            'show_ai_avatar' => $show_ai_avatar,
            'markdown_parse' => $markdown_parse,
            'katex_parse' => $katex_parse,
            'is_azure' => $is_azure,
            'persistent_guests' => $persistent_guests,
            'chat_initial_messages' => $chat_initial_messages,
            'chat_editing' => $chat_editing,
            'show_gdpr' => $show_gdpr,
            'blocked_words' => $blocked_words,
            'updown_navigate' => $updown_navigate,
            'typewriter_delay' => $typewriter_delay,
            'conversation_starters' => $conversation_starters,
            'workflows' => $bot_workflow,
            'strip_botname' => $strip_botname,
            'bots_data' => $bots_data,
            'bg_color' => $bg_color
        );
        wp_localize_script($name . 'openai-chat-ajax', 'aiomatic_chat_ajax_object' . $chatid, $custom_vars);
        $return_me .= '<div id="aiomatic-conversation-starters' . $chatid . '"></div>';
        $return_me .= '<div class="aiomatic_input_container openai-relative"><textarea aria-label="' . esc_attr($placeholder) . '" id="aiomatic_chat_input' . $chatid . '" rows="2" class="aiomatic_chat_input chat-form-control" placeholder="' . esc_attr($placeholder) . '"';
        if(($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'off' || $prompt_editable == 'disabled' || $prompt_editable == 'disable' || $prompt_editable == "false") && $prompt_templates !== '')
        {
            $return_me .= ' disabled';
        }
        $return_me .= '></textarea>';
        if(($enable_vision == 'on' || $enable_vision == 'yes' || $enable_vision == '1' || $enable_vision == 'enable' || $enable_vision == 'enabled') && aiomatic_is_vision_model($model, $assistant_id))
        {
            $return_me .= '<i id="aivisionbut' . $chatid . '" class="aiomatic-gg-image" title="' . esc_html__('Upload an image to the chatbot', 'aiomatic-automatic-ai-content-writer') . '"></i>';
        }
        if($enable_file_uploads)
        {
            $return_me .= '<span id="aifilebut' . $chatid . '" class="aiomatic-file-image" title="' . esc_html__('Upload a file to the chatbot', 'aiomatic-automatic-ai-content-writer') . '"><svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" stroke-width="3" stroke="' . $go_color . '" fill="none"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><polygon points="25.15 6.32 50.81 6.32 50.81 54.84 13.19 54.84 13.19 19.18 25.15 6.32" stroke-linecap="round"></polygon><polyline points="25.17 6.32 25.15 19.18 13.19 19.18"></polyline><path d="M40.26,34v7.4a.82.82,0,0,1-.82.81H24.56a.82.82,0,0,1-.82-.81V34"></path><polyline points="36.08 30.87 32 26.79 27.93 30.87"></polyline><line x1="32" y1="26.79" x2="32" y2="38.74"></line></g></svg></span>';
        }
        if ($upload_pdf == 'on' || $upload_pdf == 'enabled' || $upload_pdf == 'yes' || $upload_pdf == '1' || (isset($aiomatic_Chatbot_Settings['upload_pdf']) && $aiomatic_Chatbot_Settings['upload_pdf'] == 'on' && $upload_pdf != 'disabled' && $upload_pdf != 'no' && $upload_pdf != '0' && $upload_pdf != 'off'))
        {
            if($all_ok === true)
            {
                $return_me .= '<span id="aipdfbut' . $chatid . '" class="aiomatic-pdf-image" title="' . esc_html__('Upload a PDF file to the chatbot', 'aiomatic-automatic-ai-content-writer') . '"><svg fill="' . $go_color . '" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 550.801 550.801" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M267.342,414.698c-6.613,0-10.884,0.585-13.413,1.165v85.72c2.534,0.586,6.616,0.586,10.304,0.586 c26.818,0.189,44.315-14.576,44.315-45.874C308.738,429.079,292.803,414.698,267.342,414.698z"></path> <path d="M152.837,414.313c-6.022,0-10.104,0.58-12.248,1.16v38.686c2.531,0.58,5.643,0.78,9.903,0.78 c15.757,0,25.471-7.973,25.471-21.384C175.964,421.506,167.601,414.313,152.837,414.313z"></path> <path d="M475.095,131.992c-0.032-2.526-0.833-5.021-2.568-6.993L366.324,3.694c-0.021-0.034-0.062-0.045-0.084-0.076 c-0.633-0.707-1.36-1.29-2.141-1.804c-0.232-0.15-0.475-0.285-0.718-0.422c-0.675-0.366-1.382-0.67-2.13-0.892 c-0.19-0.058-0.38-0.14-0.58-0.192C359.87,0.114,359.037,0,358.203,0H97.2C85.292,0,75.6,9.693,75.6,21.601v507.6 c0,11.913,9.692,21.601,21.6,21.601H453.6c11.908,0,21.601-9.688,21.601-21.601V133.202 C475.2,132.796,475.137,132.398,475.095,131.992z M193.261,463.873c-10.104,9.523-25.072,13.806-42.569,13.806 c-3.882,0-7.391-0.2-10.102-0.58v46.839h-29.35V394.675c9.131-1.55,21.967-2.721,40.047-2.721 c18.267,0,31.292,3.501,40.036,10.494c8.363,6.612,13.985,17.497,13.985,30.322C205.308,445.605,201.042,456.49,193.261,463.873z M318.252,508.392c-13.785,11.464-34.778,16.906-60.428,16.906c-15.359,0-26.238-0.97-33.637-1.94V394.675 c10.887-1.74,25.083-2.721,40.046-2.721c24.867,0,41.004,4.472,53.645,13.995c13.61,10.109,22.164,26.241,22.164,49.37 C340.031,480.4,330.897,497.697,318.252,508.392z M439.572,417.225h-50.351v29.932h47.039v24.11h-47.039v52.671H359.49V392.935 h80.082V417.225z M97.2,366.752V21.601h250.203v110.515c0,5.961,4.831,10.8,10.8,10.8H453.6l0.011,223.836H97.2z"></path> <path d="M386.205,232.135c-0.633-0.059-15.852-1.448-39.213-1.448c-7.319,0-14.691,0.143-21.969,0.417 c-46.133-34.62-83.919-69.267-104.148-88.684c0.369-2.138,0.623-3.828,0.741-5.126c2.668-28.165-0.298-47.179-8.786-56.515 c-5.558-6.101-13.721-8.131-22.233-5.806c-5.286,1.385-15.071,6.513-18.204,16.952c-3.459,11.536,2.101,25.537,16.708,41.773 c0.232,0.246,5.189,5.44,14.196,14.241c-5.854,27.913-21.178,88.148-28.613,117.073c-17.463,9.331-32.013,20.571-43.277,33.465 l-0.738,0.844l-0.477,1.013c-1.16,2.437-6.705,15.087-2.542,25.249c1.901,4.62,5.463,7.995,10.302,9.767l1.297,0.349 c0,0,1.17,0.253,3.227,0.253c9.01,0,31.25-4.735,43.179-48.695l2.89-11.138c41.639-20.239,93.688-26.768,131.415-28.587 c19.406,14.391,38.717,27.611,57.428,39.318l0.611,0.354c0.907,0.464,9.112,4.515,18.721,4.524l0,0 c13.732,0,23.762-8.427,27.496-23.113l0.189-1.004c1.044-8.393-1.065-15.958-6.096-21.872 C407.711,233.281,387.978,232.195,386.205,232.135z M142.812,319.744c-0.084-0.1-0.124-0.194-0.166-0.3 c-0.896-2.157,0.179-7.389,1.761-11.222c6.792-7.594,14.945-14.565,24.353-20.841 C159.598,317.039,146.274,319.603,142.812,319.744z M200.984,122.695L200.984,122.695c-14.07-15.662-13.859-23.427-13.102-26.041 c1.242-4.369,6.848-6.02,6.896-6.035c2.824-0.768,4.538-0.617,6.064,1.058c3.451,3.791,6.415,15.232,5.244,36.218 C202.764,124.557,200.984,122.695,200.984,122.695z M193.714,256.068l0.243-0.928l-0.032,0.011 c7.045-27.593,17.205-67.996,23.047-93.949l0.211,0.201l0.021-0.124c18.9,17.798,47.88,43.831,82.579,70.907l-0.39,0.016 l0.574,0.433C267.279,235.396,228.237,241.84,193.714,256.068z M408.386,265.12c-2.489,9.146-7.277,10.396-11.665,10.396l0,0 c-5.094,0-9.998-2.12-11.116-2.632c-12.741-7.986-25.776-16.688-38.929-25.998c0.105,0,0.2,0,0.316,0 c22.549,0,37.568,1.369,38.158,1.411c3.766,0.14,15.684,1.9,20.82,7.938C407.984,258.602,408.755,261.431,408.386,265.12z"></path> </g> </g> </g></svg></span>';
            }
        }
        if($persistent === 'history' && $assistant_id == '')
        {
            $conversation_data = array();
            $user_id = get_current_user_id();
            if($user_id == 0 && ($persistent_guests == 'on' || $persistent_guests == '1'))
            {
                $user_id = aiomatic_get_the_user_ip();
            }
            if($user_id != 0)
            {
                if(is_numeric($user_id))
                {
                    $conversation_data = get_user_meta($user_id, 'aiomatic_chat_history_' . $persistent, true);
                    if(!is_array($conversation_data))
                    {
                        $conversation_data = array();
                    }
                    else
                    {
                        uasort($conversation_data, function ($a, $b) 
                        {
                            return $b['time'] <=> $a['time'];
                        });
                    }
                }
                else
                {
                    $conversation_data = get_transient('aiomatic_chat_history_' . $persistent . '_' . $user_id);
                    if(!is_array($conversation_data))
                    {
                        $conversation_data = array();
                    }
                    else
                    {
                        uasort($conversation_data, function ($a, $b) 
                        {
                            return $b['time'] <=> $a['time'];
                        });
                    }
                }
            }
            if($user_id !== 0)
            {
                $return_me .= '<span onclick="aiomatic_toggle_chat_logs(\'' . $chatid . '\');" id="aihistorybut' . $chatid . '" class="aiomatic-show-logs" title="' . esc_html__('Show chat logs', 'aiomatic-automatic-ai-content-writer') . '">
<svg fill="#000000" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg">
<path d="M677.68-.034v338.937h112.942V113.02h1016.47v790.476h-225.995v259.764l-259.651-259.764h-79.172V451.844H.034v1016.47h338.71v418.9l417.996-418.9h485.534v-451.877h32.753l419.125 419.124v-419.124h225.882V-.033H677.68ZM338.825 903.53H903.53V790.59H338.824v112.94Zm0 225.883H677.76v-113.054H338.824v113.054Zm-225.849-564.74h1016.47v790.701H710.435l-258.748 259.652v-259.652h-338.71V564.672Z" fill-rule="evenodd"/>
</svg></span>';
                $return_me .= '<div id="chat-logs-dropdown' . $chatid . '" class="chat-logs-dropdown cr_none">';
                $return_me .= '<div class="chat-log-item chat-log-new" data-id="new-chat" onclick="aiomatic_trigger_chat_logs(\'new-chat\', \'' . $chatid . '\')"><b>' . esc_html__("Start a new chat", 'aiomatic-automatic-ai-content-writer') . '</b></div>';
                $conversations_by_date = array();
                foreach($conversation_data as $cnvid => $cnvd)
                {
                    if(isset($cnvd['name']) && isset($cnvd['main_index']))
                    {
                        if($cnvd['main_index'] === $chat_unique_id)
                        {
                            $conv_timestamp = isset($cnvd['time']) ? intval($cnvd['time']) : null;
                            if ($conv_timestamp)
                            {
                                $date_group = aiomatic_get_date_group($conv_timestamp);
                                $group_label = $date_group['label'];
                                $group_order = $date_group['order'];
                                if (!isset($conversations_by_date[$group_label]))
                                {
                                    $conversations_by_date[$group_label] = array('order' => $group_order, 'conversations' => array());
                                }
                                $conversations_by_date[$group_label]['conversations'][$cnvid] = $cnvd;
                            }
                        }
                    }
                }
                uasort($conversations_by_date, function($a, $b) {
                    return $a['order'] - $b['order'];
                });
                foreach($conversations_by_date as $date_label => $data)
                {
                    $return_me .= '<div class="date-group-label">' . esc_html($date_label) . '</div>';
                    $conversations = $data['conversations'];
                    uasort($conversations, function($a, $b) {
                        $time_a = isset($a['time']) ? intval($a['time']) : 0;
                        $time_b = isset($b['time']) ? intval($b['time']) : 0;
                        return $time_b - $time_a;
                    });
                    foreach($conversations as $cnvid => $cnvd)
                    {
                        $return_me .= '<div class="chat-log-item" id="chat-log-item' . esc_attr($cnvid) . '" data-id="' . esc_attr($cnvid) . '" onclick="aiomatic_trigger_chat_logs(\'' . esc_attr($cnvid) . '\', \'' . $chatid . '\')">' . esc_html($cnvd['name']) . '<span onclick="aiomatic_remove_chat_logs(event, \'' . esc_attr($cnvid) . '\', \'' . esc_attr($chatid) . '\');" class="aiomatic-remove-item">X</span></div>';
                    }
                }
                $return_me .= '</div>
<input type="hidden" id="chat-log-identifier' . $chatid . '" value="' . esc_attr(uniqid()) . '">
<input type="hidden" id="chat-main-identifier' . $chatid . '" value="' . esc_attr($chat_unique_id) . '">';
            }
        }
        if (isset($aiomatic_Chatbot_Settings['allow_stream_stop']) && $aiomatic_Chatbot_Settings['allow_stream_stop'] == 'on') 
        {
            $return_me .= '<span id="aistopbut' . $chatid . '" class="aiomatic-stop-image cr_none" title="' . esc_html__('Stop message processing', 'aiomatic-automatic-ai-content-writer') . '"><svg fill="' . $go_color . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g data-name="stop circle"><path d="M12 0a12 12 0 1 0 12 12A12 12 0 0 0 12 0zm0 22a10 10 0 1 1 10-10 10 10 0 0 1-10 10z"/><path d="M16 7H8a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1zm-1 8H9V9h6z"/></g></svg></span>'; 
        }
        if($prompt_templates != '')
        {
            $predefined_prompts_arr = explode(';', $prompt_templates);
            $return_me .= '<select id="aiomatic_chat_templates' . $chatid . '" class="aiomatic_chat_input chat-form-control cr_width_full">';
            $return_me .= '<option disabled selected>' . esc_html($select_prompt) . '</option>';
            foreach($predefined_prompts_arr as $sval)
            {
                $ppro = explode('|~|~|', $sval);
                if(isset($ppro[1]))
                {
                    $return_me .= '<option value="' . esc_attr($ppro[1]) . '" title="' . esc_attr($ppro[0]) . '">' . esc_html($ppro[0]) . '</option>';
                }
                else
                {
                    $return_me .= '<option value="' . esc_attr($sval) . '" title="' . esc_attr($sval) . '">' . esc_html($sval) . '</option>';
                }
            }
            $return_me .= '</select>';
        }        
        if($model == 'default' || $model == '')
        {
            $return_me .= '<label for="model-chat-selector' . $chatid . '" class="aiomatic-ai-text">Model:</label><select class="aiomatic-ai-input" id="model-chat-selector' . $chatid . '">';
            foreach ($models as $zmodel) {
                $return_me .= "<option value='" . $zmodel . "'>" . $zmodel . "</option>";
            }
            $return_me .= '</select>';
        }
        if($temp == 'default' || $temp == '')
        {
            $return_me .= '<label for="temperature-chat-input' . $chatid . '" class="aiomatic-ai-text">Temperature:</label><input type="number" min="0" step="0.01" max="2" class="aiomatic-ai-input" id="temperature-chat-input' . $chatid . '" name="temperature" value="1">';
        }
        if($top_p == 'default' || $top_p == '')
        {
            $return_me .= '<label for="top_p-chat-input' . $chatid . '" class="aiomatic-ai-text">Top_p:</label><input type="number" min="0" step="0.01" max="1" class="aiomatic-ai-input" id="top_p-chat-input' . $chatid . '" name="top_p" value="1">';
        }
        if($presence == 'default' || $presence == '')
        {
            $return_me .= '<label for="presence-chat-input' . $chatid . '" class="aiomatic-ai-text">Presence Penalty:</label><input type="number" min="-2" step="0.01" max="2" class="aiomatic-ai-input" id="presence-chat-input' . $chatid . '" name="presence" value="0">';
        }
        if($frequency == 'default' || $frequency == '')
        {
            $return_me .= '<label for="frequency-chat-input' . $chatid . '" class="aiomatic-ai-text">Frequency Penalty:</label><input type="number" min="0" step="0.01" max="2" class="aiomatic-ai-input" id="frequency-chat-input' . $chatid . '" name="frequency" value="0">';
        }
        $return_me .= '</div>';
        if (isset($aiomatic_Chatbot_Settings['voice_input']) && $aiomatic_Chatbot_Settings['voice_input'] == 'on')
        {
            if(!($prompt_editable == 'no' || $prompt_editable === '0' || $prompt_editable == 'off' || $prompt_editable == 'disabled' || $prompt_editable == 'disable' || $prompt_editable == "false") || $prompt_templates == '')
            {
                $return_me .= '<button type="button" id="openai-chat-speech-button' . $chatid . '" class="openai-chat-speech-button btn btn-primary"';
                            if($submit_full == 'on')
                            {
                                $return_me .= ' style="width: 100%!important;"';
                            }
                            $return_me .= ' title="Record your voice">
<img src="' . plugins_url('images/mic.ico', __FILE__) . '">
</button>&nbsp;';
            }
        }
        if(!empty($assistant_id))
        {
            $ai_assistant_id = get_post_meta($assistant_id, '_assistant_id', true);
        }
        else
        {
            $ai_assistant_id = '';
        }
        $return_me .= $complete_me;
        if($submit_image != '')
        {
            $submit = '<img src="' . esc_url_raw($submit_image) . '" title="' . esc_html($submit) . '" alt="' . esc_html($submit) . '" />';
        }
        $return_me .= '<input type="hidden" id="aiomatic_assistant_id' . $chatid . '" value="' . esc_html($ai_assistant_id) . '"><input type="hidden" id="aiomatic_thread_id' . $chatid . '" value="' . esc_html($thread_id) . '">
<button type="button" id="aichatsubmitbut' . $chatid . '" class="aichatsubmitbut btn btn-primary"';
                            if($submit_full == 'on')
                            {
                                $return_me .= ' style="width: 100%!important;"';
                            }
                            $return_me .= '><span id="button-chat-text' . $chatid . '">' . $submit . '</span></button>';
        $return_me .= '<div id="openai-chat-response' . $chatid . '">&nbsp;</div>
<div id="compliance' . $chatid . '" class="aiomatic-text-center cr_fullw">' . $compliance . '</div>
</div>';
            if(!empty($custom_footer))
            {
                $return_me .= '<div class="openai-custom-footer">' . $custom_footer . '</div>';
            }
            if(!empty($custom_css))
            {
                $return_me .= '<style>' . $custom_css . '</style>';
            }
            if (isset($aiomatic_Chatbot_Settings['show_gdpr']) && $aiomatic_Chatbot_Settings['show_gdpr'] == 'on')
            {
                $privacy_url = '/privacy-policy';
                if(function_exists('get_privacy_policy_url'))
                {
                    $privacy_url = get_privacy_policy_url();
                    if(empty($privacy_url))
                    {
                        $privacy_url = '/privacy-policy';
                    }
                }
                $gdpr_button = 'Start chatting';
                if (isset($aiomatic_Chatbot_Settings['gdpr_button']) && $aiomatic_Chatbot_Settings['gdpr_button'] != '')
                {
                    $gdpr_button = $aiomatic_Chatbot_Settings['gdpr_button'];
                }
                $gdpr_checkbox = 'I agree to the terms.';
                if (isset($aiomatic_Chatbot_Settings['gdpr_checkbox']) && $aiomatic_Chatbot_Settings['gdpr_checkbox'] != '')
                {
                    $gdpr_checkbox = $aiomatic_Chatbot_Settings['gdpr_checkbox'];
                }
                $gdpr_notice = 'By using this chatbot, you consent to the collection and use of your data as outlined in our <a href=\'%%privacy_policy_url%%\' target=\'_blank\'>Privacy Policy</a>. Your data will only be used to assist with your inquiry.';
                if (isset($aiomatic_Chatbot_Settings['gdpr_notice']) && $aiomatic_Chatbot_Settings['gdpr_notice'] != '')
                {
                    $gdpr_notice = $aiomatic_Chatbot_Settings['gdpr_notice'];
                }
                $gdpr_notice = str_replace('%%privacy_policy_url%%', $privacy_url, $gdpr_notice);
                $return_me .= '<div class="aiomatic-chatbot-overlay cr_none" id="aiomatic-chatbot-overlay' . $chatid . '">
<div class="aiomatic-overlay-content">
    <p>' . wp_kses_post($gdpr_notice) . '</p>
    <label for="aiomatic-consent-checkbox' . $chatid . '">
        <input type="checkbox" class="aiomatic-consent-checkbox" id="aiomatic-consent-checkbox' . $chatid . '">' . esc_html($gdpr_checkbox) . '</label>
    <button type="button" class="aiomatic-start-chatting-button" id="aiomatic-start-chatting-button' . $chatid . '" disabled>' . esc_html($gdpr_button) . '</button>
</div>
</div>';
            }
            $return_me .= '
</form> 
';
    }
    $return_me .= '</div>';
    return $return_me;
}
add_shortcode( 'aiomatic-audio-converter', 'aiomatic_audio_convert' );
function aiomatic_audio_convert( $atts ) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    $aiomatic_languages = array(
        'en' => 'English',
        'af' => 'Afrikaans',
        'ar' => 'Arabic',
        'hy' => 'Armenian',
        'az' => 'Azerbaijani',
        'be' => 'Belarusian',
        'bs' => 'Bosnian',
        'bg' => 'Bulgarian',
        'ca' => 'Catalan',
        'zh' => 'Chinese',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'nl' => 'Dutch',
        'et' => 'Estonian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'gl' => 'Galician',
        'de' => 'German',
        'el' => 'Greek',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hu' => 'Hungarian',
        'is' => 'Icelandic',
        'id' => 'Indonesian',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'kn' => 'Kannada',
        'kk' => 'Kazakh',
        'ko' => 'Korean',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'mk' => 'Macedonian',
        'ms' => 'Malay',
        'mr' => 'Marathi',
        'mi' => 'Maori',
        'ne' => 'Nepali',
        'no' => 'Norwegian',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sr' => 'Serbian',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'es' => 'Spanish',
        'sw' => 'Swahili',
        'sv' => 'Swedish',
        'tl' => 'Tagalog',
        'ta' => 'Tamil',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'vi' => 'Vietnamese',
        'cy' => 'Welsh'
    );
    ob_start();
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') {
        aiomatic_log_to_file('You need to add an API key in plugin settings for this shortcode to work.');
        return '';
    }
    else
    {
        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
        $appids = array_filter($appids);
        $token = $appids[array_rand($appids)];
        if(aiomatic_is_aiomaticapi_key($token))
        {
            aiomatic_log_to_file('Currently only OpenAI API is supported for audio processing!');
            return '';
        }
    }
    $reg_css_code = '.aiomatic-hide {display:none!important;visibility:hidden}
    .aiomatic_progress{
        height: 15px;
        width: calc(100% - 25px);
        background: #727272;
        border-radius: 5px;
        color: #fff;
        padding: 2px 12px;
        position: relative;
        font-size: 12px;
        text-align: center;
    }
    .aiomatic_progress.aiomatic_error span{
        background: #bb0505;
    }
    .aiomatic_progress span{
        display: block;
        position: absolute;
        height: 100%;
        border-radius: 5px;
        background: #2271b1;
        top: 0;
        left: 0;
        transition: width .6s ease;
    }
    .aiomatic_progress small{
        position: relative;
        font-size: 12px;
    }
    .aiomatic_width_10
    {
        width:10%;
    }
    .aiomatic_width_40
    {
        width:40%;
    }
    .aiomatic_width_50
    {
        width:50%;
    }
    .cr_fullw
    {
        width:100%;
    }';
    $name = md5(get_bloginfo());
    wp_register_style( $name . '-audio-reg-style', false );
    wp_enqueue_style( $name . '-audio-reg-style' );
    wp_add_inline_style( $name . '-audio-reg-style', $reg_css_code );
    wp_register_script( $name . '-audio-js', trailingslashit( plugins_url('', __FILE__) ) . 'scripts/audio.js', array('jquery'), AIOMATIC_MAJOR_VERSION );
    wp_enqueue_script( $name . '-audio-js' );
    wp_localize_script( $name . '-audio-js', 'aiomatic_audio_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-audio-nonce')
	));
?>
<form class="aiomatic-audio-form">
<table class="form-table">
    <tr><td colspan="2"><h3><?php echo esc_html__('Settings', 'aiomatic-automatic-ai-content-writer');?></h3></td><td><h3><?php echo esc_html__('Result', 'aiomatic-automatic-ai-content-writer');?></h3></td></tr>
<tr>
    <th class="aiomatic_width_10" scope="row"><?php echo esc_html__('Purpose', 'aiomatic-automatic-ai-content-writer');?></th>
    <td class="aiomatic_width_40">
        <select name="audio_purpose" class="coderevolution_gutenberg_input regular-text aiomatic-audio-purpose">
            <option value="transcriptions" selected><?php echo esc_html__('Transcriptions', 'aiomatic-automatic-ai-content-writer');?></option>
            <option value="translations"><?php echo esc_html__('Translations', 'aiomatic-automatic-ai-content-writer');?></option>
        </select>
    </td>
    <td class="aiomatic_width_50" rowspan="8"><textarea rows="22" class="cr_fullw" disabled placeholder="The result will be displayed here" id="aiomatic_audio_result"></textarea></td>
</tr>
<tr>
    <th scope="row"><?php echo esc_html__('File', 'aiomatic-automatic-ai-content-writer');?></th>
    <td>
        <div class="mb-2">
            <label for="aiomatic-label-computer"><input checked id="aiomatic-label-computer" class="aiomatic-audio-select" name="type" value="upload" type="radio">&nbsp;<?php echo esc_html__('Computer', 'aiomatic-automatic-ai-content-writer');?></label>
            <label for="aiomatic-label-url"><input id="aiomatic-label-url" class="aiomatic-audio-select" name="type" value="url" type="radio">&nbsp;<?php echo esc_html__('URL', 'aiomatic-automatic-ai-content-writer');?></label>
            <label for="aiomatic-label-recording"><input id="aiomatic-label-recording" class="aiomatic-audio-select" name="type" value="record" type="radio">&nbsp;<?php echo esc_html__('Recording', 'aiomatic-automatic-ai-content-writer');?></label>
        </div>
        <div class="aiomatic-audio-type aiomatic-audio-upload">
            <input type="file" name="file" accept="audio/mpeg,video/mp4,video/mpeg,audio/m4a,audio/wav,video/webm">
        </div>
        <div class="aiomatic-audio-type aiomatic-audio-url aiomatic-hide">
            <input type="url" name="url" class="coderevolution_gutenberg_input regular-text" placeholder="Example: https://domain.com/audio.mp3">
        </div>
        <div class="aiomatic-audio-type aiomatic-audio-record aiomatic-hide">
            <button type="button" class="button button-primary" id="btn-audio-record"><?php echo esc_html__('Record', 'aiomatic-automatic-ai-content-writer');?></button>
            <button type="button" class="button button-primary aiomatic-hide" id="btn-audio-record-pause"><?php echo esc_html__('Pause', 'aiomatic-automatic-ai-content-writer');?></button>
            <button type="button" class="button button-link-delete aiomatic-hide" id="btn-audio-record-stop"><?php echo esc_html__('Stop', 'aiomatic-automatic-ai-content-writer');?></button>
            <div class="aiomatic-hide" id="aiomatic-audio-record-result"></div>
        </div>
    </td>
</tr>
<tr>
    <th scope="row"><?php echo esc_html__('Model', 'aiomatic-automatic-ai-content-writer');?></th>
    <td>
        <select name="model" class="coderevolution_gutenberg_input regular-text">
            <option selected value="whisper-1">whisper-1</option>
        </select>
    </td>
</tr>
<tr>
    <th scope="row"><?php echo esc_html__('Prompt (Optional)', 'aiomatic-automatic-ai-content-writer');?></th>
    <td>
        <input type="text" class="coderevolution_gutenberg_input regular-text" placeholder="Enter your AI prompt (optional)" name="prompt" maxlength="255">
    </td>
</tr>
<tr>
    <th scope="row"><?php echo esc_html__('Temperature (Optional)', 'aiomatic-automatic-ai-content-writer');?></th>
    <td>
        <input value="" class="coderevolution_gutenberg_input regular-text" placeholder="Enter your AI temperature (optional)" name="temperature" type="number" min="0" step="0.01" max="2">
    </td>
</tr>
<tr class="aiomatic_languages">
    <th scope="row"><?php echo esc_html__('Language (Optional)', 'aiomatic-automatic-ai-content-writer');?></th>
    <td>
        <select name="language" class="coderevolution_gutenberg_input regular-text">
            <?php
            foreach ($aiomatic_languages as $key => $aiomatic_language){
                echo '<option value="' . esc_html($key) . '">' . esc_html($aiomatic_language) . '</option>';
            }
            ?>
        </select>
    </td>
</tr>
<tr>
    <th scope="row"></th>
    <td>
        <div>
            <div class="aiomatic_upload_success aiomatic-hide"><?php echo esc_html__('Conversion has completed successfully.', 'aiomatic-automatic-ai-content-writer');?></div>
            <div class="aiomatic_progress aiomatic-hide"><span></span><small><?php echo esc_html__('Converting. This will take some time. Please wait!', 'aiomatic-automatic-ai-content-writer');?></small></div>
            <div class="aiomatic-error-msg"></div>
        </div>
    </td>
</tr>
<tr>
    <th scope="row"></th>
    <td>
        <button class="button button-primary" id="button-start-converter"><?php echo esc_html__('Start', 'aiomatic-automatic-ai-content-writer');?></button>
        <button class="aiomatic-hide button button-link-delete" id="aiomatic-btn-cancel" type="button"><?php echo esc_html__('Cancel', 'aiomatic-automatic-ai-content-writer');?></button>
    </td>
</tr>
</table>
</form>
<?php
    $myvariable = ob_get_clean();
    return $myvariable;
}
add_shortcode( 'aiomatic-text-moderation', 'aiomatic_text_moderation' );
function aiomatic_text_moderation( $atts ) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    ob_start();
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') {
        aiomatic_log_to_file('You need to add an API key in plugin settings for this shortcode to work.');
        return '';
    }
    else
    {
        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
        $appids = array_filter($appids);
        $token = $appids[array_rand($appids)];
        if(aiomatic_is_aiomaticapi_key($token))
        {
            aiomatic_log_to_file('Currently only OpenAI API is supported for text moderation!');
            return '';
        }
    }
    $reg_css_code = '.aiomatic-hide {display:none!important;visibility:hidden}
    .aiomatic_moderation_progress{
        height: 15px;
        width: calc(100% - 25px);
        background: #727272;
        border-radius: 5px;
        color: #fff;
        padding: 2px 12px;
        position: relative;
        font-size: 12px;
        text-align: center;
    }
    .aiomatic_moderation_progress.aiomatic_error span{
        background: #bb0505;
    }
    .aiomatic_moderation_progress span{
        display: block;
        position: absolute;
        height: 100%;
        border-radius: 5px;
        background: #2271b1;
        top: 0;
        left: 0;
        transition: width .6s ease;
    }
    .aiomatic_moderation_progress small{
        position: relative;
        font-size: 12px;
    }
    .aiomatic_width_half
    {
        width:50%;
    }
    .cr_fullw
    {
        width:100%;
    }';
    $name = md5(get_bloginfo());
    wp_register_style( $name . '-moderation-reg-style', false );
    wp_enqueue_style( $name . '-moderation-reg-style' );
    wp_add_inline_style( $name . '-moderation-reg-style', $reg_css_code );
    wp_register_script( $name . '-moderation-js', trailingslashit( plugins_url('', __FILE__) ) . 'scripts/moderation.js', array('jquery'), AIOMATIC_MAJOR_VERSION );
    wp_enqueue_script( $name . '-moderation-js' );
    wp_localize_script( $name . '-moderation-js', 'aiomatic_moderation_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-moderation-nonce')
	));
?>
<form class="aiomatic-moderation-form">
<table class="form-table">
    <tr><td><h3><?php echo esc_html__('Input', 'aiomatic-automatic-ai-content-writer');?></h3></td><td><h3><?php echo esc_html__('Result', 'aiomatic-automatic-ai-content-writer');?></h3></td></tr>
<tr>
    <td class="aiomatic_width_half" >
    <textarea class="cr_fullw" rows="30" placeholder="Enter your text here" id="aiomatic_moderation_input"></textarea>
    </td>
    <td class="aiomatic_width_half">
    <textarea class="cr_fullw" rows="30" disabled placeholder="API response" id="aiomatic_moderation_result"></textarea>
    </td>   
</tr>
<tr>
    <td colspan="2">
        <div>
            <div id="aiomatic_moderation_success" class="aiomatic-hide"><?php echo esc_html__('Text moderation has completed successfully.', 'aiomatic-automatic-ai-content-writer');?></div>
            <div id="aiomatic_moderation_progress" class="aiomatic-hide"><span></span><small><?php echo esc_html__('Checking. This will take some time. Please wait!', 'aiomatic-automatic-ai-content-writer');?></small></div>
            <div id="aiomatic-error-msg"></div>
        </div>
    </td>
</tr>
<tr>
    <td colspan="2">
        <button class="button button-primary" id="button-start-moderation"><?php echo esc_html__('Text Moderation Check', 'aiomatic-automatic-ai-content-writer');?></button>
    </td>
</tr>
</table>
</form>
<?php
    $myvariable = ob_get_clean();
    return $myvariable;
}
add_shortcode( 'aiomatic-plagiarism-check', 'aiomatic_text_plagiarism' );
function aiomatic_text_plagiarism( $atts ) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    ob_start();
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['plagiarism_api']) || trim($aiomatic_Main_Settings['plagiarism_api']) == '') {
        return esc_html__('You need to add a PlagiarismCheck API key in plugin settings for this shortcode to work.', 'aiomatic-automatic-ai-content-writer');
    }
    $reg_css_code = '.aiomatic-hide {display:none!important;visibility:hidden}
    .aiomatic_plagiarism_progress{
        height: 15px;
        width: calc(100% - 25px);
        background: #727272;
        border-radius: 5px;
        color: #fff;
        padding: 2px 12px;
        position: relative;
        font-size: 12px;
        text-align: center;
    }
    .aiomatic_plagiarism_progress.aiomatic_error span{
        background: #bb0505;
    }
    .aiomatic_plagiarism_progress span{
        display: block;
        position: absolute;
        height: 100%;
        border-radius: 5px;
        background: #2271b1;
        top: 0;
        left: 0;
        transition: width .6s ease;
    }
    .aiomatic_plagiarism_progress small{
        position: relative;
        font-size: 12px;
    }
    .aiomatic_width_half
    {
        width:50%;
    }
    .cr_fullw
    {
        width:100%;
    }';
    $name = md5(get_bloginfo());
    wp_register_style( $name . '-plagiarism-reg-style', false );
    wp_enqueue_style( $name . '-plagiarism-reg-style' );
    wp_add_inline_style( $name . '-plagiarism-reg-style', $reg_css_code );
    wp_register_script( $name . '-plagiarism-js', trailingslashit( plugins_url('', __FILE__) ) . 'scripts/plagiarism.js', array('jquery'), AIOMATIC_MAJOR_VERSION );
    wp_enqueue_script( $name . '-plagiarism-js' );
    wp_localize_script( $name . '-plagiarism-js', 'aiomatic_plagiarism_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-plagiarism-nonce')
	));
?>
<form class="aiomatic-plagiarism-form">
<table class="form-table">
    <tr><td><h3><?php echo esc_html__('Input', 'aiomatic-automatic-ai-content-writer');?></h3></td><td><h3><?php echo esc_html__('Result', 'aiomatic-automatic-ai-content-writer');?></h3></td></tr>
<tr>
    <td class="aiomatic_width_half" >
    <textarea class="cr_fullw" rows="32" placeholder="Enter your text here" id="aiomatic_plagiarism_input"></textarea>
    </td>
    <td class="aiomatic_width_half">
    <input type="text" value="" disabled placeholder="Detected plagiarism percentage" id="aiomatic_plagiarism_percentage">
    <textarea class="cr_fullw" rows="30" disabled placeholder="Detected plagiated source list" id="aiomatic_plagiarism_result"></textarea>
    </td>   
</tr>
<tr>
    <td colspan="2">
        <div>
            <div id="aiomatic_plagiarism_success" class="aiomatic-hide"><?php echo esc_html__('Text plagiarism checking has completed successfully.', 'aiomatic-automatic-ai-content-writer');?></div>
            <div id="aiomatic_plagiarism_progress" class="aiomatic-hide"><span></span><small><?php echo esc_html__('Checking. This will take some time. Please wait!', 'aiomatic-automatic-ai-content-writer');?></small></div>
            <div id="aiomatic-error-msg-plagiarism"></div>
        </div>
    </td>
</tr>
<tr>
    <td colspan="2">
        <button class="button button-primary" id="button-start-plagiarism"><?php echo esc_html__('Text Plagiarism Check', 'aiomatic-automatic-ai-content-writer');?></button>
    </td>
</tr>
</table>
</form>
<?php
    $myvariable = ob_get_clean();
    return $myvariable;
}
add_shortcode( 'aiomatic-ai-detector', 'aiomatic_text_ai_detector' );
function aiomatic_text_ai_detector( $atts ) {
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) 
        {
            return;
        }
    }
    if(!aiomatic_validate_activation())
    {
        return;
    }
    ob_start();
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['plagiarism_api']) || trim($aiomatic_Main_Settings['plagiarism_api']) == '') {
        return esc_html__('You need to add a PlagiarismCheck API key in plugin settings for this shortcode to work.', 'aiomatic-automatic-ai-content-writer');
    }
    $reg_css_code = '.aiomatic-hide {display:none!important;visibility:hidden}
    .aiomatic_aidetector_progress{
        height: 15px;
        width: calc(100% - 25px);
        background: #727272;
        border-radius: 5px;
        color: #fff;
        padding: 2px 12px;
        position: relative;
        font-size: 12px;
        text-align: center;
    }
    .aiomatic_aidetector_progress.aiomatic_error span{
        background: #bb0505;
    }
    .aiomatic_aidetector_progress span{
        display: block;
        position: absolute;
        height: 100%;
        border-radius: 5px;
        background: #2271b1;
        top: 0;
        left: 0;
        transition: width .6s ease;
    }
    .aiomatic_aidetector_progress small{
        position: relative;
        font-size: 12px;
    }
    .aiomatic_width_half
    {
        width:50%;
    }
    .cr_fullw
    {
        width:100%;
    }';
    $name = md5(get_bloginfo());
    wp_register_style( $name . '-aidetector-reg-style', false );
    wp_enqueue_style( $name . '-aidetector-reg-style' );
    wp_add_inline_style( $name . '-aidetector-reg-style', $reg_css_code );
    wp_register_script( $name . '-aidetector-js', trailingslashit( plugins_url('', __FILE__) ) . 'scripts/aidetector.js', array('jquery'), AIOMATIC_MAJOR_VERSION );
    wp_enqueue_script( $name . '-aidetector-js' );
    wp_localize_script( $name . '-aidetector-js', 'aiomatic_aidetector_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('openai-aidetector-nonce')
	));
?>
<form class="aiomatic-aidetector-form">
<table class="form-table">
    <tr><td><h3><?php echo esc_html__('Input', 'aiomatic-automatic-ai-content-writer');?></h3></td><td><h3><?php echo esc_html__('Result', 'aiomatic-automatic-ai-content-writer');?></h3></td></tr>
<tr>
    <td class="aiomatic_width_half" >
    <textarea class="cr_fullw" rows="32" placeholder="Enter your text here" id="aiomatic_aidetector_input"></textarea>
    </td>
    <td class="aiomatic_width_half">
    <input type="text" value="" disabled placeholder="Conclusion confidence percentage" id="aiomatic_aidetector_percentage">
    <textarea class="cr_fullw" rows="30" disabled placeholder="AI content detector conclusion" id="aiomatic_aidetector_result"></textarea>
    </td>   
</tr>
<tr>
    <td colspan="2">
        <div>
            <div id="aiomatic_aidetector_success" class="aiomatic-hide"><?php echo esc_html__('Text AI content detection has completed successfully.', 'aiomatic-automatic-ai-content-writer');?></div>
            <div id="aiomatic_aidetector_progress" class="aiomatic-hide"><span></span><small><?php echo esc_html__('Checking. This will take some time. Please wait!', 'aiomatic-automatic-ai-content-writer');?></small></div>
            <div id="aiomatic-error-msg-aidetector"></div>
        </div>
    </td>
</tr>
<tr>
    <td colspan="2">
        <button class="button button-primary" id="button-start-aidetector"><?php echo esc_html__('Text AI Content Check', 'aiomatic-automatic-ai-content-writer');?></button>
    </td>
</tr>
</table>
</form>
<?php
    $myvariable = ob_get_clean();
    return $myvariable;
}
?>