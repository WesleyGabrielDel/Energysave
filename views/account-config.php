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
    <link rel="stylesheet" href="/EnergySaveProject/public/css/components.css">
    <link rel="stylesheet" href="/EnergySaveProject/public/css/account-config-page.css">
    <link rel="shortcut icon" href="/EnergySaveProject/public/images/logo-energysave-without-text-borda-arredondada.ico" type="image/x-icon">
    <script>
        let userAccountInfo = <?= json_encode($userAccountInfo) ?>;
    </script>
    <script src="https://kit.fontawesome.com/245fa0f253.js" crossorigin="anonymous"></script>
    <script type="module" src="/EnergySaveProject/public/js/account-config.js" defer></script>
    <title>Sua conta - ES</title>
</head>
<body>

    <!-- POPUP: Desvincular Dispositivo -->
    <div class="popup-exclude-device">
        <div class="popup-content">
            <h3>Desconectar Dispositivo</h3>
            <p>Tem certeza que deseja desvincular este dispositivo da sua conta?</p>
            <div class="popup-buttons">
                <button id="btn-confirm-disconnect" class="btn primary warning">Sim, desvincular</button>
                <button id="btn-cancel-disconnect" class="btn secundary">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- POPUP: Upload de Foto de Perfil -->
    <div class="popup-avatar" id="popup-avatar">
        <div class="popup-content popup-avatar-content">
            <div class="popup-avatar-header">
                <h3>Atualizar foto de perfil</h3>
                <button class="popup-close-btn" id="btn-close-avatar"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="avatar-upload-area" id="avatar-upload-area">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <p>Arraste uma imagem aqui ou</p>
                <label for="avatar-file-input" class="btn-upload-label">Escolher arquivo</label>
                <input type="file" id="avatar-file-input" accept="image/*" hidden>
                <span class="upload-hint">JPG, PNG ou GIF · Máx 5MB</span>
            </div>
            <div class="avatar-preview" id="avatar-preview" style="display:none;">
                <img id="avatar-preview-img" src="" alt="Preview">
            </div>
            <div class="popup-buttons">
                <button class="btn primary" id="btn-save-avatar">Salvar foto</button>
                <button class="btn secundary" id="btn-cancel-avatar">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- POPUP: Alterar Senha -->
    <div class="popup-change-password" id="popup-change-password">
        <div class="popup-content">
            <div class="popup-avatar-header">
                <h3>Alterar Senha</h3>
                <button class="popup-close-btn" id="btn-close-password"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="password-fields">
                <div class="field-group">
                    <label class="field-label">Senha atual</label>
                    <div class="input-wrapper">
                        <input type="password" class="account-input" id="current-password" placeholder="••••••••">
                        <button class="toggle-pw" onclick="togglePw('current-password', this)"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Nova senha</label>
                    <div class="input-wrapper">
                        <input type="password" class="account-input" id="new-password" placeholder="••••••••">
                        <button class="toggle-pw" onclick="togglePw('new-password', this)"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Confirmar nova senha</label>
                    <div class="input-wrapper">
                        <input type="password" class="account-input" id="confirm-password" placeholder="••••••••">
                        <button class="toggle-pw" onclick="togglePw('confirm-password', this)"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
            </div>
            <div class="popup-buttons">
                <button class="btn primary" id="btn-save-password">Salvar senha</button>
                <button class="btn secundary" id="btn-cancel-password">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo"><a href="http://localhost/EnergySaveProject/landing">EnergySave</a></div>
            <div class="nav-buttons">

            </div>
        </div>
    </nav>

    <div class="container">
        <nav class="side-bar">
            <div class="container-sidebar">
                <div class="ctn-info-account"></div>
                <div class="line-sidebar home-line" data-tab="home">
                    <a href="#"><i class="fa-solid fa-house"></i><p>Início</p></a>
                </div>

                <div class="line-sidebar your-info-line" data-tab="conta">
                    <a href="#"><i class="fa-solid fa-user"></i><p>Sua conta</p></a>
                </div>

                <div class="line-sidebar devices-line" data-tab="dispositivos">
                    <a href="#"><i class="fa-solid fa-hard-drive"></i><p>Meus Dispositivos</p></a>
                </div>

                <div class="line-sidebar security-info-line" data-tab="seguranca">
                    <a href="#"><i class="fa-solid fa-shield-halved"></i><p>Segurança</p></a>
                </div>

                <div class="line-sidebar privacy-info-line" data-tab="privacidade">
                    <a href="#"><i class="fa-solid fa-user-shield"></i><p>Privacidade</p></a>
                </div>
            </div>
        </nav>

        <div class="container-geral">

            <!-- HOME / PREFERÊNCIAS -->
            <div class="container-home container-options" data-panel="home">

                <section class="section-ctn">
                    <div class="title-section">
                        <div><i class="fa-solid fa-bell"></i><h3>Notificações Push</h3></div>
                        <p>Receba alertas em tempo real sobre eventos importantes</p>
                    </div>
                    <div class="ctn">
                        <div class="config notification-config">
                            <div>
                                <h4>Ativar Notificações Push</h4>
                                <p>Habilita ou desabilita todas as notificações push</p>
                            </div>
                            <div>
                                <input type="checkbox" id="chkpush" checked>
                                <label for="chkpush" class="switch"><span class="slider"></span></label>
                            </div>
                        </div>
                        <div class="push-sub-toggles expanded" id="push-sub-toggles">
                            <div class="config sub-notification-config">
                                <div>
                                    <h4><i class="fa-solid fa-bolt sub-icon"></i> Alertas de Pico de Energia</h4>
                                    <p>Notifique quando houver pico de consumo em algum dispositivo</p>
                                </div>
                                <div>
                                    <input type="checkbox" id="chkpush-pico" checked>
                                    <label for="chkpush-pico" class="switch switch-sm"><span class="slider"></span></label>
                                </div>
                            </div>
                            <div class="config sub-notification-config">
                                <div>
                                    <h4><i class="fa-solid fa-lightbulb sub-icon"></i> Dicas de Economia</h4>
                                    <p>Sugestões personalizadas para reduzir o consumo</p>
                                </div>
                                <div>
                                    <input type="checkbox" id="chkpush-dicas" checked>
                                    <label for="chkpush-dicas" class="switch switch-sm"><span class="slider"></span></label>
                                </div>
                            </div>
                            <div class="config sub-notification-config">
                                <div>
                                    <h4><i class="fa-solid fa-chart-line sub-icon"></i> Resumo Semanal</h4>
                                    <p>Relatório push com o resumo de consumo da semana</p>
                                </div>
                                <div>
                                    <input type="checkbox" id="chkpush-resumo">
                                    <label for="chkpush-resumo" class="switch switch-sm"><span class="slider"></span></label>
                                </div>
                            </div>
                            <div class="config sub-notification-config sub-last">
                                <div>
                                    <h4><i class="fa-solid fa-circle-exclamation sub-icon"></i> Alertas do Sistema</h4>
                                    <p>Avisos de manutenção e atualizações do aplicativo</p>
                                </div>
                                <div>
                                    <input type="checkbox" id="chkpush-sistema" checked>
                                    <label for="chkpush-sistema" class="switch switch-sm"><span class="slider"></span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-ctn">
                    <div class="title-section">
                        <div><i class="fa-solid fa-envelope"></i><h3>Notificações por E-mail</h3></div>
                        <p>Relatórios e alertas enviados ao seu e-mail</p>
                    </div>
                    <div class="ctn">
                        <div class="config email-notification-config email-last">
                            <div>
                                <h4>Relatórios por E-mail</h4>
                                <p>Receba relatórios mensais de seu consumo, que serão enviados para seu e-mail</p>
                            </div>
                            <div>
                                <input type="checkbox" id="chkrelatorios">
                                <label for="chkrelatorios" class="switch"><span class="slider"></span></label>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- CONTA / INFORMAÇÕES PESSOAIS -->
            <div class="container-account container-options" data-panel="conta">
                <div class="ctn-name-account account-hero">
                    <button class="avatar-btn" id="btn-open-avatar" title="Alterar foto de perfil">
                        <img src="/EnergySaveProject/public/images/avatars/logo-conta.png" alt="Foto de perfil" id="profile-avatar">
                        <div class="avatar-overlay"><i class="fa-solid fa-camera"></i></div>
                    </button>
                    <div class="ctn-account-infos">
                        <h2 id="showable-name">Henrique Pedroso</h2>
                        <p id="showable-email">henrique.pedroso@energysave.project</p>
                    </div>
                </div>

                <section class="section-ctn">
                    <div class="title-section">
                        <div><i class="fa-solid fa-user"></i><h3>Informações Pessoais</h3></div>
                        <p>Gerencie seus dados de perfil</p>
                    </div>
                    <div class="ctn personal-fields">
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Nome completo</label>
                                <div class="field-input-wrap">
                                    <input type="text" class="account-input" value="Henrique Pedroso" id="field-nome" readonly>
                                    <button class="field-edit-btn" onclick="toggleEdit('field-nome', this)"><i class="fa-solid fa-pen"></i></button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Gênero <span class="field-optional">(opcional)</span></label>
                                <div class="field-input-wrap">
                                    <select class="account-input account-select" id="field-genero">
                                        <option value="nao-informado">Não Informado</option>
                                        <option value="masculino" selected>Masculino</option>
                                        <option value="feminino">Feminino</option>
                                        <option value="nao-binario">Não-binário</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">E-mail</label>
                                <div class="field-input-wrap">
                                    <input type="email" class="account-input" value="henrique.pedroso@energysave.project" id="field-email" readonly>
                                    <span class="field-badge">Verificado <i class="fa-solid fa-circle-check"></i></span>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Telefone</label>
                                <div class="field-input-wrap">
                                    <input type="tel" class="account-input" value="+55 41 99999-0000" id="field-telefone" readonly>
                                    <button class="field-edit-btn" onclick="toggleEdit('field-telefone', this)"><i class="fa-solid fa-pen"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Data de nascimento <span class="field-optional">(opcional)</span></label>
                                <div class="field-input-wrap">
                                    <input type="date" class="account-input" value="1998-04-12" id="field-nascimento" readonly>
                                    <button class="field-edit-btn" onclick="toggleEdit('field-nascimento', this)"><i class="fa-solid fa-pen"></i></button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Idioma <span class="field-optional">(opcional)</span></label>
                                <div class="field-input-wrap">
                                    <select class="account-input account-select" id="field-idioma">
                                        <option value="pt-br" selected>Português (Brasil)</option>
                                        <option value="en">English</option>
                                        <option value="es">Español</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Conta criada em</label>
                                <div class="field-input-wrap">
                                    <input type="text" class="account-input" id="field-criado" value="12 de janeiro de 2024" readonly>
                                    <span class="field-badge readonly-badge"><i class="fa-solid fa-lock"></i> Somente leitura</span>
                                </div>
                            </div>
                        </div>
                        <div class="field-save-row">
                            <button class="btn-save-info" id="btn-save-info">
                                <i class="fa-solid fa-floppy-disk"></i> Salvar alterações
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- DISPOSITIVOS -->
            <div class="container-devices container-options" data-panel="dispositivos">
                <section class="section-ctn-devices section-ctn">
                    <div class="title-devices title-section">
                        <div><i class="fa-brands fa-chromecast"></i><h3>Dispositivos</h3></div>
                        <p>Gerencie os dispositivos conectados à sua conta</p>
                    </div>
                    <div class="devices-ctn ctn">
                        <div class="devices-config config">
                            <div class="container-select-disp">
                                <div class="ctn-scan">
                                    <p>SELECIONE UM DISPOSITIVO ABAIXO</p>
                                    <div class="ctn-scan-circles">
                                        <div class="point-search first"></div>
                                        <div class="point-search second"></div>
                                        <div class="point-search third"></div>
                                    </div>
                                </div>
                                <div class="ctn-dispositivos">
                                    <div class="disp">
                                        <div class="disp-info">
                                            <i class="fa-solid fa-hard-drive"></i>
                                            <div class="info-disp">
                                                <h3>L3250 Series</h3>
                                                <p>DIRECT-9E-EPSON-75AEBB</p>
                                            </div>
                                        </div>
                                        <div>
                                            <button id="btn-config-disp"><i class="fa-solid fa-gear"></i><p>Configurar</p></button>
                                            <button id="btn-disconnect-disp"><i class="fa-solid fa-link-slash"></i><p>Desvincular</p></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- SEGURANÇA -->
            <div class="container-security container-options" data-panel="seguranca">

                <section class="section-ctn">
                    <div class="title-section">
                        <div><i class="fa-solid fa-lock"></i><h3>Senha</h3></div>
                        <p>Mantenha sua conta protegida com uma senha forte</p>
                    </div>
                    <div class="ctn">
                        <div class="config password-config-row">
                            <div>
                                <h4>Alterar Senha</h4>
                                <p class="sec-meta">Última alteração: <span class="sec-date">14 de março de 2025</span></p>
                            </div>
                            <button class="change-password-btn btn-primary" id="btn-change-password">
                                <i class="fa-solid fa-key"></i> Alterar Senha
                            </button>
                        </div>
                    </div>
                </section>

                <section class="section-ctn">
                    <div class="title-section">
                        <div><i class="fa-solid fa-circle-question"></i><h3>Recuperação de Conta</h3></div>
                        <p>Adicione formas alternativas de recuperar o acesso</p>
                    </div>
                    <div class="ctn recovery-fields">
                        <div class="field-group field-last">
                            <label class="field-label">E-mail de recuperação</label>
                            <div class="field-input-wrap">
                                <input type="email" class="account-input" placeholder="ex: backup@email.com" id="field-email-recovery" readonly>
                                <button class="field-edit-btn" onclick="toggleEdit('field-email-recovery', this)"><i class="fa-solid fa-pen"></i></button>
                            </div>
                        </div>
                        <div class="field-save-row">
                            <button class="btn-save-info" id="btn-save-recovery">
                                <i class="fa-solid fa-floppy-disk"></i> Salvar dados de recuperação
                            </button>
                        </div>
                    </div>
                </section>

                <section class="section-ctn">
                    <div class="title-section">
                        <div><i class="fa-solid fa-key"></i><h3>Autenticação de Dois Fatores</h3></div>
                        <p>Ative o 2FA para maior segurança em sua conta</p>
                    </div>
                    <div class="ctn">
                        <div class="config twofa-config">
                            <div>
                                <h4>Ativar o 2FA</h4>
                                <p>Autenticação de dois fatores via aplicativo autenticador</p>
                            </div>
                            <div>
                                <input type="checkbox" id="chk2fa">
                                <label for="chk2fa" class="switch"><span class="slider"></span></label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-ctn">
                    <div class="title-section">
                        <div><i class="fa-solid fa-display"></i><h3>Sessões Ativas</h3></div>
                        <p>Gerencie os dispositivos com sessão aberta na sua conta</p>
                    </div>
                    <div class="ctn sessions-ctn">

                    </div>
                        <div class="sessions-footer">
                            <button class="btn-revoke-all" id="btn-revoke-all">
                                <i class="fa-solid fa-power-off"></i> Encerrar todas as outras sessões
                            </button>
                        </div>
                </section>
            </div>

            <!-- PRIVACIDADE -->
            <div class="container-privacy container-options" data-panel="privacidade">
                <section class="section-ctn-privacy section-ctn">
                    <div class="title-privacy title-section">
                        <div><i class="fa-solid fa-user-shield"></i><h3>Privacidade</h3></div>
                        <p>Gerencie as configurações de privacidade da sua conta</p>
                    </div>
                </section>
            </div>

        </div>
    </div>
</body>
</html>
