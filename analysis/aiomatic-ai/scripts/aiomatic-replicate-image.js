"use strict"; 
const { registerBlockType: customRegisterBlockType18 } = wp.blocks;
const mygcel16 = wp.element.createElement;

customRegisterBlockType18( 'aiomatic-automatic-ai-content-writer/aiomatic-replicate-image', {
    title: 'AIomatic Replicate Image',
    icon: 'text',
    category: 'embed',
    attributes: {
        seed_expre : {
            default: '',
            type:   'string',
        },
        image_size : {
            default: '512x512',
            type:   'string',
        },
        copy_locally : {
            default: 'off',
            type:   'string',
        },
        cache_seconds : {
            default: '2592000',
            type:   'string',
        }
    },
    keywords: ['list', 'posts', 'aiomatic'],
    edit: (function( props ) {
        var seed_expre = props.attributes.seed_expre;
        var image_size = props.attributes.image_size;
        var cache_seconds = props.attributes.cache_seconds;
        var copy_locally = props.attributes.copy_locally;
        function updateMessage2( event ) {
            props.setAttributes( { seed_expre: event.target.value} );
		}
        function updateMessage3( event ) {
            props.setAttributes( { image_size: event.target.value} );
		}
        function updateMessage4( event ) {
            props.setAttributes( { cache_seconds: event.target.value} );
		}
        function updateMessage5( event ) {
            props.setAttributes( { copy_locally: event.target.value} );
		}
		return mygcel16(
			'div', 
			{ className: 'coderevolution_gutenberg_div' },
            mygcel16(
				'h4',
				{ className: 'coderevolution_gutenberg_title' },
                'AIomatic Replicate Image ',
                mygcel16(
                    'div', 
                    {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                    ,
                    mygcel16(
                        'div', 
                        {className:'bws_hidden_help_text'},
                        'This block is used to generate images.'
                    )
                )
			),
            mygcel16(
				'br'
			),
            mygcel16(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Seed Expression: '
			),
            mygcel16(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel16(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set a seed expression for the image. You can also use the following shortcodes: %%post_title%%, %%post_content%%, %%post_excerpt%%, %%post_cats%%, %%post_tags%%, %%featured_image%%, %%blog_title%%, %%author_name%%, %%post_link%%, %%random_sentence%%, %%random_sentence2%%. You can also use custom fields (post meta) that it\'s assigned to posts using custom shortcodes in this format: %%!custom_field_slug!%%. You can also use custom user meta fields (user meta) which is assigned to users using custom shortcodes in this format: %%~custom_field_slug~%%. Example: if you wish to add data that is imported from the custom field post_data, you should use this shortcode: %%!post_data!%%. The length of this command should not be greater than the max token count set in the settings for the seed command. If you leave this blank, the plugin will automatically use the content, excerpt or title of the post where this block is added.'
                )
            ),
			mygcel16(
				'textarea',
				{ rows:1,placeholder:'Short introduction of the image', value: seed_expre, onChange: updateMessage2, className: 'coderevolution_gutenberg_input' }
			),
            mygcel16(
				'br'
			),
            mygcel16(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Image Size: '
			),
            mygcel16(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel16(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select the generated image size.'
                )
            ),
            mygcel16(
				'select',
				{ value: image_size, onChange: updateMessage3, className: 'coderevolution_gutenberg_select' }, 
                mygcel16(
                    'option',
                    { value: '512x512'},
                    '512x512'
                ), 
                mygcel16(
                    'option',
                    { value: '1024x1024'},
                    '1024x1024'
                ), 
                mygcel16(
                    'option',
                    { value: '1024x1792'},
                    '1024x1792'
                ), 
                mygcel16(
                    'option',
                    { value: '1792x1024'},
                    '1792x1024'
                )
            ),
            mygcel16(
				'br'
			),
            mygcel16(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Copy Locally: '
			),
            mygcel16(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel16(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Do you want to copy the image locally?'
                )
            ),
            mygcel16(
				'select',
				{ value: copy_locally, onChange: updateMessage5, className: 'coderevolution_gutenberg_select' }, 
                mygcel16(
                    'option',
                    { value: 'off'},
                    'off'
                ), 
                mygcel16(
                    'option',
                    { value: 'on'},
                    'on'
                )
            ),
            mygcel16(
				'br'
			),
            mygcel16(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Caching Period For AI Generated Content (Seconds): '
			),
            mygcel16(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel16(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the caching period for AI generated content in seconds.'
                )
            ),
			mygcel16(
				'input',
				{ type:'number',min:0,placeholder:'Caching period in seconds', value: cache_seconds, onChange: updateMessage4, className: 'coderevolution_gutenberg_input' }
			)
		);
    }),
    save: (function( props ) {
       return null;
    }),
} );