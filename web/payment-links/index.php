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

$sql = "SELECT user_id, nome, status, permission FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission);
$stmt->fetch();
$stmt->close();

if ($status == 0 || $status == 5) {
    header("Location: ../home");
    exit;
}
$_SESSION['user_id'] = $user_id;

// 2. Fetch User's Payment Links
$sql_links = "SELECT pl.*, p.provider_name FROM payment_links pl LEFT JOIN user_providers p ON pl.provider_id = p.id WHERE pl.user_id = ? ORDER BY pl.id DESC";
$stmt = $conn->prepare($sql_links);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$links_data = [];
while ($row = $result->fetch_assoc()) {
    $links_data[] = $row;
}
$stmt->close();

// Fetch Providers for Modal
$sql_prov = "SELECT id, provider_name FROM user_providers WHERE user_id = ?";
$stmt_prov = $conn->prepare($sql_prov);
$stmt_prov->bind_param("i", $user_id);
$stmt_prov->execute();
$res_prov = $stmt_prov->get_result();
$providers = [];
while ($p = $res_prov->fetch_assoc()) {
    $providers[] = $p;
}
$stmt_prov->close();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';

ob_start();
?>

<!-- Add Link Modal -->
<div id="addModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="toggleModal('addModal')"></div>
    <div class="flex flex-col items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-surface-dark rounded-2xl w-full max-w-2xl border border-slate-200 dark:border-border-dark shadow-2xl relative z-10 flex flex-col max-h-[90vh]">
            
            <div class="p-6 border-b border-slate-200 dark:border-border-dark flex justify-between items-center bg-slate-50/50 dark:bg-black/20 rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-bold dark:text-white flex items-center gap-2">
                        <span class="material-icons-round text-primary">link</span>
                        Novo Link de Pagamento
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Crie um link direto para cobranças rápidas.</p>
                </div>
                <button onclick="toggleModal('addModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors bg-white dark:bg-surface-dark p-2 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            <form id="addLinkForm" method="POST" action="insert_link.php" class="flex flex-col flex-1 overflow-hidden">
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar relative bg-white dark:bg-surface-dark space-y-5">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nome do Link <span class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="Ex: Consultoria Premium" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Descrição (Opcional)</label>
                        <textarea name="description" rows="2" placeholder="Descreva o serviço para o cliente..." class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-100 dark:border-white/5 pb-2 border-b">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Valor Base (R$) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">R$</span>
                                <input type="text" name="amount" id="amount-input" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl pl-12 pr-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white font-semibold" required oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                            </div>
                        </div>

                        <?php
// The page already loaded $conn and session. 
// We should include PlanService if it hasn't been included.
require_once __DIR__ . '/../app/Services/PlanService.php';
$planServiceLink = new \App\Services\PlanService($conn, $_SESSION['user_id'] ?? 0);
$editableLocked = !$planServiceLink->hasFeature('allow_editable_amount');
$editLockAttr = $editableLocked ? 'disabled title="Disponível apenas no plano PRO"' : '';
$editLockIcon = $editableLocked ? '<span class="material-icons-round text-xs text-amber-500 ml-1 block" title="Upgrade para PRO">lock</span>' : '';
$editBgLocked = $editableLocked ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-black/60' : 'bg-slate-50 dark:bg-black/20';
?>
                        <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark <?php echo $editBgLocked; ?> <?php echo !$editableLocked ? 'cursor-pointer hover:border-primary transition-colors' : ''; ?>" <?php echo $editLockAttr; ?>>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm flex items-center">Valor Editável? <?php echo $editLockIcon; ?></p>
                                <p class="text-xs text-slate-500 mt-1">O cliente digita o valor final.</p>
                            </div>
                            <div class="relative">
                                <input type="checkbox" name="editable_amount" id="editable_amount_toggle" value="1" class="sr-only peer" <?php echo $editLockAttr; ?>>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Provedor de Pagamento <span class="text-red-500">*</span></label>
                        <select name="provider_id" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                            <option value="" disabled selected>Selecione a conta de destino...</option>
                            <?php foreach ($providers as $prov): ?>
                                <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars(ucfirst($prov['provider_name'])) . " (Cód: " . $prov['id'] . ")"; ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Expiração (Opcional)</label>
                            <input type="datetime-local" name="expires_at" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white [color-scheme:light] dark:[color-scheme:dark]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Limite de Pagamentos</label>
                            <input type="number" name="max_payments" placeholder="Deixe vazio para ilimitado" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Página de Obrigado (Opcional)</label>
                        <input type="url" name="thank_you_url" placeholder="https://eudominio.com/obrigado" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Status do Link</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="1" class="peer sr-only" checked>
                                <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-500 transition-all">
                                    Ativo
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="0" class="peer sr-only">
                                <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-500 transition-all">
                                    Inativo
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <div class="p-6 border-t border-slate-200 dark:border-border-dark flex gap-3 bg-slate-50 dark:bg-background-dark shrink-0 rounded-b-2xl">
                    <button type="button" onclick="toggleModal('addModal')" class="flex-1 py-3.5 rounded-xl border border-slate-200 dark:border-zinc-700 font-bold hover:bg-slate-100 dark:hover:bg-white/5 transition-colors text-slate-600 dark:text-zinc-300">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white font-bold py-3.5 rounded-xl transition-all purple-glow hover:scale-[1.02] shadow-xl text-lg flex justify-center items-center gap-2">
                        <span class="material-icons-round">check_circle</span> Criar Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark overflow-hidden flex flex-col h-full min-h-[500px]">
    <div class="px-6 py-5 border-b border-slate-200 dark:border-border-dark flex items-center justify-between flex-wrap gap-4">
        <h2 class="text-sm font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Links de Pagamento</h2>
        <div class="flex items-center gap-2">
            <button onclick="toggleModal('addModal')" class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-all purple-glow hover:scale-105 active:scale-95">
                <span class="material-icons-round text-lg">add_circle_outline</span>
                Novo Link
            </button>
        </div>
    </div>
    
    <div class="flex-1 overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50/50 dark:bg-black/20 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Nome do Link</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Provedor</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Valor</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark">Status</th>
                    <th class="px-6 py-4 border-b border-slate-200 dark:border-border-dark text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-gray-800/50">
                <?php if (count($links_data) > 0): ?>
                    <?php foreach ($links_data as $row): ?>
                        <?php
        $statusBadge = $row['status'] ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-white/5 dark:text-slate-400 dark:border-white/10';
        $statusText = $row['status'] ? 'Ativo' : 'Inativo';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $baseUrl = $protocol . $_SERVER['HTTP_HOST'];
        $full_link = $baseUrl . "/pay/" . htmlspecialchars($row['slug']);
        $amt = $row['editable_amount'] ? 'A Definir' : 'R$ ' . number_format($row['amount'], 2, ',', '.');
        $json_data = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-700 dark:text-slate-200 text-sm"><?php echo htmlspecialchars($row['name']); ?></span>
                                <div class="text-[10px] text-slate-400 font-mono mt-1 break-all truncate max-w-[200px]" title="<?php echo $full_link; ?>">
                                    /pay/<?php echo htmlspecialchars($row['slug']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <?php echo htmlspecialchars(ucfirst(empty($row['provider_name']) ? 'N/A' : $row['provider_name'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-600 dark:text-slate-300">
                                <?php echo $amt; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $statusBadge; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button class="p-2 bg-slate-100 dark:bg-white/5 hover:bg-primary/10 text-slate-600 dark:text-slate-300 hover:text-primary rounded-lg transition-colors border border-slate-200 dark:border-white/10 btn-edit" title="Editar" data-json="<?php echo $json_data; ?>">
                                        <span class="material-icons-round text-sm">edit</span>
                                    </button>
                                    <button class="p-2 bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 rounded-lg transition-colors border border-slate-200 dark:border-white/10" title="Copiar Link" onclick="copyLink('<?php echo $full_link; ?>')">
                                        <span class="material-icons-round text-sm">content_copy</span>
                                    </button>
                                    <a href="delete_link.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este link?')" class="p-2 bg-slate-100 dark:bg-white/5 hover:bg-red-500/10 text-slate-600 dark:text-slate-300 hover:text-red-500 rounded-lg transition-colors border border-slate-200 dark:border-white/10" title="Excluir">
                                        <span class="material-icons-round text-sm">delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php
    endforeach; ?>
                <?php
else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <span class="material-icons-round text-4xl opacity-50">link_off</span>
                                <p class="font-medium">Nenhum Link de Pagamento encontrado.</p>
                                <p class="text-sm">Clique em "Novo Link" para começar a receber.</p>
                            </div>
                        </td>
                    </tr>
                <?php
endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Link Modal -->
<div id="editModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="toggleModal('editModal')"></div>
    <div class="flex flex-col items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-surface-dark rounded-2xl w-full max-w-2xl border border-slate-200 dark:border-border-dark shadow-2xl relative z-10 flex flex-col max-h-[90vh]">
            
            <div class="p-6 border-b border-slate-200 dark:border-border-dark flex justify-between items-center bg-slate-50/50 dark:bg-black/20 rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-bold dark:text-white flex items-center gap-2">
                        <span class="material-icons-round text-primary">edit</span>
                        Editar Link
                    </h3>
                </div>
                <button onclick="toggleModal('editModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors bg-white dark:bg-surface-dark p-2 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            <form id="editLinkForm" method="POST" action="update_link.php" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" name="id" id="edit_id">
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar relative bg-white dark:bg-surface-dark space-y-5">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nome do Link <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Descrição (Opcional)</label>
                        <textarea name="description" id="edit_description" rows="2" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-100 dark:border-white/5 pb-2 border-b">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Valor Base (R$) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">R$</span>
                                <input type="text" name="amount" id="edit_amount" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl pl-12 pr-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white font-semibold" required oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                            </div>
                        </div>

                        <label class="flex justify-between items-center p-4 rounded-xl border border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-black/20 cursor-pointer hover:border-primary transition-colors">
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white text-sm">Valor Editável?</p>
                                <p class="text-xs text-slate-500 mt-1">O cliente digita o valor final.</p>
                            </div>
                            <div class="relative">
                                <input type="checkbox" name="editable_amount" id="edit_editable_amount" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Provedor de Pagamento <span class="text-red-500">*</span></label>
                        <select name="provider_id" id="edit_provider_id" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white" required>
                            <option value="" disabled selected>Selecione a conta de destino...</option>
                            <?php foreach ($providers as $prov): ?>
                                <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars(ucfirst($prov['provider_name'])) . " (Cód: " . $prov['id'] . ")"; ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Expiração (Opcional)</label>
                            <input type="datetime-local" name="expires_at" id="edit_expires_at" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white [color-scheme:light] dark:[color-scheme:dark]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Limite de Pagamentos</label>
                            <input type="number" name="max_payments" id="edit_max_payments" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Página de Obrigado (Opcional)</label>
                        <input type="url" name="thank_you_url" id="edit_thank_you_url" class="w-full bg-slate-50 dark:bg-black/20 border border-slate-200 dark:border-border-dark rounded-xl px-4 py-3 outline-none focus:border-primary transition-all text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Status do Link</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="1" id="edit_status_1" class="peer sr-only">
                                <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-500 transition-all">
                                    Ativo
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="0" id="edit_status_0" class="peer sr-only">
                                <div class="px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-border-dark text-center font-bold text-slate-500 peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-500 transition-all">
                                    Inativo
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <div class="p-6 border-t border-slate-200 dark:border-border-dark flex gap-3 bg-slate-50 dark:bg-background-dark shrink-0 rounded-b-2xl">
                    <button type="button" onclick="toggleModal('editModal')" class="flex-1 py-3.5 rounded-xl border border-slate-200 dark:border-zinc-700 font-bold hover:bg-slate-100 dark:hover:bg-white/5 transition-colors text-slate-600 dark:text-zinc-300">
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


<script>
function toggleModal(modalID){
    const modal = document.getElementById(modalID);
    if(modal.classList.contains('hidden')){
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert("Link copiado para a área de transferência!");
    }).catch(err => {
        console.error('Falha ao copiar:', err);
        prompt("Copie manualmente o link:", url);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-json'));
            
            document.getElementById('edit_id').value = data.id || '';
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_description').value = data.description || '';
            
            if (data.amount !== null) {
                document.getElementById('edit_amount').value = parseFloat(data.amount).toFixed(2).replace('.', ',');
            }
            
            document.getElementById('edit_editable_amount').checked = (data.editable_amount == 1);
            
            if (data.provider_id) {
                document.getElementById('edit_provider_id').value = data.provider_id;
            }
            
            if (data.expires_at) {
                // Convert MySQL datetime to datetime-local format
                document.getElementById('edit_expires_at').value = data.expires_at.replace(' ', 'T');
            } else {
                document.getElementById('edit_expires_at').value = '';
            }
            
            document.getElementById('edit_max_payments').value = data.max_payments !== null ? data.max_payments : '';
            document.getElementById('edit_thank_you_url').value = data.thank_you_url || '';
            
            if (data.status == 1) {
                document.getElementById('edit_status_1').checked = true;
            } else {
                document.getElementById('edit_status_0').checked = true;
            }
            
            toggleModal('editModal');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include '../layouts/base_new.php';
?>
