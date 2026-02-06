<?php
function aiomatic_openai_list_batches($token, $limit = 100) 
{
    $batches = [];
    $has_more = true;
    $after = null;
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_batch']) && $aiomatic_Main_Settings['azure_api_selector_batch'] != '' && $aiomatic_Main_Settings['azure_api_selector_batch'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_batch'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_BATCHES_API_VERSION;
        }
        $api_endpoint = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/batches/' . $api_ver . '&limit=' . $limit;
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token
        ];
    }
    else
    {
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
            $api_endpoint = $api_endpoint . '/v1/batches?limit=' . $limit;
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
            $api_endpoint = $api_endpoint . '/v1/batches?limit=' . $limit;
        }
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
    }
    while ($has_more) 
    {
        $url = $api_endpoint;
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
        $batches = array_merge($batches, $decodedResponse['data']);
        $has_more = $decodedResponse['has_more'];
        $after = $decodedResponse['has_more'] ? end($decodedResponse['data'])['id'] : null;
        if($after === null)
        {
            $has_more = false;
        }
        curl_close($ch);
    }
    return $batches;
}

function aiomatic_openai_retrieve_batch($token, $batch_id) 
{
    if (empty($batch_id)) {
        throw new Exception('Batch Request ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_batch']) && $aiomatic_Main_Settings['azure_api_selector_batch'] != '' && $aiomatic_Main_Settings['azure_api_selector_batch'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_batch'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_BATCHES_API_VERSION;
        }
        $api_endpoint = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/batches/' . $batch_id . $api_ver;
    }
    else
    {
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        $api_endpoint = $api_endpoint . "/v1/batches/" . $batch_id;
    }
    $url = $api_endpoint;
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
        throw new Exception("HTTP request failed with code (batch id: ' . $batch_id . ') " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to decode JSON response');
    }
    return $decodedResponse;
}
function aiomatic_openai_cancel_batch($token, $batch_id) 
{
    if (empty($batch_id)) {
        throw new Exception('Batch Request ID cannot be empty');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_check_if_azure($aiomatic_Main_Settings))
    {
        $headers = [
            'Content-Type: application/json',
            'api-key:' . $token
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_batch']) && $aiomatic_Main_Settings['azure_api_selector_batch'] != '' && $aiomatic_Main_Settings['azure_api_selector_batch'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_batch'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_BATCHES_API_VERSION;
        }
        $api_endpoint = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/batches/' . $batch_id . '/cancel' . $api_ver;
    }
    else
    {
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
        $api_endpoint = $api_endpoint . "/v1/batches/" . $batch_id . '/cancel';
    }
    $url = $api_endpoint;
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
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
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
        throw new Exception("HTTP request failed with code (batch id: ' . $batch_id . ') " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to decode JSON response');
    }
    return $decodedResponse;
}

function aiomatic_openai_save_batch($token, $aiomatic_batch_file, $aiomatic_completion_window, $aiomatic_endpoint, $metadata = null) 
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
            'api-key:' . $token
        ];
        if (isset($aiomatic_Main_Settings['azure_api_selector_batch']) && $aiomatic_Main_Settings['azure_api_selector_batch'] != '' && $aiomatic_Main_Settings['azure_api_selector_batch'] != 'default')
        {
            $api_ver = '?api-version=' . $aiomatic_Main_Settings['azure_api_selector_batch'];
        }
        else
        {
            $api_ver = AIOMATIC_AZURE_BATCHES_API_VERSION;
        }
        $api_endpoint = trailingslashit(trim($aiomatic_Main_Settings['azure_endpoint'])) . 'openai/batches/' . $api_ver;
    }
    else
    {
        if (isset($aiomatic_Main_Settings['api_selector']) && trim($aiomatic_Main_Settings['api_selector']) == 'custom' && isset($aiomatic_Main_Settings['custom_endpoint']) && trim($aiomatic_Main_Settings['custom_endpoint'], ' /') != '') 
        {
            $api_endpoint = trim($aiomatic_Main_Settings['custom_endpoint'], ' /');
        }
        else
        {
            $api_endpoint = 'https://api.openai.com';
        }
        $api_endpoint = $api_endpoint . '/v1/batches';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];
        if (isset($aiomatic_Main_Settings['openai_organization']) && $aiomatic_Main_Settings['openai_organization'] != '')
        {
            $headers['OpenAI-Organization'] = $aiomatic_Main_Settings['openai_organization'];
        }
    }
    $url = $api_endpoint;
    $postData = [
        'input_file_id' => $aiomatic_batch_file,
        'endpoint' => $aiomatic_endpoint,
        'completion_window' => $aiomatic_completion_window,
        'metadata' => $metadata
    ];
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
        throw new Exception("Response with Status Code: " . $httpCode . (isset($jsdec['error']['message']) ? ', error: ' . $jsdec['error']['message'] : ''));
    }
    curl_close($ch);
    return $jsdec;
}
?>