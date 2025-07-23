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
$email = $telefone = "";
$email_err = $telefone_err = "";
$success_message = "";

// Processar dados do formulário quando for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, informe o email.";
    } else {
        $email = trim($_POST["email"]);
        // Verificar se o email é válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Por favor, informe um email válido.";
        }
    }
    
    // Validar telefone
    if (empty(trim($_POST["telefone"]))) {
        $telefone_err = "Por favor, informe o telefone.";
    } else {
        $telefone = trim($_POST["telefone"]);
    }
    
    // Verificar erros de entrada antes de inserir no banco de dados
    if (empty($email_err) && empty($telefone_err)) {
        // Preparar uma declaração de atualização
        $sql = "UPDATE rodape SET email = ?, telefone = ? WHERE id = 1";
        
        if ($stmt = $mysqli->prepare($sql)) {
            // Vincular variáveis à instrução preparada como parâmetros
            $stmt->bind_param("ss", $param_email, $param_telefone);
            
            // Definir parâmetros
            $param_email = $email;
            $param_telefone = $telefone;
            
            // Tentar executar a instrução preparada
            if ($stmt->execute()) {
                $success_message = "Informações do rodapé atualizadas com sucesso!";
                
                // Atualizar o arquivo HTML
                updateRodapeSection($email, $telefone);
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }

            // Fechar declaração
            $stmt->close();
        }
    }
    
    // Fechar conexão
    $mysqli->close();
    
    // Redirecionar para evitar reenvio do formulário
    if (!empty($success_message)) {
        header("location: rodape.php?success=1");
        exit;
    }
} else {
    // Verificar se há mensagem de sucesso
    if (isset($_GET["success"]) && $_GET["success"] == 1) {
        $success_message = "Informações do rodapé atualizadas com sucesso!";
    }
    
    // Buscar dados atuais do rodapé
    $sql = "SELECT email, telefone FROM rodape WHERE id = 1";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row["email"];
        $telefone = $row["telefone"];
    }
    
    // Fechar conexão
    $mysqli->close();
}

// Função para atualizar a seção de rodapé no arquivo HTML
function updateRodapeSection($email, $telefone) {
    $file_path = "../index.html";
    $html_content = file_get_contents($file_path);
    
    // Atualizar o email
    $pattern_email = '/<a href="mailto:[^"]*" class="text-white hover:text-primary transition-colors">\s*.*?\s*<\/a>/s';
    $replacement_email = '<a href="mailto:' . $email . '" class="text-white hover:text-primary transition-colors">' . $email . '</a>';
    $html_content = preg_replace($pattern_email, $replacement_email, $html_content, 1);
    
    // Atualizar o telefone
    $pattern_telefone = '/<a href="tel:[^"]*" class="text-white hover:text-primary transition-colors">\s*.*?\s*<\/a>/s';
    $replacement_telefone = '<a href="tel:' . preg_replace('/[^0-9+]/', '', $telefone) . '" class="text-white hover:text-primary transition-colors">' . $telefone . '</a>';
    $html_content = preg_replace($pattern_telefone, $replacement_telefone, $html_content, 1);
    
    // Salvar as alterações no arquivo
    file_put_contents($file_path, $html_content);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Rodapé - Painel Administrativo Bullseye</title>
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
                            <a class="nav-link active" href="rodape.php">
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
                    <h1 class="h2">Editar Rodapé</h1>
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

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Informações de Contato</h5>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo $email; ?>" required>
                                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telefone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control <?php echo (!empty($telefone_err)) ? 'is-invalid' : ''; ?>" id="telefone" name="telefone" value="<?php echo $telefone; ?>" required>
                                            <span class="invalid-feedback"><?php echo $telefone_err; ?></span>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Prévia do Rodapé</h5>
                                <div class="footer-preview p-4 bg-dark text-white rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Contato</h5>
                                            <p>
                                                <i class="bi bi-envelope me-2"></i> <a href="mailto:<?php echo $email; ?>" class="text-white"><?php echo $email; ?></a>
                                            </p>
                                            <p>
                                                <i class="bi bi-telephone me-2"></i> <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $telefone); ?>" class="text-white"><?php echo $telefone; ?></a>
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <p class="mt-3">&copy; <?php echo date('Y'); ?> Bullseye. Todos os direitos reservados.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Informações Importantes</h5>
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Dicas para o rodapé:</h6>
                                    <ul>
                                        <li>Certifique-se de que o email informado é válido e está ativo.</li>
                                        <li>O telefone deve estar em um formato que facilite a leitura (ex: +55 11 1234-5678).</li>
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