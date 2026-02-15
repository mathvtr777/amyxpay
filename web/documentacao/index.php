<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URANOPAY API Documentation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="./document/fiveicon.png" type="image/x-icon">
    <meta name="description" content="API PIX. API PIX CASH-IN E CASH-OUT. DOCUMENTAÇÃO URANOPAY">
    <link href="./document/styles.css" rel="stylesheet">
    <script src="./6xjdo1voyy5kz1j3jqje6pjeit6msjg8.js" async></script>
</head>

<body>
    <div class="documentation-layout">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="./document/img/logo.png" alt="URANOPAY Logo">
            </div>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#introducao" class="nav-link active">Introdução</a>
                    </li>
                    <!--  <li class="nav-item">
                        <a href="#autenticacao" class="nav-link">Autenticação</a>
                    </li> -->
                    <li class="nav-item">
                        <a href="#endpoints" class="nav-link">Endpoints</a>
                        <ul class="submenu">
                            <li><a href="#create-transaction" class="nav-link">Criar Transação</a></li>
                            <li><a href="#webhook-transaction" class="nav-link">Webhook</a></li>
                            <li><a href="#get-transaction" class="nav-link">Consultar Transação</a></li>
                            <li><a href="#pay-transaction" class="nav-link">Realizar Pagamento</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="#erros" class="nav-link">Códigos de Erro</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">

            <!-- Introdução -->
            <section id="introducao" class="section">
                <div class="section-header">
                    <div class="section-badge">DOCUMENTAÇÃO API</div>
                    <h1 class="section-title">Introdução</h1>
                    <p class="section-description">Bem-vindo à API da URANOPAY, sua solução completa para integração
                        de pagamentos PIX. Nossa API foi projetada para ser intuitiva, robusta e segura, permitindo que
                        você integre facilmente funcionalidades de pagamento em suas aplicações.</p>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="./document/img/shield-icon.svg" alt="Segurança">
                        </div>
                        <h3>Segurança Avançada</h3>
                        <p>Proteção de dados e criptografia em todas as transações</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="./document/img/speed-icon.svg" alt="Performance">
                        </div>
                        <h3>Alta Performance</h3>
                        <p>Resposta rápida e processamento eficiente</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="./document/img/code-icon.svg" alt="Integração">
                        </div>
                        <h3>Fácil Integração</h3>
                        <p>Documentação clara e exemplos práticos</p>
                    </div>
                </div>
            </section>

            <!-- Autenticação -->
            <!--  <section id="autenticacao" class="section">
                <div class="section-header">
                    <div class="section-badge">AUTENTICAÇÃO</div>
                    <h2 class="section-title">Começando com a API</h2>
                    <p class="section-description">Todas as requisições à API precisam ser autenticadas usando um token
                        no cabeçalho HTTP. Obtenha seu token na <a href="https://api.uranopay.com/desenvolvedor/"
                            class="link-highlight">área do desenvolvedor</a>.</p>
                </div>

                <div class="api-method">
                    <div class="method-header">
                        <h3>Configuração do Token</h3>
                    </div>

                    <div class="code-tabs">
                        <button class="code-tab active" data-language="curl">cURL</button>
                        <button class="code-tab" data-language="php">PHP</button>
                        <button class="code-tab" data-language="python">Python</button>
                        <button class="code-tab" data-language="javascript">JavaScript</button>
                    </div>

                    <div class="code-examples">
                        <div class="code-block active" data-language="curl">
                            <div class="code-header">
                                <span class="code-language">cURL</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
curl -X POST https://api.uranopay.com/api/endpoint \
-H "Authorization: seu_token_aqui" \
-H "Content-Type: application/json"</code></pre>
                        </div>

                        <div class="code-block" data-language="php">
                            <div class="code-header">
                                <span class="code-language">PHP</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre>
                                <code >
$token = 'seu_token_aqui';

$headers = [
    'Authorization: ' . $token,
    'Content-Type: application/json'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.uranopay.com/api/endpoint");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
                                </code>
                            </pre>
                        </div>

                        <div class="code-block" data-language="python">
                            <div class="code-header">
                                <span class="code-language">Python</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
import requests

headers = {
    'Authorization': 'seu_token_aqui',
    'Content-Type': 'application/json'
}

response = requests.post(
    'https://api.uranopay.com/api/endpoint',
    headers=headers
)</code></pre>
                        </div>

                        <div class="code-block" data-language="javascript">
                            <div class="code-header">
                                <span class="code-language">JavaScript</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code>
const headers = {
    'Authorization': 'seu_token_aqui',
    'Content-Type': 'application/json'
};

fetch('https://api.uranopay.com/api/endpoint', {
    method: 'POST',
    headers: headers
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>
                        </div>
                    </div>
                </div>
            </section> -->

            <!-- Endpoints -->
            <section id="endpoints" class="section">
                <!-- Create Transaction -->
                <div id="create-transaction" class="api-method">
                    <div class="method-header">
                        <span class="method-badge">POST</span>
                        <h3>Criar Transação PIX</h3>
                    </div>

                    <div class="endpoint-url">
                        https://api.uranopay.com/v1/gateway/
                    </div>

                    <div class="parameters">
                        <h4>Parâmetros da Requisição</h4>
                        <table class="parameter-table">
                            <thead>
                                <tr>
                                    <th>Parâmetro</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">amount <span class="required-badge">Obrigatório</span></td>
                                    <td>Integer</td>
                                    <td>Valor em reais</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">name <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Nome do cliente</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">document <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>CPF / CNPJ do cliente (apenas números)</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">email <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Email do cliente</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">callbackUrl <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Endpoint para receber o status do pagamento</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">api-key <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Token único de transação.</td>
                                </tr>
                                <tr>
                                    <td style="width: 30%;min-width:30%;">split <span class="optional-badge">Opcional</span></td>
                                    <td>Object</td>
                                    <td>Caso haja necessidade faz a divisão de recebimento, enviando a porcentagem para o usuario informado</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="code-tabs">
                        <button class="code-tab active" data-language="curl">cURL</button>
                        <button class="code-tab" data-language="php">PHP</button>
                        <button class="code-tab" data-language="python">Python</button>
                        <button class="code-tab" data-language="javascript">JavaScript</button>
                    </div>

                    <div class="code-examples">
                        <div class="code-block active" data-language="curl">
                            <div class="code-header">
                                <span class="code-language">cURL</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
curl -X POST https://api.uranopay.com/v1/gateway/ \
-H "Content-Type: application/json" \
-d '{
    "amount": 500,
    "client": {
        "name": "Fulano de Tal",
        "document": "123456789",
        "telefone": "11999999999",
        "email": "fulanodetal@email.com"
    },
    "split": {
        "email": "fulano", //Utilize Seu Nome De Usuario!        
        "percentage": 50
        
    },
    "callbackUrl": "https://exemplo/URANOPAY/callback", //Utilize para enviar seu webhook
    "api-key": "fdas4f65sd-4f56ads4f-f465asd4f"
}'</code></pre>
                        </div>

                        <div class="code-block" data-language="php">
                            <div class="code-header">
                                <span class="code-language">PHP</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
$data = [
    "amount"=> 500,
    "client"=> [
        "name"=> "Fulano de Tal",
        "document"=> "123456789",
        "telefone"=> "11999999999",
        "email"=> "fulanodetal@email.com"
    ],
    "split"=> [
        "email"=> "fulano", //Utilize Seu Nome De Usuario!        
        "percentage"=> 50
        
    ],
    "callbackUrl"=> "https://exemplo/URANOPAY/callback", //Utilize para enviar seu webhook
    "api-key"=> "fdas4f65sd-4f56ads4f-f465asd4f"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.uranopay.com/v1/gateway/");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);</code></pre>
                        </div>

                        <div class="code-block" data-language="python">
                            <div class="code-header">
                                <span class="code-language">Python</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
import requests
import json

url = "https://api.uranopay.com/v1/gateway/"
headers = {
    'Content-Type': 'application/json'
}
data = {
    "amount": 500,
    "client": {
        "name": "Fulano de Tal",
        "document": "123456789",
        "telefone": "11999999999",
        "email": "fulanodetal@email.com"
    },
    "split": {
        "email": "fulano", //Utilize Seu Nome De Usuario!        
        "percentage": 50
        
    },
    "callbackUrl": "https://exemplo/URANOPAY/callback", //Utilize para enviar seu webhook
    "api-key": "fdas4f65sd-4f56ads4f-f465asd4f"
}

response = requests.post(url, headers=headers, json=data)
result = response.json()</code></pre>
                        </div>

                        <div class="code-block" data-language="javascript">
                            <div class="code-header">
                                <span class="code-language">JavaScript</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code>
const data = {
    "amount": 500,
    "client": {
        "name": "Fulano de Tal",
        "document": "123456789",
        "telefone": "11999999999",
        "email": "fulanodetal@email.com"
    },
    "split": {
        "email": "fulano", //Utilize Seu Nome De Usuario!        
        "percentage": 50
        
    },
    "callbackUrl": "https://exemplo/URANOPAY/callback", //Utilize para enviar seu webhook
    "api-key": "fdas4f65sd-4f56ads4f-f465asd4f"
};

fetch('https://api.uranopay.com/v1/gateway/', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => console.log(result));</code></pre>
                        </div>
                    </div>

                    <div class="response-example">
                        <h4>Exemplo de Resposta</h4>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">JSON</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
{
    "status": "success",
    "message": "ok",
    "paymentCode": "00020101021226790014br.gov.bcb.pix255/v2/be1920df6b714e4e84edd77d7f25204000039865802BR592**63042CA1",
    "idTransaction": "52fc5262-4063-4900-933b-55e69850",
    "paymentCodeBase64": "iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6AQAAAACgl2eQAAACwElEQVR4Xu2XS5IjIQwF4SJw/1vMUeAiMJmi2y57IjpmMaXZGH+qDLl4IelJ5bJ/Xr/K+87b+gBnfYCzPsBZfweMUuoqpfTVGl91r9Knm4nA5D36Xn3uUQdY5epmJhA/Fyedr9IHMuvZTAXmnopDHRurtP4fgNVMUCuuvglbNsAbFlZQERo8cI22piU9dFaE8oCVMd2xTXdUiFNGHmlArQtNdWvWt1+gT6LNgHAL7SwOkImuRr8JHo9E/CwlGifvJp6Q2omQPNgktTTRzuVg8YwcSJwzGoHp04QSdUM4YfIFCDMs8ItJgt1DNpcQNsSoeKhTUTR12QlADHPqFalEaViM1FwzwV4tunhWW3DiLONXYxzP3BqxEihi/Ph3TVZCcBSm56xmYO0GsnSwGmAvZuzPkkaVYs8fPyWrNuBKFfaCG3MUzV688zm/YBPeYoM04waWp0umYBmDVHW67R+SuQrE0AXEeo2T2Ryt2K6PJOVACixEq0RMi3iYRvZD5EJgF3UyVQxcGSCWynmEo1seZtpm1dzJsAGJe9eJsgrmSvjhd33w5MI2UjnR51h+s+P/MATYN3BVX8Z7IygBpRIkfNcTo0Lum6uvt2wKaBJiITIns0ESOWClisyDqPOGBEimR5lAWMavOSIlYGLc5fknU7gGHCq9HMCNlQYDgoD1ihyOE+zVgkr3roSi5Pm8ybxSgRc9q+4MFOV/DLcE4BxikTbVP+H2sot2UeyEoBTG+wstVHA54ZPIhDaCt4hV8dBoTsbmOEZg7X0TPw329nAiL7lvUYySnJ5wHamEqvwSsgdPvBIZAGRGTSW+vVvmHg1e0ki8NP6AGd9gLM+wFn/APgNfNIphReCMrQAAAAASUVORK5CYII="
}
</code></pre>
                        </div>
                    </div>
                </div>


                <!-- Get Transaction Status -->
                <div id="webhook-transaction" class="api-method">
                    <div class="method-header">
                        <!-- <span class="method-badge">POST</span> -->
                        <h3 class="code-language">Webhook</h3>
                    </div>
                    <div class="endpoint-url">
                        https://exemplo/URANOPAY/callback
                    </div>
                    <div class="endpoint-url">
                        Sua aplicação deverá retornar a Resposta: 200.

                        Caso não houver resposta o webhook será re-enviado 5 vezes com intervalo de 15 segundos!.

                        O webhook irá retornar idTransiction e status. Exemplo:
                    </div>

                    <div class="response-example">
                        <h4>Exemplo de Resposta</h4>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">JSON</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
{
    "status": "paid",
    "idTransaction": "52fc5262-4063-4900-933b-55e69850",
}
</code></pre>
                        </div>
                    </div>

                </div>


                <div id="get-transaction" class="api-method">
                    <div class="method-header">
                        <span class="method-badge">POST</span>
                        <h3>Consultar Transação</h3>
                    </div>

                    <div class="endpoint-url">
                        https://api.uranopay.com/v1/webhook/
                    </div>

                    <div class="parameters">
                        <h4>Parâmetros da Requisição</h4>
                        <table class="parameter-table">
                            <thead>
                                <tr>
                                    <th>Parâmetro</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">idTransaction <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Id da transação</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">api-key <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Token de autorização</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="code-tabs">
                        <button class="code-tab active" data-language="curl">cURL</button>
                        <button class="code-tab" data-language="php">PHP</button>
                        <button class="code-tab" data-language="python">Python</button>
                        <button class="code-tab" data-language="javascript">JavaScript</button>
                    </div>

                    <div class="code-examples">
                        <div class="code-block active" data-language="curl">
                            <div class="code-header">
                                <span class="code-language">cURL</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
        curl -X POST https://api.uranopay.com/v1/webhook/ \
        -H "Content-Type: application/json" \
        -d '{
            "idTransaction": '4f6sdf4648-654fs4d8f9-f564sdfs-54sd
            "api-key": "fdas4f65sd-4f56ads4f-f465asd4f"
        }'</code></pre>
                        </div>

                        <div class="code-block" data-language="php">
                            <div class="code-header">
                                <span class="code-language">PHP</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
        $data =[
            "idTransaction" => '4f6sdf4648-654fs4d8f9-f564sdfs-54sd
            "api-key" => "fdas4f65sd-4f56ads4f-f465asd4f"
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.uranopay.com/v1/webhook/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);</code></pre>
                        </div>

                        <div class="code-block" data-language="python">
                            <div class="code-header">
                                <span class="code-language">Python</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
        import requests
        import json
        
        url = "https://api.uranopay.com/v1/webhook/"
        headers = {
            'Content-Type': 'application/json'
        }
        data = {
            "idTransaction": '4f6sdf4648-654fs4d8f9-f564sdfs-54sd
            "api-key": "fdas4f65sd-4f56ads4f-f465asd4f"
        }
        
        response = requests.post(url, headers=headers, json=data)
        result = response.json()</code></pre>
                        </div>

                        <div class="code-block" data-language="javascript">
                            <div class="code-header">
                                <span class="code-language">JavaScript</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code>
        const data = {
            "idTransaction": '4f6sdf4648-654fs4d8f9-f564sdfs-54sd
            "api-key": "fdas4f65sd-4f56ads4f-f465asd4f"
        };
        
        fetch('https://api.uranopay.com/v1/webhook/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => console.log(result));</code></pre>
                        </div>
                    </div>

                    <div class="response-example">
                        <h4>Exemplo de Resposta</h4>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">JSON</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
{
    "status": "WAITING_FOR_APPROVAL" 
    // ou
    "status": "PAID_OUT"
}
        </code></pre>
                        </div>
                    </div>
                </div>
                <div id="pay-transaction" class="api-method">
                    <div class="method-header">
                        <span class="method-badge">POST</span>
                        <h3>Realizar um pagamento</h3>
                    </div>

                    <div class="endpoint-url">
                        https://api.uranopay.com/c1/cashout/
                    </div>

                    <div class="parameters">
                        <h4>Parâmetros da Requisição</h4>
                        <table class="parameter-table">
                            <thead>
                                <tr>
                                    <th>Parâmetro</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">name <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Nome do recebedor</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">cpf <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>CPF do recebedor </td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">keypix <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Chave PIX do recebedor</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">amount <span class="required-badge">Obrigatório</span></td>
                                    <td>Float | Decimal | Int</td>
                                    <td>Valor a ser pago</td>
                                </tr>
                                <tr>
                                    <td style="min-width: 25%;max-width:30%;">api-key <span class="required-badge">Obrigatório</span></td>
                                    <td>String</td>
                                    <td>Token de autorização</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="code-tabs">
                        <button class="code-tab active" data-language="curl">cURL</button>
                        <button class="code-tab" data-language="php">PHP</button>
                        <button class="code-tab" data-language="python">Python</button>
                        <button class="code-tab" data-language="javascript">JavaScript</button>
                    </div>

                    <div class="code-examples">
                        <div class="code-block active" data-language="curl">
                            <div class="code-header">
                                <span class="code-language">cURL</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
curl -X POST https://api.uranopay.com/c1/cashout/ \
-H "Content-Type: application/json" \
-d '{
    "api-key": "81bb141a-1746-49a8-basdasdas4a-c3b8dasdasdasaa0d2259" ,
    "name": "fulano de tal",
    "cpf": "123456789012" ,
    "keypix": "123456789012",
    "amount": 350.00
}'</code></pre>
                        </div>

                        <div class="code-block" data-language="php">
                            <div class="code-header">
                                <span class="code-language">PHP</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
        $data [
            "api-key" => "81bb141a-1746-49a8-basdasdas4a-c3b8dasdasdasaa0d2259" ,
            "name" => "fulano de tal",
            "cpf" => "123456789012" ,
            "keypix" => "123456789012",
            "amount" => 350.00
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.uranopay.com/c1/cashout/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);</code></pre>
                        </div>

                        <div class="code-block" data-language="python">
                            <div class="code-header">
                                <span class="code-language">Python</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
        import requests
        import json
        
        url = "https://api.uranopay.com/c1/cashout/"
        headers = {
            'Content-Type': 'application/json'
        }
        data = {
            "api-key": "81bb141a-1746-49a8-basdasdas4a-c3b8dasdasdasaa0d2259" ,
            "name": "fulano de tal",
            "cpf": "123456789012" ,
            "keypix": "123456789012",
            "amount": 350.00
        }
        
        response = requests.post(url, headers=headers, json=data)
        result = response.json()</code></pre>
                        </div>

                        <div class="code-block" data-language="javascript">
                            <div class="code-header">
                                <span class="code-language">JavaScript</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code>
        const data = {
            "api-key": "81bb141a-1746-49a8-basdasdas4a-c3b8dasdasdasaa0d2259" ,
            "name": "fulano de tal",
            "cpf": "123456789012" ,
            "keypix": "123456789012",
            "amount": 350.00
        };
        
        fetch('https://api.URANOPAY.com/c1/cashout/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => console.log(result));</code></pre>
                        </div>
                    </div>

                    <div class="response-example">
                        <h4>Exemplo de Resposta</h4>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">JSON</span>
                                <button class="copy-button">Copiar</button>
                            </div>
                            <pre><code >
httpsStatus: 200
        </code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Erros -->
            <section id="erros" class="section">
                <div class="section-header">
                    <div class="section-badge">ERROS</div>
                    <h2 class="section-title">Códigos de Erro</h2>
                    <p class="section-description">Lista de possíveis códigos de erro retornados pela API.</p>
                </div>

                <div class="error-grid">
                    <div class="error-card">
                        <div class="error-code">400</div>
                        <h4>Bad Request</h4>
                        <p>Requisição inválida ou mal formatada</p>
                    </div>
                    <div class="error-card">
                        <div class="error-code">401</div>
                        <h4>Unauthorized</h4>
                        <p>Autenticação falhou ou token inválido</p>
                    </div>
                    <div class="error-card">
                        <div class="error-code">404</div>
                        <h4>Not Found</h4>
                        <p>Recurso não encontrado</p>
                    </div>
                    <div class="error-card">
                        <div class="error-code">500</div>
                        <h4>Server Error</h4>
                        <p>Erro interno do servidor</p>
                    </div>
                </div>

                <div class="error-example">
                    <h4>Exemplo de Resposta de Erro</h4>
                    <div class="code-block">
                        <div class="code-header">
                            <span class="code-language">JSON</span>
                            <button class="copy-button">Copiar</button>
                        </div>
                        <pre><code >
{
    "error": "Descrição detalhada do erro"
}</code></pre>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="./document/js.js"></script>
</body>

</html>