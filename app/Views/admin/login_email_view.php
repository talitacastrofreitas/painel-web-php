<?php
// Certifique-se de que session_start() está no topo do seu index.php ou autoload.php
$pageTitle = $data['pageTitle'] ?? 'Login';
require_once BASE_PATH . 'app/Views/layouts/header.php'; // Reutiliza o cabeçalho existente
?>
<style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;

    }

    body {
        background-image: url('<?= BASE_URL ?>public/assets/img/bg_login.png');
        background-size: cover;
        background-position: center;
        font-size: 14px;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .main-content-wrapper {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;

    }

    .login-container {
        max-width: 450px;
        margin: 100px auto;
        padding: 30px;
        border-radius: 3px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    h5 {
        font-size: 18px;
    }

    .btn-primary {
        background-color: #f6993f;
        border-color: #f6993f;
        width: 100%;
        padding: 10px;
    }

    .btn-primary:hover {
        background-color: #dd750dff;
        border-color: #dd750dff;
    }
</style>

<div class="main-content-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4 login-container">
                <h5 class="text-center mb-4 fw-semibold">PAINEL ELETRÔNICO <br> BAHIANA</h5>

                <form action="<?= BASE_URL ?>admin/verifyEmail" method="POST">
                    <div class="mb-4">

                        <label for="email" class="form-label fw-semibold">E-mail institucional <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary">Acessar</button>
                </form>
                <div class="text-center mt-4">
                    <a class=" text-decoration-none" href="<?= BASE_URL ?>">Voltar à página inicial</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center text-white mt-4">
        <!-- <p class="mb-3">&copy; <?= date('Y') ?> PAINEL ELETRÔNICO BAHIANA. Todos os direitos reservados.</p> -->
        <img src="<?= BASE_URL ?>public/assets/img/logo_header.svg" alt="" width="200" class="img-fluid">
    </footer>
</div>

<?php require_once BASE_PATH . 'app/Views/layouts/footer.php'; ?>