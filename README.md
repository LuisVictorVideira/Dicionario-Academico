# Dicionário Acadêmico

Este é um projeto de um dicionário de termos acadêmicos. Ele permite que usuários pesquisem palavras e seus significados, enquanto administradores podem gerenciar o conteúdo, adicionando, editando ou removendo palavras e organizando-as por disciplinas.

## Funcionalidades

### Usuários
- Pesquisar palavras e seus significados.
- Navegar por uma lista de palavras organizadas.

### Administradores
- Adicionar novas palavras ao dicionário.
- Editar palavras existentes.
- Excluir palavras.
- Organizar palavras por disciplinas.
- Gerenciar disciplinas (adicionar, editar e remover).

## Tecnologias Utilizadas
- **PHP**: Para o backend.
- **MySQL**: Para o banco de dados.
- **HTML/CSS/JavaScript**: Para o frontend.
- **XAMPP**: Ambiente de desenvolvimento local.

## Requisitos
- PHP 7.4 ou superior.
- MySQL 5.7 ou superior.
- Servidor local (como XAMPP, WAMP ou outro).

## Instalação

1. **Clone o repositório**:
   ```bash
   git clone https://github.com/luisvictorvideira/dicionario-academico
   cd dicionario-academico

2. Configure o banco de dados automaticamente:

  Acesse o seguinte link no navegador:

  http://localhost/dicionario/config/setup.php

3. O script criará automaticamente o banco de dados e as tabelas necessárias.
Após a execução, você verá a mensagem: Banco de dados e tabelas criados com sucesso!.

Acesse o projeto no navegador:
```
http://localhost/dicionario-academico/user/index.php
```

4. Acesse a área administrativa:
```
URL: http://localhost/dicionario-academico/admin/login.php
Usuário padrão: admin
Senha padrão: senha
```

Estrutura do Projeto
```
dicionario/
├── admin/               # Área administrativa
│   ├── add.php          # Adicionar palavras
│   ├── dashboard.php    # Painel administrativo
│   ├── delete.php       # Excluir palavras
│   ├── disciplinas.php  # Gerenciar disciplinas
│   ├── edit.php         # Editar palavras
│   ├── login.php        # Login do administrador
│   ├── logout.php       # Logout do administrador
├── assets/              # Arquivos estáticos
│   ├── css/             # Estilos CSS
│   ├── js/              # Scripts JavaScript
├── config/              # Configurações do sistema
│   ├── db.php           # Conexão com o banco de dados
│   ├── setup.php        # Script para criar o banco de dados automaticamente
│   ├── session.php      # Gerenciamento de sessão
├── user/                # Área pública do dicionário
│   ├── header.php       # Cabeçalho
│   ├── footer.php       # Rodapé
│   ├── index.php        # Página inicial
│   ├── palavra.php      # Página de detalhes da palavra
└── database.sql         # (Opcional) Script SQL para criar o banco manualmente
```
