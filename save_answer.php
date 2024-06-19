<?php

header("Access-Control-Allow-Origin: https://limawebvision.github.io");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Função para conectar ao banco de dados usando PDO
function connectDB()
{
    $dbFile = 'questionnaire.db';
    $dsn = 'sqlite:' . $dbFile;
    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
    }
}

// Criação das tabelas se não existirem
function createTablesIfNeeded($pdo)
{
    $queries = [
        'CREATE TABLE IF NOT EXISTS questions_answers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question_id INTEGER,
            user_id INTEGER,
            input_name TEXT,
            input_value TEXT,
            received_at TEXT,
            FOREIGN KEY (question_id) REFERENCES questions(id),
            FOREIGN KEY (user_id) REFERENCES user_info(id)
        )',
        'CREATE TABLE IF NOT EXISTS user_info (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_hash TEXT,
            browser_info TEXT,
            ip_address TEXT,
            country TEXT,
            region TEXT,
            isp TEXT,
            currency TEXT,
            mobile INTEGER,
            proxy INTEGER,
            hosting INTEGER,
            language TEXT,
            received_at TEXT,
            CONSTRAINT unique_user UNIQUE (user_hash, ip_address)
        )'
    ];

    try {
        foreach ($queries as $query) {
            $pdo->exec($query);
        }
    } catch (PDOException $e) {
        die('Erro ao criar tabelas: ' . $e->getMessage());
    }
}

// Função para obter informações do usuário a partir do IP usando API externa
function getUserInfoFromIP($ipAddress)
{
    $apiUrl = "http://ip-api.com/json/{$ipAddress}?fields=66846719";
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);

    // Extrair informações relevantes
    $userInfo = [
        'country' => $data['country'] ?? 'N/A',
        'region' => $data['regionName'] ?? 'N/A',
        'isp' => $data['isp'] ?? 'N/A',
        'currency' => $data['currency'] ?? 'N/A',
        'mobile' => isset($data['mobile']) ? ($data['mobile'] ? 1 : 0) : 0,
        'proxy' => isset($data['proxy']) ? ($data['proxy'] ? 1 : 0) : 0,
        'hosting' => isset($data['hosting']) ? ($data['hosting'] ? 1 : 0) : 0,
        'language' => $data['language'] ?? 'N/A'
    ];

    return $userInfo;
}

// Função para inserir ou atualizar informações do usuário no banco de dados
function insertOrUpdateUserInfo($pdo, $userHash, $browserInfo, $ipAddress, $country, $region, $isp, $currency, $mobile, $proxy, $hosting, $language, $receivedAt)
{
    try {
        // Verificar se o usuário já existe na tabela user_info
        $stmt_check_user = $pdo->prepare('SELECT id FROM user_info WHERE user_hash = :user_hash AND ip_address = :ip_address');
        $stmt_check_user->bindValue(':user_hash', $userHash, PDO::PARAM_STR);
        $stmt_check_user->bindValue(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt_check_user->execute();
        $userRow = $stmt_check_user->fetch(PDO::FETCH_ASSOC);

        if (!$userRow) {
            // Inserir novo usuário na tabela user_info
            $stmt_insert_user = $pdo->prepare('INSERT INTO user_info (user_hash, browser_info, ip_address, country, region, isp, currency, mobile, proxy, hosting, language, received_at) VALUES (:user_hash, :browser_info, :ip_address, :country, :region, :isp, :currency, :mobile, :proxy, :hosting, :language, :received_at)');
            $stmt_insert_user->bindValue(':user_hash', $userHash, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':browser_info', $browserInfo, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':ip_address', $ipAddress, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':country', $country, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':region', $region, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':isp', $isp, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':currency', $currency, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':mobile', $mobile, PDO::PARAM_INT);
            $stmt_insert_user->bindValue(':proxy', $proxy, PDO::PARAM_INT);
            $stmt_insert_user->bindValue(':hosting', $hosting, PDO::PARAM_INT);
            $stmt_insert_user->bindValue(':language', $language, PDO::PARAM_STR);
            $stmt_insert_user->bindValue(':received_at', $receivedAt, PDO::PARAM_STR);
            $stmt_insert_user->execute();

            // Retornar o ID do novo usuário inserido
            return $pdo->lastInsertId();
        } else {
            // Atualizar informações existentes do usuário se necessário
            $userId = $userRow['id'];
            $stmt_update_user = $pdo->prepare('UPDATE user_info SET browser_info = :browser_info, country = :country, region = :region, isp = :isp, currency = :currency, mobile = :mobile, proxy = :proxy, hosting = :hosting, language = :language, received_at = :received_at WHERE id = :user_id');
            $stmt_update_user->bindValue(':browser_info', $browserInfo, PDO::PARAM_STR);
            $stmt_update_user->bindValue(':country', $country, PDO::PARAM_STR);
            $stmt_update_user->bindValue(':region', $region, PDO::PARAM_STR);
            $stmt_update_user->bindValue(':isp', $isp, PDO::PARAM_STR);
            $stmt_update_user->bindValue(':currency', $currency, PDO::PARAM_STR);
            $stmt_update_user->bindValue(':mobile', $mobile, PDO::PARAM_INT);
            $stmt_update_user->bindValue(':proxy', $proxy, PDO::PARAM_INT);
            $stmt_update_user->bindValue(':hosting', $hosting, PDO::PARAM_INT);
            $stmt_update_user->bindValue(':language', $language, PDO::PARAM_STR);
            $stmt_update_user->bindValue(':received_at', $receivedAt, PDO::PARAM_STR);
            $stmt_update_user->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt_update_user->execute();

            return $userId; // Retorna o ID do usuário atualizado
        }
    } catch (PDOException $e) {
        die('Erro ao inserir/atualizar informações do usuário: ' . $e->getMessage());
    }
}

// Função para inserir resposta na tabela questions_answers
function insertAnswer($pdo, $questionId, $userId, $inputName, $inputValue, $receivedAt)
{
    try {
        $stmt_insert_answer = $pdo->prepare('INSERT INTO questions_answers (question_id, user_id, input_name, input_value, received_at) VALUES (:question_id, :user_id, :input_name, :input_value, :received_at)');
        $stmt_insert_answer->bindValue(':question_id', $questionId, PDO::PARAM_INT);
        $stmt_insert_answer->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt_insert_answer->bindValue(':input_name', $inputName, PDO::PARAM_STR);
        $stmt_insert_answer->bindValue(':input_value', $inputValue, PDO::PARAM_STR);
        $stmt_insert_answer->bindValue(':received_at', $receivedAt, PDO::PARAM_STR);
        $stmt_insert_answer->execute();

        return $pdo->lastInsertId(); // Retorna o ID da resposta inserida
    } catch (PDOException $e) {
        die('Erro ao inserir resposta: ' . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputName = $_POST['inputName'] ?? '';
    $inputValue = $_POST['inputValue'] ?? '';

    $userHash = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $browserInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A';
    $receivedAt = gmdate('Y-m-d H:i:s', time() - 3 * 3600);

    $pdo = connectDB();
    createTablesIfNeeded($pdo);

    // Obter informações do usuário a partir do IP
    $userInfo = getUserInfoFromIP($ipAddress);
    $country = $userInfo['country'];
    $region = $userInfo['region'];
    $isp = $userInfo['isp'];
    $currency = $userInfo['currency'];
    $mobile = $userInfo['mobile'];
    $proxy = $userInfo['proxy'];
    $hosting = $userInfo['hosting'];
    $language = $userInfo['language'];

    // Inserir ou atualizar informações do usuário no banco de dados
    $userId = insertOrUpdateUserInfo($pdo, $userHash, $browserInfo, $ipAddress, $country, $region, $isp, $currency, $mobile, $proxy, $hosting, $language, $receivedAt);

    // Inserir resposta na tabela questions_answers
    $result = insertAnswer($pdo, 1, $userId, $inputName, $inputValue, $receivedAt); // Substituir 1 pelo ID da pergunta

    $pdo = null; // Fechar conexão com o banco de dados

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
