<?php
/**
 * m.php — Métricas próprias da Mage Sistemas.
 * Sem cookies, sem serviços externos, sem armazenar IP: cada visitante vira um
 * código anônimo que muda todo dia (hash de IP+navegador+data+sal do servidor).
 * Os registros ficam em stats/ (pasta bloqueada ao público) e o painel.php lê.
 */
date_default_timezone_set('America/Sao_Paulo');
http_response_code(204);
header('Cache-Control: no-store');

$dir = __DIR__ . '/stats';
if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
if (!file_exists($dir . '/.htaccess')) { @file_put_contents($dir . '/.htaccess', "Require all denied\n"); }
if (!file_exists($dir . '/index.html')) { @file_put_contents($dir . '/index.html', ''); }

$salf = $dir . '/sal.txt';
if (!file_exists($salf)) { @file_put_contents($salf, bin2hex(random_bytes(16))); }
$sal = trim(@file_get_contents($salf));
if ($sal === '') { $sal = 'mage'; }

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|lighthouse|headless|facebookexternal/i', $ua)) { exit; }

$raw = file_get_contents('php://input');
$j = json_decode($raw, true);
if (!is_array($j)) { exit; }

$p = substr(preg_replace('~[^a-zA-Z0-9/\-_.]~', '', (string)($j['p'] ?? '/')), 0, 80);
if ($p === '' ) { $p = '/'; }
$e = substr(preg_replace('~[^a-z\-]~', '', (string)($j['e'] ?? '')), 0, 20);

$r = '';
if (!empty($j['r'])) {
  $h = parse_url((string)$j['r'], PHP_URL_HOST);
  if ($h && stripos($h, 'magesistemas') === false) { $r = substr(strtolower($h), 0, 60); }
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$d  = date('Y-m-d');
$u  = substr(hash('sha256', $ip . '|' . $ua . '|' . $d . '|' . $sal), 0, 12);
$dv = preg_match('/Mobile|Android|iPhone/i', $ua) ? 'm' : 'd';

$linha = json_encode(
  ['t' => date('H:i'), 'd' => $d, 'p' => $p, 'r' => $r, 'e' => $e, 'u' => $u, 'dv' => $dv],
  JSON_UNESCAPED_SLASHES
) . "\n";
@file_put_contents($dir . '/visitas-' . date('Y-m') . '.jsonl', $linha, FILE_APPEND | LOCK_EX);
