
"use strict"; 
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