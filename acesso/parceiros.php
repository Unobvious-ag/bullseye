<?php
session_start();
require_once 'config.php';

// Verificar se o usuário está logado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$message = '';
$error = '';

// Processar ações
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $descricao = trim($_POST['descricao']);
                $url = trim($_POST['url']);
                
                if (!empty($descricao) && !empty($url)) {
                    // Upload do logo
                    $logo_path = '';
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                        $upload_dir = '../img/';
                        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                        $logo_filename = 'parceiro_' . time() . '.' . $file_extension;
                        $logo_path = $upload_dir . $logo_filename;
                        
                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                            $logo_path = 'img/' . $logo_filename;
                        } else {
                            $error = 'Erro ao fazer upload do logo.';
                            break;
                        }
                    } else {
                        $error = 'Logo é obrigatório.';
                        break;
                    }
                    
                    // Obter próxima ordem
                    $result = $mysqli->query("SELECT MAX(ordem) as max_ordem FROM parceiros");
                    $row = $result->fetch_assoc();
                    $ordem = ($row['max_ordem'] ?? 0) + 1;
                    
                    $stmt = $mysqli->prepare("INSERT INTO parceiros (descricao, logo, url, ordem) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $descricao, $logo_path, $url, $ordem);
                    
                    if ($stmt->execute()) {
                        $message = 'Parceiro adicionado com sucesso!';
                        updateParceirosSection();
                    } else {
                        $error = 'Erro ao adicionar parceiro.';
                    }
                    $stmt->close();
                } else {
                    $error = 'Todos os campos são obrigatórios.';
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $descricao = trim($_POST['descricao']);
                $url = trim($_POST['url']);
                
                if (!empty($descricao) && !empty($url)) {
                    // Verificar se há novo logo
                    $logo_update = '';
                    $logo_params = '';
                    
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                        $upload_dir = '../img/';
                        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                        $logo_filename = 'parceiro_' . time() . '.' . $file_extension;
                        $logo_path = $upload_dir . $logo_filename;
                        
                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                            $logo_update = ', logo = ?';
                            $logo_params = 'img/' . $logo_filename;
                        }
                    }
                    
                    if ($logo_update) {
                        $stmt = $mysqli->prepare("UPDATE parceiros SET descricao = ?, url = ?" . $logo_update . " WHERE id = ?");
                        $stmt->bind_param("sssi", $descricao, $url, $logo_params, $id);
                    } else {
                        $stmt = $mysqli->prepare("UPDATE parceiros SET descricao = ?, url = ? WHERE id = ?");
                        $stmt->bind_param("ssi", $descricao, $url, $id);
                    }
                    
                    if ($stmt->execute()) {
                        $message = 'Parceiro atualizado com sucesso!';
                        updateParceirosSection();
                    } else {
                        $error = 'Erro ao atualizar parceiro.';
                    }
                    $stmt->close();
                } else {
                    $error = 'Todos os campos são obrigatórios.';
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                // Obter logo para deletar arquivo
                $stmt = $mysqli->prepare("SELECT logo FROM parceiros WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $parceiro = $result->fetch_assoc();
                $stmt->close();
                
                if ($parceiro) {
                    // Deletar arquivo de logo
                    if (file_exists('../' . $parceiro['logo'])) {
                        unlink('../' . $parceiro['logo']);
                    }
                    
                    // Deletar do banco
                    $stmt = $mysqli->prepare("DELETE FROM parceiros WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $message = 'Parceiro removido com sucesso!';
                        updateParceirosSection();
                    } else {
                        $error = 'Erro ao remover parceiro.';
                    }
                    $stmt->close();
                }
                break;
        }
    }
}

// Função para atualizar a seção de parceiros no index.html
function updateParceirosSection() {
    global $mysqli;
    
    // Buscar todos os parceiros ordenados
    $result = $mysqli->query("SELECT * FROM parceiros ORDER BY ordem ASC");
    $parceiros = $result->fetch_all(MYSQLI_ASSOC);
    
    // Gerar HTML dos parceiros
    $parceiros_html = '';
    foreach ($parceiros as $parceiro) {
        $descricao = $parceiro['descricao']; // Já contém HTML
        $logo = htmlspecialchars($parceiro['logo'], ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($parceiro['url'], ENT_QUOTES, 'UTF-8');
        
        $parceiros_html .= "\n                                <div class=\"swiper-slide\">\n";
        $parceiros_html .= "                                    <div class=\"flex flex-col items-center h-full\">\n";
        $parceiros_html .= "                                        <div class=\"partner-logo-container flex items-center justify-center h-16 mb-6\">\n";
        $parceiros_html .= "                                            <img src=\"{$logo}\" alt=\"Parceiro\" class=\"h-12 w-auto object-contain\">\n";
        $parceiros_html .= "                                        </div>\n";
        $parceiros_html .= "                                        <h3 class=\"text-white text-lg font-medium text-center mb-2 h-14 flex items-center justify-center\">\n";
        $parceiros_html .= "                                            {$descricao}\n";
        $parceiros_html .= "                                        </h3>\n";
        $parceiros_html .= "                                        <a href=\"{$url}\" target=\"_blank\" class=\"text-sm hover:underline mt-auto text-center break-all sm:break-normal\">\n";
        $parceiros_html .= "                                            {$url}\n";
        $parceiros_html .= "                                        </a>\n";
        $parceiros_html .= "                                    </div>\n";
        $parceiros_html .= "                                </div>";
    }
    
    // Ler o arquivo index.html
    $index_path = '../index.html';
    $content = file_get_contents($index_path);
    
    if ($content !== false) {
        // Padrão para encontrar e substituir o conteúdo do swiper-wrapper
    $pattern = '/(<div class="swiper-wrapper" id="parceiros-wrapper">)(.*?)(<\/div>\s*<!-- Exemplo de como adicionar)/s';
    $replacement = '$1' . $parceiros_html . "\n                            " . '$3';
        
        $new_content = preg_replace($pattern, $replacement, $content);
        
        if ($new_content !== null) {
            file_put_contents($index_path, $new_content);
        }
    }
}

// Buscar parceiros para exibição
$result = $mysqli->query("SELECT * FROM parceiros ORDER BY ordem ASC");
$parceiros = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Parceiros - Bullseye Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <h1 class="text-2xl font-bold text-gray-900">Gerenciar Parceiros</h1>
                    <div class="flex items-center space-x-4">
                        <a href="index.php" class="text-blue-600 hover:text-blue-800">← Voltar ao Dashboard</a>
                        <a href="../index.html" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Ver Site</a>
                        <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Sair</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Mensagens -->
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Formulário para Adicionar Novo Parceiro -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Adicionar Novo Parceiro</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL do Site</label>
                        <input type="url" name="url" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                        <textarea name="descricao" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Use <br> para quebras de linha"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                        <input type="file" name="logo" accept="image/*" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Formatos aceitos: JPG, PNG, GIF, SVG. Tamanho máximo: 2MB</p>
                    </div>
                    
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors">
                        Adicionar Parceiro
                    </button>
                </form>
            </div>

            <!-- Lista de Parceiros Atuais -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold">Parceiros Atuais</h2>
                </div>
                
                <div class="p-6">
                    <?php if (empty($parceiros)): ?>
                        <p class="text-gray-500 text-center py-8">Nenhum parceiro cadastrado ainda.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($parceiros as $parceiro): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="text-center mb-4">
                                    <img src="../<?php echo htmlspecialchars($parceiro['logo']); ?>" alt="Parceiro" class="mx-auto h-16 object-contain mb-2">
                                    <p class="text-sm text-gray-600 mb-2"><?php echo $parceiro['descricao']; ?></p>
                                    <a href="<?php echo htmlspecialchars($parceiro['url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm"><?php echo htmlspecialchars($parceiro['url']); ?></a>
                                </div>
                                    
                                    <div class="flex space-x-2">
                                        <button onclick="editParceiro(<?php echo $parceiro['id']; ?>, '<?php echo htmlspecialchars($parceiro['descricao'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($parceiro['url'], ENT_QUOTES); ?>')" class="flex-1 bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                            Editar
                                        </button>
                                        
                                        <form method="POST" class="flex-1" onsubmit="return confirm('Tem certeza que deseja remover este parceiro?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $parceiro['id']; ?>">
                                            <button type="submit" class="w-full bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Editar Parceiro</h3>
                </div>
                
                <form id="editForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL do Site</label>
                        <input type="url" name="url" id="editUrl" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                        <textarea name="descricao" id="editDescricao" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo (deixe em branco para manter o atual)</label>
                        <input type="file" name="logo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Salvar Alterações
                        </button>
                        <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editParceiro(id, descricao, url) {
            document.getElementById('editId').value = id;
            document.getElementById('editDescricao').value = descricao;
            document.getElementById('editUrl').value = url;
            document.getElementById('editModal').classList.remove('hidden');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        
        // Fechar modal ao clicar fora
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>