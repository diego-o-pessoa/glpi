<?php
include('/var/www/html/glpi/inc/includes.php');
global $DB;
$result = $DB->request("DESCRIBE glpi_users");
foreach ($result as $row) {
    if (in_array($row['Field'], ['locations_id', 'groups_id', 'usercategories_id', 'usertitles_id'])) {
        echo "User Field: " . $row['Field'] . "\n";
    }
}
$result = $DB->request("DESCRIBE glpi_groups_users");
if ($result) {
    echo "glpi_groups_users exists\n";
}
