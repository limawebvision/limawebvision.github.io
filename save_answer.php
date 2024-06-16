<?php
// save_answer.php

header("Access-Control-Allow-Origin: https://limawebvision.github.io");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Função para conectar ao banco de dados SQLite3
function connectDB() {
    $db = new SQLite3('questionnaire.db');
    return $db;
}

// Função para criar a tabela se ela não existir
function createTableIfNeeded($db) {
    $query = 'CREATE TABLE IF NOT EXISTS answers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_hash TEXT,
        input_name TEXT,
        input_value TEXT,
        browser_info TEXT,
        ip_address TEXT,
        language TEXT,
        received_at TEXT
    )';
    $db->exec($query);
}

// Função para inserir uma resposta no banco de dados
function insertAnswer($db, $userHash, $inputName, $inputValue, $browserInfo, $ipAddress, $language, $receivedAt) {
    $stmt = $db->prepare('INSERT INTO answers (user_hash, input_name, input_value, browser_info, ip_address, language, received_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $userHash, SQLITE3_TEXT);
    $stmt->bindValue(2, $inputName, SQLITE3_TEXT);
    $stmt->bindValue(3, $inputValue, SQLITE3_TEXT);
    $stmt->bindValue(4, $browserInfo, SQLITE3_TEXT);
    $stmt->bindValue(5, $ipAddress, SQLITE3_TEXT);
    $stmt->bindValue(6, $language, SQLITE3_TEXT);
    $stmt->bindValue(7, $receivedAt, SQLITE3_TEXT);

    $result = $stmt->execute();

    return $result;
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do POST
    $inputName = $_POST['inputName'] ?? '';
    $inputValue = $_POST['inputValue'] ?? '';

    // Dados adicionais do usuário e ambiente
    $userHash = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $browserInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
    $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A';
    $receivedAt = gmdate('Y-m-d H:i:s', time() - 3 * 3600); // Data de recebimento -3 GMT Brasil

    // Conecta ao banco de dados SQLite3
    $db = connectDB();

    // Cria a tabela se ela não existir
    createTableIfNeeded($db);

    // Insere a resposta no banco de dados
    $result = insertAnswer($db, $userHash, $inputName, $inputValue, $browserInfo, $ipAddress, $language, $receivedAt);

    // Fecha a conexão com o banco de dados
    $db->close();

    // Retorna uma resposta JSON
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Resposta recebida e salva com sucesso!',
            'inputName' => $inputName,
            'inputValue' => $inputValue
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao salvar a resposta no banco de dados.'
        ]);
    }
} else {
    // Método HTTP não permitido
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método HTTP não permitido'
    ]);
}
