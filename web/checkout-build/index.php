<?php
session_start();

// 1. Authentication & Integrity Checks
if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit;
}

include '../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$email = $_SESSION['email'];

$sql = "SELECT user_id, nome, status, permission, transacoes_aproved, cliente_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved, $cliente_id);
$stmt->fetch();
$stmt->close();

if ($status == 0 || $status == 5) {
    header("Location: ../home");
    exit;
}
$_SESSION['user_id'] = $user_id;
$_SESSION['cliente_id'] = $cliente_id;

// 2. Fetch User's Checkout Products
$sql_products = "SELECT id, name_produto, valor, referencia, logo_produto, obrigado_page, ativo, url_checkout FROM checkout_build WHERE email = ?";
$stmt = $conn->prepare($sql_products);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$products_data = [];
while ($row = $result->fetch_assoc()) {
    $products_data[] = $row;
}
$stmt->close();
// $conn->close();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';

require_once __DIR__ . '/../app/Services/PlanService.php';
$planServiceLoc = new \App\Services\PlanService($conn, $_SESSION['user_id'] ?? 0);

ob_start();
?>

<!-- Add Product Modal (5 Tabs) -->
<div id="addModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="toggleModal('addModal')"></div>
    <div class="flex flex-col items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-surface-dark rounded-2xl w-full max-w-4xl border border-slate-200 dark:border-border-dark shadow-2xl relative z-10 flex flex-col max-h-[90vh]">
            
            <!-- Header -->
            <div class="p-6 border-b border-slate-200 dark:border-border-dark flex justify-between items-center bg-slate-50/50 dark:bg-black/20 rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-bold dark:text-white flex items-center gap-2">
                        <span class="material-icons-round text-primary">add_shopping_cart</span>
                        Novo Checkout
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure todas as propriedades do seu novo produto.</p>
                </div>
                <button onclick="toggleModal('addModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors bg-white dark:bg-surface-dark p-2 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form id="checkoutForm" method="POST" action="insert_checkout.php" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" name="cliente_id" value="<?php echo $_SESSION['cliente_id']; ?>">
                
                <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
                    
                    <!-- Sidebar Tabs Navigation -->
                    <div class="w-full md:w-64 border-b md:border-b-0 md:border-r border-slate-200 dark:border-border-dark bg-slate-50/30 dark:bg-black/10 overflow-x-auto md:overflow-y-auto shrink-0 hide-scrollbar">
                        <div class="flex md:flex-col gap-1 p-4 min-w-max md:min-w-0" id="cmd-tabs">
                            <button type="button" onclick="openTab('tab-produto')" class="cmd-tab-btn active flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-primary bg-primary/10">
                                <span class="material-icons-round text-[20px]">inventory_2</span> Produto
                            </button>
                            <button type="button" onclick="openTab('tab-pagamento')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">payments</span> Pagamento
                            </button>
                            <button type="button" onclick="openTab('tab-visual')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">palette</span> Visual & Design
                            </button>
                            <button type="button" onclick="openTab('tab-eventos')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">webhook</span> Eventos e Pixels
                            </button>
                            <button type="button" onclick="openTab('tab-seguranca')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">security</span> Segurança
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable Content Area -->
                    <div class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar relative bg-white dark:bg-surface-dark">
                        
                        <!-- TAB 1: Produto -->
                        <div id="tab-produto" class="cmd-tab-content block animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Informações do Produto</h4>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nome do Produto <span class="text-red-500">*</span></label>
                                    <input type="text" name="produto_name" placeholder="Ex: E-book Dominando PHP" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Descrição Curta</label>
                                    <textarea name="descricao" rows="3" placeholder="Aparecerá no topo do checkout..." class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white resize-none"></textarea>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Valor (R$) <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">R$</span>
                                            <input type="text" name="valor_checkout" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl pl-12 pr-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white font-semibold" required oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">SKU / Ref. Interna</label>
                                        <input type="text" name="sku_interno" placeholder="Opcional" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-100 dark:border-white/5">
                                    <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-black/20 cursor-pointer hover:border-primary transition-colors">
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-white text-sm">Permitir Parcelamento</p>
                                            <p class="text-xs text-slate-500 mt-1">Exibe opção de cartão de crédito</p>
                                        </div>
                                        <div class="relative">
                                            <input type="checkbox" name="permitir_parcelamento" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        </div>
                                    </label>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Estoque / Limite Vendas</label>
                                        <input type="number" name="quantidade_max" placeholder="Deixe vazio para ilimitado" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Pagamento -->
                        <div id="tab-pagamento" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Configurações de Pagamento</h4>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Provedor de Pagamento <span class="text-red-500">*</span></label>
                                    <select name="user_provider_id" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                                        <option value="" disabled selected>Selecione a conta de destino...</option>
                                        <?php
// Fetch Providers dynamically since we are inside index.php
$sql_prov = "SELECT id, provider_name, client_id FROM user_providers WHERE user_id = ?";
$stmt_prov = $conn->prepare($sql_prov);
$stmt_prov->bind_param("s", $_SESSION['user_id']);
$stmt_prov->execute();
$res_prov = $stmt_prov->get_result();
while ($prov = $res_prov->fetch_assoc()) {
    $disp = ucfirst($prov['provider_name']);
    echo "<option value='{$prov['id']}'>{$disp} (Cód: {$prov['id']})</option>";
}
$stmt_prov->close();
?>
                                    </select>
                                    <p class="text-xs text-slate-400 mt-2"><span class="material-icons-round text-xs align-middle">info</span> O dinheiro das vendas cairá na conta deste provedor.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Tempo Expiração PIX (minutos)</label>
                                        <input type="number" name="pix_expiracao" value="30" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Taxa Extra Fixa (R$)</label>
                                        <input type="text" name="taxa_extra" placeholder="0,00" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                                        <p class="text-[10px] text-slate-400 mt-1">Acrescido no frete ou taxas.</p>
                                    </div>
                                </div>

                                <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-black/20 cursor-pointer hover:border-primary transition-colors">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white text-sm">Habilitar Cupons de Desconto</p>
                                        <p class="text-xs text-slate-500 mt-1">Mostrar o campo 'Possui cupom?' no checkout</p>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" name="permitir_cupom" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- TAB 3: Visual -->
                        <div id="tab-visual" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Personalização Visual</h4>
                            
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Imagem do Produto (Opcional)</label>
                                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-4 text-center hover:bg-slate-50 dark:hover:bg-white/5 transition-colors cursor-pointer relative overflow-hidden group">
                                            <input type="file" name="formFile" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <span class="material-icons-round text-3xl text-slate-400 group-hover:text-primary transition-colors mb-2">image</span>
                                            <p class="text-xs text-slate-500 font-medium">Clique para fazer upload</p>
                                            <p class="text-[10px] text-slate-400 mt-1">Máx: 2MB (PNG, JPG)</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Banner Topo (Opcional)</label>
                                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-4 text-center hover:bg-slate-50 dark:hover:bg-white/5 transition-colors cursor-pointer relative overflow-hidden group">
                                            <input type="file" name="bannerFile" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <span class="material-icons-round text-3xl text-slate-400 group-hover:text-primary transition-colors mb-2">panorama</span>
                                            <p class="text-xs text-slate-500 font-medium">1200x400 recomendado</p>
                                            <p class="text-[10px] text-slate-400 mt-1">Aparece no topo (Celular e PC)</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-4 border-t border-slate-100 dark:border-white/5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Cor Principal (Bordas/Ícones)</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="cor_principal" value="#a855f7" class="w-12 h-12 rounded cursor-pointer border-0 bg-transparent p-0">
                                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">#a855f7</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Cor Botão "Comprar"</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="cor_botao" value="#7c3aed" class="w-12 h-12 rounded cursor-pointer border-0 bg-transparent p-0">
                                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">#7c3aed</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Texto do Botão Principal</label>
                                    <input type="text" name="texto_botao" value="Comprar Agora" placeholder="Ex: Pagar R$ 50,00" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                </div>
                                
                                <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-black/20 cursor-pointer hover:border-primary transition-colors">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white text-sm">Exibir Resumo do Pedido</p>
                                        <p class="text-xs text-slate-500 mt-1">Mostra os produtos da cesta e cálculo de taxa lateral</p>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" name="mostrar_resumo" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- TAB 4: Eventos -->
                        <div id="tab-eventos" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Pós-venda e Tracking</h4>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Página de Obrigado (Redirecionamento) <span class="text-red-500">*</span></label>
                                    <input type="url" name="obrigado_page" placeholder="https://seudominio.com/obrigado" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                                    <p class="text-[10px] text-slate-400 mt-1">Para onde enviar o cliente após o pagamento aprovado.</p>
                                </div>
                                
                                <div class="p-4 border border-blue-500/20 bg-blue-500/5 rounded-xl">
                                    <label class="block text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-2 flex items-center gap-1"><span class="material-icons-round text-sm">webhook</span> Webhook de Vendas (URL)</label>
                                    <input type="url" name="webhook_url" placeholder="https://api.system.com/webhook" class="w-full bg-white dark:bg-black/40 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-blue-500 transition-all text-slate-900 dark:text-white">
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Dispara POST JSON a cada status (criado, aprovado, recusado).</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pixel do Facebook (Meta)</label>
                                        <input type="text" name="pixel_meta" placeholder="ID (ex: 1234567890)" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Google Analytics / Ads</label>
                                        <input type="text" name="pixel_google" placeholder="ID (ex: G-XXXX / AW-XXXX)" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: Segurança -->
                        <div id="tab-seguranca" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Controle e Segurança</h4>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Status do Checkout</label>
                                    <div class="flex gap-4">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="status" value="1" class="peer sr-only" checked>
                                            <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-500 transition-all">
                                                Ativo (Aberto)
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="status" value="0" class="peer sr-only">
                                            <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-500 transition-all">
                                                Inativo (Pausado)
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <label class="flex justify-between items-center p-4 rounded-xl border border-orange-500/30 bg-orange-500/5 cursor-pointer hover:border-orange-500/60 transition-colors">
                                    <div>
                                        <p class="font-bold text-orange-600 dark:text-orange-400 text-sm flex items-center gap-1"><span class="material-icons-round text-sm">science</span> Modo de Teste / Sandbox</p>
                                        <p class="text-xs text-orange-500/70 mt-1">Registra vendas mas não cobra real do cliente.</p>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" name="modo_teste" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer (Sticky Output) -->
                <div class="p-6 border-t border-slate-200 dark:border-border-dark flex gap-3 bg-slate-50 dark:bg-background-dark shrink-0 rounded-b-2xl">
                    <button type="button" onclick="toggleModal('addModal')" class="flex-1 max-w-[150px] py-3.5 rounded-xl border border-slate-200 dark:border-zinc-700 font-bold hover:bg-slate-100 dark:hover:bg-white/5 transition-colors text-slate-600 dark:text-zinc-300">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white font-bold py-3.5 rounded-xl transition-all purple-glow hover:scale-[1.02] shadow-xl text-lg flex justify-center items-center gap-2">
                        <span class="material-icons-round">check_circle</span> Criar Checkout Profissional
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* CSS para o modal de tabs */
.animate-fade-in { animation: fadeIn 0.3s ease forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
// Logic to switch tabs inside the modal
function openTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.cmd-tab-content').forEach(el => el.classList.add('hidden'));
    
    // Remove active styles from buttons
    document.querySelectorAll('.cmd-tab-btn').forEach(btn => {
        btn.classList.remove('active', 'text-primary', 'bg-primary/10');
        btn.classList.add('text-slate-600', 'dark:text-slate-400');
        // If dark mode hover classes were removed, re-add them
        btn.classList.add('hover:bg-slate-100', 'dark:hover:bg-white/5');
    });

    // Show selected content
    document.getElementById(tabId).classList.remove('hidden');
    
    // Set active styles to clicked button (find the one triggering the click)
    let activeBtn = document.querySelector(`.cmd-tab-btn[onclick="openTab('${tabId}')"]`);
    if(activeBtn) {
        activeBtn.classList.remove('text-slate-600', 'dark:text-slate-400', 'hover:bg-slate-100', 'dark:hover:bg-white/5');
        activeBtn.classList.add('active', 'text-primary', 'bg-primary/10');
    }
}
</script>

<!-- Edit Product Modal -->
<!-- Add Product Modal (5 Tabs) -->
<div id="editModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="toggleModal('editModal')"></div>
    <div class="flex flex-col items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-surface-dark rounded-2xl w-full max-w-4xl border border-slate-200 dark:border-border-dark shadow-2xl relative z-10 flex flex-col max-h-[90vh]">
            
            <!-- Header -->
            <div class="p-6 border-b border-slate-200 dark:border-border-dark flex justify-between items-center bg-slate-50/50 dark:bg-black/20 rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-bold dark:text-white flex items-center gap-2">
                        <span class="material-icons-round text-primary">add_shopping_cart</span>
                        Editar Checkout
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure todas as propriedades do seu novo produto.</p>
                </div>
                <button onclick="toggleModal('editModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors bg-white dark:bg-surface-dark p-2 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form id="editForm" method="POST" action="update_checkout.php" enctype="multipart/form-data" method="POST" action="update_checkout.php" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" id="edit_modal_id" name="id">

                <input type="hidden" name="cliente_id" value="<?php echo $_SESSION['cliente_id']; ?>">
                
                <div class="flex flex-col md:flex-row flex-1 overflow-hidden">
                    
                    <!-- Sidebar Tabs Navigation -->
                    <div class="w-full md:w-64 border-b md:border-b-0 md:border-r border-slate-200 dark:border-border-dark bg-slate-50/30 dark:bg-black/10 overflow-x-auto md:overflow-y-auto shrink-0 hide-scrollbar">
                        <div class="flex md:flex-col gap-1 p-4 min-w-max md:min-w-0" id="cmd-tabs">
                            <button type="button" onclick="openTab('tab-produto-edit')" class="cmd-tab-btn active flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-primary bg-primary/10">
                                <span class="material-icons-round text-[20px]">inventory_2</span> Produto
                            </button>
                            <button type="button" onclick="openTab('tab-pagamento-edit')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">payments</span> Pagamento
                            </button>
                            <button type="button" onclick="openTab('tab-visual-edit')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">palette</span> Visual & Design
                            </button>
                            <button type="button" onclick="openTab('tab-eventos-edit')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">webhook</span> Eventos e Pixels
                            </button>
                            <button type="button" onclick="openTab('tab-seguranca-edit')" class="cmd-tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                <span class="material-icons-round text-[20px]">security</span> Segurança
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable Content Area -->
                    <div class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar relative bg-white dark:bg-surface-dark">
                        
                        <!-- TAB 1: Produto -->
                        <div id="tab-produto-edit" class="cmd-tab-content block animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Informações do Produto</h4>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nome do Produto <span class="text-red-500">*</span></label>
                                    <input type="text" name="produto_name" placeholder="Ex: E-book Dominando PHP" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Descrição Curta</label>
                                    <textarea name="descricao" rows="3" placeholder="Aparecerá no topo do checkout..." class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white resize-none"></textarea>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Valor (R$) <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">R$</span>
                                            <input type="text" name="valor_checkout" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl pl-12 pr-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white font-semibold" required oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">SKU / Ref. Interna</label>
                                        <input type="text" name="sku_interno" placeholder="Opcional" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-100 dark:border-white/5">
                                    <?php
$splitLocked = !$planServiceLoc->hasFeature('allow_split_parcelamento'); // Re-using $planServiceLoc from earlier
$splitLockAttr = $splitLocked ? 'disabled title="Disponível apenas no plano PRO"' : '';
$splitLockIcon = $splitLocked ? '<span class="material-icons-round text-xs text-amber-500 ml-1 block" title="Upgrade para PRO">lock</span>' : '';
$splitBgLocked = $splitLocked ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-black/60' : 'bg-slate-50 dark:bg-black/20';
?>
                                    <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark <?php echo $splitBgLocked; ?> <?php echo !$splitLocked ? 'cursor-pointer hover:border-primary transition-colors' : ''; ?>" <?php echo $splitLockAttr; ?>>
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-white text-sm flex items-center">Permitir Parcelamento <?php echo $splitLockIcon; ?></p>
                                            <p class="text-xs text-slate-500 mt-1">Exibe opção de cartão de crédito</p>
                                        </div>
                                        <div class="relative">
                                            <input type="checkbox" name="permitir_parcelamento" class="sr-only peer" <?php echo $splitLockAttr; ?>>
                                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        </div>
                                    </label>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Estoque / Limite Vendas</label>
                                        <input type="number" name="quantidade_max" placeholder="Deixe vazio para ilimitado" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Pagamento -->
                        <div id="tab-pagamento-edit" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Configurações de Pagamento</h4>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Provedor de Pagamento <span class="text-red-500">*</span></label>
                                    <select name="user_provider_id" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                                        <option value="" disabled selected>Selecione a conta de destino...</option>
                                        <?php
// Fetch Providers dynamically since we are inside index.php
$sql_prov = "SELECT id, provider_name, client_id FROM user_providers WHERE user_id = ?";
$stmt_prov = $conn->prepare($sql_prov);
$stmt_prov->bind_param("s", $_SESSION['user_id']);
$stmt_prov->execute();
$res_prov = $stmt_prov->get_result();
while ($prov = $res_prov->fetch_assoc()) {
    $disp = ucfirst($prov['provider_name']);
    echo "<option value='{$prov['id']}'>{$disp} (Cód: {$prov['id']})</option>";
}
$stmt_prov->close();
?>
                                    </select>
                                    <p class="text-xs text-slate-400 mt-2"><span class="material-icons-round text-xs align-middle">info</span> O dinheiro das vendas cairá na conta deste provedor.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Tempo Expiração PIX (minutos)</label>
                                        <input type="number" name="pix_expiracao" value="30" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Taxa Extra Fixa (R$)</label>
                                        <input type="text" name="taxa_extra" placeholder="0,00" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                                        <p class="text-[10px] text-slate-400 mt-1">Acrescido no frete ou taxas.</p>
                                    </div>
                                </div>

                                <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-black/20 cursor-pointer hover:border-primary transition-colors">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white text-sm">Habilitar Cupons de Desconto</p>
                                        <p class="text-xs text-slate-500 mt-1">Mostrar o campo 'Possui cupom?' no checkout</p>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" name="permitir_cupom" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- TAB 3: Visual -->
                        <div id="tab-visual-edit" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Personalização Visual</h4>
                            
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Imagem do Produto (Opcional)</label>
                                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-4 text-center hover:bg-slate-50 dark:hover:bg-white/5 transition-colors cursor-pointer relative overflow-hidden group">
                                            <input type="file" name="formFile" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <span class="material-icons-round text-3xl text-slate-400 group-hover:text-primary transition-colors mb-2">image</span>
                                            <p class="text-xs text-slate-500 font-medium">Clique para fazer upload</p>
                                            <p class="text-[10px] text-slate-400 mt-1">Máx: 2MB (PNG, JPG)</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Banner Topo (Opcional)</label>
                                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-4 text-center hover:bg-slate-50 dark:hover:bg-white/5 transition-colors cursor-pointer relative overflow-hidden group">
                                            <input type="file" name="bannerFile" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <span class="material-icons-round text-3xl text-slate-400 group-hover:text-primary transition-colors mb-2">panorama</span>
                                            <p class="text-xs text-slate-500 font-medium">1200x400 recomendado</p>
                                            <p class="text-[10px] text-slate-400 mt-1">Aparece no topo (Celular e PC)</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-4 border-t border-slate-100 dark:border-white/5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Cor Principal (Bordas/Ícones)</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="cor_principal" value="#a855f7" class="w-12 h-12 rounded cursor-pointer border-0 bg-transparent p-0">
                                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">#a855f7</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Cor Botão "Comprar"</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="cor_botao" value="#7c3aed" class="w-12 h-12 rounded cursor-pointer border-0 bg-transparent p-0">
                                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">#7c3aed</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Texto do Botão Principal</label>
                                    <input type="text" name="texto_botao" value="Comprar Agora" placeholder="Ex: Pagar R$ 50,00" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                                </div>
                                
                                <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-black/20 cursor-pointer hover:border-primary transition-colors">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white text-sm">Exibir Resumo do Pedido</p>
                                        <p class="text-xs text-slate-500 mt-1">Mostra os produtos da cesta e cálculo de taxa lateral</p>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" name="mostrar_resumo" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- TAB 4: Eventos -->
                        <div id="tab-eventos-edit" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Pós-venda e Tracking</h4>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Página de Obrigado (Redirecionamento) <span class="text-red-500">*</span></label>
                                    <input type="url" name="obrigado_page" placeholder="https://seudominio.com/obrigado" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                                    <p class="text-[10px] text-slate-400 mt-1">Para onde enviar o cliente após o pagamento aprovado.</p>
                                </div>
                                
                                <?php
require_once __DIR__ . '/../app/Services/PlanService.php';
$planServiceLoc = new \App\Services\PlanService($conn, $_SESSION['user_id'] ?? 0);

$pixelsLocked = !$planServiceLoc->hasFeature('allow_advanced_pixels');
$lockAttr = $pixelsLocked ? 'disabled title="Disponível apenas no plano PRO"' : '';
$lockIcon = $pixelsLocked ? '<span class="material-icons-round text-xs text-amber-500 ml-1 block" title="Upgrade para PRO">lock</span>' : '';
$bgLocked = $pixelsLocked ? 'bg-slate-100 dark:bg-black/60 opacity-60 cursor-not-allowed' : 'bg-white dark:bg-black/40';
$bgLockedLight = $pixelsLocked ? 'bg-slate-100 dark:bg-black/60 opacity-60 cursor-not-allowed' : 'bg-slate-50 dark:bg-black/20';
?>
                                <div class="p-4 border border-blue-500/20 bg-blue-500/5 rounded-xl">
                                    <label class="block text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-2 flex items-center gap-1"><span class="material-icons-round text-sm">webhook</span> Webhook de Vendas (URL)</label>
                                    <input type="url" name="webhook_url" placeholder="https://api.system.com/webhook" class="w-full <?php echo $bgLocked; ?> border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-blue-500 transition-all text-slate-900 dark:text-white" <?php echo $lockAttr; ?>>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Dispara POST JSON a cada status (criado, aprovado, recusado).</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 flex items-center">Pixel do Facebook (Meta) <?php echo $lockIcon; ?></label>
                                        <input type="text" name="pixel_meta" placeholder="ID (ex: 1234567890)" class="w-full <?php echo $bgLockedLight; ?> border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" <?php echo $lockAttr; ?>>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 flex items-center">Google Analytics / Ads <?php echo $lockIcon; ?></label>
                                        <input type="text" name="pixel_google" placeholder="ID (ex: G-XXXX / AW-XXXX)" class="w-full <?php echo $bgLockedLight; ?> border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" <?php echo $lockAttr; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: Segurança -->
                        <div id="tab-seguranca-edit" class="cmd-tab-content hidden animate-fade-in">
                            <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2 border-b border-slate-100 dark:border-white/5 pb-3">Controle e Segurança</h4>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Status do Checkout</label>
                                    <div class="flex gap-4">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="status" value="1" class="peer sr-only" checked>
                                            <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-500 transition-all">
                                                Ativo (Aberto)
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="status" value="0" class="peer sr-only">
                                            <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-500 transition-all">
                                                Inativo (Pausado)
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <label class="flex justify-between items-center p-4 rounded-xl border border-orange-500/30 bg-orange-500/5 cursor-pointer hover:border-orange-500/60 transition-colors">
                                    <div>
                                        <p class="font-bold text-orange-600 dark:text-orange-400 text-sm flex items-center gap-1"><span class="material-icons-round text-sm">science</span> Modo de Teste / Sandbox</p>
                                        <p class="text-xs text-orange-500/70 mt-1">Registra vendas mas não cobra real do cliente.</p>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" name="modo_teste" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer (Sticky Output) -->
                <div class="p-6 border-t border-slate-200 dark:border-border-dark flex gap-3 bg-slate-50 dark:bg-background-dark shrink-0 rounded-b-2xl">
                    <button type="button" onclick="toggleModal('editModal')" class="flex-1 max-w-[150px] py-3.5 rounded-xl border border-slate-200 dark:border-zinc-700 font-bold hover:bg-slate-100 dark:hover:bg-white/5 transition-colors text-slate-600 dark:text-zinc-300">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white font-bold py-3.5 rounded-xl transition-all purple-glow hover:scale-[1.02] shadow-xl text-lg flex justify-center items-center gap-2">
                        <span class="material-icons-round">check_circle</span> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* CSS para o modal de tabs */
.animate-fade-in { animation: fadeIn 0.3s ease forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
// Logic to switch tabs inside the modal
function openTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.cmd-tab-content').forEach(el => el.classList.add('hidden'));
    
    // Remove active styles from buttons
    document.querySelectorAll('.cmd-tab-btn').forEach(btn => {
        btn.classList.remove('active', 'text-primary', 'bg-primary/10');
        btn.classList.add('text-slate-600', 'dark:text-slate-400');
        // If dark mode hover classes were removed, re-add them
        btn.classList.add('hover:bg-slate-100', 'dark:hover:bg-white/5');
    });

    // Show selected content
    document.getElementById(tabId).classList.remove('hidden');
    
    // Set active styles to clicked button (find the one triggering the click)
    let activeBtn = document.querySelector(`.cmd-tab-btn[onclick="openTab('${tabId}')"]`);
    if(activeBtn) {
        activeBtn.classList.remove('text-slate-600', 'dark:text-slate-400', 'hover:bg-slate-100', 'dark:hover:bg-white/5');
        activeBtn.classList.add('active', 'text-primary', 'bg-primary/10');
    }
}
</script>


<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark overflow-hidden flex flex-col h-full min-h-[500px]">
    <div class="px-6 py-5 border-b border-slate-200 dark:border-border-dark flex items-center justify-between flex-wrap gap-4">
        <h2 class="text-sm font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Produtos</h2>
        <div class="flex items-center gap-2">
            <button onclick="toggleModal('addModal')" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-all purple-glow hover:scale-105 active:scale-95">
                <span class="material-icons-round text-lg">add_circle_outline</span>
                Novo Checkout
            </button>
            <div class="relative hidden sm:block">
                <span class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input class="bg-slate-50 dark:bg-background-dark border-slate-200 dark:border-border-dark rounded-lg pl-9 pr-4 py-1.5 text-sm focus:ring-primary focus:border-primary transition-all w-48" placeholder="Buscar..." type="text">
            </div>
            <button class="p-2 border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                <span class="material-icons-round text-slate-500 dark:text-slate-400 text-sm">filter_list</span>
            </button>
        </div>
    </div>
    
    <div class="flex-1 overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50/50 dark:bg-black/20 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Produto</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Status</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Valor</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Link Checkout</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-800/50">
                <?php if (count($products_data) > 0): ?>
                    <?php foreach ($products_data as $row): ?>
                        <?php
        $statusBadge = $row['ativo'] ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-white/5 dark:text-slate-400 dark:border-white/10';
        $statusText = $row['ativo'] ? 'Ativo' : 'Inativo';
        $url_checkout_stored = $row['url_checkout'];

        // Extract ID from stored URL
        $parsed_url = parse_url($url_checkout_stored);
        $query_params = [];
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query_params);
        }
        $checkout_id_val = isset($query_params['id']) ? $query_params['id'] : '';

        // Reconstruct valid URL dynamically
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://');
        $current_path = dirname($_SERVER['SCRIPT_NAME']); // /uranoPAY/web/checkout-build
        $base_project_path = dirname($current_path); // /uranoPAY/web

        // If we found an ID, rebuild the URL. Otherwise fallback to stored (legacy behavior)
        if ($checkout_id_val) {
            $url_checkout = $protocol . $_SERVER['HTTP_HOST'] . $base_project_path . "/checkout/v1/?id=" . $checkout_id_val;
        }
        else {
            $url_checkout = $url_checkout_stored;
        }

        $url_checkout_v2 = str_replace('v1', 'v2', $url_checkout);
?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-black/40 p-1 border border-slate-200 dark:border-border-dark">
                                        <img src="<?php echo htmlspecialchars($row['logo_produto']); ?>" alt="Logo" class="w-full h-full object-contain rounded">
                                    </div>
                                    <span class="font-medium text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($row['name_produto']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $statusBadge; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-300">
                                R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a href="<?php echo htmlspecialchars($url_checkout); ?>" target="_blank" class="px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary text-xs font-semibold rounded-lg transition-colors border border-primary/20 flex items-center gap-1">
                                        DIGITAL <span class="material-icons-round text-[10px]">open_in_new</span>
                                    </a>
                                    <a href="<?php echo htmlspecialchars($url_checkout_v2); ?>" target="_blank" class="px-3 py-1.5 bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg transition-colors border border-slate-200 dark:border-white/10 flex items-center gap-1">
                                        FÍSICO <span class="material-icons-round text-[10px]">open_in_new</span>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors btn-edit" 
                                            data-json='<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>'>
                                        <span class="material-icons-round text-sm">edit</span>
                                    </button>
                                    <a href="deletar_produto.php?id=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                        <span class="material-icons-round text-sm">delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php
    endforeach; ?>
                <?php
else: ?>
                    <tr class="h-64">
                        <td class="text-center px-6 py-20" colspan="5">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 bg-slate-100 dark:bg-background-dark rounded-full flex items-center justify-center">
                                    <span class="material-icons-round text-slate-300 dark:text-slate-600 text-4xl">inventory_2</span>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-slate-900 dark:text-slate-100 font-medium">Nenhum produto encontrado</p>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm">Comece criando seu primeiro checkout para ver os produtos aqui.</p>
                                </div>
                                <button onclick="toggleModal('addModal')" class="mt-2 text-primary text-sm font-semibold hover:underline flex items-center gap-1">
                                    Criar novo agora <span class="material-icons-round text-sm">arrow_forward</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php
endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }

    // Edit Modal Logic
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.btn-edit');
        const editForm = document.getElementById('editForm');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const data = JSON.parse(this.getAttribute('data-json'));
                
                // Set the hidden ID
                document.getElementById('edit_modal_id').value = data.id || '';
                
                // Map of form input names to database column names
                const dbFieldMap = {
                    'produto_name': 'name_produto',
                    'descricao': 'descricao',
                    'valor_checkout': 'valor',
                    'sku_interno': 'sku_interno',
                    'permitir_parcelamento': 'permitir_parcelamento',
                    'quantidade_max': 'quantidade_max',
                    'user_provider_id': 'user_provider_id',
                    'pix_expiracao': 'pix_expiracao',
                    'permitir_cupom': 'permitir_cupom',
                    'taxa_extra': 'taxa_extra',
                    'cor_principal': 'cor_principal',
                    'cor_botao': 'cor_botao',
                    'texto_botao': 'texto_botao',
                    'mostrar_resumo': 'mostrar_resumo',
                    'obrigado_page': 'obrigado_page',
                    'webhook_url': 'webhook_url',
                    'pixel_meta': 'pixel_meta',
                    'pixel_google': 'pixel_google',
                    'status': 'ativo', 
                    'modo_teste': 'modo_teste'
                };
                
                Object.keys(dbFieldMap).forEach(inputName => {
                    const dbField = dbFieldMap[inputName];
                    const val = data[dbField];
                    
                    const elList = editForm.querySelectorAll(`[name="${inputName}"]`);
                    if (elList.length === 0) return;
                    
                    const el = elList[0];
                    if (el.type === 'checkbox') {
                        el.checked = (val == 1);
                    } else if (el.type === 'radio') {
                        // Find the radio with the matching value
                        elList.forEach(radio => {
                            if (radio.value == val) {
                                radio.checked = true;
                            }
                        });
                    } else {
                        // For numbers, convert to comma format if it's the value field
                        if (inputName === 'valor_checkout' && val !== null) {
                            el.value = parseFloat(val).toFixed(2).replace('.', ',');
                        } else if (inputName === 'taxa_extra' && val !== null) {
                            el.value = parseFloat(val).toFixed(2).replace('.', ',');
                        } else {
                            el.value = val !== null ? val : '';
                        }
                    }
                });
                
                // Open first tab explicitly
                openTab('tab-produto-edit');
                toggleModal('editModal');
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include '../layouts/base_new.php';
?>
