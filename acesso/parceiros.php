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
$nome = $url = $descricao = "";
$nome_err = $url_err = $descricao_err = $logo_err = "";
$success_message = $error_message = "";
$parceiros = [];

// Processar dados do formulário quando for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se é uma adição de parceiro
    if (isset($_POST["add_parceiro"])) {
        // Validar nome
        if (empty(trim($_POST["nome"]))) {
            $nome_err = "Por favor, informe o nome do parceiro.";
        } else {
            $nome = trim($_POST["nome"]);
        }
        
        // Validar URL
        if (empty(trim($_POST["url"]))) {
            $url_err = "Por favor, informe a URL do parceiro.";
        } else {
            $url = trim($_POST["url"]);
            // Verificar se a URL é válida
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $url_err = "Por favor, informe uma URL válida.";
            }
        }
        
        // Validar descrição
        if (empty(trim($_POST["descricao"]))) {
            $descricao_err = "Por favor, informe a descrição do parceiro.";
        } else {
            $descricao = trim($_POST["descricao"]);
        }
        
        // Validar logo
        if (!isset($_FILES["logo"]) || $_FILES["logo"]["error"] != 0) {
            $logo_err = "Por favor, faça upload do logo do parceiro.";
        } else {
            // Verificar tipo de arquivo
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            if (!in_array($_FILES["logo"]["type"], $allowed_types)) {
                $logo_err = "Apenas arquivos JPG, PNG, GIF e SVG são permitidos.";
            }
            
            // Verificar tamanho do arquivo (máximo 2MB)
            if ($_FILES["logo"]["size"] > 2 * 1024 * 1024) {
                $logo_err = "O arquivo deve ter no máximo 2MB.";
            }
        }
        
        // Verificar erros de entrada antes de inserir no banco de dados
        if (empty($nome_err) && empty($url_err) && empty($descricao_err) && empty($logo_err)) {
            // Processar upload do logo
            $target_dir = "../img/parceiros/";
            
            // Criar diretório se não existir
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Gerar nome único para o arquivo
            $file_extension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
            $logo_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $logo_filename;
            $logo_path = "img/parceiros/" . $logo_filename; // Caminho relativo para salvar no banco
            
            // Tentar fazer upload do arquivo
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                // Preparar uma declaração de inserção
                $sql = "INSERT INTO parceiros (nome, url, descricao, logo) VALUES (?, ?, ?, ?)";
                
                if ($stmt = $mysqli->prepare($sql)) {
                    // Vincular variáveis à instrução preparada como parâmetros
                    $stmt->bind_param("ssss", $param_nome, $param_url, $param_descricao, $param_logo);
                    
                    // Definir parâmetros
                    $param_nome = $nome;
                    $param_url = $url;
                    $param_descricao = $descricao;
                    $param_logo = $logo_path;
                    
                    // Tentar executar a instrução preparada
                    if ($stmt->execute()) {
                        $success_message = "Parceiro adicionado com sucesso!";
                        
                        // Atualizar o arquivo HTML
                        updateParceirosSection();
                    } else {
                        $error_message = "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                    }

                    // Fechar declaração
                    $stmt->close();
                }
            } else {
                $error_message = "Desculpe, houve um erro ao fazer upload do arquivo.";
            }
        }
    }
    // Verificar se é uma atualização de parceiro
    elseif (isset($_POST["update_parceiro"])) {
        $parceiro_id = $_POST["parceiro_id"];
        
        // Validar nome
        if (empty(trim($_POST["nome"]))) {
            $nome_err = "Por favor, informe o nome do parceiro.";
        } else {
            $nome = trim($_POST["nome"]);
        }
        
        // Validar URL
        if (empty(trim($_POST["url"]))) {
            $url_err = "Por favor, informe a URL do parceiro.";
        } else {
            $url = trim($_POST["url"]);
            // Verificar se a URL é válida
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $url_err = "Por favor, informe uma URL válida.";
            }
        }
        
        // Validar descrição
        if (empty(trim($_POST["descricao"]))) {
            $descricao_err = "Por favor, informe a descrição do parceiro.";
        } else {
            $descricao = trim($_POST["descricao"]);
        }
        
        // Verificar erros de entrada antes de atualizar no banco de dados
        if (empty($nome_err) && empty($url_err) && empty($descricao_err)) {
            // Verificar se há um novo logo
            $logo_path = $_POST["logo_atual"];
            $logo_updated = false;
            
            if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
                // Verificar tipo de arquivo
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                if (!in_array($_FILES["logo"]["type"], $allowed_types)) {
                    $logo_err = "Apenas arquivos JPG, PNG, GIF e SVG são permitidos.";
                }
                
                // Verificar tamanho do arquivo (máximo 2MB)
                if ($_FILES["logo"]["size"] > 2 * 1024 * 1024) {
                    $logo_err = "O arquivo deve ter no máximo 2MB.";
                }
                
                if (empty($logo_err)) {
                    // Processar upload do novo logo
                    $target_dir = "../img/parceiros/";
                    
                    // Criar diretório se não existir
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    // Gerar nome único para o arquivo
                    $file_extension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
                    $logo_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $logo_filename;
                    $logo_path = "img/parceiros/" . $logo_filename; // Caminho relativo para salvar no banco
                    
                    // Tentar fazer upload do arquivo
                    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                        $logo_updated = true;
                        
                        // Remover logo antigo se não for o padrão
                        $old_logo = $_POST["logo_atual"];
                        if (!empty($old_logo) && file_exists("../" . $old_logo) && strpos($old_logo, "default") === false) {
                            unlink("../" . $old_logo);
                        }
                    } else {
                        $error_message = "Desculpe, houve um erro ao fazer upload do arquivo.";
                    }
                }
            }
            
            if (empty($error_message)) {
                // Preparar uma declaração de atualização
                $sql = "UPDATE parceiros SET nome = ?, url = ?, descricao = ?, logo = ? WHERE id = ?";
                
                if ($stmt = $mysqli->prepare($sql)) {
                    // Vincular variáveis à instrução preparada como parâmetros
                    $stmt->bind_param("ssssi", $param_nome, $param_url, $param_descricao, $param_logo, $param_id);
                    
                    // Definir parâmetros
                    $param_nome = $nome;
                    $param_url = $url;
                    $param_descricao = $descricao;
                    $param_logo = $logo_path;
                    $param_id = $parceiro_id;
                    
                    // Tentar executar a instrução preparada
                    if ($stmt->execute()) {
                        $success_message = "Parceiro atualizado com sucesso!";
                        
                        // Atualizar o arquivo HTML
                        updateParceirosSection();
                    } else {
                        $error_message = "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                    }

                    // Fechar declaração
                    $stmt->close();
                }
            }
        }
    }
    // Verificar se é uma exclusão de parceiro
    elseif (isset($_POST["delete_parceiro"])) {
        $parceiro_id = $_POST["parceiro_id"];
        
        // Buscar informações do parceiro para remover o logo
        $sql = "SELECT logo FROM parceiros WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $parceiro_id);
            $stmt->execute();
            $stmt->bind_result($logo_path);
            $stmt->fetch();
            $stmt->close();
            
            // Preparar uma declaração de exclusão
            $sql = "DELETE FROM parceiros WHERE id = ?";
            
            if ($stmt = $mysqli->prepare($sql)) {
                // Vincular variáveis à instrução preparada como parâmetros
                $stmt->bind_param("i", $param_id);
                
                // Definir parâmetros
                $param_id = $parceiro_id;
                
                // Tentar executar a instrução preparada
                if ($stmt->execute()) {
                    $success_message = "Parceiro excluído com sucesso!";
                    
                    // Remover logo se não for o padrão
                    if (!empty($logo_path) && file_exists("../" . $logo_path) && strpos($logo_path, "default") === false) {
                        unlink("../" . $logo_path);
                    }
                    
                    // Atualizar o arquivo HTML
                    updateParceirosSection();
                } else {
                    $error_message = "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
                }

                // Fechar declaração
                $stmt->close();
            }
        }
    }
    
    // Fechar conexão
    $mysqli->close();
    
    // Redirecionar para evitar reenvio do formulário
    if (!empty($success_message) || !empty($error_message)) {
        header("location: parceiros.php?success=" . (!empty($success_message) ? "1" : "0"));
        exit;
    }
} else {
    // Verificar se há mensagem de sucesso
    if (isset($_GET["success"]) && $_GET["success"] == 1) {
        $success_message = "Alterações salvas com sucesso!";
    } elseif (isset($_GET["success"]) && $_GET["success"] == 0) {
        $error_message = "Ocorreu um erro ao processar sua solicitação.";
    }
    
    // Buscar parceiros do banco de dados
    $sql = "SELECT id, nome, url, descricao, logo FROM parceiros ORDER BY id ASC";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $parceiros[] = $row;
        }
    }
    
    // Fechar conexão
    $mysqli->close();
}

// Função para atualizar a seção de parceiros no arquivo HTML
function updateParceirosSection() {
    $file_path = "../index.html";
    $html_content = file_get_contents($file_path);
    
    // Buscar todos os parceiros do banco de dados
    global $mysqli;
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    $sql = "SELECT id, nome, url, descricao, logo FROM parceiros ORDER BY id ASC";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows > 0) {
        $parceiros_html = "";
        
        while ($row = $result->fetch_assoc()) {
            $parceiros_html .= '<div class="swiper-slide">' . "\n";
            $parceiros_html .= '    <div class="partner-card">' . "\n";
            $parceiros_html .= '        <a href="' . $row["url"] . '" target="_blank" rel="noopener noreferrer">' . "\n";
            $parceiros_html .= '            <img src="' . $row["logo"] . '" alt="' . $row["nome"] . '" class="partner-logo">' . "\n";
            $parceiros_html .= '            <p class="partner-description">' . $row["descricao"] . '</p>' . "\n";
            $parceiros_html .= '        </a>' . "\n";
            $parceiros_html .= '    </div>' . "\n";
            $parceiros_html .= '</div>' . "\n";
        }
        
        // Substituir a seção de parceiros no HTML
        $pattern = '/<div class="swiper-wrapper">\s*.*?\s*<\/div>\s*<!-- Add Pagination -->/s';
        $replacement = '<div class="swiper-wrapper">' . "\n" . $parceiros_html . '</div>' . "\n" . '<!-- Add Pagination -->';
        $html_content = preg_replace($pattern, $replacement, $html_content, 1);
        
        // Salvar as alterações no arquivo
        file_put_contents($file_path, $html_content);
    }
    
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Parceiros - Painel Administrativo Bullseye</title>
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
                            <a class="nav-link active" href="parceiros.php">
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
                    <h1 class="h2">Gerenciar Parceiros</h1>
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
                if(!empty($error_message)){
                    echo '<div class="alert alert-danger">' . $error_message . '</div>';
                }
                ?>

                <!-- Adicionar Novo Parceiro -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Adicionar Novo Parceiro</h5>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nome" class="form-label">Nome do Parceiro</label>
                                            <input type="text" class="form-control <?php echo (!empty($nome_err)) ? 'is-invalid' : ''; ?>" id="nome" name="nome" value="<?php echo $nome; ?>" required>
                                            <span class="invalid-feedback"><?php echo $nome_err; ?></span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="url" class="form-label">URL do Site</label>
                                            <input type="url" class="form-control <?php echo (!empty($url_err)) ? 'is-invalid' : ''; ?>" id="url" name="url" value="<?php echo $url; ?>" required>
                                            <span class="invalid-feedback"><?php echo $url_err; ?></span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descricao" class="form-label">Descrição</label>
                                        <textarea class="form-control <?php echo (!empty($descricao_err)) ? 'is-invalid' : ''; ?>" id="descricao" name="descricao" rows="2" required><?php echo $descricao; ?></textarea>
                                        <span class="invalid-feedback"><?php echo $descricao_err; ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Logo</label>
                                        <input type="file" class="form-control <?php echo (!empty($logo_err)) ? 'is-invalid' : ''; ?>" id="logo" name="logo" accept="image/*" required>
                                        <div class="form-text">Formatos aceitos: JPG, PNG, GIF, SVG. Tamanho máximo: 2MB.</div>
                                        <span class="invalid-feedback"><?php echo $logo_err; ?></span>
                                    </div>
                                    <input type="hidden" name="add_parceiro" value="1">
                                    <button type="submit" class="btn btn-success"><i class="bi bi-plus-circle"></i> Adicionar Parceiro</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Parceiros -->
                <h3 class="h4 mb-3">Parceiros Atuais</h3>
                <div class="row">
                    <?php if (empty($parceiros)): ?>
                    <div class="col-md-12">
                        <div class="alert alert-info">Nenhum parceiro cadastrado.</div>
                    </div>
                    <?php else: ?>
                        <?php foreach ($parceiros as $parceiro): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><?php echo $parceiro['nome']; ?></h5>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este parceiro?');">
                                        <input type="hidden" name="parceiro_id" value="<?php echo $parceiro['id']; ?>">
                                        <input type="hidden" name="delete_parceiro" value="1">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <img src="../<?php echo $parceiro['logo']; ?>" alt="<?php echo $parceiro['nome']; ?>" class="img-fluid partner-preview" style="max-height: 100px;">
                                    </div>
                                    <p><strong>URL:</strong> <a href="<?php echo $parceiro['url']; ?>" target="_blank"><?php echo $parceiro['url']; ?></a></p>
                                    <p><strong>Descrição:</strong> <?php echo $parceiro['descricao']; ?></p>
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $parceiro['id']; ?>">
                                        <i class="bi bi-pencil"></i> Editar Parceiro
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal de Edição -->
                        <div class="modal fade" id="editModal<?php echo $parceiro['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $parceiro['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $parceiro['id']; ?>">Editar Parceiro: <?php echo $parceiro['nome']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="edit_nome_<?php echo $parceiro['id']; ?>" class="form-label">Nome do Parceiro</label>
                                                    <input type="text" class="form-control" id="edit_nome_<?php echo $parceiro['id']; ?>" name="nome" value="<?php echo $parceiro['nome']; ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="edit_url_<?php echo $parceiro['id']; ?>" class="form-label">URL do Site</label>
                                                    <input type="url" class="form-control" id="edit_url_<?php echo $parceiro['id']; ?>" name="url" value="<?php echo $parceiro['url']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_descricao_<?php echo $parceiro['id']; ?>" class="form-label">Descrição</label>
                                                <textarea class="form-control" id="edit_descricao_<?php echo $parceiro['id']; ?>" name="descricao" rows="2" required><?php echo $parceiro['descricao']; ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_logo_<?php echo $parceiro['id']; ?>" class="form-label">Logo</label>
                                                <input type="file" class="form-control" id="edit_logo_<?php echo $parceiro['id']; ?>" name="logo" accept="image/*">
                                                <div class="form-text">Deixe em branco para manter o logo atual. Formatos aceitos: JPG, PNG, GIF, SVG. Tamanho máximo: 2MB.</div>
                                                <div class="mt-2">
                                                    <p>Logo atual:</p>
                                                    <img src="../<?php echo $parceiro['logo']; ?>" alt="Logo atual" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            </div>
                                            <input type="hidden" name="parceiro_id" value="<?php echo $parceiro['id']; ?>">
                                            <input type="hidden" name="logo_atual" value="<?php echo $parceiro['logo']; ?>">
                                            <input type="hidden" name="update_parceiro" value="1">
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Alterações</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Informações Importantes</h5>
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle"></i> Dicas para gerenciar parceiros:</h6>
                                    <ul>
                                        <li>Use logos com fundo transparente para melhor visualização no site.</li>
                                        <li>Mantenha as descrições curtas e objetivas.</li>
                                        <li>Verifique se a URL do parceiro está correta e funcionando.</li>
                                        <li>Após adicionar ou editar um parceiro, verifique como ficou no site clicando em "Ver Site".</li>
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