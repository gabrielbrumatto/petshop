<?php
require_once 'config.php';
verificarLogin();

if (isset($_GET['logout'])) {
    fazerLogout();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shop</title>
</head>
<body>
    <h1>Sistema Pet Shop</h1>
    <div>
        <p>Usuário Logado: <?php echo $_SESSION['usuario_nome'] ?>
        <a href="?logout=1">Sair</a>
        </p>
        <hr>
        <h2>Menu</h2>
        <ul>
            <li><a href="produtos.php">Cadastro de Produtos</a></li>
            <li><a href="estoque.php">Gestão de Estoque</a></li>
        </ul>
        <hr>                
    </div>
</body>
</html>