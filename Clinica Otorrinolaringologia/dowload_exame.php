<?php
include('db.php');
$pdoConsultas = conectarBanco('ConsultasDB');

if (isset($_GET['consulta_id'])) {
    $consulta_id = $_GET['consulta_id'];

    // Consulta o banco para obter o conteúdo do arquivo
    $sql = "SELECT exame_file FROM consultas WHERE id = :consulta_id";
    $stmt = $pdoConsultas->prepare($sql);
    $stmt->bindParam(':consulta_id', $consulta_id, PDO::PARAM_INT);
    $stmt->execute();
    $exame = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exame && !empty($exame['exame_file'])) {
        // Converte o BYTEA para string binária
        $arquivo_binario = $exame['exame_file'];

        // Se for um recurso, converte para string
        if (is_resource($arquivo_binario)) {
            $arquivo_binario = stream_get_contents($arquivo_binario);
        }

        // Decodificar o BYTEA para binário (caso necessário)
        $arquivo_binario = pg_unescape_bytea($arquivo_binario);

        if ($arquivo_binario !== false) {
            // Força o tipo MIME como PNG
            $mime_type = 'image/png';
            $file_name = "exame_" . $consulta_id . ".png";

            // Configurar cabeçalhos para download
            header("Content-Type: $mime_type");
            header("Content-Disposition: attachment; filename=\"$file_name\"");
            header('Content-Length: ' . strlen($arquivo_binario));

            // Enviar o conteúdo do arquivo
            echo $arquivo_binario;
            exit;
        } else {
            echo "Erro ao processar o arquivo binário.";
        }
    } else {
        echo "Arquivo de exame não encontrado.";
    }
} else {
    echo "ID da consulta não fornecido.";
}
?>
