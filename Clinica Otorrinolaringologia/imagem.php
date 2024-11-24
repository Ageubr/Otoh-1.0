<?php
// Conexão com o banco de dados
$conn = pg_connect("host=localhost dbname=ConsultasDB user=postgres password=3561");

if (!$conn) {
    die("Erro ao conectar ao banco de dados.");
}

// ID fixo para teste
$id_consulta = 7;

// Recuperar o arquivo do banco
$query = "SELECT historico FROM consultas WHERE id = $1";
$result = pg_query_params($conn, $query, [$id_consulta]);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $arquivo_bytea = $row['historico'];

    // Decodificar o BYTEA para binário
    $arquivo_binario = pg_unescape_bytea($arquivo_bytea);

    if ($arquivo_binario) {
        // Detectar o tipo do arquivo
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($file_info, $arquivo_binario);
        finfo_close($file_info);

        // Enviar o arquivo com o cabeçalho correto
        header("Content-Type: $mime_type");
        header('Content-Disposition: inline; filename="arquivo_recuperado"'); // Nome ajustado
        echo $arquivo_binario;
    } else {
        echo "Erro ao decodificar o arquivo.";
    }
} else {
    echo "Nenhum arquivo encontrado para o ID fornecido.";
}
?>