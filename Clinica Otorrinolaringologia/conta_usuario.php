<?php
session_start();

// Verificação do login do usuário
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Inclui o arquivo que conecta aos bancos de dados
include('db.php');

$usuario_id = $_SESSION['usuario_id'];

// Busca de informações do usuário no banco UsuariosDB
$sql_usuario = "SELECT nome_completo, cpf, username, criado_em FROM usuarios WHERE id = :usuario_id";
$stmt_usuarios = $pdoUsuarios->prepare($sql_usuario);
$stmt_usuarios->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt_usuarios->execute();
$usuario = $stmt_usuarios->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário foi encontrado
if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

// Atualização dos dados do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $novo_nome = trim($_POST['nome_completo']);
    $novo_cpf = trim($_POST['cpf']);
    $novo_username = trim($_POST['username']);

    $sql_update = "UPDATE usuarios SET nome_completo = :nome_completo, cpf = :cpf, username = :username WHERE id = :usuario_id";
    $stmt_update = $pdoUsuarios->prepare($sql_update);
    $stmt_update->bindParam(':nome_completo', $novo_nome, PDO::PARAM_STR);
    $stmt_update->bindParam(':cpf', $novo_cpf, PDO::PARAM_STR);
    $stmt_update->bindParam(':username', $novo_username, PDO::PARAM_STR);
    $stmt_update->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

    if ($stmt_update->execute()) {
        echo "<script>alert('Dados atualizados com sucesso!'); window.location.href = 'conta_usuario.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar os dados.');</script>";
    }
}

// Exclusão do usuário e consultas relacionadas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        $pdoConsultas->beginTransaction();
        $pdoUsuarios->beginTransaction();

        // Excluir consultas relacionadas ao CPF do usuário
        $sql_delete_consultas = "DELETE FROM consultas WHERE cpf = :cpf";
        $stmt_delete_consultas = $pdoConsultas->prepare($sql_delete_consultas);
        $stmt_delete_consultas->bindParam(':cpf', $usuario['cpf'], PDO::PARAM_STR);
        $stmt_delete_consultas->execute();

        // Excluir usuário do banco UsuariosDB
        $sql_delete_usuario = "DELETE FROM usuarios WHERE id = :usuario_id";
        $stmt_delete_usuario = $pdoUsuarios->prepare($sql_delete_usuario);
        $stmt_delete_usuario->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt_delete_usuario->execute();

        $pdoConsultas->commit();
        $pdoUsuarios->commit();

        // Destrói a sessão e redireciona para a página inicial
        session_destroy();
        echo "<script>alert('Conta excluída com sucesso!'); window.location.href = 'home.php';</script>";
    } catch (Exception $e) {
        $pdoConsultas->rollBack();
        $pdoUsuarios->rollBack();
        echo "<script>alert('Erro ao excluir a conta: {$e->getMessage()}');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
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
            </ul>
        </nav>
        </header>
        <main>
            <section>
                <h2>Dados do Usuário</h2>
                <form method="POST" action="">
                    <label for="nome_completo">Nome Completo:</label>
                    <input type="text" id="nome_completo" name="nome_completo" value="<?php echo htmlspecialchars($usuario['nome_completo']); ?>" required>
                    <br>
                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($usuario['cpf']); ?>" required>
                    <br>
                    <label for="username">Nome de Usuário:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
                    <br>
                    <button type="submit" name="update_user">Salvar Alterações</button>
                </form>
            </section>

            <section>
                <h2>Excluir Conta</h2>
                <form method="POST" action="" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')">
                    <button type="submit" name="delete_account" style="color: red;">Excluir Conta</button>
                </form>
            </section>
        </main>
    </div>

    <footer>
        <p>© 2024 Clínica Otoh</p>
    </footer>
</body>
</html>
