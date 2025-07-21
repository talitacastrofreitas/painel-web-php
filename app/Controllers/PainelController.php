<?php

namespace App\Controllers;



use App\Models\ReservaModel;
use App\Models\PublicidadeModel;
use PDO;

class PainelController
{
    private function carregarDadosDoCampus($campus)
    {
        if ($campus !== 'brotas' && $campus !== 'cabula') {
            $errorController = new ErrorController();
            $errorController->notFound();
        }

        global $conn;
        $reservaModel = new ReservaModel($conn);

        $unidadeId = ($campus === 'brotas') ? 2 : 1;

        return [
            'campus' => $campus,
            'reservas' => $reservaModel->getProgramacaoDoDiaPorCampus($unidadeId)
        ];
    }

    public function index()
    {
        $data['pageTitle'] = 'Painel Eletrônico Bahiana - Web';
        $data['css_file'] = 'painel-web.css';
        $data['view_type'] = 'web';
        require BASE_PATH . 'app/Views/painel/index.php';
    }


    public function web($campus)
    {
        $data = $this->carregarDadosDoCampus($campus);

        $data['pageTitle'] = 'Painel Eletrônico Bahiana - Web - ' . ucfirst($campus);
        $data['css_file'] = 'painel-web.css';
        $data['view_type'] = 'web';

        require BASE_PATH . 'app/Views/painel/web_view.php';
    }

    public function tv($campus = null)
    {
        if (empty($campus)) {
            $campus = 'brotas';
        }

        global $conn;
        $data = $this->carregarDadosDoCampus($campus);

        $publicidadeModel = new PublicidadeModel($conn);
        $publicidades = $publicidadeModel->getPublicidadesAtivas();

        $data['publicidades'] = $publicidades;
        $data['pageTitle'] = 'Painel Eletrônico Bahiana - TV - ' . ucfirst($campus);
        $data['css_file'] = 'painel-tv.css';
        $data['view_type'] = 'tv';

        require BASE_PATH . 'app/Views/painel/tv_view.php';
    }


    // ---  API ---
    public function getPainelDataJson($campus)
    {
        global $conn;

        $data = $this->carregarDadosDoCampus($campus);

        $publicidadeModel = new PublicidadeModel($conn);
        $data['publicidades'] = $publicidadeModel->getPublicidadesAtivas(); // Busca dados do BD

        foreach ($data['reservas'] as $key => $reserva) {
            $data['reservas'][$key]['res_hora_inicio_formatada'] = date('H:i', strtotime($reserva['res_hora_inicio']));
            $data['reservas'][$key]['res_hora_fim_formatada'] = date('H:i', strtotime($reserva['res_hora_fim']));
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }


    public function handlePublicidadeSync()
    {
        global $conn;

        $expected_api_key = PAINEL_TV_API_KEY_SECRET;
        $received_api_key = $_POST['api_key'] ?? '';

        if ($received_api_key !== $expected_api_key) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado. Chave de API inválida.']);
            exit;
        }

        header('Content-Type: application/json');

        $operation = $_POST['operation'] ?? null;
        if (!$operation) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['status' => 'error', 'message' => 'Operação não especificada.']);
            exit;
        }

        switch ($operation) {
            case 'add_or_update_file':
                if (empty($_FILES['file']['name'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Nenhum arquivo enviado.']);
                    exit;
                }
                $final_file_name = $_POST['final_file_name'] ?? null;
                if (!$final_file_name) {
                    echo json_encode(['status' => 'error', 'message' => 'Nome do arquivo final não fornecido.']);
                    exit;
                }

                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
                $file_type = mime_content_type($_FILES['file']['tmp_name']);
                if (!in_array($file_type, $allowed_types)) {
                    echo json_encode(['status' => 'error', 'message' => 'Tipo de arquivo não permitido: ' . $file_type]);
                    exit;
                }

                $max_size = 10 * 1024 * 1024;
                if ($_FILES['file']['size'] > $max_size) {
                    echo json_encode(['status' => 'error', 'message' => 'Arquivo muito grande. Tamanho máximo permitido é 10MB.']);
                    exit;
                }

                $upload_dir_painel_tv = BASE_PATH . 'public/files/banners/';
                if (!is_dir($upload_dir_painel_tv)) {
                    mkdir($upload_dir_painel_tv, 0777, true);
                }

                $caminho_completo_no_painel_tv = $upload_dir_painel_tv . $final_file_name;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $caminho_completo_no_painel_tv)) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Arquivo salvo com sucesso no Painel TV.',
                        'caminho_salvo' => 'files/banners/' . $final_file_name
                    ]);
                    exit;
                } else {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['status' => 'error', 'message' => 'Falha ao mover o arquivo para o diretório de destino do Painel TV.']);
                    exit;
                }
                break;

            case 'delete_file':
                $file_name_to_delete = $_POST['file_name'] ?? null;
                if (!$file_name_to_delete) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['status' => 'error', 'message' => 'Nome do arquivo não fornecido para exclusão.']);
                    exit;
                }

                $caminho_fisico_no_painel_tv = BASE_PATH . 'public/files/banners/' . $file_name_to_delete;

                if (file_exists($caminho_fisico_no_painel_tv)) {
                    if (unlink($caminho_fisico_no_painel_tv)) {
                        echo json_encode(['status' => 'success', 'message' => 'Arquivo excluído com sucesso do Painel TV.']);
                        exit;
                    } else {
                        header('HTTP/1.1 500 Internal Server Error');
                        echo json_encode(['status' => 'error', 'message' => 'Falha ao excluir o arquivo físico no Painel TV.']);
                        exit;
                    }
                } else {
                    echo json_encode(['status' => 'success', 'message' => 'Arquivo não encontrado no Painel TV (já excluído ou inexistente).']);
                    exit;
                }
                break;

            default:
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['status' => 'error', 'message' => 'Operação desconhecida ou inválida para sincronização de publicidade.']);
                exit;
        }
    }
}