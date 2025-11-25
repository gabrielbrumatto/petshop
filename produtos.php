<?php
require_once 'config.php';
verificarLogin();

$mensagem = '';
$tipoMensagem = '';

// Buscar Categorias
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

// Processar Exclusão de Produto
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    try{
        $stmt = $conn->prepare("DELETE FROM produtos WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $mensagem = 'Produto Excluído com Sucesso';
        $tipoMensagem = 'sucesso';
    } catch (PDOException $e) {
        $mensagem = 'Erro ao excluir: ' . $e->getMessage();
        $tipoMensagem = 'erro';
    }
}

// Buscar produto para edição
$produtoEditar = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $produtoEditar = $stmt->fetch();
}

// Listar Produtos
$sql = "SELECT p.*, c.nome as categoria
       FROM produtos p
       INNER JOIN categorias c ON p.categoria_id = c.id
       WHERE p.ativo = 1
       ORDER BY p.nome";
$produtos = $conn->query($sql)->fetchALL();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos - Pet Shop</title>
</head>
<body>
    <h1>Cadastro de Produtos - Petshop</h1>
    <p><a href="index.php">⬅Voltar</a></p>
    <hr>

    <?php if (!empty($mensagem)): ?>
        <p style="color: <?php echo $tipoMensagem == 'sucesso' ? 'green' : 'red';  ?>">
            <?php echo $mensagem ?>
        </p>
        <hr>
    <?php endif; ?>

    <!-- Formulário de Edição e Cadastro de Produtos -->
    <h2><?php echo $produtoEditar ? 'Editar' : 'Novo'; ?> Produto</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $produtoEditar['id'] ?? 0; ?>">

        <table border="1">
            <tr>
                <td><label>Código: *</label></td>
                <td><input type="text" name="codigo" value="<?php echo $produtoEditar['codigo'] ?? ''; ?>" required></td>
            </tr>
            <tr>
                <td><label>Nome: *</label></td>
                <td><input type="text" name="nome" size="40" value="<?php echo $produtoEditar['nome'] ?? ''; ?>" required></td>
            </tr>
            <tr>
                <td><label>Categoria: *</label></td>
                <td>
                    <select name="categoria_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo ($produtoEditar['categoria_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label>Preço de Venda: *</label></td>
                <td><input type="number" name="preco" value="<?php echo $produtoEditar['preco_venda'] ?? ''; ?>" required></td>
            </tr>
            <tr>
                <td><label>Estoque Atual:</label></td>
                <td><input type="number" name="estoque_atual" min="0" value="<?php echo $produtoEditar['estoque_atual'] ?? 0; ?>"></td>
            </tr>
            <tr>
                <td><label>Estoque Mínimo:</label></td>
                <td><input type="number" name="estoque_minimo" min="0" value="<?php echo $produtoEditar['estoque_minimo'] ?? 5; ?>"></td>
            </tr>
        </table>

        <p>
            <button type="submit"><?php echo $produtoEditar ? 'Atualizar' : 'Cadastrar'; ?></button>
            <?php if ($produtoEditar): ?>
                <a href="produtos.php"><button type="button">Cancelar</button></a>
            <?php endif; ?>
        </p>
    </form>

    <hr>

    <h2>Lista de Produtos</h2>
    <?php if (count($produtos) > 0 ): ?>
    <table border="1">
        <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Categotia</th>
            <th>Preço</th>
            <th>Estoque</th>
            <th>Estoque Mínimo</th>
            <th>Ações</th>
        </tr>
        <?php foreach($produtos as $p): ?>
        <tr>
            <td><?php echo $p['codigo'] ?></td>
            <td><?php echo $p['nome'] ?></td>
            <td><?php echo $p['categoria'] ?></td>
            <td><?php echo $p['preco_venda'] ?></td>
            <td><?php echo $p['estoque_atual'] ?></td>
            <td><?php echo $p['estoque_minimo'] ?></td>
            <td>
                <a href="produtos.php?editar=<?php echo $p['id'] ?>">Editar</a>
                <a href="produtos.php?excluir=<?php echo $p['id'] ?>"
                onclick="return confirm('Deseja realmente excluir este produto?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>Nenhum produto cadastrado.</p>
    <?php endif; ?>
</body>
</html>