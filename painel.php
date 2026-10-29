<?php
/**
 * painel.php — Painel de métricas da Mage Sistemas.
 * Primeiro acesso: define a senha (guardada com hash em stats/senha.txt, fora do ar).
 * Depois: login simples por sessão. Lê os registros de stats/visitas-*.jsonl.
 */
date_default_timezone_set('America/Sao_Paulo');
session_start();
$dir = __DIR__ . '/stats';
$senhaf = $dir . '/senha.txt';
$erro = '';

if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
if (!file_exists($dir . '/.htaccess')) { @file_put_contents($dir . '/.htaccess', "Require all denied\n"); }

if (isset($_POST['definir_senha']) && !file_exists($senhaf)) {
  $s1 = $_POST['senha1'] ?? ''; $s2 = $_POST['senha2'] ?? '';
  if (strlen($s1) < 8) { $erro = 'A senha precisa ter pelo menos 8 caracteres.'; }
  elseif ($s1 !== $s2) { $erro = 'As senhas não conferem.'; }
  else { @file_put_contents($senhaf, password_hash($s1, PASSWORD_DEFAULT)); $_SESSION['mage_ok'] = true; }
}
if (isset($_POST['entrar']) && file_exists($senhaf)) {
  $hash = trim(@file_get_contents($senhaf));
  if (password_verify($_POST['senha'] ?? '', $hash)) { $_SESSION['mage_ok'] = true; }
  else { $erro = 'Senha incorreta.'; sleep(1); }
}
if (isset($_GET['sair'])) { session_destroy(); header('Location: painel.php'); exit; }

$logado = !empty($_SESSION['mage_ok']);
$precisaSenha = !file_exists($senhaf);

function carregar($dir) {
  $rows = [];
  $meses = [date('Y-m'), date('Y-m', strtotime('first day of last month'))];
  foreach ($meses as $m) {
    $f = "$dir/visitas-$m.jsonl";
    if (!file_exists($f)) continue;
    foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
      $j = json_decode($l, true);
      if (is_array($j)) $rows[] = $j;
    }
  }
  return $rows;
}

$hoje = date('Y-m-d');
$dias30 = []; for ($i = 29; $i >= 0; $i--) $dias30[date('Y-m-d', strtotime("-$i day"))] = ['pv'=>0,'u'=>[]];
$pv_hoje=0; $u_hoje=[]; $pv7=0; $u7=[]; $pv30=0; $u30=[];
$paginas=[]; $origens=[]; $eventos=[]; $disp=['m'=>0,'d'=>0];
$d7 = date('Y-m-d', strtotime('-6 day')); $d30 = date('Y-m-d', strtotime('-29 day'));

if ($logado) {
  foreach (carregar($dir) as $r) {
    $d = $r['d'] ?? ''; if ($d < $d30) continue;
    $ev = $r['e'] ?? '';
    if ($ev !== '') { $eventos[$ev] = ($eventos[$ev] ?? 0) + 1; continue; }
    $u = $r['u'] ?? '';
    $pv30++; $u30[$u]=1;
    if (isset($dias30[$d])) { $dias30[$d]['pv']++; $dias30[$d]['u'][$u]=1; }
    if ($d >= $d7) { $pv7++; $u7[$u]=1; }
    if ($d === $hoje) { $pv_hoje++; $u_hoje[$u]=1; }
    $paginas[$r['p'] ?? '/'] = ($paginas[$r['p'] ?? '/'] ?? 0) + 1;
    if (($r['r'] ?? '') !== '') $origens[$r['r']] = ($origens[$r['r']] ?? 0) + 1;
    $disp[($r['dv'] ?? 'd')]++;
  }
  arsort($paginas); arsort($origens); arsort($eventos);
  $maxdia = 1; foreach ($dias30 as $v) $maxdia = max($maxdia, $v['pv']);
}
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Painel de métricas — Mage Sistemas</title>
<link rel="icon" type="image/png" href="img/logo.png">
<style>
:root{--bg:#0A0718;--card:#191241;--line:rgba(255,255,255,.12);--text:#F3F0FC;--muted:#A8A0CE;--vio:#7C5CFF;--mag:#E23A9D}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;padding:28px 18px}
.wrap{max-width:980px;margin:0 auto}
h1{font-size:24px;margin-bottom:4px}
.sub{color:var(--muted);font-size:14px;margin-bottom:26px}
.sub a{color:#A78BFF}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:22px}
.kpi{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:16px}
.kpi b{font-size:26px;display:block}
.kpi span{color:var(--muted);font-size:12.5px}
.card{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:20px;margin-bottom:16px}
.card h2{font-size:15px;margin-bottom:14px;color:var(--muted);font-weight:600}
.bars{display:flex;align-items:flex-end;gap:3px;height:120px}
.bars div{flex:1;background:linear-gradient(180deg,var(--mag),var(--vio));border-radius:3px 3px 0 0;min-height:2px;position:relative}
.bars div[title]:hover::after{content:attr(title);position:absolute;bottom:102%;left:50%;transform:translateX(-50%);background:#000;padding:3px 8px;border-radius:6px;font-size:11px;white-space:nowrap}
table{width:100%;border-collapse:collapse;font-size:14px}
td{padding:7px 4px;border-bottom:1px solid var(--line)}
td:last-child{text-align:right;font-weight:600}
.duas{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:700px){.duas{grid-template-columns:1fr}}
form{max-width:380px;margin:60px auto;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:28px}
form h1{margin-bottom:14px}
input{width:100%;padding:12px;margin-bottom:12px;border-radius:9px;border:1px solid var(--line);background:#0E0A26;color:var(--text);font-size:15px}
button{width:100%;padding:13px;border:none;border-radius:999px;background:linear-gradient(100deg,#7C3AED,#E23A9D);color:#fff;font-weight:700;font-size:15px;cursor:pointer}
.erro{background:rgba(226,58,157,.15);border:1px solid rgba(226,58,157,.5);padding:10px 14px;border-radius:9px;font-size:13.5px;margin-bottom:12px}
.aviso{color:var(--muted);font-size:12.5px;margin-top:12px}
</style>
</head>
<body>
<?php if (!$logado): ?>
  <form method="post">
    <?php if ($precisaSenha): ?>
      <h1>Criar senha do painel</h1>
      <?php if ($erro): ?><div class="erro"><?=e($erro)?></div><?php endif; ?>
      <input type="password" name="senha1" placeholder="Nova senha (mínimo 8 caracteres)" required>
      <input type="password" name="senha2" placeholder="Repita a senha" required>
      <button name="definir_senha" value="1">Criar e entrar</button>
      <p class="aviso">A senha fica guardada com criptografia no servidor, fora do ar. Guarde-a: para trocar, apague o arquivo stats/senha.txt pelo cPanel e defina de novo.</p>
    <?php else: ?>
      <h1>Painel de métricas</h1>
      <?php if ($erro): ?><div class="erro"><?=e($erro)?></div><?php endif; ?>
      <input type="password" name="senha" placeholder="Senha" required autofocus>
      <button name="entrar" value="1">Entrar</button>
    <?php endif; ?>
  </form>
<?php else: ?>
  <div class="wrap">
    <h1>Métricas do site</h1>
    <p class="sub">magesistemas.com.br · sem cookies, dados agregados · <a href="painel.php?sair=1">sair</a></p>
    <div class="grid">
      <div class="kpi"><b><?=count($u_hoje)?></b><span>Visitantes hoje</span></div>
      <div class="kpi"><b><?=$pv_hoje?></b><span>Páginas vistas hoje</span></div>
      <div class="kpi"><b><?=count($u7)?></b><span>Visitantes · 7 dias</span></div>
      <div class="kpi"><b><?=count($u30)?></b><span>Visitantes · 30 dias</span></div>
      <div class="kpi"><b><?=$pv30?></b><span>Páginas vistas · 30 dias</span></div>
    </div>
    <div class="card">
      <h2>Visitas por dia — últimos 30 dias</h2>
      <div class="bars"><?php foreach ($dias30 as $d => $v): ?><div style="height:<?=max(2,round($v['pv']/$maxdia*100))?>%" title="<?=e(date('d/m', strtotime($d)))?>: <?=$v['pv']?> páginas, <?=count($v['u'])?> visitantes"></div><?php endforeach; ?></div>
    </div>
    <div class="card">
      <h2>Cliques que valem dinheiro — 30 dias</h2>
      <table>
        <tr><td>Conversas no WhatsApp iniciadas</td><td><?=$eventos['whatsapp'] ?? 0?></td></tr>
        <tr><td>Webchat aberto</td><td><?=$eventos['webchat'] ?? 0?></td></tr>
        <tr><td>Ligações (toque no telefone)</td><td><?=$eventos['ligacao'] ?? 0?></td></tr>
        <tr><td>Área do cliente</td><td><?=$eventos['area-cliente'] ?? 0?></td></tr>
      </table>
    </div>
    <div class="duas">
      <div class="card">
        <h2>Páginas mais vistas — 30 dias</h2>
        <table><?php foreach (array_slice($paginas,0,10,true) as $p=>$n): ?><tr><td><?=e($p)?></td><td><?=$n?></td></tr><?php endforeach; if(!$paginas): ?><tr><td colspan="2">Sem dados ainda — volte amanhã.</td></tr><?php endif; ?></table>
      </div>
      <div class="card">
        <h2>De onde vieram — 30 dias</h2>
        <table><?php foreach (array_slice($origens,0,10,true) as $o=>$n): ?><tr><td><?=e($o)?></td><td><?=$n?></td></tr><?php endforeach; if(!$origens): ?><tr><td colspan="2">Acessos diretos ou sem origem informada.</td></tr><?php endif; ?></table>
      </div>
    </div>
    <div class="card">
      <h2>Dispositivos — 30 dias</h2>
      <?php $tot = max(1, $disp['m'] + $disp['d']); ?>
      <table>
        <tr><td>📱 Celular</td><td><?=round($disp['m']/$tot*100)?>%</td></tr>
        <tr><td>💻 Computador</td><td><?=round($disp['d']/$tot*100)?>%</td></tr>
      </table>
    </div>
  </div>
<?php endif; ?>
</body>
</html>
