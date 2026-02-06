<?php
function aiomatic_realtime_chat_settings_updated($old_value, $value, $option) 
{
    if (array_key_exists('remote_chat', $value) && (!isset($old_value['remote_chat']) || $value['remote_chat'] !== $old_value['remote_chat'])) 
    {
        $myop = get_option('aiomatic_realtime_chat_page_id', false);
        if($myop !== false)
        {
            if(is_numeric($myop))
            {
                $myop = array($myop);
            }
            $changedone = false;
            foreach($myop as $mind => $marr)
            {
                $tp = get_post($marr);
                if($tp === null)
                {
                    unset($myop[$mind]);
                    $changedone = true;
                }
            }
            if($changedone == true)
            {
                aiomatic_update_option('aiomatic_realtime_chat_page_id', $myop);
            }
            if (!isset($value['remote_chat']) || trim($value['remote_chat']) != 'on')
            {
                foreach($myop as $mind => $marr)
                {
                    wp_delete_post($marr, true);
                    delete_option('aiomatic_realtime_chat_page_id');
                }
            }
        }
    }
    else
    {
        if(!isset($value['remote_chat']))
        {
            $myop = get_option('aiomatic_realtime_chat_page_id', false);
            if($myop !== false)
            {
                if(is_numeric($myop))
                {
                    $myop = array($myop);
                }
                foreach($myop as $mind => $marr)
                {
                    wp_delete_post($marr, true);
                    delete_option('aiomatic_realtime_chat_page_id');
                }
            }
        }
    }
}
add_action('aiomatic_update_option_aiomatic_Realtime_Chatbot_Settings', 'aiomatic_chat_realtime_settings_updated', 10, 3);
function aiomatic_realtime_chatbot_panel()
{
   $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
   if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
   {
      ?>
<h1><?php echo esc_html__("You must add an OpenAI API Key into the plugin's 'Settings' menu before you can use this feature!", 'aiomatic-automatic-ai-content-writer');?></h1>
<?php
return;
   }
   if (aiomatic_check_if_azure_or_others($aiomatic_Main_Settings)) 
   {
        ?>
  <h1><?php echo esc_html__("Only OpenAI API is supported for this feature!", 'aiomatic-automatic-ai-content-writer');?></h1>
  <?php
       return;
   }
   $aiomatic_Realtime_Chatbot_Settings = get_option('aiomatic_Realtime_Chatbot_Settings', false);
   if (isset($aiomatic_Realtime_Chatbot_Settings['remote_chat'])) {
        $remote_chat = $aiomatic_Realtime_Chatbot_Settings['remote_chat'];
    } else {
        $remote_chat = '';
    }
    if (isset($aiomatic_Realtime_Chatbot_Settings['allow_chatbot_site'])) {
         $allow_chatbot_site = $aiomatic_Realtime_Chatbot_Settings['allow_chatbot_site'];
     } else {
         $allow_chatbot_site = '';
     }
?>
<div class="wp-header-end"></div>
<div class="wrap gs_popuptype_holder seo_pops">
    <h2 class="cr_center"><?php echo esc_html__("AI Realtime Chat", 'aiomatic-automatic-ai-content-writer');?></h2>
    <nav class="nav-tab-wrapper">
        <a href="#tab-t" class="nav-tab"><?php echo esc_html__("Tutorial", 'aiomatic-automatic-ai-content-writer');?></a>
        <a href="#tab-0" class="nav-tab"><?php echo esc_html__("Custom Realtime Chat Builder", 'aiomatic-automatic-ai-content-writer');?></a>
        <a href="#tab-1" class="nav-tab"><?php echo esc_html__("Remote Chatbot", 'aiomatic-automatic-ai-content-writer');?></a>
    </nav>
        <form autocomplete="off" id="myForm" method="post" action="<?php if(is_multisite() && is_network_admin()){echo '../options.php';}else{echo 'options.php';}?>">
        <div class="cr_autocomplete">
 <input type="password" id="PreventChromeAutocomplete" 
  name="PreventChromeAutocomplete" autocomplete="address-level4" />
</div>
<?php
    settings_fields('aiomatic_option_group6');
    do_settings_sections('aiomatic_option_group6');
    if (isset($_GET['settings-updated'])) {
?>
<div id="message" class="updated">
<p class="cr_saved_notif"><strong>&nbsp;<?php echo esc_html__('Settings saved.', 'aiomatic-automatic-ai-content-writer');?></strong></p>
</div>
<?php
}
?>
<div class="aiomatic_class">
<div id="tab-t" class="tab-content">
<br/><h4><?php echo esc_html__("Step 1: Enable the Shortcode and Configure Parameters", 'aiomatic-automatic-ai-content-writer');?></h4>
<p><?php echo esc_html__("To enable this feature, you need to use the [aiomatic-realtime-chat] shortcode. This shortcode provides flexibility with various parameters, allowing you to tailor the chat session to your needs.", 'aiomatic-automatic-ai-content-writer');?></p>

<h5><?php echo esc_html__("Basic Shortcode Syntax:", 'aiomatic-automatic-ai-content-writer');?></h5>
<pre><?php echo esc_html__('[aiomatic-realtime-chat]', 'aiomatic-automatic-ai-content-writer');?></pre>

<h5><?php echo esc_html__("Available Parameters:", 'aiomatic-automatic-ai-content-writer');?></h5>
<ul>
    <li><strong><?php echo esc_html__("autostart:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Start the session automatically (yes) or wait for the user to initiate (no).", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("model:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Specify the AI model to be used for processing the chat.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("voice:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the voice option for the session's responses.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("temperature:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Adjust the randomness of the AI's responses. Lower values result in more focused answers, while higher values make them more creative.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("max_tokens:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Define the maximum token limit for AI responses.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("instructions:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Provide specific instructions or context for the AI.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("start_recording:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Customize the text displayed on the button to start recording.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("stop_recording:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Customize the text displayed on the button to stop recording.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("record_button_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the background color of the recording button.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("record_text_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the text color for the recording button.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("record_status_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the background color of the status indicator.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("record_status_text_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Customize the text color of the status indicator.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("chat_container_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Define the background color of the chat container.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("show_chat_log:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Select if you want to show a textual chat log in the user interface. (show/hide)", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("show_user_log:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Select if you want to show a textual chat log in the user interface also for the user's voice input. (show/hide)", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("chat_log_container_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the color of the chat log container.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("ai_bubble_text_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the color of the chat log bubble text for the AI.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("ai_bubble_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the color of the chat log bubble for the AI.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("user_bubble_text_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the color of the chat log bubble text for the user.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("user_bubble_color:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set the color of the chat log bubble for the user.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("user_token_cap_per_day:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Set a maximum number of allowed tokens to be used for each user, per day.", 'aiomatic-automatic-ai-content-writer');?></li>
</ul>

<h5><?php echo esc_html__("Example Usage:", 'aiomatic-automatic-ai-content-writer');?></h5>
<pre><?php echo esc_html__('[aiomatic-realtime-chat autostart="no" model="gpt-realtime" voice="verse" temperature="0.7"]', 'aiomatic-automatic-ai-content-writer');?></pre>

<h4><?php echo esc_html__("Step 2: Interact with the Start/Stop Recording Button", 'aiomatic-automatic-ai-content-writer');?></h4>
<p><?php echo esc_html__("When you include the shortcode on your page or post, it renders a button and status indicator. Here's how the interaction works:", 'aiomatic-automatic-ai-content-writer');?></p>

<h5><?php echo esc_html__("Visual Indicators:", 'aiomatic-automatic-ai-content-writer');?></h5>
<ul>
    <li><strong><?php echo esc_html__("Status Text:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Displays \"Not Recording\" when idle and \"Recording...\" when active.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("Button Text:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("Alternates between \"Start Recording\" and \"Stop Recording\" based on the session state.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><strong><?php echo esc_html__("Processing State:", 'aiomatic-automatic-ai-content-writer');?></strong> <?php echo esc_html__("While the session processes, the button is temporarily disabled and displays a \"Processing...\" message.", 'aiomatic-automatic-ai-content-writer');?></li>
</ul>

<h5><?php echo esc_html__("Customization Options:", 'aiomatic-automatic-ai-content-writer');?></h5>
<p><?php echo esc_html__("The colors and text displayed during interaction can be customized using shortcode parameters like record_button_color, record_text_color, and others.", 'aiomatic-automatic-ai-content-writer');?></p>

<h4><?php echo esc_html__("Step 3: Test the Functionality", 'aiomatic-automatic-ai-content-writer');?></h4>
<p><?php echo esc_html__("After embedding the shortcode and configuring your parameters, it's time to test the setup:", 'aiomatic-automatic-ai-content-writer');?></p>

<ol>
    <li><?php echo esc_html__("Add the shortcode to a page or post.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><?php echo esc_html__("Visit the page on your website.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><?php echo esc_html__("Verify the following:", 'aiomatic-automatic-ai-content-writer');?><ul>
            <li><?php echo esc_html__("The session starts and stops correctly when interacting with the button.", 'aiomatic-automatic-ai-content-writer');?></li>
            <li><?php echo esc_html__("Status indicators and button text update dynamically.", 'aiomatic-automatic-ai-content-writer');?></li>
            <li><?php echo esc_html__("The button becomes temporarily disabled while processing.", 'aiomatic-automatic-ai-content-writer');?></li>
        </ul>
    </li>
</ol>

<h4><?php echo esc_html__("Troubleshooting", 'aiomatic-automatic-ai-content-writer');?></h4>
<p><?php echo esc_html__("If the functionality isn't working as expected, consider the following:", 'aiomatic-automatic-ai-content-writer');?></p>
<ul>
    <li><?php echo esc_html__("Ensure you've added a valid OpenAI API key in the plugin's settings.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><?php echo esc_html__("Note that Azure/Claude APIs are currently not supported for this feature.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><?php echo esc_html__("Double-check your shortcode parameters for accuracy.", 'aiomatic-automatic-ai-content-writer');?></li>
</ul>

<h4><?php echo esc_html__("Advanced Integration Details", 'aiomatic-automatic-ai-content-writer');?></h4>
<p><?php echo esc_html__("The Realtime Chat shortcode integrates with the Aiomatic plugin's settings and validates your API configuration. If the required settings are missing, users will see an error message prompting them to configure their API key.", 'aiomatic-automatic-ai-content-writer');?></p>

<h5><?php echo esc_html__("Key Points of the Integration:", 'aiomatic-automatic-ai-content-writer');?></h5>
<ul>
    <li><?php echo esc_html__("The plugin dynamically injects styles and scripts based on the shortcode's parameters.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><?php echo esc_html__("Custom CSS rules for button colors, text colors, and container styling are applied inline.", 'aiomatic-automatic-ai-content-writer');?></li>
    <li><?php echo esc_html__("JavaScript handles AJAX requests to maintain a seamless interaction experience.", 'aiomatic-automatic-ai-content-writer');?></li>
</ul>

<h4><?php echo esc_html__("Final Thoughts", 'aiomatic-automatic-ai-content-writer');?></h4>
<p><?php echo esc_html__("With the Aiomatic Realtime Chat functionality, you can elevate user engagement by providing an interactive and responsive AI-driven experience. Configure the shortcode, customize the parameters, and let your users enjoy realtime voice interaction with the chatbot. Happy coding!", 'aiomatic-automatic-ai-content-writer');?></p>

<p><?php echo esc_html__("If you encounter any issues or have suggestions for improvements, feel free to reach out. As always, I'm here to make Aiomatic the best tool for your WordPress projects!", 'aiomatic-automatic-ai-content-writer');?></p>

<?php if(!isset($aiomatic_Main_Settings['hide_videos']) || trim($aiomatic_Main_Settings['hide_videos']) != 'on'){
?>
<h3><?php echo esc_html__("Realtime Chat Functionality Tutorial Video", 'aiomatic-automatic-ai-content-writer');?></h3>
<p class="cr_center"><div class="embedtool"><iframe src="https://www.youtube.com/embed/Zuy6Yd4Ik8o" frameborder="0" allowfullscreen></iframe></div></p>
<?php
}
?>
</div>
<div id="tab-0" class="tab-content">
<table class="widefat">
    <tr><td><h2><?php echo esc_html__("Use the following shortcode to add the customized chatbot to your site:", 'aiomatic-automatic-ai-content-writer');?></h2></td></tr>
    <tr><td colspan="2" class="cr_width_full"><div class="cr_center"><span class="cr_red cr_center cr_width_full cr_margin_block crf_bord" id="customized_chatbot">[aiomatic-realtime-chat model="gpt-realtime" voice="verse" instructions="" show_chat_log="show" show_user_log="show" temperature="1" max_tokens="4096" autostart="no" start_recording="Start Recording" stop_recording="Stop Recording" not_recording="Not Recording" recording="Recording..." record_button_color="#007bff" record_text_color="#ffffff" record_status_color="#f8f9fa" record_status_text_color="#333333" chat_container_color="#e4e8ec" chat_log_container_color="#ffffff" ai_bubble_color="#f5f5f5" ai_bubble_text_color="#000000" user_token_cap_per_day="" user_bubble_color="#0084ff" user_bubble_text_color="#ffffff" enable_god_mode=""]</span><button class="page-title-action aimt-10" id="aiomaticCopyShortcodeText"><?php echo esc_html__("Copy Text", 'aiomatic-automatic-ai-content-writer');?></button></div><br/></td></tr>
    <tr><td colspan="2">
<hr/></td></tr><tr><td colspan="2">
    <h2><?php echo esc_html__("Chat Backend Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
</td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set the AI model which will be used for this shortcode.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("AI Model:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
        <select id="ai_model_b" onchange="anythingChanged()">
        <?php
        foreach(AIOMATIC_REALTIME_MODELS as $aimodel){
            echo '<option value="'.esc_attr($aimodel).'">'.esc_html($aimodel).'</option>';
        }
        ?>
    </select>
    </div>
    </td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set the AI voice which will be used for this shortcode.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("AI Voice:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
        <select id="ai_voice_b" onchange="anythingChanged()">
        <?php
        foreach(AIOMATIC_REALTIME_VOICES as $aivoice){
            echo '<option value="'.esc_attr($aivoice).'">'.esc_html($aivoice).'</option>';
        }
        ?>
    </select>
    </div>
    </td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set the initial instructions for the AI chatbot.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Instructions:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
                <textarea rows="2" id="ai_instructions_b" placeholder="Initial AI instuctions" onchange="anythingChanged()"></textarea>
    </div>
    </td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set a temperature for the AI. If you set a higher temperature, the responses of the AI will be more adventurous. A lower temperature will provide more objective answers.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Temperature:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
                <input type="number" min="0" step="0.01" id="ai_temperature_b" max="1.2" class="cr_width_full" onchange="anythingChanged();" value="" placeholder="0.7">
    </div>
    </td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set a maximum token length of the AI responses.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Max Tokens:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
                <input type="number" min="1" step="1" id="max_tokens_b" class="cr_width_full" onchange="anythingChanged();" value="" placeholder="4096">
    </div>
    </td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set if you want to autostart recording on page load.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Autostart Recording:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
        <select id="autostart_b" onchange="anythingChanged()">
        <option value="no">no</option>
        <option value="yes">yes</option>
    </select>
    </div>
    </td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set if you want to show chat text logs.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Show Chat Text Logs:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
        <select id="show_logs_b" onchange="anythingChanged()">
        <option value="hide">hide</option>
        <option value="show">show</option>
    </select>
    </div>
    </td></tr>
<tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set if you want to show chat text logs.", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Transcribe Also User Voice Input To Text:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
        <select id="show_user_log_b" onchange="anythingChanged()">
        <option value="hide">hide</option>
        <option value="show">show</option>
    </select>
    </div>
    </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to enable/disable the chatbot Extensions feature. This can be used to enable a series of extensions, like social posting, email sending, Amazon product details scraping, website or RSS feed scraping, God Mode, which will allow ultimate control of your WordPress site, allowing it to call functions from WordPress directly. Using this feature, you will be able to create posts directly from the chatbot, assign taxonomies, images and many more! Warning! This is a BETA feature, use it with caution. This will apply only if regular AI models are used (not AI Assistants - for these, the God Mode needs to be enabled from Assistant editing menu). Also, God Mode will work only for logged in administrator privileged users.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Chatbot Extensions:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select multiple id="enable_god_mode_b" class="resize_vertical" onchange="anythingChanged();" >
<?php
echo '<option selected value="disabled">Disabled</option>';
echo '<option value="god_mode_ai">\'AI Generate Reasoning\' Extension</option>';
echo '<option value="god_mode_enable_chart">\'AI Charts\' Extension</option>';
echo '<option value="god_mode_enable_end">\'End Conversation\' Extension</option>';
echo '<option value="god_mode_enable_dalle">\'Dall-E Image\' Extension</option>';
echo '<option value="god_mode_enable_midjourney">\'Midjourney Image\' Extension</option>';
echo '<option value="god_mode_enable_replicate">\'Replicate Image\' Extension</option>';
echo '<option value="god_mode_enable_ideogram">\'Ideogram Image\' Extension</option>';
echo '<option value="god_mode_enable_google_image">\'Google Image\' Extension</option>';
echo '<option value="god_mode_enable_stable">\'Stable Diffusion Image\' Extension</option>';
echo '<option value="god_mode_enable_stable_video">\'Stable Diffusion Video\' Extension</option>';
echo '<option value="god_mode_enable_amazon">\'Amazon Product Listing\' Extension</option>';
echo '<option value="god_mode_enable_amazon_details">\'Amazon Product Details\' Extension</option>';
echo '<option value="god_mode_enable_scraper">\'Website Scraper\' Extension</option>';
echo '<option value="god_mode_enable_rss">\'RSS Feed Parser\' Extension</option>';
echo '<option value="god_mode_enable_google">\'Google SERP Parser\' Extension</option>';
echo '<option value="god_mode_enable_youtube_captions">\'YouTube Video Captions\' Extension</option>';
echo '<option value="god_mode_enable_royalty">\'Royalty Free Image Search\' Extension</option>';
echo '<option value="god_mode_lead_capture">\'Lead Capture\' Extension</option>';
echo '<option value="god_mode_enable_youtube">\'YouTube Video Search\' Extension</option>';
echo '<option value="god_mode_enable_email">\'Email Sending\' Extension</option>';
echo '<option value="god_mode_enable_webhook">\'Webhook Calling\' Extension</option>';
echo '<option value="god_mode_enable_omniblock">\'OmniBlock Rule Trigger\' Extension</option>';
echo '<option value="god_mode_enable_database">\'Database Query\' Extension (WordPress Database Access)</option>';
echo '<option value="god_mode_enable_wp">\'God Mode\' Extension (WordPress Function Calling)</option>';
if(!function_exists('is_plugin_active'))
{
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$no_pl = '';
if (!is_plugin_active('aiomatic-extension-toubiz-api/aiomatic-extension-toubiz-api.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_toubiz"' . esc_html($no_pl) . '>\'Toubiz API Integration\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_facebook_post"' . esc_html($no_pl) . '>\'Facebook Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_twitter_post"' . esc_html($no_pl) . '>\'Twitter (X) Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('threadsomatic-threads-auto-poster/threadsomatic-threads-auto-poster.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_threads_post"' . esc_html($no_pl) . '>\'Threads Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_instagram_post"' . esc_html($no_pl) . '>\'Instagram Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_pinterest_post"' . esc_html($no_pl) . '>\'Pinterest Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('businessomatic-google-my-business-post-generator/businessomatic-google-my-business-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_google_post"' . esc_html($no_pl) . '>\'Google My Business Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('youtubomatic-youtube-post-generator/youtubomatic-youtube-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_youtube_post"' . esc_html($no_pl) . '>\'YouTube Community Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('redditomatic-reddit-post-generator/redditomatic-reddit-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_reddit_post"' . esc_html($no_pl) . '>\'Reddit Posting\' Extension</option>';
$no_pl = '';
if (!is_plugin_active('linkedinomatic-linkedin-post-generator/linkedinomatic-linkedin-post-generator.php')) 
{
    $no_pl = ' disabled';
}
echo '<option value="god_mode_enable_linkedin_post"' . esc_html($no_pl) . '>\'LinkedIn Posting\' Extension</option>';
?>
                    </select>
        </div>
        </td></tr>
    <tr><td colspan="2">
    <h2><?php echo esc_html__("Chat Interface Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
</td></tr>
    <tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set the text of the start recording button. The default is: Start Recording", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Start Recording Button Text:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
                <textarea rows="1" id="start_recording_b" placeholder="Start Recording" onchange="anythingChanged()"><?php
echo esc_html__('Start Recording', 'aiomatic-automatic-ai-content-writer');
?></textarea>
    </div>
    </td></tr>
    <tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set the text of the stop recording button. The default is: Stop Recording", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Stop Recording Button Text:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
                <textarea rows="1" id="stop_recording_b" placeholder="Stop Recording" onchange="anythingChanged()"><?php
echo esc_html__('Stop Recording', 'aiomatic-automatic-ai-content-writer');
?></textarea>
    </div>
    </td></tr>
    <tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set the text of the Not Recording text. The default is: Not Recording", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Not Recording Text:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
                <textarea rows="1" id="not_recording_b" placeholder="Not Recording" onchange="anythingChanged()"><?php
echo esc_html__('Not Recording', 'aiomatic-automatic-ai-content-writer');
?></textarea>
    </div>
    </td></tr>
    <tr><td>
    <div>
    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                    <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Set the text of the Recording... text. The default is: Recording...", 'aiomatic-automatic-ai-content-writer');
?>
                    </div>
                </div>
                <b><?php echo esc_html__("Recording Text:", 'aiomatic-automatic-ai-content-writer');?></b>
                </div>
                </td><td>
                <div>
                <textarea rows="1" id="recording_b" placeholder="Recording..." onchange="anythingChanged()"><?php
echo esc_html__('Recording...', 'aiomatic-automatic-ai-content-writer');
?></textarea>
    </div>
    </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the start recording button. Default is #007bff", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Start Recording Button Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="record_button_color_b" value="#007bff" onchange="anythingChanged()">
        </div>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the text color of the start recording button. Default is #ffffff", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Start Recording Button Text Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="record_text_color_b" value="#ffffff" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the recording status text. Default is #f8f9fa", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Recording Status Background Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="record_status_color_b" value="#f8f9fa" onchange="anythingChanged()">
        </div>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the text color of the recording status text. Default is #333333", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Recording Status Text Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="record_status_text_color_b" value="#333333" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the recording chat container. Default is #e4e8ec", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Chat Container Background Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="chat_container_color_b" value="#e4e8ec" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the recording chat log container. Default is #ffffff", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Chat Log Container Background Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="chat_log_container_color_b" value="#ffffff" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the recording chat log container AI baloon. Default is #f5f5f5", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Chat Log Container AI Baloon Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="ai_bubble_color_b" value="#f5f5f5" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the recording chat log container AI baloon text. Default is #000000", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Chat Log Container AI Baloon Text Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="ai_bubble_text_color_b" value="#000000" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the recording chat log container User baloon. Default is #0084ff", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Chat Log Container User Baloon Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="user_bubble_color_b" value="#0084ff" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a maximum number of tokens users are allowed to use in this specific chatbot, each day.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("User Token Cap Per Day:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="user_token_cap_per_day_b" value="" onchange="anythingChanged()">
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the background color of the recording chat log container User baloon text. Default is #ffffff", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Chat Log Container User Baloon Text Color:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="color" id="user_bubble_text_color_b" value="#ffffff" onchange="anythingChanged()">
        </div>
        </td></tr>
</table>
</div>
<div id="tab-1" class="tab-content">
<h2><?php echo esc_html__("AI Chatbot Embedding On Remote Sites Settings:", 'aiomatic-automatic-ai-content-writer');?></h2>
<table class="wp-list-table widefat fixed striped table-view-list posts">
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to enable embedding of the chatbot on remote websites, using iframes. If you deactivate remote chatbots, all created remote chatbot instances will be also deleted.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Chatbot Embedding On Remote Sites:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                <input type="checkbox" id="remote_chat" name="aiomatic_Realtime_Chatbot_Settings[remote_chat]"<?php
    if ($remote_chat == 'on')
    {
        echo ' checked ';
    }
?>>
        </div>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set a list of domains (separated by commas), which will be allowed to display the chatbot on their site. To allow all sites to add this chatbot, leave this field blank. Example usage: https://www.example.org", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Domain List Allowed To Embed Chatbots:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" id="allow_chatbot_site" placeholder="https://www.example.org" name="aiomatic_Realtime_Chatbot_Settings[allow_chatbot_site]"><?php
    echo esc_textarea($allow_chatbot_site);
?></textarea>
        </div>
        </td></tr>
<?php
if ($remote_chat == 'on')
{
    $myop = get_option('aiomatic_realtime_chat_page_id', false);
    if($myop !== false)
    {
        if(is_numeric($myop))
        {
            $myop = array($myop);
        }
    ?>
    <tr>
        <td colspan="2">
        <b><?php echo esc_html__("You can use these HTML codes to embed the chatbot on other websites:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
    </tr>
    <?php
        $changedone = false;
        if(count($myop) == 0)
        {
?>
    <tr>
        <td colspan="2">
        <b><?php echo esc_html__("No remote chatbot instances created. Click the button from below to create a new instance!", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
    </tr>
<?php
        }
        foreach($myop as $zind => $myopthis)
        {
            if(get_permalink($myopthis) === false)
            {
                unset($myop[$zind]);
                $changedone = true;
                continue;
            }
    ?>
    <tr>
        <td colspan="2">
            <hr/>
        </td>
    </tr>
    <tr>
        <td colspan="2">
        <b><?php echo esc_html__("Embed HTML Code:", 'aiomatic-automatic-ai-content-writer');?></b><br/>
            <span class="cr_red">
    <?php
        echo esc_html('<iframe src="' . get_permalink($myopthis) . '" width="600" height="800" frameborder="0" scrolling="no">' . esc_html__("Your browser does not support iframes.", 'aiomatic-automatic-ai-content-writer') . '</iframe>');
    ?>
            </span>
            <p><?php
        echo sprintf( wp_kses( __( "<a href='%s' class='button' target='_blank'>Edit</a>", 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'data-id' => array(), 'class' => array(), 'href' => array(), 'target' => array() ) ) ), esc_url(get_edit_post_link($myopthis)));
?>&nbsp;<?php
        echo sprintf( wp_kses( __( "<a href='#' data-id='" . esc_attr($myopthis) . "' class='aiomatic_delete_remote_chatbot button'>Delete</a>", 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'data-id' => array(), 'class' => array(), 'href' => array(), 'target' => array() ) ) ), esc_url(get_edit_post_link($myopthis)));
    ?></p>
        </td>
    </tr>
    <?php
        }
        if($changedone == true)
        {
            aiomatic_update_option('aiomatic_realtime_chat_page_id', $myop);
        }
    }
    else
    {     
?>
<tr>
    <td colspan="2">
    <b><?php echo esc_html__("No remote chatbot instances created. Click the button from below to create a new instance.", 'aiomatic-automatic-ai-content-writer');?></b>
    </td>
</tr>
<?php
    }
?>
<tr>
    <td colspan="2">
        <hr/>
    </td>
</tr>
<tr>
    <td colspan="2">
    <button id="aiomatic_add_remote_chatbot" class="button"><?php echo esc_html__("Add A New Remote Chatbot", 'aiomatic-automatic-ai-content-writer');?></button>
    </td>
</tr>
<?php
}
?>
</table>
</div>
</div>
<div><p class="crsubmit"><input type="submit" name="btnSubmit" id="btnSubmit" class="button button-primary" value="<?php echo esc_html__("Save Settings", 'aiomatic-automatic-ai-content-writer');?>"/></p></div>
    </form>
</div>
<hr/>

<div class="wrap">
    <h2 class="cr_center"><?php echo esc_html__("Realtime Chat Preview", 'aiomatic-automatic-ai-content-writer');?></h2>
<?php
$preview_settings = array();
echo aiomatic_realtime_chat_shortcode($preview_settings);
?>
    </div>
    <hr/>
<?php
}
?>