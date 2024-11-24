<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include('db.php');
$pdoConsultas = conectarBanco('ConsultasDB');

// Carrega as consultas do usuário logado
$cpf_usuario = $_SESSION['usuario_cpf']; // O CPF do usuário logado
$sql_consultas = "SELECT id, nome, data_consulta, especialidade, resultado_consulta FROM consultas WHERE cpf = :cpf";
$stmt = $pdoConsultas->prepare($sql_consultas);
$stmt->bindParam(':cpf', $cpf_usuario, PDO::PARAM_STR);
$stmt->execute();
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados das Consultas</title>
    <link rel="stylesheet" href="./styles.css">
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
    <?php if (count($consultas) > 0): ?>
        <?php foreach ($consultas as $consulta): ?>
            <div>
                <h2>Consulta: <?php echo htmlspecialchars($consulta['especialidade']); ?></h2>
                <p>Nome: <?php echo htmlspecialchars($consulta['nome']); ?></p>
                <p>Data: <?php echo htmlspecialchars($consulta['data_consulta']); ?></p>
                <p>Resultado: <?php echo nl2br(htmlspecialchars($consulta['resultado_consulta'])); ?></p>
                <form method="GET" action="./dowload_exame.php">
                    <input type="hidden" name="consulta_id" value="<?php echo $consulta['id']; ?>">
                    <button type="submit">Baixar Exame</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma consulta encontrada.</p>
    <?php endif; ?>
</body>
</html>
