<?php
include ("inc/includes.php");
global $DB;

$res = $DB->request('glpi_itilcategories');
echo "ID | NAME | PARENT_ID | COMPLETENAME\n";
foreach($res as $row) {
    echo $row['id'] . " | " . $row['name'] . " | " . $row['itilcategories_id'] . " | " . $row['completename'] . "\n";
}
