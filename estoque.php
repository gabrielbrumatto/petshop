<?php
require_once 'config.php';
verificarLogin();

$mensagem = '';
$tipoMensagem = '';

// Processar movimentação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = (int) ($_POST['produto_id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $quantidade = (int) ($_POST['quantidade'] ?? 0);
    $data = $_POST['data'] ?? '';

    if ($produto_id == 0 || empty($tipo) || $quantidade <= 0 || empty($data)) {
        $mensagem = 'Preencha todos os campos!';
        $tipoMensagem = 'erro';
    } else {
        try {
            // Buscar estoque atual
            $sql = "SELECT * FROM produtos WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $produto_id]);
            $produto = $stmt->fetch();

            if (!$produto) {
                $mensagem = 'Produto não encontrado!';
                $tipoMensagem = 'erro';
            } else {
                $estoque_anterior = $produto['estoque_atual'];

                // Calcular novo estoque
                if ($tipo === 'ENTRADA') {
                    $estoque_novo = $estoque_anterior + $quantidade;
                } else {
                    $estoque_novo = $estoque_anterior - $quantidade;
                    if ($estoque_novo < 0) {
                        $mensagem = 'Estoque insuficiente!';
                        $tipoMensagem = 'erro';
                    }
                }

                if (empty($mensagem)) {
                    $conn->beginTransaction();

                    // Registrar movimentação
                    $sql = "INSERT INTO movimentacoes 
                           (produto_id, usuario_id, tipo_movimentacao, quantidade, data_movimentacao, 
                            estoque_anterior, estoque_posterior)
                           VALUES (:produto_id, :usuario_id, :tipo, :quantidade, :data, :ant, :post)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        'produto_id' => $produto_id,
                        'usuario_id' => $_SESSION['usuario_id'],
                        'tipo' => $tipo,
                        'quantidade' => $quantidade,
                        'data' => $data,
                        'ant' => $estoque_anterior,
                        'post' => $estoque_novo
                    ]);

                    // Atualizar estoque
                    $sql = "UPDATE produtos SET estoque_atual = :estoque WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['estoque' => $estoque_novo, 'id' => $produto_id]);

                    $conn->commit();
                    $mensagem = 'Movimentação registrada com sucesso!';
                    $tipoMensagem = 'sucesso';

                    //verificar se estoque está abaixo do minimo
                    if ($tipo === 'SAIDA' && $estoque_novo <= $produto['estoque_minimo']) {
                        $alertaEstoque = "ALERTA DE ESTOQUE BAIXO! O produto {$produto['nome']} estoque atual = {$estoque_novo}. Estoque mínimo = {$produto['estoque_minimo']}.";
                    }
                }
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $mensagem = 'Erro: ' . $e->getMessage();
            $tipoMensagem = 'erro';
        }
    }
}

// Buscar Produtos
$sql = "SELECT p.*, c.nome as categoria
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE p.ativo = 1 
        ORDER BY p.nome";
$produtos = $conn->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ept-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão do Estoque - Pet Shop</title>
</head>

<body>
    <h1>Gestão do Estoque - Pet Shop</h1>
    <p><a href="index.php">↩ Voltar</a></p>
    <hr>

    <?php if (!empty($mensagem)): ?>
        <p style="color: <?php echo $tipoMensagem == 'sucesso' ? 'green' : 'red'; ?>">
            <?php echo $mensagem ?>
        </p>
        <hr>
    <?php endif; ?>

    <?php if (!empty($alertaEstoque)): ?>
        <p style="color: red">
            <?php echo $mensagem ?>
        </p>
        <hr>
    <?php endif; ?>

    <h2>Nova Movimentação de Estoque</h2>
    <form method="post">
        <table border="1">
            <tr>
                <td>Produto: *</td>
                <td>
                    <select name="produto_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo $p['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tipo: *</td>
                <td>
                    <input type="radio" name="tipo" value="ENTRADA" required> Entrada
                    <input type="radio" name="tipo" value="SAIDA" required> Saída
                </td>
            </tr>
            <tr>
                <td>Quantidade: *</td>
                <td><input type="number" name="quantidade" min="1" required></td>
            </tr>
            <tr>
                <td>Data: *</td>
                <td><input type="date" name="data" value="<?php echo date('Y-m-d') ?>" required></td>
            </tr>
        </table>
        <br>
        <button type="submit">Registrar Movimentação</button>
    </form>

    <h2>Lista de Produtos</h2>
    <?php if (count($produtos) > 0): ?>
        <table border="1">
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Estoque Mínimo</th>
            </tr>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?php echo $p['codigo'] ?></td>
                    <td><?php echo $p['nome'] ?></td>
                    <td><?php echo $p['categoria'] ?></td>
                    <td><?php echo $p['preco_venda'] ?></td>
                    <td><?php echo $p['estoque_atual'] ?></td>
                    <td><?php echo $p['estoque_minimo'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum produto cadastrado.</p>
    <?php endif; ?>
</body>

</html>