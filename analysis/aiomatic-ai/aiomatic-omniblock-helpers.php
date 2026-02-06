<?php
defined('ABSPATH') or die();
function aiomatic_get_omniblock_data($saved_cards, $original_post)
{
    require_once (dirname(__FILE__) . "/res/aiomatic-languages.php");
    if(!isset($original_post->post_content))
    {
        $output = 'Incorrect parameters submitted';
        return $output;
    }
    $sortable_cards = $original_post->post_content;
    $default_block_types = aiomatic_omniblocks_default_block_types(); 
    $all_models = aiomatic_get_all_models(true);
    $all_models_function = aiomatic_get_all_models_function();
    $all_assistants = aiomatic_get_all_assistants(true);
    $all_dalle_models = aiomatic_get_dalle_image_models();
    $all_stable_models = aiomatic_get_stable_image_models();
    $all_formats = ['post-format-standard' => 'Standard', 'post-format-aside' => 'Aside', 'post-format-gallery' => 'Gallery', 'post-format-link' => 'Link', 'post-format-image' => 'Image', 'post-format-quote' => 'Quote', 'post-format-status' => 'Status', 'post-format-video' => 'Video', 'post-format-audio' => 'Audio', 'post-format-chat' => 'Chat'];
    $all_dalle_sizes = ['256x256' => '256x256', '512x512' => '512x512', '1024x1024' => '1024x1024', '1024x1792' => '1024x1792 (only for Dall-E 3)', '1792x1024' => '1792x1024 (only for Dall-E 3)', '1024x1536' => '1024x1536 (only for gpt-image-1)', '1536x1024' => '1536x1024 (only for gpt-image-1)'];
    $all_stable_sizes = ['512x512' => '512x512', '1024x1024' => '1024x1024'];
    $all_midjourney_sizes = ['512x512' => '512x512', '1024x1024' => '1024x1024', '1024x1792' => '1024x1792', '1792x1024' => '1792x1024'];
    $all_replicate_sizes = ['512x512' => '512x512', '1024x1024' => '1024x1024', '1024x1792' => '1024x1792', '1792x1024' => '1792x1024'];
    $all_ideogram_sizes = ['512x512' => '512x512', '1024x1024' => '1024x1024', '1024x1792' => '1024x1792', '1792x1024' => '1792x1024'];
    $all_stable_video_sizes = ['768x768' => '768x768', '1024x576' => '1024x576', '576x1024' => '576x1024'];
    $all_scraper_types = ['auto' => 'Auto Detect', 'visual' => 'Visual Selector', 'id' => 'ID', 'class' => 'Class', 'xpath' => 'XPath/CSS Selector', 'regex' => 'Regex - First Match', 'regexall' => 'Regex - All Matches', 'raw' => 'Full HTML'];
    
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['deepl_auth'])) {
        $deepl_auth = $aiomatic_Main_Settings['deepl_auth'];
    } else {
        $deepl_auth = '';
    }
    if (isset($aiomatic_Main_Settings['bing_auth'])) {
        $bing_auth = $aiomatic_Main_Settings['bing_auth'];
    } else {
        $bing_auth = '';
    }
    $cont = '';
    $cats = '';
    $save_term = array();
    $terms = wp_get_object_terms( $original_post->ID, 'ai_template_categories' );
    if(!is_wp_error($terms))
    {
        foreach($terms as  $tm)
        {
            $save_term[] = $tm->slug;
        }
        $cats = implode(';', $save_term);
    }
    $output = '<tr>
    <td class="ai-flex">
          <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
             <div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__("Set the name of the OmniBlock template to save.", 'aiomatic-automatic-ai-content-writer') . '</div>
          </div>
          <b>' . esc_html__("OmniBlock Template Title:", 'aiomatic-automatic-ai-content-writer') . '</b></div>
    </td>
    <td>
    <input type="hidden" id="omni_template_id" class="cr_width_full" name="omni_template_id" value="' . esc_html($original_post->ID) . '">
    <input type="text" id="omni_template_edit" class="cr_width_full" name="omni_template_edit" value="' . esc_html($original_post->post_title) . '" placeholder="OmniBlock Template Title">
    </td>
 </tr>
 <tr>
    <td class="ai-flex">
          <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
             <div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__("Set the category of the OmniBlock template to save. You can add multiple categories, separated by ;", 'aiomatic-automatic-ai-content-writer') . '</div>
          </div>
          <b>' . esc_html__("OmniBlock Template Category:", 'aiomatic-automatic-ai-content-writer') . '</b></div>
    </td>
    <td>
    <input type="text" id="omni_template_cat_edit" class="cr_width_full" list="edit_cats" name="omni_template_cat_edit" value="' . esc_html($cats) . '" placeholder="OmniBlock Template Category">
    <datalist id="edit_cats">';
$terms = get_terms([
'taxonomy' => 'ai_template_categories',
'hide_empty' => false,
]);
$aiomatic_tax_names = array();
foreach ($terms as $term)
{
    $aiomatic_tax_names[] = $term->slug;
}
foreach($aiomatic_tax_names as $ln)
{
    $output .= '<option>' . $ln . '</option>';
}
$output .= '</datalist>
    </td>
 </tr>
 <tr><td colspan="2">
<div><button id="ai-save-omni-template_edit" class="button">' . esc_html__("Save OmniBlock Template", 'aiomatic-automatic-ai-content-writer') . '</button></div>
</td></tr>';
    $warning = '';
    if(is_array($saved_cards))
    {
        $save_type_found = false;
        $zindex = 1;
        $num_cards = count($saved_cards);
        foreach ($saved_cards as $card_id) 
        {
            if(!isset($card_id['type']))
            {
                $warning .= '<p>' . esc_html__('OmniBlock data is incomplete or corrupted!', 'aiomatic-automatic-ai-content-writer') . '</p>';
                continue;
            }
            $card_type_found = array();
            foreach($default_block_types as $def_card)
            {
                if($card_id['type'] == $def_card['id'])
                {
                    $card_type_found = $def_card;
                    break;
                }
            }
            if(empty($card_type_found))
            {
                $warning .= '<p>' . esc_html__('OmniBlock type not found: ', 'aiomatic-automatic-ai-content-writer') . $card_id['type'] . '</p>';
            }
            if(isset($card_type_found['type']) && $card_type_found['type'] == 'save')
            {
                $save_type_found = true;
            }
            if($zindex == $num_cards)
            {
                if(isset($card_type_found['type']) && $card_type_found['type'] != 'save')
                {
                    if(strstr($cats, 'manual') === false)
                    {
                        $warning .= '<p>' . esc_html__('Last OmniBlock is not a "Action" type block! In automatic runs, all data created after the last "Action" type block will be lost.', 'aiomatic-automatic-ai-content-writer') . '</p>';
                    }
                }
            }
            $zindex++;
            if(isset($card_type_found['type']) && $card_type_found['type'] == 'create' )
            {
                if(isset($card_type_found['shortcodes']) && !empty($card_type_found['shortcodes']))
                {
                    $local_shortcodes = array();
                    foreach($card_type_found['shortcodes'] as $shtc)
                    {
                        $local_shortcodes[] = '%%' . $shtc . $card_id['identifier'] . '%%';
                    }
                    if($shtc == 'file_')
                    {
                        $local_shortcodes[] = '%%xlsx_' . $card_id['identifier'] . '_';
                    }
                    if($shtc == 'webhook_data_')
                    {
                        $local_shortcodes[] = '%%webhook_data_' . $card_id['identifier'] . '_';
                    }
                    $not_found_blocks = array();
                    $block_found = false;
                    foreach ($saved_cards as $saved_card_id) 
                    {
                        foreach($saved_card_id['parameters'] as $name => $orig_text)
                        {
                            foreach($local_shortcodes as $lsc)
                            {
                                if(!empty($orig_text) && strstr($orig_text, $lsc) !== false)
                                {
                                    $block_found = true;
                                    break;
                                }
                            }
                            if($block_found == true)
                            {
                                break;
                            }
                        }
                        if($block_found == true)
                        {
                            break;
                        }
                    }
                    if($block_found === false)
                    {
                        $not_found_blocks[] = $card_id['identifier'];
                    }
                    if(!empty($not_found_blocks))
                    {
                        if(strstr($cats, 'manual') === false)
                        {
                            $warning .= '<p>' . esc_html__('The following OmniBlock IDs are not used in the queue (you can remove them): ', 'aiomatic-automatic-ai-content-writer') . implode(',', $not_found_blocks) . '</p>';
                        }
                    }
                }
            }
        }
        if($save_type_found === false)
        {
            if(strstr($cats, 'manual') === false)
            {
                $warning .= '<p>' . esc_html__('No "Action" type OmniBlock added in the queue! Add a "Action" type OmniBlock, like: "Save Posts" to store the data which was created by the AI. Otherwise, it will be lost.', 'aiomatic-automatic-ai-content-writer') . '</p>';
            }
        }
    }
    else
    {
        $warning .= '<p>' . esc_html__('Failed to decode OmniBlocks data!', 'aiomatic-automatic-ai-content-writer') . '</p>';
    }
    if($warning != '')
    {
        $output .= '<tr><td colspan="2"><h2>' . esc_html__('Block Validation Errors', 'aiomatic-automatic-ai-content-writer') . ':</h2></td></tr>
        <tr><td colspan="2" class="cr_red">' . $warning . '</td></tr>';
    }
    $output .= '<tr><td colspan="2"><h2>' . esc_html__('Manage AI OmniBlocks', 'aiomatic-automatic-ai-content-writer') . ':</h2><div class="aiseparator aistart"><b>' . esc_html__("OmniBlock Queue Starts Here", 'aiomatic-automatic-ai-content-writer') . '</b></div></td></tr>
    <tr>
    <td colspan="2">
    <input type="hidden" id="sortable_cards_edit" name="aiomatic_sortable_cards_edit" class="cr_width_full" value="' . esc_attr($sortable_cards) . '">
    <ul id="aiomatic_sortable_cards_edit' . esc_html($cont) . '" name="aiomatic_sortable_cards_edit' . esc_html($cont) . '">';
    $last_id = '1';
    $largest_id = '1';
    if(empty($default_block_types))
    {
        $output .= esc_html__('No AI OmniBlock Types Added To This Rule', 'aiomatic-automatic-ai-content-writer');
    }
    else
    {
        
        if(empty($saved_cards) && !is_array($saved_cards))
        {
            $output .= esc_html__('Failed to decode saved blocks data!', 'aiomatic-automatic-ai-content-writer');
        }
        else
        {
            $exec = 1;
            $new_shortcodes_arr = array('%%keyword%%');
            foreach ($saved_cards as $card_id) 
            {
                if(!empty($card_id['type']))
                {
                    $assistant_helper = uniqid();
                    $urlrandval = uniqid();
                    $last_id = $card_id['identifier'];
                    if(aiomatic_compareIds($last_id, $largest_id) > 0)
                    {
                        $largest_id = $last_id;
                    }
                    $card_type_found = array();
                    foreach($default_block_types as $def_card)
                    {
                        if($card_id['type'] == $def_card['id'])
                        {
                            $card_type_found = $def_card;
                            break;
                        }
                    }
                    if(empty($card_type_found))
                    {
                        aiomatic_log_to_file('Warning! OmniBlock type not found for: ' . print_r($card_id, true));
                    }
                    else
                    {
                        $local_shortcodes = array();
                        foreach($card_type_found['shortcodes'] as $shtc)
                        {
                            $local_shortcodes[] = '%%' . $shtc . $card_id['identifier'] . '%%';
                        }
                        $plugin_required = array();
                        if(!empty($card_type_found['required_plugin']))
                        {
                            foreach($card_type_found['required_plugin'] as $pslug => $pname)
                            {
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                if (!is_plugin_active($pslug)) 
                                {
                                    $plugin_required[] = 'You need enable the "' . $pname[0] . '" plugin for this OmniBlock type to work: ' . $pname[1];
                                }
                            }
                        }
                        $output .= '<li data-id-str="' . esc_html($cont) . '" class="omniblock-card"><input data-clone-index="xc' . uniqid() . '" class="cr_center aiomatic-bold aiomatic-indigo omniblock-title" card-type="' . esc_html($card_type_found['id']) . '" type="text" onchange="updateSortableInputAI(\'\', \'_edit\');" placeholder="' . esc_html__('OmniBlock Title', 'aiomatic-automatic-ai-content-writer') . '"  title="' . esc_html__('OmniBlock Title', 'aiomatic-automatic-ai-content-writer') . '" value="' . esc_attr($card_id['name']) . '">
                        <input type="hidden" class="omniblock-id" card-type="' . esc_html($card_type_found['id']) . '" value="' . esc_attr($last_id) . '">
                        <input type="hidden" class="omniblock-shortcodes" card-type="' . esc_html($card_type_found['id']) . '" value="' . esc_attr(implode(',', $local_shortcodes)) . '">';
                        if($card_type_found['id'] == 'text_spinner')
                        {
                            if (!isset($aiomatic_Main_Settings['spin_text']) || $aiomatic_Main_Settings['spin_text'] === 'disabled')
                            {
                                $plugin_required[] = 'Spinning disabled from \'Settings\' -> \'Bulk Posts\' tab -> \'Spin Text Using Word Synonyms\' settings field, this OmniBlock will not function';
                            }
                            if (isset($aiomatic_Main_Settings['spin_what']) && $aiomatic_Main_Settings['spin_what'] === 'bulk') 
                            {
                                $plugin_required[] = 'Spinning disabled for OmniBlocks, from plugin\'s \'Settings\' menu -> \'Bulk Posts\' tab -> \'Enable Spinner For\' settings field, this OmniBlock will not function';
                            }
                        }
                        elseif($card_type_found['id'] == 'embeddings')
                        {
                            if ((!isset($aiomatic_Main_Settings['pinecone_app_id']) || trim($aiomatic_Main_Settings['pinecone_app_id']) == '') && (!isset($aiomatic_Main_Settings['qdrant_app_id']) || trim($aiomatic_Main_Settings['qdrant_app_id']) == ''))
                            {
                                $plugin_required[] = 'You must add an OpenAI/AiomaticAPI API Key into the plugin\'s \'Settings\' menu before you can use this feature!';
                            }
                            if ((!isset($aiomatic_Main_Settings['pinecone_index']) || trim($aiomatic_Main_Settings['pinecone_index']) == '') && (!isset($aiomatic_Main_Settings['qdrant_index']) || trim($aiomatic_Main_Settings['qdrant_index']) == ''))
                            {
                                $plugin_required[] = 'You must add a Pinecone API or a Qdrant API key in the plugin\'s \'Settings\' menu (API Keys tab), before you can use this feature!';
                            }
                        }
                        foreach($card_id['parameters'] as $name => $orig_text)
                        {
                            if(isset($card_type_found['parameters'][$name]) && isset($card_type_found['parameters'][$name]['type']) && ($card_type_found['parameters'][$name]['type'] == 'text' || $card_type_found['parameters'][$name]['type'] == 'textarea' || $card_type_found['parameters'][$name]['type'] == 'url' || $card_type_found['parameters'][$name]['type'] == 'scraper_string'))
                            {
                                foreach($new_shortcodes_arr as $sha)
                                {
                                    $orig_text = str_replace($sha, '', $orig_text);
                                }
                                $incorrect_sh = array();
                                foreach($default_block_types as $cardt)
                                {
                                    foreach($cardt['shortcodes'] as $shc)
                                    {
                                        preg_match_all('~(%%' . $shc . '[a-zA-Z0-9]*?%%)~', $orig_text, $submatches);
                                        if(isset($submatches[1][0]))
                                        {
                                            foreach($submatches[1] as $incsh)
                                            {
                                                $incorrect_sh[] = $incsh;
                                            }
                                        }
                                    }
                                }
                                if(!empty($incorrect_sh))
                                {
                                    $plugin_required[] = 'This block has some incorrect shortcodes: ' . implode(',', $incorrect_sh);
                                }
                            }
                        }
                        if(!empty($plugin_required))
                        {
                            $output .= '<p class="requirement cr_red"><ul class="requirement cr_red">';
                            foreach($plugin_required as $pr)
                            {
                                $output .= '<li>' . $pr . '</li>';
                            }
                            $output .= '</ul></p>';
                        }
                        $output .= '<div class="card-name';
                        if($card_type_found['type'] == 'save')
                        {
                            $output .= ' aisave-content';
                        }
                        else
                        {
                            $output .= ' aicreate-content';
                        }
                        $output .= '">' . esc_html__('OmniBlock Type:', 'aiomatic-automatic-ai-content-writer') . '&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__('Created shortcodes by this OmniBlock (usable in OmniBlocks from below this one): ', 'aiomatic-automatic-ai-content-writer');
                        $output .= '<ul>';
                        foreach($card_type_found['shortcodes'] as $shtc)
                        {
                            $output .= '<li>%%' . $shtc . $card_id['identifier'] . '%%</li>';
                        }
                        $output .= '</ul>';
                        if (isset($aiomatic_Main_Settings['omni_webhook']) && trim($aiomatic_Main_Settings['omni_webhook']) == 'on')
                        {
                            $rest_url = rest_url('omniblock/v1/webhook');
                            if(isset($card_id['parameters']['api_key']) && !empty(trim($card_id['parameters']['api_key'])))
                            {
                                $rest_url = add_query_arg('apikey', trim($card_id['parameters']['api_key']), $rest_url);
                            }
                            $rest_url = add_query_arg('omniblockid', trim($cont) . '_' . trim($card_id['identifier']), $rest_url);
                            $rest_url = add_query_arg('input', urlencode('Webhooks in WordPress'), $rest_url);
                            $card_type_found['description'] = str_replace('%%webhook_url%%', '<br/><span class="cr_red disable_drag">' . $rest_url . '</span>', $card_type_found['description']);
                        }
                        else
                        {
                            $card_type_found['description'] = str_replace('%%webhook_url%%', '<span class="cr_red">' . esc_html__('OmniBlock Webhook functionality not enabled in \'Settings\' menu of the plugin!' , 'aiomatic-automatic-ai-content-writer') . '</span>', $card_type_found['description']);
                        }
                        $card_type_found['description'] = str_replace('%%filter_name%%', '<br/><span class="cr_red disable_drag">aiomatic_diy_omniblock_' . trim($card_id['identifier']) . '</span>', $card_type_found['description']);
                        $output .= '</div></div>&nbsp;' . esc_attr($card_type_found['name']) . '</div><p class="card-desc">' . $card_type_found['description'] . '</p><div class="ai-collapsible-holder"><button class="aicollapsible" title="' . esc_html__('Show/Hide Parameters', 'aiomatic-automatic-ai-content-writer') . '"><img class="controls-icon" src="' . plugin_dir_url( __FILE__ ) . 'images/controls.png' . '"></button></div><div class="aicollapsible-parameters">';
                        $output .= '<h3>' . esc_html__('OmniBlock Parameters', 'aiomatic-automatic-ai-content-writer') . '&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__('Set the parameters which will be used in this OmniBlock.', 'aiomatic-automatic-ai-content-writer') . '</div></div></h3><hr/>';
            
                        foreach($card_type_found['parameters'] as $name => $card_type)
                        {
                            $output .= '<h4>' . esc_html($card_type['title']) . ':&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html($card_type['description']) . '</div></div></h4>';
                            if($card_type['type'] == 'text')
                            {
                                $randval = uniqid();
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<div class="main-holder-short" data-id-str="xai' . $randval . '">';
                                $output .= '<input type="text" onchange="updateSortableInputAI(\'\', \'_edit\');" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full" value="' . esc_html($value) . '" id="xai' . $randval . '" placeholder="' . esc_html($card_type['placeholder']) . '">';
                                if(count($new_shortcodes_arr) > 0)
                                {
                                    $output .= '<div class="shortcode-list" data-id-str="xai' . $randval . '">';
                                }
                                foreach($new_shortcodes_arr as $myshort)
                                {
                                    $my_id = explode('_', $myshort);
                                    $my_id = end($my_id);
                                    $my_id = substr($my_id, 0, -2);
                                    $output .=  '<p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Shortcode created by OmniBlock ID: ', 'aiomatic-automatic-ai-content-writer') . $my_id . '">';
                                    $output .=  $myshort;
                                    $output .=  '</p>';
                                }
                                if(count($new_shortcodes_arr) > 0)
                                {
                                    $output .= '</div>';
                                }
                                $output .= '</div>';
                            }
                            elseif($card_type['type'] == 'textarea')
                            {
                                $randval = uniqid();
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<div class="main-holder-short" data-id-str="xai' . $randval . '">';
                                $output .= '<textarea onchange="updateSortableInputAI(\'\', \'_edit\');" class="' . esc_attr($name) . ' cr_width_full" data-clone-index="xc' . uniqid() . '" id="xai' . $randval . '" placeholder="' . esc_html($card_type['placeholder']) . '">' . esc_textarea($value) . '</textarea>';
                                if(count($new_shortcodes_arr) > 0)
                                {
                                    $output .= '<div class="shortcode-list" data-id-str="xai' . $randval . '">';
                                }
                                foreach($new_shortcodes_arr as $myshort)
                                {
                                    $my_id = explode('_', $myshort);
                                    $my_id = end($my_id);
                                    $my_id = substr($my_id, 0, -2);
                                    $output .=  '<p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Shortcode created by OmniBlock ID: ', 'aiomatic-automatic-ai-content-writer') . $my_id . '">';
                                    $output .=  $myshort;
                                    $output .=  '</p>';
                                }
                                if($card_type_found['id'] == 'ai_text_foreach' && $name == 'prompt')
                                {
                                    $output .=  '<p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Shortcode created by OmniBlock ID: ', 'aiomatic-automatic-ai-content-writer') . $my_id . '">';
                                    $output .=  '%%current_input_line%%';
                                    $output .=  '</p>';
                                    $output .=  '<p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Shortcode created by OmniBlock ID: ', 'aiomatic-automatic-ai-content-writer') . $my_id . '">';
                                    $output .=  '%%current_input_line_counter%%';
                                    $output .=  '</p>';
                                    $output .=  '<p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Shortcode created by OmniBlock ID: ', 'aiomatic-automatic-ai-content-writer') . $my_id . '">';
                                    $output .=  '%%all_input_lines%%';
                                    $output .=  '</p>';
                                }
                                if(count($new_shortcodes_arr) > 0)
                                {
                                    $output .= '</div>';
                                }
                                $output .= '</div>';
                            }
                            elseif($card_type['type'] == 'model_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" id="xa' . $assistant_helper . '" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_models as $modelx)
                                {
                                    $output .= '<option value="' . $modelx .'"';
                                    if($value == $modelx)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'model_select_function')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" id="xa' . $assistant_helper . '" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_models_function as $modelx)
                                {
                                    $output .= '<option value="' . $modelx .'"';
                                    if($value == $modelx)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'assistant_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" id="sel_xa' . $assistant_helper . '" onchange="assistantChanged(\'xa' . $assistant_helper . '\');" class="' . esc_attr($name) . ' cr_width_full">';
                                if($all_assistants === false)
                                {
                                    $output .= '<option val="" selected disabled>' . esc_html__("Only OpenAI API is supported for Assistants API", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                }
                                else
                                {
                                    if(count($all_assistants) == 0)
                                    {
                                        $output .= '<option val="" selected disabled>' . esc_html__("No Assistans added, go to the plugin's 'AI Assistans' menu to add new assistants!", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                    }
                                    else
                                    {
                                        $output .= '<option value=""';
                                        if($value == '')
                                        {
                                            $output .= ' selected';
                                        }
                                        $output .= '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                        foreach($all_assistants as $myassistant)
                                        {
                                            $output .= '<option value="' . $myassistant->ID .'"';
                                            if($value == $myassistant->ID)
                                            {
                                                $output .= ' selected';
                                            }
                                            $output .= '>' . esc_html($myassistant->post_title);
                                            $output .= '</option>';
                                        }
                                    }
                                }
                                $output .= '</select>';
                                wp_add_inline_script(md5(get_bloginfo()) . '-footer-script', 'assistantChanged(\'xa' . $assistant_helper . '\');', 'after');
                            }
                            elseif($card_type['type'] == 'dalle_image_size_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_dalle_sizes as $sizeid => $sizex)
                                {
                                    $output .= '<option value="' . esc_attr($sizeid) .'"';
                                    if($value == $sizeid)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($sizex) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'stable_image_size_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_stable_sizes as $sizeid => $sizex)
                                {
                                    $output .= '<option value="' . esc_attr($sizeid) .'"';
                                    if($value == $sizeid)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($sizex) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'midjourney_image_size_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_midjourney_sizes as $sizeid => $sizex)
                                {
                                    $output .= '<option value="' . esc_attr($sizeid) .'"';
                                    if($value == $sizeid)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($sizex) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'replicate_image_size_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_replicate_sizes as $sizeid => $sizex)
                                {
                                    $output .= '<option value="' . esc_attr($sizeid) .'"';
                                    if($value == $sizeid)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($sizex) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'ideogram_image_size_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_ideogram_sizes as $sizeid => $sizex)
                                {
                                    $output .= '<option value="' . esc_attr($sizeid) .'"';
                                    if($value == $sizeid)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($sizex) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'stable_video_size_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_stable_video_sizes as $sizeid => $sizex)
                                {
                                    $output .= '<option value="' . esc_attr($sizeid) .'"';
                                    if($value == $sizeid)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($sizex) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'scraper_type')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="scraper_selector ' . esc_attr($name) . ' cr_width_full" data-id-str="' . esc_html($cont) . '" data-source-field-id="ur' . $urlrandval . '" data-target-field-id="' . $assistant_helper . '">';
                                foreach($all_scraper_types as $index => $modelx)
                                {
                                    $output .= '<option value="' . esc_attr($index) .'"';
                                    if($value == $index)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'scraper_string')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<input onchange="updateSortableInputAI(\'\', \'_edit\');" type="text" id="st' . $assistant_helper . '" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full" value="' . esc_html($value) . '" placeholder="' . esc_html($card_type['placeholder']) . '">';
                            }
                            elseif($card_type['type'] == 'number')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<input onchange="updateSortableInputAI(\'\', \'_edit\');" type="number" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full" value="' . esc_html($value) . '" placeholder="' . esc_html($card_type['placeholder']) . '">';
                            }
                            elseif($card_type['type'] == 'checkbox')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $output .= '<option value="0"';
                                if($value == '0')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("No", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '<option value="1"';
                                if($value == '1')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Yes", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'checkbox_overwrite')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $output .= '<option value="0"';
                                if($value == '0')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("No, but keep duplicates", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '<option value="2"';
                                if($value == '2')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("No, but discard duplicates", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '<option value="1"';
                                if($value == '1')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Yes", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'dalle_image_model_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_dalle_models as $modelx)
                                {
                                    $output .= '<option value="' . $modelx .'"';
                                    if($value == $modelx)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'stable_image_model_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_stable_models as $modelx)
                                {
                                    $output .= '<option value="' . $modelx .'"';
                                    if($value == $modelx)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'google_image_model_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach(AIOMATIC_GOOGLE_IMAGE_MODELS as $modelx)
                                {
                                    $output .= '<option value="' . $modelx .'"';
                                    if($value == $modelx)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'status_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $output .= '<option value="publish"';
                                if($value == "publish")
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Published", 'aiomatic-automatic-ai-content-writer') . '</option>
                                <option value="pending"';
                                if($value == "pending")
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Pending -> Moderate", 'aiomatic-automatic-ai-content-writer') . '</option>
                                <option value="draft"';
                                if($value == "draft")
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Draft -> Moderate", 'aiomatic-automatic-ai-content-writer') . '</option>
                                <option value="private"';
                                if($value == "private")
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Private", 'aiomatic-automatic-ai-content-writer') . '</option>
                                <option value="trash"';
                                if($value == "trash")
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Trash", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'type_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach(get_post_types( '', 'names' ) as $modelx)
                                {
                                    if(strstr($modelx, 'aiomatic_'))
                                    {
                                       continue;
                                    }
                                    $output .= '<option value="' . $modelx .'"';
                                    if($value == $modelx)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'amazon_country_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach(aiomatic_get_amazon_codes() as $key => $modelx)
                                {
                                    $output .= '<option value="' . $key .'"';
                                    if($value == $key)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'amazon_sort_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach(aiomatic_get_amazon_sorts() as $key => $modelx)
                                {
                                    $output .= '<option value="' . esc_attr($key) .'"';
                                    if($value == $key)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'yt_community_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $community_types = array('text' => 'Text', 'image' => 'Image');
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($community_types as $key => $modelx)
                                {
                                    $output .= '<option value="' . esc_attr($key) .'"';
                                    if($value == $key)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'reddit_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $reddit_types = array('auto' => 'Auto', 'link' => 'Link', 'self' => 'Text', 'image' => 'Image', 'video' => 'Video');
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($reddit_types as $key => $modelx)
                                {
                                    $output .= '<option value="' . esc_attr($key) .'"';
                                    if($value == $key)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'method_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $reddit_types = array('POST' => 'POST', 'GET' => 'GET', 'PUT' => 'PUT', 'DELETE' => 'DELETE');
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($reddit_types as $key => $modelx)
                                {
                                    $output .= '<option value="' . esc_attr($key) .'"';
                                    if($value == $key)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'content_type_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $reddit_types = array('JSON' => 'JSON', 'form' => 'Form Data');
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($reddit_types as $key => $modelx)
                                {
                                    $output .= '<option value="' . esc_attr($key) .'"';
                                    if($value == $key)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($modelx) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'facebook_page_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $store = get_option('fbomatic_page_ids', false);
                                if($store !== FALSE)
                                {
                                    $store = explode(',', $store);
                                    $fcount = count($store);
                                    for($i = 0; $i < $fcount; $i++)
                                    {
                                        $exploding = explode('-', $store[$i]);
                                        if(!isset($exploding[2]))
                                        {
                                            continue;
                                        }
                                        $output .= '<option value="' . esc_html($exploding[0]) . '"';
                                        if($exploding[0] == $value)
                                        {
                                            $output .= " selected";
                                        }
                                        $output .= '>' . esc_html($exploding[2]) . '</option>';
                                    }
                                }
                                else
                                {
                                    $output .= '<option disabled value="">' . esc_html__('You need to set up the F-omatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'location_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $locations = array('local' => 'local');
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                if (is_plugin_active('aiomatic-extension-amazon-s3-images/aiomatic-extension-amazon-s3-images.php')) 
                                {
                                    $locations['amazon'] = 'Amazon S3';
                                    $locations['wasabi'] = 'Wasabi';
                                    $locations['cloudflare'] = 'CloudFlare';
                                    $locations['digital'] = 'Digital Ocean';
                                    $locations['generics3'] = 'Generic Amazon S3 Storage';
                                }
                                foreach($locations as $id => $name)
                                {
                                    $output .= '<option value="' . esc_attr($id) . '"';
                                    if(esc_attr($id) == $value)
                                    {
                                        $output .= " selected";
                                    }
                                    $output .= '>' . ucfirst(esc_html($name)) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $values = $card_type['values'];
                                foreach($values as $id => $name)
                                {
                                    $output .= '<option value="' . esc_attr($id) . '"';
                                    if(esc_attr($id) == $value)
                                    {
                                        $output .= " selected";
                                    }
                                    $output .= '>' . esc_html($name) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'file_type_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $locations = array('txt' => 'txt', 'html' => 'html', 'doc' => 'doc', 'pdf' => 'pdf');
                                if(!function_exists('is_plugin_active'))
                                {
                                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                                }
                                foreach($locations as $id => $name)
                                {
                                    $output .= '<option value="' . esc_attr($id) . '"';
                                    if(esc_attr($id) == $value)
                                    {
                                        $output .= " selected";
                                    }
                                    if ($id == 'pdf' && !is_plugin_active('aiomatic-extension-pdf-files/aiomatic-extension-pdf-files.php')) 
                                    {
                                        $output .= " disabled title='This option requires the Aiomatic Extension - PDF File Storage And Parsing to be active'";
                                    }
                                    $output .= '>' . esc_html($name) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'file_selector')
                            {
                                if(empty($_GLOBALS['omni_files']))
                                {
                                    $_GLOBALS['omni_files'] = get_posts([
                                        'post_type' => 'aiomatic_omni_file',
                                        'post_status' => 'publish',
                                        'numberposts' => -1
                                    ]);
                                }
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                if(empty($_GLOBALS['omni_files']))
                                {
                                    $output .= '<option disabled selected>' . esc_html__('No files added, add new files in the \'OmniBlock Files\' tab', 'aiomatic-automatic-ai-content-writer') . '</option>';
                                }
                                else
                                {
                                    $output .= '<option value="random"';
                                    if('random' == $value)
                                    {
                                        $output .= " selected";
                                    }
                                    $output .= '>' . esc_html__('Random', 'aiomatic-automatic-ai-content-writer') . '</option>';
                                    $output .= '<option value="latest"';
                                    if('latest' == $value)
                                    {
                                        $output .= " selected";
                                    }
                                    $output .= '>' . esc_html__('Latest', 'aiomatic-automatic-ai-content-writer') . '</option>';
                                }
                                foreach($_GLOBALS['omni_files'] as $id => $name)
                                {
                                    $output .= '<option value="' . esc_attr($name->ID) . '"';
                                    if(esc_attr($name->ID) == $value)
                                    {
                                        $output .= " selected";
                                    }
                                    $output .= '>' . esc_html($name->post_title) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'pinterest_board_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $boards = get_option('pinterestomatic_public_boards', false);
                                if($boards !== FALSE)
                                {
                                    foreach($boards as $id => $name)
                                    {
                                        $output .= '<option value="' . esc_attr($id) . '"';
                                        if(esc_attr($id) == $value)
                                        {
                                            $output .= " selected";
                                        }
                                        $output .= '>' . esc_html($name) . '</option>';
                                    }
                                }
                                else
                                {
                                    $output .= '<option disabled value="">' . esc_html__('You need to set up the Pinterestomatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'gpb_page_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $boards = get_option('businessomatic_my_business_list', false);
                                if($boards !== FALSE)
                                {
                                    foreach($boards as $id => $name)
                                    {
                                        $output .= '<option value="' . esc_attr($id) . '"';
                                        if(esc_attr($id) == $value)
                                        {
                                            $output .= " selected";
                                        }
                                        $output .= '>' . esc_html($name) . '</option>';
                                    }
                                }
                                else
                                {
                                    $output .= '<option disabled value="">' . esc_html__('You need to set up the Businessomatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'linkedin_page_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $companies = get_option('linkedinomatic_my_companies', array());
                                if(is_array($companies) && count($companies) > 0)
                                {
                                    foreach($companies as $cmp_id => $cmp_name)
                                    {
                                        if($cmp_name == 'Profile Page')
                                        {
                                            $output .= '<option value="' . esc_attr($cmp_id) . '"';
                                            if($cmp_id == $value)
                                            {
                                                $output .= ' selected';
                                            }
                                            $output .= '>' . esc_html($cmp_name) . '</option>';
                                        }
                                        else
                                        {
                                            $output .= '<option value="xxxLinkedinomaticxxx' . esc_attr($cmp_id) . '"';
                                            if('xxxLinkedinomaticxxx' . $cmp_id == $value)
                                            {
                                                $output .= ' selected';
                                            }
                                            $output .= '>' . esc_html($cmp_name) . '</option>';
                                        }
                                    }
                                }
                                else
                                {
                                    $output .= '<option disabled value="">' . esc_html__('You need to set up the Linkedinomatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'language_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                $i = 0;
                                foreach ($language_names as $lang) {
                                    $output .= '<option value="' . esc_html($language_codes[$i]) . '"';
                                    if ($value == $language_codes[$i]) {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($language_names[$i]) . '</option>';
                                    $i++;
                                }
                                if($deepl_auth != '')
                                {
                                    $i = 0;
                                    foreach ($language_names_deepl as $lang) {
                                        $output .= '<option value="' . esc_html($language_codes_deepl[$i]) . '"';
                                        if ($value == $language_codes_deepl[$i]) {
                                            $output .= ' selected';
                                        }
                                        $output .= '>' . esc_html($language_names_deepl[$i]) . '</option>';
                                        $i++;
                                    }
                                }
                                if($bing_auth != '')
                                {
                                    $i = 0;
                                    foreach ($language_names_bing as $lang) {
                                        $output .= '<option value="' . esc_html($language_codes_bing[$i]) . '"';
                                        if ($value == $language_codes_bing[$i]) {
                                            $output .= ' selected';
                                        }
                                        $output .= '>' . esc_html($language_names_bing[$i]) . '</option>';
                                        $i++;
                                    }
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'format_selector')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                                foreach($all_formats as $modelx => $namex)
                                {
                                    $output .= '<option value="' . $modelx .'"';
                                    if($value == $modelx)
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($namex) . '</option>';
                                }
                                $output .= '</select>';
                            }
                            elseif($card_type['type'] == 'url')
                            {
                                $randval = uniqid();
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<div class="main-holder-short" data-id-str="xai' . $randval . '">';
                                $output .= '<input onchange="updateSortableInputAI(\'\', \'_edit\');" type="url" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' ur' . esc_attr($urlrandval) . ' cr_width_full" value="' . esc_html($value) . '" id="xai' . $randval . '" placeholder="' . esc_html($card_type['placeholder']) . '">';
                                if(count($new_shortcodes_arr) > 0)
                                {
                                    $output .= '<div class="shortcode-list" data-id-str="xai' . $randval . '">';
                                }
                                foreach($new_shortcodes_arr as $myshort)
                                {
                                    $my_id = explode('_', $myshort);
                                    $my_id = end($my_id);
                                    $my_id = substr($my_id, 0, -2);
                                    $output .=  '<p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Shortcode created by OmniBlock ID: ', 'aiomatic-automatic-ai-content-writer') . $my_id . '">';
                                    $output .=  $myshort;
                                    $output .=  '</p>';
                                }
                                if(count($new_shortcodes_arr) > 0)
                                {
                                    $output .= '</div>';
                                }
                                $output .= '</div>';
                            }
                            elseif($card_type['type'] == 'scraper_select')
                            {
                                $value = '';
                                if(isset($card_id['parameters'][$name]))
                                {
                                    $value = $card_id['parameters'][$name];
                                }
                                $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" id="sc' . $assistant_helper . '" class="' . esc_attr($name) . '" class="cr_width_full">';
                                $output .= '<option value="0"';
                                if($value == '0')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("WordPress (Default)", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '<option value="1"';
                                if($value == '1')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("PhantomJS (needs to be installed on server)", 'aiomatic-automatic-ai-content-writer') . '</option>';         
                                $output .= '<option value="2"';
                                if($value == '2')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Puppeteer (needs to be installed on server)", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '<option value="3"';
                                if($value == '3')
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Tor (needs to be installed on server)", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                $output .= '<option value="4"';
                                if($value == '4')
                                {
                                    $output .= ' selected';
                                }
                                if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                                {
                                    $output .= ' title="' . esc_html__("This option is disabled. To enable it, add a HeadlessBrowserAPI Key in the plugin's 'Settings' menu.", 'aiomatic-automatic-ai-content-writer') . '" disabled';
                                }
                                $output .= '>' . esc_html__("Puppeteer (HeadlessBrowserAPI)", 'aiomatic-automatic-ai-content-writer');
                                if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                                {
                                    $output .= esc_html__(' - to enable, add a HeadlessBrowserAPI key in the plugin\'s \'Settings\'', 'aiomatic-automatic-ai-content-writer');
                                }
                                $output .= '</option>';
                                $output .= '<option value="5"';
                                if($value == '5')
                                {
                                    $output .= ' selected';
                                }
                                if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                                {
                                    $output .= ' title="' . esc_html__("This option is disabled. To enable it, add a HeadlessBrowserAPI Key in the plugin's 'Settings' menu.", 'aiomatic-automatic-ai-content-writer') . '" disabled';
                                }
                                $output .= '>' . esc_html__("Tor (HeadlessBrowserAPI)", 'aiomatic-automatic-ai-content-writer');
                                if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                                {
                                    $output .= esc_html__(' - to enable, add a HeadlessBrowserAPI key in the plugin\'s \'Settings\'', 'aiomatic-automatic-ai-content-writer');
                                }
                                $output .= '</option>';
                                $output .= '<option value="6"';
                                if($value == '6')
                                {
                                    $output .= ' selected';
                                }
                                if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                                {
                                    $output .= ' title="' . esc_html__("This option is disabled. To enable it, add a HeadlessBrowserAPI Key in the plugin's 'Settings' menu.", 'aiomatic-automatic-ai-content-writer') . '" disabled';
                                }
                                $output .= '>';
                                $output .= esc_html__("PhantomJS (HeadlessBrowserAPI)", 'aiomatic-automatic-ai-content-writer');
                                if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                                {
                                    $output .= esc_html__(' - to enable, add a HeadlessBrowserAPI key in the plugin\'s \'Settings\'', 'aiomatic-automatic-ai-content-writer');
                                }
                                $output .= '</option>';
                                $output .= '</select>';
                            }
                        }
                        $critical = false;
                        if(isset($card_id['parameters']['critical']) && $card_id['parameters']['critical'] == '1')
                        {
                            $critical = true;
                        }
                        $output .= '<h4>' . esc_html__("Critical", 'aiomatic-automatic-ai-content-writer') . ':&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__("Set if this OmniBlock is critical or not. When a Critical OmniBlock fails to generate its content correctly and it fails, it will cause the entire OmniBlock running sequence to stop. Non-critical OmniBlocks when they fail, the execution of blocks will continue, the result of the failed OmniBlock will be blank.", 'aiomatic-automatic-ai-content-writer') . '</div></div>';
                        $output .= '&nbsp;<input type="checkbox" onchange="updateSortableInputAI(\'\', \'_edit\');" class="critical-blocks" data-clone-index="xc' . uniqid() . '" id="critical-' . esc_attr($last_id) . uniqid() . '"';
                        if($critical == true)
                        {
                            $output .= ' checked';
                        }
                        $output .= '>';
                        $output .= '</h4>';
                        $disabled = false;
                        if(isset($card_id['parameters']['disabled']) && $card_id['parameters']['disabled'] == '1')
                        {
                            $disabled = true;
                        }
                        $output .= '<h4>' . esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer') . ':&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__("Set if this OmniBlock is disabled or not. The disabled OmniBlocks will be skipped from processing.", 'aiomatic-automatic-ai-content-writer') . '</div></div>';
                        $output .= '&nbsp;<input type="checkbox" onchange="updateSortableInputAI(\'\', \'_edit\');" class="disabled-blocks" data-clone-index="xc' . uniqid() . '" id="disabled-' . esc_attr($last_id) . uniqid() . '"';
                        if($disabled == true)
                        {
                            $output .= ' checked';
                        }
                        $output .= '>';
                        $output .= '</h4>';
                        foreach($card_type_found['shortcodes'] as $shtc)
                        {
                            $new_shortcodes_arr[] = '%%' . $shtc . $card_id['identifier'] . '%%';
                        }
                        $output .= '</div>
                        <button class="move-up-btn_edit" title="Move Up">
        <!-- SVG for move up -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0L12 8.292a.5.5 0 0 1-.708.708L8 5.707V12.5a.5.5 0 0 1-1 0V5.707L4.707 9a.5.5 0 1 1-.708-.708l3.647-3.646z"/>
    </svg>
    </button>
    <button class="move-down-btn_edit" title="Move Down">
        <!-- SVG for move down -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M7.646 11.354a.5.5 0 0 1-.708 0L3.293 7.707a.5.5 0 1 1 .708-.708L7 10.293V3.5a.5.5 0 0 1 1 0v6.793l2.999-3.294a.5.5 0 0 1 .708.708l-4 4.147z"/>
    </svg>
    </button>
                        <button class="delete-btn" title="' . esc_html__('Delete', 'aiomatic-automatic-ai-content-writer') . '">X</button><div class="ai_common_holder"><div class="step-number">' . esc_html__("Step", 'aiomatic-automatic-ai-content-writer') . ' ' . esc_html($exec) . '</div><div class="aiomatic-run-now"></div><div class="id-shower">' . esc_html__("ID:", 'aiomatic-automatic-ai-content-writer') . ' ' . esc_html($card_id['identifier']) . '</div></div></li>';
                        $exec++;
                    }
                }
            }
        }
    }
    $output .= '</ul>
</td>
</tr>
<tr>
<td colspan="2"><div class="aiseparator aistop"><b>' . esc_html__("OmniBlock Queue Stops Here", 'aiomatic-automatic-ai-content-writer') . '</b></div><h2>' . esc_html__('Add A New OmniBlock To The Above Queue (Drag And Drop):', 'aiomatic-automatic-ai-content-writer') . '</h2>';
    $output .= '<ul id="aiomatic_new_card_types_edit" name="aiomatic_new_card_types_edit">';
    if(empty($default_block_types))
    {
        $output .= esc_html__('No AI OmniBlock Types Defined!', 'aiomatic-automatic-ai-content-writer');
    }
    else
    {
        $first = true;
        $save_id = $largest_id;
        aiomatic_increment($save_id);
        foreach ($default_block_types as $card_id) 
        {
            if(!empty($card_id['type']))
            {
                $assistant_helper = uniqid();
                $urlrandval = uniqid();
                $local_shortcodes = array();
                foreach($card_id['shortcodes'] as $shtc)
                {
                    $local_shortcodes[] = '%%' . $shtc . $save_id . '%%';
                }
                $output .= '<li data-id-str="' . esc_html($cont) . '" class="omniblock-card new-card';
                if($first != true)
                {
                    $output .= ' cr_none';
                }
                $plugin_required = array();
                if(!empty($card_id['required_plugin']))
                {
                    foreach($card_id['required_plugin'] as $pslug => $pname)
                    {
                        if(!function_exists('is_plugin_active'))
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        }
                        if (!is_plugin_active($pslug)) 
                        {
                            $plugin_required[] = 'You need enable the "' . $pname[0] . '" plugin for this OmniBlock type to work: ' . $pname[1];
                        }
                    }
                }
                $output .= '" id="' . sanitize_title($card_id['name']) . esc_html($cont) . '_edit"><input data-clone-index="xc' . uniqid() . '" class="cr_center aiomatic-bold aiomatic-indigo omniblock-title" card-type="' . esc_html($card_id['id']) . '" type="text" placeholder="' . esc_html__('OmniBlock Title', 'aiomatic-automatic-ai-content-writer') . '"  title="' . esc_html__('OmniBlock Title', 'aiomatic-automatic-ai-content-writer') . '" value="' . esc_attr($card_id['name']) . '">
                <input type="hidden" class="omniblock-id" card-type="' . esc_html($card_id['id']) . '" value="' . esc_attr($save_id) . '">
                <input type="hidden" class="omniblock-shortcodes" card-type="' . esc_html($card_id['id']) . '" value="' . esc_attr(implode(',', $local_shortcodes)) . '">';
                if($card_id['id'] == 'text_spinner')
                {
                    if (!isset($aiomatic_Main_Settings['spin_text']) || $aiomatic_Main_Settings['spin_text'] === 'disabled')
                    {
                        $plugin_required[] = 'Spinning disabled from \'Settings\' -> \'Bulk Posts\' tab -> \'Spin Text Using Word Synonyms\' settings field, this OmniBlock will not function';
                    }
                    if (isset($aiomatic_Main_Settings['spin_what']) && $aiomatic_Main_Settings['spin_what'] === 'bulk') 
                    {
                        $plugin_required[] = 'Spinning disabled for OmniBlocks, from plugin\'s \'Settings\' menu -> \'Bulk Posts\' tab -> \'Enable Spinner For\' settings field, this OmniBlock will not function';
                    }
                }
                elseif($card_id['id'] == 'embeddings')
                {
                    if ((!isset($aiomatic_Main_Settings['pinecone_app_id']) || trim($aiomatic_Main_Settings['pinecone_app_id']) == '') && (!isset($aiomatic_Main_Settings['qdrant_app_id']) || trim($aiomatic_Main_Settings['qdrant_app_id']) == ''))
                    {
                        $plugin_required[] = 'You must add an OpenAI/AiomaticAPI API Key into the plugin\'s \'Settings\' menu before you can use this feature!';
                    }
                    if ((!isset($aiomatic_Main_Settings['pinecone_index']) || trim($aiomatic_Main_Settings['pinecone_index']) == '') && (!isset($aiomatic_Main_Settings['qdrant_index']) || trim($aiomatic_Main_Settings['qdrant_index']) == ''))
                    {
                        $plugin_required[] = 'You must add a Pinecone API or a Qdrant API key in the plugin\'s \'Settings\' menu (API Keys tab), before you can use this feature!';
                    }
                }
                if(!empty($plugin_required))
                {
                    $output .= '<p class="requirement cr_red"><ul class="requirement cr_red">';
                    foreach($plugin_required as $pr)
                    {
                        $output .= '<li>' . $pr . '</li>';
                    }
                    $output .= '</ul></p>';
                }
                $output .= '<div class="card-name';
                if($card_id['type'] == 'save')
                {
                    $output .= ' aisave-content';
                }
                else
                {
                    $output .= ' aicreate-content';
                }
                if (isset($aiomatic_Main_Settings['omni_webhook']) && trim($aiomatic_Main_Settings['omni_webhook']) == 'on')
                {
                    $card_id['description'] = str_replace('%%webhook_url%%', esc_html__('add this OmniBlock and save settings to get the URL' , 'aiomatic-automatic-ai-content-writer'), $card_id['description']);
                }
                else
                {
                    $card_id['description'] = str_replace('%%webhook_url%%', '<span class="cr_red">' . esc_html__('OmniBlock Webhook functionality not enabled in \'Settings\' menu of the plugin!' , 'aiomatic-automatic-ai-content-writer') . '</span>', $card_id['description']);
                }
                $card_id['description'] = str_replace('%%filter_name%%', esc_html__('add this OmniBlock and save settings to get the filter name' , 'aiomatic-automatic-ai-content-writer'), $card_id['description']);
                $output .= '">' . esc_html__('OmniBlock Type:', 'aiomatic-automatic-ai-content-writer') . '&nbsp;' . esc_attr($card_id['name']) . '</div><p class="card-desc">' . $card_id['description'] . '</p><div class="ai-collapsible-holder"><button class="aicollapsible" title="' . esc_html__('Show/Hide Parameters', 'aiomatic-automatic-ai-content-writer') . '"><img class="controls-icon" src="' . plugin_dir_url( __FILE__ ) . 'images/controls.png' . '"></button></div><div class="aicollapsible-parameters">';
                $output .= '<h3>' . esc_html__('OmniBlock Parameters', 'aiomatic-automatic-ai-content-writer') . '&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__('Set the parameters which will be used in this OmniBlock.', 'aiomatic-automatic-ai-content-writer') . '</div></div></h3><hr/>';
                $first = false;
                foreach($card_id['parameters'] as $name => $card_type)
                {
                    $output .= '<h4>' . esc_html($card_type['title']) . ':&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html($card_type['description']) . '</div></div></h4>';   
                    if($card_type['type'] == 'text')
                    {
                        $randval = uniqid();
                        $output .= '<div class="main-holder-short" data-id-str="xai' . $randval . '">';
                        $output .= '<input type="text" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full" value="' . esc_attr($card_type['default_value']) . '" placeholder="' . esc_html($card_type['placeholder']) . '" id="xai' . $randval . '">';
                        $output .= '<div class="shortcode-list" data-id-str="xai' . $randval . '"><p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Main keyword shortcode', 'aiomatic-automatic-ai-content-writer') . '">%%keyword%%</p></div>';
                        $output .= '</div>';
                        
                    }
                    elseif($card_type['type'] == 'textarea')
                    {
                        $randval = uniqid();
                        $additional = '';
                        if($name == 'prompt' && $card_id['id'] == 'ai_text_foreach')
                        {
                            $additional .= '<p class="aishortcodes" data-suff="_edit" data-index="" data-id-str="xai' . $randval . '" title="' . esc_html__('Main keyword shortcode', 'aiomatic-automatic-ai-content-writer') . '">%%current_input_line_counter%%</p>';
                            $additional .= '<p class="aishortcodes" data-suff="_edit" data-index="" data-id-str="xai' . $randval . '" title="' . esc_html__('Main keyword shortcode', 'aiomatic-automatic-ai-content-writer') . '">%%current_input_line%%</p>';
                            $additional .= '<p class="aishortcodes" data-suff="_edit" data-index="" data-id-str="xai' . $randval . '" title="' . esc_html__('Main keyword shortcode', 'aiomatic-automatic-ai-content-writer') . '">%%all_input_lines%%</p>';
                        }
                        $output .= '<div class="main-holder-short" data-id-str="xai' . $randval . '">';
                        $output .= '<textarea data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full" placeholder="' . esc_html($card_type['placeholder']) . '" id="xai' . $randval . '">' . esc_textarea($card_type['default_value']) . '</textarea>';
                        $output .= '<div class="shortcode-list" data-id-str="xai' . $randval . '"><p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Main keyword shortcode', 'aiomatic-automatic-ai-content-writer') . '">%%keyword%%</p>' . $additional . '</div>';
                        $output .= '</div>';
                    }
                    elseif($card_type['type'] == 'model_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" id="xa' . $assistant_helper . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_models as $modelx)
                        {
                            $output .= '<option value="' . $modelx .'"';
                            if($modelx == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'model_select_function')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" id="xa' . $assistant_helper . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_models_function as $modelx)
                        {
                            $output .= '<option value="' . $modelx .'"';
                            if($modelx == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'assistant_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" id="sel_xa' . $assistant_helper . '" onchange="assistantChanged(\'xa' . $assistant_helper . '\');" class="' . esc_attr($name) . ' cr_width_full">';
                        if($all_assistants === false)
                        {
                            $output .= '<option val="" selected disabled>' . esc_html__("Only OpenAI API is supported for Assistants API", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        }
                        else
                        {
                            if(count($all_assistants) == 0)
                            {
                                $output .= '<option val="" selected disabled>' . esc_html__("No Assistans added, go to the plugin's 'AI Assistans' menu to add new assistants!", 'aiomatic-automatic-ai-content-writer') . '</option>';
                            }
                            else
                            {
                                $output .= '<option value=""';
                                if('' == $card_type['default_value'])
                                {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
                                foreach($all_assistants as $myassistant)
                                {
                                    $output .= '<option value="' . $myassistant->ID .'"';
                                    if($myassistant->ID == $card_type['default_value'])
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($myassistant->post_title);
                                    $output .= '</option>';
                                }
                            }
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'dalle_image_size_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_dalle_sizes as $sizeid => $sizex)
                        {
                            $output .= '<option value="' . esc_attr($sizeid) .'"';
                            if($sizeid == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($sizex) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'stable_image_size_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_stable_sizes as $sizeid => $sizex)
                        {
                            $output .= '<option value="' . esc_attr($sizeid) .'"';
                            if($sizeid == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($sizex) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'midjourney_image_size_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_midjourney_sizes as $sizeid => $sizex)
                        {
                            $output .= '<option value="' . esc_attr($sizeid) .'"';
                            if($sizeid == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($sizex) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'replicate_image_size_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_replicate_sizes as $sizeid => $sizex)
                        {
                            $output .= '<option value="' . esc_attr($sizeid) .'"';
                            if($sizeid == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($sizex) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'ideogram_image_size_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_ideogram_sizes as $sizeid => $sizex)
                        {
                            $output .= '<option value="' . esc_attr($sizeid) .'"';
                            if($sizeid == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($sizex) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'stable_video_size_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_stable_video_sizes as $sizeid => $sizex)
                        {
                            $output .= '<option value="' . esc_attr($sizeid) .'"';
                            if($sizeid == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($sizex) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'scraper_type')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="scraper_selector ' . esc_attr($name) . ' cr_width_full" data-id-str="' . esc_html($cont) . '" data-source-field-id="ur' . $urlrandval . '" data-target-field-id="' . $assistant_helper . '">';
                        foreach($all_scraper_types as $index => $modelx)
                        {
                            $output .= '<option value="' . esc_attr($index) .'"';
                            if($index == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'scraper_string')
                    {
                        $output .= '<input type="text" data-clone-index="xc' . uniqid() . '" id="st' . $assistant_helper . '" class="' . esc_attr($name) . ' cr_width_full" value="' . esc_attr($card_type['default_value']) . '" placeholder="' . esc_html($card_type['placeholder']) . '">';
                    }
                    elseif($card_type['type'] == 'number')
                    {
                        $output .= '<input type="number" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full" value="' . esc_attr($card_type['default_value']) . '" placeholder="' . esc_html($card_type['placeholder']) . '">';
                    }
                    elseif($card_type['type'] == 'checkbox')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $output .= '<option value="0"';
                        if('0' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("No", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '<option value="1"';
                        if('1' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Yes", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'checkbox_overwrite')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $output .= '<option value="0"';
                        if('0' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("No, but keep duplicates", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '<option value="2"';
                        if('2' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("No, but discard duplicates", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '<option value="1"';
                        if('1' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Yes", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'dalle_image_model_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_dalle_models as $modelx)
                        {
                            $output .= '<option value="' . $modelx .'"';
                            if($modelx == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'stable_image_model_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_stable_models as $modelx)
                        {
                            $output .= '<option value="' . $modelx .'"';
                            if($modelx == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'google_image_model_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach(AIOMATIC_GOOGLE_IMAGE_MODELS as $modelx)
                        {
                            $output .= '<option value="' . $modelx .'"';
                            if($modelx == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'status_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $output .= '<option value="publish"';
                        if("publish" == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Published", 'aiomatic-automatic-ai-content-writer') . '</option>
                        <option value="pending"';
                        if("pending" == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Pending -> Moderate", 'aiomatic-automatic-ai-content-writer') . '</option>
                        <option value="draft"';
                        if("draft" == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Draft -> Moderate", 'aiomatic-automatic-ai-content-writer') . '</option>
                        <option value="private"';
                        if("private" == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Private", 'aiomatic-automatic-ai-content-writer') . '</option>
                        <option value="trash"';
                        if("trash" == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Trash", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'type_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach(get_post_types( '', 'names' ) as $modelx)
                        {
                            if(strstr($modelx, 'aiomatic_'))
                            {
                               continue;
                            }
                            $output .= '<option value="' . $modelx .'"';
                            if($modelx == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'format_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($all_formats as $modelx => $namex)
                        {
                            if(strstr($modelx, 'aiomatic_'))
                            {
                               continue;
                            }
                            $output .= '<option value="' . $modelx .'"';
                            if($modelx == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($namex) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'amazon_country_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach(aiomatic_get_amazon_codes() as $key => $modelx)
                        {
                            $output .= '<option value="' . $key .'"';
                            if($key == $card_type['default_value'])
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'amazon_sort_select')
                    {
                        $value = '';
                        if(isset($card_id['parameters'][$name]))
                        {
                            $value = $card_id['parameters'][$name];
                        }
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach(aiomatic_get_amazon_sorts() as $key => $modelx)
                        {
                            $output .= '<option value="' . esc_attr($key) .'"';
                            if($value == $key)
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'yt_community_selector')
                    {
                        $community_types = array('text' => 'Text', 'image' => 'Image');
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($community_types as $key => $modelx)
                        {
                            $output .= '<option value="' . esc_attr($key) .'"';
                            if($card_type['default_value'] == $key)
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'reddit_selector')
                    {
                        $reddit_types = array('auto' => 'Auto', 'link' => 'Link', 'self' => 'Text', 'image' => 'Image', 'video' => 'Video');
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($reddit_types as $key => $modelx)
                        {
                            $output .= '<option value="' . esc_attr($key) .'"';
                            if($card_type['default_value'] == $key)
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'method_selector')
                    {
                        $reddit_types = array('POST' => 'POST', 'GET' => 'GET', 'PUT' => 'PUT', 'DELETE' => 'DELETE');
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($reddit_types as $key => $modelx)
                        {
                            $output .= '<option value="' . esc_attr($key) .'"';
                            if($card_type['default_value'] == $key)
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'content_type_selector')
                    {
                        $reddit_types = array('JSON' => 'JSON', 'form' => 'Form Data');
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        foreach($reddit_types as $key => $modelx)
                        {
                            $output .= '<option value="' . esc_attr($key) .'"';
                            if($card_type['default_value'] == $key)
                            {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($modelx) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'facebook_page_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $store = get_option('fbomatic_page_ids', false);
                        if($store !== FALSE)
                        {
                            $store = explode(',', $store);
                            $fcount = count($store);
                            for($i = 0; $i < $fcount; $i++)
                            {
                                $exploding = explode('-', $store[$i]);
                                if(!isset($exploding[2]))
                                {
                                    continue;
                                }
                                $output .= '<option value="' . esc_html($exploding[0]) . '"';
                                if($exploding[0] == $card_type['default_value'])
                                {
                                    $output .= " selected";
                                }
                                $output .= '>' . esc_html($exploding[2]) . '</option>';
                            }
                        }
                        else
                        {
                            $output .= '<option disabled value="">' . esc_html__('You need to set up the F-omatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'location_selector')
                    {
                        $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $locations = array('local' => 'local');
                        if(!function_exists('is_plugin_active'))
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        }
                        if (is_plugin_active('aiomatic-extension-amazon-s3-images/aiomatic-extension-amazon-s3-images.php')) 
                        {
                            $locations['amazon'] = 'Amazon S3';
                            $locations['wasabi'] = 'Wasabi';
                            $locations['cloudflare'] = 'CloudFlare';
                            $locations['digital'] = 'Digital Ocean';
                            $locations['generics3'] = 'Generic Amazon S3 Storage';
                        }
                        foreach($locations as $id => $name)
                        {
                            $output .= '<option value="' . esc_attr($id) . '"';
                            if(esc_attr($id) == $card_type['default_value'])
                            {
                                $output .= " selected";
                            }
                            $output .= '>' . ucfirst(esc_html($name)) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'select')
                    {
                        $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $values = $card_type['values'];
                        foreach($values as $id => $name)
                        {
                            $output .= '<option value="' . esc_attr($id) . '"';
                            if(esc_attr($id) == $card_type['default_value'])
                            {
                                $output .= " selected";
                            }
                            $output .= '>' . esc_html($name) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'file_type_selector')
                    {
                        $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $locations = array('txt' => 'txt', 'html' => 'html', 'doc' => 'doc', 'pdf' => 'pdf');
                        if(!function_exists('is_plugin_active'))
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        }
                        foreach($locations as $id => $name)
                        {
                            $output .= '<option value="' . esc_attr($id) . '"';
                            if(esc_attr($id) == $card_type['default_value'])
                            {
                                $output .= " selected";
                            }
                            if ($id == 'pdf' && !is_plugin_active('aiomatic-extension-pdf-files/aiomatic-extension-pdf-files.php')) 
                            {
                                $output .= " disabled title='This option requires the Aiomatic Extension - PDF File Storage And Parsing to be active'";
                            }
                            $output .= '>' . esc_html($name) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'file_selector')
                    {
                        if(empty($_GLOBALS['omni_files']))
                        {
                            $_GLOBALS['omni_files'] = get_posts([
                                'post_type' => 'aiomatic_omni_file',
                                'post_status' => 'publish',
                                'numberposts' => -1
                            ]);
                        }
                        $output .= '<select onchange="updateSortableInputAI(\'\', \'_edit\');" autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        if(empty($_GLOBALS['omni_files']))
                        {
                            $output .= '<option disabled selected>' . esc_html__('No files added, add new files in the \'OmniBlock Files\' tab', 'aiomatic-automatic-ai-content-writer') . '</option>';
                        }
                        else
                        {
                            $output .= '<option value="random"';
                            if('random' == $card_type['default_value'])
                            {
                                $output .= " selected";
                            }
                            $output .= '>' . esc_html__('Random', 'aiomatic-automatic-ai-content-writer') . '</option>';
                            $output .= '<option value="latest"';
                            if('latest' == $card_type['default_value'])
                            {
                                $output .= " selected";
                            }
                            $output .= '>' . esc_html__('Latest', 'aiomatic-automatic-ai-content-writer') . '</option>';
                        }
                        foreach($_GLOBALS['omni_files'] as $id => $name)
                        {
                            $output .= '<option value="' . esc_attr($name->ID) . '"';
                            if(esc_attr($name->ID) == $card_type['default_value'])
                            {
                                $output .= " selected";
                            }
                            $output .= '>' . esc_html($name->post_title) . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'pinterest_board_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $boards = get_option('pinterestomatic_public_boards', array());
                        if(is_array($boards) && count($boards) > 0)
                        {
                            foreach($boards as $id => $name)
                            {
                                $output .= '<option value="' . esc_attr($id) . '"';
                                if(esc_attr($id) == $card_type['default_value'])
                                {
                                    $output .= " selected";
                                }
                                $output .= '>' . esc_html($name) . '</option>';
                            }
                        }
                        else
                        {
                            $output .= '<option disabled value="">' . esc_html__('You need to set up the Pinterestomatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'gpb_page_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $boards = get_option('businessomatic_my_business_list', array());
                        if(is_array($boards) && count($boards) > 0)
                        {
                            foreach($boards as $id => $name)
                            {
                                $output .= '<option value="' . esc_attr($id) . '"';
                                if(esc_attr($id) == $card_type['default_value'])
                                {
                                    $output .= " selected";
                                }
                                $output .= '>' . esc_html($name) . '</option>';
                            }
                        }
                        else
                        {
                            $output .= '<option disabled value="">' . esc_html__('You need to set up the Businessomatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'linkedin_page_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $companies = get_option('linkedinomatic_my_companies', array());
                        if(is_array($companies) && count($companies) > 0)
                        {
                            foreach($companies as $cmp_id => $cmp_name)
                            {
                                if($cmp_name == 'Profile Page')
                                {
                                    $output .= '<option value="' . esc_attr($cmp_id) . '"';
                                    if($cmp_id == $card_type['default_value'])
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($cmp_name) . '</option>';
                                }
                                else
                                {
                                    $output .= '<option value="xxxLinkedinomaticxxx' . esc_attr($cmp_id) . '"';
                                    if('xxxLinkedinomaticxxx' . $cmp_id == $card_type['default_value'])
                                    {
                                        $output .= ' selected';
                                    }
                                    $output .= '>' . esc_html($cmp_name) . '</option>';
                                }
                            }
                        }
                        else
                        {
                            $output .= '<option disabled value="">' . esc_html__('You need to set up the Linkedinomatic plugin before using this feature!', 'aiomatic-automatic-ai-content-writer') . '</option>';
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'language_selector')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $i = 0;
                        foreach ($language_names as $lang) {
                            $output .= '<option value="' . esc_html($language_codes[$i]) . '"';
                            if ($card_type['default_value'] == $language_codes[$i]) {
                                $output .= ' selected';
                            }
                            $output .= '>' . esc_html($language_names[$i]) . '</option>';
                            $i++;
                        }
                        if($deepl_auth != '')
                        {
                            $i = 0;
                            foreach ($language_names_deepl as $lang) {
                                $output .= '<option value="' . esc_html($language_codes_deepl[$i]) . '"';
                                if ($card_type['default_value'] == $language_codes_deepl[$i]) {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html($language_names_deepl[$i]) . '</option>';
                                $i++;
                            }
                        }
                        if($bing_auth != '')
                        {
                            $i = 0;
                            foreach ($language_names_bing as $lang) {
                                $output .= '<option value="' . esc_html($language_codes_bing[$i]) . '"';
                                if ($card_type['default_value'] == $language_codes_bing[$i]) {
                                    $output .= ' selected';
                                }
                                $output .= '>' . esc_html($language_names_bing[$i]) . '</option>';
                                $i++;
                            }
                        }
                        $output .= '</select>';
                    }
                    elseif($card_type['type'] == 'url')
                    {
                        $randval = uniqid();
                        $output .= '<div class="main-holder-short" data-id-str="xai' . $randval . '">';
                        $output .= '<input type="url" data-clone-index="xc' . uniqid() . '" class="' . esc_attr($name) . ' ur' . esc_attr($urlrandval) . ' cr_width_full" value="' . esc_attr($card_type['default_value']) . '" placeholder="' . esc_html($card_type['placeholder']) . '" id="xai' . $randval . '">';
                        $output .= '<div class="shortcode-list" data-id-str="xai' . $randval . '"><p class="aishortcodes" data-suff="_edit" data-index="' . esc_html($cont) . '" data-id-str="xai' . $randval . '" title="' . esc_html__('Main keyword shortcode', 'aiomatic-automatic-ai-content-writer') . '">%%keyword%%</p></div>';
                        $output .= '</div>';
                    }
                    elseif($card_type['type'] == 'scraper_select')
                    {
                        $output .= '<select autocomplete="off" data-clone-index="xc' . uniqid() . '" id="sc' . $assistant_helper . '" class="' . esc_attr($name) . ' cr_width_full">';
                        $output .= '<option value="0"';
                        if('0' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("WordPress (Default)", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '<option value="1"';
                        if('1' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("PhantomJS (needs to be installed on server)", 'aiomatic-automatic-ai-content-writer') . '</option>';         
                        $output .= '<option value="2"';
                        if('2' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Puppeteer (needs to be installed on server)", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '<option value="3"';
                        if('3' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        $output .= '>' . esc_html__("Tor (needs to be installed on server)", 'aiomatic-automatic-ai-content-writer') . '</option>';
                        $output .= '<option value="4"';
                        if('4' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                        {
                            $output .= ' title="' . esc_html__("This option is disabled. To enable it, add a HeadlessBrowserAPI Key in the plugin's 'Settings' menu.", 'aiomatic-automatic-ai-content-writer') . '" disabled';
                        }
                        $output .= '>' . esc_html__("Puppeteer (HeadlessBrowserAPI)", 'aiomatic-automatic-ai-content-writer');
                        if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                        {
                            $output .= esc_html__(' - to enable, add a HeadlessBrowserAPI key in the plugin\'s \'Settings\'', 'aiomatic-automatic-ai-content-writer');
                        }
                        $output .= '</option>';
                        $output .= '<option value="5"';
                        if('5' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                        {
                            $output .= ' title="' . esc_html__("This option is disabled. To enable it, add a HeadlessBrowserAPI Key in the plugin's 'Settings' menu.", 'aiomatic-automatic-ai-content-writer') . '" disabled';
                        }
                        $output .= '>' . esc_html__("Tor (HeadlessBrowserAPI)", 'aiomatic-automatic-ai-content-writer');
                        if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                        {
                            $output .= esc_html__(' - to enable, add a HeadlessBrowserAPI key in the plugin\'s \'Settings\'', 'aiomatic-automatic-ai-content-writer');
                        }
                        $output .= '</option>';
                        $output .= '<option value="6"';
                        if('6' == $card_type['default_value'])
                        {
                            $output .= ' selected';
                        }
                        if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                        {
                            $output .= ' title="' . esc_html__("This option is disabled. To enable it, add a HeadlessBrowserAPI Key in the plugin's 'Settings' menu.", 'aiomatic-automatic-ai-content-writer') . '" disabled';
                        }
                        $output .= '>';
                        $output .= esc_html__("PhantomJS (HeadlessBrowserAPI)", 'aiomatic-automatic-ai-content-writer');
                        if (!isset($aiomatic_Main_Settings['headlessbrowserapi_key']) || trim($aiomatic_Main_Settings['headlessbrowserapi_key']) == '')
                        {
                            $output .= esc_html__(' - to enable, add a HeadlessBrowserAPI key in the plugin\'s \'Settings\'', 'aiomatic-automatic-ai-content-writer');
                        }
                        $output .= '</option>';
                        $output .= '</select>';
                    }
                }
                $critical = false;
                if(isset($card_id['parameters']['critical']) && $card_id['parameters']['critical'] == '1')
                {
                    $critical = true;
                }
                $output .= '<h4>' . esc_html__("Critical", 'aiomatic-automatic-ai-content-writer') . ':&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__("Set if this OmniBlock is critical or not. When a Critical OmniBlock fails to generate its content correctly and it fails, it will cause the entire OmniBlock running sequence to stop. Non-critical OmniBlocks when they fail, the execution of blocks will continue, the result of the failed OmniBlock will be blank.", 'aiomatic-automatic-ai-content-writer') . '</div></div>';
                $output .= '&nbsp;<input type="checkbox" class="critical-blocks" data-clone-index="xc' . uniqid() . '" id="critical-' . esc_attr($save_id) . uniqid() . '"';
                if($critical == true)
                {
                    $output .= ' checked';
                }
                $output .= '>';
                $output .= '</h4>';
                $disabled = false;
                if(isset($card_id['parameters']['disabled']) && $card_id['parameters']['disabled'] == '1')
                {
                    $disabled = true;
                }
                $output .= '<h4>' . esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer') . ':&nbsp;<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle"><div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__("Set if this OmniBlock is disabled or not. The disabled OmniBlocks will be skipped from processing.", 'aiomatic-automatic-ai-content-writer') . '</div></div>';
                $output .= '&nbsp;<input type="checkbox" class="disabled-blocks" data-clone-index="xc' . uniqid() . '" id="disabled-' . esc_attr($save_id) . uniqid() . '"';
                if($disabled == true)
                {
                    $output .= ' checked';
                }
                $output .= '>';
                $output .= '</h4>';
                $output .= '</div>
                <button disabled class="move-up-btn_edit" title="Move Up">
                <!-- SVG for move up -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0L12 8.292a.5.5 0 0 1-.708.708L8 5.707V12.5a.5.5 0 0 1-1 0V5.707L4.707 9a.5.5 0 1 1-.708-.708l3.647-3.646z"/>
            </svg>
            </button>
            <button disabled class="move-down-btn_edit" title="Move Down">
                <!-- SVG for move down -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M7.646 11.354a.5.5 0 0 1-.708 0L3.293 7.707a.5.5 0 1 1 .708-.708L7 10.293V3.5a.5.5 0 0 1 1 0v6.793l2.999-3.294a.5.5 0 0 1 .708.708l-4 4.147z"/>
            </svg>
            </button>
                <button disabled class="delete-btn" title="' . esc_html__('Delete', 'aiomatic-automatic-ai-content-writer') . '">X</button><div class="ai_common_holder"><div class="step-number"></div><div class="aiomatic-run-now"></div><div class="id-shower"></div></div></li></li>';
            }
        }
    }
    $output .= '</ul>
    </td>
    </tr>
    <tr>
    <td class="ai-flex">
          <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
             <div class="bws_hidden_help_text cr_min_260px disable_drag">' . esc_html__("Select what type of OmniBlock you want to add.", 'aiomatic-automatic-ai-content-writer') . '</div>
          </div>
          <b>' . esc_html__("OmniBlock Type To Add (Drag And Drop):", 'aiomatic-automatic-ai-content-writer') . '</b>&nbsp;<div class="ai-right-flex"><button id="add-new-btn_edit" class="button page-title-action" title="' . esc_html__('Add OmniBlock', 'aiomatic-automatic-ai-content-writer') . '">' . esc_html__('Add OmniBlock', 'aiomatic-automatic-ai-content-writer') . '</button></div>
    </td>
    <td>
    <select title="' . esc_html__('Change the OmniBlock Type which is displayed, which will be able to be added to the OmniBlock Queue.', 'aiomatic-automatic-ai-content-writer') . '" class="cr_width_full" id="omni_select_block_type_edit" onchange="aiBlockTypeChangeHandler_edit(\'' . esc_html($cont) . '\');">
        <option value="" disabled selected>' . esc_html__("Select a block type to add", 'aiomatic-automatic-ai-content-writer') . '</option>';
    $last_btype = '';
    foreach ($default_block_types as $card_id) 
    {
        if($card_id['category'] !== $last_btype)
        {
            $output .= '<option disabled value="">' . esc_html($card_id['category']) . '</option>';
            $last_btype = $card_id['category'];
        }
        $output .= '<option value="' . sanitize_title($card_id['name']) . '">' . esc_html($card_id['name']) . '</option>';
    }
    $output .= '</select>
    </td>
    </tr>';
    return $output;
}
?>