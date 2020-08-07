<?php
require_once('user/models/db-settings.php');
require_once('config.php');
require_once('user/models/funcs.php');

//$start_timestamp = time() ;

function get_source_ids($data) {
    $source_ids = array();
    foreach($data as $d) {
        $source_ids[] = array('source_id'=>$d['source_id'], 'org_id'=>$d['organization_id'], 'token'=>$d['auth_string'],
                              'campaign_id'=>$d['campaign_id']);
    }
    return $source_ids;
}


function prepare_request_url($org_id, $source_id) {
    $url = 'https://api.integrate.com/api/organizations/'.$org_id.'/contracts?query='.$source_id;
    return $url;
}


function prepare_header($auth_token) {
    $header = "Accept: application/json\r\n"."Authorization: ".$auth_token." \r\n";
    return $header;
}


function send_stats_request($url, $header) {
    try {
        $result = file_get_contents($url, false, stream_context_create(array(
            'http' => array(
                'method'  => 'GET',
                'header'  => $header
            )
        )));
    } catch (Exception $e) {
        $result = '';
    }
    return $result;
}


function _isCurl(){
    return curl_version();
}


function batch_get_stats_request($source_ids) {

    $ch = array();
    $response = array();

    foreach($source_ids as $k=>$source_id) {
        $headers = array();
        $headers[] = "accept: application/json";
        $headers[] = "Authorization: ".$source_id['token'];

        $stats_url = prepare_request_url($source_id['org_id'], $source_id['source_id']);

        $ch[$source_id['campaign_id']] = curl_init();
        curl_setopt($ch[$source_id['campaign_id']], CURLOPT_URL, $stats_url);
        curl_setopt($ch[$source_id['campaign_id']], CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch[$source_id['campaign_id']], CURLOPT_RETURNTRANSFER, true);
    }

    $mh = curl_multi_init();

    foreach($source_ids as $k=>$source_id) {
        curl_multi_add_handle($mh, $ch[$source_id['campaign_id']]);
    }

    //execute the multi handle
    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh);
        }
    } while ($active && $status == CURLM_OK);


    foreach($ch as $c) {
        curl_multi_remove_handle($mh, $c);
    }

    curl_multi_close($mh);

    foreach($ch as $k=>$c) {
        $request_result = curl_multi_getcontent($c);
        $response[$k] = parse_stats_response($request_result);
    }
    return $response;
}



function parse_stats_response($stats_response) {
    $result = null;
    $data = json_decode($stats_response, true);
    if (count($data['errors']) == 0) {
        $result = $data['result']['contracts'][0];
        $result = array('short_id'=>$result['shortId'], 'status'=>$result['status'], 'goal'=>$result['goal'],
            'accepted'=>$result['accepted'], 'rejected'=>$result['rejected']);
    }
    return $result;
}


function stats_query_execute($source_id) {
    $request_url = prepare_request_url($source_id['org_id'], $source_id['source_id']);
    $request_header = prepare_header($source_id['token']);
    $request_result = send_stats_request($request_url, $request_header);
    $result = parse_stats_response($request_result);
    return $result;
}

$offset = 40;
$records_limit = 20;

$campaign_stats_data = fetchAllCampaignsStats($offset, $records_limit);

function combineCampaignsStats($source_data) {
    $source_ids = get_source_ids($source_data);

    $stats_res = null;

    $campaign_ids_arr = array();
    $campaign_ids_str = '';
    foreach($source_ids as $k=>$source_id) {
        $campaign_ids_arr[] = $source_id['campaign_id'];
    }
    $campaign_ids_str = join(",",$campaign_ids_arr);

    $cached_campaign_ids = fetchCachedCampaignIds($campaign_ids_str);
    $source_ids_filtered = array();

    foreach($source_ids as $k=>$source_id) {
        if (!in_array($source_id['campaign_id'], $cached_campaign_ids)) {
//            $stats_res[$source_id['campaign_id']] = stats_query_execute($source_id);
            $source_ids_filtered[] = $source_id;
        }
    }

    if (count($source_ids_filtered) > 0) {
        $stats_res = batch_get_stats_request($source_ids_filtered);
    }

    $insert_stats = null;
    if (!is_null($stats_res)) {
        $insert_stats = insertRemoteCampaignStatsBatch($stats_res);
    }

    $cached_stats = fetchCachedCampaignStatsByCampaignIds($campaign_ids_str);

    foreach($source_data as $k=>&$sd) {
        $sd['leads_goal'] = (!is_null($cached_stats[$sd['campaign_id']]['goal'])) ? $cached_stats[$sd['campaign_id']]['goal'] : "";
        $sd['pass_count'] = (!is_null($cached_stats[$sd['campaign_id']]['accepted'])) ? $cached_stats[$sd['campaign_id']]['accepted'] : "";
        $sd['errors_count'] = (!is_null($cached_stats[$sd['campaign_id']]['rejected'])) ? $cached_stats[$sd['campaign_id']]['rejected'] : "";
        unset($sd['organization_id']);
        unset($sd['auth_string']);
    }
    return $source_data;
}

$result = combineCampaignsStats($campaign_stats_data);

print_r($result);
//print_r("\n".(time() - $start_timestamp));
//echo phpinfo();



?>