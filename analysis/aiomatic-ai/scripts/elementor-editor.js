
'use strict';
if (typeof ( jQuery ) != 'undefined') 
{
    ( function ( $ ) 
    {
        var Aiomatic_Content_Generator;
        var Aiomatic_Headline_Generator;

        Aiomatic_Content_Generator = function ( controlView ) 
        {
            this.init( controlView );
        };

        Aiomatic_Content_Generator.prototype = 
        {
            controls: '',
            editor: '',
            settings: '',
            init: function ( controlView ) 
            {
                this.controls = $( controlView ).get( 0 ).$el.parentsUntil( '.elementor-controls-stack' );
                this.editor = elementor.getPanelView().currentPageView.getControlViewByName( 'editor' )
                this.settings = controlView.container.settings.attributes;
            },
            handleGenerateEvent: function () 
            {
                var self = this;
                self.editor.trigger( 'change' );
                const promptControl = self.controls.find( '.elementor-control-type-textarea textarea[data-setting="aiomatic_prompt"]' );
                const siblingDiv = promptControl.next('.elementor-dynamic-cover'); 
                promptControl.css( { borderColor: 'inherit' } );
                if (siblingDiv.length === 0 && promptControl.val().trim().length == 0) {
                    promptControl.css( { borderColor: '#93003c' } );
                    alert( 'You need to add a prompt to generate content' );
                    return;
                }
                self.postGenerateRequest();
            },
            prepareForGenerate: function () 
            {
                var generateButton = this.controls.find( '.elementor-button-default[data-event="aiomatic:content:generate"]' );
                generateButton.prop( "disabled", true );
                generateButton.before( '<span class="elementor-control-spinner" style="display: inline;"><i class="eicon-spinner eicon-animation-spin"  style="font-size: 16px; color: #930b3c;"></i>&nbsp;&nbsp;</span>' );
            },
            doneWithGenerate: function () 
            {
                var generateButton = this.controls.find( '.elementor-button-default[data-event="aiomatic:content:generate"]' );
                generateButton.siblings( '.elementor-control-spinner' ).remove();
                generateButton.prop( "disabled", false );
            },
            postGenerateRequest: function () 
            {
                var self = this;
                var titleContent;
                if (typeof tinymce !== 'undefined') 
                {
                    var activeEditor = tinymce.editors.find(editor => editor && editor.id.startsWith('elementorwpeditorview') && !editor.isHidden());
                    if (activeEditor) 
                    {
                        titleContent = activeEditor.getContent();
                    } 
                    else 
                    {
                        titleContent = self.controls.find('textarea.elementor-wp-editor').val();
                    }
                } 
                else 
                {
                    titleContent = self.controls.find('textarea.elementor-wp-editor').val();
                }
                console.log(titleContent);
                if(titleContent)
                {
                    self.settings['aiomatic_prompt'] = self.settings['aiomatic_prompt'].replace("%%content%%", titleContent);
                }
                else
                {
                    self.settings['aiomatic_prompt'] = self.settings['aiomatic_prompt'].replace("%%content%%", '');
                }
                var requestData = {
                    'action': 'aiomatic_generate_content',
                    'prompt': self.settings['aiomatic_prompt'],
                    'assistant': self.settings['aiomatic_assistant'],
                    'model': self.settings['aiomatic_model'],
                    'temperature': self.settings['aiomatic_temperature'].size,
                    'topp': self.settings['aiomatic_topp'].size,
                    'presencePenalty': self.settings['aiomatic_presence_penalty'].size,
                    'frequencyPenalty': self.settings['aiomatic_frequency_penalty'].size,
                    '_ajax_nonce-aiomatic-assistant': aiomatic_ajax_object.assistant_nonce
                };

                $.ajax( {
                    url: aiomatic_ajax_object.ajax_url,
                    type: 'POST',
                    data: requestData,
                    beforeSend: function () 
                    {
                        self.prepareForGenerate();
                    },
                    success: function ( response ) 
                    {
                        self.handleGenerateResponse( response );
                    },
                    complete: function () 
                    {
                        self.doneWithGenerate();
                    }
                } )
            },
            handleGenerateResponse: function ( response ) 
            {
                var self = this;
                if (!response.success) 
                {
                    alert( response.data );
                } 
                else 
                {
                    var activeEditor = self.editor.editor;
                    var content = response.data.trim().replace( /\r?\n/g, '<br />' );
                    activeEditor.setContent( content, { format: 'html' } );
                    activeEditor.fire( 'change' );
                }
            }
        };

        Aiomatic_Headline_Generator = function ( controlView ) 
        {
            this.init( controlView );
        };

        Aiomatic_Headline_Generator.prototype = 
        {
            controls: '',
            editor: '',
            settings: '',
            init: function ( controlView ) 
            {
                this.controls = $( controlView ).get( 0 ).$el.parentsUntil( '.elementor-controls-stack' );
                this.editor = elementor.getPanelView().currentPageView.getControlViewByName( 'title' )
                this.settings = controlView.container.settings.attributes;
            },
            handleGenerateEvent: function () 
            {
                var self = this;
                self.editor.trigger( 'change' );
                const promptControl = self.controls.find( '.elementor-control-type-textarea textarea[data-setting="aiomatic_prompt"]' );
                promptControl.css( { borderColor: 'inherit' } );
                if (promptControl.val().trim().length == 0) 
                    {
                    promptControl.css( { borderColor: '#93003c' } );
                    alert( 'You need to add a prompt to generate headlines' );
                    return;
                }
                self.postGenerateRequest();
            },
            prepareForGenerate: function () 
            {
                var generateButton = this.controls.find( '.elementor-button-default[data-event="aiomatic:headline:generate"]' );
                generateButton.prop( "disabled", true );
                generateButton.before( '<span class="elementor-control-spinner" style="display: inline;"><i class="eicon-spinner eicon-animation-spin"  style="font-size: 16px; color: #930b3c;"></i>&nbsp;&nbsp;</span>' );
            },
            doneWithGenerate: function () 
            {
                var generateButton = this.controls.find( '.elementor-button-default[data-event="aiomatic:headline:generate"]' );
                generateButton.siblings( '.elementor-control-spinner' ).remove();
                generateButton.prop( "disabled", false );
            },
            postGenerateRequest: function () 
            {
                var self = this;
                var titleContent = self.controls.find('.elementor-control-type-textarea textarea[data-setting="title"]').val();
                if(titleContent)
                {
                    self.settings['aiomatic_prompt'] = self.settings['aiomatic_prompt'].replace("%%content%%", titleContent);
                }
                else
                {
                    self.settings['aiomatic_prompt'] = self.settings['aiomatic_prompt'].replace("%%content%%", '');
                }
                var requestData = {
                    'action': 'aiomatic_generate_headline',
                    'prompt': self.settings['aiomatic_prompt'],
                    'assistant': self.settings['aiomatic_assistant'],
                    'model': self.settings['aiomatic_model'],
                    'temperature': self.settings['aiomatic_temperature'].size,
                    'topp': self.settings['aiomatic_topp'].size,
                    'presencePenalty': self.settings['aiomatic_presence_penalty'].size,
                    'frequencyPenalty': self.settings['aiomatic_frequency_penalty'].size,
                    '_ajax_nonce-aiomatic-assistant': aiomatic_ajax_object.assistant_nonce
                };
                $.ajax( {
                    url: aiomatic_ajax_object.ajax_url,
                    type: 'POST',
                    data: requestData,
                    beforeSend: function () 
                    {
                        self.prepareForGenerate();
                    },
                    success: function ( response ) 
                    {
                        self.handleGenerateResponse( response );
                    },
                    complete: function () 
                    {
                        self.doneWithGenerate();
                    }
                } )

            },
            handleGenerateResponse: function ( response ) 
            {
                var self = this;
                if (!response.success) 
                {
                    alert( response.data );
                } 
                else 
                {
                    self.editor.setValue( response.data );
                    self.editor.applySavedValue();
                }
            },
        };
        $( window ).on( "elementor/init", function () 
        {
            elementor.channels.editor.on( 'aiomatic:content:generate', function ( controlView ) 
            {
                var handler = new Aiomatic_Content_Generator( controlView );
                handler.handleGenerateEvent();
            } );
            elementor.channels.editor.on( 'aiomatic:headline:generate', function ( controlView ) 
            {
                var handler = new Aiomatic_Headline_Generator( controlView );
                handler.handleGenerateEvent();
            } );
        } );
    } )( jQuery );
}