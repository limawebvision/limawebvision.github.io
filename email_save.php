<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber os dados da requisição POST
    $data = json_decode(file_get_contents('php://input'), true);

    // Verificar se o campo email está presente
    if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $email = $data['email'];

        // Nome do arquivo onde os emails serão salvos
        $file = 'emails.txt';

        // Adicionar o email no arquivo
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);

        // Resposta de sucesso
        http_response_code(200);
        echo json_encode(["message" => "Email salvo com sucesso."]);
    } else {
        // Resposta de erro para email inválido
        http_response_code(400);
        echo json_encode(["message" => "Email inválido."]);
    }
} else {
    // Resposta de erro para método não permitido
    http_response_code(405);
    echo json_encode(["message" => "Método não permitido."]);
}
?>
