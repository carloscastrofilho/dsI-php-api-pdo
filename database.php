<?php

$host = 'localhost';
$db = 'nome_do_banco_de_dados';
$port = 3306;
$user = 'nome_do_usuario';
$pass = 'senha_do_usuario';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo 'Erro na conexão com o banco de dados: ' . $e->getMessage();
    exit;
}