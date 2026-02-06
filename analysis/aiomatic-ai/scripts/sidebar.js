"use strict"; 
( function( wp ) {
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginSidebar = wp.editPost.PluginSidebar;
	var el = wp.element.createElement;
    var editing_options = '<option value="" disabled selected>No templates found (use currently saved configuration)</option>';
    if (typeof aiomatic_gut.templates === 'object' && !Array.isArray(aiomatic_gut.templates) && Object.keys(aiomatic_gut.templates).length > 0) {
        editing_options = '<option value="">Use currently saved configuration</option>';
        for (var key in aiomatic_gut.templates) {
            if (aiomatic_gut.templates.hasOwnProperty(key)) {
                editing_options += '<option value="' + key + '">' + aiomatic_gut.templates[key] + '</option>';
            }
        }
    }
	registerPlugin( 'aiomatic-sidebar', {
		render: function() {
            function updateMessage( ) {
                var postId = wp.data.select("core/editor").getCurrentPostId();
                if (confirm("Are you sure you want to submit this post now?") == true) {
                    document.getElementById('aiomatic_submit_post').setAttribute('disabled','disabled');
                    document.getElementById('aiomatic_toggle_post').setAttribute('disabled','disabled');
                    document.getElementById("aiomatic_span").innerHTML = 'Processing status: Working... (please do not close or refresh this page) ';
                    var editor_select_template = jQuery('#editor_select_template').val();
                    if(editor_select_template == null)
                    {
                        editor_select_template = '';
                    }
                    var data = {
                         action: 'aiomatic_post_now',
                         nonce: aiomatic_gut.nonce,
                         template: editor_select_template,
                         id: postId
                    };
                    jQuery.post(aiomatic_gut.ajaxurl, data, function(response) {
                        document.getElementById('aiomatic_submit_post').removeAttribute('disabled');
                        document.getElementById('aiomatic_toggle_post').removeAttribute('disabled');
                        document.getElementById("aiomatic_span").innerHTML = 'Processing status: Done! ';
                        location.reload();
                    }).fail( function(xhr) 
                    {
                        document.getElementById("aiomatic_span").innerHTML = 'Error, please check the plugin\'s \'Activity and Logging\' menu for details!';
                        console.log('Error occured in processing: ' + xhr.statusText + ' - please check plugin\'s \'Activity and Logging\' menu for details.');
                    });
                } else {
                    return;
                }
            }
            function toggleStatus( ) {
                var postId = wp.data.select("core/editor").getCurrentPostId();
                if (confirm("Are you sure you want to toggle the processing status of this post?") == true) {
                    document.getElementById('aiomatic_submit_post').setAttribute('disabled','disabled');
                    document.getElementById('aiomatic_toggle_post').setAttribute('disabled','disabled');
                    document.getElementById("aiomatic_span").innerHTML = 'Processing status: Working... (please do not close or refresh this page) ';
                    var data = {
                         action: 'aiomatic_toggle_status',
                         nonce: aiomatic_gut.nonce,
                         id: postId
                    };
                    jQuery.post(aiomatic_gut.ajaxurl, data, function(response) {
                        document.getElementById('aiomatic_submit_post').removeAttribute('disabled');
                        document.getElementById('aiomatic_toggle_post').removeAttribute('disabled');
                        document.getElementById("aiomatic_span").innerHTML = 'Processing status: Done! ';
                        location.reload();
                    }).fail( function(xhr) 
                    {
                        document.getElementById('aiomatic_submit_post').removeAttribute('disabled');
                        document.getElementById('aiomatic_toggle_post').removeAttribute('disabled');
                        document.getElementById("aiomatic_span").innerHTML = 'Error, please check the plugin\'s \'Activity and Logging\' menu for details!';
                        console.log('Error occured in processing: ' + xhr.statusText + ' - please check plugin\'s \'Activity and Logging\' menu for details.');
                    });
                } else {
                    return;
                }
            }
            var poststat = 'Post is not yet edited with Aiomatic.';
            if(aiomatic_gut.metavalue == 'pub')
            {
                poststat = 'Post is edited with Aiomatic.';
            }
            const aiIcon = el('svg', { 
                xmlns: "http://www.w3.org/2000/svg", 
                width: "20", 
                height: "20", 
                viewBox: "0 0 512 512", 
                style: { transform: "translate(10%, 10%) scale(1.0)" }
            }, 
            el('path', { 
                d: "M320,64 L320,320 L64,320 L64,64 L320,64 Z M171.749388,128 L146.817842,128 L99.4840387,256 L121.976629,256 L130.913039,230.977 L187.575039,230.977 L196.319607,256 L220.167172,256 L171.749388,128 Z M260.093778,128 L237.691519,128 L237.691519,256 L260.093778,256 L260.093778,128 Z M159.094727,149.47526 L181.409039,213.333 L137.135039,213.333 L159.094727,149.47526 Z M341.333333,256 L384,256 L384,298.666667 L341.333333,298.666667 L341.333333,256 Z M85.3333333,341.333333 L128,341.333333 L128,384 L85.3333333,384 L85.3333333,341.333333 Z M170.666667,341.333333 L213.333333,341.333333 L213.333333,384 L170.666667,384 L170.666667,341.333333 Z M85.3333333,0 L128,0 L128,42.6666667 L85.3333333,42.6666667 L85.3333333,0 Z M256,341.333333 L298.666667,341.333333 L298.666667,384 L256,384 L256,341.333333 Z M170.666667,0 L213.333333,0 L213.333333,42.6666667 L170.666667,42.6666667 L170.666667,0 Z M256,0 L298.666667,0 L298.666667,42.6666667 L256,42.6666667 L256,0 Z M341.333333,170.666667 L384,170.666667 L384,213.333333 L341.333333,213.333333 L341.333333,170.666667 Z M0,256 L42.6666667,256 L42.6666667,298.666667 L0,298.666667 L0,256 Z M341.333333,85.3333333 L384,85.3333333 L384,128 L341.333333,128 L341.333333,85.3333333 Z M0,170.666667 L42.6666667,170.666667 L42.6666667,213.333333 L0,213.333333 L0,170.666667 Z M0,85.3333333 L42.6666667,85.3333333 L42.6666667,128 L0,128 L0,85.3333333 Z"
            }));
            return el(PluginSidebar, {
                name: 'aiomatic-sidebar',
                icon: aiIcon,
                title: 'Aiomatic AI Content Writer',
            },
            el('div', { className: 'coderevolution_gutenberg_div' },
                el('h4', { className: 'coderevolution_gutenberg_title' }, 'Manual AI Editing for This Post'),
                el('p', { className: 'coderevolution_gutenberg_description' },
                    'Edit the post using the selected template or the settings configured in the AI Content Editor plugin menu.'
                ),
                el('label', { className: 'coderevolution_gutenberg_label' }, 'Select AI Content Editor Template:'),
                el('select', {
                    id: 'editor_select_template',
                    className: 'coderevolution_gutenberg_select',
                    dangerouslySetInnerHTML: { __html: editing_options }
                }),
                el(
                    'br'
                ),
                el(
                    'br'
                ),
                el('input', {
                    type: 'button',
                    id: 'aiomatic_submit_post',
                    value: 'Process with Aiomatic',
                    onClick: updateMessage,
                    className: 'coderevolution_gutenberg_button button button-primary'
                })
            ),
            el('div', { className: 'coderevolution_gutenberg_div' },
                el('h4', { className: 'coderevolution_gutenberg_title' }, 'Aiomatic Editing Status'),
                el('p', { className: 'coderevolution_gutenberg_status' }, poststat),
                el('input', {
                    type: 'button',
                    id: 'aiomatic_toggle_post',
                    value: 'Toggle Processing Status',
                    onClick: toggleStatus,
                    className: 'coderevolution_gutenberg_button button button-secondary'
                })
            ),
            el('div', { id:'aiomatic_span', className: 'coderevolution_gutenberg_status' },
                'Processing status: idle'
            )
        );
		},
	} );
} )( window.wp );