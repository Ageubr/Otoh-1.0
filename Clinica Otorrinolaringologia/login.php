<?php 
session_start(); // Inicia a sessão

include('db.php'); // Inclui o arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf']; // Corrige o nome do campo do formulário
    $senha = $_POST['password'];

    // Conecta ao banco de dados 'UsuariosDB'
    $pdo = conectarBanco('UsuariosDB');

    // Consulta o banco de dados para verificar o CPF
    $sql = "SELECT * FROM usuarios WHERE cpf = :cpf";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica a senha usando password_verify para comparar o hash
        if (password_verify($senha, $usuario['senha'])) {
            // Armazena os dados do usuário na sessão
            $_SESSION['usuario_nome'] = $usuario['nome_completo'];
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_cpf'] = $usuario['cpf']; // Adiciona o CPF na sessão
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];  // Adiciona o nível de acesso


            header("Location: home.php"); // Redireciona para a página inicial
            exit();
        } else {
            echo "<script>alert('Senha incorreta!');</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../Clinica Otorrinolaringologia/styles.css">
</head>
<body>
    <h1>Login de Usuário</h1>
    <form id="loginForm" action="login.php" method="POST">
        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" maxlength="14" required 
               placeholder="000.000.000-00" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}">
        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" placeholder="Senha" required>
        <button type="submit">Login</button>
    </form>
    <a href="./home.php">Voltar para a Página Inicial</a>
    <script src="../script.js"></script>
</body>
</html>
