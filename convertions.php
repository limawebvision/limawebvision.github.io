<?php
// show_answers.php

// Função para conectar ao banco de dados SQLite3
function connectDB() {
    $db = new SQLite3('questionnaire.db');
    return $db;
}

// Função para buscar todas as respostas agrupadas por IP
function getAnswersGroupedByIP() {
    $db = connectDB();

    $query = 'SELECT DISTINCT ip_address FROM answers';
    $results = $db->query($query);

    $answersGrouped = [];

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $ipAddress = $row['ip_address'];
        $answersGrouped[$ipAddress] = [];

        // Busca todas as respostas para este IP
        $queryAnswers = 'SELECT * FROM answers WHERE ip_address = :ip_address';
        $stmt = $db->prepare($queryAnswers);
        $stmt->bindValue(':ip_address', $ipAddress, SQLITE3_TEXT);
        $resultAnswers = $stmt->execute();

        while ($answer = $resultAnswers->fetchArray(SQLITE3_ASSOC)) {
            $answersGrouped[$ipAddress][] = $answer;
        }
    }

    $db->close();

    return $answersGrouped;
}

// Obtém todas as respostas agrupadas por IP
$answersGrouped = getAnswersGroupedByIP();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exibir Respostas do Questionário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .card {
            background-color: #1f1f1f;
            border-color: #343a40;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #343a40;
            border-color: #343a40;
            color: #ffffff;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .card-body {
            padding: 20px;
        }
        .question {
            color: #e91e63;
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .answer {
            font-size: 1rem;
            color: #fff;
            margin-bottom: 10px;
        }
        .date {
            font-size: 0.9rem;
            color: #888888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4 text-center">Respostas do Questionário</h1>
        <?php foreach ($answersGrouped as $ipAddress => $answers): ?>
            <div class="card">
                <div class="card-header">
                    IP: <?php echo $ipAddress; ?>
                </div>
                <div class="card-body">
                    <?php foreach ($answers as $answer): ?>
                        <div class="mb-3">
                            <div class="question"><?php echo str_replace('_', ' ', $answer['input_name']); ?></div>
                            <div class="answer"><?php echo $answer['input_value']; ?></div>
                            <div class="date">Data de recebimento: <?php echo date('d/m/Y H:i:s', strtotime($answer['received_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
