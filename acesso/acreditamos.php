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
$coluna1 = $coluna2 = "";
$coluna1_err = $coluna2_err = "";
$success_message = "";

// Processar dados do formulário quando for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar coluna 1
    if (empty(trim($_POST["coluna1"]))) {
        $coluna1_err = "Por favor, informe o texto da primeira coluna.";
    } else {
        $coluna1 = trim($_POST["coluna1"]);
    }
    
    // Validar coluna 2
    if (empty(trim($_POST["coluna2"]))) {
        $coluna2_err = "Por favor, informe o texto da segunda coluna.";
    } else {
        $coluna2 = trim($_POST["coluna2"]);
    }
    
    // Verificar erros de entrada antes de inserir no banco de dados
    if (empty($coluna1_err) && empty($coluna2_err)) {
        // Preparar uma declaração de atualização
        $sql = "UPDATE acreditamos SET coluna1 = ?, coluna2 = ? WHERE id = 1";
        
        if ($stmt = $mysqli->prepare($sql)) {
            // Vincular variáveis à instrução preparada como parâmetros
            $stmt->bind_param("ss", $param_coluna1, $param_coluna2);
            
            // Definir parâmetros
            $param_coluna1 = $coluna1;
            $param_coluna2 = $coluna2;
            
            // Tentar executar a instrução preparada
            if ($stmt->execute()) {
                $success_message = "Seção 'No que Acreditamos' atualizada com sucesso!";
                
                // Atualizar o arquivo HTML
                updateAcreditamosSection($coluna1, $coluna2);
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
    // Buscar dados atuais da seção No que Acreditamos
    $sql = "SELECT coluna1, coluna2 FROM acreditamos WHERE id = 1";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $coluna1 = $row["coluna1"];
        $coluna2 = $row["coluna2"];
    }
    
    // Fechar conexão
    $mysqli->close();
}

// Função para atualizar a seção No que Acreditamos no arquivo HTML
function updateAcreditamosSection($coluna1, $coluna2) {
    $file_path = "../index.html";
    $html_content = file_get_contents($file_path);
    
    // Atualizar a coluna 1
    $pattern_coluna1 = '/<div class="w-full md:w-\[556px\] font-light text-white text-base sm:text-lg md:text-\[22px\] leading-relaxed animate-fade-in delay-100">\s*.*?\s*<\/div>/s';
    $replacement_coluna1 = '<div class="w-full md:w-[556px] font-light text-white text-base sm:text-lg md:text-[22px] leading-relaxed animate-fade-in delay-100">' . $coluna1 . '</div>';
    
    // Encontrar a segunda ocorrência (seção No que Acreditamos)
    $count = 0;
    $html_content = preg_replace_callback($pattern_coluna1, function($matches) use (&$count, $replacement_coluna1) {
        $count++;
        return ($count == 2) ? $replacement_coluna1 : $matches[0];
    }, $html_content);
    
    // Atualizar a coluna 2
    $pattern_coluna2 = '/<div class="w-full md:w-\[526px\] font-normal text-white text-xs sm:text-sm md:text-base leading-relaxed animate-fade-in delay-300">\s*.*?\s*<\/div>/s';
    $replacement_coluna2 = '<div class="w-full md:w-[526px] font-normal text-white text-xs sm:text-sm md:text-base leading-relaxed animate-fade-in delay-300">' . $coluna2 . '</div>';
    
    // Encontrar a segunda ocorrência (seção No que Acreditamos)
    $count = 0;
    $html_content = preg_replace_callback($pattern_coluna2, function($matches) use (&$count, $replacement_coluna2) {
        $count++;
        return ($count == 2) ? $replacement_coluna2 : $matches[0];
    }, $html_content);
    
    // Salvar as alterações no arquivo
    file_put_contents($file_path, $html_content);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar No que Acreditamos - Painel Administrativo Bullseye</title>
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
                            <a class="nav-link active" href="acreditamos.php">
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
                    <h1 class="h2">Editar Seção "No que Acreditamos"</h1>
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
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Editar Conteúdo</h5>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="mb-3">
                                        <label for="coluna1" class="form-label">Texto Principal (Coluna 1)</label>
                                        <textarea class="form-control <?php echo (!empty($coluna1_err)) ? 'is-invalid' : ''; ?>" id="coluna1" name="coluna1" rows="4"><?php echo $coluna1; ?></textarea>
                                        <div class="form-text">Use &lt;br&gt; para quebras de linha.</div>
                                        <span class="invalid-feedback"><?php echo $coluna1_err; ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="coluna2" class="form-label">Texto Secundário (Coluna 2)</label>
                                        <textarea class="form-control <?php echo (!empty($coluna2_err)) ? 'is-invalid' : ''; ?>" id="coluna2" name="coluna2" rows="6"><?php echo $coluna2; ?></textarea>
                                        <div class="form-text">
                                            Mantenha a estrutura HTML para os parágrafos. Exemplo:<br>
                                            <code>&lt;p class="mb-3 sm:mb-4"&gt;Seu texto aqui&lt;/p&gt;</code>
                                        </div>
                                        <span class="invalid-feedback"><?php echo $coluna2_err; ?></span>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Pré-visualização</h5>
                                <div class="preview-box">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-white mb-3">Coluna 1:</h6>
                                            <div id="preview-coluna1" class="text-white"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-white mb-3">Coluna 2:</h6>
                                            <div id="preview-coluna2" class="text-white"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3">
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
            const coluna1Input = document.getElementById('coluna1');
            const coluna2Input = document.getElementById('coluna2');
            const previewColuna1 = document.getElementById('preview-coluna1');
            const previewColuna2 = document.getElementById('preview-coluna2');
            
            function updatePreview() {
                previewColuna1.innerHTML = coluna1Input.value;
                previewColuna2.innerHTML = coluna2Input.value;
            }
            
            coluna1Input.addEventListener('input', updatePreview);
            coluna2Input.addEventListener('input', updatePreview);
            
            // Inicializar pré-visualização
            updatePreview();
        });
    </script>
</body>
</html>