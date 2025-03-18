<?php
session_start();

// Gerar um token CSRF se não existir
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Função para obter o IP real do usuário
function getIPAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Função para verificar se é um bot
function isBot() {
    $botPatterns = ['/bot/i', '/crawl/i', '/spider/i', '/slurp/i', '/mediapartners/i'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    foreach ($botPatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) {
            return true;
        }
    }
    return false;
}

// Verificar se o CSRF token é válido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $inputToken)) {
        die("CSRF token invÃ¡lido!");
    }

    if (!isBot()) {
        $ip = getIPAddress();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
        $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Desconhecido';
        $screenResolution = $_POST['res'] ?? 'Desconhecido';

        // Detectar informações do User-Agent
        function parseUserAgent($userAgent) {
            $browser = "Desconhecido";
            $os = "Desconhecido";

            if (preg_match('/Windows NT 10.0/', $userAgent)) {
                $os = "Windows 10";
            } elseif (preg_match('/Mac OS X/', $userAgent)) {
                $os = "Mac OS";
            } elseif (preg_match('/Linux/', $userAgent)) {
                $os = "Linux";
            } elseif (preg_match('/Android/', $userAgent)) {
                $os = "Android";
            } elseif (preg_match('/iPhone|iPad|iPod/', $userAgent)) {
                $os = "iOS";
            }

            if (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) {
                $browser = "Firefox " . $matches[1];
            } elseif (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
                $browser = "Chrome " . $matches[1];
            } elseif (preg_match('/Safari\/(\d+)/', $userAgent) && preg_match('/Version\/(\d+)/', $userAgent, $matches)) {
                $browser = "Safari " . $matches[1];
            } elseif (preg_match('/Edge\/(\d+)/', $userAgent, $matches)) {
                $browser = "Edge " . $matches[1];
            }

            return ["os" => $os, "browser" => $browser];
        }

        $parsedUA = parseUserAgent($userAgent);
        $os = $parsedUA['os'];
        $browser = $parsedUA['browser'];

        $message = "Novo visitante detectado:\nIP: $ip\nUser-Agent: $userAgent\nSistema Operacional: $os\nNavegador: $browser\nIdioma: $language\nResolução da tela: $screenResolution";

        $ch = curl_init('https://ntfy.sh/<link>');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
} else {
    die("Método inválido!");
}
?>
