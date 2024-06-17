<?php


header("Access-Control-Allow-Origin: https://limawebvision.github.io");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


function connectDB() {
    $db = new SQLite3('questionnaire.db');
    return $db;
}


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


function getIpAddress() {

    $ipAddress = '';


    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    elseif ($response = @file_get_contents('https://api.ipify.org?format=json')) {
        $json = json_decode($response, true);
        $ipAddress = $json['ip'] ?? '';
    }


    if (empty($ipAddress) && isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }


    if (!empty($ipAddress) && strpos($ipAddress, ':') !== false) {
        $ipV4 = @inet_ntop(@inet_pton($ipAddress));
        if ($ipV4 !== false && strpos($ipV4, ':') === false && filter_var($ipV4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ipV4;
        }
    }

    return $ipAddress;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputName = $_POST['inputName'] ?? '';
    $inputValue = $_POST['inputValue'] ?? '';


    $userHash = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $browserInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
    $ipAddress = getIpAddress();
    $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A';
    $receivedAt = gmdate('Y-m-d H:i:s', time() - 3 * 3600);


    $db = connectDB();


    createTableIfNeeded($db);


    $result = insertAnswer($db, $userHash, $inputName, $inputValue, $browserInfo, $ipAddress, $language, $receivedAt);


    $db->close();


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

    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método HTTP não permitido'
    ]);
}
?>
