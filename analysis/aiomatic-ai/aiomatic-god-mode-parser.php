<?php
defined('ABSPATH') or die();
function aiomatic_add_tool_results(&$reply, $add_me)
{
    if(isset($reply->aiomatic_tool_results) && is_array($reply->aiomatic_tool_results))
    {
        $reply->aiomatic_tool_results[] = $add_me;
    }
    else
    {
        $reply->aiomatic_tool_results = array($add_me);
    }
}
function aiomatic_add_tool_direct_message(&$reply, $add_me)
{
    if(isset($reply->aiomatic_tool_direct_message) && is_array($reply->aiomatic_tool_direct_message))
    {
        $reply->aiomatic_tool_direct_message[] = $add_me;
    }
    else
    {
        $reply->aiomatic_tool_direct_message = array($add_me);
    }
}
function aiomatic_add_tool_end_conversation(&$reply, $add_me)
{
    if(isset($reply->aiomatic_tool_end_conversation) && is_array($reply->aiomatic_tool_end_conversation))
    {
        $reply->aiomatic_tool_end_conversation[] = $add_me;
    }
    else
    {
        $reply->aiomatic_tool_end_conversation = array($add_me);
    }
}
add_filter('aiomatic_google_tools_ai_reply_raw', 'aiomatic_handle_god_mode_response', 10, 2);
add_filter('aiomatic_ai_reply_raw', 'aiomatic_handle_god_mode_response', 10, 2);
function aiomatic_handle_god_mode_response($reply, $query = array()) 
{
    if (isset($reply->tool_calls) && !empty($reply->tool_calls)) 
    {
        {
            $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
            require_once(__DIR__ . '/res/amazon-direct.php');
            foreach($reply->tool_calls as $tool_call)
            {
                if (isset($tool_call->type) && $tool_call->type == 'function')
                {
                    $result = false;
                    if (isset($tool_call->function->arguments) && is_string($tool_call->function->arguments)) 
                    {
                        $targs = json_decode($tool_call->function->arguments);
                        if($targs !== null)
                        {
                            $tool_call->function->arguments = $targs;
                        }
                        else
                        {
                            $strips = stripslashes($tool_call->function->arguments);
                            $targs = json_decode($strips);
                            if($targs !== null)
                            {
                                $tool_call->function->arguments = $targs;
                            }
                            else
                            {
                                $jsons = aiomatic_parse_multiple_json($tool_call->function->arguments);
                                if(!empty($jsons) && isset($jsons[0]))
                                {
                                    $tool_call->function->arguments = $jsons[0];
                                }
                            }
                        }
                    }
                    if(!isset($tool_call->function) || !isset($tool_call->function->arguments) || !isset($tool_call->function->name))
                    {
                        continue;
                    }
                    if(isset($reply->choices[0]->message))
                    {
                        $as_mes = $reply->choices[0]->message;
                    }
                    else
                    {
                        $as_mes = '';
                    }
                    if ($tool_call->function->name === 'aiomatic_wp_god_mode') 
                    {
                        if(isset($tool_call->function->arguments->called_function_name))
                        {
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $function_name = $tool_call->function->arguments->called_function_name;
                            $params = $tool_call->function->arguments->parameter_array;
                            if (isset($aiomatic_Chatbot_Settings['god_whitelisted_functions']) && trim($aiomatic_Chatbot_Settings['god_whitelisted_functions']) != '')
                            {
                                $white = trim($aiomatic_Chatbot_Settings['god_whitelisted_functions']);
                                $white = preg_split('/\r\n|\r|\n/', trim($white));
                                $white = array_filter($white);
                                if(!in_array($function_name, $white))
                                {
                                    if(isset($reply->choices[0]))
                                    {
                                        $reply->choices[0]->text = '';
                                        $reply->choices[0]->message->content = '';
                                    }
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Function call not allowed, not whitelisted: ' . $function_name);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'You are not allowed to call this function (not on the whitelisted functions list)',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            if (isset($aiomatic_Chatbot_Settings['god_blacklisted_functions']) && trim($aiomatic_Chatbot_Settings['god_blacklisted_functions']) != '')
                            {
                                $black = trim($aiomatic_Chatbot_Settings['god_blacklisted_functions']);
                                $black = preg_split('/\r\n|\r|\n/', trim($black));
                                $black = array_filter($black);
                                if(in_array($function_name, $black))
                                {
                                    if(isset($reply->choices[0]))
                                    {
                                        $reply->choices[0]->text = '';
                                        $reply->choices[0]->message->content = '';
                                    }
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Function call not allowed, blacklisted: ' . $function_name);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'You are not allowed to call this function (on the blacklisted functions list)',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            if(function_exists($function_name))
                            {
                                if(!is_array($params))
                                {
                                    $jsony = json_decode($params, true);
                                    if($jsony !== null && is_array($jsony))
                                    {
                                        $params = $jsony;
                                    }
                                    else
                                    {
                                        if(empty($params))
                                        {
                                            $params = array();
                                        }
                                        else
                                        {
                                            $params = array($params);
                                        }
                                    }
                                }
                                if(isset($params['post_title']) && $function_name == 'wp_insert_post')
                                {
                                    $params = array($params);
                                }
                                $paramsAsString = aiomatic_format_function_params($params);
                                $reflection = new ReflectionFunction($function_name);
                                $requiredParamsCount = $reflection->getNumberOfRequiredParameters();
                                if(is_numeric($requiredParamsCount) && $requiredParamsCount > 0 && count($params) < $requiredParamsCount)
                                {
                                    $result = $function_name . ' function has ' . $requiredParamsCount .' required parameters, but only ' . count($params) . ' were passed to it.';
                                }
                                else
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Calling function ' . $function_name . '(' . $paramsAsString . ')...');
                                    }
                                    $result = call_user_func_array($function_name, $params);
                                }
                                
                            }
                            else
                            {
                                $result = $function_name . ' function was not found on the system.';
                            }
                            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                            {
                                $paramsAsString = aiomatic_format_function_params($params);
                                if($result === false)
                                {
                                    aiomatic_log_to_file('Function ' . $function_name . '(' . $paramsAsString . ') - returned false');
                                    $result = $function_name . ' returned false';
                                }
                                elseif(empty($result))
                                {
                                    aiomatic_log_to_file('Function ' . $function_name . '(' . $paramsAsString . ') - returned an empty response: ' . print_r($result, true));
                                    $result = $function_name . ' returned an empty response';
                                }
                                else
                                {
                                    aiomatic_log_to_file('Function ' . $function_name . '(' . $paramsAsString . ') - result: ' . print_r($result, true));
                                }
                            }
                            if(isset($reply->choices[0]))
                            {
                                $reply->choices[0]->text = '';
                                $reply->choices[0]->message->content = '';
                            }
                            if(is_object($result) || is_array($result))
                            {
                                $result = json_encode($result);
                            }
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'called_function_name parameter not found',
                                'assistant_message' => $as_me
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_end') 
                    {
                        aiomatic_add_tool_end_conversation($reply, array(
                            "tool_call_id" => $tool_call->id,
                            "role" => "tool",
                            "content" => 'Conversation ended',
                            'assistant_message' => $as_mes
                        ));
                    }
                    elseif ($tool_call->function->name === 'aiomatic_chart') 
                    {
                        if(isset($tool_call->function->arguments->shortcode))
                        {
                            $shortcode = $tool_call->function->arguments->shortcode;
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => do_shortcode($shortcode),
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'shortcode parameter not found',
                                'assistant_message' => $as_me
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_image') 
                    {
                        if(isset($tool_call->function->arguments->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $tool_call->function->arguments->prompt;
                            if (!isset($aiomatic_Main_Settings['app_id'])) 
                            {
                                $aiomatic_Main_Settings['app_id'] = '';
                            }
                            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                            $appids = array_filter($appids);
                            $token = $appids[array_rand($appids)];
                            if (empty($token))
                            {
                                aiomatic_log_to_file('You need to enter an OpenAI API key for this to work!');
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size']) && trim($aiomatic_Chatbot_Settings['ai_image_size']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_model']) && trim($aiomatic_Chatbot_Settings['ai_image_model']) != '')
                            {
                                $model = trim($aiomatic_Chatbot_Settings['ai_image_model']);
                            }
                            else
                            {
                                $model = 'dalle2';
                            }
                            if(empty($result))
                            {
                                $aierror = '';
                                $airesult = aiomatic_generate_ai_image($token, 1, $prompt, $image_size, 'chatFunctionDalleImage', false, 0, $aierror, $model);
                                if($airesult !== false && is_array($airesult))
                                {
                                    foreach($airesult as $tmpimg)
                                    {
                                        $result = '<img class="image_max_w_ai" src="' . $tmpimg . '">';
                                        break;
                                    }
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to generate Dall-E image in AI chatbot: ' . $aierror);
                                    if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                    {
                                        $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                    }
                                    else
                                    {
                                        $result = 'Image creation failed, please try again later.';
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => $result,
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_me
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_stable_image') 
                    {
                        if(isset($tool_call->function->arguments->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $tool_call->function->arguments->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_stable']) && trim($aiomatic_Chatbot_Settings['ai_image_size_stable']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_stable']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            if (isset($aiomatic_Chatbot_Settings['stable_model']) && trim($aiomatic_Chatbot_Settings['stable_model']) != '')
                            {
                                $model = trim($aiomatic_Chatbot_Settings['stable_model']);
                            }
                            else
                            {
                                $model = AIOMATIC_STABLE_DEFAULT_MODE;
                            }
                            $aierror = '';
                            $airesult = aiomatic_generate_stability_image($prompt, $height, $width, 'chatFunctionStableImage', 0, false, $aierror, false, $model, '', 0);
                            if($airesult !== false && isset($airesult[1]))
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult[1] . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Stable image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_google_image') 
                    {
                        if(isset($tool_call->function->arguments->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $tool_call->function->arguments->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Main_Settings['google_ai_image_model']) && trim($aiomatic_Main_Settings['google_ai_image_model']) != '') 
                            {
                                $model = trim($aiomatic_Main_Settings['google_ai_image_model']);
                            }
                            else
                            {
                                $model = AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL;
                            }
                            $aierror = '';
                            $airesult = aiomatic_generate_image_google($prompt, $model, 'chatFunctionGoogleImage', false, $aierror, false);
                            if($airesult !== false && isset($airesult[1]))
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult[1] . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Stable image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_midjourney_image') 
                    {
                        if(isset($tool_call->function->arguments->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $tool_call->function->arguments->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_midjourney']) && trim($aiomatic_Chatbot_Settings['ai_image_size_midjourney']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_midjourney']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            $aierror = '';
                            $airesult = aiomatic_generate_ai_image_midjourney($prompt, $width, $height, 'chatFunctionMidjourneyImage', false, $aierror);
                            if($airesult !== false)
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Midjourney image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_replicate_image') 
                    {
                        if(isset($tool_call->function->arguments->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $tool_call->function->arguments->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_replicate']) && trim($aiomatic_Chatbot_Settings['ai_image_size_replicate']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_replicate']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            $aierror = '';
                            $airesult = aiomatic_generate_replicate_image($prompt, $width, $height, 'chatFunctionReplicateImage', false, $aierror);
                            if($airesult !== false)
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Replicate image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_ideogram_image') 
                    {
                        if(isset($tool_call->function->arguments->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $tool_call->function->arguments->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_ideogram']) && trim($aiomatic_Chatbot_Settings['ai_image_size_ideogram']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_ideogram']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            $aierror = '';
                            $airesult = aiomatic_generate_ideogram_image($prompt, $width, $height, 'chatFunctionIdeogramImage', false, $aierror);
                            if($airesult !== false)
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Ideogram image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_stable_video') 
                    {
                        if(isset($tool_call->function->arguments->image_url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $image_url = $tool_call->function->arguments->image_url;
                            if (empty($image_url))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_video_size_stable']) && trim($aiomatic_Chatbot_Settings['ai_video_size_stable']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_video_size_stable']);
                            }
                            else
                            {
                                $image_size = '768x768';
                            }
                            $aierror = '';
                            $response_text = aiomatic_generate_stability_video($image_url, $image_size, 'chatbotStableVideo', 0, false, $aierror, false);
                            if($response_text !== false && isset($response_text[1]))
                            {
                                $result = '<div style="padding-bottom:56.25%; position:relative; display:block; width: 100%"><iframe src="' . $response_text[1] . '" width="100%" height="100%" style="position:absolute; top:0; left: 0" allowfullscreen webkitallowfullscreen frameborder="0"></iframe></div>';
                                aiomatic_add_tool_direct_message($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Dall-E video in AI chatbot: ' . $aierror);
                                $result = 'Video creation failed, please try again later.';
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_amazon_listing') 
                    {
                        if(isset($tool_call->function->arguments->query))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $asin = $tool_call->function->arguments->query;
                            if (empty($asin)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty search query provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['affiliate_id']) && trim($aiomatic_Chatbot_Settings['affiliate_id']) != '')
                            {
                                $aff_id = trim($aiomatic_Chatbot_Settings['affiliate_id']);
                            }
                            else
                            {
                                $aff_id = '';
                            }
                            if (isset($aiomatic_Chatbot_Settings['target_country']) && trim($aiomatic_Chatbot_Settings['target_country']) != '')
                            {
                                $target_country = trim($aiomatic_Chatbot_Settings['target_country']);
                            }
                            else
                            {
                                $target_country = 'com';
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_products']) && trim($aiomatic_Chatbot_Settings['max_products']) != '')
                            {
                                $max_product_count = trim($aiomatic_Chatbot_Settings['max_products']);
                            }
                            else
                            {
                                $max_product_count = '3-4';
                            }
                            if (isset($aiomatic_Chatbot_Settings['sort_results']) && trim($aiomatic_Chatbot_Settings['sort_results']) != '')
                            {
                                $amaz_sort_results = trim($aiomatic_Chatbot_Settings['sort_results']);
                            }
                            else
                            {
                                $amaz_sort_results = 'none';
                            }
                            if (isset($aiomatic_Chatbot_Settings['listing_template']) && trim($aiomatic_Chatbot_Settings['listing_template']) != '')
                            {
                                $listing_template = trim($aiomatic_Chatbot_Settings['listing_template']);
                            }
                            else
                            {
                                $listing_template = '%%product_counter%%. %%product_title%% - Desciption: %%product_description%% - Link: %%product_url%% - Price: %%product_price%%';
                            }
                            
                            if(strstr($max_product_count, '-') !== false)
                            {
                                $pr_arr = explode('-', $max_product_count);
                                $minx = trim($pr_arr[0]);
                                $maxx = trim($pr_arr[1]);
                                if(is_numeric($minx) && is_numeric($maxx))
                                {
                                    $max_product_count = rand(intval($minx), intval($maxx));
                                }
                                else
                                {
                                    if(is_numeric($minx))
                                    {
                                        $max_product_count = intval($minx);
                                    }
                                    elseif(is_numeric($maxx))
                                    {
                                        $max_product_count = intval($maxx);
                                    }
                                    else
                                    {
                                        $max_product_count = 100;
                                    }
                                }
                            }
                            if(!empty($max_product_count) && is_numeric($max_product_count))
                            {
                                $max_prod = intval($max_product_count);
                            }
                            else
                            {
                                $max_prod = 100;
                            }
                            $amazresult = aiomatic_amazon_get_post($asin, trim($aff_id), $target_country, '', '', $amaz_sort_results, $max_prod, '1', array());
                            if(is_array($amazresult) && ((isset($amazresult['status']) && $amazresult['status'] == 'nothing') || count($amazresult) == 0))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No Amazon products found for query: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(!is_array($amazresult))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'An error occurred while search Amazon for: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            
                            $final_result = '';
                            $counter = 1;
                            foreach($amazresult as $myprod)
                            {
                                $copy_template = $listing_template;
                                $copy_template = str_replace('%%product_counter%%', $counter, $copy_template);
                                $copy_template = str_replace('%%product_title%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_title)), $copy_template);
                                $copy_template = str_replace('%%product_description%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_desc)), $copy_template);
                                $copy_template = str_replace('%%product_url%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_url)), $copy_template);
                                $copy_template = str_replace('%%product_price%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_price)), $copy_template);
                                $copy_template = str_replace('%%product_list_price%%', trim(preg_replace('/\s+/', ' ', $myprod->product_list_price)), $copy_template);
                                $copy_template = str_replace('%%product_image%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_img)), $copy_template);
                                $copy_template = str_replace('%%product_cart_url%%', trim(preg_replace('/\s+/', ' ', $myprod->cart_url)), $copy_template);
                                $copy_template = str_replace('%%product_images_urls%%', trim(preg_replace('/\s+/', ' ', $myprod->product_imgs)), $copy_template);
                                $copy_template = str_replace('%%product_images%%', trim(preg_replace('/\s+/', ' ', $myprod->product_imgs_html)), $copy_template);
                                $copy_template = str_replace('%%product_reviews%%', trim(preg_replace('/\s+/', ' ', implode(PHP_EOL, $myprod->item_reviews))), $copy_template);
                                //new
                                $copy_template = str_replace('%%product_score%%', trim(preg_replace('/\s+/', ' ', $myprod->item_score)), $copy_template);
                                $copy_template = str_replace('%%product_language%%', trim(preg_replace('/\s+/', ' ', $myprod->language)), $copy_template);
                                $copy_template = str_replace('%%product_edition%%', trim(preg_replace('/\s+/', ' ', $myprod->edition)), $copy_template);
                                $copy_template = str_replace('%%product_pages_count%%', trim(preg_replace('/\s+/', ' ', $myprod->pages_count)), $copy_template);
                                $copy_template = str_replace('%%product_publication_date%%', trim(preg_replace('/\s+/', ' ', $myprod->publication_date)), $copy_template);
                                $copy_template = str_replace('%%product_contributors%%', trim(preg_replace('/\s+/', ' ', $myprod->contributors)), $copy_template);
                                $copy_template = str_replace('%%product_manufacturer%%', trim(preg_replace('/\s+/', ' ', $myprod->manufacturer)), $copy_template);
                                $copy_template = str_replace('%%product_binding%%', trim(preg_replace('/\s+/', ' ', $myprod->binding)), $copy_template);
                                $copy_template = str_replace('%%product_product_group%%', trim(preg_replace('/\s+/', ' ', $myprod->product_group)), $copy_template);
                                $copy_template = str_replace('%%product_rating%%', trim(preg_replace('/\s+/', ' ', $myprod->rating)), $copy_template);
                                $copy_template = str_replace('%%product_ean%%', trim(preg_replace('/\s+/', ' ', $myprod->eans)), $copy_template);
                                $copy_template = str_replace('%%product_part_no%%', trim(preg_replace('/\s+/', ' ', $myprod->part_no)), $copy_template);
                                $copy_template = str_replace('%%product_model%%', trim(preg_replace('/\s+/', ' ', $myprod->model)), $copy_template);
                                $copy_template = str_replace('%%product_warranty%%', trim(preg_replace('/\s+/', ' ', $myprod->warranty)), $copy_template);
                                $copy_template = str_replace('%%product_color%%', trim(preg_replace('/\s+/', ' ', $myprod->color)), $copy_template);
                                $copy_template = str_replace('%%product_is_adult%%', trim(preg_replace('/\s+/', ' ', $myprod->is_adult)), $copy_template);
                                $copy_template = str_replace('%%product_dimensions%%', trim(preg_replace('/\s+/', ' ', $myprod->dimensions)), $copy_template);
                                $copy_template = str_replace('%%product_size%%', trim(preg_replace('/\s+/', ' ', $myprod->size)), $copy_template);
                                $copy_template = str_replace('%%product_unit_count%%', trim(preg_replace('/\s+/', ' ', $myprod->unit_count)), $copy_template);
                                
                                $counter++;
                                $final_result .= $copy_template . '\r\n'; 
                            }
                            $final_result = trim($final_result);
                            $final_result = substr($final_result, 0, 400000);
                            if(!empty($final_result))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_result,
                                    'assistant_message' => $as_mes
                                ));
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Amazon did not return info for this query: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_amazon_product_details') 
                    {
                        if(isset($tool_call->function->arguments->query))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $asin = $tool_call->function->arguments->query;
                            if (empty($asin)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty search query provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['affiliate_id']) && trim($aiomatic_Chatbot_Settings['affiliate_id']) != '')
                            {
                                $aff_id = trim($aiomatic_Chatbot_Settings['affiliate_id']);
                            }
                            else
                            {
                                $aff_id = '';
                            }
                            if (isset($aiomatic_Chatbot_Settings['target_country']) && trim($aiomatic_Chatbot_Settings['target_country']) != '')
                            {
                                $target_country = trim($aiomatic_Chatbot_Settings['target_country']);
                            }
                            else
                            {
                                $target_country = 'com';
                            }
                            $max_prod = 1;
                            $amazresult = aiomatic_amazon_get_post($asin, trim($aff_id), $target_country, '', '', '', $max_prod, '1', array());
                            if(is_array($amazresult) && ((isset($amazresult['status']) && $amazresult['status'] == 'nothing') || count($amazresult) == 0))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No Amazon products found for query: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(!is_array($amazresult))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'An error occurred while search Amazon for: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $final_result = 'Product title: ' . $amazresult[0]->offer_title . '\n';
                                $final_result .= 'Description: ' . $amazresult[0]->offer_desc . '\n';
                                $final_result .= 'URL: ' . $amazresult[0]->offer_url . '\n';
                                $final_result .= 'Price: ' . $amazresult[0]->offer_price . '\n';
                                $final_result .= 'Listing Price: ' . $amazresult[0]->product_list_price . '\n';
                                $final_result .= 'Image: ' . $amazresult[0]->offer_img . '\n';
                                $final_result .= 'Add to cart URL: ' . $amazresult[0]->cart_url . '\n';
                                $final_result .= 'Other images: ' . $amazresult[0]->product_imgs . '\n';
                                //new
                                if(!empty($the_current_section->item_score))
                                {
                                    $final_result .= 'Score: ' . $amazresult[0]->item_score . '\n';
                                }
                                if(!empty($the_current_section->language))
                                {
                                    $final_result .= 'Language: ' . $amazresult[0]->language . '\n';
                                }
                                if(!empty($the_current_section->edition))
                                {
                                    $final_result .= 'Edition: ' . $amazresult[0]->edition . '\n';
                                }
                                if(!empty($the_current_section->pages_count))
                                {
                                    $final_result .= 'Pages Count: ' . $amazresult[0]->pages_count . '\n';
                                }
                                if(!empty($the_current_section->publication_date))
                                {
                                    $final_result .= 'Date: ' . $amazresult[0]->publication_date . '\n';
                                }
                                if(!empty($the_current_section->contributors))
                                {
                                    $final_result .= 'Contributors: ' . $amazresult[0]->contributors . '\n';
                                }
                                if(!empty($the_current_section->manufacturer))
                                {
                                    $final_result .= 'Manufacturer: ' . $amazresult[0]->manufacturer . '\n';
                                }
                                if(!empty($the_current_section->binding))
                                {
                                    $final_result .= 'Binding: ' . $amazresult[0]->binding . '\n';
                                }
                                if(!empty($the_current_section->product_group))
                                {
                                    $final_result .= 'Product Group: ' . $amazresult[0]->product_group . '\n';
                                }
                                if(!empty($the_current_section->rating))
                                {
                                    $final_result .= 'Rating: ' . $amazresult[0]->rating . '\n';
                                }
                                if(!empty($the_current_section->eans))
                                {
                                    $final_result .= 'EAN: ' . $amazresult[0]->eans . '\n';
                                }
                                if(!empty($the_current_section->part_no))
                                {
                                    $final_result .= 'Part No: ' . $amazresult[0]->part_no . '\n';
                                }
                                if(!empty($the_current_section->model))
                                {
                                    $final_result .= 'Model: ' . $amazresult[0]->model . '\n';
                                }
                                if(!empty($the_current_section->warranty))
                                {
                                    $final_result .= 'Warranty: ' . $amazresult[0]->warranty . '\n';
                                }
                                if(!empty($the_current_section->color))
                                {
                                    $final_result .= 'Color: ' . $amazresult[0]->color . '\n';
                                }
                                if(!empty($the_current_section->is_adult))
                                {
                                    $final_result .= 'Is Adult: ' . $amazresult[0]->is_adult . '\n';
                                }
                                if(!empty($the_current_section->dimensions))
                                {
                                    $final_result .= 'Dimensions: ' . $amazresult[0]->dimensions . '\n';
                                }
                                if(!empty($the_current_section->size))
                                {
                                    $final_result .= 'Size: ' . $amazresult[0]->size . '\n';
                                }
                                if(!empty($the_current_section->unit_count))
                                {
                                    $final_result .= 'Unit Count: ' . $amazresult[0]->unit_count . '\n';
                                }
                                $final_result .= 'Reviews: ' . implode(PHP_EOL, $amazresult[0]->item_reviews) . '\n';
                                $final_result = substr($final_result, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_result,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_toubiz') 
                    {
                        if (!function_exists('is_plugin_active')) 
                        {
                            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                        }
                        $plugin_active = is_plugin_active('aiomatic-extension-toubiz-api/aiomatic-extension-toubiz-api.php');
                        if (!$plugin_active) 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Required plugin (Aiomatic Extension Toubiz API) not activated.',
                                'assistant_message' => $as_mes
                            ));
                            continue;
                        }
                        if (!function_exists('aiomatic_get_toubiz_api_results')) 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Required plugin (Aiomatic Extension Toubiz API) not correctly installed.',
                                'assistant_message' => $as_mes
                            ));
                            continue;
                        }
                        $search = '';
                        if(isset($tool_call->function->arguments['search']))
                        {
                            $search = $tool_call->function->arguments['search'];
                        }
                        $date_after = '';
                        if(isset($tool_call->function->arguments['date_after']))
                        {
                            $date_after = $tool_call->function->arguments['date_after'];
                        }
                        $date_before = '';
                        if(isset($tool_call->function->arguments['date_before']))
                        {
                            $date_before = $tool_call->function->arguments['date_before'];
                        }
                        $type = '';
                        if(isset($tool_call->function->arguments['type']))
                        {
                            $type = $tool_call->function->arguments['type'];
                        }
                        $event_type = '';
                        if(isset($tool_call->function->arguments['event_type']))
                        {
                            $event_type = $tool_call->function->arguments['event_type'];
                        }
                        $rectangle_area = '';
                        if(isset($tool_call->function->arguments['rectangle_area']))
                        {
                            $rectangle_area = $tool_call->function->arguments['rectangle_area'];
                        }
                        $distance_from = '';
                        if(isset($tool_call->function->arguments['distance_from']))
                        {
                            $distance_from = $tool_call->function->arguments['distance_from'];
                        }
                        $languages = '';
                        if(isset($tool_call->function->arguments['languages']))
                        {
                            $languages = $tool_call->function->arguments['languages'];
                        }
                        $toubiz_data = aiomatic_get_toubiz_api_results($search, $date_after, $date_before, $type, $event_type, $rectangle_area, $distance_from, $languages);
                        if ($toubiz_data === false) 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Failed to access Toubiz API',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to access Toubiz API: ' . print_r($tool_call->function->arguments, true));
                        } 
                        else 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $toubiz_data,
                                'assistant_message' => $as_mes
                            ));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_website_scraper') 
                    {
                        if(isset($tool_call->function->arguments->url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $scurl = $tool_call->function->arguments->url;
                            if (empty($scurl)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty scrape url provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['scrape_method']) && trim($aiomatic_Chatbot_Settings['scrape_method']) != '')
                            {
                                $scrape_method = trim($aiomatic_Chatbot_Settings['scrape_method']);
                            }
                            else
                            {
                                $scrape_method = '0';
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_chars']) && trim($aiomatic_Chatbot_Settings['max_chars']) != '')
                            {
                                $max_chars = trim($aiomatic_Chatbot_Settings['max_chars']);
                            }
                            else
                            {
                                $max_chars = '';
                            }
                            $scrape_selector = 'auto';
                            $scrape_string = '';
                            if (isset($aiomatic_Chatbot_Settings['strip_tags']) && trim($aiomatic_Chatbot_Settings['strip_tags']) != '')
                            {
                                $strip_tags = trim($aiomatic_Chatbot_Settings['strip_tags']);
                            }
                            else
                            {
                                $strip_tags = '0';
                            }
                            $scraped_data = aiomatic_scrape_page($scurl, $scrape_method, $scrape_selector, $scrape_string);
                            if($scraped_data === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to scrape website URL: ' . $scurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                if($strip_tags == '1')
                                {
                                    $scraped_data = wp_strip_all_tags($scraped_data);
                                }
                                else
                                {
                                    $scraped_data = aiomatic_fix_relative_links($scraped_data, $scurl);
                                }
                                if(!empty($max_chars) && is_numeric($max_chars))
                                {
                                    $scraped_data = (strlen($scraped_data) > intval($max_chars)) ? substr($scraped_data, 0, intval($max_chars)) : $scraped_data;
                                }
                                $scraped_data = substr($scraped_data, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $scraped_data,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_rss_parser') 
                    {
                        if(isset($tool_call->function->arguments->url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $scurl = $tool_call->function->arguments->url;
                            if (empty($scurl)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty RSS feed URL provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_rss_items']) && trim($aiomatic_Chatbot_Settings['max_rss_items']) != '')
                            {
                                $max_rss_items = intval(trim($aiomatic_Chatbot_Settings['max_rss_items']));
                            }
                            else
                            {
                                $max_rss_items = PHP_INT_MAX;
                            }
                            if (isset($aiomatic_Chatbot_Settings['rss_template']) && trim($aiomatic_Chatbot_Settings['rss_template']) != '')
                            {
                                $rss_template = trim($aiomatic_Chatbot_Settings['rss_template']);
                            }
                            else
                            {
                                $rss_template = '[%%item_counter%%]: %%item_title%% - %%item_description%%';
                            }
                            try
                            {
                                if(!class_exists('SimplePie_Autoloader', false))
                                {
                                    require_once(dirname(__FILE__) . "/res/simplepie/autoloader.php");
                                }
                            }
                            catch(Exception $e) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to load RSS parser library',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $feed = new SimplePie();
                            $feed->set_timeout(120);
                            $feed->set_feed_url($scurl);
                            $feed->enable_cache(false);
                            $feed->strip_htmltags(false);
                            $feed->init();
                            $feed->handle_content_type();
                            if ($feed->error()) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Error in parsing RSS feed: ' . $feed->error(),
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $final_result = '';
                            $items = $feed->get_items();
                            foreach($items as $itemx)
                            {
                                $post_link = trim($itemx->get_permalink());
                                $post_fcats = $itemx->get_categories();
                                if ($fauthor = $itemx->get_author()) 
                                {
                                    $user_name = $fauthor->get_name();
                                }
                                else
                                {
                                    $user_name = '';
                                }
                                $feed_cats = array();
                                if(is_array($post_fcats))
                                {
                                    foreach($post_fcats as $cata)
                                    {
                                        $feed_cats[] = $cata->__toString();
                                    }
                                    $post_cats = implode(',', $feed_cats);
                                }
                                else
                                {
                                    $post_cats = '';
                                }
                                $post_excerpt = $itemx->get_description();
                                $final_content = $itemx->get_content();
                                $rss_feeds[$itemx->get_title()] = array('url' => $post_link, 'author' => $user_name,  'cats' => $post_cats, 'excerpt' => $post_excerpt, 'content' => $final_content );
                            }
                            $template_copy = '';
                            $processed = 0;
                            foreach($rss_feeds as $rtitle => $this_rss)
                            {
                                if(!empty($max_rss_items) && $processed >= $max_rss_items)
                                {
                                    break;
                                }
                                $template_copy = $rss_template;
                                $template_copy = str_replace('%%item_counter%%', $processed + 1, $template_copy);
                                $template_copy = str_replace('%%item_title%%', $rtitle, $template_copy);
                                $template_copy = str_replace('%%item_content%%', $this_rss['content'], $template_copy);
                                $template_copy = str_replace('%%item_description%%', $this_rss['excerpt'], $template_copy);
                                $template_copy = str_replace('%%item_url%%', $this_rss['url'], $template_copy);
                                $template_copy = str_replace('%%item_author%%', $this_rss['author'], $template_copy);
                                $template_copy = str_replace('%%item_categories%%', $this_rss['cats'], $template_copy);
                                if(!empty($template_copy))
                                {
                                    $final_result .= $template_copy . PHP_EOL;
                                }
                                $processed++;
                            }
                            if(empty($final_result))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse RSS URL (no data returned): ' . $scurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $final_result = substr($final_result, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_result,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_google_parser') 
                    {
                        if(isset($tool_call->function->arguments->keywords))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $keywords = $tool_call->function->arguments->keywords;
                            if (empty($keywords)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty keywords parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_google_items']) && trim($aiomatic_Chatbot_Settings['max_google_items']) != '')
                            {
                                $max_google_items = intval(trim($aiomatic_Chatbot_Settings['max_google_items']));
                            }
                            else
                            {
                                $max_google_items = PHP_INT_MAX;
                            }
                            if (isset($aiomatic_Chatbot_Settings['google_template']) && trim($aiomatic_Chatbot_Settings['google_template']) != '')
                            {
                                $google_template = trim($aiomatic_Chatbot_Settings['google_template']);
                            }
                            else
                            {
                                $google_template = '[%%item_counter%%]: %%item_title%% - %%item_snippet%%';
                            }
                            $locale = '';
                            if (isset($aiomatic_Main_Settings['internet_gl']) && $aiomatic_Main_Settings['internet_gl'] != '')
                            {
                                $locale = $aiomatic_Main_Settings['internet_gl'];
                            }
                            $internet_rez = aiomatic_internet_result($keywords, true, $locale);
                            $processed = 0;
                            $final_res = '';
                            foreach($internet_rez as $emb)
                            {
                                if(!empty($max_google_items) && $processed >= $max_google_items)
                                {
                                    break;
                                }
                                $template_copy = $google_template;
                                $template_copy = str_replace('%%item_counter%%', $processed + 1, $template_copy);
                                $template_copy = str_replace('%%item_title%%', $emb['title'], $template_copy);
                                $template_copy = str_replace('%%item_snippet%%', $emb['snippet'], $template_copy);
                                $template_copy = str_replace('%%item_url%%', $emb['link'], $template_copy);
                                $final_res .= $template_copy . PHP_EOL;
                                $processed++;
                            }
                            if(empty($final_res))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse Google SERP for keyword (no data returned): ' . $keywords,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $final_res = substr($final_res, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_res,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'keywords parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_royalty_free_image') 
                    {
                        if(isset($tool_call->function->arguments->keyword))
                        {
                            $raw_img_list = array();
                            $full_result_list = array();
                            $result = '';
                            $keyword = $tool_call->function->arguments->keyword;
                            if (empty($keyword)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty keyword parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $temp_img_attr = '';
                            $attached_id = '';
                            $temp_get_img = aiomatic_get_free_image($aiomatic_Main_Settings, $keyword, $temp_img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                            if(empty($temp_get_img))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to get royalty free image for keyword: ' . $keyword,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                aiomatic_add_tool_direct_message($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => '<img class="image_max_w_ai" src="' . $temp_get_img . '">',
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'keyword parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                            continue;
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_lead_capture') 
                    {
                        if(isset($tool_call->function->arguments->email))
                        {
                            $result = '';
                            $name = '';
                            $phone_number = '';
                            $job_title = '';
                            $company_name = '';
                            $location = '';
                            $birth_date = '';
                            $how_you_found_us = '';
                            $website_url = '';
                            $preferred_contact_method = '';
                            $email = $tool_call->function->arguments->email;
                            if (empty($email)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty email parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(isset($tool_call->function->arguments->name))
                            {
                                $name = $tool_call->function->arguments->name;
                            }
                            if(isset($tool_call->function->arguments->phone_number))
                            {
                                $phone_number = $tool_call->function->arguments->phone_number;
                            }
                            if(isset($tool_call->function->arguments->job_title))
                            {
                                $job_title = $tool_call->function->arguments->job_title;
                            }
                            if(isset($tool_call->function->arguments->company_name))
                            {
                                $company_name = $tool_call->function->arguments->company_name;
                            }
                            if(isset($tool_call->function->arguments->location))
                            {
                                $location = $tool_call->function->arguments->location;
                            }
                            if(isset($tool_call->function->arguments->birth_date))
                            {
                                $birth_date = $tool_call->function->arguments->birth_date;
                            }
                            if(isset($tool_call->function->arguments->how_you_found_us))
                            {
                                $how_you_found_us = $tool_call->function->arguments->how_you_found_us;
                            }
                            if(isset($tool_call->function->arguments->website_url))
                            {
                                $website_url = $tool_call->function->arguments->website_url;
                            }
                            if(isset($tool_call->function->arguments->preferred_contact_method))
                            {
                                $preferred_contact_method = $tool_call->function->arguments->preferred_contact_method;
                            }
                            $lead_result = aiomatic_save_lead_data(
                                $email,
                                $name,
                                $phone_number,
                                $job_title,
                                $company_name,
                                $location,
                                $birth_date,
                                $how_you_found_us,
                                $website_url,
                                $preferred_contact_method
                            );
                            if($lead_result === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Error! Failed to save the lead data.',
                                'assistant_message' => $as_mes
                            ));
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Lead data saved successfully.',
                                    'assistant_message' => $as_mes
                                ));
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'email parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                            continue;
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_database') 
                    {
                        if(isset($tool_call->function->arguments->query))
                        {
                            $query = $tool_call->function->arguments->query;
                            if (empty($query)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty query parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            if (isset($aiomatic_Chatbot_Settings['database_command_types']) && is_array($aiomatic_Chatbot_Settings['database_command_types']) && !empty($aiomatic_Chatbot_Settings['database_command_types']))
                            {
                                $allowed_commands = $aiomatic_Chatbot_Settings['database_command_types'];
                            }
                            else
                            {
                                $allowed_commands = array('SELECT');
                            }
                            preg_match('/^\s*(\w+)/i', $query, $matches);
                            $query_type = strtoupper($matches[1] ?? '');
                            if (!in_array($query_type, $allowed_commands, true)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => "Query type '$query_type' is not allowed. Allowed types: " . implode(', ', $allowed_commands),
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            global $wpdb;
                            try 
                            {
                                $lead_result = null;
                                if ($query_type === 'SELECT') 
                                {
                                    $lead_result = $wpdb->get_results($query, ARRAY_A);
                                } 
                                elseif (in_array($query_type, array('INSERT', 'UPDATE', 'DELETE'), true)) 
                                {
                                    $lead_result = $wpdb->query($query); 
                                }
                                if ($lead_result === null || $lead_result === false) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Error! Invalid query or no results found.',
                                        'assistant_message' => $as_mes
                                    ));
                                } 
                                else 
                                {
                                    if ($query_type === 'SELECT' && count($lead_result) > 500) {
                                        $lead_result = array_slice($lead_result, 0, 500);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => json_encode($lead_result), 
                                        'assistant_message' => $as_mes
                                    ));
                                }
                            } 
                            catch (Exception $e) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Error executing the query: ' . $e->getMessage(),
                                    'assistant_message' => $as_mes
                                ));
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                            continue;
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_omniblock') 
                    {
                        if(isset($tool_call->function->arguments->omniblock_id))
                        {
                            if(isset($tool_call->function->arguments->input_parameters))
                            {
                                $input_text = $tool_call->function->arguments->input_parameters;
                            }
                            else
                            {
                                $input_text = '';
                            }
                            $omniblock_id = $tool_call->function->arguments->omniblock_id;
                            if (empty($omniblock_id)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty omniblock_id parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            if (isset($aiomatic_Chatbot_Settings['god_omniblock_rules']) && !empty(trim($aiomatic_Chatbot_Settings['god_omniblock_rules'])))
                            {
                                $omniblock_descriptions = array();
                                $omni_arr = preg_split('/\r\n|\r|\n/', trim($aiomatic_Chatbot_Settings['god_omniblock_rules']));
                                $omni_arr = array_map('trim', $omni_arr);
                                $omni_arr = array_filter($omni_arr);
                                foreach ($omni_arr as $line) {
                                    if (strpos($line, '=>') !== false) {
                                        [$id, $desc] = array_map('trim', explode('=>', $line, 2));
                                        if (!empty($id) && !empty($desc)) {
                                            $omniblock_descriptions[$id] = "`$id`: $desc";
                                        }
                                    }
                                }
                                if(!array_key_exists($omniblock_id, $omniblock_descriptions))
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'This OmniBlock ID does not exist in the configured rules',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No OmniBlock rules configured in settings',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $input_text = json_decode($input_text, true);
                            if($input_text === null || $input_text === false || $input_text === true)
                            {
                                $input_text = [];
                            }
                            require_once (dirname(__FILE__) . "/aiomatic-automation.php"); 
                            $cont = 0;
                            $id_parts = explode('_', $omniblock_id);
                            if(!isset($id_parts[1]))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Incorrect OmniBlock ID submitted in request',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(count($id_parts) > 2)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Incorrect format for OmniBlock IDs',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $param = $id_parts[0];
                            $omniTriggerID = $id_parts[1];
                            $rules = get_option('aiomatic_omni_list', array());
                            $found = false;
                            if (!empty($rules)) 
                            {
                                $default_block_types = aiomatic_omniblocks_default_block_types(); 
                                foreach ($rules as $request => $bundle[]) 
                                {
                                    if ($cont == $param) 
                                    {
                                        $bundle_values    = array_values($bundle);
                                        $myValues         = $bundle_values[$cont];
                                        $array_my_values  = array_values($myValues);for($iji=0;$iji<count($array_my_values);++$iji){if(is_string($array_my_values[$iji])){$array_my_values[$iji]=stripslashes($array_my_values[$iji]);}}
                                        $schedule         = isset($array_my_values[0]) ? $array_my_values[0] : '';
                                        $active           = isset($array_my_values[1]) ? $array_my_values[1] : '';
                                        $last_run         = isset($array_my_values[2]) ? $array_my_values[2] : '';
                                        $max              = isset($array_my_values[3]) ? $array_my_values[3] : '';
                                        $main_keywords    = isset($array_my_values[4]) ? $array_my_values[4] : '';
                                        $title_once       = isset($array_my_values[5]) ? $array_my_values[5] : '';
                                        $rule_description = isset($array_my_values[6]) ? $array_my_values[6] : '';
                                        $rule_unique_id   = isset($array_my_values[7]) ? $array_my_values[7] : '';
                                        $sortable_cards   = isset($array_my_values[8]) ? $array_my_values[8] : '';
                                        $more_keywords    = isset($array_my_values[9]) ? $array_my_values[9] : '';
                                        $days_no_run      = isset($array_my_values[10]) ? $array_my_values[10] : '';
                                        $block_data       = json_decode($sortable_cards, true);
                                        if(!empty($block_data))
                                        {
                                            foreach($block_data as $index => $current_block)
                                            {
                                                $card_type_found = array();
                                                foreach($default_block_types as $def_card)
                                                {
                                                    if($current_block['type'] == $def_card['id'])
                                                    {
                                                        $card_type_found = $def_card;
                                                        break;
                                                    }
                                                }
                                                if(!empty($card_type_found) && $omniTriggerID == $current_block['identifier'] && $current_block['type'] === 'ai_chatbot_omniblock')
                                                {
                                                    $return_last_result = true;
                                                    $return_me = aiomatic_run_rule($cont, 5, 1, 0, null, '', $omniTriggerID, $return_last_result, $input_text);
                                                    if($return_me == 'fail')
                                                    {
                                                        aiomatic_log_to_file('OmniBlock AI Chatbot Trigger rule running failed, rule ID: ' . $param . ' OmniBlock ID: ' . $omniTriggerID);
                                                    }
                                                    aiomatic_add_tool_results($reply, array(
                                                        "tool_call_id" => $tool_call->id,
                                                        "role" => "tool",
                                                        "content" => wp_kses_post($return_me), 
                                                        'assistant_message' => $as_mes
                                                    ));
                                                    $found = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                    $cont = $cont + 1;
                                }
                                if($found == false)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Specified \'AI Chatbot Trigger\' OmniBlock ID not found(3): ' . $omniblock_id,
                                        'assistant_message' => $as_mes
                                    ));
                                }
                            } 
                            else 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No rules found for aiomatic_omni_list!',
                                    'assistant_message' => $as_mes
                                ));
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                            continue;
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_generate_reasoning') 
                    {
                        if(isset($tool_call->function->arguments->prompt))
                        {
                            $prompt = $tool_call->function->arguments->prompt;
                            if (empty($prompt)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $model = '';
                            if(isset($tool_call->function->arguments->model))
                            {
                                $model = $tool_call->function->arguments->model;
                            }
                            if (empty($model)) 
                            {
                                $model = AIOMATIC_DEFAULT_MODEL;
                            }
                            if (!isset($aiomatic_Main_Settings['app_id'])) 
                            {
                                $aiomatic_Main_Settings['app_id'] = '';
                            }
                            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                            $appids = array_filter($appids);
                            $token = $appids[array_rand($appids)];
                            if (empty($token))
                            {
                                aiomatic_log_to_file('You need to enter an OpenAI API key for this to work!');
                                $result = '[ERROR] API key not configured in plugin settings.';
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $query_token_count = count(aiomatic_encode($prompt));
                            $max_tokens = aiomatic_get_max_tokens($model);
                            $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                            {
                                $string_len = strlen($prompt);
                                $string_len = $string_len / 2;
                                $string_len = intval(0 - $string_len);
                                $prompt = aiomatic_substr($prompt, 0, $string_len);
                                $prompt = trim($prompt);
                                $query_token_count = count(aiomatic_encode($prompt));
                                $available_tokens = $max_tokens - $query_token_count;
                            }
                            $thread_id = '';
                            $aierror = '';
                            $finish_reason = '';
                            remove_filter('aiomatic_ai_reply_raw', 'aiomatic_handle_god_mode_response');
                            $generated_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, 1, 1, 0, 0, false, 'aiThinking', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', '', $thread_id, '', 'disabled', '', false, false, array());
                            add_filter('aiomatic_ai_reply_raw', 'aiomatic_handle_god_mode_response', 10, 2);
                            if($generated_text === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => '[ERROR] Failed to generate content using AI writer: ' . $model . ' - error: ' . $aierror,
                                    'assistant_message' => $as_mes
                                ));
                            }
                            else
                            {
                                $new_post_content = aiomatic_sanitize_ai_result($generated_text);
                                if(empty($new_post_content))
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => '[ERROR] No content returned by the AI writer!',
                                        'assistant_message' => $as_mes
                                    ));
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => $new_post_content,
                                        'assistant_message' => $as_mes
                                    ));
                                }
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_youtube_captions') 
                    {
                        if(isset($tool_call->function->arguments->url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $zurl = $tool_call->function->arguments->url;
                            if (empty($zurl)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $default_lang = array();
                            $returned_caption = '';
                            $za_video_page = '';
                            $ch  = curl_init();
                            if ($ch !== FALSE) 
                            {
                                if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                                    $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                                    $randomness = array_rand($prx);
                                    curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                                    if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                                    {
                                        $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                                        if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                                        {
                                            curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                                        }
                                    }
                                }
                                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                                if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
                                {
                                    $ztime = intval($aiomatic_Main_Settings['max_timeout']);
                                }
                                else
                                {
                                    $ztime = 300;
                                }
                                curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
                                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                                curl_setopt($ch, CURLOPT_REFERER, get_site_url());
                                curl_setopt($ch, CURLOPT_URL, $zurl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                $za_video_page = curl_exec($ch);
                                if($za_video_page === false)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to download video URL: ' . $zurl,
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                curl_close($ch);
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to init curl in YouTube caption importing: ' . $zurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if($za_video_page !== false && strstr($za_video_page, 'vssId') !== false)
                            {
                                $srt_dl_link = '';
                                preg_match_all('#{"baseUrl":"([^"]+?)","name":(?:.*?),"vssId":"a?\.([^"]+?)","languageCode":"(?:[^"]+?)",(?:"kind":"asr",)?"isTranslatable":(?:[^}]+?)}#i', $za_video_page, $zmatches);
                                if(isset($zmatches[1][0]))
                                {
                                    $eng_f = false;
                                    if(in_array('en', $zmatches[2]))
                                    {
                                        $eng_f = true;
                                    }
                                    for($i = 0; $i < count($zmatches[1]); $i++)
                                    {
                                        if(count($default_lang) > 0)
                                        {
                                            if(in_array($zmatches[2][$i], $default_lang))
                                            {
                                                $srt_dl_link = str_replace('\u0026', '&', $zmatches[1][$i]);
                                                break;
                                            }
                                        }
                                        else
                                        {
                                            if(!$eng_f)
                                            {
                                                $srt_dl_link = str_replace('\u0026', '&', $zmatches[1][$i]);
                                                break;
                                            }
                                            elseif($zmatches[2][$i] == 'en')
                                            {
                                                $srt_dl_link = str_replace('\u0026', '&', $zmatches[1][$i]);
                                                break;
                                            }
                                        }
                                    }
                                    if($srt_dl_link !== '')
                                    {
                                        $ch  = curl_init();
                                        if ($ch !== FALSE) 
                                        {
                                            if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                                                $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                                                $randomness = array_rand($prx);
                                                curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                                                if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                                                {
                                                    $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                                                    if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                                                    {
                                                        curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                                                    }
                                                }
                                            }
                                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                                            if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
                                            {
                                                $ztime = intval($aiomatic_Main_Settings['max_timeout']);
                                            }
                                            else
                                            {
                                                $ztime = 300;
                                            }
                                            curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
                                            curl_setopt($ch, CURLOPT_HTTPGET, 1);
                                            curl_setopt($ch, CURLOPT_REFERER, get_site_url());
                                            curl_setopt($ch, CURLOPT_URL, $srt_dl_link);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            $xza_video_page = curl_exec($ch);
                                            if(!empty($xza_video_page))
                                            {
                                                $returned_caption = $xza_video_page;
                                                $returned_caption = preg_replace('#\s+#', ' ', $returned_caption);
                                            }
                                            curl_close($ch);
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Failed to init curl in subtitle listing: ' . $zurl);
                                        }   
                                    }
                                }
                            }
                            if(empty($returned_caption))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse YouTube Video captions from URL (no data returned): ' . $zurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $returned_caption = substr($returned_caption, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $returned_caption,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_youtube_search') 
                    {
                        if(isset($tool_call->function->arguments->keyword))
                        {
                            $result = '';
                            $keyword = $tool_call->function->arguments->keyword;
                            if (empty($keyword)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty keyword parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $new_vid = aiomatic_get_video($keyword, '', array());
                            if(empty($new_vid))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse YouTube video search results for keyword (no data returned): ' . $keyword,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                aiomatic_add_tool_direct_message($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $new_vid,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'keyword parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_send_email') 
                    {
                        if(isset($tool_call->function->arguments->subject) && isset($tool_call->function->arguments->content) && isset($tool_call->function->arguments->recipient_email))
                        {
                            $result = '';
                            $subject = $tool_call->function->arguments->subject;
                            $content = $tool_call->function->arguments->content;
                            $recipient_email = $tool_call->function->arguments->recipient_email;
                            if (empty($subject) || empty($content) || empty($recipient_email)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty subject, content or recipient_email parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                $admin_mail = get_bloginfo('admin_email');
                                $headers[] = 'From: AIomatic Plugin Chatbot <' . $admin_mail . '>';
                                $headers[] = 'Reply-To: ' . $admin_mail;
                                $headers[] = 'X-Mailer: PHP/' . phpversion();
                                $headers[] = 'Content-Type: text/html';
                                $headers[] = 'Charset: ' . get_option('blog_charset', 'UTF-8');
                                $sent = wp_mail($recipient_email, $subject, $content, $headers);
                                if($sent === false)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to send email to address: ' . $recipient_email,
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'OK',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to send mail: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'subject, content or recipient_email parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_webhook') 
                    {
                        if(isset($tool_call->function->arguments->webhook_url) && isset($tool_call->function->arguments->method_selector))
                        {
                            $webhook_url = $tool_call->function->arguments->webhook_url;
                            if (empty($webhook_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty webhook_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $webhook_method = $tool_call->function->arguments->method_selector;
                            if (empty($webhook_method)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty method_selector parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content_type = '';
                            if(isset($tool_call->function->arguments->content_type))
                            {
                                $content_type = $tool_call->function->arguments->content_type;
                            }
                            $post_template = '';
                            if(isset($tool_call->function->arguments->data))
                            {
                                $post_template = $tool_call->function->arguments->data;
                            }
                            $headers_template = '';
                            if(isset($tool_call->function->arguments->headers))
                            {
                                $headers_template = $tool_call->function->arguments->headers;
                            }
                            $urlParsed = parse_url( $webhook_url, PHP_URL_HOST );
                            if ( filter_var( $webhook_url, FILTER_VALIDATE_URL ) === FALSE || empty( $urlParsed ) )
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Invalid webhook_url entered ' . $webhook_url,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else if ( $content_type == 'JSON' && empty( json_decode( $post_template, TRUE ) ) )
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'The JSON data must be valid ' . $webhook_url,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $headers = [];
                            if(!empty($headers_template))
                            {
                                $headers_template_arr = preg_split('/\r\n|\r|\n/', trim($headers_template));
                                foreach($headers_template_arr as $arr_fr)
                                {
                                    if(!empty(trim($arr_fr)) && strstr($arr_fr, '=>'))
                                    {
                                        $small_arr = explode('=>', $arr_fr);
                                        $headers[] = trim($small_arr[0]) . ':' . trim($small_arr[1]);
                                    }
                                }
                            }
                            $content_params = [];
                            if(!empty($post_template))
                            {
                                $post_template_arr = preg_split('/\r\n|\r|\n/', trim($post_template));
                                foreach($post_template_arr as $arr_fr)
                                {
                                    if(!empty(trim($arr_fr)) && strstr($arr_fr, '=>'))
                                    {
                                        $small_arr = explode('=>', $arr_fr);
                                        $content_params[trim($small_arr[0])] = trim($small_arr[1]);
                                    }
                                }
                            }
                            $ch = curl_init();
                            if ($ch === false) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to init curl in webhook execution',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                                $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                                $randomness = array_rand($prx);
                                curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                                if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                                {
                                    $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                                    if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                                    {
                                        curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                                    }
                                }
                            }
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            if ($webhook_method == 'POST' || $webhook_method == 'PUT' || $webhook_method == 'DELETE') 
                            {
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $webhook_method);
                                if (!empty($content_params) && $content_type == 'form_data') {
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content_params));
                                } elseif (!empty($post_template) && $content_type == 'JSON') {
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_template));
                                    $headers[] = 'Content-Type: application/json';
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                }
                            }
                            else
                            {
                                $query_string = http_build_query($content_params);
                                $webhook_url = $webhook_url . (strpos($webhook_url, '?') === false ? '?' : '&') . $query_string;
                            }
                            curl_setopt($ch, CURLOPT_URL, $webhook_url);

                            $response = curl_exec($ch);
                            if($response === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to send webhook request to ' . $webhook_url,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $err = curl_error($ch);
                            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);
                            if ($err) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Webhook request error to URL ' . $webhook_url . ' - error: ' . $err,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }

                            if ($statusCode >= 200 && $statusCode <= 299) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Webhook URL called successfully!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            } 
                            else 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Webhook unexpected return code to URL ' . $webhook_url . ' - return code: ' . $statusCode,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'webhook_url or method_selector parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_facebook') 
                    {
                        if(isset($tool_call->function->arguments->content))
                        {
                            $result = '';
                            $zcontent = $tool_call->function->arguments->content;
                            if (empty($zcontent)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $fbomatic_active = false;
                                if (is_plugin_active('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php')) 
                                {
                                    $fbomatic_active = true;
                                }
                                if(!$fbomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (F-omatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $furl = '';
                                if(isset($tool_call->function->arguments->url))
                                {
                                    $furl = $tool_call->function->arguments->url;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['facebook_post_select']) && $aiomatic_Chatbot_Settings['facebook_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['facebook_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No page where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php' => array('F-omatic Automatic Post Generator', 'https://1.envato.market/fbomatic')));
                                $return_me = aiomatic_post_to_facebook($card_type_found, $zcontent, $furl, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Facebook posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Facebook posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Facebook posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Facebook posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Facebook posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_image_facebook') 
                    {
                        if(isset($tool_call->function->arguments->image_url))
                        {
                            $result = '';
                            $image_url = $tool_call->function->arguments->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $fbomatic_active = false;
                                if (is_plugin_active('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php')) 
                                {
                                    $fbomatic_active = true;
                                }
                                if(!$fbomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (F-omatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $caption = '';
                                if(isset($tool_call->function->arguments->caption))
                                {
                                    $caption = $tool_call->function->arguments->caption;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['facebook_post_select']) && $aiomatic_Chatbot_Settings['facebook_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['facebook_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No page where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php' => array('F-omatic Automatic Post Generator', 'https://1.envato.market/fbomatic')));
                                $return_me = aiomatic_post_image_to_facebook($card_type_found, $caption, $image_url, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Facebook image posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook image posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Facebook image posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Facebook image posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook image posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Facebook image posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Facebook image posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_twitter') 
                    {
                        if(isset($tool_call->function->arguments->content))
                        {
                            $result = '';
                            $zcontent = $tool_call->function->arguments->content;
                            if (empty($zcontent)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $twitomatic_active = false;
                                if (is_plugin_active('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php')) 
                                {
                                    $twitomatic_active = true;
                                }
                                if(!$twitomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Twitomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $image_url = '';
                                if(isset($tool_call->function->arguments->image_url))
                                {
                                    $image_url = $tool_call->function->arguments->image_url;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php' => array('Twitomatic Automatic Post Generator', 'https://1.envato.market/twitomatic')));
                                $return_me = aiomatic_post_to_twitter($card_type_found, $zcontent, $image_url);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Twitter posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Twitter posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Twitter posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Twitter posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Twitter posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Twitter posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Twitter posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_threads') 
                    {
                        if(isset($tool_call->function->arguments->content))
                        {
                            $result = '';
                            $zcontent = $tool_call->function->arguments->content;
                            if (empty($zcontent)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $threadsomatic_active = false;
                                if (is_plugin_active('threadsomatic-threads-auto-poster/threadsomatic-threads-auto-poster.php')) 
                                {
                                    $threadsomatic_active = true;
                                }
                                if(!$threadsomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Threadsomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $image_url = '';
                                if(isset($tool_call->function->arguments->image_url))
                                {
                                    $image_url = $tool_call->function->arguments->image_url;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('threadsomatic-threads-auto-poster/threadsomatic-threads-auto-poster.php' => array('Threadsomatic Threads Auto Poster', 'https://wpbay.com/product/threadsomatic-threads-auto-poster/')));
                                $return_me = aiomatic_post_to_threads($card_type_found, $zcontent, $image_url);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Threads posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Threads posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Threads posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Threads posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Threads posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Threads posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Threads posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_instagram') 
                    {
                        if(isset($tool_call->function->arguments->image_url))
                        {
                            $result = '';
                            $image_url = $tool_call->function->arguments->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Instamatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $content = '';
                                if(isset($tool_call->function->arguments->content))
                                {
                                    $content = $tool_call->function->arguments->content;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php' => array('iMediamatic Automatic Post Generator', 'https://1.envato.market/instamatic')));
                                $return_me = aiomatic_post_image_to_instagram($card_type_found, $content, $image_url);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Instagram posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Instagram posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Instagram posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Instagram posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Instagram posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Instagram posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Instagram posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_pinterest') 
                    {
                        if(isset($tool_call->function->arguments->image_url))
                        {
                            $result = '';
                            $image_url = $tool_call->function->arguments->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Pinterestomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['pinterest_post_select']) && $aiomatic_Chatbot_Settings['pinterest_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['pinterest_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No boards where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $title = '';
                                if(isset($tool_call->function->arguments->title))
                                {
                                    $title = $tool_call->function->arguments->title;
                                }
                                $description = '';
                                if(isset($tool_call->function->arguments->description))
                                {
                                    $description = $tool_call->function->arguments->description;
                                }
                                $pin_url = '';
                                if(isset($tool_call->function->arguments->pin_url))
                                {
                                    $pin_url = $tool_call->function->arguments->pin_url;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php' => array('Pinterestomatic Automatic Post Generator', 'https://1.envato.market/pinterestomatic')));
                                $return_me = aiomatic_post_image_to_pinterest($card_type_found, $description, $title, $pin_url, $image_url, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Pinterest posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Pinterest posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Pinterest posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Pinterest posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Pinterest posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Pinterest posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Pinterest posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_google_my_business') 
                    {
                        if(isset($tool_call->function->arguments->content) && isset($tool_call->function->arguments->image_url))
                        {
                            $result = '';
                            $image_url = $tool_call->function->arguments->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content = $tool_call->function->arguments->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('businessomatic-google-my-business-post-generator/businessomatic-google-my-business-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Businessomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['business_post_select']) && $aiomatic_Chatbot_Settings['business_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['business_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No pages where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('businessomatic-google-my-business-post-generator/businessomatic-google-my-business-post-generator.php' => array('Businessomatic Automatic Post Generator', 'https://1.envato.market/businessomatic')));
                                $return_me = aiomatic_post_to_gmb($card_type_found, $content, $image_url, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('GMB posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'GMB posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse GMB posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse GMB posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'GMB posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('GMB posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'GMB posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url or content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_youtube_community') 
                    {
                        if(isset($tool_call->function->arguments->content) && isset($tool_call->function->arguments->post_type))
                        {
                            $result = '';
                            $post_type = $tool_call->function->arguments->post_type;
                            if (empty($post_type)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty post_type parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $post_type = trim($post_type);
                            if($post_type != 'image' && $post_type != 'text')
                            {
                                $post_type = 'text';
                            }
                            $content = $tool_call->function->arguments->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $image_url = '';
                            if(isset($tool_call->function->arguments->image_url))
                            {
                                $image_url = $tool_call->function->arguments->image_url;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('youtubomatic-youtube-post-generator/youtubomatic-youtube-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Youtubomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                if($image_url != '')
                                {
                                    $media = array($image_url);
                                }
                                else
                                {
                                    $media = array();
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('youtubomatic-youtube-post-generator/youtubomatic-youtube-post-generator.php' => array('Youtubomatic Automatic Post Generator', 'https://1.envato.market/youtubomatic')));
                                $return_me = aiomatic_post_to_youtube_community($card_type_found, $content, $post_type, $media);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('YouTube Community posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'YouTube Community posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse YouTube Community posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse YouTube Community posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'YouTube Community posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('YouTube Community posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'YouTube Community posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content or post_type parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_reddit') 
                    {
                        if(isset($tool_call->function->arguments->content) && isset($tool_call->function->arguments->title))
                        {
                            $result = '';
                            $title = $tool_call->function->arguments->title;
                            if (empty($title)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty title parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content = $tool_call->function->arguments->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('redditomatic-reddit-post-generator/redditomatic-reddit-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Redditomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $subreddit_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['subreddits_list']) && $aiomatic_Chatbot_Settings['subreddits_list'] != '') {
                                    $subreddit_to_post = $aiomatic_Chatbot_Settings['subreddits_list'];
                                }
                                if (empty($subreddit_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No subreddits were defined where to publish the post',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('redditomatic-reddit-post-generator/redditomatic-reddit-post-generator.php' => array('Redditomatic Automatic Post Generator', 'https://1.envato.market/redditomatic')));
                                $return_me = aiomatic_post_to_reddit($card_type_found, $title, $content, 'auto', $subreddit_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Reddit posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Reddit posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Reddit posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Reddit posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Reddit posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Reddit posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Reddit posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content or title parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->function->name === 'aiomatic_publish_linkedin') 
                    {
                        if(isset($tool_call->function->arguments->content) && isset($tool_call->function->arguments->title))
                        {
                            $result = '';
                            $title = $tool_call->function->arguments->title;
                            if (empty($title)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty title parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content = $tool_call->function->arguments->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $description = '';
                            $post_link = '';
                            $image_url = '';
                            if(isset($tool_call->function->arguments->description))
                            {
                                $description = $tool_call->function->arguments->description;
                            }
                            if(isset($tool_call->function->arguments->link))
                            {
                                $post_link = $tool_call->function->arguments->link;
                            }
                            if(isset($tool_call->function->arguments->image_url))
                            {
                                $image_url = $tool_call->function->arguments->image_url;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('linkedinomatic-linkedin-post-generator/linkedinomatic-linkedin-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Linkedinomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $selected_pages = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['linkedin_selected_pages']) && $aiomatic_Chatbot_Settings['linkedin_selected_pages'] != '') {
                                    $selected_pages = $aiomatic_Chatbot_Settings['linkedin_selected_pages'];
                                }
                                if (empty($selected_pages)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No pages selected where to post to LinkedIn',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('linkedinomatic-linkedin-post-generator/linkedinomatic-linkedin-post-generator.php' => array('Linkedinomatic Automatic Post Generator', 'https://1.envato.market/linkedinomatic')));
                                $return_me = aiomatic_post_to_linkedin($card_type_found, $content, $image_url, $title, $post_link, $description, '1', $selected_pages);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('LinkedIn posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'LinkedIn posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse LinkedIn posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse LinkedIn posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'LinkedIn posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('LinkedIn posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'LinkedIn posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content or title parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                }
            }
        }
    }
    return $reply;
}
add_filter('aiomatic_ai_responses_reply_raw', 'aiomatic_handle_god_mode_responses_response', 10, 2);
function aiomatic_handle_god_mode_responses_response($reply, $query = array()) 
{
    if(!isset($reply->output) || !is_array($reply->output))
    {
        return $reply;
    }
    $function_found = false;
    foreach($reply->output as $aiout)
    {
        if($aiout->type && $aiout->type == 'function_call')
        {
            $function_found = true;
        }
    }
    if ($function_found == true) 
    {
        {
            $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
            require_once(__DIR__ . '/res/amazon-direct.php');
            foreach($reply->output as $tool_call)
            {
                if (isset($tool_call->type) && $tool_call->type == 'function_call')
                {
                    $result = false;
                    if (isset($tool_call->arguments) && is_string($tool_call->arguments)) 
                    {
                        $targs = json_decode($tool_call->arguments);
                        if($targs !== null)
                        {
                            $tool_call->arguments = $targs;
                        }
                        else
                        {
                            $strips = stripslashes($tool_call->arguments);
                            $targs = json_decode($strips);
                            if($targs !== null)
                            {
                                $tool_call->arguments = $targs;
                            }
                            else
                            {
                                $jsons = aiomatic_parse_multiple_json($tool_call->arguments);
                                if(!empty($jsons) && isset($jsons[0]))
                                {
                                    $tool_call->arguments = $jsons[0];
                                }
                            }
                        }
                    }
                    if(!isset($tool_call->arguments) || !isset($tool_call->name))
                    {
                        continue;
                    }
                    $as_mes = '';
                    $fun_args = $tool_call->arguments;
                    if ($tool_call->name === 'aiomatic_wp_god_mode') 
                    {
                        if(isset($fun_args->called_function_name))
                        {
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $function_name = $fun_args->called_function_name;
                            $params = $fun_args->parameter_array;
                            if (isset($aiomatic_Chatbot_Settings['god_whitelisted_functions']) && trim($aiomatic_Chatbot_Settings['god_whitelisted_functions']) != '')
                            {
                                $white = trim($aiomatic_Chatbot_Settings['god_whitelisted_functions']);
                                $white = preg_split('/\r\n|\r|\n/', trim($white));
                                $white = array_filter($white);
                                if(!in_array($function_name, $white))
                                {
                                    if(isset($reply->choices[0]))
                                    {
                                        $reply->choices[0]->text = '';
                                        $reply->choices[0]->message->content = '';
                                    }
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Function call not allowed, not whitelisted: ' . $function_name);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'You are not allowed to call this function (not on the whitelisted functions list)',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            if (isset($aiomatic_Chatbot_Settings['god_blacklisted_functions']) && trim($aiomatic_Chatbot_Settings['god_blacklisted_functions']) != '')
                            {
                                $black = trim($aiomatic_Chatbot_Settings['god_blacklisted_functions']);
                                $black = preg_split('/\r\n|\r|\n/', trim($black));
                                $black = array_filter($black);
                                if(in_array($function_name, $black))
                                {
                                    if(isset($reply->choices[0]))
                                    {
                                        $reply->choices[0]->text = '';
                                        $reply->choices[0]->message->content = '';
                                    }
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Function call not allowed, blacklisted: ' . $function_name);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'You are not allowed to call this function (on the blacklisted functions list)',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            if(function_exists($function_name))
                            {
                                if(!is_array($params))
                                {
                                    $jsony = json_decode($params, true);
                                    if($jsony !== null && is_array($jsony))
                                    {
                                        $params = $jsony;
                                    }
                                    else
                                    {
                                        if(empty($params))
                                        {
                                            $params = array();
                                        }
                                        else
                                        {
                                            $params = array($params);
                                        }
                                    }
                                }
                                if(isset($params['post_title']) && $function_name == 'wp_insert_post')
                                {
                                    $params = array($params);
                                }
                                $paramsAsString = aiomatic_format_function_params($params);
                                $reflection = new ReflectionFunction($function_name);
                                $requiredParamsCount = $reflection->getNumberOfRequiredParameters();
                                if(is_numeric($requiredParamsCount) && $requiredParamsCount > 0 && count($params) < $requiredParamsCount)
                                {
                                    $result = $function_name . ' function has ' . $requiredParamsCount .' required parameters, but only ' . count($params) . ' were passed to it.';
                                }
                                else
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Calling function ' . $function_name . '(' . $paramsAsString . ')...');
                                    }
                                    $result = call_user_func_array($function_name, $params);
                                }
                                
                            }
                            else
                            {
                                $result = $function_name . ' function was not found on the system.';
                            }
                            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                            {
                                $paramsAsString = aiomatic_format_function_params($params);
                                if($result === false)
                                {
                                    aiomatic_log_to_file('Function ' . $function_name . '(' . $paramsAsString . ') - returned false');
                                    $result = $function_name . ' returned false';
                                }
                                elseif(empty($result))
                                {
                                    aiomatic_log_to_file('Function ' . $function_name . '(' . $paramsAsString . ') - returned an empty response: ' . print_r($result, true));
                                    $result = $function_name . ' returned an empty response';
                                }
                                else
                                {
                                    aiomatic_log_to_file('Function ' . $function_name . '(' . $paramsAsString . ') - result: ' . print_r($result, true));
                                }
                            }
                            if(isset($reply->choices[0]))
                            {
                                $reply->choices[0]->text = '';
                                $reply->choices[0]->message->content = '';
                            }
                            if(is_object($result) || is_array($result))
                            {
                                $result = json_encode($result);
                            }
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'called_function_name parameter not found',
                                'assistant_message' => $as_me
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_end') 
                    {
                        aiomatic_add_tool_end_conversation($reply, array(
                            "tool_call_id" => $tool_call->id,
                            "role" => "tool",
                            "content" => 'Conversation ended',
                            'assistant_message' => $as_mes
                        ));
                    }
                    elseif ($tool_call->name === 'aiomatic_chart') 
                    {
                        if(isset($fun_args->shortcode))
                        {
                            $shortcode = $fun_args->shortcode;
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => do_shortcode($shortcode),
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'shortcode parameter not found',
                                'assistant_message' => $as_me
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_image') 
                    {
                        if(isset($fun_args->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $fun_args->prompt;
                            if (!isset($aiomatic_Main_Settings['app_id'])) 
                            {
                                $aiomatic_Main_Settings['app_id'] = '';
                            }
                            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                            $appids = array_filter($appids);
                            $token = $appids[array_rand($appids)];
                            if (empty($token))
                            {
                                aiomatic_log_to_file('You need to enter an OpenAI API key for this to work!');
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size']) && trim($aiomatic_Chatbot_Settings['ai_image_size']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_model']) && trim($aiomatic_Chatbot_Settings['ai_image_model']) != '')
                            {
                                $model = trim($aiomatic_Chatbot_Settings['ai_image_model']);
                            }
                            else
                            {
                                $model = 'dalle2';
                            }
                            if(empty($result))
                            {
                                $aierror = '';
                                $airesult = aiomatic_generate_ai_image($token, 1, $prompt, $image_size, 'chatFunctionDalleImage', false, 0, $aierror, $model);
                                if($airesult !== false && is_array($airesult))
                                {
                                    foreach($airesult as $tmpimg)
                                    {
                                        $result = '<img class="image_max_w_ai" src="' . $tmpimg . '">';
                                        break;
                                    }
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to generate Dall-E image in AI chatbot: ' . $aierror);
                                    if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                    {
                                        $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                    }
                                    else
                                    {
                                        $result = 'Image creation failed, please try again later.';
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => $result,
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_me
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_stable_image') 
                    {
                        if(isset($fun_args->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $fun_args->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_stable']) && trim($aiomatic_Chatbot_Settings['ai_image_size_stable']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_stable']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            if (isset($aiomatic_Chatbot_Settings['stable_model']) && trim($aiomatic_Chatbot_Settings['stable_model']) != '')
                            {
                                $model = trim($aiomatic_Chatbot_Settings['stable_model']);
                            }
                            else
                            {
                                $model = AIOMATIC_STABLE_DEFAULT_MODE;
                            }
                            $aierror = '';
                            $airesult = aiomatic_generate_stability_image($prompt, $height, $width, 'chatFunctionStableImage', 0, false, $aierror, false, $model, '', 0);
                            if($airesult !== false && isset($airesult[1]))
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult[1] . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Stable image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_google_image') 
                    {
                        if(isset($fun_args->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $fun_args->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Main_Settings['google_ai_image_model']) && trim($aiomatic_Main_Settings['google_ai_image_model']) != '') 
                            {
                                $model = trim($aiomatic_Main_Settings['google_ai_image_model']);
                            }
                            else
                            {
                                $model = AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL;
                            }
                            $aierror = '';
                            $airesult = aiomatic_generate_image_google($prompt, $model, 'chatFunctionGoogleImage', false, $aierror, false);
                            if($airesult !== false && isset($airesult[1]))
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult[1] . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Google image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_midjourney_image') 
                    {
                        if(isset($fun_args->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $fun_args->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_midjourney']) && trim($aiomatic_Chatbot_Settings['ai_image_size_midjourney']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_midjourney']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            $aierror = '';
                            $airesult = aiomatic_generate_ai_image_midjourney($prompt, $width, $height, 'chatFunctionMidjourneyImage', false, $aierror);
                            if($airesult !== false)
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Midjourney image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_replicate_image') 
                    {
                        if(isset($fun_args->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $fun_args->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_replicate']) && trim($aiomatic_Chatbot_Settings['ai_image_size_replicate']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_replicate']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            $aierror = '';
                            $airesult = aiomatic_generate_replicate_image($prompt, $width, $height, 'chatFunctionReplicateImage', false, $aierror);
                            if($airesult !== false)
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Replicate image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_ideogram_image') 
                    {
                        if(isset($fun_args->prompt))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $prompt = $fun_args->prompt;
                            if (empty($prompt))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_image_size_ideogram']) && trim($aiomatic_Chatbot_Settings['ai_image_size_ideogram']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_image_size_ideogram']);
                            }
                            else
                            {
                                $image_size = '512x512';
                            }
                            if($image_size == '512x512')
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
                            $aierror = '';
                            $airesult = aiomatic_generate_ideogram_image($prompt, $width, $height, 'chatFunctionIdeogramImage', false, $aierror);
                            if($airesult !== false)
                            {
                                $result = '<img class="image_max_w_ai" src="' . $airesult . '">';
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Ideogram image in AI chatbot: ' . $aierror);
                                if (isset($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) && trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']) != '')
                                {
                                    $result = trim($aiomatic_Chatbot_Settings['god_mode_dalle_failed']);
                                }
                                else
                                {
                                    $result = 'Image creation failed, please try again later.';
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            aiomatic_add_tool_direct_message($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $result,
                                'assistant_message' => $as_mes
                            ));
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_stable_video') 
                    {
                        if(isset($fun_args->image_url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $image_url = $fun_args->image_url;
                            if (empty($image_url))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url sent',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['ai_video_size_stable']) && trim($aiomatic_Chatbot_Settings['ai_video_size_stable']) != '')
                            {
                                $image_size = trim($aiomatic_Chatbot_Settings['ai_video_size_stable']);
                            }
                            else
                            {
                                $image_size = '768x768';
                            }
                            $aierror = '';
                            $response_text = aiomatic_generate_stability_video($image_url, $image_size, 'chatbotStableVideo', 0, false, $aierror, false);
                            if($response_text !== false && isset($response_text[1]))
                            {
                                $result = '<div style="padding-bottom:56.25%; position:relative; display:block; width: 100%"><iframe src="' . $response_text[1] . '" width="100%" height="100%" style="position:absolute; top:0; left: 0" allowfullscreen webkitallowfullscreen frameborder="0"></iframe></div>';
                                aiomatic_add_tool_direct_message($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                aiomatic_log_to_file('Failed to generate Dall-E video in AI chatbot: ' . $aierror);
                                $result = 'Video creation failed, please try again later.';
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_amazon_listing') 
                    {
                        if(isset($fun_args->query))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $asin = $fun_args->query;
                            if (empty($asin)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty search query provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['affiliate_id']) && trim($aiomatic_Chatbot_Settings['affiliate_id']) != '')
                            {
                                $aff_id = trim($aiomatic_Chatbot_Settings['affiliate_id']);
                            }
                            else
                            {
                                $aff_id = '';
                            }
                            if (isset($aiomatic_Chatbot_Settings['target_country']) && trim($aiomatic_Chatbot_Settings['target_country']) != '')
                            {
                                $target_country = trim($aiomatic_Chatbot_Settings['target_country']);
                            }
                            else
                            {
                                $target_country = 'com';
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_products']) && trim($aiomatic_Chatbot_Settings['max_products']) != '')
                            {
                                $max_product_count = trim($aiomatic_Chatbot_Settings['max_products']);
                            }
                            else
                            {
                                $max_product_count = '3-4';
                            }
                            if (isset($aiomatic_Chatbot_Settings['sort_results']) && trim($aiomatic_Chatbot_Settings['sort_results']) != '')
                            {
                                $amaz_sort_results = trim($aiomatic_Chatbot_Settings['sort_results']);
                            }
                            else
                            {
                                $amaz_sort_results = 'none';
                            }
                            if (isset($aiomatic_Chatbot_Settings['listing_template']) && trim($aiomatic_Chatbot_Settings['listing_template']) != '')
                            {
                                $listing_template = trim($aiomatic_Chatbot_Settings['listing_template']);
                            }
                            else
                            {
                                $listing_template = '%%product_counter%%. %%product_title%% - Desciption: %%product_description%% - Link: %%product_url%% - Price: %%product_price%%';
                            }
                            
                            if(strstr($max_product_count, '-') !== false)
                            {
                                $pr_arr = explode('-', $max_product_count);
                                $minx = trim($pr_arr[0]);
                                $maxx = trim($pr_arr[1]);
                                if(is_numeric($minx) && is_numeric($maxx))
                                {
                                    $max_product_count = rand(intval($minx), intval($maxx));
                                }
                                else
                                {
                                    if(is_numeric($minx))
                                    {
                                        $max_product_count = intval($minx);
                                    }
                                    elseif(is_numeric($maxx))
                                    {
                                        $max_product_count = intval($maxx);
                                    }
                                    else
                                    {
                                        $max_product_count = 100;
                                    }
                                }
                            }
                            if(!empty($max_product_count) && is_numeric($max_product_count))
                            {
                                $max_prod = intval($max_product_count);
                            }
                            else
                            {
                                $max_prod = 100;
                            }
                            $amazresult = aiomatic_amazon_get_post($asin, trim($aff_id), $target_country, '', '', $amaz_sort_results, $max_prod, '1', array());
                            if(is_array($amazresult) && ((isset($amazresult['status']) && $amazresult['status'] == 'nothing') || count($amazresult) == 0))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No Amazon products found for query: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(!is_array($amazresult))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'An error occurred while search Amazon for: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            
                            $final_result = '';
                            $counter = 1;
                            foreach($amazresult as $myprod)
                            {
                                $copy_template = $listing_template;
                                $copy_template = str_replace('%%product_counter%%', $counter, $copy_template);
                                $copy_template = str_replace('%%product_title%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_title)), $copy_template);
                                $copy_template = str_replace('%%product_description%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_desc)), $copy_template);
                                $copy_template = str_replace('%%product_url%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_url)), $copy_template);
                                $copy_template = str_replace('%%product_price%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_price)), $copy_template);
                                $copy_template = str_replace('%%product_list_price%%', trim(preg_replace('/\s+/', ' ', $myprod->product_list_price)), $copy_template);
                                $copy_template = str_replace('%%product_image%%', trim(preg_replace('/\s+/', ' ', $myprod->offer_img)), $copy_template);
                                $copy_template = str_replace('%%product_cart_url%%', trim(preg_replace('/\s+/', ' ', $myprod->cart_url)), $copy_template);
                                $copy_template = str_replace('%%product_images_urls%%', trim(preg_replace('/\s+/', ' ', $myprod->product_imgs)), $copy_template);
                                $copy_template = str_replace('%%product_images%%', trim(preg_replace('/\s+/', ' ', $myprod->product_imgs_html)), $copy_template);
                                $copy_template = str_replace('%%product_reviews%%', trim(preg_replace('/\s+/', ' ', implode(PHP_EOL, $myprod->item_reviews))), $copy_template);
                                //new
                                $copy_template = str_replace('%%product_score%%', trim(preg_replace('/\s+/', ' ', $myprod->item_score)), $copy_template);
                                $copy_template = str_replace('%%product_language%%', trim(preg_replace('/\s+/', ' ', $myprod->language)), $copy_template);
                                $copy_template = str_replace('%%product_edition%%', trim(preg_replace('/\s+/', ' ', $myprod->edition)), $copy_template);
                                $copy_template = str_replace('%%product_pages_count%%', trim(preg_replace('/\s+/', ' ', $myprod->pages_count)), $copy_template);
                                $copy_template = str_replace('%%product_publication_date%%', trim(preg_replace('/\s+/', ' ', $myprod->publication_date)), $copy_template);
                                $copy_template = str_replace('%%product_contributors%%', trim(preg_replace('/\s+/', ' ', $myprod->contributors)), $copy_template);
                                $copy_template = str_replace('%%product_manufacturer%%', trim(preg_replace('/\s+/', ' ', $myprod->manufacturer)), $copy_template);
                                $copy_template = str_replace('%%product_binding%%', trim(preg_replace('/\s+/', ' ', $myprod->binding)), $copy_template);
                                $copy_template = str_replace('%%product_product_group%%', trim(preg_replace('/\s+/', ' ', $myprod->product_group)), $copy_template);
                                $copy_template = str_replace('%%product_rating%%', trim(preg_replace('/\s+/', ' ', $myprod->rating)), $copy_template);
                                $copy_template = str_replace('%%product_ean%%', trim(preg_replace('/\s+/', ' ', $myprod->eans)), $copy_template);
                                $copy_template = str_replace('%%product_part_no%%', trim(preg_replace('/\s+/', ' ', $myprod->part_no)), $copy_template);
                                $copy_template = str_replace('%%product_model%%', trim(preg_replace('/\s+/', ' ', $myprod->model)), $copy_template);
                                $copy_template = str_replace('%%product_warranty%%', trim(preg_replace('/\s+/', ' ', $myprod->warranty)), $copy_template);
                                $copy_template = str_replace('%%product_color%%', trim(preg_replace('/\s+/', ' ', $myprod->color)), $copy_template);
                                $copy_template = str_replace('%%product_is_adult%%', trim(preg_replace('/\s+/', ' ', $myprod->is_adult)), $copy_template);
                                $copy_template = str_replace('%%product_dimensions%%', trim(preg_replace('/\s+/', ' ', $myprod->dimensions)), $copy_template);
                                $copy_template = str_replace('%%product_size%%', trim(preg_replace('/\s+/', ' ', $myprod->size)), $copy_template);
                                $copy_template = str_replace('%%product_unit_count%%', trim(preg_replace('/\s+/', ' ', $myprod->unit_count)), $copy_template);
                                
                                $counter++;
                                $final_result .= $copy_template . '\r\n'; 
                            }
                            $final_result = trim($final_result);
                            $final_result = substr($final_result, 0, 400000);
                            if(!empty($final_result))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_result,
                                    'assistant_message' => $as_mes
                                ));
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Amazon did not return info for this query: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_amazon_product_details') 
                    {
                        if(isset($fun_args->query))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $asin = $fun_args->query;
                            if (empty($asin)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty search query provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['affiliate_id']) && trim($aiomatic_Chatbot_Settings['affiliate_id']) != '')
                            {
                                $aff_id = trim($aiomatic_Chatbot_Settings['affiliate_id']);
                            }
                            else
                            {
                                $aff_id = '';
                            }
                            if (isset($aiomatic_Chatbot_Settings['target_country']) && trim($aiomatic_Chatbot_Settings['target_country']) != '')
                            {
                                $target_country = trim($aiomatic_Chatbot_Settings['target_country']);
                            }
                            else
                            {
                                $target_country = 'com';
                            }
                            $max_prod = 1;
                            $amazresult = aiomatic_amazon_get_post($asin, trim($aff_id), $target_country, '', '', '', $max_prod, '1', array());
                            if(is_array($amazresult) && ((isset($amazresult['status']) && $amazresult['status'] == 'nothing') || count($amazresult) == 0))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No Amazon products found for query: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(!is_array($amazresult))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'An error occurred while search Amazon for: ' . $asin,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $final_result = 'Product title: ' . $amazresult[0]->offer_title . '\n';
                                $final_result .= 'Description: ' . $amazresult[0]->offer_desc . '\n';
                                $final_result .= 'URL: ' . $amazresult[0]->offer_url . '\n';
                                $final_result .= 'Price: ' . $amazresult[0]->offer_price . '\n';
                                $final_result .= 'Listing Price: ' . $amazresult[0]->product_list_price . '\n';
                                $final_result .= 'Image: ' . $amazresult[0]->offer_img . '\n';
                                $final_result .= 'Add to cart URL: ' . $amazresult[0]->cart_url . '\n';
                                $final_result .= 'Other images: ' . $amazresult[0]->product_imgs . '\n';
                                //new
                                if(!empty($the_current_section->item_score))
                                {
                                    $final_result .= 'Score: ' . $amazresult[0]->item_score . '\n';
                                }
                                if(!empty($the_current_section->language))
                                {
                                    $final_result .= 'Language: ' . $amazresult[0]->language . '\n';
                                }
                                if(!empty($the_current_section->edition))
                                {
                                    $final_result .= 'Edition: ' . $amazresult[0]->edition . '\n';
                                }
                                if(!empty($the_current_section->pages_count))
                                {
                                    $final_result .= 'Pages Count: ' . $amazresult[0]->pages_count . '\n';
                                }
                                if(!empty($the_current_section->publication_date))
                                {
                                    $final_result .= 'Date: ' . $amazresult[0]->publication_date . '\n';
                                }
                                if(!empty($the_current_section->contributors))
                                {
                                    $final_result .= 'Contributors: ' . $amazresult[0]->contributors . '\n';
                                }
                                if(!empty($the_current_section->manufacturer))
                                {
                                    $final_result .= 'Manufacturer: ' . $amazresult[0]->manufacturer . '\n';
                                }
                                if(!empty($the_current_section->binding))
                                {
                                    $final_result .= 'Binding: ' . $amazresult[0]->binding . '\n';
                                }
                                if(!empty($the_current_section->product_group))
                                {
                                    $final_result .= 'Product Group: ' . $amazresult[0]->product_group . '\n';
                                }
                                if(!empty($the_current_section->rating))
                                {
                                    $final_result .= 'Rating: ' . $amazresult[0]->rating . '\n';
                                }
                                if(!empty($the_current_section->eans))
                                {
                                    $final_result .= 'EAN: ' . $amazresult[0]->eans . '\n';
                                }
                                if(!empty($the_current_section->part_no))
                                {
                                    $final_result .= 'Part No: ' . $amazresult[0]->part_no . '\n';
                                }
                                if(!empty($the_current_section->model))
                                {
                                    $final_result .= 'Model: ' . $amazresult[0]->model . '\n';
                                }
                                if(!empty($the_current_section->warranty))
                                {
                                    $final_result .= 'Warranty: ' . $amazresult[0]->warranty . '\n';
                                }
                                if(!empty($the_current_section->color))
                                {
                                    $final_result .= 'Color: ' . $amazresult[0]->color . '\n';
                                }
                                if(!empty($the_current_section->is_adult))
                                {
                                    $final_result .= 'Is Adult: ' . $amazresult[0]->is_adult . '\n';
                                }
                                if(!empty($the_current_section->dimensions))
                                {
                                    $final_result .= 'Dimensions: ' . $amazresult[0]->dimensions . '\n';
                                }
                                if(!empty($the_current_section->size))
                                {
                                    $final_result .= 'Size: ' . $amazresult[0]->size . '\n';
                                }
                                if(!empty($the_current_section->unit_count))
                                {
                                    $final_result .= 'Unit Count: ' . $amazresult[0]->unit_count . '\n';
                                }
                                $final_result .= 'Reviews: ' . implode(PHP_EOL, $amazresult[0]->item_reviews) . '\n';
                                $final_result = substr($final_result, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_result,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_toubiz') 
                    {
                        if (!function_exists('is_plugin_active')) 
                        {
                            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                        }
                        $plugin_active = is_plugin_active('aiomatic-extension-toubiz-api/aiomatic-extension-toubiz-api.php');
                        if (!$plugin_active) 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Required plugin (Aiomatic Extension Toubiz API) not activated.',
                                'assistant_message' => $as_mes
                            ));
                            continue;
                        }
                        if (!function_exists('aiomatic_get_toubiz_api_results')) 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Required plugin (Aiomatic Extension Toubiz API) not correctly installed.',
                                'assistant_message' => $as_mes
                            ));
                            continue;
                        }
                        $search = '';
                        if(isset($fun_args->search))
                        {
                            $search = $fun_args->search;
                        }
                        $date_after = '';
                        if(isset($fun_args->date_after))
                        {
                            $date_after = $fun_args->date_after;
                        }
                        $date_before = '';
                        if(isset($fun_args->date_before))
                        {
                            $date_before = $fun_args->date_before;
                        }
                        $type = '';
                        if(isset($fun_args->type))
                        {
                            $type = $fun_args->type;
                        }
                        $event_type = '';
                        if(isset($fun_args->event_type))
                        {
                            $event_type = $fun_args->event_type;
                        }
                        $rectangle_area = '';
                        if(isset($fun_args->rectangle_area))
                        {
                            $rectangle_area = $fun_args->rectangle_area;
                        }
                        $distance_from = '';
                        if(isset($fun_args->distance_from))
                        {
                            $distance_from = $fun_args->distance_from;
                        }
                        $languages = '';
                        if(isset($fun_args->languages))
                        {
                            $languages = $fun_args->languages;
                        }
                        $toubiz_data = aiomatic_get_toubiz_api_results($search, $date_after, $date_before, $type, $event_type, $rectangle_area, $distance_from, $languages);
                        if ($toubiz_data === false) 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Failed to access Toubiz API',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to access Toubiz API: ' . print_r($tool_call, true));
                        } 
                        else 
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => $toubiz_data,
                                'assistant_message' => $as_mes
                            ));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_website_scraper') 
                    {
                        if(isset($fun_args->url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $scurl = $fun_args->url;
                            if (empty($scurl)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty scrape url provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['scrape_method']) && trim($aiomatic_Chatbot_Settings['scrape_method']) != '')
                            {
                                $scrape_method = trim($aiomatic_Chatbot_Settings['scrape_method']);
                            }
                            else
                            {
                                $scrape_method = '0';
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_chars']) && trim($aiomatic_Chatbot_Settings['max_chars']) != '')
                            {
                                $max_chars = trim($aiomatic_Chatbot_Settings['max_chars']);
                            }
                            else
                            {
                                $max_chars = '';
                            }
                            $scrape_selector = 'auto';
                            $scrape_string = '';
                            if (isset($aiomatic_Chatbot_Settings['strip_tags']) && trim($aiomatic_Chatbot_Settings['strip_tags']) != '')
                            {
                                $strip_tags = trim($aiomatic_Chatbot_Settings['strip_tags']);
                            }
                            else
                            {
                                $strip_tags = '0';
                            }
                            $scraped_data = aiomatic_scrape_page($scurl, $scrape_method, $scrape_selector, $scrape_string);
                            if($scraped_data === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to scrape website URL: ' . $scurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                if($strip_tags == '1')
                                {
                                    $scraped_data = wp_strip_all_tags($scraped_data);
                                }
                                else
                                {
                                    $scraped_data = aiomatic_fix_relative_links($scraped_data, $scurl);
                                }
                                if(!empty($max_chars) && is_numeric($max_chars))
                                {
                                    $scraped_data = (strlen($scraped_data) > intval($max_chars)) ? substr($scraped_data, 0, intval($max_chars)) : $scraped_data;
                                }
                                $scraped_data = substr($scraped_data, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $scraped_data,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_rss_parser') 
                    {
                        if(isset($fun_args->url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $scurl = $fun_args->url;
                            if (empty($scurl)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty RSS feed URL provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_rss_items']) && trim($aiomatic_Chatbot_Settings['max_rss_items']) != '')
                            {
                                $max_rss_items = intval(trim($aiomatic_Chatbot_Settings['max_rss_items']));
                            }
                            else
                            {
                                $max_rss_items = PHP_INT_MAX;
                            }
                            if (isset($aiomatic_Chatbot_Settings['rss_template']) && trim($aiomatic_Chatbot_Settings['rss_template']) != '')
                            {
                                $rss_template = trim($aiomatic_Chatbot_Settings['rss_template']);
                            }
                            else
                            {
                                $rss_template = '[%%item_counter%%]: %%item_title%% - %%item_description%%';
                            }
                            try
                            {
                                if(!class_exists('SimplePie_Autoloader', false))
                                {
                                    require_once(dirname(__FILE__) . "/res/simplepie/autoloader.php");
                                }
                            }
                            catch(Exception $e) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to load RSS parser library',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $feed = new SimplePie();
                            $feed->set_timeout(120);
                            $feed->set_feed_url($scurl);
                            $feed->enable_cache(false);
                            $feed->strip_htmltags(false);
                            $feed->init();
                            $feed->handle_content_type();
                            if ($feed->error()) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Error in parsing RSS feed: ' . $feed->error(),
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $final_result = '';
                            $items = $feed->get_items();
                            foreach($items as $itemx)
                            {
                                $post_link = trim($itemx->get_permalink());
                                $post_fcats = $itemx->get_categories();
                                if ($fauthor = $itemx->get_author()) 
                                {
                                    $user_name = $fauthor->get_name();
                                }
                                else
                                {
                                    $user_name = '';
                                }
                                $feed_cats = array();
                                if(is_array($post_fcats))
                                {
                                    foreach($post_fcats as $cata)
                                    {
                                        $feed_cats[] = $cata->__toString();
                                    }
                                    $post_cats = implode(',', $feed_cats);
                                }
                                else
                                {
                                    $post_cats = '';
                                }
                                $post_excerpt = $itemx->get_description();
                                $final_content = $itemx->get_content();
                                $rss_feeds[$itemx->get_title()] = array('url' => $post_link, 'author' => $user_name,  'cats' => $post_cats, 'excerpt' => $post_excerpt, 'content' => $final_content );
                            }
                            $template_copy = '';
                            $processed = 0;
                            foreach($rss_feeds as $rtitle => $this_rss)
                            {
                                if(!empty($max_rss_items) && $processed >= $max_rss_items)
                                {
                                    break;
                                }
                                $template_copy = $rss_template;
                                $template_copy = str_replace('%%item_counter%%', $processed + 1, $template_copy);
                                $template_copy = str_replace('%%item_title%%', $rtitle, $template_copy);
                                $template_copy = str_replace('%%item_content%%', $this_rss['content'], $template_copy);
                                $template_copy = str_replace('%%item_description%%', $this_rss['excerpt'], $template_copy);
                                $template_copy = str_replace('%%item_url%%', $this_rss['url'], $template_copy);
                                $template_copy = str_replace('%%item_author%%', $this_rss['author'], $template_copy);
                                $template_copy = str_replace('%%item_categories%%', $this_rss['cats'], $template_copy);
                                if(!empty($template_copy))
                                {
                                    $final_result .= $template_copy . PHP_EOL;
                                }
                                $processed++;
                            }
                            if(empty($final_result))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse RSS URL (no data returned): ' . $scurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $final_result = substr($final_result, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_result,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_google_parser') 
                    {
                        if(isset($fun_args->keywords))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $keywords = $fun_args->keywords;
                            if (empty($keywords)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty keywords parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Chatbot_Settings['max_google_items']) && trim($aiomatic_Chatbot_Settings['max_google_items']) != '')
                            {
                                $max_google_items = intval(trim($aiomatic_Chatbot_Settings['max_google_items']));
                            }
                            else
                            {
                                $max_google_items = PHP_INT_MAX;
                            }
                            if (isset($aiomatic_Chatbot_Settings['google_template']) && trim($aiomatic_Chatbot_Settings['google_template']) != '')
                            {
                                $google_template = trim($aiomatic_Chatbot_Settings['google_template']);
                            }
                            else
                            {
                                $google_template = '[%%item_counter%%]: %%item_title%% - %%item_snippet%%';
                            }
                            $locale = '';
                            if (isset($aiomatic_Main_Settings['internet_gl']) && $aiomatic_Main_Settings['internet_gl'] != '')
                            {
                                $locale = $aiomatic_Main_Settings['internet_gl'];
                            }
                            $internet_rez = aiomatic_internet_result($keywords, true, $locale);
                            $processed = 0;
                            $final_res = '';
                            foreach($internet_rez as $emb)
                            {
                                if(!empty($max_google_items) && $processed >= $max_google_items)
                                {
                                    break;
                                }
                                $template_copy = $google_template;
                                $template_copy = str_replace('%%item_counter%%', $processed + 1, $template_copy);
                                $template_copy = str_replace('%%item_title%%', $emb['title'], $template_copy);
                                $template_copy = str_replace('%%item_snippet%%', $emb['snippet'], $template_copy);
                                $template_copy = str_replace('%%item_url%%', $emb['link'], $template_copy);
                                $final_res .= $template_copy . PHP_EOL;
                                $processed++;
                            }
                            if(empty($final_res))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse Google SERP for keyword (no data returned): ' . $keywords,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $final_res = substr($final_res, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $final_res,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'keywords parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_royalty_free_image') 
                    {
                        if(isset($fun_args->keyword))
                        {
                            $raw_img_list = array();
                            $full_result_list = array();
                            $result = '';
                            $keyword = $fun_args->keyword;
                            if (empty($keyword)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty keyword parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $temp_img_attr = '';
                            $attached_id = '';
                            $temp_get_img = aiomatic_get_free_image($aiomatic_Main_Settings, $keyword, $temp_img_attr, $attached_id, 10, false, $raw_img_list, array(), $full_result_list);
                            if(empty($temp_get_img))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to get royalty free image for keyword: ' . $keyword,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                aiomatic_add_tool_direct_message($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => '<img class="image_max_w_ai" src="' . $temp_get_img . '">',
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'keyword parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_lead_capture') 
                    {
                        if(isset($fun_args->email))
                        {
                            $result = '';
                            $name = '';
                            $phone_number = '';
                            $job_title = '';
                            $company_name = '';
                            $location = '';
                            $birth_date = '';
                            $how_you_found_us = '';
                            $website_url = '';
                            $preferred_contact_method = '';
                            $email = $fun_args->email;
                            if (empty($email)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty email parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(isset($fun_args->name))
                            {
                                $name = $fun_args->name;
                            }
                            if(isset($fun_args->phone_number))
                            {
                                $phone_number = $fun_args->phone_number;
                            }
                            if(isset($fun_args->job_title))
                            {
                                $job_title = $fun_args->job_title;
                            }
                            if(isset($fun_args->company_name))
                            {
                                $company_name = $fun_args->company_name;
                            }
                            if(isset($fun_args->location))
                            {
                                $location = $fun_args->location;
                            }
                            if(isset($fun_args->birth_date))
                            {
                                $birth_date = $fun_args->birth_date;
                            }
                            if(isset($fun_args->how_you_found_us))
                            {
                                $how_you_found_us = $fun_args->how_you_found_us;
                            }
                            if(isset($fun_args->website_url))
                            {
                                $website_url = $fun_args->website_url;
                            }
                            if(isset($fun_args->preferred_contact_method))
                            {
                                $preferred_contact_method = $fun_args->preferred_contact_method;
                            }
                            $lead_result = aiomatic_save_lead_data(
                                $email,
                                $name,
                                $phone_number,
                                $job_title,
                                $company_name,
                                $location,
                                $birth_date,
                                $how_you_found_us,
                                $website_url,
                                $preferred_contact_method
                            );
                            if($lead_result === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'Error! Failed to save the lead data.',
                                'assistant_message' => $as_mes
                            ));
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Lead data saved successfully.',
                                    'assistant_message' => $as_mes
                                ));
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'email parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_database') 
                    {
                        if(isset($fun_args->query))
                        {
                            $query = $fun_args->query;
                            if (empty($query)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty query parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            if (isset($aiomatic_Chatbot_Settings['database_command_types']) && is_array($aiomatic_Chatbot_Settings['database_command_types']) && !empty($aiomatic_Chatbot_Settings['database_command_types']))
                            {
                                $allowed_commands = $aiomatic_Chatbot_Settings['database_command_types'];
                            }
                            else
                            {
                                $allowed_commands = array('SELECT');
                            }
                            preg_match('/^\s*(\w+)/i', $query, $matches);
                            $query_type = strtoupper($matches[1] ?? '');
                            if (!in_array($query_type, $allowed_commands, true)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => "Query type '$query_type' is not allowed. Allowed types: " . implode(', ', $allowed_commands),
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            global $wpdb;
                            try 
                            {
                                $lead_result = null;
                                if ($query_type === 'SELECT') 
                                {
                                    $lead_result = $wpdb->get_results($query, ARRAY_A);
                                } 
                                elseif (in_array($query_type, array('INSERT', 'UPDATE', 'DELETE'), true)) 
                                {
                                    $lead_result = $wpdb->query($query); 
                                }
                                if ($lead_result === null || $lead_result === false) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Error! Invalid query or no results found.',
                                        'assistant_message' => $as_mes
                                    ));
                                } 
                                else 
                                {
                                    if ($query_type === 'SELECT' && count($lead_result) > 500) {
                                        $lead_result = array_slice($lead_result, 0, 500);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => json_encode($lead_result), 
                                        'assistant_message' => $as_mes
                                    ));
                                }
                            } 
                            catch (Exception $e) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Error executing the query: ' . $e->getMessage(),
                                    'assistant_message' => $as_mes
                                ));
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_omniblock') 
                    {
                        if(isset($fun_args->omniblock_id))
                        {
                            if(isset($fun_args->input_parameters))
                            {
                                $input_text = $fun_args->input_parameters;
                            }
                            else
                            {
                                $input_text = '';
                            }
                            $omniblock_id = $fun_args->omniblock_id;
                            if (empty($omniblock_id)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty omniblock_id parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            if (isset($aiomatic_Chatbot_Settings['god_omniblock_rules']) && !empty(trim($aiomatic_Chatbot_Settings['god_omniblock_rules'])))
                            {
                                $omniblock_descriptions = array();
                                $omni_arr = preg_split('/\r\n|\r|\n/', trim($aiomatic_Chatbot_Settings['god_omniblock_rules']));
                                $omni_arr = array_map('trim', $omni_arr);
                                $omni_arr = array_filter($omni_arr);
                                foreach ($omni_arr as $line) {
                                    if (strpos($line, '=>') !== false) {
                                        [$id, $desc] = array_map('trim', explode('=>', $line, 2));
                                        if (!empty($id) && !empty($desc)) {
                                            $omniblock_descriptions[$id] = "`$id`: $desc";
                                        }
                                    }
                                }
                                if(!array_key_exists($omniblock_id, $omniblock_descriptions))
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'This OmniBlock ID does not exist in the configured rules',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No OmniBlock rules configured in settings',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $input_text = json_decode($input_text, true);
                            if($input_text === null || $input_text === false || $input_text === true)
                            {
                                $input_text = [];
                            }
                            require_once (dirname(__FILE__) . "/aiomatic-automation.php"); 
                            $cont = 0;
                            $id_parts = explode('_', $omniblock_id);
                            if(!isset($id_parts[1]))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Incorrect OmniBlock ID submitted in request',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if(count($id_parts) > 2)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Incorrect format for OmniBlock IDs',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $param = $id_parts[0];
                            $omniTriggerID = $id_parts[1];
                            $rules = get_option('aiomatic_omni_list', array());
                            $found = false;
                            if (!empty($rules)) 
                            {
                                $default_block_types = aiomatic_omniblocks_default_block_types(); 
                                foreach ($rules as $request => $bundle[]) 
                                {
                                    if ($cont == $param) 
                                    {
                                        $bundle_values    = array_values($bundle);
                                        $myValues         = $bundle_values[$cont];
                                        $array_my_values  = array_values($myValues);for($iji=0;$iji<count($array_my_values);++$iji){if(is_string($array_my_values[$iji])){$array_my_values[$iji]=stripslashes($array_my_values[$iji]);}}
                                        $schedule         = isset($array_my_values[0]) ? $array_my_values[0] : '';
                                        $active           = isset($array_my_values[1]) ? $array_my_values[1] : '';
                                        $last_run         = isset($array_my_values[2]) ? $array_my_values[2] : '';
                                        $max              = isset($array_my_values[3]) ? $array_my_values[3] : '';
                                        $main_keywords    = isset($array_my_values[4]) ? $array_my_values[4] : '';
                                        $title_once       = isset($array_my_values[5]) ? $array_my_values[5] : '';
                                        $rule_description = isset($array_my_values[6]) ? $array_my_values[6] : '';
                                        $rule_unique_id   = isset($array_my_values[7]) ? $array_my_values[7] : '';
                                        $sortable_cards   = isset($array_my_values[8]) ? $array_my_values[8] : '';
                                        $more_keywords    = isset($array_my_values[9]) ? $array_my_values[9] : '';
                                        $days_no_run      = isset($array_my_values[10]) ? $array_my_values[10] : '';
                                        $block_data       = json_decode($sortable_cards, true);
                                        if(!empty($block_data))
                                        {
                                            foreach($block_data as $index => $current_block)
                                            {
                                                $card_type_found = array();
                                                foreach($default_block_types as $def_card)
                                                {
                                                    if($current_block['type'] == $def_card['id'])
                                                    {
                                                        $card_type_found = $def_card;
                                                        break;
                                                    }
                                                }
                                                if(!empty($card_type_found) && $omniTriggerID == $current_block['identifier'] && $current_block['type'] === 'ai_chatbot_omniblock')
                                                {
                                                    $return_last_result = true;
                                                    $return_me = aiomatic_run_rule($cont, 5, 1, 0, null, '', $omniTriggerID, $return_last_result, $input_text);
                                                    if($return_me == 'fail')
                                                    {
                                                        aiomatic_log_to_file('OmniBlock AI Chatbot Trigger rule running failed, rule ID: ' . $param . ' OmniBlock ID: ' . $omniTriggerID);
                                                    }
                                                    aiomatic_add_tool_results($reply, array(
                                                        "tool_call_id" => $tool_call->id,
                                                        "role" => "tool",
                                                        "content" => wp_kses_post($return_me), 
                                                        'assistant_message' => $as_mes
                                                    ));
                                                    $found = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                    $cont = $cont + 1;
                                }
                                if($found == false)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Specified \'AI Chatbot Trigger\' OmniBlock ID not found(4): ' . $omniblock_id,
                                        'assistant_message' => $as_mes
                                    ));
                                }
                            } 
                            else 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'No rules found for aiomatic_omni_list!',
                                    'assistant_message' => $as_mes
                                ));
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'query parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                            continue;
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_generate_reasoning') 
                    {
                        if(isset($fun_args->prompt))
                        {
                            $prompt = $fun_args->prompt;
                            if (empty($prompt)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty prompt parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $model = '';
                            if(isset($fun_args->model))
                            {
                                $model = $fun_args->model;
                            }
                            if (empty($model)) 
                            {
                                $model = AIOMATIC_DEFAULT_MODEL;
                            }
                            if (!isset($aiomatic_Main_Settings['app_id'])) 
                            {
                                $aiomatic_Main_Settings['app_id'] = '';
                            }
                            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                            $appids = array_filter($appids);
                            $token = $appids[array_rand($appids)];
                            if (empty($token))
                            {
                                aiomatic_log_to_file('You need to enter an OpenAI API key for this to work!');
                                $result = '[ERROR] API key not configured in plugin settings.';
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $result,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $query_token_count = count(aiomatic_encode($prompt));
                            $max_tokens = aiomatic_get_max_tokens($model);
                            $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                            {
                                $string_len = strlen($prompt);
                                $string_len = $string_len / 2;
                                $string_len = intval(0 - $string_len);
                                $prompt = aiomatic_substr($prompt, 0, $string_len);
                                $prompt = trim($prompt);
                                $query_token_count = count(aiomatic_encode($prompt));
                                $available_tokens = $max_tokens - $query_token_count;
                            }
                            $thread_id = '';
                            $aierror = '';
                            $finish_reason = '';
                            remove_filter('aiomatic_ai_reply_raw', 'aiomatic_handle_god_mode_response');
                            $generated_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, 1, 1, 0, 0, false, 'aiThinking', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', '', $thread_id, '', 'disabled', '', false, false, array());
                            add_filter('aiomatic_ai_reply_raw', 'aiomatic_handle_god_mode_response', 10, 2);
                            if($generated_text === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => '[ERROR] Failed to generate content using AI writer: ' . $model . ' - error: ' . $aierror,
                                    'assistant_message' => $as_mes
                                ));
                            }
                            else
                            {
                                $new_post_content = aiomatic_sanitize_ai_result($generated_text);
                                if(empty($new_post_content))
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => '[ERROR] No content returned by the AI writer!',
                                        'assistant_message' => $as_mes
                                    ));
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => $new_post_content,
                                        'assistant_message' => $as_mes
                                    ));
                                }
                            }
                            continue;
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'prompt parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_youtube_captions') 
                    {
                        if(isset($fun_args->url))
                        {
                            $result = '';
                            $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                            $zurl = $fun_args->url;
                            if (empty($zurl)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $default_lang = array();
                            $returned_caption = '';
                            $za_video_page = '';
                            $ch  = curl_init();
                            if ($ch !== FALSE) 
                            {
                                if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                                    $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                                    $randomness = array_rand($prx);
                                    curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                                    if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                                    {
                                        $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                                        if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                                        {
                                            curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                                        }
                                    }
                                }
                                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                                if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
                                {
                                    $ztime = intval($aiomatic_Main_Settings['max_timeout']);
                                }
                                else
                                {
                                    $ztime = 300;
                                }
                                curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
                                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                                curl_setopt($ch, CURLOPT_REFERER, get_site_url());
                                curl_setopt($ch, CURLOPT_URL, $zurl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                $za_video_page = curl_exec($ch);
                                if($za_video_page === false)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to download video URL: ' . $zurl,
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                curl_close($ch);
                            }
                            else
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to init curl in YouTube caption importing: ' . $zurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if($za_video_page !== false && strstr($za_video_page, 'vssId') !== false)
                            {
                                $srt_dl_link = '';
                                preg_match_all('#{"baseUrl":"([^"]+?)","name":(?:.*?),"vssId":"a?\.([^"]+?)","languageCode":"(?:[^"]+?)",(?:"kind":"asr",)?"isTranslatable":(?:[^}]+?)}#i', $za_video_page, $zmatches);
                                if(isset($zmatches[1][0]))
                                {
                                    $eng_f = false;
                                    if(in_array('en', $zmatches[2]))
                                    {
                                        $eng_f = true;
                                    }
                                    for($i = 0; $i < count($zmatches[1]); $i++)
                                    {
                                        if(count($default_lang) > 0)
                                        {
                                            if(in_array($zmatches[2][$i], $default_lang))
                                            {
                                                $srt_dl_link = str_replace('\u0026', '&', $zmatches[1][$i]);
                                                break;
                                            }
                                        }
                                        else
                                        {
                                            if(!$eng_f)
                                            {
                                                $srt_dl_link = str_replace('\u0026', '&', $zmatches[1][$i]);
                                                break;
                                            }
                                            elseif($zmatches[2][$i] == 'en')
                                            {
                                                $srt_dl_link = str_replace('\u0026', '&', $zmatches[1][$i]);
                                                break;
                                            }
                                        }
                                    }
                                    if($srt_dl_link !== '')
                                    {
                                        $ch  = curl_init();
                                        if ($ch !== FALSE) 
                                        {
                                            if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                                                $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                                                $randomness = array_rand($prx);
                                                curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                                                if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                                                {
                                                    $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                                                    if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                                                    {
                                                        curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                                                    }
                                                }
                                            }
                                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                                            if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
                                            {
                                                $ztime = intval($aiomatic_Main_Settings['max_timeout']);
                                            }
                                            else
                                            {
                                                $ztime = 300;
                                            }
                                            curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
                                            curl_setopt($ch, CURLOPT_HTTPGET, 1);
                                            curl_setopt($ch, CURLOPT_REFERER, get_site_url());
                                            curl_setopt($ch, CURLOPT_URL, $srt_dl_link);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            $xza_video_page = curl_exec($ch);
                                            if(!empty($xza_video_page))
                                            {
                                                $returned_caption = $xza_video_page;
                                                $returned_caption = preg_replace('#\s+#', ' ', $returned_caption);
                                            }
                                            curl_close($ch);
                                        }
                                        else
                                        {
                                            aiomatic_log_to_file('Failed to init curl in subtitle listing: ' . $zurl);
                                        }   
                                    }
                                }
                            }
                            if(empty($returned_caption))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse YouTube Video captions from URL (no data returned): ' . $zurl,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                $returned_caption = substr($returned_caption, 0, 400000);
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $returned_caption,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_youtube_search') 
                    {
                        if(isset($fun_args->keyword))
                        {
                            $result = '';
                            $keyword = $fun_args->keyword;
                            if (empty($keyword)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty keyword parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $new_vid = aiomatic_get_video($keyword, '', array());
                            if(empty($new_vid))
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to parse YouTube video search results for keyword (no data returned): ' . $keyword,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else
                            {
                                aiomatic_add_tool_direct_message($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => $new_vid,
                                    'assistant_message' => $as_mes
                                ));
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'keyword parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_send_email') 
                    {
                        if(isset($fun_args->subject) && isset($fun_args->content) && isset($fun_args->recipient_email))
                        {
                            $result = '';
                            $subject = $fun_args->subject;
                            $content = $fun_args->content;
                            $recipient_email = $fun_args->recipient_email;
                            if (empty($subject) || empty($content) || empty($recipient_email)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty subject, content or recipient_email parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                $admin_mail = get_bloginfo('admin_email');
                                $headers[] = 'From: AIomatic Plugin Chatbot <' . $admin_mail . '>';
                                $headers[] = 'Reply-To: ' . $admin_mail;
                                $headers[] = 'X-Mailer: PHP/' . phpversion();
                                $headers[] = 'Content-Type: text/html';
                                $headers[] = 'Charset: ' . get_option('blog_charset', 'UTF-8');
                                $sent = wp_mail($recipient_email, $subject, $content, $headers);
                                if($sent === false)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to send email to address: ' . $recipient_email,
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'OK',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to send mail: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'subject, content or recipient_email parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_webhook') 
                    {
                        if(isset($fun_args->webhook_url) && isset($fun_args->method_selector))
                        {
                            $webhook_url = $fun_args->webhook_url;
                            if (empty($webhook_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty webhook_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $webhook_method = $fun_args->method_selector;
                            if (empty($webhook_method)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty method_selector parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content_type = '';
                            if(isset($fun_args->content_type))
                            {
                                $content_type = $fun_args->content_type;
                            }
                            $post_template = '';
                            if(isset($fun_args->data))
                            {
                                $post_template = $fun_args->data;
                            }
                            $headers_template = '';
                            if(isset($fun_args->headers))
                            {
                                $headers_template = $fun_args->headers;
                            }
                            $urlParsed = parse_url( $webhook_url, PHP_URL_HOST );
                            if ( filter_var( $webhook_url, FILTER_VALIDATE_URL ) === FALSE || empty( $urlParsed ) )
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Invalid webhook_url entered ' . $webhook_url,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            else if ( $content_type == 'JSON' && empty( json_decode( $post_template, TRUE ) ) )
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'The JSON data must be valid ' . $webhook_url,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $headers = [];
                            if(!empty($headers_template))
                            {
                                $headers_template_arr = preg_split('/\r\n|\r|\n/', trim($headers_template));
                                foreach($headers_template_arr as $arr_fr)
                                {
                                    if(!empty(trim($arr_fr)) && strstr($arr_fr, '=>'))
                                    {
                                        $small_arr = explode('=>', $arr_fr);
                                        $headers[] = trim($small_arr[0]) . ':' . trim($small_arr[1]);
                                    }
                                }
                            }
                            $content_params = [];
                            if(!empty($post_template))
                            {
                                $post_template_arr = preg_split('/\r\n|\r|\n/', trim($post_template));
                                foreach($post_template_arr as $arr_fr)
                                {
                                    if(!empty(trim($arr_fr)) && strstr($arr_fr, '=>'))
                                    {
                                        $small_arr = explode('=>', $arr_fr);
                                        $content_params[trim($small_arr[0])] = trim($small_arr[1]);
                                    }
                                }
                            }
                            $ch = curl_init();
                            if ($ch === false) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to init curl in webhook execution',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                                $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                                $randomness = array_rand($prx);
                                curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                                if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                                {
                                    $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                                    if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                                    {
                                        curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                                    }
                                }
                            }
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            if ($webhook_method == 'POST' || $webhook_method == 'PUT' || $webhook_method == 'DELETE') 
                            {
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $webhook_method);
                                if (!empty($content_params) && $content_type == 'form_data') {
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content_params));
                                } elseif (!empty($post_template) && $content_type == 'JSON') {
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_template));
                                    $headers[] = 'Content-Type: application/json';
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                }
                            }
                            else
                            {
                                $query_string = http_build_query($content_params);
                                $webhook_url = $webhook_url . (strpos($webhook_url, '?') === false ? '?' : '&') . $query_string;
                            }
                            curl_setopt($ch, CURLOPT_URL, $webhook_url);

                            $response = curl_exec($ch);
                            if($response === false)
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Failed to send webhook request to ' . $webhook_url,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $err = curl_error($ch);
                            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);
                            if ($err) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Webhook request error to URL ' . $webhook_url . ' - error: ' . $err,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }

                            if ($statusCode >= 200 && $statusCode <= 299) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Webhook URL called successfully!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            } 
                            else 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Webhook unexpected return code to URL ' . $webhook_url . ' - return code: ' . $statusCode,
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'webhook_url or method_selector parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_facebook') 
                    {
                        if(isset($fun_args->content))
                        {
                            $result = '';
                            $zcontent = $fun_args->content;
                            if (empty($zcontent)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $fbomatic_active = false;
                                if (is_plugin_active('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php')) 
                                {
                                    $fbomatic_active = true;
                                }
                                if(!$fbomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (F-omatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $furl = '';
                                if(isset($fun_args->url))
                                {
                                    $furl = $fun_args->url;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['facebook_post_select']) && $aiomatic_Chatbot_Settings['facebook_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['facebook_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No page where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php' => array('F-omatic Automatic Post Generator', 'https://1.envato.market/fbomatic')));
                                $return_me = aiomatic_post_to_facebook($card_type_found, $zcontent, $furl, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Facebook posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Facebook posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Facebook posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Facebook posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Facebook posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_image_facebook') 
                    {
                        if(isset($fun_args->image_url))
                        {
                            $result = '';
                            $image_url = $fun_args->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $fbomatic_active = false;
                                if (is_plugin_active('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php')) 
                                {
                                    $fbomatic_active = true;
                                }
                                if(!$fbomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (F-omatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $caption = '';
                                if(isset($fun_args->caption))
                                {
                                    $caption = $fun_args->caption;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['facebook_post_select']) && $aiomatic_Chatbot_Settings['facebook_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['facebook_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No page where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php' => array('F-omatic Automatic Post Generator', 'https://1.envato.market/fbomatic')));
                                $return_me = aiomatic_post_image_to_facebook($card_type_found, $caption, $image_url, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Facebook image posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook image posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Facebook image posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Facebook image posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Facebook image posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Facebook image posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Facebook image posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_twitter') 
                    {
                        if(isset($fun_args->content))
                        {
                            $result = '';
                            $zcontent = $fun_args->content;
                            if (empty($zcontent)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $twitomatic_active = false;
                                if (is_plugin_active('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php')) 
                                {
                                    $twitomatic_active = true;
                                }
                                if(!$twitomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Twitomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $image_url = '';
                                if(isset($fun_args->image_url))
                                {
                                    $image_url = $fun_args->image_url;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php' => array('Twitomatic Automatic Post Generator', 'https://1.envato.market/twitomatic')));
                                $return_me = aiomatic_post_to_twitter($card_type_found, $zcontent, $image_url);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Twitter posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Twitter posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Twitter posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Twitter posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Twitter posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Twitter posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Twitter posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_threads') 
                    {
                        if(isset($fun_args->content))
                        {
                            $result = '';
                            $zcontent = $fun_args->content;
                            if (empty($zcontent)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $threadsomatic_active = false;
                                if (is_plugin_active('threadsomatic-threads-auto-poster/threadsomatic-threads-auto-poster.php')) 
                                {
                                    $threadsomatic_active = true;
                                }
                                if(!$threadsomatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Threadsomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $image_url = '';
                                if(isset($fun_args->image_url))
                                {
                                    $image_url = $fun_args->image_url;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('threadsomatic-threads-auto-poster/threadsomatic-threads-auto-poster.php' => array('Threadsomatic Threads Auto Poster', 'https://wpbay.com/product/threadsomatic-threads-auto-poster/')));
                                $return_me = aiomatic_post_to_threads($card_type_found, $zcontent, $image_url);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Threads posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Threads posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Threads posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Threads posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Threads posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Threads posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Threads posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_instagram') 
                    {
                        if(isset($fun_args->image_url))
                        {
                            $result = '';
                            $image_url = $fun_args->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Instamatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $content = '';
                                if(isset($fun_args->content))
                                {
                                    $content = $fun_args->content;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php' => array('iMediamatic Automatic Post Generator', 'https://1.envato.market/instamatic')));
                                $return_me = aiomatic_post_image_to_instagram($card_type_found, $content, $image_url);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Instagram posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Instagram posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Instagram posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Instagram posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Instagram posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Instagram posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Instagram posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_pinterest') 
                    {
                        if(isset($fun_args->image_url))
                        {
                            $result = '';
                            $image_url = $fun_args->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Pinterestomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['pinterest_post_select']) && $aiomatic_Chatbot_Settings['pinterest_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['pinterest_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No boards where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $title = '';
                                if(isset($fun_args->title))
                                {
                                    $title = $fun_args->title;
                                }
                                $description = '';
                                if(isset($fun_args->description))
                                {
                                    $description = $fun_args->description;
                                }
                                $pin_url = '';
                                if(isset($fun_args->pin_url))
                                {
                                    $pin_url = $fun_args->pin_url;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php' => array('Pinterestomatic Automatic Post Generator', 'https://1.envato.market/pinterestomatic')));
                                $return_me = aiomatic_post_image_to_pinterest($card_type_found, $description, $title, $pin_url, $image_url, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Pinterest posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Pinterest posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Pinterest posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Pinterest posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Pinterest posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Pinterest posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Pinterest posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_google_my_business') 
                    {
                        if(isset($fun_args->content) && isset($fun_args->image_url))
                        {
                            $result = '';
                            $image_url = $fun_args->image_url;
                            if (empty($image_url)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty image_url parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content = $fun_args->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('businessomatic-google-my-business-post-generator/businessomatic-google-my-business-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Businessomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $page_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['business_post_select']) && $aiomatic_Chatbot_Settings['business_post_select'] != '') {
                                    $page_to_post = $aiomatic_Chatbot_Settings['business_post_select'];
                                }
                                if (empty($page_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No pages where to publish the post was selected',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('businessomatic-google-my-business-post-generator/businessomatic-google-my-business-post-generator.php' => array('Businessomatic Automatic Post Generator', 'https://1.envato.market/businessomatic')));
                                $return_me = aiomatic_post_to_gmb($card_type_found, $content, $image_url, $page_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('GMB posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'GMB posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse GMB posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse GMB posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'GMB posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('GMB posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'GMB posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'image_url or content parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_youtube_community') 
                    {
                        if(isset($fun_args->content) && isset($fun_args->post_type))
                        {
                            $result = '';
                            $post_type = $fun_args->post_type;
                            if (empty($post_type)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty post_type parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $post_type = trim($post_type);
                            if($post_type != 'image' && $post_type != 'text')
                            {
                                $post_type = 'text';
                            }
                            $content = $fun_args->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $image_url = '';
                            if(isset($fun_args->image_url))
                            {
                                $image_url = $fun_args->image_url;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('youtubomatic-youtube-post-generator/youtubomatic-youtube-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Youtubomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                if($image_url != '')
                                {
                                    $media = array($image_url);
                                }
                                else
                                {
                                    $media = array();
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('youtubomatic-youtube-post-generator/youtubomatic-youtube-post-generator.php' => array('Youtubomatic Automatic Post Generator', 'https://1.envato.market/youtubomatic')));
                                $return_me = aiomatic_post_to_youtube_community($card_type_found, $content, $post_type, $media);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('YouTube Community posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'YouTube Community posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse YouTube Community posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse YouTube Community posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'YouTube Community posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('YouTube Community posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'YouTube Community posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content or post_type parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_reddit') 
                    {
                        if(isset($fun_args->content) && isset($fun_args->title))
                        {
                            $result = '';
                            $title = $fun_args->title;
                            if (empty($title)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty title parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content = $fun_args->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('redditomatic-reddit-post-generator/redditomatic-reddit-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Redditomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $subreddit_to_post = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['subreddits_list']) && $aiomatic_Chatbot_Settings['subreddits_list'] != '') {
                                    $subreddit_to_post = $aiomatic_Chatbot_Settings['subreddits_list'];
                                }
                                if (empty($subreddit_to_post)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No subreddits were defined where to publish the post',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('redditomatic-reddit-post-generator/redditomatic-reddit-post-generator.php' => array('Redditomatic Automatic Post Generator', 'https://1.envato.market/redditomatic')));
                                $return_me = aiomatic_post_to_reddit($card_type_found, $title, $content, 'auto', $subreddit_to_post);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Reddit posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Reddit posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse Reddit posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse Reddit posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Reddit posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Reddit posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Reddit posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content or title parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                    elseif ($tool_call->name === 'aiomatic_publish_linkedin') 
                    {
                        if(isset($fun_args->content) && isset($fun_args->title))
                        {
                            $result = '';
                            $title = $fun_args->title;
                            if (empty($title)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty title parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $content = $fun_args->content;
                            if (empty($content)) 
                            {
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'Empty content parameter provided',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                            $description = '';
                            $post_link = '';
                            $image_url = '';
                            if(isset($fun_args->description))
                            {
                                $description = $fun_args->description;
                            }
                            if(isset($fun_args->link))
                            {
                                $post_link = $fun_args->link;
                            }
                            if(isset($fun_args->image_url))
                            {
                                $image_url = $fun_args->image_url;
                            }
                            try 
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                $instamatic_active = false;
                                if (is_plugin_active('linkedinomatic-linkedin-post-generator/linkedinomatic-linkedin-post-generator.php')) 
                                {
                                    $instamatic_active = true;
                                }
                                if(!$instamatic_active)
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Required plugin (Linkedinomatic) not activated.',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                $selected_pages = '';
                                $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                                if (isset($aiomatic_Chatbot_Settings['linkedin_selected_pages']) && $aiomatic_Chatbot_Settings['linkedin_selected_pages'] != '') {
                                    $selected_pages = $aiomatic_Chatbot_Settings['linkedin_selected_pages'];
                                }
                                if (empty($selected_pages)) 
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'No pages selected where to post to LinkedIn',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                require_once(dirname(__FILE__) . "/aiomatic-socials.php");
                                $card_type_found = array('required_plugin' => array('linkedinomatic-linkedin-post-generator/linkedinomatic-linkedin-post-generator.php' => array('Linkedinomatic Automatic Post Generator', 'https://1.envato.market/linkedinomatic')));
                                $return_me = aiomatic_post_to_linkedin($card_type_found, $content, $image_url, $title, $post_link, $description, '1', $selected_pages);
                                if(isset($return_me['error']))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('LinkedIn posting failed: ' . $return_me['error']);
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'LinkedIn posting failed: ' . $return_me['error'],
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                elseif(empty($return_me))
                                {
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Failed to parse LinkedIn posting results');
                                    }
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'Failed to parse LinkedIn posting results',
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                                else
                                {
                                    aiomatic_add_tool_results($reply, array(
                                        "tool_call_id" => $tool_call->id,
                                        "role" => "tool",
                                        "content" => 'LinkedIn posting success: ' . json_encode($return_me, true),
                                        'assistant_message' => $as_mes
                                    ));
                                    continue;
                                }
                            }
                            catch (Exception $e) 
                            {
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('LinkedIn posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!');
                                }
                                aiomatic_add_tool_results($reply, array(
                                    "tool_call_id" => $tool_call->id,
                                    "role" => "tool",
                                    "content" => 'LinkedIn posting failed: Exception thrown ' . esc_html($e->getMessage()) . '!',
                                    'assistant_message' => $as_mes
                                ));
                                continue;
                            }
                        }
                        else
                        {
                            aiomatic_add_tool_results($reply, array(
                                "tool_call_id" => $tool_call->id,
                                "role" => "tool",
                                "content" => 'content or title parameter not found',
                                'assistant_message' => $as_mes
                            ));
                            aiomatic_log_to_file('Failed to decode function calling: ' . print_r($tool_call, true));
                        }
                    }
                }
            }
        }
    }
    return $reply;
}
?>