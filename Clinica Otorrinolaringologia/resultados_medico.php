<?php
session_start();

// Verifica se o usuário tem acesso privilegiado
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include('db.php');
$pdoConsultas = conectarBanco('ConsultasDB');

// Manipulação do formulário para salvar os resultados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consulta_id = $_POST['consulta_id'];
    $resultado_consulta = $_POST['resultado_consulta'];
    $exame_file = null;

    // Verifica se há um arquivo enviado
    if (isset($_FILES['exame_file']) && $_FILES['exame_file']['error'] === UPLOAD_ERR_OK) {
        $exame_file = file_get_contents($_FILES['exame_file']['tmp_name']);
    }

    $sql = "UPDATE consultas 
            SET resultado_consulta = :resultado_consulta, exame_file = :exame_file 
            WHERE id = :consulta_id";
    $stmt = $pdoConsultas->prepare($sql);
    $stmt->bindParam(':resultado_consulta', $resultado_consulta);
    $stmt->bindParam(':exame_file', $exame_file, PDO::PARAM_LOB);
    $stmt->bindParam(':consulta_id', $consulta_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Resultado salvo com sucesso!'); window.location.href = 'home.php';</script>";
    } else {
        echo "<script>alert('Erro ao salvar os resultados.');</script>";
    }
}

// Carrega as consultas existentes para exibição
$sql_consultas = "SELECT id, nome, data_consulta, especialidade FROM consultas";
$consultas = $pdoConsultas->query($sql_consultas)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incluir Resultados de Consultas</title>
    <link rel="stylesheet" href="./styles.css" />
</head>
<body>
    <header>   
    <img src="./images/logo.png" alt="Logo da Página" class="logo">

    <nav>
            <ul>
                <li><a href="./home.php">Início</a></li>
                <li><a href="./pages/treatments.html">Tratamentos</a></li>
                <li><a href="./pages/doctors.html">Médicos</a></li>
                <li><a href="./pages/contact.html">Contato</a></li>
                <li><a href="./agendar_consulta.php">Consultas</a></li>
                <li><a href="./consulta_handler.php">Agendamentos</a></li>
                <li><a href="./resultados_usuario.php">Resultados Exames</a></li>
                <li><a href="./resultados_medico.php">Área Médica</a></li>
            </ul>
        </nav>
    </header>
    <form method="POST" enctype="multipart/form-data">
        <label for="consulta_id">Selecione a Consulta:</label>
        <select name="consulta_id" id="consulta_id" required>
            <?php foreach ($consultas as $consulta): ?>
                <option value="<?php echo $consulta['id']; ?>">
                    <?php echo "{$consulta['nome']} - {$consulta['data_consulta']} - {$consulta['especialidade']}"; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="resultado_consulta">Resultado da Consulta:</label>
        <textarea id="resultado_consulta" name="resultado_consulta" rows="4" required></textarea>
        <br>
        <label for="exame_file">Anexar Arquivo do Exame:</label>
        <input type="file" id="exame_file" name="exame_file" accept=".pdf,.jpg,.png">
        <br>
        <button type="submit">Salvar Resultado</button>
    </form>
</body>
</html>
