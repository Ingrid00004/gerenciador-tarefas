<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        .mensagem-erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            display: none;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1>Login</h1>

        <div id="msg-erro" class="mensagem-erro">
            E-mail ou senha incorretos.
        </div>

        <form id="form-login">

            <div class="campo">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Digite seu e-mail"
                    required>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    required>
            </div>

            <button type="submit" id="btn-entrar">Entrar</button>

        </form>

        <div class="link-cadastro">
            Não tem conta? <a href="cadastro.php">Criar cadastro</a>
        </div>
    </div>

    <script>
        const API = 'http://localhost:8080';

        document.getElementById('form-login').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email   = document.getElementById('email').value.trim();
            const senha   = document.getElementById('senha').value;
            const btn     = document.getElementById('btn-entrar');
            const msgErro = document.getElementById('msg-erro');

            msgErro.style.display = 'none';
            btn.textContent = 'Entrando...';
            btn.disabled = true;

            try {
                const res  = await fetch(`${API}/login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, senha })
                });

                const data = await res.json();

                if (res.ok) {
                    sessionStorage.setItem('usuario_id',   data.usuario.id);
                    sessionStorage.setItem('usuario_nome', data.usuario.nome);
                    window.location.href = 'tarefas.php';
                } else {
                    msgErro.textContent   = data.erro || 'E-mail ou senha incorretos.';
                    msgErro.style.display = 'block';
                    btn.textContent = 'Entrar';
                    btn.disabled = false;
                }
            } catch (err) {
                msgErro.textContent   = 'Erro ao conectar com a API.';
                msgErro.style.display = 'block';
                btn.textContent = 'Entrar';
                btn.disabled = false;
            }
        });
    </script>

</body>
</html>
