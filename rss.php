<?php
header('Content-Type: text/xml; charset=utf-8');

require_once('config.php');
ini_set("allow_url_fopen","1");

function sanitize_title($title){
    $title = strtolower($title);
    $title = remove_accents($title);
        
    $title = preg_replace('/&.+?;/', '', $title); // kill entities
    $title = str_replace('.', '-', $title);
    $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');

    return $title;

}
function remove_accents($string){
    return strtr($string, array('À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A','Æ' => 'AE','Ç' => 'C','È' => 'E', 'É' => 'E','Ê' => 'E','Ë' => 'E','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I','Ð' => 'D','Ñ' => 'N','Ò' => 'O','Ó' => 'O','Ô' => 'O','Õ' => 'O','Ö' => 'O','Ø' => 'O','Ù' => 'U','Ú' => 'U','Ü' => 'U','Û' => 'U','Ý' => 'Y','Þ' => 'Th','ß' => 'ss','à' => 'a','á' => 'a','â' => 'a','ã' => 'a','ä' => 'a','å' => 'a','æ' => 'ae','ç' => 'c','è' => 'e','é' => 'e','ê' => 'e','ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ð' => 'd','ñ' => 'n','ò' => 'o','ó' => 'o','ô' => 'o','õ' => 'o','ö' => 'o','ø' => 'o','ù' => 'u','ú' => 'u','û' => 'u','ü' => 'u','ý' => 'y','þ' => 'th','ÿ' => 'y','Œ' => 'OE','œ' => 'oe'));
}

$date = $_GET['date'];
$type = $_GET['type'];
$site = $_GET['site'];

if (!isset($site) || !isset($type)){
    die("Insuficient parameters");
}
if (!isset($date) || $date == 'today'){
    $range_date = 'date:[' . date("Y-m-d\T00:00:00\Z") . ' TO ' . date("Y-m-d\T23:59:59\Z") . ']';
}else if ($date == 'week'){
    $range_date = 'date:[NOW-7DAYS/DAY TO NOW/DAY+1DAY]';
}else if ($date == 'month'){    
    $range_date = 'date:[NOW-30DAYS/DAY TO NOW/DAY+1DAY]';
}else{    
    $range_date = 'date:[' . $date . 'T00:00:00Z' . ' TO ' . $date . 'T23:59:59Z]';
}    

$type_filter = 'type:' . $type;

//date:[2011-04-26T00:00:00Z TO 2011-04-26T23:59:00Z]
$query = '?q=' . $type_filter . '%20AND%20' . urlencode($range_date) . '&wt=php&rows=0';

$request_url = $config['solr_url'] . $config['solr_index'] . '/select/' . $query;

$response = @file_get_contents($request_url);
eval("\$result = " . $response . ";");

$title_list = $result['facet_counts']['facet_fields']['title'];
    
?>
<rss version="2.0">
    <channel>
        <title>Metrics Feed</title>
        <link>http://www.example.com/</link>
        <lastBuildDate><?php echo date(DATE_RSS) ?></lastBuildDate>
        
        <?php foreach($title_list as $title => $count) :?>
            <?php $link =  'http://' . $site . '/' . sanitize_title($title) . '/'; ?>
            <item>
                <title><?php echo $title?></title>
                <link><?php echo $link ?></link>
                <guid><?php echo $link ?></guid>                
                <description>Visualizado <?php echo $count ?> vezes</description>
            </item>
        <?php endforeach; ?>

</channel>
</rss>
