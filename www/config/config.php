<?php

$host = 'localhost'; // O la dirección de tu servidor de base de datos
$dbname = 'eazypay'; // Cambia por el nombre de tu base de datos
$user = 'eazypay'; // Tu usuario de la base de datos
$pass = 'eazypaypebb'; // Tu contraseña de la base de datos
$charset = 'utf8mb4'; // Codificación recomendada

$db = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($db, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
