<?php
include("/var/www/html/glpi/inc/includes.php");
$user = new User();
$search = new Search();
$params = [
    'itemtype' => 'User',
    'start'    => 0,
    'is_deleted' => 0
];
$data = Search::getDatas($params);
echo "Count: " . count($data['data']) . "\n";
print_r($data['data']);
