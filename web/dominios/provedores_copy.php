<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit;
}
include '../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error)
    die("Erro: " . $conn->connect_error);
$conn->query("CREATE TABLE IF NOT EXISTS user_providers (id INT AUTO_INCREMENT PRIMARY KEY, user_id VARCHAR(255) NOT NULL, provider_name VARCHAR(255) NOT NULL, api_key TEXT, api_token TEXT, client_id TEXT, client_secret TEXT, status TINYINT DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$email = $_SESSION['email'];
$su = $conn->prepare("SELECT user_id,nome,status,permission,transacoes_aproved,cliente_id FROM users WHERE email=?");
$su->bind_param("s", $email);
$su->execute();
$su->bind_result($user_id, $nome, $u_status, $permission, $transacoes_aproved, $cliente_id);
$su->fetch();
$su->close();
$message = "";
$messageType = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $pn = $_POST['provider_name'];
        $ak = $_POST['api_key'] ?? "";
        $at = $_POST['api_token'] ?? "";
        $ci = $_POST['client_id'] ?? "";
        $cs = $_POST['client_secret'] ?? "";
        $sc = $conn->prepare("SELECT id FROM user_providers WHERE user_id=? AND provider_name=?");
        $sc->bind_param("ss", $user_id, $pn);
        $sc->execute();
        $sc->store_result();
        if ($sc->num_rows > 0) {
            $up = $conn->prepare("UPDATE user_providers SET api_key=?,api_token=?,client_id=?,client_secret=?,status=1 WHERE user_id=? AND provider_name=?");
            $up->bind_param("ssssss", $ak, $at, $ci, $cs, $user_id, $pn);
            $up->execute();
            $up->close();
            $message = "Provedor atualizado!";
            $messageType = "success";
        }
        else {
            $sa = $conn->prepare("INSERT INTO user_providers (user_id,provider_name,api_key,api_token,client_id,client_secret) VALUES(?,?,?,?,?,?)");
            $sa->bind_param("ssssss", $user_id, $pn, $ak, $at, $ci, $cs);
            if ($sa->execute()) {
                $message = "Provedor conectado!";
                $messageType = "success";
            }
            else {
                $message = "Erro: " . $conn->error;
                $messageType = "error";
            }
            $sa->close();
        }
        $sc->close();
    }
    elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id_del = (int)$_POST['id'];
        $sd = $conn->prepare("DELETE FROM user_providers WHERE id=? AND user_id=?");
        $sd->bind_param("is", $id_del, $user_id);
        if ($sd->execute()) {
            $message = "Desconectado.";
            $messageType = "success";
        }
        $sd->close();
    }
}
$connected = array();
$s = $conn->prepare("SELECT id,provider_name FROM user_providers WHERE user_id=? AND status=1");
$s->bind_param("s", $user_id);
$s->execute();
$r = $s->get_result();
while ($row = $r->fetch_assoc()) {
    $connected[strtolower($row['provider_name'])] = $row['id'];
}
$s->close();

// Logo file mapping (relative to XAMPP root /uranoPAY/logos/)
// Returns HTML for the logo (img tag or SVG fallback)
function getProviderLogo($name, $size = 46, $radius = 11)
{
    $map = array(
        'Pushin Pay' => null, // no file — SVG fallback below
        'Mercado Pago' => 'mercadopago.svg',
        'Aoba Pay' => null, // no file — SVG fallback below
        'Atomo Pay' => 'atomo.png',
        'Aurea Pag' => 'aureapag.png',
        'Sigilo Pay' => 'sigilopay.jpg',
        'Asset Pay' => 'assetpay.png',
        'Sync Pay' => 'sync.png',
        'Velana' => 'velana.png',
        'Quantum Pay' => 'quantum.webp',
        'Pronttus' => 'pronttus.png',
        'Iron Pay' => 'ironpay.png',
    );
    $svgFallback = array(
        'Pushin Pay' => '<svg viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="46" height="46" rx="11" fill="#1B41F5"/><path d="M17 15C17 13.3431 18.3431 12 20 12H28C29.6569 12 31 13.3431 31 15V23C31 27.4183 27.4183 31 23 31C18.5817 31 15 27.4183 15 23V19C15 17.3431 16.3431 16 18 16" stroke="#5EEAD4" stroke-width="2.5" stroke-linecap="round"/><circle cx="23" cy="23" r="3" fill="#5EEAD4"/></svg>',
        'Aoba Pay' => '<svg viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="46" height="46" rx="11" fill="#1533E8"/><path d="M15 14L15 32L32 23Z" fill="#00E5A0"/></svg>',
    );
    $file = isset($map[$name]) ? $map[$name] : null;
    if ($file) {
        $url = '/uranoPAY/logos/' . $file;
        $style = "width:{$size}px;height:{$size}px;border-radius:{$radius}px;object-fit:contain;background:#111;display:block;";
        return '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($name) . '" style="' . $style . '" loading="lazy">';
    }
    // SVG fallback
    if (isset($svgFallback[$name]))
        return $svgFallback[$name];
    // Generic initials fallback
    $words = explode(' ', $name);
    $init = '';
    foreach ($words as $w) {
        $init .= strtoupper($w[0]);
    }
    return '<svg viewBox="0 0 46 46"><rect width="46" height="46" rx="11" fill="#1e1e1e"/><text x="23" y="29" font-family="Arial" font-weight="800" font-size="14" fill="#fff" text-anchor="middle">' . htmlspecialchars(substr($init, 0, 2)) . '</text></svg>';
}

$all_providers = array(
        array('name' => 'Pushin Pay', 'description' => 'Gateway de pagamentos', 'color' => '#1B41F5', 'fields' => array(array('key' => 'api_token', 'label' => 'Token da API', 'type' => 'password'))),
        array('name' => 'Mercado Pago', 'description' => 'Gateway de pagamentos', 'color' => '#009EE3', 'fields' => array(array('key' => 'api_token', 'label' => 'Access Token', 'type' => 'password'))),
        array('name' => 'Aoba Pay', 'description' => 'Gateway de pagamentos', 'color' => '#1533E8', 'fields' => array(array('key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password'))),
        array('name' => 'Atomo Pay', 'description' => 'Gateway de pagamentos', 'color' => '#F59E0B', 'fields' => array(array('key' => 'api_token', 'label' => 'Token da API', 'type' => 'password'))),
        array('name' => 'Aurea Pag', 'description' => 'Gateway de pagamentos', 'color' => '#EAB308', 'fields' => array(array('key' => 'api_token', 'label' => 'Token da API', 'type' => 'password'))),
        array('name' => 'Sigilo Pay', 'description' => 'Gateway de pagamentos', 'color' => '#10B981', 'fields' => array(array('key' => 'api_key', 'label' => 'Public Key', 'type' => 'text'), array('key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password'))),
        array('name' => 'Asset Pay', 'description' => 'Gateway de pagamentos', 'color' => '#6366F1', 'fields' => array(array('key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password'), array('key' => 'client_id', 'label' => 'Company ID', 'type' => 'text'))),
        array('name' => 'Sync Pay', 'description' => 'Gateway de pagamentos', 'color' => '#0EA5E9', 'fields' => array(array('key' => 'client_id', 'label' => 'Client ID', 'type' => 'text'), array('key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password'))),
        array('name' => 'Velana', 'description' => 'Gateway de pagamentos', 'color' => '#EC4899', 'fields' => array(array('key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password'))),
        array('name' => 'Quantum Pay', 'description' => 'Gateway de pagamentos', 'color' => '#7C3AED', 'fields' => array(array('key' => 'api_key', 'label' => 'Public Key', 'type' => 'text'), array('key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password'))),
        array('name' => 'Pronttus', 'description' => 'Gateway de pagamentos', 'color' => '#3B82F6', 'fields' => array(array('key' => 'client_id', 'label' => 'Client ID', 'type' => 'text'), array('key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password'))),
        array('name' => 'Iron Pay', 'description' => 'Gateway de pagamentos', 'color' => '#64748B', 'fields' => array(array('key' => 'api_token', 'label' => 'Token da API', 'type' => 'password'))),
);
ob_start();
?>
<style>
.pg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
@media(max-width:1100px){.pg-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.pg-grid{grid-template-columns:1fr}}
.pg-card{background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;padding:1.2rem 1.35rem 1rem;position:relative;display:flex;flex-direction:column;gap:.85rem;transition:border-color .2s,box-shadow .2s}
.pg-card:hover{border-color:#2a2a2a;box-shadow:0 4px 24px rgba(0,0,0,.5)}
.pg-top{display:flex;align-items:center;gap:.85rem}
.pg-logo{width:46px;height:46px;border-radius:11px;flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#111}
.pg-logo img{width:46px;height:46px;object-fit:contain;border-radius:11px}
.pg-logo svg{width:46px;height:46px}
.pg-name{font-size:.92rem;font-weight:700;color:#f1f5f9;margin:0;line-height:1.2}
.pg-desc{font-size:.72rem;color:#6b7280;margin:2px 0 0}
.pg-dot{position:absolute;top:.95rem;right:.95rem;width:8px;height:8px;border-radius:50%;background:#2d2d2d}
.pg-dot.on{background:#10b981;box-shadow:0 0 7px #10b98170}
.btn-pg{width:100%;background:transparent;border:1px solid #222;border-radius:8px;color:#9ca3af;font-size:.78rem;font-weight:600;padding:.55rem 1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;transition:all .18s}
.btn-pg:hover{border-color:#3B82F6;color:#60a5fa;background:rgba(59,130,246,.06)}
.btn-pg.on{border-color:#10b981;color:#10b981;background:rgba(16,185,129,.05)}
.btn-pg.on:hover{border-color:#ef4444;color:#f87171;background:rgba(239,68,68,.06)}
.pg-overlay{position:fixed;inset:0;background:rgba(0,0,0,.78);backdrop-filter:blur(8px);z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem}
.pg-modal{background:#111;border:1px solid #222;border-radius:18px;width:100%;max-width:400px;padding:1.75rem;position:relative;animation:pgIn .22s ease}
@keyframes pgIn{from{opacity:0;transform:scale(.95) translateY(8px)}to{opacity:1;transform:none}}
.pg-modal-top{display:flex;align-items:center;gap:.9rem;margin-bottom:1.4rem}
.pg-modal-logo{width:54px;height:54px;border-radius:12px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:#0a0a0a}
.pg-modal-logo img{width:54px;height:54px;object-fit:contain;border-radius:12px}
.pg-modal-logo svg{width:54px;height:54px}
.pg-modal h2{font-size:1rem;font-weight:700;color:#fff;margin:0}
.pg-modal p{font-size:.75rem;color:#6b7280;margin:3px 0 0}
.btn-pgx{position:absolute;top:.9rem;right:.9rem;background:none;border:none;color:#6b7280;cursor:pointer;font-size:1.4rem;line-height:1;padding:2px 6px;transition:color .15s}
.btn-pgx:hover{color:#fff}
.pg-fld{margin-bottom:.85rem}
.pg-fld label{display:block;font-size:.7rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px}
.pg-fld input{width:100%;box-sizing:border-box;background:#0a0a0a;border:1px solid #222;border-radius:9px;padding:.68rem 1rem;color:#e2e8f0;font-size:.85rem;outline:none;transition:border-color .15s}
.pg-fld input:focus{border-color:#3B82F6}
.pg-fld input::placeholder{color:#374151}
.btn-pg-save{width:100%;background:#3B82F6;color:#fff;border:none;border-radius:9px;padding:.78rem;font-size:.88rem;font-weight:700;cursor:pointer;margin-top:1.2rem;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:.45rem}
.btn-pg-save:hover{background:#2563EB}
.btn-pg-disc{width:100%;background:transparent;color:#ef4444;border:1px solid #ef4444;border-radius:9px;padding:.62rem;font-size:.78rem;font-weight:600;cursor:pointer;margin-top:.6rem;transition:background .15s}
.btn-pg-disc:hover{background:rgba(239,68,68,.08)}
.pg-sep{border:none;border-top:1px solid #1e1e1e;margin:1.2rem 0}
.pg-toast{position:fixed;top:1.2rem;right:1.2rem;z-index:99999;padding:.75rem 1.2rem;border-radius:10px;font-size:.84rem;font-weight:600;display:flex;align-items:center;gap:.5rem;box-shadow:0 4px 24px rgba(0,0,0,.5)}
.pg-toast.ok{background:#10b981;color:#fff}
.pg-toast.err{background:#ef4444;color:#fff}
</style>

<?php if (!empty($message)): ?>
<div class="pg-toast <?php echo $messageType == 'success' ? 'ok' : 'err'; ?>" id="pg-toast">
 <span class="material-icons-round" style="font-size:1rem"><?php echo $messageType == 'success' ? 'check_circle' : 'error'; ?></span>
 <?php echo htmlspecialchars($message); ?>
</div>
<script>setTimeout(function(){var t=document.getElementById('pg-toast');if(t)t.remove();},4000);</script>
<?php
endif; ?>

<div style="margin-bottom:1.6rem">
 <h1 style="font-size:1.35rem;font-weight:700;color:#f1f5f9;margin:0">Provedores</h1>
 <p style="color:#6b7280;font-size:.82rem;margin:5px 0 0">Conecte seus gateways de pagamento PIX</p>
</div>

<div class="pg-grid">
<?php foreach ($all_providers as $prov):
    $pkey = strtolower($prov['name']);
    $isConn = isset($connected[$pkey]);
    $connId = $isConn ? $connected[$pkey] : null;
    $provJson = htmlspecialchars(json_encode($prov), ENT_QUOTES);
    $connStr = $isConn ? 'true' : 'false';
    $idStr = ($connId !== null) ? (string)$connId : 'null';
    $logoHtml = getProviderLogo($prov['name']);
    $logoHtmlModal = getProviderLogo($prov['name'], 54, 12);
?>
<div class="pg-card">
 <div class="pg-dot <?php echo $isConn ? 'on' : ''; ?>"></div>
 <div class="pg-top">
  <div class="pg-logo"><?php echo $logoHtml; ?></div>
  <div>
   <p class="pg-name"><?php echo htmlspecialchars($prov['name']); ?></p>
   <p class="pg-desc"><?php echo htmlspecialchars($prov['description']); ?></p>
  </div>
 </div>
 <button class="btn-pg <?php echo $isConn ? 'on' : ''; ?>"
         data-prov=<?php echo $provJson; ?>
         data-conn=<?php echo $connStr; ?>
         data-id=<?php echo $idStr; ?>
         data-logo=<?php echo htmlspecialchars(json_encode($logoHtmlModal), ENT_QUOTES); ?>
         onclick="openPgModal(this)">
  <span class="material-icons-round"><?php echo $isConn ? 'check_circle' : 'electrical_services'; ?></span>
  <?php echo $isConn ? 'Conectado' : 'Conectar'; ?>
 </button>
</div>
<?php
endforeach; ?>
</div>

<div class="pg-overlay" id="pgModal" style="display:none" onclick="if(event.target===this)closePgModal()">
 <div class="pg-modal">
  <button class="btn-pgx" onclick="closePgModal()">&#215;</button>
  <div class="pg-modal-top">
   <div class="pg-modal-logo" id="pgm-logo"></div>
   <div><h2 id="pgm-title"></h2><p id="pgm-desc"></p></div>
  </div>
  <form method="POST">
   <input type="hidden" name="action" value="add">
   <input type="hidden" name="provider_name" id="pgm-pname">
   <div id="pgm-fields"></div>
   <button type="submit" class="btn-pg-save"><span class="material-icons-round" style="font-size:.95rem">electrical_services</span> Conectar</button>
  </form>
  <div id="pgm-disc" style="display:none">
   <hr class="pg-sep">
   <form method="POST">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="pgm-disc-id">
    <button type="submit" class="btn-pg-disc" onclick="return confirm('Desconectar?')"><span class="material-icons-round" style="font-size:.9rem;vertical-align:middle">link_off</span> Desconectar</button>
   </form>
  </div>
 </div>
</div>
<script>
function openPgModal(btn){
 var prov=JSON.parse(btn.getAttribute('data-prov'));
 var isConn=btn.getAttribute('data-conn')==='true';
 var connId=btn.getAttribute('data-id');
 var logoHtml=JSON.parse(btn.getAttribute('data-logo'));
 document.getElementById('pgm-logo').innerHTML=logoHtml;
 document.getElementById('pgm-title').textContent=prov.name;
 document.getElementById('pgm-desc').textContent=prov.description;
 document.getElementById('pgm-pname').value=prov.name;
 var fc=document.getElementById('pgm-fields');fc.innerHTML='';
 for(var j=0;j<prov.fields.length;j++){
  var f=prov.fields[j];var d=document.createElement('div');d.className='pg-fld';
  d.innerHTML='<label>'+f.label+'</label><input type="'+f.type+'" name="'+f.key+'" placeholder="'+f.label+'" autocomplete="off">';
  fc.appendChild(d);
 }
 var disc=document.getElementById('pgm-disc');
 if(isConn&&connId&&connId!=='null'){disc.style.display='block';document.getElementById('pgm-disc-id').value=connId;}
 else disc.style.display='none';
 document.getElementById('pgModal').style.display='flex';document.body.style.overflow='hidden';
}
function closePgModal(){document.getElementById('pgModal').style.display='none';document.body.style.overflow='';}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closePgModal();});
</script>
<?php
$content = ob_get_clean();
include '../layouts/base_new.php';
$conn->close();
?>
