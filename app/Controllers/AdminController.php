<?php
namespace App\Controllers;

use App\Models\PublicidadeModel;
use App\Models\ColaboradorModel;

class AdminController
{

    private function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }


    public function index()
    {
        if (!isset($_SESSION['colaborador_logado']) || !$_SESSION['colaborador_logado']) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit();
        }
        $this->gerenciar();
    }

    public function login()
    {
        $data['pageTitle'] = 'Painel Eletrônico Bahiana - Login';
        $data['css_file'] = 'painel-web.css';

        if (isset($_SESSION['toast_message'])) {

            $isExpectedLogoutMessage = (
                isset($_SESSION['toast_message']['type']) && $_SESSION['toast_message']['type'] === 'success' &&
                isset($_SESSION['toast_message']['message']) && $_SESSION['toast_message']['message'] === 'Você foi desconectado.'
            );

            $isAuthErrorMessage = (
                isset($_SESSION['toast_message']['type']) && $_SESSION['toast_message']['type'] === 'error' &&
                isset($_SESSION['toast_message']['message']) && $_SESSION['toast_message']['message'] === 'E-mail não encontrado ou inválido.'
            );

            if (!$isExpectedLogoutMessage && !$isAuthErrorMessage) {
                unset($_SESSION['toast_message']);

            } else {
                error_log("toast_message FOI MANTIDA (era esperada).");
            }
        } else {
            error_log("Nenhuma toast_message encontrada na sessão.");
        }

        if (!isset($_SESSION['colaborador_logado']) || !$_SESSION['colaborador_logado']) {
            if (isset($_SESSION['LAST_ACTIVITY'])) {
                unset($_SESSION['LAST_ACTIVITY']);
            }
            if (isset($_SESSION['colaborador_email'])) {
                unset($_SESSION['colaborador_email']);
            }
        }

        require_once BASE_PATH . 'app/Views/admin/login_email_view.php';
    }

    public function verifyEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
            global $conn;
            $colaboradorModel = new ColaboradorModel($conn);
            $email = trim($_POST['email']);

            if ($colaboradorModel->checkEmailExists($email)) {
                $_SESSION['colaborador_logado'] = true;
                $_SESSION['colaborador_email'] = $email;
                session_write_close();
                header('Location: ' . BASE_URL . 'admin/gerenciar');
                exit();
            } else {
                $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'E-mail não encontrado ou inválido.'];
                session_write_close();
                header('Location: ' . BASE_URL . 'admin/login');
                exit();
            }
        } else {
            $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Requisição inválida.'];
            session_write_close();
            header('Location: ' . BASE_URL . 'admin/login');
            exit();
        }
    }

    public function logout()
    {
        $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Você foi desconectado.'];
        session_regenerate_id(true);
        session_write_close();
        header('Location: ' . BASE_URL . 'admin/login');
        exit();
    }


    public function keepAlive()
    {

        if (isset($_SESSION['colaborador_logado']) && $_SESSION['colaborador_logado']) {
            $_SESSION['LAST_ACTIVITY'] = time();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Sessão atualizada.']);
            exit();
        } else {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Sessão inválida.']);
            exit();
        }
    }

    public function gerenciar()
    {

        if (!isset($_SESSION['colaborador_logado']) || !$_SESSION['colaborador_logado']) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit();
        }

        global $conn;
        $publicidadeModel = new PublicidadeModel($conn);
        $data['publicidades'] = $publicidadeModel->getAllPublicidades();
        $data['pageTitle'] = 'Painel Eletrônico Bahiana - Gerenciar Publicidade';
        $data['css_file'] = 'painel-web.css';
        require_once BASE_PATH . 'app/Views/admin/gerenciar_publicidade_view.php';
    }

    public function upload()
    {
        if (!isset($_SESSION['colaborador_logado']) || !$_SESSION['colaborador_logado']) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit();
        }


        if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] != 0) {
            $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Erro: O envio do arquivo é obrigatório.'];
            header('Location: ' . BASE_URL . 'admin/gerenciar');
            exit();
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $original_filename = $_FILES["imagem"]["name"];
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));


        $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_video_types = ['mp4', 'webm'];
        $allowed_types = array_merge($allowed_image_types, $allowed_video_types);

        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Erro: Formato de arquivo não permitido.'];
            header('Location: ' . BASE_URL . 'admin/gerenciar');
            exit();
        }

        $media_type = in_array($file_extension, $allowed_video_types) ? 'video' : 'image';


        $base_name_source = !empty($titulo) ? $titulo : pathinfo($original_filename, PATHINFO_FILENAME);
        $nome_base = $this->slugify($base_name_source);


        $upload_dir = 'public/files/banners/';
        $filename = $nome_base . '.' . $file_extension;
        $caminho_para_db = $upload_dir . $filename;
        $caminho_para_servidor = BASE_PATH . 'public/' . $caminho_para_db;

        $contador = 1;
        while (file_exists($caminho_para_servidor)) {
            $filename = $nome_base . '-' . $contador . '.' . $file_extension;
            $caminho_para_db = $upload_dir . $filename;
            $caminho_para_servidor = BASE_PATH . 'public/' . $caminho_para_db;
            $contador++;
        }

        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $caminho_para_servidor)) {
            global $conn;
            $publicidadeModel = new PublicidadeModel($conn);
            $titulo_para_db = !empty($titulo) ? $titulo : $original_filename;


            $publicidadeModel->adicionarPublicidade($caminho_para_db, $titulo_para_db, $media_type);
            $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Mídia enviada com sucesso!'];
        } else {
            $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Erro ao mover o arquivo.'];
        }

        header('Location: ' . BASE_URL . 'admin/gerenciar');
        exit();
    }

    public function toggleStatus($id)
    {

        if (!isset($_SESSION['colaborador_logado']) || !$_SESSION['colaborador_logado']) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit();
        }

        global $conn;
        $publicidadeModel = new PublicidadeModel($conn);
        if ($publicidadeModel->alternarStatus($id)) {
            $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Status alterado com sucesso!'];
        } else {
            $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Erro ao alterar o status.'];
        }
        header('Location: ' . BASE_URL . 'admin/gerenciar');
        exit();
    }

    public function salvarOrdemAjax()
    {

        if (!isset($_SESSION['colaborador_logado']) || !$_SESSION['colaborador_logado']) {

            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Não autorizado. Faça login novamente.']);
            exit();
        }

        if (isset($_POST['ordem']) && is_array($_POST['ordem'])) {
            global $conn;
            $publicidadeModel = new PublicidadeModel($conn);


            foreach ($_POST['ordem'] as $nova_ordem => $id) {

                $publicidadeModel->atualizarOrdem($id, $nova_ordem);
            }

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit();
        }


        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados de ordem inválidos.']);
        exit();
    }

    public function excluir($id)
    {

        if (!isset($_SESSION['colaborador_logado']) || !$_SESSION['colaborador_logado']) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit();
        }

        global $conn;
        $publicidadeModel = new PublicidadeModel($conn);
        if ($publicidadeModel->excluirPublicidade($id)) {
            $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Publicidade excluída com sucesso!'];
        } else {
            $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Erro ao excluir a publicidade.'];
        }
        header('Location: ' . BASE_URL . 'admin/gerenciar');
        exit();
    }

}