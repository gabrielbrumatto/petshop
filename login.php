<?php
require_once 'config.php';
iniciarSessao();

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$mensagemErro = "";

// Processa o LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $mensagemErro = 'Por favor, preencha todos os campos!';
    } else {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                $mensagemErro = 'Email ou senha incorretos!';
            } elseif ($usuario['ativo'] != 1) {
                $mensagemErro = 'Usuário inativo. Entre em contato com o administrador!';
            } elseif (password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                header('Location: index.php');
                exit;
            } else {
                $mensagemErro = 'Email ou senha incorretos!';
            }
            
        } catch (PDOException $e) {
            $mensagemErro = 'Erro ao processar login: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pet Shop</title>
</head>
<body>
    <h1>Sistema Pet Shop - LOGIN</h1>

    <?php
    if (!empty($mensagemErro)) {
        echo $mensagemErro;
    }
    ?>

    <form action="" method="post">
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" require>
        </div>
        <br>
        <div>
            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" require>
        </div>
        <br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>