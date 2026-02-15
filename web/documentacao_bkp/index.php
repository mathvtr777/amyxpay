<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOC URANOPAY</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #333; /* Cor de fundo mais escura */
            color: #fff; /* Cor do texto */
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            color: #007bff;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
        code {
            background-color: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da; /* Cor de fundo para erros */
            border-color: #f5c6cb;
            color: #721c24; /* Cor do texto para erros */
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px; /* Espaçamento superior */
        }
        /* Corrigindo a cor do código */
        pre code {
            color: #333; /* Cor do texto no código */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Documentação da API Pix URANOPAY</h1>
        
        <p>Bem-vindo à documentação da API Pix do TKI Pay! Esta API permite que você integre facilmente a funcionalidade de geração de Pix em seus aplicativos.</p>
        
        <h2>Como Usar</h2>
        <p>Para usar a API Pix do TKI Pay, faça uma requisição HTTP POST para o endpoint <code>https://api.uranopay.com/v1/gateway/</code> com os seguintes parâmetros:</p>
        
        <!-- Parâmetros da Requisição -->
        
        <h2>Exemplo de Requisição Cash in</h2>
        <!-- Exemplo de Requisição -->
        <pre>
            <code>
            {
    "amount": 100,
    "client": {
        "name": "Maria Oliveira",
        "document": "123456789",
        "telefone": "11999999999",
        "email": "maria.oliveira@email.com"
    },
    "api-key": "81bb141jmdaw9u32-d3q9md3qd-qdwq59"
}
            </code>
        </pre>
  
        <h2>Respostas da API</h2>
        <ul>
            <!-- Lista de códigos de status e descrições -->
            <li><strong>200 OK:</strong> O Pix foi gerado com sucesso. A resposta incluirá detalhes do Pix.</li>
            <!-- Adicione mais códigos de status e descrições conforme necessário -->
        </ul>
        <div class="success">
            <h3>Exemplo de Resposta (200 OK)</h3>
            <p>Aqui está um exemplo de resposta quando a requisição é bem-sucedida:</p>
            <pre>
                <code>
{
    "status": "success",
    "message": "ok",
    "paymentCode": "00020101021226790014br.gov.bcb.pix255/v2/be1920df6b714e4e84edd77d7f25204000039865802BR592**63042CA1",
    "idTransaction": "52fc5262-4063-4900-933b-55e69850",
    "paymentCodeBase64": "iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6AQAAAACgl2eQAAACwElEQVR4Xu2XS5IjIQwF4SJw/1vMUeAiMJmi2y57IjpmMaXZGH+qDLl4IelJ5bJ/Xr/K+87b+gBnfYCzPsBZfweMUuoqpfTVGl91r9Knm4nA5D36Xn3uUQdY5epmJhA/Fyedr9IHMuvZTAXmnopDHRurtP4fgNVMUCuuvglbNsAbFlZQERo8cI22piU9dFaE8oCVMd2xTXdUiFNGHmlArQtNdWvWt1+gT6LNgHAL7SwOkImuRr8JHo9E/CwlGifvJp6Q2omQPNgktTTRzuVg8YwcSJwzGoHp04QSdUM4YfIFCDMs8ItJgt1DNpcQNsSoeKhTUTR12QlADHPqFalEaViM1FwzwV4tunhWW3DiLONXYxzP3BqxEihi/Ph3TVZCcBSm56xmYO0GsnSwGmAvZuzPkkaVYs8fPyWrNuBKFfaCG3MUzV688zm/YBPeYoM04waWp0umYBmDVHW67R+SuQrE0AXEeo2T2Ryt2K6PJOVACixEq0RMi3iYRvZD5EJgF3UyVQxcGSCWynmEo1seZtpm1dzJsAGJe9eJsgrmSvjhd33w5MI2UjnR51h+s+P/MATYN3BVX8Z7IygBpRIkfNcTo0Lum6uvt2wKaBJiITIns0ESOWClisyDqPOGBEimR5lAWMavOSIlYGLc5fknU7gGHCq9HMCNlQYDgoD1ihyOE+zVgkr3roSi5Pm8ybxSgRc9q+4MFOV/DLcE4BxikTbVP+H2sot2UeyEoBTG+wstVHA54ZPIhDaCt4hV8dBoTsbmOEZg7X0TPw329nAiL7lvUYySnJ5wHamEqvwSsgdPvBIZAGRGTSW+vVvmHg1e0ki8NP6AGd9gLM+wFn/APgNfNIphReCMrQAAAAASUVORK5CYII="
}
                </code>
            </pre>
        </div>
        
        <div class="error">
            <h3>Exemplo de Resposta (401 Unauthorized)</h3>
            <p>Aqui está um exemplo de resposta quando as credenciais são inválidas:</p>
            <pre>
                <code>
{
    "status": "error",
    "message": "Falha na solicitação: Credenciais inválidas"
}
                </code>
            </pre>
        </div>

        <h2>Confirmação de Pagamento</h2>
        <p>Para confirmar o pagamento, faça uma requisição HTTP POST para o endpoint <code>https://api.uranopay.com/v1/webhook/</code> com o seguinte parâmetro:</p>
        
        <h3>Parâmetros da Requisição</h3>
        <pre>
            <code>
{
    "idtransaction": "81bb141a-1746-49a8-bb4a-c3b8aa0d2259"
}
            </code>
        </pre>
        
        <h3>Exemplo de Resposta (200 OK)</h3>
        <p>será retornado:</p>
        <pre>
            <code>
{
    "status": "WAITING_FOR_APPROVAL" 
    // ou
    "status": "PAID_OUT"
}
            </code>
        </pre>
        
        <div class="error">
            <h3>Exemplo de Resposta (400 Bad Request)</h3>
            <p>Aqui está um exemplo de resposta quando a requisição é inválida:</p>
            <pre>
                <code>
{
    "error": "Invalid JSON or missing externalreference"
}
                </code>
            </pre>
        </div>
        
        <div class="error">
            <h3>Exemplo de Resposta (404 Not Found)</h3>
            <p>Aqui está um exemplo de resposta quando a referência externa não é encontrada:</p>
            <pre>
                <code>
{
    "error": "Transaction not found"
}
                </code>
            </pre>
        </div>
    </div>
</body>
</html>
