# Painel Administrativo Bullseye

## Visão Geral

Este é o painel administrativo para o site da Bullseye, permitindo a edição de conteúdo da landing page de forma segura e eficiente. O painel foi desenvolvido com PHP e MySQL, utilizando Bootstrap para a interface de usuário.

## Funcionalidades

O painel administrativo permite editar as seguintes seções do site:

1. **Hero Section**: Editar as frases principais que aparecem na seção de destaque do site.
2. **Sobre Nós**: Editar os textos das duas colunas da seção "Sobre Nós".
3. **No que Acreditamos**: Editar os textos das duas colunas da seção "No que Acreditamos".
4. **Como Fazemos**: Editar o título e a descrição dos boxes da seção "Como Fazemos".
5. **Parceiros**: Adicionar, editar e excluir parceiros, incluindo logo, frase e URL.
6. **Rodapé**: Editar as informações de contato (email e telefone) no rodapé do site.

## Requisitos do Sistema

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache, Nginx, etc.)
- Suporte a mod_rewrite (para o Apache)

## Instalação

1. Certifique-se de que o PHP e o MySQL estão instalados e configurados corretamente.
2. Importe o banco de dados utilizando o arquivo SQL fornecido ou execute o script de configuração.
3. Configure as credenciais do banco de dados no arquivo `config.php`.
4. Acesse o painel administrativo através da URL: `http://seu-site.com/acesso/`.

## Configuração Inicial

O sistema já vem com um usuário padrão configurado:

- **Usuário**: admin
- **Senha**: admin123

**Importante**: Altere a senha padrão após o primeiro acesso por questões de segurança.

## Estrutura de Arquivos

- `index.php`: Dashboard principal do painel administrativo
- `login.php`: Página de login
- `logout.php`: Script para realizar logout
- `config.php`: Configurações do banco de dados e funções de inicialização
- `hero.php`: Gerenciamento da seção Hero
- `sobre.php`: Gerenciamento da seção Sobre Nós
- `acreditamos.php`: Gerenciamento da seção No que Acreditamos
- `como.php`: Gerenciamento da seção Como Fazemos
- `parceiros.php`: Gerenciamento de parceiros
- `rodape.php`: Gerenciamento do rodapé
- `styles.css`: Estilos do painel administrativo
- `.htaccess`: Configurações de segurança e redirecionamento

## Segurança

O painel administrativo implementa as seguintes medidas de segurança:

- Autenticação baseada em sessão
- Proteção contra SQL Injection
- Proteção contra Cross-Site Scripting (XSS)
- Senhas armazenadas com hash e salt
- Proteção contra listagem de diretórios
- Restrição de acesso a arquivos sensíveis

## Backup

Recomenda-se realizar backups regulares do banco de dados e dos arquivos do site. O backup pode ser feito através do phpMyAdmin ou utilizando ferramentas de linha de comando do MySQL.

## Suporte

Para suporte ou dúvidas sobre o painel administrativo, entre em contato com o desenvolvedor.

---

© 2023 Bullseye. Todos os direitos reservados.