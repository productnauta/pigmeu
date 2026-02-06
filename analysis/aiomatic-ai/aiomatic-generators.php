<?php
defined('ABSPATH') or die();

use \Eventviva\ImageResize;
use AiomaticOpenAI\OpenRouter\OpenRouter;
use AiomaticOpenAI\OpenAi\OpenAi;
function aiomatic_generate_text(&$token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, &$finish_reason, &$error, $no_internet = false, $no_embeddings = false, $stream = false, $vision_file = '', $user_question = '', $role = 'user', $assistant_id = '', &$thread_id = '', $embedding_namespace = '', $function_result = '', $file_data = '', $parse_markdown = false, $store_data = false, $forced_functions = array())
{
    $token = apply_filters('aiomatic_openai_api_key', $token);
    $model = apply_filters('aiomatic_model_selection', trim($model));
    $model = trim($model);
    if(empty($model))
    {
        $model = AIOMATIC_DEFAULT_MODEL;
    }
    $assistant_id = apply_filters('aiomatic_assistant_id_custom_logic', $assistant_id, $aicontent, $model);
    $aicontent = apply_filters('aiomatic_modify_ai_query', $aicontent, $model, $assistant_id);
    $retry_count = apply_filters('aiomatic_retry_count', $retry_count, $aicontent, $model, $assistant_id);
    $is_allowed = apply_filters('aiomatic_is_ai_query_allowed', true, $aicontent);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
        return false;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $function_result = apply_filters('aiomatic_function_result', $function_result, $aicontent, $model, $assistant_id);
    $role = apply_filters('aiomatic_user_role_adjustment', $role, $aicontent, $model, $assistant_id);
    $user_question = apply_filters('aiomatic_user_question', $user_question, $role, $model, $aicontent);
    $thread_id = apply_filters('aiomatic_thread_id', $thread_id, $aicontent, $model, $assistant_id);
    if(empty($function_result))
    {
        if($is_chat)
        {
            $model_parts = explode(':', $model);
            $checkmodel = $model_parts[0];
            if(in_array($model, AIOMATIC_FUNCTION_CALLING_MODELS) || in_array($model, AIOMATIC_GROQ_FUNCTION_CALLING_MODELS) || in_array($model, AIOMATIC_XAI_FUNCTION_CALLING_MODELS) || in_array($model, AIOMATIC_GOOGLE_FUNCTION_CALLING_MODELS) || in_array($checkmodel, AIOMATIC_OLLAMA_FUNCTION_CALLING_MODELS))
            {
                require_once(dirname(__FILE__) . "/aiomatic-god-mode.php");
                require_once(dirname(__FILE__) . "/aiomatic-god-mode-parser.php");
            }
        }
    }
    else
    {
        if($function_result != 'disabled')
        {
            if($is_chat)
            {
                remove_filter('aiomatic_ai_functions', 'aiomatic_add_god_mode', 999);
            }
        }
    }
    $temperature = apply_filters('aiomatic_temperature', $temperature, $aicontent, $model, $assistant_id);
    $top_p = apply_filters('aiomatic_top_p', $top_p, $aicontent, $model, $assistant_id);
    $presence_penalty = apply_filters('aiomatic_presence_penalty', $presence_penalty, $aicontent, $model, $assistant_id);
    $frequency_penalty = apply_filters('aiomatic_frequency_penalty', $frequency_penalty, $aicontent, $model, $assistant_id);
    $vision_file = apply_filters('aiomatic_vision_file', $vision_file, $aicontent, $model, $assistant_id);
    $embedding_namespace = apply_filters('aiomatic_embedding_namespace', $embedding_namespace, $aicontent, $model, $assistant_id);
    if ($stream) 
    {
        do_action('aiomatic_on_streamed_query', $aicontent, $model, $assistant_id);
    }
    $functions = apply_filters('aiomatic_ai_functions', false, $forced_functions);
    $functions = apply_filters('aiomatic_post_ai_functions', $functions);
    if(!empty($functions) && is_array($functions))
    {
        if(isset($functions['functions']) && is_array($functions['functions']))
        {
            if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
            {
                $total_used_tokens = 0;
                foreach($functions['functions'] as $func_count)
                {
                    if(isset($func_count['function']->name))
                    {
                        $total_used_tokens += count(aiomatic_encode($func_count['function']->name));
                    }
                    if(isset($func_count['function']->description))
                    {
                        $total_used_tokens += count(aiomatic_encode($func_count['function']->description));
                    }
                    if(isset($func_count['function']->parameters) && is_array($func_count['function']->parameters))
                    {
                        foreach($func_count['function']->parameters as $fpar)
                        {
                            if(isset($fpar->name))
                            {
                                $total_used_tokens += count(aiomatic_encode($fpar->name));
                            }
                            if(isset($fpar->description))
                            {
                                $total_used_tokens += count(aiomatic_encode($fpar->description));
                            }
                        }
                    }
                }
                $available_tokens = $available_tokens - $total_used_tokens;
                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_CHAT)
                {
                    do_action('aiomatic_on_token_limit_warning', $available_tokens, $aicontent, $model, $assistant_id);
                    if(is_string($aicontent))
                    {
                        $string_len = strlen($aicontent);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $aicontent = aiomatic_substr($aicontent, 0 - $string_len);
                        $aicontent = trim($aicontent);
                        if(empty($aicontent))
                        {
                            $error = 'Incorrect chat prompt provided(2): ' . $aicontent;
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
                            return false;
                        }
                        $query_token_count = count(aiomatic_encode($aicontent));
                        $max_tokens = aiomatic_get_max_tokens($model);
                        $available_tokens = $max_tokens - $query_token_count;
                    }
                    else
                    {
                        $aitext = '';
                        foreach($aicontent as $aimess)
                        {
                            if(isset($aimess['content']))
                            {
                                if(!is_array($aimess['content']))
                                {
                                    $aitext .= $aimess['content'] . '\n';
                                }
                                else
                                {
                                    foreach($aimess['content'] as $internalmess)
                                    {
                                        if($internalmess['type'] == 'text')
                                        {
                                            $aitext .= $internalmess['text'] . '\n';
                                        }
                                    }
                                }
                            }
                        }
                        $max_tokens = aiomatic_get_max_tokens($model);
                        $query_token_count = count(aiomatic_encode($aitext));
                        $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $aitext, $query_token_count);
                        if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                        {
                            $startIndex = intdiv(count($aicontent), 2);
                            $aicontent = array_slice($aicontent, $startIndex);
                            $ars = array_keys($aicontent);
                            $lastindex = end($ars);
                            $string_len = strlen($aicontent[$lastindex]['content']);
                            $string_len = $string_len / 2;
                            $string_len = intval(0 - $string_len);
                            $aicontent[$lastindex]['content'] = aiomatic_substr($aicontent[$lastindex]['content'], 0, $string_len);
                            $aicontent[$lastindex]['content'] = trim($aicontent[$lastindex]['content']);
                            $aitext = '';
                            foreach($aicontent as $aimess)
                            {
                                if(isset($aimess['content']))
                                {
                                    if(!is_array($aimess['content']))
                                    {
                                        $aitext .= $aimess['content'] . '\n';
                                    }
                                    else
                                    {
                                        foreach($aimess['content'] as $internalmess)
                                        {
                                            if($internalmess['type'] == 'text')
                                            {
                                                $aitext .= $internalmess['text'] . '\n';
                                            }
                                        }
                                    }
                                }
                            }
                            $query_token_count = count(aiomatic_encode($aitext));
                            $available_tokens = $max_tokens - $query_token_count;
                        }
                    }
                }
            }
        }
    }
    $available_tokens = apply_filters('aiomatic_available_tokens_before_check', $available_tokens, $aicontent, $model, $assistant_id);
    if(isset($aiomatic_Main_Settings['multiple_key']) && $aiomatic_Main_Settings['multiple_key'] == 'on')
    {
        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
    }
    if ( empty($token) ) 
    {
        $error = esc_html__('Empty API key provided!', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
        return false;
    }
    if(empty($user_question))
    {
        if(is_array($aicontent))
        {
            $arkey = array_keys($aicontent);
            $lastindex = end($arkey);
            if(isset($aicontent[$lastindex]['content']))
            {
                $user_question = $aicontent[$lastindex]['content'];
            }
        }
        else
        {
            $user_question = $aicontent;
        }
    }
    if(empty($assistant_id))
    {
        $assistant_id = '';
    }
    $store_data = apply_filters('aiomatic_should_store_data', $store_data, $aicontent, $model, $assistant_id);
    if(!empty(trim($assistant_id)) && !aiomatic_is_aiomaticapi_key($token) && !(aiomatic_check_if_azure_or_others($aiomatic_Main_Settings)))
    {
        if(!aiomatic_is_vision_model('', $assistant_id) && $vision_file != '')
        {
            $vision_file = '';
        }
        if(!empty($vision_file))
        {
            do_action('aiomatic_before_vision_file_process', $vision_file, $aicontent, $model, $assistant_id);
        }
        try
        {
            $local_assistant_id = '';
            if(is_numeric($assistant_id))
            {
                $assistant_id_temp = get_post_meta($assistant_id, '_assistant_id', true);
                if(!empty($assistant_id_temp))
                {
                    $local_assistant_id = $assistant_id;
                    $assistant_id = $assistant_id_temp;
                }
            }
            if(empty($local_assistant_id))
            {
                $local_assist = aiomatic_find_local_assistant_id($assistant_id);
                if($local_assist !== false)
                {
                    $local_assistant_id = $local_assist;
                }
            }
            do_action('aiomatic_before_assistant_ai_query', $token, $assistant_id, $local_assistant_id, $role, $user_question, $thread_id, $no_internet, $no_embeddings, $env, $retry_count, $embedding_namespace, $stream, $function_result, $vision_file, $file_data);
            $response_ai = aiomatic_generate_text_assistant($token, $assistant_id, $local_assistant_id, $role, $user_question, $thread_id, $no_internet, $no_embeddings, $env, $retry_count, $embedding_namespace, $stream, $function_result, $vision_file, $file_data);
            do_action('aiomatic_after_assistant_ai_query', $response_ai, $token, $assistant_id, $local_assistant_id, $role, $user_question, $thread_id, $no_internet, $no_embeddings, $env, $retry_count, $embedding_namespace, $stream, $function_result, $vision_file, $file_data);
            if(isset($response_ai['content'][0]['text']['value']))
            {
                $response_text = $response_ai['content'][0]['text']['value'];
            }
            else
            {
                throw new Exception('Cannot parse AI response: ' . print_r($response_ai, true));
            }
        }
        catch(Exception $e)
        {
            $error = 'Error in AI (' . $assistant_id . '): ' . $e->getMessage();
            apply_filters('aiomatic_modify_ai_error', $error);
            do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
            return false;
        }
        if($response_text === false || empty($response_text))
        {
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
            return false;
        }
        if($is_chat == false)
        {
            $response_text = aiomatic_clean_language_model_texts($response_text);
            $response_text= trim($response_text);
        }
    }
    else
    {
        if(!aiomatic_is_vision_model($model, '') && $vision_file != '')
        {
            $vision_file = '';
        }
        if(!empty($vision_file))
        {
            do_action('aiomatic_before_vision_file_process', $vision_file, $aicontent, $model, '');
        }
        if(aiomatic_is_chatgpt_model($model) || aiomatic_is_chatgpt_turbo_model($model) || aiomatic_is_perplexity_model($model) || aiomatic_is_google_chat_model($model) || aiomatic_is_groq_model($model) || aiomatic_is_nvidia_model($model) || aiomatic_is_xai_model($model) || aiomatic_is_chatgpt_o_model($model) || aiomatic_is_o1_model($model) || aiomatic_is_huggingface_model($model) || aiomatic_is_ollama_model($model) || aiomatic_is_openrouter_model($model) || aiomatic_is_custom_model($model, $aiomatic_Main_Settings))
        {
            if(is_array($aicontent))
            {
                $chatgpt_obj = $aicontent;
            }
            else
            {
                $role = 'user';
                $chatgpt_obj = array();
                $chatgpt_obj[] = array("role" => $role, "content" => $aicontent);
                $additional_tokens = count(aiomatic_encode($role . ': '));
                if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
                {
                    if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_CHAT)
                    {
                        do_action('aiomatic_on_token_limit_warning', $available_tokens, $aicontent, $model, $assistant_id);
                        $string_len = strlen($aicontent);
                        $string_len = $string_len / 2;
                        $string_len = intval(0 - $string_len);
                        $aicontent = aiomatic_substr($aicontent, 0 - $string_len);
                        $aicontent = trim($aicontent);
                        if(empty($aicontent))
                        {
                            $error = 'Incorrect chat prompt provided: ' . $aicontent;
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
                            return false;
                        }
                        $query_token_count = count(aiomatic_encode($aicontent));
                        $max_tokens = aiomatic_get_max_tokens($model);
                        $available_tokens = $max_tokens - $query_token_count;
                        $chatgpt_obj = array();
                        $chatgpt_obj[] = array("role" => $role, "content" => $aicontent);
                    }
                }
                if($available_tokens - $additional_tokens <= 0)
                {
                    $error = 'Not enough tokens for the call: ' . $aicontent;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
                    return false;
                }
                else
                {
                    if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
                    {
                        $available_tokens = $available_tokens - $additional_tokens;
                    }
                }
            }
            do_action('aiomatic_before_chat_ai_query', $token, $model, $chatgpt_obj, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, $no_internet, $no_embeddings, $functions, $stream, $vision_file, false, $user_question, $embedding_namespace, $function_result, false, $store_data);
            $response_text = aiomatic_generate_text_chat($token, $model, $chatgpt_obj, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, $no_internet, $no_embeddings, $functions, $stream, $vision_file, false, $user_question, $embedding_namespace, $function_result, false, $store_data);
            do_action('aiomatic_after_chat_ai_query', $response_text, $token, $model, $chatgpt_obj, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, $no_internet, $no_embeddings, $functions, $stream, $vision_file, false, $user_question, $embedding_namespace, $function_result, false, $store_data);
            if($response_text === false || empty($response_text))
            {
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
                return false;
            }
            if(stristr($response_text, '<body') !== false)
            {
                preg_match_all("/<body[^>]*>([\s\S]*?)<\s*\/body>/i", $response_text, $matches);
                if(isset($matches[1][0]))
                {
                    $response_text = trim($matches[1][0]);
                }
            }
            if($is_chat == false)
            {
                $response_text = aiomatic_clean_language_model_texts($response_text);
                $response_text= trim($response_text);
            }
        }
        else
        {
            if(is_array($aicontent))
            {
                $aitext = '';
                foreach($aicontent as $aimess)
                {
                    if(isset($aimess['content']))
                    {
                        if(!is_array($aimess['content']))
                        {
                            $aitext .= $aimess['content'] . '\n';
                        }
                        else
                        {
                            foreach($aimess['content'] as $internalmess)
                            {
                                if($internalmess['type'] == 'text')
                                {
                                    $aitext .= $internalmess['text'] . '\n';
                                }
                            }
                        }
                    }
                }
                $aicontent = $aitext;
            }
            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
            {
                $string_len = strlen($aicontent);
                $string_len = $string_len / 2;
                $string_len = intval(0 - $string_len);
                $aicontent = aiomatic_substr($aicontent, 0, $string_len);
                $aicontent = trim($aicontent);
                $query_token_count = count(aiomatic_encode($aicontent));
                $max_tokens = aiomatic_get_max_tokens($model);
                $available_tokens = $max_tokens - $query_token_count;
            }
            do_action('aiomatic_before_completion_ai_query', $token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, $no_internet, $no_embeddings, $stream, $user_question, $embedding_namespace, $vision_file);
            $response_text = aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, $no_internet, $no_embeddings, $stream, $user_question, $embedding_namespace, $vision_file);
            do_action('aiomatic_after_chat_ai_query', $response_text, $token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, $no_internet, $no_embeddings, $stream, $user_question, $embedding_namespace, $vision_file);
            if($response_text === false || empty($response_text))
            {
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                do_action('aiomatic_on_error', $error, $aicontent, $model, $assistant_id);
                return false;
            }
        }
    }
    $response_text = aiomatic_sanitize_ai_result($response_text);
    $response_text = apply_filters( 'aiomatic_modify_ai_reply', $response_text, $aicontent );
    if (!isset($aiomatic_Main_Settings['no_pre_code_remove']) || $aiomatic_Main_Settings['no_pre_code_remove'] != 'on')
    {
        $response_text = aiomatic_pre_code_remove($response_text);
    }
    $response_text = preg_replace('/```html([\s\S]*?)```/', '$1', $response_text);
    if ($parse_markdown == true && isset($aiomatic_Main_Settings['markdown_parse']) && $aiomatic_Main_Settings['markdown_parse'] == 'on')
    {
        if(aiomatic_containsMarkdown($response_text))
        {
            $response_text = aiomatic_parse_markdown($response_text);
        }
    }
    $response_text = str_replace(array('—','–'), '-', $response_text);
    $response_text = str_replace(array('‘','’'), '\'', $response_text);
    $response_text = str_replace(array('“','”'), '"', $response_text);
    $response_text = apply_filters('aiomatic_final_response_text', $response_text, $aicontent, $model, $assistant_id);
    do_action('aiomatic_on_successful_response', $response_text, $aicontent, $model, $assistant_id);
    return $response_text;
}
function aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, &$finish_reason, &$error, $no_internet = false, $no_embeddings = false, $stream = false, $user_question = '', $embedding_namespace = '', $vision_file = '')
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $embeddings_enabled = false;
    $internet_enabled = false;
    $max_tokens = aiomatic_get_max_tokens($model);
    $particular_embedding_namespace = '';
    aiomatic_get_internet_embeddings_result($aiomatic_Main_Settings, $env, $embeddings_enabled, $internet_enabled, $particular_embedding_namespace);
    if(empty($embedding_namespace) && !empty($particular_embedding_namespace))
    {
        $embedding_namespace = $particular_embedding_namespace;
    }
    if (isset($aiomatic_Main_Settings['first_embeddings']) && $aiomatic_Main_Settings['first_embeddings'] == 'on')
    {
        if($no_embeddings !== true && $embeddings_enabled == true && $retry_count == 0)
        {
            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
            {
                aiomatic_log_to_file('Getting embedding results for completions');
            }
            if(empty($user_question))
            {
                $user_question = $aicontent;
            }
            $embed_rez = aiomatic_embeddings_result($user_question, $token, $embedding_namespace);
            if($embed_rez['status'] == 'error')
            {
                if($embed_rez['data'] != 'No results found' && $embed_rez['data'] != 'No data returned' && $embed_rez['data'] != 'No embeddings are added in the plugin config!')
                {
                    aiomatic_log_to_file('Embeddings failed: ' . print_r($embed_rez, true));
                }
            }
            else
            {
                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                {
                    aiomatic_log_to_file('Embeddings results ok');
                }
                $aicontent_temp = '"' . $embed_rez['data'] . '" ' . $aicontent;
                $suffix_tokens = count(aiomatic_encode($aicontent_temp));
                $available_tokens = $available_tokens - $suffix_tokens;
                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                {
                    aiomatic_log_to_file('Negative available tokens resulted after embeddings, skipping it.');
                }
                else
                {
                    $aicontent = $aicontent_temp;
                }
            }
        }
    }
    if($no_internet !== true && $internet_enabled == true && $retry_count == 0)
    {
        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
        {
            aiomatic_log_to_file('Getting internet search results for : ' . $aicontent);
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
        if(empty($user_question))
        {
            $user_question = $aicontent;
        }
        $locale = '';
        if (isset($aiomatic_Main_Settings['internet_gl']) && $aiomatic_Main_Settings['internet_gl'] != '')
        {
            $locale = $aiomatic_Main_Settings['internet_gl'];
        }
        $internet_rez = aiomatic_internet_result($user_question, false, $locale);
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
        if($internet_results == '')
        {
            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
            {
                aiomatic_log_to_file('Internet search failed for text completion, no data returned!');
            }
        }
        else
        {
            if($internet_prompt != '')
            {
                $internet_prompt = str_ireplace('%%original_query%%', $aicontent, $internet_prompt);
                $internet_prompt = str_ireplace('%%current_date%%', date('Y-m-d'), $internet_prompt);
                $internet_prompt = str_ireplace('%%web_results%%', $internet_results, $internet_prompt);
                if($internet_prompt != '')
                {
                    $internet_tokens = count(aiomatic_encode($internet_prompt));
                    if($internet_tokens > $max_tokens - 300)
                    {
                        aiomatic_log_to_file('Negative available tokens resulted after internet results, skipping it.');
                    }
                    else
                    {
                        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                        {
                            aiomatic_log_to_file('Changing prompt to: ' . $internet_prompt);
                        }
                        $aicontent = $internet_prompt;
                        $available_tokens = $max_tokens - $internet_tokens;
                    }
                }
            }
        }
    }
    if (!isset($aiomatic_Main_Settings['first_embeddings']) || $aiomatic_Main_Settings['first_embeddings'] != 'on')
    {
        if($no_embeddings !== true && $embeddings_enabled == true && $retry_count == 0)
        {
            if(empty($user_question))
            {
                $user_question = $aicontent;
            }
            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
            {
                aiomatic_log_to_file('Getting embedding results for completions');
            }
            $embed_rez = aiomatic_embeddings_result($user_question, $token, $embedding_namespace);
            if($embed_rez['status'] == 'error')
            {
                if($embed_rez['data'] != 'No results found' && $embed_rez['data'] != 'No data returned' && $embed_rez['data'] != 'No embeddings are added in the plugin config!')
                {
                    aiomatic_log_to_file('Embeddings failed: ' . print_r($embed_rez, true));
                }
            }
            else
            {
                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                {
                    aiomatic_log_to_file('Embeddings ok');
                }
                $aicontent_temp = '"' . $embed_rez['data'] . '" ' . $aicontent;
                $suffix_tokens = count(aiomatic_encode($aicontent_temp));
                $available_tokens = $available_tokens - $suffix_tokens;
                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                {
                    aiomatic_log_to_file('Negative available tokens resulted after embeddings, skipping it.');
                }
                else
                {
                    $aicontent = $aicontent_temp;
                }
            }
        }
    }
    $content_tokens = count(aiomatic_encode($aicontent));
    $total_tokens = $content_tokens + $available_tokens;
    if($total_tokens >= $max_tokens && !aiomatic_is_new_token_window_model($model))
    {
        $available_tokens = $max_tokens - $content_tokens;
        if($available_tokens < AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
        {
            $string_len = strlen($aicontent);
            $string_len = $string_len / 2;
            $string_len = intval(0 - $string_len);
            $aicontent = aiomatic_substr($aicontent, 0, $string_len);
            $aicontent = trim($aicontent);
            if(empty($aicontent))
            {
                $error = 'Empty prompt returned after content trimming!';
                return false;
            }
            $query_token_count = count(aiomatic_encode($aicontent));
            $available_tokens = $max_tokens - $query_token_count;
        }
    }
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'text';
    $maxResults = 1;
    $query = new Aiomatic_Query($aicontent, $available_tokens, $model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, '', '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = $ok;
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
    {
        aiomatic_log_to_file('Generating AI completion text using model: ' . $model . ' and prompt: ' . $aicontent);
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
        usleep(intval($delay) * 1000);
    }
    if($temperature < 0 || $temperature > 1)
    {
        $temperature = 1;
    }
    if($top_p < 0 || $top_p > 1)
    {
        $top_p = 1;
    }
    if($presence_penalty < -2 || $presence_penalty > 2)
    {
        $presence_penalty = 0;
    }
    if($frequency_penalty < -2 || $frequency_penalty > 2)
    {
        $frequency_penalty = 0;
    }
    if($temperature == '' || (empty($temperature) && $temperature !== 0))
    {
        $temperature = 1;
    }
    if($top_p == '' || (empty($top_p) && $top_p !== 0))
    {
        $top_p = 1;
    }
    if($presence_penalty == '' || (empty($presence_penalty) && $presence_penalty !== 0))
    {
        $presence_penalty = 0;
    }
    if($frequency_penalty == '' || (empty($frequency_penalty) && $frequency_penalty !== 0))
    {
        $frequency_penalty = 0;
    }
    if(aiomatic_is_aiomaticapi_key($token))
    {
        $pargs = array();
        $api_url = 'https://aiomaticapi.com/apis/ai/v1/text/';
        $pargs['model'] = trim($model);
        $pargs['temperature'] = $temperature;
        $pargs['top_p'] = $top_p;
        $pargs['presence_penalty'] = $presence_penalty;
        $pargs['frequency_penalty'] = $frequency_penalty;
        $pargs['prompt'] = trim($aicontent);
        if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
        {
            $pargs['max_tokens'] = $available_tokens;
        }
        $api_headers = array(
            'Authorization' => 'Bearer ' . trim($token)
        );  
        $ai_response = aiomatic_get_web_page_api($api_url, $pargs, $api_headers);
        if($ai_response === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get AiomaticAPI response!';
                return false;
            }
        }
        $ai_json = json_decode($ai_response);
        if($ai_json === null)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to decode AiomaticAPI response: ' . $ai_response;
                return false;
            }
        }
        if(isset($ai_json->error))
        {
            if (stristr($ai_json->error, 'Your subscription expired, please renew it.') === false && stristr($ai_json->error, '[RATE LIMITED]') === false && isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after error failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error while processing AI response: ' . $ai_json->error;
                return false;
            }
        }
        if(!isset($ai_json->result))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after parse failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to parse AiomaticAPI response: ' . $ai_response;
                return false;
            }
        }
        if(isset($ai_json->remainingtokens))
        {
            set_transient('aiomaticapi_tokens', $ai_json->remainingtokens, 86400);
        }
        $ai_json = apply_filters( 'aiomatic_ai_reply_raw', $ai_json, $aicontent );
        apply_filters( 'aiomatic_ai_reply', $ai_json->result, $query );
        return $ai_json->result;
    }
    elseif (aiomatic_is_claude_model($model)) 
    {
        if(in_array($model, AIOMATIC_CLAUDE_MODELS) === false)
        {
            $error = 'This model is not currently supported by Claude API: ' . $model;
            return false;
        }
        if (!isset($aiomatic_Main_Settings['app_id_claude']) || trim($aiomatic_Main_Settings['app_id_claude']) == '')
        {
            aiomatic_log_to_file('You need to enter an Anthropic Claude API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_claude = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_claude']));
        $appids_claude = array_filter($appids_claude);
        $token_claude = $appids_claude[array_rand($appids_claude)];
        if($retry_count > 0)
        {
            $token_claude = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_claude']), $token);
        }
        $result = aiomatic_generate_text_local_claude($token_claude, $model, $aicontent, $temperature, $top_p, $vision_file, $available_tokens, $stream, $is_chat, $error);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Claude chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token_claude, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial Claude chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_openrouter_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_openrouter']) || trim($aiomatic_Main_Settings['app_id_openrouter']) == '')
        {
            aiomatic_log_to_file('You need to enter an OpenRouter API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_openrouter = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_openrouter']));
        $appids_openrouter = array_filter($appids_openrouter);
        $token_openrouter = $appids_openrouter[array_rand($appids_openrouter)];
        if($retry_count > 0)
        {
            $token_openrouter = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_openrouter']), $token);
        }
        $result = aiomatic_generate_text_openrouter($token_openrouter, $model, $aicontent, $temperature, $top_p, false, $available_tokens, $stream, $error);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') OpenRouter API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token_openrouter, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial OpenRouter API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_perplexity_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_perplexity']) || trim($aiomatic_Main_Settings['app_id_perplexity']) == '')
        {
            aiomatic_log_to_file('You need to enter an PerplexityAI API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_perplexity = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_perplexity']));
        $appids_perplexity = array_filter($appids_perplexity);
        $token_perplexity = $appids_perplexity[array_rand($appids_perplexity)];
        if($retry_count > 0)
        {
            $token_perplexity = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
        }
        $result = aiomatic_generate_text_perplexity($token_perplexity, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, $available_tokens, $stream, $retry_count, $query, false, '', $user_question, $env, $is_chat, $error, '');
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') PerplexityAI API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token_perplexity, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial PerplexityAI API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_groq_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_groq']) || trim($aiomatic_Main_Settings['app_id_groq']) == '')
        {
            aiomatic_log_to_file('You need to enter an Groq API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_groq = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_groq']));
        $appids_groq = array_filter($appids_groq);
        $token_groq = $appids_groq[array_rand($appids_groq)];
        if($retry_count > 0)
        {
            $token_groq = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_groq']), $token);
        }
        $result = aiomatic_generate_text_groq($token_groq, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, $available_tokens, $stream, $retry_count, $query, false, '', $user_question, $env, $is_chat, $error, '');
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Groq API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token_groq, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial Groq API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_nvidia_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_nvidia']) || trim($aiomatic_Main_Settings['app_id_nvidia']) == '')
        {
            aiomatic_log_to_file('You need to enter an Nvidia API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_nvidia = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_nvidia']));
        $appids_nvidia = array_filter($appids_nvidia);
        $token_nvidia = $appids_nvidia[array_rand($appids_nvidia)];
        if($retry_count > 0)
        {
            $token_nvidia = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_nvidia']), $token);
        }
        $result = aiomatic_generate_text_nvidia($token_nvidia, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, $available_tokens, $stream, $retry_count, $query, false, '', $user_question, $env, $is_chat, $error, '');
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Nvidia API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token_nvidia, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial Nvidia API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_xai_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_xai']) || trim($aiomatic_Main_Settings['app_id_xai']) == '')
        {
            aiomatic_log_to_file('You need to enter an xAI API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_xai = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_xai']));
        $appids_xai = array_filter($appids_xai);
        $token_xai = $appids_xai[array_rand($appids_xai)];
        if($retry_count > 0)
        {
            $token_xai = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_xai']), $token);
        }
        $result = aiomatic_generate_text_xai($token_xai, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, $available_tokens, $stream, $retry_count, $query, false, '', $user_question, $env, $is_chat, $error, '');
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') xAI API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token_xai, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial xAI API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_google_model($model)) 
    {
        if(in_array($model, AIOMATIC_GOOGLE_MODELS) === false)
        {
            $error = 'This model is not currently supported by Google API: ' . $model;
            return false;
        }
        if (!isset($aiomatic_Main_Settings['app_id_google']) || trim($aiomatic_Main_Settings['app_id_google']) == '')
        {
            aiomatic_log_to_file('You need to enter an Google Vertex API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_google = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_google']));
        $appids_google = array_filter($appids_google);
        $token_google = $appids_google[array_rand($appids_google)];
        if($retry_count > 0)
        {
            $token_google = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_google']), $token);
        }
        $result = aiomatic_generate_text_google_completion($token_google, $model, $aicontent, $stream, $error);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Google chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token_google, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial Google chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_check_if_azure($aiomatic_Main_Settings)) 
    {
        if (!isset($aiomatic_Main_Settings['azure_endpoint']) || trim($aiomatic_Main_Settings['azure_endpoint']) == '') 
        {
            $error = 'You need to enter an Azure Endpoint for this to work!';
            return false;
        }
        if(in_array($model, AIOMATIC_AZURE_MODELS) === false)
        {
            $error = 'This model is not currently supported by Azure API: ' . $model;
            return false;
        }
        if(aiomatic_is_trained_model($model))
        {
            $error = 'Fine-tuned models are not supported for Azure API';
            return false;
        }
        $localAzureDeployments = array();
        if($retry_count > 0)
        {
            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
        }
        $depl_arr = aiomatic_get_deployments($token);
        if(is_array($depl_arr))
        {
            foreach($depl_arr as $dar)
            {
                if(empty($dar))
                {
                    continue;
                }
                if(is_string($dar))
                {
                    $localAzureDeployments[trim($dar)] = trim($dar);
                }
                else
                {
                    $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                }
            }
        }
        $azureDeployment = '';
        foreach ( $localAzureDeployments as $dmodel => $dname ) 
        {
            if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                $azureDeployment = $dname;
                break;
            }
        }
        if ( $azureDeployment == '' ) 
        {
            $new_dep = aiomatic_update_deployments_azure($token);
            if($new_dep !== false)
            {
                $localAzureDeployments = array();
                foreach($new_dep as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
                foreach ( $localAzureDeployments as $dmodel => $dname ) 
                {
                    if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                        $azureDeployment = $dname;
                        break;
                    }
                }
            }
            if ( $azureDeployment == '' ) 
            {
                $error = 'No added Azure deployment found for completion model: ' . $model . ' - you need to add this model in your Azure Portal as a Deployment';
                return false;
            }
        }
        if (isset($aiomatic_Main_Settings['azure_api_selector']) && $aiomatic_Main_Settings['azure_api_selector'] != '' && $aiomatic_Main_Settings['azure_api_selector'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_API_VERSION;
        }
        $apiurl = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/deployments/' . $azureDeployment . '/completions' . $api_ver;
        $base_params = [
            'model' => str_replace('.', '', $model),
            'prompt' => $aicontent,
            'temperature' => $temperature,
            'top_p' => $top_p,
            'presence_penalty' => $presence_penalty,
            'frequency_penalty' => $frequency_penalty
        ];
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            $base_params['stream'] = true;
        }
        if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
        {
            $base_params['max_tokens'] = $available_tokens;
        }
        try
        {
            $send_json = aiomatic_safe_json_encode($base_params);
        }
        catch(Exception $e)
        {
            $error = 'Error: Exception in API payload encoding: ' . print_r($e->getMessage(), true);
            return false;
        }
        if($send_json === false)
        {
            $error = 'Error: Failed to encode API payload: ' . print_r($base_params, true);
            return false;
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            add_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        add_action('http_api_curl', 'aiomatic_add_proxy');
        $api_call = wp_remote_post(
            $apiurl,
            array(
                'headers' => array( 'Content-Type' => 'application/json', 'api-key' => $token ),
                'body'        => $send_json,
                'method'      => 'POST',
                'data_format' => 'body',
                'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
            )
        );
        remove_action('http_api_curl', 'aiomatic_add_proxy');
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        if(is_wp_error( $api_call ))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial failure: ' . print_r($api_call, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial API response: ' . print_r($api_call, true);
                return false;
            }
        }
        else
        {
            $result = json_decode( $api_call['body'] );
            if($stream === false && $result === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode failure: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                }
                else
                {
                    $error = 'Error: Failed to decode initial API response: ' . print_r($api_call, true);
                    return false;
                }
            }
            $is_error = false;
            $sleep_time = false;
            if(isset($result->error))
            {
                $result = $result->error;
                $is_error = true;
            }
            if($is_error && isset($result->type))
            {
                if($result->type == 'insufficient_quota')
                {
                    $error = 'Error: You exceeded your OpenAI quota limit. To fix this, if you are using a free OpenAI account, you need to add a VISA card to your account, as OpenAI heavily limits free accounts. Please check details here: https://platform.openai.com/docs/guides/rate-limits';
                    return false;
                }
                elseif($result->type == 'invalid_request_error')
                {
                    $error = 'Error: Invalid request submitted to the Azure completions API, result: ' . print_r($result, true);
                    return false;
                }
                else
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                        {
                            $errmessage = $result->message;
                            preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                            if(isset($htmlrez[1][0]))
                            {
                                $sleep_time = ceil(floatval($htmlrez[1][0]));
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                                }
                                sleep($sleep_time);
                            }
                        }
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after type failure: ' . print_r($api_call['body'], true));
                        if($sleep_time === false)
                        {
                            sleep(pow(2, $retry_count));
                        }
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                    }
                    else
                    {
                        $error = 'Error: An error occurred when initially calling OpenAI API: ' . print_r($result, true);
                        return false;
                    }
                }
            }
            if(!$stream)
            {
                if(!isset($result->choices[0]->text))
                {
                    delete_option('aiomatic_deployments_list');
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after choices failure: ' . print_r($api_call['body'], true));
                        sleep(pow(2, $retry_count));
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                    }
                    else
                    {
                        if(isset($result->code) && $result->code == 'content_filter')
                        {
                            $error = 'Error: The response was filtered by our content management policy.';
                            return false;
                        }
                        else
                        {
                            $error = 'Error: Choices not found in initial API result: ' . print_r($result, true);
                            return false;
                        }
                    }
                }
                else
                {
                    $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                    apply_filters( 'aiomatic_ai_reply', $result->choices[0]->text, $query );
                    if(isset($result->choices[0]->finish_reason))
                    {
                        $finish_reason = $result->choices[0]->finish_reason;
                    }
                    else
                    {
                        $finish_reason = $result->choices[0]->finish_details->type;
                    }
                    if($is_chat == true)
                    {
                        $chat_max_characters = 16000;
                        $max_continue_characters = 4000;
                        if($finish_reason == 'stop')
                        {
                            if (empty($result->choices[0]->text) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                            {
                                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                            }
                            else
                            {
                                return $result->choices[0]->text;
                            }
                        }
                        else
                        {
                            $return_text = $result->choices[0]->text;
                            $aicontent .= $return_text;
                            $complet_retry_count = 0;
                            while($finish_reason != 'stop' && strlen($return_text) < $chat_max_characters)
                            {
                                if (isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $complet_retry_count)
                                {
                                    break;
                                }
                                $complet_retry_count++;
                                if(strlen($aicontent) > $max_continue_characters)
                                {
                                    $aicontent = aiomatic_substr($aicontent, 0, (0 - $max_continue_characters));
                                }
                                $aicontent = trim($aicontent);
                                if(empty($aicontent))
                                {
                                    break;
                                }
                                $query_token_count = count(aiomatic_encode($aicontent));
                                $available_tokens = $max_tokens - $query_token_count;
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($aicontent);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $aicontent = aiomatic_substr($aicontent, 0, $string_len);
                                    $aicontent = trim($aicontent);
                                    if(empty($aicontent))
                                    {
                                        break;
                                    }
                                    $query_token_count = count(aiomatic_encode($aicontent));
                                    $available_tokens = $max_tokens - $query_token_count;
                                }
                                $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
                                if ( $ok !== true ) {
                                    aiomatic_log_to_file('Rate limited: ' . $ok);
                                    break;
                                }
                                $aierror = '';
                                $generated_text = aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, 0, $finish_reason, $aierror, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                                if($generated_text === false)
                                {
                                    aiomatic_log_to_file('Chat response completion error: ' . $aierror);
                                    break;
                                }
                                else
                                {
                                    $return_text .= $generated_text;
                                    $aicontent .= $generated_text;
                                }
                            }
                            return $return_text;
                        }
                    }
                    else
                    {
                        return $result->choices[0]->text;
                    }
                }
            }
            else
            {
                return $result;
            }
        }
    }
    else
    {
        $base_params = [
            'model' => $model,
            'prompt' => $aicontent,
            'temperature' => $temperature,
            'top_p' => $top_p,
            'presence_penalty' => $presence_penalty,
            'frequency_penalty' => $frequency_penalty
        ];
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            $base_params['stream'] = true;
        }
        if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
        {
            $base_params['max_tokens'] = $available_tokens;
        }
        if (isset($aiomatic_Main_Settings['ai_seed']) && $aiomatic_Main_Settings['ai_seed'] != '')
        {
            $base_params['seed'] = intval($aiomatic_Main_Settings['ai_seed']);
        }
        try
        {
            $send_json = aiomatic_safe_json_encode($base_params);
        }
        catch(Exception $e)
        {
            $error = 'Error: Exception in API payload encoding: ' . print_r($e->getMessage(), true);
            return false;
        }
        if($send_json === false)
        {
            $error = 'Error: Failed to encode API payload: ' . print_r($base_params, true);
            return false;
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            add_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        add_action('http_api_curl', 'aiomatic_add_proxy');
        $xh = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        );
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $xh['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $api_call = wp_remote_post(
            $api_endpoint . '/v1/completions',
            array(
                'headers' => $xh,
                'body'        => $send_json,
                'method'      => 'POST',
                'data_format' => 'body',
                'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
            )
        );
        remove_action('http_api_curl', 'aiomatic_add_proxy');
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        if(is_wp_error( $api_call ))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial failure: ' . print_r($api_call, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
            }
            else
            {
                $error = 'Error: Failed to get initial API response: ' . print_r($api_call, true);
                return false;
            }
        }
        else
        {
            $result = json_decode( $api_call['body'] );
            if($result === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode failure: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                }
                else
                {
                    $error = 'Error: Failed to decode initial API response: ' . print_r($api_call, true);
                    return false;
                }
            }
            $is_error = false;
            $sleep_time = false;
            if(isset($result->error))
            {
                $result = $result->error;
                $is_error = true;
            }
            if($is_error && isset($result->type))
            {
                if($result->type == 'insufficient_quota')
                {
                    $error = 'Error: You exceeded your OpenAI quota limit for this API key. To fix this, if you are using a free OpenAI account, you need to add a VISA card to your account, as OpenAI heavily limits free accounts. Please check details here: https://platform.openai.com/docs/guides/rate-limits';
                    return false;
                }
                elseif($result->type == 'invalid_request_error')
                {
                    $error = 'Error: Invalid request submitted to the completions API, result: ' . print_r($result, true);
                    return false;
                }
                else
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                        if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                        {
                            $errmessage = $result->message;
                            preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                            if(isset($htmlrez[1][0]))
                            {
                                $sleep_time = ceil(floatval($htmlrez[1][0]));
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                                }
                                sleep($sleep_time);
                            }
                        }
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after type failure: ' . print_r($api_call['body'], true));
                        if($sleep_time === false)
                        {
                            sleep(pow(2, $retry_count));
                        }
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                    }
                    else
                    {
                        $error = 'Error: An error occurred when initially calling OpenAI API: ' . print_r($result, true);
                        return false;
                    }
                }
            }
            if(!$stream)
            {
                if(!isset($result->choices[0]->text))
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after choices failure: ' . print_r($api_call['body'], true));
                        sleep(pow(2, $retry_count));
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                    }
                    else
                    {
                        if(isset($result->code) && $result->code == 'content_filter')
                        {
                            $error = 'Error: The response was filtered by our content management policy.';
                            return false;
                        }
                        else
                        {
                            $error = 'Error: Choices not found in initial API result: ' . print_r($result, true);
                            return false;
                        }
                    }
                }
                else
                {
                    $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                    apply_filters( 'aiomatic_ai_reply', $result->choices[0]->text, $query );
                    if(isset($result->choices[0]->finish_reason))
                    {
                        $finish_reason = $result->choices[0]->finish_reason;
                    }
                    else
                    {
                        $finish_reason = $result->choices[0]->finish_details->type;
                    }
                    if($is_chat == true)
                    {
                        $chat_max_characters = 16000;
                        $max_continue_characters = 4000;
                        if($finish_reason == 'stop')
                        {
                            if (empty($result->choices[0]->text) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                            {
                                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                                return aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                            }
                            else
                            {
                                return $result->choices[0]->text;
                            }
                        }
                        else
                        {
                            $return_text = $result->choices[0]->text;
                            $aicontent .= $return_text;
                            $complet_retry_count = 0;
                            while($finish_reason != 'stop' && strlen($return_text) < $chat_max_characters)
                            {
                                if (isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $complet_retry_count)
                                {
                                    break;
                                }
                                $complet_retry_count++;
                                if(strlen($aicontent) > $max_continue_characters)
                                {
                                    $aicontent = aiomatic_substr($aicontent, 0, (0 - $max_continue_characters));
                                }
                                $aicontent = trim($aicontent);
                                if(empty($aicontent))
                                {
                                    break;
                                }
                                $query_token_count = count(aiomatic_encode($aicontent));
                                $available_tokens = $max_tokens - $query_token_count;
                                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                                {
                                    $string_len = strlen($aicontent);
                                    $string_len = $string_len / 2;
                                    $string_len = intval(0 - $string_len);
                                    $aicontent = aiomatic_substr($aicontent, 0, $string_len);
                                    $aicontent = trim($aicontent);
                                    if(empty($aicontent))
                                    {
                                        break;
                                    }
                                    $query_token_count = count(aiomatic_encode($aicontent));
                                    $available_tokens = $max_tokens - $query_token_count;
                                }
                                $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
                                if ( $ok !== true ) {
                                    aiomatic_log_to_file('Rate limited: ' . $ok);
                                    break;
                                }
                                $aierror = '';
                                $generated_text = aiomatic_generate_text_completion($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, 0, $finish_reason, $aierror, true, true, $stream, $user_question, $embedding_namespace, $vision_file);
                                if($generated_text === false)
                                {
                                    aiomatic_log_to_file('Chat response completion error: ' . $aierror);
                                    break;
                                }
                                else
                                {
                                    $return_text .= $generated_text;
                                    $aicontent .= $generated_text;
                                }
                            }
                            return $return_text;
                        }
                    }
                    else
                    {
                        return $result->choices[0]->text;
                    }
                }
            }
            else
            {
                return $result;
            }
        }
    }
    $error = 'Failed to finish API call correctly.';
    return false;
}
function aiomatic_generate_response_api($token, $model, $input, $available_tokens = null, $temperature = 1.0, $top_p = 1.0, $stream = false, $instructions = null, $max_output_tokens = null, $store_data = false, $functions = false, $query = null, $retry_count = 0, &$error = '') {
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(is_array($input))
    {
        foreach($input as $inputvar => $za_val)
        {
            if(isset($input[$inputvar]['content']) && is_array($input[$inputvar]['content']))
            {
                foreach($input[$inputvar]['content'] as $cntind => $cnt_val)
                {
                    if(isset($input[$inputvar]['content'][$cntind]['type']))
                    {
                        if($input[$inputvar]['content'][$cntind]['type'] == 'text')
                        {
                            $input[$inputvar]['content'][$cntind]['type'] = 'input_text';
                        }
                        elseif($input[$inputvar]['content'][$cntind]['type'] == 'image_url')
                        {
                            $input[$inputvar]['content'][$cntind]['type'] = 'input_image';
                            if(isset($input[$inputvar]['content'][$cntind]['image_url']) && is_array($input[$inputvar]['content'][$cntind]['image_url'])&& isset($input[$inputvar]['content'][$cntind]['image_url']['url']) )
                            {
                                $input[$inputvar]['content'][$cntind]['image_url'] = $input[$inputvar]['content'][$cntind]['image_url']['url'];
                            }
                        }
                    }
                }
            }
        }
    }
    foreach($input as $inputvar => $za_val)
    {
        if(!isset($input[$inputvar]['role']))
        {
            unset($input[$inputvar]);
            continue;
        }
        if(isset($input[$inputvar]['run_id']))
        {
            unset($input[$inputvar]['run_id']);
        }
        if(isset($input[$inputvar]['thread_id']))
        {
            unset($input[$inputvar]['thread_id']);
        }
    }
    foreach($input as $inputvar => $za_val)
    {
        if(isset($input[$inputvar]['role']) && $input[$inputvar]['role'] == 'tool')
        {
            $input[$inputvar]['role'] = 'developer';
            if(isset($input[$inputvar]['tool_call_id']))
            {
                unset($input[$inputvar]['tool_call_id']);
            }
        }
    }
    $input = array_values($input);
    $base_params = [
        'model' => $model,
        'input' => $input, 
        'temperature' => floatval($temperature),
        'top_p' => floatval($top_p),
        'truncation' => 'auto',
        'parallel_tool_calls' => false
    ];
    if ($stream === true && function_exists('curl_init') && extension_loaded("curl")) {
        $base_params['stream'] = true;
    }
    if (!empty($instructions)) {
        $base_params['instructions'] = $instructions;
    }
    if ($max_output_tokens !== null && (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')) {
        $base_params['max_output_tokens'] = intval($max_output_tokens);
    }
    if ((isset($aiomatic_Main_Settings['store_data']) && $aiomatic_Main_Settings['store_data'] == 'on') || ($store_data === true || $store_data === 'on' || $store_data === '1'))
    {
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {//don't store results for custom endpoints
        }
        else
        {
            $base_params['store'] = true;
        }
    }
    if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
    {
        //currently tool choice is not supported in o1 models
        if(!aiomatic_is_o1_model($model))
        {
            $converted_functions = array();
            foreach($functions['functions'] as $indx => $funcx)
            {
                if(!isset($functions['functions'][$indx]['name']) && isset($functions['functions'][$indx]['function']->name))
                {
                    $functions['functions'][$indx]['name'] = $functions['functions'][$indx]['function']->name;
                }
                $function_obj = $funcx['function'];
                $add_func = new stdClass();
                $add_func->type = 'function';
                $add_func->name = $funcx['name'] ?? $function_obj->name;
                $add_func->description = $function_obj->description;
                $add_func->parameters = new stdClass();
                $add_func->parameters->type = 'object';
                $add_func->parameters->properties = new stdClass();
                $required = [];
                if (!empty($function_obj->parameters)) {
                    foreach ($function_obj->parameters as $param) {
                        $param_name = $param->name;
                        $param_desc = $param->description;
                        $param_type = $param->type;
    
                        $add_func->parameters->properties->$param_name = [
                            'type' => $param_type,
                            'description' => $param_desc,
                        ];
    
                        if (!empty($param->required)) {
                            $required[] = $param_name;
                        }
                    }
                }
                if (!empty($required)) {
                    $add_func->parameters->required = $required;
                }
                $converted_functions[] = $add_func;
            }
            $base_params['tools'] = $converted_functions;
            $base_params['tool_choice'] = 'auto';
        }
    }
    try {
        $send_json = aiomatic_safe_json_encode($base_params);
    } catch (Exception $e) {
        $error = 'Error: Exception in API payload encoding: ' . print_r($e->getMessage(), true);
        return false;
    }
    if ($send_json === false) {
        $error = 'Error: Failed to encode API payload: ' . print_r($base_params, true);
        return false;
    }
    $xh = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $token,
    ];
    if (isset($aiomatic_Main_Settings['openai_organization']) && !empty($aiomatic_Main_Settings['openai_organization'])) {
        $xh['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
    }
    if ($stream === true && function_exists('curl_init') && extension_loaded("curl")) {
        add_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    add_action('http_api_curl', 'aiomatic_add_proxy');
    $api_call = wp_remote_post(
        'https://api.openai.com/v1/responses',
        array(
            'headers' => $xh,
            'body' => $send_json,
            'method' => 'POST',
            'data_format' => 'body',
            'timeout' => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        )
    );
    remove_action('http_api_curl', 'aiomatic_add_proxy');
    if ($stream === true && function_exists('curl_init') && extension_loaded("curl")) {
        remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    if (is_wp_error($api_call)) {
        if (isset($aiomatic_Main_Settings['max_retry']) && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count) {
            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial failure: ' . print_r($api_call, true));
            sleep(pow(2, $retry_count));
            return aiomatic_generate_response_api($token, $model, $input, $available_tokens, $temperature, $top_p, $stream, $instructions, $max_output_tokens, $store_data, $functions, $query, intval($retry_count) + 1, $error);
        } else {
            $error = 'Error: Failed to get initial API response: ' . print_r($api_call, true);
            return false;
        }
    }
    if (!$stream) 
    {
        $result = json_decode($api_call['body']);
        if ($result === null) {
            if (isset($aiomatic_Main_Settings['max_retry']) && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count) {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode failure: ' . print_r($api_call['body'], true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_response_api($token, $model, $input, $available_tokens, $temperature, $top_p, $stream, $instructions, $max_output_tokens, $store_data, $functions, $query, intval($retry_count) + 1, $error);
            } else {
                $error = 'Error: Failed to decode initial API response: ' . print_r($api_call, true);
                return false;
            }
        }
        if (isset($result->error)) {
            $error_data = $result->error;
            if ($error_data->type == 'insufficient_quota') {
                $error = 'Error: You exceeded your OpenAI quota limit for this API key. Please check details here: https://platform.openai.com/docs/guides/rate-limits';
                return false;
            } elseif ($error_data->type == 'invalid_request_error') {
                $error = 'Error: Invalid request submitted to the responses API: ' . print_r($error_data, true);
                return false;
            } else {
                if (isset($aiomatic_Main_Settings['max_retry']) && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count) {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                    if (isset($error_data->code) && $error_data->code == 'rate_limit_exceeded') {
                        preg_match('#Please try again in ([\d.]*?)s#i', $error_data->message, $match);
                        $sleep_time = isset($match[1]) ? ceil(floatval($match[1])) : pow(2, $retry_count);
                        aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                        sleep($sleep_time);
                    } else {
                        sleep(pow(2, $retry_count));
                    }
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after error: ' . print_r($api_call['body'], true));
                    return aiomatic_generate_response_api($token, $model, $input, $available_tokens, $temperature, $top_p, $stream, $instructions, $max_output_tokens, $store_data, $functions, $query, intval($retry_count) + 1, $error);
                } else {
                    $error = 'Error: An error occurred when calling OpenAI Responses API: ' . print_r($error_data, true);
                    return false;
                }
            }
        }
        $current_cont = 0;
        if(isset($result->output[$current_cont]->type) && $result->output[$current_cont]->type == 'reasoning')
        {
            $current_cont++;
        }
        if (!isset($result->output[$current_cont]->content[0]->text) && !isset($result->output[$current_cont]->type)) {
            if (isset($aiomatic_Main_Settings['max_retry']) && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count) {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after output failure: ' . print_r($api_call['body'], true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_response_api($token, $model, $input, $available_tokens, $temperature, $top_p, $stream, $instructions, $max_output_tokens, $store_data, $functions, $query, intval($retry_count) + 1, $error);
            } else {
                $error = 'Error: Output text not found in API result: ' . print_r($result, true);
                return false;
            }
        }
        $result = apply_filters('aiomatic_ai_responses_reply_raw', $result, $input);
        return $result;
    } else {
        $result = '';
        return $result;
    }
}
function aiomatic_generate_text_assistant($token, $assistant_id, $local_assistant_id, $role, $content, &$thread_id, $no_internet = false, $no_embeddings = false, $env = '', $retry_count = 0, $embedding_namespace = '', $stream = false, $function_result = '', $vision_file = '', $file_data = '') 
{
    $error = '';
    $aicontent = '';
    try
    {
        if(empty($content))
        {
            throw new Exception('Empty array submitted to AI');
        }
        require_once (dirname(__FILE__) . "/res/aiomatic-assistants-api.php"); 
        try
        {
            if (!empty($assistant_id)) 
            {
                $assistant = aiomatic_openai_retrieve_assistant($token, $assistant_id);
                if(!isset($assistant['id']))
                {
                    throw new Exception('Incorrect response from assistant grabbing assistant ID ' . $assistant_id . ': ' . print_r($thread, true));
                }
                else
                {
                    $model = $assistant['model'];
                }
            }
        }
        catch(Exception $e)
        {
            throw new Exception('Failed to query assistant: ' . $e->getMessage());
        }
        $count_vision = false;
        if(!aiomatic_is_vision_model('', $assistant_id))
        {
            $vision_file = '';
        }
        else
        {
            $count_vision = true;
        }
        if(empty($function_result) || $function_result == 'disabled')
        {
            $embeddings_enabled = false;
            $internet_enabled = false;
            $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
            $particular_embedding_namespace = '';
            aiomatic_get_internet_embeddings_result($aiomatic_Main_Settings, $env, $embeddings_enabled, $internet_enabled, $particular_embedding_namespace);
            if(empty($embedding_namespace) && !empty($particular_embedding_namespace))
            {
                $embedding_namespace = $particular_embedding_namespace;
            }
            $user_question = $content;
            if (isset($aiomatic_Main_Settings['first_embeddings']) && $aiomatic_Main_Settings['first_embeddings'] == 'on')
            {
                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                {
                    aiomatic_log_to_file('Getting embedding results for assistants');
                }
                if($no_embeddings !== true && $embeddings_enabled == true && $retry_count == 0)
                {
                    $embed_rez = aiomatic_embeddings_result($content, $token, $embedding_namespace);
                    if($embed_rez['status'] == 'error')
                    {
                        if($embed_rez['data'] != 'No results found' && $embed_rez['data'] != 'No data returned' && $embed_rez['data'] != 'No embeddings are added in the plugin config!')
                        {
                            aiomatic_log_to_file('Embeddings failed for assistant: ' . print_r($embed_rez, true));
                        }
                    }
                    else
                    {
                        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                        {
                            aiomatic_log_to_file('Embeddings results ok');
                        }
                        $content = $embed_rez['data'] . '\n' . $content;
                    }
                }
            }
            if($no_internet !== true && $internet_enabled == true && $retry_count == 0)
            {
                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                {
                    aiomatic_log_to_file('Getting internet search results for assistant');
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
                $internet_rez = aiomatic_internet_result($user_question, false, $locale);
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
                if($internet_results == '')
                {
                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                    {
                        aiomatic_log_to_file('Internet search failed for assistant, no data returned!');
                    }
                }
                else
                {
                    if($internet_prompt != '')
                    {
                        $internet_prompt = str_ireplace('%%original_query%%', $content, $internet_prompt);
                        $internet_prompt = str_ireplace('%%current_date%%', date('Y-m-d'), $internet_prompt);
                        $internet_prompt = str_ireplace('%%web_results%%', $internet_results, $internet_prompt);
                        if($internet_prompt != '')
                        {
                            $content = $internet_prompt . '\n' . $content;
                        }
                    }
                }
            }
            if (!isset($aiomatic_Main_Settings['first_embeddings']) || $aiomatic_Main_Settings['first_embeddings'] != 'on')
            {
                if($no_embeddings !== true && $embeddings_enabled == true && $retry_count == 0)
                {
                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                    {
                        aiomatic_log_to_file('Getting embedding results for assistants');
                    }
                    $embed_rez = aiomatic_embeddings_result($content, $token, $embedding_namespace);
                    if($embed_rez['status'] == 'error')
                    {
                        if($embed_rez['data'] != 'No results found' && $embed_rez['data'] != 'No data returned' && $embed_rez['data'] != 'No embeddings are added in the plugin config!')
                        {
                            aiomatic_log_to_file('Embeddings failed for assistant: ' . print_r($embed_rez, true));
                        }
                    }
                    else
                    {
                        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                        {
                            aiomatic_log_to_file('Embeddings ok');
                        }
                        $content = $embed_rez['data'] . '\n' . $content;
                    }
                }
            }
            $last_message = false;
            try
            {
                if (empty($thread_id)) 
                {
                    $assistant_first_message = '';
                    if(!empty($local_assistant_id))
                    {
                        $assistant_first_message = get_post_meta($local_assistant_id, '_assistant_first_message', true);
                    }
                    if(!empty($assistant_first_message))
                    {
                        $simulate_conv = array();
                        $simulate_conv[] = array("role" => 'assistant', "content" => $assistant_first_message);
                    }
                    else
                    {
                        $simulate_conv = [];
                    }
                    $thread = aiomatic_openai_create_thread($token, $simulate_conv, $file_data);
                    if(!isset($thread['id']))
                    {
                        throw new Exception('Invalid thread format: ' . print_r($thread, true));
                    }
                    $thread_id = $thread['id'];
                }
            }
            catch(Exception $e)
            {
                throw new Exception('Failed to create thread: ' . $e->getMessage());
            }
            $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
            $stop = null;
            $session = aiomatic_get_session_id();
            $mode = 'text';
            $maxResults = 1;
            $temperature = 1;
            $max_tokens = aiomatic_get_max_tokens($model);
            if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
            {
                $prompt_tokens = count(aiomatic_encode($content));
                $available_tokens = $max_tokens - $prompt_tokens;
            }
            else
            {
                $available_tokens = $max_tokens;
            }
            $query = new Aiomatic_Query($content, $available_tokens, $model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, '', $assistant_id);
            $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
            if ( $ok !== true ) {
                throw new Exception($ok);
            }
            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
            {
                aiomatic_log_to_file('Generating chat AI text using assistant: ' . $assistant_id . ' and prompt: ' . $content);
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
                usleep(intval($delay) * 1000);
            }
            
            try
            {
                $message = aiomatic_openai_create_message($token, $thread_id, $role, $content, null, $vision_file);
                if(!isset($message['id']))
                {
                    throw new Exception('Invalid message format: ' . print_r($message, true));
                }
            }
            catch(Exception $e)
            {
                throw new Exception('Failed to create message: ' . $e->getMessage());
            }
            if($stream === false)
            {
                try
                {
                    $run = aiomatic_openai_create_run($token, $thread_id, $assistant_id);
                    if(!isset($run['id']))
                    {
                        throw new Exception('Invalid run format: ' . print_r($run, true));
                    }
                    $run_id = $run['id'];
                }
                catch(Exception $e)
                {
                    throw new Exception('Failed to run thread: ' . $e->getMessage());
                }
                try
                {
                    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
                    {
                        $timeout = intval($aiomatic_Main_Settings['max_timeout']);
                    }
                    else
                    {
                        $timeout = 300;
                    }
                    $start_time = time();
                    do 
                    {
                        $run_status = aiomatic_openai_retrieve_run($token, $thread_id, $run_id);
                        if(!isset($run_status['id']))
                        {
                            throw new Exception('Invalid run_status format: ' . print_r($run_status, true));
                        }
                        if ($run_status['status'] === 'requires_action' && $run_status['required_action']['type'] === 'submit_tool_outputs') 
                        {
                            $last_message_tool = '';
                            try
                            {
                                $tool_outputs = aiomatic_call_required_tool($run_status['required_action']);
                            }
                            catch(Exception $e)
                            {
                                throw new Exception('Failed to call required tool: ' . $e->getMessage());
                            }
                            foreach($tool_outputs as $jinx => $to)
                            {
                                if(isset($to['echo']))
                                {
                                    $last_message_tool .= $to['echo'] . ' ';
                                    unset($to['echo']);
                                }
                            }
                            $last_message_tool = trim($last_message_tool);
                            if(!empty($last_message_tool))
                            {
                                $last_message_tool_ret = array();
                                $last_message_tool_ret['content'][0]['text']['value'] = $last_message_tool;
                                return $last_message_tool_ret;
                            }
                            $run_status = aiomatic_openai_submit_tool_outputs_to_run($token, $thread_id, $run_id, $tool_outputs);
                        }
                        if($run_status['status'] !== 'completed' && $run_status['status'] !== 'failed')
                        {
                            $now_time = time();
                            if($start_time + $timeout < $now_time)
                            {
                                throw new Exception('Timeout in retrive run polling (s): ' . $timeout);
                            }
                            sleep(1);
                        }
                    } while ($run_status['status'] !== 'completed' && $run_status['status'] !== 'failed');
                }
                catch(Exception $e)
                {
                    throw new Exception('Failed to poll thread: ' . $e->getMessage());
                }
                if ($run_status['status'] === 'completed') 
                {
                    try
                    {
                        $messages = aiomatic_openai_list_messages($token, $thread_id);
                        if(!isset($messages['data'][0]['id']))
                        {
                            throw new Exception('Invalid messages format: ' . print_r($messages, true));
                        }
                    }
                    catch(Exception $e)
                    {
                        throw new Exception('Failed to get message: ' . $e->getMessage());
                    }
                    $last_message = reset($messages['data']);
                    $last_message = apply_filters( 'aiomatic_ai_reply_raw', $last_message, $content );
                    apply_filters( 'aiomatic_ai_reply', $last_message, $query );
                    if($count_vision)
                    {
                        $stats = [
                            "env" => $query->env,
                            "session" => $query->session,
                            "mode" => 'image',
                            "model" => $query->model,
                            "apiRef" => $query->apiKey,
                            "units" => 1,
                            "type" => 'images',
                        ];
                        if (empty($stats["price"])) {
                            if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                                $stats["price"] = 0;
                            } else {
                                $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                            }
                        }
                        $GLOBALS['aiomatic_stats']->add($stats);
                    }
                    return $last_message;
                } 
                else 
                {
                    throw new Exception('Run failed or did not complete. ' . print_r($run_status, true));
                }
            }
            else
            {
                try
                {
                    aiomatic_openai_create_stream_run($token, $thread_id, $assistant_id);
                    $last_message = '';
                }
                catch(Exception $e)
                {
                    throw new Exception('Failed to run thread: ' . $e->getMessage());
                }
            }
        }
        else
        {
            try
            {
                $run_id = '';
                $tool_outputs = array();
                if(is_array($function_result))
                {
                    foreach($function_result as $fr)
                    {
                        if(isset($fr['run_id']))
                        {
                            $run_id = $fr['run_id'];
                        }
                        if(isset($fr['thread_id']))
                        {
                            $thread_id = $fr['thread_id'];
                        }
                        $tool_outputs[] = [
                            'tool_call_id' => $fr['tool_call_id'], 
                            'output' => $fr['content']
                        ];
                    }
                }
                if(empty($tool_outputs))
                {
                    throw new Exception('Failed to get results: ' . print_r($function_result, true));
                }
                if(empty($run_id))
                {
                    throw new Exception('Failed to get run_id: ' . print_r($function_result, true));
                }
                if(empty($thread_id))
                {
                    throw new Exception('Failed to get thread_id: ' . print_r($function_result, true));
                }
                aiomatic_openai_submit_tool_outputs_to_stream_run($token, $thread_id, $run_id, $tool_outputs);
                $last_message = '';
            }
            catch(Exception $e)
            {
                throw new Exception('Failed to run thread: ' . $e->getMessage());
            }
        }
    }
    catch(Exception $e)
    {
        $is_error = true;
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') assistants API call after failure: ' . $e->getMessage());
            sleep(pow(2, $retry_count));
            try
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                $assistant_id = apply_filters('aiomatic_assistant_id_fallback', $assistant_id, $error, $aicontent);
                $local_assistant_id = apply_filters('aiomatic_local_assistant_id_id_fallback', $local_assistant_id, $error, $aicontent);
                $content = apply_filters('aiomatic_content_try_fix', $content, $error, $model);
                $thread_id = apply_filters('aiomatic_thread_id_try_fix', $thread_id, $error, $model);
                $last_message = aiomatic_generate_text_assistant($token, $assistant_id, $local_assistant_id, $role, $content, $thread_id, $no_internet, $no_embeddings, $env, intval($retry_count) + 1, $embedding_namespace, $stream, $function_result, $vision_file, $file_data);
                $is_error = false;
            }
            catch(Exception $e)
            {
                aiomatic_log_to_file('Failed to generate text using Assistants API for retry (' . $retry_count . ': ' . $e->getMessage());
            }
        }
        if($is_error === true)
        {
            throw $e;
        }
    }
    return $last_message;
}
function aiomatic_normalise_context( array &$messages,
                                     string $model,
                                     int $reserve = 256 )
{
    $max_ctx = aiomatic_get_max_tokens( $model );
    $encoder = 'aiomatic_encode';
    $prompt_tokens = 0;
    foreach ( $messages as $m ) {
        $prompt_tokens += count( $encoder( $m['role'] ?? '' ) );

        if ( isset( $m['content'] ) && is_array( $m['content'] ) ) {
            foreach ( $m['content'] as $part ) {
                $prompt_tokens += count( $encoder( $part['text'] ?? '' ) );
            }
        } else {
            $prompt_tokens += count( $encoder( $m['content'] ?? '' ) );
        }
    }
    if ( $prompt_tokens + $reserve <= $max_ctx ) {
        return $max_ctx - $prompt_tokens;
    }
    for ( $i = 0; $i < count( $messages ); $i++ ) {
        if ( $messages[$i]['role'] === 'system' ) {
            $messages[$i]['content'] =
                aiomatic_strip_to_token_count( $messages[$i]['content'],
                                               max( 50, $reserve / 2 ) );
        } else {
            unset( $messages[$i] );
        }
        $messages = array_values( $messages );
        $prompt_tokens = 0;
        foreach ( $messages as $m ) {
            $prompt_tokens += count( $encoder( $m['role'] ?? '' ) );
            $prompt_tokens += is_array( $m['content'] )
                ? array_sum( array_map( fn($p) => count( $encoder( $p['text'] ?? '' ) ),
                                        $m['content'] ) )
                : count( $encoder( $m['content'] ?? '' ) );
        }
        if ( $prompt_tokens + $reserve <= $max_ctx ) {
            return $max_ctx - $prompt_tokens;
        }
    }
    $last = count( $messages ) - 1;
    $messages[$last]['content'] =
        aiomatic_strip_to_token_count( $messages[$last]['content'],
                                       $max_ctx - $reserve - 10 );
    return $reserve;
}
function aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, &$finish_reason, &$error, $no_internet = false, $no_embeddings = false, $functions = false, $stream = false, $vision_file = '', $dont_add_vision = false, $user_question = '', $embedding_namespace = '', $function_result = '', $dump_result = false, $store_data = false)
{
    if(!is_array($aicontent))
    {
        $error = 'Only arrays are supported for chat text: ' . $aicontent;
        return false;
    }
    if(empty($aicontent))
    {
        $error = 'Empty array submitted to AI chat';
        return false;
    }
    //remove this after the o1 models will not be any more in beta: https://platform.openai.com/docs/guides/reasoning/beta-limitations
    if(aiomatic_is_o1_model($model))
    {
        foreach($aicontent as $zind => $checkme)
        {
            if($aicontent[$zind]['role'] == 'system')
            {
                $aicontent[$zind]['role'] = 'user';
            }
        }
        $temperature = 1;
        $top_p = 1;
        $presence_penalty = 0;
        $frequency_penalty = 0;
    }
    if(aiomatic_is_gpt5_model($model))
    {
        $temperature = 1;
        $top_p = 1;
        $presence_penalty = 0;
        $frequency_penalty = 0;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $embeddings_enabled = false;
    $internet_enabled = false;
    $max_tokens = aiomatic_get_max_tokens($model);
    $particular_embedding_namespace = '';
    aiomatic_get_internet_embeddings_result($aiomatic_Main_Settings, $env, $embeddings_enabled, $internet_enabled, $particular_embedding_namespace);
    if(empty($embedding_namespace) && !empty($particular_embedding_namespace))
    {
        $embedding_namespace = $particular_embedding_namespace;
    }
    $aitext = '';
    if(!empty($function_result) && $function_result != 'disabled')
    {
        if($is_chat)
        {
            remove_filter('aiomatic_ai_functions', 'aiomatic_add_god_mode', 999);
            $functions = apply_filters('aiomatic_ai_functions', false);
        }
    }
    if(!empty($function_result) && is_array($function_result) && $retry_count == 0)
    {
        $already_added = false;
        foreach($function_result as $find => $fr)
        {
            if(isset($fr['assistant_message']) && !empty($fr['assistant_message']))
            {
                $objectJson = json_encode($fr['assistant_message']);
                $associativeArray = json_decode($objectJson, true);
                if($associativeArray !== null)
                {
                    $associativeArray = aiomatic_convertIntToStrings($associativeArray);
                    if(is_array($associativeArray))
                    {
                        if(isset($associativeArray['tool_calls']) && is_array($associativeArray['tool_calls']))
                        {
                            for($j = 0; $j < count($associativeArray['tool_calls']); $j++)
                            {
                                if(isset($associativeArray['tool_calls'][$j]['function']['arguments']))
                                {
                                    $associativeArray['tool_calls'][$j]['function']['arguments'] = json_encode($associativeArray['tool_calls'][$j]['function']['arguments']);
                                }
                            }
                        }
                        if(!$already_added)
                        {
                            $already_added = true;
                            $aicontent[] = $associativeArray;
                        }
                    }
                }
                unset($function_result[$find]['assistant_message']);
                unset($fr['assistant_message']);
            }
            elseif(isset($fr['assistant_message']) && empty($fr['assistant_message']))
            {
                unset($function_result[$find]['assistant_message']);
                unset($fr['assistant_message']);
            }
            $aicontent[] = aiomatic_convertIntToStrings($fr);
        }
        $available_tokens = aiomatic_normalise_context( $aicontent, $model, AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS );
    }
    foreach($aicontent as $aimess)
    {
        if(isset($aimess['content']))
        {
            if(!is_array($aimess['content']))
            {
                $aitext .= $aimess['content'] . '\n';
            }
            else
            {
                foreach($aimess['content'] as $internalmess)
                {
                    if($internalmess['type'] == 'text')
                    {
                        $aitext .= $internalmess['text'] . '\n';
                    }
                }
            }
        }
    }
    $aitext = rtrim($aitext);
    $prompt_modified = false;
    if (isset($aiomatic_Main_Settings['first_embeddings']) && $aiomatic_Main_Settings['first_embeddings'] == 'on')
    {
        if($no_embeddings !== true && $embeddings_enabled == true && $retry_count == 0)
        {
            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
            {
                aiomatic_log_to_file('Getting embedding results for chat');
            }
            if(empty($user_question))
            {
                $user_question = $aitext;
            }
            $embed_rez = aiomatic_embeddings_result($user_question, $token, $embedding_namespace);
            if($embed_rez['status'] == 'error')
            {
                if($embed_rez['data'] != 'No results found' && $embed_rez['data'] != 'No data returned' && $embed_rez['data'] != 'No embeddings are added in the plugin config!')
                {
                    aiomatic_log_to_file('Embeddings failed for chat: ' . print_r($embed_rez, true));
                }
            }
            else
            {
                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                {
                    aiomatic_log_to_file('Embeddings results ok');
                }
                $prompt_tokens = 0;
                $aicontent_copy = $aicontent;
                $aicontent_copy[0]['content'] = $embed_rez['data'] . '\n' . $aicontent_copy[0]['content'];
                $prompt_tokens = 0;
                //check if there are enough tokens
                foreach($aicontent_copy as $aimess)
                {
                    $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                }
                if(aiomatic_is_chatgpt_turbo_model($model) || aiomatic_is_chatgpt_o_model($model) || aiomatic_is_o1_model($model))
                {
                    if(isset($aiomatic_Main_Settings['gpt4_context_limit']) && $aiomatic_Main_Settings['gpt4_context_limit'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['gpt4_context_limit']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['gpt4_context_limit'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_chatgpt35_16k_context_model($model))
                {
                    if(isset($aiomatic_Main_Settings['gpt35_context_limit']) && $aiomatic_Main_Settings['gpt35_context_limit'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['gpt35_context_limit']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['gpt35_context_limit'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_claude_model_200k($model))
                {
                    if(isset($aiomatic_Main_Settings['claude_context_limit_200k']) && $aiomatic_Main_Settings['claude_context_limit_200k'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit_200k']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['claude_context_limit_200k'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_claude_model($model))
                {
                    if(isset($aiomatic_Main_Settings['claude_context_limit']) && $aiomatic_Main_Settings['claude_context_limit'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['claude_context_limit'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_openrouter_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_openrouter($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_openrouter($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_huggingface_model($model))
                {
                    if($prompt_tokens > AIOMATIC_MAX_HUGGINGFACE_TOKEN_COUNT)
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], AIOMATIC_MAX_HUGGINGFACE_TOKEN_COUNT, true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_ollama_model($model))
                {
                    if($prompt_tokens > AIOMATIC_MAX_OLLAMA_TOKEN_COUNT)
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], AIOMATIC_MAX_OLLAMA_TOKEN_COUNT, true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_perplexity_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_perplexity($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_perplexity($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_groq_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_groq($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_groq($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_nvidia_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_nvidia($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_nvidia($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_xai_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_xai($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_xai($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                else
                {
                    if($max_tokens - AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS > $prompt_tokens || aiomatic_is_new_token_window_model($model))
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                        if($prompt_tokens > aiomatic_get_max_input_tokens($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                            $prompt_tokens = 0;
                            //check if there are enough tokens
                            foreach($aicontent as $aimess)
                            {
                                $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                            }
                        }
                        if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
                        {
                            $available_tokens = $max_tokens - $prompt_tokens;
                        }
                    }
                    else
                    {
                        if(strlen($embed_rez['data']) > 1000)
                        {
                            $embed_rez['data'] = aiomatic_substr($embed_rez['data'], 0, 1000);
                            $aicontent_copy[0]['content'] = $embed_rez['data'] . '\n' . $aicontent_copy[0]['content'];
                            $prompt_tokens = 0;
                            //check if there are enough tokens
                            foreach($aicontent_copy as $aimess)
                            {
                                $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                            }
                        }
                        if($max_tokens - AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS > $prompt_tokens)
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                            $available_tokens = $max_tokens - $prompt_tokens;
                        }
                        else
                        {
                            if($max_tokens - 100 > $prompt_tokens)
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                                $available_tokens = $max_tokens - $prompt_tokens;
                            }
                            else
                            {
                                $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $max_tokens, true);
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                                $prompt_tokens = 0;
                                //check if there are enough tokens
                                foreach($aicontent_copy as $aimess)
                                {
                                    $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                                }
                                $available_tokens = $max_tokens - $prompt_tokens;
                            }
                        }
                    }
                }
            }
        }
    }
    if($no_internet !== true && $internet_enabled == true && $retry_count == 0)
    {
        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
        {
            aiomatic_log_to_file('Getting internet search results for chat');
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
        if(empty($user_question))
        {
            if(is_array($aicontent))
            {
                $arkey = array_keys($aicontent);
                $lastindex = end($arkey);
                if(isset($aicontent[$lastindex]['content']))
                {
                    $user_question = $aicontent[$lastindex]['content'];
                }
            }
        }
        $locale = '';
        if (isset($aiomatic_Main_Settings['internet_gl']) && $aiomatic_Main_Settings['internet_gl'] != '')
        {
            $locale = $aiomatic_Main_Settings['internet_gl'];
        }
        $internet_rez = aiomatic_internet_result($user_question, false, $locale);
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
        if($internet_results == '')
        {
            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
            {
                aiomatic_log_to_file('Internet search failed for chat, no data returned!');
            }
        }
        else
        {
            if($internet_prompt != '')
            {
                $internet_prompt = str_ireplace('%%original_query%%', $aicontent[0]['content'], $internet_prompt);
                $internet_prompt = str_ireplace('%%current_date%%', date('Y-m-d'), $internet_prompt);
                $internet_prompt = str_ireplace('%%web_results%%', $internet_results, $internet_prompt);
                if($internet_prompt != '')
                {
                    $aicontent_copy = $aicontent;
                    $content_save = $aicontent_copy[0]['content'];
                    $aicontent_copy[0]['content'] = $internet_prompt . '\n' . $content_save;
                    $prompt_tokens = 0;
                    //check if there are enough tokens
                    foreach($aicontent_copy as $aimess)
                    {
                        $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                    }
                    if(aiomatic_is_chatgpt_turbo_model($model) || aiomatic_is_chatgpt_o_model($model) || aiomatic_is_o1_model($model))
                    {
                        if(isset($aiomatic_Main_Settings['gpt4_context_limit']) && $aiomatic_Main_Settings['gpt4_context_limit'] != '')
                        {
                            if($prompt_tokens > intval($aiomatic_Main_Settings['gpt4_context_limit']))
                            {
                                $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['gpt4_context_limit'], true);
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                            else
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                        }
                        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_chatgpt35_16k_context_model($model))
                    {
                        if(isset($aiomatic_Main_Settings['gpt35_context_limit']) && $aiomatic_Main_Settings['gpt35_context_limit'] != '')
                        {
                            if($prompt_tokens > intval($aiomatic_Main_Settings['gpt35_context_limit']))
                            {
                                $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['gpt35_context_limit'], true);
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                            else
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                        }
                        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_claude_model_200k($model))
                    {
                        if(isset($aiomatic_Main_Settings['claude_context_limit_200k']) && $aiomatic_Main_Settings['claude_context_limit_200k'] != '')
                        {
                            if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit_200k']))
                            {
                                $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['claude_context_limit_200k'], true);
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                            else
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                        }
                        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_claude_model($model))
                    {
                        if(isset($aiomatic_Main_Settings['claude_context_limit']) && $aiomatic_Main_Settings['claude_context_limit'] != '')
                        {
                            if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit']))
                            {
                                $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['claude_context_limit'], true);
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                            else
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                        }
                        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_openrouter_model($model))
                    {
                        if($prompt_tokens > aiomatic_get_max_input_tokens_openrouter($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_openrouter($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_huggingface_model($model))
                    {
                        if($prompt_tokens > AIOMATIC_MAX_HUGGINGFACE_TOKEN_COUNT)
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], AIOMATIC_MAX_HUGGINGFACE_TOKEN_COUNT, true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_ollama_model($model))
                    {
                        if($prompt_tokens > AIOMATIC_MAX_OLLAMA_TOKEN_COUNT)
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], AIOMATIC_MAX_OLLAMA_TOKEN_COUNT, true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_perplexity_model($model))
                    {
                        if($prompt_tokens > aiomatic_get_max_input_tokens_perplexity($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_perplexity($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_groq_model($model))
                    {
                        if($prompt_tokens > aiomatic_get_max_input_tokens_groq($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_groq($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_nvidia_model($model))
                    {
                        if($prompt_tokens > aiomatic_get_max_input_tokens_nvidia($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_nvidia($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif(aiomatic_is_xai_model($model))
                    {
                        if($prompt_tokens > aiomatic_get_max_input_tokens_xai($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_xai($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    else
                    {
                        if($max_tokens - AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS > $prompt_tokens || aiomatic_is_new_token_window_model($model))
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                            if($prompt_tokens > aiomatic_get_max_input_tokens($model))
                            {
                                $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                                $prompt_tokens = 0;
                                //check if there are enough tokens
                                foreach($aicontent as $aimess)
                                {
                                    $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                                }
                                if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
                                {
                                    $available_tokens = $max_tokens - $prompt_tokens;
                                }
                            }
                            else
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                            }
                        }
                        else
                        {
                            if(strlen($internet_prompt) > 1000)
                            {
                                $internet_prompt = aiomatic_substr($internet_prompt, 0, 1000);
                                $aicontent_copy[0]['content'] = $internet_prompt . '\n' . $aicontent_copy[0]['content'];
                                $prompt_tokens = 0;
                                //check if there are enough tokens
                                foreach($aicontent_copy as $aimess)
                                {
                                    $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                                }
                            }
                            if($max_tokens - AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS > $prompt_tokens)
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                                $available_tokens = $max_tokens - $prompt_tokens;
                            }
                            else
                            {
                                if($max_tokens - 100 > $prompt_tokens)
                                {
                                    $aicontent = $aicontent_copy;
                                    $prompt_modified = true;
                                    $available_tokens = $max_tokens - $prompt_tokens;
                                }
                                else
                                {
                                    $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $max_tokens, true);
                                    $aicontent = $aicontent_copy;
                                    $prompt_modified = true;
                                    $prompt_tokens = 0;
                                    //check if there are enough tokens
                                    foreach($aicontent_copy as $aimess)
                                    {
                                        $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                                    }
                                    $available_tokens = $max_tokens - $prompt_tokens;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if (!isset($aiomatic_Main_Settings['first_embeddings']) || $aiomatic_Main_Settings['first_embeddings'] != 'on')
    {
        if($no_embeddings !== true && $embeddings_enabled == true && $retry_count == 0)
        {
            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
            {
                aiomatic_log_to_file('Getting embedding results for chat');
            }
            if(empty($user_question))
            {
                $user_question = $aitext;
            }
            $embed_rez = aiomatic_embeddings_result($user_question, $token, $embedding_namespace);
            if($embed_rez['status'] == 'error')
            {
                if($embed_rez['data'] != 'No results found' && $embed_rez['data'] != 'No data returned' && $embed_rez['data'] != 'No embeddings are added in the plugin config!')
                {
                    aiomatic_log_to_file('Embeddings failed for chat: ' . print_r($embed_rez, true));
                }
            }
            else
            {
                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                {
                    aiomatic_log_to_file('Getting ok');
                }
                $prompt_tokens = 0;
                $aicontent_copy = $aicontent;
                $aicontent_copy[0]['content'] = $embed_rez['data'] . '\n' . $aicontent_copy[0]['content'];
                $prompt_tokens = 0;
                //check if there are enough tokens
                foreach($aicontent_copy as $aimess)
                {
                    $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                }
                if(aiomatic_is_chatgpt_turbo_model($model) || aiomatic_is_chatgpt_o_model($model) || aiomatic_is_o1_model($model))
                {
                    if(isset($aiomatic_Main_Settings['gpt4_context_limit']) && $aiomatic_Main_Settings['gpt4_context_limit'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['gpt4_context_limit']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['gpt4_context_limit'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_chatgpt35_16k_context_model($model))
                {
                    if(isset($aiomatic_Main_Settings['gpt35_context_limit']) && $aiomatic_Main_Settings['gpt35_context_limit'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['gpt35_context_limit']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['gpt35_context_limit'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_claude_model_200k($model))
                {
                    if(isset($aiomatic_Main_Settings['claude_context_limit_200k']) && $aiomatic_Main_Settings['claude_context_limit_200k'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit_200k']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['claude_context_limit_200k'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_claude_model($model))
                {
                    if(isset($aiomatic_Main_Settings['claude_context_limit']) && $aiomatic_Main_Settings['claude_context_limit'] != '')
                    {
                        if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit']))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $aiomatic_Main_Settings['claude_context_limit'], true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                        else
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                        }
                    }
                    elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_openrouter_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_openrouter($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_openrouter($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_huggingface_model($model))
                {
                    if($prompt_tokens > AIOMATIC_MAX_HUGGINGFACE_TOKEN_COUNT)
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], AIOMATIC_MAX_HUGGINGFACE_TOKEN_COUNT, true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_ollama_model($model))
                {
                    if($prompt_tokens > AIOMATIC_MAX_OLLAMA_TOKEN_COUNT)
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], AIOMATIC_MAX_OLLAMA_TOKEN_COUNT, true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_perplexity_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_perplexity($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_perplexity($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_groq_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_groq($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_groq($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_nvidia_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_nvidia($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_nvidia($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                elseif(aiomatic_is_xai_model($model))
                {
                    if($prompt_tokens > aiomatic_get_max_input_tokens_xai($model))
                    {
                        $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens_xai($model), true);
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                    else
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                    }
                }
                else
                {
                    if($max_tokens - AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS > $prompt_tokens || aiomatic_is_new_token_window_model($model))
                    {
                        $aicontent = $aicontent_copy;
                        $prompt_modified = true;
                        if($prompt_tokens > aiomatic_get_max_input_tokens($model))
                        {
                            $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], aiomatic_get_max_input_tokens($model), true);
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                            $prompt_tokens = 0;
                            //check if there are enough tokens
                            foreach($aicontent as $aimess)
                            {
                                $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                            }
                        }
                        if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
                        {
                            $available_tokens = $max_tokens - $prompt_tokens;
                        }
                    }
                    else
                    {
                        if(strlen($embed_rez['data']) > 1000)
                        {
                            $embed_rez['data'] = aiomatic_substr($embed_rez['data'], 0, 1000);
                            $aicontent_copy[0]['content'] = $embed_rez['data'] . '\n' . $aicontent_copy[0]['content'];
                            $prompt_tokens = 0;
                            //check if there are enough tokens
                            foreach($aicontent_copy as $aimess)
                            {
                                $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                            }
                        }
                        if($max_tokens - AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS > $prompt_tokens)
                        {
                            $aicontent = $aicontent_copy;
                            $prompt_modified = true;
                            $available_tokens = $max_tokens - $prompt_tokens;
                        }
                        else
                        {
                            if($max_tokens - 100 > $prompt_tokens)
                            {
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                                $available_tokens = $max_tokens - $prompt_tokens;
                            }
                            else
                            {
                                $aicontent_copy[0]['content'] = aiomatic_strip_to_token_count($aicontent_copy[0]['content'], $max_tokens, true);
                                $aicontent = $aicontent_copy;
                                $prompt_modified = true;
                                $prompt_tokens = 0;
                                //check if there are enough tokens
                                foreach($aicontent_copy as $aimess)
                                {
                                    $prompt_tokens += count(aiomatic_encode($aimess['content'])) + count(aiomatic_encode($aimess['role']));
                                }
                                $available_tokens = $max_tokens - $prompt_tokens;
                            }
                        }
                    }
                }
            }
        }
    }
    if($prompt_modified == true)
    {
        $aitext = '';
        foreach($aicontent as $aimess)
        {
            if(isset($aimess['content']))
            {
                if(!is_array($aimess['content']))
                {
                    $aitext .= $aimess['content'] . '\n';
                }
                else
                {
                    foreach($aimess['content'] as $internalmess)
                    {
                        if($internalmess['type'] == 'text')
                        {
                            $aitext .= $internalmess['text'] . '\n';
                        }
                    }
                }
            }
        }
    }
    $aitext = rtrim($aitext);
    if(aiomatic_check_if_available_token_recalc_needed($model, $aiomatic_Main_Settings))
    {
        $content_tokens = count(aiomatic_encode($aitext));
        $total_tokens = $content_tokens + $available_tokens;
        if($total_tokens >= $max_tokens && !aiomatic_is_new_token_window_model($model))
        {
            $available_tokens = $max_tokens - $content_tokens;
            if($available_tokens < AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
            {
                $string_len = strlen($aicontent[0]['content']);
                $string_len = $string_len / 2;
                $string_len = intval(0 - $string_len);
                $aicontent[0]['content'] = aiomatic_substr($aicontent[0]['content'], 0, $string_len);
                $aicontent[0]['content'] = trim($aicontent[0]['content']);
                $aitext = '';
                foreach($aicontent as $aimess)
                {
                    if(isset($aimess['content']))
                    {
                        if(!is_array($aimess['content']))
                        {
                            $aitext .= $aimess['content'] . '\n';
                        }
                        else
                        {
                            foreach($aimess['content'] as $internalmess)
                            {
                                if($internalmess['type'] == 'text')
                                {
                                    $aitext .= $internalmess['text'] . '\n';
                                }
                            }
                        }
                    }
                }
                $query_token_count = count(aiomatic_encode($aitext));
                $available_tokens = $max_tokens - $query_token_count;
            }
        }
    }
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'text';
    $maxResults = 1;
    $query = new Aiomatic_Query($aitext, $available_tokens, $model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, '', '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = $ok;
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
    {
        aiomatic_log_to_file('Generating chat AI text using model: ' . $model . ' and prompt: ' . $aitext);
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
        usleep(intval($delay) * 1000);
    }
    if($temperature < 0 || $temperature > 1)
    {
        $temperature = 1;
    }
    if($top_p < 0 || $top_p > 1)
    {
        $top_p = 1;
    }
    if($presence_penalty < -2 || $presence_penalty > 2)
    {
        $presence_penalty = 0;
    }
    if($frequency_penalty < -2 || $frequency_penalty > 2)
    {
        $frequency_penalty = 0;
    }
    if($temperature == '' || (empty($temperature) && $temperature !== 0))
    {
        $temperature = 1;
    }
    if($top_p == '' || (empty($top_p) && $top_p !== 0))
    {
        $top_p = 1;
    }
    if($presence_penalty == '' || (empty($presence_penalty) && $presence_penalty !== 0))
    {
        $presence_penalty = 0;
    }
    if($frequency_penalty == '' || (empty($frequency_penalty) && $frequency_penalty !== 0))
    {
        $frequency_penalty = 0;
    }
    $count_vision = false;
    if(aiomatic_check_if_azure($aiomatic_Main_Settings, $model) || aiomatic_is_vision_groq_model($model) || aiomatic_is_vision_xai_model($model) || (!aiomatic_check_if_azure_or_others($aiomatic_Main_Settings, $model) && aiomatic_is_vision_model($model, '') && !aiomatic_is_ollama_model($model)))
    {
        if($dont_add_vision == false && $vision_file != '' && $retry_count == 0)
        {
            $base64_vision = '';
            foreach(array_reverse($aicontent, true) as $ind => $indval)
            {
                if($indval['role'] != 'user')
                {
                    continue;
                }
                $xcopy = $aicontent[$ind]['content'];
                if(stristr($vision_file, 'http://localhost/') || stristr($vision_file, 'https://localhost/'))
                {
                    $base64_vision = aiomatic_get_base64_from_url($vision_file);
                }
                if(!empty($base64_vision))
                {
                    $xacontent = [
                        [ "type" => "text", "text" => $xcopy ],
                        [ "type" => "image_url", "image_url" => [ "url" => "data:image/jpeg;base64," . $base64_vision, "detail" => "low" ] ]
                    ];
                }
                else
                {
                    $xacontent = [
                        [ "type" => "text", "text" => $xcopy ],
                        [ "type" => "image_url", "image_url" => [ "url" => $vision_file, "detail" => "low" ] ]
                    ];
                }
                $aicontent[$ind]['content'] = $xacontent;
                $count_vision = true;
                break;
            }
        }
    }
    if(aiomatic_is_aiomaticapi_key($token))
    {
        $pargs = array();
        $api_url = 'https://aiomaticapi.com/apis/ai/v1/chat/';
        $pargs['model'] = trim($model);
        $pargs['temperature'] = $temperature;
        $pargs['top_p'] = $top_p;
        $pargs['presence_penalty'] = $presence_penalty;
        $pargs['frequency_penalty'] = $frequency_penalty;
        $pargs['messages'] = $aicontent;
        if($model == 'gpt-4o-mini-search-preview' || $model == 'gpt-4o-search-preview')
        {
            unset($pargs['temperature']);
            unset($pargs['top_p']);
            unset($pargs['presence_penalty']);
            unset($pargs['frequency_penalty']);
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            $pargs['stream'] = true;
        }
        if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
        {
            if(aiomatic_is_o1_model($model))
            {
                $pargs['max_completion_tokens'] = $available_tokens;
            }
            else
            {
                $pargs['max_tokens'] = $available_tokens;
            }
        }
        if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
        {
            //currently tool choice is not supported in o1 models
            if(!aiomatic_is_o1_model($model))
            {
                $pargs['tools'] = (array) $functions['functions'];
                $pargs['tool_choice'] = 'auto';
            }
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            add_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        $api_headers = array(
            'Authorization' => 'Bearer ' . trim($token)
        );  
        $ai_response = aiomatic_get_web_page_api($api_url, $pargs, $api_headers);
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        if($ai_response === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get AiomaticAPI response!';
                return false;
            }
        }
        $ai_json = json_decode($ai_response);
        if(!$stream)
        {
            if($ai_json === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                }
                else
                {
                    $error = 'Error: Failed to decode AiomaticAPI response: ' . $ai_response;
                    return false;
                }
            }
        }
        if(isset($ai_json->error))
        {
            if (stristr($ai_json->error, 'Your subscription expired, please renew it.') === false && stristr($ai_json->error, '[RATE LIMITED]') === false && isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after error failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error while processing AI response: ' . $ai_json->error;
                return false;
            }
        }
        if(!$stream)
        {
            if((isset($ai_json->finish_reason) && $ai_json->finish_reason == 'tool_calls') || (isset($ai_json->finish_details->type) && $ai_json->finish_details->type == 'tool_calls'))
            {
                if(isset($functions['message']))
                {
                    $ai_json->result->content = $functions['message'];
                }
                else
                {
                    $ai_json->result->content = 'OK';
                }
            }
            if(!isset($ai_json->result->content))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after parse failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                }
                else
                {
                    $error = 'Error: Failed to parse AiomaticAPI response: ' . $ai_response;
                    return false;
                }
            }
            if(isset($ai_json->remainingtokens))
            {
                set_transient('aiomaticapi_tokens', $ai_json->remainingtokens, 86400);
            }
            $ai_json = apply_filters( 'aiomatic_ai_reply_raw', $ai_json, $aicontent );
            apply_filters( 'aiomatic_ai_reply', $ai_json->result, $query );
            if($count_vision)
            {
                $stats = [
                    "env" => $query->env,
                    "session" => $query->session,
                    "mode" => 'image',
                    "model" => $query->model,
                    "apiRef" => $query->apiKey,
                    "units" => 1,
                    "type" => 'images',
                ];
                if (empty($stats["price"])) {
                    if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                        $stats["price"] = 0;
                    } else {
                        $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                    }
                }
                $GLOBALS['aiomatic_stats']->add($stats);
            }
            return $ai_json->result->content;
        }
        else
        {
            return '';
        }
    }
    elseif (aiomatic_is_claude_model($model)) 
    {
        if(in_array($model, AIOMATIC_CLAUDE_MODELS) === false)
        {
            $error = 'This model is not currently supported by Claude API: ' . $model;
            return false;
        }
        if (!isset($aiomatic_Main_Settings['app_id_claude']) || trim($aiomatic_Main_Settings['app_id_claude']) == '')
        {
            aiomatic_log_to_file('You need to enter an Anthropic Claude API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_claude = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_claude']));
        $appids_claude = array_filter($appids_claude);
        $token_claude = $appids_claude[array_rand($appids_claude)];
        if($retry_count > 0)
        {
            $token_claude = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_claude']), $token);
        }
        $result = aiomatic_generate_text_local_claude($token_claude, $model, $aicontent, $temperature, $top_p, $vision_file, $available_tokens, $stream, $is_chat, $error);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Claude chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token_claude, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial Claude chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif(aiomatic_is_huggingface_model($model))
    {
        if(!isset($aiomatic_Main_Settings['app_id_huggingface']) || $aiomatic_Main_Settings['app_id_huggingface'] == '')
        {
            $error = apply_filters('aiomatic_modify_ai_error', 'You need to add a HuggingFace API key for this to work.');
            return false;
        }
        else
        {
            $appids_hugging = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_huggingface']));
            $appids_hugging = array_filter($appids_hugging);
            $token_hugging = $appids_hugging[array_rand($appids_hugging)];
            $error = '';
            $available_tokens = 2000;
            if($retry_count > 0)
            {
                $token_hugging = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_huggingface']), $token);
            }
            $response_text = aiomatic_generate_text_huggingface($token_hugging, $model, $aicontent, $env, $temperature, $top_p, $available_tokens, $stream, $error);
            if(empty($response_text))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') HuggingFace chat API call after initial failure: ' . print_r($error, true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_chat($token_hugging, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                }
                else
                {
                    $error = apply_filters('aiomatic_modify_ai_error', 'Error in HuggingFace: ' . $error);
                    return false;
                }
            }
            else
            {
                if($response_text === false || empty($response_text))
                {
                    $error = apply_filters('aiomatic_modify_ai_error', 'Response empty ' . $error);
                    return false;
                }
                return $response_text;
            }
        }
    }
    elseif(aiomatic_is_ollama_model($model))
    {
        if(!isset($aiomatic_Main_Settings['ollama_url']) || $aiomatic_Main_Settings['ollama_url'] == '')
        {
            $error = apply_filters('aiomatic_modify_ai_error', 'You need to add an Ollama API URL in plugin settings, for this to work.');
            return false;
        }
        else
        {
            $ollama_url = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['ollama_url']));
            $ollama_url = array_filter($ollama_url);
            $ollama_url = $ollama_url[array_rand($ollama_url)];
            $ollama_url = rtrim(trim($ollama_url), '/');
            $error = '';
            $available_tokens = 4000;
            $response_text = aiomatic_generate_text_ollama($ollama_url, $model, $aicontent, $env, $temperature, $top_p, $functions, $vision_file, $available_tokens, $stream, $error);
            if(!$stream)
            {
                if(empty($response_text))
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Ollama chat API call after initial failure: ' . print_r($error, true));
                        sleep(pow(2, $retry_count));
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                    }
                    else
                    {
                        $error = apply_filters('aiomatic_modify_ai_error', 'Error in Ollama: ' . $error);
                        return false;
                    }
                }
                else
                {
                    if($response_text === false || empty($response_text))
                    {
                        $error = apply_filters('aiomatic_modify_ai_error', 'Response empty ' . $error);
                        return false;
                    }
                    return $response_text;
                }
            }
            else
            {
                return $response_text;
            }
        }
    }
    elseif (aiomatic_is_openrouter_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_openrouter']) || trim($aiomatic_Main_Settings['app_id_openrouter']) == '')
        {
            aiomatic_log_to_file('You need to enter an OpenRouter API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_openrouter = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_openrouter']));
        $appids_openrouter = array_filter($appids_openrouter);
        $token_openrouter = $appids_openrouter[array_rand($appids_openrouter)];
        if($retry_count > 0)
        {
            $token_openrouter = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_openrouter']), $token);
        }
        $result = aiomatic_generate_text_openrouter($token_openrouter, $model, $aicontent, $temperature, $top_p, $vision_file, $available_tokens, $stream, $error);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') OpenRouter chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token_openrouter, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial OpenRouter chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_perplexity_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_perplexity']) || trim($aiomatic_Main_Settings['app_id_perplexity']) == '')
        {
            aiomatic_log_to_file('You need to enter an PerplexityAI API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_perplexity = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_perplexity']));
        $appids_perplexity = array_filter($appids_perplexity);
        $token_perplexity = $appids_perplexity[array_rand($appids_perplexity)];
        if($retry_count > 0)
        {
            $token_perplexity = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
        }
        $result = aiomatic_generate_text_perplexity($token_perplexity, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') PerplexityAI chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token_perplexity, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial PerplexityAI chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_groq_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_groq']) || trim($aiomatic_Main_Settings['app_id_groq']) == '')
        {
            aiomatic_log_to_file('You need to enter an Groq API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_groq = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_groq']));
        $appids_groq = array_filter($appids_groq);
        $token_groq = $appids_groq[array_rand($appids_groq)];
        if($retry_count > 0)
        {
            $token_groq = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_groq']), $token);
        }
        $result = aiomatic_generate_text_groq($token_groq, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Groq chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token_groq, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial Groq chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_nvidia_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_nvidia']) || trim($aiomatic_Main_Settings['app_id_nvidia']) == '')
        {
            aiomatic_log_to_file('You need to enter an Nvidia API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_nvidia = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_nvidia']));
        $appids_nvidia = array_filter($appids_nvidia);
        $token_nvidia = $appids_nvidia[array_rand($appids_nvidia)];
        if($retry_count > 0)
        {
            $token_nvidia = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_nvidia']), $token);
        }
        $result = aiomatic_generate_text_nvidia($token_nvidia, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Nvidia chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token_nvidia, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial Nvidia chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_xai_model($model)) 
    {
        if (!isset($aiomatic_Main_Settings['app_id_xai']) || trim($aiomatic_Main_Settings['app_id_xai']) == '')
        {
            aiomatic_log_to_file('You need to enter an xAI API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_xai = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_xai']));
        $appids_xai = array_filter($appids_xai);
        $token_xai = $appids_xai[array_rand($appids_xai)];
        if($retry_count > 0)
        {
            $token_xai = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_xai']), $token);
        }
        $result = aiomatic_generate_text_xai($token_xai, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') xAI chat API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token_xai, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial xAI chat API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_is_google_model($model)) 
    {
        if(in_array($model, AIOMATIC_GOOGLE_MODELS) === false)
        {
            $error = 'This model is not currently supported by Google API: ' . $model;
            return false;
        }
        if (!isset($aiomatic_Main_Settings['app_id_google']) || trim($aiomatic_Main_Settings['app_id_google']) == '')
        {
            aiomatic_log_to_file('You need to enter an Google AI Studio API key in the plugin settings for this feature to work!');
            return false;
        }
        $appids_google = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_google']));
        $appids_google = array_filter($appids_google);
        $token_google = $appids_google[array_rand($appids_google)];
        if($retry_count > 0)
        {
            $token_google = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_google']), $token);
        }
        $result = aiomatic_generate_text_google_chat($token_google, $model, $aicontent, $temperature, $top_p, $vision_file, $functions, $function_result, $available_tokens, $stream, $error);
        if($result === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Google API call after initial failure: ' . print_r($error, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token_google, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial Google API response: ' . print_r($error, true);
                return false;
            }
        }
        else
        {
            if($stream === false)
            {
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result, $query );
                $finish_reason = 'stop';
                return $result;
            }
            else
            {
                return $result;
            }
        }
    }
    elseif (aiomatic_check_if_azure($aiomatic_Main_Settings)) 
    {
        if (!isset($aiomatic_Main_Settings['azure_endpoint']) || trim($aiomatic_Main_Settings['azure_endpoint']) == '') 
        {
            $error = 'You need to enter an Azure Endpoint for this to work!';
            return false;
        }
        if(in_array($model, AIOMATIC_AZURE_MODELS) === false)
        {
            $error = 'This model is not currently supported by Azure API: ' . $model;
            return false;
        }
        if(aiomatic_is_trained_model($model))
        {
            $error = 'Fine-tuned models are not supported for Azure API';
            return false;
        }
        if($retry_count > 0)
        {
            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
        }
        $localAzureDeployments = array();
        $depl_arr = aiomatic_get_deployments($token);
        if(is_array($depl_arr))
        {
            foreach($depl_arr as $dar)
            {
                if(empty($dar))
                {
                    continue;
                }
                if(is_string($dar))
                {
                    $localAzureDeployments[trim($dar)] = trim($dar);
                }
                else
                {
                    $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                }
            }
        }
        $azureDeployment = '';
        foreach ( $localAzureDeployments as $dmodel => $dname ) 
        {
            if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) 
            {
                $azureDeployment = $dname;
                break;
            }
        }
        if ( $azureDeployment == '' ) 
        {
            $new_dep = aiomatic_update_deployments_azure($token);
            if($new_dep !== false)
            {
                $localAzureDeployments = array();
                foreach($new_dep as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
                foreach ( $localAzureDeployments as $dmodel => $dname ) 
                {
                    if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                        $azureDeployment = $dname;
                        break;
                    }
                }
            }
            if ( $azureDeployment == '' ) 
            {
                $error = 'No added Azure deployment found for chat model: ' . $model . ' - you need to add this model in your Azure Portal as a Deployment';
                return false;
            }
        }
        if (isset($aiomatic_Main_Settings['azure_api_selector']) && $aiomatic_Main_Settings['azure_api_selector'] != '' && $aiomatic_Main_Settings['azure_api_selector'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_API_VERSION;
        }
        $apiurl = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/deployments/' . $azureDeployment . '/chat/completions' . $api_ver;
        $base_params = [
            'model' => str_replace('.', '', $model),
            'messages' => $aicontent,
            'temperature' => $temperature,
            'top_p' => $top_p,
            'presence_penalty' => $presence_penalty,
            'frequency_penalty' => $frequency_penalty
        ];
        if($model == 'gpt-4o-mini-search-preview' || $model == 'gpt-4o-search-preview')
        {
            unset($base_params['temperature']);
            unset($base_params['top_p']);
            unset($base_params['presence_penalty']);
            unset($base_params['frequency_penalty']);
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            $base_params['stream'] = true;
        }
        if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
        {
            if(aiomatic_is_o1_model($model))
            {
                $base_params['max_completion_tokens'] = $available_tokens;
            }
            else
            {
                $base_params['max_tokens'] = $available_tokens;
            }
        }
        if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
        {
            //currently tool choice is not supported in o1 models
            if(!aiomatic_is_o1_model($model))
            {
                $base_params['tools'] = $functions['functions'];
                $base_params['tool_choice'] = 'auto';
            }
        }
        try
        {
            $send_json = aiomatic_safe_json_encode($base_params);
        }
        catch(Exception $e)
        {
            $error = 'Error: Exception in chat API payload encoding: ' . print_r($e->getMessage(), true);
            return false;
        }
        if($send_json === false)
        {
            $error = 'Error: Failed to encode chat API payload: ' . print_r($base_params, true);
            return false;
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            add_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        add_action('http_api_curl', 'aiomatic_add_proxy');
        $api_call = wp_remote_post(
            $apiurl,
            array(
                'headers' => array( 'Content-Type' => 'application/json', 'api-key' => $token ),
                'body'        => $send_json,
                'method'      => 'POST',
                'data_format' => 'body',
                'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
            )
        );
        remove_action('http_api_curl', 'aiomatic_add_proxy');
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        if(is_wp_error( $api_call ))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Azure chat API call after initial failure: ' . print_r($api_call, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
                return false;
            }
        }
        else
        {
            $result = json_decode( $api_call['body'] );
            if(!aiomatic_check_if_azure($aiomatic_Main_Settings))
            {
                if($result === null)
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after decode failure(1): ' . print_r($api_call['body'], true));
                        sleep(pow(2, $retry_count));
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                    }
                    else
                    {
                        $error = 'Error: Failed to decode initial chat API response(1): ' . print_r($api_call, true);
                        return false;
                    }
                }
                $is_error = false;
                $sleep_time = false;
                if(isset($result->error))
                {
                    $result = $result->error;
                    $is_error = true;
                }
                if($is_error && isset($result->type))
                {
                    if($result->type == 'insufficient_quota')
                    {
                        $error = 'Error: You exceeded your Azure OpenAI quota limit, please wait a period for the quota to refill (chat initial call).';
                        return false;
                    }
                    elseif($result->type == 'invalid_request_error')
                    {
                        $error = 'Error: Invalid request submitted to the Azure chat API, result: ' . print_r($result, true);
                        return false;
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                        {
                            if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                            {
                                $errmessage = $result->message;
                                preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                                if(isset($htmlrez[1][0]))
                                {
                                    $sleep_time = ceil(floatval($htmlrez[1][0]));
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                                    }
                                    sleep($sleep_time);
                                }
                            }
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Azure chat API call after type failure: ' . print_r($api_call['body'], true));
                            if($sleep_time === false)
                            {
                                sleep(pow(2, $retry_count));
                            }
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                        }
                        else
                        {
                            $error = 'Error: An error occurred when initially calling OpenAI chat API: ' . print_r($result, true);
                            return false;
                        }
                    }
                }
            }
            if(!$stream)
            {
                if((isset($result->choices[0]->finish_reason) && $result->choices[0]->finish_reason == 'tool_calls') || (isset($result->choices[0]->finish_details->type) && $result->choices[0]->finish_details->type == 'tool_calls'))
                {
                    if(isset($functions['message']))
                    {
                        $result->choices[0]->message->content = $functions['message'];
                    }
                    else
                    {
                        $result->choices[0]->message->content = 'OK';
                    }
                }
                if(!isset($result->choices[0]->message->content))
                {
                    delete_option('aiomatic_deployments_list');
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after choices failure: ' . print_r($api_call['body'], true));
                        sleep(pow(2, $retry_count));
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                    }
                    else
                    {
                        if(isset($result->code) && $result->code == 'content_filter')
                        {
                            $error = 'Error: The response was filtered by our content management policy.';
                            return false;
                        }
                        else
                        {
                            $error = 'Error: Choices not found in initial Azure chat API result: ' . print_r($result, true);
                            return false;
                        }
                    }
                }
                else
                {
                    if(isset($result->choices[0]->message->tool_calls))
                    {
                        $result->tool_calls = $result->choices[0]->message->tool_calls;
                    }
                    $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                    apply_filters( 'aiomatic_ai_reply', $result->choices[0]->message->content, $query );
                    if(isset($result->choices[0]->finish_reason))
                    {
                        $finish_reason = $result->choices[0]->finish_reason;
                    }
                    else
                    {
                        $finish_reason = $result->choices[0]->finish_details->type;
                    }
                    if($is_chat == true)
                    {
                        if (empty($result->choices[0]->message->content) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                        {
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                        }
                        else
                        {
                            if (empty($result->choices[0]->message->content))
                            {
                                $error = 'Error: No content found in the response(1) ' . print_r($result, true);
                                return false;
                            }
                            else
                            {
                                if($count_vision)
                                {
                                    $stats = [
                                        "env" => $query->env,
                                        "session" => $query->session,
                                        "mode" => 'image',
                                        "model" => $query->model,
                                        "apiRef" => $query->apiKey,
                                        "units" => 1,
                                        "type" => 'images',
                                    ];
                                    if (empty($stats["price"])) {
                                        if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                                            $stats["price"] = 0;
                                        } else {
                                            $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                        }
                                    }
                                    $GLOBALS['aiomatic_stats']->add($stats);
                                }
                                if($dump_result === true)
                                {
                                    return $result;
                                }
                                return $result->choices[0]->message->content;
                            }
                        }
                    }
                    else
                    {
                        if (empty($result->choices[0]->message->content))
                        {
                            $error = 'Error: No content found in the response(2) ' . print_r($result, true);
                            return false;
                        }
                        else
                        {
                            if($count_vision)
                            {
                                $stats = [
                                    "env" => $query->env,
                                    "session" => $query->session,
                                    "mode" => 'image',
                                    "model" => $query->model,
                                    "apiRef" => $query->apiKey,
                                    "units" => 1,
                                    "type" => 'images',
                                ];
                                if (empty($stats["price"])) {
                                    if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                                        $stats["price"] = 0;
                                    } else {
                                        $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                    }
                                }
                                $GLOBALS['aiomatic_stats']->add($stats);
                            }
                            return $result->choices[0]->message->content;
                        }
                    }
                }
            }
            else
            {
                return $result;
            }
        }
    }
    else
    {
        //using the new Responses API o1-pro supported only in responses
        if(( aiomatic_is_o1_pro_model($model) || (isset($aiomatic_Main_Settings['responses_api']) && $aiomatic_Main_Settings['responses_api'] == 'on')) && (!isset($aiomatic_Main_Settings['api_selector']) || trim($aiomatic_Main_Settings['api_selector']) == 'openai') )
        {
            $instructions = null;
            $result = aiomatic_generate_response_api($token, $model, $aicontent, null, $temperature, $top_p, $stream, $instructions, null, $store_data, $functions, $query, 0, $error);
            if($result === false)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') responses API call');
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                }
                else
                {
                    $error = 'Error: Responses API failed: ' . $error;
                    return false;
                }
            }
            if(!$stream)
            {
                if(isset($result->output) && is_array($result->output))
                {
                    foreach($result->output as $rkey => $rvalue)
                    {
                        if((isset($result->output[$rkey]->finish_reason) && $result->output[$rkey]->finish_reason == 'tool_calls') || (isset($result->output[$rkey]->finish_details->type) && $result->output[$rkey]->finish_details->type == 'tool_calls'))
                        {
                            if(isset($functions['message']))
                            {
                                $result->output[$rkey]->content[0]->text = $functions['message'];
                            }
                            else
                            {
                                $result->output[$rkey]->content[0]->text = 'OK';
                            }
                        }
                    }
                    $current_cont = 0;
                    if(isset($result->output[$current_cont]->type) && $result->output[$current_cont]->type == 'reasoning')
                    {
                        $current_cont++;
                    }
                    if(!isset($result->output[$current_cont]->content[0]->text) && !isset($result->aiomatic_tool_direct_message) && !isset($result->aiomatic_tool_end_conversation) && !isset($result->aiomatic_tool_results))
                    {
                        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                        {
                            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') responses API call after output failure');
                            sleep(pow(2, $retry_count));
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                        }
                        else
                        {
                            if(isset($result->code) && $result->code == 'content_filter')
                            {
                                $error = 'Error: The response was filtered by our content management policy.';
                                return false;
                            }
                            else
                            {
                                $error = 'Error: output not found in initial OpenAI responses API result: ' . print_r($result, true);
                                return false;
                            }
                        }
                    }
                    else
                    {
                        if(isset($result->output[$current_cont]->content[0]->tool_calls))
                        {
                            $result->tool_calls = $result->output[$current_cont]->content[0]->tool_calls;
                        }
                        $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                        if(isset($result->output[$current_cont]->content[0]->text))
                        {
                            apply_filters( 'aiomatic_ai_reply', $result->output[$current_cont]->content[0]->text, $query );
                        }
                        $finish_reason = 'completed';
                        if($is_chat == true)
                        {
                            if($dump_result === true)
                            {
                                return $result;
                            }
                            if (empty($result->output[$current_cont]->content[0]->text) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                            {
                                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') responses API call after AI writer ended conversation.');
                                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                                return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                            }
                            else
                            {
                                if (!isset($result->aiomatic_tool_direct_message) && !isset($result->aiomatic_tool_end_conversation) && !isset($result->aiomatic_tool_results) && empty($result->output[$current_cont]->content[0]->text))
                                {
                                    $error = 'Error: No content found in the response(3) ' . print_r($result, true);
                                    return false;
                                }
                                else
                                {
                                    if($count_vision)
                                    {
                                        $stats = [
                                            "env" => $query->env,
                                            "session" => $query->session,
                                            "mode" => 'image',
                                            "model" => $query->model,
                                            "apiRef" => $query->apiKey,
                                            "units" => 1,
                                            "type" => 'images',
                                        ];
                                        if (empty($stats["price"])) {
                                            if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                                                $stats["price"] = 0;
                                            } else {
                                                $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                            }
                                        }
                                        $GLOBALS['aiomatic_stats']->add($stats);
                                    }
                                    if(isset($result->aiomatic_tool_direct_message) && empty($function_result))
                                    {
                                        $my_message = '';
                                        foreach($result->aiomatic_tool_direct_message as $dm)
                                        {
                                            $my_message .= $dm['content'] . ' ';
                                        }
                                        $my_message = trim($my_message);
                                        return $my_message;
                                    }
                                    if(isset($result->aiomatic_tool_end_conversation) && empty($function_result))
                                    {
                                        return 'AiomaticConversationEnded~!@#$%^&*()';
                                    }
                                    
                                    if(isset($result->aiomatic_tool_results) && empty($function_result))
                                    {
                                        //recalling with function result
                                        return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, true, true, $functions, $stream, $vision_file, $dont_add_vision, $user_question, $embedding_namespace, $result->aiomatic_tool_results, $dump_result, $store_data);
                                    }
                                    return $result->output[$current_cont]->content[0]->text;
                                }
                            }
                        }
                        else
                        {
                            if (empty($result->output[$current_cont]->content[0]->text))
                            {
                                $error = 'Error: No content found in the response(4) ' . print_r($result, true);
                                return false;
                            }
                            else
                            {
                                if($count_vision)
                                {
                                    $stats = [
                                        "env" => $query->env,
                                        "session" => $query->session,
                                        "mode" => 'image',
                                        "model" => $query->model,
                                        "apiRef" => $query->apiKey,
                                        "units" => 1,
                                        "type" => 'images',
                                    ];
                                    if (empty($stats["price"])) {
                                        if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                                            $stats["price"] = 0;
                                        } else {
                                            $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                        }
                                    }
                                    $GLOBALS['aiomatic_stats']->add($stats);
                                }
                                return $result->output[$current_cont]->content[0]->text;
                            }
                        }
                    }
                }
                else
                {
                    $error = 'Error: Unexpected result format ' . print_r($result, true);
                    return false;
                }
            }
            else
            {
                return $result;
            }
        }
        $base_params = [
            'model' => $model,
            'messages' => $aicontent,
            'temperature' => $temperature,
            'top_p' => $top_p,
            'presence_penalty' => $presence_penalty,
            'frequency_penalty' => $frequency_penalty,
        ];
        if($model == 'gpt-4o-mini-search-preview' || $model == 'gpt-4o-search-preview')
        {
            unset($base_params['temperature']);
            unset($base_params['top_p']);
            unset($base_params['presence_penalty']);
            unset($base_params['frequency_penalty']);
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            $base_params['stream'] = true;
        }
        if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
        {
            if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') == 'https://api.mistral.ai') 
            {//don't add max completion for mistralai
            }
            else
            {
                $base_params['max_completion_tokens'] = $available_tokens;
            }
        }
        if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
        {
            //currently tool choice is not supported in o1 models
            if(!aiomatic_is_o1_model($model))
            {
                $base_params['tools'] = $functions['functions'];
                $base_params['tool_choice'] = 'auto';
            }
        }
        if (isset($aiomatic_Main_Settings['ai_seed']) && $aiomatic_Main_Settings['ai_seed'] != '')
        {
            $base_params['seed'] = intval($aiomatic_Main_Settings['ai_seed']);
        }

        if ((isset($aiomatic_Main_Settings['store_data']) && $aiomatic_Main_Settings['store_data'] == 'on') || ($store_data === true || $store_data === 'on' || $store_data === '1'))
        {
            if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
            {//don't store results for custom endpoints
            }
            else
            {
                $base_params['store'] = true;
            }
        }
        try
        {
            $send_json = aiomatic_safe_json_encode($base_params);
        }
        catch(Exception $e)
        {
            $error = 'Error: Exception in chat API payload encoding: ' . print_r($e->getMessage(), true);
            return false;
        }
        if($send_json === false)
        {
            $error = 'Error: Failed to encode chat API payload: ' . print_r($base_params, true);
            return false;
        }
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            add_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        add_action('http_api_curl', 'aiomatic_add_proxy');
        $xh = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        );
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $xh['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $api_call = wp_remote_post(
            $api_endpoint . '/v1/chat/completions',
            array(
                'headers'     => $xh,
                'body'        => $send_json,
                'method'      => 'POST',
                'data_format' => 'body',
                'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
            )
        );
        remove_action('http_api_curl', 'aiomatic_add_proxy');
        if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
        {
            remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
        }
        if(is_wp_error( $api_call ))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after initial failure: ' . print_r($api_call, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
            }
            else
            {
                $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
                return false;
            }
        }
        else
        {
            $result = json_decode( $api_call['body'] );
            if($stream === false && $result === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after decode failure(2): ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                }
                else
                {
                    $error = 'Error: Failed to decode initial chat API response(2): ' . print_r($api_call, true);
                    return false;
                }
            }
            $is_error = false;
            $sleep_time = false;
            if(isset($result->error))
            {
                $result = $result->error;
                $is_error = true;
            }
            if($is_error && isset($result->type))
            {
                if($result->type == 'insufficient_quota')
                {
                    $error = 'Error: You exceeded your OpenAI chat quota limit. To fix this, if you are using a free OpenAI account, you need to add a VISA card to your account, as OpenAI heavily limits free accounts. Please check details here: https://platform.openai.com/docs/guides/rate-limits.';
                    return false;
                }
                elseif($result->type == 'invalid_request_error')
                {
                    $error = 'Error: Invalid request submitted to the chat API, result: ' . print_r($result, true);
                    return false;
                }
                else
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                        if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                        {
                            $errmessage = $result->message;
                            preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                            if(isset($htmlrez[1][0]))
                            {
                                $sleep_time = ceil(floatval($htmlrez[1][0]));
                                if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                {
                                    aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                                }
                                sleep($sleep_time);
                            }
                        }
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after type failure: ' . print_r($api_call['body'], true));
                        if($sleep_time === false)
                        {
                            sleep(pow(2, $retry_count));
                        }
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                    }
                    else
                    {
                        $error = 'Error: An error occurred when initially calling OpenAI chat API: ' . print_r($result, true);
                        return false;
                    }
                }
            }
            if(!$stream)
            {
                if((isset($result->choices[0]->finish_reason) && $result->choices[0]->finish_reason == 'tool_calls') || (isset($result->choices[0]->finish_details->type) && $result->choices[0]->finish_details->type == 'tool_calls'))
                {
                    if(isset($functions['message']))
                    {
                        $result->choices[0]->message->content = $functions['message'];
                    }
                    else
                    {
                        $result->choices[0]->message->content = 'OK';
                    }
                }
                if(!isset($result->choices[0]->message->content))
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after choices failure: ' . print_r($api_call['body'], true));
                        sleep(pow(2, $retry_count));
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                    }
                    else
                    {
                        if(isset($result->code) && $result->code == 'content_filter')
                        {
                            $error = 'Error: The response was filtered by our content management policy.';
                            return false;
                        }
                        else
                        {
                            $error = 'Error: Choices not found in initial OpenAI chat API result: ' . print_r($result, true);
                            return false;
                        }
                    }
                }
                else
                {
                    if(isset($result->choices[0]->message->tool_calls))
                    {
                        $result->tool_calls = $result->choices[0]->message->tool_calls;
                    }
                    $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                    apply_filters( 'aiomatic_ai_reply', $result->choices[0]->message->content, $query );
                    if(isset($result->choices[0]->finish_reason))
                    {
                        $finish_reason = $result->choices[0]->finish_reason;
                    }
                    else
                    {
                        $finish_reason = $result->choices[0]->finish_details->type;
                    }

                    if($is_chat == true)
                    {
                        if($dump_result === true)
                        {
                            return $result;
                        }
                        if (empty($result->choices[0]->message->content) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                        {
                            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id']), $token);
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, intval($retry_count) + 1, $finish_reason, $error, true, true, $functions, $stream, $vision_file, true, $user_question, $embedding_namespace, $function_result, $dump_result, $store_data);
                        }
                        else
                        {
                            if (!isset($result->aiomatic_tool_direct_message) && !isset($result->aiomatic_tool_end_conversation) && !isset($result->aiomatic_tool_results) && empty($result->choices[0]->message->content))
                            {
                                $error = 'Error: No content found in the response(5) ' . print_r($result, true);
                                return false;
                            }
                            else
                            {
                                if($count_vision)
                                {
                                    $stats = [
                                        "env" => $query->env,
                                        "session" => $query->session,
                                        "mode" => 'image',
                                        "model" => $query->model,
                                        "apiRef" => $query->apiKey,
                                        "units" => 1,
                                        "type" => 'images',
                                    ];
                                    if (empty($stats["price"])) {
                                        if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                                            $stats["price"] = 0;
                                        } else {
                                            $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                        }
                                    }
                                    $GLOBALS['aiomatic_stats']->add($stats);
                                }
                                if(isset($result->aiomatic_tool_direct_message) && empty($function_result))
                                {
                                    $my_message = '';
                                    foreach($result->aiomatic_tool_direct_message as $dm)
                                    {
                                        $my_message .= $dm['content'] . ' ';
                                    }
                                    $my_message = trim($my_message);
                                    return $my_message;
                                }
                                if(isset($result->aiomatic_tool_end_conversation) && empty($function_result))
                                {
                                    return 'AiomaticConversationEnded~!@#$%^&*()';
                                }
                                if(isset($result->aiomatic_tool_results) && empty($function_result))
                                {
                                    //recalling with function result
                                    return aiomatic_generate_text_chat($token, $model, $aicontent, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, $is_chat, $env, $retry_count, $finish_reason, $error, true, true, $functions, $stream, $vision_file, $dont_add_vision, $user_question, $embedding_namespace, $result->aiomatic_tool_results, $dump_result, $store_data);
                                }
                                return $result->choices[0]->message->content;
                            }
                        }
                    }
                    else
                    {
                        if (empty($result->choices[0]->message->content))
                        {
                            $error = 'Error: No content found in the response(6) ' . print_r($result, true);
                            return false;
                        }
                        else
                        {
                            if($count_vision)
                            {
                                $stats = [
                                    "env" => $query->env,
                                    "session" => $query->session,
                                    "mode" => 'image',
                                    "model" => $query->model,
                                    "apiRef" => $query->apiKey,
                                    "units" => 1,
                                    "type" => 'images',
                                ];
                                if (empty($stats["price"])) {
                                    if (aiomatic_is_aiomaticapi_key($query->apiKey)) {
                                        $stats["price"] = 0;
                                    } else {
                                        $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                    }
                                }
                                $GLOBALS['aiomatic_stats']->add($stats);
                            }
                            return $result->choices[0]->message->content;
                        }
                    }
                }
            }
            else
            {
                return $result;
            }
        }
    }
    $error = 'Failed to finish chat API call correctly.';
    return false;
}
function aiomatic_generate_text_google_completion($token, $model, $aicontent, $stream, &$error)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $token = apply_filters('aiomatic_google_api_key', $token);
    if(!aiomatic_is_google_model($model))
    {
        $model = 'text-bison-001';
    }
    if(aiomatic_is_google_chat_model($model))
    {
        $error = 'Error: incorrect model type provided: ' . print_r($model, true);
        return false;
    }
    if($stream === true && !in_array($model, AIOMATIC_GOOGLE_STREAMING_MODELS))
    {
        $stream = false;
    }
    $prompt_tokens = count(aiomatic_encode($aicontent));
    if(aiomatic_is_google_model($model))
    {
        if($prompt_tokens > aiomatic_get_max_input_tokens($model))
        {
            $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens($model), true);
        }
    }
    else
    {
        $error = 'Error: incorrect model provided: ' . print_r($model, true);
        return false;
    }
    $ch = curl_init();
    if($ch === false)
    {
        $error = 'Error: failed to init curl in Google AI API';
        return false;
    }
    $postData = '';
    switch ($model) 
    {
        case 'text-bison-001':
            $url = "https://generativelanguage.googleapis.com/v1beta3/models/text-bison-001:generateText?key=" . $token;
            $postData = json_encode(array("prompt" => array("text" => $aicontent)));
            break;
        case 'chat-bison-001':
            $url = "https://generativelanguage.googleapis.com/v1beta2/models/chat-bison-001:generateMessage?key=" . $token;
            $postData = json_encode(array("prompt" => array("messages" => array(array("content" => $aicontent)))));
            break;
        default:
            $error = 'Error: Google AI model not recognized: ' . print_r($model, true);
            return false;
    }
    if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') 
    {
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
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if($stream === true && in_array($model, AIOMATIC_GOOGLE_STREAMING_MODELS))
    {
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) 
        {
            $normalizedText = '[' . trim($data, " ,[]") . ']';
            $decodedResponse = json_decode($normalizedText, true);
            if($decodedResponse === null || isset($decodedResponse[0]['error']['message']))
            {
                echo "data: [ERROR]\n\n";
                if (ob_get_length())
                {
                    ob_flush();
                }
                flush();
                aiomatic_log_to_file('Google AI streaming error: ' . $decodedResponse[0]['error']['message']);
            }
            else
            {
                if(isset($decodedResponse[0]['candidates'][0]['content']['parts'][0]['text']))
                {
                    echo "data: " . $decodedResponse[0]['candidates'][0]['content']['parts'][0]['text'] . "\n\n";
                    if (ob_get_length())
                    {
                        ob_flush();
                    }
                    flush();
                }
                else
                {
                    echo "data: [DONE]\n\n";
                    if (ob_get_length())
                    {
                        ob_flush();
                    }
                    flush();
                }
            }
            return strlen($data);
        });
    }
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        $error = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }
    if($response === false)
    {
        $error = 'Failed to get Google API response';
        curl_close($ch);
        return false;
    }
    if($stream === true && in_array($model, AIOMATIC_GOOGLE_STREAMING_MODELS))
    {
        return '';
    }
    else
    {
        curl_close($ch);
    }
    $decodedResponse = json_decode($response, true);
    if(isset($decodedResponse[0]))
    {
        $decodedResponse = $decodedResponse[0];
    }
    if (isset($decodedResponse['error'])) 
    {
        $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'Unknown error from Google AI Studio API';
        $error = 'Error: ' . $errorMsg . ' data: ' . $postData;
        return false;
    } 
    elseif (isset($decodedResponse[0]['error'])) 
    {
        $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse[0]['error']['message'] : 'Unknown error from Google AI Studio API';
        $error = 'Error: ' . $errorMsg . ' data: ' . $postData;
        return false;
    } 
    elseif (empty($decodedResponse)) 
    {
        $error = 'No data found in the response ' . print_r($response, true);
        return false;
    }
    if (isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) 
    {
        $generatedText = $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
        return $generatedText;
    } 
    else 
    {
        if (isset($decodedResponse['candidates'][0]['content']) && is_string($decodedResponse['candidates'][0]['content'])) 
        {
            $generatedText = $decodedResponse['candidates'][0]['content'];
            return $generatedText;
        } 
        else 
        {
            if (isset($decodedResponse['candidates'][0]['output']) && is_string($decodedResponse['candidates'][0]['output'])) 
            {
                $generatedText = $decodedResponse['candidates'][0]['output'];
                return $generatedText;
            } 
            else 
            {
                $error = 'No valid content found in the API response ' . print_r($decodedResponse, true);
                return false;
            }
        }
    }
    return $decodedResponse;
}

function aiomatic_generate_image_google($prompt, $model = AIOMATIC_GOOGLE_IMAGE_DEFAULT_MODEL, $env = '', $returnbase64 = false, &$error = '', $nolocal = false)
{
    $is_allowed = apply_filters('aiomatic_is_ai_image_allowed', true, $prompt);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $prompt = apply_filters('aiomatic_modify_ai_image_query', $prompt);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(in_array($model, AIOMATIC_GOOGLE_IMAGE_MODELS) === false)
    {
        $error = 'This model is not currently supported by Google Images API: ' . $model;
        return false;
    }
    if (!isset($aiomatic_Main_Settings['app_id_google']) || trim($aiomatic_Main_Settings['app_id_google']) == '')
    {
        aiomatic_log_to_file('You need to enter an Google Vertex API key in the plugin settings for this feature to work!');
        return false;
    }
    $appids_google = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_google']));
    $appids_google = array_filter($appids_google);
    $token = $appids_google[array_rand($appids_google)];
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'geminiImage';
    $maxResults = 1;
    $available_tokens = 1000;
    $temperature = 1;
    if(!isset($aiomatic_Main_Settings['no_img_translate']) || $aiomatic_Main_Settings['no_img_translate'] != 'on')
    {
        $text_trans = aiomatic_translate_stability($prompt);
        if($text_trans != $prompt && !empty($text_trans))
        {
            aiomatic_log_to_file('Google Gemini prompt translated from: "' . $prompt . '" to: "' . $text_trans . '"');
            $prompt = $text_trans;
        }
    }
    if(strlen($prompt) > 2000)
    {
        $prompt = aiomatic_substr($prompt, 0, 2000);
    }
    $query = new Aiomatic_Query($prompt, $available_tokens, $model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, '800x600', '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = 'Image generator is rate limited: ' . $ok;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
    {
        aiomatic_log_to_file('Generating Google Gemini Image using prompt: ' . $prompt);
    }
    $token = apply_filters('aiomatic_google_api_key', $token);
    $prompt = apply_filters('aiomatic_modify_ai_image_query', trim($prompt));

    if (empty($prompt)) {
        $error = __('Empty prompt provided for Gemini image generation', 'aiomatic-automatic-ai-content-writer');
        return false;
    }

    if(stristr($model, 'gemini') !==false)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . $token;
        $aicontent = [array('role' => 'user', 'content' => $prompt)];
        $gemini_contents = array_map(function ($message) 
        {
            $parts = [];
            if(isset($message['content']))
            {
                $parts = [["text" => $message['content']]];
            }
            elseif(isset($message['function_call']))
            {
                $parts = [["functionCall" => ["name" => $message['function_call']['name']]]];
            }
            elseif(isset($message['function_data']))
            {
                $parts = [["functionResponse" => ["name" => $message['function_data']['name'], "response" => ["name" => $message['function_data']['name'], "content" => $message['function_data']['content']]]]];
            }
            if(!empty($parts))
            {
                return [
                    "role" => $message['role'],
                    "parts" => $parts
                ];
            }
            else
            {
                return;
            }
        }, $aicontent);
        $gemini_functions = [];
        $gemini_contents = array_filter($gemini_contents);
        $gemini_contents = array_values($gemini_contents);
        $js_arr = array(
            "contents" => $gemini_contents,
            "generationConfig" => [
                "temperature" => $temperature,
                "topK" => 1,
                "stopSequences" => [],
                "response_modalities" => ["TEXT", "IMAGE"]
            ],
            "safetySettings" => [
                ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
            ],
            "tools" => $gemini_functions,
            "tool_config" => [
                "function_calling_config" => [
                    "mode" => "auto" 
                ]
            ]
        );
        if(empty($gemini_functions))
        {
            unset($js_arr['tool_config']);
        }
        try
        {
            $postData = aiomatic_safe_json_encode($js_arr);
        }
        catch(Exception $e)
        {
            $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
            return false;
        }
        if(empty($postData))
        {
            $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
            return false;
        }
        $ch = curl_init();
        if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') 
        {
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
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
        {
            $ztime = intval($aiomatic_Main_Settings['max_timeout']);
        }
        else
        {
            $ztime = 300;
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) 
        {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
        if($response === false)
        {
            $error = 'Failed to get Google API response';
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        $decodedResponse = json_decode($response, true);
        if(isset($decodedResponse[0]))
        {
            $decodedResponse = $decodedResponse[0];
        }
        if (isset($decodedResponse['error'])) 
        {
            $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'Unknown error from Google AI Studio API';
            $error = 'Error: ' . $errorMsg . ' data: ' . $postData;
            return false;
        } 
        elseif (isset($decodedResponse[0]['error'])) 
        {
            $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse[0]['error']['message'] : 'Unknown error from Google AI Studio API';
            $error = 'Error: ' . $errorMsg . ' data: ' . $postData;
            return false;
        } 
        elseif (empty($decodedResponse)) 
        {
            $error = 'No data found in the response ' . print_r($response, true);
            return false;
        }
        $image_data = '';
        if (isset($decodedResponse['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
            $image_data = $decodedResponse['candidates'][0]['content']['parts'][0]['inlineData']['data'];
        } else {
            $error = 'No image generated. Response: ' . print_r($decodedResponse, true);
            return false;
        }
        
        apply_filters( 'aiomatic_ai_reply', $image_data, $query );
        if($returnbase64 == true)
        {
            return $image_data;
        }
        $upload_dir = wp_upload_dir();
        $keyword_class = new Aiomatic_keywords();
        $filename = $keyword_class->keywords($prompt, 4);
        $filename = str_replace(' ', '-', $filename);
        if(empty($filename))
        {
            $seed = rand();
            if(isset($json_resp['seed']))
            {
                $seed = $json_resp['seed'];
            }
            $filename = $seed . '.png';
        }
        else
        {
            $filename .= '-' . rand(1,99999) . '.png';
        }
        if (wp_mkdir_p($upload_dir['path']))
        {
            $file = $upload_dir['path'] . '/' . $filename;
            $ret_path = $upload_dir['url'] . '/' . $filename;
        }
        else
        {
            $file = $upload_dir['basedir'] . '/' . $filename;
            $ret_path = $upload_dir['baseurl'] . '/' . $filename;
        }
        $rezi = aiomatic_base64_to_jpeg($image_data, $file, $ret_path);
        if($rezi !== false && $rezi !== '')
        {
            if($nolocal === false && isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
            {
                $attached_id = '';
                $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $prompt);
                if($localpath !== false)
                {
                    unlink($rezi[0]);
                    $localrez = array();
                    $localrez[0] = $localpath[1];
                    $localrez[1] = $localpath[0];
                    $rezi = $localrez;
                }
            }
        }
        return $decodedResponse;
    }
    else
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:predict?key=" . urlencode($token);

        $payload = json_encode([
            "instances" => [[
                "prompt" => $prompt
            ]],
            "parameters" => [
                "sampleCount" => 1
            ]
        ]);

        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        if (isset($decoded['error'])) {
            $error = 'Gemini API Error: ' . $decoded['error']['message'];
            return false;
        }
        $image_data = '';
        if (isset($decoded['predictions'][0]['bytesBase64Encoded'])) {
            $image_data = $decoded['predictions'][0]['bytesBase64Encoded'];
        }
        apply_filters( 'aiomatic_ai_reply', $image_data, $query );
        if($returnbase64 == true)
        {
            return $image_data;
        }
        $upload_dir = wp_upload_dir();
        $keyword_class = new Aiomatic_keywords();
        $filename = $keyword_class->keywords($prompt, 4);
        $filename = str_replace(' ', '-', $filename);
        if(empty($filename))
        {
            $seed = rand();
            if(isset($json_resp['seed']))
            {
                $seed = $json_resp['seed'];
            }
            $filename = $seed . '.png';
        }
        else
        {
            $filename .= '-' . rand(1,99999) . '.png';
        }
        if (wp_mkdir_p($upload_dir['path']))
        {
            $file = $upload_dir['path'] . '/' . $filename;
            $ret_path = $upload_dir['url'] . '/' . $filename;
        }
        else
        {
            $file = $upload_dir['basedir'] . '/' . $filename;
            $ret_path = $upload_dir['baseurl'] . '/' . $filename;
        }
        $rezi = aiomatic_base64_to_jpeg($image_data, $file, $ret_path);
        if($rezi !== false && $rezi !== '')
        {
            if($nolocal === false && isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
            {
                $attached_id = '';
                $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $prompt);
                if($localpath !== false)
                {
                    unlink($rezi[0]);
                    $localrez = array();
                    $localrez[0] = $localpath[1];
                    $localrez[1] = $localpath[0];
                    $rezi = $localrez;
                }
            }
        }
    }
    return $rezi;
}
function aiomatic_list_models_google_gemini(&$error = '')
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['app_id_google']) || trim($aiomatic_Main_Settings['app_id_google']) == '')
    {
        aiomatic_log_to_file('You need to enter an Google Vertex API key in the plugin settings for this feature to work!');
        return false;
    }
    $appids_google = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_google']));
    $appids_google = array_filter($appids_google);
    $token = $appids_google[array_rand($appids_google)];
    $token = apply_filters('aiomatic_google_api_key', $token);


    $url = "https://generativelanguage.googleapis.com/v1beta/models?pageSize=1000&key=" . urlencode($token);

    $headers = [
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    $decoded = json_decode($response, true);
    if (isset($decoded['error'])) {
        $error = 'Gemini API Error: ' . $decoded['error']['message'];
        return false;
    }
    return $decoded;
}
function aiomatic_generate_text_google_chat($token, $model, $aicontent, $temperature, $top_p, $vision_file, $functions, $function_result, $max_tokens, $stream, &$error)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $token = apply_filters('aiomatic_google_api_key', $token);
    if(!aiomatic_is_google_model($model))
    {
        $model = 'gemini-1.5-pro-latest';
    }
    if(!aiomatic_is_google_chat_model($model))
    {
        $error = 'Error: incorrect model type provided: ' . print_r($model, true);
        return false;
    }
    if(is_string($aicontent))
    {
        $prompt_tokens = count(aiomatic_encode($aicontent));
        if(aiomatic_is_google_model($model))
        {
            if($prompt_tokens > aiomatic_get_max_input_tokens($model))
            {
                $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens($model), true);
            }
        }
        else
        {
            $error = 'Error: incorrect model provided: ' . print_r($model, true);
            return false;
        }
    }
    if(!empty($function_result) && is_array($function_result) && is_array($aicontent))
    {
        foreach($function_result as $fname => $fdata)
        {
            $fonedata = array("role" => 'model',
                "function_call" => ['name' => $fname]);
            $aicontent[] = $fonedata;

            $ftwodata = array("role" => 'user',
                "function_data" => ['name' => $fname, 'content' => $fdata]);
            $aicontent[] = $ftwodata;
        }
    }
    $gemini_contents = array_map(function ($message) 
    {
        $parts = [];
        if(isset($message['content']))
        {
            $parts = [["text" => $message['content']]];
        }
        elseif(isset($message['function_call']))
        {
            $parts = [["functionCall" => ["name" => $message['function_call']['name']]]];
        }
        elseif(isset($message['function_data']))
        {
            $parts = [["functionResponse" => ["name" => $message['function_data']['name'], "response" => ["name" => $message['function_data']['name'], "content" => $message['function_data']['content']]]]];
        }
        if(!empty($parts))
        {
            return [
                "role" => $message['role'],
                "parts" => $parts
            ];
        }
        else
        {
            return;
        }
    }, $aicontent);
    $gemini_contents = array_filter($gemini_contents);
    $gemini_contents = array_values($gemini_contents);
    if($stream === true && !in_array($model, AIOMATIC_GOOGLE_STREAMING_MODELS))
    {
        $stream = false;
    }
    $gemini_functions = array();
    if(isset($functions['functions']) && is_array($functions['functions']) && !empty($functions['functions']))
    {
        $gemini_functions['function_declarations'] = array();
        foreach($functions['functions'] as $my_func)
        {
            if($my_func['type'] == 'function')
            {
                $func_details = $my_func['function'];
                $fbuild = array();
                $fbuild['name'] = $func_details->name;
                $fbuild['description'] = $func_details->description;
                $fbuild['parameters'] = array('type' => 'object');
                if(isset($func_details->parameters) && is_array($func_details->parameters))
                {
                    $req_arr = array();
                    $fbuild['parameters']['properties'] = array();
                    foreach($func_details->parameters as $pp)
                    {
                        $fbuild['parameters']['properties'][$pp->name] = array("type" => $pp->type, "description" => $pp->description);
                        if(isset($pp->required) && $pp->required == '1')
                        {
                            $req_arr[] = $pp->name;
                        }
                    }
                    $fbuild['parameters']['required'] = $req_arr;
                }
                $gemini_functions['function_declarations'][] = $fbuild;
            }
        }
    }
    $ch = curl_init();
    if($ch === false)
    {
        $error = 'Error: failed to init curl in Google AI API';
        return false;
    }
    $postData = '';
    switch ($model) 
    {
        case 'gemini-1.5-pro-latest':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions, 
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-1.5-flash-latest':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'learnlm-1.5-pro-experimental':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/learnlm-1.5-pro-experimental:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/learnlm-1.5-pro-experimental:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.0-flash-thinking-exp-01-21':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-thinking-exp-01-21:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-thinking-exp-01-21:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-pro-exp-03-25':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro-exp-03-25:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro-exp-03-25:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-flash-lite-preview-06-17':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite-preview-06-17:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite-preview-06-17:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-pro-preview-06-05':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro-preview-06-05:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro-preview-06-05:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-pro':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-flash':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-flash-lite':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-flash-preview-05-20':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.5-flash-preview-04-17':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-04-17:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-04-17:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemma-3-27b-it':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemma-3-27b-it:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemma-3-27b-it:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.0-pro-exp-02-05':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-pro-exp-02-05:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-pro-exp-02-05:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-exp-1206':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-exp-1206:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-exp-1206:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.0-flash-exp':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => [],
                    /*
                    "response_modalities" => array('Text', 'Image')
                    */
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.0-flash':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-2.0-flash-lite':
            if($stream == false)
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . $token;
            }
            else
            {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:streamGenerateContent?key=" . $token;
            }
            if (!empty($vision_file)) {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
                if (!empty($base64_vision)) {
                    $gemini_contents[0]['parts'][]['inline_data'] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $base64_vision
                    ];
                }
            }
            $js_arr = array(
                "contents" => $gemini_contents,
                "generationConfig" => [
                    "temperature" => $temperature,
                    "topK" => 1,
                    "topP" => $top_p,
                    "maxOutputTokens" => $max_tokens,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                ],
                "tools" => $gemini_functions,
                "tool_config" => [
                    "function_calling_config" => [
                        "mode" => "auto" 
                    ]
                ]
            );
            if(empty($gemini_functions))
            {
                unset($js_arr['tool_config']);
            }
            try
            {
                $postData = aiomatic_safe_json_encode($js_arr);
            }
            catch(Exception $e)
            {
                $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                return false;
            }
            if(empty($postData))
            {
                $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                return false;
            }
            break;
        case 'gemini-1.5-flash-8b-latest':
                if($stream == false)
                {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-8b-latest:generateContent?key=" . $token;
                }
                else
                {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-8b-latest:streamGenerateContent?key=" . $token;
                }
                if (!empty($vision_file)) {
                    $base64_vision = aiomatic_get_base64_from_url($vision_file);
                    if (!empty($base64_vision)) {
                        $gemini_contents[0]['parts'][]['inline_data'] = [
                            'mime_type' => 'image/jpeg',
                            'data' => $base64_vision
                        ];
                    }
                }
                $js_arr = array(
                    "contents" => $gemini_contents,
                    "generationConfig" => [
                        "temperature" => $temperature,
                        "topK" => 1,
                        "topP" => $top_p,
                        "maxOutputTokens" => $max_tokens,
                        "stopSequences" => []
                    ],
                    "safetySettings" => [
                        ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"]
                    ],
                    "tools" => $gemini_functions, 
                    "tool_config" => [
                        "function_calling_config" => [
                            "mode" => "auto" 
                        ]
                    ]
                );
                if(empty($gemini_functions))
                {
                    unset($js_arr['tool_config']);
                }
                try
                {
                    $postData = aiomatic_safe_json_encode($js_arr);
                }
                catch(Exception $e)
                {
                    $error = 'Error: Exception in the API payload encoding: ' . print_r($e->getMessage(), true);
                    return false;
                }
                if(empty($postData))
                {
                    $error = 'Error: Failed to encode post data: ' . print_r($js_arr, true);
                    return false;
                }
                break;
        default:
            $error = 'Error: Google AI model not recognized: ' . print_r($model, true);
            return false;
    }
    if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') 
    {
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
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if($stream === true && in_array($model, AIOMATIC_GOOGLE_STREAMING_MODELS))
    {
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) 
        {
            $normalizedText = '[' . trim($data, " ,[]") . ']';
            $decodedResponse = json_decode($normalizedText, true);
            if($decodedResponse === null || isset($decodedResponse[0]['error']['message']))
            {
                echo "data: [ERROR]\n\n";
                if (ob_get_length())
                {
                    ob_flush();
                }
                flush();
                aiomatic_log_to_file('Google AI streaming error: ' . print_r($decodedResponse, true));
            }
            else
            {
                if(isset($decodedResponse[0]['candidates'][0]['content']['parts'][0]['text']))
                {
                    echo "data: " . $decodedResponse[0]['candidates'][0]['content']['parts'][0]['text'] . "\n\n";
                    if (ob_get_length())
                    {
                        ob_flush();
                    }
                    flush();
                }
                elseif(isset($decodedResponse[0]['candidates'][0]['content']['parts'][0]['functionCall']))
                {
                    echo "data: [AIOMATICTOOLCALLGOOGLE]" . json_encode($decodedResponse[0]['candidates'][0]['content']['parts'][0]['functionCall']) . "\n\n";
                    if (ob_get_length())
                    {
                        ob_flush();
                    }
                    flush();
                }
                else
                {
                    echo "data: [DONE]\n\n";
                    if (ob_get_length())
                    {
                        ob_flush();
                    }
                    flush();
                }
            }
            return strlen($data);
        });
    }
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        $error = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }
    if($response === false)
    {
        $error = 'Failed to get Google API response';
        curl_close($ch);
        return false;
    }
    if($stream === true && in_array($model, AIOMATIC_GOOGLE_STREAMING_MODELS))
    {
        return '';
    }
    else
    {
        curl_close($ch);
    }
    $decodedResponse = json_decode($response, true);
    if(isset($decodedResponse[0]))
    {
        $decodedResponse = $decodedResponse[0];
    }
    if (isset($decodedResponse['error'])) 
    {
        $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'Unknown error from Google AI Studio API';
        $error = 'Error: ' . $errorMsg . ' data: ' . $postData;
        return false;
    } 
    elseif (isset($decodedResponse[0]['error'])) 
    {
        $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse[0]['error']['message'] : 'Unknown error from Google AI Studio API';
        $error = 'Error: ' . $errorMsg . ' data: ' . $postData;
        return false;
    } 
    elseif (empty($decodedResponse)) 
    {
        $error = 'No data found in the response ' . print_r($response, true);
        return false;
    }
    $response_text = false;
    if (isset($decodedResponse['candidates'][0]['content']['parts'][0])) 
    {
        foreach($decodedResponse['candidates'][0]['content']['parts'] as $current_data)
        {
            if(isset($current_data['text']))
            {
                $response_text = $current_data['text'];
            }
            elseif(isset($current_data['functionCall']))
            {
                $filter_tool_object = new stdClass();
                $filter_tool_object->tool_calls = array();
                $tool_oarr = new stdClass();
                $tool_oarr->type = 'function';
                $tool_oarr->id = uniqid();
                $tool_oarr->function = new stdClass();
                $tool_oarr->function->name = $current_data['functionCall']['name'];
                $tool_oarr->function->arguments = (object) $current_data['functionCall']['args'];
                $filter_tool_object->tool_calls[] = $tool_oarr;
                $filter_tool_object = apply_filters('aiomatic_google_tools_ai_reply_raw', $filter_tool_object);
                if(isset($filter_tool_object->aiomatic_tool_direct_message) && isset($filter_tool_object->aiomatic_tool_direct_message[0]) && isset($filter_tool_object->aiomatic_tool_direct_message[0]['content']) && !empty($filter_tool_object->aiomatic_tool_direct_message[0]['content']))
                {
                    return $filter_tool_object->aiomatic_tool_direct_message[0]['content'];
                }
                if(isset($filter_tool_object->aiomatic_tool_end_conversation) && isset($filter_tool_object->aiomatic_tool_end_conversation[0]) && isset($filter_tool_object->aiomatic_tool_end_conversation[0]['content']) && !empty($filter_tool_object->aiomatic_tool_end_conversation[0]['content']))
                {
                    return 'AiomaticConversationEnded~!@#$%^&*()';
                }
                if(isset($filter_tool_object->aiomatic_tool_results) && isset($filter_tool_object->aiomatic_tool_results[0]) && isset($filter_tool_object->aiomatic_tool_results[0]['content']) && !empty($filter_tool_object->aiomatic_tool_results[0]['content']))
                {
                    remove_filter('aiomatic_ai_functions', 'aiomatic_add_god_mode', 999);
                    $function_back_in = array($tool_oarr->function->name => $filter_tool_object->aiomatic_tool_results[0]['content']);
                    return aiomatic_generate_text_google_chat($token, $model, $aicontent, $temperature, $top_p, $vision_file, false, $function_back_in, $max_tokens, $stream, $error);
                }
            }
        }
        if($response_text !== false)
        {
            return $response_text;
        }
        else
        {
            $error = 'No valid response found ' . print_r($decodedResponse, true);
            return false;
        }
    } 
    else 
    {
        if (isset($decodedResponse['candidates'][0]['content']) && is_string($decodedResponse['candidates'][0]['content'])) 
        {
            $generatedText = $decodedResponse['candidates'][0]['content'];
            return $generatedText;
        } 
        else 
        {
            if (isset($decodedResponse['candidates'][0]['output']) && is_string($decodedResponse['candidates'][0]['output'])) 
            {
                $generatedText = $decodedResponse['candidates'][0]['output'];
                return $generatedText;
            } 
            else 
            {
                $error = 'No valid content found in the response ' . print_r($decodedResponse, true);
                return false;
            }
        }
    }
    return $decodedResponse;
}
function aiomatic_generate_text_perplexity($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, &$error, $function_result)
{
    $token = apply_filters('aiomatic_perplexity_api_key', $token);
    if($temperature == 2)
    {
        $temperature = 1.9;
    }
    if($frequency_penalty <= 0)
    {
        $frequency_penalty = 1;
    }
    if(is_array($aicontent))
    {
        if(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'system')
        {
            if(isset($aicontent[1]['role']) && $aicontent[1]['role'] == 'assistant')
            {
                array_splice( $aicontent, 1, 0, array(array('role' => 'user', 'content' => ' ')) );
            }
        }
        elseif(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'assistant')
        {
            array_splice( $aicontent, 0, 0, array(array('role' => 'user', 'content' => ' ')) );
        }
    }
    $base_params = [
        'model' => $model,
        'messages' => $aicontent,
        'temperature' => $temperature,
        'top_p' => $top_p,
        'presence_penalty' => $presence_penalty,
        'frequency_penalty' => $frequency_penalty,
    ];
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        $base_params['stream'] = true;
    }
    if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
    {
        $base_params['max_tokens'] = $available_tokens;
    }
    if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
    {
        //currently tool choice is not supported in o1 models
        if(!aiomatic_is_o1_model($model))
        {
            $base_params['tools'] = $functions['functions'];
            $base_params['tool_choice'] = 'auto';
        }
    }
    if (isset($aiomatic_Main_Settings['ai_seed']) && $aiomatic_Main_Settings['ai_seed'] != '')
    {
        $base_params['seed'] = intval($aiomatic_Main_Settings['ai_seed']);
    }
    try
    {
        $send_json = aiomatic_safe_json_encode($base_params);
    }
    catch(Exception $e)
    {
        $error = 'Error: Exception in chat API payload encoding: ' . print_r($e->getMessage(), true);
        return false;
    }
    if($send_json === false)
    {
        $error = 'Error: Failed to encode chat API payload: ' . print_r($base_params, true);
        return false;
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        add_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    add_action('http_api_curl', 'aiomatic_add_proxy');
    $api_call = wp_remote_post(
        'https://api.perplexity.ai/chat/completions',
        array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ),
            'body'        => $send_json,
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        )
    );
    remove_action('http_api_curl', 'aiomatic_add_proxy');
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    if(is_wp_error( $api_call ))
    {
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after initial failure: ' . print_r($api_call, true));
            sleep(pow(2, $retry_count));
            return aiomatic_generate_text_perplexity($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        }
        else
        {
            $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
            return false;
        }
    }
    else
    {
        $result = json_decode( $api_call['body'] );
        if($result === null)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after decode failure(3): ' . print_r($api_call['body'], true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_text_perplexity($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
            }
            else
            {
                $error = 'Error: Failed to decode initial chat API response(3): ' . print_r($api_call, true);
                return false;
            }
        }
        $is_error = false;
        $sleep_time = false;
        if(isset($result->error))
        {
            $result = $result->error;
            $is_error = true;
        }
        if($is_error && isset($result->type))
        {
            if($result->type == 'insufficient_quota')
            {
                $error = 'Error: You exceeded your PerplexityAI chat quota limit.';
                return false;
            }
            elseif($result->type == 'invalid_request_error')
            {
                $error = 'Error: Invalid request submitted to the chat API (2), result: ' . print_r($result, true);
                return false;
            }
            else
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
                    if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                    {
                        $errmessage = $result->message;
                        preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                        if(isset($htmlrez[1][0]))
                        {
                            $sleep_time = ceil(floatval($htmlrez[1][0]));
                            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                            {
                                aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                            }
                            sleep($sleep_time);
                        }
                    }
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after type failure: ' . print_r($api_call['body'], true));
                    if($sleep_time === false)
                    {
                        sleep(pow(2, $retry_count));
                    }
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_perplexity($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    $error = 'Error: An error occurred when initially calling PerplexityAI chat API: ' . print_r($result, true);
                    return false;
                }
            }
        }
        if(!$stream)
        {
            if((isset($result->choices[0]->finish_reason) && $result->choices[0]->finish_reason == 'tool_calls') || (isset($result->choices[0]->finish_details->type) && $result->choices[0]->finish_details->type == 'tool_calls'))
            {
                if(isset($functions['message']))
                {
                    $result->choices[0]->message->content = $functions['message'];
                }
                else
                {
                    $result->choices[0]->message->content = 'OK';
                }
            }
            if(!isset($result->choices[0]->message->content))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after choices failure: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_perplexity($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    if(isset($result->code) && $result->code == 'content_filter')
                    {
                        $error = 'Error: The response was filtered by our content management policy.';
                        return false;
                    }
                    else
                    {
                        $error = 'Error: Choices not found in initial PerplexityAI chat API result: ' . print_r($result, true);
                        return false;
                    }
                }
            }
            else
            {
                if(isset($result->choices[0]->message->tool_calls))
                {
                    $result->tool_calls = $result->choices[0]->message->tool_calls;
                }
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result->choices[0]->message->content, $query );
                if(isset($result->choices[0]->finish_reason))
                {
                    $finish_reason = $result->choices[0]->finish_reason;
                }
                else
                {
                    $finish_reason = $result->choices[0]->finish_details->type;
                }
                if($is_chat == true)
                {
                    if (empty($result->choices[0]->message->content) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_perplexity($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                    }
                    else
                    {
                        if (empty($result->choices[0]->message->content))
                        {
                            $error = 'Error: No content found in the response(7) ' . print_r($result, true);
                            return false;
                        }
                        else
                        {
                            if($count_vision)
                            {
                                $stats = [
                                    "env" => $query->env,
                                    "session" => $query->session,
                                    "mode" => 'image',
                                    "model" => $query->model,
                                    "apiRef" => $query->apiKey,
                                    "units" => 1,
                                    "type" => 'images',
                                ];
                                if (empty($stats["price"])) {
                                    $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                }
                                $GLOBALS['aiomatic_stats']->add($stats);
                            }
                            return $result->choices[0]->message->content;
                        }
                    }
                }
                else
                {
                    if (empty($result->choices[0]->message->content) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_perplexity']), $token);
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_perplexity($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                    }
                    else
                    {
                        if (empty($result->choices[0]->message->content))
                        {
                            $error = 'Error: No content found in the response(8) ' . print_r($result, true);
                            return false;
                        }
                        else
                        {
                            if($count_vision)
                            {
                                $stats = [
                                    "env" => $query->env,
                                    "session" => $query->session,
                                    "mode" => 'image',
                                    "model" => $query->model,
                                    "apiRef" => $query->apiKey,
                                    "units" => 1,
                                    "type" => 'images',
                                ];
                                if (empty($stats["price"])) {
                                    $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                }
                                $GLOBALS['aiomatic_stats']->add($stats);
                            }
                            return $result->choices[0]->message->content;
                        }
                    }
                }
            }
        }
        else
        {
            return $result;
        }
    }
}
function aiomatic_generate_embeddings_google($model, $aicontent, &$error)
{
    //https://github.com/google/generative-ai-docs/blob/main/site/en/tutorials/rest_quickstart.ipynb
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['app_id_google']) || trim($aiomatic_Main_Settings['app_id_google']) == '')
    {
        $error = 'You need to enter an Google Vertex API key in the plugin settings for this feature to work!';
        return false;
    }
    $appids_google = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_google']));
    $appids_google = array_filter($appids_google);
    $token = trim($appids_google[array_rand($appids_google)]);
    $token = apply_filters('aiomatic_google_api_key', $token);
    if(!aiomatic_google_extension_is_google_embeddings_model($model))
    {
        $model = 'embedding-001';
    }
    $prompt_tokens = count(aiomatic_encode($aicontent));
    if($prompt_tokens > aiomatic_get_max_input_tokens($model))
    {
        $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens($model), true);
    }
    $ch = curl_init();
    if($ch === false)
    {
        $error = 'Error: failed to init curl in Google AI API';
        return false;
    }
    switch ($model) 
    {
        case 'embedding-001':
            $url = "https://generativelanguage.googleapis.com/v1beta/models/embedding-001:embedContent?key=" . $token;
            $postData = json_encode(array("model" => "models/" . $model, "content" => array("parts" => array("text" => $aicontent))));
            break;
        case 'text-embedding-004':
                $url = "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key=" . $token;
                $postData = json_encode(array("model" => "models/" . $model, "content" => array("parts" => array("text" => $aicontent))));
                break;
        case 'text-embedding-005':
                $url = "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-005:embedContent?key=" . $token;
                $postData = json_encode(array("model" => "models/" . $model, "content" => array("parts" => array("text" => $aicontent))));
                break;
        default:
            $error = 'Error: Google AI Embeddings model not recognized: ' . print_r($model, true);
            return false;
    }
    if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') 
    {
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
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        $error = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }
    if($response === false)
    {
        $error = 'Failed to get Google API Embeddings response';
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    $decodedResponse = json_decode($response, true);
    if(isset($decodedResponse[0]))
    {
        $decodedResponse = $decodedResponse[0];
    }
    if (isset($decodedResponse['error'])) 
    {
        $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'Unknown error from Google AI Studio API';
        $error = 'Error: ' . $errorMsg;
        return false;
    } 
    elseif (empty($decodedResponse)) 
    {
        $error = 'No data found in the response ' . print_r($response, true);
        return false;
    }
    if (isset($decodedResponse['embedding']['values'])) 
    {
        $generatedText = $decodedResponse['embedding']['values'];
        return $generatedText;
    } 
    else 
    {
        $error = 'No valid embeddings content found in the response ' . print_r($decodedResponse, true);
        return false;
    }
    return $decodedResponse['embedding']['values'];
}
function aiomatic_generate_embeddings_ollama($model, $aicontent, &$error)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['ollama_url']) || trim($aiomatic_Main_Settings['ollama_url']) == '')
    {
        $error = 'You need to enter an Ollama Server URL in the plugin settings for this feature to work!';
        return false;
    }
    $ollama_url = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['ollama_url']));
    $ollama_url = array_filter($ollama_url);
    $ollama_url = $ollama_url[array_rand($ollama_url)];
    $ollama_url = rtrim(trim($ollama_url), '/');
    $ollama_url = apply_filters('aiomatic_ollama_url', $ollama_url);
    if(!aiomatic_is_ollama_embeddings_model($model))
    {
        $error = 'The selected model is an not Ollama Embeddings model!';
        return false;
    }
    $response = '';
    try 
    {
        $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
        $stop = null;
        $session = aiomatic_get_session_id();
        $mode = 'embeddings';
        $maxResults = 1;
        $aicontentquery = $aicontent;
        $token = '';
        $query = new Aiomatic_Query($aicontentquery, 2000, $model, 0, $stop, 'OllamaEmbeddings', $mode, $token, $session, $maxResults, '', '');
        $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
        if ( $ok !== true ) {
            $error = $ok;
            return false;
        }
        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
        {
            aiomatic_log_to_file('Generating chat AI text using model: ' . $model . ' and prompt: ' . $aicontent);
        }
        require_once (dirname(__FILE__) . "/res/ollama/ollama.php");
        $ollama = new AiomaticOllamaAPI($ollama_url);
        try 
        {
            $response = $ollama->embeddings($model, $aicontent);
            if(empty($response))
            {
                throw new Exception('Empty response from Ollama');
            }
        } 
        catch (Exception $e) 
        {
            throw new Exception('Failed to generate Ollama API response: ' . $e->getMessage());
        }
        $response = apply_filters( 'aiomatic_ai_reply_raw', $response, $aicontent );
        apply_filters( 'aiomatic_ai_reply', $response, $query );
    } 
    catch (Exception $e) 
    {
        $error = 'Ollama failure: ' . $e->getMessage();
        return false;
    }
    return $response;
}
function aiomatic_generate_text_groq($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, &$error, $function_result)
{
    $token = apply_filters('aiomatic_groq_api_key', $token);
    if($temperature == 2)
    {
        $temperature = 1.9;
    }
    if($frequency_penalty <= 0)
    {
        $frequency_penalty = 1;
    }
    if(is_array($aicontent))
    {
        if(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'system')
        {
            if(isset($aicontent[1]['role']) && $aicontent[1]['role'] == 'assistant')
            {
                array_splice( $aicontent, 1, 0, array(array('role' => 'user', 'content' => ' ')) );
            }
        }
        elseif(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'assistant')
        {
            array_splice( $aicontent, 0, 0, array(array('role' => 'user', 'content' => ' ')) );
        }
    }
    if(!empty($vision_file) && $aicontent[0]['role'] == 'system')
    {
        $aicontent[0]['role'] = 'user';
    }
    $base_params = [
        'model' => $model,
        'messages' => $aicontent,
        'temperature' => $temperature,
        'top_p' => $top_p,
        'presence_penalty' => $presence_penalty,
        'frequency_penalty' => $frequency_penalty,
    ];
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        $base_params['stream'] = true;
    }
    if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
    {
        $base_params['max_tokens'] = $available_tokens;
    }
    if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
    {
        //currently tool choice is not supported in o1 models
        if(!aiomatic_is_o1_model($model))
        {
            $base_params['tools'] = $functions['functions'];
            $base_params['tool_choice'] = 'auto';
        }
    }
    if (isset($aiomatic_Main_Settings['ai_seed']) && $aiomatic_Main_Settings['ai_seed'] != '')
    {
        $base_params['seed'] = intval($aiomatic_Main_Settings['ai_seed']);
    }
    try
    {
        $send_json = aiomatic_safe_json_encode($base_params);
    }
    catch(Exception $e)
    {
        $error = 'Error: Exception in chat API payload encoding: ' . print_r($e->getMessage(), true);
        return false;
    }
    if($send_json === false)
    {
        $error = 'Error: Failed to encode chat API payload: ' . print_r($base_params, true);
        return false;
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        add_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    add_action('http_api_curl', 'aiomatic_add_proxy');
    $api_call = wp_remote_post(
        'https://api.groq.com/openai/v1/chat/completions',
        array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ),
            'body'        => $send_json,
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        )
    );
    remove_action('http_api_curl', 'aiomatic_add_proxy');
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    if(is_wp_error( $api_call ))
    {
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_groq']), $token);
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after initial failure: ' . print_r($api_call, true));
            sleep(pow(2, $retry_count));
            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
            return aiomatic_generate_text_groq($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        }
        else
        {
            $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
            return false;
        }
    }
    else
    {
        $result = json_decode( $api_call['body'] );
        if($result === null)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_groq']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after decode failure(3): ' . print_r($api_call['body'], true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_groq($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
            }
            else
            {
                $error = 'Error: Failed to decode initial chat API response(4): ' . print_r($api_call, true);
                return false;
            }
        }
        $is_error = false;
        $sleep_time = false;
        if(isset($result->error))
        {
            $result = $result->error;
            $is_error = true;
        }
        if($is_error && isset($result->type))
        {
            if($result->type == 'insufficient_quota')
            {
                $error = 'Error: You exceeded your PerplexityAI chat quota limit.';
                return false;
            }
            elseif($result->type == 'invalid_request_error')
            {
                $error = 'Error: Invalid request submitted to the chat API (2), result: ' . print_r($result, true);
                return false;
            }
            else
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_groq']), $token);
                    if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                    {
                        $errmessage = $result->message;
                        preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                        if(isset($htmlrez[1][0]))
                        {
                            $sleep_time = ceil(floatval($htmlrez[1][0]));
                            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                            {
                                aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                            }
                            sleep($sleep_time);
                        }
                    }
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after type failure: ' . print_r($api_call['body'], true));
                    if($sleep_time === false)
                    {
                        sleep(pow(2, $retry_count));
                    }
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_groq($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    $error = 'Error: An error occurred when initially calling PerplexityAI chat API: ' . print_r($result, true);
                    return false;
                }
            }
        }
        if(!$stream)
        {
            if((isset($result->choices[0]->finish_reason) && $result->choices[0]->finish_reason == 'tool_calls') || (isset($result->choices[0]->finish_details->type) && $result->choices[0]->finish_details->type == 'tool_calls'))
            {
                if(isset($functions['message']))
                {
                    $result->choices[0]->message->content = $functions['message'];
                }
                else
                {
                    $result->choices[0]->message->content = 'OK';
                }
            }
            if(!isset($result->choices[0]->message->content))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_groq']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after choices failure: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_groq($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    if(isset($result->code) && $result->code == 'content_filter')
                    {
                        $error = 'Error: The response was filtered by our content management policy.';
                        return false;
                    }
                    else
                    {
                        $error = 'Error: Choices not found in initial Groq chat API result: ' . print_r($result, true);
                        return false;
                    }
                }
            }
            else
            {
                if(isset($result->choices[0]->message->tool_calls))
                {
                    $result->tool_calls = $result->choices[0]->message->tool_calls;
                }
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result->choices[0]->message->content, $query );
                if(isset($result->choices[0]->finish_reason))
                {
                    $finish_reason = $result->choices[0]->finish_reason;
                }
                else
                {
                    $finish_reason = $result->choices[0]->finish_details->type;
                }
                if($is_chat == true)
                {
                    if (empty($result->choices[0]->message->content) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_groq']), $token);
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_groq($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                    }
                    else
                    {
                        if (empty($result->choices[0]->message->content))
                        {
                            $error = 'Error: No content found in the response(9) ' . print_r($result, true);
                            return false;
                        }
                        else
                        {
                            if($count_vision)
                            {
                                $stats = [
                                    "env" => $query->env,
                                    "session" => $query->session,
                                    "mode" => 'image',
                                    "model" => $query->model,
                                    "apiRef" => $query->apiKey,
                                    "units" => 1,
                                    "type" => 'images',
                                ];
                                if (empty($stats["price"])) {
                                    $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                }
                                $GLOBALS['aiomatic_stats']->add($stats);
                            }
                            return $result->choices[0]->message->content;
                        }
                    }
                }
                else
                {
                    if (empty($result->choices[0]->message->content))
                    {
                        $error = 'Error: No content found in the response(10) ' . print_r($result, true);
                        return false;
                    }
                    else
                    {
                        if($count_vision)
                        {
                            $stats = [
                                "env" => $query->env,
                                "session" => $query->session,
                                "mode" => 'image',
                                "model" => $query->model,
                                "apiRef" => $query->apiKey,
                                "units" => 1,
                                "type" => 'images',
                            ];
                            if (empty($stats["price"])) {
                                $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                            }
                            $GLOBALS['aiomatic_stats']->add($stats);
                        }
                        return $result->choices[0]->message->content;
                    }
                }
            }
        }
        else
        {
            return $result;
        }
    }
}
function aiomatic_generate_text_nvidia($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, &$error, $function_result)
{
    $token = apply_filters('aiomatic_nvidia_api_key', $token);
    if($temperature == 2)
    {
        $temperature = 1.9;
    }
    if($frequency_penalty <= 0)
    {
        $frequency_penalty = 1;
    }
    if(is_array($aicontent))
    {
        if(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'system')
        {
            if(isset($aicontent[1]['role']) && $aicontent[1]['role'] == 'assistant')
            {
                array_splice( $aicontent, 1, 0, array(array('role' => 'user', 'content' => ' ')) );
            }
        }
        elseif(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'assistant')
        {
            array_splice( $aicontent, 0, 0, array(array('role' => 'user', 'content' => ' ')) );
        }
    }
    if(!empty($vision_file) && $aicontent[0]['role'] == 'system')
    {
        $aicontent[0]['role'] = 'user';
    }
    $base_params = [
        'model' => $model,
        'messages' => $aicontent,
        'temperature' => $temperature,
        'top_p' => $top_p,
        'presence_penalty' => $presence_penalty,
        'frequency_penalty' => $frequency_penalty,
    ];
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        $base_params['stream'] = true;
    }
    if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
    {
        $base_params['max_tokens'] = $available_tokens;
    }
    if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
    {
        //currently tool choice is not supported in o1 models
        if(!aiomatic_is_o1_model($model))
        {
            $base_params['tools'] = $functions['functions'];
            $base_params['tool_choice'] = 'auto';
        }
    }
    if (isset($aiomatic_Main_Settings['ai_seed']) && $aiomatic_Main_Settings['ai_seed'] != '')
    {
        $base_params['seed'] = intval($aiomatic_Main_Settings['ai_seed']);
    }
    try
    {
        $send_json = aiomatic_safe_json_encode($base_params);
    }
    catch(Exception $e)
    {
        $error = 'Error: Exception in chat API payload encoding: ' . print_r($e->getMessage(), true);
        return false;
    }
    if($send_json === false)
    {
        $error = 'Error: Failed to encode chat API payload: ' . print_r($base_params, true);
        return false;
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        add_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    add_action('http_api_curl', 'aiomatic_add_proxy');
    $api_call = wp_remote_post(
        'https://integrate.api.nvidia.com/v1/chat/completions',
        array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ),
            'body'        => $send_json,
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        )
    );
    remove_action('http_api_curl', 'aiomatic_add_proxy');
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    if(is_wp_error( $api_call ))
    {
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_nvidia']), $token);
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after initial failure: ' . print_r($api_call, true));
            sleep(pow(2, $retry_count));
            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
            return aiomatic_generate_text_nvidia($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        }
        else
        {
            $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
            return false;
        }
    }
    else
    {
        $result = json_decode( $api_call['body'] );
        if($result === null)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_nvidia']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after decode failure(3): ' . print_r($api_call['body'], true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_nvidia($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
            }
            else
            {
                $error = 'Error: Failed to decode initial chat API response(4): ' . print_r($api_call, true);
                return false;
            }
        }
        $is_error = false;
        $sleep_time = false;
        if(isset($result->error))
        {
            $result = $result->error;
            $is_error = true;
        }
        if($is_error && isset($result->type))
        {
            if($result->type == 'insufficient_quota')
            {
                $error = 'Error: You exceeded your PerplexityAI chat quota limit.';
                return false;
            }
            elseif($result->type == 'invalid_request_error')
            {
                $error = 'Error: Invalid request submitted to the chat API (2), result: ' . print_r($result, true);
                return false;
            }
            else
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_nvidia']), $token);
                    if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                    {
                        $errmessage = $result->message;
                        preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                        if(isset($htmlrez[1][0]))
                        {
                            $sleep_time = ceil(floatval($htmlrez[1][0]));
                            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                            {
                                aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                            }
                            sleep($sleep_time);
                        }
                    }
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after type failure: ' . print_r($api_call['body'], true));
                    if($sleep_time === false)
                    {
                        sleep(pow(2, $retry_count));
                    }
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_nvidia($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    $error = 'Error: An error occurred when initially calling PerplexityAI chat API: ' . print_r($result, true);
                    return false;
                }
            }
        }
        if(!$stream)
        {
            if((isset($result->choices[0]->finish_reason) && $result->choices[0]->finish_reason == 'tool_calls') || (isset($result->choices[0]->finish_details->type) && $result->choices[0]->finish_details->type == 'tool_calls'))
            {
                if(isset($functions['message']))
                {
                    $result->choices[0]->message->content = $functions['message'];
                }
                else
                {
                    $result->choices[0]->message->content = 'OK';
                }
            }
            if(!isset($result->choices[0]->message->content))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_nvidia']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after choices failure: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_nvidia($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    if(isset($result->code) && $result->code == 'content_filter')
                    {
                        $error = 'Error: The response was filtered by our content management policy.';
                        return false;
                    }
                    else
                    {
                        $error = 'Error: Choices not found in initial Nvidia chat API result: ' . print_r($result, true);
                        return false;
                    }
                }
            }
            else
            {
                if(isset($result->choices[0]->message->tool_calls))
                {
                    $result->tool_calls = $result->choices[0]->message->tool_calls;
                }
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result->choices[0]->message->content, $query );
                if(isset($result->choices[0]->finish_reason))
                {
                    $finish_reason = $result->choices[0]->finish_reason;
                }
                else
                {
                    if(isset($result->choices[0]->finish_details))
                    {
                        $finish_reason = $result->choices[0]->finish_details->type;
                    }
                    else
                    {
                        $finish_reason = 'stop';
                    }
                }
                if($is_chat == true)
                {
                    if (empty($result->choices[0]->message->content) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_nvidia']), $token);
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_nvidia($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                    }
                    else
                    {
                        if (empty($result->choices[0]->message->content))
                        {
                            $error = 'Error: No content found in the response(11) ' . print_r($result, true);
                            return false;
                        }
                        else
                        {
                            if($count_vision)
                            {
                                $stats = [
                                    "env" => $query->env,
                                    "session" => $query->session,
                                    "mode" => 'image',
                                    "model" => $query->model,
                                    "apiRef" => $query->apiKey,
                                    "units" => 1,
                                    "type" => 'images',
                                ];
                                if (empty($stats["price"])) {
                                    $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                }
                                $GLOBALS['aiomatic_stats']->add($stats);
                            }
                            return $result->choices[0]->message->content;
                        }
                    }
                }
                else
                {
                    if (empty($result->choices[0]->message->content))
                    {
                        $error = 'Error: No content found in the response(12) ' . print_r($result, true);
                        return false;
                    }
                    else
                    {
                        if($count_vision)
                        {
                            $stats = [
                                "env" => $query->env,
                                "session" => $query->session,
                                "mode" => 'image',
                                "model" => $query->model,
                                "apiRef" => $query->apiKey,
                                "units" => 1,
                                "type" => 'images',
                            ];
                            if (empty($stats["price"])) {
                                $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                            }
                            $GLOBALS['aiomatic_stats']->add($stats);
                        }
                        return $result->choices[0]->message->content;
                    }
                }
            }
        }
        else
        {
            return $result;
        }
    }
}
function aiomatic_generate_text_xai($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, $retry_count, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, &$error, $function_result)
{
    $token = apply_filters('aiomatic_xai_api_key', $token);
    if($temperature == 2)
    {
        $temperature = 1.9;
    }
    if($frequency_penalty <= 0)
    {
        $frequency_penalty = 1;
    }
    if(is_array($aicontent))
    {
        if(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'system')
        {
            if(isset($aicontent[1]['role']) && $aicontent[1]['role'] == 'assistant')
            {
                array_splice( $aicontent, 1, 0, array(array('role' => 'user', 'content' => ' ')) );
            }
        }
        elseif(isset($aicontent[0]['role']) && $aicontent[0]['role'] == 'assistant')
        {
            array_splice( $aicontent, 0, 0, array(array('role' => 'user', 'content' => ' ')) );
        }
    }
    if(!empty($vision_file) && $aicontent[0]['role'] == 'system')
    {
        $aicontent[0]['role'] = 'user';
    }
    $base_params = [
        'model' => $model,
        'messages' => $aicontent,
        'temperature' => $temperature,
        'top_p' => $top_p,
        'presence_penalty' => $presence_penalty,
        'frequency_penalty' => $frequency_penalty,
    ];
    if($model == 'grok-3-mini' || $model == 'grok-3-fast' || $model == 'grok-3-mini-fast' || $model == 'grok-4-0709')
    {
        unset($base_params['presence_penalty']);
        unset($base_params['frequency_penalty']);
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        $base_params['stream'] = true;
    }
    if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
    {
        $base_params['max_tokens'] = $available_tokens;
    }
    if($functions !== false && !empty($functions) && isset($functions['functions']) && !empty($functions['functions']))
    {
        $base_params['tools'] = $functions['functions'];
        $base_params['tool_choice'] = 'auto';
    }
    if (isset($aiomatic_Main_Settings['ai_seed']) && $aiomatic_Main_Settings['ai_seed'] != '')
    {
        $base_params['seed'] = intval($aiomatic_Main_Settings['ai_seed']);
    }
    try
    {
        $send_json = aiomatic_safe_json_encode($base_params);
    }
    catch(Exception $e)
    {
        $error = 'Error: Exception in chat API payload encoding: ' . print_r($e->getMessage(), true);
        return false;
    }
    if($send_json === false)
    {
        $error = 'Error: Failed to encode chat API payload: ' . print_r($base_params, true);
        return false;
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        add_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    add_action('http_api_curl', 'aiomatic_add_proxy');
    $api_call = wp_remote_post(
        'https://api.x.ai/v1/chat/completions',
        array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ),
            'body'        => $send_json,
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        )
    );
    remove_action('http_api_curl', 'aiomatic_add_proxy');
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        remove_action('http_api_curl', 'aiomatic_filterCurlForStream');
    }
    if(is_wp_error( $api_call ))
    {
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_xai']), $token);
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after initial failure: ' . print_r($api_call, true));
            sleep(pow(2, $retry_count));
            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
            return aiomatic_generate_text_xai($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
        }
        else
        {
            $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
            return false;
        }
    }
    else
    {
        $result = json_decode( $api_call['body'] );
        if($result === null)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_xai']), $token);
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after decode failure(3): ' . print_r($api_call['body'], true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_text_xai($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
            }
            else
            {
                $error = 'Error: Failed to decode initial chat API response(4): ' . print_r($api_call, true);
                return false;
            }
        }
        $is_error = false;
        $sleep_time = false;
        if(isset($result->error))
        {
            $result = $result->error;
            $is_error = true;
        }
        if($is_error && isset($result->type))
        {
            if($result->type == 'insufficient_quota')
            {
                $error = 'Error: You exceeded your PerplexityAI chat quota limit.';
                return false;
            }
            elseif($result->type == 'invalid_request_error')
            {
                $error = 'Error: Invalid request submitted to the chat API (2), result: ' . print_r($result, true);
                return false;
            }
            else
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_xai']), $token);
                    if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                    {
                        $errmessage = $result->message;
                        preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                        if(isset($htmlrez[1][0]))
                        {
                            $sleep_time = ceil(floatval($htmlrez[1][0]));
                            if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                            {
                                aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                            }
                            sleep($sleep_time);
                        }
                    }
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after type failure: ' . print_r($api_call['body'], true));
                    if($sleep_time === false)
                    {
                        sleep(pow(2, $retry_count));
                    }
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_xai($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    $error = 'Error: An error occurred when initially calling PerplexityAI chat API: ' . print_r($result, true);
                    return false;
                }
            }
        }
        if(!$stream)
        {
            if((isset($result->choices[0]->finish_reason) && $result->choices[0]->finish_reason == 'tool_calls') || (isset($result->choices[0]->finish_details->type) && $result->choices[0]->finish_details->type == 'tool_calls'))
            {
                if(isset($functions['message']))
                {
                    $result->choices[0]->message->content = $functions['message'];
                }
                else
                {
                    $result->choices[0]->message->content = 'OK';
                }
            }
            if(!isset($result->choices[0]->message->content))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_xai']), $token);
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after choices failure: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_text_xai($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                }
                else
                {
                    if(isset($result->code) && $result->code == 'content_filter')
                    {
                        $error = 'Error: The response was filtered by our content management policy.';
                        return false;
                    }
                    else
                    {
                        $error = 'Error: Choices not found in initial xAI chat API result: ' . print_r($result, true);
                        return false;
                    }
                }
            }
            else
            {
                if(isset($result->choices[0]->message->tool_calls))
                {
                    $result->tool_calls = $result->choices[0]->message->tool_calls;
                }
                $result = apply_filters( 'aiomatic_ai_reply_raw', $result, $aicontent );
                apply_filters( 'aiomatic_ai_reply', $result->choices[0]->message->content, $query );
                if(isset($result->choices[0]->finish_reason))
                {
                    $finish_reason = $result->choices[0]->finish_reason;
                }
                else
                {
                    $finish_reason = $result->choices[0]->finish_details->type;
                }
                if($is_chat == true)
                {
                    if (empty($result->choices[0]->message->content) && isset($aiomatic_Main_Settings['max_chat_retry']) && $aiomatic_Main_Settings['max_chat_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_chat_retry']) && intval($aiomatic_Main_Settings['max_chat_retry']) > $retry_count)
                    {
                        $token = aiomatic_try_new_key(trim($aiomatic_Main_Settings['app_id_xai']), $token);
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') chat API call after AI writer ended conversation.');
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_text_xai($token, $model, $aicontent, $temperature, $top_p, $presence_penalty, $frequency_penalty, $functions, $available_tokens, $stream, intval($retry_count) + 1, $query, $count_vision, $vision_file, $user_question, $env, $is_chat, $error, $function_result);
                    }
                    else
                    {
                        if (empty($result->choices[0]->message->content))
                        {
                            $error = 'Error: No content found in the response(13) ' . print_r($result, true);
                            return false;
                        }
                        else
                        {
                            if($count_vision)
                            {
                                $stats = [
                                    "env" => $query->env,
                                    "session" => $query->session,
                                    "mode" => 'image',
                                    "model" => $query->model,
                                    "apiRef" => $query->apiKey,
                                    "units" => 1,
                                    "type" => 'images',
                                ];
                                if (empty($stats["price"])) {
                                    $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                                }
                                $GLOBALS['aiomatic_stats']->add($stats);
                            }
                            return $result->choices[0]->message->content;
                        }
                    }
                }
                else
                {
                    if (empty($result->choices[0]->message->content))
                    {
                        $error = 'Error: No content found in the response(14) ' . print_r($result, true);
                        return false;
                    }
                    else
                    {
                        if($count_vision)
                        {
                            $stats = [
                                "env" => $query->env,
                                "session" => $query->session,
                                "mode" => 'image',
                                "model" => $query->model,
                                "apiRef" => $query->apiKey,
                                "units" => 1,
                                "type" => 'images',
                            ];
                            if (empty($stats["price"])) {
                                $stats["price"] = $GLOBALS['aiomatic_stats']->getVisionPrice($query->model);
                            }
                            $GLOBALS['aiomatic_stats']->add($stats);
                        }
                        return $result->choices[0]->message->content;
                    }
                }
            }
        }
        else
        {
            return $result;
        }
    }
}
function aiomatic_generate_text_local_claude($token, $model, $aicontent, $temperature, $top_p, $vision_file, $max_tokens, $stream, $is_chat, &$error)
{
    $token = apply_filters('aiomatic_anthropic_api_key', $token);
    if(!aiomatic_is_claude_3_model($model))
    {
        return aiomatic_generate_text_local_claude_completion($token, $model, $aicontent, $temperature, $top_p, $max_tokens, $stream, $is_chat, $error);
    }
    else
    {
        return aiomatic_generate_text_local_claude_chat($token, $model, $aicontent, $temperature, $top_p, $vision_file, $max_tokens, $stream, $is_chat, $error);
    }
}
function aiomatic_generate_text_local_claude_completion($token, $model, $aicontent, $temperature, $top_p, $max_tokens, $stream, $is_chat, &$error)
{
    if(!aiomatic_claude_local_extension_is_claude_model($model))
    {
        $model = 'claude-2.1';
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $prompt_tokens = count(aiomatic_encode($aicontent));
    if(aiomatic_is_claude_model_200k($model))
    {
        if(isset($aiomatic_Main_Settings['claude_context_limit_200k']) && $aiomatic_Main_Settings['claude_context_limit_200k'] != '')
        {
            if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit_200k']))
            {
                $aicontent = aiomatic_strip_to_token_count($aicontent, $aiomatic_Main_Settings['claude_context_limit_200k'], true);
            }
        }
        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
        {
            $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens($model), true);
        }
    }
    elseif(aiomatic_is_claude_model($model))
    {
        if(isset($aiomatic_Main_Settings['claude_context_limit']) && $aiomatic_Main_Settings['claude_context_limit'] != '')
        {
            if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit']))
            {
                $aicontent = aiomatic_strip_to_token_count($aicontent, $aiomatic_Main_Settings['claude_context_limit'], true);
            }
        }
        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
        {
            $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens($model), true);
        }
    }
    else
    {
        $error = 'Error: incorrect model provided: ' . print_r($model, true);
        return false;
    }
    if($is_chat)
    {
        $aicontent = "\n\nHuman: " . $aicontent . "\n\nAssistant:";
    }
    else
    {
        $aicontent = "\n\nHuman: Don't act as an assistant, reply strictly what you are asked. " . $aicontent . "\n\nAssistant:";
    }
    $base_params = [
        'model' => $model,
        'prompt' => $aicontent,
        'temperature' => $temperature,
        'top_p' => $top_p,
        'stream' => false
    ];
    if($is_chat)
    {
        $base_params['stop_sequences'] = array("\n\nUser:");
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        $base_params['stream'] = true;
    }
    if (!isset($aiomatic_Main_Settings['no_max']) || $aiomatic_Main_Settings['no_max'] != 'on')
    {
        $base_params['max_tokens_to_sample'] = $max_tokens;
    }
    try
    {
        $send_json = aiomatic_safe_json_encode($base_params);
    }
    catch(Exception $e)
    {
        $error = 'Error: Exception in chat API Claude payload encoding: ' . print_r($e->getMessage(), true);
        return false;
    }
    if($send_json === false)
    {
        $error = 'Error: Failed to encode Claude API payload: ' . print_r($base_params, true);
        return false;
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        add_action('http_api_curl', 'aiomatic_filterClaudeForStream_local');
    }
    add_action('http_api_curl', 'aiomatic_add_proxy');
    $api_call = wp_remote_post(
        'https://api.anthropic.com/v1/complete',
            array(
            'headers' => [
                'accept' =>  'application/json',
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
                'x-api-key' => $token,
            ],
            'body'        => $send_json,
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        )
    );
    remove_action('http_api_curl', 'aiomatic_add_proxy');
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        remove_action('http_api_curl', 'aiomatic_filterClaudeForStream_local');
    }
    $httpStatusCode = wp_remote_retrieve_response_code( $api_call );
    if(is_wp_error( $api_call ))
    {
        $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
        return false;
    }
    if ($api_call === false) 
    {
        $error = 'Claude curl error!';
        return false;
    } 
    elseif ($httpStatusCode >= 400) 
    {
        $decodedResponse = json_decode($api_call['body'], true);
        $errorType = isset($decodedResponse['error']['type']) ? $decodedResponse['error']['type'] : 'unknown_error';
        $errorMessage = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'No specific error message provided';
        switch ($httpStatusCode) 
        {
            case 400:
                $error = "Claude Invalid request error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 401:
                $error = "Claude Unauthorized error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 403:
                $error = "Claude Forbidden error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 404:
                $error = "Claude Not found error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 429:
                $error = "Claude Rate limit exceeded: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 500:
                $error = "Claude Internal server error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 529:
                $error = "Claude API overloaded error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            default:
                $error = "Claude Error $httpStatusCode: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
        }
    } 
    else
    {
        $decodedResponse = json_decode( $api_call['body'], true);
        if($decodedResponse === null)
        {
            $error = 'Error: Failed to decode initial chat API response(5): ' . print_r($api_call, true);
            return false;
        }
        if (isset($decodedResponse['error'])) 
        {
            $error = 'Claude Error: ' . $decodedResponse['error']['message'];
            return false;
        } 
        else 
        {
            if(!isset($decodedResponse['completion']))
            {
                $error = 'Claude Failed to parse response!' . print_r($api_call['body'], true);
                return false;
            }
            return $decodedResponse['completion'];
        }
    }
    $error = 'Claude failure: text cannot be generated';
    return false;
}

function aiomatic_generate_text_local_claude_chat($token, $model, $aicontent, $temperature, $top_p, $vision_file, $max_tokens, $stream, $is_chat, &$error)
{
    if(!aiomatic_claude_local_extension_is_claude_model($model))
    {
        $model = 'claude-3-5-sonnet-20240620';
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $prompt_tokens = count(aiomatic_encode($aicontent));
    if(aiomatic_is_claude_model_200k($model))
    {
        if(isset($aiomatic_Main_Settings['claude_context_limit_200k']) && $aiomatic_Main_Settings['claude_context_limit_200k'] != '')
        {
            if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit_200k']))
            {
                $aicontent = aiomatic_strip_to_token_count($aicontent, $aiomatic_Main_Settings['claude_context_limit_200k'], true);
            }
        }
        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
        {
            $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens($model), true);
        }
    }
    elseif(aiomatic_is_claude_model($model))
    {
        if(isset($aiomatic_Main_Settings['claude_context_limit']) && $aiomatic_Main_Settings['claude_context_limit'] != '')
        {
            if($prompt_tokens > intval($aiomatic_Main_Settings['claude_context_limit']))
            {
                $aicontent = aiomatic_strip_to_token_count($aicontent, $aiomatic_Main_Settings['claude_context_limit'], true);
            }
        }
        elseif($prompt_tokens > aiomatic_get_max_input_tokens($model))
        {
            $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens($model), true);
        }
    }
    else
    {
        $error = 'Error: incorrect model provided: ' . print_r($model, true);
        return false;
    }
    $chatgpt_obj = array();
    if(!empty($vision_file))
    {
        if($is_chat)
        {
            $claude_content = $aicontent;
        }
        else
        {
            $claude_content = "Don't act as an assistant, reply strictly what you are asked. " . $aicontent;
        }
        $base64_vision = '';
        //if(stristr($vision_file, 'http://localhost/') || stristr($vision_file, 'https://localhost/')) //Claude cannot read image URLs
        {
            $base64_vision = aiomatic_get_base64_from_url($vision_file);
        }
        if(!empty($base64_vision))
        {
            $xacontent = [
                [ "type" => "text", "text" => $claude_content ],
                [ "type" => "image", "source" => [ "type" => "base64", "media_type" => "image/jpeg", "data" => $base64_vision ] ]
            ];
        }
        else
        {
            $xacontent = [ "type" => "text", "text" => $claude_content ];
        }
        $chatgpt_obj[] = array("role" => 'user', "content" => $xacontent);
    }
    else
    {
        if($is_chat)
        {
            $chatgpt_obj[] = array("role" => 'user', "content" => $aicontent);
        }
        else
        {
            $chatgpt_obj[] = array("role" => 'user', "content" => "Don't act as an assistant, reply strictly what you are asked. " . $aicontent);
        }
    }
    $max_tokens = aiomatic_get_max_tokens($model);
    $base_params = [
        'model' => $model,
        'messages' => $chatgpt_obj,
        'temperature' => $temperature,
        'top_p' => $top_p,
        'stream' => false,
        'max_tokens' => $max_tokens
    ];
    if($is_chat)
    {
        $base_params['stop_sequences'] = array("\n\nUser:");
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        $base_params['stream'] = true;
    }
    if($model == 'claude-opus-4-1-20250805')
    {
        if(isset($base_params['temperature']) && isset($base_params['top_p']))
        {
            if($base_params['temperature'] == 1 && $base_params['top_p'] == 1)
            {
                unset($base_params['top_p']);
            }
            elseif($base_params['temperature'] == 1)
            {
                unset($base_params['top_p']);
            }
            elseif($base_params['top_p'] == 1)
            {
                unset($base_params['temperature']);
            }
            else
            {
                unset($base_params['top_p']);
            }
        }
    }
    try
    {
        $send_json = aiomatic_safe_json_encode($base_params);
    }
    catch(Exception $e)
    {
        $error = 'Error: Exception in chat API Claude payload encoding: ' . print_r($e->getMessage(), true);
        return false;
    }
    if($send_json === false)
    {
        $error = 'Error: Failed to encode Claude API payload: ' . print_r($base_params, true);
        return false;
    }
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        add_action('http_api_curl', 'aiomatic_filterClaudeForStream_local');
    }
    add_action('http_api_curl', 'aiomatic_add_proxy');
    $api_call = wp_remote_post(
        'https://api.anthropic.com/v1/messages',
            array(
            'headers' => [
                'accept' =>  'application/json',
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
                'x-api-key' => $token,
            ],
            'body'        => $send_json,
            'method'      => 'POST',
            'data_format' => 'body',
            'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        )
    );
    remove_action('http_api_curl', 'aiomatic_add_proxy');
    if($stream === true && function_exists('curl_init') && extension_loaded("curl"))
    {
        remove_action('http_api_curl', 'aiomatic_filterClaudeForStream_local');
    }
    $httpStatusCode = wp_remote_retrieve_response_code( $api_call );
    if(is_wp_error( $api_call ))
    {
        $error = 'Error: Failed to get initial chat API response: ' . print_r($api_call, true);
        return false;
    }
    if ($api_call === false) 
    {
        $error = 'Claude curl error!';
        return false;
    } 
    elseif ($httpStatusCode >= 400) 
    {
        $decodedResponse = json_decode($api_call['body'], true);
        $errorType = isset($decodedResponse['error']['type']) ? $decodedResponse['error']['type'] : 'unknown_error';
        $errorMessage = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'No specific error message provided';
        switch ($httpStatusCode) 
        {
            case 400:
                $error = "Claude Invalid messages request error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 401:
                $error = "Claude Unauthorized error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 403:
                $error = "Claude Forbidden error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 404:
                $error = "Claude Not found error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 429:
                $error = "Claude Rate limit exceeded: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 500:
                $error = "Claude Internal server error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            case 529:
                $error = "Claude API overloaded error: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
            default:
                $error = "Claude Error $httpStatusCode: " . $errorMessage . " type: " . $errorType;
                return false;
                break;
        }
    } 
    else
    {
        $decodedResponse = json_decode( $api_call['body'], true);
        if($decodedResponse === null)
        {
            $error = 'Error: Failed to decode initial chat API response(6): ' . print_r($api_call, true);
            return false;
        }
        if (isset($decodedResponse['error'])) 
        {
            $error = 'Claude Error: ' . $decodedResponse['error']['message'];
            return false;
        } 
        else 
        {
            if(!isset($decodedResponse['content'][0]['text']))
            {
                $error = 'Claude Failed to parse chat response!' . print_r($api_call['body'], true);
                return false;
            }
            return $decodedResponse['content'][0]['text'];
        }
    }
    $error = 'Claude failure: text cannot be generated';
    return false;
}
function aiomatic_generate_text_openrouter($token, $model, $aicontent, $temperature, $top_p, $vision_file, $max_tokens, $stream, &$error)
{
    $token = apply_filters('aiomatic_openrouter_api_key', $token);
    if(!aiomatic_is_openrouter_model($model))
    {
        $model = 'openrouter/auto';
    }
    if(is_string($aicontent))
    {
        $prompt_tokens = count(aiomatic_encode($aicontent));
        if($prompt_tokens > aiomatic_get_max_input_tokens_openrouter($model))
        {
            $aicontent = aiomatic_strip_to_token_count($aicontent, aiomatic_get_max_input_tokens_openrouter($model), true);
        }
        $role = 'user';
        $chatgpt_obj = array();
        if(!empty($vision_file))
        {
            $base64_vision = '';
            if(stristr($vision_file, 'http://localhost/') || stristr($vision_file, 'https://localhost/'))
            {
                $base64_vision = aiomatic_get_base64_from_url($vision_file);
            }
            if(!empty($base64_vision))
            {
                $xacontent = [
                    [ "type" => "text", "text" => $aicontent ],
                    [ "type" => "image_url", "image_url" => [ "url" => "data:image/jpeg;base64," . $base64_vision ] ]
                ];
            }
            else
            {
                $xacontent = [
                    [ "type" => "text", "text" => $aicontent ],
                    [ "type" => "image_url", "image_url" => [ "url" => $vision_file ] ]
                ];
            }
            $chatgpt_obj[] = array("role" => $role, "content" => $xacontent);
        }
        else
        {
            $chatgpt_obj[] = array("role" => $role, "content" => $aicontent);
        }
    }
    else
    {
        $chatgpt_obj = $aicontent;
    }
    $base_params = [
        'model' => $model,
        'messages' => $chatgpt_obj,
        'temperature' => $temperature,
        'top_p' => $top_p
    ];
    if($stream == true)
    {
        $base_params['stream'] = true;
    }
    $base_params['max_tokens'] = $max_tokens;
    require_once (dirname(__FILE__) . "/res/openrouter/UrlOpenRouter.php"); 
    require_once (dirname(__FILE__) . "/res/openrouter/OpenRouter.php");
    $open_ai = new OpenRouter($token);
    if($stream == true)
    {
        $responsex = $open_ai->chat($base_params, function ($curl_info, $data) {
            echo $data . "<br><br>";
            echo PHP_EOL;
            ob_flush();
            flush();
            return strlen($data);
        });
        $result = json_decode(trim($responsex));
        return $result;
    }
    else
    {
        $responsex = $open_ai->chat($base_params);
        $result = json_decode(trim($responsex));
        if (isset($result->error) && !empty($result->error)) {
            $error = 'Error in OpenRouter API: ' . $result->error->message;
            return false;
        } 
        else 
        {
            if(!isset($result->choices[0]->message->content))
            {
                $error = 'Failed to parse API response: ' . trim($responsex);
                return false;
            }
            return $result->choices[0]->message->content;
        }
        $error = 'OpenRouter failure: text cannot be generated';
        return false;
    }
}
function aiomatic_generate_text_ollama($api_url, $model, $aicontent, $zenv, $temperature, $top_p, $functions, $vision_file, $max_tokens, $stream, &$error)
{
    $api_url = apply_filters('aiomatic_ollama_url', $api_url);
    if(!empty($functions))
    {
        $stream = false;
    }
    $response = '';
    try 
    {
        $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
        $stop = null;
        $session = aiomatic_get_session_id();
        $mode = 'text';
        $maxResults = 1;
        $aicontentquery = '';
        $token = '';
        if(is_array($aicontent))
        {
            $aicontentquery = $aicontent[0]['content'];
            if(is_array($aicontentquery) && isset($aicontentquery[0]['content']))
            {
                $aicontentquery = $aicontentquery[0]['content'];
            }
        }
        $query = new Aiomatic_Query($aicontentquery, $max_tokens, $model, $temperature, $stop, $zenv, $mode, $token, $session, $maxResults, '', '');
        $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
        if ( $ok !== true ) {
            $error = $ok;
            return false;
        }
        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
        {
            aiomatic_log_to_file('Generating chat AI text using model: ' . $model . ' and prompt: ' . $aicontent);
        }
        $env = [
            'temperature' => $temperature,
            'top_p' => $top_p
        ];
        require_once (dirname(__FILE__) . "/res/ollama/ollama.php");
        $ollama = new AiomaticOllamaAPI($api_url);
        try 
        {
            $response = $ollama->chatCompletion($model, $aicontent, $stream, $env, $functions, false, $vision_file);
            if(!$stream && empty($response))
            {
                throw new Exception('Empty response from Ollama');
            }
        } 
        catch (Exception $e) 
        {
            throw new Exception('Failed to generate Ollama API response: ' . $e->getMessage());
        }
        if(!$stream)
        {
            $response = apply_filters( 'aiomatic_ai_reply_raw', $response, $aicontent );
            if(!isset($response['message']['content']))
            {
                throw new Exception('Incorrect Ollama response: ' . print_r($response, true));
            }
            if(isset($response['message']['tool_calls']) && is_array($response['message']['tool_calls']) && count($response['message']['tool_calls']) > 0) 
            {
                $toolOutputs = aiomatic_call_ollama_tool($response);
                if(!empty($toolOutputs))
                {
                    try 
                    {
                        $response = $ollama->chatCompletion($model, $aicontent, $stream, $env, $functions, $toolOutputs, $vision_file);
                        if(!$stream && empty($response))
                        {
                            throw new Exception('Empty response from Ollama function call');
                        }
                        $response = apply_filters( 'aiomatic_ai_reply_raw', $response, $aicontent );
                        if(!isset($response['message']['content']))
                        {
                            throw new Exception('Incorrect Ollama function call response: ' . print_r($response, true));
                        }
                    } 
                    catch (Exception $e) 
                    {
                        throw new Exception('Failed to generate Ollama API response: ' . $e->getMessage());
                    }
                }
            }
            $response = $response['message']['content'];
            apply_filters( 'aiomatic_ai_reply', $response, $query );
        }
    } 
    catch (Exception $e) 
    {
        $error = 'Ollama failure: ' . $e->getMessage();
        return false;
    }
    return $response;
}
function aiomatic_generate_text_huggingface($token, $model, $aicontent, $zenv, $temperature, $top_p, $max_tokens, $stream, &$error)
{
    $token = apply_filters('aiomatic_huggingface_api_key', $token);
    $response = '';
    try 
    {
        $custom_url = '';
        $all_models = get_option('aiomatic_huggingface_models', array());
        if(!array_key_exists($model, $all_models))
        {
            throw new Exception('Inexistent HuggingFace model provided: ' . $model);
        }
        else
        {
            if(is_array($all_models[$model]) && isset($all_models[$model][1]) && trim($all_models[$model][1]) != '')
            {
                $custom_url = trim($all_models[$model][1]);
            }
        }
        $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
        $stop = null;
        $session = aiomatic_get_session_id();
        $mode = 'text';
        $maxResults = 1;
        if(is_array($aicontent))
        {
            $aicontent = $aicontent[0]['content'];
        }
        $query = new Aiomatic_Query($aicontent, $max_tokens, $model, $temperature, $stop, $zenv, $mode, $token, $session, $maxResults, '', '');
        $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
        if ( $ok !== true ) {
            $error = $ok;
            return false;
        }
        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
        if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
        {
            aiomatic_log_to_file('Generating chat AI text using model: ' . $model . ' and prompt: ' . $aicontent);
        }
        require_once (dirname(__FILE__) . "/res/huggingface/api.php"); 
        $env = [
            'apikey' => $token,
            'model' => $model,
            'temperature' => $temperature,
            'top_p' => $top_p
        ];
        $extra = array();
        if($stream == true)
        {
            $env['stream'] = true;
            $extra['stream'] = true;
        }
        else
        {
            $env['stream'] = false;
        }
        $sdk = new AiomaticHuggingFaceSDK($env, $custom_url);
        $response = $sdk->generate_text($aicontent, $extra);
        if(!$stream && empty($response))
        {
            throw new Exception('Failed to generate API response!');
        }
        if(!isset($response[0]['generated_text']))
        {
            throw new Exception('Failed to parse HuggingFace response: ' . print_r($response, true));
        }
        if(!$stream)
        {
            $response = $response[0]['generated_text'];
            $pos = strpos($response, $aicontent);
            if ($pos !== false) 
            {
                $response = substr_replace($response, '', $pos, strlen($aicontent));
            }
            $response = apply_filters( 'aiomatic_ai_reply_raw', $response, $aicontent );
            apply_filters( 'aiomatic_ai_reply', $response, $query );
        }
    } 
    catch (Exception $e) 
    {
        $error = 'HuggingFace failure: ' . $e->getMessage();
        return false;
    }
    return $response;
}
function aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, $retry_count, &$error, $image_model = 'dalle2')
{
    $token = apply_filters('aiomatic_openai_api_key', $token);
    $is_allowed = apply_filters('aiomatic_is_ai_image_allowed', true, $prompt);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $image_model = trim($image_model);
    if(!in_array($image_model, AIOMATIC_DALLE_IMAGE_MODELS))
    {
        $image_model = 'dalle2';
    }
    $prompt = apply_filters('aiomatic_modify_ai_image_query', $prompt);
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'image';
    $maxResults = 1;
    $temperature = 1;
    if(($size == '1792x1024' || $size == '1024x1792') && stristr($image_model, 'dall') === false)
    {
        if($size == '1792x1024')
        {
            $size == '1536x1024';
        }
        elseif($size == '1024x1792')
        {
            $size == '1024x1536';
        }
    }
    $query = new Aiomatic_Query($prompt, 4000, $image_model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, $size, '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = $ok;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $delay = '';
    if(!isset($aiomatic_Main_Settings['no_img_translate']) || $aiomatic_Main_Settings['no_img_translate'] != 'on')
    {
        $text_trans = aiomatic_translate_stability($prompt);
        if($text_trans != $prompt && !empty($text_trans))
        {
            aiomatic_log_to_file('Dall-E prompt translated from: "' . $prompt . '" to: "' . $text_trans . '"');
            $prompt = $text_trans;
        }
    }
    $prompt = trim($prompt);
    if($prompt == '')
    {
        $error = esc_html__('Empty prompt added to image generator', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if($image_model == 'dalle2')
    {
        if(strlen($prompt) > 1000)
        {
            $prompt = aiomatic_substr($prompt, 0, 1000);
        }
    }
    else
    {
        if(stristr($image_model, 'dall') !== false)
        {
            if(strlen($prompt) > 4000)
            {
                $prompt = aiomatic_substr($prompt, 0, 4000);
            }
        }
        else
        {
            if(strlen($prompt) > 32000)
            {
                $prompt = aiomatic_substr($prompt, 0, 32000);
            }
        }
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
    {
        aiomatic_log_to_file('Generating AI Image using prompt: ' . $prompt);
    }
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
    else
    {
        if (isset($aiomatic_Main_Settings['request_delay_dalle']) && $aiomatic_Main_Settings['request_delay_dalle'] != '') 
        {
            if(stristr($aiomatic_Main_Settings['request_delay_dalle'], ',') !== false)
            {
                $tempo = explode(',', $aiomatic_Main_Settings['request_delay_dalle']);
                if(isset($tempo[1]) && is_numeric(trim($tempo[1])) && is_numeric(trim($tempo[0])))
                {
                    $delay = rand(trim($tempo[0]), trim($tempo[1]));
                }
            }
            else
            {
                if(is_numeric(trim($aiomatic_Main_Settings['request_delay_dalle'])))
                {
                    $delay = intval(trim($aiomatic_Main_Settings['request_delay_dalle']));
                }
            }
        }
        if($delay != '' && is_numeric($delay))
        {
            usleep($delay);
        }
    }
    if($image_model == 'dalle2')
    {
        if($size != '256x256' && $size != '512x512' && $size != '1024x1024')
        {
            $size = '512x512';
        }
    }
    else
    {
        if($size != '1792x1024' && $size != '1024x1792' && $size != '1024x1024' && stristr($image_model, 'dall') !== false)
        {
            $size = '1024x1024';
        }
    }
    $is_hd = false;
    if($image_model == 'dalle3hd')
    {
        $is_hd = true;
    }
    if($image_model == 'dalle2')
    {
        $image_model = 'dall-e-2';
    }
    elseif($image_model == 'dalle3' || $image_model == 'dalle3hd')
    {
        $image_model = 'dall-e-3';
        $number = 1;
    }
    $return_arr = array();
    if(aiomatic_is_aiomaticapi_key($token))
    {
        $send_model = $image_model;
        if($is_hd == true)
        {
            $send_model .= '-hd';
        }
        $pargs = array();
        $api_url = 'https://aiomaticapi.com/apis/ai/v1/image/';
        $pargs['prompt'] = trim($prompt);
        $pargs['model'] = trim($send_model);
        $pargs['image_size'] = $size;
        if ($image_model != 'dall-e-2' && stristr($image_model, 'dall') !== false && isset($aiomatic_Main_Settings['dalle_style']) && $aiomatic_Main_Settings['dalle_style'] != '')
        {
            $pargs['style'] = $aiomatic_Main_Settings['dalle_style'];
        }
        $api_headers = array(
            'Authorization' => 'Bearer ' . trim($token)
        );  
        $ai_response = aiomatic_get_web_page_api($api_url, $pargs, $api_headers);
        if($ai_response === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial AiomaticAPI response: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
            }
            else
            {
                $error = 'Error: Failed to get AiomaticAPI image response!';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        $ai_json = json_decode($ai_response);
        if($ai_json === null)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode AiomaticAPI response: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
            }
            else
            {
                $error = 'Error: Failed to decode AiomaticAPI image response: ' . $ai_response;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        if(isset($ai_json->error))
        {
            if (stristr($ai_json->error, 'Your subscription expired, please renew it.') === false && stristr($ai_json->error, '[RATE LIMITED]') === false && isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after error AiomaticAPI response: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
            }
            else
            {
                $error = 'Error while processing AI image response: ' . $ai_json->error;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        if(!isset($ai_json->result))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after result AiomaticAPI response: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
            }
            else
            {
                $error = 'Error: Failed to parse AiomaticAPI image response: ' . $ai_response;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        if(stristr($image_model, 'dall') !== false)
        {
            $ai_json = apply_filters( 'aiomatic_dalle_reply_raw', $ai_json, $prompt );
            apply_filters( 'aiomatic_ai_reply', $ai_json->result, $query );
            if(isset($ai_json->remainingtokens))
            {
                set_transient('aiomaticapi_tokens', $ai_json->remainingtokens, 86400);
            }
            if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $nocopy === false) 
            {
                $attached_id = '';
                $localpath = aiomatic_copy_image_locally($ai_json->result, $attached_id, '', false, $prompt);
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
                    $return_arr[] = $localpath[0];
                }
                else
                {
                    $return_arr[] = $ai_json->result;
                }
            }
            else
            {
                $return_arr[] = $ai_json->result;
            }
        }
        else
        {
            apply_filters( 'aiomatic_ai_reply', $ai_json->result, $query );
            if(isset($ai_json->remainingtokens))
            {
                set_transient('aiomaticapi_tokens', $ai_json->remainingtokens, 86400);
            }
            $img = $ai_json->result;
            $upload_dir = wp_upload_dir();
            $keyword_class = new Aiomatic_keywords();
            $filename = $keyword_class->keywords($prompt, 4);
            $filename = str_replace(' ', '-', $filename);
            if(empty($filename))
            {
                $seed = rand();
                $filename = $seed . '.png';
            }
            else
            {
                $filename .= '-' . rand(1,99999) . '.png';
            }
            if (wp_mkdir_p($upload_dir['path']))
            {
                $file = $upload_dir['path'] . '/' . $filename;
                $ret_path = $upload_dir['url'] . '/' . $filename;
            }
            else
            {
                $file = $upload_dir['basedir'] . '/' . $filename;
                $ret_path = $upload_dir['baseurl'] . '/' . $filename;
            }
            $rezi = aiomatic_base64_to_jpeg($img, $file, $ret_path);
            if($rezi !== false && $rezi !== '')
            {
                if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
                {
                    $attached_id = '';
                    $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $text);
                    if($localpath !== false)
                    {
                        $rezi = $localpath;
                    }
                }
            }
            $return_arr[] = $rezi[1];
        }
    }
    elseif (aiomatic_check_if_azure($aiomatic_Main_Settings)) 
    {
        if (!isset($aiomatic_Main_Settings['azure_endpoint']) || trim($aiomatic_Main_Settings['azure_endpoint']) == '') 
        {
            $error = 'You need to enter an Azure Endpoint for this to work!';
            return false;
        }
        if($image_model == 'dall-e-3' || $image_model == 'gpt-image-1')
        {
            $localAzureDeployments = array();
            $depl_arr = aiomatic_get_deployments($token);
            if(is_array($depl_arr))
            {
                foreach($depl_arr as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
            }
            $azureDeployment = '';
            foreach ( $localAzureDeployments as $dmodel => $dname ) 
            {
                if ( $dmodel === str_replace('.', '', $image_model) || $dmodel === $image_model ) {
                    $azureDeployment = $dname;
                    break;
                }
            }
            if ( $azureDeployment == '' ) 
            {
                $new_dep = aiomatic_update_deployments_azure($token);
                if($new_dep !== false)
                {
                    $localAzureDeployments = array();
                    foreach($new_dep as $dar)
                    {
                        if(empty($dar))
                        {
                            continue;
                        }
                        if(is_string($dar))
                        {
                            $localAzureDeployments[trim($dar)] = trim($dar);
                        }
                        else
                        {
                            $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                        }
                    }
                    foreach ( $localAzureDeployments as $dmodel => $dname ) 
                    {
                        if ( $dmodel === str_replace('.', '', $image_model) || $dmodel === $image_model ) {
                            $azureDeployment = $dname;
                            break;
                        }
                    }
                }
                if ( $azureDeployment == '' ) 
                {
                    $error = 'No added Azure deployment found for image model: ' . $image_model . ' - you need to add this model in your Azure Portal as a Deployment';
                    return false;
                }
            }
            if (isset($aiomatic_Main_Settings['azure_api_selector_dalle3']) && $aiomatic_Main_Settings['azure_api_selector_dalle3'] != '' && $aiomatic_Main_Settings['azure_api_selector_dalle3'] != 'default')
            {
                $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_dalle3'];
            }
            else
            {
                $api_ver = AIOMATIC_AZURE_DALLE3_API_VERSION;
            }
            $apiurl = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/deployments/' . $azureDeployment . '/images/generations' . $api_ver;
        }
        else
        {
            if (isset($aiomatic_Main_Settings['azure_api_selector_dalle2']) && $aiomatic_Main_Settings['azure_api_selector_dalle2'] != '' && $aiomatic_Main_Settings['azure_api_selector_dalle2'] != 'default')
            {
                $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_dalle2'];
            }
            else
            {
                $api_ver = AIOMATIC_AZURE_DALLE_API_VERSION;
            }
            $apiurl = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/images/generations:submit' . $api_ver;
        }
        //https://learn.microsoft.com/en-us/azure/ai-services/openai/dall-e-quickstart?tabs=dalle3%2Ccommand-line&pivots=rest-api
        try
        {
            $jsarrx = [
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size
            ];
            if($is_hd == true)
            {
                $jsarrx['quality'] = 'hd';
            }
            if ($image_model != 'dall-e-2' && stristr($image_model, 'dall') !== false && isset($aiomatic_Main_Settings['dalle_style']) && $aiomatic_Main_Settings['dalle_style'] != '')
            {
                $jsarrx['style'] = $aiomatic_Main_Settings['dalle_style'];
            }
            $send_json = aiomatic_safe_json_encode($jsarrx);
        }
        catch(Exception $e)
        {
            $error = 'Error: Exception in API payload encoding: ' . print_r($e->getMessage(), true);
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        if($send_json === false)
        {
            $error = 'Error: Failed to encode API payload: ' . print_r($prompt, true);
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        add_action('http_api_curl', 'aiomatic_add_proxy');
        $api_call = wp_remote_post(
            $apiurl,
            array(
                'headers' => array( 'Content-Type' => 'application/json', 'api-key' => $token ),
                'body'        => $send_json,
                'method'      => 'POST',
                'data_format' => 'body',
                'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
            )
        );
        remove_action('http_api_curl', 'aiomatic_add_proxy');
        if(is_wp_error( $api_call ))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial DALLE response: ' . print_r($api_call, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
            }
            else
            {
                $error = 'Failed to get DallE API response: ' . print_r($api_call, true);
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        else
        {
            $result = json_decode( $api_call['body'] );
            if($result === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode DALLE response: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                }
                else
                {
                    $error = 'Failed to decode initial DallE API response: ' . print_r($api_call, true);
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            else
            {
                $is_error = false;
                $sleep_time = false;
                if(isset($result->error))
                {
                    $result = $result->error;
                    $is_error = true;
                }
                if($is_error && isset($result->type))
                {
                    if($result->type == 'insufficient_quota')
                    {
                        $error = 'You exceeded your Azure OpenAI quota limit for image generator, please wait a period for the quota to refill (initial call).';
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                    elseif($result->type == 'invalid_request_error')
                    {
                        $error = 'Error: Invalid request submitted to the image API, result: ' . print_r($result, true);
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                        {
                            if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                            {
                                $errmessage = $result->message;
                                preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                                if(isset($htmlrez[1][0]))
                                {
                                    $sleep_time = ceil(floatval($htmlrez[1][0]));
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                                    }
                                    sleep($sleep_time);
                                }
                            }
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after type DALLE response: ' . print_r($api_call['body'], true));
                            if($sleep_time === false)
                            {
                                sleep(pow(2, $retry_count));
                            }
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                        }
                        else
                        {
                            $error = 'An error occurred when initially calling OpenAI API, no type found: ' . print_r($result, true);
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            return false;
                        }
                    }
                }
                if($image_model == 'dall-e-3')
                {
                    if(!isset($result->created) || !isset($result->data))
                    {
                        delete_option('aiomatic_deployments_list');
                        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                        {
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after incorrect DALLE3 response: ' . print_r($result, true));
                            sleep(pow(2, $retry_count));
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                        }
                        else
                        {
                            $error = 'Incorrect response format for Azure Dall-E3 images: ' . print_r($result, true);
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            return false;
                        }
                    }
                    else
                    {
                        $final_url = $result->data[0]->url;
                        $result = apply_filters( 'aiomatic_dalle_reply_raw', $result, $prompt );
                        apply_filters( 'aiomatic_ai_reply', $final_url, $query );
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $nocopy === false) 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally($final_url, $attached_id, '', false, $prompt);
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
                                $return_arr[] = $localpath[0];
                            }
                            else
                            {
                                $return_arr[] = $final_url;
                            }
                        }
                        else
                        {
                            $return_arr[] = $final_url;
                        }
                    }
                }
                elseif($image_model == 'gpt-image-1')
                {
                    if(!isset($result->created) || !isset($result->data))
                    {
                        delete_option('aiomatic_deployments_list');
                        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                        {
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after incorrect gpt-image-1 response: ' . print_r($result, true));
                            sleep(pow(2, $retry_count));
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                        }
                        else
                        {
                            $error = 'Incorrect response format for Azure gpt-image-1 images: ' . print_r($result, true);
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            return false;
                        }
                    }
                    else
                    {
                        $img = $result->data[0]->b64_json;
                        apply_filters( 'aiomatic_ai_reply', $img, $query );
                        $upload_dir = wp_upload_dir();
                        $keyword_class = new Aiomatic_keywords();
                        $filename = $keyword_class->keywords($prompt, 4);
                        $filename = str_replace(' ', '-', $filename);
                        if(empty($filename))
                        {
                            $seed = rand();
                            $filename = $seed . '.png';
                        }
                        else
                        {
                            $filename .= '-' . rand(1,99999) . '.png';
                        }
                        if (wp_mkdir_p($upload_dir['path']))
                        {
                            $file = $upload_dir['path'] . '/' . $filename;
                            $ret_path = $upload_dir['url'] . '/' . $filename;
                        }
                        else
                        {
                            $file = $upload_dir['basedir'] . '/' . $filename;
                            $ret_path = $upload_dir['baseurl'] . '/' . $filename;
                        }
                        $rezi = aiomatic_base64_to_jpeg($img, $file, $ret_path);
                        if($rezi !== false && $rezi !== '')
                        {
                            if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
                            {
                                $attached_id = '';
                                $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $text);
                                if($localpath !== false)
                                {
                                    $rezi = $localpath;
                                }
                            }
                        }
                        if(isset($rezi[1]))
                        {
                            $return_arr[] = $rezi[1];
                        }
                    }
                }
                else
                {
                    if(!isset($result->id) || !isset($result->status))
                    {
                        delete_option('aiomatic_deployments_list');
                        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                        {
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after incorrect DALLE response: ' . print_r($result, true));
                            sleep(pow(2, $retry_count));
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                        }
                        else
                        {
                            $error = 'Incorrect response format for Azure Dall-E images: ' . print_r($result, true);
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            return false;
                        }
                    }
                    else
                    {
                        $retry_after = 2;
                        $retry_url = wp_remote_retrieve_header( $api_call, 'Operation-Location' );
                        if(empty($retry_url))
                        {
                            $error = 'Failed to find the Operation-Location header: ' . print_r($api_call, true);
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            return false;
                        }
                        $final_url = '';
                        $max_wait = 300;
                        $waited = 0;
                        while($final_url == '')
                        {
                            if($waited > $max_wait)
                            {
                                $error = 'Timeout for image generator in Azure DallEAPI: ' . print_r($prompt, true);
                                $error = apply_filters('aiomatic_modify_ai_error', $error);
                                return false;
                            }
                            sleep($retry_after);
                            $waited += $retry_after;
                            remove_action('http_api_curl', 'aiomatic_add_proxy');
                            $api_call = wp_remote_get(
                                $retry_url,
                                array(
                                    'headers' => array( 'Content-Type' => 'application/json', 'api-key' => $token ),
                                    'timeout'     => 30,
                                )
                            );
                            remove_action('http_api_curl', 'aiomatic_add_proxy');
                            if(is_wp_error( $api_call ))
                            {
                                $error = 'Failed to get Azure DallE API Image URL response: ' . print_r($api_call, true);
                                $error = apply_filters('aiomatic_modify_ai_error', $error);
                                return false;
                            }
                            else
                            {
                                $result = json_decode( $api_call['body'] );
                                if($result === null)
                                {
                                    $error = 'Failed to decode Azure DallE API Image URL response: ' . print_r($api_call['body'], true);
                                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                                    return false;
                                }
                                if(isset($result->status) && $result->status == 'succeeded')
                                {
                                    if(isset($result->result->data) && $result->result->data[0]->url != '')
                                    {
                                        $final_url = $result->result->data[0]->url;
                                    }
                                    else
                                    {
                                        $error = 'Corrupted response from Azure DallE API Image URL: ' . print_r($result, true);
                                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                                        return false;
                                    }
                                }
                            }
                        }
                        if($final_url == '')
                        {
                            $error = 'Failed to generate Azure DallE API Image URL for prompt: ' . print_r($prompt, true);
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            return false;
                        }
                        $result = apply_filters( 'aiomatic_dalle_reply_raw', $result, $prompt );
                        apply_filters( 'aiomatic_ai_reply', $final_url, $query );
                        if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $nocopy === false) 
                        {
                            $attached_id = '';
                            $localpath = aiomatic_copy_image_locally($final_url, $attached_id, '', false, $prompt);
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
                                $return_arr[] = $localpath[0];
                            }
                            else
                            {
                                $return_arr[] = $final_url;
                            }
                        }
                        else
                        {
                            $return_arr[] = $final_url;
                        }
                    }
                }
            }
        }
    }
    else
    {
        try
        {
            $send_arr = [
                'n' => intval($number),
                'prompt' => $prompt,
                'size' => $size,
                'model' => $image_model
            ];
            if(stristr($image_model, 'dall') !== false)
            {
                $send_arr['response_format'] = 'url';
            }
            if($is_hd === true)
            {
                $send_arr['quality'] = 'hd';
            }
            if ($image_model != 'dall-e-2' && stristr($image_model, 'dall') !== false && isset($aiomatic_Main_Settings['dalle_style']) && $aiomatic_Main_Settings['dalle_style'] != '')
            {
                $send_arr['style'] = $aiomatic_Main_Settings['dalle_style'];
            }
            $send_json = aiomatic_safe_json_encode( $send_arr );
        }
        catch(Exception $e)
        {
            $error = 'Error: Exception in API payload encoding: ' . print_r($e->getMessage(), true);
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        if($send_json === false)
        {
            $error = 'Error: Failed to encode API payload: ' . print_r($prompt, true);
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        add_action('http_api_curl', 'aiomatic_add_proxy');
        $xh = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        );
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $xh['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $api_call = wp_remote_post(
            $api_endpoint . '/v1/images/generations',
            array(
                'headers' => $xh,
                'body'        => $send_json,
                'method'      => 'POST',
                'data_format' => 'body',
                'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
            )
        );
        remove_action('http_api_curl', 'aiomatic_add_proxy');
        if(is_wp_error( $api_call ))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after initial DALLE response: ' . print_r($api_call, true));
                sleep(pow(2, $retry_count));
                $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
            }
            else
            {
                $error = 'Failed to get DallE API response: ' . print_r($api_call, true);
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        else
        {
            $result = json_decode( $api_call['body'] );
            if($result === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after decode DALLE response: ' . print_r($api_call['body'], true));
                    sleep(pow(2, $retry_count));
                    $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                    $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                    $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                    return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                }
                else
                {
                    $error = 'Failed to decode initial DallE API response: ' . print_r($api_call, true);
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            else
            {
                $is_error = false;
                $sleep_time = false;
                if(isset($result->error))
                {
                    $result = $result->error;
                    $is_error = true;
                }
                if($is_error && isset($result->type))
                {
                    if($result->type == 'insufficient_quota')
                    {
                        $error = 'You exceeded your OpenAI quota limit for images. To fix this, if you are using a free OpenAI account, you need to add a VISA card to your account, as OpenAI heavily limits free accounts. Please check details here: https://platform.openai.com/docs/guides/rate-limits';
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                    elseif($result->type == 'invalid_request_error')
                    {
                        $error = 'Error: Invalid request submitted to the image API, result: ' . print_r($result, true);
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                        {
                            if(isset($result->code) && $result->code == 'rate_limit_exceeded')
                            {
                                $errmessage = $result->message;
                                preg_match_all('#Rate limit reached for.*?in organization.*?Please try again in ([\d.]*?)s#i', $errmessage, $htmlrez);
                                if(isset($htmlrez[1][0]))
                                {
                                    $sleep_time = ceil(floatval($htmlrez[1][0]));
                                    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
                                    {
                                        aiomatic_log_to_file('Rate limit reached for model: ' . $model . ', sleeping for: ' . $sleep_time . ' seconds');
                                    }
                                    sleep($sleep_time);
                                }
                            }
                            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after type DALLE response: ' . print_r($api_call['body'], true));
                            if($sleep_time === false)
                            {
                                sleep(pow(2, $retry_count));
                            }
                            $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                            $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                            $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                            return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                        }
                        else
                        {
                            $error = 'An error occurred when initially calling OpenAI API, no type found: ' . print_r($result, true);
                            $error = apply_filters('aiomatic_modify_ai_error', $error);
                            return false;
                        }
                    }
                }
                if(!isset($result->data))
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') API call after data DALLE response: ' . print_r($api_call['body'], true));
                        sleep(pow(2, $retry_count));
                        $model = apply_filters('aiomatic_model_fallback', $model, $error, $aicontent);
                        $aicontent = apply_filters('aiomatic_aicontent_try_fix', $aicontent, $error, $model);
                        $available_tokens = apply_filters('aiomatic_available_tokens_try_fix', $available_tokens, $error, $model);
                        return aiomatic_generate_ai_image($token, $number, $prompt, $size, $env, $nocopy, intval($retry_count) + 1, $error, $image_model);
                    }
                    else
                    {
                        $error = 'An error occurred when initially calling OpenAI data API: ' . print_r($result, true);
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                }
                else
                {
                    $result = apply_filters( 'aiomatic_dalle_reply_raw', $result, $prompt );
                    foreach($result->data as $rdata)
                    {
                        if(isset($rdata->b64_json))
                        {
                            $upload_dir = wp_upload_dir();
                            $keyword_class = new Aiomatic_keywords();
                            $filename = $keyword_class->keywords($prompt, 4);
                            $filename = str_replace(' ', '-', $filename);
                            if(empty($filename))
                            {
                                $seed = rand();
                                $filename = $seed . '.png';
                            }
                            else
                            {
                                $filename .= '-' . rand(1,99999) . '.png';
                            }
                            if (wp_mkdir_p($upload_dir['path']))
                            {
                                $file = $upload_dir['path'] . '/' . $filename;
                                $ret_path = $upload_dir['url'] . '/' . $filename;
                            }
                            else
                            {
                                $file = $upload_dir['basedir'] . '/' . $filename;
                                $ret_path = $upload_dir['baseurl'] . '/' . $filename;
                            }
                            $img = $rdata->b64_json;
                            apply_filters( 'aiomatic_ai_reply', $img, $query );
                            $rezi = aiomatic_base64_to_jpeg($img, $file, $ret_path);
                            if($rezi !== false && $rezi !== '')
                            {
                                if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
                                {
                                    $attached_id = '';
                                    $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $text);
                                    if($localpath !== false)
                                    {
                                        $rezi = $localpath;
                                    }
                                }
                            }
                            if(isset($rezi[1]))
                            {
                                $return_arr[] = $rezi[1];
                            }
                        }
                        else
                        {
                            apply_filters( 'aiomatic_ai_reply', $rdata->url, $query );
                            if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $nocopy === false) 
                            {
                                $attached_id = '';
                                $localpath = aiomatic_copy_image_locally($rdata->url, $attached_id, '', false, $prompt);
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
                                    $return_arr[] = $localpath[0];
                                }
                                else
                                {
                                    $return_arr[] = $rdata->url;
                                }
                            }
                            else
                            {
                                $return_arr[] = $rdata->url;
                            }
                        }
                    }
                }
            }
        }
    }
    return $return_arr;
}

function aiomatic_generate_ai_image_midjourney($prompt, $width, $height, $env, $nocopy, &$error)
{
    $return_url = false;
    $is_allowed = apply_filters('aiomatic_is_ai_image_allowed', true, $prompt);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['midjourney_app_id']) || trim($aiomatic_Main_Settings['midjourney_app_id']) == '')
    { 
        $error = 'GoAPI API key is needed to be entered for this feature to work!';
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['midjourney_app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_goapi_api_key', $token);
    if (!isset($aiomatic_Main_Settings['midjourney_image_model']) || trim($aiomatic_Main_Settings['midjourney_image_model']) == '')
    {
        $image_model = 'fast';
    }
    else
    {
        $image_model = trim($aiomatic_Main_Settings['midjourney_image_model']);
    }

    if (!isset($aiomatic_Main_Settings['midjourney_image_engine']) || trim($aiomatic_Main_Settings['midjourney_image_engine']) == '')
    {
        $image_engine = 'midjourney';
    }
    else
    {
        $image_engine = trim($aiomatic_Main_Settings['midjourney_image_engine']);
    }
    
    $prompt = apply_filters('aiomatic_modify_ai_image_query', $prompt);
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'image';
    $maxResults = 1;
    $temperature = 1;
    $query = new Aiomatic_Query($prompt, 1000, $image_model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, $width . 'x' . $height, '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) 
    {
        $error = $ok;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $delay = '';
    if(!isset($aiomatic_Main_Settings['no_img_translate']) || $aiomatic_Main_Settings['no_img_translate'] != 'on')
    {
        $text_trans = aiomatic_translate_stability($prompt);
        if($text_trans != $prompt && !empty($text_trans))
        {
            aiomatic_log_to_file('Midjourney prompt translated from: "' . $prompt . '" to: "' . $text_trans . '"');
            $prompt = $text_trans;
        }
    }
    $prompt = trim($prompt);
    if($prompt == '')
    {
        $error = esc_html__('Empty prompt added to image generator', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(strlen($prompt) > 30000)
    {
        $prompt = aiomatic_substr($prompt, 0, 30000);
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
    {
        aiomatic_log_to_file('Generating AI Image using GoAPI Midjourney prompt: ' . $prompt);
    }
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
    else
    {
        if (isset($aiomatic_Main_Settings['request_delay_dalle']) && $aiomatic_Main_Settings['request_delay_dalle'] != '') 
        {
            if(stristr($aiomatic_Main_Settings['request_delay_dalle'], ',') !== false)
            {
                $tempo = explode(',', $aiomatic_Main_Settings['request_delay_dalle']);
                if(isset($tempo[1]) && is_numeric(trim($tempo[1])) && is_numeric(trim($tempo[0])))
                {
                    $delay = rand(trim($tempo[0]), trim($tempo[1]));
                }
            }
            else
            {
                if(is_numeric(trim($aiomatic_Main_Settings['request_delay_dalle'])))
                {
                    $delay = intval(trim($aiomatic_Main_Settings['request_delay_dalle']));
                }
            }
        }
        if($delay != '' && is_numeric($delay))
        {
            usleep($delay);
        }
    }
    $imagineUrl = 'https://api.goapi.ai/api/v1/task'; 
    $ch = curl_init($imagineUrl); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [ 
        "x-api-key: " . $token, 
    ]); 
    if($width > $height)
    {
        $aspect_ratio = '16:9';
    }
    elseif($width < $height)
    {
        $aspect_ratio = '9:16';
    }
    else
    {
        $aspect_ratio = '1:1';
    }
    if($image_engine == 'midjourney')
    {
        $input_arr = [
            "prompt" => $prompt, 
            "aspect_ratio" => $aspect_ratio, 
            "process_mode" => $image_model,
            "skip_prompt_check" => true
        ];
        $task_type = 'imagine';
    }
    else
    {
        $input_arr = [
            "prompt" => $prompt, 
            "width" => intval($width), 
            "height" => intval($height)
        ];
        $task_type = 'txt2img';
    }
    curl_setopt( 
        $ch, 
        CURLOPT_POSTFIELDS, 
        json_encode([ 
            "model" => $image_engine,
            "task_type" => $task_type,
            "input" => $input_arr
        ]) 
    ); 
    $response = curl_exec($ch); 
    curl_close($ch); 
    if($response === false)
    {
        $error = esc_html__('Failed to execute the Midjourney task!', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $response = str_replace('&quot;', '"', $response);
    $task = json_decode($response, true); 
    $taskId = $task["data"]["task_id"]; 
    if (empty($taskId)) 
    { 
        $error = esc_html__('Failed to create the Midjourney task!', 'aiomatic-automatic-ai-content-writer') . ' ' . print_r($response, true);
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $fetchUrl = 'https://api.goapi.ai/api/v1/task/' . $taskId; 
    $ch = curl_init($fetchUrl); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [ 
        "x-api-key: " . $token, 
    ]); 
    $timeout = 300;
    $start_time = time();
    $fetchResult = array();
    $fetchResult["data"]["status"] = 'NotStarted';
    while ($fetchResult["data"]["status"] !== "completed" && $fetchResult["data"]["status"] !== "failed" && $start_time + $timeout >= time()) 
    {
        $fetchResponse = curl_exec($ch); 
        if($fetchResponse === false)
        {
            $error = esc_html__('Failed to check the Midjourney task!', 'aiomatic-automatic-ai-content-writer');
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        $fetchResult = json_decode($fetchResponse, true); 
        if(!is_array($fetchResult))
        {
            $fetchResult = array();
        }
        sleep(4); 
    } 
    curl_close($ch); 
    if ( $fetchResult["data"]["status"] !== "completed" ) 
    { 
        $error = esc_html__('Error during GoAPI fetch call: ', 'aiomatic-automatic-ai-content-writer') . print_r($fetchResult, true);
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if($image_engine == 'midjourney')
    {
        $upscaleUrl = 'https://api.goapi.ai/api/v1/task'; 
        $ch = curl_init($upscaleUrl); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 
            "X-API-Key: " . $token, 
        ]); 
        curl_setopt( 
            $ch, 
            CURLOPT_POSTFIELDS, 
            json_encode([ 
                "model" => "midjourney",
                "task_type" => "upscale",
                "input" => [
                    "origin_task_id" => $taskId, 
                    "index" => "1", 
                ]
            ]) 
        ); 
        $upscaleResponse = curl_exec($ch); 
        curl_close($ch); 
        if($upscaleResponse === false)
        {
            $error = esc_html__('Failed to execute the Midjourney upscale task!', 'aiomatic-automatic-ai-content-writer');
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        $upscaleResponse = str_replace('&quot;', '"', $upscaleResponse);
        $upscaleTask = json_decode($upscaleResponse, true);
        $upscaleTaskId = $upscaleTask["data"]["task_id"]; 
        if (empty($upscaleTaskId)) { 
            $error = esc_html__('Failed to get upscale API response', 'aiomatic-automatic-ai-content-writer');
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        $fetchUrl = 'https://api.goapi.ai/api/v1/task/' . $upscaleTaskId; 
        $ch = curl_init($fetchUrl); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 
            "X-API-Key: " . $token, 
        ]); 
        $timeout = 300;
        $start_time = time();
        $fetchResult = array();
        $fetchResult["data"]["status"] = 'NotStarted';
        while ($fetchResult["data"]["status"] !== "completed" && $fetchResult["data"]["status"] !== "failed" && $start_time + $timeout >= time()) 
        {
            $fetchResponse = curl_exec($ch); 
            if($fetchResponse === false)
            {
                $error = esc_html__('Failed to check the Midjourney task!', 'aiomatic-automatic-ai-content-writer');
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            $fetchResponse = str_replace('&quot;', '"', $fetchResponse);
            $fetchResult = json_decode($fetchResponse, true); 
            if(!is_array($fetchResult))
            {
                $fetchResult = array();
            }
            sleep(4); 
        } 
        curl_close($ch); 
        if ( $fetchResult["data"]["status"] !== "completed" || !isset($fetchResult["data"]["output"]["image_url"]) ) 
        { 
            $error = esc_html__('Error during GoAPI call: ', 'aiomatic-automatic-ai-content-writer') . print_r($fetchResult, true);
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
    }
    apply_filters( 'aiomatic_ai_reply', $fetchResult["data"]["output"]["image_url"], $query );
    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $nocopy === false) 
    {
        $attached_id = '';
        $localpath = aiomatic_copy_image_locally($fetchResult["data"]["output"]["image_url"], $attached_id, '', false, $prompt);
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
            $return_url = $localpath[0];
        }
        else
        {
            $return_url = $fetchResult["data"]["output"]["image_url"];
        }
    }
    else
    {
        $return_url = $fetchResult["data"]["output"]["image_url"];
    }
    return $return_url;
}
function aiomatic_generate_stability_image($text = '', $height = '512', $width = '512', $env = '', $retry_count = 0, $returnbase64 = false, &$error = '', $nolocal = false, $stable_model = false, $source_image_url = '', $image_strength = 0.5)
{
    $is_allowed = apply_filters('aiomatic_is_ai_image_allowed', true, $text);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if($source_image_url != '')
    {
        if($stable_model == 'stable-diffusion-ultra' || $stable_model == 'stable-diffusion-3-0-turbo' || $stable_model == 'stable-diffusion-3-0-large' || $stable_model == 'stable-diffusion-3-0-medium' || $stable_model == 'sd3.5-large' || $stable_model == 'sd3.5-large-turbo' || $stable_model == 'sd3.5-medium')
        {}
        else
        {
            //care if you change this to AIOMATIC_STABLE_DEFAULT_MODE, as not all stability ai endpoints allow image sending
            $stable_model == 'sd3.5-large-turbo';
        }
    }
    $text = apply_filters('aiomatic_modify_ai_image_query', $text);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(empty($stable_model))
    {
        if (!isset($aiomatic_Main_Settings['stable_model']) || trim($aiomatic_Main_Settings['stable_model']) == '') 
        {
            $stable_model = AIOMATIC_STABLE_DEFAULT_MODE;
        }
        else
        {
            $stable_model = trim($aiomatic_Main_Settings['stable_model']);
        }
    }
    if (!isset($aiomatic_Main_Settings['stability_app_id']) || trim($aiomatic_Main_Settings['stability_app_id']) == '') 
    {
        $error = 'You need to enter a Stability.AI API key in the plugin\'s "Settings" menu to use this feature!';
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['stability_app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_stability_api_key', $token);
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'stable';
    $maxResults = 1;
    $available_tokens = 1000;
    $temperature = 1;
    if($stable_model == 'stable-diffusion-xl-beta-v2-2-2')
    {
        if(intval($width) > 512)
        {
            $width = 512;
        }
        if(intval($height) > 512)
        {
            $height = 512;
        }
    }
    if(!isset($aiomatic_Main_Settings['no_img_translate']) || $aiomatic_Main_Settings['no_img_translate'] != 'on')
    {
        $text_trans = aiomatic_translate_stability($text);
        if($text_trans != $text && !empty($text_trans))
        {
            aiomatic_log_to_file('Stability.ai prompt translated from: "' . $text . '" to: "' . $text_trans . '"');
            $text = $text_trans;
        }
    }
    if(strlen($text) > 2000)
    {
        $text = aiomatic_substr($text, 0, 2000);
    }
    $query = new Aiomatic_Query($text, $available_tokens, $stable_model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, $width . 'x' . $height, '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = 'Image generator is rate limited: ' . $ok;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
    {
        aiomatic_log_to_file('Generating Stability.AI Image using prompt: ' . $text . ' height: ' . $height . ' width: ' . $width);
    }
    if(intval($height) < 300)
    {
        $error = 'Invalid height (>300): ' . $height;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(intval($width) < 300 )
    {
        $error = 'Invalid width (>300): ' . $width;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
    {
        aiomatic_log_to_file('Generating AI Stable Difussion image using model: ' . $stable_model . ' and prompt: ' . $text);
    }
    if (!isset($aiomatic_Main_Settings['steps']) || trim($aiomatic_Main_Settings['steps']) == '') 
    {
        $steps = '50';
    }
    else
    {
        $steps = trim($aiomatic_Main_Settings['steps']);
    }
    if (!isset($aiomatic_Main_Settings['cfg_scale']) || trim($aiomatic_Main_Settings['cfg_scale']) == '') 
    {
        $cfg_scale = '7';
    }
    else
    {
        $cfg_scale = trim($aiomatic_Main_Settings['cfg_scale']);
    }
    if (!isset($aiomatic_Main_Settings['cfg_seed']) || trim($aiomatic_Main_Settings['cfg_seed']) == '') 
    {
        $cfg_seed = '';
    }
    else
    {
        $cfg_seed = trim($aiomatic_Main_Settings['cfg_seed']);
    }
    if (!isset($aiomatic_Main_Settings['clip_guidance_preset']) || trim($aiomatic_Main_Settings['clip_guidance_preset']) == '') 
    {
        $clip_guidance_preset = 'NONE';
    }
    else
    {
        $clip_guidance_preset = trim($aiomatic_Main_Settings['clip_guidance_preset']);
    }
    if (!isset($aiomatic_Main_Settings['clip_style_preset']) || trim($aiomatic_Main_Settings['clip_style_preset']) == '') 
    {
        $clip_style_preset = 'NONE';
    }
    else
    {
        $clip_style_preset = trim($aiomatic_Main_Settings['clip_style_preset']);
    }
    if (!isset($aiomatic_Main_Settings['sampler']) || trim($aiomatic_Main_Settings['sampler']) == '') 
    {
        $sampler = 'auto';
    }
    else
    {
        $sampler = trim($aiomatic_Main_Settings['sampler']);
    }
    if(intval($steps) < 10 || intval($steps) > 250)
    {
        $error = 'Invalid steps count provided (10-250): ' . intval($steps);
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(intval($cfg_scale) < 0 || intval($cfg_scale) > 35)
    {
        $error = 'Invalid cfg_scale count provided (0-35): ' . intval($cfg_scale);
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(intval($cfg_seed) < 0 || intval($cfg_seed) > 4294967295)
    {
        $error = 'Invalid cfg_seed count provided (0-4294967295): ' . intval($cfg_seed);
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if($stable_model == 'stable-diffusion-core')
    {
        $api_url = 'https://api.stability.ai/v2beta/stable-image/generate/core';
        $ch = curl_init();
        if($ch === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after initial failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to create Stability curl request.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: multipart/form-data', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = [
            'prompt' => $text,
            'output_format' => 'png'
        ];
        if(trim($cfg_seed) != '')
        {
            $data['seed'] = trim($cfg_seed);
        }
        if(trim($clip_style_preset) != '' && $clip_style_preset != 'NONE')
        {
            $data['style_preset'] = trim($clip_style_preset);
        }
        $data['height'] = $height;
        $data['width'] = $width;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $ai_response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if($info['http_code'] != 200)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after http_code failure: ' . print_r($api_url, true) . ' code: ' . $info['http_code'] . ' response: ' . print_r($ai_response, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $er = ' ';
                $json_resp = json_decode($ai_response, true);
                if($json_resp !== null)
                {
                    $er .= 'Error: ' . $json_resp['name'] . ': ' . $json_resp['message'];
                }
                aiomatic_log_to_file('Invalid return code from API: ' . $info['http_code'] . $er);
                $error = 'Failed to generate the image, please try again later! Error: ' . $json_resp['message'];
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_close($ch);
        if($ai_response === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to get AI response: ' . $api_url;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        else
        {
            $json_resp = json_decode($ai_response, true);
            if($json_resp === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after decode failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Failed to decode AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            if(!isset($json_resp['image']))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Invalid AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            $upload_dir = wp_upload_dir();
            $keyword_class = new Aiomatic_keywords();
            $filename = $keyword_class->keywords($text, 4);
            $filename = str_replace(' ', '-', $filename);
            if(empty($filename))
            {
                $seed = rand();
                if(isset($json_resp['seed']))
                {
                    $seed = $json_resp['seed'];
                }
                $filename = $seed . '.png';
            }
            else
            {
                $filename .= '-' . rand(1,99999) . '.png';
            }
            if (wp_mkdir_p($upload_dir['path']))
            {
                $file = $upload_dir['path'] . '/' . $filename;
                $ret_path = $upload_dir['url'] . '/' . $filename;
            }
            else
            {
                $file = $upload_dir['basedir'] . '/' . $filename;
                $ret_path = $upload_dir['baseurl'] . '/' . $filename;
            }
            $reason = ''; 
            if(isset($json_resp['finish_reason']))
            {
                $reason = $json_resp['finish_reason'];
                if($reason == 'ERROR')
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after error failure: ' . print_r($api_url, true));
                        sleep(pow(2, $retry_count));
                        return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                    }
                    else
                    {
                        $error = 'An error was encountered during API call: ' . $ai_response;
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                }
                elseif($reason == 'CONTENT_FILTERED')
                {
                    aiomatic_log_to_file('The image was filtered, by the nudity filter, blurred parts may appear in it, prompt: ' . $ret_path);
                }
            }
            $json_resp = apply_filters( 'aiomatic_stability_reply_raw', $json_resp, $text );
            $img = $json_resp['image'];
            apply_filters( 'aiomatic_ai_reply', $img, $query );
            if($returnbase64 == true)
            {
                return $img;
            }
            $rezi = aiomatic_base64_to_jpeg($img, $file, $ret_path);
            if($rezi !== false && $rezi !== '')
            {
                if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
                {
                    $attached_id = '';
                    $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $text);
                    if($localpath !== false)
                    {
                        unlink($rezi[0]);
                        $localrez = array();
                        $localrez[0] = $localpath[1];
                        $localrez[1] = $localpath[0];
                        $rezi = $localrez;
                    }
                }
            }
            return $rezi;
        }
    }
    elseif($stable_model == 'stable-diffusion-ultra')
    {
        $api_url = 'https://api.stability.ai/v2beta/stable-image/generate/ultra';
        $ch = curl_init();
        if($ch === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after initial failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to create Stability curl request.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: multipart/form-data', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = [
            'prompt' => $text,
            'output_format' => 'png'
        ];
        if(trim($cfg_seed) != '')
        {
            $data['seed'] = trim($cfg_seed);
        }
        if($width > $height)
        {
            $data['aspect_ratio'] = '16:9';
        }
        elseif($width < $height)
        {
            $data['aspect_ratio'] = '9:16';
        }
        else
        {
            $data['aspect_ratio'] = '1:1';
        }
        if (!empty($source_image_url)) {
            
            global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
                include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
                wp_filesystem($creds);
            }
            $temp_file = tempnam(sys_get_temp_dir(), 'aiomatic_');
            $image_data = $wp_filesystem->get_contents($source_image_url);
            if ($image_data === false) {
                $error = 'Failed to download source image.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            $wp_filesystem->put_contents($temp_file, $image_data);
            if ($temp_file === false) {
                $error = 'Failed to save source image.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            $data['image'] = new CURLFile($temp_file);
            $data['strength'] = 1 - floatval($image_strength);
            $data['mode'] = 'image-to-image';
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $ai_response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if($info['http_code'] != 200)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after http_code failure: ' . print_r($api_url, true) . ' code: ' . $info['http_code'] . ' response: ' . print_r($ai_response, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $er = ' ';
                $json_resp = json_decode($ai_response, true);
                if($json_resp !== null)
                {
                    $er .= 'Error: ' . $json_resp['name'] . ': ' . $json_resp['message'];
                }
                aiomatic_log_to_file('Invalid return code from API: ' . $info['http_code'] . $er);
                $error = 'Failed to generate the image, please try again later! Error: ' . $json_resp['message'];
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_close($ch);
        if($ai_response === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to get AI response: ' . $api_url;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        else
        {
            $json_resp = json_decode($ai_response, true);
            if($json_resp === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after decode failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Failed to decode AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            if(!isset($json_resp['image']))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Invalid AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            $upload_dir = wp_upload_dir();
            $keyword_class = new Aiomatic_keywords();
            $filename = $keyword_class->keywords($text, 4);
            $filename = str_replace(' ', '-', $filename);
            if(empty($filename))
            {
                $seed = rand();
                if(isset($json_resp['seed']))
                {
                    $seed = $json_resp['seed'];
                }
                $filename = $seed . '.png';
            }
            else
            {
                $filename .= '-' . rand(1,99999) . '.png';
            }
            if (wp_mkdir_p($upload_dir['path']))
            {
                $file = $upload_dir['path'] . '/' . $filename;
                $ret_path = $upload_dir['url'] . '/' . $filename;
            }
            else
            {
                $file = $upload_dir['basedir'] . '/' . $filename;
                $ret_path = $upload_dir['baseurl'] . '/' . $filename;
            }
            $reason = ''; 
            if(isset($json_resp['finish_reason']))
            {
                $reason = $json_resp['finish_reason'];
                if($reason == 'ERROR')
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after error failure: ' . print_r($api_url, true));
                        sleep(pow(2, $retry_count));
                        return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                    }
                    else
                    {
                        $error = 'An error was encountered during API call: ' . $ai_response;
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                }
                elseif($reason == 'CONTENT_FILTERED')
                {
                    aiomatic_log_to_file('The image was filtered, by the nudity filter, blurred parts may appear in it, prompt: ' . $ret_path);
                }
            }
            $json_resp = apply_filters( 'aiomatic_stability_reply_raw', $json_resp, $text );
            $img = $json_resp['image'];
            apply_filters( 'aiomatic_ai_reply', $img, $query );
            if($returnbase64 == true)
            {
                return $img;
            }
            $rezi = aiomatic_base64_to_jpeg($img, $file, $ret_path);
            if($rezi !== false && $rezi !== '')
            {
                if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
                {
                    $attached_id = '';
                    $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $text);
                    if($localpath !== false)
                    {
                        unlink($rezi[0]);
                        $localrez = array();
                        $localrez[0] = $localpath[1];
                        $localrez[1] = $localpath[0];
                        $rezi = $localrez;
                    }
                }
            }
            return $rezi;
        }
    }
    elseif($stable_model == 'stable-diffusion-3-0-turbo' || $stable_model == 'stable-diffusion-3-0-large' || $stable_model == 'stable-diffusion-3-0-medium' || $stable_model == 'sd3.5-large' || $stable_model == 'sd3.5-large-turbo' || $stable_model == 'sd3.5-medium')
    {
        $api_url = 'https://api.stability.ai/v2beta/stable-image/generate/sd3';
        $ch = curl_init();
        if($ch === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after initial failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to create Stability curl request.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: multipart/form-data', 'Authorization: Bearer ' . $token));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($stable_model == 'stable-diffusion-3-0-turbo')
        {
            $smodel = 'sd3-large-turbo';
        }
        elseif($stable_model == 'stable-diffusion-3-0-large')
        {
            $smodel = 'sd3-large';
        }
        elseif($stable_model == 'stable-diffusion-3-0-medium')
        {
            $smodel = 'sd3-medium';
        }
        else
        {
            $smodel = $stable_model;
        }
        $data = [
            'prompt' => $text,
            'mode' => 'text-to-image',
            'output_format' => 'png',
            'model' => $smodel
        ];
        if(trim($cfg_seed) != '')
        {
            $data['seed'] = trim($cfg_seed);
        }
        $data['height'] = $height;
        $data['width'] = $width;
        if (!empty($source_image_url)) {
            
            global $wp_filesystem;
            if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
                include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
                wp_filesystem($creds);
            }
            $temp_file = tempnam(sys_get_temp_dir(), 'aiomatic_');
            $image_data = $wp_filesystem->get_contents($source_image_url);
            if ($image_data === false) {
                $error = 'Failed to download source image.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            $wp_filesystem->put_contents($temp_file, $image_data);
            if ($temp_file === false) {
                $error = 'Failed to save source image.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            $data['image'] = new CURLFile($temp_file);
            $data['strength'] = 1 - floatval($image_strength);
            $data['mode'] = 'image-to-image';
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $ai_response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if($info['http_code'] != 200)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after http_code failure: ' . print_r($api_url, true) . ' code: ' . $info['http_code'] . ' response: ' . print_r($ai_response, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $er = ' ';
                $json_resp = json_decode($ai_response, true);
                if($json_resp !== null)
                {
                    if(!isset($json_resp['message']))
                    {
                        $json_resp['message'] = 'Error';
                    }
                    $er .= 'Error: ' . $json_resp['name'] . ': ' . $json_resp['message'];
                }
                aiomatic_log_to_file('Invalid return code from API: ' . $info['http_code'] . $er);
                $error = 'Failed to generate the image, please try again later! Error: ' . $json_resp['message'];
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_close($ch);
        if($ai_response === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to get AI response: ' . $api_url;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        else
        {
            $json_resp = json_decode($ai_response, true);
            if($json_resp === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after decode failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Failed to decode AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            if(!isset($json_resp['image']))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Invalid AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            $upload_dir = wp_upload_dir();
            $keyword_class = new Aiomatic_keywords();
            $filename = $keyword_class->keywords($text, 4);
            $filename = str_replace(' ', '-', $filename);
            if(empty($filename))
            {
                $seed = rand();
                if(isset($json_resp['seed']))
                {
                    $seed = $json_resp['seed'];
                }
                $filename = $seed . '.png';
            }
            else
            {
                $filename .= '-' . rand(1,99999) . '.png';
            }
            if (wp_mkdir_p($upload_dir['path']))
            {
                $file = $upload_dir['path'] . '/' . $filename;
                $ret_path = $upload_dir['url'] . '/' . $filename;
            }
            else
            {
                $file = $upload_dir['basedir'] . '/' . $filename;
                $ret_path = $upload_dir['baseurl'] . '/' . $filename;
            }
            $reason = ''; 
            if(isset($json_resp['finish_reason']))
            {
                $reason = $json_resp['finish_reason'];
                if($reason == 'ERROR')
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after error failure: ' . print_r($api_url, true));
                        sleep(pow(2, $retry_count));
                        return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                    }
                    else
                    {
                        $error = 'An error was encountered during API call: ' . $ai_response;
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                }
                elseif($reason == 'CONTENT_FILTERED')
                {
                    aiomatic_log_to_file('The image was filtered, by the nudity filter, blurred parts may appear in it, prompt: ' . $ret_path);
                }
            }
            $json_resp = apply_filters( 'aiomatic_stability_reply_raw', $json_resp, $text );
            $img = $json_resp['image'];
            apply_filters( 'aiomatic_ai_reply', $img, $query );
            if($returnbase64 == true)
            {
                return $img;
            }
            $rezi = aiomatic_base64_to_jpeg($img, $file, $ret_path);
            if($rezi !== false && $rezi !== '')
            {
                if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
                {
                    $attached_id = '';
                    $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $text);
                    if($localpath !== false)
                    {
                        unlink($rezi[0]);
                        $localrez = array();
                        $localrez[0] = $localpath[1];
                        $localrez[1] = $localpath[0];
                        $rezi = $localrez;
                    }
                }
            }
            return $rezi;
        }
    }
    else
    {
        if(intval($height) > 2048)
        {
            $height = 2048;
        }
        if(intval($height) < 512)
        {
            $height = 512;
        }
        if(intval($width) > 2048)
        {
            $width = 2048;
        }
        if(intval($width) < 512)
        {
            $width = 512;
        }
        if(intval($width) * intval($height) > 1048576)
        {
            $error = 'Width x Height must not be greater than 1 Megapixel (1048576), current is: ' . intval($width) * intval($height);
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        $api_url = 'https://api.stability.ai/v1/generation/' . $stable_model . '/text-to-image';
        $ch = curl_init();
        if($ch === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after initial failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to create Stability curl request.';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json', 'Authorization: ' . $token));
        $post_fields = '{"samples": 1,';
        if(trim($cfg_scale) != '' && trim($cfg_scale) != '7')
        {
            $post_fields .= '"cfg_scale": ' . trim($cfg_scale) . ',';
        }
        if(trim($cfg_seed) != '')
        {
            $post_fields .= '"seed": ' . trim($cfg_seed) . ',';
        }
        if(trim($clip_guidance_preset) != '' && trim($clip_guidance_preset) != 'NONE')
        {
            $post_fields .= '"clip_guidance_preset": "' . trim($clip_guidance_preset) . '",';
        }
        if(trim($clip_style_preset) != '' && trim($clip_style_preset) != 'NONE')
        {
            $post_fields .= '"style_preset": "' . trim($clip_style_preset) . '",';
        }
        if(trim($height) != '' && trim($height) != '512')
        {
            $post_fields .= '"height": ' . trim($height) . ',';
        }
        if(trim($width) != '' && trim($width) != '512')
        {
            $post_fields .= '"width": ' . trim($width) . ',';
        }
        if(trim($steps) != '' && trim($steps) != '50')
        {
            $post_fields .= '"steps": ' . trim($steps) . ',';
        }
        if(trim($sampler) != '' && trim($sampler) != 'auto')
        {
            $post_fields .= '"sampler": "' . trim($sampler) . '",';
        }
        $post_fields .= '"text_prompts": [{"text": "' . str_replace('"', '\'', $text) . '","weight": 1}]}';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $ai_response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if($info['http_code'] != 200)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after http_code failure: ' . print_r($api_url, true) . ' code: ' . $info['http_code'] . ' response: ' . print_r($ai_response, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $er = ' ';
                $json_resp = json_decode($ai_response, true);
                if($json_resp !== null)
                {
                    $er .= 'Error: ' . $json_resp['name'] . ': ' . $json_resp['message'];
                }
                aiomatic_log_to_file('Invalid return code from API: ' . $info['http_code'] . $er);
                aiomatic_log_to_file('PostFields: ' . $post_fields);
                $error = 'Failed to generate the image, please try again later! Error: ' . $json_resp['message'];
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        curl_close($ch);
        if($ai_response === false)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
            }
            else
            {
                $error = 'Failed to get AI response: ' . $api_url;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        else
        {
            $json_resp = json_decode($ai_response, true);
            if($json_resp === null)
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after decode failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Failed to decode AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            if(!isset($json_resp['artifacts'][0]['base64']))
            {
                if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                {
                    aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after response failure: ' . print_r($api_url, true));
                    sleep(pow(2, $retry_count));
                    return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                }
                else
                {
                    $error = 'Invalid AI response: ' . $ai_response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            $upload_dir = wp_upload_dir();
            $keyword_class = new Aiomatic_keywords();
            $filename = $keyword_class->keywords($text, 4);
            $filename = str_replace(' ', '-', $filename);
            if(empty($filename))
            {
                $seed = rand();
                if(isset($json_resp['artifacts'][0]['seed']))
                {
                    $seed = $json_resp['artifacts'][0]['seed'];
                }
                $filename = $seed . '.png';
            }
            else
            {
                $filename .= '-' . rand(1,99999) . '.png';
            }
            if (wp_mkdir_p($upload_dir['path']))
            {
                $file = $upload_dir['path'] . '/' . $filename;
                $ret_path = $upload_dir['url'] . '/' . $filename;
            }
            else
            {
                $file = $upload_dir['basedir'] . '/' . $filename;
                $ret_path = $upload_dir['baseurl'] . '/' . $filename;
            }
            $reason = ''; 
            if(isset($json_resp['artifacts'][0]['finishReason']))
            {
                $reason = $json_resp['artifacts'][0]['finishReason'];
                if($reason == 'ERROR')
                {
                    if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
                    {
                        aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability API call after error failure: ' . print_r($api_url, true));
                        sleep(pow(2, $retry_count));
                        return aiomatic_generate_stability_image($text, $height, $width, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal, $stable_model, $source_image_url, $image_strength);
                    }
                    else
                    {
                        $error = 'An error was encountered during API call: ' . $ai_response;
                        $error = apply_filters('aiomatic_modify_ai_error', $error);
                        return false;
                    }
                }
                elseif($reason == 'CONTENT_FILTERED')
                {
                    aiomatic_log_to_file('The image was filtered, by the nudity filter, blurred parts may appear in it, prompt: ' . $ret_path);
                }
            }
            $json_resp = apply_filters( 'aiomatic_stability_reply_raw', $json_resp, $text );
            $img = $json_resp['artifacts'][0]['base64'];
            apply_filters( 'aiomatic_ai_reply', $img, $query );
            if($returnbase64 == true)
            {
                return $img;
            }
            $rezi = aiomatic_base64_to_jpeg($img, $file, $ret_path);
            if($rezi !== false && $rezi !== '')
            {
                if(isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
                {
                    $attached_id = '';
                    $localpath = aiomatic_copy_image_locally($rezi[1], $attached_id, '', false, $text);
                    if($localpath !== false)
                    {
                        unlink($rezi[0]);
                        $rezi = $localrez;
                    }
                }
            }
            return $rezi;
        }
    }
}
function aiomatic_generate_replicate_image($text = '', $height = '512', $width = '512', $env = '', $retry_count = 0, &$error = '', $nolocal = false, $replicate_model = false)
{
    $is_allowed = apply_filters('aiomatic_is_ai_image_allowed', true, $text);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $text = apply_filters('aiomatic_modify_ai_image_query', $text);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(empty($replicate_model))
    {
        if (!isset($aiomatic_Main_Settings['replicate_image_model']) || trim($aiomatic_Main_Settings['replicate_image_model']) == '') 
        {
            $replicate_model = AIOMATIC_REPLICATE_DEFAULT_API_VERSION;
        }
        else
        {
            $replicate_model = trim($aiomatic_Main_Settings['replicate_image_model']);
        }
    }
    if (!isset($aiomatic_Main_Settings['replicate_app_id']) || trim($aiomatic_Main_Settings['replicate_app_id']) == '') 
    {
        $error = 'You need to enter a Replicate API key in the plugin\'s "Settings" menu to use this feature!';
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['replicate_app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_replicate_api_key', $token);
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'replicate';
    $maxResults = 1;
    $available_tokens = 1000;
    $temperature = 1;
    if(!isset($aiomatic_Main_Settings['no_img_translate']) || $aiomatic_Main_Settings['no_img_translate'] != 'on')
    {
        $text_trans = aiomatic_translate_stability($text);
        if($text_trans != $text && !empty($text_trans))
        {
            aiomatic_log_to_file('Replicate prompt translated from: "' . $text . '" to: "' . $text_trans . '"');
            $text = $text_trans;
        }
    }
    if(strlen($text) > 2000)
    {
        $text = aiomatic_substr($text, 0, 2000);
    }
    $query = new Aiomatic_Query($text, $available_tokens, $replicate_model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, $width . 'x' . $height, '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = 'Image generator is rate limited: ' . $ok;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(intval($height) < 512 || intval($height) > 2048)
    {
        $error = 'Invalid height (512-2048): ' . $height;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(intval($width) < 512 || intval($width) > 2048)
    {
        $error = 'Invalid width (512-2048): ' . $width;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(intval($width) * intval($height) > 1048576)
    {
        $error = 'Width x Height must not be greater than 1 Megapixel (1048576), current is: ' . intval($width) * intval($height);
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on' && AIOMATIC_IS_DEBUG === true)
    {
        aiomatic_log_to_file('Generating Replicate image using model: ' . $replicate_model . ' and prompt: ' . $text);
    }
    if(isset($aiomatic_Main_Settings['prompt_strength']) && $aiomatic_Main_Settings['prompt_strength'] != '')
    {
        $prompt_strength = floatval($aiomatic_Main_Settings['prompt_strength']);
    }
    else
    {
        $prompt_strength = 0.8;
    }
    if(isset($aiomatic_Main_Settings['num_inference_steps']) && $aiomatic_Main_Settings['num_inference_steps'] != '')
    {
        $num_inference_steps = intval($aiomatic_Main_Settings['num_inference_steps']);
    }
    else
    {
        $num_inference_steps = 4;
    }
    if(isset($aiomatic_Main_Settings['ai_scheduler']) && $aiomatic_Main_Settings['ai_scheduler'] != '')
    {
        $ai_scheduler = intval($aiomatic_Main_Settings['ai_scheduler']);
    }
    else
    {
        $ai_scheduler = 'DPMSolverMultistep';
    }
    if(isset($aiomatic_Main_Settings['replicate_ratio']) && $aiomatic_Main_Settings['replicate_ratio'] != '' && $aiomatic_Main_Settings['replicate_ratio'] != 'default')
    {
        $replicate_ratio = $aiomatic_Main_Settings['replicate_ratio'];
    }
    else
    {
        $replicate_ratio = '';
    }
    $queryArray = array();
    if(isset($aiomatic_Main_Settings['custom_params_replicate']) && $aiomatic_Main_Settings['custom_params_replicate'] != '')
    {
        parse_str($aiomatic_Main_Settings['custom_params_replicate'], $queryArray);
    }
    $body = array(
        'version' => $replicate_model,
        'input' => array(
            'prompt' => $text,
            'num_outputs' => 1,
            'negative_prompt' => '',
            'width' => intval($width),
            'height' => intval($height),
            'prompt_strength' => $prompt_strength,
            'num_inference_steps' => $num_inference_steps,
            'scheduler' => $ai_scheduler,
        )
    );
    if(!empty($replicate_ratio))
    {
        $body['input']['aspect_ratio'] = $replicate_ratio;
    }
    if(!empty($queryArray))
    {
        foreach($queryArray as $indxd => $qa)
        {
            if(is_numeric($qa))
            {
                $queryArray[$indxd] = floatval($qa);
            }
        }
        $body['input'] = array_merge($body['input'], $queryArray);
    }
    $headers = array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Token ' . $token
    );
    try {
        $aiomatic_response = wp_remote_post('https://api.replicate.com/v1/predictions', array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
        ));
        if (is_wp_error($aiomatic_response)) {
            $error = 'Error duing Replicate image creation: ' . $aiomatic_response->get_error_message();
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        } else {
            $response_body = isset($aiomatic_response['body']) && !empty($aiomatic_response['body']) ? json_decode($aiomatic_response['body'], true) : false;
            if (isset($response_body['detail']) && !empty($response_body['detail'])) {
                $error = 'Error in Replicate image processing: ' . $response_body['detail'];
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            } elseif (!isset($response_body['urls']['get'])) {
                $error = 'Empty Replicate results';
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            } else {
                $images = aiomatic_process_replicate_images($response_body['urls']['get'], $headers);
                if (!is_array($images)) {
                    $images = array($images);
                }
            }
        }
    } catch (\Exception $exception) {
        $error = 'Failed to generate Replicate image: ' . $exception->getMessage();
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }

    $upload_dir = wp_upload_dir();
    $keyword_class = new Aiomatic_keywords();
    $filename = $keyword_class->keywords($text, 4);
    $filename = str_replace(' ', '-', $filename);
    if(empty($filename))
    {
        $seed = rand();
        $filename = $seed . '.png';
    }
    else
    {
        $filename .= '-' . rand(1,99999) . '.png';
    }
    if (wp_mkdir_p($upload_dir['path']))
    {
        $file = $upload_dir['path'] . '/' . $filename;
        $ret_path = $upload_dir['url'] . '/' . $filename;
    }
    else
    {
        $file = $upload_dir['basedir'] . '/' . $filename;
        $ret_path = $upload_dir['baseurl'] . '/' . $filename;
    }
    
    $rezi = $images[0];
    $return_url = $rezi;

    apply_filters( 'aiomatic_ai_reply', $rezi, $query );
    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $nolocal == false) 
    {
        $attached_id = '';
        $localpath = aiomatic_copy_image_locally($rezi, $attached_id, '', false, $text);
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
            $return_url = $localpath[0];
        }
        else
        {
            $return_url = $rezi;
        }
    }
    else
    {
        $return_url = $rezi;
    }
    return $return_url;
}
function aiomatic_generate_ideogram_image($text = '', $height = '512', $width = '512', $env = '', $retry_count = 0, &$error = '', $nolocal = false, $ideogram_model = false)
{
    $is_allowed = apply_filters('aiomatic_is_ai_image_allowed', true, $text);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $text = apply_filters('aiomatic_modify_ai_image_query', $text);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(empty($ideogram_model))
    {
        if (!isset($aiomatic_Main_Settings['ideogram_image_model']) || trim($aiomatic_Main_Settings['ideogram_image_model']) == '') 
        {
            $ideogram_model = AIOMATIC_IDEOGRAM_MODELS[0];
        }
        else
        {
            $ideogram_model = trim($aiomatic_Main_Settings['ideogram_image_model']);
        }
    }
    if (!isset($aiomatic_Main_Settings['ideogram_app_id']) || trim($aiomatic_Main_Settings['ideogram_app_id']) == '') 
    {
        $error = 'You need to enter a Ideogram API key in the plugin\'s "Settings" menu to use this feature!';
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['ideogram_app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_ideogram_api_key', $token);
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'ideogram';
    $maxResults = 1;
    $available_tokens = 1000;
    $temperature = 1;
    if(!isset($aiomatic_Main_Settings['no_img_translate']) || $aiomatic_Main_Settings['no_img_translate'] != 'on')
    {
        $text_trans = aiomatic_translate_stability($text);
        if($text_trans != $text && !empty($text_trans))
        {
            aiomatic_log_to_file('Ideogram prompt translated from: "' . $text . '" to: "' . $text_trans . '"');
            $text = $text_trans;
        }
    }
    if(strlen($text) > 3000)
    {
        $text = aiomatic_substr($text, 0, 3000);
    }
    $query = new Aiomatic_Query($text, $available_tokens, $ideogram_model, $temperature, $stop, $env, $mode, $token, $session, $maxResults, $width . 'x' . $height, '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = 'Image generator is rate limited: ' . $ok;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
    {
        aiomatic_log_to_file('Generating Ideogram Image using prompt: ' . $text);
    }
    if(isset($aiomatic_Main_Settings['ideogram_magic_prompt']) && $aiomatic_Main_Settings['ideogram_magic_prompt'] != '')
    {
        $ideogram_magic_prompt = $aiomatic_Main_Settings['ideogram_magic_prompt'];
    }
    else
    {
        $ideogram_magic_prompt = 'AUTO';
    }
    if(isset($aiomatic_Main_Settings['ideogram_style']) && $aiomatic_Main_Settings['ideogram_style'] != '')
    {
        $ideogram_style = $aiomatic_Main_Settings['ideogram_style'];
    }
    else
    {
        $ideogram_style = 'AUTO';
    }
    if(isset($aiomatic_Main_Settings['ideogram_palette']) && $aiomatic_Main_Settings['ideogram_palette'] != '')
    {
        $ideogram_palette = $aiomatic_Main_Settings['ideogram_palette'];
    }
    else
    {
        $ideogram_palette = '';
    }
    if(isset($aiomatic_Main_Settings['ideogram_ratio']) && $aiomatic_Main_Settings['ideogram_ratio'] != '' && $aiomatic_Main_Settings['ideogram_ratio'] != 'default')
    {
        $ideogram_ratio = $aiomatic_Main_Settings['ideogram_ratio'];
    }
    else
    {
        $ideogram_ratio = '';
    }
    $body = array(
    'image_request' => array(
        'prompt' => $text,
        'model' => $ideogram_model,
        'magic_prompt_option' => $ideogram_magic_prompt,
    ));
    if(!empty($ideogram_ratio) && $ideogram_ratio != 'default')
    {
        $body['image_request']['aspect_ratio'] = $ideogram_ratio;
    }
    if(!empty($ideogram_style) && $ideogram_style != 'default' && $ideogram_model != 'V_1' && $ideogram_model != 'V_1_TURBO')
    {
        $body['image_request']['style_type'] = $ideogram_style;
    }
    if(!empty($ideogram_palette) && $ideogram_palette != 'default')
    {
        $body['image_request']['color_palette']['name'] = $ideogram_palette;
    }
    $headers = array(
        'Api-Key' => $token,
        'Content-Type' => 'application/json'
    );
    $images = array();
    try {
        if ($ideogram_model === 'V_3') 
        {
            $v3_endpoint = 'https://api.ideogram.ai/v1/ideogram-v3/generate';
            $form_data = [
                'prompt' => $text,
                'rendering_speed' => 'TURBO',
                'magic_prompt' => $ideogram_magic_prompt,
                'style_type' => $ideogram_style !== 'default' ? $ideogram_style : 'GENERAL',
                'num_images' => 1
            ];

            if (!empty($ideogram_ratio) && $ideogram_ratio !== 'default') {
                $ideogram_ratio = str_replace('ASPECT_', '', $ideogram_ratio);
                $ideogram_ratio = str_replace('_', 'x', $ideogram_ratio);
                $form_data['aspect_ratio'] = $ideogram_ratio;
            }

            if (!empty($ideogram_palette) && $ideogram_palette !== 'default') {
                $form_data['color_palette'] = json_encode([
                    'name' => $ideogram_palette
                ]);
            }

            $boundary = '----WebKitFormBoundary' . uniqid();
            $body = '';

            foreach ($form_data as $key => $value) {
                $body .= "--$boundary\r\n";
                $body .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
                $body .= "$value\r\n";
            }
            $body .= "--$boundary--\r\n";

            $headers = [
                'Api-Key' => $token,
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary
            ];

            $aiomatic_response = wp_remote_post($v3_endpoint, [
                'headers' => $headers,
                'body' => $body,
                'timeout' => AIOMATIC_DEFAULT_BIG_TIMEOUT
            ]);
        }
        else
        {
            $aiomatic_response = wp_remote_post('https://api.ideogram.ai/generate', array(
                'headers' => $headers,
                'body' => json_encode($body),
                'timeout'     => AIOMATIC_DEFAULT_BIG_TIMEOUT,
            ));
        }
        if (is_wp_error($aiomatic_response)) {
            $error = 'Error duing Ideogram image creation: ' . $aiomatic_response->get_error_message();
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        } else {
            $response_body = isset($aiomatic_response['body']) && !empty($aiomatic_response['body']) ? json_decode($aiomatic_response['body'], true) : false;
            if (!isset($response_body['data'][0]['url']) || empty($response_body['data'][0]['url'])) {
                $error = 'Error in Ideogram image processing: ' . print_r($response_body, true);
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            } else {
                $images = array($response_body['data'][0]['url']);
            }
        }
    } catch (\Exception $exception) {
        $error = 'Failed to generate Ideogram image: ' . $exception->getMessage();
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }

    $upload_dir = wp_upload_dir();
    $keyword_class = new Aiomatic_keywords();
    $filename = $keyword_class->keywords($text, 4);
    $filename = str_replace(' ', '-', $filename);
    if(empty($filename))
    {
        $seed = rand();
        $filename = $seed . '.png';
    }
    else
    {
        $filename .= '-' . rand(1,99999) . '.png';
    }
    if (wp_mkdir_p($upload_dir['path']))
    {
        $file = $upload_dir['path'] . '/' . $filename;
        $ret_path = $upload_dir['url'] . '/' . $filename;
    }
    else
    {
        $file = $upload_dir['basedir'] . '/' . $filename;
        $ret_path = $upload_dir['baseurl'] . '/' . $filename;
    }
    
    $rezi = $images[0];
    $return_url = $rezi;

    apply_filters( 'aiomatic_ai_reply', $rezi, $query );
    if (isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $nolocal == false) 
    {
        $attached_id = '';
        $localpath = aiomatic_copy_image_locally($rezi, $attached_id, '', false, $text);
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
            $return_url = $localpath[0];
        }
        else
        {
            $return_url = $rezi;
        }
    }
    else
    {
        $return_url = $rezi;
    }
    return $return_url;
}

function aiomatic_generate_stability_video($image_url = '', $image_size = '768x768', $env = '', $retry_count = 0, $returnbase64 = false, &$error = '', $nolocal = false)
{
    $all_stable_video_sizes = ['768x768' => '768x768', '1024x576' => '1024x576', '576x1024' => '576x1024'];
    $is_allowed = apply_filters('aiomatic_is_ai_video_allowed', true, $image_url);
    if ( $is_allowed !== true ) {
        $error = is_string( $is_allowed ) ? $is_allowed : esc_html__('You are not allowed to do this query', 'aiomatic-automatic-ai-content-writer');
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
        wp_filesystem($creds);
    }
    $image_url = apply_filters('aiomatic_modify_ai_video_url', $image_url);
    if($image_size != '')
    {
        $expl = explode('x', $image_size);
        if(!isset($expl[1]))
        {
            $error = 'Invalid resize size provided: ' . $image_size;
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        $width = $expl[0];
        $height = $expl[1];
        $upload_dir = wp_upload_dir();
        if(substr( $image_url, 0, 10 ) === "data:image")
        {
            $data = explode(',', $image_url);
            if(isset($data[1]))
            {
                $image_data = base64_decode($data[1]);
                if($image_data === FALSE)
                {
                    $error = 'Failed to decode image URL data: ' . $image_url;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
            }
            else
            {
                $error = 'Failed to download image URL post data: ' . $image_url;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            preg_match('{data:image/(.*?);}', $image_url, $ex_matches);
            if(isset($ex_matches[1]))
            {
                $image_url = 'image.' . $ex_matches[1];
            }
            else
            {
                $image_url = 'image.jpg';
            }
        }
        else
        {
            $image_data = aiomatic_get_web_page($image_url);
            if ($image_data === FALSE || strpos($image_data, '<Message>Access Denied</Message>') !== FALSE || strpos($image_data, '<title>Just a moment...</title>') !== FALSE || strpos($image_data, '<html') !== FALSE) 
            {
                $error = 'Failed to download initial image URL: ' . $image_url;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        $image_data_temp = aiomatic_string_to_string_compress($image_data);
        if($image_data_temp !== false)
        {
            $image_data = $image_data_temp;
        }
        $filename = basename($image_url);
        $filename = explode("?", $filename);
        $filename = $filename[0];
        $filename = urlencode('res-' . $filename);
        $filename = str_replace('%', '-', $filename);
        $filename = str_replace('#', '-', $filename);
        $filename = str_replace('&', '-', $filename);
        $filename = str_replace('{', '-', $filename);
        $filename = str_replace('}', '-', $filename);
        $filename = str_replace('\\', '-', $filename);
        $filename = str_replace('<', '-', $filename);
        $filename = str_replace('>', '-', $filename);
        $filename = str_replace('*', '-', $filename);
        $filename = str_replace('/', '-', $filename);
        $filename = str_replace('$', '-', $filename);
        $filename = str_replace('\'', '-', $filename);
        $filename = str_replace('"', '-', $filename);
        $filename = str_replace(':', '-', $filename);
        $filename = str_replace('@', '-', $filename);
        $filename = str_replace('+', '-', $filename);
        $filename = str_replace('|', '-', $filename);
        $filename = str_replace('=', '-', $filename);
        $filename = str_replace('`', '-', $filename);
        $file_parts = pathinfo($filename);
        if(!isset($file_parts['extension']))
        {
            $file_parts['extension'] = '';
        }
        switch($file_parts['extension'])
        {
            case "":
            if(!aiomatic_endsWith($filename, '.jpg'))
                $filename .= '.jpg';
            break;
            case NULL:
            if(!aiomatic_endsWith($filename, '.jpg'))
                $filename .= '.jpg';
            break;
        }
        if (wp_mkdir_p($upload_dir['path']))
        {
            $file = $upload_dir['path'] . '/' . $filename;
            $ret_path = $upload_dir['url'] . '/' . $filename;
        }
        else
        {
            $file = $upload_dir['basedir'] . '/' . $filename;
            $ret_path = $upload_dir['baseurl'] . '/' . $filename;
        }
        if($wp_filesystem->exists($file))
        {
            unlink($file);
        }
        $ret = $wp_filesystem->put_contents($file, $image_data);
        if ($ret === FALSE) {
            $error = 'Failed to save initial image URL: ' . $image_url;
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        try
        {
            if(!class_exists('\Eventviva\ImageResize')){require_once (dirname(__FILE__) . "/res/ImageResize/ImageResize.php");}
            $imageRes = new ImageResize($file);
            if (isset($aiomatic_Main_Settings['ai_resize_quality']) && $aiomatic_Main_Settings['ai_resize_quality'] !== '')
            {
                $imageRes->quality_jpg = intval($aiomatic_Main_Settings['ai_resize_quality']);
            }
            else
            {
                $imageRes->quality_jpg = 100;
            }
            $imageRes->resize($width, $height, true);
            $imageRes->save($file);
            $imageData = $wp_filesystem->get_contents($file);
            if($imageData === false)
            {
                $error = 'Failed to read local file: ' . $file;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            unlink($file);
        }
        catch(Exception $e)
        {
            $error = 'Failed to resize AI generated image: ' . $image_url . ' to sizes ' . $width . ' - ' . $height . '. Exception thrown ' . esc_html($e->getMessage()) . '!';
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
    }
    else
    {
        $imageData = aiomatic_get_web_page($image_url);
        if ($imageData === false) 
        {
            $error = 'Failed to download image URL: ' . $image_url;
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $video_cfg_scale = '2.5';
    if (isset($aiomatic_Main_Settings['video_cfg_scale']) && trim($aiomatic_Main_Settings['video_cfg_scale']) != '') 
    {
        $video_cfg_scale = $aiomatic_Main_Settings['video_cfg_scale'];
    }
    $motion_bucket_id = '40';
    if (isset($aiomatic_Main_Settings['motion_bucket_id']) && trim($aiomatic_Main_Settings['motion_bucket_id']) != '') 
    {
        $motion_bucket_id = $aiomatic_Main_Settings['motion_bucket_id'];
    }
    if (!isset($aiomatic_Main_Settings['stability_app_id']) || trim($aiomatic_Main_Settings['stability_app_id']) == '') 
    {
        $error = 'You need to enter a Stability.AI API key in the plugin\'s "Settings" menu to use this feature!';
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(!in_array($image_size, $all_stable_video_sizes))
    {
        $image_size = '768x768';
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['stability_app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_stability_api_key', $token);
    $aiomatic_Limit_Settings = get_option('aiomatic_Limit_Settings', false);
    $stop = null;
    $session = aiomatic_get_session_id();
    $mode = 'video';
    $maxResults = 1;
    $available_tokens = 1000;
    $temperature = 1;
    $query = new Aiomatic_Query($image_url, $available_tokens, 'stable-diffusion-video', $temperature, $stop, $env, $mode, $token, $session, $maxResults, $image_size, '');
    $ok = apply_filters( 'aiomatic_ai_allowed', true, $aiomatic_Limit_Settings );
    if ( $ok !== true ) {
        $error = 'Video generator is rate limited: ' . $ok;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    if(isset($aiomatic_Main_Settings['enable_detailed_logging']) && $aiomatic_Main_Settings['enable_detailed_logging'] == 'on')
    {
        aiomatic_log_to_file('Generating Stability.AI Video using image URL: ' . $image_url . ' size: ' . $image_size);
    }
    $tmpFilePath = tempnam(sys_get_temp_dir(), 'upload_');
    if ( $tmpFilePath === false ) {
        $error = 'Failed to generate a temporary file name';
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $tmpFilePath .= '.jpg';
    $ret = $wp_filesystem->put_contents($tmpFilePath, $imageData);
    if ($ret === FALSE) {
        $error = 'Failed to save temp image URL: ' . $tmpFilePath;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $mime = aiomatic_get_mime($tmpFilePath);
    if($mime !== 'image/png' && $mime !== 'image/jpeg')
    {
        $error = 'Invalid mime type for image (jpeg and png supported only): ' . $image_url;
        $error = apply_filters('aiomatic_modify_ai_error', $error);
        return false;
    }
    $cfile = new CURLFile($tmpFilePath, $mime);
    $api_url = 'https://api.stability.ai/v2alpha/generation/image-to-video';
    $ch = curl_init();
    if($ch === false)
    {
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability video API call after initial failure: ' . print_r($api_url, true));
            sleep(pow(2, $retry_count));
            return aiomatic_generate_stability_video($image_url, $image_size, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal);
        }
        else
        {
            $error = 'Failed to create Stability curl request.';
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
    }
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: multipart/form-data', 'authorization: ' . $token));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $post_fields = array(
        'image' => $cfile,
        'cfg_scale' => floatval($video_cfg_scale), 
        'motion_bucket_id' => intval($motion_bucket_id)
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $ai_response = curl_exec($ch);
    $info = curl_getinfo($ch);
    unlink($tmpFilePath);
    curl_close($ch);
    if($info['http_code'] != 200)
    {
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability video API call after http_code failure: ' . print_r($api_url, true));
            sleep(pow(2, $retry_count));
            return aiomatic_generate_stability_video($image_url, $image_size, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal);
        }
        else
        {
            $er = ' ';
            $json_resp = json_decode($ai_response, true);
            if($json_resp !== null)
            {
                $er .= 'Error: ' . print_r($json_resp, true);
            }
            aiomatic_log_to_file('Invalid return code from API: ' . $info['http_code'] . $er);
            aiomatic_log_to_file('PostFields: ' . print_r($post_fields, true));
            $error = 'Failed to generate the video, please try again later!';
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
    }
    if($ai_response === false)
    {
        if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
        {
            aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability video API call after response failure: ' . print_r($api_url, true));
            sleep(pow(2, $retry_count));
            return aiomatic_generate_stability_video($image_url, $image_size, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal);
        }
        else
        {
            $error = 'Failed to get AI response: ' . $api_url;
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
    }
    else
    {
        $json_resp = json_decode($ai_response, true);
        if($json_resp === null)
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability video API call after decode failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_video($image_url, $image_size, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal);
            }
            else
            {
                $error = 'Failed to decode AI response: ' . $ai_response;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        if(!isset($json_resp['id']))
        {
            if (isset($aiomatic_Main_Settings['max_retry']) && $aiomatic_Main_Settings['max_retry'] != '' && is_numeric($aiomatic_Main_Settings['max_retry']) && intval($aiomatic_Main_Settings['max_retry']) > $retry_count)
            {
                aiomatic_log_to_file('Retrying (' . (intval($retry_count) + 1) . ') Stability video API call after response failure: ' . print_r($api_url, true));
                sleep(pow(2, $retry_count));
                return aiomatic_generate_stability_video($image_url, $image_size, $env, intval($retry_count) + 1, $returnbase64, $error, $nolocal);
            }
            else
            {
                $error = 'Invalid AI response: ' . $ai_response;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
        }
        $filename = 'video' . uniqid() . '.mp4';
        $upload_dir = wp_upload_dir();
        if (wp_mkdir_p($upload_dir['path'] . '/videos'))
        {
            $file = $upload_dir['path'] . '/videos/' . $filename;
            $ret_path = $upload_dir['url'] . '/videos/' . $filename;
        }
        else
        {
            $file = $upload_dir['basedir'] . '/videos/' . $filename;
            $ret_path = $upload_dir['baseurl'] . '/videos/' . $filename;
        }
        $my_id = $json_resp['id'];
        $ch = curl_init();
        if ( $ch === false ) {
            $error = 'Failed to init CURL!';
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authorization: ' . $token,
            'accept: application/json'
        ]);
        $apiUrl = 'https://api.stability.ai/v2alpha/generation/image-to-video/result/' . $my_id;
        $max_timeout = 600;
        $wait_time = 0;
        $videoData = false;
        do 
        {
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 200) 
            {
                $response = apply_filters( 'aiomatic_stability_video_reply_raw', $response, $image_url );
                $videojson = json_decode($response, true);
                if ( $videojson === null ) {
                    $error = 'Failed to decode video API response! ' . $response;
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
                if(!isset($videojson['video']))
                {
                    $error = 'Cannot decode video response ' . print_r($videojson, true);
                    $error = apply_filters('aiomatic_modify_ai_error', $error);
                    return false;
                }
                $videoData = $videojson['video'];
                break;
            } 
            elseif ($httpCode != 202) 
            {
                $error = 'Error returned from video API call ' . $httpCode . ' - ID: ' . $my_id . ' - data: ' . $response;
                $error = apply_filters('aiomatic_modify_ai_error', $error);
                return false;
            }
            sleep(2);
            $wait_time += 2;
        } while ($wait_time < $max_timeout);
        curl_close($ch);
        if($videoData === false)
        {
            $error = 'Failed to get video data for ' . $image_url;
            $error = apply_filters('aiomatic_modify_ai_error', $error);
            return false;
        }
        apply_filters( 'aiomatic_ai_reply', $videoData, $query );
        if($returnbase64 == true)
        {
            return $videoData;
        }
        $rezi = aiomatic_base64_to_file($videoData, $file, $ret_path);
        if($rezi !== false && $rezi !== '')
        {
            if($nolocal === false && isset($aiomatic_Main_Settings['copy_locally']) && $aiomatic_Main_Settings['copy_locally'] != 'disabled' && $aiomatic_Main_Settings['copy_locally'] != 'on')
            {
                $copy_it = $aiomatic_Main_Settings['copy_locally'];
                $localpath = aiomatic_copy_video_locally($rezi[1], 'video_' . time(), $copy_it);
                if($localpath !== false)
                {
                    unlink($rezi[0]);
                    $localrez = array();
                    $localrez[0] = $localpath[1];
                    $localrez[1] = $localpath[0];
                    $rezi = $localrez;
                }
            }
        }
        return $rezi;
    }
}
function aiomatic_generate_audio_convertion($prompt, $url = '', $purpose = 'transcriptions', $model = 'whisper-1', $temperature = 0, $language = 'en')
{
    $aiomatic_result = array('status' => 'error', 'msg' => 'Something went wrong with audio converter');
    $mime_types = ['mp3' => 'audio/mpeg','mp4' => 'video/mp4','mpeg' => 'video/mpeg','m4a' => 'audio/m4a','wav' => 'audio/wav','webm' => 'video/webm'];
    if($purpose != 'transcriptions' && $purpose != 'translations')
    {
        $aiomatic_result['msg'] = 'Unknown purpose submitted.';
        return $aiomatic_result;
    }
    if(empty($url)){
        $aiomatic_result['msg'] = 'The audio URL is required';
        return $aiomatic_result;
    }
    $remoteFile = get_headers($url, 1);
    $file_name = basename($url);
    $is_in_mime_types = false;
    $file_ext = '';
    foreach($mime_types as $key=>$mime_type){
        if((is_array($remoteFile['Content-Type']) && in_array($mime_type,$remoteFile['Content-Type'])) || strpos($remoteFile['Content-Type'], $mime_type) !== false){
            $is_in_mime_types = true;
            $file_ext = '.'.$key;
            break;
        }
    }
    if(!$is_in_mime_types){
        $aiomatic_result['msg'] = 'We only accept mp3, mp4, mpeg, mpga, m4a, wav, or webm.';
        return $aiomatic_result;
    }

    if(strpos($file_name, $file_ext) === false){
        $file_name = md5(uniqid() . time()) . $file_ext;
    }
    if($remoteFile['Content-Length'] > 26214400){
        $aiomatic_result['msg'] = 'Audio file maximum 25MB';
        return $aiomatic_result;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') {
        $aiomatic_result['msg'] = 'You need to add an API key in plugin settings for this shortcode to work.';
        return $aiomatic_result;
    }
    else
    {
        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
        $appids = array_filter($appids);
        $token = $appids[array_rand($appids)];
        $token = apply_filters('aiomatic_openai_api_key', $token);
        if(empty($token))
        {
            $aiomatic_result['msg'] = 'Invalid API key submitted';
        return $aiomatic_result;
        }
        if(aiomatic_is_aiomaticapi_key($token))
        {
            $aiomatic_result['msg'] = 'Currently only OpenAI API is supported for audio processing.';
        return $aiomatic_result;
        }
    }
    if (aiomatic_check_if_azure_or_others($aiomatic_Main_Settings)) 
    {
        $aiomatic_result['msg'] = 'Azure/Claude API is not currently supported for audio conversion.';
        return $aiomatic_result;
    }
    require_once (dirname(__FILE__) . "/res/openai/Url.php"); 
    require_once (dirname(__FILE__) . "/res/openai/OpenAi.php"); 
    $open_ai = new OpenAi($token);
    if(!$open_ai){
        $aiomatic_result['msg'] = 'Missing API Setting';
        return $aiomatic_result;
    }
    if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
    {
        $open_ai->setCustomURL(trim($aiomatic_Main_Settings['custom_endpoint'], ' /'));
    }
    if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
    {
        $open_ai->setORG($aiomatic_Main_Settings['openai_organization']);
    }
    if(!function_exists('download_url')){
        include_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    $tmp_file = download_url($url);
    if ( is_wp_error( $tmp_file ) ){
        $aiomatic_result['msg'] = $tmp_file->get_error_message();
        return $aiomatic_result;
    }
    $response_format = 'text';
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
        wp_filesystem($creds);
    }
    $data_request = array(
        'audio' => array(
            'filename' => $file_name,
            'data' => $wp_filesystem->get_contents($tmp_file)
        ),
        'model' => $model,
        'temperature' => $temperature,
        'response_format' => $response_format,
        'prompt' => $prompt
    );
    if($purpose == 'transcriptions' && !empty($language)){
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
    if($purpose == 'transcriptions')
    {
        $completion = $open_ai->transcribe($data_request);
    }
    elseif($purpose == 'translations')
    {
        $completion = $open_ai->translate($data_request);
    }
    $result = json_decode($completion);
    if($result && isset($result->error)){
        $aiomatic_result['msg'] = $result->error->message;
        return $aiomatic_result;
    }
    $aiomatic_result['status'] = 'success';
    $text_generated = $completion;
    $aiomatic_result['data'] = $text_generated;
    if(empty($text_generated)){
        $aiomatic_result['msg'] = 'OpenAI returned empty content';
        return $aiomatic_result;
    }
    do_action('aiomatic_audio_converter_reply', $aiomatic_result);
    return $aiomatic_result;
}
?>