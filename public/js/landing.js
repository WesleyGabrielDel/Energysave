import { request, throwError, BASE_URL, REQUEST_URL } from './lib/utils.js';

document.addEventListener("DOMContentLoaded", async function () {
    // Carregando Animações
    setObserverAnimations();
    setTypeWriterAnimation();
    
    // Carregando os eventos
    loadProfileIcon();
    bindEvents();
});

async function handleProfileIcon(profileIcon){

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

            await request(REQUEST_URL + "/logout");
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

}

function loadProfileIcon(){

    const navButtonsContainer = document.querySelector(".nav-buttons");
    const accountContainer = document.querySelector(".account-container");

    userAccountInfo = JSON.parse(userAccountInfo); 

    if (!userAccountInfo) {
        navButtonsContainer.innerHTML = 
        `               
            <button class="btn-signup" id="btnSignup">Sign Up</button>
            <button class="btn-login" id="btnLogin">Login</button>
        `;
    } 
    else {
        navButtonsContainer.innerHTML = 
        `               
            <img src="${userAccountInfo.profile_picture}" alt="avatar" class="profile">
        `;
    }

}

function setTypeWriterAnimation(){

    const hero1 = document.getElementById("heroHeadline1");
    const hero2 = document.getElementById("heroHeadline2");

    if (!hero1 || !hero2) return;

    hero2.style.visibility = "hidden";

    hero1.style.animation = "typing 1.5s steps(30) forwards, cursor .6s step-end infinite";
    hero1.style.borderRight = "3px solid #ffffff";

    hero1.addEventListener("animationend", () => {
        hero1.style.borderRight = "none";

        hero2.style.visibility = "visible";
        hero2.style.animation = "typing2 0.35s steps(30) forwards, cursor .6s step-end infinite";
        hero2.style.borderRight = "3px solid #ffffff";
    });

}

function setObserverAnimations(){

    const myObserver = new IntersectionObserver( (entries) => {
        entries.forEach( (entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show')
                    // console.log('Visivel')
            } else {
                entry.target.classList.remove('show')
                    // console.log('Invisivel')
            }
        })
    })

    const elements = document.querySelectorAll('.hidden')
    elements.forEach( (element) => myObserver.observe(element))

}

function bindEvents() {

    const btnSignup = document.getElementById('btnSignup');
    const btnLogin = document.getElementById('btnLogin');
    const btnStart = document.getElementById('btnStart');
    const btnStartTest = document.getElementById('btnStartTest');
    const profileIcon = document.querySelector(".profile");

    if (profileIcon) {
        profileIcon.addEventListener("click", () => {
            handleProfileIcon(profileIcon);
        });
    }

    if (btnSignup) {
        btnSignup.addEventListener("click", () => {
            window.location.href = "http://localhost/EnergySaveProject/auth?page=2";
        });
    }

    if (btnLogin) {
        btnLogin.addEventListener("click", () => {
            window.location.href = "http://localhost/EnergySaveProject/auth?page=1";
        });
    }

    if (btnStart) {
        btnStart.addEventListener("click", async () => {
            const isLogged = await request(REQUEST_URL + "/isLogged");

            if (isLogged?.data === true) {
                window.location.href = "http://localhost/EnergySaveProject/home";
            } else {
                window.location.href = BASE_URL + "views/auth.php";
            }
        });
    }

    if (btnStartTest) {
        btnStartTest.addEventListener("click", () => {
            window.location.href = BASE_URL + "views/auth.php?pagina=2";
        });
    }

}