<?php
// Direct PHP write of the new provedores/index.php
$target = __DIR__ . '/index.php';

ob_start();
?>
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
$all_providers = array(
        array('name' => 'Pushin Pay', 'description' => 'Gateway de pagamentos', 'color' => '#3B82F6', 'fields' => array(array('key' => 'api_token', 'label' => 'Token da API', 'type' => 'password'))),
        array('name' => 'Mercado Pago', 'description' => 'Gateway de pagamentos', 'color' => '#00B1EA', 'fields' => array(array('key' => 'api_token', 'label' => 'Access Token', 'type' => 'password'))),
        array('name' => 'Aoba Pay', 'description' => 'Gateway de pagamentos', 'color' => '#8B5CF6', 'fields' => array(array('key' => 'api_token', 'label' => 'Secret Key', 'type' => 'password'))),
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
include __DIR__ . '/../layouts/base_new.php'; // load base then inject content below
$content_placeholder = ob_get_clean();
// Actually render the page the proper way:
ob_start();
?>
<style>
.pg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
@media(max-width:1100px){.pg-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.pg-grid{grid-template-columns:1fr}}
.pg-card{background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;padding:1.2rem 1.35rem 1rem;position:relative;display:flex;flex-direction:column;gap:.85rem;transition:border-color .2s,box-shadow .2s}
.pg-card:hover{border-color:#2a2a2a;box-shadow:0 4px 24px rgba(0,0,0,.5)}
.pg-top{display:flex;align-items:center;gap:.85rem}
.pg-logo{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;color:#fff;flex-shrink:0}
.pg-name{font-size:.92rem;font-weight:700;color:#f1f5f9;margin:0}
.pg-desc{font-size:.72rem;color:#6b7280;margin:2px 0 0}
.pg-dot{position:absolute;top:.95rem;right:.95rem;width:8px;height:8px;border-radius:50%;background:#2d2d2d}
.pg-dot.on{background:#10b981;box-shadow:0 0 7px #10b98170}
.btn-pg{width:100%;background:transparent;border:1px solid #222;border-radius:8px;color:#9ca3af;font-size:.78rem;font-weight:600;padding:.55rem 1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;transition:all .18s}
.btn-pg:hover{border-color:#3B82F6;color:#60a5fa;background:rgba(59,130,246,.06)}
.btn-pg.on{border-color:#10b981;color:#10b981;background:rgba(16,185,129,.05)}
.btn-pg.on:hover{border-color:#ef4444;color:#f87171;background:rgba(239,68,68,.06)}
.pg-overlay{position:fixed;inset:0;background:rgba(0,0,0,.78);backdrop-filter:blur(8px);z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem}
.pg-modal{background:#111;border:1px solid #222;border-radius:18px;width:100%;max-width:400px;padding:1.75rem;position:relative}
.pg-modal-top{display:flex;align-items:center;gap:.9rem;margin-bottom:1.4rem}
.pg-modal-logo{width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;color:#fff;flex-shrink:0}
.pg-modal h2{font-size:1rem;font-weight:700;color:#fff;margin:0}
.pg-modal p{font-size:.75rem;color:#6b7280;margin:3px 0 0}
.btn-pgx{position:absolute;top:.9rem;right:.9rem;background:none;border:none;color:#6b7280;cursor:pointer;font-size:1.4rem;line-height:1;padding:2px 6px}
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
    $words = explode(' ', $prov['name']);
    $initials = '';
    foreach ($words as $w) {
        $initials .= strtoupper($w[0]);
    }
    $initials = substr($initials, 0, 2);
    $provJson = htmlspecialchars(json_encode($prov), ENT_QUOTES);
    $connStr = $isConn ? 'true' : 'false';
    $idStr = ($connId !== null) ? (string)$connId : 'null';
?>
<div class="pg-card">
 <div class="pg-dot <?php echo $isConn ? 'on' : ''; ?>"></div>
 <div class="pg-top">
  <div class="pg-logo" style="background:linear-gradient(135deg,<?php echo $prov['color']; ?>,<?php echo $prov['color']; ?>99)"><?php echo $initials; ?></div>
  <div>
   <p class="pg-name"><?php echo htmlspecialchars($prov['name']); ?></p>
   <p class="pg-desc"><?php echo htmlspecialchars($prov['description']); ?></p>
  </div>
 </div>
 <button class="btn-pg <?php echo $isConn ? 'on' : ''; ?>" onclick="openPgModal(<?php echo $provJson; ?>,<?php echo $connStr; ?>,<?php echo $idStr; ?>)">
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
function openPgModal(prov,isConn,connId){
 var words=prov.name.split(' ');var init='';
 for(var i=0;i<words.length&&i<2;i++)init+=words[i][0].toUpperCase();
 var logo=document.getElementById('pgm-logo');
 logo.textContent=init;logo.style.background='linear-gradient(135deg,'+prov.color+','+prov.color+'99)';
 document.getElementById('pgm-title').textContent=prov.name;
 document.getElementById('pgm-desc').textContent=prov.description;
 document.getElementById('pgm-pname').value=prov.name;
 var fc=document.getElementById('pgm-fields');fc.innerHTML='';
 for(var j=0;j<prov.fields.length;j++){var f=prov.fields[j];var d=document.createElement('div');d.className='pg-fld';d.innerHTML='<label>'+f.label+'</label><input type="'+f.type+'" name="'+f.key+'" placeholder="'+f.label+'" autocomplete="off">';fc.appendChild(d);}
 var disc=document.getElementById('pgm-disc');
 if(isConn&&connId){disc.style.display='block';document.getElementById('pgm-disc-id').value=connId;}else disc.style.display='none';
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
<?php
// ------ THIS CODE RUNS FIRST (it's the apply script)
$rendered = ob_get_clean(); // the entire PHP code above was captured
$r = file_put_contents($target, $rendered);
if (function_exists('opcache_invalidate'))
    opcache_invalidate($target, true);
header('Content-Type: text/plain');
echo "Written: $r bytes\n";
echo "MD5: " . md5_file($target) . "\n";
echo "Size: " . filesize($target) . " bytes\n";
unlink(__FILE__);
