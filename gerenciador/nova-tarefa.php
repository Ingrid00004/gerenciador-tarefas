<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Tarefa</title>
    <link rel="stylesheet" href="css/nova.css">
    <style>
        .mensagem-sucesso,
        .mensagem-erro {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            display: none;
        }
        .mensagem-sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

    <header>
        <h1>Gerenciador de Tarefas</h1>
        <button class="btn-voltar" onclick="window.location.href='tarefas.php'">
            ← Voltar
        </button>
    </header>

    <main>
        <h2 class="titulo-lista">Cadastrar Nova Tarefa</h2>

        <form class="form-tarefa" id="form-nova-tarefa">

            <div id="msg-sucesso" class="mensagem-sucesso">
                Tarefa criada com sucesso! Redirecionando...
            </div>
            <div id="msg-erro" class="mensagem-erro">
                Erro ao criar tarefa. Verifique se a API está rodando.
            </div>

            <div class="campo">
                <label for="titulo">Título</label>
                <input
                    type="text"
                    id="titulo"
                    placeholder="Digite o título da tarefa"
                    required>
            </div>

            <div class="campo">
                <label for="descricao">Descrição</label>
                <textarea
                    id="descricao"
                    rows="5"
                    placeholder="Digite a descrição da tarefa"></textarea>
            </div>

            <div class="acoes-form">
                <button type="submit" class="btn-salvar" id="btn-salvar">
                    Salvar Tarefa
                </button>
                <button type="reset" class="btn-limpar">
                    Limpar
                </button>
            </div>

        </form>
    </main>

    <script>
        const API = 'http://localhost:8080';
        const usuario_id = sessionStorage.getItem('usuario_id');

        if (!usuario_id) {
            window.location.href = 'login.php';
        }

        document.getElementById('form-nova-tarefa').addEventListener('submit', async function(e) {
            e.preventDefault();

            const titulo    = document.getElementById('titulo').value.trim();
            const descricao = document.getElementById('descricao').value.trim();
            const btnSalvar = document.getElementById('btn-salvar');

            if (!titulo) return;

            btnSalvar.textContent = 'Salvando...';
            btnSalvar.disabled = true;

            try {
                const res = await fetch(`${API}/tarefas`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ usuario_id, titulo, descricao })
                });

                if (res.ok) {
                    document.getElementById('msg-sucesso').style.display = 'block';
                    document.getElementById('msg-erro').style.display    = 'none';
                    setTimeout(() => window.location.href = 'tarefas.php', 1500);
                } else {
                    throw new Error('Erro na API');
                }
            } catch (err) {
                document.getElementById('msg-erro').style.display    = 'block';
                document.getElementById('msg-sucesso').style.display = 'none';
                btnSalvar.textContent = 'Salvar Tarefa';
                btnSalvar.disabled = false;
            }
        });
    </script>

</body>
</html>
