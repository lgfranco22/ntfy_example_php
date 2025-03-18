<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
</head>
<body>
    <h1>Bem-vindo!</h1>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";
            const resolution = `${window.screen.width}x${window.screen.height}`;

            fetch("tracking.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `csrf_token=${encodeURIComponent(csrfToken)}&res=${encodeURIComponent(resolution)}`
            })
            .then(response => console.log("Tracking enviado com sucesso."))
            .catch(error => console.error("Erro ao enviar tracking:", error));
        });
    </script>
</body>
</html>
