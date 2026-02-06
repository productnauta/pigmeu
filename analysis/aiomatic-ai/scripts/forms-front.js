"use strict";
function aiomatic_parseMarkdown(text) 
{
    text = text.replace(/(<br\s*\/?>\s*){2,}/gi, '<br>');
    // Headers
    text = text.replace(/^###### (.+?)$/gm, '<h6>$1</h6>');
    text = text.replace(/^##### (.+?)$/gm, '<h5>$1</h5>');
    text = text.replace(/^#### (.+?)$/gm, '<h4>$1</h4>');
    text = text.replace(/^### (.+?)$/gm, '<h3>$1</h3>');
    text = text.replace(/^## (.+?)$/gm, '<h2>$1</h2>');
    text = text.replace(/^# (.+?)$/gm, '<h1>$1</h1>');

    // Bold
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/__(.+?)__/g, '<strong>$1</strong>');

    // Italic
    text = text.replace(/([^\*])\*([^\*]+?)\*/gs, '$1<em>$2</em>');
    text = text.replace(/([^_])_([^_]+?)_/gs, '$1<em>$2</em>');

    // Strikethrough
    text = text.replace(/~~(.+?)~~/g, '<del>$1</del>');

    // Inline Code
    text = text.replace(/([^`])`([^`]+?)`/g, '$1<code>$2</code>');

    // Code Block
    text = text.replace(/```([\s\S]+?)```/g, '<code>$1</code>');

    // Link
    text = text.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2">$1</a>');

    // Images
    text = text.replace(/!\[(.+?)\]\((.+?)\)/g, '<img src="$2" alt="$1">');

    // Horizontal Rule
    text = text.replace(/^-{3,}$/gm, '<hr>');

    // Blockquote
    text = text.replace(/^> (.+?)$/gm, '<blockquote>$1</blockquote>');

    return text;
}
function processKatexMarkdown(text) 
{
    text = text.replace(/(\\\[)([\s\S]*?)(\\\])/g, (match, start, content, end) => {
        const cleanedContent = content.replace(/<br\s*\/?>/gi, '');
        return `${start}${cleanedContent}${end}`;
    });
    return text;
}
function aiomaticCountChars(textbox, counter) 
{
    var counterElement = document.getElementById(counter);
    if (!counterElement) 
    {
        return;
    }
    if (typeof tinymce !== 'undefined' && tinymce.get(textbox)) 
    {
        textboxElement = tinymce.get(textbox).getContent({format: 'text'});
        counterElement.innerHTML = textboxElement.length;
        return;
    }
    var textboxElement = document.getElementById(textbox);    
    if (!textboxElement) 
    {
        return;
    }
    var count = textboxElement.value.length;
    counterElement.innerHTML = count;
}
jQuery(document).ready(function ($)
{
    var model_type = 'gpt';
    function aiomatic_nl2br (str, is_xhtml) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }
    function aiChatUploadDataomaticForm(aiomatic_completition_ajax_object_forms, uniqid, input_text, remember_string, user_question, function_result) 
    {
        var formData = new FormData();
        formData.append('uniqid', uniqid);
        formData.append('input_text', input_text);
        formData.append('remember_string', remember_string);
        formData.append('user_question', user_question);
        if(function_result !== null)
        {
            formData.append('function_result', function_result);
        }
        formData.append('action', 'aiomatic_save_chat_data');
        formData.append('nonce', aiomatic_completition_ajax_object_forms.persistentnonce);
        return jQuery.ajax({
            url: aiomatic_completition_ajax_object_forms.ajax_url,
            async: false, 
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false
        });
    };
    function aiomatic_mergeDeep(target, source) 
    {
        Object.keys(source).forEach(key => 
        {
            if (source[key] && typeof source[key] === 'object' && key !== 'arguments') 
            {
                if (!target[key]) 
                {
                    target[key] = {};
                }
                aiomatic_mergeDeep(target[key], source[key]);
            } 
            else 
            {
                if (key === 'arguments') 
                {
                    if (!target[key]) 
                    {
                        target[key] = '';
                    } 
                    target[key] += source[key];
                } 
                else 
                {
                    target[key] = source[key];
                }
            }
        });
    }
    function AiomaticValidateEmail(input) 
    {
        var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        if (input.match(validRegex)) {
          return true;
        } else {
          return false;
        }
    }
    function AiomaticisValidUrl(string) {
        try {
          new URL(string);
          return true;
        } catch (err) {
          return false;
        }
      }
    function aiomatichtmlDecode(input) {
        var doc = new DOMParser().parseFromString(input, "text/html");
        return doc.documentElement.textContent;
    }
    function aiomaticBasicEditor(dataId){
        var basicEditor = true;
        if (typeof tinyMCE === 'undefined' || tinyMCE === null)
        {
            return basicEditor;
        }
        var editor = tinyMCE.get('aiomatic-prompt-result' + dataId);
        var inputp = document.getElementById('wp-aiomatic-prompt-result' + dataId + '-wrap');
        if ( inputp !== null && inputp.classList.contains('tmce-active') && editor ) {
            basicEditor = false;
        }
        return basicEditor;
    }
    function aiomaticSetContent(dataId, value)
    {
        var html_found = false;
        if (document.getElementById('aiomatic_html_output' + dataId)) {
            document.getElementById('aiomatic_html_output' + dataId).innerHTML = value;
            html_found = true;
        } else if (aiomaticBasicEditor(dataId)) {
            document.getElementById('aiomatic-prompt-result' + dataId).value = aiomatichtmlDecode(value);
        } else {
            var editor = tinyMCE.get('aiomatic-prompt-result' + dataId);
            editor.setContent(aiomatichtmlDecode(value));
        }
        aiomaticCountChars('aiomatic-prompt-result' + dataId,'charCount_span' + dataId);
        return html_found;
    }
    function aiomaticGetContent(dataId) {
        if (document.getElementById('aiomatic_html_output' + dataId)) {
            return document.getElementById('aiomatic_html_output' + dataId).innerHTML;
        } else if (aiomaticBasicEditor(dataId)) {
            return document.getElementById('aiomatic-prompt-result' + dataId).value;
        } else {
            var editor = tinyMCE.get('aiomatic-prompt-result' + dataId);
            return editor.getContent();
        }
    }
    function aiomaticLoadingBtn(btn){
        btn.attr('disabled', '');
        btn.addClass('button--loading');
    }
    function aiomaticRmLoading(btn){
        btn.removeAttr('disabled');
        btn.removeClass('button--loading');
    }
    function aiSimpleDecryptWithKey(encryptedText, key) 
    {
        var decodedData = atob(encryptedText);
        var result = '';
        for (var i = 0, len = decodedData.length; i < len; i++) 
        {
            var shift = key.charCodeAt(i % key.length);
            var charCode = (decodedData.charCodeAt(i) - shift + 256) % 256;
            var char = String.fromCharCode(charCode);
            result += char;
        }
        return result;
    }
    var eventGenerator = false;
    var aiomatic_generator_working = false;
    $(document).on('click','.aiomatic_copy_btn', function(e) 
    {
        e.preventDefault();
        var editorId = $(this).attr('data-id');
        if(editorId !== undefined && editorId !== null)
        {
            var content;
            if (tinyMCE.get('aiomatic-prompt-result' + editorId)) {
                content = tinyMCE.get('aiomatic-prompt-result' + editorId).getContent();
            } else {
                content = $('#aiomatic-prompt-result' + editorId).val();
            }
            if(navigator.clipboard !== undefined)
            {
                navigator.clipboard.writeText(content).then(function() {
                    alert('Content copied to clipboard');
                }, function(err) {
                    console.error('Could not copy text: ', err);
                });
            }
            else
            {
                console.error('Failed to copy text');
            }
        }
    });
    $(document).on('click', '.aiomatic_pdf_btn', function(e) {
        e.preventDefault();
        var editorId = $(this).attr('data-id');
        if (!editorId) return;

        var content;
        var isHTML = false;

        if (tinyMCE.get('aiomatic-prompt-result' + editorId)) {
            content = tinyMCE.get('aiomatic-prompt-result' + editorId).getContent();
            isHTML = true;
        } else {
            content = $('#aiomatic-prompt-result' + editorId).val();
        }
        if (!content || content.trim() === '' || (isHTML && $(content).text().trim() === '')) {
            alert('Cannot generate PDF: content is empty.');
            return;
        }
        if (typeof html2canvas === 'function' && window.jspdf && window.jspdf.jsPDF) {
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            tempDiv.style.position = 'absolute';
            tempDiv.style.left = '-9999px';
            document.body.appendChild(tempDiv);

            html2canvas(tempDiv).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new window.jspdf.jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });

                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save('content-' + editorId + '.pdf');

                document.body.removeChild(tempDiv);
            });
        } else {
            alert('html2canvas is not available in the preview mode.');
        }
    });

    $(document).on('click','.aiomatic_copy_btn_html', function(e) 
    {
        e.preventDefault();
        var editorId = $(this).attr('data-id');
        if(editorId !== undefined && editorId !== null)
        {
            var content;
            content = $('#aiomatic_html_output' + editorId).html();
            if(navigator.clipboard !== undefined)
            {
                navigator.clipboard.writeText(content).then(function() {
                    alert('Content copied to clipboard');
                }, function(err) {
                    console.error('Could not copy text: ', err);
                });
            }
            else
            {
                console.error('Failed to copy text');
            }
        }
    });
    $(document).on('click', '.aiomatic_pdf_btn_html', function(e) {
        e.preventDefault();

        if (typeof html2canvas !== 'function') {
            alert('PDF export is not available in the preview window.');
            return;
        }

        const editorId = $(this).attr('data-id');
        if (!editorId) return;

        const element = document.getElementById('aiomatic_html_output' + editorId);
        if (!element) return;
        const content = element.innerHTML.trim();
        if (!content || content === '') {
            alert('There is no content to export.');
            return;
        }
        html2canvas(element).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf || {};

            if (!jsPDF) {
                alert('jsPDF is not available in the preview window.');
                return;
            }

            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            pdf.save('content-' + editorId + '.pdf');
        });
    });
    function aiomaticScrapeData(value) {
        return new Promise((resolve, reject) => {
            jQuery.ajax({
                type: 'POST',
                url: aiomatic_completition_ajax_object_forms.ajax_url,
                data: {
                    action: 'aiomatic_scrape_form_submit',
                    scrapeurl: value,
                    nonce: aiomatic_completition_ajax_object_forms.nonce
                },
                success: function(response) {
                    if (typeof response === 'string' || response instanceof String) {
                        try {
                            var responset = JSON.parse(response);
                            response = responset;
                        } catch (error) {
                            reject("An error occurred while parsing the JSON: " + error + ' Json: ' + response);
                            return;
                        }
                    }
                    resolve(response);
                },
                error: function(error) {
                    reject(error);
                }
            });
        });
    }
    $(document).on('click','.aiomatic-generate-button', async function(e)
    {
        e.preventDefault();
        var targetButton = e.target.closest('.aiomatic-generate-button');
        if (targetButton) 
        {
            var dataId = targetButton.getAttribute('data-id');
        }
        else
        {
            jQuery('#openai-response').html('<div class="text-primary highlight-text-fail" role="status">Failed to process form response, please try again later.</div>');
            console.log('aiomatic-generate-button not found!');
            return;
        }
        var downloadButton = jQuery("#download_button" + dataId);
        downloadButton.hide();
        jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status"></div>');
        var aiomaticGenerateBtn = $('#aiomatic-generate-button' + dataId);
        if (aiomaticGenerateBtn.length == 0) 
        {
            jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">There was an error during processing, please try again later.</div>');
            return;
        }
        var formid = aiomatic_completition_ajax_object_forms.formid;
        if(!formid || formid === '') {
            formid = $('#aiomatic-formid' + dataId).val();
        }
        var aiomaticMaxToken = document.getElementById('aiomatic-engine' + dataId);
        var max_tokens = aiomaticMaxToken.value;
        var aiomaticTemperature = document.getElementById('aiomatic-temperature' + dataId);
        var aiomaticTypeInput = document.getElementById('aiomatic-form-type' + dataId);
        var aiomaticType = aiomaticTypeInput.value;
        var temperature = aiomaticTemperature.value;
        var aiomaticTopP = document.getElementById('aiomatic-top_p' + dataId);
        var top_p = aiomaticTopP.value;
        var aiomaticFP = document.getElementById('aiomatic-frequency_penalty' + dataId);
        var frequency_penalty = aiomaticFP.value;
        var aiomaticPP = document.getElementById('aiomatic-presence_penalty' + dataId);
        var presence_penalty = aiomaticPP.value;
        var error_message = false;
        var aiomaticPromptTitle = document.getElementById('aiomatic-prompt' + dataId);
        var prompt = aiomaticPromptTitle.value;
        prompt = aiSimpleDecryptWithKey(prompt, aiomatic_completition_ajax_object_forms.secretkey);
        var model = jQuery("#aiomatic-engine" + dataId).val();
        if(aiomatic_completition_ajax_object_forms.claude_models.includes(model))
        {
            model_type = 'claude';
        }
        else
        {
            if(aiomatic_completition_ajax_object_forms.google_models.includes(model))
            {
                model_type = 'google';
            }
            else
            {
                if(aiomatic_completition_ajax_object_forms.huggingface_models.includes(model))
                {
                    model_type = 'huggingface';
                }
            }
        }
        var instant_response = jQuery("#aiomatic-streaming" + dataId).val();
        var wait_omniblock = jQuery("#aiomatic-wait" + dataId).val();
        var assistant_id = jQuery("#aiomatic-assistant-id" + dataId).val();
        var allFormData = {};
        if(aiomaticType == 'omniblock-form')
        {
            allFormData['prompt'] = prompt;
        }
        if(assistant_id === null)
        {
            assistant_id = '';
        }
        if(prompt === ''){
            error_message = 'Please insert prompt';
        }
        else if(max_tokens === ''){
            error_message = 'Please enter max tokens';
        }
        else if(parseFloat(max_tokens) < 1 || parseFloat(max_tokens) > 8000){
            error_message = 'Please enter a valid max tokens value between 1 and 8000';
        }
        else if(temperature === ''){
            error_message = 'Please enter temperature';
        }
        else if(parseFloat(temperature) < 0 || parseFloat(temperature) > 2){
            error_message = 'Please enter a valid temperature value between 0 and 2';
        }
        else if(top_p === ''){
            error_message = 'Please enter Top P';
        }
        else if(parseFloat(top_p) < 0 || parseFloat(top_p) > 1){
            error_message = 'Please enter a valid Top P value between 0 and 1';
        }
        else if(frequency_penalty === ''){
            error_message = 'Please enter frequency penalty';
        }
        else if(parseFloat(frequency_penalty) < -2 || parseFloat(frequency_penalty) > 2){
            error_message = 'Please enter a valid frequency penalty value between -2 and 2';
        }
        else if(presence_penalty === ''){
            error_message = 'Please enter presence penalty';
        }
        else if(parseFloat(presence_penalty) < -2 || parseFloat(presence_penalty) > 2){
            error_message = 'Please enter a valid presence penalty value between -2 and 2';
        }
        const scrapePromises = [];
        let formin = $('.aiomatic-form-input' + dataId);
        for (const element of formin)
        {
            let $element = $(element);
            let name = $element.attr('aiomatic-name');
            if(name === undefined || name === null || name === '')
            {
                name = $element.attr('name');
            }
            let value = $element.val();
            if(value === null)
            {
                value = '';
            }
            let type = $element.attr('data-type');
            let min = $element.attr('data-min');
            let max = $element.attr('data-max');
            let required = $element.attr('data-required');
            let limit = $element.attr('data-limit');
            if (type === 'file') 
            {
                let file = $element.prop('files')[0];
                if (file) {
                    let accept = $element.attr('data-accept');
                    if (accept) {
                        let acceptedTypes = accept.split(',').map(type => type.trim());
                        let isAccepted = acceptedTypes.some(acceptedType => {
                            if (acceptedType.endsWith('/*')) {
                                let baseType = acceptedType.split('/')[0];
                                return file.type.startsWith(baseType + '/');
                            }
                            if (acceptedType.startsWith('.')) {
                                let fileExtension = '.' + file.name.split('.').pop();
                                return fileExtension.toLowerCase() === acceptedType.toLowerCase();
                            }
                            return file.type === acceptedType;
                        });

                        if (!isAccepted) {
                            error_message = name + ': The selected file type is not allowed. Accepted types are: ' + accept;
                        }
                    }
                    if (!error_message) {
                        value = await new Promise((resolve, reject) => {
                            let fileReader = new FileReader();
                            if (file.type.startsWith('text/') || file.type === 'application/json') {
                                fileReader.onload = function () {
                                    resolve(fileReader.result);
                                };
                                fileReader.readAsText(file);
                            } else if (file.type === 'application/pdf' || file.type.startsWith('application/')) {
                                import('https://mozilla.github.io/pdf.js/build/pdf.mjs').then(pdfjsLib => {
                                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://mozilla.github.io/pdf.js/build/pdf.worker.mjs';
                                    const fileReader = new FileReader();
                                    fileReader.onload = function () {
                                        const pdfData = new Uint8Array(fileReader.result);
                                        const pdf = pdfjsLib.getDocument({ data: pdfData });
                                        pdf.promise.then(function (pdf) {
                                            const totalPageCount = pdf.numPages;
                                            const pagePromises = [];
                                            for (let currentPage = 1; currentPage <= totalPageCount; currentPage++) {
                                                const page = pdf.getPage(currentPage);
                                                pagePromises.push(
                                                    page.then(function (page) {
                                                        return page.getTextContent().then(function (textContent) {
                                                            return textContent.items.map(item => item.str).join('');
                                                        });
                                                    })
                                                );
                                            }
                                            Promise.all(pagePromises).then(function (pagesText) {
                                                const fullText = pagesText.join('');
                                                resolve(fullText); 
                                            });
                                        }).catch(function (error) {
                                            console.error('Error extracting text from PDF:', error);
                                            reject(error); 
                                        });
                                    };
                                    fileReader.readAsArrayBuffer(file); 
                                });
                            } else {
                                fileReader.onload = function () {
                                    let base64String = fileReader.result.split(',')[1];
                                    resolve(base64String);
                                };
                                fileReader.readAsDataURL(file);
                            }
                            fileReader.onerror = function () {
                                reject('Error reading file');
                            };
                        });
                    }
                }
                else 
                {
                    value = '';
                    if(required == 'yes')
                    {
                        error_message = name + ': This field is required';
                    }
                }
            }
            console.log(name + ': ' + value);
            if(aiomatic_completition_ajax_object_forms.min_len != '')
            {
                if(name != 'presence_penalty' && name != 'frequency_penalty' && name != 'top_p' && name != 'temperature' && name != 'max_tokens' && name != 'engine' && name != 'assistant-id' && value.length < parseInt(aiomatic_completition_ajax_object_forms.min_len, 10))
                {
                    error_message = name + ': You need to enter a longer input value, minimum is: ' + aiomatic_completition_ajax_object_forms.min_len;
                }
            }
            if(aiomatic_completition_ajax_object_forms.max_len != '')
            {
                if(name != 'presence_penalty' && name != 'frequency_penalty' && name != 'top_p' && name != 'temperature' && name != 'max_tokens' && name != 'engine' && name != 'assistant-id' && value && value.length && value.length > parseInt(aiomatic_completition_ajax_object_forms.max_len, 10))
                {
                    error_message = name + ': You need to enter a shorter input value, maximum is: ' + aiomatic_completition_ajax_object_forms.max_len;
                }
            }
            if(min != '')
            {
                if(name != 'presence_penalty' && name != 'frequency_penalty' && name != 'top_p' && name != 'temperature' && name != 'max_tokens' && name != 'engine' && name != 'assistant-id' && min > parseInt(value, 10))
                {
                    error_message = name + ': You need to enter a value larger than ' + min;
                }
            }
            if(max != '')
            {
                if(name != 'presence_penalty' && name != 'frequency_penalty' && name != 'top_p' && name != 'temperature' && name != 'max_tokens' && name != 'engine' && name != 'assistant-id' && max < parseInt(value, 10))
                {
                    error_message = name + ': You need to enter a value smaller than ' + max;
                }
            }
            if(type == 'email')
            {
                if(!AiomaticValidateEmail(value))
                {
                    error_message = name + ': Invalid email address submitted: ' + value;
                }
            }
            if(type == 'url' || type == 'scrape')
            {
                if(!AiomaticisValidUrl(value))
                {
                    error_message = name + ': Invalid URL submitted: ' + value;
                }
            }
            if(required == 'yes' && value == '')
            {
                error_message = name + ': This field is required';
            }
            if(type == 'scrape')
            {
                aiomaticLoadingBtn(aiomaticGenerateBtn);
                scrapePromises.push(
                    aiomaticScrapeData(value).then(response => {
                        if (response.status == 'success') 
                        {
                            if (response.data == '') 
                            {
                                error_message = name + ': Cannot scrape URL, no content found.';
                            } 
                            else 
                            {
                                if (typeof limit !== 'undefined' && limit !== false && limit !== null && limit !== '') 
                                {
                                    limit = parseInt(limit, 10);
                                    if (!isNaN(limit) && limit > 0) 
                                    {
                                        if (response.data.length > limit) 
                                        {
                                            response.data = response.data.substring(0, limit);
                                        }
                                    }
                                }
                                prompt = prompt.replace('%%' + name + '%%', response.data);
                            }
                        } 
                        else 
                        {
                            if (typeof response.msg !== 'undefined') 
                            {
                                error_message = name + ': Error in scraping: ' + response.msg;
                            } 
                            else 
                            {
                                error_message = name + ': Scraping failed, please try again later.';
                            }
                        }
                    }).catch(error => {
                        error_message = name + ': Error in request: ' + error.responseText;
                    }).finally(() => {
                        aiomaticRmLoading(aiomaticGenerateBtn);
                    })
                );
            }
            else
            {
                if (typeof limit !== 'undefined' && limit !== false && limit !== null && limit !== '') 
                {
                    limit = parseInt(limit, 10);
                    if (!isNaN(limit) && limit > 0) 
                    {
                        if (value.length > limit) 
                        {
                            value = value.substring(0, limit);
                        }
                    }
                }
                prompt = prompt.replace('%%' + name + '%%', value);
                if(aiomaticType == 'omniblock-form')
                {
                    if(name !== 'assistant-id' && name !== 'engine' && name !== 'temperature' && name !== 'top_p' && name !== 'frequency_penalty' && name !== 'presence_penalty' && name !== 'max_tokens')
                    {
                        allFormData[name] = value;
                    }
                }
            }
        };
        let captchaInput = document.getElementById('aiomatic-captcha-' + dataId);
        if (captchaInput) {
            let userAnswer = captchaInput.value.trim();
            let correctAnswer = captchaInput.dataset.captchaAnswer;

            if (userAnswer === '') {
                error_message = 'Please solve the CAPTCHA.';
            } else if (userAnswer !== correctAnswer) {
                error_message = 'Incorrect CAPTCHA answer. Try again.';
            }
        }
        await Promise.all(scrapePromises);
        if(prompt === '')
        {
            error_message = 'Empty prompt was returned';
        }
        if(error_message)
        {
            jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">' + error_message + '</div>');
            console.log('AI Form processing failed: ' + error_message);
            jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
            aiomaticRmLoading(aiomaticGenerateBtn);
            return;
        }
        else
        {
            aiomaticLoadingBtn(aiomaticGenerateBtn);
            var image_placeholder = aiomatic_completition_ajax_object_forms.image_placeholder;
            jQuery("#aiomatic_form_response" + dataId).attr("src", image_placeholder).fadeIn();
            if(aiomaticType == 'text' || aiomaticType == 'omniblock-form')
            {
                aiomaticSetContent(dataId, '');
            }
            document.getElementById('openai-response' + dataId).innerHTML = '';

            if(instant_response == 'stream' && aiomaticType == 'text')
            {
                if(aiomatic_generator_working === true)
                {
                    console.log('AI already processing a request!');
                    jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                    jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">Failed to generate content, try again later.</div>');
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    return;
                }
                var enable_god_mode = 'off';
                aiomatic_generator_working = true;
                var count_line = 0;
                var response_data = '';
                var pdf_data = '';
                var ai_thread_id = '';
                var user_token_cap_per_day = '';
                var remember_string = '';
                var user_question = prompt;
                var input_text = prompt;
                var fileInput = document.getElementById('aiomatic_vision_input' + dataId);
                if (fileInput && fileInput.files) 
                {
                    var xfile = fileInput.files[0];
                    if (xfile) {
                        var vision_file = URL.createObjectURL(xfile);
                    } else {
                        var vision_file = '';
                    }
                }
                else
                {
                    var vision_file = '';
                }
                var mystream = aiomatic_completition_ajax_object_forms.stream_url;
                if(aiomatic_completition_ajax_object_forms.claude_models.includes(model))
                {
                    mystream = aiomatic_completition_ajax_object_forms.stream_url_claude;
                }
                var eventURL = mystream + '&pdf_data=' + encodeURIComponent(pdf_data) + 
                '&user_token_cap_per_day=' + encodeURIComponent(user_token_cap_per_day) + 
                '&forms_replace=1' + 
                '&user_id=' + encodeURIComponent(aiomatic_completition_ajax_object_forms.user_id) + 
                '&frequency=' + encodeURIComponent(frequency_penalty) + 
                '&presence=' + encodeURIComponent(presence_penalty) + 
                '&top_p=' + encodeURIComponent(top_p) + 
                '&temp=' + encodeURIComponent(temperature) + 
                '&model=' + encodeURIComponent(model) + 
                '&assistant_id=' + encodeURIComponent(assistant_id) + 
                '&thread_id=' + encodeURIComponent(ai_thread_id) + 
                '&input_text=' + encodeURIComponent(input_text) + 
                '&remember_string=' + encodeURIComponent(remember_string) + 
                '&user_question=' + encodeURIComponent(user_question) + 
                '&enable_god_mode=' + encodeURIComponent(enable_god_mode);
                if(vision_file != '')
                {
                    eventURL += '&vision_file=' + encodeURIComponent(vision_file);
                }
                if(eventURL.length > 2080)
                {
                    console.log('URL too long, using alternative event method');
                    var unid = "id" + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);;
                    aiChatUploadDataomaticForm(aiomatic_completition_ajax_object_forms, unid, input_text, remember_string, user_question, null);
                    eventURL = mystream + '&pdf_data=' + encodeURIComponent(pdf_data) + 
                    '&user_token_cap_per_day=' + encodeURIComponent(user_token_cap_per_day) + 
                    '&forms_replace=1' + 
                    '&user_id=' + encodeURIComponent(aiomatic_completition_ajax_object_forms.user_id) + 
                    '&frequency=' + encodeURIComponent(frequency_penalty) + 
                    '&presence=' + encodeURIComponent(presence_penalty) + 
                    '&top_p=' + encodeURIComponent(top_p) + 
                    '&temp=' + encodeURIComponent(temperature) + 
                    '&model=' + encodeURIComponent(model) + 
                    '&assistant_id=' + encodeURIComponent(assistant_id) + 
                    '&thread_id=' + encodeURIComponent(ai_thread_id) + 
                    '&input_text=0' + 
                    '&remember_string=0' +  
                    '&user_question=0' + 
                    '&enable_god_mode=' + encodeURIComponent(enable_god_mode) +
                    '&bufferid=' + encodeURIComponent(unid);
                    if(vision_file != '')
                    {
                        eventURL += '&vision_file=' + encodeURIComponent(vision_file);
                    }
                }
                eventGenerator = new EventSource(eventURL);
                var error_generated = '';
                var func_call = {
                    init_data: {
                        pdf_data: pdf_data, 
                        user_token_cap_per_day: user_token_cap_per_day, 
                        user_id: aiomatic_completition_ajax_object_forms.user_id, 
                        frequency: frequency_penalty, 
                        presence: presence_penalty, 
                        top_p: top_p, 
                        temp: temperature, 
                        model: model, 
                        input_text: input_text, 
                        remember_string: remember_string, 
                        user_question: user_question
                    },
                };
                function handleResponsesMessageFailedEvent(e) 
                {
                    console.log('Responses API failed: ' + JSON.stringify(e));
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                    return;
                }
                function handleMessageErrorEvent(e) 
                {
                    console.log('Error in Event running: ' + JSON.stringify(e));
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                    return;
                }
                function handleRefusalDone(e) { 
                    console.error('The AI refused to respond:', JSON.stringify(e.data));          
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                }
                function handleOutputItemAdded(e) {
                }
                function handleContentBlockDeltaResponses(e) 
                {
                    var hasFinishReason = false;
                    var aiomatic_response_events = 0;
                    var aiomatic_limitLines = 1;
                    var resultData = null;
                    var content_generated = '';
                    var aiomatic_newline_before = false;
                    if (e.type === 'response.output_text.delta' || e.type === 'response.completed' || e.type === 'response.failed' || e.type === 'response.incomplete') {
                        try {
                            resultData = JSON.parse(e.data);
                        } catch (err) {
                            console.warn('Failed to parse OpenAI event data: ' + err);
                            aiomaticRmLoading(chatbut);
                            aiomatic_generator_working = false;
                            eventGenerator.close();
                            jQuery('#aistopbut' + dataId).hide();
                            return;
                        }
                
                        if (e.type === 'response.output_text.delta') {
                            content_generated = resultData.delta || '';
                            aiomatic_response_events += 1;
                        } else if (e.type === 'response.completed') {
                            hasFinishReason = true;
                            content_generated = ''; 
                        } else if (e.type === 'response.failed') {
                            error_generated = resultData.response.error?.message || 'Unknown error';
                            console.log('OpenAI Response failed: ' + error_generated);
                            jQuery('#openai-chat-response' + dataId).html('<div class="text-primary highlight-text" role="status">' + error_generated + '</div>');
                            eventGenerator.close();
                            jQuery('#aistopbut' + dataId).hide();
                            aiomaticRmLoading(chatbut);
                            aiomatic_generator_working = false;
                            return;
                        } else if (e.type === 'response.incomplete') {
                            hasFinishReason = true;
                            error_generated = 'Response incomplete: ' + (resultData.response.incomplete_details?.reason || 'unknown reason');
                            console.warn(error_generated);
                        }
                        response_data += aiomatic_nl2br(content_generated);
                        if((content_generated === '\n' || content_generated === ' \n' || content_generated === '.\n' || content_generated === '\n\n' || content_generated === '.\n\n' || content_generated === '"\n') && aiomatic_response_events > 0){
                            if(!aiomatic_newline_before) {
                                aiomatic_newline_before = true;
                            }
                        }
                        else if(content_generated === '\n' && aiomatic_response_events === 0){

                        }
                        else{
                            aiomatic_newline_before = false;
                            aiomatic_response_events += 1;
                            if (aiomatic_completition_ajax_object_forms.katex_parse == 'on') {
                                response_data = processKatexMarkdown(response_data);
                            }
                            if (aiomatic_completition_ajax_object_forms.markdown_parse == 'on') {
                                response_data = aiomatic_parseMarkdown(response_data);
                            }
                            var htmlfound = aiomaticSetContent(dataId, response_data);
                            if(htmlfound === true) {
                                if (aiomatic_completition_ajax_object_forms.katex_parse == 'on' && typeof renderMathInElement === 'function') {
                                    var katexContainer = document.getElementById('aiomatic_html_output' + dataId);
                                    renderMathInElement(katexContainer, {
                                        delimiters: [
                                            
                                            { left: '\\(', right: '\\)', display: false },
                                            { left: '\\[', right: '\\]', display: true }
                                        ],
                                        throwOnError: false
                                    });
                                }
                            }
                        }
                    }
                    if(hasFinishReason){
                        count_line += 1;
                        aiomatic_response_events = 0;
                    }
                    if(count_line >= aiomatic_limitLines)
                    {
                        eventGenerator.close();
                        aiomatic_generator_working = false;
                        jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                        aiomaticRmLoading(aiomaticGenerateBtn);
                        const responseContainer = jQuery(`#aiomatic-form-history-` + formid + ` .aiomatic-history-list`);
                        if (responseContainer) 
                        {
                            var entryText = aiomaticGetContent(dataId);
                            jQuery.ajax({
                                type: 'POST',
                                url: aiomatic_completition_ajax_object_forms.ajax_url,
                                data: {
                                    action: 'aiomatic_add_form_history_entry',
                                    response_text: entryText,
                                    nonce: aiomatic_completition_ajax_object_forms.nonce,
                                    form_id: formid,
                                },
                                success: function(response) {
                                    if(response.data.status == 'success')
                                    {
                                        const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                        const index = response.data.unique_index; 
                                        const formId = formid;
                                        if(index != '')
                                        {
                                            const newEntryHtml = `
                                                <li class="aiomatic-history-item">
                                                    <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                        ${snippet}
                                                    </span>
                                                    <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                        &times;
                                                    </button>
                                                </li>
                                            `;
                                            responseContainer.prepend(newEntryHtml);
                                        }
                                    }
                                    else
                                    {
                                        console.log('Error in form response: ' + JSON.stringify(response));
                                    }
                                },
                                error: function(error) {
                                    console.log('Error in forms: ' + error.responseText);
                                },
                            });
                        }
                        if(error_generated == '')
                        {
                            var entryText = aiomaticGetContent(dataId);
                            jQuery.ajax({
                                type: 'POST',
                                url: aiomatic_completition_ajax_object_forms.ajax_url,
                                data: {
                                    action: 'aiomatic_record_user_usage',
                                    nonce: aiomatic_completition_ajax_object_forms.persistentnonce,
                                    user_id: aiomatic_completition_ajax_object_forms.user_id,
                                    input_text: input_text,
                                    response_text: response_data,
                                    model: model,
                                    temp: temperature,
                                    vision_file: vision_file,
                                    user_token_cap_per_day: '',
                                    source: 'forms'
                                },
                                success: function() 
                                {
                                },
                                error: function(error) {
                                    console.log('Error while saving user data: ' + error.responseText);
                                },
                            });
                        }
                    }
                }
                function handleContentBlockDelta(e) 
                {
                    var hasFinishReason = false;
                    if(model_type == 'claude')
                    {
                        var aiomatic_newline_before = false;
                        var aiomatic_response_events = 0;
                        var aiomatic_limitLines = 1;
                        var resultData = null;
                        if(e.data == '[DONE]')
                        {
                            hasFinishReason = true;
                        }
                        else
                        {
                            try 
                            {
                                resultData = JSON.parse(e.data);
                            } 
                            catch (e) 
                            {
                                console.warn(e);
                                jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">Failed to generate content, try again later.</div>');
                                aiomaticRmLoading(aiomaticGenerateBtn);
                                aiomatic_generator_working = false;
                                eventGenerator.close();
                                return;
                            }
                            var hasFinishReason = resultData &&
                            (resultData.finish_reason === "stop" ||
                            resultData.finish_reason === "length");
                            if(resultData.stop_reason == 'stop_sequence' || resultData.stop_reason == 'max_tokens')
                            {
                                hasFinishReason = true;
                            }
                        }
                        var content_generated = '';
                        if(hasFinishReason){
                            count_line += 1;
                            aiomatic_response_events = 0;
                        }
                        else
                        {
                            if(resultData !== null)
                            {
                                var result = resultData;
                            }
                            else
                            {
                                var result = null;
                                try {
                                    result = JSON.parse(e.data);
                                } 
                                catch (e) 
                                {
                                    console.warn(e);
                                    jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                    jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">Failed to generate content, try again later.</div>');
                                    aiomaticRmLoading(aiomaticGenerateBtn);
                                    aiomatic_generator_working = false;
                                    eventGenerator.close();
                                    return;
                                };
                            }
                            if(result.error !== undefined){
                                error_generated = result.error[0].message;
                                if(error_generated === undefined)
                                {
                                    error_generated = result.error.message;
                                }
                                if(error_generated === undefined)
                                {
                                    error_generated = result.error;
                                }
                                jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text" role="status">' + error_generated + '</div>');
                                console.log('Error while processing request(1): ' + error_generated);
                            }
                            else
                            {
                                if(result.completion !== undefined)
                                {
                                    content_generated = result.completion;
                                }
                                else if(result.delta.text !== undefined)
                                {
                                    content_generated = result.delta.text;
                                }
                                else
                                {
                                    console.log('Unrecognized format: ' + result);
                                    content_generated = '';
                                }
                            }
                            response_data += aiomatic_nl2br(content_generated);
                            if((content_generated === '\n' || content_generated === ' \n' || content_generated === '.\n' || content_generated === '\n\n' || content_generated === '.\n\n' || content_generated === '"\n') && aiomatic_response_events > 0){
                                if(!aiomatic_newline_before) {
                                    aiomatic_newline_before = true;
                                }
                            }
                            else if(content_generated === '\n' && aiomatic_response_events === 0){

                            }
                            else{
                                aiomatic_newline_before = false;
                                aiomatic_response_events += 1;
                                if (aiomatic_completition_ajax_object_forms.katex_parse == 'on') {
                                    response_data = processKatexMarkdown(response_data);
                                }
                                if (aiomatic_completition_ajax_object_forms.markdown_parse == 'on') {
                                    response_data = aiomatic_parseMarkdown(response_data);
                                }
                                var htmlfound = aiomaticSetContent(dataId, response_data);
                                if(htmlfound === true) {
                                    if (aiomatic_completition_ajax_object_forms.katex_parse == 'on' && typeof renderMathInElement === 'function') {
                                        var katexContainer = document.getElementById('aiomatic_html_output' + dataId);
                                        renderMathInElement(katexContainer, {
                                            delimiters: [
                                                
                                                { left: '\\(', right: '\\)', display: false },
                                                { left: '\\[', right: '\\]', display: true }
                                            ],
                                            throwOnError: false
                                        });
                                    }
                                }
                            }
                        }
                        if(count_line >= aiomatic_limitLines)
                        {
                            eventGenerator.close();
                            aiomatic_generator_working = false;
                            jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                            aiomaticRmLoading(aiomaticGenerateBtn);
                            const responseContainer = jQuery(`#aiomatic-form-history-` + formid + ` .aiomatic-history-list`);
                            if (responseContainer) 
                            {
                                var entryText = aiomaticGetContent(dataId);
                                jQuery.ajax({
                                    type: 'POST',
                                    url: aiomatic_completition_ajax_object_forms.ajax_url,
                                    data: {
                                        action: 'aiomatic_add_form_history_entry',
                                        response_text: entryText,
                                        nonce: aiomatic_completition_ajax_object_forms.nonce,
                                        form_id: formid,
                                    },
                                    success: function(response) {
                                        if(response.data.status == 'success')
                                        {
                                            const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                            const index = response.data.unique_index; 
                                            const formId = formid;
                                            if(index != '')
                                            {
                                                const newEntryHtml = `
                                                    <li class="aiomatic-history-item">
                                                        <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                            ${snippet}
                                                        </span>
                                                        <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                            &times;
                                                        </button>
                                                    </li>
                                                `;
                                                responseContainer.prepend(newEntryHtml);
                                            }
                                        }
                                        else
                                        {
                                            console.log('Error in form response: ' + JSON.stringify(response));
                                        }
                                    },
                                    error: function(error) {
                                        console.log('Error in forms: ' + error.responseText);
                                    },
                                });
                            }
                            if(error_generated == '')
                            {
                                var entryText = aiomaticGetContent(dataId);
                                jQuery.ajax({
                                    type: 'POST',
                                    url: aiomatic_completition_ajax_object_forms.ajax_url,
                                    data: {
                                        action: 'aiomatic_record_user_usage',
                                        nonce: aiomatic_completition_ajax_object_forms.persistentnonce,
                                        user_id: aiomatic_completition_ajax_object_forms.user_id,
                                        input_text: input_text,
                                        response_text: response_data,
                                        model: model,
                                        temp: temperature,
                                        vision_file: vision_file,
                                        user_token_cap_per_day: '',
                                        source: 'forms'
                                    },
                                    success: function() 
                                    {
                                    },
                                    error: function(error) {
                                        console.log('Error while saving user data: ' + error.responseText);
                                    },
                                });
                            }
                        }
                    }
                }
                if(assistant_id != '')
                {
                    var run_id = '';
                    var func_calls = 0;
                    var response_data = '';
                    var content_generated = '';
                    var th_id = '';
                    eventGenerator.addEventListener('thread.created', threadCreatedEventHandler);
                    eventGenerator.addEventListener('thread.run.created', threadRunCreatedEventHandler);
                    eventGenerator.addEventListener('thread.run.queued', threadRunQueuedEventHandler);
                    eventGenerator.addEventListener('thread.run.in_progress', threadRunInProgressEventHandler);
                    eventGenerator.addEventListener('thread.run.requires_action', threadRunRequiresActionEventHandler);
                    eventGenerator.addEventListener('thread.run.completed', threadRunCompletedEventHandler);
                    eventGenerator.addEventListener('thread.run.failed', threadRunFailedEventHandler);
                    eventGenerator.addEventListener('thread.run.cancelling', threadRunCancellingEventHandler);
                    eventGenerator.addEventListener('thread.run.cancelled', threadRunCancelledEventHandler);
                    eventGenerator.addEventListener('thread.run.expired', threadRunExpiredEventHandler);
                    eventGenerator.addEventListener('thread.run.step.created', threadRunStepCreatedEventHandler);
                    eventGenerator.addEventListener('thread.run.step.in_progress', threadRunStepInProgressEventHandler);
                    eventGenerator.addEventListener('thread.run.step.delta', threadRunStepDeltaEventHandler);
                    eventGenerator.addEventListener('thread.run.step.completed', threadRunStepCompletedEventHandler);
                    eventGenerator.addEventListener('thread.run.step.failed', threadRunStepFailedEventHandler);
                    eventGenerator.addEventListener('thread.run.step.cancelled', threadRunStepCancelledEventHandler);
                    eventGenerator.addEventListener('thread.run.step.expired', threadRunStepExpiredEventHandler);
                    eventGenerator.addEventListener('thread.message.created', threadMessageCreatedEventHandler);
                    eventGenerator.addEventListener('thread.message.in_progress', threadMessageInProgressEventHandler);
                    eventGenerator.addEventListener('thread.message.delta', threadMessageDeltaEventHandler);
                    eventGenerator.addEventListener('thread.message.incomplete', threadMessageIncompleteEventHandler);
                    eventGenerator.addEventListener('thread.message.completed', threadMessageCompletedEventHandler);
                    eventGenerator.addEventListener('error', function(e) {
                        try {
                            if (e.data && typeof e.data === 'string') {
                                var data = JSON.parse(e.data);
                                console.error('Stream Error:', data);
                                jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">Failed to generate content, try again later.</div>');
                            } else if (e.isTrusted) {
                                console.warn("Browser-generated error event:", e);
                            } else {
                                console.error("Event data is not JSON or is missing:", e.data);
                                jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">An unexpected error occurred. Please try again later.</div>');
                            }
                        } catch (error) {
                            console.log("An error occurred while parsing this JSON: " + error);
                        }
                        aiomaticRmLoading(aiomaticGenerateBtn);
                        aiomatic_generator_working = false;
                        eventGenerator.close();
                        return;
                    });
                    
                    eventGenerator.addEventListener('done', function(e) {
                        console.log('Stream ended');
                        aiomaticRmLoading(aiomaticGenerateBtn);
                        aiomatic_generator_working = false;
                        eventGenerator.close();
                        return;
                    });
                }
                else
                {
                    eventGenerator.onmessage = handleMessageEvent;
                    eventGenerator.addEventListener('content_block_delta', handleContentBlockDelta);
                    eventGenerator.addEventListener('message_stop', handleMessageStopEvent);
                    eventGenerator.addEventListener('completion', handleCompletionEvent);
                    
                    eventGenerator.addEventListener('response.output_text.delta', handleContentBlockDeltaResponses);
                    eventGenerator.addEventListener('response.completed', handleResponsesMessageStopEvent);
                    eventGenerator.addEventListener('response.failed', handleResponsesMessageFailedEvent);
                    eventGenerator.addEventListener('response.incomplete', function (event) {
                        console.log('Incomplete message');
                    });
                    eventGenerator.addEventListener('error', handleMessageErrorEvent);

                    //eventGenerator.addEventListener('response.created', e => console.log('Response created'));
                    //eventGenerator.addEventListener('response.in_progress', e => console.log('Response in progress'));

                    eventGenerator.addEventListener('response.output_item.added', handleOutputItemAdded);
                    //eventGenerator.addEventListener('response.output_item.done', e => console.log('Output item done'));

                    //eventGenerator.addEventListener('response.content_part.added', e => console.log('Content part added'));
                    //eventGenerator.addEventListener('response.content_part.done', e => console.log('Content part done'));

                    //eventGenerator.addEventListener('response.output_text.done', e => console.log('Output text done'));

                    //eventGenerator.addEventListener('response.output_text.annotation.added', e => console.log('Annotation added'));

                    //eventGenerator.addEventListener('response.refusal.delta', e => console.log('Refusal delta'));

                    //eventGenerator.addEventListener('response.reasoning_summary_part.added', e => console.log('Reasoning summary part added'));
                    //eventGenerator.addEventListener('response.reasoning_summary_part.done', e => console.log('Reasoning summary part done'));

                    //eventGenerator.addEventListener('response.reasoning_summary_text.delta', e => console.log('Reasoning summary delta'));
                    //eventGenerator.addEventListener('response.reasoning_summary_text.done', e => console.log('Reasoning summary done'));
                    eventGenerator.addEventListener('response.refusal.done', handleRefusalDone);

                    eventGenerator.addEventListener('response.function_call_arguments.delta', handleFunctionCallArgumentsDelta);
                    eventGenerator.addEventListener('response.function_call_arguments.done', handleFunctionCallArgumentsDone);

                    eventGenerator.addEventListener('response.file_search_call.in_progress', e => console.log('File search in progress'));
                    eventGenerator.addEventListener('response.file_search_call.searching', e => console.log('File search searching'));
                    eventGenerator.addEventListener('response.file_search_call.completed', e => console.log('File search completed'));

                    eventGenerator.addEventListener('response.web_search_call.in_progress', e => console.log('Web search in progress'));
                    eventGenerator.addEventListener('response.web_search_call.searching', e => console.log('Web search searching'));
                    eventGenerator.addEventListener('response.web_search_call.completed', e => console.log('Web search completed'));
                }
                function handleFunctionCallArgumentsDelta(e)
                {

                }
                function handleFunctionCallArgumentsDone(e)
                {

                }
                function handleResponsesMessageStopEvent(e) 
                {
                    const responseContainer = jQuery(`#aiomatic-form-history-` + formid + ` .aiomatic-history-list`);
                    if (responseContainer) 
                    {
                        var entryText = aiomaticGetContent(dataId);
                        jQuery.ajax({
                            type: 'POST',
                            url: aiomatic_completition_ajax_object_forms.ajax_url,
                            data: {
                                action: 'aiomatic_add_form_history_entry',
                                response_text: entryText,
                                nonce: aiomatic_completition_ajax_object_forms.nonce,
                                form_id: formid,
                            },
                            success: function(response) {
                                if(response.data.status == 'success')
                                {
                                    const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                    const index = response.data.unique_index; 
                                    const formId = formid;
                                    if(index != '')
                                    {
                                        const newEntryHtml = `
                                            <li class="aiomatic-history-item">
                                                <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                    ${snippet}
                                                </span>
                                                <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                    &times;
                                                </button>
                                            </li>
                                        `;
                                        responseContainer.prepend(newEntryHtml);
                                    }
                                }
                                else
                                {
                                    console.log('Error in form response: ' + JSON.stringify(response));
                                }
                            },
                            error: function(error) {
                                console.log('Error in forms: ' + error.responseText);
                            },
                        });
                    }
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                    return;
                }
                function handleMessageStopEvent(e) 
                {
                    const responseContainer = jQuery(`#aiomatic-form-history-` + aiomatic_completition_ajax_object_forms.formid + ` .aiomatic-history-list`);
                    if (responseContainer) 
                    {
                        var entryText = aiomaticGetContent(dataId);
                        jQuery.ajax({
                            type: 'POST',
                            url: aiomatic_completition_ajax_object_forms.ajax_url,
                            data: {
                                action: 'aiomatic_add_form_history_entry',
                                response_text: entryText,
                                nonce: aiomatic_completition_ajax_object_forms.nonce,
                                form_id: formid,
                            },
                            success: function(response) {
                                if(response.data.status == 'success')
                                {
                                    const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                    const index = response.data.unique_index; 
                                    const formId = formid;
                                    if(index != '')
                                    {
                                        const newEntryHtml = `
                                            <li class="aiomatic-history-item">
                                                <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                    ${snippet}
                                                </span>
                                                <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                    &times;
                                                </button>
                                            </li>
                                        `;
                                        responseContainer.prepend(newEntryHtml);
                                    }
                                }
                                else
                                {
                                    console.log('Error in form response: ' + JSON.stringify(response));
                                }
                            },
                            error: function(error) {
                                console.log('Error in forms: ' + error.responseText);
                            },
                        });
                    }
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                    return;
                }
                function threadCreatedEventHandler(e) {
                }
                function threadRunCreatedEventHandler(e) {
                    try {
                        var data = JSON.parse(e.data);
                        run_id = data.id;
                        th_id = data.thread_id;
                    } catch (error) {
                        console.log("An error occurred while parsing JSON: " + error);
                    }
                }
                function threadRunQueuedEventHandler(e) {
                }
                function threadRunInProgressEventHandler(e) {
                }
                function threadRunRequiresActionEventHandler(e) {
                }
                function threadRunCompletedEventHandler(e) {
                    eventGenerator.close();
                    aiomatic_generator_working = false;
                    jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    const responseContainer = jQuery(`#aiomatic-form-history-` + aiomatic_completition_ajax_object_forms.formid + ` .aiomatic-history-list`);
                    if (responseContainer) 
                    {
                        var entryText = aiomaticGetContent(dataId);
                        jQuery.ajax({
                            type: 'POST',
                            url: aiomatic_completition_ajax_object_forms.ajax_url,
                            data: {
                                action: 'aiomatic_add_form_history_entry',
                                response_text: entryText,
                                nonce: aiomatic_completition_ajax_object_forms.nonce,
                                form_id: formid,
                            },
                            success: function(response) {
                                if(response.data.status == 'success')
                                {
                                    const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                    const index = response.data.unique_index; 
                                    const formId = formid;
                                    if(index != '')
                                    {
                                        const newEntryHtml = `
                                            <li class="aiomatic-history-item">
                                                <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                    ${snippet}
                                                </span>
                                                <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                    &times;
                                                </button>
                                            </li>
                                        `;
                                        responseContainer.prepend(newEntryHtml);
                                    }
                                }
                                else
                                {
                                    console.log('Error in form response: ' + JSON.stringify(response));
                                }
                            },
                            error: function(error) {
                                console.log('Error in forms: ' + error.responseText);
                            },
                        });
                    }
                    if(error_generated == '')
                    {
                        jQuery.ajax({
                            type: 'POST',
                            url: aiomatic_completition_ajax_object_forms.ajax_url,
                            data: {
                                action: 'aiomatic_record_user_usage',
                                nonce: aiomatic_completition_ajax_object_forms.persistentnonce,
                                user_id: aiomatic_completition_ajax_object_forms.user_id,
                                input_text: input_text,
                                response_text: response_data,
                                model: model,
                                temp: temperature,
                                vision_file: vision_file,
                                user_token_cap_per_day: '',
                                source: 'forms'
                            },
                            success: function() 
                            {
                            },
                            error: function(error) {
                                console.log('Error while saving user data: ' + error.responseText);
                            },
                        });
                    }
                }
                function threadRunCancellingEventHandler(e) {
                }
                function threadRunCancelledEventHandler(e) {
                }
                function threadRunExpiredEventHandler(e) {
                }
                function threadRunStepCreatedEventHandler(e) {
                }
                function threadRunStepInProgressEventHandler(e) {
                }
                function threadRunStepDeltaEventHandler(e) {
                    try {
                        var data = JSON.parse(e.data);
                        var xarr = {'tool_calls': []};
                        xarr.tool_calls.push(data.delta.step_details.tool_calls[0]);
                        aiomatic_mergeDeep(func_call, xarr);
                        func_calls++;
                    } catch (error) {
                        console.log("An error occurred while parsing step JSON: " + error);
                    }
                }
                function threadRunStepCompletedEventHandler(e) {
                }
                function threadRunStepCancelledEventHandler(e) {
                }
                function threadRunStepExpiredEventHandler(e) {
                }
                function threadMessageCreatedEventHandler(e) {
                }
                function threadMessageInProgressEventHandler(e) {
                }
                function threadMessageDeltaEventHandler(e) {
                    try {
                        var data = JSON.parse(e.data);
                        if(typeof data.delta.content[0].text.value !== 'undefined')
                        {
                            content_generated = data.delta.content[0].text.value;
                            response_data += aiomatic_nl2br(content_generated);
                            if (aiomatic_completition_ajax_object_forms.katex_parse == 'on') {
                                response_data = processKatexMarkdown(response_data);
                            }
                            if (aiomatic_completition_ajax_object_forms.markdown_parse == 'on') {
                                response_data = aiomatic_parseMarkdown(response_data);
                            }
                            var htmlfound = aiomaticSetContent(dataId, response_data);
                            if(htmlfound === true) {
                                if (aiomatic_completition_ajax_object_forms.katex_parse == 'on' && typeof renderMathInElement === 'function') {
                                    var katexContainer = document.getElementById('aiomatic_html_output' + dataId);
                                    renderMathInElement(katexContainer, {
                                        delimiters: [
                                            
                                            { left: '\\(', right: '\\)', display: false },
                                            { left: '\\[', right: '\\]', display: true }
                                        ],
                                        throwOnError: false
                                    });
                                }
                            }
                        }
                        else
                        {
                            console.log('Generated content not found: ' + data);
                        }
                    } catch (error) {
                        console.log("An error occurred while parsing delta JSON: " + error);
                    }
                    
                }
                function threadMessageIncompleteEventHandler(e) {
                    eventGenerator.close();
                }
                function threadRunFailedEventHandler(e) {
                    console.warn(e);
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                    return;
                }
                function threadRunStepFailedEventHandler(e) {
                    console.warn(e);
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                    return;
                }
                function threadMessageCompletedEventHandler(e) 
                {
                }
                function handleCompletionEvent(e) 
                {
                    if(model_type == 'claude')
                    {
                        var aiomatic_newline_before = false;
                        var aiomatic_response_events = 0;
                        var aiomatic_limitLines = 1;
                        var resultData = null;
                        var hasFinishReason = false;
                        if(e.data == '[DONE]')
                        {
                            hasFinishReason = true;
                        }
                        else
                        {
                            try 
                            {
                                resultData = JSON.parse(e.data);
                            } 
                            catch (e) 
                            {
                                console.warn(e);
                                aiomaticRmLoading(aiomaticGenerateBtn);
                                aiomatic_generator_working = false;
                                eventGenerator.close();
                                return;
                            }
                            hasFinishReason = resultData &&
                            (resultData.finish_reason === "stop" ||
                            resultData.finish_reason === "length");
                            if(resultData.stop_reason == 'stop_sequence' || resultData.stop_reason == 'max_tokens')
                            {
                                hasFinishReason = true;
                            }
                        }
                        var content_generated = '';
                        if(hasFinishReason){
                            count_line += 1;
                            aiomatic_response_events = 0;
                        }
                        else
                        {
                            if(resultData !== null)
                            {
                                var result = resultData;
                            }
                            else
                            {
                                var result = null;
                                try {
                                    result = JSON.parse(e.data);
                                } 
                                catch (e) 
                                {
                                    console.warn(e);
                                    aiomaticRmLoading(aiomaticGenerateBtn);
                                    aiomatic_generator_working = false;
                                    eventGenerator.close();
                                    return;
                                };
                            }
                            if(result.error !== undefined){
                                error_generated = result.error[0].message;
                                if(error_generated === undefined)
                                {
                                    error_generated = result.error.message;
                                }
                                if(error_generated === undefined)
                                {
                                    error_generated = result.error;
                                }
                                console.log('Error while processing request(1): ' + error_generated);
                                jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text" role="status">' + error_generated + '</div>');
                                aiomaticRmLoading(aiomaticGenerateBtn);
                                aiomatic_generator_working = false;
                                eventGenerator.close();
                                return;
                            }
                            else
                            {
                                if(result.completion !== undefined)
                                {
                                    content_generated = result.completion;
                                }
                                else if(result.content[0].text !== undefined)
                                {
                                    content_generated = result.content[0].text;
                                }
                                else
                                {
                                    content_generated = '';
                                }
                            }
                            response_data += aiomatic_nl2br(content_generated);
                            if((content_generated === '\n' || content_generated === ' \n' || content_generated === '.\n' || content_generated === '\n\n' || content_generated === '.\n\n' || content_generated === '"\n') && aiomatic_response_events > 0){
                                if(!aiomatic_newline_before) {
                                    aiomatic_newline_before = true;
                                }
                            }
                            else if(content_generated === '\n' && aiomatic_response_events === 0){

                            }
                            else{
                                aiomatic_newline_before = false;
                                aiomatic_response_events += 1;
                                if (aiomatic_completition_ajax_object_forms.katex_parse == 'on') {
                                    response_data = processKatexMarkdown(response_data);
                                }
                                if (aiomatic_completition_ajax_object_forms.markdown_parse == 'on') {
                                    response_data = aiomatic_parseMarkdown(response_data);
                                }
                                var htmlfound = aiomaticSetContent(dataId, response_data);
                                if(htmlfound === true) {
                                    if (aiomatic_completition_ajax_object_forms.katex_parse == 'on' && typeof renderMathInElement === 'function') {
                                        var katexContainer = document.getElementById('aiomatic_html_output' + dataId);
                                        renderMathInElement(katexContainer, {
                                            delimiters: [
                                                
                                                { left: '\\(', right: '\\)', display: false },
                                                { left: '\\[', right: '\\]', display: true }
                                            ],
                                            throwOnError: false
                                        });
                                    }
                                }
                            }
                        }
                        if(count_line >= aiomatic_limitLines)
                        {
                            eventGenerator.close();
                            aiomatic_generator_working = false;
                            jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                            aiomaticRmLoading(aiomaticGenerateBtn);
                            
                            const responseContainer = jQuery(`#aiomatic-form-history-` + aiomatic_completition_ajax_object_forms.formid + ` .aiomatic-history-list`);
                            if (responseContainer) 
                            {
                                var entryText = aiomaticGetContent(dataId);
                                jQuery.ajax({
                                    type: 'POST',
                                    url: aiomatic_completition_ajax_object_forms.ajax_url,
                                    data: {
                                        action: 'aiomatic_add_form_history_entry',
                                        response_text: entryText,
                                        nonce: aiomatic_completition_ajax_object_forms.nonce,
                                        form_id: formid,
                                    },
                                    success: function(response) {
                                        if(response.data.status == 'success')
                                        {
                                            const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                            const index = response.data.unique_index; 
                                            const formId = formid;
                                            if(index != '')
                                            {
                                                const newEntryHtml = `
                                                    <li class="aiomatic-history-item">
                                                        <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                            ${snippet}
                                                        </span>
                                                        <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                            &times;
                                                        </button>
                                                    </li>
                                                `;
                                                responseContainer.prepend(newEntryHtml);
                                            }
                                        }
                                        else
                                        {
                                            console.log('Error in form response: ' + JSON.stringify(response));
                                        }
                                    },
                                    error: function(error) {
                                        console.log('Error in forms: ' + error.responseText);
                                    },
                                });
                            }
                            if(error_generated == '')
                            {
                                var entryText = aiomaticGetContent(dataId);
                                jQuery.ajax({
                                    type: 'POST',
                                    url: aiomatic_completition_ajax_object_forms.ajax_url,
                                    data: {
                                        action: 'aiomatic_record_user_usage',
                                        nonce: aiomatic_completition_ajax_object_forms.persistentnonce,
                                        user_id: aiomatic_completition_ajax_object_forms.user_id,
                                        input_text: input_text,
                                        response_text: response_data,
                                        model: model,
                                        temp: temperature,
                                        vision_file: vision_file,
                                        user_token_cap_per_day: '',
                                        source: 'forms'
                                    },
                                    success: function() 
                                    {
                                    },
                                    error: function(error) {
                                        console.log('Error while saving user data: ' + error.responseText);
                                    },
                                });
                            }
                        }
                    }
                }
                function handleMessageEvent(e)
                {
                    var hasFinishReason = false;
                    if(model_type != 'claude')
                    {
                        var aiomatic_newline_before = false;
                        var aiomatic_response_events = 0;
                        var aiomatic_limitLines = 1;
                        var resultData = null;
                        if(e.data == '[DONE]')
                        {
                            hasFinishReason = true;
                        }
                        else
                        {
                            if(model_type != 'google')
                            {
                                try 
                                {
                                    resultData = JSON.parse(e.data);
                                } 
                                catch (e) 
                                {
                                    console.warn(e);
                                    aiomaticRmLoading(aiomaticGenerateBtn);
                                    aiomatic_generator_working = false;
                                    eventGenerator.close();
                                    return;
                                }
                                hasFinishReason = resultData.choices &&
                                resultData.choices[0] &&
                                (resultData.choices[0].finish_reason === "stop" ||
                                resultData.choices[0].finish_reason === "length");
                            }
                        }
                        if(model_type != 'google')
                        {
                            var content_generated = '';
                            if(hasFinishReason){
                                count_line += 1;
                                aiomatic_response_events = 0;
                            }
                            else
                            {
                                var result = null;
                                try {
                                    result = JSON.parse(e.data);
                                } 
                                catch (e) 
                                {
                                    console.warn(e);
                                    aiomaticRmLoading(aiomaticGenerateBtn);
                                    aiomatic_generator_working = false;
                                    eventGenerator.close();
                                    return;
                                };
                                if(result.error !== undefined){
                                    error_generated = result.error[0].message;
                                    if(error_generated === undefined)
                                    {
                                        error_generated = result.error.message;
                                    }
                                    if(error_generated === undefined)
                                    {
                                        error_generated = result.error;
                                    }
                                    console.log('Error while processing request(2): ' + error_generated);
                                    jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                    jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text" role="status">' + error_generated + '</div>');
                                    aiomaticRmLoading(aiomaticGenerateBtn);
                                    aiomatic_generator_working = false;
                                    eventGenerator.close();
                                    return;
                                }
                                else
                                {
                                    if(model_type == 'huggingface')
                                    {
                                        if (result.generated_text)
                                        {
                                            var hasFinishReason = true;
                                            count_line += 1;
                                            aiomatic_response_events = 0;
                                        }
                                        else
                                        {
                                            content_generated = result.token.text;
                                        }
                                    }
                                    else
                                    {
                                        content_generated = result.choices[0].delta !== undefined ? (result.choices[0].delta.content !== undefined ? result.choices[0].delta.content : '') : result.choices[0].text;
                                    }
                                }
                                response_data += aiomatic_nl2br(content_generated);
                                if((content_generated === '\n' || content_generated === ' \n' || content_generated === '.\n' || content_generated === '\n\n' || content_generated === '.\n\n' || content_generated === '"\n') && aiomatic_response_events > 0){
                                    if(!aiomatic_newline_before) {
                                        aiomatic_newline_before = true;
                                    }
                                }
                                else if(content_generated === '\n' && aiomatic_response_events === 0){
                                    
                                }
                                else if(response_data == '')
                                {
                                    aiomatic_newline_before = false;
                                    aiomatic_response_events += 1;
                                }
                                else{
                                    aiomatic_newline_before = false;
                                    aiomatic_response_events += 1;
                                    if (aiomatic_completition_ajax_object_forms.katex_parse == 'on') {
                                        response_data = processKatexMarkdown(response_data);
                                    }
                                    if (aiomatic_completition_ajax_object_forms.markdown_parse == 'on') {
                                        response_data = aiomatic_parseMarkdown(response_data);
                                    }
                                    var htmlfound = aiomaticSetContent(dataId, response_data);
                                    if(htmlfound === true) {
                                        if (aiomatic_completition_ajax_object_forms.katex_parse == 'on' && typeof renderMathInElement === 'function') {
                                            var katexContainer = document.getElementById('aiomatic_html_output' + dataId);
                                            renderMathInElement(katexContainer, {
                                                delimiters: [
                                                    
                                                    { left: '\\(', right: '\\)', display: false },
                                                    { left: '\\[', right: '\\]', display: true }
                                                ],
                                                throwOnError: false
                                            });
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if(hasFinishReason){
                                count_line += 1;
                                aiomatic_response_events = 0;
                            }
                            if(e.data == '[ERROR]')
                            {
                                error_generated = 'Failed to get form response!';
                                console.log('Error while processing request: ' + error_generated);
                                jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text" role="status">' + error_generated + '</div>');
                                count_line += 1;
                            }
                            else
                            {
                                if(e.data !== '[DONE]')
                                {
                                    response_data += aiomatic_nl2br(e.data);
                                    aiomatic_response_events += 1;
                                    if (aiomatic_completition_ajax_object_forms.katex_parse == 'on') {
                                        response_data = processKatexMarkdown(response_data);
                                    }
                                    if (aiomatic_completition_ajax_object_forms.markdown_parse == 'on') {
                                        response_data = aiomatic_parseMarkdown(response_data);
                                    }
                                    var htmlfound = aiomaticSetContent(dataId, response_data);
                                    if(htmlfound === true) {
                                        if (aiomatic_completition_ajax_object_forms.katex_parse == 'on' && typeof renderMathInElement === 'function') {
                                            var katexContainer = document.getElementById('aiomatic_html_output' + dataId);
                                            renderMathInElement(katexContainer, {
                                                delimiters: [
                                                    
                                                    { left: '\\(', right: '\\)', display: false },
                                                    { left: '\\[', right: '\\]', display: true }
                                                ],
                                                throwOnError: false
                                            });
                                        }
                                    }
                                }
                            }
                        }
                        if(count_line >= aiomatic_limitLines)
                        {
                            eventGenerator.close();
                            aiomatic_generator_working = false;
                            jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                            aiomaticRmLoading(aiomaticGenerateBtn);
                            
                            const responseContainer = jQuery(`#aiomatic-form-history-` + aiomatic_completition_ajax_object_forms.formid + ` .aiomatic-history-list`);
                            if (responseContainer) 
                            {
                                var entryText = aiomaticGetContent(dataId);
                                jQuery.ajax({
                                    type: 'POST',
                                    url: aiomatic_completition_ajax_object_forms.ajax_url,
                                    data: {
                                        action: 'aiomatic_add_form_history_entry',
                                        response_text: entryText,
                                        nonce: aiomatic_completition_ajax_object_forms.nonce,
                                        form_id: formid,
                                    },
                                    success: function(response) {
                                        if(response.data.status == 'success')
                                        {
                                            const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                            const index = response.data.unique_index; 
                                            const formId = formid;
                                            if(index != '')
                                            {
                                                const newEntryHtml = `
                                                    <li class="aiomatic-history-item">
                                                        <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                            ${snippet}
                                                        </span>
                                                        <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                            &times;
                                                        </button>
                                                    </li>
                                                `;
                                                responseContainer.prepend(newEntryHtml);
                                            }
                                        }
                                        else
                                        {
                                            console.log('Error in form response: ' + JSON.stringify(response));
                                        }
                                    },
                                    error: function(error) {
                                        console.log('Error in forms: ' + error.responseText);
                                    },
                                });
                            }
                            if(error_generated == '')
                            {
                                var entryText = aiomaticGetContent(dataId);
                                jQuery.ajax({
                                    type: 'POST',
                                    url: aiomatic_completition_ajax_object_forms.ajax_url,
                                    data: {
                                        action: 'aiomatic_record_user_usage',
                                        nonce: aiomatic_completition_ajax_object_forms.persistentnonce,
                                        user_id: aiomatic_completition_ajax_object_forms.user_id,
                                        input_text: input_text,
                                        response_text: response_data,
                                        model: model,
                                        temp: temperature,
                                        vision_file: vision_file,
                                        user_token_cap_per_day: '',
                                        source: 'forms'
                                    },
                                    success: function() 
                                    {
                                    },
                                    error: function(error) {
                                        console.log('Error while saving user data: ' + error.responseText);
                                    },
                                });
                            }
                        }
                    }
                };
                eventGenerator.onerror = handleErrorEvent;
                function handleErrorEvent(e) 
                {
                    console.log('Halting execution, EventGenerator error: ' + JSON.stringify(e));
                    jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                    jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text" role="status">Failed to process response, please try again later.</div>');
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    aiomatic_generator_working = false;
                    eventGenerator.close();
                    return;
                };
            }
            else
            {
                if(aiomaticType == 'omniblock-form')
                {
                    prompt = JSON.stringify(allFormData);
                }
                if (typeof wait_omniblock !== 'undefined' && wait_omniblock === 'no') {
                    // Fire-and-forget mode — no need to wait for response
                    jQuery.ajax({
                        type: 'POST',
                        url: aiomatic_completition_ajax_object_forms.ajax_url,
                        data: {
                            action: 'aiomatic_form_submit',
                            input_text: prompt,
                            nonce: aiomatic_completition_ajax_object_forms.nonce,
                            model: model,
                            assistant_id: assistant_id,
                            temp: temperature,
                            top_p: top_p,
                            presence: presence_penalty,
                            aiomaticType: aiomaticType,
                            frequency: frequency_penalty,
                            user_token_cap_per_day: '',
                            user_id: aiomatic_completition_ajax_object_forms.user_id,
                            form_id: formid,
                            isForm: '1'
                        }
                    });
                    aiomaticRmLoading(aiomaticGenerateBtn);
                    alert('Form submitted successfully!');
                }
                else
                {
                    jQuery.ajax({
                        type: 'POST',
                        url: aiomatic_completition_ajax_object_forms.ajax_url,
                        data: {
                            action: 'aiomatic_form_submit',
                            input_text: prompt,
                            nonce: aiomatic_completition_ajax_object_forms.nonce,
                            model: model,
                            assistant_id: assistant_id,
                            temp: temperature,
                            top_p: top_p,
                            presence: presence_penalty,
                            aiomaticType: aiomaticType,
                            frequency: frequency_penalty,
                            user_token_cap_per_day: '',
                            user_id: aiomatic_completition_ajax_object_forms.user_id,
                            form_id: formid,
                            isForm: '1'
                        },
                        success: function(response) {
                            if(typeof response === 'string' || response instanceof String)
                            {
                                try {
                                    var responset = JSON.parse(response);
                                    response = responset;
                                } catch (error) {
                                    console.error("An error occurred while parsing the JSON: " + error + ' Json: ' + response);
                                }
                            }
                            if(response.status == 'success')
                            {
                                if(response.data == '')
                                {
                                    jQuery('#openai-response' + dataId).html('<div class="text-primary" role="status">AI considers this as the end of the text. Please try using a different text input.</div>');
                                }
                                else
                                {
                                    jQuery('#openai-response' + dataId).html('');
                                    if(aiomaticType == 'text' || aiomaticType == 'omniblock-form')
                                    {
                                        const responseContainer = jQuery(`#aiomatic-form-history-` + formid + ` .aiomatic-history-list`);
                                        if (responseContainer) {
                                            const entryText = response.data;
                                            const snippet = entryText.split(' ').slice(0, 10).join(' ') + '...'; 
                                            const index = response.unique_index; 
                                            const formId = formid;
                                            if(index != '')
                                            {
                                                const newEntryHtml = `
                                                    <li class="aiomatic-history-item">
                                                        <span class="aiomatic-history-snippet" data-index="${index}" data-full-text="${entryText}" data-form-id="${formId}">
                                                            ${snippet}
                                                        </span>
                                                        <button class="aiomatic-delete-entry button-link-delete" title="Delete entry" data-index="${index}" data-form-id="${formId}">
                                                            &times;
                                                        </button>
                                                    </li>
                                                `;
                                                responseContainer.prepend(newEntryHtml);
                                            }
                                        }
                                        if (aiomatic_completition_ajax_object_forms.katex_parse == 'on') {
                                            response.data = processKatexMarkdown(response.data);
                                        }
                                        if (aiomatic_completition_ajax_object_forms.markdown_parse == 'on') {
                                            response.data = aiomatic_parseMarkdown(response.data);
                                        }
                                        var htmlfound = aiomaticSetContent(dataId, response.data);
                                        if(htmlfound === true) {
                                            if (aiomatic_completition_ajax_object_forms.katex_parse == 'on' && typeof renderMathInElement === 'function') {
                                                var katexContainer = document.getElementById('aiomatic_html_output' + dataId);
                                                renderMathInElement(katexContainer, {
                                                    delimiters: [
                                                        
                                                        { left: '\\(', right: '\\)', display: false },
                                                        { left: '\\[', right: '\\]', display: true }
                                                    ],
                                                    throwOnError: false
                                                });
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if(aiomaticType == 'image' || aiomaticType == 'image-new' || aiomaticType == 'gpt-image-1' || aiomaticType == 'image-mid' || aiomaticType == 'image-rep' || aiomaticType == 'image-ide')
                                        {
                                            downloadButton.attr("href", aiomatichtmlDecode(response.data));
                                            downloadButton.show();
                                            jQuery("#aiomatic_form_response" + dataId).attr("src", aiomatichtmlDecode(response.data)).fadeIn();
                                        }
                                        else
                                        {
                                            downloadButton.attr("href", "data:image/gif;base64," + aiomatichtmlDecode(response.data));
                                            downloadButton.show();
                                            jQuery("#aiomatic_form_response" + dataId).attr("src", "data:image/gif;base64," + aiomatichtmlDecode(response.data)).fadeIn();
                                        }
                                    }
                                }
                            }
                            else
                            {
                                if(typeof response.msg !== 'undefined')
                                {
                                    jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">' + response.msg + '</div>');
                                    console.log('Error in processing: ' + response.msg);
                                    jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                }
                                else
                                {
                                    console.log('Error: ' + response);
                                    jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">Processing failed, please try again</div>');
                                    jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                                }
                            }
                            aiomaticRmLoading(aiomaticGenerateBtn);
                        },
                        error: function(error) {
                            console.log('Error: ' + error.responseText);
                            jQuery("#aiomatic_form_response" + dataId).attr("src", '').fadeIn();
                            // Clear the response container
                            jQuery('#openai-response' + dataId).html('<div class="text-primary highlight-text-fail" role="status">Failed to generate content, try again later.</div>');
                            aiomaticRmLoading(aiomaticGenerateBtn);
                        },
                    });
                }
            }
        }
    });
});