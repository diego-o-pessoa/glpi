<?php
include("/opt/glpi/glpi_data/inc/includes.php");
global $DB;
echo "GROUPS:\n";
$grp_iterator = $DB->request(['FROM' => 'glpi_groups', 'WHERE' => ['completename' => ['LIKE', '%Ativa%']]]);
foreach ($grp_iterator as $row) {
    echo $row['id'] . " - " . $row['completename'] . "\n";
}
echo "\nENTITIES:\n";
$ent_iterator = $DB->request(['FROM' => 'glpi_entities', 'WHERE' => ['completename' => ['LIKE', '%Ativa%']]]);
foreach ($ent_iterator as $row) {
    echo $row['id'] . " - " . $row['completename'] . "\n";
}
