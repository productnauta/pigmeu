"use strict";
var initial = '';
function mainChanged()
{
    if(jQuery("#ai_rewriter").val() == 'enabled')
    {            
        jQuery(".hideMain").show();
        visionSelectedAI();
        titleChanged();
        contentChanged();
        slugChanged();
        excerptChanged();
    }
    else
    {
        jQuery(".hideMain").hide();
        jQuery(".hideTitle").hide();
        jQuery(".hideContent").hide();
        jQuery(".hideSlug").hide();
        jQuery(".hideExcerpt").hide();
    }
}
function titleChanged()
{
    if(jQuery("#no_title").val() != 'on')
    {            
        jQuery(".hideTitle").show();
    }
    else
    {
        jQuery(".hideTitle").hide();
    }
}
function enhanceChanged()
{
    if(jQuery('#enhance_articles').is(":checked"))
    {            
        jQuery(".hideEnhance").show();
    }
    else
    {
        jQuery(".hideEnhance").hide();
    }
}
function contentChanged()
{
    if(jQuery("#no_content").val() != 'on')
    {            
        jQuery(".hideContent").show();
        gutChanged();
    }
    else
    {
        jQuery(".hideContent").hide();
    }
}
function gutChanged()
{
    if(jQuery("#enable_gutenberg").val() == '1')
    {            
        jQuery(".hideGut").show();
    }
    else
    {
        jQuery(".hideGut").hide();
    }
}
function slugChanged()
{
    if(jQuery("#no_slug").val() != 'on')
    {            
        jQuery(".hideSlug").show();
    }
    else
    {
        jQuery(".hideSlug").hide();
    }
}
function excerptChanged()
{
    if(jQuery("#no_excerpt").val() != 'on')
    {            
        jQuery(".hideExcerpt").show();
    }
    else
    {
        jQuery(".hideExcerpt").hide();
    }
}
function mainChanged2()
{
    if(jQuery("#ai_featured_image").val() == 'enabled')
    {            
        jQuery(".hideMain2").show();
        mainChangedImg();
    }
    else
    {
        jQuery(".hideMain2").hide();
        jQuery(".hideImg").hide();
    }
}
function mainChanged2e()
{
    if(jQuery("#ai_featured_image_edit").val() == 'enabled')
    {            
        jQuery(".hideMain2e").show();
    }
    else
    {
        jQuery(".hideMain2e").hide();
    }
}
function mainChanged2c()
{
    if(jQuery("#ai_featured_image_edit_content").val() == 'enabled')
    {            
        jQuery(".hideMain2c").show();
    }
    else
    {
        jQuery(".hideMain2c").hide();
    }
}
function mainChangedImg()
{
    if(jQuery("#ai_featured_image_source").val() == '1')
    {            
        jQuery(".hideImg").show();
    }
    else
    {
        jQuery(".hideImg").hide();
    }
}

function aiomatic_audio_changed()
{
    var selected = jQuery('#content_text_speech').val();
    if(selected == 'google')
    {
        jQuery(".hideeleven").hide();
        jQuery(".hideopen").hide();
        jQuery(".hidedid").hide();
        jQuery(".hidegoogle").show();
        jQuery(".hideWideAudio").show();
    }
    if(selected == 'elevenlabs')
    {
        jQuery(".hideeleven").show();
        jQuery(".hideopen").hide();
        jQuery(".hidedid").hide();
        jQuery(".hidegoogle").hide();
        jQuery(".hideWideAudio").show();
    }
    if(selected == 'did')
    {
        jQuery(".hidedid").show();
        jQuery(".hideopen").hide();
        jQuery(".hideeleven").hide();
        jQuery(".hidegoogle").hide();
        jQuery(".hideWideAudio").show();
    }
    if(selected == 'openai')
    {
        jQuery(".hidedid").hide();
        jQuery(".hideopen").show();
        jQuery(".hideeleven").hide();
        jQuery(".hidegoogle").hide();
        jQuery(".hideWideAudio").show();
    }
    if(selected == 'off')
    {
        jQuery(".hideeleven").hide();
        jQuery(".hideopen").hide();
        jQuery(".hidedid").hide();
        jQuery(".hidegoogle").hide();
        jQuery(".hideWideAudio").hide();
    }
}
function aiomatic_speech_changed()
{
    var selected = jQuery('#content_speech_text').val();
    if(selected == 'openai')
    {
        jQuery(".hideSpeechText").show();
    }
    if(selected == 'off')
    {
        jQuery(".hideSpeechText").hide();
    }
}
function mainChanged3()
{
    if(jQuery("#append_spintax").val() == 'append' || jQuery("#append_spintax").val() == 'preppend' || jQuery("#append_spintax").val() == 'inside')
    {            
        jQuery(".hideMain3").show();
        visionSelectedAI3();
    }
    else
    {
        jQuery(".hideMain3").hide();
    }
}
function mainChanged4()
{
    if(jQuery("#add_links").val() == 'enabled')
    {  
        jQuery(".hideMain4").show();
        if(jQuery("#link_method").val() == 'aiomatic')
        {        
            jQuery(".hideMain4a").show();
            jQuery(".hideMain4l").hide();
        }
        else
        {
            jQuery(".hideMain4a").hide();
            jQuery(".hideMain4l").show();
            
            var selected = jQuery('#link_juicer_model').val();
            var found = false;
            aiomatic_object.modelsvision.forEach((model) => {
                let selectedParts = selected.split(':');
                selected = selectedParts[0];
                if(model == selected)
                {
                    found = true;
                }
            });
            if(found == true)
            {
                jQuery(".hideVision9").show();
            }
            else
            {
                jQuery(".hideVision9").hide();
            }
        }
        hideLinks();
    }
    else
    {
        jQuery(".hideMain4").hide();
        jQuery(".hideMain4a").hide();
        jQuery(".hideMain4l").hide();
    }
}
function hideLinks()
{
    if(jQuery("#add_links").val() == 'disabled')
    {
        jQuery(".hidelinks").hide();
    }
    else
    {
        jQuery(".hidelinks").show();
        if(jQuery("#link_type").val() == 'amazon')
        {            
            jQuery(".hidelinksamazon").show();
        }
        else
        {
            jQuery(".hidelinksamazon").hide();
        }
        if(jQuery("#link_type").val() == 'internal' || jQuery("#link_type").val() == 'mixed' || jQuery("#link_type").val() == 'intserp' || jQuery("#link_type").val() == 'intmanserp')
        {            
            jQuery(".hidelinksinternal").show();
        }
        else
        {
            jQuery(".hidelinksinternal").hide();
        }
        if(jQuery("#link_type").val() == 'manual' || jQuery("#link_type").val() == 'mixed' || jQuery("#link_type").val() == 'manserp' || jQuery("#link_type").val() == 'intmanserp')
        {            
            jQuery(".hidelinksmanual").show();
        }
        else
        {
            jQuery(".hidelinksmanual").hide();
        }
        if(jQuery("#link_type").val() == 'serp' || jQuery("#link_type").val() == 'manual' || jQuery("#link_type").val() == 'mixed' || jQuery("#link_type").val() == 'intserp' || jQuery("#link_type").val() == 'manserp' || jQuery("#link_type").val() == 'intmanserp')
        {            
            jQuery(".hidelinksexternal").show();
        }
        else
        {
            jQuery(".hidelinksexternal").hide();
        }
    }
}
function mainChanged5()
{
    if(jQuery("#add_comments").val() == 'enabled')
    {            
        jQuery(".hideMain5").show();
        visionSelectedAI5();
    }
    else
    {
        jQuery(".hideMain5").hide();
    }
}
function mainChanged7()
{
    if(jQuery("#add_cats").val() == 'enabled')
    {            
        jQuery(".hideMain7").show();
        visionSelectedAI7();
    }
    else
    {
        jQuery(".hideMain7").hide();
    }
}
function mainChanged8()
{
    if(jQuery("#add_tags").val() == 'enabled')
    {            
        jQuery(".hideMain8").show();
        visionSelectedAI8();
    }
    else
    {
        jQuery(".hideMain8").hide();
    }
}
function mainChanged10()
{
    if(jQuery("#add_custom").val() == 'enabled')
    {            
        jQuery(".hideMain10").show();
        visionSelectedAI10();
    }
    else
    {
        jQuery(".hideMain10").hide();
    }
}
function mainChanged9()
{
    if(jQuery("#append_toc").val() == 'append' || jQuery("#append_toc").val() == 'preppend' || jQuery("#append_toc").val() == 'heading' || jQuery("#append_toc").val() == 'heading2')
    {            
        jQuery(".hideMain9").show();
    }
    else
    {
        jQuery(".hideMain9").hide();
    }
}
function mainChangedx()
{
    if(jQuery("#ai_duplication").val() == 'enabled')
    {            
        jQuery(".hideDup").show();
        enhanceChanged();
    }
    else
    {
        jQuery(".hideDup").hide();
    }
}
function mainChanged6()
{
    if(jQuery("#add_seo").val() == 'enabled')
    {            
        jQuery(".hideMain6").show();
        visionSelectedAI6();
    }
    else
    {
        jQuery(".hideMain6").hide();
    }
}
function mainChanged66()
{
    if(jQuery("#add_seo_title").val() == 'enabled')
    {            
        jQuery(".hideMain66").show();
        visionSelectedAI6();
    }
    else
    {
        jQuery(".hideMain66").hide();
    }
}
function loadMe()
{
    toggleMain();
    mainChanged();
    toggleCustom();
    defaultChanged();
    mainChangedImg();
    mainChanged2();
    mainChanged2e();
    mainChanged2c();
    mainChanged3();
    mainChanged4();
    mainChanged5();
    mainChanged6();
    mainChanged66();
    mainChanged7();
    mainChanged8();
    mainChanged9();
    mainChangedx();
    mainChanged10();
    aiomatic_audio_changed();
    aiomatic_speech_changed();
    hideLinks();
}
window.onload = loadMe;
var unsaved = false;
jQuery(document).ready(function () {
    jQuery(document).on('click','.aisaveedittemplate', function (e)
    {
        e.preventDefault();
        if(confirm('Are you sure you want to save the configured template?'))
        {
            var extractedData = {};
            jQuery('[name^="aiomatic_Spinner_Settings["]:not(.skip-from-processing)').each(function() 
            {
                var name = jQuery(this).attr('name');
                name = name.replace("aiomatic_Spinner_Settings[", "");
                var value;
                if (jQuery(this).is(':checkbox')) {
                    value = jQuery(this).is(':checked') ? jQuery(this).val() : '';
                } else if (jQuery(this).is(':radio')) {
                    if (jQuery(this).is(':checked')) {
                        value = jQuery(this).val();
                    } else {
                        return;
                    }
                } else {
                    value = jQuery(this).val();
                }
                extractedData[name] = value;
            });
            if(extractedData != {})
            {
                let tmpname = window.prompt("Please enter the name of the new template:", "Template 1");
                if (tmpname != null && tmpname != "") 
                {
                    var data = {
                        action: 'aiomatic_save_editor_template',
                        nonce: aiomatic_object.nonce,
                        editor_template_new: tmpname,
                        extractedData: extractedData
                    };
                    jQuery.ajax({
                        url: aiomatic_object.ajax_url,
                        data: data,
                        dataType: 'JSON',
                        type: 'POST',
                        success: function (res){
                            if(res.status === 'success'){
                                alert('Template saved successfully');
                                location.reload();
                            }
                            else{
                                alert(res.msg);
                            }
                        },
                        error: function (r, s, error){
                            alert('Error in processing AI Content Editor templates saving: ' + error);
                        }
                    });
                }
            }
            else
            {
                console.log('No data found!');
            }
        }
    });
    jQuery(document).on('click','.airenametemplate', function (e)
    {
        e.preventDefault();
        var selected = jQuery('#editor_select_template').val();
        if(selected == '' || selected == null)
        {
            alert('You need to select a template before you can do this action.');
            return;
        }
        if(confirm('Are you sure you want to rename the currently selected template?'))
        {
            let tmpname = window.prompt("Please enter the new name of the template:", "Template 1");
            if (tmpname != null && tmpname != "") 
            {
                var data = {
                    action: 'aiomatic_rename_editor_template',
                    nonce: aiomatic_object.nonce,
                    editor_template_new: tmpname,
                    selected: selected
                };
                jQuery.ajax({
                    url: aiomatic_object.ajax_url,
                    data: data,
                    dataType: 'JSON',
                    type: 'POST',
                    success: function (res){
                        if(res.status === 'success'){
                            alert('Template renamed successfully');
                            location.reload();
                        }
                        else{
                            alert(res.msg);
                        }
                    },
                    error: function (r, s, error){
                        alert('Error in processing AI Content Editor templates renaming: ' + error);
                    }
                });
            }
        }
    });
    jQuery(document).on('click','.aioverwritetemplate', function (e)
    {
        e.preventDefault();
        var selected = jQuery('#editor_select_template').val();
        if(selected == '' || selected == null)
        {
            alert('You need to select a template before you can do this action.');
            return;
        }
        if(confirm('Are you sure you want to overwrite the currently selected template?'))
        {
            var extractedData = {};
            jQuery('[name^="aiomatic_Spinner_Settings["]:not(.skip-from-processing)').each(function() 
            {
                var name = jQuery(this).attr('name');
                name = name.replace("aiomatic_Spinner_Settings[", "");
                var value;
                if (jQuery(this).is(':checkbox')) {
                    value = jQuery(this).is(':checked') ? jQuery(this).val() : '';
                } else if (jQuery(this).is(':radio')) {
                    if (jQuery(this).is(':checked')) {
                        value = jQuery(this).val();
                    } else {
                        return;
                    }
                } else {
                    value = jQuery(this).val();
                }
                extractedData[name] = value;
            });
            if(extractedData != {})
            {
                var data = {
                    action: 'aiomatic_overwrite_editor_template',
                    nonce: aiomatic_object.nonce,
                    selected: selected,
                    extractedData: extractedData
                };
                jQuery.ajax({
                    url: aiomatic_object.ajax_url,
                    data: data,
                    dataType: 'JSON',
                    type: 'POST',
                    success: function (res){
                        if(res.status === 'success'){
                            alert('Template overwritten successfully');
                            location.reload();
                        }
                        else{
                            alert(res.msg);
                        }
                    },
                    error: function (r, s, error){
                        alert('Error in processing AI Content Editor templates overwriting: ' + error);
                    }
                });
            }
            else
            {
                console.log('No data found!');
            }
        }
    });
    jQuery(document).on('click','.aideleteedittemplate', function (e)
    {
        e.preventDefault();
        var selected = jQuery('#editor_select_template').val();
        if(selected == '' || selected == null)
        {
            alert('You need to select a template before you can do this action.');
            return;
        }
        if(confirm('Are you sure you want to delete the currently selected template?'))
        {
            var data = {
                action: 'aiomatic_delete_editor_template',
                nonce: aiomatic_object.nonce,
                selected: selected
            };
            jQuery.ajax({
                url: aiomatic_object.ajax_url,
                data: data,
                dataType: 'JSON',
                type: 'POST',
                success: function (res){
                    if(res.status === 'success'){
                        alert('Template deleted successfully');
                        location.reload();
                    }
                    else{
                        alert(res.msg);
                    }
                },
                error: function (r, s, error){
                    alert('Error in processing AI Content Editor templates deletion: ' + error);
                }
            });
        }
    });
    jQuery(document).on('click','.ailoadedittemplate', function (e)
    {
        e.preventDefault();
        var selected = jQuery('#editor_select_template').val();
        if(selected == '' || selected == null)
        {
            alert('You need to select a template before you can do this action.');
            return;
        }
        if(confirm('Are you sure you want to load the currently selected template? WARNING, this will overwrite your currently set settings from above!'))
        {
            var data = {
                action: 'aiomatic_load_editor_template',
                nonce: aiomatic_object.nonce,
                selected: selected
            };
            jQuery.ajax({
                url: aiomatic_object.ajax_url,
                data: data,
                dataType: 'JSON',
                type: 'POST',
                success: function (res){
                    if(res.status === 'success'){
                        alert('Template loaded successfully');
                        location.reload();
                    }
                    else{
                        alert(res.msg);
                    }
                },
                error: function (r, s, error){
                    alert('Error in processing AI Content Editor templates loading: ' + error);
                }
            });
        }
    });
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
function toggleCustom()
{
    if(jQuery('#post_custom').val() !== 'on')
    {            
        jQuery(".hideCustom").hide();
        jQuery(".hideCustomAlt").show();
    }
    else
    {
        jQuery(".hideCustom").show();
        jQuery(".hideCustomAlt").hide();
    }
}
function defaultChanged()
{
    if(jQuery('#enable_default').val() === 'on')
    {            
        jQuery(".hideDefault").hide();
    }
    else
    {
        jQuery(".hideDefault").show();
    }
}
function toggleMain()
{
    if(!jQuery('#aiomatic_spinning').is(":checked"))
    {            
        jQuery(".hideAuto").hide();
    }
    else
    {
        jQuery(".hideAuto").show();
    }
}