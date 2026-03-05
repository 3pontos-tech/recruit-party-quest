<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Novo contato via site – Fale Conosco</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 15px;
                color: #222;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .wrapper {
                max-width: 600px;
                margin: 32px auto;
                background: #fff;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid #e0e0e0;
            }
            .header {
                background-color: #1a1a2e;
                color: #fff;
                padding: 28px 32px;
            }
            .header h1 {
                margin: 0;
                font-size: 20px;
                font-weight: 700;
            }
            .body {
                padding: 32px;
            }
            .field {
                margin-bottom: 20px;
            }
            .field-label {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: #666;
                margin-bottom: 4px;
            }
            .field-value {
                font-size: 15px;
                color: #222;
                line-height: 1.6;
                word-break: break-word;
            }
            .message-box {
                background: #f8f8f8;
                border-left: 4px solid #1a1a2e;
                padding: 16px 20px;
                border-radius: 0 4px 4px 0;
                white-space: pre-wrap;
            }
            .footer {
                padding: 20px 32px;
                background: #f5f5f5;
                border-top: 1px solid #e0e0e0;
                font-size: 12px;
                color: #999;
            }
            hr {
                border: none;
                border-top: 1px solid #e0e0e0;
                margin: 24px 0;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="header">
                <h1>Novo contato via site &ndash; Fale Conosco</h1>
            </div>
            <div class="body">
                <div class="field">
                    <div class="field-label">Nome</div>
                    <div class="field-value">{{ $senderName }}</div>
                </div>
                <div class="field">
                    <div class="field-label">E-mail</div>
                    <div class="field-value">{{ $senderEmail }}</div>
                </div>
                @if ($senderPhone)
                    <div class="field">
                        <div class="field-label">Telefone</div>
                        <div class="field-value">{{ $senderPhone }}</div>
                    </div>
                @endif

                <hr />
                <div class="field">
                    <div class="field-label">Mensagem</div>
                    <div class="field-value message-box">{{ $messageBody }}</div>
                </div>
            </div>
            <div class="footer">Enviado em: {{ $sentAt }}</div>
        </div>
    </body>
</html>
