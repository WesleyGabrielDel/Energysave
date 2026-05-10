import { BASE_URL, REQUEST_URL } from './lib/utils.js';

document.addEventListener("DOMContentLoaded", () => {

    // --------------------------------------------
    // Declarando as variáveis
    // -------------------------------------------- 

    const configContainer = document.querySelector(".container-config");
    let page = 1;

    // --------------------------------------------
    // InnerHTML 
    // -------------------------------------------- 

    const gerenciarDispositivos = `<div class="container-title">
                    <i class="fa-solid fa-network-wired"></i>
                    <h1 class="titleConfig">Gerenciar dispositivo</h1>
                    <p>Escolha se você deseja configurar um novo dispositivo ou se conectar a um já existente.</p>
                </div>
        
                <button class="btnConfigDisp primary">
                    <i class="fa-solid fa-plus"></i>
                    <p>Configurar Novo Dispositivo</p>
                </button>
                <button class="btnConnectDisp secundary">
                    <i class="fa-solid fa-wifi"></i>
                    <p>Conectar Dispositivo Existente</p>
    </button>`;
    const selecionarDispositivo = `<div class="ctnProgressBar">
                <div class="ctnInfoBar">
                    <p>PASSO 1 DE 4</p>
                    <p class="porcentagemConfig">0%</p>
                </div>
                <div class="progressBar">
                    <div class="baseProgressBar" style="margin: 10px 0; width: 100%; border-radius: 5px; background-color: #9d9d9d;">
                        <div class="qtdProgressBar" style="width: 99%; padding: 1px; border-radius: 5px; background: linear-gradient(to right, #3b82f6, #76c6f4bd);"></div>
                    </div>
                </div>
            </div>
            <div class="container-title">
                <i class="fa-solid fa-house-signal"></i>
                <h1 class="titleConfig">Selecione o dispositivo ao qual quer se conectar</h1>
                <p>Faça a conexão com seu dispositivo para que seja possível configura-lo.</p>
            </div>

            <div class="container-select-disp">
                <div class="ctn-scan">
                    <p>ESCANEANDO REDE...</p>
                    <div class="ctn-scan-circles">
                        <div class="point-search first"></div>
                        <div class="point-search second"></div>
                        <div class="point-search third"></div>
                    </div>
                </div>
                <div class="ctn-dispositivos">
                    <div class="disp">
                        <i class="fa-solid fa-wifi"></i>
                        <div class="info-disp">
                            <h3>L3250 Series</h3>
                            <p>DIRECT-9E-EPSON-75AEBB</p>
                        </div>
                    </div>
                    <div class="disp">
                        <i class="fa-solid fa-wifi"></i>
                        <div class="info-disp">
                            <h3>L3250 Series</h3>
                            <p>DIRECT-9E-EPSON-75AEBB</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ctnBtnSubmit">
                <button class="btnSubmit primary" id="btnSubmitConfig">
                    <i class="fa-solid fa-arrow-rotate-right"></i>
                    <p>Buscar Novamente</p>
                </button>
            </div>
            <div class="ctnBtnSubmit">
                <button class="btnSubmit secundary produtoNaoEncontrado" id="btnSubmitConfig">
                    <i class="fa-solid fa-info"></i>
                    <p>Produto não encontrado</p>
                </button>
            </div>

            <div class="ctn-help help2">
                <span><i class="fa-regular fa-circle-question"></i> Certifique-se de que o dispositivo está em <a>modo de emparelhamento</a>.</span>
    </div>`
    const tipoDeDispositivo = `<div class="ctnProgressBar">
                    <div class="ctnInfoBar">
                        <p>PASSO 2 DE 4</p>
                        <p class="porcentagemConfig">50%</p>
                    </div>
                    <div class="progressBar">
                        <div class="baseProgressBar">
                            <div class="qtdProgressBar"></div>
                        </div>
                    </div>

                    <div class="container-title">
                        <h1 class="titleConfig">Tipo de Dispositivos</h1>
                        <p id="titleConfigP">Liste os aparelhos que você possui para gerarmos uma análise inteligente e pesonalizada de consumo.</p>
                    </div>

                </div>
                <div class="container-options">
                    <div class="option opt1">
                        <div class="option-info">
                            <i class="fa-solid fa-lightbulb"></i>
                            <div class="option-desc">
                                <h3 class="titloOpt">Iluminação</h3>
                                <p class="descOpt">Lâmpadas, fitas LED e sistemas de luz</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-circle-check checkOpt"></i>
                    </div>
                    <div class="option opt2">
                        <div class="option-info">
                            <i class="fa-solid fa-wind"></i>
                            <div class="option-desc">
                                <h3 class="titloOpt">Sistemas de climatização</h3>
                                <p class="descOpt">Ar condicionado, ventilador</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-circle-check checkOpt"></i>
                    </div>
                    <div class="option opt3">
                        <div class="option-info">
                            <i class="fa-solid fa-plug"></i>
                            <div class="option-desc">
                                <h3 class="titloOpt">Eletrodomésticos</h3>
                                <p class="descOpt">Televisões, fornos e cafeteiras</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-circle-check checkOpt"></i>
                    </div>
                    <div class="option opt4">
                        <div class="option-info">
                            <i class="fa-solid fa-snowflake"></i>
                            <div class="option-desc">
                                <h3 class="titloOpt">Refrigeração</h3>
                                <p class="descOpt">Geladeiras, freezers e adegas</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-circle-check checkOpt"></i>
                    </div>
                    <div class="ctnOther">
                        <div class="option opt5">
                            <div class="option-info">
                                <i class="fa-solid fa-note-sticky"></i>
                                <div class="option-desc">
                                    <h3 class="titloOpt">Outro</h3>
                                    <p class="descOpt">Dispositivos não listados acima</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-circle-check checkOpt"></i>
                        </div>
                        <div class="ctnOther">
                            <!-- <h4>Digite os outros tipos de dispositivos: </h4>
                            <textarea name="textAreaDisps" id="textAreaDisp" maxlength="100" class="textAreaDisp"></textarea> -->
                        </div>
                    </div>

                    <div class="ctnBtnSubmit">
                        <button class="btnSubmit primary" id="btnSubmitConfig">
                            Continuar
                        </button>
                    </div>
    </div>`;
    const configurarWifi = `<div class="ctnProgressBar">
                <div class="ctnInfoBar">
                    <p>PASSO 3 DE 4</p>
                    <p class="porcentagemConfig">75%</p>
                </div>
                <div class="progressBar">
                    <div class="baseProgressBar" style="margin: 10px 0; width: 100%; border-radius: 5px; background-color: #9d9d9d;">
                        <div class="qtdProgressBar" style="width: 75%; padding: 1px; border-radius: 5px; background: linear-gradient(to right, #3b82f6, #76c6f4bd);"></div>
                    </div>
                </div>
            </div>
            <div class="container-title">
                <i class="fa-solid fa-house-signal"></i>
                <h1 class="titleConfig">Conectar Dispositivo</h1>
                <p>Conecte seu dispositivo a sua rede local para que seja possível conecta-lo a internet.</p>
            </div>

            <div class="container-config-wifi-inputs">
                <div class="inputs-config-wifi">
                    <div class="ctn-nome-wifi">
                        <label>NOME DA REDE WIFI</label><br>
                        <input type="text" placeholder="Ex: Minha_Rede_5G">
                        <i class="fa-solid fa-wifi"></i>
                    </div>
                    <div class="ctn-senha-wifi">
                        <label>SENHA</label><br>
                        <input type="password" placeholder="********">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>
                <div class="ctnBtnSubmit">
                    <button class="btnSubmit primary" id="btnSubmitConfig">
                        Confirmar
                    </button>
                </div>
            </div>

            <div class="ctn-help">
                <span><a href="/EnergySaveProject/public"><i class="fa-regular fa-circle-question"></i> Não consegue encontrar sua rede?</a></span>
    </div>`;

    const navButtonsContainer = document.querySelector(".nav-buttons");
    const accountContainer = document.querySelector(".account-container");

    userAccountInfo = JSON.parse(userAccountInfo); 
    /**
     * Campos userAccountInfo:
     *  - email
     *  - nome
     *  - id
     *  - profile_picture
     */

    if(userAccountInfo) {
        navButtonsContainer.innerHTML = 
        `               
            <img src="${userAccountInfo.profile_picture}" alt="avatar" class="profile">
        `;
    }

    const profileIcon = document.querySelector(".profile");

    if (profileIcon) {
        profileIcon.addEventListener("click", (e) => {

            const existing = document.getElementById("accountContainer");
            if (existing) {
                existing.remove();
                return;
            }

            const container = document.createElement("div");
            container.id = "accountContainer";

            container.innerHTML = `
                <div class="account-box">
                    <div class="account-header">
                        <img src="${userAccountInfo.profile_picture}" class="account-avatar">
                        <div class="account-info">
                            <p class="account-name">${userAccountInfo.nome}</p>
                            <p class="account-email">${userAccountInfo.email}</p>
                        </div>
                    </div>
                    <div class="account-actions">
                        <button id="btnSettings" class="account-btn">Configurações</button>
                        <button id="btnLogout" class="account-btn logout">Sair</button>
                    </div>
                </div>
            `;

            document.body.appendChild(container);

            const rect = profileIcon.getBoundingClientRect();

            // agora FIXO na tela
            container.style.position = "fixed";
            container.style.top = `${rect.bottom + 8}px`;

            const containerWidth = container.offsetWidth;

            let left = rect.right - containerWidth - 10;

            const padding = 10;
            const minLeft = padding;
            const maxLeft = window.innerWidth - containerWidth - padding;

            left = Math.max(minLeft, Math.min(left, maxLeft));

            container.style.left = `${left}px`;

            const btnSettings = document.getElementById("btnSettings");
            const btnLogout = document.getElementById("btnLogout");

            if (btnSettings) {
                btnSettings.addEventListener("click", () => {
                    window.location.href = "http://localhost/EnergySaveProject/myaccount";
                });
            }

            if (btnLogout) {
                btnLogout.addEventListener("click", async (e) => {
                    e.preventDefault();

                    try {
                        const r = await fetch(REQUEST_URL + "/logout", {
                            method: "GET",
                            credentials: "include"
                        });

                        const res = await r.json();
                        console.log(res);
                    } 
                    
                    catch (err) {
                        console.error(err);
                    }

                    location.reload();
                });
            }

            function close(e) {
                if (!container.contains(e.target) && e.target !== profileIcon) {
                    container.remove();
                    document.removeEventListener("click", close);
                    window.removeEventListener("scroll", handleScroll);
                }
            }

            function handleScroll() {
                container.remove();
                document.removeEventListener("click", close);
                window.removeEventListener("scroll", handleScroll);
            }

            document.addEventListener("click", close);
            window.addEventListener("scroll", handleScroll);

        });
    }

    // --------------------------------------------
    // Sistema de páginas 
    // --------------------------------------------

    //#region Sistema de páginas

    function addDispEventListener(){
        const dispositivos = document.querySelectorAll(".disp");

        dispositivos.forEach(disp => { // Para cada dispositivo que aparecer vai adicionar a lógica
            disp.addEventListener("click", () => {
                nextPage(3);
            });
        });
    }

    function addElementsEventListeners(page){
        if(page === 1){ // Gerenciar Dispositivo
            // --------------------------------------------
            // Botão de configurar dispositivo
            // --------------------------------------------

            //#region Botão configurar dispositivo

            document.querySelector(".btnConfigDisp").addEventListener("click", () => {
                nextPage(2)
            });

            //#endregion

        }

        else if(page === 2){ // Encontrar os dispositivos
            addDispEventListener();
        }

        else if(page === 3){ // Tipo de dispositivo
            // --------------------------------------------
            // Selecionar opções
            // --------------------------------------------

            //#region Selecionar opções


            document.querySelectorAll('.option').forEach(option => {
                option.addEventListener('click', () => {
                    option.classList.toggle('selected');
                    const selecionados = [...document.querySelectorAll('.option.selected')]
                    .map(el => el.querySelector('h3').textContent);
                });
            });




            // selecionados = ['Iluminação', 'Ar Condicionado', ...]

            //#endregion
            
            // --------------------------------------------
            // Botão de continuar
            // --------------------------------------------

            //#region Botão continuar

            document.querySelector(".btnSubmit").addEventListener("click", () => {
                const selecionados = [...document.querySelectorAll('.option.selected')]
                    .map(el => el.querySelector('h3').textContent);

                if(selecionados.length > 0){
                    nextPage(4);
                }
                else {
                    alert("Selecione pelo menos uma opção!");
                }
            });
            
            //#endregion
            
        }
        
        else if(page === 4){ // Configurar Wifi
        }
    }

    function verifyPage(page){

        switch(page){
            case 1:
                configContainer.innerHTML = gerenciarDispositivos;
                configContainer.style.padding = "50px";
                break;
            case 2:
                configContainer.innerHTML = selecionarDispositivo;
                configContainer.style.padding = "40px";
                break;
            case 3:
                configContainer.innerHTML = tipoDeDispositivo;
                configContainer.style.padding = "30px";
                break;
            case 4:
                configContainer.innerHTML = configurarWifi;
                configContainer.style.padding = "30px";
                break;
            case 4:
                configContainer.innerHTML = configurarWifi;
                configContainer.style.padding = "30px";
                break;
   
        }

        addElementsEventListeners(page);
    }

    function nextPage(page){
        
        switch(page){
            case 1:
                changePageAnimation(gerenciarDispositivos, 1);
                break;
            case 2:
                changePageAnimation(selecionarDispositivo, 2);
                break;
            case 3:
                changePageAnimation(tipoDeDispositivo, 3);
                break;
            case 4:
                changePageAnimation(configurarWifi, 4);
                break;
        }

    }

    function changePageAnimation(innerHTML, page){
        configContainer.style.animation = "opacityAnOut 0.5s ease";

        [...configContainer.children].forEach(e => {
            e.style.animation = "opacityAnOutElements 0.5s ease";
        });

        configContainer.addEventListener("animationend", () => {

            configContainer.innerHTML = innerHTML;
            configContainer.style.animation = "opacityAnIn 0.5s ease";

            addElementsEventListeners(page)

        }, { once: true });
    }

    verifyPage(page);

    //#endregion

})
