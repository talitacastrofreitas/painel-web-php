<?php

$pageTitle = 'Painel Web - ' . ucfirst($data['campus']);

require_once BASE_PATH . 'app/Views/layouts/header.php';
?>
<header>
    <div class="row m-0">
        <div class="col-3 col-md-4 text-start d-flex align-items-center">
            <img src="<?= BASE_URL ?>public/assets/img/logo_header.svg" alt="" width="220px" class="img-fluid">
        </div>
        <div class="col-8 col-md-7 text-start d-flex align-items-center">
            <h1 class="title-painel">PROGRAMAÇÃO DIÁRIA</h1>
        </div>
        <div class="col-1 col-md-1 text-end d-flex align-items-center">
            <button class="botao_nav d-flex align-items-center" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-list"
                    viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
                </svg>
            </button>
        </div>
    </div>
</header>

<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">





            <?php require_once BASE_PATH . 'app/Views/partials/date_header.php'; ?>


            <div class="row">
                <div class="col-lg-12">


                    <div class="card rounded-0">

<style>

    #tabela tbody tr td {
        border-bottom: 1px solid #e0e0e0 !important; 
    }

</style>

                        <div class="card-body p-0">
                         

                                 <table id="tabela"
                                class="table dt-responsive nowrap align-middle dataTable no-footer dtr-inline collapsed "
                                style="width:100%">

                                <thead>
                                    <tr class="teste">
                                        <th nowrap="nowrap">Início</th>
                                        <th>Fim</th>
                                        <th>Curso</th>
                                        <th>Componente curricular / Atividade</th>
                                        <th>Módulo</th>
                                        <th>Professor / Solicitante</th>
                                        <th>Sala / Laboratório</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php // Verifica se existem reservas para exibir
                                    if (!empty($data['reservas'])): ?>

                                        <?php // para cada reserva, cria uma linha na tabela
                                            foreach ($data['reservas'] as $row): ?>
                                            <?php
                                            // Lógica para definir o nome do componente
                                            $componente = '';
                                            if (!empty($row['res_componente_atividade'])) {
                                                $componente = $row['compc_componente'];
                                            } elseif (!empty($row['res_componente_atividade_nome'])) {
                                                $componente = $row['res_componente_atividade_nome'];
                                            } elseif (!empty($row['res_nome_atividade'])) {
                                                $componente = $row['res_nome_atividade'];
                                            }
                                            ?>
                                            <tr style="font-size: 11.2px;">
                                                <td>
                                                    <?= htmlspecialchars(date('H:i', strtotime($row['res_hora_inicio']))) ?>
                                                </td>
                                                <td><?= htmlspecialchars(date('H:i', strtotime($row['res_hora_fim']))) ?></td>
                                                <td><?= htmlspecialchars($row['curs_curso']) ?></td>
                                                <td><?= htmlspecialchars($componente) ?></td>
                                                <td><?= htmlspecialchars($row['res_modulo']) ?></td>
                                                <td><?= htmlspecialchars($row['res_professor']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($row['esp_nome_local_resumido']) ?>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>

                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once BASE_PATH . 'app/Views/partials/nav.php'; ?>
<?php
require_once BASE_PATH . 'app/Views/layouts/footer.php';
?>