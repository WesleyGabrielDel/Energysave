import { request, throwError, BASE_URL, REQUEST_URL } from './lib/utils.js';

const configPage = "http://localhost/EnergySaveProject/device-settings";
const homePage = "http://localhost/EnergySaveProject/home";

let givenEmail, givenPassword, givenName;

document.addEventListener('DOMContentLoaded', function () {

    const card               = document.querySelector('.card');
    const sideLoginBtn       = document.getElementById('sideLoginBtn');
    const sideSignupBtn      = document.getElementById('sideSignupBtn');
    const forgotPasswordBtn  = document.getElementById('forgotPasswordSubmit');
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const backFromRemember   = document.getElementById('backFromRemember');
    const backFrom2fa        = document.getElementById('backFrom2fa');

    let activeForm = null;
    let activeFormListener = null;
    let googleHandler = null;


    if(paginaAtual){
        if(paginaAtual === 1){
            switchTo('loginActive');
        }

        else if(paginaAtual === 2){ 
            switchTo('signupActive');
        }
    }

    function addEvents(state){
        let dataForm, btnSubmit, btnForgotPassword;

        if (state === 'loginActive') {
            dataForm = document.querySelector('.formLogin .dataForm');
        } 
        
        else if (state === 'signupActive') {
            dataForm = document.querySelector('.formSignup .dataForm');
        } 
        
        else if (state === 'twofaActive') {
            dataForm = document.getElementById('twofaForm');
            btnSubmit = document.getElementById('twofaSubmitBtn');
        } 
        
        else {
            return;
        }

        if (!dataForm) return;

        if (!btnSubmit) {
            btnSubmit = dataForm.querySelector(".btnSubmit");
        }

        if (!btnSubmit) return;

        if (state === 'twofaActive') return;

        if (activeForm && activeFormListener) {
            activeForm.removeEventListener('submit', activeFormListener);
            activeFormListener = null;
        }

        activeForm = dataForm;

        const authType = state === 'loginActive'
            ? 'login'
            : state === 'signupActive'
                ? 'cadastro'
                : null;

        if (!authType) return;

        activeFormListener = function (e) {
            e.preventDefault();
            sendRequest(authType, dataForm);
        };

        dataForm.addEventListener('submit', activeFormListener);
    }

    // ============================================================
    //  FUNÇÕES DO GOOGLE
    // ============================================================
    
    async function handleCredentialResponse(response) {
        const token = response.credential;
        let cadastro = false;

        if (card.classList.contains('loginActive')) {
            cadastro = false;
        }
        else if (card.classList.contains('signupActive')) {
            cadastro = true;
        }

        const payload = JSON.stringify({ 
            token: token,   
            "loginType": 1,         
            "cadastro": cadastro    
        }) ;

        const resp = await request(REQUEST_URL + "/google", "POST", { body: payload });
        console.log(resp);
        
        if (resp?.data?.changeLocation) {
            window.location.href = configPage;
        } 
        else if (resp) {
            alert(resp.message || "Erro inesperado.");
        }
        
    }

    window.onload = function () {
        google.accounts.id.initialize({
            client_id: "315083166922-la7c6stmrbkau0dbj35rpto887nd4tm2.apps.googleusercontent.com",
            callback: handleCredentialResponse
        });

        google.accounts.id.prompt(); 
    };

    addEvents('loginActive');

    // ============================================================
    //  TROCA DE ABAS
    // ============================================================

    card.classList.add('init-anim');
    setTimeout(() => card.classList.remove('init-anim'), 700);

    function switchTo(newState) {
        card.classList.remove('loginActive', 'signupActive', 'rememberActive', 'twofaActive');
        void card.offsetWidth;
        card.classList.add(newState, 'animating');

        setTimeout(() => {
            card.classList.remove('animating');
            addEvents(newState); 
        }, 50);
    }

    window.switchTo = switchTo;

    sideLoginBtn.addEventListener('click', () => {
        if (!card.classList.contains('loginActive')) switchTo('loginActive');
    });

    sideSignupBtn.addEventListener('click', () => {
        if (!card.classList.contains('signupActive')) switchTo('signupActive');
    });

    forgotPasswordLink?.addEventListener('click', () => switchTo('rememberActive'));

    forgotPasswordBtn.addEventListener('click', () => {
        console.log("a");
    })

    backFromRemember?.addEventListener('click', () => switchTo('loginActive'));
    backFrom2fa?.addEventListener('click', () => switchTo('loginActive'));

    // ============================================================
    //  API pública: abre o painel 2FA com o email do usuário
    //  Chamada pelo script-auth.js após login bem-sucedido com 2FA
    //  Uso: window.open2FA('joao@email.com')
    // ============================================================

    window.open2FA = function (email) {
        const hint = document.getElementById('twofaEmailHint');
        if (hint) {
            const [user, domain] = email.split('@');
            const masked = user.slice(0, 2) + '**@' + domain;
            hint.textContent = masked;
        }
        otpReset();
        switchTo('twofaActive');
        document.getElementById('twofaSubmitBtn').classList.add('btnSubmit');
        setTimeout(() => getOtpInputs()[0]?.focus(), 450);
    };

    // ============================================================
    //  LÓGICA OTP
    // ============================================================

    const OTP_LENGTH     = 6;
    const RESEND_TIMEOUT = 30;

    const otpContainer   = document.getElementById('otpFields');
    const otpStatus      = document.getElementById('otpStatus');
    const twofaForm      = document.getElementById('twofaForm');
    const twofaSubmitBtn = document.getElementById('twofaSubmitBtn');
    const resendBtn      = document.getElementById('twofaResendBtn');

    for (let i = 0; i < OTP_LENGTH; i++) {
        const inp = document.createElement('input');
        inp.type        = 'tel';
        inp.maxLength   = 1;
        inp.inputMode   = 'numeric';
        inp.pattern     = '[0-9]*';
        inp.autocomplete = i === 0 ? 'one-time-code' : 'off';
        inp.dataset.idx = i;
        otpContainer.appendChild(inp);
    }

    function getOtpInputs() {
        return [...otpContainer.querySelectorAll('input')];
    }

    function getOtpValue() {
        return getOtpInputs().map(i => i.value).join('');
    }

    getOtpInputs().forEach((inp, i, all) => {

        inp.addEventListener('keydown', e => {
            if (e.key === 'Backspace') {
                e.preventDefault();
                if (inp.value) {
                    inp.value = '';
                    inp.classList.remove('otp-filled');
                } else if (i > 0) {
                    all[i - 1].focus();
                    all[i - 1].value = '';
                    all[i - 1].classList.remove('otp-filled');
                }
                otpClearState();
            }

            if (e.key === 'ArrowLeft'  && i > 0)            all[i - 1].focus();
            if (e.key === 'ArrowRight' && i < all.length - 1) all[i + 1].focus();

        });

        inp.addEventListener('input', e => {
            const digit = e.target.value.replace(/\D/g, '');

            if (digit.length > 1) {
                distributePaste(digit, i);
                return;
            }

            inp.value = digit;
            digit ? inp.classList.add('otp-filled') : inp.classList.remove('otp-filled');

            if (digit && i < all.length - 1) all[i + 1].focus();
            otpClearState();
        });

        inp.addEventListener('focus', () => inp.select());

        inp.addEventListener('paste', e => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, OTP_LENGTH);
            distributePaste(text, 0);
        });
    });

    function distributePaste(digits, startIndex) {
        const all = getOtpInputs();
        digits.split('').forEach((d, j) => {
            const target = all[startIndex + j];
            if (target) {
                target.value = d;
                target.classList.add('otp-filled');
            }
        });
        const nextFocus = Math.min(startIndex + digits.length, all.length - 1);
        all[nextFocus].focus();
        otpClearState();
    }

    function otpSetState(state) {
        getOtpInputs().forEach(inp => {
            inp.classList.remove('otp-error', 'otp-success');
            if (state) inp.classList.add('otp-' + state);
        });
    }

    function otpClearState() {
        otpSetState(null);
        setStatus('', '');
    }

    function otpReset() {
        getOtpInputs().forEach(inp => {
            inp.value = '';
            inp.classList.remove('otp-filled', 'otp-error', 'otp-success');
        });
        setStatus('', '');
        twofaSubmitBtn.disabled = false;
    }

    function setStatus(msg, type) {
        otpStatus.textContent = msg;
        otpStatus.className   = 'otp-status' + (type ? ' is-' + type : '');
    }

    twofaForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const code = getOtpValue();

        if (code.length < OTP_LENGTH) {
            otpSetState('error');
            setStatus('Preencha todos os ' + OTP_LENGTH + ' dígitos.', 'error');
            return;
        }

        twofaSubmitBtn.disabled = true;
        twofaSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verificando...';
        setStatus('', '');
        let status, resposta;

        try {
            resposta = await sendRequest('twofaActive', twofaForm, code);

            if (resposta.success && resposta.changeLocation) {
                window.location.href = "/EnergySaveProject/device-settings";
                return;
            }

            if (!resposta.success) {
                status = "error";
            } else {
                status = "success";
            }

            otpSetState(status);
            setStatus(resposta.message || 'Código verificado!', status);
        } 
        catch (err) {
            otpSetState('error');
            setStatus('Erro de conexão. Tente novamente.', 'error');
        } 
        finally {
            twofaSubmitBtn.disabled = false;
            twofaSubmitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Verificar código';
        }

    });

    let resendTimer = null;

    function startResendCountdown() {
        let remaining = RESEND_TIMEOUT;
        resendBtn.disabled = true;

        function tick() {
            resendBtn.innerHTML =
                `<i class="fa-solid fa-rotate-right"></i> Reenviar em ${remaining}s`;
            if (remaining <= 0) {
                clearInterval(resendTimer);
                resendBtn.disabled = false;
                resendBtn.innerHTML =
                    '<i class="fa-solid fa-rotate-right"></i> Reenviar código';
                return;
            }
            remaining--;
        }

        tick();
        resendTimer = setInterval(tick, 1000);
    }

    resendBtn.addEventListener('click', async () => {
        if (resendBtn.disabled) return;

        setStatus('Reenviando código...', 'info');
        startResendCountdown();

        try {
            const payload = JSON.stringify({ 
                email: window.givenEmail,
                emailType: "cadastro"
            }) ;

            const res = await request(REQUEST_URL + "/resend-code", "POST", { body: payload });

            if (res?.success) {
                setStatus('Código reenviado! Verifique seu email.', 'info');
            } 
            
            else {
                setStatus(res?.message || 'Não foi possível reenviar.', 'error');
            }
        } catch {
            setStatus('Erro ao reenviar. Tente mais tarde.', 'error');
        }
    });

    const observer = new MutationObserver(() => {
        if (card.classList.contains('twofaActive')) {
            if (!resendBtn.disabled) startResendCountdown();
        } else {
            clearInterval(resendTimer);
            resendBtn.disabled = false;
            resendBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Reenviar código';
        }
    });

    observer.observe(card, { attributes: true, attributeFilter: ['class'] });

});

async function sendRequest(type, form = null, extra = null){
    let url, payload, formData, res;
    let givenEmail, givenPassword, givenName;

    if(form !== null){
        formData = new FormData(form);
    }

    switch(type){
        case "login":
            url = REQUEST_URL + "/login";
            payload = JSON.stringify({ 
                loginType: 2, 
                email: formData.get("email"),
                password: formData.get("password"),
                remember: formData.get("remember") === "on",
                cadastro: false
            });
            break;

        case "cadastro":
            url = REQUEST_URL + "/signup";
            payload = JSON.stringify({ 
                loginType: 2,
                email: formData.get("email"),
                password: formData.get("senha"),
                name: formData.get("nome"),
                cadastro: true
            });

            window.givenEmail = formData.get("email");
            window.givenPassword = formData.get("senha");
            window.givenName = formData.get("nome");

            if (formData.get("senha").length < 8) {
                alert("A senha tem menos que 8 caracteres");
                return;
            }

            break;
        
        case "twofaActive":
            url = REQUEST_URL + "/src/api/services/auth/VerifyEmailCodeService.php";
            payload = JSON.stringify({ 
                type: "validate",
                userCode: extra,
                email: window.givenEmail,
                password: window.givenPassword,
                name: window.givenName
            }) 
            console.log(payload);
            console.log(`${window.givenEmail} | ${window.givenPassword} | ${window.givenName}`);
            break;

        default:
            throwError("Tipo incorreto de sendRequest inserido!");
            return;
    }

    res = await request(url, "POST", { body: payload, credentials: "include" });
    console.log(res);
    
    if (type === "twofaActive" && !res.success) {
        return res;
    }

    if(res.success && type === "cadastro"){
        switchTo('twofaActive');
        return res;
    }

    if (res?.data?.changeLocation && type === "login") {
        window.location.href = configPage;
        return res;
    } 

    const alertMessage = res?.message || "Erro inesperado.";
    if(type !== "twofaActive") {
        alert(alertMessage);
    }
    
    return res;
}