"use strict";
jQuery(document).ready(function ($) {
    $(document).on('click', '.aiomatic-history-snippet', function () {
        const fullText = $(this).data('full-text');
        const formId = $(this).data('form-id');
        $('#aiomatic-full-text-display-' + formId).text(fullText).fadeIn();
    });
    $(document).on('click', '.aiomatic-delete-entry', function () {
        const index = $(this).data('index');
        const formId = $(this).data('form-id');
        if (confirm('Are you sure you want to delete this entry?')) {
            $.ajax({
                url: aiomatic_history_ajax_object_forms.ajax_url,
                method: 'POST',
                data: {
                    action: 'aiomatic_delete_form_history_entry',
                    nonce: aiomatic_history_ajax_object_forms.nonce,
                    form_id: formId,
                    index: index
                },
                success: function (response) {
                    if (response.success) {
                        location.reload(); 
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function () {
                    alert('An error occurred while deleting the entry.');
                }
            });
        }
    });
});