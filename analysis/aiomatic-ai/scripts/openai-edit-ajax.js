"use strict";
function aiomaticLoading(btn){
    btn.attr('disabled','disabled');
    if(!btn.find('aiomatic-jumping-dots').length){
        btn.append('<span class="aiomatic-jumping-dots">&nbsp;<span class="aiomatic-dot-1">.</span><span class="aiomatic-dot-2">.</span><span class="aiomatic-dot-3">.</span></span>');
    }
    btn.find('.aiomatic-jumping-dots').css('visibility','unset');
}
function aiomaticRmLoading(btn){
    btn.removeAttr('disabled');
    btn.find('.aiomatic-jumping-dots').remove();
}
function openaieditfunct() {
    aiomaticLoading(jQuery('#aieditsubmitbut'));
    var input_text = jQuery('#aiomatic_edit_input').val();
    var instruction = jQuery('#aiomatic_edit_instruction').val();
    var model = aiomatic_edit_ajax_object.model;
    var user_token_cap_per_day = aiomatic_edit_ajax_object.user_token_cap_per_day;
    var user_id = aiomatic_edit_ajax_object.user_id;
    var prompt = aiomatic_edit_ajax_object.prompt;
    // Show the loading animation
    var temp = aiomatic_edit_ajax_object.temp;
    var top_p = aiomatic_edit_ajax_object.top_p;
    if(temp == 'default' || temp == '')
    {
        temp = jQuery('#temperature-edit-input').val();
    }
    if(top_p == 'default' || top_p == '')
    {
        top_p = jQuery('#top_p-edit-input').val();
    }
    if(model == 'default' || model == '')
    {
        model = jQuery( "#model-edit-selector option:selected" ).text();
    }
    if(prompt)
    {
        instruction = prompt;
    }
    if(instruction === "")
    {
        console.log('Instruction cannot be empty.');
        jQuery('#openai-edit-response').html('<div class="text-primary highlight-text-fail" role="status">Please add a command in the instruction field.</div>');
        aiomaticRmLoading(jQuery('#aieditsubmitbut'));
    }
    else
    {
        jQuery.ajax({
            type: 'POST',
            url: aiomatic_edit_ajax_object.ajax_url,
            data: {
                action: 'aiomatic_edit_submit',
                input_text: input_text,
                instruction: instruction,
                nonce: aiomatic_edit_ajax_object.nonce,
                temp: temp,
                top_p: top_p,
                model: model,
                user_token_cap_per_day: user_token_cap_per_day,
                user_id: user_id
            },
            success: function(response) 
            {
                if(typeof response === 'string' || response instanceof String)
                {
                    try {
                        var responset = JSON.parse(response);
                        response = responset;
                    } catch (error) {
                        console.error("An error occurred while parsing the JSON: " + error + ' Json: ' + response);
                    }
                }
                if(response.status == 'success')
                {
                    if(response.data == '')
                    {
                        jQuery('#openai-edit-response').html('<div class="text-primary" role="status">No edit was returned. Please try using a different text input.</div>');
                    }
                    else
                    {
                        if(prompt)
                        {
                            jQuery('#aiomatic_edit_result').val(response.data);
                        }
                        else
                        {
                            jQuery('#aiomatic_edit_response').val(response.data);
                        }
                        jQuery('#openai-edit-response').html('');
                    }
                }
                else
                {
                    if(typeof response.msg !== 'undefined')
                    {
                        jQuery('#openai-edit-response').html('<div class="text-primary highlight-text-fail" role="status">' + response.msg + '</div>');
                    }
                    else
                    {
                        console.log('Error: ' + response);
                        jQuery('#openai-edit-response').html('<div class="text-primary highlight-text-fail" role="status">Processing failed, please try again</div>');
                    }
                }
                aiomaticRmLoading(jQuery('#aieditsubmitbut'));
            },
            error: function(error) {
                console.log('Error: ' + error.responseText);
                jQuery('#openai-edit-response').html('<div class="text-primary highlight-text-fail" role="status">Failed to edit content, try again later.</div>');
                // Enable the submit button
                aiomaticRmLoading(jQuery('#aieditsubmitbut'));
            },
        });
    }
}

var recognition;
var recognizing = false;
jQuery(document).ready(function() {
    jQuery('#copy-edit-button').on('click', function() {
        var getid = jQuery(this).attr("data-target")
        var jsf = jQuery("#" + getid).val();
        if(navigator.clipboard !== undefined)
        {
            navigator.clipboard.writeText(jsf);
        }
    });
    if(!jQuery('#aiomatic_edit_templates').length)
    {
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;
            jQuery('#openai-edit-speech-button').on('click', function() {
                if (recognizing) {
                    recognition.stop();
                    recognizing = false;
                } else {
                    recognition.start();
                    recognizing = true;
                }
            });
            recognition.onresult = function(event) {
                for (var i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        jQuery('#aiomatic_edit_input').val(jQuery('#aiomatic_edit_input').val() + " " + event.results[i][0].transcript);
                    }
                }
            };
        }
    }
    else
    {
        jQuery('#aiomatic_edit_templates').on('change', function()
        {
            jQuery('#aiomatic_edit_instruction').val(jQuery( "#aiomatic_edit_templates" ).val());
        });
    }
});