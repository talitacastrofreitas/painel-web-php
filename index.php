<?php
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config/config.php';

use App\Core\DatabaseSessionHandler;

global $conn;


$sessionHandler = new DatabaseSessionHandler($conn);


session_set_save_handler(
    [$sessionHandler, 'open'],
    [$sessionHandler, 'close'],
    [$sessionHandler, 'read'],
    [$sessionHandler, 'write'],
    [$sessionHandler, 'destroy'],
    [$sessionHandler, 'gc']
);

ini_set('session.gc_maxlifetime', 60);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$inactive_timeout = 60;

if (isset($_SESSION['colaborador_logado']) && $_SESSION['colaborador_logado']) {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $inactive_timeout)) {

        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'admin/login');
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
} else {

    if (isset($_SESSION['colaborador_logado'])) {
        unset($_SESSION['colaborador_logado']);
    }
    if (isset($_SESSION['colaborador_email'])) {
        unset($_SESSION['colaborador_email']);
    }
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        unset($_SESSION['LAST_ACTIVITY']);
    }

    if (isset($_SESSION['toast_message'])) {
        $isLogoutMessage = (
            isset($_SESSION['toast_message']['type']) && $_SESSION['toast_message']['type'] === 'success' &&
            isset($_SESSION['toast_message']['message']) && $_SESSION['toast_message']['message'] === 'Você foi desconectado.'
        );


        $isAuthErrorMessage = (
            isset($_SESSION['toast_message']['type']) && $_SESSION['toast_message']['type'] === 'error' &&
            isset($_SESSION['toast_message']['message']) && $_SESSION['toast_message']['message'] === 'E-mail não encontrado ou inválido.'
        );


        if (!$isLogoutMessage && !$isAuthErrorMessage) {
            unset($_SESSION['toast_message']);
        }
    }
}

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if (ob_get_length()) {
            ob_end_clean();
        }
        $errorController = new \App\Controllers\ErrorController();
        $errorController->serverError();
    }
});


$url = $_GET['url'] ?? '';
$segments = array_filter(explode('/', $url));

$controllerName = null;
$methodName = null;
$params = [];

// --- LÓGICA DE ROTAS ---

if (isset($segments[0]) && ($segments[0] === 'public')) {

    $filePath = __DIR__ . '/' . implode('/', $segments);
    if (file_exists($filePath)) {
        return readfile($filePath);
    }
}
// ROTA DA API: /api/painel-data
elseif (isset($segments[0]) && $segments[0] === 'api' && isset($segments[1]) && $segments[1] === 'painel-data') {
    $controllerName = 'PainelController';
    $methodName = 'getPainelDataJson';
    $params = array_slice($segments, 2);
}

// NOVO ENDPOINT DE API PARA SINCRONIZAÇÃO DE PUBLICIDADES
elseif (isset($segments[0]) && $segments[0] === 'api' && isset($segments[1]) && $segments[1] === 'publicidade-sync') {
    $controllerName = 'PainelController';
    $methodName = 'handlePublicidadeSync';
    // Não há parâmetros de URL, mas os dados vêm via POST
    $params = [];
}

// ROTAS DE ADMINISTRAÇÃO E AUTENTICAÇÃO
elseif (isset($segments[0]) && $segments[0] === 'admin') {
    $controllerName = 'AdminController';
    $methodName = $segments[1] ?? 'index';

    // Lógica para métodos específicos com parâmetros
    if ($methodName === 'toggleStatus' && isset($segments[2])) {
        $params[] = $segments[2];
    } elseif ($methodName === 'excluir' && isset($segments[2])) {
        $params[] = $segments[2];
    } elseif ($methodName === 'verifyEmail' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    } elseif ($methodName === 'salvarOrdemAjax' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    } elseif ($methodName === 'keepAlive') {
    } else {

        $params = array_slice($segments, 2);
    }
}
// ROTA PRINCIPAL: 
elseif (isset($segments[0]) && $segments[0] === 'painel') {
    $controllerName = 'PainelController';
    $methodName = 'index';

    if (isset($segments[1])) {
        if ($segments[1] === 'web') {
            $methodName = 'webView';
        } elseif ($segments[1] === 'tv') {
            $methodName = 'tvView';
        }
    }
    $params = array_slice($segments, 2);
}
// ROTAS GERAIS
else {
    $defaultControllerName = 'PainelController';

    if (empty($segments)) {
        $controllerName = $defaultControllerName;
        $methodName = 'index';
    } else {
        $potentialController = ucfirst($segments[0]) . 'Controller';
        if (class_exists('App\\Controllers\\' . $potentialController)) {
            $controllerName = $potentialController;
            $methodName = $segments[1] ?? 'index';
            $params = array_slice($segments, 2);
        } else {
            $controllerName = $defaultControllerName;
            $methodName = $segments[0];
            $params = array_slice($segments, 1);
        }
    }
}

// --- EXECUÇÃO DO CONTROLADOR E MÉTODO ---
$fullControllerName = '\\App\\Controllers\\' . $controllerName;
if ($controllerName && class_exists($fullControllerName)) {
    $controller = new $fullControllerName();
    if ($methodName === 'handlePublicidadeSync') {
        call_user_func([$controller, $methodName]);
    }

    if (method_exists($controller, $methodName)) {
        if (($methodName === 'verifyEmail' || $methodName === 'salvarOrdemAjax') && $_SERVER['REQUEST_METHOD'] === 'POST') {
            call_user_func([$controller, $methodName]);
        } else {
            call_user_func_array([$controller, $methodName], $params);
        }
    } else {
        $errorController = new \App\Controllers\ErrorController();
        $errorController->notFound();
    }
} else {
    $errorController = new \App\Controllers\ErrorController();
    $errorController->notFound();
}