<?php
$host = 'mysql';
$db   = 'glpidb';
$user = 'glpiuser';
$pass = 'Ativ%40Adm2026';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass);
     $stmt = $pdo->query('SELECT id, name, itilcategories_id FROM glpi_itilcategories ORDER BY name');
     $output = "ID | NAME | PARENT_ID\n";
     while ($row = $stmt->fetch()) {
         $output .= $row['id'] . " | " . $row['name'] . " | " . $row['itilcategories_id'] . "\n";
     }
     file_put_contents('categories_dump.txt', $output);
} catch (\PDOException $e) {
     file_put_contents('categories_dump.txt', $e->getMessage());
}
