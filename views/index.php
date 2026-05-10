<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnergySave - Bem-vindo(a)!</title>
    <link rel="stylesheet" href="/EnergySaveProject/public/css/components.css">
    <link rel="stylesheet" href="/EnergySaveProject/public/css/landing-login.css">
    <link rel="shortcut icon" href="/EnergySaveProject/public/images/logo-energysave-without-text-borda-arredondada.ico" type="image/x-icon">
    <script type="module" src="/EnergySaveProject/public/js/landing.js" defer></script>
    <script>
    let userAccountInfo = <?php 

    if(isset($_COOKIE["rememberCookie"])){
        echo json_encode(UserService::getAuthenticatedUser($_COOKIE["rememberCookie"]));
    } else {
        echo "null";
    }

    ?>;
    </script>
    
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">EnergySave</div>
            <div class="nav-buttons">

            </div>
        </div>
    </nav>

    <section class="show hero">
        <div class="hero-background">
            <img src="/EnergySaveProject/public/images/background-landing-page.jpg"
                alt="Energy analytics">
            <div class="hero-overlay"></div>
        </div>

        <div class="hero-content">
            <h1 class="hero-title">EnergySave</h1>
            <span class="hero-headline" id="heroHeadline1">Análise inteligente de energia para sua</span><br>
            <span class="hero-headline" id="heroHeadline2">instituição</span>

            <p class="hero-description">Monitore, analise e reduza os custos de energia da sua escola com dados em tempo
                real</p>
            <button class="btn-primary" id="btnStart">Começar agora</button>
        </div>
    </section>

    <section class="hidden features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Por que escolher EnergySave?</h2>
                <p class="section-description">Plataforma completa de análise e gestão de energia</p>
            </div>

            <div class="features-grid">
                <div class="feature-card hidden">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">Análise em tempo real</h3>
                    <p class="feature-description">Monitore o consumo de energia de toda a instituição com dashboards interativos e atualizados.</p>
                </div>

                <div class="feature-card hidden">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                    </div>
                    <h3 class="feature-title">Redução de custos</h3>
                    <p class="feature-description">Identifique desperdícios e reduza sua conta de energia com insights inteligentes.</p>
                </div>

                <div class="feature-card hidden">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                            <line x1="12" y1="19" x2="12" y2="22"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">IA preditiva</h3>
                    <p class="feature-description">Previsões de consumo e recomendações automáticas baseadas em machine learning.</p>
                </div>

                <div class="feature-card hidden">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Alertas inteligentes</h3>
                    <p class="feature-description">Notificações instantâneas sobre picos de consumo e anomalias no sistema.</p>
                </div>

                <div class="feature-card hidden">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="16" height="20" x="4" y="2" rx="2" ry="2"></rect>
                            <path d="M9 22v-4h6v4"></path>
                            <path d="M8 6h.01"></path>
                            <path d="M16 6h.01"></path>
                            <path d="M12 6h.01"></path>
                            <path d="M12 10h.01"></path>
                            <path d="M12 14h.01"></path>
                            <path d="M16 10h.01"></path>
                            <path d="M16 14h.01"></path>
                            <path d="M8 10h.01"></path>
                            <path d="M8 14h.01"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">Multi-unidades</h3>
                    <p class="feature-description">Gerencie várias escolas e unidades a partir de uma única plataforma centralizada.</p>
                </div>

                <div class="feature-card hidden">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                    <h3 class="feature-title">Relatórios automáticos</h3>
                    <p class="feature-description">Relatórios detalhados mensais com análises de economia e sugestões de melhorias.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="hidden stats">
        <div class="stats-background"></div>
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">+500</div>
                    <div class="stat-label">Instituições atendidas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">40%</div>
                    <div class="stat-label">Economia média</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Monitoramento</div>
                </div>
            </div>
        </div>
    </section>

    <section class="hidden cta">
        <div class="container-small">
            <div class="cta-card">
                <div class="cta-blob cta-blob-1"></div>
                <div class="cta-blob cta-blob-2"></div>

                <div class="cta-content">
                    <h2 class="cta-title">Pronto para otimizar seus custos?</h2>
                    <p class="cta-description">Comece a monitorar o consumo de energia da sua instituição gratuitamente
                    </p>
                    <button class="btn-primary" id="btnStartTest">Iniciar teste gratuito</button>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p class="footer-text">© 2026 EnergySave. Análise inteligente de energia para instituições.</p>
        </div>
    </footer>
</body>

</html>

