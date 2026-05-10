# Importanto as Bibliotecas
import smtplib, os
from dotenv import load_dotenv
from email.message import EmailMessage
from flask import Flask, jsonify, request

#Diretórios do projeto
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DOTENV_PATH = BASE_DIR + "/.env"

load_dotenv(DOTENV_PATH)

# Criando o servidor Flask
app = Flask(__name__) 

@app.route("/health", methods=["GET"])
def health():
    return True

@app.route("/send-email", methods=["POST"])
def sendEmail():

    # Dados de envio
    data = request.json

    remetente = "teamenergysave.contact@gmail.com"
    destinatario = data["email"]
    codigo = data["code"]
    assunto = ""
    mensagem_html = ""
    app_key = os.getenv("APP_KEY")

    if("type" not in data):
        return jsonify("O campo 'type' é obrigatório!"), 400

    if(data["type"] == "cadastro"):

        assunto = "Seu código de cadastro"
        mensagem_html = f"""
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Seu código de acesso</title>
        <style>
            body {{
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                color: #f5f5f7;
            }}

            table {{
                border-spacing: 0;
            }}

            .wrapper {{
                width: 100%;
                background:
                    radial-gradient(circle at top left, rgba(131, 58, 180, 0.08), transparent 28%),
                    radial-gradient(circle at top right, rgba(253, 29, 29, 0.06), transparent 24%),
                    linear-gradient(180deg, #ffffff 0%, #f3f4f6 100%);
                padding: 40px 16px;
            }}

            .card {{
                width: 100%;
                height: 800px;
                max-width: 560px;
                margin: 0 auto;
                background: #151922;
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
                overflow: hidden;
            }}

            .hero {{
                padding: 36px 52px 18px 52px;
                text-align: center;
                margin-bottom: 47px;
            }}

            .logo {{
                margin: 0;
                font-size: 34px;
                font-weight: 700;
                letter-spacing: -0.03em;
                color: #ffffff;
            }}

            .subtitle {{
                margin: 1px 0 30px 0;
                font-size: 15px;
                line-height: 1.35;
                color: #c7cad1;
            }}

            .content {{
                padding: 0 52px 32px 52px;
            }}

            .greeting {{
                margin: 0 0 16px 0;
                font-size: 16px;
                color: #f5f5f7;
            }}

            .paragraph {{
                margin: 0 0 14px 0;
                font-size: 15px;
                line-height: 1.75;
                color: #d3d7df;
            }}

            .highlight {{
                font-weight: 600;
                color: #ffffff;
            }}

            .code-box {{
                margin: 28px 0;
                padding: 26px 20px;
                text-align: center;
                background: linear-gradient(180deg, #1b2130 0%, #161b26 100%);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 22px;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }}

            .code-label {{
                margin-bottom: 12px;
                font-size: 12px;
                color: #a3a8b3;
                font-weight: 700;
                letter-spacing: 0.14em;
                text-transform: uppercase;
            }}

            .code {{
                font-size: 34px;
                line-height: 1;
                font-weight: 700;
                letter-spacing: 12px;
                color: #ffffff;
            }}

            .note {{
                margin: 0;
                font-size: 13px;
                line-height: 1.7;
                color: #9ea4af;
                text-align: center;
            }}

            .footer {{
                padding: 32px 52px 42px 52px;
                border-top: 1px solid rgba(255, 255, 255, 0.06);
                text-align: center;
            }}

            .footer p {{
                margin: 6px 0;
                font-size: 12px;
                line-height: 1.6;
                color: #8f96a3;
            }}

            @media only screen and (max-width: 600px) {{
                .wrapper {{
                    padding: 20px 10px;
                }}

                .hero,
                .content,
                .footer {{
                    padding-left: 34px;
                    padding-right: 34px;
                }}

                .logo {{
                    font-size: 28px;
                }}

                .code {{
                    font-size: 28px;
                    letter-spacing: 8px;
                }}
            }}
        </style>
        </head>
        <body>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="wrapper">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="card">
                        <tr>
                            <td class="hero">
                                <h1 class="logo">EnergySave</h1>
                                <p class="subtitle">Confirmação de cadastro</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="content">
                                <p class="greeting">Olá, <span class="highlight">{destinatario}</span></p>
                                <p class="paragraph">Recebemos uma tentativa de criação de conta a partir de um dispositivo ou navegador.</p>
                                <p class="paragraph">Para continuar com a criação de conta, use o código de verificação abaixo:</p>

                                <div class="code-box">
                                    <div class="code-label">Código de verificação</div>
                                    <div class="code">{codigo}</div>
                                </div>

                                <p class="note">Este código é pessoal e expira em cinco minutos. Não compartilhe com ninguém.</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="footer">
                                <p>Se você não tentou criar uma conta, ignore este e-mail.</p>
                                <p>&copy; EnergySave</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </body>
        </html>
        """

    elif(data["type"] == "2fa"):

        assunto = "Seu código de acesso a conta"
        mensagem_html = f"""
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Seu código de acesso</title>
        <style>
            body {{
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                color: #f5f5f7;
            }}

            table {{
                border-spacing: 0;
            }}

            .wrapper {{
                width: 100%;
                background:
                    radial-gradient(circle at top left, rgba(131, 58, 180, 0.08), transparent 28%),
                    radial-gradient(circle at top right, rgba(253, 29, 29, 0.06), transparent 24%),
                    linear-gradient(180deg, #ffffff 0%, #f3f4f6 100%);
                padding: 40px 16px;
            }}

            .card {{
                width: 100%;
                height: 800px;
                max-width: 560px;
                margin: 0 auto;
                background: #151922;
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
                overflow: hidden;
            }}

            .hero {{
                padding: 36px 52px 18px 52px;
                text-align: center;
                margin-bottom: 47px;
            }}

            .logo {{
                margin: 0;
                font-size: 34px;
                font-weight: 700;
                letter-spacing: -0.03em;
                color: #ffffff;
            }}

            .subtitle {{
                margin: 1px 0 30px 0;
                font-size: 15px;
                line-height: 1.35;
                color: #c7cad1;
            }}

            .content {{
                padding: 0 52px 32px 52px;
            }}

            .greeting {{
                margin: 0 0 16px 0;
                font-size: 16px;
                color: #f5f5f7;
            }}

            .paragraph {{
                margin: 0 0 14px 0;
                font-size: 15px;
                line-height: 1.75;
                color: #d3d7df;
            }}

            .highlight {{
                font-weight: 600;
                color: #ffffff;
            }}

            .code-box {{
                margin: 28px 0;
                padding: 26px 20px;
                text-align: center;
                background: linear-gradient(180deg, #1b2130 0%, #161b26 100%);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 22px;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }}

            .code-label {{
                margin-bottom: 12px;
                font-size: 12px;
                color: #a3a8b3;
                font-weight: 700;
                letter-spacing: 0.14em;
                text-transform: uppercase;
            }}

            .code {{
                font-size: 34px;
                line-height: 1;
                font-weight: 700;
                letter-spacing: 12px;
                color: #ffffff;
            }}

            .note {{
                margin: 0;
                font-size: 13px;
                line-height: 1.7;
                color: #9ea4af;
                text-align: center;
            }}

            .footer {{
                padding: 32px 52px 42px 52px;
                border-top: 1px solid rgba(255, 255, 255, 0.06);
                text-align: center;
            }}

            .footer p {{
                margin: 6px 0;
                font-size: 12px;
                line-height: 1.6;
                color: #8f96a3;
            }}

            @media only screen and (max-width: 600px) {{
                .wrapper {{
                    padding: 20px 10px;
                }}

                .hero,
                .content,
                .footer {{
                    padding-left: 34px;
                    padding-right: 34px;
                }}

                .logo {{
                    font-size: 28px;
                }}

                .code {{
                    font-size: 28px;
                    letter-spacing: 8px;
                }}
            }}
        </style>
        </head>
        <body>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="wrapper">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="card">
                        <tr>
                            <td class="hero">
                                <h1 class="logo">EnergySave</h1>
                                <p class="subtitle">Verificação de dois fatores</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="content">
                                <p class="greeting">Olá, <span class="highlight">{destinatario}</span></p>
                                <p class="paragraph">Recebemos uma tentativa de acesso a sua conta a partir de um dispositivo ou navegador.</p>
                                <p class="paragraph">Para continuar com o acesso, use o código de verificação abaixo:</p>

                                <div class="code-box">
                                    <div class="code-label">Código de verificação</div>
                                    <div class="code">{codigo}</div>
                                </div>

                                <p class="note">Este código é pessoal e expira em cinco minutos. Não compartilhe com ninguém.</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="footer">
                                <p>Se você não tentou acessar sua conta, ignore este e-mail.</p>
                                <p>&copy; EnergySave</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </body>
        </html>
        """

    # Criar o email
    msg = EmailMessage()
    msg['From'] = remetente
    msg['To'] = destinatario
    msg['Subject'] = assunto
    msg.add_alternative(mensagem_html, subtype="html")

    # Enviar o email
    try:
        with smtplib.SMTP_SSL("smtp.gmail.com", 465) as email:
            email.login(remetente, app_key)
            email.send_message(msg)

    except Exception as e:
        return jsonify("Não foi possível enviar o email! " + str(e))

    return jsonify(f"Email enviado com sucesso para {destinatario}!")

if __name__ == "__main__":
    app.run(debug=True)
