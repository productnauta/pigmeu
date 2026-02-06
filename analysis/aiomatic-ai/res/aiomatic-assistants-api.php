<?php
function aiomatic_openai_save_assistant($token, $model, $name, $description = null, $temperature = '', $topp = '', $instructions = null, $tools = [], $file_ids = [], $metadata = null, &$vector_store_id = '') 
{
    if (empty($metadata)) 
    {
        $metadata = new stdClass();
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/assistants' . $api_ver;

        $localAzureDeployments = array();
        $depl_arr = aiomatic_get_deployments($token);
        if(is_array($depl_arr))
        {
            foreach($depl_arr as $dar)
            {
                if(empty($dar))
                {
                    continue;
                }
                if(is_string($dar))
                {
                    $localAzureDeployments[trim($dar)] = trim($dar);
                }
                else
                {
                    $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                }
            }
        }
        $azureDeployment = '';
        foreach ( $localAzureDeployments as $dmodel => $dname ) 
        {
            if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) 
            {
                $azureDeployment = $dname;
                break;
            }
        }
        if ( $azureDeployment == '' ) 
        {
            $new_dep = aiomatic_update_deployments_azure($token);
            if($new_dep !== false)
            {
                $localAzureDeployments = array();
                foreach($new_dep as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
                foreach ( $localAzureDeployments as $dmodel => $dname ) 
                {
                    if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                        $azureDeployment = $dname;
                        break;
                    }
                }
            }
            if ( $azureDeployment == '' ) 
            {
                throw new Exception('No added Azure deployment found for chat model: ' . $model . ' - you need to add this model in your Azure Portal as a Deployment');
            }
        }
        $model = $azureDeployment;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . '/v1/assistants';
    }
    if(empty($name))
    {
        throw new Exception('Name cannot be empty');
    }
    if(empty($model))
    {
        throw new Exception('Model cannot be empty');
    }
    $code_interpreter_found = false;
    foreach($tools as $xthist)
    {
        if($xthist['type'] == 'code_interpreter')
        {
            $code_interpreter_found = true;
            break;
        }
    }
    if(is_array($file_ids) && $code_interpreter_found === true && count($file_ids) > 20)
    {
        $file_ids = array_slice($file_ids, 0, 20);
    }
    $tools_resource = new stdClass();
    foreach($tools as $thist)
    {
        if($thist['type'] == 'code_interpreter')
        {
            $tools_resource->code_interpreter = new stdClass();
            $tools_resource->code_interpreter->file_ids = $file_ids;
        }
        elseif($thist['type'] == 'file_search')
        {
            if(count($file_ids) > 0)
            {
                $tools_resource->file_search = new stdClass();
                $tools_resource->file_search->vector_stores = array();
                $vs = array();
                $vs['file_ids'] = $file_ids;
                $tools_resource->file_search->vector_stores[] = $vs;
            }
        }
    }
    $postData = [
        'model' => $model,
        'name' => $name,
        'description' => $description,
        'instructions' => $instructions,
        'tools' => $tools,
        'tool_resources' => $tools_resource,
        'metadata' => $metadata
    ];
    if(!empty($temperature))
    {
        $postData['temperature'] = floatval($temperature);
    }
    if(!empty($topp))
    {
        $postData['top_p'] = floatval($topp);
    }
    $ch = curl_init($url);
    if($ch === false)
    {
        throw new Exception('Failed to init curl');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if($response === false)
    {
        curl_close($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        throw new Exception('Failed to exec curl, unknown issue.');
    }
    $jsdec = json_decode($response, true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        curl_close($ch);
        $err_mess = isset($jsdec['error']['code']) ? ', error: ' . $jsdec['error']['code'] : '';
        if(isset($jsdec['error']['message']))
        {
            $err_mess = ', error: ' . $jsdec['error']['message'];
        }
        throw new Exception("Response with Status Code: " . $httpCode . $err_mess);
    }
    curl_close($ch);
    if(isset($jsdec['tool_resources']['file_search']['vector_store_ids'][0]))
    {
        $vector_store_id = $jsdec['tool_resources']['file_search']['vector_store_ids'][0];
    }
    return $jsdec;
}
function aiomatic_openai_modify_assistant($token, $assistant_id, $model = null, $name = null, $description = null, $instructions = null, $temperature = '', $topp = '', $tools = [], $file_ids = [], $metadata = null, &$vector_store_id = '', $address_post_id = '') 
{
    if (empty($metadata)) 
    {
        $metadata = new stdClass();
    }
    if (empty($assistant_id)) 
    {
        throw new Exception('Assistant ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/assistants/' . $assistant_id . $api_ver;

        $localAzureDeployments = array();
        $depl_arr = aiomatic_get_deployments($token);
        if(is_array($depl_arr))
        {
            foreach($depl_arr as $dar)
            {
                if(empty($dar))
                {
                    continue;
                }
                if(is_string($dar))
                {
                    $localAzureDeployments[trim($dar)] = trim($dar);
                }
                else
                {
                    $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                }
            }
        }
        $azureDeployment = '';
        foreach ( $localAzureDeployments as $dmodel => $dname ) 
        {
            if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) 
            {
                $azureDeployment = $dname;
                break;
            }
        }
        if ( $azureDeployment == '' ) 
        {
            $new_dep = aiomatic_update_deployments_azure($token);
            if($new_dep !== false)
            {
                $localAzureDeployments = array();
                foreach($new_dep as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
                foreach ( $localAzureDeployments as $dmodel => $dname ) 
                {
                    if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                        $azureDeployment = $dname;
                        break;
                    }
                }
            }
            if ( $azureDeployment == '' ) 
            {
                throw new Exception('No added Azure deployment found for chat model: ' . $model . ' - you need to add this model in your Azure Portal as a Deployment');
            }
        }
        $model = $azureDeployment;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/assistants/" . $assistant_id;
    }
    $tools_resource = new stdClass();
    foreach($tools as $thist)
    {
        if($thist['type'] == 'code_interpreter')
        {
            $tools_resource->code_interpreter = new stdClass();
            $tools_resource->code_interpreter->file_ids = $file_ids;
        }
        elseif($thist['type'] == 'file_search')
        {
            if(count($file_ids) > 0)
            {
                if(!empty($vector_store_id))
                {
                    $solved = false;
                    $ls_fid = [];
                    try
                    {
                        $list_stores = aiomatic_openai_list_vector_store_files($token, $vector_store_id);
                    }
                    catch(Exception $e)
                    {
                        if(strstr($e->getMessage(), 'No vector store found with id') !== false)
                        {
                            $vs = aiomatic_openai_create_vector_store($token, 'New Vector Store', $file_ids);
                            if(isset($vs['id']))
                            {
                                $tools_resource->file_search = new stdClass();
                                $tools_resource->file_search->vector_store_ids = array($vs['id']);
                                $vector_store_id = $vs['id'];
                                if(!empty($address_post_id))
                                {
                                    update_post_meta($address_post_id, '_assistant_vector_store_id', $address_post_id);
                                    $solved = true;
                                }
                            }
                        }
                        else
                        {
                            throw $e;
                        }
                    }
                    if(!$solved)
                    {
                        foreach($list_stores as $ls)
                        {
                            $ls_fid[] = $ls['id'];
                        }
                        foreach($file_ids as $fid)
                        {
                            if(!in_array($fid, $ls_fid))
                            {
                                aiomatic_openai_create_vector_store_file($token, $vector_store_id, $fid);
                            }
                        }
                        foreach($ls_fid as $fid)
                        {
                            if(!in_array($fid, $file_ids))
                            {
                                aiomatic_openai_delete_vector_store_file($token, $vector_store_id, $fid);
                            }
                        }
                        $tools_resource->file_search = new stdClass();
                        $tools_resource->file_search->vector_store_ids = array($vector_store_id);
                    }
                }
                else
                {
                    if($vector_store_id !== null)
                    {
                        $vs = aiomatic_openai_create_vector_store($token, 'New Vector Store', $file_ids);
                        if(isset($vs['id']))
                        {
                            $tools_resource->file_search = new stdClass();
                            $tools_resource->file_search->vector_store_ids = array($vs['id']);
                            $vector_store_id = $vs['id'];
                            if(!empty($address_post_id))
                            {
                                update_post_meta($address_post_id, '_assistant_vector_store_id', $address_post_id);
                            }
                        }
                    }
                }
            }
            else
            {
                $tools_resource->file_search = new stdClass();
                $tools_resource->file_search->vector_store_ids = array();
            }
        }
    }
    $postData = array_filter([
        'model' => $model,
        'name' => $name,
        'description' => $description,
        'instructions' => $instructions,
        'tools' => $tools,
        'tool_resources' => $tools_resource,
        'metadata' => $metadata
    ], function($value) { return !is_null($value); });
    if(!empty($temperature))
    {
        $postData['temperature'] = floatval($temperature);
    }
    if(!empty($topp))
    {
        $postData['top_p'] = floatval($topp);
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP modify assistant request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_duplicate_assistant($token, $assistant_id, &$vector_store_id = '') 
{
    $existingAssistant = aiomatic_openai_retrieve_assistant($token, $assistant_id);
    if (empty($existingAssistant) || $existingAssistant['id'] != $assistant_id) {
        throw new Exception('Failed to retrieve the assistant to duplicate');
    }
    $name = $existingAssistant['name'] . ' (Copy)';
    $description = $existingAssistant['description'];
    $model = $existingAssistant['model'];
    $instructions = $existingAssistant['instructions'];
    $tools = $existingAssistant['tools'];
    $temperature = $existingAssistant['temperature']; 
    $topp = $existingAssistant['top_p']; 
    if(isset($existingAssistant['tool_resources']['code_interpreter']['file_ids']))
    {
        $file_ids = $existingAssistant['tool_resources']['code_interpreter']['file_ids'];
    }
    else
    {
        $file_ids = array();
    }
    if(!is_object($existingAssistant['metadata']))
    {
        $existingAssistant['metadata'] = new stdClass();
    }
    $metadata = $existingAssistant['metadata'];
    $newAssistant = aiomatic_openai_save_assistant($token, $model, $name, $description, $temperature, $topp, $instructions, $tools, $file_ids, $metadata, $vector_store_id);
    return $newAssistant;
}
function aiomatic_openai_delete_assistant($token, $assistant_id) 
{
    if(empty($assistant_id)) {
        throw new Exception('Assistant ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/assistants/' . $assistant_id . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . '/v1/assistants/' . $assistant_id;
    }
    $ch = curl_init($url);
    if($ch === false) {
        throw new Exception('Failed to init curl');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Curl error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $jsdec = json_decode($response, true);
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Response with Status Code: " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_list_assistants($token, $limit = 100, $order = 'desc') 
{
    $assistants = [];
    $has_more = true;
    $after = null;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    while ($has_more) 
    {
        if(aiomatic_check_if_azure($aiomatic_Main_Settings))
        {
            $headers = [
                'Content-Type: application/json',
                'api-key:' . $token,
                'OpenAI-Beta: assistants=v2'
            ];
            if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
            {
                $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
            }
            else
            {
                $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
            }
            $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/assistants?limit=' . $limit . '&order=' . $order . str_replace('?', '&', $api_ver);
        }
        else
        {
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'OpenAI-Beta: assistants=v2'
            ];
            if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
            {
                $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
            }
            if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
            {
                $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
            }
            else
            {
                $api_endpoint = 'https://api.openai.com';
            }
            $url = $api_endpoint . '/v1/assistants?limit=' . $limit . '&order=' . $order;
        }
        if ($after) {
            $url .= '&after=' . $after;
        }
        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Failed to init curl');
        }
        if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
        {
            $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
            $randomness = array_rand($prx);
            curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
            if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
            {
                $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
                }
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
        {
            $ztime = intval($aiomatic_Main_Settings['max_timeout']);
        }
        else
        {
            $ztime = 300;
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Curl error: ' . $error);
        }
        $decodedResponse = json_decode($response, true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Response with Status Code: " . $httpCode . (isset($decodedResponse['error']['message']) ? ', error: ' . $decodedResponse['error']['message'] : ''));
        }
        $assistants = array_merge($assistants, $decodedResponse['data']);
        $has_more = $decodedResponse['has_more'];
        $after = $decodedResponse['has_more'] ? end($decodedResponse['data'])['id'] : null;
        if($after === null)
        {
            $has_more = false;
        }
        curl_close($ch);
    }
    return $assistants;
}
function aiomatic_openai_list_vector_store_files($token, $vector_store_id, $limit = 100, $order = 'desc') 
{
    $assistants = [];
    $has_more = true;
    $after = null;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    while ($has_more) 
    {
        if(aiomatic_check_if_azure($aiomatic_Main_Settings))
        {
            $headers = [
                'Content-Type: application/json',
                'api-key:' . $token,
                'OpenAI-Beta: assistants=v2'
            ];
            if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
            {
                $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
            }
            else
            {
                $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
            }
            $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/vector_stores/' . $vector_store_id . '/files?limit=' . $limit . '&order=' . $order . str_replace('?', '&', $api_ver);
        }
        else
        {
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'OpenAI-Beta: assistants=v2'
            ];
            if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
            {
                $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
            }
            if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
            {
                $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
            }
            else
            {
                $api_endpoint = 'https://api.openai.com';
            }
            $url = $api_endpoint . '/v1/vector_stores/' . $vector_store_id . '/files?limit=' . $limit . '&order=' . $order;
        }
        if ($after) {
            $url .= '&after=' . $after;
        }
        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Failed to init curl');
        }
        if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
        {
            $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
            $randomness = array_rand($prx);
            curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
            if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
            {
                $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
                }
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
        {
            $ztime = intval($aiomatic_Main_Settings['max_timeout']);
        }
        else
        {
            $ztime = 300;
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Curl error: ' . $error);
        }
        $decodedResponse = json_decode($response, true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Response with Status Code: " . $httpCode . (isset($decodedResponse['error']['message']) ? ', error: ' . $decodedResponse['error']['message'] : ''));
        }
        $assistants = array_merge($assistants, $decodedResponse['data']);
        $has_more = $decodedResponse['has_more'];
        $after = $decodedResponse['has_more'] ? end($decodedResponse['data'])['id'] : null;
        if($after === null)
        {
            $has_more = false;
        }
        curl_close($ch);
    }
    return $assistants;
}
function aiomatic_openai_retrieve_assistant($token, $assistant_id) 
{
    if (empty($assistant_id)) {
        throw new Exception('Assistant ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/assistants/' . $assistant_id . $api_ver;
    }
    else
    {
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token,
            "OpenAI-Beta: assistants=v2"
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/assistants/" . $assistant_id;
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error: " . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP request failed with code (assistant id: ' . $assistant_id . ') " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to decode JSON response');
    }
    return $decodedResponse;
}
function aiomatic_openai_create_vector_store($token, $name, $files = []) 
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/vector_stores' . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/vector_stores";
    }
    $postData = [
        'name' => $name,
        'file_ids' => $files
    ];

    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create thread request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_list_vector_stores($token, $limit = 100, $order = 'desc') 
{
    $stores = [];
    $has_more = true;
    $after = null;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    while ($has_more) 
    {
        if(aiomatic_check_if_azure($aiomatic_Main_Settings))
        {
            $headers = [
                'Content-Type: application/json',
                'api-key:' . $token,
                'OpenAI-Beta: assistants=v2'
            ];
            if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
            {
                $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
            }
            else
            {
                $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
            }
            $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/vector_stores?limit=' . $limit . '&order=' . $order . str_replace('?', '&', $api_ver);
        }
        else
        {
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'OpenAI-Beta: assistants=v2'
            ];
            if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
            {
                $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
            }
            if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
            {
                $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
            }
            else
            {
                $api_endpoint = 'https://api.openai.com';
            }
            $url = $api_endpoint . '/v1/vector_stores?limit=' . $limit . '&order=' . $order;
        }
        if ($after) {
            $url .= '&after=' . $after;
        }
        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Failed to init curl');
        }
        if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
        {
            $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
            $randomness = array_rand($prx);
            curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
            if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
            {
                $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
                }
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
        {
            $ztime = intval($aiomatic_Main_Settings['max_timeout']);
        }
        else
        {
            $ztime = 300;
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Curl error: ' . $error);
        }
        $decodedResponse = json_decode($response, true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Response with Status Code: " . $httpCode . (isset($decodedResponse['error']['message']) ? ', error: ' . $decodedResponse['error']['message'] : ''));
        }
        $stores = array_merge($stores, $decodedResponse['data']);
        $has_more = $decodedResponse['has_more'];
        $after = $decodedResponse['has_more'] ? end($decodedResponse['data'])['id'] : null;
        if($after === null)
        {
            $has_more = false;
        }
        curl_close($ch);
    }
    return $stores;
}
function aiomatic_openai_modify_vector_store($token, $vector_store_id, $name) 
{
    if (empty($vector_store_id)) 
    {
        throw new Exception('Vectore store ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/vector_stores/' . $vector_store_id . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/vector_stores/" . $vector_store_id;
    }
    $postData = [
        'name' => $name
    ];
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP modify vector store request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_delete_vector_store($token, $vector_store_id) 
{
    if(empty($vector_store_id)) {
        throw new Exception('Vector store ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/vector_stores/' . $vector_store_id . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . '/v1/vector_stores/' . $vector_store_id;
    }
    $ch = curl_init($url);
    if($ch === false) {
        throw new Exception('Failed to init curl');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Curl error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $decodedResponse = json_decode($response, true);
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Response with Status Code: " . $httpCode . (isset($decodedResponse['error']['message']) ? ', error: ' . $decodedResponse['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_delete_vector_store_file($token, $vector_store_id, $file_id) 
{
    if(empty($vector_store_id)) {
        throw new Exception('Vectore store ID cannot be empty');
    }if(empty($file_id)) {
        throw new Exception('File ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/vector_stores/' . $vector_store_id . '/files/' . $file_id . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . '/v1/vector_stores/' . $vector_store_id . '/files/' . $file_id;
    }
    $ch = curl_init($url);
    if($ch === false) {
        throw new Exception('Failed to init curl');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Curl error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $decodedResponse = json_decode($response, true);
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("Response with Status Code: " . $httpCode . (isset($decodedResponse['error']['message']) ? ', error: ' . $decodedResponse['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_create_vector_store_file($token, $vector_store_id, $file_id) 
{
    if (empty($vector_store_id)) {
        throw new Exception('Vector store ID cannot be empty');
    }
    if (empty($file_id)) {
        throw new Exception('File ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/vector_stores/' . $vector_store_id . "/files" . $api_ver;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/vector_stores/" . $vector_store_id . "/files";
    }
    $postData = [
        'file_id' => $file_id
    ];
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create assistant request failed with status code" . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_create_assistant_file($token, $assistant_id, $file_id) 
{
    if (empty($assistant_id)) {
        throw new Exception('Assistant ID cannot be empty');
    }
    if (empty($file_id)) {
        throw new Exception('File ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/' . $assistant_id . "/files" . $api_ver;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/assistants/" . $assistant_id . "/files";
    }
    $postData = [
        'file_id' => $file_id
    ];
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create assistant request failed with status code" . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_modify_thread($token, $thread_id, $vector_store_id = '') 
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id;
    }
    $postData = [];
    if($vector_store_id != '')
    {
        $postData['tool_resources'] = [
            'file_search' => [
                'vector_store_ids' => [$vector_store_id]
            ]
        ];
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create thread request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_create_thread($token, $messages = [], $vector_store_id = '') 
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads' . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads";
    }
    $postData = [
        'messages' => $messages
    ];
    if($vector_store_id != '')
    {
        $postData['tool_resources'] = [
            'file_search' => [
                'vector_store_ids' => [$vector_store_id]
            ]
        ];
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create thread request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_create_thread_and_run($token, $assistant_id, $thread = null, $model = null, $instructions = null, $tools = [], $metadata = null, $file_data = '') 
{
    if (empty($metadata)) 
    {
        $metadata = new stdClass();
    }
    if (empty($assistant_id)) {
        throw new Exception('Assistant ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/runs' . $api_ver;
        
        $localAzureDeployments = array();
        $depl_arr = aiomatic_get_deployments($token);
        if(is_array($depl_arr))
        {
            foreach($depl_arr as $dar)
            {
                if(empty($dar))
                {
                    continue;
                }
                if(is_string($dar))
                {
                    $localAzureDeployments[trim($dar)] = trim($dar);
                }
                else
                {
                    $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                }
            }
        }
        $azureDeployment = '';
        foreach ( $localAzureDeployments as $dmodel => $dname ) 
        {
            if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) 
            {
                $azureDeployment = $dname;
                break;
            }
        }
        if ( $azureDeployment == '' ) 
        {
            $new_dep = aiomatic_update_deployments_azure($token);
            if($new_dep !== false)
            {
                $localAzureDeployments = array();
                foreach($new_dep as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
                foreach ( $localAzureDeployments as $dmodel => $dname ) 
                {
                    if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                        $azureDeployment = $dname;
                        break;
                    }
                }
            }
            if ( $azureDeployment == '' ) 
            {
                throw new Exception('No added Azure deployment found for chat model: ' . $model . ' - you need to add this model in your Azure Portal as a Deployment');
            }
        }
        $model = $azureDeployment;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/runs";
    }
    $postData = [
        'assistant_id' => $assistant_id
    ];
    if($file_data != '')
    {
        $postData['tool_resources'] = [
            'file_search' => [
                'vector_store_ids' => [$file_data]
            ]
        ];
    }
    if(!empty($thread))
    {
        $postData['thread'] = $thread;
    }
    if ($model !== null) {
        $postData['model'] = $model;
    }
    if ($instructions !== null) {
        $postData['instructions'] = $instructions;
    }
    if (!empty($tools)) {
        $postData['tools'] = $tools;
    }
    if (!empty($metadata)) {
        $postData['metadata'] = $metadata;
    }
    if(isset($aiomatic_Main_Settings['assist_max_prompt_token']) && $aiomatic_Main_Settings['assist_max_prompt_token'] != '')
    {
        $postData['max_prompt_tokens'] = intval($aiomatic_Main_Settings['assist_max_prompt_token']);
    }
    if(isset($aiomatic_Main_Settings['assist_max_completion_token']) && $aiomatic_Main_Settings['assist_max_completion_token'] != '')
    {
        $postData['max_completion_tokens'] = intval($aiomatic_Main_Settings['assist_max_completion_token']);
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create thread rund request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_create_message($token, $thread_id, $role, $content, $metadata = null, $vision_file = '') 
{
    if (empty($metadata)) 
    {
        $metadata = new stdClass();
    }
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($role)) {
        throw new Exception('Role cannot be empty');
    }
    if (empty($content)) {
        throw new Exception('Content cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . '/messages' . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/messages";
    }
    if($vision_file != '' && $role == 'user')
    {
        $base64_vision = '';
        $xcopy = $content;
        if(stristr($vision_file, 'http://localhost/') || stristr($vision_file, 'https://localhost/'))
        {
            $base64_vision = aiomatic_get_base64_from_url($vision_file);
        }
        if(!empty($base64_vision))
        {
            $xacontent = [
                [ "type" => "text", "text" => $xcopy ],
                [ "type" => "image_url", "image_url" => [ "url" => "data:image/jpeg;base64," . $base64_vision, "detail" => "low" ] ]
            ];
        }
        else
        {
            $xacontent = [
                [ "type" => "text", "text" => $xcopy ],
                [ "type" => "image_url", "image_url" => [ "url" => $vision_file, "detail" => "low" ] ]
            ];
        }
        $content = $xacontent;
    }
    $postData = [
        'role' => $role,
        'content' => $content,
        'metadata' => $metadata
    ];
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create message request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_submit_tool_outputs_to_run($token, $thread_id, $run_id, $tool_outputs) 
{
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($run_id)) {
        throw new Exception('Run ID cannot be empty');
    }
    if (empty($tool_outputs)) {
        throw new Exception('Tool outputs cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/runs/" . $run_id . "/submit_tool_outputs" . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/runs/" . $run_id . "/submit_tool_outputs";
    }
    $postData = [
        'tool_outputs' => $tool_outputs
    ];
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    $encoded = json_encode($postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP submit tool output request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_submit_tool_outputs_to_stream_run($token, $thread_id, $run_id, $tool_outputs) 
{
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($run_id)) {
        throw new Exception('Run ID cannot be empty');
    }
    if (empty($tool_outputs)) {
        throw new Exception('Tool outputs cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/runs/" . $run_id . "/submit_tool_outputs" . $api_ver;
    }
    else
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/runs/" . $run_id . "/submit_tool_outputs";
    }
    $postData = [
        'tool_outputs' => $tool_outputs,
        'stream' => true
    ];
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) 
    {
        $my_copy_data = trim($data);
        $prefix = 'data: ';
        if (substr($my_copy_data, 0, strlen($prefix)) == $prefix) {
            $my_copy_data = substr($my_copy_data, strlen($prefix));
        }
        $suffix = 'data: [DONE]';
        $needle_length = strlen($suffix);
        if (substr($my_copy_data, -$needle_length) === $suffix) {
            $my_copy_data = substr($my_copy_data, 0, -$needle_length);
        }
        $my_copy_data = trim($my_copy_data);
        $response = json_decode($my_copy_data, true);
        if (isset($response['error']) && !empty($response['error'])) 
        {
            $message = isset($response['error']['message']) && !empty($response['error']['message']) ? $response['error']['message'] : '';
            if (empty($message) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key') {
                $message = "Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.";
            }
            echo "event: message\n";
            echo 'data: {"error":[{"message":"' . $message . '"}]}';
            echo "\n\n";
            $l1 = ob_get_length();
            if($l1 === false)
            {
                $l1 = 0;
            }
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            echo 'data: {"choices":[{"finish_reason":"stop"}]}';
            echo "\n\n";
            $l2 = ob_get_length();
            if($l2 === false)
            {
                $l2 = 0;
            }
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            return $l1 + $l2;
        } 
        else 
        {
            echo $data;
            if (ob_get_length())
            {
                ob_flush();
            }
            flush();
            return strlen($data);
        }
    });
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP streamed tool output submit request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_retrieve_run($token, $thread_id, $run_id) 
{
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($run_id)) {
        throw new Exception('Run ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/runs/" . $run_id . $api_ver;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/runs/" . $run_id;
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP retrieve run request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_create_run($token, $thread_id, $assistant_id, $model = null, $instructions = null, $tools = [], $metadata = null) 
{
    if (empty($metadata)) 
    {
        $metadata = new stdClass();
    }
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($assistant_id)) {
        throw new Exception('Assistant ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/runs" . $api_ver;

        $localAzureDeployments = array();
        $depl_arr = aiomatic_get_deployments($token);
        if(is_array($depl_arr))
        {
            foreach($depl_arr as $dar)
            {
                if(empty($dar))
                {
                    continue;
                }
                if(is_string($dar))
                {
                    $localAzureDeployments[trim($dar)] = trim($dar);
                }
                else
                {
                    $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                }
            }
        }
        $azureDeployment = '';
        foreach ( $localAzureDeployments as $dmodel => $dname ) 
        {
            if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) 
            {
                $azureDeployment = $dname;
                break;
            }
        }
        if ( $azureDeployment == '' ) 
        {
            $new_dep = aiomatic_update_deployments_azure($token);
            if($new_dep !== false)
            {
                $localAzureDeployments = array();
                foreach($new_dep as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
                foreach ( $localAzureDeployments as $dmodel => $dname ) 
                {
                    if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                        $azureDeployment = $dname;
                        break;
                    }
                }
            }
            if ( $azureDeployment == '' ) 
            {
                throw new Exception('No added Azure deployment found for chat model: ' . $model . ' - you need to add this model in your Azure Portal as a Deployment');
            }
        }
        $model = $azureDeployment;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/runs";
    }
    $postData = [
        'assistant_id' => $assistant_id
    ];
    if ($model !== null) {
        $postData['model'] = $model;
    }
    if ($instructions !== null) {
        $postData['instructions'] = $instructions;
    }
    if (!empty($tools)) {
        $postData['tools'] = $tools;
    }
    if (!empty($metadata)) {
        $postData['metadata'] = $metadata;
    }
    if(isset($aiomatic_Main_Settings['assist_max_prompt_token']) && $aiomatic_Main_Settings['assist_max_prompt_token'] != '')
    {
        $postData['max_prompt_tokens'] = intval($aiomatic_Main_Settings['assist_max_prompt_token']);
    }
    if(isset($aiomatic_Main_Settings['assist_max_completion_token']) && $aiomatic_Main_Settings['assist_max_completion_token'] != '')
    {
        $postData['max_completion_tokens'] = intval($aiomatic_Main_Settings['assist_max_completion_token']);
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create run request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_create_stream_run($token, $thread_id, $assistant_id, $model = null, $instructions = null, $tools = [], $metadata = null) 
{
    if (empty($metadata)) 
    {
        $metadata = new stdClass();
    }
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($assistant_id)) {
        throw new Exception('Assistant ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/runs" . $api_ver;

        $localAzureDeployments = array();
        $depl_arr = aiomatic_get_deployments($token);
        if(is_array($depl_arr))
        {
            foreach($depl_arr as $dar)
            {
                if(empty($dar))
                {
                    continue;
                }
                if(is_string($dar))
                {
                    $localAzureDeployments[trim($dar)] = trim($dar);
                }
                else
                {
                    $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                }
            }
        }
        $azureDeployment = '';
        foreach ( $localAzureDeployments as $dmodel => $dname ) 
        {
            if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) 
            {
                $azureDeployment = $dname;
                break;
            }
        }
        if ( $azureDeployment == '' ) 
        {
            $new_dep = aiomatic_update_deployments_azure($token);
            if($new_dep !== false)
            {
                $localAzureDeployments = array();
                foreach($new_dep as $dar)
                {
                    if(empty($dar))
                    {
                        continue;
                    }
                    if(is_string($dar))
                    {
                        $localAzureDeployments[trim($dar)] = trim($dar);
                    }
                    else
                    {
                        $localAzureDeployments[trim($dar->model)] = trim($dar->id);
                    }
                }
                foreach ( $localAzureDeployments as $dmodel => $dname ) 
                {
                    if ( $dmodel === str_replace('.', '', $model) || $dmodel === $model ) {
                        $azureDeployment = $dname;
                        break;
                    }
                }
            }
            if ( $azureDeployment == '' ) 
            {
                throw new Exception('No added Azure deployment found for chat model: ' . $model . ' - you need to add this model in your Azure Portal as a Deployment');
            }
        }
        $model = $azureDeployment;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/runs";
    }
    $postData = [
        'assistant_id' => $assistant_id
    ];
    if ($model !== null) {
        $postData['model'] = $model;
    }
    if ($instructions !== null) {
        $postData['instructions'] = $instructions;
    }
    if (!empty($tools)) {
        $postData['tools'] = $tools;
    }
    if (!empty($metadata)) {
        $postData['metadata'] = $metadata;
    }
    if(isset($aiomatic_Main_Settings['assist_max_prompt_token']) && $aiomatic_Main_Settings['assist_max_prompt_token'] != '')
    {
        $postData['max_prompt_tokens'] = intval($aiomatic_Main_Settings['assist_max_prompt_token']);
    }
    if(isset($aiomatic_Main_Settings['assist_max_completion_token']) && $aiomatic_Main_Settings['assist_max_completion_token'] != '')
    {
        $postData['max_completion_tokens'] = intval($aiomatic_Main_Settings['assist_max_completion_token']);
    }
    $postData['stream'] = true;
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) 
    {
        $my_copy_data = trim($data);
        $prefix = 'data: ';
        if (substr($my_copy_data, 0, strlen($prefix)) == $prefix) {
            $my_copy_data = substr($my_copy_data, strlen($prefix));
        }
        $suffix = 'data: [DONE]';
        $needle_length = strlen($suffix);
        if (substr($my_copy_data, -$needle_length) === $suffix) {
            $my_copy_data = substr($my_copy_data, 0, -$needle_length);
        }
        $my_copy_data = trim($my_copy_data);
        $response = json_decode($my_copy_data, true);
        if (isset($response['error']) && !empty($response['error'])) 
        {
            $message = isset($response['error']['message']) && !empty($response['error']['message']) ? $response['error']['message'] : '';
            if (empty($message) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key') {
                $message = "Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.";
            }
            echo "event: message\n";
            echo 'data: {"error":[{"message":"' . $message . '"}]}';
            echo "\n\n";
            $l1 = ob_get_length();
            if($l1 === false)
            {
                $l1 = 0;
            }
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            echo 'data: {"choices":[{"finish_reason":"stop"}]}';
            echo "\n\n";
            $l2 = ob_get_length();
            if($l2 === false)
            {
                $l2 = 0;
            }
            if (ob_get_length())
            {
                ob_end_flush();
            }
            flush();
            return $l1 + $l2;
        } 
        else 
        {
            echo $data;
            if (ob_get_length())
            {
                ob_flush();
            }
            flush();
            return strlen($data);
        }
    });
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP create stream run request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_retrieve_run_step($token, $thread_id, $run_id, $step_id) 
{
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($run_id)) {
        throw new Exception('Run ID cannot be empty');
    }
    if (empty($step_id)) {
        throw new Exception('Step ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/runs/" . $run_id . "/steps/" . $step_id . $api_ver;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/runs/" . $run_id . "/steps/" . $step_id;
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP retrieve run step request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_retrieve_message($token, $thread_id, $message_id) 
{
    if (empty($thread_id)) {
        throw new Exception('Thread ID cannot be empty');
    }
    if (empty($message_id)) {
        throw new Exception('Message ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/messages/" . $message_id . $api_ver;
    }
    else
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/messages/" . $message_id;
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL session');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP retrieve message request failed with status code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return json_decode($response, true);
}
function aiomatic_openai_list_messages($token, $thread_id, $limit = 20, $order = 'desc', $after = null, $before = null) 
{
    if (empty($thread_id)) {
        throw new Exception('Thread ID is required');
    }
    $queryParams = http_build_query([
        'limit' => $limit,
        'order' => $order,
        'after' => $after,
        'before' => $before
    ]);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token,
            'OpenAI-Beta: assistants=v2'
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_assistants']) && $aiomatic_Main_Settings['azure_api_selector_assistants'] != '' && $aiomatic_Main_Settings['azure_api_selector_assistants'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_assistants'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_ASSISTANTS_API_VERSION;
        }
        $url = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/threads/' . $thread_id . "/messages?" . $queryParams . str_replace('?', '&', $api_ver);
    }
    else
    {
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token,
            "OpenAI-Beta: assistants=v2"
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $url = $api_endpoint . "/v1/threads/" . $thread_id . "/messages?" . $queryParams;
    }
    $ch = curl_init($url);
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL');
    }
    if (isset($aiomatic_Main_Settings['proxy_ai']) && $aiomatic_Main_Settings['proxy_ai'] == 'on' && isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled')
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt($ch, CURLOPT_PROXY , trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD , trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error: " . $error);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        $error = curl_error($ch);
        curl_close($ch);
        $jsdec = json_decode($response, true);
        throw new Exception("HTTP request for messages failed with code " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to decode JSON response');
    }
    return $decodedResponse;
}
?>