<?php
defined('ABSPATH') or die();
function aiomatic_twitter_get_embed($rule_keywords, &$error)
{
    if(!function_exists('is_plugin_active'))
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    if (!is_plugin_active('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php')) 
    {
        $error = 'Twitomatic plugin not enabled';
        return false;
    }
    $twitomatic_Main_Settings = get_option('twitomatic_Main_Settings', false);
    if (!isset($twitomatic_Main_Settings['app_id']) || trim($twitomatic_Main_Settings['app_id']) == '') {
        $error = 'Twitter app id not set up in Twitomatic';
        return false;
    }
    if (!isset($twitomatic_Main_Settings['app_secret']) || trim($twitomatic_Main_Settings['app_secret']) == '') {
        $error = 'Twitter app secret not set up in Twitomatic';
        return false;
    }
    $q_max = 100;
    $feed_uri = 'https://api.twitter.com/2/tweets/search/recent?query=' . urlencode(trim($rule_keywords)) . '&max_results=' . $q_max . '&sort_order=relevancy';
    $feed_uri .= '&expansions=author_id,attachments.media_keys,entities.mentions.username,geo.place_id,in_reply_to_user_id,referenced_tweets.id,referenced_tweets.id.author_id&media.fields=preview_image_url,type,url,variants,alt_text&tweet.fields=attachments,author_id,created_at,in_reply_to_user_id,lang,referenced_tweets,source,text&user.fields=created_at,description,entities,id,location,name,profile_image_url,url,username';
    $ch  = curl_init();
    if ($ch === FALSE) {
        $error = 'failed to init curl';
        return false;
    }
    if (isset($twitomatic_Main_Settings['proxy_url']) && $twitomatic_Main_Settings['proxy_url'] != '') {
        curl_setopt($ch, CURLOPT_PROXY, $twitomatic_Main_Settings['proxy_url']);
        if (isset($twitomatic_Main_Settings['proxy_auth']) && $twitomatic_Main_Settings['proxy_auth'] != '') {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $twitomatic_Main_Settings['proxy_auth']);
        }
    }
    $access_token = get_option('twitomatic_access_token', false);
    $access_token_id = get_option('twitomatic_access_token_id', false);
    $access_token_secret = get_option('twitomatic_access_token_secret', false);
    if ((isset($twitomatic_Main_Settings['new_token']) && $twitomatic_Main_Settings['new_token'] == 'on') || $access_token === false || $access_token_id === false || $access_token_secret === false || $access_token_id != trim($twitomatic_Main_Settings['app_id']) || $access_token_secret != trim($twitomatic_Main_Settings['app_secret'])) {
        $url='https://api.twitter.com/oauth2/token';
        $urlenc = urlencode(trim($twitomatic_Main_Settings['app_id'])) . ':'. urlencode(trim($twitomatic_Main_Settings['app_secret']));
        $base64urlenc = base64_encode($urlenc);
        $ch  = curl_init();
        if ($ch === FALSE) {
            $error = 'failed to init curl in auth';
            return false;
        }
        if (isset($twitomatic_Main_Settings['proxy_url']) && $twitomatic_Main_Settings['proxy_url'] != '') {
            curl_setopt($ch, CURLOPT_PROXY, $twitomatic_Main_Settings['proxy_url']);
            if (isset($twitomatic_Main_Settings['proxy_auth']) && $twitomatic_Main_Settings['proxy_auth'] != '') {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $twitomatic_Main_Settings['proxy_auth']);
            }
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization:Basic " . $base64urlenc, "Content-Type:application/x-www-form-urlencoded;charset=UTF-8."));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $exec=curl_exec($ch);
        if ($exec === FALSE) {
            curl_close($ch);
            $error = 'failed to exec curl';
            return false;
        }
        curl_close($ch);
        if(stristr($exec, 'bearer')){
            $token_json = json_decode($exec);
            $access_token = $token_json->access_token;

            if(trim($access_token) == ''){
                $error = 'failed to get access token';
            }else{
                aiomatic_update_option('twitomatic_access_token', $access_token);
                aiomatic_update_option('twitomatic_access_token_id', trim($twitomatic_Main_Settings['app_id']));
                aiomatic_update_option('twitomatic_access_token_secret', trim($twitomatic_Main_Settings['app_secret']));
            }
        }else{
            $error = 'failed to get bearer token';
            return false;
        }
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer " . $access_token));
    curl_setopt($ch, CURLOPT_URL, $feed_uri);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $exec=curl_exec($ch);
    if ($exec === FALSE) {
        curl_close($ch);
        $error = 'failed to access api';
        return false;
    }
    curl_close($ch);
    if ($exec == '[]') {
        $error = 'no results returned';
        return false;
    }
    if (stristr($exec, 'data') === FALSE) {
        $error = 'failed to get data, rate limited';
        return false;
    }
    $json  = json_decode($exec);
    if($json === false)
    {
        $error = 'failed to decode json';
        return false;
    }
    $items = $json->data;
    if (count($items) == 0) {
        $error = 'no results returned for query';
        return false;
    }
    shuffle($items);
    $arrx = array_reverse($items);
    $item = array_pop($arrx);
    if (isset($item->full_text)) {
        $item->full_text = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$0</a>', $item->full_text);
        $item->full_text = preg_replace('/#(\w+)/u', ' <a href="https://www.twitter.com/hashtag/$1">#$1</a>', $item->full_text);
    } else {
        if(isset($item->text))
        {
            $item->full_text = $item->text;
        }
        else
        {
            $item->full_text = '';
        }
        $item->full_text = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$0</a>', $item->full_text);
        $item->full_text = preg_replace('/#(\w+)/u', ' <a href="https://www.twitter.com/hashtag/$1">#$1</a>', $item->full_text);
    }
    if(empty($item->full_text) && isset($item->retweeted_status->full_text))
    {
        $item->full_text = $item->retweeted_status->full_text;
    }
    $includes = array();
    if(isset($json->includes))
    {
        $includes = $json->includes;
    }
    $date = $item->created_at;
    $content = $item->full_text;
    $xuser_name = '';
    $url = "https://twitter.com/" . $item->author_id . "/status/" . $item->id;
    if(isset($includes->users))
    {
        foreach($includes->users as $cus)
        {
            if($cus->id == $item->author_id)
            {
                $xuser_name = $cus->name;
                break;
            }
        }
    }
    $ret_me = '<blockquote class="twitter-tweet"><p lang="en" dir="ltr">' . esc_html($content) . '</p>&mdash; ' . esc_html($xuser_name) . ' <a href="' . esc_url($url) . '?ref_src=twsrc%5Etfw">' . esc_html($date) . '</a></blockquote><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
    return $ret_me;
}
function aiomatic_instagram_get_embed($rule_keywords, &$error)
{
    if(!function_exists('is_plugin_active'))
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    if (!is_plugin_active('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php')) 
    {
        $error = 'iMediamatic plugin not enabled';
        return false;
    }
    $instamatic_Main_Settings = get_option('instamatic_Main_Settings', false);
    if (!isset($instamatic_Main_Settings['session_id']) || trim($instamatic_Main_Settings['session_id']) == '') {
        $error = 'Instagram session ID not set up in iMediamatic';
        return false;
    }
    
    $my_plugin = WP_PLUGIN_DIR . '/instamatic-instagram-post-generator';
    require_once ($my_plugin . "/res/scrape.instagram.php");
    try
    {
        $param_arr = array();
        if (isset($instamatic_Main_Settings['proxy_url']) && $instamatic_Main_Settings['proxy_url'] != '') 
        {
            if (isset($instamatic_Main_Settings['proxy_prot']) && $instamatic_Main_Settings['proxy_prot'] != '') 
            {
                $prot = $instamatic_Main_Settings['proxy_prot'];
            }
            else
            {
                $prot = 'http://';
            }
            $auth = '';
            $prx = explode(',', $instamatic_Main_Settings['proxy_url']);
            $randomness = array_rand($prx);
            $prxx = $prx[$randomness];
            if (isset($instamatic_Main_Settings['proxy_auth']) && $instamatic_Main_Settings['proxy_auth'] != '') 
            {
                $prx_auth = explode(',', $instamatic_Main_Settings['proxy_auth']);
                if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                {
                    $prxx_u = explode(':', trim($prx_auth[$randomness]));
                    if(isset($prxx_u[0]) && isset($prxx_u[1]))
                    {
                        $auth = $prxx_u[0] . ':' . $prxx_u[1] . '@';
                    }
                }
            }
            $param_arr['proxy'] = $prot . $auth . $prxx;                  
        }
        $rand_index = 0;
        $ch = curl_init();
        if($ch === false)
        {
            $error = 'Failed to init curl in Instamatic';
            return false;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 160);
        if (isset($instamatic_Main_Settings['proxy_url']) && $instamatic_Main_Settings['proxy_url'] != '') 
        {
            $prx = explode(',', $instamatic_Main_Settings['proxy_url']);
            $randomness = array_rand($prx);
            curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            if (isset($instamatic_Main_Settings['proxy_auth']) && $instamatic_Main_Settings['proxy_auth'] != '') 
            {
                $prx_auth = explode(',', $instamatic_Main_Settings['proxy_auth']);
                if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                {
                    curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]) );
                }
            }
        }
        $appsecrets = preg_split('/\r\n|\r|\n/', trim($instamatic_Main_Settings['session_id']));
        $appsecrets = array_filter($appsecrets);
        $rand_index = array_rand($appsecrets);
        $instagram = new InstagramScrape($ch, trim($appsecrets[$rand_index]));
        if (isset($instamatic_Main_Settings['ua_strings']) && $instamatic_Main_Settings['ua_strings'] != '')
        {
            $ua_strings = preg_split('/\r\n|\r|\n/', trim($instamatic_Main_Settings['ua_strings']));
            $rand_index = array_rand($ua_strings);
            if(!isset($ua_strings[$rand_index]))
            {
                if(trim($user_agent) != '')
                {
                    curl_setopt($ch, CURLOPT_USERAGENT, trim($user_agent));
                }
                else
                {
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/121.0.6167.138 Mobile/15E148 Safari/604.1');
                }
            }
            else
            {
                curl_setopt($ch, CURLOPT_USERAGENT, trim($ua_strings[$rand_index]));
            }
        }
        else
        {
            if(trim($user_agent) != '')
            {
                curl_setopt($ch, CURLOPT_USERAGENT, trim($user_agent));
            }
            else
            {
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/121.0.6167.138 Mobile/15E148 Safari/604.1');
            }
        }
        $rule_keywords = strtolower($rule_keywords);
        $rule_keywords = preg_replace('/[^a-zA-Z0-9\s]/', '', $rule_keywords);
        $words = explode(' ', $rule_keywords);
        usort($words, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        $rule_keywords = $words[0];
        $next_max_id = 0;
        try 
        {
            $jsonArr = $instagram->getItemsByHashtag ( $rule_keywords, $next_max_id );
        } 
        catch ( Exception $e ) 
        {
            $error = 'Failed to get hashtag items from Instagram: ' . $e->getMessage ();
            return false;
        }
        if (isset($jsonArr->data) && isset($jsonArr->data->hashtag) && isset($jsonArr->data->hashtag->edge_hashtag_to_media) && isset($jsonArr->data->hashtag->edge_hashtag_to_media->edges)) 
        {
            $items = $jsonArr->data->hashtag->edge_hashtag_to_media->edges;
        }
        else
        {
            $error = 'Failed to decode user items from Instagram: ' . print_r($jsonArr, true);
            return false;
        }
        if(empty($items))
        {
            $error = 'Empty response from Instagram';
            return false;
        }
        $arrx = array_reverse($items);
        $item = array_pop($arrx);
    }
    catch(Exception $e)
    {
        $error = 'Exception thrown in Instagram exec ' . esc_html($e->getMessage()) . '!' . ' Query: ' . $rule_keywords;
        return false;
    }
    if(isset($item->node) && isset($item->node->shortcode))
    {
        $url = 'https://instagram.com/p/' . $item->node->shortcode;
    }
    else
    {
        $url = 'https://instagram.com/p/' . $item->shortcode;
    }
    if(!isset($item->edge_media_to_caption) || !isset($item->edge_media_to_caption->edges) || !isset($item->edge_media_to_caption->edges[0]) || !isset($item->edge_media_to_caption->edges[0]->node) || !isset($item->edge_media_to_caption->edges[0]->node->text))
    {
        $description = '';
    }
    else
    {
        $description = $item->edge_media_to_caption->edges[0]->node->text;
    }
    $ret_me = '<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="' . $url . '" data-instgrm-version="12" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"><div style="padding:16px;"> <a href="' . $url . '" style=" background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%;" target="_blank"> <div style=" display: flex; flex-direction: row; align-items: center;"> <div style="background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 40px; margin-right: 14px; width: 40px;"></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 100px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 60px;"></div></div></div><div style="padding: 19% 0;"></div> <div style="display:block; height:50px; margin:0 auto 12px; width:50px;"><svg width="50px" height="50px" viewBox="0 0 60 60" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-511.000000, -20.000000)" fill="#000000"><g><path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.834 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.834 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.326,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.326,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.296 516.035,74.965 C517.703,76.634 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.634 565.965,74.965 C567.633,73.296 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path></g></g></g></svg></div><div style="padding-top: 8px;"> <div style=" color:#3897f0; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:550; line-height:18px;"> ' . esc_html__('View this on Instagram', 'aiomatic-automatic-ai-content-writer') . '</div></div><div style="padding: 12.5% 0;"></div> <div style="display: flex; flex-direction: row; margin-bottom: 14px; align-items: center;"><div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(0px) translateY(7px);"></div> <div style="background-color: #F4F4F4; height: 12.5px; transform: rotate(-45deg) translateX(3px) translateY(1px); width: 12.5px; flex-grow: 0; margin-right: 14px; margin-left: 2px;"></div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(9px) translateY(-18px);"></div></div><div style="margin-left: 8px;"> <div style=" background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 20px; width: 20px;"></div> <div style=" width: 0; height: 0; border-top: 2px solid transparent; border-left: 6px solid #f4f4f4; border-bottom: 2px solid transparent; transform: translateX(16px) translateY(-4px) rotate(30deg)"></div></div><div style="margin-left: auto;"> <div style=" width: 0px; border-top: 8px solid #F4F4F4; border-right: 8px solid transparent; transform: translateY(16px);"></div> <div style=" background-color: #F4F4F4; flex-grow: 0; height: 12px; width: 16px; transform: translateY(-4px);"></div> <div style=" width: 0; height: 0; border-top: 8px solid #F4F4F4; border-left: 8px solid transparent; transform: translateY(-4px) translateX(8px);"></div></div></div></a> <p style=" margin:8px 0 0 0; padding:0 4px;"> <a href="' . $url . '" style=" color:#000; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none; word-wrap:break-word;" target="_blank">' . $description . '</a></p></div></blockquote>';
    return $ret_me;
}
function aiomatic_pinterest_get_embed($rule_keywords, &$error)
{
    if(!function_exists('is_plugin_active'))
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    if (!is_plugin_active('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php')) 
    {
        $error = 'Pinterestomatic plugin not enabled';
        return false;
    }
    $pinterestomatic_Main_Settings = get_option('pinterestomatic_Main_Settings', false);
    try
    {
        $feed_uri = "https://www.pinterest.com/resource/BaseSearchResource/get/?source_url=%2Fsearch%2Fpins%2F%3Fq%3D" . urlencode(trim($rule_keywords)) . "&data=%7B%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22" . urlencode(trim($rule_keywords)). "%22%7D%2C%22context%22%3A%7B%7D%2C%22module%22%3A%7B%22name%22%3A%22SearchPage%22%2C%22options%22%3A%7B%22restrict%22%3Anull%2C%22scope%22%3A%22pins%22%2C%22constraint_string%22%3Anull%2C%22show_scope_selector%22%3Atrue%2C%22query%22%3A%22" . urlencode(trim($rule_keywords)) . "%22%7D%7D%2C%22render_type%22%3A1%2C%22error_strategy%22%3A0%7D&module_path=App()%3EHeader()%3ESearchForm()%3ETypeaheadField(enable_recent_queries%3Dtrue%2C+support_guided_search%3Dtrue%2C+resource_name%3DAdvancedTypeaheadResource%2C+name%3Dq%2C+tags%3Dautocomplete%2C+class_name%3DbuttonOnRight%2C+type%3Dtokenized%2C+prefetch_on_focus%3Dtrue%2C+value%3D%22%22%2C+input_log_element_type%3D227%2C+hide_tokens_on_focus%3Dundefined%2C+support_advanced_typeahead%3Dfalse%2C+view_type%3Dguided%2C+populate_on_result_highlight%3Dtrue%2C+search_delay%3D0%2C+search_on_focus%3Dtrue%2C+placeholder%3DDiscover%2C+show_remove_all%3Dtrue)&_=1430685210358";
        $ch               = curl_init();
        if ($ch === FALSE) 
        {
            $error = 'Failed to init curl (Pinterestomatic)';
            return false;
        }
        if (isset($pinterestomatic_Main_Settings['proxy_url']) && $pinterestomatic_Main_Settings['proxy_url'] != '') {
            curl_setopt($ch, CURLOPT_PROXY, $pinterestomatic_Main_Settings['proxy_url']);
            if (isset($pinterestomatic_Main_Settings['proxy_auth']) && $pinterestomatic_Main_Settings['proxy_auth'] != '') {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $pinterestomatic_Main_Settings['proxy_auth']);
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Referer: https://www.pinterest.com/','X-NEW-APP: 1','X-Requested-With: XMLHttpRequest'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_ENCODING , "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $feed_uri);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $exec = curl_exec($ch);
        if($exec === false)
        {
            $error = 'Failed to access Pinterest API: ' . $feed_uri;
            return false;
        }
        if (stristr($exec, 'request_identifier') === FALSE) 
        {
            $error = 'Unrecognized API response: ' . $feed_uri;
            return false;
        }
        curl_close($ch);
        $items = json_decode($exec);
        if(isset($items->resource_data_cache[0]->data->results) && count($items->resource_data_cache[0]->data->results) > 0)
        {
            $items = $items->resource_data_cache[0]->data->results;
        }
        else
        {
            if(isset($items->resource_response->data->results) && is_array($items->resource_response->data->results))
            {
                $items = $items->resource_response->data->results;
            }
            else
            {
                $items = $items->resource_response->data;
            }
        }
        if(!isset($items[0]))
        {
            $error = 'No results returned from Pinterest.';
            return false;
        }
        $arrx = array_reverse($items);
        $item = array_pop($arrx);
    }
    catch(Exception $e)
    {
        $error = 'Exception thrown in Pinterest exec ' . esc_html($e->getMessage()) . '!' . ' Query: ' . $rule_keywords;
        return false;
    }
    if(isset($item->id))
    {
        $item_id = $item->id;
    }
    else
    {
        $error = 'Cannot find item ID of pin';
        return false;
    }
    $ret_me = '<iframe src="https://assets.pinterest.com/ext/embed.html?id=' . esc_html($item_id) . '" height="445" width="345" frameborder="0" scrolling="no" ></iframe>';
    return $ret_me;
}
?>