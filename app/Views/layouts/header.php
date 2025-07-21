<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['pageTitle'] ?? 'Painel Eletrônico' ?></title>

    <link href="<?= BASE_URL ?>public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>public/assets/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- FAVICON -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>public/assets/img/favicon.ico">
    <!-- FONTAWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <?php if (isset($data['css_file'])): ?>
        <link href="<?= BASE_URL ?>public/assets/css/<?= htmlspecialchars($data['css_file']) ?>" rel="stylesheet">
    <?php endif; ?>
</head>

<body>
    <div id="preloader">
        <div class="inner">
            <div class="custom-loader"></div>
        </div>
    </div>