"use strict";
jQuery(document).ready(function($) {
    $(document).on('click', '.aiomatic-language-action', function(e) {
        e.preventDefault();
        var $button = $(this);
        var action = $button.data('action'); 
        var pack = $button.data('pack');   

        $button.prop('disabled', true).text(action === 'install_language' ? 'Installing...' : 'Uninstalling...');

        $.ajax({
            url: aiomatic_object_trans.ajax_url,
            type: 'POST',
            data: {
                action: action === 'install_language' ? 'aiomatic_install_language' : 'aiomatic_uninstall_language',
                pack: pack,
                security: aiomatic_object_trans.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || 'An error occurred.');
                    $button.prop('disabled', false).text(action === 'install_language' ? 'Install' : 'Uninstall');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $button.prop('disabled', false).text(action === 'install_language' ? 'Install' : 'Uninstall');
            }
        });
    });
});
