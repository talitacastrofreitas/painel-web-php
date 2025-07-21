<?php
$pageTitle = $data['pageTitle'];
require_once BASE_PATH . 'app/Views/layouts/header.php';
?>
<style>
    .table {
        border-collapse: collapse !important;

    }

    .drag-handle {
        cursor: grab;
    }

    .drag-handle:active {
        cursor: grabbing;
    }
</style>
<header class="header_index">
    <div class="row m-0 d-flex align-items-center">
        <div class="col-md-8 text-center text-md-start order-md-1 order-2">
            <h1>PAINEL ELETRÔNICO BAHIANA</h1>
        </div>
        <div class="col-md-4 text-center text-md-end order-md-2 order-1">
            <img src="<?= BASE_URL ?>public/assets/img/logo_header.svg" alt="" class="img-fluid">
        </div>
    </div>
</header>

<div class="container">
    <div class="row justify-content-center m-0">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?= BASE_URL ?>admin/logout" class="btn botao text-uppercase">Sair</a>
            </div>
            <div class="desc">
                <h3 class="text-uppercase">Instruções para Upload de Publicidade</h3>

                <ol>
                    <li class="mb-2">O preenchimento do título é <strong>opcional</strong>.</li>
                    <li class="mb-2">Formatos aceitos: <strong>PNG, JPG, JPEG, GIF, MP4, WEBM</strong>.</li>
                    <li class="mb-2">Tamanho máximo por arquivo: <strong>10 MB</strong>.</li>
                    <li class="mb-2">Novas imagens são cadastradas como <strong>"Ativas"</strong> e adicionadas ao
                        <strong>final da lista</strong>, Você pode
                        alterar a ordem a qualquer momento na tabela.
                    </li>
                </ol>

            </div>


            <!-- FORMULÁRIO DE UPLOAD -->
            <form action="<?= BASE_URL ?>admin/upload" method="POST" enctype="multipart/form-data" class="mb-5">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título ou Nome da Publicidade</label>
                    <input type="text" class="form-control" id="titulo" name="titulo"
                        placeholder="Ex: publicidade_01.png">
                </div>

                <div class="mb-3">
                    <label for="imagem" class="form-label">Arquivo</label>
                    <input class="form-control" type="file" id="imagem" name="imagem"
                        accept="image/png, image/jpeg, image/gif, video/mp4, video/webm" required>
                </div>

                <div class="d-flex justify-content-between">
                    <small class="form-text text-muted">Apenas arquivos .png, .jpg, .gif, .mp4, .webm</small>
                    <button type="submit" class="btn botao text-uppercase">Enviar</button>

                </div>

            </form>



            <!-- TABELA COM IMAGENS/VIDEOS DE PUBLICIDADE CADASTRADAS -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-white ">
                            <th></th>
                            <th class="text-center" style="width: 15%;">Prévia</th>
                            <th>Nome do arquivo</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody id="sortable-tbody">
                        <?php
                        $total_items = count($data['publicidades']);
                        foreach ($data['publicidades'] as $index => $pub):
                            ?>
                            <tr data-id="<?= $pub['id'] ?>">
                                <td class="text-cinza-escuro">
                                    <i class="fa-solid fa-grip-vertical drag-handle" style="cursor: grab;"
                                        title="Arrastar para reordenar"></i>
                                </td>
                                <td class="text-center" style="width: 60px;">
                                    <?php if ($pub['media_type'] === 'video'): ?>
                                        <!-- Se for um vídeo, mostra um ícone de filme -->
                                        <i class="fa-solid fa-film fa-2x text-muted" title="Vídeo"></i>
                                    <?php else: ?>
                                        <!-- Se for uma imagem, mostra a miniatura -->
                                        <img src="<?= BASE_URL . htmlspecialchars($pub['caminho_imagem']) ?>" alt="Prévia"
                                            height="40" class="rounded">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($pub['titulo']) ?></strong><br>
                                    <small
                                        class="text-muted"><?= htmlspecialchars(basename($pub['caminho_imagem'])) ?></small>
                                </td>

                                <td>
                                    <span
                                        class="badge rounded-1 px-2 fw-medium text-center text-uppercase bg-<?= $pub['ativo'] ? 'opaco-verde-fraco text-verde-fraco' : 'opaco-cinza text-cinza-escuro' ?>">
                                        <?= $pub['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>

                                    <div class="d-flex justify-content-end">
                                        <a href="<?= BASE_URL ?>admin/toggleStatus/<?= $pub['id'] ?>" class=" ms-3">
                                            <?= $pub['ativo'] ? '<i class="text-verde-fraco fa-solid fa-toggle-on fa-lg me-3"></i>' : '<i class="text-cinza-escuro fa-solid fa-toggle-off fa-lg me-3"></i>' ?>
                                        </a>

                                        <a href="<?= BASE_URL ?>admin/excluir/<?= $pub['id'] ?>"
                                            class="btn-excluir text-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<?php require_once BASE_PATH . 'app/Views/layouts/footer.php'; ?>

<?php if (isset($_SESSION['colaborador_logado']) && $_SESSION['colaborador_logado']): ?>
    <script>

        $(document).ready(function () {
            const INACTIVITY_TIMEOUT_JS = 60000; // 1 minuto para o alerta de logout (em milissegundos)
            const KEEP_ALIVE_INTERVAL = 45000;  // 45 segundos para enviar o sinal keep-alive (menos que 1 min)
            const LOGOUT_URL = '<?= BASE_URL ?>admin/logout';
            let logoutTimer;       // Timer para o SweetAlert de logout
            let keepAliveTimer;    // Timer para o sinal keep-alive AJAX
            let isLogoutProcessActive = false;

            // Função principal para iniciar/resetar ambos os timers
            function resetActivityTimers() {

                // Se o processo de logout já está ativo, não faça nada
                if (isLogoutProcessActive) {
                    return;
                }

                // 1. Resetar o timer de logout
                clearTimeout(logoutTimer);
                logoutTimer = setTimeout(doLogout, INACTIVITY_TIMEOUT_JS);

                // 2. Resetar e (re)iniciar o timer do keep-alive
                clearInterval(keepAliveTimer); // Limpa qualquer keep-alive anterior
                keepAliveTimer = setInterval(sendKeepAliveSignal, KEEP_ALIVE_INTERVAL); // Envia periodicamente

                // Envia um sinal keep-alive imediatamente na primeira atividade (ou reinício)
                sendKeepAliveSignal();
            }

            function doLogout() {

                if (isLogoutProcessActive) {
                    console.log("doLogout já está em execução, ignorando chamada duplicada.");
                    return;
                }
                isLogoutProcessActive = true; // Define a flag como true para indicar que o processo começou

                // Parar o timer de keep-alive para não continuar enviando após o logout
                clearInterval(keepAliveTimer);

                Swal.fire({
                    title: 'Sessão Expirada',
                    text: 'Você foi desconectado por inatividade.',
                    icon: 'info',
                    showConfirmButton: true,
                    confirmButtonText: 'Ok!',
                    timer: false,
                    timerProgressBar: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    willClose: () => {

                        window.location.href = LOGOUT_URL;
                    }
                }).then((result) => {

                    isLogoutProcessActive = false;
                });

            }

            function sendKeepAliveSignal() {

                if (isLogoutProcessActive) {
                    console.log("Processo de logout já ativo, não enviando keep-alive.");
                    return;
                }

                $.ajax({
                    url: '<?= BASE_URL ?>admin/keepAlive',
                    type: 'POST',
                    success: function (response) {

                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                        if (jqXHR.status === 401) {
                            clearTimeout(logoutTimer);    // Limpa o timer principal (se ainda ativo)
                            clearInterval(keepAliveTimer); // Para o envio de keep-alive
                            doLogout(); // Chama a função de logout para exibir o SweetAlert
                        }
                    }
                });
            }

            // Inicia os timers quando a página carrega.
            resetActivityTimers();

            // Reseta os timers em qualquer atividade do usuário na página
            $(document).on('mousemove keypress click scroll', function () {
                resetActivityTimers();
            });
        });
    </script>
<?php endif; ?>