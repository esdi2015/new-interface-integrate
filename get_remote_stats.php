<?php
require_once('user/models/db-settings.php');
require_once('config.php');
require_once('user/models/funcs.php');


$source_ids = array(array('source_id'=>'6670A6', 'org_id'=>'07420798-d492-4f5a-b1bf-46ec24e84708',
                          'token'=>'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJJZCI6IjA3NDIwNzk4LWQ0OTItNGY1YS1iMWJmLTQ2ZWMyNGU4NDcwOCIsIkV4cGlyZXMiOiIyMjAwLTA3LTE1VDE1OjA4OjQ5LjAzMjMxMzhaIiwiRW1haWwiOiJwZW5hZ29zQGluZnVzZW1lZGlhLmNvbSIsIkZpcnN0TmFtZSI6InBlbmFnb3NAaW5mdXNlbWVkaWEuY29tIiwiTGFzdE5hbWUiOiJwZW5hZ29zQGluZnVzZW1lZGlhLmNvbSIsIk9yZ2FuaXphdGlvbklkcyI6W10sIk9yZ2FuaXphdGlvbkNsYWltcyI6W3siT3JnYW5pemF0aW9uSWQiOiIwNzQyMDc5OC1kNDkyLTRmNWEtYjFiZi00NmVjMjRlODQ3MDgiLCJDbGFpbXMiOlsiVXNlciJdfV0sIk9yZ2FuaXphdGlvbkdyb3VwcyI6W10sIlBlcm1pdHRlZE1hcmtldGVycyI6W119.OfP4liXXDL71FcOrMmYtVCz7s1JPOaMjZdGu185RnUw'),
                    array('source_id'=>'02B7B3', 'org_id'=>'07420798-d492-4f5a-b1bf-46ec24e84708',
                          'token'=>'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJJZCI6IjA3NDIwNzk4LWQ0OTItNGY1YS1iMWJmLTQ2ZWMyNGU4NDcwOCIsIkV4cGlyZXMiOiIyMjAwLTA3LTE1VDE1OjA4OjQ5LjAzMjMxMzhaIiwiRW1haWwiOiJwZW5hZ29zQGluZnVzZW1lZGlhLmNvbSIsIkZpcnN0TmFtZSI6InBlbmFnb3NAaW5mdXNlbWVkaWEuY29tIiwiTGFzdE5hbWUiOiJwZW5hZ29zQGluZnVzZW1lZGlhLmNvbSIsIk9yZ2FuaXphdGlvbklkcyI6W10sIk9yZ2FuaXphdGlvbkNsYWltcyI6W3siT3JnYW5pemF0aW9uSWQiOiIwNzQyMDc5OC1kNDkyLTRmNWEtYjFiZi00NmVjMjRlODQ3MDgiLCJDbGFpbXMiOlsiVXNlciJdfV0sIk9yZ2FuaXphdGlvbkdyb3VwcyI6W10sIlBlcm1pdHRlZE1hcmtldGVycyI6W119.OfP4liXXDL71FcOrMmYtVCz7s1JPOaMjZdGu185RnUw'),
                    array('source_id'=>'C11B1A', 'org_id'=>'1ddd3bed-56d2-4a60-b67f-f3fc1ea31114',
                          'token'=>'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJJZCI6IjFkZGQzYmVkLTU2ZDItNGE2MC1iNjdmLWYzZmMxZWEzMTExNCIsIkV4cGlyZXMiOiIyMjAwLTA3LTE1VDIyOjI2OjM0LjgxMTYyNTVaIiwiRW1haWwiOiJwZW5hZ29zQGluZnVzZW1lZGlhLmNvbSIsIkZpcnN0TmFtZSI6InBlbmFnb3NAaW5mdXNlbWVkaWEuY29tIiwiTGFzdE5hbWUiOiJwZW5hZ29zQGluZnVzZW1lZGlhLmNvbSIsIk9yZ2FuaXphdGlvbklkcyI6W10sIk9yZ2FuaXphdGlvbkNsYWltcyI6W3siT3JnYW5pemF0aW9uSWQiOiIxZGRkM2JlZC01NmQyLTRhNjAtYjY3Zi1mM2ZjMWVhMzExMTQiLCJDbGFpbXMiOlsiVXNlciJdfV0sIk9yZ2FuaXphdGlvbkdyb3VwcyI6W10sIlBlcm1pdHRlZE1hcmtldGVycyI6W119.TA37GbAkUSCqrCrhqFbWX6owFw5gwxRzIyxTsPV4fPE'),
                    );


function get_source_ids($offset, $records_limit) {
    $source_ids = array();
    $data = fetchAllCampaignsStats($offset, $records_limit);
    foreach($data as $d) {
        $source_ids[] = array('source_id'=>$d['source_id'], 'org_id'=>$d['organization_id'], 'token'=>$d['auth_string'],
                              'campaign_id'=>$d['campaign_id']);
    }
//    print_r($source_ids);
    return $source_ids;
}


function check_campaigns_cache($source_ids) {

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

$offset = 0;
$records_limit = 20;

$source_ids = get_source_ids($offset, $records_limit);

//print_r($source_ids);

foreach($source_ids as $k=>$source_id) {
    $test_res[$source_id['campaign_id']] = stats_query_execute($source_id);
}

print_r($test_res);

$insert_stats = insertRemoteCampaignStatsBatch($test_res);

print_r($insert_stats);



?>