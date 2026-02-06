"use strict";
jQuery(document).ready(function($) 
{
    let isProgrammaticChange = false;
    var workflows = [];
    let workflowVisibility = {};
    let currentPage = 1;
    const workflowsPerPage = 10;
    var textarea = $('#aiomatic_bot_workflow');
    if(!textarea)
    {
        return;
    }
    var textareaValue = textarea.val();
    try 
    {
        if (textareaValue) 
        {
            workflows = JSON.parse(textareaValue);
        }
    } catch (e) {
        console.error(aiomaticWorkflowBuilderData.invalid_json, e);
        alert(aiomaticWorkflowBuilderData.correct_json_or_reset);
        textarea.css('border', '2px solid red');
        return;
    }
    textarea.css('border', '');
    textarea.on('input', function () 
    {
        if (!isProgrammaticChange) {
            updateWorkflows(true);
        }
    });
    function updateWorkflows(noalert = false) 
    {
        saveWorkflowVisibility();
        var utextarea = $('#aiomatic_bot_workflow');
        if(!utextarea)
        {
            return;
        }
        var utextareaValue = utextarea.val();
        try 
        {
            if (utextareaValue) 
            {
                workflows = JSON.parse(utextareaValue);
            }
        } catch (e) {
            if(noalert === false)
            {
                alert(aiomaticWorkflowBuilderData.correct_json_or_reset);
                console.error(aiomaticWorkflowBuilderData.invalid_json, e);
            }
            textarea.css('border', '2px solid red');
            return;
        }
        textarea.css('border', '');
        var container = $('#aiomatic-workflow-builder-container');
        container.empty();
        var html = `<div id="workflow-errors" class="cr_red"></div>
<table class="form-table">
    <tbody id="workflows-container">
    </tbody>
</table>
<p class="center_p_element">
    <button type="button" class="button button-secondary add-workflow" id="add-workflow">` + aiomaticWorkflowBuilderData.add_workflow + `</button>
</p>
        `;
        container.append(html);
        let startIndex = (currentPage - 1) * workflowsPerPage;
        let endIndex = startIndex + workflowsPerPage;
        let paginatedWorkflows = workflows.slice(startIndex, endIndex);
        paginatedWorkflows.forEach(function(workflow, index) {
            var actualIndex = startIndex + index;
            var workflowHtml = renderWorkflow(workflow, actualIndex);
            $('#workflows-container').append(workflowHtml);
        });
        updatePagination();
        restoreWorkflowVisibility();
        onlyValidate();
    }
    function updatePagination() {
        let totalPages = Math.ceil(workflows.length / workflowsPerPage);
        $('.page-info').text(`Page ${currentPage} of ${totalPages}`);

        if (currentPage <= 1) {
            $('.prev-page').hide();
        } else {
            $('.prev-page').show();
        }

        if (currentPage >= totalPages) {
            $('.next-page').hide();
        } else {
            $('.next-page').show();
        }
    }

    $(document).on('click', '.prev-page', function () {
        if (currentPage > 1) {
            currentPage--;
            renderWorkflows();
        }
    });

    $(document).on('click', '.next-page', function () {
        if (currentPage < Math.ceil(workflows.length / workflowsPerPage)) {
            currentPage++;
            renderWorkflows();
        }
    });
    function saveWorkflowVisibility() {
        workflowVisibility = {};
        $('.workflow').each(function () {
            var index = $(this).data('index');
            workflowVisibility[index] = $(this).find('.workflow-content').is(':visible');
        });
    }

    function restoreWorkflowVisibility() {
        $('.workflow').each(function () {
            var index = $(this).data('index');
            if (workflowVisibility[index] !== undefined) {
                if (workflowVisibility[index]) {
                    $(this).find('.workflow-content').show();
                    $(this).find('.toggle-workflow').html('Hide Details ▲');
                } else {
                    $(this).find('.workflow-content').hide();
                    $(this).find('.toggle-workflow').html('Show Details ▼');
                }
            }
        });
    }
    window.updateWorkflows = updateWorkflows;
    function renderWorkflows() {
        saveWorkflowVisibility();
        var container = $('#aiomatic-workflow-builder-container');
        container.empty();
        var totalPages = Math.ceil(workflows.length / workflowsPerPage);
        var html = `<div id="workflow-errors" class="cr_red"></div>
<table class="form-table">
    <tbody id="workflows-container">
    </tbody>
</table>`;
    if (totalPages > 1) {
        html += `<div class="pagination-controls center_p_element">
    <button type="button" class="button button-secondary prev-page">« Previous Page</button>
    <span class="page-info"></span>
    <button type="button" class="button button-secondary next-page">Next Page »</button>
</div>`;
    }
html += `<p class="center_p_element">
    <button type="button" class="button button-secondary add-workflow" id="add-workflow">` + aiomaticWorkflowBuilderData.add_workflow + `</button>
</p>
        `;
        container.append(html);
        let startIndex = (currentPage - 1) * workflowsPerPage;
        let endIndex = startIndex + workflowsPerPage;
        let paginatedWorkflows = workflows.slice(startIndex, endIndex);
        paginatedWorkflows.forEach(function(workflow, index) {
            var actualIndex = startIndex + index;
            var workflowHtml = renderWorkflow(workflow, actualIndex);
            $('#workflows-container').append(workflowHtml);
        });
        updateTextarea();
        restoreWorkflowVisibility();
    }
    function onlyValidate() {
        var errors = validateWorkflows(workflows);
        var errorContainer = $('#workflow-errors');
        if (errors.length > 0) {
            errorContainer.html('<strong>' + aiomaticWorkflowBuilderData.validation_errors + ':</strong><br>' + errors.join('<br>'));
        } else {
            errorContainer.html('');
        }
    }
    function updateTextarea() {
        isProgrammaticChange = true;
        textarea.val(JSON.stringify(workflows, null, 4));
        isProgrammaticChange = false;
        textarea.css('border', '');
        var errors = validateWorkflows(workflows);
        var errorContainer = $('#workflow-errors');
        if (errors.length > 0) {
            errorContainer.html('<strong>' + aiomaticWorkflowBuilderData.validation_errors + ':</strong><br>' + errors.join('<br>'));
        } else {
            errorContainer.html('');
        }
    }
    function validateWorkflows(workflows) {
        var errors = [];
        var ids = new Set();
        workflows.forEach(function(workflow, index) 
        {
            var workflowLabel = workflow.id ? aiomaticWorkflowBuilderData.workflow_val + ' "' + workflow.id + '"' : aiomaticWorkflowBuilderData.workflow_val + ' #' + (index + 1);
            if (!workflow.id) {
                errors.push(workflowLabel + ': ' + aiomaticWorkflowBuilderData.missing_id);
            } else {
                if (ids.has(workflow.id)) {
                    errors.push(aiomaticWorkflowBuilderData.duplicate_id.replace('{id}', workflow.id));
                } else {
                    ids.add(workflow.id);
                }
            }
            if (workflow.max_repeat_count == null || workflow.max_repeat_count === '') {
                errors.push(workflowLabel + ': ' + aiomaticWorkflowBuilderData.missing_max_repeat_count);
            }
            if (workflow.priority == null || workflow.priority === '') {
                errors.push(workflowLabel + ': ' + aiomaticWorkflowBuilderData.missing_priority);
            }
            if (!workflow.triggers) {
                errors.push(workflowLabel + ': ' + aiomaticWorkflowBuilderData.missing_triggers);
            } else {
                if (!workflow.triggers.operator) {
                    errors.push(workflowLabel + ': ' + aiomaticWorkflowBuilderData.missing_operator);
                }
                if (!workflow.triggers.conditions || workflow.triggers.conditions.length === 0) {
                    errors.push(workflowLabel + ': ' + aiomaticWorkflowBuilderData.no_conditions);
                } else {
                    workflow.triggers.conditions.forEach(function(condition, condIndex) {
                        var conditionLabel = aiomaticWorkflowBuilderData.condition + ' ' + (condIndex + 1);
                        if (!condition.type) {
                            errors.push(workflowLabel + ', ' + conditionLabel + ': ' + aiomaticWorkflowBuilderData.missing_condition_type);
                        }
                        if (!condition.value && condition.type != 'user_logged_in' && condition.type != 'user_not_logged_in') {
                            errors.push(workflowLabel + ', ' + conditionLabel + ': ' + aiomaticWorkflowBuilderData.missing_condition_value);
                        }
                        switch (condition.type) {
                            case 'message_contains':
                                break;
                            case 'message_not_contains':
                                break;
                            case 'message_matches_regex':
                                break;
                            case 'message_not_matches_regex':
                                break;
                            case 'message_is':
                                break;
                            case 'any_message_contains':
                                break;
                            case 'previous_message_is':
                                break;
                            case 'nth_message':
                                break;
                            case 'nth_or_larger_message':
                                break;
                            case 'nth_or_smaller_message':
                                break;
                            case 'on_post_id':
                                break;
                            case 'on_post_category_id':
                                break;
                            case 'on_post_type':
                                break;
                            case 'user_logged_in':
                                break;
                            case 'user_not_logged_in':
                                break;
                            case 'user_role_is':
                                break;
                            case 'user_role_is_not':
                                break;
                            case 'user_name_is':
                                break;
                            case 'user_name_is_not':
                                break;
                            case 'message_starts_with':
                                break;
                            case 'message_ends_with':
                                break;
                            case 'message_length_is':
                                break;
                            case 'day_of_week':
                                break;
                            case 'time_of_day':
                                break;
                            case 'specific_date':
                                break;
                            default:
                                errors.push(workflowLabel + ', ' + conditionLabel + ': ' + aiomaticWorkflowBuilderData.invalid_condition_type.replace('{type}', condition.type));
                                break;
                        }
                    });
                }
            }
            if (!workflow.actions || workflow.actions.length === 0) {
                errors.push(workflowLabel + ': ' + aiomaticWorkflowBuilderData.no_actions);
            } else {
                workflow.actions.forEach(function(action, actionIndex) {
                    var actionLabel = aiomaticWorkflowBuilderData.action + ' ' + (actionIndex + 1);
                    if (!action.type) {
                        errors.push(workflowLabel + ', ' + actionLabel + ': ' + aiomaticWorkflowBuilderData.missing_action_type);
                    } else {
                        switch (action.type) {
                            case 'send_message':
                                if (!action.message) {
                                    errors.push(workflowLabel + ', ' + actionLabel + ' (' + aiomaticWorkflowBuilderData.send_message + '): ' + aiomaticWorkflowBuilderData.missing_message);
                                }
                                break;
                            case 'append_to_prompt':
                                if (!action.value) {
                                    errors.push(workflowLabel + ', ' + actionLabel + ' (' + aiomaticWorkflowBuilderData.append_prompt + '): ' + aiomaticWorkflowBuilderData.missing_value);
                                }
                                break;
                            case 'redirect_user':
                                if (!action.value) {
                                    errors.push(workflowLabel + ', ' + actionLabel + ' (' + aiomaticWorkflowBuilderData.redirect_user + '): ' + aiomaticWorkflowBuilderData.missing_value);
                                }
                                break;
                            case 'call_webhook':
                                if (!action.value) {
                                    errors.push(workflowLabel + ', ' + actionLabel + ' (' + aiomaticWorkflowBuilderData.call_webhook + '): ' + aiomaticWorkflowBuilderData.missing_value);
                                }
                                break;
                            case 'jump_to_workflow':
                                if (!action.workflow_id) {
                                    errors.push(workflowLabel + ', ' + actionLabel + ' (' + aiomaticWorkflowBuilderData.jump_to_workflow + '): ' + aiomaticWorkflowBuilderData.missing_workflow_id);
                                }
                                break;
                            case 'end_conversation':
                                break;
                            default:
                                errors.push(workflowLabel + ', ' + actionLabel + ': ' + aiomaticWorkflowBuilderData.invalid_action_type.replace('{type}', action.type));
                                break;
                        }
                    }
                });
            }
        });

        return errors;
    }
    jQuery(document).ready(function($) {
        $(document).on('click', '.toggle-workflow', function() {
            var $workflowContent = $(this).closest('tr').find('.workflow-content');
            if ($workflowContent.is(':visible')) {
                $(this).html('Show Details ▼');
            } else {
                $(this).html('Hide Details ▲');
            }
            $workflowContent.slideToggle("fast");
            var index = $(this).closest('.workflow').data('index');
            workflowVisibility[index] = !$workflowContent.is(':hidden');
        });
    });
    function renderWorkflow(workflow, index) {
        var isVisible = workflowVisibility[index] !== undefined ? workflowVisibility[index] : false;
        var html = `
            <tr class="workflow" data-index="${index}">
                <td colspan="2">
                    <h2 class="cr_center">` + aiomaticWorkflowBuilderData.workflow_val + ` ${index + 1}&nbsp;
                    <button type="button" class="toggle-workflow button">
                        ${isVisible ? 'Hide Details ▲' : 'Show Details ▼'}
                    </button></h2>
                    <p>
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.tooltip_id + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.id_val + `: <input type="text" name="workflows[${index}][id]" class="cr_width_full" value="${workflow.id || ''}" placeholder="` + aiomaticWorkflowBuilderData.workflowid_val + `" required></label>
                    </p>
                    <div class="workflow-content"${isVisible ? '' : 'style="display: none;"'}>
                        <p>
                            <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.tooltip_max_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.max_rep_val + `: <input type="number" class="cr_width_full" min="1" step="1" placeholder="` + aiomaticWorkflowBuilderData.max_exec + `" name="workflows[${index}][max_repeat_count]" value="${workflow.max_repeat_count || 1}" required></label>
                            <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.priority_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.priority_val + `: <input type="number" class="cr_width_full" step="1" placeholder="` + aiomaticWorkflowBuilderData.higher_exec + `" name="workflows[${index}][priority]" value="${workflow.priority || 0}" required></label>
                            <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.active_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.active_val + `: <input type="checkbox" name="workflows[${index}][active]" ${workflow.active ? 'checked' : ''}></label>
                        </p>
                        <div class="trigger_actions">
                        <div class="triggers">
                            <h3><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.triggers_val_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.triggers_val + `</h3>
                            ${renderTriggers(workflow.triggers, index)}
                        </div>
                        <div class="actions">
                            <h3><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                                <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.actions_list_val_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.actions_list_val + `</h3>
                            ${renderActions(workflow.actions, index)}
                        </div>
                        </div>
                        <hr/>
                        <p class="center_p_element">
                        <button type="button" class="button button-link-delete delete-workflow">` + aiomaticWorkflowBuilderData.delete_workflow + `</button>
                        </p>
                    </div>
                    <hr style="height: 12px;border: 0;box-shadow: inset 0 12px 12px -12px rgba(0, 0, 0, 0.5);">
                </td>
            </tr>
        `;
        return html;
    }

    function renderTriggers(triggers, workflowIndex) {
        triggers = triggers || { operator: 'AND', conditions: [] };
        var html = `
            <p>
                <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.operator_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.operator_val + `:
                    <select name="workflows[${workflowIndex}][triggers][operator]"class="cr_width_full">
                        <option value="AND" ${triggers.operator === 'AND' ? 'selected' : ''}>AND</option>
                        <option value="OR" ${triggers.operator === 'OR' ? 'selected' : ''}>OR</option>
                        <option value="NOT" ${triggers.operator === 'NOT' ? 'selected' : ''}>NOT</option>
                    </select>
                </label>
            </p>
            <table class="widefat fixed triggers-table">
                <thead>
                    <tr>
                        <th class="cr_center">` + aiomaticWorkflowBuilderData.type_val + `</th>
                        <th class="cr_center">` + aiomaticWorkflowBuilderData.value_val + `</th>
                        <th class="cr_center">` + aiomaticWorkflowBuilderData.actions_val + `</th>
                    </tr>
                </thead>
                <tbody>
        `;

        triggers.conditions.forEach(function(condition, index) {
            html += renderCondition(condition, workflowIndex, index);
        });

        html += `
                </tbody>
            </table>
            <button type="button" class="button add-condition" data-workflow-index="${workflowIndex}">` + aiomaticWorkflowBuilderData.add_condition + `</button>
        `;
        return html;
    }

    function renderCondition(condition, workflowIndex, conditionIndex) {
        return `
            <tr>
                <td>
                    <select name="workflows[${workflowIndex}][triggers][conditions][${conditionIndex}][type]" class="cr_width_full" required>
                        <option value="">` + aiomaticWorkflowBuilderData.select_type + `</option>
                        <option value="message_contains" ${condition.type === 'message_contains' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_contains + `</option>
                        <option value="message_not_contains" ${condition.type === 'message_not_contains' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_not_contains + `</option>
                        <option value="message_matches_regex" ${condition.type === 'message_matches_regex' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_match_regex + `</option>
                        <option value="message_not_matches_regex" ${condition.type === 'message_not_matches_regex' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_not_match_regex + `</option>
                        <option value="message_is" ${condition.type === 'message_is' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_is + `</option>
                        <option value="any_message_contains" ${condition.type === 'any_message_contains' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.any_message_contains + `</option>
                        <option value="previous_message_is" ${condition.type === 'previous_message_is' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.previous_message_is + `</option>
                        <option value="nth_message" ${condition.type === 'nth_message' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_nth + `</option>
                        <option value="nth_or_larger_message" ${condition.type === 'nth_or_larger_message' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_nth_or_larger + `</option>
                        <option value="nth_or_smaller_message" ${condition.type === 'nth_or_smaller_message' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_nth_or_smaller + `</option>
                        <option value="on_post_id" ${condition.type === 'on_post_id' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.on_post_id + `</option>
                        <option value="on_post_category_id" ${condition.type === 'on_post_category_id' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.on_post_category_id + `</option>
                        <option value="on_post_type" ${condition.type === 'on_post_type' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.on_post_type + `</option>
                        <option value="user_logged_in" ${condition.type === 'user_logged_in' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_user_logged_in + `</option>
                        <option value="user_not_logged_in" ${condition.type === 'user_not_logged_in' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_user_not_logged_in + `</option>
                        <option value="user_role_is" ${condition.type === 'user_role_is' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_user_role_is + `</option>
                        <option value="user_role_is_not" ${condition.type === 'user_role_is_not' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_user_role_is_not + `</option>
                        <option value="user_name_is" ${condition.type === 'user_name_is' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_user_name_is + `</option>
                        <option value="user_name_is_not" ${condition.type === 'user_name_is_not' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_user_name_is_not + `</option>
                        <option value="message_starts_with" ${condition.type === 'message_starts_with' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_starts_with + `</option>
                        <option value="message_ends_with" ${condition.type === 'message_ends_with' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_ends_with + `</option>
                        <option value="message_length_is" ${condition.type === 'message_length_is' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.message_length_is + `</option>
                        <option value="day_of_week" ${condition.type === 'day_of_week' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.day_of_week + `</option>
                        <option value="time_of_day" ${condition.type === 'time_of_day' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.time_of_day + `</option>
                        <option value="specific_date" ${condition.type === 'specific_date' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.specific_date + `</option>
                    </select>
                </td>
                <td>
                    <textarea rows="1" placeholder="` + aiomaticWorkflowBuilderData.condition_value + `" class="cr_width_full" name="workflows[${workflowIndex}][triggers][conditions][${conditionIndex}][value]" required>${condition.value || ''}</textarea>
                </td>
                <td class="cr_center">
                    <button type="button" class="button button-link-delete delete-condition">` + aiomaticWorkflowBuilderData.message_delete + `</button>
                </td>
            </tr>
        `;
    }

    function renderActions(actions, workflowIndex) {
        actions = actions || [];
        var html = `
            <table class="widefat fixed actions-table">
                <thead>
                    <tr>
                        <th class="cr_center">` + aiomaticWorkflowBuilderData.type_val + `</th>
                        <th class="cr_center">` + aiomaticWorkflowBuilderData.details_val + `</th>
                        <th class="cr_center">` + aiomaticWorkflowBuilderData.actions_val + `</th>
                    </tr>
                </thead>
                <tbody>
        `;

        actions.forEach(function(action, index) {
            html += renderAction(action, workflowIndex, index);
        });

        html += `
                </tbody>
            </table>
            <button type="button" class="button add-action" data-workflow-index="${workflowIndex}">` + aiomaticWorkflowBuilderData.add_action + `</button>
        `;
        return html;
    }

    function renderAction(action, workflowIndex, actionIndex) 
    {
        var detailsHtml = '';
        if(action)
        {
            if(!action.response_options)
            {
                action.response_options = '';
            }
            switch (action.type) {
                case 'send_message':
                    detailsHtml = `
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.message_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.message_val + `:
                            <textarea name="workflows[${workflowIndex}][actions][${actionIndex}][message]" class="cr_width_full" placeholder="` + aiomaticWorkflowBuilderData.message_val + `" required>${action.message || ''}</textarea>
                        </label>
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.delay_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.delay_val + `:
                            <input type="text" name="workflows[${workflowIndex}][actions][${actionIndex}][delay]" class="cr_width_full" value="${action.delay || ''}" placeholder="` + aiomaticWorkflowBuilderData.number_range + `">
                        </label>
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.response_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.respobj_val + `:
                            <textarea rows="1" name="workflows[${workflowIndex}][actions][${actionIndex}][response_options]" class="cr_width_full" placeholder="` + aiomaticWorkflowBuilderData.response_options + `">${action.response_options || ''}</textarea>
                        </label>
                    `;
                    break;
                case 'append_to_prompt':
                    detailsHtml = `
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.value_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.prompt_val + `:
                            <textarea placeholder="` + aiomaticWorkflowBuilderData.text_prepend + `" class="cr_width_full" name="workflows[${workflowIndex}][actions][${actionIndex}][value]" required>${action.value || ''}</textarea>
                        </label>
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.response_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.respobj_val + `:
                            <textarea rows="1" name="workflows[${workflowIndex}][actions][${actionIndex}][response_options]" class="cr_width_full" placeholder="` + aiomaticWorkflowBuilderData.response_options + `">${action.response_options || ''}</textarea>
                        </label>
                    `;
                    break;
                case 'jump_to_workflow':
                    detailsHtml = `
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.workflow_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.workflowid_val + `:
                            <input type="text" placeholder="` + aiomaticWorkflowBuilderData.jump_id + `" class="cr_width_full" name="workflows[${workflowIndex}][actions][${actionIndex}][workflow_id]" value="${action.workflow_id || ''}" required>
                        </label>
                    `;
                    break;
                case 'redirect_user':
                    detailsHtml = `
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.redirect_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.redirect_val + `:
                            <textarea placeholder="` + aiomaticWorkflowBuilderData.text_redirect + `" class="cr_width_full" name="workflows[${workflowIndex}][actions][${actionIndex}][value]" required>${action.value || ''}</textarea>
                        </label>
                    `;
                    break;
                case 'call_webhook':
                    detailsHtml = `
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.webhook_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.webhook_val + `:
                            <textarea placeholder="` + aiomaticWorkflowBuilderData.text_webhook + `" class="cr_width_full" name="workflows[${workflowIndex}][actions][${actionIndex}][value]" required>${action.value || ''}</textarea>
                        </label>
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.message_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.message_val + `:
                            <textarea name="workflows[${workflowIndex}][actions][${actionIndex}][message]" class="cr_width_full" placeholder="` + aiomaticWorkflowBuilderData.message_val + `" required>${action.message || ''}</textarea>
                        </label>
                        <label><span class="bws_help_box bws_help_box_right dashicons dashicons-editor-help cr_align_middle">
                            <span class="bws_hidden_help_text cr_min_260px">` + aiomaticWorkflowBuilderData.delay_rep + `</span></span>&nbsp;` + aiomaticWorkflowBuilderData.delay_val + `:
                            <input type="text" name="workflows[${workflowIndex}][actions][${actionIndex}][delay]" class="cr_width_full" value="${action.delay || ''}" placeholder="` + aiomaticWorkflowBuilderData.number_range + `">
                        </label>
                    `;
                    break;
                case 'end_conversation':
                    detailsHtml = '';
                    break;
                default:
                    detailsHtml = '';
                    break;
            }
            return `
                <tr>
                    <td>
                        <select name="workflows[${workflowIndex}][actions][${actionIndex}][type]" class="action-type-select cr_width_full" required>
                            <option value="">` + aiomaticWorkflowBuilderData.select_type + `</option>
                            <option value="send_message" ${action.type === 'send_message' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.send_message + `</option>
                            <option value="append_to_prompt" ${action.type === 'append_to_prompt' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.append_prompt + `</option>
                            <option value="jump_to_workflow" ${action.type === 'jump_to_workflow' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.jump_to_workflow + `</option>
                            <option value="redirect_user" ${action.type === 'redirect_user' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.redirect_user + `</option>
                            <option value="call_webhook" ${action.type === 'call_webhook' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.call_webhook + `</option>
                            <option value="end_conversation" ${action.type === 'end_conversation' ? 'selected' : ''}>` + aiomaticWorkflowBuilderData.end_conversation + `</option>
                        </select>
                    </td>
                    <td class="action-details">
                        ${detailsHtml}
                    </td>
                    <td class="cr_center">
                        <button type="button" class="button button-link-delete delete-action">` + aiomaticWorkflowBuilderData.message_delete + `</button>
                    </td>
                </tr>
            `;
        }
        return '';
    }

    function initializeConditionVisibility() {
        $('select[name^="workflows"][name$="[type]"]').each(function () {
            var $select = $(this);
            var conditionType = $select.val();
            var $textarea = $select.closest('tr').find('textarea');
    
            if (conditionType === 'user_logged_in' || conditionType === 'user_not_logged_in') {
                $textarea.closest('td').css('visibility', 'hidden');
            } else {
                $textarea.closest('td').css('visibility', 'visible');
            }
        });
    }

    $(document).on('click', '#add-workflow', function() {
        workflows.push({
            id: '',
            active: true,
            max_repeat_count: 1,
            priority: 0,
            triggers: { operator: 'AND', conditions: [] },
            actions: []
        });
        currentPage = Math.ceil(workflows.length / workflowsPerPage);
        renderWorkflows();
    });

    $(document).on('click', '.delete-workflow', function() {
        var index = $(this).closest('.workflow').data('index');
        workflows.splice(index, 1);
        let totalPages = Math.ceil(workflows.length / workflowsPerPage);
        if (currentPage > totalPages) {
            currentPage = totalPages > 0 ? totalPages : 1;
        }
        renderWorkflows();
    });

    $(document).on('change', 'select[name^="workflows"][name$="[type]"]', function () {
        var $select = $(this); 
        var conditionType = $select.val(); 
        var $textarea = $select.closest('tr').find('textarea'); 
    
        if (conditionType === 'user_logged_in' || conditionType === 'user_not_logged_in') {
            $textarea.closest('td').css('visibility', 'hidden');
        } else {
            $textarea.closest('td').css('visibility', 'visible');
        }
    });

    $(document).on('click', '.add-condition', function() {
        var workflowIndex = $(this).data('workflow-index');
        workflows[workflowIndex].triggers.conditions.push({ type: '', value: '' });
        renderWorkflows();
    });

    $(document).on('click', '.delete-condition', function() {
        var workflowIndex = $(this).closest('.workflow').data('index');
        var conditionIndex = $(this).closest('tr').index();
        workflows[workflowIndex].triggers.conditions.splice(conditionIndex, 1);
        renderWorkflows();
    });

    $(document).on('click', '.add-action', function() {
        var workflowIndex = $(this).data('workflow-index');
        workflows[workflowIndex].actions.push({ type: '', message: '', delay: '', response_options: '' });
        renderWorkflows();
    });

    $(document).on('click', '.delete-action', function() {
        var workflowIndex = $(this).closest('.workflow').data('index');
        var actionIndex = $(this).closest('tr').index();
        workflows[workflowIndex].actions.splice(actionIndex, 1);
        renderWorkflows();
    });

    $(document).on('change', '.action-type-select', function() {
        var workflowIndex = $(this).closest('.workflow').data('index');
        var actionIndex = $(this).closest('tr').index();
        var actionType = $(this).val();
        workflows[workflowIndex].actions[actionIndex] = { type: actionType };
        renderWorkflows();
    });

    $(document).on('input change', '#aiomatic-workflow-builder-container input, #aiomatic-workflow-builder-container textarea, #aiomatic-workflow-builder-container select', function() {
        var name = $(this).attr('name');
        var value = $(this).val();
        var checked = $(this).is(':checked');
        var inputType = $(this).attr('type');
        if(name)
        {
            updateWorkflowData(name, value, checked, inputType);
        }
        updateTextarea();
    });

    function updateWorkflowData(name, value, checked, inputType) {
        var nameParts = name.match(/workflows\[(\d+)\](.*)/);
        if (!nameParts) return;

        var workflowIndex = parseInt(nameParts[1]);
        var path = nameParts[2]; 

        var workflow = workflows[workflowIndex];
        var obj = workflow;
        var pathParts = path.match(/\[([^\]]+)\]/g);

        for (var i = 0; i < pathParts.length - 1; i++) {
            var key = pathParts[i].replace(/\[|\]/g, '');
            if (!obj[key]) obj[key] = {};
            obj = obj[key];
        }

        var lastKey = pathParts[pathParts.length - 1].replace(/\[|\]/g, '');

        if (inputType === 'checkbox') {
            obj[lastKey] = checked;
        } else if (inputType === 'number') {
            obj[lastKey] = parseInt(value);
        } else if ($(this).attr('multiple')) {
            obj[lastKey] = $(this).val();
        } else {
            obj[lastKey] = value;
        }
    }
    renderWorkflows(true);
    initializeConditionVisibility();
});