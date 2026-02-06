"use strict"; 
var initial = '';
function aiomaticLoading(btn)
{
    btn.attr('disabled','disabled');
    if(!btn.find('spinner').length){
        btn.append('<span class="spinner"></span>');
    }
    btn.find('.spinner').css('visibility','unset');
}
function aiomaticRmLoading(btn)
{
    btn.removeAttr('disabled');
    btn.find('.spinner').remove();
}
function assistantSelected(checkID, disableClass)
{
    if(jQuery('#' + checkID).val() == '')
    {
        jQuery('.' + disableClass).find('option').removeAttr('disabled');
    }
    else
    {
        jQuery('.' + disableClass).find('option').attr('disabled', 'disabled');
    }
}
function detectabilityChanged()
{
    if(!jQuery('#no_undetectibility').is(":checked"))
    {
        jQuery('.hideDetectability').show();
    }
    else
    {
        jQuery('.hideDetectability').hide();
    }
}
jQuery(document).ready(function($){
    $(document).on('click', '.notice.is-dismissible', function() {
        var $this = $(this);
        var notice_id = $this.attr('data-dismissible');

        if (notice_id) {
            $.ajax({
                url: mycustommainsettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiomatic_dismiss_notice',
                    nonce: mycustommainsettings.nonce,
                    notice_id: notice_id
                },
                success: function(response) {
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        }
    });
});
function aiomaticRefreshOllama()
{
    var confirm_delete = confirm('Are you sure you want to refresh Ollama model list?');
    if (confirm_delete) {
        document.getElementById('ollamaButton').setAttribute('disabled','disabled');
        jQuery.ajax({
            url: mycustommainsettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'aiomatic_refresh_ollama_models',
                nonce: mycustommainsettings.nonce
            },
            success: function(res) {
                if(res.success == true)
                {
                    document.getElementById('ollamaButton').removeAttribute('disabled');
                    alert('Ollama models refreshed successfully!');
                }
                else
                {
                    alert('Failed to refresh model list: ' + res.data.message);
                    console.log('Failed to refresh model list: ' + JSON.stringify(res));
                    document.getElementById('ollamaButton').removeAttribute('disabled');
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('ollamaButton').removeAttribute('disabled');
                alert('Failed to refresh model list, please try again later!');
                console.log('Error: ' + error);
            }
        });
    }
}
function aiomaticRefreshOpenRouter()
{
    var confirm_delete = confirm('Are you sure you want to refresh OpenRouter model list?');
    if (confirm_delete) {
        document.getElementById('routerButton').setAttribute('disabled','disabled');
        jQuery.ajax({
            url: mycustommainsettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'aiomatic_refresh_openrouter_models',
                nonce: mycustommainsettings.nonce
            },
            success: function(res) {
                if(res.success == true)
                {
                    document.getElementById('routerButton').removeAttribute('disabled');
                    alert('OpenRouter models refreshed successfully!');
                }
                else
                {
                    alert('Failed to refresh model list: ' + res.data.message);
                    console.log('Failed to refresh model list: ' + JSON.stringify(res));
                    document.getElementById('routerButton').removeAttribute('disabled');
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('routerButton').removeAttribute('disabled');
                alert('Failed to refresh model list, please try again later!');
                console.log('Error: ' + error);
            }
        });
    }
}
function aiomaticRefreshReplicate()
{
    var confirm_delete = confirm('Are you sure you want to refresh Replicate model list?');
    if (confirm_delete) {
        document.getElementById('replicateButton').setAttribute('disabled','disabled');
        jQuery.ajax({
            url: mycustommainsettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'aiomatic_refresh_replicate_models',
                nonce: mycustommainsettings.nonce
            },
            success: function(res) {
                if(res.success == true)
                {
                    document.getElementById('replicateButton').removeAttribute('disabled');
                    alert('Replicate models refreshed successfully!');
                    location.reload();
                }
                else
                {
                    alert('Failed to refresh model list: ' + res.data.message);
                    console.log('Failed to refresh model list: ' + JSON.stringify(res));
                    document.getElementById('replicateButton').removeAttribute('disabled');
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('replicateButton').removeAttribute('disabled');
                alert('Failed to refresh model list, please try again later!');
                console.log('Error: ' + error);
            }
        });
    }
}
jQuery(document).ready(function($) 
{
    if($('#aiomatic_roaylty_free_sortable').length)
    {
        var mainCardOrder = $('#aiomatic_roaylty_free_sortable');
        if(mainCardOrder !== undefined)
        {
            mainCardOrder.sortable({
                update: function(event, ui) {
                    var cardOrder = $('#aiomatic_roaylty_free_sortable');
                    if(cardOrder !== undefined)
                    {
                        var scardOrder = cardOrder.sortable('toArray').toString();
                        $('#sortable_cards').val(scardOrder);
                    }
                    else
                    {
                        console.log('Cannot find the aiomatic_roaylty_free_sortable input!');
                    }
                }
            });
        }
        else
        {
            console.log('Error, aiomatic_roaylty_free_sortable input not found!');
        }
    }
});

jQuery(document).ready(function($) 
{
    if($('#aiomatic_serp_sortable').length)
    {
        var mainCardOrder = $('#aiomatic_serp_sortable');
        if(mainCardOrder !== undefined)
        {
            mainCardOrder.sortable({
                update: function(event, ui) {
                    var cardOrder = $('#aiomatic_serp_sortable');
                    if(cardOrder !== undefined)
                    {
                        var scardOrder = cardOrder.sortable('toArray').toString();
                        $('#sortable_cards_serp').val(scardOrder);
                    }
                    else
                    {
                        console.log('Cannot find the aiomatic_serp_sortable input!');
                    }
                }
            });
        }
        else
        {
            console.log('Error, aiomatic_serp_sortable input not found!');
        }
    }
});
    function populate_default_internet()
    {
        jQuery("#internet_prompt").val(`Web search results:
%%web_results%%
Current date: %%current_date%%
Instructions: Using the provided web search results, write a comprehensive reply to the given query. Make sure to cite results using [[number](URL)] notation after the reference. If the provided search results refer to multiple subjects with the same name, write separate answers for each subject.
Query: %%original_query%%`);
    }
    function populate_default_template()
    {
        jQuery("#internet_single_template").val(`[%%result_counter%%]: %%result_title%% %%result_snippet%% 
URL: %%result_link%%`);
    }
    function embeddingsChanged()
    {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][id^="embeddings_"]');
        checkboxes.forEach((checkbox) => 
        {
            const namespaceInput = document.getElementById(checkbox.id + '_namespace');
            if (namespaceInput) 
            {
                namespaceInput.style.display = checkbox.checked ? 'block' : 'none';
            }
        });

        if(jQuery('#embeddings_bulk').is(":checked"))
        {            
            jQuery(".hideEmbeddingsContent").show();
        }
        else
        {
            jQuery(".hideEmbeddingsContent").hide();
        }
    }
    function internetChanged()
    {
        if(jQuery('#internet_bulk').is(":checked"))
        {            
            jQuery(".hideInternetContent").show();
        }
        else
        {
            jQuery(".hideInternetContent").hide();
        }
    }
    function imgChanged()
    {
        if(jQuery('#random_image_sources').is(":checked"))
        {            
            jQuery(".hideImgs").show();
        }
        else
        {
            jQuery(".hideImgs").hide();
        }
    }
    function serpChanged()
    {
        if(jQuery('#random_serp_sources').is(":checked"))
        {            
            jQuery(".hideSERP").show();
        }
        else
        {
            jQuery(".hideSERP").hide();
        }
    }
    function imgCopyChanged()
    {
        if(jQuery("#copy_locally option:selected").val() === 'amazon')
        {
            jQuery(".hideCompress").show();
        }
        else
        {
            if(jQuery("#copy_locally option:selected").val() === 'digital')
            {
                jQuery(".hideCompress").show();
            }
            else
            {
                if(jQuery("#copy_locally option:selected").val() === 'generics3')
                {
                    jQuery(".hideCompress").show();
                }
                else
                {
                    if(jQuery("#copy_locally option:selected").val() === 'wasabi')
                    {
                        jQuery(".hideCompress").show();
                    }
                    else
                    {
                        if(jQuery("#copy_locally option:selected").val() === 'cloudflare')
                        {
                            jQuery(".hideCompress").show();
                        }
                        else
                        {
                            if(jQuery("#copy_locally option:selected").val() === 'on')
                            {            
                                jQuery(".hideCompress").show();
                            }
                            else
                            {
                                jQuery(".hideCompress").hide();
                            }
                        }
                    }
                }
            }
        }
    }
    function formThemeChanged()
    {
        var selected = jQuery('#forms_theme').val();
        var form_background = jQuery('#form_background');
        var form_text_color = jQuery('#form_color');
        var button_color = jQuery('#button_color');
        var button_text_color = jQuery('#button_text_color');
        if(selected == 'light') {
            form_background.val('#ffffff'); // White
            form_text_color.val('#000000'); // Black
            button_color.val('#e0e0e0'); // Light gray
            button_text_color.val('#000000'); // Black
        }
        else if(selected == 'dark') {
            form_background.val('#1e1e1e'); // Near black
            form_text_color.val('#ffffff'); // White
            button_color.val('#333333'); // Dark gray
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'midnight') {
            form_background.val('#2e2e3e'); // Dark blue-gray
            form_text_color.val('#ffffff'); // White
            button_color.val('#4e4e6e'); // Deep blue-gray
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'sunrise') {
            form_background.val('#ffcc66'); // Soft orange-yellow
            form_text_color.val('#663300'); // Dark brown
            button_color.val('#ff9966'); // Warm orange
            button_text_color.val('#663300'); // Dark brown
        }
        else if(selected == 'ocean') {
            form_background.val('#006994'); // Ocean blue
            form_text_color.val('#ffffff'); // White
            button_color.val('#0099cc'); // Lighter ocean blue
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'forest') {
            form_background.val('#2c5f2d'); // Deep green
            form_text_color.val('#ffffff'); // White
            button_color.val('#97bc62'); // Light green
            button_text_color.val('#2c5f2d'); // Deep green
        }
        else if(selected == 'winter') {
            form_background.val('#d0e7ff'); // Icy blue
            form_text_color.val('#002d4d'); // Dark blue
            button_color.val('#b3d9ff'); // Frosty blue
            button_text_color.val('#002d4d'); // Dark blue
        }
        else if(selected == 'twilight') {
            form_background.val('#4b0082'); // Indigo
            form_text_color.val('#ffffff'); // White
            button_color.val('#8a2be2'); // Blue-violet
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'desert') {
            form_background.val('#edc9af'); // Desert sand
            form_text_color.val('#5d3a00'); // Dark brown
            button_color.val('#e4a672'); // Light sand
            button_text_color.val('#5d3a00'); // Dark brown
        }
        else if(selected == 'cosmic') {
            form_background.val('#330033'); // Dark purple
            form_text_color.val('#ffccff'); // Soft pink
            button_color.val('#660066'); // Purple
            button_text_color.val('#ffccff'); // Soft pink
        }
        else if(selected == 'rose') {
            form_background.val('#ffe4e1'); // Soft pink
            form_text_color.val('#800000'); // Dark red
            button_color.val('#ff9999'); // Light rose
            button_text_color.val('#800000'); // Dark red
        }
        else if(selected == 'tropical') {
            form_background.val('#ffcc00'); // Bright yellow
            form_text_color.val('#006600'); // Jungle green
            button_color.val('#ff9900'); // Tropical orange
            button_text_color.val('#006600'); // Jungle green
        }
        else if(selected == 'facebook') {
            form_background.val('#3b5998'); // Facebook blue
            form_text_color.val('#ffffff'); // White
            button_color.val('#8b9dc3'); // Light Facebook blue
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'twitter') {
            form_background.val('#00aced'); // Twitter blue
            form_text_color.val('#ffffff'); // White
            button_color.val('#c0deed'); // Light Twitter blue
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'instagram') {
            form_background.val('#f77737'); // Instagram orange
            form_text_color.val('#ffffff'); // White
            button_color.val('#e1306c'); // Instagram pink
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'whatsapp') {
            form_background.val('#25d366'); // WhatsApp green
            form_text_color.val('#ffffff'); // White
            button_color.val('#075e54'); // Darker green
            button_text_color.val('#ffffff'); // White
        }
        else if(selected == 'linkedin') {
            form_background.val('#0077b5'); // LinkedIn blue
            form_text_color.val('#ffffff'); // White
            button_color.val('#00a0dc'); // Lighter blue
            button_text_color.val('#ffffff'); // White
        }
    }
    function kwChanged()
    {
        if(jQuery('#kw_method').val() == 'ai')
        {            
            jQuery(".kwai").show();
            jQuery(".kwbuiltin").hide();
        }
        else
        {
            jQuery(".kwai").hide();
            jQuery(".kwbuiltin").show();
        }
    }
    function imageAIChanged()
    {
        if(jQuery('#use_image_ai').is(":checked"))
        {            
            jQuery(".hideimgai").show();
        }
        else
        {
            jQuery(".hideimgai").hide();
        }
    }
    function ytKwChanged()
    {
        if(jQuery('#improve_yt_kw').is(":checked"))
        {            
            jQuery(".hideytkw").show();
        }
        else
        {
            jQuery(".hideytkw").hide();
        }
    }
    function embChanged()
    {
        if(jQuery('#rewrite_embedding').is(":checked"))
        {            
            jQuery(".hideEmb").show();
        }
        else
        {
            jQuery(".hideEmb").hide();
        }
    }
    function keyUpdated()
    {
        var enteredText = jQuery('#app_id').val();
        if(enteredText !== undefined)
        {
            var numberOfLineBreaks = (enteredText.match(/\n/g)||[]).length;
            if(numberOfLineBreaks > 0)
            {
                jQuery(".multiplehide").show();
            }
            else
            {
                jQuery(".multiplehide").hide();
            }
        }
    }
    function embeddingsAPIchanged()
    {
        var check = jQuery("#embeddings_api").val();
        if(check == 'qdrant')
        {
            jQuery('.hidePine').hide();
            jQuery('.hideQdr').show();
        }
        else
        {
            if(check == 'pinecone')
            {
                jQuery('.hidePine').show();
                jQuery('.hideQdr').hide();
            }
        }
    }
    function mainChanged()
    {
        embeddingsAPIchanged();
        if(jQuery('#aiomatic-logo').length)
        {
            return;
        }
        imgCopyChanged();
        imgChanged();
        serpChanged();
        embeddingsChanged();
        internetChanged();
        ytKwChanged();
        imageAIChanged();
        keyUpdated();
        embChanged();
        kwChanged();
        if(jQuery('.input-checkbox-ai').is(":checked"))
        {            
            jQuery(".hideMain").show();
        }
        else
        {
            jQuery(".hideMain").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'best' || jQuery("#spin_text option:selected").val() === 'wordai' || jQuery("#spin_text option:selected").val() === 'spinrewriter' || jQuery("#spin_text option:selected").val() === 'spinnerchief' || jQuery("#spin_text option:selected").val() === 'chimprewriter' || jQuery("#spin_text option:selected").val() === 'contentprofessor') 
        {      
            jQuery(".hideBest").show();
        }
        else
        {
            jQuery(".hideBest").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'spinnerchief') 
        {      
            jQuery(".hideChief").show();
        }
        else
        {
            jQuery(".hideChief").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'spinrewriter') 
        {      
            jQuery(".hideSpinRewriterSpecific").show();
        }
        else
        {
            jQuery(".hideSpinRewriterSpecific").hide();
        }
if (mycustommainsettings.best_user == '' || mycustommainsettings.best_password == '')
{
        if(jQuery("#spin_text option:selected").val() === 'best') 
        {      
            jQuery("#bestspin").show();
        }
        else
        {
            jQuery("#bestspin").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'wordai') 
        {      
            jQuery("#wordai").show();
        }
        else
        {
            jQuery("#wordai").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'spinrewriter') 
        {      
            jQuery("#spinrewriter").show();
        }
        else
        {
            jQuery("#spinrewriter").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'spinnerchief') 
        {      
            jQuery("#spinnerchief").show();
        }
        else
        {
            jQuery("#spinnerchief").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'chimprewriter') 
        {      
            jQuery("#chimprewriter").show();
        }
        else
        {
            jQuery("#chimprewriter").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'contentprofessor') 
        {      
            jQuery("#contentprofessor").show();
        }
        else
        {
            jQuery("#contentprofessor").hide();
        }
}
else
{
if(initial == '')
{
    initial = jQuery("#spin_text option:selected").val();
}
if(initial != '' && initial != jQuery("#spin_text option:selected").val())
{
        if(jQuery("#spin_text option:selected").val() === 'best') 
        {      
            jQuery("#bestspin").show();
        }
        else
        {
            jQuery("#bestspin").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'wordai') 
        {      
            jQuery("#wordai").show();
        }
        else
        {
            jQuery("#wordai").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'spinrewriter') 
        {      
            jQuery("#spinrewriter").show();
        }
        else
        {
            jQuery("#spinrewriter").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'spinnerchief') 
        {      
            jQuery("#spinnerchief").show();
        }
        else
        {
            jQuery("#spinnerchief").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'chimprewriter') 
        {      
            jQuery("#chimprewriter").show();
        }
        else
        {
            jQuery("#chimprewriter").hide();
        }
        if(jQuery("#spin_text option:selected").val() === 'contentprofessor') 
        {      
            jQuery("#contentprofessor").show();
        }
        else
        {
            jQuery("#contentprofessor").hide();
        }
}
else
{
    jQuery("#spinrewriter").hide();
    jQuery("#spinnerchief").hide();
    jQuery("#chimprewriter").hide();
    jQuery("#contentprofessor").hide();
    jQuery("#wordai").hide();
    jQuery("#bestspin").hide();
}
}
        if(jQuery('#send_email').is(":checked"))
        {            
            jQuery(".hideMail").show();
        }
        else
        {
            jQuery(".hideMail").hide();
        }
        if(jQuery('#enable_logging').is(":checked"))
        {            
            jQuery(".hideLog").show();
        }
        else
        {
            jQuery(".hideLog").hide();
        }
        if(jQuery('#skip_old').is(":checked"))
        {            
            jQuery(".hideOld").show();
        }
        else
        {
            jQuery(".hideOld").hide();
        }
    }
    window.onload = mainChanged;
    jQuery(document).ready(function(){
        jQuery('span.wpaiomatic-delete').on('click', function(){
            var confirm_delete = confirm('Are you sure you want to delete this rule?');
            if (confirm_delete) 
            {
                var dataid = jQuery(this).attr('data-id');
                if(dataid !== undefined && dataid !== null)
                {
                    jQuery('.aiuniq-' + dataid).remove();
                }
                else
                {
                    jQuery(this).parent().parent().remove();
                }
                jQuery('#myForm').submit();					
            }
        });
        var plugin_slug = mycustomsettings.plugin_slug;
        jQuery('#' + plugin_slug + '_register').on('click', function()
        {
            var ajaxurl = mycustomsettings.ajaxurl;
            var nonce = jQuery('#' + plugin_slug + '_activation_nonce').val();
            var code = jQuery('#' + plugin_slug + '_register_code').val();
            if(code == '')
            {
                alert('You need to enter a purchase code for the activation to work.');
            }
            else
            {
                var thisbut = jQuery(this);
                aiomaticLoading(thisbut);
                var data = {
                    action: 'aiomatic_activation',
                    code: code,
                    nonce: nonce
                };
                jQuery.post(ajaxurl, data, function(response) {
                    aiomaticRmLoading(thisbut);
                    if(response.trim() == 'ok')
                    {
                        location.reload();
                    }
                    else
                    {
                        alert('Error in registration process: ' + response);
                    }
                }).fail( function(xhr) 
                {
                    aiomaticRmLoading(thisbut);
                    alert('Exception in registration process: ' + xhr.statusText);
                });
            }
        });
        jQuery('#' + plugin_slug + '_revoke_license').on('click', function()
        {
            var confirm_delete = confirm('Are you sure you want to revoke your license?');
            if (confirm_delete) 
            {
                var ajaxurl = mycustomsettings.ajaxurl;
                var nonce = jQuery('#' + plugin_slug + '_activation_nonce').val();
                var thisbut = jQuery(this);
                aiomaticLoading(thisbut);
                var data = {
                    action: 'aiomatic_revoke',
                    nonce: nonce
                };
                jQuery.post(ajaxurl, data, function(response) {
                    aiomaticRmLoading(thisbut);
                    if(response.trim() == 'ok')
                    {
                        location.reload();
                    }
                    else
                    {
                        alert('Error in revoking process: ' + response);
                    }
                }).fail( function(xhr) 
                {
                    aiomaticRmLoading(thisbut);
                    alert('Exception in revoking process: ' + xhr.statusText);
            });
            }
        });
    });
    var unsaved = false;
    jQuery(document).ready(function () {
        jQuery("#api_selector").on('change', function(){
            if(jQuery("#api_selector").val() === 'azure') 
            {      
                jQuery(".azurehide").show();
                jQuery(".openhide").hide();
                jQuery(".customhide").hide();
                jQuery("#apilinks").html("<a href='https://portal.azure.com/?microsoft_azure_marketplace_ItemHideKey=microsoft_openai_tip#home' target='_blank'>Azure</a>");
            }
            else
            {
                if(jQuery("#api_selector").val() === 'custom') 
                {
                    jQuery(".azurehide").hide();
                    jQuery(".openhide").hide();
                    jQuery(".customhide").show();
                    jQuery("#apilinks").html("OpenAI Compatible");
                }
                else
                {
                    jQuery(".azurehide").hide();
                    jQuery(".openhide").show();
                    jQuery(".customhide").hide();
                    jQuery("#apilinks").html("<a href='https://platform.openai.com/api-keys' target='_blank'>OpenAI</a>&nbsp;/&nbsp;<a href='https://aiomaticapi.com/api-keys/' target='_blank'>AiomaticAPI</a>");
                }
            }
        });
        if(jQuery("#api_selector").val() === 'azure') 
        {      
            jQuery(".azurehide").show();
            jQuery(".openhide").hide();
            jQuery(".customhide").hide();
            jQuery("#apilinks").html("<a href='https://portal.azure.com/?microsoft_azure_marketplace_ItemHideKey=microsoft_openai_tip#home' target='_blank'>Azure</a>");
        }
        else
        {
            if(jQuery("#api_selector").val() === 'custom') 
            {
                jQuery(".azurehide").hide();
                jQuery(".openhide").hide();
                jQuery(".customhide").show();
                jQuery("#apilinks").html("OpenAI Compatible");
            }
            else
            {
                jQuery(".azurehide").hide();
                jQuery(".openhide").show();
                jQuery(".customhide").hide();
                jQuery("#apilinks").html("<a href='https://platform.openai.com/api-keys' target='_blank'>OpenAI</a>&nbsp;/&nbsp;<a href='https://aiomaticapi.com/api-keys/' target='_blank'>AiomaticAPI</a>");
            }
        }
        jQuery(":input").on('change', function(){
            var classes = this.className;
            var classes = this.className.split(' ');
            var found = jQuery.inArray('actions', classes) > -1;
            if (this.id != 'select-shortcode' && this.id != 'PreventChromeAutocomplete' && this.id != 'editor_select_template' && this.className != 'sc_chat_form_field_prompt_text' && this.id != 'actions' && this.className != 'codemainfzr' && !found)
            {
                unsaved = true;
            }
        });
        function unloadPage(){ 
            if(unsaved){
                return "You have unsaved changes on this page. Do you want to leave this page and discard your changes or stay on this page?";
            }
        }
        window.onbeforeunload = unloadPage;
    });
function revealRec(){document.getElementById("diviIdrec").innerHTML = '<br/>We recommend that you check <b><a href="https://www.elegantthemes.com/affiliates/idevaffiliate.php?id=50837_5_1_16" target="_blank">Divi theme</a></b>, by <b><a href="https://www.elegantthemes.com/affiliates/idevaffiliate.php?id=50837_1_1_3" target="_blank">ElegantThemes</a></b>! It is easy to configure and it looks gorgeous. Check it out now!<br/><br/><a href="https://www.elegantthemes.com/affiliates/idevaffiliate.php?id=50837_5_1_19" target="_blank" rel="nofollow"><img style="border:0px" src="https://3.bp.blogspot.com/-h9TLQozNO6Q/W92Sk80zwjI/AAAAAAAAAjg/JC8sFWAUPzseR4nnjhVNbRQmCnr1ZMu4gCLcBGAs/s1600/divi.jpg" width="468" height="60" alt="Divi WordPress Theme"></a>';}

jQuery(document).ready(function ($) {
    $('#myForm').find('input, select, textarea').on('invalid', function (event) {
        event.preventDefault();
        let $field = $(this);
        let fieldName = $('label[for="' + this.id + '"]').text() || this.name || 'Unnamed field';
        let errorMessage = '';
        if (this.validity.valueMissing) {
            errorMessage = `${fieldName} is required.`;
        } else if (this.validity.typeMismatch) {
            errorMessage = `${fieldName} has an incorrect format.`;
        } else if (this.validity.rangeOverflow) {
            errorMessage = `${fieldName} exceeds the max value (${this.max}).`;
        } else if (this.validity.rangeUnderflow) {
            errorMessage = `${fieldName} is below the min value (${this.min}).`;
        } else if (this.validity.patternMismatch) {
            errorMessage = `${fieldName} does not match the required pattern.`;
        } else if (this.validity.stepMismatch) {
            errorMessage = `${fieldName} has an invalid step value.`;
        } else {
            errorMessage = `${fieldName} has an invalid value.`;
        }
        let $hiddenParent = $field.closest('.hidden, [style*="display: none"], [style*="visibility: hidden"]');
        if ($hiddenParent.length) {
            $hiddenParent.show();
            $field.show().addClass('aiomatic-highlight-error');
        }
        alert(errorMessage); 
        $field.focus();
    });
    $('#myForm').on('submit', function (event) {
        let invalidFields = $(this).find(':invalid');
        if (invalidFields.length > 0) {
            event.preventDefault();
        }
    });
});