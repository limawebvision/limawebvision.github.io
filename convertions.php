<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respostas do Questionário</title>
    <!-- Incluindo Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        ::-webkit-scrollbar {
            width: 10px;
            background-color: #333;
            transition: background-color 0.3s ease;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #888;
            transition: background-color 0.3s ease;
        }

        ::-webkit-scrollbar-track {
            background-color: #222;
            transition: background-color 0.3s ease;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;

            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/assets/land-bg.jpeg') center/cover no-repeat;
        }

        .wrapper {
            width: 100%;
            max-width: 1200px;
        }

        .user-container {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .user-container:hover {
            transform: translateY(-5px);
        }

        .user-info {
            font-weight: bold;
            margin-bottom: 15px;
        }

        .user-info h3 {
            color: #3498db;
            font-size: 1.8rem;
            margin-top: 0;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .user-info p {
            margin: 5px 0;
        }

        .answers-list {
            list-style-type: none;
            padding: 0;
            margin-top: 15px;
        }

        .answer-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .answer-item:last-child {
            border-bottom: none;
        }

        .answer-label {
            font-weight: bold;
            color: #3498db;
            width: 150px;
            display: inline-block;
        }

        .answer-value {
            color: #666;
        }

        .fa-icon {
            margin-right: 5px;
        }

        @media screen and (max-width: 600px) {
            .user-container {
                border-radius: 0;
                border-left: none;
                border-right: none;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">

    <?php
    // Conexão com o banco de dados usando PDO
    $dsn = 'sqlite:questionnaire.db';
    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para obter todas as respostas organizadas por usuário
        $query = 'SELECT user_info.id, user_info.user_hash, user_info.ip_address, user_info.browser_info, user_info.language,
                         user_info.country, user_info.region, user_info.currency, user_info.mobile, user_info.proxy, user_info.hosting,
                         questions_answers.input_name, questions_answers.input_value, questions_answers.received_at
                  FROM questions_answers
                  INNER JOIN user_info ON questions_answers.user_id = user_info.id
                  ORDER BY user_info.user_hash, questions_answers.received_at';
        $stmt = $pdo->query($query);

        $current_user_hash = null;

        // Loop para exibir as respostas
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Se mudou de usuário, inicia um novo container de usuário
            if ($row['user_hash'] !== $current_user_hash) {
                // Fechar o container anterior, se necessário
                if ($current_user_hash !== null) {
                    echo '</ul></div>';
                }
                // Iniciar novo container para o usuário
                echo '<div class="user-container">';
                echo '<div class="user-info">';
                echo '<h3><i class="fas fa-user fa-icon"></i>IP: ' . $row['ip_address'];
                if ($row['mobile'] == true) {
                    echo '<i class="fas fa-mobile-alt fa-icon" title="Acessando via dispositivo móvel"></i>';
                }
                echo '</h3>';
                echo '<p><i class="fas fa-globe-americas fa-icon"></i><span class="answer-label">País:</span> <span class="answer-value">' . $row['country'] . '</span></p>';
                echo '<p><i class="fas fa-map-marker-alt fa-icon"></i><span class="answer-label">Região/Estado:</span> <span class="answer-value">' . $row['region'] . '</span></p>';
                echo '<p><i class="fas fa-money-bill-wave fa-icon"></i><span class="answer-label">Moeda:</span> <span class="answer-value">' . $row['currency'] . '</span></p>';
                echo '<p><i class="fas fa-info-circle fa-icon"></i><span class="answer-label">Informações do Navegador:</span> <span class="answer-value">' . $row['browser_info'] . '</span></p>';
                echo '<p><i class="fas fa-language fa-icon"></i><span class="answer-label">Idioma:</span> <span class="answer-value">' . $row['language'] . '</span></p>';
                echo '</div>'; // fecha user-info
                echo '<ul class="answers-list">';
                $current_user_hash = $row['user_hash'];
            }

            // Exibir cada resposta dentro do container do usuário atual
            echo '<li class="answer-item">';
            echo '<p><span class="answer-label">' . str_replace('_', ' ', ucfirst($row['input_name'])) . ':</span> <span class="answer-value">' . $row['input_value'] . '</span></p>';
            echo '<p><span class="answer-label">Recebido em:</span> <span class="answer-value">' . $row['received_at'] . '</span></p>';
            echo '</li>';
        }

        // Fechar o último container de usuário
        if ($current_user_hash !== null) {
            echo '</ul></div>';
        }

        // Fechar a conexão com o banco de dados
        $pdo = null;
    } catch (PDOException $e) {
        echo '<p>Erro ao conectar ao banco de dados: ' . $e->getMessage() . '</p>';
    }
    ?>

    </div>
</body>
</html>
