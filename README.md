Gerenciador de Tarefas
Aplicação web para gerenciar tarefas do dia a dia, com cadastro de usuários e API REST.

Requisitos:
PHP 8+
Composer
XAMPP (MySQL + phpMyAdmin)

Como rodar
1. Banco de dados
Abra o phpMyAdmin, crie um banco chamado “minha_api” e execute:

CREATE TABLE usuarios (
id        INT AUTO_INCREMENT PRIMARY KEY,
nome      VARCHAR(100) NOT NULL,
email     VARCHAR(150) NOT NULL UNIQUE,
senha     VARCHAR(255) NOT NULL,
criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tarefas (
id         INT AUTO_INCREMENT PRIMARY KEY,
usuario_id INT NOT NULL,
titulo     VARCHAR(200) NOT NULL,
descricao  TEXT,
concluida  TINYINT(1) DEFAULT 0,
criada_em  DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

2. Backend
cd minha-api
composer install
php -S localhost:8080 -t public

API disponível em “http://localhost:8080”.

3. Frontend
Abra a pasta “frontend” no navegador pelo XAMPP ou acesse diretamente:
http://localhost/frontend/cadastro.php

Estrutura do projeto
minha-api/
├── public/
│   └── index.php
├── src/
│   ├── routes.php
│   └── config/
│       └── database.php
└── vendor/
frontend/
├── cadastro.php
├── login.php
├── tarefas.php
├── nova-tarefa.php
└── css/
	  ├── cadastro.css
  	├── login.css
  	├── nova.css
  	└── tarefas.css
