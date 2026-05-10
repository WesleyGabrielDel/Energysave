<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/EnergySaveProject/public/images/logo-energysave-without-text-borda-arredondada.ico" type="image/x-icon">
    <link rel="stylesheet" href="/EnergySaveProject/public/css/auth-page.css">
    <script type="module" src="/EnergySaveProject/public/js/script-auth.js" defer></script>
    <script src="https://kit.fontawesome.com/245fa0f253.js" crossorigin="anonymous"></script>
    <script>
        const paginaAtual = 
        <?php echo isset($_GET["page"]) ? (int) $_GET["page"] : 1; ?>;
    </script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <title>EnergySave - Login</title>
</head>
<body>
    <div class="container">
        <div class="card loginActive">

            <!-- PAINEL ESCURO: formulários login / cadastro -->
            <div class="panel-dark">

                <!-- Formulário de Login -->
                <div class="formLogin">
                    <h2>Fazer Login</h2>
                    <form class="dataForm">
                        <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                        <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                        <button type="submit" class="btnSubmit">Entrar</button>
                        
                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" id="remember" name="remember">
                                <span class="checkbox-label">Lembrar de mim</span>
                            </label>
                            <a class="forgot-link" id="forgotPasswordLink">Esqueceu a senha?</a>
                        </div>
                    </form>
                    <div class="ctnOtherLogins">
                        <p>Ou faça login com:</p>
                        <div class="otherLogins">
                            <button class="social-btn" id="googleSignIn" onclick="google.accounts.id.prompt();"> <i class="fa-brands fa-google"></i>Google</button>
                            <button class="social-btn" id="passkeySignIn"><i class="fa-solid fa-key"></i>Passkey</button>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Cadastro -->
                <div class="formSignup">
                    <h2>Registrar-se</h2>
                    <form class="dataForm">
                        <input type="text" placeholder="Digite seu nome completo" name="nome" required >
                        <input type="email" placeholder="Email" name="email" required>
                        <input type="password" placeholder="Senha" name="senha" required>
                        <button class="btnSubmit" type="submit">Registrar</button>
                    </form>
                    <div class="ctnOtherSignups">
                        <p>Ou registre-se com:</p>
                        <div class="otherSignups">
                            <button class="social-btn" id="googleSignUp" onclick="google.accounts.id.prompt();"><i class="fa-brands fa-google"></i>Google</button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- PAINEL CLARO: convites login / cadastro -->
            <div class="panel-light">
                <div class="contentLogin">
                    <h2>Já tem uma conta?</h2>
                    <p>Clique no botão abaixo para fazer login</p>
                    <button id="sideLoginBtn">Fazer Login</button>
                </div>
                <div class="contentSignup">
                    <h2>Não tem uma conta?</h2>
                    <p>Clique no botão abaixo para se registrar</p>
                    <button id="sideSignupBtn">Registrar-se</button>
                </div>
            </div>

            <!-- PAINEL RECUPERAÇÃO DE SENHA -->
            <div class="panel-remember">
                <div class="contentRemember">
                    <h2>Esqueceu a senha?</h2>
                    <form>
                        <label for="emailRecuperacao">Digite seu email para receber as instruções de recuperação</label>
                        <input type="email" placeholder="Digite seu email" name="emailRecuperacao" required>
                        <button type="submit" id="forgotPasswordSubmit">Enviar</button>
                    </form>
                    <a class="back-link" id="backFromRemember">← Voltar ao login</a>
                </div>
            </div>

            <!-- PAINEL 2FA -->
            <div class="panel-twofa">
                <div class="content2fa">

                    <div class="twofa-header">
                        <div class="twofa-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h2>Verificação em duas etapas</h2>
                        <p class="twofa-desc">
                            Digite o código de 6 dígitos enviado para
                            <span id="twofaEmailHint"></span>
                        </p>
                    </div>

                    <form id="twofaForm">
                        <!-- 6 inputs OTP gerados pelo JS -->
                        <div class="otp-fields" id="otpFields"></div>

                        <p class="otp-status" id="otpStatus"></p>

                        <button type="submit" class="twofa-submit-btn" id="twofaSubmitBtn">
                            <i class="fa-solid fa-check"></i>
                            Verificar código
                        </button>
                    </form>

                    <div class="twofa-footer">
                        <button class="twofa-resend-btn" id="twofaResendBtn">
                            <i class="fa-solid fa-rotate-right"></i>
                            Reenviar código
                        </button>
                        <span class="twofa-separator">·</span>
                        <a class="back-link" id="backFrom2fa">← Voltar</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</body>
</html>