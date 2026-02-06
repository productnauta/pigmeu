"use strict"; 
const { registerBlockType: customRegisterBlockTypex1 } = wp.blocks;
const mygcel13x = wp.element.createElement;
let editing_optionsxc = '';
if (Array.isArray(aiomatic_object.models)) {
    aiomatic_object.models.forEach(element => {
        editing_optionsxc += '<option value="' + element + '">' + element + '</option>';
    });
} else if (typeof aiomatic_object.models === 'object' && aiomatic_object.models !== null) 
{
    Object.entries(aiomatic_object.models).forEach(([key, value]) => {
        editing_optionsxc += '<option value="' + key + '">' + value + '</option>';
    });
}
customRegisterBlockTypex1( 'aiomatic-automatic-ai-content-writer/aiomatic-realtime-chat', {
    title: 'Aiomatic Realtime Chat Form',
    icon: 'text',
    category: 'embed',
    attributes: {
        model : {
            default: 'gpt-realtime',
            type:   'string',
        },
        temperature : {
            default: '1',
            type:   'string',
        },
        voice : {
            default: 'verse',
            type:   'string',
        },
        max_tokens : {
            default: '4096',
            type:   'string',
        },
        instructions : {
            default: '',
            type:   'string',
        },
        autostart : {
            default: '0',
            type:   'string',
        },
        start_recording : {
            default: 'Start Recording',
            type:   'string',
        },
        stop_recording : {
            default: 'Stop Recording',
            type:   'string',
        },
        not_recording : {
            default: 'Not Recording',
            type:   'string',
        },
        recording : {
            default: 'Recording...',
            type:   'string',
        },
        record_button_color : {
            default: '#007bff',
            type:   'string',
        },
        record_text_color : {
            default: '#ffffff',
            type:   'string',
        },
        record_status_color : {
            default: '#f8f9fa',
            type:   'string',
        },
        record_status_text_color : {
            default: '#333333',
            type:   'string',
        },
        chat_container_color : {
            default: '#e4e8ec',
            type:   'string',
        },
        show_chat_log : {
            default: 'show',
            type:   'string',
        },
        show_user_log : {
            default: 'show',
            type:   'string',
        },
        chat_log_container_color : {
            default: '#ffffff',
            type:   'string',
        },
        ai_bubble_text_color : {
            default: '#000000',
            type:   'string',
        },
        ai_bubble_color : {
            default: '#f5f5f5',
            type:   'string',
        },
        user_bubble_text_color : {
            default: '#ffffff',
            type:   'string',
        },
        user_bubble_color : {
            default: '#0084ff',
            type:   'string',
        },
        user_token_cap_per_day : {
            default: '',
            type:   'string',
        },
    },
    keywords: ['list', 'posts', 'aiomatic'],
    edit: (function( props ) {
        var model = props.attributes.model;
        var temperature = props.attributes.temperature;
        var voice = props.attributes.voice;
        var max_tokens = props.attributes.max_tokens;
        var instructions = props.attributes.instructions;
        var autostart = props.attributes.autostart;
        var start_recording = props.attributes.start_recording;
        var stop_recording = props.attributes.stop_recording;
        var not_recording = props.attributes.not_recording;
        var recording = props.attributes.recording;
        var record_button_color = props.attributes.record_button_color;
        var record_text_color = props.attributes.record_text_color;
        var record_status_color = props.attributes.record_status_color;
        var record_status_text_color = props.attributes.record_status_text_color;
        var chat_container_color = props.attributes.chat_container_color;
        var show_chat_log = props.attributes.show_chat_log;
        var show_user_log = props.attributes.show_user_log;
        var chat_log_container_color = props.attributes.chat_log_container_color;
        var ai_bubble_text_color = props.attributes.ai_bubble_text_color;
        var ai_bubble_color = props.attributes.ai_bubble_color;
        var user_bubble_text_color = props.attributes.user_bubble_text_color;
        var user_bubble_color = props.attributes.user_bubble_color;
        var user_token_cap_per_day = props.attributes.user_token_cap_per_day;
        function updateMessage18( event ) {
            props.setAttributes( { show_chat_log: event.target.value} );
		}
        function updateMessage19( event ) {
            props.setAttributes( { show_user_log: event.target.value} );
		}
        function updateMessage20( event ) {
            props.setAttributes( { chat_log_container_color: event.target.value} );
		}
        function updateMessage21( event ) {
            props.setAttributes( { ai_bubble_text_color: event.target.value} );
		}
        function updateMessage22( event ) {
            props.setAttributes( { user_bubble_text_color: event.target.value} );
		}
        function updateMessage23( event ) {
            props.setAttributes( { user_bubble_color: event.target.value} );
		}
        function updateMessage24( event ) {
            props.setAttributes( { ai_bubble_color: event.target.value} );
		}
        function updateMessage25( event ) {
            props.setAttributes( { user_token_cap_per_day: event.target.value} );
		}
        function updateMessage8( event ) {
            props.setAttributes( { start_recording: event.target.value} );
		}
        function updateMessage9( event ) {
            props.setAttributes( { stop_recording: event.target.value} );
		}
        function updateMessage10( event ) {
            props.setAttributes( { not_recording: event.target.value} );
		}
        function updateMessage11( event ) {
            props.setAttributes( { recording: event.target.value} );
		}
        function updateMessage12( event ) {
            props.setAttributes( { record_button_color: event.target.value} );
		}
        function updateMessage13( event ) {
            props.setAttributes( { record_text_color: event.target.value} );
		}
        function updateMessage14( event ) {
            props.setAttributes( { record_status_color: event.target.value} );
		}
        function updateMessage15( event ) {
            props.setAttributes( { record_status_text_color: event.target.value} );
		}
        function updateMessage16( event ) {
            props.setAttributes( { chat_container_color: event.target.value} );
		}
        function updateMessage( event ) {
            props.setAttributes( { temperature: event.target.value} );
		}
        function updateMessage6( event ) {
            props.setAttributes( { model: event.target.value} );
		}
        function updateMessage3( event ) {
            props.setAttributes( { voice: event.target.value} );
		}
        function updateMessage4( event ) {
            props.setAttributes( { max_tokens: event.target.value} );
		}
        function updateMessage5( event ) {
            props.setAttributes( { instructions: event.target.value} );
		}
        function updateMessage7( event ) {
            props.setAttributes( { autostart: event.target.value} );
		}
		return mygcel13x(
			'div', 
			{ className: 'coderevolution_gutenberg_div' },
            mygcel13x(
				'h4',
				{ className: 'coderevolution_gutenberg_title' },
                'AIomatic Realtime Chat Form ',
                mygcel13x(
                    'div', 
                    {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                    ,
                    mygcel13x(
                        'div', 
                        {className:'bws_hidden_help_text'},
                        'This block is used to generate a Realtime AI chat.'
                    )
                )
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'AI Temperature: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'What sampling temperature to use. Higher values means the model will take more risks. Try 0.9 for more creative applications, and 0 (argmax sampling) for ones with a well-defined answer. We generally recommend altering this or top_p but not both.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'number',min:0,step:0.1,placeholder:'AI Temperature', value: temperature, onChange: updateMessage, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Model: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select the AI model you want to use to generate the content.'
                )
            ),
            mygcel13x(
				'select',
				{ value: model, onChange: updateMessage6, className: 'coderevolution_gutenberg_select', dangerouslySetInnerHTML: {
                    __html: editing_optionsxc
                } }
            ),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'AI Voice: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select the AI Voice.'
                )
            ),
            mygcel13x(
                'select',
                { value: voice, onChange: updateMessage3, className: 'coderevolution_gutenberg_select' }, 
                mygcel13x(
                    'option',
                    { value: 'alloy'},
                    'alloy'
                ), 
                mygcel13x(
                    'option',
                    { value: 'ash'},
                    'ash'
                ), 
                mygcel13x(
                    'option',
                    { value: 'ballad'},
                    'ballad'
                ), 
                mygcel13x(
                    'option',
                    { value: 'coral'},
                    'coral'
                ), 
                mygcel13x(
                    'option',
                    { value: 'echo'},
                    'echo'
                ), 
                mygcel13x(
                    'option',
                    { value: 'sage'},
                    'sage'
                ), 
                mygcel13x(
                    'option',
                    { value: 'shimmer'},
                    'shimmer'
                ), 
                mygcel13x(
                    'option',
                    { value: 'verse'},
                    'verse'
                )
            ),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Max Tokens: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select the max output tokens for the AI.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'number',min:1,placeholder:'Max output tokens', value: max_tokens, onChange: updateMessage4, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Text-To-Speech/Video: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select if you want to enable/disable the text-to-speech/video feature of the chatbot.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'Additional instructions for the AI', value: instructions, onChange: updateMessage5, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Autostart Chatbot Session: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select if you want to autostart the chatbot session when the page loaded.'
                )
            ),
            mygcel13x(
                'select',
                { value: autostart, onChange: updateMessage7, className: 'coderevolution_gutenberg_select' }, 
                mygcel13x(
                    'option',
                    { value: 'no'},
                    'no'
                ), 
                mygcel13x(
                    'option',
                    { value: 'yes'},
                    'yes'
                )
            ),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Show Chat Logs: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select if you want to display a textual chat log to the user.'
                )
            ),
            mygcel13x(
                'select',
                { value: show_chat_log, onChange: updateMessage18, className: 'coderevolution_gutenberg_select' }, 
                mygcel13x(
                    'option',
                    { value: 'hide'},
                    'hide'
                ), 
                mygcel13x(
                    'option',
                    { value: 'show'},
                    'show'
                )
            ),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Show User Chat Logs: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select if you want to display a textual chat log to the user also for their own voice input.'
                )
            ),
            mygcel13x(
                'select',
                { value: show_user_log, onChange: updateMessage19, className: 'coderevolution_gutenberg_select' }, 
                mygcel13x(
                    'option',
                    { value: 'hide'},
                    'hide'
                ), 
                mygcel13x(
                    'option',
                    { value: 'show'},
                    'show'
                )
            ),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Start Recording Button Text: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the text for the Start Recording button.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'Start Recording', value: start_recording, onChange: updateMessage8, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Stop Recording Button Text: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the text for the Stop Recording button.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'Stop Recording', value: stop_recording, onChange: updateMessage9, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Not Recording Status Text: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the text for the Not Recording status.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'Not Recording', value: not_recording, onChange: updateMessage10, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Recording Status Text: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the text for the Recording status.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'Recording', value: recording, onChange: updateMessage11, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Record Button Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the record button.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#007bff', value: record_button_color, onChange: updateMessage12, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Record Button Text Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the record button text.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#ffffff', value: record_text_color, onChange: updateMessage13, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Record Status Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the record status.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#f8f9fa', value: record_status_color, onChange: updateMessage14, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Record Status Text Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the record status text.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#333333', value: record_status_text_color, onChange: updateMessage15, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Main Container Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the main container.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#e4e8ec', value: chat_container_color, onChange: updateMessage16, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Main Container Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the main container.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#ffffff', value: chat_log_container_color, onChange: updateMessage20, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'AI Bubble Text Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of AI bubble text.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#000000', value: ai_bubble_text_color, onChange: updateMessage21, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'AI Bubble Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of AI bubble.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#f5f5f5', value: ai_bubble_color, onChange: updateMessage24, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'User Bubble Text Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the user voice input bubble text.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#ffffff', value: user_bubble_text_color, onChange: updateMessage22, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'User Bubble Color: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the color of the user voice input bubble.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'#0084ff', value: user_bubble_color, onChange: updateMessage23, className: 'coderevolution_gutenberg_input' }
			),
            mygcel13x(
				'br'
			),
            mygcel13x(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'User Token Cap Per Day: '
			),
            mygcel13x(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel13x(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select a maximum number of tokens users are allowed to use in this specific chatbot, each day.'
                )
            ),
			mygcel13x(
				'input',
				{ type:'text',placeholder:'Maximum usage tokens per user, per day', value: user_token_cap_per_day, onChange: updateMessage25, className: 'coderevolution_gutenberg_input' }
			)
		);
    }),
    save: (function( props ) {
       return null;
    }),
} );