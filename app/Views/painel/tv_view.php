<?php

$pageTitle = 'Painel TV - ' . ucfirst($data['campus']);

require_once BASE_PATH . 'app/Views/layouts/header.php';

// --- Configurações do Carrossel ---
$reservasPorSlide = 12;
$intervaloSlideReservas = 2000;
$intervaloSlidePublicidade = 1500;

// Variáveis para controle do slide ativo
$first_item_active = true; // Para garantir que apenas o primeiro item total seja ativo
?>

<div id="info-header">
    <header>
        <div class="row m-0">
            <div class="col-3 col-md-4 text-start d-flex align-items-center">
                <img src="<?= BASE_URL ?>public/assets/img/logo_header.svg" alt="" width="220px" class="img-fluid">
            </div>
            <div class="col-8 col-md-7 text-start d-flex align-items-center">
                <h1>ENCONTRE SUA SALA OU LABORATÓRIO</h1>
            </div>
        </div>
    </header>

    <?php require_once BASE_PATH . 'app/Views/partials/date_header.php'; ?>
</div>

<div id="painelCarouselTV" class="carousel carousel-dark slide" data-bs-ride="carousel"
    data-campus="<?= htmlspecialchars($data['campus']) ?>" data-base-url="<?= BASE_URL ?>">
    <div class="carousel-inner">

        <?php
        if (!empty($data['reservas'])) {
            $slidesDeReservas = array_chunk($data['reservas'], $reservasPorSlide);

            foreach ($slidesDeReservas as $index => $reservasDoSlide) {
                $active_class = $first_item_active ? 'active' : ''; // Ativa o primeiro slide geral
                $first_item_active = false; // Desativa para os próximos itens
                ?>
                <div class="carousel-item <?= $active_class ?>" data-bs-interval="<?= $intervaloSlideReservas ?>">
                    <div class="table-responsive tabela">
                        <table class="table table-striped ">
                            <?php require BASE_PATH . 'app/Views/partials/thead.php'; ?>
                            <tbody>
                                <?php foreach ($reservasDoSlide as $row): ?>
                                    <?php
                                    $componente = $row['compc_componente'] ?? $row['res_componente_atividade_nome'] ?? $row['res_nome_atividade'] ?? '';
                                    ?>
                                    <tr valign="middle">
                                        <td class="border_radiu_row_start">
                                            <?= htmlspecialchars(date('H:i', strtotime($row['res_hora_inicio']))) ?>
                                        </td>
                                        <td><?= htmlspecialchars(date('H:i', strtotime($row['res_hora_fim']))) ?></td>
                                        <td><?= htmlspecialchars($row['curs_curso']) ?></td>
                                        <td><?= htmlspecialchars($componente) ?></td>
                                        <td><?= htmlspecialchars($row['res_modulo']) ?></td>
                                        <td><?= htmlspecialchars($row['res_professor']) ?></td>
                                        <td class="border_radiu_row_end"><?= htmlspecialchars($row['esp_nome_local_resumido']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
        } else {
            // Se não houver reservas, o primeiro item a ser processado será este
            // Se first_item_active ainda for true, este será o slide ativo
            ?>
            <div class="carousel-item <?= $first_item_active ? 'active' : '' ?>"
                data-bs-interval="<?= $intervaloSlideReservas ?>">
                <div class="d-flex justify-content-center align-items-center" style="height: 50vh;">
                    <h4>Nenhuma programação encontrada para hoje.</h4>
                </div>
            </div>
            <?php
            $first_item_active = false; // Desativa para os próximos itens
        }
        ?>

        <?php if (!empty($data['publicidades'])): ?>
            <?php foreach ($data['publicidades'] as $publicidade): ?>
                <div class="carousel-item publicidade-item <?= $first_item_active ? 'active' : '' ?>"
                    data-bs-interval="<?= $intervaloSlidePublicidade ?>">
                    <?php
                    // Caminho da imagem/vídeo para o Painel TV
                    // BASE_URL já é 'http://localhost:8080/painel/'
                    // caminho_imagem do BD já é 'files/banners/nome.jpg'
                    // Isso gera http://localhost:8080/painel/files/banners/nome.jpg
                    // Mas o Painel TV serve os arquivos da PASTA PUBLIC, então precisamos:
                    $mediaSrcForPainelTV = BASE_URL . 'public/' . htmlspecialchars($publicidade['caminho_imagem']);
                    ?>
                    <?php if ($publicidade['media_type'] === 'video'): ?>
                        <video class="d-block w-100 vh-100" style="object-fit: cover;" autoplay muted loop playsinline>
                            <source src="<?= $mediaSrcForPainelTV ?>"
                                type="video/<?= pathinfo($publicidade['caminho_imagem'], PATHINFO_EXTENSION) ?>">
                            Seu navegador não suporta o formato de vídeo.
                        </video>
                    <?php else: ?>
                        <div class="img_public_dinamica vh-100" style="background-image: url('<?= $mediaSrcForPainelTV ?>');">
                        </div>
                    <?php endif; ?>

                </div>
                <?php
                $first_item_active = false; // Garante que apenas o primeiro item de publicidade (se for o primeiro geral) seja ativo
                endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<?php

require_once BASE_PATH . 'app/Views/layouts/footer.php';
?>
<script src="<?= BASE_URL ?>public/assets/js/header_tv.js"></script>