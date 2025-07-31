<?php
/* Configurações do banco de dados */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'admbull');
define('DB_PASSWORD', 'copriNSOnAbLeTerChuMUlarMAiNELo');
define('DB_NAME', 'bullseye_admin');

/* Tentativa de conexão com o banco de dados MySQL */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if($mysqli === false){
    die("ERRO: Não foi possível conectar ao banco de dados. " . $mysqli->connect_error);
}

// Função para criar o banco de dados e tabelas se não existirem
function setup_database() {
    global $mysqli;
    
    // Criar banco de dados se não existir
    $mysqli->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $mysqli->select_db(DB_NAME);
    
    // Criar tabela de usuários
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Verificar se já existe um usuário admin
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $row = $result->fetch_assoc();
    
    // Se não existir, criar usuário admin padrão
    if ($row['count'] == 0) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT); // Senha padrão: admin123
        
        $stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->close();
    }
    
    // Criar tabela para conteúdo da Hero Section
    $sql = "CREATE TABLE IF NOT EXISTS hero_section (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        title TEXT NOT NULL,
        subtitle TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Inserir conteúdo padrão da Hero Section se não existir
    $result = $mysqli->query("SELECT COUNT(*) as count FROM hero_section");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $title = 'O QUE HÁ DE<br>MELHOR NO<br>MUNDO';
        $subtitle = 'Conectado às necessidades<br>de empresas visionárias.';
        
        $stmt = $mysqli->prepare("INSERT INTO hero_section (title, subtitle) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $subtitle);
        $stmt->execute();
        $stmt->close();
    }
    
    // Criar tabela para conteúdo da seção Sobre Nós
    $sql = "CREATE TABLE IF NOT EXISTS sobre_nos (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        coluna1 TEXT NOT NULL,
        coluna2 TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Inserir conteúdo padrão da seção Sobre Nós se não existir
    $result = $mysqli->query("SELECT COUNT(*) as count FROM sobre_nos");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $coluna1 = 'Somos uma importadora focada em conectar o mercado brasileiro às tecnologias mais inovadoras do mundo, e aos melhores fabricantes de diversos produtos, componentes e matérias-primas.';
        $coluna2 = '<div class="flex items-start gap-2 mb-3 sm:mb-4 hover:translate-x-1 transition-transform duration-300"><img src="img/bullet.png" alt="Bullet" class="w-2 sm:w-3 h-2 sm:h-3"><p class="m-0">Know-how em importação de produtos de ponta.</p></div><div class="flex items-start gap-2 mb-3 sm:mb-4 hover:translate-x-1 transition-transform duration-300"><img src="img/bullet.png" alt="Bullet" class="w-2 sm:w-3 h-2 sm:h-3"><p class="m-0">Rede Global de fornecedores estratégicos.</p></div><div class="flex items-start gap-2 mb-3 sm:mb-4 hover:translate-x-1 transition-transform duration-300"><img src="img/bullet.png" alt="Bullet" class="w-2 sm:w-3 h-2 sm:h-3"><p class="m-0">Portas abertas com os principais fabricantes na China.</p></div><div class="flex items-start gap-2 mb-3 sm:mb-4 hover:translate-x-1 transition-transform duration-300"><img src="img/bullet.png" alt="Bullet" class="w-2 sm:w-3 h-2 sm:h-3"><p class="m-0">Curadoria de produtos únicos e personalizados.</p></div>';
        
        $stmt = $mysqli->prepare("INSERT INTO sobre_nos (coluna1, coluna2) VALUES (?, ?)");
        $stmt->bind_param("ss", $coluna1, $coluna2);
        $stmt->execute();
        $stmt->close();
    }
    
    // Criar tabela para conteúdo da seção No que Acreditamos
    $sql = "CREATE TABLE IF NOT EXISTS acreditamos (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        coluna1 TEXT NOT NULL,
        coluna2 TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Inserir conteúdo padrão da seção No que Acreditamos se não existir
    $result = $mysqli->query("SELECT COUNT(*) as count FROM acreditamos");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $coluna1 = 'Inspirar inovação no mercado brasileiro, conectando tecnologias de ponta às necessidades de empresas visionárias.';
        $coluna2 = '<p class="mb-3 sm:mb-4">Acreditamos que o futuro pertence a quem adota a inovação como diferencial competitivo.</p><p>Por isso, nossa missão é trazer soluções exclusivas e certeiras, que impactem positivamente os negócios de nossos clientes.</p>';
        
        $stmt = $mysqli->prepare("INSERT INTO acreditamos (coluna1, coluna2) VALUES (?, ?)");
        $stmt->bind_param("ss", $coluna1, $coluna2);
        $stmt->execute();
        $stmt->close();
    }
    
    // Criar tabela para conteúdo da seção Como Fazemos
    $sql = "CREATE TABLE IF NOT EXISTS como_fazemos (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        descricao TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Inserir conteúdo padrão da seção Como Fazemos se não existir
    $result = $mysqli->query("SELECT COUNT(*) as count FROM como_fazemos");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $descricao = 'Com know-how em importação, curadoria especializada e parcerias com os maiores fabricantes de tecnologia da China.';
        
        $stmt = $mysqli->prepare("INSERT INTO como_fazemos (descricao) VALUES (?)");
        $stmt->bind_param("s", $descricao);
        $stmt->execute();
        $stmt->close();
    }
    
    // Criar tabela para os cards da seção Como Fazemos
    $sql = "CREATE TABLE IF NOT EXISTS como_fazemos_cards (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT NOT NULL,
        icone VARCHAR(255) NOT NULL,
        ordem INT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Inserir conteúdo padrão dos cards da seção Como Fazemos se não existirem
    $result = $mysqli->query("SELECT COUNT(*) as count FROM como_fazemos_cards");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $cards = [
            [
                'titulo' => 'Know-how',
                'descricao' => 'Estabelecemos relações estratégicas com fabricantes e fornecedores globais para garantir acesso a tecnologias exclusivas.',
                'icone' => 'img/know-how.png',
                'ordem' => 1
            ],
            [
                'titulo' => 'Parcerias Estratégicas',
                'descricao' => 'Mais que importar, entregamos expertise na escolha e introdução de produtos que atendem demandas específicas do mercado brasileiro.',
                'icone' => 'img/parcerias.png',
                'ordem' => 2
            ],
            [
                'titulo' => 'Curadoria',
                'descricao' => 'Selecionamos produtos que combinam alta qualidade, inovação e diferenciação, em pequenas quantidades, para nichos que valorizam a exclusividade.',
                'icone' => 'img/curadoria.png',
                'ordem' => 3
            ],
            [
                'titulo' => 'LEDs de Alta Precisão',
                'descricao' => 'Equipamentos de última geração para diferentes aplicações.',
                'icone' => 'img/leds.png',
                'ordem' => 4
            ],
            [
                'titulo' => 'Soluções Customizadas',
                'descricao' => 'Produtos inovadores, alinhados às tendências globais.',
                'icone' => 'img/solucoes.png',
                'ordem' => 5
            ],
            [
                'titulo' => 'Representação Comercial',
                'descricao' => 'Facilitamos o acesso às melhores marcas e tecnologias chinesas, com suporte completo.',
                'icone' => 'img/representacoes.png',
                'ordem' => 6
            ]
        ];
        
        $stmt = $mysqli->prepare("INSERT INTO como_fazemos_cards (titulo, descricao, icone, ordem) VALUES (?, ?, ?, ?)");
        
        foreach ($cards as $card) {
            $stmt->bind_param("sssi", $card['titulo'], $card['descricao'], $card['icone'], $card['ordem']);
            $stmt->execute();
        }
        
        $stmt->close();
    }
    
    // Criar tabela para parceiros
    $sql = "CREATE TABLE IF NOT EXISTS parceiros (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT NOT NULL,
        logo VARCHAR(255) NOT NULL,
        url VARCHAR(255) NOT NULL,
        ordem INT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Inserir conteúdo padrão dos parceiros se não existirem
    $result = $mysqli->query("SELECT COUNT(*) as count FROM parceiros");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $parceiros = [
            [
                'nome' => 'Unobvious',
                'descricao' => '<span class="inline-block">Agência referência em</span><br><span class="inline-block">brand experience no Brasil</span>',
                'logo' => 'img/unobvious.png',
                'url' => 'http://www.unobvious.ag',
                'ordem' => 1
            ],
            [
                'nome' => 'Premteco',
                'descricao' => '<span class="inline-block">Renomado fabricante</span><br><span class="inline-block">de LED</span>',
                'logo' => 'img/premteco.png',
                'url' => 'http://www.premteco.com',
                'ordem' => 2
            ],
            [
                'nome' => 'Zhenxiang Technology',
                'descricao' => '<span class="inline-block">Zhenxiang Technology,</span><br><span class="inline-block">renomado fabricante de soluções ópticas</span>',
                'logo' => 'img/china.png',
                'url' => 'http://www.imagetruth.com/english',
                'ordem' => 3
            ]
        ];
        
        $stmt = $mysqli->prepare("INSERT INTO parceiros (nome, descricao, logo, url, ordem) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($parceiros as $parceiro) {
            $stmt->bind_param("ssssi", $parceiro['nome'], $parceiro['descricao'], $parceiro['logo'], $parceiro['url'], $parceiro['ordem']);
            $stmt->execute();
        }
        
        $stmt->close();
    }
    
    // Criar tabela para informações do rodapé
    $sql = "CREATE TABLE IF NOT EXISTS rodape (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL,
        telefone VARCHAR(255) NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $mysqli->query($sql);
    
    // Inserir conteúdo padrão do rodapé se não existir
    $result = $mysqli->query("SELECT COUNT(*) as count FROM rodape");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $email = 'pedro.cardone@bullseyetrade.com.br';
        $telefone = '+55 11 99664-7689';
        
        $stmt = $mysqli->prepare("INSERT INTO rodape (email, telefone) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $telefone);
        $stmt->execute();
        $stmt->close();
    }
}

// Executar configuração inicial do banco de dados
setup_database();
?>
