<?php
include('db.php'); // Inclui a conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados do formulário
    $nome = $_POST['nome'];
    $idade = $_POST['idade'];
    $cpf = $_POST['cpf'];
    $telefone = $_POST['telefone'];
    $especialidade = $_POST['especialidade'];
    $data = $_POST['data'];
    $horario = $_POST['horario'];
    $pagamento = $_POST['pagamento'];

    // Validação da data (não permitir datas passadas)
    if (strtotime($data) < strtotime(date('Y-m-d'))) {
        echo "A data selecionada não pode ser no passado.";
        exit;
    }

    // Verificação de upload de arquivo (limite de 5MB, tipo PDF, JPG ou PNG)
    if (isset($_FILES['historico']) && $_FILES['historico']['error'] == 0) {
        $historico_nome = $_FILES['historico']['name'];
        $historico_tmp = $_FILES['historico']['tmp_name'];
        $historico_tipo = $_FILES['historico']['type'];
        $historico_tamanho = $_FILES['historico']['size'];

        if ($historico_tamanho > 5 * 1024 * 1024) {
            die("O arquivo é muito grande. O limite é 5MB.");
        }

        if (!in_array($historico_tipo, ['application/pdf', 'image/jpeg', 'image/png'])) {
            die("Formato de arquivo não permitido. Aceito apenas PDF, JPG e PNG.");
        }

        // Lendo o conteúdo do arquivo como binário
        $historico_conteudo = file_get_contents($historico_tmp);
    } else {
        $historico_conteudo = null; // Se não houver arquivo, armazena null
    }

    try {
        // Prepara o SQL para inserção no banco de dados
        $sql = "INSERT INTO consultas 
                (nome, idade, cpf, telefone, especialidade, data_consulta, horario, pagamento, historico) 
                VALUES (:nome, :idade, :cpf, :telefone, :especialidade, :data_consulta, :horario, :pagamento, :historico)";
        $stmt = $pdoConsultas->prepare($sql);

        // Vincula os parâmetros com o PDO
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':idade', $idade);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':especialidade', $especialidade);
        $stmt->bindParam(':data_consulta', $data);
        $stmt->bindParam(':horario', $horario);
        $stmt->bindParam(':pagamento', $pagamento);

        // Vincula o arquivo como um binário (usando PDO::PARAM_LOB)
        $stmt->bindParam(':historico', $historico_conteudo, PDO::PARAM_LOB);

        // Executa a consulta no banco
        if ($stmt->execute()) {
            echo "Consulta agendada com sucesso!";
        } else {
            echo "Erro ao agendar a consulta.";
        }
    } catch (PDOException $e) {
        die("Erro: " . $e->getMessage());
    }
}
?>

<!-- Formulário HTML -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta - Clínica Otoh</title>
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
        <section id="consulta">
            <h2>Agende sua Consulta</h2>
            <form id="consulta-form" action="agendar_consulta.php" method="POST" enctype="multipart/form-data">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>

                <label for="idade">Idade:</label>
                <input type="number" id="idade" name="idade" required>

                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" maxlength="14" required 
                       placeholder="000.000.000-00" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}">

                <label for="telefone">Telefone:</label>
                <input type="tel" id="telefone" name="telefone" required>

                <label for="especialidade">Especialidade:</label>
                <select id="especialidade" name="especialidade" required>
                    <option value="amigdalite">Amigdalite</option>
                    <option value="apneia">Apneia do Sono</option>
                    <option value="labirinto">Distúrbio do Labirinto</option>
                    <option value="faringite">Faringite</option>
                    <option value="otite">Otite</option>
                    <option value="perda_auditiva">Perda Auditiva</option>
                    <option value="rinite">Rinite</option>
                    <option value="sinusite">Sinusite</option>
                </select>

                <label for="data">Data da Consulta:</label>
                <input type="date" id="data" name="data" min="" required>

                <label for="horario">Horário da Consulta:</label>
                <select id="horario" name="horario" required>
                    <option value="08:00">08:00</option>
                    <option value="09:00">09:00</option>
                    <option value="10:00">10:00</option>
                    <option value="11:00">11:00</option>
                    <option value="12:00">12:00</option>
                    <option value="13:00">13:00</option>
                    <option value="14:00">14:00</option>
                    <option value="15:00">15:00</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                    <option value="18:00">18:00</option>
                    <option value="19:00">19:00</option>
                </select>

                <label for="pagamento">Formas de pagamentos disponíveis:</label>
                <p>Valor da consulta: R$120,00 (Realizar pagamento presencialmente!)</p>
                <select id="pagamento" name="pagamento" required>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="cartao">Cartão de crédito/débito</option>
                    <option value="pix">Pix</option>
                </select>

                <label for="historico">Histórico de Exames (máx. 5MB):</label>
                <input type="file" id="historico" name="historico" accept=".pdf,.jpg,.png">

                <button type="submit">Agendar Consulta</button>
            </form>
        </section>
    </main>

    <footer>
        <p>© 2024 Clínica Otoh. Todos os direitos reservados.</p>
    </footer>

    <script src="./script.js"></script>
</body>
</html>
