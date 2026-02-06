"use strict"; 
const { registerBlockType: customRegisterBlockType5 } = wp.blocks;
const mygcel9 = wp.element.createElement;
let editing_optionse = '';
if (Array.isArray(aiomatic_object.models)) {
    aiomatic_object.models.forEach(element => {
        editing_optionse += '<option value="' + element + '">' + element + '</option>';
    });
} else if (typeof aiomatic_object.models === 'object' && aiomatic_object.models !== null) 
{
    Object.entries(aiomatic_object.models).forEach(([key, value]) => {
        editing_optionse += '<option value="' + key + '">' + value + '</option>';
    });
}
customRegisterBlockType5( 'aiomatic-automatic-ai-content-writer/aiomatic-editing', {
    title: 'AIomatic Text Editing Form',
    icon: 'text',
    category: 'embed',
    attributes: {
        temperature : {
            default: 'default',
            type:   'string',
        },
        top_p : {
            default: 'default',
            type:   'string',
        },
        model : {
            default: 'default',
            type:   'string',
        },
        user_token_cap_per_day : {
            default: '',
            type:   'string',
        },
        prompt_templates : {
            default: '',
            type:   'string',
        },
        prompt_editable : {
            default: '',
            type:   'string',
        },
        prompt : {
            default: '',
            type:   'string',
        },
        edit_placeholder : {
            default: '',
            type:   'string',
        },
        instruction_placeholder : {
            default: '',
            type:   'string',
        },
        result_placeholder : {
            default: '',
            type:   'string',
        },
        submit_text : {
            default: '',
            type:   'string',
        },
        enable_speech : {
            default: '',
            type:   'string',
        },
        enable_copy : {
            default: '',
            type:   'string',
        }
    },
    keywords: ['list', 'posts', 'aiomatic'],
    edit: (function( props ) {
        var temperature = props.attributes.temperature;
        var top_p = props.attributes.top_p;
        var model = props.attributes.model;
        var user_token_cap_per_day = props.attributes.user_token_cap_per_day;
        var prompt_templates = props.attributes.prompt_templates;
        var prompt_editable = props.attributes.prompt_editable;
        var prompt = props.attributes.prompt;
        var edit_placeholder = props.attributes.edit_placeholder;
        var instruction_placeholder = props.attributes.instruction_placeholder;
        var result_placeholder = props.attributes.result_placeholder;
        var submit_text = props.attributes.submit_text;
        var enable_copy = props.attributes.enable_copy;
        var enable_speech = props.attributes.enable_speech;
        function updateMessage( event ) {
            props.setAttributes( { temperature: event.target.value} );
		}
        function updateMessage3( event ) {
            props.setAttributes( { top_p: event.target.value} );
		}
        function updateMessage4( event ) {
            props.setAttributes( { model: event.target.value} );
		}
        function updateMessage6( event ) {
            props.setAttributes( { user_token_cap_per_day: event.target.value} );
		}
        function updateMessage8( event ) {
            props.setAttributes( { prompt_templates: event.target.value} );
		}
        function updateMessage9( event ) {
            props.setAttributes( { prompt_editable: event.target.value} );
		}
        function updateMessage10( event ) {
            props.setAttributes( { prompt: event.target.value} );
		}
        function updateMessage11( event ) {
            props.setAttributes( { edit_placeholder: event.target.value} );
		}
        function updateMessage12( event ) {
            props.setAttributes( { instruction_placeholder: event.target.value} );
		}
        function updateMessage13( event ) {
            props.setAttributes( { result_placeholder: event.target.value} );
		}
        function updateMessage14( event ) {
            props.setAttributes( { submit_text: event.target.value} );
		}
        function updateMessage15( event ) {
            props.setAttributes( { enable_copy: event.target.value} );
		}
        function updateMessage16( event ) {
            props.setAttributes( { enable_speech: event.target.value} );
		}
		return mygcel9(
			'div', 
			{ className: 'coderevolution_gutenberg_div' },
            mygcel9(
				'h4',
				{ className: 'coderevolution_gutenberg_title' },
                'AIomatic Text Editing Form ',
                mygcel9(
                    'div', 
                    {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                    ,
                    mygcel9(
                        'div', 
                        {className:'bws_hidden_help_text'},
                        'This block is used for AI text editing.'
                    )
                )
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'AI Temperature: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'What sampling temperature to use. Higher values means the model will take more risks. Try 0.9 for more creative applications, and 0 (argmax sampling) for ones with a well-defined answer. We generally recommend altering this or top_p but not both.'
                )
            ),
			mygcel9(
				'input',
				{ type:'number',min:0,step:0.1,placeholder:'AI Temperature', value: temperature, onChange: updateMessage, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'AI Top_p: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered. We generally recommend altering this or temperature but not both.'
                )
            ),
			mygcel9(
				'input',
				{ type:'number', min:0,max:1,step:0.1,placeholder:'AI Top_p', value: top_p, onChange: updateMessage3, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Model: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select the AI model you want to use to generate the content.'
                )
            ),
            mygcel9(
				'select',
				{ value: model, onChange: updateMessage4, className: 'coderevolution_gutenberg_select', dangerouslySetInnerHTML: {
                    __html: editing_optionse
                } }                
            ),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Daily Token Count for Logged In Users: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set the daily token count for logged in users. Users who are not logged in will not be allowed to submit the form. To disable this feature, leave this field blank.'
                )
            ),
			mygcel9(
				'input',
				{ type:'number',min:0,placeholder:'Daily token count for users', value: user_token_cap_per_day, onChange: updateMessage6, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Prompt Templates (Semicolon Separated): '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Add a semicolon (;) separated list of prompt templates from which the users will be able to select and submit one.'
                )
            ),
			mygcel9(
				'input',
				{ type:'text',placeholder:'Template1;Template2;Template3', value: prompt_templates, onChange: updateMessage8, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Prompt Editable: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Select wheather the prompt will be editable by users. This is useful when combined with prompt templates from above, when you don\'t want the users to edit the entered template.'
                )
            ),
            mygcel9(
				'select',
				{ value: prompt_editable, onChange: updateMessage9, className: 'coderevolution_gutenberg_select' },
                mygcel9(
                    'option',
                    { value: 'yes'},
                    'yes'
                ), 
                mygcel9(
                    'option',
                    { value: 'no'},
                    'no'
                )
            ),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Predefined Prompt: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set a predefined prompt for the editor form. If you define a prompt here, the user will not be able to change it, but will only be able to edit text which should be edited using this prompt.'
                )
            ),
			mygcel9(
				'input',
				{ type:'text',placeholder:'Predefined prompt', value: prompt, onChange: updateMessage10, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Edit Text Placeholder: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set a a placeholder text for this textarea input'
                )
            ),
			mygcel9(
				'input',
				{ type:'text',placeholder:'Edit text placeholder', value: edit_placeholder, onChange: updateMessage11, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Instruction Text Placeholder: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set a a placeholder text for this textarea input'
                )
            ),
			mygcel9(
				'input',
				{ type:'text',placeholder:'Instruction text placeholder', value: instruction_placeholder, onChange: updateMessage12, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Results Text Placeholder: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set a a placeholder text for this textarea input'
                )
            ),
			mygcel9(
				'input',
				{ type:'text',placeholder:'Results text placeholder', value: result_placeholder, onChange: updateMessage13, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Submit Button Text:'
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Set a a submit button text. Defaul is: "Submit"'
                )
            ),
			mygcel9(
				'input',
				{ type:'text',placeholder:'Submit button text', value: submit_text, onChange: updateMessage14, className: 'coderevolution_gutenberg_input' }
			),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Enable Copy Button: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Do you want to enable the text copy button?'
                )
            ),
            mygcel9(
				'select',
				{ value: enable_copy, onChange: updateMessage15, className: 'coderevolution_gutenberg_select' },
                mygcel9(
                    'option',
                    { value: '1'},
                    '1'
                ), 
                mygcel9(
                    'option',
                    { value: '0'},
                    '0'
                )
            ),
            mygcel9(
				'br'
			),
            mygcel9(
				'label',
				{ className: 'coderevolution_gutenberg_label' },
                'Enable Speech Button: '
			),
            mygcel9(
                'div', 
                {className:'bws_help_box bws_help_box_right dashicons dashicons-editor-help'}
                ,
                mygcel9(
                    'div', 
                    {className:'bws_hidden_help_text'},
                    'Do you want to enable the speech input button?'
                )
            ),
            mygcel9(
				'select',
				{ value: enable_speech, onChange: updateMessage16, className: 'coderevolution_gutenberg_select' },
                mygcel9(
                    'option',
                    { value: '1'},
                    '1'
                ), 
                mygcel9(
                    'option',
                    { value: '0'},
                    '0'
                )
            ),
		);
    }),
    save: (function( props ) {
       return null;
    }),
} );