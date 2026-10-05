<?php
if (!defined('GLPI_ROOT')) {
    include ("../../../inc/includes.php");
}

Session::checkLoginUser();
global $DB, $CFG_GLPI;

$user = new User();
$user->getFromDB(Session::getLoginUserID());
$firstname = $user->fields['firstname'];
$realname = $user->fields['realname'];
$display_name = trim($firstname) ? trim($firstname) : (trim($realname) ? trim($realname) : $user->fields['name']);
$primeiro_nome = explode(' ', $display_name)[0];
$primeiro_nome = explode('-', $primeiro_nome)[0];

$picture = $user->fields['picture'] ?? '';
$avatar_url = $picture ? $CFG_GLPI['root_doc'] . '/front/document.send.php?file=_pictures/' . $picture : '';

// Verifica se o usuário tem perfil de Admin na sessão
$has_super_admin = false;
$super_admin_prof_id = 0;
if (isset($_SESSION['glpiprofiles'])) {
    foreach ($_SESSION['glpiprofiles'] as $id => $prof) {
        if (stripos($prof['name'], 'admin') !== false) {
            $has_super_admin = true;
            $super_admin_prof_id = $id;
            break;
        }
    }
}

if (isset($_GET['switch_to_admin']) && $has_super_admin) {
    Session::changeProfile($super_admin_prof_id);
    header("Location: " . $CFG_GLPI['root_doc'] . "/front/central.php");
    exit();
}

// Fetch all categories and build tree based on hyphen separation
$tree = [];
$departments = [];
$iterator = $DB->request([
    'SELECT' => ['id', 'name'],
    'FROM'   => 'glpi_itilcategories',
    'ORDER'  => 'name ASC'
]);

foreach ($iterator as $row) {
    // Apenas considera categorias que possuem hífen no nome
    if (strpos($row['name'], ' - ') !== false) {
        $parts = explode(' - ', $row['name'], 2);
        $dept = trim($parts[0]);
        $msg = trim($parts[1]);
        
        if (!isset($tree[$dept])) {
            $tree[$dept] = [];
            $departments[] = $dept;
        }
        
        $tree[$dept][] = [
            'id' => $row['id'],
            'name' => $msg
        ];
    }
}
// Ordena os departamentos alfabeticamente
sort($departments);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Chamado - Ativa Locação</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #F7F8FA; color: #333; }
        a { text-decoration: none; color: inherit; }
        
        /* Topbar reutilizada */
        .topbar { display: flex; justify-content: space-between; align-items: center; background-color: #FFFFFF; padding: 0 40px; height: 70px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .topbar-left { display: flex; align-items: center; }
        .logo { display: flex; align-items: center; font-weight: 800; font-size: 20px; color: #0b457e; margin-right: 40px; }
        .nav-links a { display: flex; align-items: center; padding: 25px 0; color: #555; font-weight: 600; font-size: 14px; border-bottom: 2px solid transparent; }
        .nav-links a.active { color: #333; border-bottom: 2px solid #333; }
        .nav-links a i { margin-right: 8px; }
        .topbar-right { display: flex; align-items: center; }
        .search-icon { font-size: 16px; color: #666; margin-right: 20px; cursor: pointer; }
        .user-profile { display: flex; align-items: center; position: relative; cursor: pointer; padding-bottom: 15px; margin-bottom: -15px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background-color: #E2E4E8; display: flex; align-items: center; justify-content: center; color: #666; margin-right: 12px; font-size: 14px; background-size: cover; background-position: center; overflow: hidden; }
        .user-name { font-size: 14px; color: #333; }
        .dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background-color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-radius: 8px; padding: 10px 0; min-width: 220px; z-index: 100; border: 1px solid #EEE; }
        .dropdown-menu::before { content: ''; position: absolute; top: -20px; left: 0; width: 100%; height: 20px; }
        .user-profile:hover .dropdown-menu { display: block; }
        .dropdown-menu a { display: block; padding: 12px 20px; font-size: 14px; color: #444; font-weight: 500; }
        .dropdown-menu a:hover { background-color: #F7F8FA; color: #0b457e; }
        .dropdown-menu i { margin-right: 12px; width: 16px; text-align: center; color: #888; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 0 30px; }
        
        .header-title { font-size: 28px; font-weight: 800; color: #1A1F36; margin-bottom: 30px; }
        
        .form-card { background: #FFFFFF; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 8px; }
        
        .form-control { width: 100%; padding: 14px 16px; border-radius: 8px; border: 1px solid #E5E7EB; background-color: #F3F4F6; font-size: 14px; color: #374151; outline: none; transition: all 0.2s; font-family: 'Inter', sans-serif; }
        .form-control:focus { border-color: #0b457e; background-color: #FFFFFF; box-shadow: 0 0 0 3px rgba(11, 69, 126, 0.1); }
        select.form-control { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 15px center; background-size: 16px; padding-right: 40px; }
        
        .reply-textarea { width: 100%; min-height: 150px; border: 1px solid #E5E7EB; border-radius: 8px; padding: 15px; font-size: 14px; font-family: inherit; resize: vertical; outline: none; transition: border-color 0.3s; background: #F3F4F6; }
        .reply-textarea:focus { border-color: #0b457e; background: #FFFFFF; box-shadow: 0 0 0 3px rgba(11, 69, 126, 0.1); }
        
        /* Drag and Drop area */
        .drag-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(11, 69, 126, 0.9); z-index: 9999; justify-content: center; align-items: center; color: #FFF; font-size: 24px; font-weight: bold; flex-direction: column; }
        .drag-overlay i { font-size: 60px; margin-bottom: 20px; }
        
        .file-list { display: flex; flex-direction: column; gap: 5px; width: 100%; margin-top: 10px; }
        .file-item { display: flex; align-items: center; justify-content: space-between; background: #F9FAFB; padding: 10px 15px; border-radius: 6px; font-size: 13px; color: #374151; border: 1px solid #E5E7EB; }
        .file-item i.remove-file { color: #EF4444; cursor: pointer; margin-left: 10px; }
        
        .btn-attach { background: #F3F4F6; color: #4B5563; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.3s; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #E5E7EB; margin-top: 10px; }
        .btn-attach:hover { background: #E5E7EB; }
        
        .btn-submit { background: #0b457e; color: #FFF; border: none; padding: 14px 30px; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: background 0.3s; width: 100%; margin-top: 20px; }
        .btn-submit:hover { background: #08335e; }
        
    </style>
</head>
<body>

    <!-- Drag & Drop Overlay Global -->
    <div class="drag-overlay" id="drag-overlay">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <span>Solte o arquivo para anexar ao chamado</span>
    </div>

    <div class="topbar">
        <div class="topbar-left">
            <div class="logo">
                <img src="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/logo.php" alt="Ativa Locação" style="height: 45px;">
            </div>
            <nav class="nav-links">
                <a href="<?=$CFG_GLPI['root_doc']?>/plugins/painel/front/painel.php"><i class="fa-regular fa-window-maximize"></i> Chamados</a>
            </nav>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <div class="avatar" style="<?= isset($avatar_url) && $avatar_url ? 'background-image: url('.$avatar_url.');' : '' ?>"><?= isset($avatar_url) && $avatar_url ? '' : '<i class="fa-solid fa-user"></i>' ?></div>
                <span class="user-name">Olá, <strong><?= htmlspecialchars($primeiro_nome) ?></strong> <i class="fa-solid fa-caret-down" style="font-size: 10px; margin-left: 5px; color: #888;"></i></span>
                
                <div class="dropdown-menu">
                    <?php if ($has_super_admin): ?>
                        <a href="?create_ticket=1&switch_to_admin=1"><i class="fa-solid fa-user-shield"></i> Trocar para Admin</a>
                    <?php endif; ?>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?custom_profile=1"><i class="fa-solid fa-gear"></i> Minhas configurações</a>
                    <a href="<?=$CFG_GLPI['root_doc']?>/front/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <h1 class="header-title">Abrir Novo Chamado</h1>
        
        <form action="<?=$CFG_GLPI['root_doc']?>/front/helpdesk.public.php?create_ticket=1" method="POST" enctype="multipart/form-data" class="form-card" id="create-ticket-form">
            <input type="hidden" name="_glpi_csrf_token" value="<?=Session::getNewCSRFToken()?>">
            
            <div class="form-group">
                <label>Departamento</label>
                <select id="departamento" class="form-control" required onchange="loadSubCategories(this.value)">
                    <option value="">- Selecione -</option>
                    <?php foreach($departments as $dep): ?>
                        <option value="<?=htmlspecialchars($dep)?>"><?=htmlspecialchars($dep)?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tipo de Mensagem</label>
                <select id="tipo_mensagem" name="itilcategories_id" class="form-control" required disabled>
                    <option value="">- Selecione -</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Prioridade</label>
                <select name="priority" class="form-control" required>
                    <option value="">- Selecione -</option>
                    <option value="1">Muito Baixa</option>
                    <option value="2">Baixa</option>
                    <option value="3" selected>Média</option>
                    <option value="4">Alta</option>
                    <option value="5">Muito Alta</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Assunto</label>
                <input type="text" name="name" class="form-control" placeholder="Assunto do chamado" required>
            </div>
            
            <div class="form-group">
                <label>Tipo de Chamado</label>
                <select name="type" class="form-control" required>
                    <option value="">- Selecione -</option>
                    <option value="1">Incidente</option>
                    <option value="2">Requisição</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Mensagem</label>
                <textarea name="content" id="content-textarea" class="reply-textarea" placeholder="Descreva aqui o seu chamado"></textarea>
                
                <div id="file-list" class="file-list"></div>
                <input type="file" id="file-input" style="display: none;" multiple>
                
                <button type="button" class="btn-attach" onclick="document.getElementById('file-input').click();">
                    <i class="fa-solid fa-paperclip"></i> Adicionar Anexo
                </button>
            </div>
            
            <button type="submit" name="add" class="btn-submit">Abrir Chamado</button>
            <input type="hidden" name="add" value="1">
        </form>
    </div>

    <script>
        // TinyMCE Initialization
        window.onload = function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#content-textarea',
                    plugins: 'image link lists table autolink paste',
                    toolbar: 'bold italic underline forecolor | alignleft aligncenter alignright alignjustify | numlist bullist | blocks | link image table | removeformat',
                    toolbar_location: 'bottom',
                    menubar: false,
                    statusbar: false,
                    paste_data_images: false,
                    height: 250,
                    branding: false,
                    content_style: "html { cursor: text; } body { font-family: Inter, sans-serif; font-size: 14px; background-color: #F3F4F6; }",
                    setup: function (editor) {
                        editor.on('init', function() {
                            editor.getDoc().documentElement.addEventListener('click', function() {
                                editor.focus();
                            });
                        });
                        
                        editor.on('change', function () { editor.save(); });
                        
                        editor.on('paste', function (e) {
                            if (e.clipboardData && e.clipboardData.files.length > 0) {
                                e.preventDefault();
                                if (typeof handleGlobalFiles === 'function') handleGlobalFiles(e.clipboardData.files);
                            }
                        });
                        
                        editor.on('drop', function (e) {
                            if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                                e.preventDefault(); e.stopPropagation();
                                if (typeof handleGlobalFiles === 'function') handleGlobalFiles(e.dataTransfer.files);
                            }
                        });
                    }
                });
            }
        };

        // Lógica de Categorias (Em Árvore)
        var categoryTree = <?= json_encode($tree) ?>;
        
        window.loadSubCategories = function(deptName) {
            var tipoMensagem = document.getElementById('tipo_mensagem');
            tipoMensagem.innerHTML = '<option value="">- Selecione -</option>';
            tipoMensagem.disabled = true;
            
            if (deptName && categoryTree[deptName]) {
                var cats = categoryTree[deptName];
                cats.forEach(function(cat) {
                    var option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    tipoMensagem.appendChild(option);
                });
                tipoMensagem.disabled = false;
            }
        };

        // Global Drag & Drop + Paste logic for Attachments
        const dataTransfer = new DataTransfer();
        const fileListElement = document.getElementById('file-list');
        const fileInput = document.getElementById('file-input');
        const dragOverlay = document.getElementById('drag-overlay');
        const form = document.getElementById('create-ticket-form');
        
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024, dm = decimals < 0 ? 0 : decimals, sizes = ['Bytes', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function handleGlobalFiles(files) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                let isDuplicate = false;
                for (let j = 0; j < dataTransfer.files.length; j++) {
                    if (dataTransfer.files[j].name === file.name && dataTransfer.files[j].size === file.size) {
                        isDuplicate = true; break;
                    }
                }
                
                if (!isDuplicate) {
                    let ext = file.name.split('.').pop().toLowerCase();
                    let finalFile = file;
                    if (file.type.startsWith('image/') && !file.name.includes('.')) {
                        ext = file.type.split('/')[1];
                        if (ext === 'jpeg') ext = 'jpg';
                        finalFile = new File([file], 'colagem_' + Date.now() + '.' + ext, {type: file.type});
                    }
                    
                    dataTransfer.items.add(finalFile);
                    addFileToUI(finalFile);
                }
            }
        }
        
        function addFileToUI(file) {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            let iconClass = 'fa-file';
            if (file.type.startsWith('image/')) iconClass = 'fa-image';
            else if (file.type === 'application/pdf') iconClass = 'fa-file-pdf';
            
            fileItem.innerHTML = `
                <div><i class="fa-solid ${iconClass}" style="margin-right: 8px;"></i> ${file.name} <span style="color:#888; margin-left: 5px;">(${formatBytes(file.size)})</span></div>
                <i class="fa-solid fa-xmark remove-file"></i>
            `;
            
            fileItem.querySelector('.remove-file').addEventListener('click', function() {
                const fileName = file.name;
                const newDt = new DataTransfer();
                for (let i = 0; i < dataTransfer.files.length; i++) {
                    if (dataTransfer.files[i].name !== fileName) {
                        newDt.items.add(dataTransfer.files[i]);
                    }
                }
                dataTransfer.items.clear();
                for (let i = 0; i < newDt.files.length; i++) {
                    dataTransfer.items.add(newDt.files[i]);
                }
                fileItem.remove();
            });
            
            fileListElement.appendChild(fileItem);
        }
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleGlobalFiles(this.files);
                this.value = ''; 
            }
        });
        
        document.addEventListener('paste', function(e) {
            if (e.clipboardData && e.clipboardData.files.length > 0) {
                handleGlobalFiles(e.clipboardData.files);
            }
        });
        
        let dragCounter = 0;
        document.addEventListener('dragenter', function(e) {
            e.preventDefault();
            if (e.dataTransfer.types.includes('Files')) {
                dragCounter++;
                dragOverlay.style.display = 'flex';
            }
        });
        
        document.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dragCounter--;
            if (dragCounter === 0) dragOverlay.style.display = 'none';
        });
        
        document.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('drop', function(e) {
            e.preventDefault();
            dragCounter = 0;
            dragOverlay.style.display = 'none';
            if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                handleGlobalFiles(e.dataTransfer.files);
            }
        });
        
        form.addEventListener('submit', function(e) {
            const formData = new FormData(this);
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
                formData.set('content', document.getElementById('content-textarea').value);
            }
            
            for (let i = 0; i < dataTransfer.files.length; i++) {
                formData.append('filename[]', dataTransfer.files[i]);
            }
            
            // Força o envio do parâmetro 'add' pois alguns navegadores ignoram o botão submit no FormData
            formData.append('add', '1');
            
            e.preventDefault();
            
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Enviando...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            }).then(response => {
                if(response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text().then(text => {
                        console.log("SERVER RESPONSE:", text);
                        var trimmed = text.trim();
                        if (trimmed.startsWith('http') || trimmed.startsWith('/')) {
                            window.location.href = trimmed;
                        } else {
                            alert("O servidor respondeu com um erro desconhecido:\n\n" + trimmed.substring(0, 100));
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Abrir Chamado';
                        }
                    });
                }
            }).catch(err => {
                console.error(err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Abrir Chamado';
                alert('Erro ao abrir o chamado. Tente novamente.');
            });
        });
    </script>
</body>
</html>
