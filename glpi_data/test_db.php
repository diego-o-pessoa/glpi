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
     while ($row = $stmt->fetch()) {
         echo $row['id'] . " | parent: " . $row['itilcategories_id'] . " | " . $row['name'] . "\n";
     }
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
