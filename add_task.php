<?php
require 'database.php';
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data[ "nome"])) {
    $nome = $data['nome'];
    $telefone = $data['telefone'];
    $observacao = $data['observacao'];

    try {
        $stmt = $conn->prepare('INSERT INTO pessoas (nome,telefone,observacao) VALUES (:nome, :telefone, :observacao)');
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':observacao', $observacao);
        $stmt->execute();
        $pessoaId = $conn->lastInsertId();
        echo json_encode(['id' => $pessoaId, 'nome' => $nome, 'telefone' => $telefone ]);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'O nome é obrigatório']);
}
