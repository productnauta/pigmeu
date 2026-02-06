"use strict";

jQuery(document).ready(function($) {
    $('#deactivate-aiomatic-automatic-ai-content-writer').on('click', function(e) {
        e.preventDefault(); 
        $('#aiomatic-deactivation-modal').dialog('open');
    });
    $('body').append(`
        <div id="aiomatic-deactivation-modal" style="display:none;" title="Revoke Plugin License">
            <p>Do you also want to automatically revoke the purchase code from this website after deactivating the plugin?</p>
            <label class="aiomatic_check_label">
                <input type="checkbox" id="wipe_plugin_data" />
                Also wipe all data of the plugin (settings, options, rules, will keep created content - warning: irreversible)
            </label>
        </div>
    `);
    $('#wipe_plugin_data').on('change', function() {
        if ($(this).is(':checked')) {
            if (!confirm('This action is irreversible. Are you sure you want to wipe all plugin data (settings, options, rules, but will still keep created content)?')) 
            {
                $(this).prop('checked', false);
            }
        }
    });
    $('#aiomatic-deactivation-modal').dialog({
        autoOpen: false,
        modal: true,
        buttons: {
            "Yes, Revoke License": function() {
                var wipe_data = $('#wipe_plugin_data').is(':checked') ? '1' : '0';
                $.post(aiomatic.ajaxurl, {
                    action: 'aiomatic_clear_data',
                    wipe_data: wipe_data,
                    revoke: '1',
                    nonce: aiomatic.clear_data_nonce
                }, function(response) {
                    if (response.trim() === 'ok') {
                    } else {
                        alert('Error in clearing data: ' + response);
                    }
                });
                window.location.href = $('#deactivate-aiomatic-automatic-ai-content-writer').attr('href');
                $(this).dialog("close");
            },
            "No, Keep License Active": function() {
                var wipe_data = $('#wipe_plugin_data').is(':checked') ? '1' : '0';
                if(wipe_data === '1')
                {
                    $.post(aiomatic.ajaxurl, {
                        action: 'aiomatic_clear_data',
                        wipe_data: wipe_data,
                        revoke: '0',
                        nonce: aiomatic.clear_data_nonce
                    }, function(response) {
                        if (response.trim() === 'ok') {
                        } else {
                            alert('Error in clearing data: ' + response);
                        }
                    });
                }
                window.location.href = $('#deactivate-aiomatic-automatic-ai-content-writer').attr('href');
                $(this).dialog("close");
            }
        },
        create: function() 
        {
            $(this).parent().find('.ui-dialog-buttonset').css({
                'display': 'flex',
                'justify-content': 'space-between', 
                'width': '100%'
            });
        }
    });
});