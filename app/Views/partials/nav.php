<?php

$route_prefix = $data['view_type'] ?? 'web';


?>


<button class="botao_nav d-flex align-items-center" type="button" data-bs-toggle="offcanvas"
    data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-list"
        viewBox="0 0 16 16">
        <path fill-rule="evenodd"
            d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
    </svg>
</button>


<div class="offcanvas offcanvas-end menu_canva" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1"
    id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title menu_canva_titulo" id="offcanvasRightLabel">Unidades</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="menu_canva_corpo">


            <hr style="border-color: rgba(255,255,255,0.2);">


            <a href="<?= BASE_URL . $route_prefix ?>/brotas" class="botao_canva">BROTAS</a>
            <a href="<?= BASE_URL . $route_prefix ?>/cabula" class="botao_canva">CABULA</a>
        </div>
    </div>
</div>