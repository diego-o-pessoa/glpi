<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}
header('Content-Type: application/json');
Session::checkLoginUser();

$parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
if ($parent_id <= 0) {
    echo json_encode([]);
    exit;
}

global $DB;
$categories = [];

error_log("AJAX_CATEGORIES_DEBUG: called with parent_id = " . $parent_id);

$iterator = $DB->request([
    'SELECT' => ['id', 'name', 'itilcategories_id'],
    'FROM'   => 'glpi_itilcategories'
]);

$all_count = 0;
foreach ($iterator as $row) {
    $all_count++;
    if ($row['itilcategories_id'] == $parent_id) {
        $categories[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name'])
        ];
    }
}
error_log("AJAX_CATEGORIES_DEBUG: total categories in DB = " . $all_count . ", matched for parent = " . count($categories));

echo json_encode($categories);
exit;
