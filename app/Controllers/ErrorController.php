<?php
// Caminho: app/Controllers/ErrorController.php

namespace App\Controllers;

class ErrorController
{

    /**
     * Exibe uma página de erro genérica.
     *
     * @param int    $code     
     * @param string $title    
     * @param string $message 
     */
    public function showError($code, $title, $message)
    {

        http_response_code($code);


        $data['pageTitle'] = "$code - $title";
        $data['error_code'] = $code;
        $data['error_title'] = $title;
        $data['error_message'] = $message;


        $data['css_file'] = 'painel-web.css';


        require_once BASE_PATH . 'app/Views/error/error_view.php';
        exit();
    }


    public function notFound()
    {
        $this->showError(
            404,
            'Página Não Encontrada',
            'O recurso que você está a tentar aceder não existe ou foi movido.'
        );
    }


    public function serverError()
    {
        $this->showError(
            500,
            'Erro Interno do Servidor',
            'Ocorreu uma falha inesperada. A nossa equipa já foi notificada. Por favor, tente novamente mais tarde.'
        );
    }
}
