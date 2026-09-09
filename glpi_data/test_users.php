<?php
include ("inc/includes.php");
global $DB;
$res = $DB->request('SHOW COLUMNS FROM glpi_users');
foreach($res as $row) {
    echo $row['Field'] . "\n";
}
