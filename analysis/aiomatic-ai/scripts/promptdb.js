"use strict";
jQuery(document).ready(function($) {
    $(document).on('click', '.copy-prompt', async function(e) {
        e.preventDefault();
        var prompt = $(this).data('prompt');
        
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(prompt);
                showCopyMessage('Prompt copied to clipboard!', 'success');
            } catch (err) {
                console.error('Could not copy text: ', err);
                showCopyMessage('Failed to copy prompt. Please copy manually.', 'error');
            }
        } else {
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(prompt).focus().select();
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    showCopyMessage('Prompt copied to clipboard!', 'success');
                } else {
                    showCopyMessage('Failed to copy prompt. Please copy manually.', 'error');
                }
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
                showCopyMessage('Failed to copy prompt. Please copy manually.', 'error');
            }
            $temp.remove();
        }
    });
    
    function showCopyMessage(message, type) {
        var $messageDiv = $('#copy-message');
        $messageDiv.text(message).removeClass().addClass(type).fadeIn();
        setTimeout(function() {
            $messageDiv.fadeOut();
        }, 3000);
    }
});