<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "config.php";

// Definir variáveis e inicializar com valores vazios
$descricao = "";
$descricao_err = "";
$success_message = "";
$cards = [];

// Processar dados do formulário quando for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se é uma atualização da descrição principal
    if (isset($_POST["update_description"])) {
        // Validar descrição
        if (empty(trim($_POST["descricao"]))) {
            $descricao_err = "Por favor, informe a descrição.";
        } else {
            $descricao = trim($_POST["descricao"]);
        }
        
        // Verificar erros de entrada antes de inserir no banco de dados
        if (empty($descricao_err)) {
            // Preparar uma declaração de atualização
            $sql = "UPDATE como_fazemos SET descricao = ? WHERE id = 1";
            
            if ($stmt = $mysqli->prepare($sql)) {
                // Vincular variáveis à instrução preparada como parâmetros
                $stmt->bind_param("s", $param_descricao);
                
                // Definir parâmetros
                $param_descricao = $descricao;
                
                // Tentar executar a instrução preparada
                if ($stmt->execute()) {
                    $success_message = "Descrição da seção 'Como Fazemos' atualizada com sucesso!";
                    $descricao_atualizada = $descricao; // Salvar para uso posterior
                } else {
                    echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }

                // Fechar declaração
                $stmt->close();
            }
        }
    }
    // Verificar se é uma atualização de card
    elseif (isset($_POST["update_card"])) {
        $card_id = $_POST["card_id"];
        $titulo = trim($_POST["titulo"]);
        $descricao_card = trim($_POST["descricao_card"]);
        
        if (!empty($titulo) && !empty($descricao_card)) {
            // Preparar uma declaração de atualização
            $sql = "UPDATE como_fazemos_cards SET titulo = ?, descricao = ? WHERE id = ?";
            
            if ($stmt = $mysqli->prepare($sql)) {
                // Vincular variáveis à instrução preparada como parâmetros
                $stmt->bind_param("ssi", $titulo, $descricao_card, $card_id);
                
                // Tentar executar a instrução preparada
                if ($stmt->execute()) {
                    $success_message = "Card atualizado com sucesso!";
                } else {
                    echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }

                // Fechar declaração
                $stmt->close();
            }
        }
    }
    
    // Fechar conexão
    $mysqli->close();
    
    // Atualizar o arquivo HTML se houver sucesso
    if (!empty($success_message)) {
        // Atualizar a descrição principal se foi modificada
        if (isset($descricao_atualizada)) {
            updateComoFazemosDescription($descricao_atualizada);
        }
        // Atualizar os cards
        updateComoFazemosCards();
    }
    
    // Redirecionar para evitar reenvio do formulário
    if (!empty($success_message)) {
        header("location: como.php?success=1");
        exit;
    }
} else {
    // Verificar se há mensagem de sucesso
    if (isset($_GET["success"]) && $_GET["success"] == 1) {
        $success_message = "Alterações salvas com sucesso!";
    }
    
    // Buscar dados atuais da seção Como Fazemos
    $sql = "SELECT descricao FROM como_fazemos WHERE id = 1";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $descricao = $row["descricao"];
    }
    
    // Buscar cards da seção Como Fazemos
    $sql = "SELECT id, titulo, descricao, icone, ordem FROM como_fazemos_cards ORDER BY ordem ASC";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cards[] = $row;
        }
    }
    
    // Fechar conexão
    $mysqli->close();
}

// Função para atualizar a descrição da seção Como Fazemos no arquivo HTML
function updateComoFazemosDescription($descricao) {
    $file_path = "../index.html";
    $html_content = file_get_contents($file_path);
    
    // Atualizar a descrição - Pattern correto para a seção Como Fazemos
    $pattern = '/<p class="w-full md:w-\[652px\] font-normal text-white text-xs sm:text-sm md:text-base leading-relaxed md:ml-8">[\s\S]*?<\/p>/';
    $replacement = '<p class="w-full md:w-[652px] font-normal text-white text-xs sm:text-sm md:text-base leading-relaxed md:ml-8">' . "\n                        " . $descricao . "\n                    </p>";
    $html_content = preg_replace($pattern, $replacement, $html_content, 1);
    
    // Salvar as alterações no arquivo
    if (file_put_contents($file_path, $html_content) === false) {
        error_log("Erro ao salvar o arquivo index.html");
        return false;
    }
    return true;
}

// Função para atualizar os cards da seção Como Fazemos no arquivo HTML
function updateComoFazemosCards() {
    $file_path = "../index.html";
    $html_content = file_get_contents($file_path);
    
    // Criar uma nova conexão para esta função
    $mysqli_local = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Verificar conexão
    if ($mysqli_local->connect_error) {
        die("Falha na conexão: " . $mysqli_local->connect_error);
    }
    
    $sql = "SELECT id, titulo, descricao, icone, ordem FROM como_fazemos_cards ORDER BY ordem ASC";
    $result = $mysqli_local->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $cards_html = "";
        $delay = 100;
        
        while ($row = $result->fetch_assoc()) {
            $cards_html .= '<!-- Card ' . $row["ordem"] . ' -->' . "\n";
            $cards_html .= '<div class="card p-6 animate-fade-in delay-' . $delay . '">' . "\n";
            $cards_html .= '    <div class="flex flex-col items-center pt-4 pb-4 h-full">' . "\n";
            $cards_html .= '        <div class="card-icon">' . "\n";
            $cards_html .= '            <img src="' . $row["icone"] . '" alt="' . $row["titulo"] . '" class="w-[40px] h-[40px]">' . "\n";
            $cards_html .= '        </div>' . "\n";
            $cards_html .= '        <h3 class="card-title text-base">' . "\n";
            $cards_html .= '            ' . $row["titulo"] . "\n";
            $cards_html .= '        </h3>' . "\n";
            $cards_html .= '        <p class="card-description text-sm">' . "\n";
            $cards_html .= '            ' . $row["descricao"] . "\n";
            $cards_html .= '        </p>' . "\n";
            $cards_html .= '    </div>' . "\n";
            $cards_html .= '</div>' . "\n\n";
            
            $delay += 100;
            if ($delay > 400) $delay = 100; // Reset delay after 400
        }
        
        // Substituir a seção de cards no HTML
        $pattern = '/<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 md:gap-10">\s*.*?\s*<\/div>\s*<\/section>/s';
        $replacement = '<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 md:gap-10">' . "\n" . $cards_html . '</div>' . "\n" . '</section>';
        $html_content = preg_replace($pattern, $replacement, $html_content, 1);
        
        // Salvar as alterações no arquivo
        file_put_contents($file_path, $html_content);
    }
    
    // Fechar a conexão local
    $mysqli_local->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Como Fazemos - Painel Administrativo Bullseye</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="../img/bullseye_logo.png" alt="Bullseye Logo" class="img-fluid" style="max-height: 60px;">
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="hero.php">
                                <i class="bi bi-image me-2"></i> Hero Section
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sobre.php">
                                <i class="bi bi-info-circle me-2"></i> Sobre Nós
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="acreditamos.php">
                                <i class="bi bi-lightbulb me-2"></i> No que Acreditamos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="como.php">
                                <i class="bi bi-gear me-2"></i> Como Fazemos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="parceiros.php">
                                <i class="bi bi-people me-2"></i> Parceiros
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="rodape.php">
                                <i class="bi bi-layout-text-window-reverse me-2"></i> Rodapé
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Editar Seção "Como Fazemos"</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../index.html" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver Site
                        </a>
                    </div>
                </div>

                <?php 
                if(!empty($success_message)){
                    echo '<div class="alert alert-success">' . $success_message . '</div>';
                }        
                ?>

                <!-- Descrição Principal -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Editar Descrição Principal</h5>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="mb-3">
                                        <label for="descricao" class="form-label">Descrição</label>
                                        <textarea class="form-control <?php echo (!empty($descricao_err)) ? 'is-invalid' : ''; ?>" id="descricao" name="descricao" rows="3"><?php echo $descricao; ?></textarea>
                                        <div class="form-text">Use &lt;br&gt; para quebras de linha.</div>
                                        <span class="invalid-feedback"><?php echo $descricao_err; ?></span>
                                    </div>
                                    <input type="hidden" name="update_description" value="1">
                                    <button type="submit" class="btn btn-primary">Salvar Descrição</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards -->
                <h3 class="h4 mb-3">Editar Cards</h3>
                <div class="row">
                    <?php foreach ($cards as $card): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="../<?php echo $card['icone']; ?>" alt="<?php echo $card['titulo']; ?>" class="me-3" style="width: 40px; height: 40px;">
                                    <h5 class="card-title mb-0"><?php echo $card['titulo']; ?></h5>
                                </div>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="mb-3">
                                        <label for="titulo_<?php echo $card['id']; ?>" class="form-label">Título</label>
                                        <input type="text" class="form-control" id="titulo_<?php echo $card['id']; ?>" name="titulo" value="<?php echo $card['titulo']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descricao_<?php echo $card['id']; ?>" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="descricao_<?php echo $card['id']; ?>" name="descricao_card" rows="3" required><?php echo $card['descricao']; ?></textarea>
                                    </div>
                                    <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                    <input type="hidden" name="update_card" value="1">
                                    <button type="submit" class="btn btn-primary">Salvar Card</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Informações Importantes</h5>
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Dicas para edição:</h6>
                                    <ul>
                                        <li>Os ícones dos cards não podem ser alterados através do painel administrativo.</li>
                                        <li>Mantenha os títulos curtos e objetivos para melhor visualização no site.</li>
                                        <li>As descrições devem ser concisas e informativas.</li>
                                        <li>Após salvar as alterações, verifique como ficou no site clicando em "Ver Site".</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>