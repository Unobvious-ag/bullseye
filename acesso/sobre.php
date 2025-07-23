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
        $sql = "UPDATE sobre_nos SET coluna1 = ?, coluna2 = ? WHERE id = 1";
        
        if ($stmt = $mysqli->prepare($sql)) {
            // Vincular variáveis à instrução preparada como parâmetros
            $stmt->bind_param("ss", $param_coluna1, $param_coluna2);
            
            // Definir parâmetros
            $param_coluna1 = $coluna1;
            $param_coluna2 = $coluna2;
            
            // Tentar executar a instrução preparada
            if ($stmt->execute()) {
                $success_message = "Seção Sobre Nós atualizada com sucesso!";
                
                // Atualizar o arquivo HTML
                updateSobreNosSection($coluna1, $coluna2);
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
    // Buscar dados atuais da seção Sobre Nós
    $sql = "SELECT coluna1, coluna2 FROM sobre_nos WHERE id = 1";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $coluna1 = $row["coluna1"];
        $coluna2 = $row["coluna2"];
    }
    
    // Fechar conexão
    $mysqli->close();
}

// Função para atualizar a seção Sobre Nós no arquivo HTML
function updateSobreNosSection($coluna1, $coluna2) {
    $file_path = "../index.html";
    $html_content = file_get_contents($file_path);
    
    // Atualizar a coluna 1
    $pattern_coluna1 = '/<div class="w-full md:w-\[556px\] font-light text-white text-base sm:text-lg md:text-\[22px\] leading-relaxed animate-fade-in delay-100">\s*.*?\s*<\/div>/s';
    $replacement_coluna1 = '<div class="w-full md:w-[556px] font-light text-white text-base sm:text-lg md:text-[22px] leading-relaxed animate-fade-in delay-100">' . $coluna1 . '</div>';
    $html_content = preg_replace($pattern_coluna1, $replacement_coluna1, $html_content, 1);
    
    // Atualizar a coluna 2
    $pattern_coluna2 = '/<div class="w-full md:w-\[526px\] font-normal text-white text-xs sm:text-sm md:text-base leading-relaxed animate-fade-in delay-300">\s*.*?\s*<\/div>/s';
    $replacement_coluna2 = '<div class="w-full md:w-[526px] font-normal text-white text-xs sm:text-sm md:text-base leading-relaxed animate-fade-in delay-300">' . $coluna2 . '</div>';
    $html_content = preg_replace($pattern_coluna2, $replacement_coluna2, $html_content, 1);
    
    // Salvar as alterações no arquivo
    file_put_contents($file_path, $html_content);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Sobre Nós - Painel Administrativo Bullseye</title>
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
                            <a class="nav-link active" href="sobre.php">
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
                    <h1 class="h2">Editar Seção Sobre Nós</h1>
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
                                        <label for="coluna2" class="form-label">Lista de Tópicos (Coluna 2)</label>
                                        <textarea class="form-control <?php echo (!empty($coluna2_err)) ? 'is-invalid' : ''; ?>" id="coluna2" name="coluna2" rows="8"><?php echo $coluna2; ?></textarea>
                                        <div class="form-text">
                                            Mantenha a estrutura HTML para os bullets. Exemplo:<br>
                                            <code>&lt;div class="flex items-start gap-2 mb-3 sm:mb-4 hover:translate-x-1 transition-transform duration-300"&gt;&lt;img src="img/bullet.png" alt="Bullet" class="w-2 sm:w-3 h-2 sm:h-3"&gt;&lt;p class="m-0"&gt;Seu texto aqui&lt;/p&gt;&lt;/div&gt;</code>
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
                                <h5 class="card-title">Ajuda para Edição</h5>
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Dicas para edição:</h6>
                                    <ul>
                                        <li>Para adicionar um novo tópico na lista, copie a estrutura de um tópico existente e altere apenas o texto.</li>
                                        <li>Mantenha as classes CSS e estrutura HTML para preservar o estilo do site.</li>
                                        <li>Use &lt;br&gt; para quebras de linha no texto principal.</li>
                                        <li>Não remova as classes CSS ou atributos HTML, pois isso pode afetar o layout do site.</li>
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