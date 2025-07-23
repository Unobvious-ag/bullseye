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
$title = $subtitle = "";
$title_err = $subtitle_err = "";
$success_message = "";

// Processar dados do formulário quando for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar título
    if (empty(trim($_POST["title"]))) {
        $title_err = "Por favor, informe o título.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validar subtítulo
    if (empty(trim($_POST["subtitle"]))) {
        $subtitle_err = "Por favor, informe o subtítulo.";
    } else {
        $subtitle = trim($_POST["subtitle"]);
    }
    
    // Verificar erros de entrada antes de inserir no banco de dados
    if (empty($title_err) && empty($subtitle_err)) {
        // Preparar uma declaração de atualização
        $sql = "UPDATE hero_section SET title = ?, subtitle = ? WHERE id = 1";
        
        if ($stmt = $mysqli->prepare($sql)) {
            // Vincular variáveis à instrução preparada como parâmetros
            $stmt->bind_param("ss", $param_title, $param_subtitle);
            
            // Definir parâmetros
            $param_title = $title;
            $param_subtitle = $subtitle;
            
            // Tentar executar a instrução preparada
            if ($stmt->execute()) {
                $success_message = "Seção Hero atualizada com sucesso!";
                
                // Atualizar o arquivo HTML
                updateHeroSection($title, $subtitle);
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }

            // Fechar declaração
            $stmt->close();
        }
    }
    
    // Fechar conexão
    $mysqli->close();
} else {
    // Buscar dados atuais da seção Hero
    $sql = "SELECT title, subtitle FROM hero_section WHERE id = 1";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $title = $row["title"];
        $subtitle = $row["subtitle"];
    }
    
    // Fechar conexão
    $mysqli->close();
}

// Função para atualizar a seção Hero no arquivo HTML
function updateHeroSection($title, $subtitle) {
    $file_path = "../index.html";
    $html_content = file_get_contents($file_path);
    
    // Atualizar o título
    $pattern_title = '/<h1 class="font-bold text-white text-4xl sm:text-5xl md:text-6xl lg:text-\[64px\] leading-tight tracking-tight">\s*.*?\s*<\/h1>/s';
    $replacement_title = '<h1 class="font-bold text-white text-4xl sm:text-5xl md:text-6xl lg:text-[64px] leading-tight tracking-tight">' . $title . '<img src="img/flecha-verde.png" alt="" class="h-6 sm:h-8 md:h-10 inline-block ml-2 mb-2"></h1>';
    $html_content = preg_replace($pattern_title, $replacement_title, $html_content);
    
    // Atualizar o subtítulo
    $pattern_subtitle = '/<h2 class="font-light text-white text-base sm:text-lg md:text-xl mt-4 md:mt-6">\s*.*?\s*<\/h2>/s';
    $replacement_subtitle = '<h2 class="font-light text-white text-base sm:text-lg md:text-xl mt-4 md:mt-6">' . $subtitle . '</h2>';
    $html_content = preg_replace($pattern_subtitle, $replacement_subtitle, $html_content);
    
    // Salvar as alterações no arquivo
    file_put_contents($file_path, $html_content);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Hero Section - Painel Administrativo Bullseye</title>
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
                            <a class="nav-link active" href="hero.php">
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
                            <a class="nav-link" href="como.php">
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
                    <h1 class="h2">Editar Hero Section</h1>
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Editar Conteúdo</h5>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Título</label>
                                        <textarea class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" id="title" name="title" rows="3"><?php echo $title; ?></textarea>
                                        <div class="form-text">Use &lt;br&gt; para quebras de linha.</div>
                                        <span class="invalid-feedback"><?php echo $title_err; ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="subtitle" class="form-label">Subtítulo</label>
                                        <textarea class="form-control <?php echo (!empty($subtitle_err)) ? 'is-invalid' : ''; ?>" id="subtitle" name="subtitle" rows="2"><?php echo $subtitle; ?></textarea>
                                        <div class="form-text">Use &lt;br&gt; para quebras de linha.</div>
                                        <span class="invalid-feedback"><?php echo $subtitle_err; ?></span>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Pré-visualização</h5>
                                <div class="preview-box">
                                    <div class="preview-title" id="preview-title"></div>
                                    <div class="preview-subtitle" id="preview-subtitle"></div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Esta é apenas uma pré-visualização simplificada. Verifique o site para ver as alterações reais.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Atualizar pré-visualização em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const subtitleInput = document.getElementById('subtitle');
            const previewTitle = document.getElementById('preview-title');
            const previewSubtitle = document.getElementById('preview-subtitle');
            
            function updatePreview() {
                previewTitle.innerHTML = titleInput.value;
                previewSubtitle.innerHTML = subtitleInput.value;
            }
            
            titleInput.addEventListener('input', updatePreview);
            subtitleInput.addEventListener('input', updatePreview);
            
            // Inicializar pré-visualização
            updatePreview();
        });
    </script>
</body>
</html>