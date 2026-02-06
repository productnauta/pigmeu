"use strict";

jQuery(document).ready(function($)
{
    var copyButton = jQuery('#aiomaticCopyShortcodeText');
    copyButton.on('click', function (e)
    {
        e.preventDefault();
        var textToCopy = document.getElementById("customized_chatbot").innerHTML;
        if(navigator.clipboard !== undefined)
        {
            navigator.clipboard.writeText(textToCopy)
            .then(() => {
                alert('Text copied to clipboard.');
            })
            .catch(err => {
                console.error('Failed to copy text: ', err);
            });
        }
        else
        {
            console.error('Failed to copy text');
        }   
    });
    jQuery('#aiomatic_add_remote_chatbot').on('click', function (e){
        e.preventDefault();
        var data = {
            action: 'aiomatic_add_remote_realtime_chatbot',
            nonce: aiomatic_object.nonce,
        };
        jQuery.ajax({
            url: aiomatic_object.ajax_url,
            data: data,
            dataType: 'JSON',
            type: 'POST',
            success: function (res)
            {
                if(res.status === 'success')
                {
                    location.reload();
                }
                else{
                    alert(res.msg);
                }
            },
            error: function (r, s, error){
                alert('Error in processing remote chatbot saving: ' + error);
            }
        });
    });
    jQuery('.aiomatic_delete_remote_chatbot').on('click', function (e){
        e.preventDefault();
        var dataid = jQuery(this).attr('data-id');
        var data = {
            action: 'aiomatic_delete_remote_chatbot',
            dataid: dataid,
            nonce: aiomatic_object.nonce,
        };
        jQuery.ajax({
            url: aiomatic_object.ajax_url,
            data: data,
            dataType: 'JSON',
            type: 'POST',
            success: function (res)
            {
                if(res.status === 'success')
                {
                    location.reload();
                }
                else{
                    alert(res.msg);
                }
            },
            error: function (r, s, error){
                alert('Error in processing remote chatbot deletion: ' + error);
            }
        });
    });
});
function updateCSSAttribute(css_attr_name, css_attr_value, original_css_code) {
  const regex = new RegExp(`((?<!-)${css_attr_name}\\s*:\\s*[^;]+;?)`);
  const match = original_css_code.match(regex);
  var updated_css_code = original_css_code;
  if (match) {
    updated_css_code = original_css_code.replace(regex, `${css_attr_name}: ${css_attr_value}`);
    return updated_css_code;
  } else {
    const new_css_attribute = `${css_attr_name}: ${css_attr_value}`;
    if(original_css_code == '')
    {
        updated_css_code = new_css_attribute;
    }
    else
    {
        if(original_css_code.endsWith(';'))
        {
            updated_css_code = `${original_css_code}${new_css_attribute}`;
        }
        else
        {
            updated_css_code = `${original_css_code};${new_css_attribute}`;
        }
    }
    return updated_css_code;
  }
}
function startRecordingChanged_b()
{
    var selected = jQuery('#start_recording_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/start_recording="([^"]*?)"/g, 'start_recording="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function stopRecordingChanged_b()
{
    var selected = jQuery('#stop_recording_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/stop_recording="([^"]*?)"/g, 'stop_recording="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function notRecordingChanged_b()
{
    var selected = jQuery('#not_recording_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/not_recording="([^"]*?)"/g, 'not_recording="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
    var text_input = jQuery('#aiomatic-realtime-status');
    if(text_input !== undefined && selected != undefined)
    {
        text_input.text(selected);
    }
}
function recordingChanged_b()
{
    var selected = jQuery('#recording_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/recording="([^"]*?)"/g, 'recording="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function modelChanged_b()
{
    var selected = jQuery('#ai_model_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/model="([^"]*?)"/g, 'model="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function logChanged_b()
{
    var selected = jQuery('#show_logs_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/show_chat_log="([^"]*?)"/g, 'show_chat_log="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function logUserChanged_b()
{
    var selected = jQuery('#show_user_log_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/show_user_log="([^"]*?)"/g, 'show_user_log="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function voiceChanged_b()
{
    var selected = jQuery('#ai_voice_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/voice="([^"]*?)"/g, 'voice="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function instructionsChanged_b()
{
    var selected = jQuery('#ai_instructions_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/instructions="([^"]*?)"/g, 'instructions="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function temperatureChanged_b()
{
    var selected = jQuery('#ai_temperature_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/temperature="([^"]*?)"/g, 'temperature="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function maxtokensChanged_b()
{
    var selected = jQuery('#max_tokens_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/max_tokens="([^"]*?)"/g, 'max_tokens="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function autostartChanged_b()
{
    var selected = jQuery('#autostart_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/autostart="([^"]*?)"/g, 'autostart="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function recordingChanged_b()
{
    var selected = jQuery('#record_button_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/record_button_color="([^"]*?)"/g, 'record_button_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
    var text_input = jQuery('#aiomatic-realtime-toggle');
    if(selected != undefined && text_input !== null)
    {
        var origCSS = text_input.attr("style");
        if(origCSS === undefined)
        {
            origCSS = "";
        }
        var updatedCSS = updateCSSAttribute('background-color', selected + '!important;', origCSS);
        text_input.attr('style', updatedCSS);
    }
}
function recordingTextChanged_b()
{
    var selected = jQuery('#record_text_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/record_text_color="([^"]*?)"/g, 'record_text_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
    var text_input = jQuery('#aiomatic-realtime-toggle');
    if(selected != undefined && text_input !== null)
    {
        var origCSS = text_input.attr("style");
        if(origCSS === undefined)
        {
            origCSS = "";
        }
        var updatedCSS = updateCSSAttribute('color', selected + '!important;', origCSS);
        text_input.attr('style', updatedCSS);
    }
}
function chatContainerChanged_b()
{
    var selected = jQuery('#chat_container_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/chat_container_color="([^"]*?)"/g, 'chat_container_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
    var text_input = jQuery('#aiomatic-realtime-chat-container');
    if(selected != undefined && text_input !== null)
    {
        var origCSS = text_input.attr("style");
        if(origCSS === undefined)
        {
            origCSS = "";
        }
        var updatedCSS = updateCSSAttribute('background-color', selected + '!important;', origCSS);
        text_input.attr('style', updatedCSS);
    }
}
function chatLogContainerChanged_b()
{
    var selected = jQuery('#chat_log_container_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/chat_log_container_color="([^"]*?)"/g, 'chat_log_container_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
    var text_input = jQuery('#aiomatic-realtime-log');
    if(selected != undefined && text_input !== null)
    {
        var origCSS = text_input.attr("style");
        if(origCSS === undefined)
        {
            origCSS = "";
        }
        var updatedCSS = updateCSSAttribute('background-color', selected + '!important;', origCSS);
        text_input.attr('style', updatedCSS);
    }
}
function chatLogBaloonContainerChanged_b()
{
    var selected = jQuery('#ai_bubble_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/ai_bubble_color="([^"]*?)"/g, 'ai_bubble_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function chatLogBaloonTextContainerChanged_b()
{
    var selected = jQuery('#ai_bubble_text_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/ai_bubble_text_color="([^"]*?)"/g, 'ai_bubble_text_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function chatLogBaloonUserTextContainerChanged_b()
{
    var selected = jQuery('#user_bubble_text_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/user_bubble_text_color="([^"]*?)"/g, 'user_bubble_text_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function chatUserTokensChanged_b()
{
    var selected = jQuery('#user_token_cap_per_day_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/user_token_cap_per_day="([^"]*?)"/g, 'user_token_cap_per_day="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function chatLogBaloonUserContainerChanged_b()
{
    var selected = jQuery('#user_bubble_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/user_bubble_color="([^"]*?)"/g, 'user_bubble_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
}
function recordingStatusChanged_b()
{
    var selected = jQuery('#record_status_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/record_status_color="([^"]*?)"/g, 'record_status_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
    var text_input = jQuery('#aiomatic-realtime-status');
    if(selected != undefined && text_input !== null)
    {
        var origCSS = text_input.attr("style");
        if(origCSS === undefined)
        {
            origCSS = "";
        }
        var updatedCSS = updateCSSAttribute('background-color', selected + '!important;', origCSS);
        text_input.attr('style', updatedCSS);
    }
}
function recordingStatusTextChanged_b()
{
    var selected = jQuery('#record_status_text_color_b').val();
    if(selected != undefined)
    {
        selected = selected.replace(/"/g, "'");
        var customized_chatbot = jQuery('#customized_chatbot').text();
        if(customized_chatbot !== undefined)
        {
            customized_chatbot = customized_chatbot.replace(/record_status_text_color="([^"]*?)"/g, 'record_status_text_color="' + selected + '"');
            jQuery('#customized_chatbot').text(customized_chatbot);
        }
    }
    var text_input = jQuery('#aiomatic-realtime-status');
    if(selected != undefined && text_input !== null)
    {
        var origCSS = text_input.attr("style");
        if(origCSS === undefined)
        {
            origCSS = "";
        }
        var updatedCSS = updateCSSAttribute('color', selected + '!important;', origCSS);
        text_input.attr('style', updatedCSS);
    }
}
function godModeChanged_b() {
    var selectedOptions = jQuery('#enable_god_mode_b').val(); 
    var selectedValue;

    if (selectedOptions && selectedOptions.length > 0) {
        if (selectedOptions.includes('disabled')) {
            selectedValue = 'disabled';
        } 
        else 
        {
            if (selectedOptions.includes('enabled')) {
                selectedValue = 'enabled';
            } 
            else
            {
                selectedValue = selectedOptions.join(',').replace(/"/g, "'");
            }
        }
    } else {
        selectedValue = 'disabled';
    }

    var customizedChatbot = jQuery('#customized_chatbot').text();
    if (customizedChatbot !== undefined) {
        customizedChatbot = customizedChatbot.replace(
            /enable_god_mode="([^"]*?)"/g,
            'enable_god_mode="' + selectedValue + '"'
        );
        jQuery('#customized_chatbot').text(customizedChatbot);
    }
}
function anythingChanged()
{
    modelChanged_b();
    logChanged_b();
    godModeChanged_b();
    logUserChanged_b();
    voiceChanged_b();
    instructionsChanged_b();
    temperatureChanged_b();
    maxtokensChanged_b();
    autostartChanged_b();
    startRecordingChanged_b();
    stopRecordingChanged_b();
    notRecordingChanged_b();
    recordingChanged_b();
    recordingTextChanged_b();
    recordingStatusChanged_b();
    recordingStatusTextChanged_b();
    chatContainerChanged_b();
    chatLogContainerChanged_b();
    chatLogBaloonContainerChanged_b();
    chatLogBaloonTextContainerChanged_b();
    chatLogBaloonUserTextContainerChanged_b();
    chatUserTokensChanged_b();
    chatLogBaloonUserContainerChanged_b();
}
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