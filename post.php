<?php

require_once('config.php');

//=======================================================================
function postIt($url, $data) {
    $url_parts = parse_url($url);
    $host = $url_parts["host"];
    $port = ($url_parts["port"]) ? $url_parts["port"] : 80;
    $path = $url_parts["path"];
    
    $timeout = 10;
    $contentLength = strlen($data);

    // Generate the request header
    $request =
      "POST $path HTTP/1.0\n".
      "Host: $host\n".
      "User-Agent: PostIt\n".
      "Content-Type: Content-type:application/json\n".
      "Content-Length: $contentLength\n\n".
      "$data\n";

    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);

    fputs( $fp, $request );
    if ($fp) {
        while (!feof($fp)){
            $result .= fgets($fp, 4096);
        }
    }
    return $result;
}


$title = $_REQUEST['title'];
$site = $_REQUEST['site'];
$type = $_REQUEST['type'];
$timestamp = date("Y-m-d\Th:i:s\Z");

$params = array('add'=> array('doc'=> array("date" => $timestamp, "title" => $title, "site" => $site, "type" => $type)));

$json_update_command = json_encode($params);

$solr_url = $config['solr_url'] . $config['solr_index'] . '/update/json';

$result = postIt($solr_url, $json_update_command);

echo $result;

?>
