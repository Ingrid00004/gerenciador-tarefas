<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/config/database.php';

$app->get('/tarefas', function (Request $request, Response $response) {
    $pdo = getConnection();

    $usuario_id = $request->getQueryParams()['usuario_id'] ?? null;

    if (!$usuario_id) {
        $response->getBody()->write(json_encode(['erro' => 'usuario_id obrigatório.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare('SELECT * FROM tarefas WHERE usuario_id = ? ORDER BY concluida ASC, criada_em DESC');
    $stmt->execute([$usuario_id]);
    $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($tarefas, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/tarefas', function (Request $request, Response $response) {
    $body = json_decode(file_get_contents('php://input'), true);
    $pdo = getConnection();

    if (empty($body['usuario_id'])) {
        $response->getBody()->write(json_encode(['erro' => 'usuario_id obrigatório.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare('INSERT INTO tarefas (usuario_id, titulo, descricao) VALUES (?, ?, ?)');
    $stmt->execute([$body['usuario_id'], $body['titulo'], $body['descricao'] ?? '']);

    $nova = [
        'id'         => $pdo->lastInsertId(),
        'usuario_id' => $body['usuario_id'],
        'titulo'     => $body['titulo'],
        'descricao'  => $body['descricao'] ?? '',
        'concluida'  => false
    ];
    $response->getBody()->write(json_encode($nova, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->put('/tarefas/{id}', function (Request $request, Response $response, array $args) {
    $body = json_decode(file_get_contents('php://input'), true);
    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE tarefas SET titulo = ?, descricao = ? WHERE id = ?');
    $stmt->execute([$body['titulo'], $body['descricao'] ?? '', $args['id']]);

    $response->getBody()->write(json_encode(['mensagem' => 'Tarefa atualizada'], JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->patch('/tarefas/{id}/concluir', function (Request $request, Response $response, array $args) {
    $body = json_decode(file_get_contents('php://input'), true);
    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE tarefas SET concluida = ? WHERE id = ?');
    $stmt->execute([$body['concluida'], $args['id']]);

    $response->getBody()->write(json_encode(['mensagem' => 'Status atualizado'], JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->delete('/tarefas/{id}', function (Request $request, Response $response, array $args) {
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM tarefas WHERE id = ?');
    $stmt->execute([$args['id']]);

    $response->getBody()->write(json_encode(['mensagem' => 'Tarefa removida'], JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/usuarios', function ($request, $response) {
    $body  = json_decode(file_get_contents('php://input'), true);
    $pdo   = getConnection();

    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
    $check->execute([$body['email']]);
    if ($check->fetch()) {
        $response->getBody()->write(json_encode(['erro' => 'E-mail já cadastrado.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
    }

    $senha_hash = password_hash($body['senha'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
    $stmt->execute([$body['nome'], $body['email'], $senha_hash]);

    $novo = ['id' => $pdo->lastInsertId(), 'nome' => $body['nome'], 'email' => $body['email']];
    $response->getBody()->write(json_encode($novo, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->post('/login', function ($request, $response) {
    $body = json_decode(file_get_contents('php://input'), true);
    $pdo  = getConnection();

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$body['email']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($body['senha'], $usuario['senha'])) {
        $response->getBody()->write(json_encode(['erro' => 'E-mail ou senha incorretos.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $resposta = [
        'mensagem' => 'Login realizado com sucesso.',
        'usuario'  => ['id' => $usuario['id'], 'nome' => $usuario['nome'], 'email' => $usuario['email']]
    ];
    $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});