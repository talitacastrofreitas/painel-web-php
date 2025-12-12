<?php

$route_prefix = $data['view_type'] ?? 'web';


?>


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