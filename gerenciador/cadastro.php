<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="css/cadastro.css">
</head>
<body>

    <div class="cadastro-container">
        <h1>Cadastro</h1>

        <div id="msg-sucesso" class="mensagem-sucesso">
            Cadastro realizado! Redirecionando para o login...
        </div>
        <div id="msg-erro" class="mensagem-erro"></div>

        <form id="form-cadastro">

            <div class="campo">
                <label for="nome">Nome</label>
                <input
                    type="text"
                    id="nome"
                    placeholder="Digite seu nome"
                    required>
            </div>

            <div class="campo">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    placeholder="Digite seu e-mail"
                    required>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    id="senha"
                    placeholder="Digite sua senha"
                    required>
            </div>

            <div class="campo">
                <label for="confirmar-senha">Confirmar Senha</label>
                <input
                    type="password"
                    id="confirmar-senha"
                    placeholder="Confirme sua senha"
                    required>
            </div>

            <button type="submit" id="btn-cadastrar">Cadastrar</button>

        </form>

        <div class="link-login">
            Já tem conta? <a href="login.php">Fazer login</a>
        </div>
    </div>

    <script>
        const API = 'http://localhost:8080';

        document.getElementById('form-cadastro').addEventListener('submit', async function(e) {
            e.preventDefault();

            const nome           = document.getElementById('nome').value.trim();
            const email          = document.getElementById('email').value.trim();
            const senha          = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar-senha').value;
            const btn            = document.getElementById('btn-cadastrar');
            const msgErro        = document.getElementById('msg-erro');
            const msgSucesso     = document.getElementById('msg-sucesso');

            msgErro.style.display    = 'none';
            msgSucesso.style.display = 'none';

            if (senha !== confirmarSenha) {
                msgErro.textContent   = 'As senhas não coincidem.';
                msgErro.style.display = 'block';
                return;
            }

            if (senha.length < 6) {
                msgErro.textContent   = 'A senha deve ter pelo menos 6 caracteres.';
                msgErro.style.display = 'block';
                return;
            }

            btn.textContent = 'Cadastrando...';
            btn.disabled    = true;

            try {
                const res = await fetch(`${API}/usuarios`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome, email, senha })
                });

                const data = await res.json();

                if (res.ok) {
                    msgSucesso.style.display = 'block';
                    setTimeout(() => window.location.href = 'login.php', 1500);
                } else {
                    msgErro.textContent   = data.erro || 'Erro ao cadastrar.';
                    msgErro.style.display = 'block';
                    btn.textContent = 'Cadastrar';
                    btn.disabled    = false;
                }
            } catch (err) {
                msgErro.textContent   = 'Erro ao conectar com a API.';
                msgErro.style.display = 'block';
                btn.textContent = 'Cadastrar';
                btn.disabled    = false;
            }
        });
    </script>

</body>
</html>
