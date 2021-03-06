<?php
require_once('user/models/db-settings.php');
require_once('config.php');
require_once('user/models/funcs.php');


function get_source_ids($data) {
    $source_ids = array();
    foreach($data as $d) {
        $source_ids[] = array('source_id'=>$d['source_id'], 'org_id'=>$d['organization_id'], 'token'=>$d['auth_string'],
                              'campaign_id'=>$d['campaign_id']);
    }
    return $source_ids;
}


function prepare_request_url($org_id, $source_id) {
    $url = 'http://api.integrate.com/api/organizations/'.$org_id.'/contracts?query='.$source_id;
    return $url;
}


function prepare_header($auth_token) {
    $header = "accept: application/json\r\n"."Authorization: ".$auth_token." \r\n";
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

//$offset = 0;
//$records_limit = 20;

//$campaign_stats_data = fetchAllCampaignsStats($offset, $records_limit);

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

    foreach($source_ids as $k=>$source_id) {
        if (!in_array($source_id['campaign_id'], $cached_campaign_ids)) {
            $stats_res[$source_id['campaign_id']] = stats_query_execute($source_id);
        }
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

//$result = combineCampaignsStats($campaign_stats_data);

//print_r($result);

?>