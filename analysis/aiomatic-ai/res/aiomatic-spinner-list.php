<?php
function aiomatic_spinner_panel()
{
   $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
   if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
   {
      ?>
<h1><?php echo esc_html__("You must add an OpenAI/AiomaticAPI API Key into the plugin's 'Settings' menu before you can use this feature!", 'aiomatic-automatic-ai-content-writer');?></h1>
<?php
return;
   }
   $all_models = aiomatic_get_all_models(true);
   $all_assistants = aiomatic_get_all_assistants(true);
   $all_edit_models = array_merge($all_models, AIOMATIC_EDIT_MODELS);
   $all_speech_models = AIOMATIC_OPENAI_SPEECH_MODELS;
?>
<div class="wp-header-end"></div>
<?php
$temp_list = array(); 
$paged = 1; 
$posts_per_page = 100; 

do {
    $args = array(
        'post_type'      => 'aiomatic_editor_temp',
        'posts_per_page' => $posts_per_page,     
        'paged'          => $paged,            
    );

    $the_query = new WP_Query($args);

    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $temp_list[get_the_ID()] = get_the_title(); 
        }
    }

    $paged++;
    wp_reset_postdata(); 
} while ($the_query->have_posts());
$max_execution = ini_get('max_execution_time');
if($max_execution != 0 && $max_execution < 1000)
{
    ?>
    <div class="notice notice-error">
        <p class="cr_red">
            <?php echo sprintf( wp_kses( __( "Warning! Your PHP INI max_execution_time is less than 1000 seconds (%s). This means that the plugin's execution will be forcefully stopped by your server after this amount of seconds. Please increase it to ensure that the plugin functions properly. Please check details on server settings, <a href='%s' target='_blank'>here</a>.", 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_html($max_execution), esc_url_raw( get_admin_url() . 'admin.php?page=aiomatic_logs#tab-2' ) );?>
        </p>
    </div>
    <?php
}
?>
<div class="wrap gs_popuptype_holder seo_pops">
    <h2 class="cr_center"><?php echo esc_html__("AI Content Editor", 'aiomatic-automatic-ai-content-writer');?></h2>
    <nav class="nav-tab-wrapper">
        <a href="#tab-0" class="nav-tab"><?php echo esc_html__("Tutorial", 'aiomatic-automatic-ai-content-writer');?></a>
        <a href="#tab-1" class="nav-tab"><?php echo esc_html__("Editing Template Manager", 'aiomatic-automatic-ai-content-writer');?></a>
        <a href="#tab-3" class="nav-tab"><?php echo esc_html__("Automatic Content Editing", 'aiomatic-automatic-ai-content-writer');?></a>
        <a href="#tab-4" class="nav-tab"><?php echo esc_html__("Existing Content Editing", 'aiomatic-automatic-ai-content-writer');?></a>
        <a href="#tab-2" class="nav-tab"><?php echo esc_html__("More AI Tools", 'aiomatic-automatic-ai-content-writer');?></a>
    </nav>
        <form id="myForm" method="post" action="<?php if(is_multisite() && is_network_admin()){echo '../options.php';}else{echo 'options.php';}?>">
        <div class="cr_autocomplete">
 <input type="password" id="PreventChromeAutocomplete" 
  name="PreventChromeAutocomplete" autocomplete="address-level4" />
</div>
<?php
    settings_fields('aiomatic_option_group2');
    do_settings_sections('aiomatic_option_group2');
    $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
    if (isset($aiomatic_Spinner_Settings['aiomatic_spinning'])) {
        $aiomatic_spinning = $aiomatic_Spinner_Settings['aiomatic_spinning'];
    } else {
        $aiomatic_spinning = '';
    }
    if (isset($aiomatic_Spinner_Settings['post_posts'])) {
        $post_posts = $aiomatic_Spinner_Settings['post_posts'];
    } else {
        $post_posts = '';
    }
    if (isset($aiomatic_Spinner_Settings['post_pages'])) {
        $post_pages = $aiomatic_Spinner_Settings['post_pages'];
    } else {
        $post_pages = '';
    }
    if (isset($aiomatic_Spinner_Settings['post_custom'])) {
        $post_custom = $aiomatic_Spinner_Settings['post_custom'];
    } else {
        $post_custom = '';
    }
    if (isset($aiomatic_Spinner_Settings['except_type'])) {
        $except_type = $aiomatic_Spinner_Settings['except_type'];
    } else {
        $except_type = '';
    }
    if (isset($aiomatic_Spinner_Settings['only_type'])) {
        $only_type = $aiomatic_Spinner_Settings['only_type'];
    } else {
        $only_type = '';
    }
    if (isset($aiomatic_Spinner_Settings['disable_tags'])) {
        $disable_tags = $aiomatic_Spinner_Settings['disable_tags'];
    } else {
        $disable_tags = '';
    }
    if (isset($aiomatic_Spinner_Settings['enable_tags'])) {
        $enable_tags = $aiomatic_Spinner_Settings['enable_tags'];
    } else {
        $enable_tags = '';
    }
    if (isset($aiomatic_Spinner_Settings['disable_users'])) {
        $disable_users = $aiomatic_Spinner_Settings['disable_users'];
    } else {
        $disable_users = '';
    }
    if (isset($aiomatic_Spinner_Settings['enable_users'])) {
        $enable_users = $aiomatic_Spinner_Settings['enable_users'];
    } else {
        $enable_users = '';
    }
    if (isset($aiomatic_Spinner_Settings['featured_status'])) {
        $featured_status = $aiomatic_Spinner_Settings['featured_status'];
    } else {
        $featured_status = '';
    }
    if (isset($aiomatic_Spinner_Settings['seo_status'])) {
        $seo_status = $aiomatic_Spinner_Settings['seo_status'];
    } else {
        $seo_status = '';
    }
    if (isset($aiomatic_Spinner_Settings['enable_field'])) {
        $enable_field = $aiomatic_Spinner_Settings['enable_field'];
    } else {
        $enable_field = '';
    }
    if (isset($aiomatic_Spinner_Settings['disable_field'])) {
        $disable_field = $aiomatic_Spinner_Settings['disable_field'];
    } else {
        $disable_field = '';
    }
    if (isset($aiomatic_Spinner_Settings['change_status'])) {
        $change_status = $aiomatic_Spinner_Settings['change_status'];
    } else {
        $change_status = '';
    }
    if (isset($aiomatic_Spinner_Settings['change_date'])) {
        $change_date = $aiomatic_Spinner_Settings['change_date'];
    } else {
        $change_date = '';
    }
    if (isset($aiomatic_Spinner_Settings['store_data'])) {
        $store_data = $aiomatic_Spinner_Settings['store_data'];
    } else {
        $store_data = '';
    }
    if (isset($aiomatic_Spinner_Settings['delay_post'])) {
        $delay_post = $aiomatic_Spinner_Settings['delay_post'];
    } else {
        $delay_post = '';
    }
    if (isset($aiomatic_Spinner_Settings['process_event'])) {
        $process_event = $aiomatic_Spinner_Settings['process_event'];
    } else {
        $process_event = '';
    }
    if (isset($aiomatic_Spinner_Settings['use_template_auto'])) {
        $use_template_auto = $aiomatic_Spinner_Settings['use_template_auto'];
    } else {
        $use_template_auto = '';
    }
    if (isset($aiomatic_Spinner_Settings['run_background'])) {
        $run_background = $aiomatic_Spinner_Settings['run_background'];
    } else {
        $run_background = '';
    }
    if (isset($aiomatic_Spinner_Settings['enable_default'])) {
        $enable_default = $aiomatic_Spinner_Settings['enable_default'];
    } else {
        $enable_default = '';
    }
    if (isset($aiomatic_Spinner_Settings['append_spintax'])) {
        $append_spintax = $aiomatic_Spinner_Settings['append_spintax'];
    } else {
        $append_spintax = '';
    }
    if (isset($aiomatic_Spinner_Settings['append_location'])) {
        $append_location = $aiomatic_Spinner_Settings['append_location'];
    } else {
        $append_location = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_featured_image'])) {
        $ai_featured_image = $aiomatic_Spinner_Settings['ai_featured_image'];
    } else {
        $ai_featured_image = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_featured_image_source'])) {
        $ai_featured_image_source = $aiomatic_Spinner_Settings['ai_featured_image_source'];
    } else {
        $ai_featured_image_source = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_image_command'])) {
        $ai_image_command = $aiomatic_Spinner_Settings['ai_image_command'];
    } else {
        $ai_image_command = '';
    }
    if (isset($aiomatic_Spinner_Settings['url_image_list'])) {
        $url_image_list = $aiomatic_Spinner_Settings['url_image_list'];
    } else {
        $url_image_list = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_featured_image_edit'])) {
        $ai_featured_image_edit = $aiomatic_Spinner_Settings['ai_featured_image_edit'];
    } else {
        $ai_featured_image_edit = 'disabled';
    }
    if (isset($aiomatic_Spinner_Settings['ai_featured_image_engine'])) {
        $ai_featured_image_engine = $aiomatic_Spinner_Settings['ai_featured_image_engine'];
    } else {
        $ai_featured_image_engine = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_image_command_edit'])) {
        $ai_image_command_edit = $aiomatic_Spinner_Settings['ai_image_command_edit'];
    } else {
        $ai_image_command_edit = 'Slightly change the image, making it unique.';
    }
    if (isset($aiomatic_Spinner_Settings['image_strength'])) {
        $image_strength = $aiomatic_Spinner_Settings['image_strength'];
    } else {
        $image_strength = '0.90';
    }
    if (isset($aiomatic_Spinner_Settings['image_strength_content'])) {
        $image_strength_content = $aiomatic_Spinner_Settings['image_strength_content'];
    } else {
        $image_strength_content = '0.90';
    }
    if (isset($aiomatic_Spinner_Settings['image_size'])) {
        $image_size = $aiomatic_Spinner_Settings['image_size'];
    } else {
        $image_size = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_edit_content'])) {
        $max_edit_content = $aiomatic_Spinner_Settings['max_edit_content'];
    } else {
        $max_edit_content = '';
    }
    if (isset($aiomatic_Spinner_Settings['image_model'])) {
        $image_model = $aiomatic_Spinner_Settings['image_model'];
    } else {
        $image_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['min_char'])) {
        $min_char = $aiomatic_Spinner_Settings['min_char'];
    } else {
        $min_char = '';
    }
    if (isset($aiomatic_Spinner_Settings['videos'])) {
        $videos = $aiomatic_Spinner_Settings['videos'];
    } else {
        $videos = '';
    }
    if (isset($aiomatic_Spinner_Settings['social_list'])) {
        $social_list = $aiomatic_Spinner_Settings['social_list'];
    } else {
        $social_list = array();
    }
    if (isset($aiomatic_Spinner_Settings['rich_embed_location'])) {
        $rich_embed_location = $aiomatic_Spinner_Settings['rich_embed_location'];
    } else {
        $rich_embed_location = 'bottom';
    }
    if (isset($aiomatic_Spinner_Settings['append_toc'])) {
        $append_toc = $aiomatic_Spinner_Settings['append_toc'];
    } else {
        $append_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['when_toc'])) {
        $when_toc = $aiomatic_Spinner_Settings['when_toc'];
    } else {
        $when_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['title_toc'])) {
        $title_toc = $aiomatic_Spinner_Settings['title_toc'];
    } else {
        $title_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['hierarchy_toc'])) {
        $hierarchy_toc = $aiomatic_Spinner_Settings['hierarchy_toc'];
    } else {
        $hierarchy_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['add_numbers_toc'])) {
        $add_numbers_toc = $aiomatic_Spinner_Settings['add_numbers_toc'];
    } else {
        $add_numbers_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['heading_levels1'])) {
        $heading_levels1 = $aiomatic_Spinner_Settings['heading_levels1'];
    } else {
        $heading_levels1 = '';
    }
    if (isset($aiomatic_Spinner_Settings['heading_levels2'])) {
        $heading_levels2 = $aiomatic_Spinner_Settings['heading_levels2'];
    } else {
        $heading_levels2 = '';
    }
    if (isset($aiomatic_Spinner_Settings['heading_levels3'])) {
        $heading_levels3 = $aiomatic_Spinner_Settings['heading_levels3'];
    } else {
        $heading_levels3 = '';
    }
    if (isset($aiomatic_Spinner_Settings['heading_levels4'])) {
        $heading_levels4 = $aiomatic_Spinner_Settings['heading_levels4'];
    } else {
        $heading_levels4 = '';
    }
    if (isset($aiomatic_Spinner_Settings['heading_levels5'])) {
        $heading_levels5 = $aiomatic_Spinner_Settings['heading_levels5'];
    } else {
        $heading_levels5 = '';
    }
    if (isset($aiomatic_Spinner_Settings['heading_levels6'])) {
        $heading_levels6 = $aiomatic_Spinner_Settings['heading_levels6'];
    } else {
        $heading_levels6 = '';
    }
    if (isset($aiomatic_Spinner_Settings['exclude_toc'])) {
        $exclude_toc = $aiomatic_Spinner_Settings['exclude_toc'];
    } else {
        $exclude_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['float_toc'])) {
        $float_toc = $aiomatic_Spinner_Settings['float_toc'];
    } else {
        $float_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['color_toc'])) {
        $color_toc = $aiomatic_Spinner_Settings['color_toc'];
    } else {
        $color_toc = '';
    }
    if (isset($aiomatic_Spinner_Settings['add_links'])) {
        $add_links = $aiomatic_Spinner_Settings['add_links'];
    } else {
        $add_links = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_method'])) {
        $link_method = $aiomatic_Spinner_Settings['link_method'];
    } else {
        $link_method = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_juicer_prompt'])) {
        $link_juicer_prompt = $aiomatic_Spinner_Settings['link_juicer_prompt'];
    } else {
        $link_juicer_prompt = 'Generate a comma-separated list of relevant keywords for the post title (for use in the Link Juicer plugin): "%%post_title%%".';
    }
    if (isset($aiomatic_Spinner_Settings['link_juicer_assistant_id'])) {
        $link_juicer_assistant_id = $aiomatic_Spinner_Settings['link_juicer_assistant_id'];
    } else {
        $link_juicer_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_juicer_model'])) {
        $link_juicer_model = $aiomatic_Spinner_Settings['link_juicer_model'];
    } else {
        $link_juicer_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_link_juicer'])) {
        $ai_vision_link_juicer = $aiomatic_Spinner_Settings['ai_vision_link_juicer'];
    } else {
        $ai_vision_link_juicer = '';
    }
    if (isset($aiomatic_Spinner_Settings['title_model'])) {
        $title_model = $aiomatic_Spinner_Settings['title_model'];
    } else {
        $title_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_title_vision'])) {
        $ai_title_vision = $aiomatic_Spinner_Settings['ai_title_vision'];
    } else {
        $ai_title_vision = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_excerpt_vision'])) {
        $ai_excerpt_vision = $aiomatic_Spinner_Settings['ai_excerpt_vision'];
    } else {
        $ai_excerpt_vision = '';
    }
    if (isset($aiomatic_Spinner_Settings['excerpt_model'])) {
        $excerpt_model = $aiomatic_Spinner_Settings['excerpt_model'];
    } else {
        $excerpt_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_slug_vision'])) {
        $ai_slug_vision = $aiomatic_Spinner_Settings['ai_slug_vision'];
    } else {
        $ai_slug_vision = '';
    }
    if (isset($aiomatic_Spinner_Settings['slug_model'])) {
        $slug_model = $aiomatic_Spinner_Settings['slug_model'];
    } else {
        $slug_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_links'])) {
        $max_links = $aiomatic_Spinner_Settings['max_links'];
    } else {
        $max_links = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_list'])) {
        $link_list = $aiomatic_Spinner_Settings['link_list'];
    } else {
        $link_list = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_nofollow'])) {
        $link_nofollow = $aiomatic_Spinner_Settings['link_nofollow'];
    } else {
        $link_nofollow = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_type'])) {
        $link_type = $aiomatic_Spinner_Settings['link_type'];
    } else {
        $link_type = 'internal';
    }
    if (isset($aiomatic_Spinner_Settings['affiliate_id'])) {
        $affiliate_id = $aiomatic_Spinner_Settings['affiliate_id'];
    } else {
        $affiliate_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['target_country'])) {
        $target_country = $aiomatic_Spinner_Settings['target_country'];
    } else {
        $target_country = 'com';
    }
    if (isset($aiomatic_Spinner_Settings['link_post_types'])) {
        $link_post_types = $aiomatic_Spinner_Settings['link_post_types'];
    } else {
        $link_post_types = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_post_not_ids'])) {
        $link_post_not_ids = $aiomatic_Spinner_Settings['link_post_not_ids'];
    } else {
        $link_post_not_ids = '';
    }
    if (isset($aiomatic_Spinner_Settings['force_keywords'])) {
        $force_keywords = $aiomatic_Spinner_Settings['force_keywords'];
    } else {
        $force_keywords = '';
    }
    if (isset($aiomatic_Spinner_Settings['link_post_ids'])) {
        $link_post_ids = $aiomatic_Spinner_Settings['link_post_ids'];
    } else {
        $link_post_ids = '';
    }
    if (isset($aiomatic_Spinner_Settings['add_cats'])) {
        $add_cats = $aiomatic_Spinner_Settings['add_cats'];
    } else {
        $add_cats = 'disabled';
    }
    if (isset($aiomatic_Spinner_Settings['add_tags'])) {
        $add_tags = $aiomatic_Spinner_Settings['add_tags'];
    } else {
        $add_tags = 'disabled';
    }
    if (isset($aiomatic_Spinner_Settings['cats_tax'])) {
        $cats_tax = $aiomatic_Spinner_Settings['cats_tax'];
    } else {
        $cats_tax = '';
    }
    if (isset($aiomatic_Spinner_Settings['tags_tax'])) {
        $tags_tax = $aiomatic_Spinner_Settings['tags_tax'];
    } else {
        $tags_tax = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_cats'])) {
        $max_cats = $aiomatic_Spinner_Settings['max_cats'];
    } else {
        $max_cats = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_tags'])) {
        $max_tags = $aiomatic_Spinner_Settings['max_tags'];
    } else {
        $max_tags = '';
    }
    if (isset($aiomatic_Spinner_Settings['skip_inexist'])) {
        $skip_inexist = $aiomatic_Spinner_Settings['skip_inexist'];
    } else {
        $skip_inexist = '';
    }
    if (isset($aiomatic_Spinner_Settings['skip_inexist_tags'])) {
        $skip_inexist_tags = $aiomatic_Spinner_Settings['skip_inexist_tags'];
    } else {
        $skip_inexist_tags = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_cats'])) {
        $ai_cats = $aiomatic_Spinner_Settings['ai_cats'];
    } else {
        $ai_cats = 'Write a comma separated list of 5 categories for post title: %%post_title%%';
    }
    if (isset($aiomatic_Spinner_Settings['ai_tags'])) {
        $ai_tags = $aiomatic_Spinner_Settings['ai_tags'];
    } else {
        $ai_tags = 'Write a comma separated list of 5 tags for post title: %%post_title%%';
    }
    if (isset($aiomatic_Spinner_Settings['cats_model'])) {
        $cats_model = $aiomatic_Spinner_Settings['cats_model'];
    } else {
        $cats_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['tags_model'])) {
        $tags_model = $aiomatic_Spinner_Settings['tags_model'];
    } else {
        $tags_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_tag'])) {
        $ai_vision_tag = $aiomatic_Spinner_Settings['ai_vision_tag'];
    } else {
        $ai_vision_tag = '';
    }
    if (isset($aiomatic_Spinner_Settings['add_custom'])) {
        $add_custom = $aiomatic_Spinner_Settings['add_custom'];
    } else {
        $add_custom = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_custom_field'])) {
        $ai_custom_field = $aiomatic_Spinner_Settings['ai_custom_field'];
    } else {
        $ai_custom_field = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_custom_field_prompt'])) {
        $no_custom_field_prompt = $aiomatic_Spinner_Settings['no_custom_field_prompt'];
    } else {
        $no_custom_field_prompt = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_custom_tax_prompt'])) {
        $no_custom_tax_prompt = $aiomatic_Spinner_Settings['no_custom_tax_prompt'];
    } else {
        $no_custom_tax_prompt = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_custom_tax'])) {
        $ai_custom_tax = $aiomatic_Spinner_Settings['ai_custom_tax'];
    } else {
        $ai_custom_tax = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_custom'])) {
        $max_custom = $aiomatic_Spinner_Settings['max_custom'];
    } else {
        $max_custom = '';
    }
    if (isset($aiomatic_Spinner_Settings['skip_inexist_custom'])) {
        $skip_inexist_custom = $aiomatic_Spinner_Settings['skip_inexist_custom'];
    } else {
        $skip_inexist_custom = '';
    }
    if (isset($aiomatic_Spinner_Settings['custom_assistant_id'])) {
        $custom_assistant_id = $aiomatic_Spinner_Settings['custom_assistant_id'];
    } else {
        $custom_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['custom_model'])) {
        $custom_model = $aiomatic_Spinner_Settings['custom_model'];
    } else {
        $custom_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_custom'])) {
        $ai_vision_custom = $aiomatic_Spinner_Settings['ai_vision_custom'];
    } else {
        $ai_vision_custom = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_cat'])) {
        $ai_vision_cat = $aiomatic_Spinner_Settings['ai_vision_cat'];
    } else {
        $ai_vision_cat = '';
    }
    if (isset($aiomatic_Spinner_Settings['tags_assistant_id'])) {
        $tags_assistant_id = $aiomatic_Spinner_Settings['tags_assistant_id'];
    } else {
        $tags_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['add_comments'])) {
        $add_comments = $aiomatic_Spinner_Settings['add_comments'];
    } else {
        $add_comments = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_comments'])) {
        $max_comments = $aiomatic_Spinner_Settings['max_comments'];
    } else {
        $max_comments = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_comments'])) {
        $ai_comments = $aiomatic_Spinner_Settings['ai_comments'];
    } else {
        $ai_comments = '';
    }
    if (isset($aiomatic_Spinner_Settings['star_count'])) {
        $star_count = $aiomatic_Spinner_Settings['star_count'];
    } else {
        $star_count = '';
    }
    if (isset($aiomatic_Spinner_Settings['prev_comms'])) {
        $prev_comms = $aiomatic_Spinner_Settings['prev_comms'];
    } else {
        $prev_comms = '';
    }
    if (isset($aiomatic_Spinner_Settings['comments_assistant_id'])) {
        $comments_assistant_id = $aiomatic_Spinner_Settings['comments_assistant_id'];
    } else {
        $comments_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['comments_model'])) {
        $comments_model = $aiomatic_Spinner_Settings['comments_model'];
    } else {
        $comments_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_assistant_id'])) {
        $tax_assistant_id = $aiomatic_Spinner_Settings['tax_assistant_id'];
    } else {
        $tax_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_description_model'])) {
        $tax_description_model = $aiomatic_Spinner_Settings['tax_description_model'];
    } else {
        $tax_description_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_description_prompt'])) {
        $tax_description_prompt = $aiomatic_Spinner_Settings['tax_description_prompt'];
    } else {
        $tax_description_prompt = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_seo_auto'])) {
        $tax_seo_auto = $aiomatic_Spinner_Settings['tax_seo_auto'];
    } else {
        $tax_seo_auto = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_seo_assistant_id'])) {
        $tax_seo_assistant_id = $aiomatic_Spinner_Settings['tax_seo_assistant_id'];
    } else {
        $tax_seo_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_seo_description_prompt'])) {
        $tax_seo_description_prompt = $aiomatic_Spinner_Settings['tax_seo_description_prompt'];
    } else {
        $tax_seo_description_prompt = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_description_auto'])) {
        $tax_description_auto = $aiomatic_Spinner_Settings['tax_description_auto'];
    } else {
        $tax_description_auto = array();
    }
    if (isset($aiomatic_Spinner_Settings['tax_description_manual'])) {
        $tax_description_manual = $aiomatic_Spinner_Settings['tax_description_manual'];
    } else {
        $tax_description_manual = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_tax_nr'])) {
        $max_tax_nr = $aiomatic_Spinner_Settings['max_tax_nr'];
    } else {
        $max_tax_nr = '';
    }
    if (isset($aiomatic_Spinner_Settings['overwite_tax'])) {
        $overwite_tax = $aiomatic_Spinner_Settings['overwite_tax'];
    } else {
        $overwite_tax = '';
    }
    if (isset($aiomatic_Spinner_Settings['tax_seo_description_model'])) {
        $tax_seo_description_model = $aiomatic_Spinner_Settings['tax_seo_description_model'];
    } else {
        $tax_seo_description_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['user_list'])) {
        $user_list = $aiomatic_Spinner_Settings['user_list'];
    } else {
        $user_list = '%%random_user%%';
    }
    if (isset($aiomatic_Spinner_Settings['email_list'])) {
        $email_list = $aiomatic_Spinner_Settings['email_list'];
    } else {
        $email_list = '';
    }
    if (isset($aiomatic_Spinner_Settings['url_list'])) {
        $url_list = $aiomatic_Spinner_Settings['url_list'];
    } else {
        $url_list = '';
    }
    if (isset($aiomatic_Spinner_Settings['min_time'])) {
        $min_time = $aiomatic_Spinner_Settings['min_time'];
    } else {
        $min_time = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_time'])) {
        $max_time = $aiomatic_Spinner_Settings['max_time'];
    } else {
        $max_time = '';
    }
    if (isset($aiomatic_Spinner_Settings['headings'])) {
        $headings = $aiomatic_Spinner_Settings['headings'];
    } else {
        $headings = '';
    }
    if (isset($aiomatic_Spinner_Settings['headings_model'])) {
        $headings_model = $aiomatic_Spinner_Settings['headings_model'];
    } else {
        $headings_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['headings_assistant_id'])) {
        $headings_assistant_id = $aiomatic_Spinner_Settings['headings_assistant_id'];
    } else {
        $headings_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['categories_assistant_id'])) {
        $categories_assistant_id = $aiomatic_Spinner_Settings['categories_assistant_id'];
    } else {
        $categories_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['headings_ai_command'])) {
        $headings_ai_command = $aiomatic_Spinner_Settings['headings_ai_command'];
    } else {
        $headings_ai_command = '';
    }
    if (isset($aiomatic_Spinner_Settings['append_assistant_id'])) {
        $append_assistant_id = $aiomatic_Spinner_Settings['append_assistant_id'];
    } else {
        $append_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['enable_ai_images'])) {
        $enable_ai_images = $aiomatic_Spinner_Settings['enable_ai_images'];
    } else {
        $enable_ai_images = '';
    }
    if (isset($aiomatic_Spinner_Settings['images'])) {
        $images = $aiomatic_Spinner_Settings['images'];
    } else {
        $images = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_tokens'])) {
        $max_tokens = $aiomatic_Spinner_Settings['max_tokens'];
    } else {
        $max_tokens = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_seed_tokens'])) {
        $max_seed_tokens = $aiomatic_Spinner_Settings['max_seed_tokens'];
    } else {
        $max_seed_tokens = '';
    }
    if (isset($aiomatic_Spinner_Settings['add_seo'])) {
        $add_seo = $aiomatic_Spinner_Settings['add_seo'];
    } else {
        $add_seo = '';
    }
    if (isset($aiomatic_Spinner_Settings['add_seo_title'])) {
        $add_seo_title = $aiomatic_Spinner_Settings['add_seo_title'];
    } else {
        $add_seo_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_seo_title'])) {
        $ai_seo_title = $aiomatic_Spinner_Settings['ai_seo_title'];
    } else {
        $ai_seo_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['meta_assistant_id_title'])) {
        $meta_assistant_id_title = $aiomatic_Spinner_Settings['meta_assistant_id_title'];
    } else {
        $meta_assistant_id_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['seo_model_title'])) {
        $seo_model_title = $aiomatic_Spinner_Settings['seo_model_title'];
    } else {
        $seo_model_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['seo_max_char_title'])) {
        $seo_max_char_title = $aiomatic_Spinner_Settings['seo_max_char_title'];
    } else {
        $seo_max_char_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_seo_title'])) {
        $ai_vision_seo_title = $aiomatic_Spinner_Settings['ai_vision_seo_title'];
    } else {
        $ai_vision_seo_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_seo'])) {
        $ai_seo = $aiomatic_Spinner_Settings['ai_seo'];
    } else {
        $ai_seo = '';
    }
    if (isset($aiomatic_Spinner_Settings['meta_assistant_id'])) {
        $meta_assistant_id = $aiomatic_Spinner_Settings['meta_assistant_id'];
    } else {
        $meta_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['seo_model'])) {
        $seo_model = $aiomatic_Spinner_Settings['seo_model'];
    } else {
        $seo_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_com'])) {
        $ai_vision_com = $aiomatic_Spinner_Settings['ai_vision_com'];
    } else {
        $ai_vision_com = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_approve'])) {
        $no_approve = $aiomatic_Spinner_Settings['no_approve'];
    } else {
        $no_approve = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_seo'])) {
        $ai_vision_seo = $aiomatic_Spinner_Settings['ai_vision_seo'];
    } else {
        $ai_vision_seo = '';
    }
    if (isset($aiomatic_Spinner_Settings['seo_max_char'])) {
        $seo_max_char = $aiomatic_Spinner_Settings['seo_max_char'];
    } else {
        $seo_max_char = '';
    }
    if (isset($aiomatic_Spinner_Settings['seo_copy_excerpt'])) {
        $seo_copy_excerpt = $aiomatic_Spinner_Settings['seo_copy_excerpt'];
    } else {
        $seo_copy_excerpt = '';
    }
    if (isset($aiomatic_Spinner_Settings['content_text_speech'])) {
        $content_text_speech = $aiomatic_Spinner_Settings['content_text_speech'];
    } else {
        $content_text_speech = 'off';
    }
    if (isset($aiomatic_Spinner_Settings['did_image'])) {
        $did_image = $aiomatic_Spinner_Settings['did_image'];
    } else {
        $did_image = '';
    }
    if (isset($aiomatic_Spinner_Settings['did_voice'])) {
        $did_voice = $aiomatic_Spinner_Settings['did_voice'];
    } else {
        $did_voice = '';
    }
    if (isset($aiomatic_Spinner_Settings['eleven_voice'])) {
        $eleven_voice = $aiomatic_Spinner_Settings['eleven_voice'];
    } else {
        $eleven_voice = '';
    }
    if (isset($aiomatic_Spinner_Settings['eleven_voice_custom'])) {
        $eleven_voice_custom = $aiomatic_Spinner_Settings['eleven_voice_custom'];
    } else {
        $eleven_voice_custom = '';
    }
    if (isset($aiomatic_Spinner_Settings['eleven_model_id'])) {
        $eleven_model_id = $aiomatic_Spinner_Settings['eleven_model_id'];
    } else {
        $eleven_model_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['voice_stability'])) {
        $voice_stability = $aiomatic_Spinner_Settings['voice_stability'];
    } else {
        $voice_stability = '';
    }
    if (isset($aiomatic_Spinner_Settings['voice_similarity_boost'])) {
        $voice_similarity_boost = $aiomatic_Spinner_Settings['voice_similarity_boost'];
    } else {
        $voice_similarity_boost = '';
    }
    if (isset($aiomatic_Spinner_Settings['voice_style'])) {
        $voice_style = $aiomatic_Spinner_Settings['voice_style'];
    } else {
        $voice_style = '';
    }
    if (isset($aiomatic_Spinner_Settings['speaker_boost'])) {
        $speaker_boost = $aiomatic_Spinner_Settings['speaker_boost'];
    } else {
        $speaker_boost = '';
    }
    if (isset($aiomatic_Spinner_Settings['open_model_id'])) {
        $open_model_id = $aiomatic_Spinner_Settings['open_model_id'];
    } else {
        $open_model_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['open_voice'])) {
        $open_voice = $aiomatic_Spinner_Settings['open_voice'];
    } else {
        $open_voice = '';
    }
    if (isset($aiomatic_Spinner_Settings['open_format'])) {
        $open_format = $aiomatic_Spinner_Settings['open_format'];
    } else {
        $open_format = '';
    }
    if (isset($aiomatic_Spinner_Settings['open_speed'])) {
        $open_speed = $aiomatic_Spinner_Settings['open_speed'];
    } else {
        $open_speed = '';
    }
    if (isset($aiomatic_Spinner_Settings['voice_language'])) {
        $voice_language = $aiomatic_Spinner_Settings['voice_language'];
    } else {
        $voice_language = '';
    }
    if (isset($aiomatic_Spinner_Settings['google_voice'])) {
        $google_voice = $aiomatic_Spinner_Settings['google_voice'];
    } else {
        $google_voice = '';
    }
    if (isset($aiomatic_Spinner_Settings['audio_profile'])) {
        $audio_profile = $aiomatic_Spinner_Settings['audio_profile'];
    } else {
        $audio_profile = '';
    }
    if (isset($aiomatic_Spinner_Settings['voice_speed'])) {
        $voice_speed = $aiomatic_Spinner_Settings['voice_speed'];
    } else {
        $voice_speed = '';
    }
    if (isset($aiomatic_Spinner_Settings['voice_pitch'])) {
        $voice_pitch = $aiomatic_Spinner_Settings['voice_pitch'];
    } else {
        $voice_pitch = '';
    }
    if (isset($aiomatic_Spinner_Settings['text_to_audio'])) {
        $text_to_audio = $aiomatic_Spinner_Settings['text_to_audio'];
    } else {
        $text_to_audio = '%%post_content%%';
    }
    if (isset($aiomatic_Spinner_Settings['audio_location'])) {
        $audio_location = $aiomatic_Spinner_Settings['audio_location'];
    } else {
        $audio_location = 'append';
    }
    if (isset($aiomatic_Spinner_Settings['content_speech_text'])) {
        $content_speech_text = $aiomatic_Spinner_Settings['content_speech_text'];
    } else {
        $content_speech_text = 'off';
    }
    if (isset($aiomatic_Spinner_Settings['speech_model'])) {
        $speech_model = $aiomatic_Spinner_Settings['speech_model'];
    } else {
        $speech_model = 'whisper-1';
    }
    if (isset($aiomatic_Spinner_Settings['max_speech'])) {
        $max_speech = $aiomatic_Spinner_Settings['max_speech'];
    } else {
        $max_speech = '';
    }
    if (isset($aiomatic_Spinner_Settings['audio_to_text'])) {
        $audio_to_text = $aiomatic_Spinner_Settings['audio_to_text'];
    } else {
        $audio_to_text = '%%audio_to_text%%';
    }
    if (isset($aiomatic_Spinner_Settings['audio_to_text_prompt'])) {
        $audio_to_text_prompt = $aiomatic_Spinner_Settings['audio_to_text_prompt'];
    } else {
        $audio_to_text_prompt = '';
    }
    if (isset($aiomatic_Spinner_Settings['speech_temperature'])) {
        $speech_temperature = $aiomatic_Spinner_Settings['speech_temperature'];
    } else {
        $speech_temperature = '';
    }
    if (isset($aiomatic_Spinner_Settings['audio_text_location'])) {
        $audio_text_location = $aiomatic_Spinner_Settings['audio_text_location'];
    } else {
        $audio_text_location = 'append';
    }
    if (isset($aiomatic_Spinner_Settings['prep_audio'])) {
        $prep_audio = $aiomatic_Spinner_Settings['prep_audio'];
    } else {
        $prep_audio = '';
    }
    if (isset($aiomatic_Spinner_Settings['copy_location'])) {
        $copy_location = $aiomatic_Spinner_Settings['copy_location'];
    } else {
        $copy_location = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_result_tokens'])) {
        $max_result_tokens = $aiomatic_Spinner_Settings['max_result_tokens'];
    } else {
        $max_result_tokens = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_continue_tokens'])) {
        $max_continue_tokens = $aiomatic_Spinner_Settings['max_continue_tokens'];
    } else {
        $max_continue_tokens = '';
    }
    if (isset($aiomatic_Spinner_Settings['model'])) {
        $model = $aiomatic_Spinner_Settings['model'];
    } else {
        $model = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_command'])) {
        $ai_command = $aiomatic_Spinner_Settings['ai_command'];
    } else {
        $ai_command = '';
    }
    if (isset($aiomatic_Spinner_Settings['temperature'])) {
        $temperature = $aiomatic_Spinner_Settings['temperature'];
    } else {
        $temperature = '';
    }
    if (isset($aiomatic_Spinner_Settings['top_p'])) {
        $top_p = $aiomatic_Spinner_Settings['top_p'];
    } else {
        $top_p = '';
    }
    if (isset($aiomatic_Spinner_Settings['presence_penalty'])) {
        $presence_penalty = $aiomatic_Spinner_Settings['presence_penalty'];
    } else {
        $presence_penalty = '';
    }
    if (isset($aiomatic_Spinner_Settings['frequency_penalty'])) {
        $frequency_penalty = $aiomatic_Spinner_Settings['frequency_penalty'];
    } else {
        $frequency_penalty = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_rewriter'])) {
        $ai_rewriter = $aiomatic_Spinner_Settings['ai_rewriter'];
    } else {
        $ai_rewriter = '';
    }
    if (isset($aiomatic_Spinner_Settings['similarity_threshold'])) {
        $similarity_threshold = $aiomatic_Spinner_Settings['similarity_threshold'];
    } else {
        $similarity_threshold = '';
    }
    if (isset($aiomatic_Spinner_Settings['duplication_template'])) {
        $duplication_template = $aiomatic_Spinner_Settings['duplication_template'];
    } else {
        $duplication_template = '';
    }
    if (isset($aiomatic_Spinner_Settings['enhance_model'])) {
        $enhance_model = $aiomatic_Spinner_Settings['enhance_model'];
    } else {
        $enhance_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['enhance_assistant_id'])) {
        $enhance_assistant_id = $aiomatic_Spinner_Settings['enhance_assistant_id'];
    } else {
        $enhance_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_to_process_in_one_go'])) {
        $max_to_process_in_one_go = $aiomatic_Spinner_Settings['max_to_process_in_one_go'];
    } else {
        $max_to_process_in_one_go = '';
    }
    if (isset($aiomatic_Spinner_Settings['duplication_index'])) {
        $duplication_index = $aiomatic_Spinner_Settings['duplication_index'];
    } else {
        $duplication_index = '';
    }
    if (isset($aiomatic_Spinner_Settings['dup_titles'])) {
        $dup_titles = $aiomatic_Spinner_Settings['dup_titles'];
    } else {
        $dup_titles = '';
    }
    if (isset($aiomatic_Spinner_Settings['enhance_articles'])) {
        $enhance_articles = $aiomatic_Spinner_Settings['enhance_articles'];
    } else {
        $enhance_articles = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_instruction_enhance'])) {
        $ai_instruction_enhance = $aiomatic_Spinner_Settings['ai_instruction_enhance'];
    } else {
        $ai_instruction_enhance = 'You are an AI content editor tasked with resolving duplicate content issues on a blog. Compare the content of two posts to determine if any valuable or unique information from the duplicate post can be integrated into the post to be kept. If the duplicate post contains unique information that is not already present in the post to be kept, integrate it seamlessly into the content of the post to be kept while preserving its natural flow and meaning. Ensure that the HTML tags and structure are maintained during processing. If the duplicate post contains no new or unique information, return the content of the post to be kept unchanged. Do not make unnecessary edits.
----
Post content from where additional info should be gathered (if found):
%%post_plain_text_content_to_be_removed%%
----
Post content where info should be inserted (if needed):
%%post_content_to_be_kept%%
----
Process the content now, return only the edited content, nothing else.';
    }
    if (isset($aiomatic_Spinner_Settings['duplicate_action'])) {
        $duplicate_action = $aiomatic_Spinner_Settings['duplicate_action'];
    } else {
        $duplicate_action = '';
    }
    if (isset($aiomatic_Spinner_Settings['duplicate_handle'])) {
        $duplicate_handle = $aiomatic_Spinner_Settings['duplicate_handle'];
    } else {
        $duplicate_handle = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_duplication'])) {
        $ai_duplication = $aiomatic_Spinner_Settings['ai_duplication'];
    } else {
        $ai_duplication = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_instruction'])) {
        $ai_instruction = $aiomatic_Spinner_Settings['ai_instruction'];
    } else {
        $ai_instruction = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_instruction_title'])) {
        $ai_instruction_title = $aiomatic_Spinner_Settings['ai_instruction_title'];
    } else {
        $ai_instruction_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_add_excerpt'])) {
        $no_add_excerpt = $aiomatic_Spinner_Settings['no_add_excerpt'];
    } else {
        $no_add_excerpt = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_add_slug'])) {
        $no_add_slug = $aiomatic_Spinner_Settings['no_add_slug'];
    } else {
        $no_add_slug = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_add_content'])) {
        $no_add_content = $aiomatic_Spinner_Settings['no_add_content'];
    } else {
        $no_add_content = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_add_title'])) {
        $no_add_title = $aiomatic_Spinner_Settings['no_add_title'];
    } else {
        $no_add_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_instruction_slug'])) {
        $ai_instruction_slug = $aiomatic_Spinner_Settings['ai_instruction_slug'];
    } else {
        $ai_instruction_slug = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_slug'])) {
        $no_slug = $aiomatic_Spinner_Settings['no_slug'];
    } else {
        $no_slug = '';
    }
    if (isset($aiomatic_Spinner_Settings['edit_temperature'])) {
        $edit_temperature = $aiomatic_Spinner_Settings['edit_temperature'];
    } else {
        $edit_temperature = '';
    }
    if (isset($aiomatic_Spinner_Settings['edit_top_p'])) {
        $edit_top_p = $aiomatic_Spinner_Settings['edit_top_p'];
    } else {
        $edit_top_p = '';
    }
    if (isset($aiomatic_Spinner_Settings['edit_presence_penalty'])) {
        $edit_presence_penalty = $aiomatic_Spinner_Settings['edit_presence_penalty'];
    } else {
        $edit_presence_penalty = '';
    }
    if (isset($aiomatic_Spinner_Settings['edit_frequency_penalty'])) {
        $edit_frequency_penalty = $aiomatic_Spinner_Settings['edit_frequency_penalty'];
    } else {
        $edit_frequency_penalty = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_char_chunks'])) {
        $max_char_chunks = $aiomatic_Spinner_Settings['max_char_chunks'];
    } else {
        $max_char_chunks = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_char'])) {
        $max_char = $aiomatic_Spinner_Settings['max_char'];
    } else {
        $max_char = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_title'])) {
        $no_title = $aiomatic_Spinner_Settings['no_title'];
    } else {
        $no_title = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision'])) {
        $ai_vision = $aiomatic_Spinner_Settings['ai_vision'];
    } else {
        $ai_vision = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_vision_add'])) {
        $ai_vision_add = $aiomatic_Spinner_Settings['ai_vision_add'];
    } else {
        $ai_vision_add = '';
    }
    if (isset($aiomatic_Spinner_Settings['preppend_add'])) {
        $preppend_add = $aiomatic_Spinner_Settings['preppend_add'];
    } else {
        $preppend_add = '';
    }
    if (isset($aiomatic_Spinner_Settings['append_add'])) {
        $append_add = $aiomatic_Spinner_Settings['append_add'];
    } else {
        $append_add = '';
    }
    if (isset($aiomatic_Spinner_Settings['rewrite_url'])) {
        $rewrite_url = $aiomatic_Spinner_Settings['rewrite_url'];
    } else {
        $rewrite_url = '';
    }
    if (isset($aiomatic_Spinner_Settings['edit_model'])) {
        $edit_model = $aiomatic_Spinner_Settings['edit_model'];
    } else {
        $edit_model = '';
    }
    if (isset($aiomatic_Spinner_Settings['edit_assistant_id'])) {
        $edit_assistant_id = $aiomatic_Spinner_Settings['edit_assistant_id'];
    } else {
        $edit_assistant_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['no_html_check'])) {
        $no_html_check = $aiomatic_Spinner_Settings['no_html_check'];
    } else {
        $no_html_check = '';
    }
    if (isset($aiomatic_Spinner_Settings['protect_html'])) {
        $protect_html = $aiomatic_Spinner_Settings['protect_html'];
    } else {
        $protect_html = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_featured_image_edit_content'])) {
        $ai_featured_image_edit_content = $aiomatic_Spinner_Settings['ai_featured_image_edit_content'];
    } else {
        $ai_featured_image_edit_content = 'disabled';
    }
    if (isset($aiomatic_Spinner_Settings['ai_featured_image_engine_content'])) {
        $ai_featured_image_engine_content = $aiomatic_Spinner_Settings['ai_featured_image_engine_content'];
    } else {
        $ai_featured_image_engine_content = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_image_command_edit_content'])) {
        $ai_image_command_edit_content = $aiomatic_Spinner_Settings['ai_image_command_edit_content'];
    } else {
        $ai_image_command_edit_content = 'Slightly change the image, making it unique.';
    }
    if (isset($aiomatic_Spinner_Settings['no_content'])) {
        $no_content = $aiomatic_Spinner_Settings['no_content'];
    } else {
        $no_content = '';
    }
    if (isset($aiomatic_Spinner_Settings['enable_gutenberg'])) {
        $enable_gutenberg = $aiomatic_Spinner_Settings['enable_gutenberg'];
    } else {
        $enable_gutenberg = '1';
    }
    if (isset($aiomatic_Spinner_Settings['enable_elementor'])) {
        $enable_elementor = $aiomatic_Spinner_Settings['enable_elementor'];
    } else {
        $enable_elementor = '1';
    }
    if (isset($aiomatic_Spinner_Settings['no_excerpt'])) {
        $no_excerpt = $aiomatic_Spinner_Settings['no_excerpt'];
    } else {
        $no_excerpt = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_slug_len'])) {
        $max_slug_len = $aiomatic_Spinner_Settings['max_slug_len'];
    } else {
        $max_slug_len = '';
    }
    if (isset($aiomatic_Spinner_Settings['ai_instruction_excerpt'])) {
        $ai_instruction_excerpt = $aiomatic_Spinner_Settings['ai_instruction_excerpt'];
    } else {
        $ai_instruction_excerpt = '';
    }
    if (isset($aiomatic_Spinner_Settings['tag_name'])) {
        $tag_name = $aiomatic_Spinner_Settings['tag_name'];
    } else {
        $tag_name = '';
    }
    if (isset($aiomatic_Spinner_Settings['post_id'])) {
        $post_id = $aiomatic_Spinner_Settings['post_id'];
    } else {
        $post_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['post_name'])) {
        $post_name = $aiomatic_Spinner_Settings['post_name'];
    } else {
        $post_name = '';
    }
    if (isset($aiomatic_Spinner_Settings['page_id'])) {
        $page_id = $aiomatic_Spinner_Settings['page_id'];
    } else {
        $page_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['post_parent'])) {
        $post_parent = $aiomatic_Spinner_Settings['post_parent'];
    } else {
        $post_parent = '';
    }
    if (isset($aiomatic_Spinner_Settings['post_status'])) {
        $post_status = $aiomatic_Spinner_Settings['post_status'];
    } else {
        $post_status = '';
    }
    if (isset($aiomatic_Spinner_Settings['type_post'])) {
        $type_post = $aiomatic_Spinner_Settings['type_post'];
    } else {
        $type_post = '';
    }
    if (isset($aiomatic_Spinner_Settings['pagename'])) {
        $pagename = $aiomatic_Spinner_Settings['pagename'];
    } else {
        $pagename = '';
    }
    if (isset($aiomatic_Spinner_Settings['search_offset'])) {
        $search_offset = $aiomatic_Spinner_Settings['search_offset'];
    } else {
        $search_offset = '';
    }
    if (isset($aiomatic_Spinner_Settings['search_query'])) {
        $search_query = $aiomatic_Spinner_Settings['search_query'];
    } else {
        $search_query = '';
    }
    if (isset($aiomatic_Spinner_Settings['meta_name'])) {
        $meta_name = $aiomatic_Spinner_Settings['meta_name'];
    } else {
        $meta_name = '';
    }
    if (isset($aiomatic_Spinner_Settings['meta_value'])) {
        $meta_value = $aiomatic_Spinner_Settings['meta_value'];
    } else {
        $meta_value = '';
    }
    if (isset($aiomatic_Spinner_Settings['year'])) {
        $year = $aiomatic_Spinner_Settings['year'];
    } else {
        $year = '';
    }
    if (isset($aiomatic_Spinner_Settings['month'])) {
        $month = $aiomatic_Spinner_Settings['month'];
    } else {
        $month = '';
    }
    if (isset($aiomatic_Spinner_Settings['day'])) {
        $day = $aiomatic_Spinner_Settings['day'];
    } else {
        $day = '';
    }
    if (isset($aiomatic_Spinner_Settings['order'])) {
        $order = $aiomatic_Spinner_Settings['order'];
    } else {
        $order = '';
    }
    if (isset($aiomatic_Spinner_Settings['orderby'])) {
        $orderby = $aiomatic_Spinner_Settings['orderby'];
    } else {
        $orderby = '';
    }
    if (isset($aiomatic_Spinner_Settings['featured_image'])) {
        $featured_image = $aiomatic_Spinner_Settings['featured_image'];
    } else {
        $featured_image = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_posts'])) {
        $max_posts = $aiomatic_Spinner_Settings['max_posts'];
    } else {
        $max_posts = '';
    }
    if (isset($aiomatic_Spinner_Settings['category_name'])) {
        $category_name = $aiomatic_Spinner_Settings['category_name'];
    } else {
        $category_name = '';
    }
    if (isset($aiomatic_Spinner_Settings['author_id'])) {
        $author_id = $aiomatic_Spinner_Settings['author_id'];
    } else {
        $author_id = '';
    }
    if (isset($aiomatic_Spinner_Settings['author_name'])) {
        $author_name = $aiomatic_Spinner_Settings['author_name'];
    } else {
        $author_name = '';
    }
    if (isset($aiomatic_Spinner_Settings['max_nr'])) {
        $max_nr = $aiomatic_Spinner_Settings['max_nr'];
    } else {
        $max_nr = '';
    }
    if (isset($aiomatic_Spinner_Settings['delay_request'])) {
        $delay_request = $aiomatic_Spinner_Settings['delay_request'];
    } else {
        $delay_request = '';
    }
    if (isset($aiomatic_Spinner_Settings['secret_word'])) {
        $secret_word = $aiomatic_Spinner_Settings['secret_word'];
    } else {
        $secret_word = '';
    }
    if (isset($aiomatic_Spinner_Settings['use_template_manual'])) {
        $use_template_manual = $aiomatic_Spinner_Settings['use_template_manual'];
    } else {
        $use_template_manual = '';
    }
    if (isset($aiomatic_Spinner_Settings['auto_edit'])) {
        $auto_edit = $aiomatic_Spinner_Settings['auto_edit'];
    } else {
        $auto_edit = 'disabled';
    }
    if (isset($aiomatic_Spinner_Settings['auto_run_interval'])) {
        $auto_run_interval = $aiomatic_Spinner_Settings['auto_run_interval'];
    } else {
        $auto_run_interval = 'No';
    }
    if (isset($aiomatic_Spinner_Settings['no_twice'])) {
        $no_twice = $aiomatic_Spinner_Settings['no_twice'];
    } else {
        $no_twice = '';
    }
    if (isset($aiomatic_Spinner_Settings['custom_name'])) {
        $custom_name = $aiomatic_Spinner_Settings['custom_name'];
    } else {
        $custom_name = 'aiomatic_published';
    }
    if (isset($_GET['settings-updated'])) {
?>
<div id="message" class="updated">
<p class="cr_saved_notif"><strong>&nbsp;<?php echo esc_html__('Settings saved.', 'aiomatic-automatic-ai-content-writer');?></strong></p>
</div>
<?php
$get = get_option('coderevolution_settings_changed', 0);
if($get == 1)
{
    delete_option('coderevolution_settings_changed');
?>
<div id="message" class="updated">
<p class="cr_failed_notif"><strong>&nbsp;<?php echo esc_html__('Plugin registration failed!', 'aiomatic-automatic-ai-content-writer');?></strong></p>
</div>
<?php 
}
elseif($get == 2)
{
        delete_option('coderevolution_settings_changed');
?>
<div id="message" class="updated">
<p class="cr_saved_notif"><strong>&nbsp;<?php echo esc_html__('Plugin registration successful!', 'aiomatic-automatic-ai-content-writer');?></strong></p>
</div>
<?php 
}
elseif($get != 0)
{
        delete_option('coderevolution_settings_changed');
?>
<div id="message" class="updated">
<p class="cr_failed_notif"><strong>&nbsp;<?php echo esc_html($get);?></strong></p>
</div>
<?php 
}
    }
?>
<div class="aiomatic_class">
<div id="tab-0" class="tab-content">     
    <br/>       
<h2><?php echo esc_html__('Welcome to Automatic AI Content Editing', 'aiomatic-automatic-ai-content-writer');?></h2>
<p>
<?php echo esc_html__('Welcome to this comprehensive guide on how to use the automatic post editing feature of the Aiomatic plugin. This powerful tool leverages artificial intelligence to automatically edit and enhance your WordPress posts, saving you time and effort while ensuring your content is optimized and engaging. Whether you\'re publishing new posts, drafting content, or revising existing posts, the Aiomatic plugin can be configured to automatically apply a range of edits. These include rewriting content, assigning featured images, appending or prepending AI-generated content, adding internal links, inserting related comments, and generating SEO meta descriptions.', 'aiomatic-automatic-ai-content-writer');?>
</p>
<p>
<?php echo esc_html__('In this tutorial, we will walk you through each step of setting up and using this feature, from installation and activation of the plugin, to configuring automatic and manual editing settings, to defining your editing templates and options, and finally, to adjusting advanced AI API settings for the editing process. By the end of this guide, you\'ll be able to harness the power of AI to streamline your content creation process and enhance the quality of your posts. Let\'s get started!', 'aiomatic-automatic-ai-content-writer');?>
</p>
<h2><?php echo esc_html__('"Automatic Content Editing Settings" Tab', 'aiomatic-automatic-ai-content-writer');?></h2>
<?php echo esc_html__('Here, you can set up the conditions for automatic post editing:', 'aiomatic-automatic-ai-content-writer');?>

<ul><li><?php echo esc_html__('When to edit posts: Choose whether you want posts to be edited when they are published, drafted, or set as pending.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('What post types to edit: Select the types of posts you want to be edited. This could be blog posts, pages, or any custom post types you have on your site.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('How long to wait before editing new posts: Set a delay for the editing process. This could be useful if you want to review the posts yourself before they are automatically edited.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('What categories or tags not to edit: If there are certain categories or tags you don\'t want to be edited, you can specify them here.', 'aiomatic-automatic-ai-content-writer');?>
</li></ul>
<h2><?php echo esc_html__('"Existing Content Editing" Tab', 'aiomatic-automatic-ai-content-writer');?></h2>

<?php echo esc_html__('In the \'Existing Content Editing\' tab, you can set up the conditions for manual post editing. This is useful for editing existing posts. You can set detailed filters on what posts/pages/custom post types to automatically edit.', 'aiomatic-automatic-ai-content-writer');?>

<h2><?php echo esc_html__('"Editing Template Manager" Tab', 'aiomatic-automatic-ai-content-writer');?></h2>

<?php echo esc_html__('In the \'Editing Template Manager\' tab, you can set how to edit posts. Here are the options:', 'aiomatic-automatic-ai-content-writer');?>
<ul><li>
<?php echo esc_html__('Enable AI Content Rewriting: This will enable the editing and rewriting of the content.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('Enable Featured Image Creation: This will automatically assign a featured image to the published content.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('Enable Featured Image Editing: This will automatically edit the current featured image of the post, based on a predefined prompt.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('Add AI Generated Content: This will automatically append or prepend AI generated content to posts.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('Automatically Add Internal Links: This will automatically add internal links to posts.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('Automatically Add Post Comments/Product Reviews: This will add related comments to posts or reviews to products.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('Automatically Add AI Generated SEO Description To Posts: This will automatically add SEO meta description for posts.', 'aiomatic-automatic-ai-content-writer');?>
</li><li>
<?php echo esc_html__('You can also change the status of the posts after they were edited, using the \'Change Post Status After Editing\' settings field.', 'aiomatic-automatic-ai-content-writer');?>
</li></ul>

<h2><?php echo esc_html__('General Tips', 'aiomatic-automatic-ai-content-writer');?></h2>
<?php echo esc_html__('Be sure to always save settings you change.', 'aiomatic-automatic-ai-content-writer');?>

<?php echo esc_html__('After you\'ve configured all the settings to your liking, make sure to click the "Save Changes" button at the bottom of the page.', 'aiomatic-automatic-ai-content-writer');?>

<?php echo esc_html__('And that\'s it! Your Aiomatic plugin is now set up to automatically edit your posts using AI. Remember, you can always go back and change these settings if you find that the automatic editing isn\'t working quite how you want it to.', 'aiomatic-automatic-ai-content-writer');?>
<?php if(!isset($aiomatic_Main_Settings['hide_videos']) || trim($aiomatic_Main_Settings['hide_videos']) != 'on'){
?>
<h2><?php echo esc_html__("AI Content Editor Tutorial Video", 'aiomatic-automatic-ai-content-writer');?></h2>
<p class="cr_center"><div class="embedtool"><iframe src="https://www.youtube.com/embed/WVccxtXQTcc" frameborder="0" allowfullscreen></iframe></div></p>
<h2><?php echo esc_html__("AI Content Editor Templates Tutorial Video", 'aiomatic-automatic-ai-content-writer');?></h2>
<p class="cr_center"><div class="embedtool"><iframe src="https://www.youtube.com/embed/pZ9EtV_t3gs" frameborder="0" allowfullscreen></iframe></div></p>
<?php
}
?>
</div>
<div id="tab-1" class="tab-content">
                    <table class="widefat">
                    <tr><td colspan="2">
                    <h2><?php echo esc_html__("AI Based Content Duplication Checking:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will check the content which is published on your site for duplication, using AI. Uses AI embeddings to analyze full content (not just titles), remove duplicates, and keep the most thorough articles. If a newly published post adds no value, delete it. If it does add more value than the previous post which was matched, replace the old post with the new one (delete the old post).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Enable AI Content Duplication Checking:", 'aiomatic-automatic-ai-content-writer');?></b>
                    <span class="new-feature-badge"><?php echo esc_html__("New", 'aiomatic-automatic-ai-content-writer'); ?></span>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_duplication" name="aiomatic_Spinner_Settings[ai_duplication]" onchange="mainChangedx();" >
                              <option value="enabled"<?php
                                 if ($ai_duplication == "enabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="disabled"<?php
                                 if ($ai_duplication == "disabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideDup"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select how to handle duplicated posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("How To Handle Duplicates:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="duplicate_handle" name="aiomatic_Spinner_Settings[duplicate_handle]">
                              <option value="keep_relevant"<?php
                                 if ($duplicate_handle == "keep_relevant") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Keep The More Relevant Article", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="keep_new"<?php
                                 if ($duplicate_handle == "keep_new") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Keep New", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="keep_old"<?php
                                if ($duplicate_handle == "keep_old") {
                                    echo " selected";
                                }
                                ?>><?php echo esc_html__("Keep Old", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div></td></tr><tr class="hideDup"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the action which will be done for the duplicated posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Duplicate Posts Action:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="duplicate_action" name="aiomatic_Spinner_Settings[duplicate_action]">
                              <option value="delete"<?php
                                 if ($duplicate_action == "delete") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Delete", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="thrash"<?php
                                 if ($duplicate_action == "thrash") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Thrash", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="nothing"<?php
                                if ($duplicate_action == "nothing") {
                                    echo " selected";
                                }
                                ?>><?php echo esc_html__("Only Log (No Action)", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div></td></tr>
        <tr class="hideDup">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to directly flag as duplicates also posts with matching titles? No AI based checking will be used in this case.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Directly Flag As Duplicates Posts Which Have The Exact Same Title:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="dup_titles" name="aiomatic_Spinner_Settings[dup_titles]"<?php
    if ($dup_titles == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideDup">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enhance duplicated articles using AI?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enhance Articles Content Using Data From The Duplicated Articles:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="enhance_articles" name="aiomatic_Spinner_Settings[enhance_articles]"<?php
    if ($enhance_articles == 'on')
        echo ' checked ';
?> onclick="enhanceChanged();">
            </td>
            </tr><tr class="hideDup hideEnhance"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Instruction for the AI editor, to edit the duplicated post content. You can use the following shortcodes: %%post_title_to_be_kept%%, %%post_content_to_be_kept%%, %%post_plain_text_content_to_be_kept%%, %%post_source_to_be_kept%%, %%post_title_to_be_removed%%, %%post_content_to_be_removed%%, %%post_plain_text_content_to_be_removed%%, %%post_source_to_be_removed%%", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enhance Duplicated Post Content Prompt:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_instruction_enhance]" placeholder="Please insert duplicated post content enhancer prompt"><?php
    echo esc_textarea($ai_instruction_enhance);
?></textarea>
        </div>
        </td></tr><tr class="hideDup hideEnhance">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="enhance_assistant_id" name="aiomatic_Spinner_Settings[enhance_assistant_id]" class="cr_width_full" onchange="assistantSelected('enhance_assistant_id', 'disableEnhance');">
    <?php
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
        if($enhance_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($enhance_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
        <tr class="hideDup hideEnhance">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI model to use for prompt enhancement.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model To Use For Prompt Enhancement:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="enhance_model" name="aiomatic_Spinner_Settings[enhance_model]" <?php if($enhance_assistant_id != ''){echo ' disabled';}?> class="disableEnhance">
<?php
foreach($all_edit_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($enhance_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . '</option>';
}
?>
            </select>
            </td>
            </tr>
            <tr class="hideDup"><td colspan="2">
                    <h3><?php echo esc_html__("Advanced Duplication Checking Settings:", 'aiomatic-automatic-ai-content-writer');?></h3>
                    
                    </td></tr>
                    <tr class="hideDup"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the template the plugin will use for duplication checking. You can include the post content, post title and other post data. You can use the following shortcodes: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%. Default is: %%post_content_plain_text%%", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Duplication Checking Template:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[duplication_template]" placeholder="%%post_content_plain_text%%"><?php
    echo esc_textarea($duplication_template);
?></textarea>
        </div>
        </td></tr>
            <tr class="hideDup"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the similarity threshold between 2 article contents for them to be classified as duplicates. You can set values between 1 and 0. 1 represents identical content, 0 repesents no matches in the content. Default is 0.9", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Matching Content Similarity Threashold:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="0.01" max="1" id="similarity_threshold" name="aiomatic_Spinner_Settings[similarity_threshold]" class="cr_450" value="<?php echo esc_html($similarity_threshold);?>" placeholder="0.9">
        </td></tr>
            <tr class="hideDup"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the maximum number of unindexed articles to process when running duplication checking. If you install the plugin on a site with a large number of articleas already published, if you publish a new article with this feature enabled, the plugin will index all existing articles and check them for similarity. This will happen only once per article. Here you can limit the number of articles the plugin should process at each published article. Default is 300", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Unindexed Articles To Process:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" id="max_to_process_in_one_go" name="aiomatic_Spinner_Settings[max_to_process_in_one_go]" class="cr_450" value="<?php echo esc_html($max_to_process_in_one_go);?>" placeholder="No limit">
        </td></tr><tr class="hideDup"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the name of the custom field you want to use for content duplication checking indexing. The default is: _aiomatic_index", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Custom Field Name For Indexing:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[duplication_index]" placeholder="_aiomatic_index"><?php
    echo esc_textarea($duplication_index);
?></textarea>
        </div>
        </td></tr>
        <tr><td colspan="2">
                    <h2><?php echo esc_html__("AI Content Editing Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will rewrite the textual content of the post, using AI.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Enable AI Content Rewriting:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_rewriter" name="aiomatic_Spinner_Settings[ai_rewriter]" onchange="mainChanged();" >
                              <option value="enabled"<?php
                                 if ($ai_rewriter == "enabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="disabled"<?php
                                 if ($ai_rewriter == "disabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr>
            <tr class="hideMain"><td colspan="2"><hr/></td></tr>
            <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enable post title editing?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Post Title Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select name="aiomatic_Spinner_Settings[no_title]" id="no_title" class="cr_width_full" onchange="titleChanged();">
                     <option value="yes"<?php
    if ($no_title == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($no_title == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
            </td>
            </tr><tr class="hideTitle">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to rewrite also post URL with the modified title?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Rewrite Also Post URL With The Modified Title:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="rewrite_url" name="aiomatic_Spinner_Settings[rewrite_url]"<?php
    if ($rewrite_url == 'on')
        echo ' checked ';
?>>
            </td>
            </tr><tr class="hideTitle"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Instruction for the AI editor, to edit post title. Please specify your instruction without adding the %%post_title%% shortcode, as the content will be automatically added at processing time.  Nested shortcodes from other plugins also supported here. You can also use the following shortcodes: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Instructions to Send For the AI Editor (Title Editing):", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_instruction_title]" placeholder="Please insert a title editor instruction"><?php
    echo esc_textarea($ai_instruction_title);
?></textarea>
        </div>
        </td></tr><tr class="hideTitle">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you don't want to auto add the title to the above AI prompt.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Auto Add The Title To The AI Prompt:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="no_add_title" name="aiomatic_Spinner_Settings[no_add_title]"<?php
    if ($no_add_title == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
            <tr class="hideMain"><td colspan="2"><hr/></td></tr>
            <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enable post content editing?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Post Content Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select name="aiomatic_Spinner_Settings[no_content]" id="no_content" class="cr_width_full" onchange="contentChanged();">
                     <option value="yes"<?php
    if ($no_content == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($no_content == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
            </td>
            </tr><tr class="hideContent">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enable Gutenberg block support and edit each Gutenberg block separately? If this is disabled, the who post content will be sent to the AI editor, and the result might not respect Gutenberg block structure.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Gutenberg Block Support (Will Run The Prompt Separately For Each Block):", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select name="aiomatic_Spinner_Settings[enable_gutenberg]" id="enable_gutenberg" class="cr_width_full" onchange="gutChanged();">
                     <option value="1"<?php
    if ($enable_gutenberg == '1')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="0"<?php
    if ($enable_gutenberg == '0')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
            </td>
            </tr><tr class="hideContent">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enable Elementor block support and edit each Elementor block separately? If this is disabled, Elementor Blocks will not be processed.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Elementor Editor Block Support (Will Run The Prompt Separately For Each Elementor Block):", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select name="aiomatic_Spinner_Settings[enable_elementor]" id="enable_elementor" class="cr_width_full">
                     <option value="1"<?php
    if ($enable_elementor == '1')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="0"<?php
    if ($enable_elementor == '0')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
            </td>
            </tr><tr class="hideContent hideGut">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enable editing of the below Gutenberg blocks only? If you leave this list empty, the plugin will automatically edit only default textual Gutenberg blocks.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Editing For These Gutenberg Blocks:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
<select name="aiomatic_Spinner_Settings[gutenberg_list][]" multiple class="cr_width_full ai_resize_vertical">
<?php
    $gutenberg_block_types = aiomatic_get_gutenberg_block_list();
    foreach ($gutenberg_block_types as $bname => $bid) 
    {
        $selected = '';
        if (isset($aiomatic_Spinner_Settings['gutenberg_list']) && !empty($aiomatic_Spinner_Settings['gutenberg_list'])) {
            $selected = in_array($bid, $aiomatic_Spinner_Settings['gutenberg_list']) ? 'selected' : '';
        }
        $name_arr = explode('~', $bname);
        if(isset($name_arr[1]) && $name_arr[1] != '')
        {
            $explicit_name = $name_arr[1];
        } 
        else
        {
            $explicit_name = $name_arr[0];
        }
?>
<option value="<?php echo esc_html($bid); ?>" <?php echo esc_html($selected); ?>>
    <?php echo esc_html(sanitize_text_field($explicit_name)); ?>
</option>
<?php
    }
?>
</select>
            </td>
            </tr><tr class="hideContent"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Instruction for the AI editor, to edit post content. Please specify your instruction without adding the %%post_content%% shortcode, as the content will be automatically added at processing time. Nested shortcodes from other plugins also supported here. You can also use the following shortcodes: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Instructions to Send For the AI Editor (Content Editing):", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_instruction]" placeholder="Please insert a content editor instruction"><?php
    echo esc_textarea($ai_instruction);
?></textarea>
        </div>
        </td></tr><tr class="hideContent">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you don't want to auto add the content to the above AI prompt.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Auto Add The Content To The AI Prompt:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="no_add_content" name="aiomatic_Spinner_Settings[no_add_content]"<?php
    if ($no_add_content == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
            <tr class="hideMain"><td colspan="2"><hr/></td></tr>
            <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enable post slug (URL) editing?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Post Slug Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select name="aiomatic_Spinner_Settings[no_slug]" id="no_slug" class="cr_width_full" onchange="slugChanged();">
                     <option value="yes"<?php
    if ($no_slug == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($no_slug == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
            </td>
            </tr><tr class="hideSlug"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Instruction for the AI editor, to edit post slug. Please specify your instruction without adding the %%post_slug%% shortcode, as the excerpt will be automatically added at processing time. Nested shortcodes from other plugins also supported here. You can also use the following shortcodes: %%post_slug%%, %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Instructions to Send For the AI Editor (Slug Editing):", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_instruction_slug]" placeholder="Please insert a slug editor instruction"><?php
    echo esc_textarea($ai_instruction_slug);
?></textarea>
        </div>
        </td></tr><tr class="hideSlug">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you don't want to auto add the slug to the above AI prompt.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Auto Add The Slug To The AI Prompt:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="no_add_slug" name="aiomatic_Spinner_Settings[no_add_slug]"<?php
    if ($no_add_slug == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideSlug"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the maximum length of the edited post slug (in characters).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Edited Post Slug Max Length (Optional):", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="1" step="1" id="max_slug_len" name="aiomatic_Spinner_Settings[max_slug_len]" class="cr_450" value="<?php echo esc_html($max_slug_len);?>" placeholder="Slug max length">
        </td></tr>
            <tr class="hideMain"><td colspan="2"><hr/></td></tr>
            <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to enable post excerpt editing?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Post Excerpt Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select name="aiomatic_Spinner_Settings[no_excerpt]" id="no_excerpt" class="cr_width_full" onchange="excerptChanged();">
                     <option value="yes"<?php
    if ($no_excerpt == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($no_excerpt == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
            </td>
            </tr><tr class="hideExcerpt"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Instruction for the AI editor, to edit post excerpt. Please specify your instruction without adding the %%post_excerpt%% shortcode, as the excerpt will be automatically added at processing time. Nested shortcodes from other plugins also supported here. You can also use the following shortcodes: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Instructions to Send For the AI Editor (Excerpt Editing):", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_instruction_excerpt]" placeholder="Please insert a excerpt editor instruction"><?php
    echo esc_textarea($ai_instruction_excerpt);
?></textarea>
        </div>
        </td></tr><tr class="hideExcerpt">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you don't want to auto add the excerpt to the above AI prompt.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Auto Add The Excerpt To The AI Prompt:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="no_add_excerpt" name="aiomatic_Spinner_Settings[no_add_excerpt]"<?php
    if ($no_add_excerpt == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
            <tr class="hideMain"><td colspan="2"><hr/></td></tr>
        <tr class="hideMain">
        <td colspan="2">
        <h3><?php echo esc_html__("Advanced Editing Options:", 'aiomatic-automatic-ai-content-writer');?></h3></td></tr>
        <tr class="hideMain">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="edit_assistant_id" name="aiomatic_Spinner_Settings[edit_assistant_id]" class="cr_width_full" onchange="assistantSelected('edit_assistant_id', 'disableEdit');">
    <?php
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
        if($edit_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($edit_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
        <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI model to use for post content editing.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model To Use For Post Content Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="edit_model" name="aiomatic_Spinner_Settings[edit_model]" <?php if($edit_assistant_id != ''){echo ' disabled';}?> class="disableEdit" onchange="visionSelectedAI();">
<?php
foreach($all_edit_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($edit_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . '</option>';
}
?>
            </select>
            </td>
            </tr>
            <tr class="hideMain hideVision">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision" name="aiomatic_Spinner_Settings[ai_vision]"<?php
    if ($ai_vision == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI model to use for post title editing.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model To Use For Post Title Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="title_model" name="aiomatic_Spinner_Settings[title_model]" <?php if($edit_assistant_id != ''){echo ' disabled';}?> class="disableEdit" onchange="visionTitleSelectedAI();">
<?php
foreach($all_edit_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($title_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . '</option>';
}
?>
            </select>
            </td>
            </tr>
            <tr class="hideMain hideTitleVision">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_title_vision" name="aiomatic_Spinner_Settings[ai_title_vision]"<?php
    if ($ai_title_vision == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI model to use for post excerpt editing.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model To Use For Post Excerpt Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="excerpt_model" name="aiomatic_Spinner_Settings[excerpt_model]" <?php if($edit_assistant_id != ''){echo ' disabled';}?> class="disableEdit" onchange="visionExcerptSelectedAI();">
<?php
foreach($all_edit_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($excerpt_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . '</option>';
}
?>
            </select>
            </td>
            </tr>
            <tr class="hideMain hideExcerptVision">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_excerpt_vision" name="aiomatic_Spinner_Settings[ai_excerpt_vision]"<?php
    if ($ai_excerpt_vision == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI model to use for post slug editing.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model To Use For Post Slug Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="slug_model" name="aiomatic_Spinner_Settings[slug_model]" <?php if($edit_assistant_id != ''){echo ' disabled';}?> class="disableEdit" onchange="visionSlugSelectedAI();">
<?php
foreach($all_edit_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($slug_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . '</option>';
}
?>
            </select>
            </td>
            </tr>
            <tr class="hideMain hideSlugVision">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_slug_vision" name="aiomatic_Spinner_Settings[ai_slug_vision]"<?php
    if ($ai_slug_vision == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
            <tr class="hideMain"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("What sampling temperature to use. Higher values means the model will take more risks. Try 0.9 for more creative applications, and 0 (argmax sampling) for ones with a well-defined answer. We generally recommend altering this or top_p but not both.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Content Editor Temperature:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="0.01" max="2" id="edit_temperature" name="aiomatic_Spinner_Settings[edit_temperature]" class="cr_450" value="<?php echo esc_html($edit_temperature);?>" placeholder="0">
        </td></tr><tr class="hideMain"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered. We generally recommend altering this or temperature but not both.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Content Editor Top_p:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="0.01" max="1" id="edit_top_p" name="aiomatic_Spinner_Settings[edit_top_p]" class="cr_450" value="<?php echo esc_html($edit_top_p);?>" placeholder="1">
        </td></tr><tr class="hideMain"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Number between -2.0 and 2.0. Positive values penalize new tokens based on whether they appear in the text so far, increasing the model's likelihood to talk about new topics.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Content Editor Presence Penalty:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="-2" step="0.01" max="2" id="edit_presence_penalty" name="aiomatic_Spinner_Settings[edit_presence_penalty]" class="cr_450" value="<?php echo esc_html($edit_presence_penalty);?>" placeholder="0">
        </td></tr><tr class="hideMain"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Number between -2.0 and 2.0. Positive values penalize new tokens based on their existing frequency in the text so far, decreasing the model's likelihood to repeat the same line verbatim.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Content Editor Frequency Penalty:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="-2" step="0.01" max="2" id="edit_frequency_penalty" name="aiomatic_Spinner_Settings[edit_frequency_penalty]" class="cr_450" value="<?php echo esc_html($edit_frequency_penalty);?>" placeholder="0">
        </td></tr><tr class="hideMain"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do you want to disable automatically editing of content longer than this character count?", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Disable Editing of Content Longer Than This Character Count:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="1" id="max_char" name="aiomatic_Spinner_Settings[max_char]" class="cr_450" value="<?php echo esc_html($max_char);?>" placeholder="Max editing character count">
        </td></tr><tr class="hideMain"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Currently, as the AI editor is in beta, it might have difficulties editing longer texts. If you encounter this issue, you can limit the chunk size which is sent to the AI editor (in characters). Leave this blank if editing works well in your case.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Character Chunk Size To Send To The AI Editor (Optional):", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="100" step="1" id="max_char_chunks" name="aiomatic_Spinner_Settings[max_char_chunks]" class="cr_450" value="<?php echo esc_html($max_char_chunks);?>" placeholder="Max character count">
        </td></tr>
            <tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Currently, because of an issue with the AI editor, sometimes it might remove parts of the HTML content you send to it for editing. The Aiomatic plugin can check if this happens and not change the post in these cases. If you check this checkbox, the edited content will be published, even if it misses some HTML tags. Do you want to publish edited content even if the AI editor removed some or all HTML content from the text?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Publish Edited Content Even if the AI Removed Parts of the HTML Text:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="no_html_check" name="aiomatic_Spinner_Settings[no_html_check]"<?php
    if ($no_html_check == 'on')
        echo ' checked ';
?>>
            </td>
            </tr><tr class="hideMain">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to protect HTML tags in edited text? This will add to the prompt you enter, a phrase which specifies to protect HTML tags from the edited text.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Protect HTML Tags in Edited Text:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="protect_html" name="aiomatic_Spinner_Settings[protect_html]"<?php
    if ($protect_html == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr><td colspan="2"><hr/></td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically edit the images found in the post content, using AI, based on the prompt you define in the settings.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Enable Post Content Image Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_featured_image_edit_content" name="aiomatic_Spinner_Settings[ai_featured_image_edit_content]" onchange="mainChanged2c();"  >
                              <option value="enabled"<?php
                                 if ($ai_featured_image_edit_content == "enabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="disabled"<?php
                                 if ($ai_featured_image_edit_content == "disabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideMain2c"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the engine which will be used for content image editing.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Content Image Editing Engine:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_featured_image_engine_content" name="aiomatic_Spinner_Settings[ai_featured_image_engine_content]" onchange="mainChangedImg();">
                              <option value="2"<?php
                                    if ($ai_featured_image_engine_content == "2") {
                                        echo " selected";
                                    }
                                    if (!isset($aiomatic_Main_Settings['stability_app_id']) || trim($aiomatic_Main_Settings['stability_app_id']) == '')
                                    {
                                        echo ' disabled title="You need to add a Stability.ai API key for this feature to work"';
                                    }
                                    ?>><?php echo esc_html__("Stability.AI", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideMain2c"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI content image editor. This command can be any given task or order, based on which, it will edit the featured image of the post. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). If you use Royalty Free Images as a source, you can also set their keywords here, if no keywords set, they will be automatically generated.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt To Send To The AI Content Image Editor:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_image_command_edit_content]" placeholder="Please insert a command for the AI content image editor"><?php
    echo esc_textarea($ai_image_command_edit_content);
?></textarea>
        </div>
        </td></tr><tr class="hideMain2c"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("How much influence the init_image has on the diffusion process. Values close to 1 will yield images very similar to the init_image while values close to 0 will yield images wildly different than the init_image. The behavior of this is meant to mirror DreamStudio's \"Image Strength\" slider. This parameter is just an alternate way to set step_schedule_start, which is done via the calculation 1 - image_strength. For example, passing in an Image Strength of 35% (0.35) would result in a step_schedule_start of 0.65.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Original Content Image Strength:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" max="1" step="0.01" id="image_strength_content" name="aiomatic_Spinner_Settings[image_strength_content]" class="cr_450" value="<?php echo esc_html($image_strength_content);?>" placeholder="Original content image strength">
        </td></tr>
        <tr class="hideMain2c"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Maximum number of images that should be edited from the post content. This is a protection mechanism, to not allow the plugin to edit too many images from a single post's content. To disable this feature, leave it blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Images To Edit From A Single Post Content:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="1" step="1" id="max_edit_content" name="aiomatic_Spinner_Settings[max_edit_content]" class="cr_450" value="<?php echo esc_html($max_edit_content);?>" placeholder="Maximum number of images to edit in a single post">
        </td></tr>
                    <tr><td colspan="2">
                    <h2><?php echo esc_html__("AI Generated Featured Image Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will generate AI generated or royalty free images, that will be assigned as featured images for posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Enable Featured Image Creation:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_featured_image" name="aiomatic_Spinner_Settings[ai_featured_image]" onchange="mainChanged2();"  >
                              <option value="enabled"<?php
                                 if ($ai_featured_image == "enabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="disabled"<?php
                                 if ($ai_featured_image == "disabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideMain2"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the source of the created featured images.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Featured Image Source:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_featured_image_source" name="aiomatic_Spinner_Settings[ai_featured_image_source]" onchange="mainChangedImg();">
                              <option value="1"<?php
                                 if ($ai_featured_image_source == "1") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("OpenAI/AiomaticAPI", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <?php
                                 if (isset($aiomatic_Main_Settings['stability_app_id']) && trim($aiomatic_Main_Settings['stability_app_id']) != '')
                                 {
                                 ?>
                                 <option value="2"<?php
                                    if ($ai_featured_image_source == "2") {
                                        echo " selected";
                                    }
                                    ?>><?php echo esc_html__("Stability.AI", 'aiomatic-automatic-ai-content-writer');?></option>
                                <?php
                                 }
                                 if (isset($aiomatic_Main_Settings['midjourney_app_id']) && trim($aiomatic_Main_Settings['midjourney_app_id']) != '')
                                 {
                                 ?>
                                 <option value="4"<?php
                                    if ($ai_featured_image_source == "4") {
                                        echo " selected";
                                    }
                                    ?>><?php echo esc_html__("Midjourney", 'aiomatic-automatic-ai-content-writer');?></option>
                                <?php
                                 }
                                 if (isset($aiomatic_Main_Settings['replicate_app_id']) && trim($aiomatic_Main_Settings['replicate_app_id']) != '')
                                 {
                                 ?>
                                 <option value="5"<?php
                                    if ($ai_featured_image_source == "5") {
                                        echo " selected";
                                    }
                                    ?>><?php echo esc_html__("Replicate", 'aiomatic-automatic-ai-content-writer');?></option>
                                <?php
                                 }
                                 if (isset($aiomatic_Main_Settings['ideogram_app_id']) && trim($aiomatic_Main_Settings['ideogram_app_id']) != '')
                                 {
                                 ?>
                                 <option value="6"<?php
                                    if ($ai_featured_image_source == "6") {
                                        echo " selected";
                                    }
                                    ?>><?php echo esc_html__("Ideogram", 'aiomatic-automatic-ai-content-writer');?></option>
                                <?php
                                 }
                                 if (isset($aiomatic_Main_Settings['app_id_google']) && trim($aiomatic_Main_Settings['app_id_google']) != '')
                                 {
                                 ?>
                                 <option value="7"<?php
                                    if ($ai_featured_image_source == "7") {
                                        echo " selected";
                                    }
                                    ?>><?php echo esc_html__("Google", 'aiomatic-automatic-ai-content-writer');?></option>
                                <?php
                                 }
                                 ?>
                              <option value="0"<?php
                                 if ($ai_featured_image_source == "0") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Royalty Free", 'aiomatic-automatic-ai-content-writer');?></option>
                                <option value="3"<?php
                                if ($ai_featured_image_source == "3") {
                                    echo " selected";
                                }
                                ?>><?php echo esc_html__("Manual URL List", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideMain2 hideMainAgain2"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI image generator. This command can be any given task or order, based on which, it will generate content for posts. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). If you use Royalty Free Images as a source, you can also set their keywords here, if no keywords set, they will be automatically generated.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt To Send To The AI Image Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_image_command]" placeholder="Please insert a command for the AI image generator"><?php
    echo esc_textarea($ai_image_command);
?></textarea>
        </div>
        </td></tr><tr class="hideMain2 hideMainAgain2"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the size of the generated featured image.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Generated Featured Image Size:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="image_size" name="aiomatic_Spinner_Settings[image_size]" >
                              <option value="256x256"<?php
                                 if ($image_size == "256x256") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("256x256 (only for Dall-E 2)", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="512x512"<?php
                                 if ($image_size == "512x512") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("512x512 (only for Dall-E 2 & Stable Diffusion)", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="1024x1024"<?php
                                 if ($image_size == "1024x1024") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("1024x1024", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="1024x1792"<?php
                                 if ($image_size == "1024x1792") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("1024x1792 (only for Dall-E 3)", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="1792x1024"<?php
                                 if ($image_size == "1792x1024") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("1792x1024 (only for Dall-E 3)", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="1024x1536"<?php
                                 if ($image_size == "1024x1536") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("1024x1536 (only for gpt-image-1)", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="1536x1024"<?php
                                 if ($image_size == "1536x1024") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("1536x1024 (only for gpt-image-1)", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr>
        <tr class="hideImg hideMainAgain2"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the size of the generated featured image.", 'aiomatic-automatic-ai-content-writer');
?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Image Model:", 'aiomatic-automatic-ai-content-writer');?></b>
            </div>
            </td><td>
            <div>
            <select id="image_model" name="aiomatic_Spinner_Settings[image_model]">
                <option value="dalle2"<?php
                    if ($image_model == "dalle2") {
                        echo " selected";
                    }
                    ?>><?php echo esc_html__("Dall-E 2", 'aiomatic-automatic-ai-content-writer');?></option>
                <option value="dalle3"<?php
                    if ($image_model == "dalle3") {
                        echo " selected";
                    }
                    ?>><?php echo esc_html__("Dall-E 3", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="dalle3hd"<?php
                    if ($image_model == "dalle3hd") {
                        echo " selected";
                    }
                    ?>><?php echo esc_html__("Dall-E 3 HD", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="gpt-image-1"<?php
                    if ($image_model == "gpt-image-1") {
                        echo " selected";
                    }
                    ?>><?php echo esc_html__("gpt-image-1", 'aiomatic-automatic-ai-content-writer');?></option>
            </select>
</div>
</td></tr><tr class="hideMain2 hideMainSecond2"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set a comma sepatated list of images to assign to posts. You can also use the AI to select the best matching image (basd on keywords from image name and URL).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Default Featured Image List:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[url_image_list]" placeholder="Image URL List"><?php
    echo esc_textarea($url_image_list);
?></textarea>
        </div>
        </td></tr>
        <tr><td colspan="2"><hr/></td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically edit the featured image of the post, using AI, based on the prompt you define in the settings.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Enable Featured Image Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_featured_image_edit" name="aiomatic_Spinner_Settings[ai_featured_image_edit]" onchange="mainChanged2e();"  >
                              <option value="enabled"<?php
                                 if ($ai_featured_image_edit == "enabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="disabled"<?php
                                 if ($ai_featured_image_edit == "disabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideMain2e"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the engine which will be used for image editing.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Featured Image Editing Engine:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="ai_featured_image_engine" name="aiomatic_Spinner_Settings[ai_featured_image_engine]" onchange="mainChangedImg();">
                              <option value="2"<?php
                                    if ($ai_featured_image_engine == "2") {
                                        echo " selected";
                                    }
                                    if (!isset($aiomatic_Main_Settings['stability_app_id']) || trim($aiomatic_Main_Settings['stability_app_id']) == '')
                                    {
                                        echo ' disabled title="You need to add a Stability.ai API key for this feature to work"';
                                    }
                                    ?>><?php echo esc_html__("Stability.AI", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideMain2e"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI image editor. This command can be any given task or order, based on which, it will edit the featured image of the post. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). If you use Royalty Free Images as a source, you can also set their keywords here, if no keywords set, they will be automatically generated.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt To Send To The AI Image Editor:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_image_command_edit]" placeholder="Please insert a command for the AI image editor"><?php
    echo esc_textarea($ai_image_command_edit);
?></textarea>
        </div>
        </td></tr><tr class="hideMain2e"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("How much influence the init_image has on the diffusion process. Values close to 1 will yield images very similar to the init_image while values close to 0 will yield images wildly different than the init_image. The behavior of this is meant to mirror DreamStudio's \"Image Strength\" slider. This parameter is just an alternate way to set step_schedule_start, which is done via the calculation 1 - image_strength. For example, passing in an Image Strength of 35% (0.35) would result in a step_schedule_start of 0.65.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Original Image Strength:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" max="1" step="0.01" id="image_strength" name="aiomatic_Spinner_Settings[image_strength]" class="cr_450" value="<?php echo esc_html($image_strength);?>" placeholder="Original image strength">
        </td></tr>
        <tr><td>
                    <h2><?php echo esc_html__("AI Content Completion Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will generate AI content, that will be prepended or appended to each post's content.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Add AI Generated Content:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="append_spintax" name="aiomatic_Spinner_Settings[append_spintax]" onchange="mainChanged3();" >
                              <option value="append"<?php
                                 if ($append_spintax == "append") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Append To The End", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="preppend"<?php
                                 if ($append_spintax == "preppend") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Prepend To The Beginning", 'aiomatic-automatic-ai-content-writer');?></option>
                                <option value="inside"<?php
                                 if ($append_spintax == "inside") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Inject Into Existing Content", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="disabled"<?php
                                 if ($append_spintax == "disabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr><tr class="hideMain3"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select where to add the AI generated content.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Where To Add The AI Generated Content:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="append_location" name="aiomatic_Spinner_Settings[append_location]" >
                              <option value="content"<?php
                                 if ($append_location == "content") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Post Content", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="title"<?php
                                 if ($append_location == "title") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Post Title", 'aiomatic-automatic-ai-content-writer');?></option>
                                <option value="excerpt"<?php
                                 if ($append_location == "excerpt") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Post Excerpt", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr>
                    <tr class="hideMain3"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI text generator. This command can be any given task or order, based on which, it will generate content for posts. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%first_content_paragraph_plain_text%%, %%last_content_paragraph_plain_text%%, %%first_content_paragraph%%, %%last_content_paragraph%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI Text Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_command]" placeholder="Please insert a command for the AI"><?php
    echo esc_textarea($ai_command);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain3">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="append_assistant_id" name="aiomatic_Spinner_Settings[append_assistant_id]" class="cr_width_full" onchange="assistantSelected('append_assistant_id', 'disableAppend');">
    <?php
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
        if($append_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($append_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
        <tr class="hideMain3"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the AI Model you want to use.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model To Use:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="model" name="aiomatic_Spinner_Settings[model]" <?php if($append_assistant_id != ''){echo ' disabled';}?> class="disableAppend" onchange="visionSelectedAI3();">
<?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
                           </select>
        </div>
                    </td></tr><tr class="hideMain3 hideVision3">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_add" name="aiomatic_Spinner_Settings[ai_vision_add]"<?php
    if ($ai_vision_add == 'on')
        echo ' checked ';
?>>
            </td>
            </tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Add your additional static content to prepend the AI generated content. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Additional Text To Prepend To The AI Generated Content:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                        
            <textarea rows="1" cols="70" name="aiomatic_Spinner_Settings[preppend_add]" placeholder="<?php echo esc_html__("Your optional text to prepend", 'aiomatic-automatic-ai-content-writer');?>" class="cr_width_full"><?php echo esc_textarea($preppend_add);?></textarea>
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Add your additional static content to append the AI generated content. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Additional Text To Append To The AI Generated Content:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                        
            <textarea rows="1" cols="70" name="aiomatic_Spinner_Settings[append_add]" placeholder="<?php echo esc_html__("Your optional text to append", 'aiomatic-automatic-ai-content-writer');?>" class="cr_width_full"><?php echo esc_textarea($append_add);?></textarea>
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the maximum number of API tokens to use with each request. This will define the length of the resulting API response. Each token usually consists of approximately 4 characters. Note that in this value the number of tokens sent to the API as an article prompt will also be counted. The maximum amount which can be set it 4000.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Total Token Count To Use Per API Request:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="1" step="1" id="max_tokens" name="aiomatic_Spinner_Settings[max_tokens]" class="cr_450" value="<?php echo esc_html($max_tokens);?>" placeholder="Maximum Token Count To Spend on Each Request">
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the maximum number of prompt API tokens to use with each request. This will define the length of the resulting API response. Each token usually consists of approximately 4 characters. This defines how much content does the API receive each time you call it. If the API gets more initial data, better quality results will be expected. The maximum amount which can be set it 1000.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Prompt Token Count To Use Per API Request:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="1" step="1" id="max_seed_tokens" name="aiomatic_Spinner_Settings[max_seed_tokens]" class="cr_450" value="<?php echo esc_html($max_seed_tokens);?>" placeholder="Maximum Prompt Token Count To Spend on Each Request">
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the maximum number of result API tokens to use with each request. This will define the length of the resulting API response. Each token usually consists of approximately 4 characters. This defines how much content does the API receive each time you call it. If the API gets more initial data, better quality results will be expected. The maximum amount which can be set it 2048.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Result Token Count To Use Per API Request:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="1" step="1" max="2048" id="max_result_tokens" name="aiomatic_Spinner_Settings[max_result_tokens]" class="cr_450" value="<?php echo esc_html($max_result_tokens);?>" placeholder="Maximum Result Token Count To Spend on Each Request">
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the maximum number of continue API tokens to use with each request. This will define the length of the resulting API response. Each token usually consists of approximately 4 characters. This defines how much content does the API receive each time you call it. If the API gets more initial data, better quality results will be expected. The maximum amount which can be set it 2048.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Continue Token Count To Use Per API Request:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="1" step="1" max="2048" id="max_continue_tokens" name="aiomatic_Spinner_Settings[max_continue_tokens]" class="cr_450" value="<?php echo esc_html($max_continue_tokens);?>" placeholder="Maximum Result Continue Count To Spend on Each Request">
        </td></tr><tr class="hideMain3"><td colspan="2">
                    <h2><?php echo esc_html__("Advanced API Settings:", 'aiomatic-automatic-ai-content-writer');?></h2>
                    
                    </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("What sampling temperature to use. Higher values means the model will take more risks. Try 0.9 for more creative applications, and 0 (argmax sampling) for ones with a well-defined answer. We generally recommend altering this or top_p but not both.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Content Writer Temperature:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="0.01" max="2" id="temperature" name="aiomatic_Spinner_Settings[temperature]" class="cr_450" value="<?php echo esc_html($temperature);?>" placeholder="1">
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered. We generally recommend altering this or temperature but not both.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Content Writer Top_p:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="0.01" max="1" id="top_p" name="aiomatic_Spinner_Settings[top_p]" class="cr_450" value="<?php echo esc_html($top_p);?>" placeholder="1">
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Number between -2.0 and 2.0. Positive values penalize new tokens based on whether they appear in the text so far, increasing the model's likelihood to talk about new topics.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Presence Penalty:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="-2" step="0.01" max="2" id="presence_penalty" name="aiomatic_Spinner_Settings[presence_penalty]" class="cr_450" value="<?php echo esc_html($presence_penalty);?>" placeholder="0">
        </td></tr><tr class="hideMain3"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Number between -2.0 and 2.0. Positive values penalize new tokens based on their existing frequency in the text so far, decreasing the model's likelihood to repeat the same line verbatim.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Frequency Penalty:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="-2" step="0.01" max="2" id="frequency_penalty" name="aiomatic_Spinner_Settings[frequency_penalty]" class="cr_450" value="<?php echo esc_html($frequency_penalty);?>" placeholder="0">
        </td></tr><tr class="hideMain3"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo sprintf( wp_kses( __( "Select the minimum number of characters that the content additional content should have. If the API returns content which has fewer characters than this number, another API call will be made, until this character limit is met. Please check about API rate limiting <a href='%s'>here</a>.", 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), 'https://beta.openai.com/docs/api-reference/introduction' );
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Created Content Minimum Character Count:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="number" min="1" step="1" name="aiomatic_Spinner_Settings[min_char]" value="<?php echo esc_html($min_char);?>" placeholder="Please insert a minimum number of characters for posts" class="cr_width_full">
        </div>
        </td></tr><tr class="hideMain3"><td colspan="2">
                    <h2><?php echo esc_html__("Rich Content Creation Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr>
        <tr class="hideMain3">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set the maximum number of related headings to add to the created post content. This feature will use the 'People Also Ask' feature from Google and Bing. By default, the Bing engine is scraped, if you want to enable also Google scraping, add a SerpAPI key in the plugin's 'Settings' menu -> 'SerpAPI API Key' settings field.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Related Headings to Add To The Content:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="number" min="0" name="aiomatic_Spinner_Settings[headings]" value="<?php echo esc_html($headings);?>" placeholder="Max heading count" class="cr_width_full">
            </td>
            </tr>
        <tr class="hideMain3">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="headings_assistant_id" name="aiomatic_Spinner_Settings[headings_assistant_id]" class="cr_width_full" onchange="assistantSelected('headings_assistant_id', 'disableHeadings');">
    <?php
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
        if($headings_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($headings_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
            <tr class="hideMain3">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for headings generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The Headings Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="headings_model" name="aiomatic_Spinner_Settings[headings_model]" <?php if($headings_assistant_id != ''){echo ' disabled';}?> class="cr_width_full disableHeadings">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($headings_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain3">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set the prompt you will use when searching for related headings. You can use the following shortcodes: %%post_title%%, %%needed_heading_count%%. The same model will be used, as the one selected for content creation. If you leave this field blank, the default prompt will be used: 'Write %%needed_heading_count%% PAA related questions, each on a new line, for the title: %%post_title%%'", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI Related Headings Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <textarea rows="2" cols="70" name="aiomatic_Spinner_Settings[headings_ai_command]" placeholder="Write %%needed_heading_count%% PAA related questions, each on a new line, for the title: %%post_title%%" class="cr_width_full"><?php echo esc_textarea($headings_ai_command);?></textarea>
            </td>
            </tr>
            <tr class="hideMain3">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set the maximum number of related images to add to the created post content. This feature will use the 'Royalty Free Image' settings from the plugin's 'Settings' menu.'", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Related Images to Add To The Content:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="number" min="0" name="aiomatic_Spinner_Settings[images]" value="<?php echo esc_html($images);?>" placeholder="Max image count" class="cr_width_full">
            </td>
            </tr>
            <tr class="hideMain3">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do you want to replace the royalty free image with an AI generated image?", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Image Source:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="enable_ai_images" name="aiomatic_Spinner_Settings[enable_ai_images]" class="cr_width_full">
            <option value="1"<?php
    if ($enable_ai_images == '1' || $enable_ai_images == 'on')
        echo ' selected ';
?>><?php echo esc_html__("OpenAI/AiomaticAPI", 'aiomatic-automatic-ai-content-writer');?></option>
            <?php
            if (isset($aiomatic_Main_Settings['stability_app_id']) && trim($aiomatic_Main_Settings['stability_app_id']) != '')
            {
            ?>
            <option value="2"<?php
    if ($enable_ai_images == '2')
        echo ' selected ';
?>><?php echo esc_html__("Stability.AI", 'aiomatic-automatic-ai-content-writer');?></option>
            <?php
            }
            if (isset($aiomatic_Main_Settings['midjourney_app_id']) && trim($aiomatic_Main_Settings['midjourney_app_id']) != '')
            {
            ?>
            <option value="3"<?php
    if ($enable_ai_images == '3')
        echo ' selected ';
?>><?php echo esc_html__("Midjourney", 'aiomatic-automatic-ai-content-writer');?></option>
            <?php
            }
            if (isset($aiomatic_Main_Settings['replicate_app_id']) && trim($aiomatic_Main_Settings['replicate_app_id']) != '')
            {
            ?>
            <option value="4"<?php
    if ($enable_ai_images == '4')
        echo ' selected ';
?>><?php echo esc_html__("Replicate", 'aiomatic-automatic-ai-content-writer');?></option>
            <?php
            }
            if (isset($aiomatic_Main_Settings['ideogram_app_id']) && trim($aiomatic_Main_Settings['ideogram_app_id']) != '')
            {
            ?>
            <option value="5"<?php
    if ($enable_ai_images == '5')
        echo ' selected ';
?>><?php echo esc_html__("Ideogram", 'aiomatic-automatic-ai-content-writer');?></option>
            <?php
            }
            if (isset($aiomatic_Main_Settings['app_id_google']) && trim($aiomatic_Main_Settings['app_id_google']) != '')
            {
            ?>
            <option value="6"<?php
    if ($enable_ai_images == '6')
        echo ' selected ';
?>><?php echo esc_html__("Google", 'aiomatic-automatic-ai-content-writer');?></option>
            <?php
            }
            ?>
            <option value="0"<?php
    if ($enable_ai_images == '0' || $enable_ai_images == '')
        echo ' selected ';
?>><?php echo esc_html__("Royalty Free", 'aiomatic-automatic-ai-content-writer');?></option>
        </select>
            </td>
            </tr>
            <tr class="hideMain3">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Add related social embeds to the created post content. This feature will require you to add at least one YouTube API key in the plugin's 'Settings' -> 'YouTube API Key List' settings field.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Add Related Rich Embeds To The Post:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="videos" name="aiomatic_Spinner_Settings[videos]"<?php
    if ($videos == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
            <tr class="hideMain3">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the location where you want to add the rich embed in the edited content.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Rich Embed Location:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="rich_embed_location" name="aiomatic_Spinner_Settings[rich_embed_location]" class="cr_width_full">
            <option value="bottom"<?php
    if ($rich_embed_location == '' || $rich_embed_location == 'bottom')
        echo ' selected ';
?>><?php echo esc_html__("Bottom", 'aiomatic-automatic-ai-content-writer');?></option>
            <option value="top"<?php
                if ($rich_embed_location == 'top')
                    echo ' selected ';
            ?>><?php echo esc_html__("Top", 'aiomatic-automatic-ai-content-writer');?></option>
            <option value="inside"<?php
                if ($rich_embed_location == 'inside')
                    echo ' selected ';
            ?>><?php echo esc_html__("Randomly replace images", 'aiomatic-automatic-ai-content-writer');?></option>
        </select>
            </td>
            </tr>
         <tr class="hideMain3">
            <th>
               <div>
                  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                     <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                           echo esc_html__("Set the list of social networks to get embeds from, in AI created articles. If you leave this blank, YouTube videos will be imported by default.", 'aiomatic-automatic-ai-content-writer');
                           ?>
                     </div>
                  </div>
                  <b><?php echo esc_html__("Get Rich Embeds In Articles From These Social Networks:", 'aiomatic-automatic-ai-content-writer');?></b>
               </div>
            </th>
            <td>
               <div>
                  <select autocomplete="off" id="social_list" multiple name="aiomatic_Spinner_Settings[social_list][]" class="cr_width_full">
                  <option value="youtube" <?php
                  if (empty($social_list) || in_array('youtube', $social_list)) 
                  {
                        echo ' selected';
                  }
                  ?>
               ><?php echo esc_html__("YouTube", 'aiomatic-automatic-ai-content-writer');?></option>
                  <option value="twitter" <?php
                  if (!is_plugin_active('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php')) 
                  {
                     echo ' disabled title="' . esc_html__("Please install and activate the X-omatic plugin for this feature to be activated.", 'aiomatic-automatic-ai-content-writer'). '"';
                  }
                  else
                  {
                     if (in_array('twitter', $social_list)) 
                     {
                           echo ' selected';
                     }
                  }
                  ?>
               ><?php echo esc_html__("X (Twitter)", 'aiomatic-automatic-ai-content-writer');?></option>
                  <option value="instagram"
               <?php
                  if (!is_plugin_active('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php')) 
                  {
                     echo ' disabled title="' . esc_html__("Please install and activate the iMediamatic plugin for this feature to be activated.", 'aiomatic-automatic-ai-content-writer'). '"';
                  }
                  else
                  {
                     if (in_array('instagram', $social_list)) 
                     {
                           echo ' selected';
                     }
                  }
                  ?>
               ><?php echo esc_html__("Instagram", 'aiomatic-automatic-ai-content-writer');?></option>
                  <option value="pinterest"
               <?php
                  if (!is_plugin_active('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php')) 
                  {
                     echo ' disabled title="' . esc_html__("Please install and activate the Pinterestomatic plugin for this feature to be activated.", 'aiomatic-automatic-ai-content-writer'). '"';
                  }
                  else
                  {
                     if (in_array('pinterest', $social_list)) 
                     {
                           echo ' selected';
                     }
                  }
                  ?>
               ><?php echo esc_html__("Pinterest", 'aiomatic-automatic-ai-content-writer');?></option>
               </select>
               </div>
            </td>
         </tr>
        <tr><td colspan="2"><hr/></td></tr>
            <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will generate a table of contents for edited posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Add Table Of Contents To Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="append_toc" name="aiomatic_Spinner_Settings[append_toc]" onchange="mainChanged9();" >
                              <option value="preppend"<?php
                                 if ($append_toc == "preppend") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Prepend To The Beginning", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="append"<?php
                                 if ($append_toc == "append") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Append To The End", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="heading"<?php
                                 if ($append_toc == "heading") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Before First Heading", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="heading2"<?php
                                 if ($append_toc == "heading2") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("After First Heading", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="disabled"<?php
                                 if (empty($append_toc) || $append_toc == "disabled") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr>
                    <tr class="hideMain9"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select when do you want to show the ToC in edited posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("When To Show The Table Of Contents:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="when_toc" name="aiomatic_Spinner_Settings[when_toc]" >
                              <option value="2"<?php
                                 if ($when_toc == "2") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("2 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="3"<?php
                                 if ($when_toc == "3") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("3 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="4"<?php
                                 if (empty($when_toc) || $when_toc == "4") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("4 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="5"<?php
                                 if ($when_toc == "5") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("5 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="6"<?php
                                 if ($when_toc == "6") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("6 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="7"<?php
                                 if ($when_toc == "7") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("7 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="8"<?php
                                 if ($when_toc == "8") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("8 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="9"<?php
                                 if ($when_toc == "9") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("9 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="10"<?php
                                 if ($when_toc == "10") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("10 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="11"<?php
                                 if ($when_toc == "11") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("11 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                                 <option value="12"<?php
                                 if ($when_toc == "12") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("12 Or More Headings", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr>
        <tr class="hideMain9"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the title to show on top of the table of contents.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Table Of Contents Title:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="text" id="title_toc" name="aiomatic_Spinner_Settings[title_toc]" placeholder="Table of Contents" class="cr_width_full" value="<?php echo esc_attr($title_toc);?>">
        </div>
        </td></tr>
        <tr class="hideMain9"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to show headings hierarchically.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Show Hierarchical Table of Contents:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="checkbox" id="hierarchy_toc" name="aiomatic_Spinner_Settings[hierarchy_toc]"<?php
    if ($hierarchy_toc == 'on')
        echo ' checked ';
?>>
        </div>
        </td></tr>
        <tr class="hideMain9"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to add numbers to list items from the Table of Contents.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Add Numbers To List Items:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="checkbox" id="add_numbers_toc" name="aiomatic_Spinner_Settings[add_numbers_toc]"<?php
    if ($add_numbers_toc == 'on')
        echo ' checked ';
?>>
        </div>
        </td></tr>
        <tr class="hideMain9"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Include the following heading levels. Deselecting a heading will exclude it.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Content Heading Levels To Include:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="checkbox" id="heading_levels1" name="aiomatic_Spinner_Settings[heading_levels1]"<?php
    if ($heading_levels1 == 'on')
        echo ' checked ';
?>><label for="heading_levels1"><?php echo esc_html__('Heading 1 - h1', 'aiomatic-automatic-ai-content-writer');?></label><br/>
<input type="checkbox" id="heading_levels2" name="aiomatic_Spinner_Settings[heading_levels2]"<?php
if ($heading_levels2 == 'on')
echo ' checked ';
?>><label for="heading_levels2"><?php echo esc_html__('Heading 2 - h2', 'aiomatic-automatic-ai-content-writer');?></label><br/>
<input type="checkbox" id="heading_levels3" name="aiomatic_Spinner_Settings[heading_levels3]"<?php
if ($heading_levels3 == 'on')
echo ' checked ';
?>><label for="heading_levels3"><?php echo esc_html__('Heading 3 - h3', 'aiomatic-automatic-ai-content-writer');?></label><br/>
<input type="checkbox" id="heading_levels4" name="aiomatic_Spinner_Settings[heading_levels4]"<?php
if ($heading_levels4 == 'on')
echo ' checked ';
?>><label for="heading_levels4"><?php echo esc_html__('Heading 4 - h4', 'aiomatic-automatic-ai-content-writer');?></label><br/>
<input type="checkbox" id="heading_levels5" name="aiomatic_Spinner_Settings[heading_levels5]"<?php
if ($heading_levels5 == 'on')
echo ' checked ';
?>><label for="heading_levels5"><?php echo esc_html__('Heading 5 - h5', 'aiomatic-automatic-ai-content-writer');?></label><br/>
<input type="checkbox" id="heading_levels6" name="aiomatic_Spinner_Settings[heading_levels6]"<?php
if ($heading_levels6 == 'on')
echo ' checked ';
?>><label for="heading_levels6"><?php echo esc_html__('Heading 6 - h6', 'aiomatic-automatic-ai-content-writer');?></label><br/>
        </div>
        </td></tr>
        <tr class="hideMain9"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Specify headings to be excluded from appearing in the table of contents. Separate multiple headings with a pipe |. Use an asterisk * as a wildcard to match other text. Note that this is not case sensitive. Some examples: Fruit* ignore headings starting with \"Fruit\" *Fruit Diet* ignore headings with \"Fruit Diet\" somewhere in the heading Apple Tree|Oranges|Yellow Bananas ignore headings that are exactly \"Apple Tree\", \"Oranges\" or \"Yellow Bananas\"", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Excluded Headings Patterns:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
            <textarea rows="1" cols="70" name="aiomatic_Spinner_Settings[exclude_toc]" placeholder="Headings to exclude" class="cr_width_full"><?php echo esc_textarea($exclude_toc);?></textarea>
        </div>
        </td></tr>
            <tr class="hideMain9"><td>
            <div>
<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Select the direction in which you want to float the Table of Contents.", 'aiomatic-automatic-ai-content-writer');
?>
                </div>
            </div>
            <b><?php echo esc_html__("Table Of Contents Position:", 'aiomatic-automatic-ai-content-writer');?></b>
            </div>
            </td><td>
            <div>
            <select id="float_toc" name="aiomatic_Spinner_Settings[float_toc]" >
            <option value="none"<?php
                if (empty($float_toc) || $float_toc == "none") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("None", 'aiomatic-automatic-ai-content-writer');?></option>
                <option value="left"<?php
                if ($float_toc == "left") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("Left", 'aiomatic-automatic-ai-content-writer');?></option>
                <option value="right"<?php
                if ($float_toc == "right") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("Right", 'aiomatic-automatic-ai-content-writer');?></option>
        </select>
        </div>
        </td></tr>
            <tr class="hideMain9"><td>
            <div>
<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <div class="bws_hidden_help_text cr_min_260px">
<?php
echo esc_html__("Select the color theme for the Table of Contents.", 'aiomatic-automatic-ai-content-writer');
?>
                </div>
            </div>
            <b><?php echo esc_html__("Table Of Contents Color Theme:", 'aiomatic-automatic-ai-content-writer');?></b>
            </div>
            </td><td>
            <div>
            <select id="color_toc" name="aiomatic_Spinner_Settings[color_toc]" >
            <option value="transparent"<?php
                if ($color_toc == "transparent") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("Transparent", 'aiomatic-automatic-ai-content-writer');?></option>
                <option value="gray"<?php
                if (empty($color_toc) || $color_toc == "gray") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("Gray", 'aiomatic-automatic-ai-content-writer');?></option>
                <option value="blue"<?php
                if ($color_toc == "blue") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("Light Blue", 'aiomatic-automatic-ai-content-writer');?></option>
                <option value="white"<?php
                if ($color_toc == "white") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("White", 'aiomatic-automatic-ai-content-writer');?></option>
                <option value="black"<?php
                if ($color_toc == "black") {
                    echo " selected";
                }
                ?>><?php echo esc_html__("Black", 'aiomatic-automatic-ai-content-writer');?></option>
        </select>
        </div>
        </td></tr>
            <tr><td>
                    <h2><?php echo esc_html__("Post Content Automatic Linking Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically add automatic links to other posts from your site, to keywords from each post.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Automatically Add Links To Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="add_links" name="aiomatic_Spinner_Settings[add_links]" onchange="mainChanged4();" >
                        <option value="disabled"<?php
                            if ($add_links == "disabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="enabled"<?php
                            if ($add_links == "enabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr class="hideMain4">
            <td class="cr_min_width_200">
                <div>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the automatic linking method you want to use in the edited content. You can choose between Aiomatic's built-in method and using the Internal Link Juicer plugin (in which case, Aiomatic will create keywords which will be able to be used by Internal Link Juicer, for internal linking).", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Internal Linking Method To Use:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
                    <select id="link_method" name="aiomatic_Spinner_Settings[link_method]" onchange="mainChanged4();">
                        <option value="aiomatic"<?php
if(!function_exists('is_plugin_active'))
{
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$linkjuicer_active = true;
if (!is_plugin_active('internal-links/wp-internal-linkjuicer.php') && !is_plugin_active('internal-links-premium/wp-internal-linkjuicer.php')) 
{
    $linkjuicer_active = false;
}
                            if (empty($link_method) || $link_method == "aiomatic" || $linkjuicer_active == false) {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Aiomatic's Built-in Method", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="linkjuicer"<?php
                            if ($link_method == "linkjuicer" && $linkjuicer_active != false) {
                                echo " selected";
                            }
                            
if($linkjuicer_active === false)
{
    echo ' disabled title="You need to install the Internal Link Juicer plugin for this option to be active!"'; 
}
                            ?>><?php echo esc_html__("Internal Link Juicer plugin", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
            </td>
        </tr>
        <tr class="hideMain4l"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the Internal Link Juicer Keyword Extractor. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The Internal Link Juicer Keyword Extractor:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[link_juicer_prompt]" placeholder="Generate a comma-separated list of relevant keywords for the post title: '%%post_title%%'."><?php
    echo esc_textarea($link_juicer_prompt);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain4l">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="link_juicer_assistant_id" name="aiomatic_Spinner_Settings[link_juicer_assistant_id]" class="cr_width_full" onchange="assistantSelected('link_juicer_assistant_id', 'disableLinkJuicer');">
    <?php
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
        if($link_juicer_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($link_juicer_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
            <tr class="hideMain4l">
            <td>
                <div>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for categories generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The Categories Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="link_juicer_model" name="aiomatic_Spinner_Settings[link_juicer_model]" <?php if($link_juicer_assistant_id != ''){echo ' disabled';}?> class="disableLinkJuicer cr_width_full" onchange="visionSelectedAI9();">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($link_juicer_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain4l hideVision9">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_link_juicer" name="aiomatic_Spinner_Settings[ai_vision_link_juicer]"<?php
    if ($ai_vision_link_juicer == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideMain4a">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set the maximum number of automatic links to add to created posts. You can also define custom ranges, like: 3-5. Please note that this feature will work best if you already have a considerable number of posts published on your site, which will be used for internal linking. The default value for this settings field is 3-5", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Automatic Links To Add To The Post Content:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="max_links" name="aiomatic_Spinner_Settings[max_links]" placeholder="3-5" class="cr_width_full" value="<?php echo esc_attr($max_links);?>">
            </td>
        </tr>
        <tr class="hideMain4a">
        <td>
                <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                    <div class="bws_hidden_help_text cr_min_260px">
                    <?php
                        echo esc_html__("Select the linking method to use in posts.", 'aiomatic-automatic-ai-content-writer');
                        ?>
                    </div>
                </div>
                <b><?php echo esc_html__("Automatic Linking Type:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td>
        <select autocomplete="off" class="cr_width_full" id="link_type" onchange="hideLinks();" name="aiomatic_Spinner_Settings[link_type]">
        <option value="internal"<?php
                            if ($link_type == "internal") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Internal Links", 'aiomatic-automatic-ai-content-writer');?></option>
        <option value="manual"<?php
                            if ($link_type == "manual") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Manual Links", 'aiomatic-automatic-ai-content-writer');?></option>
        <option value="serp"<?php
                            if ($link_type == "serp") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Related SERP Links", 'aiomatic-automatic-ai-content-writer');?></option>
        <option value="mixed"<?php
                            if ($link_type == "mixed") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Internal & Manual Links", 'aiomatic-automatic-ai-content-writer');?></option>
                            <option value="intserp"<?php
                            if ($link_type == "intserp") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Internal & SERP Links", 'aiomatic-automatic-ai-content-writer');?></option>
                            <option value="manserp"<?php
                            if ($link_type == "manserp") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Manual & SERP Links", 'aiomatic-automatic-ai-content-writer');?></option>
                            <option value="intmanserp"<?php
                            if ($link_type == "intmanserp") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Internal & Manual & SERP Links", 'aiomatic-automatic-ai-content-writer');?></option>
                            <option value="amazon"<?php
                            if ($link_type == "amazon") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Amazon Related Products", 'aiomatic-automatic-ai-content-writer');?></option>
        </select>   
        </td>
        </tr>
        <tr class="hideMain4a hidelinksamazon hidelinks">
        <td class="cr_min_width_200">
                <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                    <div class="bws_hidden_help_text cr_min_260px">
                    <?php
                        echo esc_html__("Enter your Amazon Affiliate ID.", 'aiomatic-automatic-ai-content-writer');
                        ?>
                    </div>
                </div>
                <b><?php echo esc_html__("Amazon Affiliate ID:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td>
            <input type="text" name="aiomatic_Spinner_Settings[affiliate_id]" value="<?php echo esc_attr($affiliate_id);?>" placeholder="Please insert your Amazon Affiliate ID" class="cr_width_full">   
        </td>
        </tr>
        <tr class="hideMain4a hidelinksamazon hidelinks">
        <td class="cr_min_width_200">
                <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                    <div class="bws_hidden_help_text cr_min_260px">
                    <?php
                        echo esc_html__("Enter your Amazon target country.", 'aiomatic-automatic-ai-content-writer');
                        ?>
                    </div>
                </div>
                <b><?php echo esc_html__("Amazon Target Country:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td>
            <select name="aiomatic_Spinner_Settings[target_country]" class="cr_width_full">
            <?php
            $amaz_countries = aiomatic_get_amazon_codes();
            foreach ($amaz_countries as $key => $value) {
                echo '<option value="' . esc_html($key) . '"';
                if($target_country == $key)
                {
                    echo ' selected';
                }
                echo '>' . esc_html($value) . '</option>';
            }
            ?>
            </select>  
        </td>
        </tr>
        <tr class="hideMain4a hidelinksmanual hidelinks">
        <td class="cr_min_width_200">
                <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                    <div class="bws_hidden_help_text cr_min_260px">
                    <?php
                        echo esc_html__("Enter a manual list of links, where the plugin will create links.", 'aiomatic-automatic-ai-content-writer');
                        ?>
                    </div>
                </div>
                <b><?php echo esc_html__("Manual List Of URLs (One Per Line):", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td>
        <textarea rows="1" cols="70" name="aiomatic_Spinner_Settings[link_list]" placeholder="URL list (one per line)" class="cr_width_full"><?php echo esc_textarea($link_list);?></textarea>
        </td>
        </tr>
        <tr class="hideMain4 hidelinksexternal hidelinks">
        <td class="cr_min_width_200">
                <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                    <div class="bws_hidden_help_text cr_min_260px">
                    <?php
                        echo esc_html__("Do you want to add nofollow attribute to manually entered, external links?", 'aiomatic-automatic-ai-content-writer');
                        ?>
                    </div>
                </div>
                <b><?php echo esc_html__("Add Nofollow Attribute To External Links:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td>
                    <input type="checkbox" id="link_nofollow" name="aiomatic_Spinner_Settings[link_nofollow]"<?php
    if ($link_nofollow == 'on')
        echo ' checked ';
?>>
        </td>
        </tr>
        <tr class="hideMain4a hidelinksinternal hidelinks">
            <td class="cr_min_width_200">
                <div>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set the post types where to create automatic links in posts. You can also add a comma separated list of multiple post types.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Post Types Where To Generate Inboud Links:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="link_post_types" name="aiomatic_Spinner_Settings[link_post_types]" placeholder="post" class="cr_width_full" value="<?php echo esc_attr($link_post_types);?>">
            </td>
        </tr>
        <tr class="hideMain4a hidelinksinternal hidelinks">
            <td class="cr_min_width_200">
                <div>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Allow creating internal links only to this list of post IDs. Add a comma separated list of post ids.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Allow Inbound Links Only To These Post IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="link_post_ids" name="aiomatic_Spinner_Settings[link_post_ids]" placeholder="postID1,postID2,postID3" class="cr_width_full" value="<?php echo esc_attr($link_post_ids);?>">
            </td>
        </tr>
        <tr class="hideMain4a hidelinksinternal hidelinks">
            <td class="cr_min_width_200">
                <div>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Do not allow creating internal links to this list of post IDs. Add a comma separated list of post ids.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Allow Inbound Links To These Post IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="link_post_not_ids" name="aiomatic_Spinner_Settings[link_post_not_ids]" placeholder="postID1,postID2,postID3" class="cr_width_full" value="<?php echo esc_attr($link_post_not_ids);?>">
            </td>
        </tr>
        <tr class="hideMain4a">
            <td class="cr_min_width_200">
                <div>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set a comma separated list of keywords which will be forced to be used in keyword creation. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Keyword List To Force To Be Used:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="force_keywords" name="aiomatic_Spinner_Settings[force_keywords]" placeholder="keyword1,keyword2,keyword3" class="cr_width_full" value="<?php echo esc_attr($force_keywords);?>">
            </td>
        </tr>
        <tr><td>
                    <h2><?php echo esc_html__("Post Automatic Categories Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically add categories to posts from your site.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Automatically Add Categories To Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="add_cats" name="aiomatic_Spinner_Settings[add_cats]" onchange="mainChanged7();" >
                        <option value="disabled"<?php
                            if ($add_cats == "disabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="enabled"<?php
                            if ($add_cats == "enabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr class="hideMain7">
            <td class="cr_min_width_200">
                <div>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Maximum number of categories to add. You can also use value ranges, like: 3-5. The default value is 1-2", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Categories To Add To The Post:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="max_cats" name="aiomatic_Spinner_Settings[max_cats]" placeholder="1-2" class="cr_width_full" value="<?php echo esc_attr($max_cats);?>">
            </td>
        </tr>
        <tr class="hideMain7"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("This option will make the plugin not create categories which are not already existing on your site. For best results in this case, be sure to add to the prompt the list of categories from where the AI should select.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Add Inexistent Categories:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="checkbox" id="skip_inexist" name="aiomatic_Spinner_Settings[skip_inexist]"<?php
    if ($skip_inexist == 'on')
        echo ' checked ';
?>>
        </td></tr>
        <tr class="hideMain7"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI category generator. This command can be any given task or order, based on which, it will generate categories for posts. You can use the following shortcodes here: %%all_categories%%, %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI Category Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_cats]" placeholder="Write a comma separated list of 5 categories for post title: %%post_title%%"><?php
    echo esc_textarea($ai_cats);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain7">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="categories_assistant_id" name="aiomatic_Spinner_Settings[categories_assistant_id]" class="cr_width_full" onchange="assistantSelected('categories_assistant_id', 'disableCategories');">
    <?php
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
        if($categories_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($categories_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
            <tr class="hideMain7">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for categories generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The Categories Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="cats_model" name="aiomatic_Spinner_Settings[cats_model]" <?php if($categories_assistant_id != ''){echo ' disabled';}?> class="disableCategories cr_width_full" onchange="visionSelectedAI7();">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($cats_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain7 hideVision7">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_cat" name="aiomatic_Spinner_Settings[ai_vision_cat]"<?php
    if ($ai_vision_cat == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideMain7"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("If you want to assign categories to custom taxonomies of custom post types, you can set the taxonomy name here.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Custom Taxonomy Name For Categories:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="text" id="cats_tax" name="aiomatic_Spinner_Settings[cats_tax]" placeholder="category" class="cr_width_full" value="<?php echo esc_attr($cats_tax);?>">
        </div>
        </td></tr>
        <tr><td>
                    <h2><?php echo esc_html__("Post Automatic Tags Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically add tags to posts from your site.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Automatically Add Tags To Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="add_tags" name="aiomatic_Spinner_Settings[add_tags]" onchange="mainChanged8();" >
                        <option value="disabled"<?php
                            if ($add_tags == "disabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="enabled"<?php
                            if ($add_tags == "enabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr class="hideMain8">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Maximum number of tags to add. You can also use value ranges, like: 3-5. The default value is 1-2", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Tags To Add To The Post:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="max_tags" name="aiomatic_Spinner_Settings[max_tags]" placeholder="1-2" class="cr_width_full" value="<?php echo esc_attr($max_tags);?>">
            </td>
        </tr>
        <tr class="hideMain8"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("This option will make the plugin not create tags which are not already existing on your site. For best results in this case, be sure to add to the prompt the list of tags from where the AI should select.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Add Inexistent Tags:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="checkbox" id="skip_inexist_tags" name="aiomatic_Spinner_Settings[skip_inexist_tags]"<?php
    if ($skip_inexist_tags == 'on')
        echo ' checked ';
?>>
        </td></tr>
        <tr class="hideMain8"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI tag generator. This command can be any given task or order, based on which, it will generate tags for posts. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI Tags Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_tags]" placeholder="Write a comma separated list of 5 tags for post title: %%post_title%%"><?php
    echo esc_textarea($ai_tags);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain8">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="tags_assistant_id" name="aiomatic_Spinner_Settings[tags_assistant_id]" class="cr_width_full" onchange="assistantSelected('tags_assistant_id', 'disableTags');">
    <?php
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
        if($tags_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($tags_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
            <tr class="hideMain8">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for tags generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The Tags Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="tags_model" name="aiomatic_Spinner_Settings[tags_model]" <?php if($tags_assistant_id != ''){echo ' disabled';}?> class="disableTags cr_width_full" onchange="visionSelectedAI8();">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($tags_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain8 hideVision8">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_tag" name="aiomatic_Spinner_Settings[ai_vision_tag]"<?php
    if ($ai_vision_tag == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr class="hideMain8"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("If you want to assign tags to custom taxonomies of custom post types, you can set the taxonomy name here.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Custom Taxonomy Name For Tags:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="text" id="tags_tax" name="aiomatic_Spinner_Settings[tags_tax]" placeholder="post_tag" class="cr_width_full" value="<?php echo esc_attr($tags_tax);?>">
        </div>
        </td></tr><tr><td>
                    <h2><?php echo esc_html__("Post Automatic Custom Fields/Custom Taxonomies Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically add custom fields or custom taxonomies to posts from your site.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Automatically Add Custom Fields/Custom Taxonomies To Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="add_custom" name="aiomatic_Spinner_Settings[add_custom]" onchange="mainChanged10();" >
                        <option value="disabled"<?php
                            if ($add_custom == "disabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="enabled"<?php
                            if ($add_custom == "enabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr class="hideMain10"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set a list of custom field slugs and prompts which will generate values for them (each on a new line). This command can be any given task or order, based on which, it will generate custom taxonomies for posts. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). To disable this functionality, leave this settings field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI Custom Fields Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_custom_field]" placeholder="custom_field_slug => What is the distance of from the Earth to the Sun? Write only the numeric answer, nothing else."><?php
    echo esc_textarea($ai_custom_field);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain10"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set if you want to assign custom fields as you entered them above and not process them as AI prompts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Process Custom Field Input As AI Prompts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="checkbox" id="no_custom_field_prompt" name="aiomatic_Spinner_Settings[no_custom_field_prompt]"<?php
    if ($no_custom_field_prompt == 'on')
        echo ' checked ';
?>>
        </td></tr>
        <tr class="hideMain10"><td colspan="2"><hr/></td></tr>
        <tr class="hideMain10"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set a list of custom taxonomy slugs and prompts which will generate values for them (each on a new line). This command can be any given task or order, based on which, it will generate custom taxonomies for posts. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). To disable this functionality, leave this settings field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI Custom Taxonomy Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_custom_tax]" placeholder="taxonomy_slug => Write a comma separated list of 5 categories for post title: %%post_title%%"><?php
    echo esc_textarea($ai_custom_tax);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain10"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set if you want to assign custom fields as you entered them above and not process them as AI prompts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Process Custom Taxonomy Input As AI Prompts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="checkbox" id="no_custom_tax_prompt" name="aiomatic_Spinner_Settings[no_custom_tax_prompt]"<?php
    if ($no_custom_tax_prompt == 'on')
        echo ' checked ';
?>>
        </td></tr>
        <tr class="hideMain10">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Maximum number of custom taxonomies to add. You can also use value ranges, like: 3-5. The default value is 1-2", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Custom Taxonomies To Add To The Post:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="max_custom" name="aiomatic_Spinner_Settings[max_custom]" placeholder="1-2" class="cr_width_full" value="<?php echo esc_attr($max_custom);?>">
            </td>
        </tr>
        <tr class="hideMain10"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("This option will make the plugin not create custom taxonomies which are not already existing on your site. For best results in this case, be sure to add to the prompt the list of custom taxonomies from where the AI should select.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Do Not Add Inexistent Custom Taxonomies:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="checkbox" id="skip_inexist_custom" name="aiomatic_Spinner_Settings[skip_inexist_custom]"<?php
    if ($skip_inexist_custom == 'on')
        echo ' checked ';
?>>
        </td></tr>
        <tr class="hideMain10"><td colspan="2"><hr/></td></tr>
        <tr class="hideMain10">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="custom_assistant_id" name="aiomatic_Spinner_Settings[custom_assistant_id]" class="cr_width_full" onchange="assistantSelected('custom_assistant_id', 'disableCustom');">
    <?php
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
        if($custom_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($custom_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
            <tr class="hideMain10">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for custom taxonomies/custom fields generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The Custom Taxonomies/Custom Fields Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="custom_model" name="aiomatic_Spinner_Settings[custom_model]" <?php if($custom_assistant_id != ''){echo ' disabled';}?> class="disableCustom cr_width_full" onchange="visionSelectedAI10();">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($custom_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain10 hideVision10">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_custom" name="aiomatic_Spinner_Settings[ai_vision_custom]"<?php
    if ($ai_vision_custom == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
        <tr><td>
                    <h2><?php echo esc_html__("Automatic Post Comments/Product Reviews Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically add post comments/product reviews from your site.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Automatically Add Post Comments/Product Reviews:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="add_comments" name="aiomatic_Spinner_Settings[add_comments]" onchange="mainChanged5();" >
                        <option value="disabled"<?php
                            if ($add_comments == "disabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="enabled"<?php
                            if ($add_comments == "enabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr class="hideMain5">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Maximum number of comments/reviews to add. You can also use value ranges, like: 3-5. The default value is 1-2", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Comments/Reviews To Add To The Post:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="max_comments" name="aiomatic_Spinner_Settings[max_comments]" placeholder="1-2" class="cr_width_full" value="<?php echo esc_attr($max_comments);?>">
            </td>
        </tr>
        <tr class="hideMain5"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI comment/review generator. This command can be any given task or order, based on which, it will generate comments for posts. You can use the following shortcodes here: %%previous_comments%%, %%post_title%%, %%comment_author_name%%, %%comment_author_email%%, %%comment_author_url%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%, %%product_star_rating%%. For the %%product_star_rating%% shortcode, a random value will be selected, defined by the 'WooCommerce Product Review Minimum-Maximum Star Count' settings field from below. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI Comment/Review Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_comments]" placeholder="Please insert a command for the AI"><?php
    echo esc_textarea($ai_comments);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain5">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set the range of product reviews which the plugin will create for WooCommerce products. If you set this value, you can use the %%product_star_rating%% shortcode in the 'Prompt For The AI Comment Generator' settings field from above. You can also use value ranges, like: 3-5. The default is 5", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("WooCommerce Product Review Minimum-Maximum Star Count:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="text" id="star_count" name="aiomatic_Spinner_Settings[star_count]" placeholder="4-5" class="cr_width_full" value="<?php echo esc_attr($star_count);?>">
            </td>
        </tr>
        <tr class="hideMain5">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Maximum number of comments/reviews to add to the %%previous_comments%% shortcode, The default value is 5", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("%%previous_comments%% Shortcode Comment/Reviews Count:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="number" min="0" step="1" id="prev_comms" name="aiomatic_Spinner_Settings[prev_comms]" placeholder="5" class="cr_width_full" value="<?php echo esc_attr($prev_comms);?>">
            </td>
        </tr>
        <tr class="hideMain5">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="comments_assistant_id" name="aiomatic_Spinner_Settings[comments_assistant_id]" class="cr_width_full" onchange="assistantSelected('comments_assistant_id', 'disableComments');">
    <?php
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
        if($comments_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($comments_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
            <tr class="hideMain5">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for comments/reviews generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The Comments/Reviews Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="comments_model" name="aiomatic_Spinner_Settings[comments_model]" <?php if($comments_assistant_id != ''){echo ' disabled';}?> class="disableComments cr_width_full" onchange="visionSelectedAI5();">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($comments_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain5 hideVision5">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_com" name="aiomatic_Spinner_Settings[ai_vision_com]"<?php
    if ($ai_vision_com == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
               <tr class="hideMain5">
                  <td>
                     <div>
                        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text cr_min_260px">
                              <?php
                                 echo esc_html__("Do you want to keep created comments/reviews for manual approval?", 'aiomatic-automatic-ai-content-writer');
                                 ?>
                           </div>
                        </div>
                        <b><?php echo esc_html__("Keep Created Comments For Review (Don't Auto Approve Them):", 'aiomatic-automatic-ai-content-writer');?></b>
                     </div>
                  </td>
                  <td>
                     <div>
            <input type="checkbox" id="no_approve" name="aiomatic_Spinner_Settings[no_approve]"<?php
    if ($no_approve == 'on')
        echo ' checked ';
?>>
                     </div>
                  </td>
               </tr>
               <tr class="hideMain5">
                  <td>
                     <div>
                        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text cr_min_260px">
                              <?php
                                 echo esc_html__("Input a list of user names that the plugin will use when submitting comments/reviews. One per line. If you leave this field empty, a random user will be selected from your site. Possible shortcode that can be used here: %%random_user%%, %%random_new_name%%, %%author_name%%, %%random_sentence%%, %%random_sentence2%%", 'aiomatic-automatic-ai-content-writer');
                                 ?>
                           </div>
                        </div>
                        <b><?php echo esc_html__("Comment/Review User Name List:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </div>
                  </td>
                  <td>
                     <div>
                        <textarea rows="4" name="aiomatic_Spinner_Settings[user_list]" placeholder="Insert a list of user names to use when submitting comments (one per line)" class="cr_width_full"><?php
                           echo esc_textarea($user_list);
                           ?></textarea>
                     </div>
                  </td>
               </tr>
               <tr class="hideMain5">
                  <td>
                     <div>
                        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text cr_min_260px">
                              <?php
                                 echo esc_html__("Input a list of e-mails that the plugin will use when submitting comments/reviews. One per line. If you leave this field empty, a random email will be generated. Possible shortcode that can be used here: %%random_sentence%%, %%random_sentence2%%", 'aiomatic-automatic-ai-content-writer');
                                 ?>
                           </div>
                        </div>
                        <b><?php echo esc_html__("Comment/Review E-mail List:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </div>
                  </td>
                  <td>
                     <div>
                        <textarea rows="4" name="aiomatic_Spinner_Settings[email_list]" placeholder="Insert a list of e-mails to use when submitting comments (one per line)" class="cr_width_full"><?php
                           echo esc_textarea($email_list);
                           ?></textarea>
                     </div>
                  </td>
               </tr>
               <tr class="hideMain5">
                  <td>
                     <div>
                        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text cr_min_260px">
                              <?php
                                 echo esc_html__("Input a list of URLs that the plugin will use when submitting comments/reviews. One per line. Possible shortcode that can be used here: %%post_link%%, %%random_sentence%%, %%random_sentence2%%", 'aiomatic-automatic-ai-content-writer');
                                 ?>
                           </div>
                        </div>
                        <b><?php echo esc_html__("Comment/Review URL List:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </div>
                  </td>
                  <td>
                     <div>
                        <textarea rows="4" name="aiomatic_Spinner_Settings[url_list]" placeholder="Insert a list of URLs to use when submitting comments (one per line)" class="cr_width_full"><?php
                           echo esc_textarea($url_list);
                           ?></textarea>
                     </div>
                  </td>
               </tr>
                <tr class="hideMain5">
                <td>
                        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <div class="bws_hidden_help_text cr_min_260px">
                            <?php
                                echo sprintf( wp_kses( __( "Do you want to set a custom comment/review publish date? You can input 2 dates, minimum and maximum date - the plugin will select a random date from the specified interval, for each new comment created. Set the range in the below field. Set the range in the below field Accepted values for this field are listed: <a href='%s' target='_blank'>here</a>. To disable this feature, leave this field blank. This format is suggested for dates: mm/dd/yyyy", 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url_raw( 'https://www.php.net/manual/en/datetime.formats.php' ) );
                                ?>
                            </div>
                        </div>
                        <b><?php echo esc_html__("Set a Custom Comment/Review Date Range:", 'aiomatic-automatic-ai-content-writer');?></b>
                </td>
                <td>
                <input type="text" id="min_time" name="aiomatic_Spinner_Settings[min_time]" value="<?php echo esc_attr($min_time);?>" placeholder="Start time" class="cr_half"> - <input type="text" id="max_time" name="aiomatic_Spinner_Settings[max_time]" value="<?php echo esc_attr($max_time);?>" placeholder="End time" class="cr_half">
                </td>
                </tr><tr><td>
                    <h2><?php echo esc_html__("SEO Meta Title Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically add AI generated SEO meta titles to posts from your site.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Automatically Add AI Generated SEO Title To Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="add_seo_title" name="aiomatic_Spinner_Settings[add_seo_title]" onchange="mainChanged66();" >
                        <option value="disabled"<?php
                            if ($add_seo_title == "disabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="enabled"<?php
                            if ($add_seo_title == "enabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr class="hideMain66"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI SEO meta title generator. This command can be any given task or order, based on which, it will generate meta title for posts. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). The default value is: Write a SEO meta description for the post title: %%post_title%%", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI SEO Meta Title Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_seo_title]" placeholder="Please insert a command for the AI"><?php
    echo esc_textarea($ai_seo_title);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain66">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="meta_assistant_id_title" name="aiomatic_Spinner_Settings[meta_assistant_id_title]" class="cr_width_full" onchange="assistantSelected('meta_assistant_id_title', 'disableMeta');">
    <?php
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
        if($meta_assistant_id_title == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($meta_assistant_id_title == $myassistant->ID)
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
        </td>
        </tr><tr class="hideMain66">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for AI SEO Meta Title Generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The AI SEO Meta Title Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="seo_model_title" name="aiomatic_Spinner_Settings[seo_model_title]" <?php if($meta_assistant_id_title != ''){echo ' disabled';}?> class="disableMeta cr_width_full" onchange="visionSelectedAI6();">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($seo_model_title == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain66 hideVision6">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_seo_title" name="aiomatic_Spinner_Settings[ai_vision_seo_title]"<?php
    if ($ai_vision_seo_title == 'on')
        echo ' checked ';
?>>
            </td>
            </tr><tr class="hideMain66">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set if you want to limit the AI generated meta title length.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Limit AI Generated Meta Title Character Count:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <input type="number" min="0" step="1" id="seo_max_char_title" name="aiomatic_Spinner_Settings[seo_max_char_title]" class="cr_450" value="<?php echo esc_html($seo_max_char_title);?>" placeholder="Maximum character length">
            </td>
            </tr>
               <tr><td>
                    <h2><?php echo esc_html__("SEO Meta Description Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr><tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will automatically add AI generated SEO meta descriptions to posts from your site.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Automatically Add AI Generated SEO Description To Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="add_seo" name="aiomatic_Spinner_Settings[add_seo]" onchange="mainChanged6();" >
                        <option value="disabled"<?php
                            if ($add_seo == "disabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                        <option value="enabled"<?php
                            if ($add_seo == "enabled") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Enabled", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr class="hideMain6"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set an prompt command you want to send to the AI SEO meta description generator. This command can be any given task or order, based on which, it will generate meta descriptions for posts. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). The default value is: Write a SEO meta description for the post title: %%post_title%%", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt For The AI SEO Meta Description Generator:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[ai_seo]" placeholder="Please insert a command for the AI"><?php
    echo esc_textarea($ai_seo);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideMain6">
        <td class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assigned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </td>
        <td><select id="meta_assistant_id" name="aiomatic_Spinner_Settings[meta_assistant_id]" class="cr_width_full" onchange="assistantSelected('meta_assistant_id', 'disableMeta');">
    <?php
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
        if($meta_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($meta_assistant_id == $myassistant->ID)
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
        </td>
        </tr><tr class="hideMain6">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI Model to be used for AI SEO Meta Description Generator.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model For The AI SEO Meta Description Generator:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <select id="seo_model" name="aiomatic_Spinner_Settings[seo_model]" <?php if($meta_assistant_id != ''){echo ' disabled';}?> class="disableMeta cr_width_full" onchange="visionSelectedAI6();">
            <?php
foreach($all_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($seo_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
            </select>  
            </td>
            </tr>
            <tr class="hideMain6 hideVision6">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select if you want to use AI vision and send to the AI model also the Featured Image of the edited post. Note that the AI prompt might also be needed to be updated if you enable this feature.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable AI Vision:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="checkbox" id="ai_vision_seo" name="aiomatic_Spinner_Settings[ai_vision_seo]"<?php
    if ($ai_vision_seo == 'on')
        echo ' checked ';
?>>
            </td>
            </tr><tr class="hideMain6">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set if you want to limit the AI generated meta description length.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Limit AI Generated Meta Description Character Count:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <input type="number" min="0" step="1" id="seo_max_char" name="aiomatic_Spinner_Settings[seo_max_char]" class="cr_450" value="<?php echo esc_html($seo_max_char);?>" placeholder="Maximum character length">
            </td>
            </tr><tr class="hideMain6">
            <td>
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Set if you want to copy meta description from post excerpt, instead of creating it. Note that this will disable the AI generator of the SEO meta.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Copy Meta Description From Post Excerpt Instead Of Generating It:", 'aiomatic-automatic-ai-content-writer');?></b>   
            </td>
            <td class="cr_min_width_200">
            <input type="checkbox" id="seo_copy_excerpt" name="aiomatic_Spinner_Settings[seo_copy_excerpt]"<?php
    if ($seo_copy_excerpt == 'on')
        echo ' checked ';
?>>
            </td>
            </tr>
            <tr><td>
                    <h2><?php echo esc_html__("Text to Audio/Video Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr>

        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to enable text to speech/video feature of the plugin.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Enable Content Text-to-Speech/Video:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
<select id="content_text_speech" onchange="aiomatic_audio_changed()" name="aiomatic_Spinner_Settings[content_text_speech]" >
<?php
echo '<option' . ($content_text_speech == 'off' ? ' selected': '') . ' value="off">Disabled</option>';
if (!isset($aiomatic_Main_Settings['app_id'])) 
{
    $aiomatic_Main_Settings['app_id'] = '';
}
$appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
$appids = array_filter($appids);
if(empty($appids))
{
$token = '';
}
else
{
$token = $appids[array_rand($appids)];
} 
if(!aiomatic_is_aiomaticapi_key($token) && (!isset($aiomatic_Main_Settings['api_selector']) || (trim($aiomatic_Main_Settings['api_selector']) != 'azure')))
{
    echo '<option' . ($content_text_speech == 'openai' ? ' selected': '') . ' value="openai">OpenAI Text-to-Speech</option>';
}
else
{
    echo '<option' . ($content_text_speech == 'openai' ? ' selected': '') . ' disabled value="openai">OpenAI Text-to-Speech (' . esc_html__("Currently Only OpenAI API is supported for TTS", 'aiomatic-automatic-ai-content-writer') . ')</option>';
}
if (isset($aiomatic_Main_Settings['elevenlabs_app_id']) && trim($aiomatic_Main_Settings['elevenlabs_app_id']) != '')
{
    echo '<option' . ($content_text_speech == 'elevenlabs' ? ' selected': '') . ' value="elevenlabs">ElevenLabs.io Text-to-Speech</option>';
}
else
{
    echo '<option' . ($content_text_speech == 'elevenlabs' ? ' selected': '') . ' disabled value="elevenlabs">ElevenLabs.io Text-to-Speech (' . esc_html__("Enter API key in Settings to enable", 'aiomatic-automatic-ai-content-writer') . ')</option>';
}
if (isset($aiomatic_Main_Settings['google_app_id']) && trim($aiomatic_Main_Settings['google_app_id']) != '')
{
    echo '<option' . ($content_text_speech == 'google' ? ' selected': '') . ' value="google">Google Text-to-Speech</option>';
}
else
{
    echo '<option' . ($content_text_speech == 'google' ? ' selected': '') . ' disabled value="google">Google Text-to-Speech (' . esc_html__("Enter API key in Settings to enable", 'aiomatic-automatic-ai-content-writer') . ')</option>';
}
if (isset($aiomatic_Main_Settings['did_app_id']) && trim($aiomatic_Main_Settings['did_app_id']) != '')
{
    echo '<option' . ($content_text_speech == 'did' ? ' selected': '') . ' value="did">D-ID Text-to-Video</option>';
}
else
{
    echo '<option' . ($content_text_speech == 'did' ? ' selected': '') . ' disabled value="did">D-ID Text-to-Video (' . esc_html__("Enter API key in Settings to enable", 'aiomatic-automatic-ai-content-writer') . ')</option>';
}
?>
</select>
        </div>
        </td></tr>
<tr class="hidedid"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The URL of the source image to be animated by the driver video, or a selection from the list of provided studio actors.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Actor Source Image URL:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input id="did_image" name="aiomatic_Spinner_Settings[did_image]" list="did_image_list" type="text" list="did_image" class="coderevolution_gutenberg_input" value="<?php echo esc_attr($did_image);?>" placeholder="Actor URL"/>
                    <datalist id="did_image_list">
                    <option>https://create-images-results.d-id.com/api_docs/assets/noelle.jpeg</option>
                    <option>https://create-images-results.d-id.com/api_docs/assets/amy.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Zivva_f/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/William_m/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Sara_f/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Magen_f/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Luna_f/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Joaquin_m/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Jenna_f/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Ibrahim_m/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Hassan_m/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Gordon_m/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Fatha_f/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Fanna_f/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Eric_m/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Emma_f/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Emily_f/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Bull_m/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Brandon_m/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Billy_m/image.jpeg</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Aria_f/image.png</option>
                    <option>https://create-images-results.d-id.com/DefaultPresenters/Amber_f/image.jpeg</option>
                    </datalist>
        </div>
        </td></tr>
<tr class="hidedid"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a voice you want to use for your video content. You can add voices in the following format: voice_provider:voice_name:voice_config - available voices lists:", 'aiomatic-automatic-ai-content-writer');
    echo '&nbsp;<a href="https://speech.microsoft.com/portal/voicegallery" target="_blank">https://speech.microsoft.com/portal/voicegallery</a> - <a href="https://docs.aws.amazon.com/polly/latest/dg/voicelist.html" target="_blank">https://docs.aws.amazon.com/polly/latest/dg/voicelist.html</a>';
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Select a Voice:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input id="did_voice" name="aiomatic_Spinner_Settings[did_voice]" type="text" list="did_voice_list" class="coderevolution_gutenberg_input" value="<?php echo esc_attr($did_voice);?>" placeholder="Voice config"/>
                    <datalist id="did_voice_list">
                    <option>microsoft:en-US-JennyNeural:Neutral</option>
                    <option>microsoft:en-US-JennyNeural:Cheerful</option>
                    <option>microsoft:en-US-JennyNeural:Sad</option>
                    <option>microsoft:en-US-JennyNeural:Assistant</option>
                    <option>microsoft:en-US-JennyNeural:Chat</option>
                    <option>microsoft:en-US-JennyNeural:Newscast</option>
                    <option>microsoft:en-US-JennyNeural:Angry</option>
                    <option>microsoft:en-US-JennyNeural:Excited</option>
                    <option>microsoft:en-US-JennyNeural:Friendly</option>
                    <option>microsoft:en-US-JennyNeural:Terrified</option>
                    <option>microsoft:en-US-JennyNeural:Shouting</option>
                    <option>microsoft:en-US-JennyNeural:Unfriendly</option>
                    <option>microsoft:en-US-JennyNeural:Whispering</option>
                    <option>microsoft:en-US-JennyNeural:Hopeful</option>
                    <option>microsoft:en-US-JennyNeural:Default</option>
                    <option>microsoft:en-US-GuyNeural:Default</option>
                    <option>microsoft:en-US-GuyNeural:Newscast</option>
                    <option>microsoft:en-US-GuyNeural:Angry</option>
                    <option>microsoft:en-US-GuyNeural:Cheerful</option>
                    <option>microsoft:en-US-GuyNeural:Sad</option>
                    <option>microsoft:en-US-GuyNeural:Excited</option>
                    <option>microsoft:en-US-GuyNeural:Friendly</option>
                    <option>microsoft:en-US-GuyNeural:Terrified</option>
                    <option>microsoft:en-US-GuyNeural:Shouting</option>
                    <option>microsoft:en-US-GuyNeural:Unfriendly</option>
                    <option>microsoft:en-US-GuyNeural:Whispering</option>
                    <option>microsoft:en-US-GuyNeural:Hopeful</option>
                    <option>microsoft:en-US-AmberNeural</option>
                    <option>microsoft:en-US-AnaNeural</option>
                    <option>microsoft:en-US-AriaNeural:Default</option>
                    <option>microsoft:en-US-AriaNeural:Chat</option>
                    <option>microsoft:en-US-AriaNeural:Cheerful</option>
                    <option>microsoft:en-US-AriaNeural:Empathetic</option>
                    <option>microsoft:en-US-AriaNeural:Angry</option>
                    <option>microsoft:en-US-AriaNeural:Sad</option>
                    <option>microsoft:en-US-AriaNeural:Excited</option>
                    <option>microsoft:en-US-AriaNeural:Friendly</option>
                    <option>microsoft:en-US-AriaNeural:Terrified</option>
                    <option>microsoft:en-US-AriaNeural:Shouting</option>
                    <option>microsoft:en-US-AriaNeural:Unfriendly</option>
                    <option>microsoft:en-US-AriaNeural:Whispering</option>
                    <option>microsoft:en-US-AriaNeural:Hopeful</option>
                    <option>microsoft:en-US-AshleyNeural</option>
                    <option>microsoft:en-US-BrandonNeural</option>
                    <option>microsoft:en-US-ChristopherNeural</option>
                    <option>microsoft:en-US-CoraNeural</option>
                    <option>microsoft:en-US-DavisNeural:Default</option>
                    <option>microsoft:en-US-DavisNeural:Chat</option>
                    <option>microsoft:en-US-DavisNeural:Angry</option>
                    <option>microsoft:en-US-DavisNeural:Cheerful</option>
                    <option>microsoft:en-US-DavisNeural:Excited</option>
                    <option>microsoft:en-US-DavisNeural:Friendly</option>
                    <option>microsoft:en-US-DavisNeural:Hopeful</option>
                    <option>microsoft:en-US-DavisNeural:Sad</option>
                    <option>microsoft:en-US-DavisNeural:Shouting</option>
                    <option>microsoft:en-US-DavisNeural:Terrified</option>
                    <option>microsoft:en-US-DavisNeural:Unfriendly</option>
                    <option>microsoft:en-US-DavisNeural:Whispering</option>
                    <option>microsoft:en-US-ElizabethNeural</option>
                    <option>microsoft:en-US-EricNeural</option>
                    <option>microsoft:en-US-JacobNeural</option>
                    <option>microsoft:en-US-JaneNeural:Default</option>
                    <option>microsoft:en-US-JaneNeural:Cheerful</option>
                    <option>microsoft:en-US-JaneNeural:Angry</option>
                    <option>microsoft:en-US-JaneNeural:Excited</option>
                    <option>microsoft:en-US-JaneNeural:Friendly</option>
                    <option>microsoft:en-US-JaneNeural:Hopeful</option>
                    <option>microsoft:en-US-JaneNeural:Sad</option>
                    <option>microsoft:en-US-JaneNeural:Shouting</option>
                    <option>microsoft:en-US-JaneNeural:Terrified</option>
                    <option>microsoft:en-US-JaneNeural:Unfriendly</option>
                    <option>microsoft:en-US-JaneNeural:Whispering</option>
                    <option>microsoft:en-US-JaneNeural:Default</option>
                    <option>microsoft:en-US-JasonNeural:Default</option>
                    <option>microsoft:en-US-JasonNeural:Angry</option>
                    <option>microsoft:en-US-JasonNeural:Cheerful</option>
                    <option>microsoft:en-US-JasonNeural:Excited</option>
                    <option>microsoft:en-US-JasonNeural:Friendly</option>
                    <option>microsoft:en-US-JasonNeural:Hopeful</option>
                    <option>microsoft:en-US-JasonNeural:Sad</option>
                    <option>microsoft:en-US-JasonNeural:Shouting</option>
                    <option>microsoft:en-US-JasonNeural:Terrified</option>
                    <option>microsoft:en-US-JasonNeural:Unfriendly</option>
                    <option>microsoft:en-US-JasonNeural:Whispering</option>
                    <option>microsoft:en-US-MichelleNeural</option>
                    <option>microsoft:en-US-MonicaNeural</option>
                    <option>microsoft:en-US-NancyNeural:Default</option>
                    <option>microsoft:en-US-NancyNeural:Angry</option>
                    <option>microsoft:en-US-NancyNeural:Cheerful</option>
                    <option>microsoft:en-US-NancyNeural:Excited</option>
                    <option>microsoft:en-US-NancyNeural:Friendly</option>
                    <option>microsoft:en-US-NancyNeural:Hopeful</option>
                    <option>microsoft:en-US-NancyNeural:Sad</option>
                    <option>microsoft:en-US-NancyNeural:Shouting</option>
                    <option>microsoft:en-US-NancyNeural:Terrified</option>
                    <option>microsoft:en-US-NancyNeural:Unfriendly</option>
                    <option>microsoft:en-US-NancyNeural:Whispering</option>
                    <option>microsoft:en-US-RogerNeural</option>
                    <option>microsoft:en-US-SaraNeural:Default</option>
                    <option>microsoft:en-US-SaraNeural:Angry</option>
                    <option>microsoft:en-US-SaraNeural:Cheerful</option>
                    <option>microsoft:en-US-SaraNeural:Excited</option>
                    <option>microsoft:en-US-SaraNeural:Friendly</option>
                    <option>microsoft:en-US-SaraNeural:Hopeful</option>
                    <option>microsoft:en-US-SaraNeural:Sad</option>
                    <option>microsoft:en-US-SaraNeural:Shouting</option>
                    <option>microsoft:en-US-SaraNeural:Terrified</option>
                    <option>microsoft:en-US-SaraNeural:Unfriendly</option>
                    <option>microsoft:en-US-SaraNeural:Whispering</option>
                    <option>microsoft:en-US-SteffanNeural</option>
                    <option>microsoft:en-US-TonyNeural:Default</option>
                    <option>microsoft:en-US-TonyNeural:Angry</option>
                    <option>microsoft:en-US-TonyNeural:Cheerful</option>
                    <option>microsoft:en-US-TonyNeural:Excited</option>
                    <option>microsoft:en-US-TonyNeural:Friendly</option>
                    <option>microsoft:en-US-TonyNeural:Hopeful</option>
                    <option>microsoft:en-US-TonyNeural:Sad</option>
                    <option>microsoft:en-US-TonyNeural:Shouting</option>
                    <option>microsoft:en-US-TonyNeural:Terrified</option>
                    <option>microsoft:en-US-TonyNeural:Unfriendly</option>
                    <option>microsoft:en-US-TonyNeural:Whispering</option>
                    <option>microsoft:en-US-AIGenerate1Neural</option>
                    <option>microsoft:en-US-AIGenerate2Neural</option>
                    <option>amazon:Amy</option>
                    <option>amazon:Emma</option>
                    <option>amazon:Brian</option>
                    <option>amazon:Arthur</option>
                    <option>amazon:Nicole</option>
                    <option>amazon:Olivia</option>
                    <option>amazon:Russell</option>
                    <option>amazon:Ivy</option>
                    <option>amazon:Joanna</option>
                    <option>amazon:Kendra</option>
                    <option>amazon:Kimberly</option>
                    <option>amazon:Salli</option>
                    <option>amazon:Joey</option>
                    <option>amazon:Justin</option>
                    <option>amazon:Kevin</option>
                    <option>amazon:Matthew</option>
                    <option>amazon:Ruth</option>
                    <option>amazon:Stephen</option>
                    <option>amazon:Geraint</option>
                    <option>amazon:Ayanda</option>
                    <option>amazon:Aria</option>
                    <option>amazon:Aditi</option>
                    <option>amazon:Raveena</option>
                    <option>amazon:Kajal</option>
                    <option>amazon:Zeina</option>
                    <option>amazon:Hala</option>
                    <option>amazon:Arlet</option>
                    <option>amazon:Hiujin</option>
                    <option>amazon:Zhiyu</option>
                    <option>amazon:Naja</option>
                    <option>amazon:Mads</option>
                    <option>amazon:Laura</option>
                    <option>amazon:Lotte</option>
                    <option>amazon:Ruben</option>
                    <option>amazon:Suvi</option>
                    <option>amazon:Celine</option>
                    <option>amazon:L??a</option>
                    <option>amazon:Mathieu</option>
                    <option>amazon:R??mi</option>
                    <option>amazon:Chantal</option>
                    <option>amazon:Gabrielle</option>
                    <option>amazon:Liam</option>
                    <option>amazon:Marlene</option>
                    <option>amazon:Vicki</option>
                    <option>amazon:Hans</option>
                    <option>amazon:Daniel</option>
                    <option>amazon:Hannah</option>
                    <option>amazon:Dora</option>
                    <option>amazon:Karl</option>
                    <option>amazon:Carla</option>
                    <option>amazon:Bianca</option>
                    <option>amazon:Giorgio</option>
                    <option>amazon:Adriano</option>
                    <option>amazon:Mizuki</option>
                    <option>amazon:Takumi</option>
                    <option>amazon:Kazuha</option>
                    <option>amazon:Tomoko</option>
                    <option>amazon:Seoyeon</option>
                    <option>amazon:Liv</option>
                    <option>amazon:Ida</option>
                    <option>amazon:Ewa</option>
                    <option>amazon:Maja</option>
                    <option>amazon:Jacek</option>
                    <option>amazon:Jan</option>
                    <option>amazon:Ola</option>
                    <option>amazon:Camila</option>
                    <option>amazon:Vitoria</option>
                    <option>amazon:Ricardo</option>
                    <option>amazon:Thiago</option>
                    <option>amazon:Ines</option>
                    <option>amazon:Cristiano</option>
                    <option>amazon:Carmen</option>
                    <option>amazon:Tatyana</option>
                    <option>amazon:Maxim</option>
                    <option>amazon:Conchita</option>
                    <option>amazon:Lucia</option>
                    <option>amazon:Enrique</option>
                    <option>amazon:Sergio</option>
                    <option>amazon:Mia</option>
                    <option>amazon:Andr??s</option>
                    <option>amazon:Lupe</option>
                    <option>amazon:Penelope</option>
                    <option>amazon:Miguel</option>
                    <option>amazon:Pedro</option>
                    <option>amazon:Astrid</option>
                    <option>amazon:Elin</option>
                    <option>amazon:Filiz</option>
                    <option>amazon:Gwyneth</option>
                    <option>afflorithmics:en-US-JennyNeural</option>
                    <option>elevenlabs:en-US-JennyNeural</option>
                    </datalist>
        </div>
        </td></tr>
<tr class="hideeleven"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a voice you want to use for your content.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Select a Voice:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
<select id="eleven_voice" name="aiomatic_Spinner_Settings[eleven_voice]" >
<?php
$eleven_voices = aiomatic_get_eleven_voices();
if($eleven_voices === false)
{
    echo '<option value="" disabled>'.esc_html__("Failed to list voices!", 'aiomatic-automatic-ai-content-writer').'</option>';
}
else
{
    foreach($eleven_voices as $key => $voice)
    {
        echo '<option' . ($eleven_voice == esc_attr($key) ? ' selected': '') . ' value="'.esc_attr($key).'">'.esc_html($voice).'</option>';
    }
}
?>
</select>
        </div>
        </td></tr>
<tr class="hideeleven"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a voice you want to use for your content.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Select a Voice:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="text" id="width" name="aiomatic_Spinner_Settings[eleven_voice_custom]" value="<?php echo esc_html($eleven_voice_custom);?>" placeholder="Custom voice ID">
        </div>
        </td></tr>
        <tr class="hideeleven"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the model to be used when generating the voices.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice AI Model:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="eleven_model_id" name="aiomatic_Spinner_Settings[eleven_model_id]" >
<?php
echo '<option' . ($eleven_model_id == 'eleven_monolingual_v1' ? ' selected': '') . ' value="eleven_monolingual_v1">eleven_monolingual_v1</option>';
echo '<option' . ($eleven_model_id == 'eleven_multilingual_v1' ? ' selected': '') . ' value="eleven_multilingual_v1">eleven_multilingual_v1</option>';
echo '<option' . ($eleven_model_id == 'eleven_multilingual_v2' ? ' selected': '') . ' value="eleven_multilingual_v2">eleven_multilingual_v2</option>';
?>
                    </select>
        </div>
        </td></tr>
        <tr class="hideeleven"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a the Voice stability of the chosen voice. Higher stability ensures consistency but may result in monotony, therefore for longer text, it is recommended to decrease stability. The default value is 0.75", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice Stability:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="number" min="0" step="0.01" id="voice_stability" name="aiomatic_Spinner_Settings[voice_stability]" class="cr_width_full" value="<?php echo esc_html($voice_stability);?>" placeholder="0.75">
        </div>
        </td></tr>
        <tr class="hideeleven"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Boosting voice clarity and target speaker similarity is achieved by high enhancement; however, very high values can produce artifacts, so it's essential to find the optimal setting. The default value is 0.75", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice Similarity Boost:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="number" min="0" step="0.01" id="voice_similarity_boost" name="aiomatic_Spinner_Settings[voice_similarity_boost]" class="cr_width_full" value="<?php echo esc_html($voice_similarity_boost);?>" placeholder="0.75">
        </div>
        </td></tr>
        <tr class="hideeleven"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Boost the characteristics of the voice. Default is disabled.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Style Exaggeration:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="number" min="0" step="0.01" id="voice_style" name="aiomatic_Spinner_Settings[voice_style]" class="cr_width_full" value="<?php echo esc_html($voice_style);?>" placeholder="Style exaggeration">
        </div>
        </td></tr>
        <tr class="hideeleven"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Boost the similarity of the synthesized speech and the voice at the cost of some generation speed.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Speaker Boost:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                <input type="checkbox" id="speaker_boost" name="aiomatic_Spinner_Settings[speaker_boost]"<?php
    if ($speaker_boost == 'on')
        echo ' checked ';
?>>
        </div>
        </td></tr>
        <tr class="hideopen"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the model to be used when generating the voices.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice AI Model:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="open_model_id" name="aiomatic_Spinner_Settings[open_model_id]" >
<?php
echo '<option' . ($open_model_id == 'tts-1' ? ' selected': '') . ' value="tts-1">tts-1</option>';
echo '<option' . ($open_model_id == 'tts-1-hd' ? ' selected': '') . ' value="tts-1-hd">tts-1-hd</option>';
?>
                    </select>
        </div>
        </td></tr>
        <tr class="hideopen"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the voice to be used when generating the text to speech.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Voice Selector:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="open_voice" name="aiomatic_Spinner_Settings[open_voice]" >
<?php
echo '<option' . ($open_voice == 'alloy' ? ' selected': '') . ' value="alloy">alloy</option>';
echo '<option' . ($open_voice == 'echo' ? ' selected': '') . ' value="echo">echo</option>';
echo '<option' . ($open_voice == 'fable' ? ' selected': '') . ' value="fable">fable</option>';
echo '<option' . ($open_voice == 'nova' ? ' selected': '') . ' value="nova">nova</option>';
echo '<option' . ($open_voice == 'onyx' ? ' selected': '') . ' value="onyx">onyx</option>';
echo '<option' . ($open_voice == 'shimmer' ? ' selected': '') . ' value="shimmer">shimmer</option>';
?>
                    </select>
        </div>
        </td></tr>
        <tr class="hideopen"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the output format to be used when generating the text to speech.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Voice Output Format:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="open_format" name="aiomatic_Spinner_Settings[open_format]" >
<?php
echo '<option' . ($open_format == 'mp3' ? ' selected': '') . ' value="mp3">mp3</option>';
echo '<option' . ($open_format == 'opus' ? ' selected': '') . ' value="opus">opus</option>';
echo '<option' . ($open_format == 'aac' ? ' selected': '') . ' value="aac">aac</option>';
echo '<option' . ($open_format == 'flac' ? ' selected': '') . ' value="flac">flac</option>';
?>
                    </select>
        </div>
        </td></tr>
        <tr class="hideopen"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a the Voice speed of the chosen voice. The default value is 1. Min: 0.25, max: 4.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice Stability:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="number" min="0.25" step="0.01" max="4" id="open_speed" name="aiomatic_Spinner_Settings[open_speed]" class="cr_width_full" value="<?php echo esc_html($open_speed);?>" placeholder="1">
        </div>
        </td></tr>
<tr class="hidegoogle"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a the language of the chosen voice.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice Language:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
<select id="voice_language" name="aiomatic_Spinner_Settings[voice_language]" >
<?php
$gvlanguages = array(
    'af-ZA' => 'Afrikaans (South Africa)',
    'ar-XA' => 'Arabic, multi-region',
    'id-ID' => 'Indonesian (Indonesia)',
    'ms-MY' => 'Malay (Malaysia)',
    'ca-ES' => 'Catalan (Spain)',
    'da-DK' => 'Danish (Denmark)',
    'de-DE' => 'German (Germany)',
    'en-AU' => 'English (Australia)',
    'en-GB' => 'English (Great Britain)',
    'en-IN' => 'English (India)',
    'en-US' => 'English (United States)',
    'es-ES' => 'Spanish (Spain)',
    'es-US' => 'Spanish (United States)',
    'eu-ES' => 'Basque (Spain)',
    'fil-PH' => 'Filipino (Philippines)',
    'fr-CA' => 'French (Canada)',
    'fr-FR' => 'French (France)',
    'gl-ES' => 'Galician (Spain)',
    'it-IT' => 'Italian (Italy)',
    'lv-LV' => 'Latvian (Latvia)',
    'lt-LT' => 'Lithuanian (Lithuania)',
    'hu-HU' => 'Hungarian (Hungary)',
    'nl-NL' => 'Dutch (Netherlands)',
    'nb-NO' => 'Norwegian Bokm??l (Norway)',
    'pl-PL' => 'Polish (Poland)',
    'pt-BR' => 'Portuguese (Brazil)',
    'pt-PT' => 'Portuguese (Portugal)',
    'ro-RO' => 'Romanian (Romania)',
    'sk-SK' => 'Slovak (Slovakia)',
    'fi-FI' => 'Finnish (Finland)',
    'sv-SE' => 'Swedish (Sweden)',
    'vi-VN' => 'Vietnamese (Vietnam)',
    'tr-TR' => 'Turkish (Turkey)',
    'is-IS' => 'Icelandic (Iceland)',
    'cs-CZ' => 'Czech (Czech Republic)',
    'el-GR' => 'Greek (Greece)',
    'bg-BG' => 'Bulgarian (Bulgaria)',
    'ru-RU' => 'Russian (Russia)',
    'sr-RS' => 'Serbian (Serbia)',
    'uk-UA' => 'Ukrainian (Ukraine)',
    'he-IL' => 'Hebrew (Israel)',
    'mr-IN' => 'Marathi (India)',
    'hi-IN' => 'Hindi (India)',
    'bn-IN' => 'Bengali (India)',
    'gu-IN' => 'Gujarati (India)',
    'ta-IN' => 'Tamil (India)',
    'te-IN' => 'Telugu (India)',
    'kn-IN' => 'Kannada (India)',
    'ml-IN' => 'Malayalam (India)',
    'th-TH' => 'Thai (Thailand)',
    'cmn-TW' => 'Mandarin (Taiwan)',
    'yue-HK' => 'Cantonese (Hong Kong)',
    'ja-JP' => 'Japanese (Japan)',
    'cmn-CN' => 'Mandarin (Mainland China)',
    'ko-KR' => 'Korean (South Korea)'
);
foreach($gvlanguages as $key => $lang)
{
    echo '<option' . ($voice_language == esc_attr($key) ? ' selected': '') . ' value="'.esc_attr($key).'">'.esc_html($lang).'</option>';
}
?>
</select>
        </div>
        </td></tr>
        <tr class="hidegoogle"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a the name of the chosen voice.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice Name:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
<select id="google_voice" name="aiomatic_Spinner_Settings[google_voice]" >
<?php
if (!isset($aiomatic_Main_Settings['google_app_id']) || trim($aiomatic_Main_Settings['google_app_id']) == '')
{
    $google_voices = false;
}
else
{
    $google_voices = aiomatic_get_google_voices($voice_language);
}
if($google_voices === false)
{
    echo '<option value="" disabled>'.esc_html__("Failed to list voices!", 'aiomatic-automatic-ai-content-writer').'</option>';
}
else
{
    foreach($google_voices as $key => $voice)
    {
        echo '<option' . ($google_voice == esc_attr($voice['name']) ? ' selected': '') . ' value="'.esc_attr($voice['name']).'">'.esc_html($voice['name']).'</option>';
    }
}
?>
</select>
        </div>
        </td></tr>
        <tr class="hidegoogle"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a the Audio Device Profile of the chosen voice.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Audio Device Profile:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
<select id="audio_profile" name="aiomatic_Spinner_Settings[audio_profile]" >
<?php
$gvprofiles = array(
    '' => esc_html__('Default','aiomatic-automatic-ai-content-writer'),
    'wearable-class-device' => esc_html__('Smart watch or wearable','aiomatic-automatic-ai-content-writer'),
    'handset-class-device' => esc_html__('Smartphone','aiomatic-automatic-ai-content-writer'),
    'headphone-class-device' => esc_html__('Headphones or earbuds','aiomatic-automatic-ai-content-writer'),
    'small-bluetooth-speaker-class-device' => esc_html__('Small home speaker','aiomatic-automatic-ai-content-writer'),
    'medium-bluetooth-speaker-class-device' => esc_html__('Smart home speaker','aiomatic-automatic-ai-content-writer'),
    'large-home-entertainment-class-device' => esc_html__('Home entertainment system or smart TV','aiomatic-automatic-ai-content-writer'),
    'large-automotive-class-device' => esc_html__('Car speaker','aiomatic-automatic-ai-content-writer'),
    'telephony-class-application' => esc_html__('Interactive Voice Response (IVR) system','aiomatic-automatic-ai-content-writer')
);
foreach($gvprofiles as $key => $val)
{
    echo '<option' . ($audio_profile == esc_attr($key) ? ' selected': '') . ' value="'.esc_attr($key).'">'.esc_html($val).'</option>';
}
?>
</select>
        </div>
        </td></tr>
        <tr class="hidegoogle"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a the Voice Speed of the chosen voice. Speaking rate/speed, in the range [0.25, 4.0]. 1.0 is the normal native speed supported by the specific voice. 2.0 is twice as fast, and 0.5 is half as fast. If unset(0.0), defaults to the native 1.0 speed. Any other values < 0.25 or > 4.0 will return an error.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice Speed:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="number" min="0.25" max="4" step="0.01" id="voice_speed" name="aiomatic_Spinner_Settings[voice_speed]" class="cr_width_full" value="<?php echo esc_html($voice_speed);?>" placeholder="Voice speed">
        </div>
        </td></tr>
        <tr class="hidegoogle"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a the Voice Pitch of the chosen voice. Speaking pitch, in the range [-20.0, 20.0]. 20 means increase 20 semitones from the original pitch. -20 means decrease 20 semitones from the original pitch.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Voice Pitch:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="number" min="-20" step="0.1" max="20" id="voice_pitch" name="aiomatic_Spinner_Settings[voice_pitch]" class="cr_width_full" value="<?php echo esc_html($voice_pitch);?>" placeholder="Voice pitch">
        </div>
        </td></tr>
        <tr class="hideWideAudio"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a textual template you want to send to the AI audio/video converter.You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). If you use Royalty Free Images as a source, you can also set their keywords here, if no keywords set, they will be automatically generated.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Text To Audio/Video Template:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[text_to_audio]" placeholder="Please insert a template"><?php
    echo esc_textarea($text_to_audio);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideWideAudio"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will generate AI content, that will be prepended or appended to each post's content.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Generated Audio/Video Location:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select name="aiomatic_Spinner_Settings[audio_location]">
                              <option value="append"<?php
                                 if ($audio_location == "append") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Append To The End", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="preppend"<?php
                                 if ($audio_location == "preppend") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Prepend To The Beginning", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr>
        <tr class="hideWideAudio"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a textual template you want to prepend the audio/video embed. You can use the following shortcodes here: %%post_title%%, %%post_content%%, %%post_content_plain_text%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%current_date_time%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it's assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the prompt command - Update: %%related_questions_KEYWORD%% is also supported, to get a list of PAA questions for the KEYWORD you want to use. Update: nested shortcodes also supported (shortcodes generated by rules from other plugins). If you use Royalty Free Images as a source, you can also set their keywords here, if no keywords set, they will be automatically generated.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("HTML Text To Prepend Audio/Video:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[prep_audio]" placeholder="HTML to prepend audio/video"><?php
    echo esc_textarea($prep_audio);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideWideAudio"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__('Select where you want to copy audio/video files. You can copy the files also to a cloud storage, using this extension: ', 'aiomatic-automatic-ai-content-writer') . '<a href="https://wpbay.com/product/aiomatic-extension-amazon-s3-storage/" target="_blank">https://wpbay.com/product/aiomatic-extension-amazon-s3-storage/</a>';
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Copy Audio/Video Files:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select name="aiomatic_Spinner_Settings[copy_location]">
                        <option value="local"<?php
                            if ($copy_location == "local") {
                                echo " selected";
                            }
                            ?>><?php echo esc_html__("Locally To Server", 'aiomatic-automatic-ai-content-writer');?></option>
<?php
$amazon_s3_active = true;
if (!is_plugin_active('aiomatic-extension-amazon-s3-images/aiomatic-extension-amazon-s3-images.php')) 
{
    $amazon_s3_active = false;
}
?>
<option value="amazon"<?php
if ($copy_location == "amazon") {
        echo " selected";
}
if($amazon_s3_active == false)
{
    echo ' disabled';
}
?> >Amazon S3</option>
<option value="cloudflare"<?php
if ($copy_location == "cloudflare") {
        echo " selected";
}
if($amazon_s3_active == false)
{
    echo ' disabled';
}
?> >CloudFlare R2</option>
<option value="digital"<?php
if ($copy_location == "digital") {
        echo " selected";
}
if($amazon_s3_active == false)
{
    echo ' disabled';
}
?> >Digital Ocean Spaces</option>
<option value="wasabi"<?php
if ($copy_location == "wasabi") {
        echo " selected";
}
if($amazon_s3_active == false)
{
    echo ' disabled';
}
?> >Wasabi</option>
<option value="generics3"<?php
if ($copy_location == "generics3") {
        echo " selected";
}
if($amazon_s3_active == false)
{
    echo ' disabled';
}
?> >Generic Amazon S3 Compatible Storage</option>
                    </select>
        </div>
        </td></tr>
            <tr><td colspan="2">
                    <h2><?php echo esc_html__("Audio to Text Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr>
        <tr><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to enable text to speech/video feature of the plugin. The following input file types are supported: mp3, mp4, mpeg, mpga, m4a, wav, and webm.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b class="wpaiomatic-delete"><?php echo esc_html__("Enable Content Audio To Text:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
<select id="content_speech_text" onchange="aiomatic_speech_changed()" name="aiomatic_Spinner_Settings[content_speech_text]" >
<?php
echo '<option' . ($content_speech_text == 'off' ? ' selected': '') . ' value="off">Disabled</option>';
if (!isset($aiomatic_Main_Settings['app_id'])) 
{
    $aiomatic_Main_Settings['app_id'] = '';
}
$appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
$appids = array_filter($appids);
if(empty($appids))
{
$token = '';
}
else
{
$token = $appids[array_rand($appids)];
} 
if(!aiomatic_is_aiomaticapi_key($token) && (!isset($aiomatic_Main_Settings['api_selector']) || (trim($aiomatic_Main_Settings['api_selector']) != 'azure') && trim($aiomatic_Main_Settings['api_selector']) != 'custom'))
{
    echo '<option' . ($content_speech_text == 'openai' ? ' selected': '') . ' value="openai">OpenAI</option>';
}
else
{
    echo '<option' . ($content_text_speech == 'openai' ? ' selected': '') . ' disabled value="openai">OpenAI (' . esc_html__("Currently Only OpenAI API is supported for this feature", 'aiomatic-automatic-ai-content-writer') . ')</option>';
}
?>
</select>
        </div>
        </td></tr>
        <tr class="hideSpeechText">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the maximum number of audio files to process from the post content.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Maximum Number Of Audio Files To Process:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <input type="number" min="1" step="1" id="max_speech" name="aiomatic_Spinner_Settings[max_speech]" value="<?php echo esc_html($max_speech);?>" placeholder="Maximum number of audio files to process">
            </td>
            </tr>
        <tr class="hideSpeechText">
            <td class="cr_min_width_200">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                        <?php
                            echo esc_html__("Select the AI model to use for speech to text.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Model To Use For Speech-to-Text:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select id="speech_model" name="aiomatic_Spinner_Settings[speech_model]">
<?php
foreach($all_speech_models as $modelx)
{
   echo '<option value="' . esc_html($modelx) .'"';
   if ($speech_model == $modelx) 
   {
       echo " selected";
   }
   echo '>' . esc_html($modelx) . '</option>';
}
?>
            </select>
            </td>
            </tr>
        <tr class="hideSpeechText"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Set the AI prompt to be used. This will define the formatting of the resulting text. You can use a prompt to improve the quality of the transcripts generated by the Whisper API. The model will try to match the style of the prompt, so it will be more likely to use capitalization and punctuation if the prompt does too. However, the current prompting system is much more limited than our other language models and only provides limited control over the generated audio. Here are some examples of how prompting can help in different scenarios: Prompts can be very helpful for correcting specific words or acronyms that the model may misrecognize in the audio. For example, the following prompt improves the transcription of the words DALL-E and GPT-3, which were previously written as \"GDP 3\" and \"DALI\": \"The transcript is about OpenAI which makes technology like DALL-E, GPT-3, and ChatGPT with the hope of one day building an AGI system that benefits all of humanity\" To preserve the context of a file that was split into segments, you can prompt the model with the transcript of the preceding segment. This will make the transcript more accurate, as the model will use the relevant information from the previous audio. The model will only consider the final 224 tokens of the prompt and ignore anything earlier. For multilingual inputs, Whisper uses a custom tokenizer. For English only inputs, it uses the standard GPT-2 tokenizer which are both accessible through the open source Whisper Python package. Sometimes the model might skip punctuation in the transcript. You can avoid this by using a simple prompt that includes punctuation: \"Hello, welcome to my lecture.\" The model may also leave out common filler words in the audio. If you want to keep the filler words in your transcript, you can use a prompt that contains them: \"Umm, let me think like, hmm... Okay, here's what I'm, like, thinking.\" Some languages can be written in different ways, such as simplified or traditional Chinese. The model might not always use the writing style that you want for your transcript by default. You can improve this by using a prompt in your preferred writing style.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Prompt To Send To The AI:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[audio_to_text_prompt]" placeholder="AI prompt"><?php
    echo esc_textarea($audio_to_text_prompt);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideSpeechText"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("What sampling temperature to use. Higher values means the model will take more risks. Try 0.9 for more creative applications, and 0 (argmax sampling) for ones with a well-defined answer. We generally recommend altering this or top_p but not both.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Temperature:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="0.01" max="2" id="speech_temperature" name="aiomatic_Spinner_Settings[speech_temperature]" value="<?php echo esc_html($speech_temperature);?>" placeholder="0">
        </td></tr>
        <tr class="hideSpeechText"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select a textual template which will be attached to posts, based on the generated text from the audio file. You can use the following shortcode here: %%audio_to_text%%", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Textual Template To Be Added To The Post:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[audio_to_text]" placeholder="%%audio_to_text%%"><?php
    echo esc_textarea($audio_to_text);
?></textarea>
        </div>
        </td></tr>
        <tr class="hideSpeechText"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("The plugin will generate AI content, that will be prepended or appended to each post's content.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("AI Generated Text Location:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select name="aiomatic_Spinner_Settings[audio_text_location]">
                              <option value="append"<?php
                                 if ($audio_text_location == "append") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Append To The End", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="preppend"<?php
                                 if ($audio_text_location == "preppend") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Prepend To The Beginning", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
        </div>
        </td></tr>
        <tr><td colspan="2">
                    <h2><?php echo esc_html__("Extra Features:", 'aiomatic-automatic-ai-content-writer');?></h2>
                    
                    </td></tr>
                    <tr><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to change post status after editing posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Change Post Status After Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="change_status" name="aiomatic_Spinner_Settings[change_status]" class="cr_width_full">
                    <option value="no" 
<?php
if ($change_status == 'no' || $change_status == '') 
{
    echo " selected";
}
?>
><?php echo esc_html__("No Change", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="pending"<?php
if ($change_status == 'pending') 
{
    echo " selected";
}
?>><?php echo esc_html__("Pending", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="draft"<?php
if ($change_status == 'draft') 
{
    echo " selected";
}
?>><?php echo esc_html__("Draft", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="publish"<?php
if ($change_status == 'publish') 
{
    echo " selected";
}
?>><?php echo esc_html__("Published", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="private"<?php
if ($change_status == 'private') 
{
    echo " selected";
}
?>><?php echo esc_html__("Private", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="trash"<?php
if ($change_status == 'trash') 
{
    echo " selected";
}
?>><?php echo esc_html__("Trash", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr><tr><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select if you want to change post status after editing posts. You can enter static dates like: 31-01-2025 or dynamic dates like: now, 2 days ago, 2 minutes ago, -2 years, last week, last Sunday. If you leave this field blank, it will be disabled. You can also use the %%post_date%% shortcode to get the original date of the post, which can be combined with expressions, like: %%post_date%% +1 day", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Change Post Date After Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <input type="text" class="cr_width_full" name="aiomatic_Spinner_Settings[change_date]" value="<?php echo esc_attr($change_date);?>" placeholder="<?php echo esc_html__("No Change", 'aiomatic-automatic-ai-content-writer');?>" class="cr_width_full">
        </div>
        </td></tr>
                    <tr><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("If you check this checkbox, the plugin will store all prompts used in the plugin, to allow model dillution and other features on OpenAI API's part. This works only if you are using an AI model provided by OpenAI.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Store AI Prompts On OpenAI's Part:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <select id="store_data" name="aiomatic_Spinner_Settings[store_data]" class="cr_width_full">
                    <option value="off" 
<?php
if ($store_data == 'off') 
{
    echo " selected";
}
?>
><?php echo esc_html__("Off", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="on"<?php
if ($store_data == 'on') 
{
    echo " selected";
}
?>><?php echo esc_html__("On", 'aiomatic-automatic-ai-content-writer');?></option>
                    </select>
        </div>
        </td></tr>
        <tr><td colspan="2"><hr/></td></tr>
                    <tr><td colspan="2">
                    <h2><?php echo esc_html__("Manage AI Content Editor Templates:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td></tr>
        <tr>
            <td class="ai-flex">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px disable_drag">
                        <?php
                            echo esc_html__("Select a AI Content Editor template to be loaded.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Saved AI Content Editor Templates:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select title="<?php echo esc_html__('Select an AI Content Editor Template to be loaded.', 'aiomatic-automatic-ai-content-writer');?>" class="cr_width_full editor_select_template" id="editor_select_template" onchange="unsaved = false;">
<?php
if(!empty($temp_list))
{
    $loaded_template = get_option('aiomatic_loaded_template', false);
    echo '<option value="" disabled selected>' . esc_html__("Select a template to load", 'aiomatic-automatic-ai-content-writer') . '</option>';
    foreach($temp_list as $templid => $templ)
    {
        echo '<option value="' . esc_attr($templid) . '"';
        if($loaded_template == $templid)
        {
            echo 'selected';
        }
        echo '>' . esc_html($templ) . ' (' . esc_attr($templid) . ')</option>';
    }
}
else
{
echo '<option value="" disabled selected>' . esc_html__("No templates found (use currently saved configuration)", 'aiomatic-automatic-ai-content-writer') . '</option>';
}
?>
                        </select>
                        </td>
                        </tr>
                    <tr>
                    <td class="ai-flex">
                    </td>
                    <td>
                    <input type="button" class="aisaveedittemplate button page-title-action" title="<?php echo esc_html__('Saves the AI Content Editor settings, as a new template', 'aiomatic-automatic-ai-content-writer');?>" value="<?php echo esc_html__("Save New Template", 'aiomatic-automatic-ai-content-writer');?>">&nbsp;
                    <input type="button" class="aideleteedittemplate button page-title-action" title="<?php echo esc_html__('Deletes the currently selected template', 'aiomatic-automatic-ai-content-writer');?>" value="<?php echo esc_html__("Delete Selected Template", 'aiomatic-automatic-ai-content-writer');?>">&nbsp;
                    <input type="button" class="ailoadedittemplate button page-title-action" title="<?php echo esc_html__('Loads the currently selected template', 'aiomatic-automatic-ai-content-writer');?>" value="<?php echo esc_html__("Load Selected Template", 'aiomatic-automatic-ai-content-writer');?>">&nbsp;
                    <input type="button" class="airenametemplate button page-title-action" title="<?php echo esc_html__('Renames the currently selected template', 'aiomatic-automatic-ai-content-writer');?>" value="<?php echo esc_html__("Rename Selected Template", 'aiomatic-automatic-ai-content-writer');?>">&nbsp;
                    <input type="button" class="aioverwritetemplate button page-title-action" title="<?php echo esc_html__('Overwrite the currently selected template', 'aiomatic-automatic-ai-content-writer');?>" value="<?php echo esc_html__("Overwrite Selected Template", 'aiomatic-automatic-ai-content-writer');?>">
                    </td>
                    </tr>
        </table>
        </div>
        <div id="tab-3" class="tab-content">            
    <table class="widefat">
    <tr>
    <td>
        <h1><span class="gs-sub-heading"><b><?php echo esc_html__("Automatic Post Editing:", 'aiomatic-automatic-ai-content-writer');?></b>&nbsp;</span>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Enable or disable automatic post modifications every time you publish a new post (manually or automatically).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div></h1>
                    </td>
                    <td>
                        <label class="aiomatic-switch"><input type="checkbox" id="aiomatic_spinning" onchange="toggleMain();" name="aiomatic_Spinner_Settings[aiomatic_spinning]"<?php
                     if ($aiomatic_spinning == 'on')
                         echo ' checked ';
                     ?>><span class="aiomatic-slider round"></span></label>

                    </td>
                    </tr>
                    </table>
                    <hr/>
            <table class="hideAuto widefat">
            <tr><td colspan="2">
<?php
    echo esc_html__("INFO: You can change the way the posts are edited by changing settings in the 'Editing Template Manager' tab from above!", 'aiomatic-automatic-ai-content-writer');
?>
</td></tr>
                    <tr><td colspan="2">
                    <h2><?php echo esc_html__("Posts Automatic Editing Options:", 'aiomatic-automatic-ai-content-writer');?></h2>
                    </td></tr><tr><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select when do you want to automatically process posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Automatically Process Posts When They Are:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                     <select id="process_event" name="aiomatic_Spinner_Settings[process_event]" class="skip-from-processing">
                     <option value="publish"<?php
                        if ($process_event == "publish") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Published", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="draft"<?php
                        if ($process_event == "draft") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Drafted", 'aiomatic-automatic-ai-content-writer');?></option>
                    <option value="pending"<?php
                        if ($process_event == "pending") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Pending", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select>
        </td></tr><tr><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do you want delay automatic editing of the posted article with this amount of seconds from post publish? This will create a single cron job for each post (cron is a requirement for this to function). If you leave this field blank, posts will be automatically spun on post publish.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Delay Article Editing By (Seconds):", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="number" min="0" step="1" id="delay_post" name="aiomatic_Spinner_Settings[delay_post]" class="cr_450 skip-from-processing" value="<?php echo esc_html($delay_post);?>" placeholder="Delay editing by X seconds">
        </td></tr><tr class="hidethis"><td>
        <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("This option will allow you to select if you want to run posting in async mode. This means that each time you publish a post, the plugin will try to execute it's task in the background - it will no longer block new post posting, while it finishes it's job.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Use Async Posting Method:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="checkbox" id="run_background" name="aiomatic_Spinner_Settings[run_background]" class="skip-from-processing"<?php
    if ($run_background == 'on')
        echo ' checked ';
?>>
        </td></tr>
        <tr><td colspan="3"><hr/></td></tr>
        <tr><td colspan="2">
        <h2><?php echo esc_html__("Legacy Automatic Content Editor Settings:", 'aiomatic-automatic-ai-content-writer');?></h2>
        </td>
        </tr>
        <tr><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do you want to enable the legacy automatic content editor of the plugin? This feature is kept for backwards compatibility with older versions of the plugin. Instead of this, it is recommended to use the moder content editor feature.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable The Legacy AI Content Editor:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </td><td>
                    <select name="aiomatic_Spinner_Settings[enable_default]" id="enable_default" class="skip-from-processing cr_width_full" onchange="defaultChanged();">
                     <option value="yes"<?php
    if ($enable_default == 'yes' || empty($enable_default))
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($enable_default == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
        </td></tr>
        <tr class="hideDefault">
            <td class="ai-flex">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px disable_drag">
                        <?php
                            echo esc_html__("Select a AI Content Editor template to be used for automatic bulk content editing.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Use This AI Content Editor Template For Automatic Post Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select title="<?php echo esc_html__('Select an AI Content Editor Template to be used', 'aiomatic-automatic-ai-content-writer');?>" name="aiomatic_Spinner_Settings[use_template_auto]" class="skip-from-processing cr_width_full" id="use_template_auto">
<?php
if(!empty($temp_list))
{
?>
<option value="" <?php if($use_template_auto == ''){ echo ' selected';}?> ><?php echo esc_html__("Use currently saved configuration", 'aiomatic-automatic-ai-content-writer');?></option>
<?php
foreach($temp_list as $templid => $templ)
{
    echo '<option value="' . esc_attr($templid) . '"';
    if($use_template_auto == $templid)
    { 
        echo ' selected';
    }
    echo '>' . esc_html($templ) . ' (' . esc_attr($templid) . ')</option>';
}
}
else
{
echo '<option value="" disabled selected>' . esc_html__("No templates found (use currently saved configuration)", 'aiomatic-automatic-ai-content-writer') . '</option>';
}
?>
            </select>
            </td>
            </tr>
        <tr class="hideDefault"><td>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do you want to enable automatically editing of WordPress 'posts'?", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Editing of 'Posts':", 'aiomatic-automatic-ai-content-writer');?></b>
                    </td><td>
                    <select name="aiomatic_Spinner_Settings[post_posts]" id="post_posts" class="skip-from-processing cr_width_full">
                     <option value="yes"<?php
    if ($post_posts == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($post_posts == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do you want to enable automatically editing of WordPress 'pages'?", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Editing of 'Pages':", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <select name="aiomatic_Spinner_Settings[post_pages]" id="post_pages" class="skip-from-processing cr_width_full">
                     <option value="yes"<?php
    if ($post_pages == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($post_pages == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do you want to enable automatically editing of WordPress 'custom post types'?", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Editing of 'Custom Post Types':", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <select name="aiomatic_Spinner_Settings[post_custom]" onchange="toggleCustom();" id="post_custom" class="skip-from-processing cr_width_full">
                     <option value="yes"<?php
    if ($post_custom == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"<?php
    if ($post_custom == 'on')
        echo ' selected ';
?>><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
        </td></tr><tr class="hideDefault hideCustom"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("If you checked the above checkbox to disable processing of custom post types, you can define here a comma separated list of posts types which should still be process (excepted from skipping).", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Excepting This Comma Separated List Of Custom Post Types:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="text" id="except_type" name="aiomatic_Spinner_Settings[except_type]" class="skip-from-processing" value="<?php echo esc_html($except_type);?>" placeholder="Excepted custom post types">
        </td></tr><tr class="hideDefault hideCustomAlt"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("If you enabled custom post type processing and want to set a comma separated list of custom post types which should be processed, you can do it here.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Process Only This Comma Separated List Of Custom Post Types:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <input type="text" id="only_type" name="aiomatic_Spinner_Settings[only_type]" class="skip-from-processing" value="<?php echo esc_html($only_type);?>" placeholder="Process only these custom post types">
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do you want to disable automatically editing of WordPress categories?", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Disable Editing of Selected Post Categories:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </td><td>
                    <div id="hideCats" class="hideCats">
<?php
    $cat_args   = array(
        'orderby' => 'name',
        'hide_empty' => 0,
        'order' => 'ASC'
    );
?>
<select name="aiomatic_Spinner_Settings[disabled_categories][]" multiple class="ai_resize_vertical skip-from-processing">
<?php
    $categories = get_categories($cat_args);
    foreach ($categories as $category) {
        $selected = '';
        if (isset($aiomatic_Spinner_Settings['disabled_categories']) && !empty($aiomatic_Spinner_Settings['disabled_categories'])) {
            $selected = in_array($category->term_id, $aiomatic_Spinner_Settings['disabled_categories']) ? 'selected' : '';
        }
?>
<option value="<?php echo esc_html($category->term_id); ?>" <?php echo $selected; ?>>
    <?php echo esc_html(sanitize_text_field($category->name)); ?>
</option>
<?php
    }
?>
</select>
        </div>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Input the tags for which you want to disable editing. You can enter more tags, separated by comma. Ex: cars, vehicles, red, luxury. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Disable Editing of Selected Post Tags:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[disable_tags]" class="skip-from-processing" placeholder="Please insert the tags for which you want to disable editing"><?php
    echo esc_textarea($disable_tags);
?></textarea>
        </div>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Input the tags for which you want to enable editing. You can enter more tags, separated by comma. Ex: cars, vehicles, red, luxury. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Editing of Selected Post Tags:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[enable_tags]" class="skip-from-processing" placeholder="Please insert the tags for which you want to enable editing"><?php
    echo esc_textarea($enable_tags);
?></textarea>
        </div>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Input the author user IDs for which you want to disable editing. You can enter more user IDs, separated by comma. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Disable Editing Of Posts By Author User IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[disable_users]" class="skip-from-processing" placeholder="Please insert the user IDs for which you want to disable editing"><?php
    echo esc_textarea($disable_users);
?></textarea>
        </div>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Input the author user IDs for which you want to enable editing. You can enter more user IDs, separated by comma. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Editing Of Posts By Author User IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[enable_users]" class="skip-from-processing" placeholder="Please insert the user IDs for which you want to enable editing"><?php
    echo esc_textarea($enable_users);
?></textarea>
        </div>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Edit only posts with this custom field value. Syntax: custom_field_name => custom_field_value. To accept any value, add a * in the value field. You can add more custom fields, each on a new line. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Enable Editing If Custom Field Has Value:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[enable_field]" class="skip-from-processing" placeholder="custom_field_name => custom_field_value"><?php
    echo esc_textarea($enable_field);
?></textarea>
        </div>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Do not edit posts with this custom field value. Syntax: custom_field_name => custom_field_value. To accept any value, add a * in the value field. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Disable Editing If Custom Field Has Value:", 'aiomatic-automatic-ai-content-writer');?></b>
                    </div>
                    </td><td>
                    <div>
                    <textarea rows="1" name="aiomatic_Spinner_Settings[disable_field]" class="skip-from-processing" placeholder="custom_field_name => custom_field_value"><?php
    echo esc_textarea($disable_field);
?></textarea>
        </div>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the featured image status of edited posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Featured Image Status:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <select name="aiomatic_Spinner_Settings[featured_status]" id="featured_status" class="skip-from-processing cr_width_full">
                     <option value="any"<?php
    if (empty($featured_status) || $featured_status == 'any')
        echo ' selected ';
?>><?php echo esc_html__("Don't Check", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="yes"<?php
    if ($featured_status == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Has Featured Image", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="no"<?php
    if ($featured_status == 'no')
        echo ' selected ';
?>><?php echo esc_html__("Does Not Have Featured Image", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
        </td></tr><tr class="hideDefault"><td>
                    <div>
        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                        <div class="bws_hidden_help_text cr_min_260px">
<?php
    echo esc_html__("Select the SEO Meta Description status of edited posts.", 'aiomatic-automatic-ai-content-writer');
?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("SEO Meta Description Status:", 'aiomatic-automatic-ai-content-writer');?></b>
                    
                    </td><td>
                    <select name="aiomatic_Spinner_Settings[seo_status]" id="seo_status" class="skip-from-processing cr_width_full">
                     <option value="any"<?php
    if (empty($seo_status) || $seo_status == 'any')
        echo ' selected ';
?>><?php echo esc_html__("Don't Check", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="yes"<?php
    if ($seo_status == 'yes')
        echo ' selected ';
?>><?php echo esc_html__("Has SEO Description", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="no"<?php
    if ($seo_status == 'no')
        echo ' selected ';
?>><?php echo esc_html__("Does Not Have SEO Description", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
        </td></tr>
        <tr><td colspan="2"><hr/></td></tr>
        <tr><td colspan="2">
         <h3><?php echo esc_html__("Automatic Content Editing Rules (Define Multiple Editors):", 'aiomatic-automatic-ai-content-writer');?></h3>
         <?php
         wp_nonce_field( 'aiomatic_save_edits', '_aiomaticr_nonce_edits' );
         ?>
         <table class="responsive table cr_main_table wrapspace">
            <thead>
               <tr>
                  <th class="cr_center">
                     <?php echo esc_html__("ID", 'aiomatic-automatic-ai-content-writer');?>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("This is the ID of the rule. ", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                  </th>
                  <th class="cr_center">
                     <?php echo esc_html__("AI Content Editing Template", 'aiomatic-automatic-ai-content-writer');?>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Select the AI Content Editing template to use for post editing, when this rule is running. To create a template, go to the 'Editing Template Manager' tab.", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                  </th>
                  <th class="cr_30 cr_center">
                     <?php echo esc_html__("Options", 'aiomatic-automatic-ai-content-writer');?>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Shows advanced settings for this rule.", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                  </th>
                  <th class="cr_30 cr_center">
                     <?php echo esc_html__("Del", 'aiomatic-automatic-ai-content-writer');?>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Do you want to delete this rule?", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                  </th>
                  <th class="cr_32 cr_center" >
                     <?php echo esc_html__("Active", 'aiomatic-automatic-ai-content-writer');?>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Do you want to enable this rule? You can deactivate any rule (you don't have to delete them to deactivate them).", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                  </th>
               </tr>
            </thead>
            <tbody>
               <?php 
echo aiomatic_expand_editors($temp_list); ?>
               <tr>
                  <td class="cr_td_xo"><input type="text" name="aiomatic_Editor_Rules[rule_description][]" id="rule_description" class="cr_center" placeholder="Rule ID" value="" class="cr_width_full"/></td>
                  <td class="cr_sxss"><select title="<?php echo esc_html__('Select an AI Content Editor Template to be used', 'aiomatic-automatic-ai-content-writer');?>" name="aiomatic_Editor_Rules[use_template_manual][]" class="skip-from-processing cr_width_full" id="use_template_manual">
<?php
?>
<option value="" selected><?php echo esc_html__("Select a template", 'aiomatic-automatic-ai-content-writer');?></option>
<option value="default"><?php echo esc_html__("Use currently saved configuration", 'aiomatic-automatic-ai-content-writer');?></option>
<?php
foreach($temp_list as $templid => $templ)
{
    echo '<option value="' . esc_attr($templid) . '">' . esc_html($templ) . ' (' . esc_attr($templid) . ')</option>';
}
?>
                                                </select></td>
                  <td class="cr_width_30">
                     <center><input type="button" id="mybtnfzr" value="<?php echo esc_html__("Settings", 'aiomatic-automatic-ai-content-writer');?>"></center>
                     <div id="mymodalfzr" class="codemodalfzr">
                        <div class="codemodalfzr-content">
                           <div class="codemodalfzr-header">
                              <span id="aiomatic_close" class="codeclosefzr">&times;</span>
                              <h2><span class="cr_color_white"><?php echo esc_html__("New Rule", 'aiomatic-automatic-ai-content-writer');?></span> <?php echo esc_html__("Advanced Settings", 'aiomatic-automatic-ai-content-writer');?></h2>
                           </div>
                           <div class="codemodalfzr-body">
                              <div class="table-responsive">
                                 <table class="responsive table cr_main_table_nowr">
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Do you want to enable automatically editing of WordPress 'posts'?", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Enable Editing of 'Posts':", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                    <select name="aiomatic_Editor_Rules[post_posts][]" class="skip-from-processing cr_width_full">
                     <option value="yes" selected><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on"><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Do you want to enable automatically editing of WordPress 'pages'?", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Enable Editing of 'Pages':", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                    <select name="aiomatic_Editor_Rules[post_pages][]" class="skip-from-processing cr_width_full">
                     <option value="yes"><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on" selected><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Do you want to enable automatically editing of WordPress 'custom post types'?", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Enable Editing of 'Custom Post Types':", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <select name="aiomatic_Editor_Rules[post_custom][]" class="skip-from-processing cr_width_full">
                     <option value="yes"><?php echo esc_html__("Enable", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="on" selected><?php echo esc_html__("Disable", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("If you checked the above checkbox to disable processing of custom post types, you can define here a comma separated list of posts types which should still be process (excepted from skipping).", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Excepting This Comma Separated List Of Custom Post Types:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <input type="text" id="except_type_spin" name="aiomatic_Editor_Rules[except_type][]" class="skip-from-processing" value="" placeholder="Excepted custom post types">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("If you enabled custom post type processing and want to set a comma separated list of custom post types which should be processed, you can do it here.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Process Only This Comma Separated List Of Custom Post Types:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <input type="text" id="only_type_spin" name="aiomatic_Editor_Rules[only_type][]" class="skip-from-processing" value="" placeholder="Process only these custom post types">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Do you want to disable automatically editing of WordPress categories?", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Disable Editing of Selected Post Categories:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <div id="hideCats" class="hideCats">
<?php
    $cat_args   = array(
        'orderby' => 'name',
        'hide_empty' => 0,
        'order' => 'ASC'
    );
?>
<select name="aiomatic_Editor_Rules[disabled_categories][]" multiple class="ai_resize_vertical skip-from-processing">
<option value="aiomatic_no_category_12345678" selected><?php echo esc_html__("Do Not Check Categories", 'aiomatic-automatic-ai-content-writer');?></option>
<?php
    $categories = get_categories($cat_args);
    foreach ($categories as $category) {
?>
<option value="<?php echo esc_html($category->term_id); ?>">
    <?php echo esc_html(sanitize_text_field($category->name)); ?>
</option>
<?php
    }
?>
</select>
        </div>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Input the tags for which you want to disable editing. You can enter more tags, separated by comma. Ex: cars, vehicles, red, luxury. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Disable Editing of Selected Post Tags:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <textarea rows="1" name="aiomatic_Editor_Rules[disable_tags][]" class="skip-from-processing" placeholder="Please insert the tags for which you want to disable editing"></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Input the tags for which you want to enable editing. You can enter more tags, separated by comma. Ex: cars, vehicles, red, luxury. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Enable Editing of Selected Post Tags:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <textarea rows="1" name="aiomatic_Editor_Rules[enable_tags][]" class="skip-from-processing" placeholder="Please insert the tags for which you want to enable editing"></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Input the author user IDs for which you want to disable editing. You can enter more user IDs, separated by comma. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Disable Editing of Author User IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <textarea rows="1" name="aiomatic_Editor_Rules[disable_users][]" class="skip-from-processing" placeholder="Please insert the user IDs for which you want to disable editing"></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Input the author user IDs for which you want to enable editing. You can enter more user IDs, separated by comma. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Enable Editing Of Posts By Author User IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <textarea rows="1" name="aiomatic_Editor_Rules[enable_users][]" class="skip-from-processing" placeholder="Please insert the user IDs for which you want to enable editing"></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Edit only posts with this custom field value. Syntax: custom_field_name => custom_field_value. To accept any value, add a * in the value field. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Enable Editing If Custom Field Has Value:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <textarea rows="1" name="aiomatic_Editor_Rules[enable_field][]" class="skip-from-processing" placeholder="custom_field_name => custom_field_value"></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Do not edit only posts with this custom field value. Syntax: custom_field_name => custom_field_value. To accept any value, add a * in the value field. To disable this feature, leave this field blank.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Disable Editing If Custom Field Has Value:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <textarea rows="1" name="aiomatic_Editor_Rules[disable_field][]" class="skip-from-processing" placeholder="custom_field_name => custom_field_value"></textarea>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Select the featured image status of edited posts.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("Featured Image Status:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <select name="aiomatic_Editor_Rules[featured_status][]" class="skip-from-processing cr_width_full">
                    <option value="any" selected><?php echo esc_html__("Don't Check", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="yes"><?php echo esc_html__("Has Featured Image", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="no"><?php echo esc_html__("Does Not Have Featured Image", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td class="cr_min_width_200">
                                             <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                                <div class="bws_hidden_help_text cr_min_260px">
                                                   <?php
                                                      echo esc_html__("Select the SEO Description status of edited posts.", 'aiomatic-automatic-ai-content-writer');
                                                      ?>
                                                </div>
                                             </div>
                                             <b><?php echo esc_html__("SEO Description Status:", 'aiomatic-automatic-ai-content-writer');?></b>
                                       </td>
                                       <td>
                                       <select name="aiomatic_Editor_Rules[seo_status][]" class="skip-from-processing cr_width_full">
                    <option value="any" selected><?php echo esc_html__("Don't Check", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="yes"><?php echo esc_html__("Has SEO Description", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="no"><?php echo esc_html__("Does Not Have SEO Description", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select>
                                       </td>
                                    </tr>
                                 </table>
                           <div class="codemodalfzr-footer">
                              <br/>
                              <h3 class="cr_inline"><?php echo esc_html__("Aiomatic Content Editor Rules", 'aiomatic-automatic-ai-content-writer');?></h3>
                              <span id="aiomatic_ok" class="codeokfzr cr_inline">OK&nbsp;</span>
                              <br/><br/>
                           </div>
                        </div>
                        </div>
                        </div>
                        </div>
                  </td>
                  <td class="cr_30 cr_center" ><span class="cr_30">X</span></td>
                  <td class="cr_short_td">
                  <select name="aiomatic_Editor_Rules[active][]" class="cr_width_full">
                     <option value="1" selected><?php echo esc_html__("Yes", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="0"><?php echo esc_html__("No", 'aiomatic-automatic-ai-content-writer');?></option>
                  </select></td>
               </tr>
            </tbody>
         </table>
</td></tr>
                    </table>
</div>
<div id="tab-2" class="tab-content">
    <table class="widefat">
        <tr class="aiomatic-title-holder"><td colspan="2">
<h2><?php echo esc_html__('AI Taxonomy SEO Tools', 'aiomatic-automatic-ai-content-writer');?></h2>
    <p class="aiomatic-settings-desc"><?php
                            echo esc_html__("The 'AI Taxonomy SEO Tools' offers automated tools to create and refine descriptive content for categories and tags, enhancing the organization and SEO of your WordPress site.", 'aiomatic-automatic-ai-content-writer');
?></p>
<hr/>
</td></tr>
        <tr class="aiomatic-title-holder"><td colspan="2"><h2 class="aiomatic-inner-title"><?php echo esc_html__("AI Taxonomy Description Writer Settings:", 'aiomatic-automatic-ai-content-writer');?></h2></td></tr>
        <tr>
        <th class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assisgned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </th>
        <td><select id="tax_assistant_id" name="aiomatic_Spinner_Settings[tax_assistant_id]" class="cr_width_full" onchange="assistantSelected('tax_assistant_id', 'disableTax');">
    <?php
if($all_assistants === false)
{
    echo '<option value="" selected disabled>' . esc_html__("Only OpenAI API is supported for Assistants API", 'aiomatic-automatic-ai-content-writer') . '</option>';
}
else
{
    if(count($all_assistants) == 0)
    {
        echo '<option value="" selected disabled>' . esc_html__("No Assistans added, go to the plugin's 'AI Assistans' menu to add new assistants!", 'aiomatic-automatic-ai-content-writer') . '</option>';
    }
    else
    {
        echo '<option value=""';
        if($tax_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($tax_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
        <tr>
               <th>
                  <div>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Select the default model you want to use for the AI Taxonomy Description Writer.", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                     <b><?php echo esc_html__("AI Taxonomy Description Writer Model:", 'aiomatic-automatic-ai-content-writer');?></b>
                  </div>
               </th>
               <td>
                  <div>
                     <select id="tax_description_model" name="aiomatic_Spinner_Settings[tax_description_model]" <?php if($tax_assistant_id != ''){echo ' disabled';}?> class="disableTax cr_width_full">
<?php
foreach($all_models as $modelx)
{
echo '<option value="' . esc_html($modelx) .'"';
if ($tax_description_model == $modelx) 
{
   echo " selected";
}
echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
                     </select>  
                  </div>
               </td>
            </tr>
            <tr>
                  <th class="cr_min_width_200">
                        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text cr_min_260px">
                              <?php
                                 echo esc_html__("Set the prompt to be used for the AI Taxonomy Description Writer feature. You can use the following shortcodes here: %%term_name%%, %%term_id%%, %%term_slug%%, %%term_description%%, %%term_taxonomy_name%%, %%term_taxonomy_id%% - default is: Write a description for a WordPress %%term_taxonomy_name%% with the following title: \"%%term_name%%\"", 'aiomatic-automatic-ai-content-writer');
                                 ?>
                           </div>
                        </div>
                        <b><?php echo esc_html__("AI Taxonomy Description Writer Prompt:", 'aiomatic-automatic-ai-content-writer');?></b>
                  </th>
                  <td>
                  <textarea name="aiomatic_Spinner_Settings[tax_description_prompt]" placeholder="AI Taxonomy Description Writer Prompt" class="cr_width_full"><?php echo esc_textarea($tax_description_prompt);?></textarea>
                  </td>
               </tr>
               <tr class="aiomatic-title-holder"><td colspan="2"><h2 class="aiomatic-inner-title"><?php echo esc_html__("AI Taxonomy SEO Meta Writer Settings:", 'aiomatic-automatic-ai-content-writer');?></h2></td></tr>
            <tr>
               <th>
                  <div>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Select if you want to enable also SEO meta description writing for the taxonomies. Note that this will work only for taxonomies automatically processed using the 'Enable Automatic Processing Of All Newly Added Taxonomies For' settings field from above. Also, note that you will need to have a SEO plugin installed on your site, from the following list: Yoast SEO, All In One SEO, Rank Math, Squirrly SEO.", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                     <b><?php echo esc_html__("Enable SEO Meta Description Writing:", 'aiomatic-automatic-ai-content-writer');?></b>
                  </div>
               </th>
               <td>
                  <div>
                     <select id="tax_seo_auto" onchange="taxSeoChanged();" name="aiomatic_Spinner_Settings[tax_seo_auto]" class="cr_width_full">
                     <option value="off"<?php
                           if ($tax_seo_auto == "off") {
                                 echo " selected";
                           }
                           ?> ><?php echo esc_html__("Off", 'aiomatic-automatic-ai-content-writer');?></option>
                           <option value="copy"<?php
                           if ($tax_seo_auto == "copy") {
                                 echo " selected";
                           }
                           ?> ><?php echo esc_html__("Copy The Taxonomy Description", 'aiomatic-automatic-ai-content-writer');?></option>
                           <option value="write"<?php
                           if ($tax_seo_auto == "write") {
                                 echo " selected";
                           }
                           ?> ><?php echo esc_html__("Write Separate SEO Description", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select>  
                  </div>
               </td>
            </tr><tr class="TaxSEO">
        <th class="cr_min_width_200">
            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                <div class="bws_hidden_help_text cr_min_260px">
                <?php
                    echo esc_html__("Select the AI Assistant to be used. This will disable the ability to select AI models, as the models assisgned to the assistant will be used for content creation.", 'aiomatic-automatic-ai-content-writer');
                    ?>
                </div>
            </div>
            <b><?php echo esc_html__("AI Assistant Name:", 'aiomatic-automatic-ai-content-writer');?></b>
        </th>
        <td><select id="tax_seo_assistant_id" name="aiomatic_Spinner_Settings[tax_seo_assistant_id]" class="cr_width_full" onchange="assistantSelected('tax_seo_assistant_id', 'disableTaxSEO');">
    <?php
if($all_assistants === false)
{
    echo '<option value="" selected disabled>' . esc_html__("Only OpenAI API is supported for Assistants API", 'aiomatic-automatic-ai-content-writer') . '</option>';
}
else
{
    if(count($all_assistants) == 0)
    {
        echo '<option value="" selected disabled>' . esc_html__("No Assistans added, go to the plugin's 'AI Assistans' menu to add new assistants!", 'aiomatic-automatic-ai-content-writer') . '</option>';
    }
    else
    {
        echo '<option value=""';
        if($tax_seo_assistant_id == '')
        {
            echo ' selected';
        }
        echo '>' . esc_html__("Don't use assistants, use AI models instead", 'aiomatic-automatic-ai-content-writer') . '</option>';
        foreach($all_assistants as $myassistant)
        {
            echo '<option value="' . esc_html($myassistant->ID) .'"';
            if($tax_seo_assistant_id == $myassistant->ID)
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
        </td>
        </tr>
            <tr class="TaxSEO">
               <th>
                  <div>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Select the default model you want to use for the AI Taxonomy SEO Description Writer. This will set the SEO description for the following SEO plugins: Yoast SEO, All In One SEO, Rank Math, Squirrly SEO.", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                     <b><?php echo esc_html__("AI Taxonomy SEO Description Writer Model:", 'aiomatic-automatic-ai-content-writer');?></b>
                  </div>
               </th>
               <td>
                  <div>
                     <select id="tax_seo_description_model" name="aiomatic_Spinner_Settings[tax_seo_description_model]" <?php if($tax_seo_assistant_id != ''){echo ' disabled';}?> class="disableTaxSEO cr_width_full">
<?php
foreach($all_models as $modelx)
{
echo '<option value="' . esc_html($modelx) .'"';
if ($tax_seo_description_model == $modelx) 
{
   echo " selected";
}
echo '>' . esc_html($modelx) . esc_html(aiomatic_get_model_provider($modelx)) . '</option>';
}
?>
                     </select>  
                  </div>
               </td>
            </tr>
            <tr class="TaxSEO">
                  <th class="cr_min_width_200">
                        <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text cr_min_260px">
                              <?php
                                 echo esc_html__("Set the prompt to be used for the AI Taxonomy SEO Description Writer feature. You can use the following shortcodes here: %%term_name%%, %%term_id%%, %%term_slug%%, %%term_description%%, %%term_taxonomy_name%%, %%term_taxonomy_id%% - default is: Write a description for a WordPress %%term_taxonomy_name%% with the following title: \"%%term_name%%\". This will set the SEO description for the following SEO plugins: Yoast SEO, All In One SEO, Rank Math, Squirrly SEO.", 'aiomatic-automatic-ai-content-writer');
                                 ?>
                           </div>
                        </div>
                        <b><?php echo esc_html__("AI Taxonomy SEO Description Writer Prompt:", 'aiomatic-automatic-ai-content-writer');?></b>
                  </th>
                  <td>
                  <textarea name="aiomatic_Spinner_Settings[tax_seo_description_prompt]" placeholder="AI Taxonomy Description Writer Prompt" class="cr_width_full"><?php echo esc_textarea($tax_seo_description_prompt);?></textarea>
                  </td>
               </tr>
               <tr class="aiomatic-title-holder"><td colspan="2"><h2 class="aiomatic-inner-title"><?php echo esc_html__("Automatic Taxonomy Processing At Creation:", 'aiomatic-automatic-ai-content-writer');?></h2></td></tr>
            <tr>
               <th>
                  <div>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Select if you want to enable automatic processing and description creation using AI of all newly added taxonomies for the taxonomy names you select (which don't already have a description set when they are created).", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                     <b><?php echo esc_html__("Enable Automatic Processing Of All Newly Added Taxonomies For:", 'aiomatic-automatic-ai-content-writer');?></b>
                  </div>
               </th>
               <td>
                  <div>
                     <select id="tax_description_auto" multiple name="aiomatic_Spinner_Settings[tax_description_auto][]" class="cr_width_full ai_resize_vertical">
<?php
$taxonomies = get_taxonomies();

foreach ($taxonomies as $tx) 
{
    echo '<option value="' . esc_html($tx) . '"';
    if (in_array($tx, $tax_description_auto)) {
          echo " selected";
    }
    echo '>' . esc_html($tx) . '</option>';
}
?>
                     </select>  
                  </div>
               </td>
            </tr>
            <tr class="aiomatic-title-holder"><td colspan="2"><h2 class="aiomatic-inner-title"><?php echo esc_html__("Existing Taxonomy AI Description Writer:", 'aiomatic-automatic-ai-content-writer');?></h2></td></tr>
                  <tr>
               <th>
                  <div>
                     <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px">
                           <?php
                              echo esc_html__("Select the taxonomies which you want to be affected by the manual taxonomy writing process.", 'aiomatic-automatic-ai-content-writer');
                              ?>
                        </div>
                     </div>
                     <b><?php echo esc_html__("Manually Run Taxonomy Description Writing On:", 'aiomatic-automatic-ai-content-writer');?></b>
                  </div>
               </th>
               <td>
                  <div>
                     <select id="tax_description_manual" name="aiomatic_Spinner_Settings[tax_description_manual]" class="cr_width_full">
<?php
foreach ($taxonomies as $tx) 
{
    echo '<option value="' . esc_html($tx) . '"';
    if ($tx == $tax_description_manual) {
          echo " selected";
    }
    echo '>' . esc_html($tx) . '</option>';
}
?>
                     </select>  
                  </div>
               </td>
            </tr>
                  <tr>
                     <th>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Set the maximum number of taxonomies to be processed at each run.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Maximum Number Of Taxonomies To Process:", 'aiomatic-automatic-ai-content-writer');?></b>
                        </div>
                     </th>
                     <td>
                        <div>
                           <input type="number" id="max_tax_nr" step="1" min="1" placeholder="Maximum Taxonomy Count" name="aiomatic_Spinner_Settings[max_tax_nr]" value="<?php echo esc_html($max_tax_nr);?>" class="cr_width_full"/>
                        </div>
                     </td>
                  </tr>
         <tr>
            <th>
               <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                  <div class="bws_hidden_help_text cr_min_260px">
                     <?php
                        echo esc_html__("Check this to force the plugin to make draft posts before they would be fully published. This can help you you use other third party plugins with the automatically published posts.", 'aiomatic-automatic-ai-content-writer');
                        ?>
                  </div>
               </div>
               <b><?php echo esc_html__("Process Also Taxonomies Which Already Have A Description:", 'aiomatic-automatic-ai-content-writer');?></b>
            </th>
            <td>
                  <label class="aiomatic-switch"><input type="checkbox" id="overwite_tax" name="aiomatic_Spinner_Settings[overwite_tax]"<?php
                     if ($overwite_tax == 'on')
                         echo ' checked ';
                     ?>><span class="aiomatic-slider round"></span></label>
            </td>
         </tr>
            <tr>
                     <th>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Do you want to run manual writing of description for existing taxonomies, now? Please check configuration from below before clicking 'Run Taxonomy Desciption Writing'.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Manually Run Taxonomy Description Writing Now:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </th>
                     <td>
                     <img id="run_imgx" src="<?php echo esc_url_raw(plugin_dir_url(dirname(__FILE__)) . 'images/running.gif');?>" alt="Running" class="cr_hidden cr_align_middle" title="status">
                     <div class="codemainfzr">
                     <select id="taxactions" class="actions" name="aiomatic_tax_actions" onchange="actionsChangedTax(this.value);" onfocus="this.selectedIndex = 0;">
                     <option value="select" disabled selected><?php echo esc_html__("Select an Action", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="run"><?php echo esc_html__("Run Taxonomy Desciption Writing", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select>
                     </div>
                     </td>
                  </tr>
<?php if(!isset($aiomatic_Main_Settings['hide_videos']) || trim($aiomatic_Main_Settings['hide_videos']) != 'on'){
?>
               <tr class="aiomatic-title-holder"><td colspan="2"><h2 class="aiomatic-inner-title"><?php echo esc_html__("AI Taxonomy Description Writer Tutorial Video:", 'aiomatic-automatic-ai-content-writer');?></h2></td></tr>
               <tr>
               <td class="coderevolution_block_input" colspan="2">
<iframe class="youtube-responsive" src="https://www.youtube.com/embed/k5BFo9jcmcs" allowfullscreen></iframe>
   </td></tr>
               <tr class="aiomatic-title-holder"><td colspan="2"><h2 class="aiomatic-inner-title"><?php echo esc_html__("Automate the AI Taxonomy Description Writing Process:", 'aiomatic-automatic-ai-content-writer');?></h2></td></tr>
               <tr>
               <td class="coderevolution_block_input" colspan="2">
<iframe class="youtube-responsive" src="https://www.youtube.com/embed/1OibwD73JIA" allowfullscreen></iframe>
   </td>
</tr>
<?php
}
?>
        </table>
</div>
        <div id="tab-4" class="tab-content">            
        <table class="widefat">
                  <tr>
                     <td colspan="2">
                        <h2>
                        <?php echo esc_html__("Existing Content Editing:", 'aiomatic-automatic-ai-content-writer');?></h3>
                        <div class="crf_bord cr_color_red cr_width_full"><?php echo esc_html__('Bulk post editing might consume a large number of AI model tokens to complete! Be sure you check', 'aiomatic-automatic-ai-content-writer') . '&nbsp;<a href="https://openai.com/pricing" target="_blank">' .  esc_html__('token pricing', 'aiomatic-automatic-ai-content-writer') . '</a>&nbsp;' .  esc_html__('before you continue. You can filter which posts you need edited. Doing a general site backup is also recommended before doing bulk content editing.', 'aiomatic-automatic-ai-content-writer');?></div>
                     </td>
                  </tr>
                  <tr><td colspan="2">
<?php
    echo esc_html__("INFO: You can change the way the posts are edited by changing settings in the 'Editing Template Manager' tab from above! Also, be sure to save settings before running bulk post editing!", 'aiomatic-automatic-ai-content-writer');
?>
</td></tr>
                  <tr>
                     <td>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Do you want to run manual post editing, now? Please check configuration from below before clicking 'Run Post Editing'.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Manually Run Post Editing Now:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td class="cr_min_100 cr_center">
                     <img id="run_img" src="<?php echo esc_url_raw(plugin_dir_url(dirname(__FILE__)) . 'images/running.gif');?>" alt="Running" class="cr_hidden cr_align_middle" title="status">
                     </td>
                     <td>
                     <div class="codemainfzr">
                     <select id="actions" class="actions" name="aiomatic_bulk_actions" onchange="actionsChangedManual(this.value);" onfocus="this.selectedIndex = 0;">
                     <option value="select" disabled selected><?php echo esc_html__("Select an Action", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="run"><?php echo esc_html__("Run Post Editing", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="test"><?php echo esc_html__("Simulate Post Editing", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select>
                     </div>
                     </td>
                  </tr>
                  <tr>
                     <td colspan="2">
                        <div id="results_shower">
                        </div>
                     </td>
                  </tr>
                  <tr>
                     <td>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Select the way you want to schedule automatic editing of existing posts from your site, using the below settings.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Automatic Editing Of Existing Posts:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td colspan="2">
                     <select id="auto_edit" class="cr_width_full skip-from-processing" onchange="aiomatic_edit_changed();" name="aiomatic_Spinner_Settings[auto_edit]" >
                     <option value="disabled"<?php
                        if ($auto_edit == "disabled") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="wp"<?php
                        if ($auto_edit == "wp") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("WordPress Cron Job", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="external"<?php
                        if ($auto_edit == "external") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("External Cron Job", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select> 
                     </td>
                  </tr>
                  <tr class="hidewp">
                     <td>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Choose how often you want to automatically check for old posts. This will change the cron scheduling time.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Plugin Autorun Interval:", 'aiomatic-automatic-ai-content-writer');?></b>
                        </div>
                     </td>
                     <td colspan="2">
                        <div >
                           <select class="cr_width_full skip-from-processing" id="auto_run_interval" name="aiomatic_Spinner_Settings[auto_run_interval]" >
                              <option value="No"<?php
                                 if ($auto_run_interval == "No") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Disabled", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="monthly"<?php
                                 if ($auto_run_interval == "monthly") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Once a month", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="weekly"<?php
                                 if ($auto_run_interval == "weekly") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Once a week", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="daily"<?php
                                 if ($auto_run_interval == "daily") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Once a day", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="twicedaily"<?php
                                 if ($auto_run_interval == "twicedaily") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Twice a day", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="hourly"<?php
                                 if ($auto_run_interval == "hourly") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Once an hour", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="aiomatic_cron_half"<?php
                                 if ($auto_run_interval == "aiomatic_cron_half") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Once 30 minutes", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="aiomatic_cron_sfert"<?php
                                 if ($auto_run_interval == "aiomatic_cron_sfert") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Once 15 minutes", 'aiomatic-automatic-ai-content-writer');?></option>
                              <option value="aiomatic_cron_ten"<?php
                                 if ($auto_run_interval == "aiomatic_cron_ten") {
                                     echo " selected";
                                 }
                                 ?>><?php echo esc_html__("Once 10 minutes", 'aiomatic-automatic-ai-content-writer');?></option>
                           </select>
                        </div>
                     </td>
                  </tr>
                    <tr class="hideexternal">
                        <td>
                        <div>
                            <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <div class="bws_hidden_help_text cr_min_260px">
                                    <?php
                                    echo esc_html__("Select a secret word that will be used when you run the post editing part of the plugin manually by URL/by cron. See details about this below.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                                </div>
                            </div>
                            <b><?php echo esc_html__("Secret Word Used For Cron Running (Optional):", 'aiomatic-automatic-ai-content-writer');?></b>
                        </td>
                        <td colspan="2">
                        <input type="text" id="secret_word" name="aiomatic_Spinner_Settings[secret_word]" class="skip-from-processing" value="<?php echo esc_html($secret_word);?>" placeholder="<?php echo esc_html__("Input a secret word", 'aiomatic-automatic-ai-content-writer');?>">
                        </td>
                    </tr>
                    <tr class="hideexternal">
                        <td colspan="3">
                        <div>
                            <br/><b><?php echo esc_html__("If you want to schedule the cron event manually in your server, to allow recurring editing of existing posts on your site, you should schedule this address:", 'aiomatic-automatic-ai-content-writer');?> <span class="cr_red"><?php if($secret_word != '') { echo esc_url(get_site_url() . '/?run_aiomatic_edit=' . urlencode($secret_word));} else { echo esc_html__('You must enter a secret word above, to use this feature.', 'aiomatic-automatic-ai-content-writer'); }?></span><br/><?php if($secret_word != '') { echo esc_html__("Example:", 'aiomatic-automatic-ai-content-writer') . '&nbsp;<span class="cr_red">15,45****wget -q -O /dev/null ' . esc_url(get_site_url() . '/?run_aiomatic_edit=' . urlencode($secret_word)) . '</span>';}?></b>
                        </div>
                        <br/><br/>
                        </td>
                    </tr>
               </table>
               <br/>
               <table class="widefat">
                  <tr>
                     <td colspan="2">
                        <h2>
                        <?php echo esc_html__("Bulk AI Editing Settings:", 'aiomatic-automatic-ai-content-writer');?></h3>
                     </td>
                  </tr>
        <tr>
            <td class="ai-flex">
                    <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                        <div class="bws_hidden_help_text cr_min_260px disable_drag">
                        <?php
                            echo esc_html__("Select a AI Content Editor template to be used for bulk content editing.", 'aiomatic-automatic-ai-content-writer');
                            ?>
                        </div>
                    </div>
                    <b><?php echo esc_html__("Use This AI Content Editor Template For Manual Post Editing:", 'aiomatic-automatic-ai-content-writer');?></b>
            </td>
            <td>
            <select title="<?php echo esc_html__('Select an AI Content Editor Template to be used', 'aiomatic-automatic-ai-content-writer');?>" name="aiomatic_Spinner_Settings[use_template_manual]" class="skip-from-processing cr_width_full" id="bulk_use_select_template">
<?php
if(!empty($temp_list))
{
?>
<option value="" <?php if($use_template_manual == ''){ echo ' selected';}?> ><?php echo esc_html__("Use currently saved configuration", 'aiomatic-automatic-ai-content-writer');?></option>
<?php
foreach($temp_list as $templid => $templ)
{
    echo '<option value="' . esc_attr($templid) . '"';
    if($use_template_manual == $templid)
    { 
        echo ' selected';
    }
    echo '>' . esc_html($templ) . ' (' . esc_attr($templid) . ')</option>';
}
}
else
{
echo '<option value="" disabled selected>' . esc_html__("No templates found (use currently saved configuration)", 'aiomatic-automatic-ai-content-writer') . '</option>';
}
?>
            </select>
            </td>
            </tr>
                  <tr>
                     <td>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Set the maximum number of posts to be processed at each run.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Maximum Number Of Posts To Process:", 'aiomatic-automatic-ai-content-writer');?></b>
                        </div>
                     </td>
                     <td>
                        <div>
                           <input type="number" id="max_nr" step="1" min="1" placeholder="Maximum Post Count" name="aiomatic_Spinner_Settings[max_nr]" class="skip-from-processing" value="<?php echo esc_html($max_nr);?>"/>
                        </div>
                     </td>
                  </tr>
                  <tr>
                     <td>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Set a delay to wait after each request. This is useful for rate limiting purposes. This is optional. To disable this feature, leave it blank.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Delay Between Requests (Milliseconds):", 'aiomatic-automatic-ai-content-writer');?></b>
                        </div>
                     </td>
                     <td>
                        <div>
                           <input type="number" id="delay_request" step="1" min="1" placeholder="Delay (ms)" name="aiomatic_Spinner_Settings[delay_request]" class="skip-from-processing" value="<?php echo esc_html($delay_request);?>"/>
                        </div>
                     </td>
                  </tr>
                  <tr>
                     <td>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Select if you don't want to process the same post twice using bulk post editing.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Don't Process Same Post Twice:", 'aiomatic-automatic-ai-content-writer');?></b>
                        </div>
                     </td>
                     <td>
                        <div>
                        <input type="checkbox" onchange="sameChanged();" id="no_twice" name="aiomatic_Spinner_Settings[no_twice]" class="skip-from-processing"<?php
    if ($no_twice == 'on')
        echo ' checked ';
?>>
                        </div>
                     </td>
                  </tr>
                  <tr class="hideField">
                     <td>
                        <div>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Set the name of the custom field which will be set to posts which were already edited. Changing this can be useful if you want to reedit already edited posts. The default is: aiomatic_published - You can also use this shortcode here: %%current_date%%", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Duplicate Checking Custom Field Name (Optional):", 'aiomatic-automatic-ai-content-writer');?></b>
                        </div>
                     </td>
                     <td>
                        <div>
                     <input type="text" id="custom_name" name="aiomatic_Spinner_Settings[custom_name]" class="skip-from-processing" value="<?php echo esc_html($custom_name);?>" placeholder="Optional">
                        </div>
                     </td>
                  </tr>
                  <tr>
                     <td>
                        <hr/>
                     </td>
                     <td>
                        <hr/>
                     </td>
                  </tr>
                  <tr>
                     <td colspan="2">
                        <h3><?php echo esc_html__("Which Posts Should Bulk AI Editing Affect:", 'aiomatic-automatic-ai-content-writer');?></h3>
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int,int,int) - use author id [use minus (-) to exclude authors by ID ex. -1,-2,-3]", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Author IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="author_id" name="aiomatic_Spinner_Settings[author_id]" class="skip-from-processing" value="<?php echo esc_html($author_id);?>" placeholder="Author IDs">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - use 'user_nicename' (NOT name)", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Author Names:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="author_name" list="author_name_suggestions" name="aiomatic_Spinner_Settings[author_name]" class="skip-from-processing" value="<?php echo esc_html($author_name);?>" placeholder="Author names">
<?php
$xusers = get_users([
    'number' => 1000,
    'fields' => ['display_name'], 
]);
?>
                    <datalist id="author_name_suggestions">
<?php foreach ($xusers as $user) : ?>
        <option value="<?php echo esc_attr($user->display_name); ?>">
<?php endforeach; ?>
                    </datalist>
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string,string,string) - use category slugs instead of names. ", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Category Names:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
<?php
$categories = get_terms([
    'taxonomy'   => 'category',
    'hide_empty' => false, 
    'number'     => 1000, 
    'fields'     => 'names', 
]);
?>
                     <input type="text" id="category_name" list="category_name_suggestions" name="aiomatic_Spinner_Settings[category_name]" class="skip-from-processing" value="<?php echo esc_html($category_name);?>" placeholder="Category names">
                     <datalist id="category_name_suggestions">
    <?php foreach ($categories as $category) : ?>
        <option value="<?php echo esc_attr($category); ?>">
    <?php endforeach; ?>
</datalist>
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string,string,string) - use tag slugs instead of names. ", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Tag Names:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="tag_name" name="aiomatic_Spinner_Settings[tag_name]" class="skip-from-processing" value="<?php echo esc_html($tag_name);?>" placeholder="Tag names">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Comma separated list of post IDs to edit.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Post IDs:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="post_id" name="aiomatic_Spinner_Settings[post_id]" class="skip-from-processing" value="<?php echo esc_html($post_id);?>" placeholder="Post ID">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - use post slug.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Post Name:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="post_name" name="aiomatic_Spinner_Settings[post_name]" class="skip-from-processing" value="<?php echo esc_html($post_name);?>" placeholder="Post name">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int) - use page id.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Page ID:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="page_id" name="aiomatic_Spinner_Settings[page_id]" class="skip-from-processing" value="<?php echo esc_html($page_id);?>" placeholder="Page ID">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - use page slug.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Page Name:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="pagename" name="aiomatic_Spinner_Settings[pagename]" class="skip-from-processing" value="<?php echo esc_html($pagename);?>" placeholder="Page name">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int) - use page id. Return just the child Pages.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Post Parent:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="post_parent" name="aiomatic_Spinner_Settings[post_parent]" class="skip-from-processing" value="<?php echo esc_html($post_parent);?>" placeholder="Post parent ID">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string,string) - The post types to return. Valid values are: post, page, revision, attachment, other-custom-post-types. To match any post type enter the keyword: any. The default is post (if left empty).", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Post Type:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
<?php
$post_types = get_post_types(
    [
        'public' => true,
    ],
    'names'
);
?>
                     <input type="text" id="type_post" list="post_type_suggestions" name="aiomatic_Spinner_Settings[type_post]" class="skip-from-processing" value="<?php echo esc_html($type_post);?>" placeholder="Post type">
                     <datalist id="post_type_suggestions">
    <?php foreach ($post_types as $post_type) : ?>
        <option value="<?php echo esc_attr($post_type); ?>">
    <?php endforeach; ?>
</datalist>
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - The post status to return. Valid values are:  publish, pending, draft, auto-draft, future, private, inherit, trash, other-custom-post-statuses", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Post Status:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
<?php
$post_statuses = get_post_statuses();
?>
                     <input type="text" list="post_status_suggestions" id="post_status" name="aiomatic_Spinner_Settings[post_status]" class="skip-from-processing" value="<?php echo esc_html($post_status);?>" placeholder="Post status">
                     <datalist id="post_status_suggestions">
    <?php foreach ($post_statuses as $status_key => $status_label) : ?>
        <option value="<?php echo esc_attr($status_key); ?>"><?php echo esc_html($status_label); ?></option>
    <?php endforeach; ?>
</datalist>
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int) - number of post to alter. Use 'posts_per_page'=-1 to alter all posts.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Maximum Posts To Change:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="number" min="-1" step="1" id="max_posts" name="aiomatic_Spinner_Settings[max_posts]" class="skip-from-processing" value="<?php echo esc_html($max_posts);?>" placeholder="Max posts">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int) - number of post to displace or pass over.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Search Offset:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="number" min="-1" step="1" id="search_offset" name="aiomatic_Spinner_Settings[search_offset]" class="skip-from-processing" value="<?php echo esc_html($search_offset);?>" placeholder="Post offset">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - Custom field key.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Meta Key Name:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="meta_name" name="aiomatic_Spinner_Settings[meta_name]" class="skip-from-processing" value="<?php echo esc_html($meta_name);?>" placeholder="Meta Key Name">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - Custom field value.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Meta Key Value:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="meta_value" name="aiomatic_Spinner_Settings[meta_value]" class="skip-from-processing" value="<?php echo esc_html($meta_value);?>" placeholder="Meta Key Value">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo sprintf( wp_kses( __( "(string) - Passes along the query string variable from a search.", 'aiomatic-automatic-ai-content-writer'), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ) );
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Search Query:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="text" id="search_query" name="aiomatic_Spinner_Settings[search_query]" class="skip-from-processing" value="<?php echo esc_html($search_query);?>" placeholder="Search query">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int) - 4 digit year (e.g. 2011).", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Year Query:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="number" min="0" step="1" id="year" name="aiomatic_Spinner_Settings[year]" class="skip-from-processing" value="<?php echo esc_html($year);?>" placeholder="Year">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int) - Month number (from 1 to 12).", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Month Query:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="number" min="1" max="12" step="1" id="month" name="aiomatic_Spinner_Settings[month]" class="skip-from-processing" value="<?php echo esc_html($month);?>" placeholder="Month">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(int) - Day of the month (from 1 to 31).", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Day Query:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <input type="number" min="1" max="31" step="1" id="day" name="aiomatic_Spinner_Settings[day]" class="skip-from-processing" value="<?php echo esc_html($day);?>" placeholder="Day">
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("Select which posts should be processed - posts with or without featured images.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Featured Image Status:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <select id="featured_image" name="aiomatic_Spinner_Settings[featured_image]" class="skip-from-processing">
                     <option value="any"<?php
                        if ($featured_image == "any") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Any", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="with"<?php
                        if ($featured_image == "with") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("With Featured Images", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="without"<?php
                        if ($featured_image == "without") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Without Featured Images", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select> 
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - Designates the ascending or descending order of the 'orderby' parameter. Defaultto 'DESC'.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Order Results:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <select id="order" name="aiomatic_Spinner_Settings[order]" class="skip-from-processing">
                     <option value="default"<?php
                        if ($order == "default") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Default", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="DESC"<?php
                        if ($order == "DESC") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Descendent", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="ASC"<?php
                        if ($order == "ASC") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Ascendent", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select> 
                     </td>
                  </tr>
                  <tr>
                     <td>
                           <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                              <div class="bws_hidden_help_text cr_min_260px">
                                 <?php
                                    echo esc_html__("(string) - Sort retrieved posts by parameter. Defaults to 'date'.", 'aiomatic-automatic-ai-content-writer');
                                    ?>
                              </div>
                           </div>
                           <b><?php echo esc_html__("Order Results By:", 'aiomatic-automatic-ai-content-writer');?></b>
                     </td>
                     <td>
                     <select id="orderby" name="aiomatic_Spinner_Settings[orderby]" class="skip-from-processing">
                     <option value="default"<?php
                        if ($orderby == "default") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Default", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="date"<?php
                        if ($orderby == "date") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Date", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="none"<?php
                        if ($orderby == "none") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("None", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="ID"<?php
                        if ($orderby == "ID") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("ID", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="author"<?php
                        if ($orderby == "author") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Author", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="title"<?php
                        if ($orderby == "title") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Title", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="date"<?php
                        if ($orderby == "date") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Date", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="modified"<?php
                        if ($orderby == "modified") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Modified", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="parent"<?php
                        if ($orderby == "parent") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Parent", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="rand"<?php
                        if ($orderby == "rand") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Random", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="comment_count"<?php
                        if ($orderby == "comment_count") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Comment Count", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="menu_order"<?php
                        if ($orderby == "menu_order") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Menu Order", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="meta_value"<?php
                        if ($orderby == "meta_value") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Meta Value", 'aiomatic-automatic-ai-content-writer');?></option>
                     <option value="meta_value_num"<?php
                        if ($orderby == "meta_value_num") {
                            echo " selected";
                        }
                        ?>><?php echo esc_html__("Meta Value Number", 'aiomatic-automatic-ai-content-writer');?></option>
                     </select> 
                     </td>
                  </tr>
               </table>
</div>
    <div><p class="crsubmit"><input type="submit" name="btnSubmit" id="btnSubmit" class="button button-primary" onclick="unsaved = false;" value="<?php echo esc_html__("Save Settings", 'aiomatic-automatic-ai-content-writer');?>"/></p></div><div>
    <?php echo esc_html__("New! You can use the [aicontent]Your Prompt[/aicontent] shortcode in this or other", 'aiomatic-automatic-ai-content-writer') . " <a href='https://1.envato.market/coderevolutionplugins' target='_blank'>" . esc_html__("'omatic plugins created by CodeRevolution", 'aiomatic-automatic-ai-content-writer') . "</a>" .  esc_html__(", click for details:", 'aiomatic-automatic-ai-content-writer');?>&nbsp;<a href="https://coderevolution.ro/knowledge-base/faq/how-to-create-ai-generated-content-from-any-plugin-built-by-coderevolution/" target="_blank"><img src="https://i.ibb.co/WGQ1rtF/ai.webp" alt="artificial-intelligence-badge" title="AI content generator support, when used together with the Aiomatic plugin"></a><br/><br/><a href="https://www.youtube.com/watch?v=5rbnu_uis7Y" target="_blank"><?php echo esc_html__("Nested Shortcodes also supported!", 'aiomatic-automatic-ai-content-writer');?></a><br/>
</div>
    </form>
    <div id="running_status_ai"></div>
    </div>
<?php
}
function aiomatic_save_editors($data) {
    check_admin_referer( 'aiomatic_save_edits', '_aiomaticr_nonce_edits' );
    $data = $_POST['aiomatic_Editor_Rules'];
    if(isset($data['use_template_manual']))
    {
        $cat_cont = 0;
        $editors = array();
        for($i = 0; $i < sizeof($data['use_template_manual']); ++$i) 
        {
            $bundle = array();
            $use_template_manual = trim( sanitize_text_field( $data['use_template_manual'][$i] ) );
            $bundle[] = $use_template_manual;
            $bundle[] = trim( sanitize_text_field( $data['post_posts'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['post_pages'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['post_custom'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['except_type'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['active'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['only_type'][$i] ) );
            if($i == sizeof($data['use_template_manual']) - 1)
            {
                if(isset($data['disabled_categories']))
                {
                    $bundle[]     = $data['disabled_categories'];
                }
                else
                {
                    if(!isset($data['disabled_categories' . $cat_cont]))
                    {
                        $cat_cont++;
                    }
                    if(!isset($data['disabled_categories' . $cat_cont]))
                    {
                        $bundle[]     = array('aiomatic_no_category_12345678');
                    }
                    else
                    {
                        $bundle[]     = $data['disabled_categories' . $cat_cont];
                    }
                }
            }
            else
            {
                if(!isset($data['disabled_categories' . $cat_cont]))
                {
                    $cat_cont++;
                }
                if(!isset($data['disabled_categories' . $cat_cont]))
                {
                    $bundle[]     = array('aiomatic_no_category_12345678');
                }
                else
                {
                    $bundle[]     = $data['disabled_categories' . $cat_cont];
                }
            }
            $bundle[] = trim( sanitize_text_field( $data['rule_description'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['disable_tags'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['disable_users'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['enable_users'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['featured_status'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['enable_field'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['disable_field'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['enable_tags'][$i] ) );
            $bundle[] = trim( sanitize_text_field( $data['seo_status'][$i] ) );
            if ($use_template_manual == '') 
            {
                $cat_cont++;
                continue; 
            }
            else 
            { 
                $editors[$i] = $bundle; 
            }
            $cat_cont++;
        }
        aiomatic_update_option('aiomatic_Editor_Rules', $editors);
    }
}
if (isset($_POST['aiomatic_Editor_Rules'])) {
	add_action('admin_init', 'aiomatic_save_editors');
}
function aiomatic_expand_editors($temp_list) 
{
   $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
   if(empty(trim($aiomatic_Main_Settings['app_id'])))
   {
       return 'You need to add an API key in plugin settings for this to work!';
   }
   $editors = get_option('aiomatic_Editor_Rules');
   $output = '';
   if (!empty($editors)) {
      $name = md5(get_bloginfo());
      wp_register_script($name . '-editor-extra-script', '');
      wp_enqueue_script($name . '-editor-extra-script');
      foreach ($editors as $cont => $bundle[]) {
              $bundle_values = array_values($bundle); 
              $myValues = $bundle_values[$cont];
              
              $array_my_values = array_values($myValues);
              for($iji=0;$iji<count($array_my_values);++$iji){if(is_string($array_my_values[$iji])){$array_my_values[$iji]=stripslashes($array_my_values[$iji]);}} 
              $use_template_manual = $array_my_values[0];
              $post_posts = $array_my_values[1];
              $post_pages = $array_my_values[2];
              $post_custom = $array_my_values[3];
              $except_type = $array_my_values[4];
              $active = $array_my_values[5];
              $only_type = $array_my_values[6];
              $disabled_categories = $array_my_values[7];
              $rule_description = $array_my_values[8];
              $disable_tags = $array_my_values[9];
              $disable_users = $array_my_values[10];
              $enable_users = $array_my_values[11];
              $featured_status = $array_my_values[12];
              $enable_field = $array_my_values[13];
              $disable_field = $array_my_values[14];
              $enable_tags = $array_my_values[15];
              $seo_status = $array_my_values[16];
              if($rule_description == '')
              {
                 $rule_description = $cont;
              }
              wp_add_inline_script($name . '-editor-extra-script', 'aiomaticCreateAdmin(' . esc_html($cont) . ');', 'after');
         $output .= '
         <tr>
            <td class="cr_td_xo"><input type="text" name="aiomatic_Editor_Rules[rule_description][]" id="rule_description' . esc_html($cont) . '" class="cr_center" placeholder="Rule ID" value="' . esc_html($rule_description) . '" class="cr_width_full"/></td>
            <td class="cr_min_100"><select title="' . esc_html__('Select an AI Content Editor Template to be used', 'aiomatic-automatic-ai-content-writer') . '" name="aiomatic_Editor_Rules[use_template_manual][]" class="skip-from-processing cr_width_full">';
    $output .= '<option value="default"';
    if($use_template_manual == 'default')
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Use currently saved configuration", 'aiomatic-automatic-ai-content-writer') . '</option>';
    foreach($temp_list as $templid => $templ)
    {
        $output .= '<option value="' . esc_attr($templid) . '"';
        if($use_template_manual == $templid)
        {
            $output .= ' selected';
        }
        $output .= '>' . esc_html($templ) . ' (' . esc_attr($templid) . ')</option>';
    }
$output .= '</select></td>
            <td class="cr_width_70">
         <center><input type="button" id="mybtnfzr' . esc_html($cont) . '" value="Settings"></center>
         <div id="mymodalfzr' . esc_html($cont) . '" class="codemodalfzr">
<div class="codemodalfzr-content">
<div class="codemodalfzr-header">
<span id="aiomatic_close' . esc_html($cont) . '" class="codeclosefzr">&times;</span>
<h2>' . esc_html__('Rule', 'aiomatic-automatic-ai-content-writer') . ' <span class="cr_color_white">ID ' . esc_html($cont) . '</span> ' . esc_html__('Advanced Settings', 'aiomatic-automatic-ai-content-writer') . '</h2>
</div>
<div class="codemodalfzr-body">
<div class="table-responsive">
<table class="responsive table cr_main_table_nowr">
<tr><td colspan="2"><h2>' . esc_html__('What to Restrict', 'aiomatic-automatic-ai-content-writer') . ':</h2></td></tr>
<tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text" >' . esc_html__('Do you want to enable automatically editing of WordPress \'posts\'?', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Enable Editing of 'Posts':", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <select name="aiomatic_Editor_Rules[post_posts][]" class="skip-from-processing cr_width_full">
                     <option value="yes"';
    if($post_posts == "yes")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Enable", 'aiomatic-automatic-ai-content-writer') . '</option>
            <option value="on"';
    if($post_posts == "on")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Disable", 'aiomatic-automatic-ai-content-writer') . '</option>
                  </select>
  </div>
  </td></tr>
<tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text" >' . esc_html__('Do you want to enable automatically editing of WordPress \'pages\'?', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Enable Editing of 'Pages':", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <select name="aiomatic_Editor_Rules[post_pages][]" class="skip-from-processing cr_width_full">
                     <option value="yes"';
    if($post_pages == "yes")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Enable", 'aiomatic-automatic-ai-content-writer') . '</option>
            <option value="on"';
    if($post_pages == "on")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Disable", 'aiomatic-automatic-ai-content-writer') . '</option>
                  </select>
  </div>
  </td></tr>
<tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text" >' . esc_html__('Do you want to enable automatically editing of WordPress \'custom post types\'?', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>'. esc_html__("Enable Editing of 'Custom Post Types':", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <select name="aiomatic_Editor_Rules[post_custom][]" class="skip-from-processing cr_width_full">
            <option value="yes"';
    if($post_custom == "yes")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Enable", 'aiomatic-automatic-ai-content-writer') . '</option>
            <option value="on"';
    if($post_custom == "on")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Disable", 'aiomatic-automatic-ai-content-writer') . '</option>
        </select>
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('If you checked the above checkbox to disable processing of custom post types, you can define here a comma separated list of posts types which should still be process (excepted from skipping).', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Excepting This Comma Separated List Of Custom Post Types:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <input type="text" name="aiomatic_Editor_Rules[except_type][]" class="skip-from-processing" value="'.esc_attr($except_type).'" placeholder="Excepted custom post types">
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('If you enabled custom post type processing and want to set a comma separated list of custom post types which should be processed, you can do it here.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Process Only This Comma Separated List Of Custom Post Types:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <input type="text" name="aiomatic_Editor_Rules[only_type][]" class="skip-from-processing" value="'.esc_attr($only_type).'" placeholder="Process only these custom post types">
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('Do you want to disable automatically editing of WordPress categories?', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Disable Editing of Selected Post Categories:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>';
    $cat_args   = array(
        'orderby' => 'name',
        'hide_empty' => 0,
        'order' => 'ASC'
    );
$output .= '<select name="aiomatic_Editor_Rules[disabled_categories' . esc_html($cont) . '][]" multiple class="ai_resize_vertical skip-from-processing">
<option value="aiomatic_no_category_12345678"';
if(!is_array($disabled_categories))
{
    $disabled_categories = array($disabled_categories);
}
if(count($disabled_categories) == 1)
{
    foreach($disabled_categories as $dc)
    {
        if ("aiomatic_no_category_12345678" == $dc) {
            $output .= ' selected';
            break;
        }
    }
}
$output .= '>' . esc_html__("Do Not Check Categories", 'aiomatic-automatic-ai-content-writer') . '</option>';
$categories = get_categories($cat_args);
foreach ($categories as $category) 
{
    $output .= '<option value="' . esc_html($category->term_id) . '"';
    foreach($disabled_categories as $dc)
    {
        if($dc == $category->term_id)
        {
            $output .= ' selected';
        }
    }
    $output .= '>' . esc_html(sanitize_text_field($category->name)) . '</option>';
}
$output .= '</select>
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('Input the tags for which you want to disable editing. You can enter more tags, separated by comma. Ex: cars, vehicles, red, luxury. To disable this feature, leave this field blank.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Disable Editing of Selected Post Tags:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <textarea rows="1" name="aiomatic_Editor_Rules[disable_tags][]" class="skip-from-processing" placeholder="Please insert the tags for which you want to disable editing">' . esc_textarea($disable_tags) . '</textarea>
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('Input the tags for which you want to enable editing. You can enter more tags, separated by comma. Ex: cars, vehicles, red, luxury. To disable this feature, leave this field blank.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Enable Editing of Selected Post Tags:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <textarea rows="1" name="aiomatic_Editor_Rules[enable_tags][]" class="skip-from-processing" placeholder="Please insert the tags for which you want to enable editing">' . esc_textarea($enable_tags) . '</textarea>
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('Input the author user IDs for which you want to disable editing. You can enter more user IDs, separated by comma. To disable this feature, leave this field blank.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Disable Editing Of Posts By Author User IDs:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <textarea rows="1" name="aiomatic_Editor_Rules[disable_users][]" class="skip-from-processing" placeholder="Please insert the user IDs for which you want to disable editing">' . esc_textarea($disable_users) . '</textarea>
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('Input the author user IDs for which you want to enable editing. You can enter more user IDs, separated by comma. To disable this feature, leave this field blank.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Enable Editing of Posts By Author User IDs:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <textarea rows="1" name="aiomatic_Editor_Rules[enable_users][]" class="skip-from-processing" placeholder="Please insert the user IDs for which you want to enable editing">' . esc_textarea($enable_users) . '</textarea>
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('Edit posts with this custom field value. Syntax: custom_field_name => custom_field_value. To accept any value, add a * in the value field. To disable this feature, leave this field blank.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Enable Editing If Custom Field Has Value:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <textarea rows="1" name="aiomatic_Editor_Rules[enable_field][]" class="skip-from-processing" placeholder="custom_field_name => custom_field_value">' . esc_textarea($enable_field) . '</textarea>
  </div>
  </td></tr>
  <tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text">' . esc_html__('Do not edit posts with this custom field value. Syntax: custom_field_name => custom_field_value. To accept any value, add a * in the value field. To disable this feature, leave this field blank.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>' . esc_html__("Disable Editing If Custom Field Has Value:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <textarea rows="1" name="aiomatic_Editor_Rules[disable_field][]" class="skip-from-processing" placeholder="custom_field_name => custom_field_value">' . esc_textarea($disable_field) . '</textarea>
  </div>
  </td></tr>
<tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text" >' . esc_html__('Set the featured image status of edited posts.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>'. esc_html__("Featured Image Status:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <select name="aiomatic_Editor_Rules[featured_status][]" class="skip-from-processing cr_width_full">
            <option value="any"';
    if($featured_status == "any")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Don't Check", 'aiomatic-automatic-ai-content-writer') . '</option>
            <option value="yes"';
    if($featured_status == "yes")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Has Featured Image", 'aiomatic-automatic-ai-content-writer') . '</option>
            <option value="no"';
    if($featured_status == "no")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Does Not Have Featured Image", 'aiomatic-automatic-ai-content-writer') . '</option>
        </select>
  </div>
  </td></tr>
<tr>
<td class="cr_min_width_200">
<div>
  <div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                           <div class="bws_hidden_help_text" >' . esc_html__('Set the featured image status of edited posts.', 'aiomatic-automatic-ai-content-writer') . '
         </div>
      </div>
      <b>'. esc_html__("SEO Description Status:", 'aiomatic-automatic-ai-content-writer') . '</b>
      
      </td><td>
      <select name="aiomatic_Editor_Rules[seo_status][]" class="skip-from-processing cr_width_full">
            <option value="any"';
    if($seo_status == "any")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Don't Check", 'aiomatic-automatic-ai-content-writer') . '</option>
            <option value="yes"';
    if($seo_status == "yes")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Has SEO Description", 'aiomatic-automatic-ai-content-writer') . '</option>
            <option value="no"';
    if($seo_status == "no")
    {
        $output .= ' selected';
    }
    $output .= '>' . esc_html__("Does Not Have SEO Description", 'aiomatic-automatic-ai-content-writer') . '</option>
        </select>
  </div>
  </td></tr>
</table></div> 
</div>
<div class="codemodalfzr-footer">
<br/>
<h3 class="cr_inline">Aiomatic Restrictions</h3><span id="aiomatic_ok' . esc_html($cont) . '" class="codeokfzr cr_inline">OK&nbsp;</span>
<br/><br/>
</div>
</div>

</div>     
              </td>
              <td class="cr_30 cr_center" ><span class="wpaiomatic-delete">X</span></td>
                  <td class="cr_short_td">
                  <select name="aiomatic_Editor_Rules[active][]" class="cr_width_full">
      <option value="1" ';
         if($active === '1')
         {
               $output .= 'selected="selected"';
         }
         $output .= '>' . esc_html__('Yes', 'aiomatic-automatic-ai-content-writer') . '</option>
         <option value="0" ';
         if($active === '0')
         {
               $output .= 'selected="selected"';
         }
         $output .= '>' . esc_html__('No', 'aiomatic-automatic-ai-content-writer') . '</option></select></td>
         </tr>	
         ';
              $cont = $cont + 1;
      }
   }
   return $output;
}
?>