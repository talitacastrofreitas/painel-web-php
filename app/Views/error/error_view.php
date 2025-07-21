<?php
// Caminho: app/Views/error/error_view.php


require_once BASE_PATH . 'app/Views/layouts/header.php';
?>

<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <!-- Imagem de erro  -->
            <img src="<?= BASE_URL ?>public/assets/img/error_illustration.svg" class="img-fluid mb-4"
                alt="Ilustração de erro" style="max-width: 350px;">

            <!-- Exibe o código e título do erro -->
            <h1 class="display-2 fw-bold"><?= htmlspecialchars($data['error_code']) ?></h1>
            <h2 class="display-5"><?= htmlspecialchars($data['error_title']) ?></h2>

            <!-- Exibe a mensagem  -->
            <p class="lead text-muted my-4">
                <?= htmlspecialchars($data['error_message']) ?>
            </p>

            <!-- Botão para voltar à página inicial -->
            <a href="<?= BASE_URL ?>" class="btn botao btn-lg text-uppercase">
                Voltar ao Início
            </a>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . 'app/Views/layouts/footer.php';
?>