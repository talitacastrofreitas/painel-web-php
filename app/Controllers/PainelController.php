<?php

namespace App\Controllers;



use App\Models\ReservaModel;
use App\Models\PublicidadeModel;
use PDO;

class PainelController
{
    private function carregarDadosDoCampus($campus, $filtrarPorHora = false)
    {
        if ($campus !== 'brotas' && $campus !== 'cabula') {
            $errorController = new ErrorController();
            $errorController->notFound();
        }

        global $conn;
        $reservaModel = new ReservaModel($conn);

        $unidadeId = ($campus === 'brotas') ? 2 : 1;

        if ($filtrarPorHora) {
            // NOVO: Usa o método filtrado para a visualização da TV
            $reservas = $reservaModel->getProgramacaoAtualizadaDoDia($unidadeId);
        } else {
            // Mantém o método original para a visualização Web
            $reservas = $reservaModel->getProgramacaoDoDiaPorCampus($unidadeId);
        }

        return [
            'campus' => $campus,
            'reservas' => $reservas
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
        // NOVO: Passa TRUE para filtrar por hora
        $data = $this->carregarDadosDoCampus($campus, true);

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

        // NOVO: A API deve usar o método filtrado para o painel de TV
        $data = $this->carregarDadosDoCampus($campus, true);

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
        // 1. Evita que erros de PHP quebrem o JSON (captura e exibe no final)
        ini_set('display_errors', 0);
        error_reporting(E_ALL);

        global $conn;

        $expected_api_key = PAINEL_TV_API_KEY;
        $received_api_key = $_POST['api_key'] ?? '';

        header('Content-Type: application/json');

        // --- DEBUG DE ERROS FATAIS ---
        try {

            if ($received_api_key !== $expected_api_key) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado. Chave incorreta.']);
                exit;
            }

            $operation = $_POST['operation'] ?? null;
            if (!$operation) {
                // Se POST estiver vazio, pode ser limite de tamanho do PHP estourado
                if (empty($_POST) && empty($_FILES)) {
                    throw new \Exception('POST e FILES vazios. O arquivo enviado provavelmente excede o limite post_max_size do php.ini.');
                }
                throw new \Exception('Operação não especificada.');
            }

            // Define o caminho da raiz do Painel de forma segura
            // App/Controllers -> sobe 2 niveis -> Raiz
            $baseDir = dirname(__DIR__, 2); 
            $upload_dir_painel_tv = $baseDir . '/public/files/banners/';

            // Garante que o diretório existe
            if (!is_dir($upload_dir_painel_tv)) {
                if (!mkdir($upload_dir_painel_tv, 0777, true)) {
                    throw new \Exception("Não foi possível criar a pasta: $upload_dir_painel_tv. Verifique permissões.");
                }
            }

            switch ($operation) {
                case 'add_or_update_file':
                    // Verificação rigorosa se o arquivo chegou
                    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                        $erro_cod = $_FILES['file']['error'] ?? 'N/A';
                        $msg_erro = match ($erro_cod) {
                            1 => 'Arquivo excede upload_max_filesize no php.ini',
                            2 => 'Arquivo excede MAX_FILE_SIZE do form',
                            3 => 'Upload parcial',
                            4 => 'Nenhum arquivo enviado',
                            6 => 'Pasta temporária ausente',
                            7 => 'Falha ao escrever em disco',
                            default => "Erro desconhecido no upload (Cod: $erro_cod)"
                        };
                        throw new \Exception("Erro no recebimento do arquivo: $msg_erro");
                    }

                    $final_file_name = $_POST['final_file_name'] ?? basename($_FILES['file']['name']);
                    $caminho_completo = $upload_dir_painel_tv . $final_file_name;

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $caminho_completo)) {
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Arquivo salvo com sucesso.',
                            'debug_path' => $caminho_completo
                        ]);
                    } else {
                        // Captura erro detalhado do sistema se move_uploaded_file falhar
                        $error = error_get_last();
                        throw new \Exception("Falha ao mover arquivo. Detalhe: " . ($error['message'] ?? 'Sem detalhes'));
                    }
                    break;

                case 'delete_file':
                    $file_name = $_POST['file_name'] ?? '';
                    if (!$file_name) throw new \Exception('Nome do arquivo não fornecido.');

                    $caminho_completo = $upload_dir_painel_tv . $file_name;

                    if (file_exists($caminho_completo)) {
                        if (unlink($caminho_completo)) {
                            echo json_encode(['status' => 'success', 'message' => 'Arquivo excluído.']);
                        } else {
                            throw new \Exception("Falha ao excluir o arquivo físico (permissão?).");
                        }
                    } else {
                        echo json_encode(['status' => 'success', 'message' => 'Arquivo não existia, considerado excluído.']);
                    }
                    break;

                default:
                    throw new \Exception("Operação desconhecida: $operation");
            }

        } catch (\Throwable $e) {
            // Captura qualquer Crash/Exception e retorna 500 com a mensagem REAL
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Erro Interno no Painel: ' . $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ]);
        }
        exit;
    }


    // public function handlePublicidadeSync()
    // {
    //     global $conn;

    //     $expected_api_key = PAINEL_TV_API_KEY_SECRET;
    //     $received_api_key = $_POST['api_key'] ?? '';

    //     if ($received_api_key !== $expected_api_key) {
    //         header('HTTP/1.1 401 Unauthorized');
    //         echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado. Chave de API inválida.']);
    //         exit;
    //     }

    //     header('Content-Type: application/json');

    //     $operation = $_POST['operation'] ?? null;
    //     if (!$operation) {
    //         header('HTTP/1.1 400 Bad Request');
    //         echo json_encode(['status' => 'error', 'message' => 'Operação não especificada.']);
    //         exit;
    //     }

    //     switch ($operation) {
    //         case 'add_or_update_file':
    //             if (empty($_FILES['file']['name'])) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Nenhum arquivo enviado.']);
    //                 exit;
    //             }
    //             $final_file_name = $_POST['final_file_name'] ?? null;
    //             if (!$final_file_name) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Nome do arquivo final não fornecido.']);
    //                 exit;
    //             }

    //             $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
    //             $file_type = mime_content_type($_FILES['file']['tmp_name']);
    //             if (!in_array($file_type, $allowed_types)) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Tipo de arquivo não permitido: ' . $file_type]);
    //                 exit;
    //             }

    //             $max_size = 10 * 1024 * 1024;
    //             if ($_FILES['file']['size'] > $max_size) {
    //                 echo json_encode(['status' => 'error', 'message' => 'Arquivo muito grande. Tamanho máximo permitido é 10MB.']);
    //                 exit;
    //             }

    //             // $upload_dir_painel_tv = BASE_PATH . 'public/files/banners/';
    //             $upload_dir_painel_tv = dirname(__DIR__, 2) . '/public/files/banners/';
    //             if (!is_dir($upload_dir_painel_tv)) {
    //                 mkdir($upload_dir_painel_tv, 0777, true);
    //             }

    //             $caminho_completo_no_painel_tv = $upload_dir_painel_tv . $final_file_name;

    //             if (move_uploaded_file($_FILES['file']['tmp_name'], $caminho_completo_no_painel_tv)) {
    //                 echo json_encode([
    //                     'status' => 'success',
    //                     'message' => 'Arquivo salvo com sucesso no Painel TV.',
    //                     'caminho_salvo' => 'files/banners/' . $final_file_name
    //                 ]);
    //                 exit;
    //             } else {
    //                 header('HTTP/1.1 500 Internal Server Error');
    //                 echo json_encode(['status' => 'error', 'message' => 'Falha ao mover o arquivo para o diretório de destino do Painel TV.']);
    //                 exit;
    //             }
    //             break;

    //         case 'delete_file':
    //             $file_name_to_delete = $_POST['file_name'] ?? null;
    //             if (!$file_name_to_delete) {
    //                 header('HTTP/1.1 400 Bad Request');
    //                 echo json_encode(['status' => 'error', 'message' => 'Nome do arquivo não fornecido para exclusão.']);
    //                 exit;
    //             }

    //             // $caminho_fisico_no_painel_tv = BASE_PATH . 'public/files/banners/' . $file_name_to_delete;

    //             $caminho_fisico_no_painel_tv = dirname(__DIR__, 2) . '/public/files/banners/' . $file_name_to_delete;

    //             if (file_exists($caminho_fisico_no_painel_tv)) {
    //                 if (unlink($caminho_fisico_no_painel_tv)) {
    //                     echo json_encode(['status' => 'success', 'message' => 'Arquivo excluído com sucesso do Painel TV.']);
    //                     exit;
    //                 } else {
    //                     header('HTTP/1.1 500 Internal Server Error');
    //                     echo json_encode(['status' => 'error', 'message' => 'Falha ao excluir o arquivo físico no Painel TV.']);
    //                     exit;
    //                 }
    //             } else {
    //                 echo json_encode(['status' => 'success', 'message' => 'Arquivo não encontrado no Painel TV (já excluído ou inexistente).']);
    //                 exit;
    //             }
    //             break;

    //         default:
    //             header('HTTP/1.1 400 Bad Request');
    //             echo json_encode(['status' => 'error', 'message' => 'Operação desconhecida ou inválida para sincronização de publicidade.']);
    //             exit;
    //     }
    // }
}