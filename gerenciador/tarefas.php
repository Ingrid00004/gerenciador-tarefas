<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Tarefas</title>
    <link rel="stylesheet" href="css/tarefas.css">
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
        .modal-overlay.ativo {
            display: flex;
        }
        .modal {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 480px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .modal h3 {
            margin-bottom: 20px;
            color: #333;
        }
        .modal .campo {
            margin-bottom: 15px;
        }
        .modal label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .modal input,
        .modal textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        .modal input:focus,
        .modal textarea:focus {
            outline: none;
            border-color: #8400ff;
        }
        .modal-acoes {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-salvar-edicao {
            background-color: #8400ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-cancelar-edicao {
            background-color: transparent;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-cancelar-edicao:hover {
            background-color: #f3e8ff;
            color: #8400ff;
        }
        .mensagem-vazia {
            color: #999;
            font-size: 14px;
            padding: 20px 0;
        }
    </style>
</head>
<body>

    <header>
        <h1>Gerenciador de Tarefas</h1>
        <div class="header-botoes">
            <a href="nova-tarefa.php">
                <button class="btn-nova-tarefa">Nova Tarefa</button>
            </a>
            <a href="login.php">
                <button class="btn-sair" onclick="sessionStorage.clear()">Sair</button>
            </a>
        </div>
    </header>

    <main>
        <h2 class="titulo-lista">Tarefas cadastradas</h2>
        <section class="lista-tarefas" id="lista-pendentes">
            <p class="mensagem-vazia">Carregando tarefas...</p>
        </section>

        <h2 class="titulo-lista concluida">Tarefas concluídas</h2>
        <section class="lista-tarefas" id="lista-concluidas">
            <p class="mensagem-vazia">Nenhuma tarefa concluída ainda.</p>
        </section>
    </main>

    <div class="modal-overlay" id="modal-edicao">
        <div class="modal">
            <h3>Editar Tarefa</h3>
            <input type="hidden" id="editar-id">
            <div class="campo">
                <label for="editar-titulo">Título</label>
                <input type="text" id="editar-titulo" placeholder="Título da tarefa">
            </div>
            <div class="campo">
                <label for="editar-descricao">Descrição</label>
                <textarea id="editar-descricao" rows="4" placeholder="Descrição da tarefa"></textarea>
            </div>
            <div class="modal-acoes">
                <button class="btn-salvar-edicao" onclick="salvarEdicao()">Salvar</button>
                <button class="btn-cancelar-edicao" onclick="fecharModal()">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        const API = 'http://localhost:8080';

        const usuario_id   = sessionStorage.getItem('usuario_id');
        const usuario_nome = sessionStorage.getItem('usuario_nome');

        if (!usuario_id) {
            window.location.href = 'login.php';
        }

        async function carregarTarefas() {
            try {
                const res = await fetch(`${API}/tarefas?usuario_id=${usuario_id}`);
                const tarefas = await res.json();

                const pendentes  = tarefas.filter(t => t.concluida == 0);
                const concluidas = tarefas.filter(t => t.concluida == 1);

                renderizarLista(pendentes,  'lista-pendentes',  false);
                renderizarLista(concluidas, 'lista-concluidas', true);
            } catch (e) {
                document.getElementById('lista-pendentes').innerHTML =
                    '<p class="mensagem-vazia">Erro ao conectar com a API. Verifique se o servidor está rodando.</p>';
            }
        }

        function renderizarLista(tarefas, idLista, concluida) {
            const lista = document.getElementById(idLista);

            if (tarefas.length === 0) {
                lista.innerHTML = concluida
                    ? '<p class="mensagem-vazia">Nenhuma tarefa concluída ainda.</p>'
                    : '<p class="mensagem-vazia">Nenhuma tarefa pendente. Adicione uma nova!</p>';
                return;
            }

            lista.innerHTML = tarefas.map(t => `
                <div class="tarefa ${concluida ? 'tarefa-concluida' : 'tarefa-pendente'}" id="tarefa-${t.id}">
                    <div class="conteudo-tarefa">
                        <h3>${escapar(t.titulo)}</h3>
                        <p>${escapar(t.descricao)}</p>
                    </div>
                    ${!concluida ? `
                    <div class="acoes">
                        <button class="btn-editar"  onclick="abrirModal(${t.id}, '${escaparAtributo(t.titulo)}', '${escaparAtributo(t.descricao)}')">Editar</button>
                        <button class="btn-concluir" onclick="concluir(${t.id})">Concluído</button>
                        <button class="btn-excluir"  onclick="excluir(${t.id})">Excluir</button>
                    </div>` : ''}
                </div>
            `).join('');
        }

        async function concluir(id) {
            await fetch(`${API}/tarefas/${id}/concluir`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ concluida: 1 })
            });
            carregarTarefas();
        }

        async function excluir(id) {
            if (!confirm('Tem certeza que deseja excluir esta tarefa?')) return;
            await fetch(`${API}/tarefas/${id}`, { method: 'DELETE' });
            carregarTarefas();
        }

        function abrirModal(id, titulo, descricao) {
            document.getElementById('editar-id').value       = id;
            document.getElementById('editar-titulo').value   = titulo;
            document.getElementById('editar-descricao').value = descricao;
            document.getElementById('modal-edicao').classList.add('ativo');
        }

        function fecharModal() {
            document.getElementById('modal-edicao').classList.remove('ativo');
        }

        async function salvarEdicao() {
            const id       = document.getElementById('editar-id').value;
            const titulo   = document.getElementById('editar-titulo').value.trim();
            const descricao = document.getElementById('editar-descricao').value.trim();

            if (!titulo) { alert('O título é obrigatório.'); return; }

            await fetch(`${API}/tarefas/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ titulo, descricao })
            });

            fecharModal();
            carregarTarefas();
        }

        document.getElementById('modal-edicao').addEventListener('click', function(e) {
            if (e.target === this) fecharModal();
        });

        function escapar(str) {
            const d = document.createElement('div');
            d.textContent = str || '';
            return d.innerHTML;
        }
        function escaparAtributo(str) {
            return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
        }

        carregarTarefas();
    </script>

</body>
</html>
