<?php

$pageTitle = "Painel Eletrônico - Início";

require_once BASE_PATH . 'app/Views/layouts/header.php';
?>

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

<div class="row d-flex justify-content-center p-3 m-0">
    <div class="col-md-4">

        <a class="botao_index mb-4 " href="<?= BASE_URL ?>web/brotas">BROTAS</a>


        <a class="botao_index mb-4 " href="<?= BASE_URL ?>web/cabula">CABULA</a>


    </div>
</div>






<?php
// Carrega o rodapé do layout principal
require_once BASE_PATH . 'app/Views/layouts/footer.php';
?>