"use strict";
function assistantSelected(checkID, disableClass)
{
    if(jQuery('#' + checkID).val() == '')
    {
        jQuery('.' + disableClass).find('option').removeAttr('disabled');
    }
    else
    {
        jQuery('.' + disableClass).find('option').attr('disabled', 'disabled');
    }
}
function visionSelectedAI()
{
    var selected = jQuery('#edit_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision").show();
    }
    else
    {
        jQuery(".hideVision").hide();
    }
}
function taxSeoChanged()
{
    if(jQuery('#tax_seo_auto').val() == 'write')
    {            
        jQuery(".TaxSEO").show();
    }
    else
    {
        jQuery(".TaxSEO").hide();
    }
}
function actionsChangedTax()
{
    var confirm_delete = confirm('Are you sure you want to run manual taxonomy description writing?');
    if (confirm_delete) {
        var runImg = document.getElementById('run_imgx');
        document.getElementById('taxactions').setAttribute('disabled','disabled');
        runImg.classList.remove('cr_hidden');
        jQuery.ajax({
            url: mycustomspinsettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'aiomatic_write_tax_description_manual',
                nonce: mycustomspinsettings.nonce
            },
            success: function(res) {
                runImg.classList.add('cr_hidden');
                    document.getElementById('taxactions').removeAttribute('disabled');
                if(res.success == true)
                {
                    alert('Taxonomy descriptions were written successfully!');
                }
                else
                {
                    alert('Failed to generate manual tax description: ' + res.data.message);
                    console.log('Taxonomy manual description generator returned an error: ' + JSON.stringify(res));
                }
            },
            error: function(xhr, status, error) {
                runImg.classList.add('cr_hidden');
                document.getElementById('taxactions').removeAttribute('disabled');
                alert('Failed to generate the taxonomy description, please try again later!');
                console.log('Error: ' + error);
            }
        });
    }
}
function visionTitleSelectedAI()
{
    var selected = jQuery('#title_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideTitleVision").show();
    }
    else
    {
        jQuery(".hideTitleVision").hide();
    }
}
function visionSlugSelectedAI()
{
    var selected = jQuery('#slug_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideSlugVision").show();
    }
    else
    {
        jQuery(".hideSlugVision").hide();
    }
}
function visionExcerptSelectedAI()
{
    var selected = jQuery('#excerpt_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideExcerptVision").show();
    }
    else
    {
        jQuery(".hideExcerptVision").hide();
    }
}
function visionSelectedAI3()
{
    var selected = jQuery('#model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision3").show();
    }
    else
    {
        jQuery(".hideVision3").hide();
    }
}
function visionSelectedAI7()
{
    var selected = jQuery('#cats_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision7").show();
    }
    else
    {
        jQuery(".hideVision7").hide();
    }
}
function visionSelectedAI9()
{
    var selected = jQuery('#link_juicer_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision9").show();
    }
    else
    {
        jQuery(".hideVision9").hide();
    }
}
function visionSelectedAI8()
{
    var selected = jQuery('#tags_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision8").show();
    }
    else
    {
        jQuery(".hideVision8").hide();
    }
}
function visionSelectedAI10()
{
    var selected = jQuery('#custom_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision10").show();
    }
    else
    {
        jQuery(".hideVision10").hide();
    }
}
function visionSelectedAI5()
{
    var selected = jQuery('#comments_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision5").show();
    }
    else
    {
        jQuery(".hideVision5").hide();
    }
}
function visionSelectedAI6()
{
    var selected = jQuery('#seo_model').val();
    var found = false;
    aiomatic_object.modelsvision.forEach((model) => {
        let selectedParts = selected.split(':');
        selected = selectedParts[0];
        if(model == selected)
        {
            found = true;
        }
    });
    if(found == true)
    {
        jQuery(".hideVision6").show();
    }
    else
    {
        jQuery(".hideVision6").hide();
    }
}
jQuery(document).ready(function()
{
    visionSelectedAI();
    visionTitleSelectedAI();
    visionSlugSelectedAI();
    visionExcerptSelectedAI();
    visionSelectedAI3();
    visionSelectedAI7();
    visionSelectedAI8();
    visionSelectedAI10();
    visionSelectedAI9();
    visionSelectedAI5();
    visionSelectedAI6();
    taxSeoChanged();
});
jQuery(document).ready(function ($) {
    $('#myForm').find('input, select, textarea').on('invalid', function (event) {
        event.preventDefault();
        let $field = $(this);
        let fieldName = $('label[for="' + this.id + '"]').text() || this.name || 'Unnamed field';
        let errorMessage = '';
        if (this.validity.valueMissing) {
            errorMessage = `${fieldName} is required.`;
        } else if (this.validity.typeMismatch) {
            errorMessage = `${fieldName} has an incorrect format.`;
        } else if (this.validity.rangeOverflow) {
            errorMessage = `${fieldName} exceeds the max value (${this.max}).`;
        } else if (this.validity.rangeUnderflow) {
            errorMessage = `${fieldName} is below the min value (${this.min}).`;
        } else if (this.validity.patternMismatch) {
            errorMessage = `${fieldName} does not match the required pattern.`;
        } else if (this.validity.stepMismatch) {
            errorMessage = `${fieldName} has an invalid step value.`;
        } else {
            errorMessage = `${fieldName} has an invalid value.`;
        }
        let $hiddenParent = $field.closest('.hidden, [style*="display: none"], [style*="visibility: hidden"]');
        if ($hiddenParent.length) {
            $hiddenParent.show();
            $field.show().addClass('aiomatic-highlight-error');
        }
        alert(errorMessage); 
        $field.focus();
    });
    $('#myForm').on('submit', function (event) {
        let invalidFields = $(this).find(':invalid');
        if (invalidFields.length > 0) {
            event.preventDefault();
        }
    });
});
