<?php
function aiomatic_playground_panel()
{
   $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
   if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
   {
      ?>
<h1><?php echo esc_html__("You must add an OpenAI/AiomaticAPI API Key into the plugin's 'Settings' menu before you can use this feature!", 'aiomatic-automatic-ai-content-writer');?></h1>
<?php
return;
   }
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.edit-prompt').forEach(button => {
        button.addEventListener('click', (e) => {
            const card = e.target.closest('.prompt-card');
            const textEl = card.querySelector('.prompt-text');
            const textarea = card.querySelector('.edit-textarea');
            const saveBtn = card.querySelector('.save-prompt');

            textEl.style.display = 'none';
            textarea.style.display = 'block';
            e.target.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        });
    });

    document.querySelectorAll('.save-prompt').forEach(button => {
        button.addEventListener('click', (e) => {
            const card = e.target.closest('.prompt-card');
            const textEl = card.querySelector('.prompt-text');
            const textarea = card.querySelector('.edit-textarea');
            const category = card.querySelector('.prompt-category').textContent;
            const oldPrompt = textEl.dataset.original;
            const newPrompt = textarea.value;

            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'update_prompt',
                    old_prompt: oldPrompt,
                    new_prompt: newPrompt,
                    category: category,
                    _wpnonce: '<?php echo wp_create_nonce('update_prompt_nonce'); ?>'
                })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      textEl.textContent = newPrompt;
                      textEl.dataset.original = newPrompt;
                      textEl.style.display = 'block';
                      textarea.style.display = 'none';
                      e.target.style.display = 'none';
                      card.querySelector('.edit-prompt').style.display = 'inline-block';
                  } else {
                      alert('Error: ' + JSON.stringify(data));
                  }
              });
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('add-prompt-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const category = form.category.value.trim();
        const prompt = form.prompt.value.trim();

        if (!category || !prompt) return;

        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'add_prompt',
                category,
                prompt,
                _wpnonce: '<?php echo wp_create_nonce('update_prompt_nonce'); ?>'
            })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                alert('Prompt added.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });

    document.querySelectorAll('.delete-prompt').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const card = e.target.closest('.prompt-card');
            const prompt = card.querySelector('.prompt-text').dataset.original;
            const category = card.querySelector('.prompt-category').textContent;

            if (!confirm('Are you sure you want to delete this prompt?')) return;

            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'delete_prompt',
                    category,
                    prompt,
                    _wpnonce: '<?php echo wp_create_nonce('update_prompt_nonce'); ?>'
                })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    alert('Prompt deleted.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });
    });

});
</script>
<div class="wp-header-end"></div>
<div class="wrap gs_popuptype_holder seo_pops">
<h2 class="cr_center"><?php echo esc_html__("Aiomatic Playground", 'aiomatic-automatic-ai-content-writer');?></h2>
</div>
<div class="wrap gs_popuptype_holder">
        <nav class="nav-tab-wrapper">
            <a href="#tab-0" class="nav-tab"><?php echo esc_html__("Tutorial", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-13" class="nav-tab"><?php echo esc_html__("Prompt Library", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-14" class="nav-tab"><?php echo esc_html__("Model Comparison", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-1" class="nav-tab"><?php echo esc_html__("Text Completion", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-2" class="nav-tab"><?php echo esc_html__("Text Editing", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-3" class="nav-tab"><?php echo esc_html__("DALL-E 2 Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-8" class="nav-tab"><?php echo esc_html__("DALL-E 3 Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-81" class="nav-tab"><?php echo esc_html__("gpt-image-1 Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-4" class="nav-tab"><?php echo esc_html__("Stable Diffusion Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-40" class="nav-tab"><?php echo esc_html__("Stable Diffusion Image Editor", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-11" class="nav-tab"><?php echo esc_html__("Midjourney Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-41" class="nav-tab"><?php echo esc_html__("Google Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-12" class="nav-tab"><?php echo esc_html__("Replicate Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-15" class="nav-tab"><?php echo esc_html__("Ideogram Image Generator", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-5" class="nav-tab"><?php echo esc_html__("Aiomatic Chat", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-6" class="nav-tab"><?php echo esc_html__("Whisper Speech To Text", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-7" class="nav-tab"><?php echo esc_html__("Text Moderation", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-9" class="nav-tab"><?php echo esc_html__("Plagiarism Checker", 'aiomatic-automatic-ai-content-writer');?></a>
            <a href="#tab-10" class="nav-tab"><?php echo esc_html__("AI Content Checker", 'aiomatic-automatic-ai-content-writer');?></a>
        </nav>
        <div id="tab-0" class="tab-content">
        <br/>
        <h2><?php echo esc_html__("AI Playground", 'aiomatic-automatic-ai-content-writer');?></h2>
        <p>
        <?php echo esc_html__("Welcome to this comprehensive tutorial on the 'AI Playground' functionality of the Aiomatic plugin. This powerful tool harnesses the capabilities of artificial intelligence to provide a wide range of features that can enhance your digital experience. In this tutorial, we'll cover several key functionalities of the AI Playground, including text completion, text editing, image generation using AI technologies like DALL-E 2 and Stable Diffusion, a chatbot feature, speech-to-text conversion using the Whisper API, and text moderation. Each of these features can be used in various ways to generate and manage content, interact with users, and more.", 'aiomatic-automatic-ai-content-writer');?>
</p>
<p>
<?php echo esc_html__("Please note that you will also be able to use the shortcodes provided at the bottom of the forms which can be used in the AI Playground. These will add the same forms also to the front end of your site.", 'aiomatic-automatic-ai-content-writer');?>
</p>
<p>
<?php echo esc_html__("Please check below the available playgrounds to test the plugin's features:", 'aiomatic-automatic-ai-content-writer');?>
</p>
<h4><?php echo esc_html__("Text Completion", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("Text completion is a feature where the AI can continue a text entered by the user. To use this feature, you would typically enter a piece of text, and the AI would generate a continuation of that text. This could be used for a variety of purposes, such as generating ideas for a story or completing a sentence in a natural-sounding way.", 'aiomatic-automatic-ai-content-writer');?>

<h4><?php echo esc_html__("Text Editing", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("Text editing is a feature where the AI can be instructed to edit a text in multiple different ways. For example, you might input a piece of text and ask the AI to rewrite it in a more formal or informal tone, to simplify it, or to correct any grammatical errors. This could be useful for improving the quality of written content or adapting it for different audiences.", 'aiomatic-automatic-ai-content-writer');?>

<h4><?php echo esc_html__("Image Generation Using DALL-E 2 and Stable Diffusion", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("Image generation is a feature where the AI generates images based on prompts. You would typically enter a text prompt, and the AI would generate an image that represents that prompt. This could be used for a variety of creative purposes, such as generating artwork or visualizing concepts. Please note that as of my last update in September 2021, DALL-E 2 and Stable Diffusion were not released or announced, so I can't provide specific details about these technologies.", 'aiomatic-automatic-ai-content-writer');?>

<h4><?php echo esc_html__("Chatbot Feature", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("The chatbot feature allows you to chat with an AI bot, ask questions, and get replies. You would typically enter a question or statement, and the AI would generate a response. This could be used for a variety of purposes, such as answering frequently asked questions, providing customer support, or just having a conversation.", 'aiomatic-automatic-ai-content-writer');?>

<h4><?php echo esc_html__("Speech to Text Using the Whisper API", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("Speech to text is a feature where the AI converts speech to text. You would typically record a piece of audio, and the AI would transcribe it into text. This could be useful for a variety of purposes, such as transcribing interviews, dictating notes, or making audio content more accessible.", 'aiomatic-automatic-ai-content-writer');?>

<h4><?php echo esc_html__("Text Moderation", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("Text moderation is a feature where the AI filters unwanted content from your site. You would typically set up rules or criteria for what constitutes unwanted content, and the AI would review incoming content and filter out anything that meets those criteria. This could be used for a variety of purposes, such as preventing spam, blocking offensive content, or maintaining a positive community environment.", 'aiomatic-automatic-ai-content-writer');?>
        
<h4><?php echo esc_html__("Plagiarism Checker", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("Check text for plagiarism, using the PlagiarismCheck API.", 'aiomatic-automatic-ai-content-writer');?>

<h4><?php echo esc_html__("AI Content Detector", 'aiomatic-automatic-ai-content-writer');?></h4>

<?php echo esc_html__("Check texts and detect if are fully AI generated or if they contain chunks of AI generated content, using the PlagiarismCheck API.", 'aiomatic-automatic-ai-content-writer');?>

<h4><a href="https://platform.openai.com/playground/chat" target="_blank"><?php echo esc_html__("Check Also OpenAI's Playground", 'aiomatic-automatic-ai-content-writer');?></a></h4>
<br/>
        </div>
        <div id="tab-1" class="tab-content">
        <br/>
        <?php echo aiomatic_form_shortcode(array( 'temperature' => 'default', 'top_p' => 'default', 'presence_penalty' => 'default', 'frequency_penalty' => 'default', 'model' => 'default' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-text-completion-form]</b></p>
        </div>
        <div id="tab-2" class="tab-content">
        <br/>
        <?php echo aiomatic_edit_shortcode(array( 'temperature' => 'default', 'top_p' => 'default', 'model' => 'default' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-text-editing-form]</b></p>
        </div>
        <div id="tab-3" class="tab-content">
        <br/>
        <?php echo aiomatic_image_shortcode(array( 'image_size' => 'default', 'image_model' => 'dalle2' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-image-generator-form image_model="dalle2"]</b></p>
        </div>
        <div id="tab-8" class="tab-content">
        <br/>
        <?php echo aiomatic_image_shortcode(array( 'image_size' => 'default', 'image_model' => 'dalle3' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-image-generator-form image_model="dalle3"]</b></p>
        </div>
        <div id="tab-81" class="tab-content">
        <br/>
        <?php echo aiomatic_image_shortcode(array( 'image_size' => 'default', 'image_model' => 'gpt-image-1' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-image-generator-form image_model="gpt-image-1"]</b></p>
        </div>
        <div id="tab-4" class="tab-content">
        <br/>
        <?php echo aiomatic_stable_image_shortcode(array( 'image_size' => 'default' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-stable-image-generator-form]</b></p>
        </div>
        <div id="tab-40" class="tab-content">
        <br/>
        <?php echo aiomatic_image_editor_shortcode(array( 'image_size' => 'default' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-stable-image-editor-form]</b></p>
        </div>
        <div id="tab-41" class="tab-content">
        <br/>
        <?php echo aiomatic_google_image_shortcode(array( ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-google-image-generator-form]</b></p>
        </div>
        <div id="tab-11" class="tab-content">
        <br/>
        <?php echo aiomatic_midjourney_image_shortcode(array( 'image_size' => 'default' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-midjourney-image-generator-form]</b></p>
        </div>
        <div id="tab-12" class="tab-content">
        <br/>
        <?php echo aiomatic_replicate_image_shortcode(array( 'image_size' => 'default' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-replicate-image-generator-form]</b></p>
        </div>
        <div id="tab-15" class="tab-content">
        <br/>
        <?php echo aiomatic_ideogram_image_shortcode(array( 'image_size' => 'default' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-ideogram-image-generator-form]</b></p>
        </div>
        <div id="tab-5" class="tab-content">
        <br/>
        <?php echo aiomatic_chat_shortcode(array( 'temperature' => '', 'top_p' => '', 'presence_penalty' => '', 'frequency_penalty' => '', 'model' => '', 'instant_response' => '', 'show_in_window' => 'off' ));?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-chat-form]</b></p>
        </div>
        <div id="tab-14" class="tab-content">
        <br/>
        <?php echo aiomatic_comparison_form_shortcode(array());?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-comparison-form]</b></p>
        </div>
        <div id="tab-6" class="tab-content">
        <br/>
        <?php echo aiomatic_audio_convert(array());?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-audio-converter]</b></p>
        </div>
        <div id="tab-7" class="tab-content">
        <br/>
        <?php echo aiomatic_text_moderation(array());?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-text-moderation]</b></p>
        </div>
        <div id="tab-9" class="tab-content">
        <br/>
        <?php echo aiomatic_text_plagiarism(array());?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-plagiarism-check]</b></p>
        </div>
        <div id="tab-10" class="tab-content">
        <br/>
        <?php echo aiomatic_text_ai_detector(array());?>
        <br/>
        <p class="cr_image_center"><?php echo esc_html__("Shortcode alternative: ", 'aiomatic-automatic-ai-content-writer');?><b>[aiomatic-ai-detector]</b></p>
        </div>
        <div id="tab-13" class="tab-content">
        <br/>
        <?php
    $upload_dir = wp_upload_dir();
    $prompts_dir = trailingslashit($upload_dir['basedir']) . 'aiomatic';
    $prompts_file = trailingslashit($prompts_dir) . 'prompts.json';

    if (!file_exists($prompts_file)) {
        $default_prompts_file = plugin_dir_path(__FILE__) . 'assets/prompts.json';
        
        if (file_exists($default_prompts_file)) {
            wp_mkdir_p($prompts_dir);
            copy($default_prompts_file, $prompts_file);
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Default prompts file not found.', 'aiomatic-automatic-ai-content-writer') . '</p></div>';
            return;
        }
    }
    $prompts_json = file_get_contents($prompts_file);
    $prompts_data = json_decode($prompts_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error decoding prompts JSON file.', 'aiomatic-automatic-ai-content-writer') . '</p></div>';
        return;
    }

    $all_prompts = array();
    $categories = array();
    foreach ($prompts_data as $category => $prompts_list) {
        $categories[] = $category;
        foreach ($prompts_list as $prompt) {
            $all_prompts[] = array(
                'category' => $category,
                'prompt' => $prompt
            );
        }
    }
    $categories = array_unique($categories);
    
    $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

    if ($search_term !== '') {
        $all_prompts = array_filter($all_prompts, function($prompt_item) use ($search_term) {
            return (stripos($prompt_item['prompt'], $search_term) !== false) ||
                   (stripos($prompt_item['category'], $search_term) !== false);
        });
        $all_prompts = array_values($all_prompts);
    }
    if ($selected_category !== '') {
        $all_prompts = array_filter($all_prompts, function($prompt_item) use ($selected_category) {
            return $prompt_item['category'] === $selected_category;
        });
        $all_prompts = array_values($all_prompts);
    }

    $prompts_per_page = 12;
    $total_prompts = count($all_prompts);
    $total_pages = ceil($total_prompts / $prompts_per_page);
    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $prompts_per_page;
    $prompts_to_display = array_slice($all_prompts, $offset, $prompts_per_page);
?>

<div class="wrap">
    <div class="keyword-filters">
    <h1><?php echo esc_html__('Prompt Library', 'aiomatic-automatic-ai-content-writer'); ?></h1>
    <h2><?php echo esc_html__('Filter by Keywords', 'aiomatic-automatic-ai-content-writer'); ?></h2>
    <p><?php echo esc_html__('Select from a variety of example AI prompts to use in your projects.', 'aiomatic-automatic-ai-content-writer'); ?></p>
    </div>
    <form method="get" action="" class="search-form">
        <input type="hidden" name="page" value="aiomatic_playground_panel" />
        <input type="text" name="s" id="prompt-search" class="search-input" placeholder="<?php echo esc_attr__('Search prompts...', 'aiomatic-automatic-ai-content-writer'); ?>" value="<?php echo esc_attr($search_term); ?>" />
        <input type="submit" value="<?php echo esc_attr__('Search', 'aiomatic-automatic-ai-content-writer'); ?>" class="search-button" />
    </form>

    <div class="category-filters">
    <h2><?php echo esc_html__('Filter by Category', 'aiomatic-automatic-ai-content-writer'); ?></h2>
    <div class="category-buttons">
        <a href="<?php echo esc_url(remove_query_arg('category')); ?>" class="category-button <?php echo ($selected_category === '') ? 'active' : ''; ?>">
            <?php echo esc_html__('All', 'aiomatic-automatic-ai-content-writer'); ?>
        </a>
        <?php foreach ($categories as $category): ?>
            <a href="<?php echo esc_url(add_query_arg('category', urlencode($category))); ?>" class="category-button <?php echo ($selected_category === $category) ? 'active' : ''; ?>">
                <?php echo esc_html($category); ?>
            </a>
        <?php endforeach; ?>
    </div>
    </div>
<h2><?php echo esc_html__('Add New Prompt', 'aiomatic-automatic-ai-content-writer'); ?></h2>
<form id="add-prompt-form">
    <label>
        <?php esc_html_e('Category:', 'aiomatic-automatic-ai-content-writer'); ?>
        <input type="text" name="category" required>
    </label>
    <label>
        <?php esc_html_e('Prompt:', 'aiomatic-automatic-ai-content-writer'); ?>
        <textarea name="prompt" required></textarea>
    </label>
    <button type="submit" class="button button-primary"><?php esc_html_e('Add Prompt', 'aiomatic-automatic-ai-content-writer'); ?></button>
</form>
    <div id="prompts-container">
        <?php if (!empty($prompts_to_display)): ?>
            <div class="prompt-cards">
                <?php foreach ($prompts_to_display as $prompt_item): ?>
                    <div class="prompt-card">
                        <span class="prompt-category"><?php echo esc_html($prompt_item['category']); ?></span>
                        <p class="prompt-text" data-original="<?php echo esc_attr($prompt_item['prompt']); ?>"><?php echo esc_html($prompt_item['prompt']); ?></p>
<textarea class="edit-textarea" style="display:none;"><?php echo esc_textarea($prompt_item['prompt']); ?></textarea>
<div class="prompt-actions">
    <button class="button edit-prompt"><?php echo esc_html__('Edit', 'aiomatic-automatic-ai-content-writer'); ?></button>
    <button class="button save-prompt" style="display:none;"><?php echo esc_html__('Save', 'aiomatic-automatic-ai-content-writer'); ?></button>
    <button class="button copy-prompt" style="position: static;" data-prompt="<?php echo esc_attr($prompt_item['prompt']); ?>"><?php echo esc_html__('Copy', 'aiomatic-automatic-ai-content-writer'); ?></button>
    <button class="button delete-prompt"><?php echo esc_html__('Delete', 'aiomatic-automatic-ai-content-writer'); ?></button>
</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php echo esc_html__('No prompts found.', 'aiomatic-automatic-ai-content-writer'); ?></p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $base_url = remove_query_arg(['paged', 'category']);
            if ($search_term !== '') {
                $base_url = add_query_arg('s', urlencode($search_term), $base_url);
            }
            if ($selected_category !== '') {
                $base_url = add_query_arg('category', urlencode($selected_category), $base_url);
            }

            $range = 2;
            $start = max(1, $current_page - $range);
            $end = min($total_pages, $current_page + $range);

            if ($current_page > 1) {
                echo '<a class="page-numbers" href="' . esc_url(add_query_arg('paged', 1, $base_url)) . '">First</a>';
                echo '<a class="page-numbers" href="' . esc_url(add_query_arg('paged', $current_page - 1, $base_url)) . '">Prev</a>';
            }

            if ($start > 1) {
                echo '<span class="page-numbers">...</span>';
            }

            for ($i = $start; $i <= $end; $i++):
                if ($i == $current_page):
                    echo '<span class="page-numbers current">' . esc_html($i) . '</span>';
                else:
                    echo '<a class="page-numbers" href="' . esc_url(add_query_arg('paged', $i, $base_url)) . '">' . esc_html($i) . '</a>';
                endif;
            endfor;

            if ($end < $total_pages) {
                echo '<span class="page-numbers">...</span>';
            }

            if ($current_page < $total_pages) {
                echo '<a class="page-numbers" href="' . esc_url(add_query_arg('paged', $current_page + 1, $base_url)) . '">Next</a>';
                echo '<a class="page-numbers" href="' . esc_url(add_query_arg('paged', $total_pages, $base_url)) . '">Last</a>';
            }
            ?>
        </div>
    <?php endif; ?>
    
    <div id="copy-message" style="display:none;"></div>
</div>

        </div>
    </div>
<?php
}
?>