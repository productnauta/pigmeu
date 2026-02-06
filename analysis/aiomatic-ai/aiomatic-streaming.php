<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('Aiomatic_Streaming')) 
{
    class Aiomatic_Streaming
    {
        private static  $instance = null ;
        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public function __construct()
        {
            add_action('init', [$this, 'aiomatic_stream'], 1);
        }
        public function aiomatic_stream()
        {
            if(isset($_GET['aiomatic_stream']) && sanitize_text_field($_GET['aiomatic_stream']) == 'yes')
            {
                header('Content-type: text/event-stream');
                header('Cache-Control: no-cache');
                if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'aiomatic-streaming-nonce')) 
                {
                    $message = esc_html__('You are not allowed to do this action!', 'aiomatic-automatic-ai-content-writer');
                    $this->aiomatic_event_exit($message);
                }
                else 
                {
                    if (isset($_REQUEST['input_text']) && $_REQUEST['input_text'] != '') 
                    {
                        $thread_id = '';
                        if(isset($_REQUEST['thread_id']) && trim($_REQUEST['thread_id']) !== '')
                        {
                            $thread_id = stripslashes($_REQUEST['thread_id']);
                        }
                        $assistant_id = '';
                        if(isset($_REQUEST['assistant_id']) && trim($_REQUEST['assistant_id']) !== '')
                        {
                            $assistant_id = stripslashes($_REQUEST['assistant_id']);
                        }
                        $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                        $no_internet = false;
                        if(isset($_REQUEST['internet_access']) && ($_REQUEST['internet_access'] === 'no' || $_REQUEST['internet_access'] === '0' || $_REQUEST['internet_access'] == 'off' || $_REQUEST['internet_access'] == 'disabled' || $_REQUEST['internet_access'] == 'Disabled' || $_REQUEST['internet_access'] == 'disable' || $_REQUEST['internet_access'] == "false"))
                        {
                            $no_internet = true;
                        }
                        $no_embeddings = false;
                        if(isset($_REQUEST['embeddings']) && ($_REQUEST['embeddings'] === 'no' || $_REQUEST['embeddings'] === '0' || $_REQUEST['embeddings'] == 'off' || $_REQUEST['embeddings'] == 'disabled' || $_REQUEST['embeddings'] == 'disable' || $_REQUEST['embeddings'] == 'Disabled' || $_REQUEST['embeddings'] == "false"))
                        {
                            $no_embeddings = true;
                        }
                        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
                        if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
                        {
                            $aiomatic_result = esc_html__('You need to insert a valid OpenAI/AiomaticAPI API Key for this to work!', 'aiomatic-automatic-ai-content-writer');
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if(isset($_REQUEST['user_token_cap_per_day']) && trim($_REQUEST['user_token_cap_per_day']) !== '')
                        {
                            $user_token_cap_per_day = sanitize_text_field($_REQUEST['user_token_cap_per_day']);
                        }
                        else
                        {
                            $user_token_cap_per_day = '';
                        }
                        if(!empty($user_token_cap_per_day))
                        {
                            $user_token_cap_per_day = intval($user_token_cap_per_day);
                        }
                        if(isset($_REQUEST['file_data']))
                        {
                            $file_data = stripslashes($_REQUEST['file_data']);
                        }
                        else
                        {
                            $file_data = '';
                        }
                        if(isset($_REQUEST['user_id']))
                        {
                            $user_id = stripslashes($_REQUEST['user_id']);
                        }
                        else
                        {
                            $user_id = '';
                        }
                        $forced_functions = array();
                        if(isset($_REQUEST['enable_god_mode']))
                        {
                            $enable_god_mode = stripslashes($_REQUEST['enable_god_mode']);
                        }
                        else
                        {
                            $enable_god_mode = '';
                        }
                        if($enable_god_mode === 'on' || $enable_god_mode === 'yes' || $enable_god_mode === 'true' || $enable_god_mode === '1' || $enable_god_mode === 'enable' || $enable_god_mode === 'enabled')
                        {
                            $function_result = '';
                        }
                        elseif(is_string($enable_god_mode) && !empty($enable_god_mode))
                        {
                            $forced_functions = explode(',', $enable_god_mode);
                            $function_result = '';
                        }
                        else
                        {
                            $function_result = 'disabled';
                        }
                        if(isset($_REQUEST['functions_result']) && trim($_REQUEST['functions_result']) != '' && (!isset($_REQUEST['bufferid']) || $_REQUEST['bufferid'] == ''))
                        {
                            $fr = $_REQUEST['functions_result'];
                            $fr = json_decode($fr, true);
                            if($fr !== null)
                            {
                                $function_result = $fr;
                            }
                            else
                            {
                                $fr = $_REQUEST['functions_result'];
                                $fr = json_decode(stripslashes($fr), true);
                                if($fr !== null)
                                {
                                    $function_result = $fr;
                                }
                            }
                        }
                        $remember_data = false;
                        if(isset($_REQUEST['functions_result']) && trim($_REQUEST['functions_result']) == '0' && (isset($_REQUEST['bufferid']) && $_REQUEST['bufferid'] != ''))
                        {
                            $remember_data = get_transient('aiomatic_ai_data_' . $_REQUEST['bufferid']);
                            if(is_array($remember_data))
                            {
                                if(isset($remember_data[3]))
                                {
                                    $fr = stripslashes($remember_data[3]);
                                    $fr = json_decode($fr, true);
                                    if($fr !== null)
                                    {
                                        $function_result = $fr;
                                    }
                                }
                            }
                        }
                        if(is_array($function_result) && is_array($function_result[0]))
                        {
                            if(isset($_REQUEST['run_id']) && trim($_REQUEST['run_id']) != '')
                            {
                                for($k = 0; $k < count($function_result); $k++)
                                {
                                    $function_result[$k]['run_id'] = stripslashes($_REQUEST['run_id']);
                                    $function_result[$k]['thread_id'] = $thread_id;
                                }
                            }
                        }
                        elseif(is_string($function_result) && !empty($function_result))
                        {
                            $function_result = array('function_result' => $function_result);
                        }
                        if(isset($_REQUEST['pdf_data']))
                        {
                            $embedding_namespace = stripslashes($_REQUEST['pdf_data']);
                        }
                        else
                        {
                            $embedding_namespace = '';
                        }
                        if(empty($embedding_namespace))
                        {
                            if (isset($aiomatic_Chatbot_Settings['persistent']) && $aiomatic_Chatbot_Settings['persistent'] == 'vector')
                            {
                                $embedding_namespace = 'persistentchat_' . $user_id . '_' . $thread_id;
                            }
                            else
                            {
                                if(isset($_REQUEST['embeddings_namespace']) && !empty($_REQUEST['embeddings_namespace']))
                                {
                                    $embedding_namespace = $_REQUEST['embeddings_namespace'];
                                }
                            }
                        }
                        if(isset($_REQUEST['bufferid']) && $_REQUEST['bufferid'] != '')
                        {
                            if($remember_data === false)
                            {
                                $remember_data = get_transient('aiomatic_ai_data_' . $_REQUEST['bufferid']);
                            }
                            delete_transient('aiomatic_ai_data_' . $_REQUEST['bufferid']);
                            if(is_array($remember_data))
                            {
                                if(isset($remember_data[2]))
                                {
                                    $input_text = stripslashes($remember_data[0]);
                                    $remember_string = stripslashes($remember_data[1]);
                                    $user_question = stripslashes($remember_data[2]);
                                }
                                else
                                {
                                    $message = esc_html__('Invalid bufferid sent!', 'aiomatic-automatic-ai-content-writer');
                                    $this->aiomatic_event_exit($message);
                                }
                            }
                            else
                            {
                                $input_text = '';
                                $remember_string = '';
                                $user_question = '';
                            }
                        }
                        else
                        {
                            $input_text = stripslashes($_REQUEST['input_text']);
                            if(isset($_REQUEST['user_question']))
                            {
                                $user_question = stripslashes($_REQUEST['user_question']);
                            }
                            else
                            {
                                $user_question = '';
                            }
                            if(isset($_REQUEST['remember_string']))
                            {
                                $remember_string = stripslashes($_REQUEST['remember_string']);
                            }
                            else
                            {
                                $remember_string = '';
                            }
                        }
                        if(isset($_REQUEST['forms_replace']) && trim($_REQUEST['forms_replace']) === '1')
                        {
                            if (isset($aiomatic_Main_Settings['form_prepend']) && trim($aiomatic_Main_Settings['form_prepend']) != '') 
                            {
                                $input_text = trim($aiomatic_Main_Settings['form_prepend']) . ' ' . $input_text;
                            }
                            if (isset($aiomatic_Main_Settings['form_append']) && trim($aiomatic_Main_Settings['form_append']) != '') 
                            {
                                $input_text = $input_text . ' ' . trim($aiomatic_Main_Settings['form_append']);
                            }
                            $post_id = get_the_ID();
                            $input_text = aiomatic_replaceEmbeddingsAIPostShortcodes($input_text, $post_id);
                            $current_user = wp_get_current_user();
                            if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
                            {
                                $input_text = str_replace('%%user_name%%', '', $input_text);
                                $input_text = str_replace('%%user_email%%', '' , $input_text);
                                $input_text = str_replace('%%user_display_name%%', '', $input_text);
                                $input_text = str_replace('%%user_role%%', '', $input_text);
                                $input_text = str_replace('%%user_id%%', '' , $input_text);
                                $input_text = str_replace('%%user_firstname%%', '' , $input_text);
                                $input_text = str_replace('%%user_lastname%%', '' , $input_text);
                                $input_text = str_replace('%%user_description%%', '' , $input_text);
                                $input_text = str_replace('%%user_url%%', '' , $input_text);
                            }
                            else
                            {
                                $input_text = str_replace('%%user_name%%', $current_user->user_login, $input_text);
                                $input_text = str_replace('%%user_email%%', $current_user->user_email , $input_text);
                                $input_text = str_replace('%%user_display_name%%', $current_user->display_name, $input_text);
                                $input_text = str_replace('%%user_role%%', implode(',', $current_user->roles), $input_text);
                                $input_text = str_replace('%%user_id%%', $current_user->ID , $input_text);
                                $input_text = str_replace('%%user_firstname%%', $current_user->user_firstname , $input_text);
                                $input_text = str_replace('%%user_lastname%%', $current_user->user_lastname , $input_text);
                                $user_desc = get_the_author_meta( 'description', $current_user->ID );
                                $input_text = str_replace('%%user_description%%', $user_desc , $input_text);
                                $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                                $input_text = str_replace('%%user_url%%', $user_url , $input_text);
                            }
                        }
                        if (isset($aiomatic_Chatbot_Settings['max_input_length']) && $aiomatic_Chatbot_Settings['max_input_length'] != '' && is_numeric($aiomatic_Chatbot_Settings['max_input_length'])) 
                        {
                            if(strlen($input_text) > intval($aiomatic_Chatbot_Settings['max_input_length']))
                            {
                                $input_text = substr($input_text, 0, intval($aiomatic_Chatbot_Settings['max_input_length']));
                            }
                        }
                        $is_modern_gpt = '0';
                        if(isset($_REQUEST['is_modern_gpt']))
                        {
                            $is_modern_gpt = stripslashes($_REQUEST['is_modern_gpt']);
                        }
                        if($is_modern_gpt == '1')
                        {
                            if(!empty($remember_string))
                            {
                                $remember_string = json_decode($remember_string, true);
                                if($remember_string === null)
                                {
                                    $aiomatic_result = esc_html__('Failed to decode conversation data!', 'aiomatic-automatic-ai-content-writer');
                                    $this->aiomatic_event_exit($aiomatic_result);
                                }
                                if(!is_array($remember_string))
                                {
                                    $remember_string = [];
                                }
                            }
                            else
                            {
                                $remember_string = [];
                            }
                            if (isset($aiomatic_Chatbot_Settings['chatbot_prepend']) && trim($aiomatic_Chatbot_Settings['chatbot_prepend']) != '') 
                            {
                                array_unshift($remember_string, array ('role' => 'user', 'content' => trim($aiomatic_Chatbot_Settings['chatbot_prepend'])));
                            }
                            if (isset($aiomatic_Chatbot_Settings['chatbot_append']) && trim($aiomatic_Chatbot_Settings['chatbot_append']) != '') 
                            {
                                $remember_string[] = array ('role' => 'user', 'content' => trim($aiomatic_Chatbot_Settings['chatbot_append']));
                            }
                            $remember_string[] = array ('role' => 'user', 'content' => $input_text);
                            $input_text = $remember_string;
                        }
                        else
                        {
                            if (isset($aiomatic_Chatbot_Settings['chatbot_prepend']) && trim($aiomatic_Chatbot_Settings['chatbot_prepend']) != '') 
                            {
                                $remember_string = trim($aiomatic_Chatbot_Settings['chatbot_prepend']) . ' ' . $remember_string;
                            }
                            if (isset($aiomatic_Chatbot_Settings['chatbot_append']) && trim($aiomatic_Chatbot_Settings['chatbot_append']) != '') 
                            {
                                $remember_string = $remember_string . ' ' . trim($aiomatic_Chatbot_Settings['chatbot_append']);
                            }
                            if(!empty(trim($remember_string)))
                            {
                                $input_text = trim($remember_string) . PHP_EOL . $input_text;
                            }
                        }
                        if(isset($_REQUEST['model']))
                        {
                            $model = sanitize_text_field(stripslashes($_REQUEST['model']));
                        }
                        else
                        {
                            $model = 'default';
                        }
                        if($model == 'default')
                        {
                            $model = AIOMATIC_DEFAULT_MODEL;
                        }
                        if(isset($_REQUEST['temp']))
                        {
                            $temperature = sanitize_text_field(stripslashes($_REQUEST['temp']));
                        }
                        else
                        {
                            $temperature = '1';
                        }
                        if(isset($_REQUEST['top_p']))
                        {
                            $top_p = sanitize_text_field(stripslashes($_REQUEST['top_p']));
                        }
                        else
                        {
                            $top_p = '1';
                        }
                        if(isset($_REQUEST['presence']))
                        {
                            $presence_penalty = sanitize_text_field(stripslashes($_REQUEST['presence']));
                        }
                        else
                        {
                            $presence_penalty = '0';
                        }
                        if(isset($_REQUEST['frequency']))
                        {
                            $frequency_penalty = sanitize_text_field(stripslashes($_REQUEST['frequency']));
                        }
                        else
                        {
                            $frequency_penalty = '0';
                        }
                        if(isset($_REQUEST['store_data']))
                        {
                            $store_data = sanitize_text_field(stripslashes($_REQUEST['store_data']));
                        }
                        else
                        {
                            $store_data = 'off';
                        }
                        $models = aiomatic_get_all_models();
                        if(!in_array($model, $models))
                        {
                            $aiomatic_result = esc_html__('Invalid model provided: ', 'aiomatic-automatic-ai-content-writer') . $model;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        $vision_file = '';
                        if(isset($_REQUEST['vision_file']))
                        {
                            if(aiomatic_is_vision_model($model, $assistant_id))
                            {
                                $vision_file = stripslashes($_REQUEST['vision_file']);
                            }
                        }
                        $temperature = floatval($temperature);
                        $top_p = floatval($top_p);
                        $presence_penalty = floatval($presence_penalty);
                        $frequency_penalty = floatval($frequency_penalty);
                        if($temperature < 0 || $temperature > 2)
                        {
                            $aiomatic_result = esc_html__('Invalid temperature provided: ', 'aiomatic-automatic-ai-content-writer') . $temperature;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if($top_p < 0 || $top_p > 1)
                        {
                            $aiomatic_result = esc_html__('Invalid top_p provided: ', 'aiomatic-automatic-ai-content-writer') . $top_p;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if($presence_penalty < -2 || $presence_penalty > 2)
                        {
                            $aiomatic_result = esc_html__('Invalid presence_penalty provided: ', 'aiomatic-automatic-ai-content-writer') . $presence_penalty;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if($frequency_penalty < -2 || $frequency_penalty > 2)
                        {
                            $aiomatic_result = esc_html__('Invalid frequency_penalty provided: ', 'aiomatic-automatic-ai-content-writer') . $frequency_penalty;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        $used_token_count = 0;
                        if(is_numeric($user_token_cap_per_day))
                        {
                            if(empty($user_id) || $user_id == 0 || !is_numeric($user_id))
                            {
                                $aiomatic_result = sprintf( wp_kses( __( 'You are not allowed to access this form if you are not logged in. Please <a href="%s" target="_blank">log in</a> to continue.', 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), wp_login_url(get_permalink()) );
                                $this->aiomatic_event_exit($aiomatic_result);
                            }
                            $used_token_count = get_user_meta($user_id, 'aiomatic_used_chat_tokens', true);
                            if($used_token_count !== '' && $used_token_count !== false && is_numeric($used_token_count))
                            {
                                $used_token_count = intval($used_token_count);
                                if($used_token_count > $user_token_cap_per_day)
                                {
                                    $aiomatic_result = esc_html__('Daily token count for your user account is exceeded! Please try again tomorrow.', 'aiomatic-automatic-ai-content-writer');
                                    $this->aiomatic_event_exit($aiomatic_result);
                                }
                            }
                            else
                            {
                                $used_token_count = 0;
                            }
                        }
                        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                        $appids = array_filter($appids);
                        $token = $appids[array_rand($appids)];
                        $max_tokens = aiomatic_get_max_tokens($model);
                        if (isset($aiomatic_Chatbot_Settings['max_tokens']) && $aiomatic_Chatbot_Settings['max_tokens'] !== '' && is_numeric($aiomatic_Chatbot_Settings['max_tokens']))
                        {
                            $max_tokens_chatbot = intval($aiomatic_Chatbot_Settings['max_tokens']);
                            if(intval($max_tokens_chatbot) < $max_tokens)
                            {
                                $max_tokens = intval($max_tokens_chatbot);
                                if($max_tokens <= 0)
                                {
                                    $max_tokens = 1000;
                                }
                            }
                        }
                        if($is_modern_gpt == '1')
                        {
                            $aitext = '';
                            foreach($input_text as $aimess)
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
                            $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $aitext, $query_token_count);
                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                            {
                                $startIndex = intdiv(count($input_text), 2);
                                $input_text = array_slice($input_text, $startIndex);
                                $arrx = array_keys($input_text);
                                $lastindex = end($arrx);
                                $string_len = strlen($input_text[$lastindex]['content']);
                                $string_len = $string_len / 2;
                                $string_len = intval(0 - $string_len);
                                $input_text[$lastindex]['content'] = aiomatic_substr($input_text[$lastindex]['content'], 0, $string_len);
                                $input_text[$lastindex]['content'] = trim($input_text[$lastindex]['content']);
                                $aitext = '';
                                foreach($input_text as $aimess)
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
                        else
                        {
                            $query_token_count = count(aiomatic_encode($input_text));
                            $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $input_text, $query_token_count);
                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                            {
                                $string_len = strlen($input_text);
                                $string_len = $string_len / 2;
                                $string_len = intval(0 - $string_len);
                                $input_text = aiomatic_substr($input_text, 0, $string_len);
                                $input_text = trim($input_text);
                                if(empty($input_text))
                                {
                                    $aiomatic_result = esc_html__('Empty API seed expression provided (after processing)', 'aiomatic-automatic-ai-content-writer');
                                    $this->aiomatic_event_exit($aiomatic_result);
                                }
                                $query_token_count = count(aiomatic_encode($input_text));
                                $available_tokens = $max_tokens - $query_token_count;
                            }
                        }
                        $error = '';
                        $finish_reason = '';
                        do_action('aiomatic_calling_stream', $input_text, $model);
                        $zerret = aiomatic_generate_text($token, $model, $input_text, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, true, 'shortcodeChat',  0, $finish_reason, $error, $no_internet, $no_embeddings, true, $vision_file, $user_question, 'user', $assistant_id, $thread_id, $embedding_namespace, $function_result, $file_data, false, $store_data, $forced_functions);
                        if($zerret === false && !empty($error))
                        {
                            $this->aiomatic_event_exit($error);
                        }
                    }
                    else
                    {
                        $message = esc_html__('Empty input text provided!', 'aiomatic-automatic-ai-content-writer');
                        $this->aiomatic_event_exit($message);
                    }
                }
                exit;
            }
        }

        private function aiomatic_event_exit($message)
        {
            echo "event: message\n";
            echo 'data: {"error":[{"message":"' . $message . '"}]}';
            echo "\n\n";
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            echo 'data: {"choices":[{"finish_reason":"stop"}]}';
            echo "\n\n";
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            exit;
        }
    }
    Aiomatic_Streaming::get_instance();
}
if(!class_exists('Aiomatic_Claude_Streaming')) 
{
    class Aiomatic_Claude_Streaming
    {
        private static  $instance = null ;
        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public function __construct()
        {
            add_action('init', [$this, 'aiomatic_claude_stream'], 1);
        }
        public function aiomatic_claude_stream()
        {
            if(isset($_GET['aiomatic_claude_stream']) && sanitize_text_field($_GET['aiomatic_claude_stream']) == 'yes')
            {
                header('Content-type: text/event-stream');
                header('Cache-Control: no-cache');
                if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'aiomatic-streaming-nonce')) 
                {
                    $message = esc_html__('You are not allowed to do this action!', 'aiomatic-automatic-ai-content-writer');
                    $this->aiomatic_event_exit($message);
                }
                else 
                {
                    if (isset($_REQUEST['input_text']) && !empty($_REQUEST['input_text'])) 
                    {
                        if(!isset($_REQUEST['model']) || !isset($_REQUEST['temp']) || !isset($_REQUEST['top_p']) || !isset($_REQUEST['presence']) || !isset($_REQUEST['frequency']))
                        {
                            $message = esc_html__('Incomplete POST request for chat!', 'aiomatic-automatic-ai-content-writer');
                            $this->aiomatic_event_exit($message);
                        }
                        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
                        if (!isset($aiomatic_Main_Settings['app_id_claude']) || trim($aiomatic_Main_Settings['app_id_claude']) == '') 
                        {
                            $aiomatic_result = esc_html__('You need to insert a valid Anthropic Claude API Key for this to work!', 'aiomatic-automatic-ai-content-writer');
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if(isset($_REQUEST['user_token_cap_per_day']))
                        {
                            $user_token_cap_per_day = sanitize_text_field($_REQUEST['user_token_cap_per_day']);
                            if(!empty($user_token_cap_per_day))
                            {
                                $user_token_cap_per_day = intval($user_token_cap_per_day);
                            }
                        }
                        else
                        {
                            $user_token_cap_per_day = '';
                        }
                        if(isset($_REQUEST['user_id']))
                        {
                            $user_id = sanitize_text_field($_REQUEST['user_id']);
                        }
                        else
                        {
                            $user_id = '';
                        }
                        if(isset($_REQUEST['file_data']))
                        {
                            $file_data = stripslashes($_REQUEST['file_data']);
                        }
                        else
                        {
                            $file_data = '';
                        }
                        $forced_functions = array();
                        if(isset($_REQUEST['enable_god_mode']))
                        {
                            $enable_god_mode = stripslashes($_REQUEST['enable_god_mode']);
                        }
                        else
                        {
                            $enable_god_mode = '';
                        }
                        if($enable_god_mode === 'on' || $enable_god_mode === 'yes' || $enable_god_mode === 'true' || $enable_god_mode === '1' || $enable_god_mode === 'enable' || $enable_god_mode === 'enabled')
                        {
                            $function_result = '';
                        }
                        elseif(is_string($enable_god_mode) && !empty($enable_god_mode))
                        {
                            $forced_functions = explode(',', $enable_god_mode);
                            $function_result = '';
                        }
                        else
                        {
                            $function_result = 'disabled';
                        }
                        if(isset($_REQUEST['functions_result']) && trim($_REQUEST['functions_result']) != '' && (!isset($_REQUEST['bufferid']) || $_REQUEST['bufferid'] == ''))
                        {
                            $fr = $_REQUEST['functions_result'];
                            $fr = json_decode($fr, true);
                            if($fr !== null)
                            {
                                $function_result = $fr;
                            }
                            else
                            {
                                $fr = $_REQUEST['functions_result'];
                                $fr = json_decode(stripslashes($fr), true);
                                if($fr !== null)
                                {
                                    $function_result = $fr;
                                }
                            }
                        }
                        $remember_data = false;
                        if(isset($_REQUEST['functions_result']) && trim($_REQUEST['functions_result']) == '0' && (isset($_REQUEST['bufferid']) && $_REQUEST['bufferid'] != ''))
                        {
                            $remember_data = get_transient('aiomatic_ai_data_' . $_REQUEST['bufferid']);
                            if(is_array($remember_data))
                            {
                                if(isset($remember_data[3]))
                                {
                                    $fr = stripslashes($remember_data[3]);
                                    $fr = json_decode($fr, true);
                                    if($fr !== null)
                                    {
                                        $function_result = $fr;
                                    }
                                }
                            }
                        }
                        if(is_array($function_result) && is_array($function_result[0]))
                        {
                            if(isset($_REQUEST['run_id']) && trim($_REQUEST['run_id']) != '')
                            {
                                for($k = 0; $k < count($function_result); $k++)
                                {
                                    $function_result[$k]['run_id'] = stripslashes($_REQUEST['run_id']);
                                }
                            }
                        }
                        elseif(is_string($function_result) && !empty($function_result))
                        {
                            $function_result = array('function_result' => $function_result);
                        }
                        if(isset($_REQUEST['store_data']))
                        {
                            $store_data = sanitize_text_field(stripslashes($_REQUEST['store_data']));
                        }
                        else
                        {
                            $store_data = 'off';
                        }
                        $input_text = stripslashes($_REQUEST['input_text']);
                        if(isset($_REQUEST['vision_file']))
                        {
                            $vision_file = stripslashes($_REQUEST['vision_file']);
                        }
                        else
                        {
                            $vision_file = '';
                        }
                        $no_internet = false;
                        if(isset($_REQUEST['internet_access']) && ($_REQUEST['internet_access'] === 'no' || $_REQUEST['internet_access'] === '0' || $_REQUEST['internet_access'] == 'off' || $_REQUEST['internet_access'] == 'disabled' || $_REQUEST['internet_access'] == 'Disabled' || $_REQUEST['internet_access'] == 'disable' || $_REQUEST['internet_access'] == "false"))
                        {
                            $no_internet = true;
                        }
                        $no_embeddings = false;
                        if(isset($_REQUEST['embeddings']) && ($_REQUEST['embeddings'] === 'no' || $_REQUEST['embeddings'] === '0' || $_REQUEST['embeddings'] == 'off' || $_REQUEST['embeddings'] == 'disabled' || $_REQUEST['embeddings'] == 'disable' || $_REQUEST['embeddings'] == 'Disabled' || $_REQUEST['embeddings'] == "false"))
                        {
                            $no_embeddings = true;
                        }
                        $user_question = '';
                        if(isset($_REQUEST['user_question']))
                        {
                            $user_question = stripslashes($_REQUEST['user_question']);
                        }
                        $aiomatic_Chatbot_Settings = get_option('aiomatic_Chatbot_Settings', false);
                        if(isset($_REQUEST['forms_replace']) && trim($_REQUEST['forms_replace']) === '1')
                        {
                            if (isset($aiomatic_Main_Settings['form_prepend']) && trim($aiomatic_Main_Settings['form_prepend']) != '') 
                            {
                                $input_text = trim($aiomatic_Main_Settings['form_prepend']) . ' ' . $input_text;
                            }
                            if (isset($aiomatic_Main_Settings['form_append']) && trim($aiomatic_Main_Settings['form_append']) != '') 
                            {
                                $input_text = $input_text . ' ' . trim($aiomatic_Main_Settings['form_append']);
                            }
                            $post_id = get_the_ID();
                            $input_text = aiomatic_replaceEmbeddingsAIPostShortcodes($input_text, $post_id);
                            $current_user = wp_get_current_user();
                            if ( !($current_user instanceof WP_User) || !is_user_logged_in()) 
                            {
                                $input_text = str_replace('%%user_name%%', '', $input_text);
                                $input_text = str_replace('%%user_email%%', '' , $input_text);
                                $input_text = str_replace('%%user_display_name%%', '', $input_text);
                                $input_text = str_replace('%%user_role%%', '', $input_text);
                                $input_text = str_replace('%%user_id%%', '' , $input_text);
                                $input_text = str_replace('%%user_firstname%%', '' , $input_text);
                                $input_text = str_replace('%%user_lastname%%', '' , $input_text);
                                $input_text = str_replace('%%user_description%%', '' , $input_text);
                                $input_text = str_replace('%%user_url%%', '' , $input_text);
                            }
                            else
                            {
                                $input_text = str_replace('%%user_name%%', $current_user->user_login, $input_text);
                                $input_text = str_replace('%%user_email%%', $current_user->user_email , $input_text);
                                $input_text = str_replace('%%user_display_name%%', $current_user->display_name, $input_text);
                                $input_text = str_replace('%%user_role%%', implode(',', $current_user->roles), $input_text);
                                $input_text = str_replace('%%user_id%%', $current_user->ID , $input_text);
                                $input_text = str_replace('%%user_firstname%%', $current_user->user_firstname , $input_text);
                                $input_text = str_replace('%%user_lastname%%', $current_user->user_lastname , $input_text);
                                $user_desc = get_the_author_meta( 'description', $current_user->ID );
                                $input_text = str_replace('%%user_description%%', $user_desc , $input_text);
                                $user_url = get_the_author_meta( 'user_url', $current_user->ID );
                                $input_text = str_replace('%%user_url%%', $user_url , $input_text);
                            }
                        }
                        if (isset($aiomatic_Chatbot_Settings['max_input_length']) && $aiomatic_Chatbot_Settings['max_input_length'] != '' && is_numeric($aiomatic_Chatbot_Settings['max_input_length'])) 
                        {
                            if(strlen($input_text) > intval($aiomatic_Chatbot_Settings['max_input_length']))
                            {
                                $input_text = substr($input_text, 0, intval($aiomatic_Chatbot_Settings['max_input_length']));
                            }
                        }
                        if(isset($_REQUEST['pdf_data']))
                        {
                            $embedding_namespace = stripslashes($_REQUEST['pdf_data']);
                        }
                        else
                        {
                            $embedding_namespace = '';
                        }
                        if(empty($embedding_namespace))
                        {
                            if (isset($aiomatic_Chatbot_Settings['persistent']) && $aiomatic_Chatbot_Settings['persistent'] == 'vector')
                            {
                                $embedding_namespace = 'persistentchat_' . $user_id;
                            }
                            else
                            {
                                if(isset($_REQUEST['embeddings_namespace']) && !empty($_REQUEST['embeddings_namespace']))
                                {
                                    $embedding_namespace = $_REQUEST['embeddings_namespace'];
                                }
                            }
                        }
                        $remember_string = '';
                        if(isset($_REQUEST['remember_string']))
                        {
                            $remember_string = stripslashes($_REQUEST['remember_string']);
                        }
                        if (isset($aiomatic_Chatbot_Settings['chatbot_prepend']) && trim($aiomatic_Chatbot_Settings['chatbot_prepend']) != '') 
                        {
                            $remember_string = trim($aiomatic_Chatbot_Settings['chatbot_prepend']) . ' ' . $remember_string;
                        }
                        if (isset($aiomatic_Chatbot_Settings['chatbot_append']) && trim($aiomatic_Chatbot_Settings['chatbot_append']) != '') 
                        {
                            $remember_string = $remember_string . ' ' . trim($aiomatic_Chatbot_Settings['chatbot_append']);
                        }
                        if(!empty(trim($remember_string)))
                        {
                            $input_text = trim($remember_string) . PHP_EOL . $input_text;
                        }
                        $model = sanitize_text_field(stripslashes($_REQUEST['model']));
                        $temperature = sanitize_text_field($_REQUEST['temp']);
                        $top_p = sanitize_text_field($_REQUEST['top_p']);
                        $presence_penalty = sanitize_text_field($_REQUEST['presence']);
                        $frequency_penalty = sanitize_text_field($_REQUEST['frequency']);
                        $models = aiomatic_get_all_models_claude();
                        if(!in_array($model, $models))
                        {
                            $aiomatic_result = esc_html__('Invalid model provided: ', 'aiomatic-automatic-ai-content-writer') . $model;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        $temperature = floatval($temperature);
                        $top_p = floatval($top_p);
                        $presence_penalty = floatval($presence_penalty);
                        $frequency_penalty = floatval($frequency_penalty);
                        if($temperature < 0 || $temperature > 2)
                        {
                            $aiomatic_result = esc_html__('Invalid temperature provided: ', 'aiomatic-automatic-ai-content-writer') . $temperature;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if($top_p < 0 || $top_p > 1)
                        {
                            $aiomatic_result = esc_html__('Invalid top_p provided: ', 'aiomatic-automatic-ai-content-writer') . $top_p;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if($presence_penalty < -2 || $presence_penalty > 2)
                        {
                            $aiomatic_result = esc_html__('Invalid presence_penalty provided: ', 'aiomatic-automatic-ai-content-writer') . $presence_penalty;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        if($frequency_penalty < -2 || $frequency_penalty > 2)
                        {
                            $aiomatic_result = esc_html__('Invalid frequency_penalty provided: ', 'aiomatic-automatic-ai-content-writer') . $frequency_penalty;
                            $this->aiomatic_event_exit($aiomatic_result);
                        }
                        $used_token_count = 0;
                        if(is_numeric($user_token_cap_per_day))
                        {
                            if(empty($user_id) || $user_id == 0 || !is_numeric($user_id))
                            {
                                $aiomatic_result = sprintf( wp_kses( __( 'You are not allowed to access this form if you are not logged in. Please <a href="%s" target="_blank">log in</a> to continue.', 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), wp_login_url(get_permalink()) );
                                $this->aiomatic_event_exit($aiomatic_result);
                            }
                            $used_token_count = get_user_meta($user_id, 'aiomatic_used_chat_tokens', true);
                            if($used_token_count !== '' && $used_token_count !== false && is_numeric($used_token_count))
                            {
                                $used_token_count = intval($used_token_count);
                                if($used_token_count > $user_token_cap_per_day)
                                {
                                    $aiomatic_result = esc_html__('The daily token count for your user account was exceeded! Please try again tomorrow.', 'aiomatic-automatic-ai-content-writer');
                                    $this->aiomatic_event_exit($aiomatic_result);
                                }
                            }
                            else
                            {
                                $used_token_count = 0;
                            }
                        }
                        $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                        $appids = array_filter($appids);
                        $token = $appids[array_rand($appids)];
                        $token = apply_filters('aiomatic_openai_api_key', $token);
                        $max_tokens = aiomatic_get_max_tokens($model);
                        $query_token_count = count(aiomatic_encode($input_text));
                        $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $input_text, $query_token_count);
                        $thread_id = '';
                        $error = '';
                        $finish_reason = '';
                        do_action('aiomatic_calling_stream', $input_text, $model);
                        aiomatic_generate_text($token, $model, $input_text, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, true, 'shortcodeChat',  0, $finish_reason, $error, $no_internet, $no_embeddings, true, $vision_file, $user_question, 'user', '', $thread_id, $embedding_namespace, $function_result, $file_data, false, $store_data, $forced_functions);
                    }
                }
                exit;
            }
        }

        private function aiomatic_event_exit($message)
        {
            echo "event: message\n";
            echo 'data: {"error":[{"message":"' . $message . '"}]}';
            echo "\n\n";
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            echo 'data: {"choices":[{"finish_reason":"stop"}]}';
            echo "\n\n";
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            exit;
        }
    }
    Aiomatic_Claude_Streaming::get_instance();
}
?>