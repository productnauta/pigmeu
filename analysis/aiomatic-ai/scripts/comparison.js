"use strict";
jQuery(document).ready(function ($) 
{
    function aiomaticLoading(btn) {
        btn.attr('disabled', 'disabled');
        btn.after('<span class="loading-indicator"></span>');
    }
    function aiomaticRmLoading(btn) {
        btn.removeAttr('disabled');
        btn.next('.loading-indicator').remove();
    }
    $(document).on('click', '.aiomatic-comparison-cancel', function (e){
        let id = $(e.currentTarget).attr('data-id');
        let item = $(e.currentTarget).closest('.aiomatic-comparison-item');
        window['aiomatic_comparison_'+id].abort();
        let btn = item.find('.aiomatic-comparison-submit');
        aiomaticRmLoading(btn);
        item.find('.aiomatic-comparison-space').hide();
        $(e.currentTarget).hide();
        aiomaticCompareResult();
    });
    $(document).on('click','.aiomatic-comparison-close', function (e){
        $(e.currentTarget).closest('.aiomatic-comparison-item').remove();
        aiomaticCompareResult();
    });
    function aiomaticCompareResult(){
        let max_tokens = 0;
        let max_words = 0;
        let min_cost = 0;
        let min_duration = 0;
        let el_tokens = false;
        let el_words = false;
        let el_cost =false;
        let el_duration = false;
        $('.aiomatic-comparison-result').each(function (idx, item){
            let duration = parseFloat($(item).attr('data-duration'));
            let words = parseFloat($(item).attr('data-words'));
            let cost = parseFloat($(item).attr('data-cost'))
            let tokens = parseFloat($(item).attr('data-tokens'));
            if(tokens > max_tokens){
                max_tokens = tokens;
                el_tokens = item;
            }
            if((min_cost > 0 && cost < min_cost) || min_cost === 0){
                min_cost = cost;
                el_cost = item;
            }
            if((min_duration > 0 && duration < min_duration) || min_duration === 0){
                min_duration = duration;
                el_duration = item;
            }
            if((max_words > 0 && words > max_words) || max_words === 0){
                max_words = words;
                el_words = item;
            }
        });
        $('.aiomatic-comparison-result').each(function (idx, item){
            $(item).find('.aiomatic-comparison-cost').removeClass('aiomatic-good');
            $(item).find('.aiomatic-comparison-words').removeClass('aiomatic-good');
            $(item).find('.aiomatic-comparison-tokens').removeClass('aiomatic-good');
            $(item).find('.aiomatic-comparison-duration').removeClass('aiomatic-good');
            $(item).find('.aiomatic-comparison-tokens').addClass('aiomatic-not-good');
            $(item).find('.aiomatic-comparison-words').addClass('aiomatic-not-good');
            $(item).find('.aiomatic-comparison-duration').addClass('aiomatic-not-good');
            $(item).find('.aiomatic-comparison-cost').addClass('aiomatic-not-good');
        });
        if(el_tokens){
            $(el_tokens).find('.aiomatic-comparison-tokens').removeClass('aiomatic-not-good');
            $(el_tokens).find('.aiomatic-comparison-tokens').addClass('aiomatic-good');
        }
        if(el_words){
            $(el_words).find('.aiomatic-comparison-words').removeClass('aiomatic-not-good');
            $(el_words).find('.aiomatic-comparison-words').addClass('aiomatic-good');
        }
        if(el_cost){
            $(el_cost).find('.aiomatic-comparison-cost').removeClass('aiomatic-not-good');
            $(el_cost).find('.aiomatic-comparison-cost').addClass('aiomatic-good');
        }
        if(el_duration){
            $(el_duration).find('.aiomatic-comparison-duration').removeClass('aiomatic-not-good');
            $(el_duration).find('.aiomatic-comparison-duration').addClass('aiomatic-good');
        }

    }
    $(document).on('change','.aiomatic-comparison-select-prompt', function (e){
        let sel = $(e.currentTarget);
        let value = sel.val();
        let form = sel.closest('.aiomatic-comparison-form');
        let textarea = form.find('textarea[name=prompt]');
        textarea.val(value);
        textarea.trigger('input');
    });
    $(document).on('input', 'textarea[name="prompt"]', function () {
        const maxLength = 128000;
        const length = $(this).val().length;
        $(this).next('.character-counter').text(length + "/" + maxLength);
        if (length > maxLength) {
            $(this).next('.character-counter').css("color", "red");
        } else {
            $(this).next('.character-counter').css("color", "inherit");
        }
    });

    $(document).on('click', '.advanced-settings-toggle', function () {
        $(this).next('.advanced-settings').slideToggle();
        $(this).text($(this).text() === 'Show Advanced Settings' ? 'Hide Advanced Settings' : 'Show Advanced Settings');
    });

    for (let i = 0; i < 2; i++) {
        let html = $('.aiomatic-comparison-default').html();
        html = html.replace('[ID]', i);
        $('.comparison_tool').append(html);
        if(i == 1)
        {
            $('.comparison_tool').append('<div class="aiomatic-comparison-add"><span class="dashicons dashicons-plus-alt"></span>' + aiomatic_completition_ajax_object_comparison.add_comparison + '</div>' );
        }
    }

    $(document).on('click', '.aiomatic-comparison-add', function () {
        $(this).before($('.aiomatic-comparison-default').html());
    });

    $(document).on('submit', '.aiomatic-comparison-form', function (e) {
        e.preventDefault();
        const btn = $(this).find('.aiomatic-comparison-submit');
        aiomaticLoading(btn);
        aiomaticRmLoading(btn);
    });
    
    $(document).on('submit','.aiomatic-comparison-form', function (e){
        e.preventDefault();
        let startTime = new Date();
        let form = $(e.currentTarget);
        let item = form.closest('.aiomatic-comparison-item');
        item.removeClass('aiomatic-comparison-result')
        let temperature = parseFloat(form.find('input[name=temperature]').val());
        let top_p = parseFloat(form.find('input[name=top_p]').val());
        let frequency_penalty = parseFloat(form.find('input[name=frequency_penalty]').val());
        let presence_penalty = parseFloat(form.find('input[name=presence_penalty]').val());
        let prompt = form.find('textarea[name=prompt]').val();
        let model;

        let provider = $('.aiomatic-mb-10').data('provider'); 

        if (provider === 'Azure') {
            model = form.find('input[name=model]').val();
        } else {
            model = form.find('select[name=model]').val();
        }

        let has_error = false;
        let btn = form.find('.aiomatic-comparison-submit');
        if(prompt === ''){
            has_error = 'Please enter Prompt';
        }

        if(!has_error && (temperature > 2 || temperature < 0)){
            has_error = aiomatic_completition_ajax_object_comparison.valid_temp;
        }
        if(!has_error && (top_p > 1 || top_p < 0)){
            has_error = aiomatic_completition_ajax_object_comparison.valid_topp;
        }
        if(!has_error && (frequency_penalty > 2 || frequency_penalty < 0)){
            has_error = aiomatic_completition_ajax_object_comparison.valid_frequency;
        }
        if(!has_error && (presence_penalty > 2 || presence_penalty < -2)){
            has_error = aiomatic_completition_ajax_object_comparison.valid_presense;
        }
        if(!has_error){
            let randomID = Math.ceil(Math.random() * 10000);
            item.find('.aiomatic-comparison-cancel').show();
            item.find('.aiomatic-comparison-space').show();
            item.find('.aiomatic-comparison-cancel').attr('data-id', randomID);
            $('.aiomatic-comparison-item').each(function (idx, itemx){
                $(itemx).find('.aiomatic-comparison-cost').removeClass('aiomatic-good');
                $(itemx).find('.aiomatic-comparison-words').removeClass('aiomatic-good');
                $(itemx).find('.aiomatic-comparison-tokens').removeClass('aiomatic-good');
                $(itemx).find('.aiomatic-comparison-duration').removeClass('aiomatic-good');
                $(itemx).find('.aiomatic-comparison-tokens').removeClass('aiomatic-not-good');
                $(itemx).find('.aiomatic-comparison-words').removeClass('aiomatic-not-good');
                $(itemx).find('.aiomatic-comparison-duration').removeClass('aiomatic-not-good');
                $(itemx).find('.aiomatic-comparison-cost').removeClass('aiomatic-not-good');
            });
            $(item).find('.aiomatic-comparison-cost').empty();
            $(item).find('.aiomatic-comparison-words').empty();
            $(item).find('.aiomatic-comparison-tokens').empty();
            $(item).find('.aiomatic-comparison-duration').empty();
            window['aiomatic_comparison_' + randomID] = $.ajax({
                url: aiomatic_completition_ajax_object_comparison.ajax_url,
                data: form.serialize(),
                dataType: 'JSON',
                type:'POST',
                beforeSend: function (res){
                    aiomaticLoading(btn)
                },
                success: function (res){
                    aiomaticRmLoading(btn);
                    item.find('.aiomatic-comparison-cancel').hide();
                    item.find('.aiomatic-comparison-space').hide();
                    if(res.status === 'success'){
                        let endTime = new Date();
                        let timeDiff = (endTime - startTime) / 1000;
                        let text = res.text;
                        text = text.replace(/\\/g,'');
                        form.find('.aiomatic-comparison-output').val(text);
                        form.find('.aiomatic-comparison-tokens').html(res.tokens);
                        form.find('.aiomatic-comparison-cost').html('$' + parseFloat(res.cost).toFixed(5));
                        form.find('.aiomatic-comparison-words').html(res.words);
                        form.find('.aiomatic-comparison-duration').html(timeDiff.toFixed(2) + ' seconds');
                        item.addClass('aiomatic-comparison-result');
                        item.attr('data-tokens',res.tokens);
                        item.attr('data-cost',res.cost);
                        item.attr('data-words',res.words);
                        item.attr('data-duration',timeDiff);
                        aiomaticCompareResult();
                    }
                    else{
                        form.find('.aiomatic-comparison-output').val(res.msg);
                        aiomaticCompareResult();
                    }
                }
            })
        }
        else{
            alert(has_error);
        }
    });
});