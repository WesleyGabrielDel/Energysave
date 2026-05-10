<?php 

requireAuth();

$userAccountInfo = null;

if (isset($_COOKIE["rememberCookie"])) {
    $userAccountInfo = UserService::getAuthenticatedUser($_COOKIE["rememberCookie"]);
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script>
        let userAccountInfo = <?= json_encode($userAccountInfo) ?>;
    </script>

    <script src="https://kit.fontawesome.com/245fa0f253.js" crossorigin="anonymous"></script>
    <script type="module" src="/EnergySaveProject/public/js/script-config.js" defer></script>
    
    <link rel="stylesheet" href="/EnergySaveProject/public/css/components.css">
    <link rel="stylesheet" href="/EnergySaveProject/public/css/config-disp-page.css">
    <link rel="shortcut icon" href="/EnergySaveProject/public/images/logo-energysave-without-text-borda-arredondada.ico" type="image/x-icon">
    <title>Configurar Dispositivo - ES</title>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo"><a href="http://localhost/EnergySaveProject/landing">EnergySave</a></div>
            <div class="nav-buttons">

            </div>
        </div>
    </nav>

    <section>
        <div class="container-config">
            
    </section>

    <footer class="footerConfigPage">
        <div class="links-extras">
            <a href="/EnergySaveProject/public">LANDING PAGE</a>
            <a href="/EnergySaveProject/public">TERMOS</a>
            <a href="/EnergySaveProject/public">SUPORTE</a>
        </div>
        <div class="copyright">
            <p>© 2026 EnergySave. Análise inteligente de energia para instituições.</p>
        </div>
    </footer>
</body>

</html>
