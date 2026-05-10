import { BASE_URL, REQUEST_URL } from './lib/utils.js';

let userSessions;

document.addEventListener("DOMContentLoaded", async function () {

    const navButtonsContainer = document.querySelector(".nav-buttons");
    const accountContainer = document.querySelector(".account-container");

    userAccountInfo = JSON.parse(userAccountInfo); 
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

            const btnLogout = document.getElementById("btnLogout");

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

    /* =============================================
    PEGANDO AS INFORMAÇÕES DA CONTA CONECTADA
    ============================================= */

    let accountData;

    try {
        const r = await fetch(REQUEST_URL + "/getUserData", { 
            method: "GET", 
            credentials: "include"
        });

        accountData = await r.json();
    }
    catch(e) {
        throwError(`Não foi possível resgatar as informações do usuário! ${e}`, 400)
    }

    if (!accountData.push) {
        accountData.pushPico = false;
        accountData.pushDicas = false;
        accountData.pushResumo = false;
        accountData.pushSistema = false;

        const subToggles = document.getElementById("push-sub-toggles");
        if (subToggles) {
            subToggles.classList.remove("expanded");
        }
    }

    /* =============================================
    PEGANDO AS SESSÕES CONECTADAS A CONTA
    ============================================= */
  
    try {
        const r = await fetch(REQUEST_URL + "/getUserSessions", { 
            method: "GET", 
            credentials: "include"
        });

        userSessions = await r.json();
    }
    catch(e) {
        throwError(`Não foi possível resgatar as informações do usuário! ${e}`, 400)
    }

    const userSessionsIdentifier = refreshConfigData(accountData, userSessions);

    //#region 


    /* =============================================
       NAVEGAÇÃO SIDEBAR
    ============================================= */
    const sidebarItems = document.querySelectorAll('.line-sidebar[data-tab]');
    const panels = document.querySelectorAll('.container-options[data-panel]');

    sidebarItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const tab = item.getAttribute('data-tab');
            sidebarItems.forEach(i => i.classList.remove('selected-line'));
            item.classList.add('selected-line');
            panels.forEach(p => p.classList.remove('active'));
            document.querySelector(`[data-panel="${tab}"]`).classList.add('active');
        });
    });

    document.querySelector('[data-tab="home"]').click();

    /* =============================================
       POPUP: DESVINCULAR DISPOSITIVO
    ============================================= */
    const showPopupBtn = document.querySelector("#btn-disconnect-disp");
    const closePopupBtn = document.querySelector("#btn-cancel-disconnect");
    const popup = document.querySelector(".popup-exclude-device");

    if (showPopupBtn) showPopupBtn.onclick = () => popup.classList.add('active');
    if (closePopupBtn) closePopupBtn.onclick = () => popup.classList.remove('active');

    /* =============================================
       2FA
    ============================================= */
    const toggle2fa = document.getElementById("chk2fa");
    if (toggle2fa) {
        toggle2fa.addEventListener("change", function () {
            showToast(toggle2fa.checked ? "2FA ativado com sucesso." : "2FA desativado.");
        });
    }

    /* =============================================
       TOGGLE PUSH — expande/colapsa sub-toggles
    ============================================= */
    const chkPush = document.getElementById("chkpush");
    const subToggles = document.getElementById("push-sub-toggles");
    const subCheckboxes = subToggles ? subToggles.querySelectorAll('input[type="checkbox"]') : [];

    if (chkPush && subToggles) {
        chkPush.addEventListener("change", function () {
            if (chkPush.checked) {
                subToggles.classList.add("expanded");
            } else {
                subToggles.classList.remove("expanded");
                subCheckboxes.forEach(c => c.checked = false);
            }
            showToast(chkPush.checked ? "Notificações push ativadas." : "Notificações push desativadas.");
        });
    }

    /* =============================================
       POPUP: FOTO DE PERFIL
    ============================================= */
    const btnOpenAvatar = document.getElementById("btn-open-avatar");
    const popupAvatar = document.getElementById("popup-avatar");
    const btnCloseAvatar = document.getElementById("btn-close-avatar");
    const btnCancelAvatar = document.getElementById("btn-cancel-avatar");
    const btnSaveAvatar = document.getElementById("btn-save-avatar");
    const avatarFileInput = document.getElementById("avatar-file-input");
    const avatarUploadArea = document.getElementById("avatar-upload-area");
    const avatarPreview = document.getElementById("avatar-preview");
    const avatarPreviewImg = document.getElementById("avatar-preview-img");
    const profileAvatar = document.getElementById("profile-avatar");
    const homeAvatar = document.getElementById("home-avatar");

    let pendingAvatarSrc = null;

    function openAvatarPopup() { popupAvatar.classList.add('active'); }
    function closeAvatarPopup() {
        popupAvatar.classList.remove('active');
        pendingAvatarSrc = null;
        avatarPreview.style.display = "none";
        avatarUploadArea.style.display = "flex";
        avatarFileInput.value = "";
    }

    const toggleIds = [
        "chkpush",
        "chkpush-pico",
        "chkpush-dicas",
        "chkpush-resumo",
        "chkpush-sistema",
        "chkrelatorios",
        "chk2fa"
    ];

    toggleIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener("change", () => {
                changeUserInfo("toggle");
            });
        }
    });

    if (btnOpenAvatar) btnOpenAvatar.addEventListener("click", openAvatarPopup);
    if (btnCloseAvatar) btnCloseAvatar.addEventListener("click", closeAvatarPopup);
    if (btnCancelAvatar) btnCancelAvatar.addEventListener("click", closeAvatarPopup);

    // Drag & drop
    if (avatarUploadArea) {
        avatarUploadArea.addEventListener("dragover", e => { e.preventDefault(); avatarUploadArea.style.borderColor = "var(--primaria)"; });
        avatarUploadArea.addEventListener("dragleave", () => avatarUploadArea.style.borderColor = "");
        avatarUploadArea.addEventListener("drop", e => {
            e.preventDefault();
            avatarUploadArea.style.borderColor = "";
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith("image/")) loadAvatarPreview(file);
        });
    }

    if (avatarFileInput) {
        avatarFileInput.addEventListener("change", function () {
            if (this.files[0]) loadAvatarPreview(this.files[0]);
        });
    }

    function loadAvatarPreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            pendingAvatarSrc = e.target.result;
            avatarPreviewImg.src = pendingAvatarSrc;
            avatarUploadArea.style.display = "none";
            avatarPreview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }

    if (btnSaveAvatar) {
        btnSaveAvatar.addEventListener("click", function () {
            if (!pendingAvatarSrc) { showToast("Selecione uma imagem primeiro.", true); return; }
            if (profileAvatar) profileAvatar.src = pendingAvatarSrc;
            if (homeAvatar) homeAvatar.src = pendingAvatarSrc;
            changeUserAvatar(pendingAvatarSrc);
            closeAvatarPopup();
        });
    }

    /* =============================================
       POPUP: ALTERAR SENHA
    ============================================= */
    const btnChangePassword = document.getElementById("btn-change-password");
    const popupPassword = document.getElementById("popup-change-password");
    const btnClosePassword = document.getElementById("btn-close-password");
    const btnCancelPassword = document.getElementById("btn-cancel-password");
    const btnSavePassword = document.getElementById("btn-save-password");

    if (btnSavePassword) {
        btnSavePassword.addEventListener("click", function () {
            changeUserInfo("password")
        });
    }

    function openPasswordPopup() { popupPassword.classList.add('active'); }
    function closePasswordPopup() {
        popupPassword.classList.remove('active');
        ["current-password","new-password","confirm-password"].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = "";
        });
    }

    if (btnChangePassword) btnChangePassword.addEventListener("click", openPasswordPopup);
    if (btnClosePassword) btnClosePassword.addEventListener("click", closePasswordPopup);
    if (btnCancelPassword) btnCancelPassword.addEventListener("click", closePasswordPopup);

    if (btnSavePassword) {
        btnSavePassword.addEventListener("click", function () {
            const current = document.getElementById("current-password").value;
            const newPw = document.getElementById("new-password").value;
            const confirm = document.getElementById("confirm-password").value;
            if (!current || !newPw || !confirm) { showToast("Preencha todos os campos.", true); return; }
            if (newPw !== confirm) { showToast("As novas senhas não coincidem.", true); return; }
            if (newPw.length < 8) { showToast("A senha deve ter ao menos 8 caracteres.", true); return; }
            closePasswordPopup();
        });
    }

    /* =============================================
       FECHAR POPUPS AO CLICAR NO BACKDROP
    ============================================= */
    [popupAvatar, popupPassword, popup].forEach(p => {
        if (!p) return;
        p.addEventListener("click", function (e) {
            if (e.target === p) {
                p.classList.remove('active');
                if (p === popupAvatar) { pendingAvatarSrc = null; avatarPreview.style.display="none"; avatarUploadArea.style.display="flex"; }
            }
        });
    });

    /* =============================================
       SALVAR INFORMAÇÕES PESSOAIS
    ============================================= */
    const btnSaveInfo = document.getElementById("btn-save-info");
    if (btnSaveInfo) {
        btnSaveInfo.addEventListener("click", function () {
            // Bloqueia campos editáveis após salvar
            document.querySelectorAll('.container-account .account-input:not([readonly])').forEach(inp => {
                if (inp.tagName === "INPUT") inp.setAttribute("readonly", true);
            });
            changeUserInfo("personal");
        });
    }

    const btnSaveRecovery = document.getElementById("btn-save-recovery");
    if (btnSaveRecovery) {
        btnSaveRecovery.addEventListener("click", function () {
            document.querySelectorAll('.recovery-fields .account-input').forEach(inp => {
                if (inp.tagName === "INPUT") inp.setAttribute("readonly", true);
            });
            changeUserInfo("recovery");
            showToast("Dados de recuperação salvos!");
        });
    }

    /* =============================================
       ENCERRAR TODAS AS SESSÕES
    ============================================= */
    const btnRevokeAll = document.getElementById("btn-revoke-all");
    if (btnRevokeAll) {
        btnRevokeAll.addEventListener("click", async function () {

            let res;
            try {
                await fetch(REQUEST_URL + "/revokeAll", { 
                    method: "GET", 
                    credentials: "include"
                });
            }   
            catch(e){
                throwError(`Não foi remover a sessão! ${e}`, 400)
            }

            const cards = document.querySelectorAll(".session-card:not(.session-current)");
            cards.forEach(card => {
                card.style.transition = "opacity 0.3s, transform 0.3s";
                card.style.opacity = "0";
                card.style.transform = "translateX(20px)";
                setTimeout(() => card.remove(), 320);
            });
            
            showToast("Todas as outras sessões foram encerradas.");
        });
    }

    //#endregion
});

/* =============================================
   FUNÇÕES GLOBAIS
============================================= */

// Alternar campo readonly / editável
function toggleEdit(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isReadonly = input.hasAttribute("readonly");
    if (isReadonly) {
        input.removeAttribute("readonly");
        input.focus();
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        btn.title = "Confirmar";
    } else {
        input.setAttribute("readonly", true);
        btn.innerHTML = '<i class="fa-solid fa-pen"></i>';
        btn.title = "Editar";
    }
}

// Mostrar/ocultar senha
function togglePw(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    if (input.type === "password") {
        input.type = "text";
        btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
    } else {
        input.type = "password";
        btn.innerHTML = '<i class="fa-solid fa-eye"></i>';
    }
}

// Encerrar sessão individual
async function revokeSession(sessionId, btn) {

    let res;
    try {
        await fetch(REQUEST_URL + "/revokeSession", { 
            method: "POST", 
            credentials: "include",
            body: JSON.stringify({
                "id": sessionId
            })
        });
    }   
    catch(e){
        throwError(`Não foi remover a sessão! ${e}`, 400)
    }

    const card = btn.closest(".session-card");
    if (!card) return;
    card.style.transition = "opacity 0.3s, transform 0.3s";
    card.style.opacity = "0";
    card.style.transform = "translateX(20px)";
    setTimeout(() => {
        card.remove();
        if(!typeof variavel !== "undefined"){
            showToast("Sessão encerrada.");
        }
        else {
            showToast("Não foi possível encerrar a sessão!", true);
        }
    }, 320);
}

// Toast de feedback
let toastTimer = null;
function showToast(msg, isError = false) {
    let toast = document.querySelector(".toast");
    if (!toast) {
        toast = document.createElement("div");
        toast.className = "toast";
        document.body.appendChild(toast);
    }
    toast.className = "toast" + (isError ? " toast-error" : "");
    toast.innerHTML = `<i class="fa-solid fa-${isError ? "circle-xmark" : "circle-check"}"></i> ${msg}`;
    toast.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove("show"), 3200);
}

function refreshConfigData(data, userSessions = false){
    // Inputs comuns
    document.getElementById("field-nome").value = data.nome;
    document.getElementById("showable-name").textContent = data.nome;
    document.getElementById("field-email").value = data.email;
    document.getElementById("showable-email").textContent = data.email;
    document.getElementById("field-telefone").value = data.telefone;
    document.getElementById("field-nascimento").value = data.nascimento;
    document.getElementById("field-criado").value = data.criado;

    // Selects
    document.getElementById("field-genero").value = data.genero;
    document.getElementById("field-idioma").value = data.idioma;

    // Checkboxes
    document.getElementById("chkpush").checked = data.push;
    document.getElementById("chkpush-pico").checked = data.pushPico;
    document.getElementById("chkpush-dicas").checked = data.pushDicas;
    document.getElementById("chkpush-resumo").checked = data.pushResumo;
    document.getElementById("chkpush-sistema").checked = data.pushSistema;

    document.getElementById("chkrelatorios").checked = data.relatoriosEmail;
    document.getElementById("chk2fa").checked = data.twoFA;

    // Recovery
    document.getElementById("field-email-recovery").value = data.emailRecovery;
    document.querySelector(".sec-date").textContent = data.password_change;

    // Profile picture
    document.getElementById("profile-avatar").src = data.avatar;

    // limpa os cards antigos 
    const sessionsContainer = document.querySelector(".sessions-ctn");
    sessionsContainer.querySelectorAll(".session-card").forEach(el => el.remove());

    const userSessionsIdentifier = [];

    if (Array.isArray(userSessions) && userSessions.length > 0) {

        userSessions.forEach(session => {

            const card = document.createElement("div");
            card.classList.add("session-card");

            card.dataset.sessionId = session.id;

            userSessionsIdentifier.push({
                element: card,
                remember_token_id: session.remember_token_id
            });

            if (session.is_current) {
                card.classList.add("session-current");
            }

            let iconClass = "fa-computer";
            let extraIconClass = "";

            if (session.tipo_dispositivo === "Mobile") {
                iconClass = "fa-mobile-screen-button";
                extraIconClass = "session-icon-mobile";
            } 
            else if (session.tipo_dispositivo === "Tablet") {
                iconClass = "fa-tablet-screen-button";
                extraIconClass = "session-icon-tablet";
            } 
            else {
                iconClass = "fa-computer";
                extraIconClass = "session-icon-pc";
            }

            card.innerHTML = `
                ${session.is_current ? `<div class="session-badge-current">Dispositivo atual</div>` : ""}
                <div class="session-main">
                    <div class="session-icon ${extraIconClass}">
                        <i class="fa-solid ${iconClass}"></i>
                    </div>
                    <div class="session-info">
                        <h4>${session.sistema_operacional} · ${session.origem_login}</h4>
                        <p class="session-meta">
                            <i class="fa-solid fa-location-dot"></i> ${session.ip}
                            &nbsp;·&nbsp;
                            <i class="fa-solid fa-clock"></i> ${session.is_current ? "Ativo agora" : session.primeiro_login}
                        </p>
                    </div>
                </div>
                ${!session.is_current ? `
                <button class="session-revoke-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Encerrar
                </button>` : ""}
            `;

            if (!session.is_current) {
                const btn = card.querySelector(".session-revoke-btn");
                btn.addEventListener("click", () => {
                    const card = btn.closest(".session-card");
                    const sessionId = card.dataset.sessionId;
                    revokeSession(sessionId, btn);
                });
            }

            if (session.is_current) {
                sessionsContainer.insertBefore(card, sessionsContainer.firstChild);
            } 
            else {
                sessionsContainer.insertBefore(card, sessionsContainer.querySelector(".sessions-footer"));
            }

        });
    }

    return userSessionsIdentifier;
}

async function changeUserInfo(type){

    if(type === "personal"){
        const data = {
            nome: document.getElementById("field-nome").value,
            telefone: document.getElementById("field-telefone").value,
            nascimento: document.getElementById("field-nascimento").value,
            genero: document.getElementById("field-genero").value,
            idioma: document.getElementById("field-idioma").value,
        };
        
        const r = await fetch(REQUEST_URL + "/updateUserData", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                type: type,
                nome: data.nome,
                telefone: data.telefone,
                nascimento: data.nascimento,
                genero: data.genero,
                idioma: data.idioma
            })
        });

        const res = await r.json();
        console.log(res)
        showToast(typeof res === "string" ? res : res.message, res.typeMessage === "error");

        document.getElementById("showable-name").textContent = data.nome;
    }

    else if(type === "recovery"){
        const data = {
            emailRecovery: document.getElementById("field-email-recovery").value,
        };

        const r = await fetch(REQUEST_URL + "/updateUserData", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                type: type,
                emailRecovery: data.emailRecovery
            })
        });

        const res = await r.json();
        showToast(typeof res === "string" ? res : res.message, res.typeMessage === "error");
    }

    else if(type === "password"){
        const data = {
            currentPassword: document.getElementById("current-password").value,
            newPassword: document.getElementById("new-password").value,
            confirmPassword: document.getElementById("confirm-password").value
        };

        const r = await fetch(REQUEST_URL + "/updateUserData", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                type: type,
                currentPassword: data.currentPassword,
                newPassword: data.newPassword,
                confirmPassword: data.confirmPassword
            })
        });

        const res = await r.json();
        showToast(typeof res === "string" ? res : res.message, res.typeMessage === "error");
    }

    else if(type === "toggle"){
        const data = {
            push: document.getElementById("chkpush")?.checked,
            pushPico: document.getElementById("chkpush-pico")?.checked,
            pushDicas: document.getElementById("chkpush-dicas")?.checked,
            pushResumo: document.getElementById("chkpush-resumo")?.checked,
            pushSistema: document.getElementById("chkpush-sistema")?.checked,
            relatoriosEmail: document.getElementById("chkrelatorios")?.checked,
            twoFA: document.getElementById("chk2fa")?.checked           
        };

        await fetch(REQUEST_URL + "/updateUserData", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                type: type,
                ...data
            })
        });

    }

}

async function changeUserAvatar(src) {
    const r = await fetch(REQUEST_URL + "/updateUserData", {
        method: "POST",
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            type: "avatar",
            src: src
        })
    });

    const res = await r.json();
    showToast(typeof res === "string" ? res : res.message, res.typeMessage === "error");   
}

function throwError(errorMessage, errorCode){
    let erro = new Error(errorMessage); // Cria um novo erro
    erro.code = errorCode; // Seta o código do erro

    console.log(errorMessage)
    throw erro; 
}

// Disponibilizar funções globalmente para onclick inline
window.toggleEdit = toggleEdit;
window.togglePw = togglePw;