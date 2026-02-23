<?php
session_start();
if (!isset($_SESSION['email'])) { header("Location: ../"); exit; }
include '../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) die("Erro: " . $conn->connect_error);

// Garante que a tabela domains existe com suporte a system_subdomain
$conn->query("ALTER TABLE domains MODIFY COLUMN type ENUM('custom','subdomain','system_subdomain') NOT NULL DEFAULT 'custom'");
$conn->query("ALTER TABLE domains ADD COLUMN IF NOT EXISTS prefix VARCHAR(32) NULL");
$conn->query("ALTER TABLE domains ADD COLUMN IF NOT EXISTS slug VARCHAR(128) NULL");

$email = $_SESSION['email'];
$su = $conn->prepare("SELECT user_id, nome, status, permission FROM users WHERE email=?");
$su->bind_param("s", $email); $su->execute();
$su->bind_result($user_id, $nome, $u_status, $permission);
$su->fetch(); $su->close();

// === Prefixos e palavras reservadas ===
$ALLOWED_PREFIXES = ['pay', 'checkout', 'pagar', 'pix'];
$RESERVED_SLUGS   = ['admin', 'api', 'painel', 'suporte', 'root', 'www', 'mail', 'ftp',
                      'smtp', 'help', 'status', 'ns', 'ns1', 'ns2', 'uranopay', 'urano',
                      'sistema', 'system', 'portal', 'dashboard', 'app'];

$message = ""; $messageType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // === CRIAR SUBDOMINIO SISTEMA ===
    if ($action === 'add_system') {
        $prefix = strtolower(trim($_POST['prefix'] ?? ''));
        $slug   = strtolower(trim($_POST['slug']   ?? ''));

        // Validar prefixo
        if (!in_array($prefix, $ALLOWED_PREFIXES)) {
            $message = "Prefixo inválido."; $messageType = "error";
        }
        // Validar slug: só letras, números e hífen
        elseif (!preg_match('/^[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]$/', $slug)) {
            $message = "Slug inválido. Use apenas letras minúsculas, números e hífen (mín. 3 caracteres, não comece/termine com hífen).";
            $messageType = "error";
        }
        // Bloquear palavras reservadas
        elseif (in_array($slug, $RESERVED_SLUGS)) {
            $message = "Este nome é reservado pelo sistema. Escolha outro.";
            $messageType = "error";
        }
        else {
            $full_domain = $prefix . '.' . $slug . '.amyxcheckout.com.br';

            // Verificar duplicidade pelo full_domain
            $chk = $conn->prepare("SELECT id FROM domains WHERE domain=?");
            $chk->bind_param("s", $full_domain); $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) {
                $message = "Este domínio já está em uso. Tente outro slug.";
                $messageType = "error";
            } else {
                $type = 'system_subdomain'; $status = 'active'; $ssl = 'active';
                $ins = $conn->prepare("INSERT INTO domains (user_id, domain, type, status, ssl_status, prefix, slug) VALUES (?,?,?,?,?,?,?)");
                $ins->bind_param("sssssss", $user_id, $full_domain, $type, $status, $ssl, $prefix, $slug);
                if ($ins->execute()) {
                    $message = "Checkout criado! Seu endereço: " . $full_domain;
                    $messageType = "success";
                } else {
                    $message = "Erro: " . $conn->error; $messageType = "error";
                }
                $ins->close();
            }
            $chk->close();
        }
    }

    // === CONECTAR DOMINIO PROPRIO ===
    elseif ($action === 'add_custom') {
        $raw = trim($_POST['domain_name'] ?? '');
        if (empty($raw)) { $message = "Preencha o campo."; $messageType = "error"; }
        else {
            $raw = strtolower($raw);
            $raw = preg_replace('#^https?://#', '', $raw);
            $raw = explode('/', $raw)[0];
            if (strpos($raw, 'www.') === 0) $raw = substr($raw, 4);
            $raw = filter_var($raw, FILTER_SANITIZE_URL);

            $chk = $conn->prepare("SELECT id FROM domains WHERE domain=?");
            $chk->bind_param("s", $raw); $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) { $message = "Domínio já cadastrado."; $messageType = "error"; }
            else {
                $type = 'custom'; $status = 'active'; $ssl = 'pending';
                $ins = $conn->prepare("INSERT INTO domains (user_id, domain, type, status, ssl_status) VALUES (?,?,?,?,?)");
                $ins->bind_param("sssss", $user_id, $raw, $type, $status, $ssl);
                if ($ins->execute()) { $message = "Domínio conectado! Status SSL: Gerando..."; $messageType = "success"; }
                else { $message = "Erro: " . $conn->error; $messageType = "error"; }
                $ins->close();
            }
            $chk->close();
        }
    }

    // === DELETAR ===
    elseif ($action === 'delete') {
        $did = (int)($_POST['domain_id'] ?? 0);
        $del = $conn->prepare("DELETE FROM domains WHERE id=? AND user_id=?");
        $del->bind_param("is", $did, $user_id);
        if ($del->execute() && $del->affected_rows > 0) { $message = "Domínio removido."; $messageType = "success"; }
        $del->close();
    }
}

$domains = [];
$sq = $conn->prepare("SELECT id, domain, type, status, ssl_status, prefix, slug FROM domains WHERE user_id=? ORDER BY id DESC");
$sq->bind_param("s", $user_id); $sq->execute(); $dr = $sq->get_result();
while ($row = $dr->fetch_assoc()) $domains[] = $row;
$sq->close();

ob_start();
?>
<style>
.pg-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}
@media(max-width:900px){.pg-grid{grid-template-columns:1fr}}
.pg-card{background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;padding:1.5rem;display:flex;flex-direction:column;gap:.95rem}
.pg-card h3{font-size:1.05rem;font-weight:700;color:#f1f5f9;margin:0}
.pg-card .sub-desc{font-size:.78rem;color:#9ca3af;margin:0}
.dm-fld{margin-bottom:.85rem}
.dm-fld label{display:block;font-size:.7rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px}
.dm-row{display:flex;gap:.6rem}
.dm-row .dm-fld{flex:1;margin-bottom:0}
.dm-input,.dm-select{width:100%;box-sizing:border-box;background:#0a0a0a;border:1px solid #222;border-radius:9px;padding:.72rem 1rem;color:#e2e8f0;font-size:.85rem;outline:none;transition:border-color .15s;-webkit-appearance:none;appearance:none}
.dm-input:focus,.dm-select:focus{border-color:#a855f7}
.dm-input::placeholder{color:#374151}
.dm-select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .9rem center;padding-right:2.2rem;cursor:pointer}
.dm-select option{background:#1a1a1a;color:#e2e8f0}
/* Preview */
.dm-preview{background:rgba(168,85,247,.06);border:1px solid rgba(168,85,247,.2);border-radius:9px;padding:.65rem 1rem;font-size:.8rem;color:#9ca3af;display:flex;align-items:center;gap:.4rem;min-height:38px;transition:all .2s}
.dm-preview b{color:#c084fc;font-weight:600;word-break:break-all}
.dm-preview .empty{color:#374151;font-style:italic}
/* Btn */
.btn-dm{border:none;border-radius:9px;padding:.75rem 1.2rem;font-size:.83rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:all .15s;margin-top:.3rem}
.btn-dm.purple{background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff}
.btn-dm.purple:hover{background:linear-gradient(135deg,#9333ea,#6d28d9);box-shadow:0 4px 15px rgba(168,85,247,.3)}
.btn-dm.green{background:#10b981;color:#fff}.btn-dm.green:hover{background:#059669}
/* Badge instant */
.instant-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:.68rem;font-weight:600;background:rgba(16,185,129,.1);color:#10b981;border:1px solid rgba(16,185,129,.2);margin-left:.4rem}
/* Table */
.dm-tw{margin-top:2rem;background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;overflow:hidden}
.dm-t{width:100%;border-collapse:collapse;text-align:left}
.dm-t th{background:rgba(255,255,255,.02);padding:.85rem 1.2rem;font-size:.71rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #1e1e1e}
.dm-t td{padding:1rem 1.2rem;font-size:.83rem;color:#e2e8f0;border-bottom:1px solid #1e1e1e;vertical-align:middle}
.dm-t tr:last-child td{border-bottom:none}
.bj{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:.69rem;font-weight:600}
.bj.ok{background:rgba(16,185,129,.1);color:#10b981;border:1px solid rgba(16,185,129,.2)}
.bj.pd{background:rgba(245,158,11,.1);color:#f59e0b;border:1px solid rgba(245,158,11,.2)}
.bj.fl{background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.2)}
.type-pill{font-size:.68rem;padding:2px 8px;border-radius:20px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.type-pill.sys{background:rgba(168,85,247,.1);color:#c084fc;border:1px solid rgba(168,85,247,.2)}
.type-pill.cst{background:rgba(14,165,233,.1);color:#38bdf8;border:1px solid rgba(14,165,233,.2)}
.btn-dl{background:rgba(255,255,255,.04);border:1px solid #2a2a2a;color:#9ca3af;padding:5px 9px;border-radius:6px;cursor:pointer;transition:all .15s;line-height:1}
.btn-dl:hover{background:rgba(239,68,68,.1);color:#ef4444;border-color:rgba(239,68,68,.3)}
/* Toast */
.dm-ts{position:fixed;top:1.2rem;right:1.2rem;z-index:99999;padding:.75rem 1.4rem;border-radius:10px;font-size:.84rem;font-weight:600;display:flex;align-items:center;gap:.5rem;box-shadow:0 4px 24px rgba(0,0,0,.5);max-width:420px}
.dm-ts.ok{background:#10b981;color:#fff}.dm-ts.er{background:#ef4444;color:#fff}
</style>

<?php if (!empty($message)): ?>
<div class="dm-ts <?php echo $messageType=='success'?'ok':'er'; ?>" id="dm-ts">
  <span class="material-icons-round" style="font-size:1rem"><?php echo $messageType=='success'?'check_circle':'error_outline'; ?></span>
  <?php echo htmlspecialchars($message); ?>
</div>
<script>setTimeout(function(){var t=document.getElementById('dm-ts');if(t)t.remove();},5000);</script>
<?php endif; ?>

<div style="margin-bottom:1.8rem">
  <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin:0 0 4px">Domínios</h1>
  <p style="color:#9ca3af;font-size:.83rem;margin:0">Crie seu checkout gratuito ou conecte um domínio próprio.</p>
</div>

<div class="pg-grid">
  <!-- CARD: Subdomínio do Sistema -->
  <div class="pg-card">
    <div>
      <h3 style="display:flex;align-items:center">
        Checkout Gratuito
        <span class="instant-badge"><span class="material-icons-round" style="font-size:.8rem">bolt</span> Instantâneo</span>
      </h3>
      <p class="sub-desc" style="margin-top:4px">Escolha um prefixo e o nome da sua loja para gerar seu endereço de checkout.</p>
    </div>
    <form method="POST" id="sysForm">
      <input type="hidden" name="action" value="add_system">
      <div class="dm-row">
        <div class="dm-fld">
          <label>Prefixo</label>
          <select name="prefix" class="dm-select" id="inp-prefix" onchange="updatePreview()" required>
            <option value="">Selecione...</option>
            <?php foreach(['pay','checkout','pagar','pix'] as $pf): ?>
            <option value="<?=$pf?>"><?=$pf?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="dm-fld" style="flex:1.6">
          <label>Nome da loja (slug)</label>
          <input type="text" name="slug" id="inp-slug" class="dm-input" placeholder="minhaloja"
                 pattern="[a-z0-9][a-z0-9\-]{1,48}[a-z0-9]"
                 oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9\-]/g,'');updatePreview()"
                 required autocomplete="off">
        </div>
      </div>
      <div class="dm-fld">
        <label>Seu endereço gerado</label>
        <div class="dm-preview" id="dm-preview">
          <span class="material-icons-round" style="font-size:.9rem;opacity:.4">link</span>
          <span class="empty" id="preview-text">Preencha os campos acima...</span>
        </div>
      </div>
      <button type="submit" class="btn-dm purple">
        <span class="material-icons-round" style="font-size:1rem">rocket_launch</span>
        Criar Checkout
      </button>
    </form>
  </div>

  <!-- CARD: Domínio Próprio -->
  <div class="pg-card">
    <div>
      <h3 style="display:flex;align-items:center;gap:6px">
        Domínio Próprio
        <span class="material-icons-round" style="font-size:.95rem;color:#f59e0b">star</span>
      </h3>
      <p class="sub-desc" style="margin-top:4px">Use seu <b>.com</b> ou <b>.com.br</b>. Aponte o DNS Tipo A para o IP abaixo.</p>
      <div style="margin-top:9px;padding:7px 12px;background:rgba(255,255,255,.02);border:1px dashed #333;border-radius:8px;font-size:.74rem;color:#9ca3af">
        IP do servidor: <b style="color:#fff;user-select:all;cursor:pointer" onclick="navigator.clipboard.writeText(this.innerText)">72.60.244.72</b>
        <span style="font-size:.65rem;color:#6b7280;margin-left:4px">(clique para copiar)</span>
      </div>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_custom">
      <div class="dm-fld">
        <label>Seu domínio</label>
        <input type="text" name="domain_name" class="dm-input" placeholder="lojatop.com.br" required autocomplete="off">
      </div>
      <button type="submit" class="btn-dm green">
        <span class="material-icons-round" style="font-size:1rem">link</span>
        Conectar Domínio
      </button>
    </form>
  </div>
</div>

<!-- TABELA DE DOMÍNIOS -->
<div class="dm-tw">
<?php if (count($domains) === 0): ?>
  <div style="padding:3rem;text-align:center;color:#6b7280">
    <span class="material-icons-round" style="font-size:2.5rem;opacity:.15;display:block;margin-bottom:8px">language</span>
    Nenhum domínio conectado. Crie seu primeiro checkout acima!
  </div>
<?php else: ?>
<table class="dm-t">
  <thead>
    <tr>
      <th>Domínio</th>
      <th>Tipo</th>
      <th>DNS</th>
      <th>SSL</th>
      <th style="text-align:right">Ação</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($domains as $dm):
    $isSys = $dm['type'] === 'system_subdomain';
    $isSub = $dm['type'] === 'subdomain';
    $fqdn  = ($isSys || $isSub) ? $dm['domain'] : $dm['domain'];
    // system_subdomain already stores full FQDN; legacy subdomain needs suffix
    if ($isSub) $fqdn = $dm['domain'] . '.amyxcheckout.com.br';
  ?>
  <tr>
    <td>
      <a href="https://<?=htmlspecialchars($fqdn)?>" target="_blank"
         style="color:#60a5fa;text-decoration:none;display:flex;align-items:center;gap:4px;font-weight:600">
        <?=htmlspecialchars($fqdn)?>
        <span class="material-icons-round" style="font-size:.78rem">open_in_new</span>
      </a>
    </td>
    <td>
      <?php if($isSys): ?>
        <span class="type-pill sys">Sistema</span>
      <?php elseif($isSub): ?>
        <span class="type-pill sys">Subdomínio</span>
      <?php else: ?>
        <span class="type-pill cst">Customizado</span>
      <?php endif; ?>
    </td>
    <td>
      <?=$dm['status']==='active'
        ? '<div class="bj ok"><span class="material-icons-round" style="font-size:.82rem">check_circle</span> Ativo</div>'
        : '<div class="bj pd"><span class="material-icons-round" style="font-size:.82rem">hourglass_empty</span> Pendente</div>'?>
    </td>
    <td>
      <?php
        if($dm['ssl_status']==='active') echo '<div class="bj ok"><span class="material-icons-round" style="font-size:.82rem">lock</span> Seguro</div>';
        elseif($dm['ssl_status']==='pending') echo '<div class="bj pd"><span class="material-icons-round" style="font-size:.82rem">sync</span> Gerando</div>';
        else echo '<div class="bj fl"><span class="material-icons-round" style="font-size:.82rem">error_outline</span> Sem SSL</div>';
      ?>
    </td>
    <td style="text-align:right">
      <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="domain_id" value="<?=$dm['id']?>">
        <button type="submit" class="btn-dl" title="Remover" onclick="return confirm('Desvincular '+<?=json_encode($fqdn)?>+'?')">
          <span class="material-icons-round" style="font-size:1rem;vertical-align:middle">delete_outline</span>
        </button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>

<script>
function updatePreview() {
  var prefix = document.getElementById('inp-prefix').value;
  var slug   = document.getElementById('inp-slug').value.trim();
  var el     = document.getElementById('preview-text');
  if (prefix && slug.length >= 3) {
    el.className = '';
    el.innerHTML = '<b>' + prefix + '.' + slug + '.amyxcheckout.com.br</b>';
  } else {
    el.className = 'empty';
    el.textContent = 'Preencha os campos acima...';
  }
}
</script>

<?php
$content = ob_get_clean();
include '../layouts/base_new.php';
$conn->close();
?>