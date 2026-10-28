<?php
/**
 * baixar-logos.php — Mage Sistemas
 * Acesse UMA VEZ no navegador: https://magesistemas.com.br/baixar-logos.php
 * Ele baixa os logotipos dos parceiros para a pasta img/ e se apaga sozinho ao final.
 */
header('Content-Type: text/html; charset=utf-8');
set_time_limit(120);

$dir = __DIR__ . '/img';
if (!is_dir($dir)) { mkdir($dir, 0755, true); }

// [url principal, url alternativa (favicon em alta), arquivo de destino]
$fontes = [
  ['https://brajan.com.br/partners/stone.png',   null, 'parceiro-stone.png'],
  ['https://brajan.com.br/partners/cielo.png',   null, 'parceiro-cielo.png'],
  ['https://brajan.com.br/partners/vero.png',    null, 'parceiro-vero.png'],
  ['https://brajan.com.br/partners/fiserv.png',  null, 'parceiro-fiserv.png'],
  ['https://brajan.com.br/partners/pagbank.png', null, 'parceiro-pagbank.png'],
  // Para estes três não há arte pública direta; usamos o ícone do site como provisório:
  [null, 'https://www.google.com/s2/favicons?domain=supertef.com.br&sz=128', 'parceiro-supertef.png'],
  [null, 'https://www.google.com/s2/favicons?domain=paygo.com.br&sz=128',    'parceiro-paygo.png'],
  [null, 'https://www.google.com/s2/favicons?domain=getcard.com.br&sz=128',  'parceiro-getcard.png'],
  // Adquirentes homologadas via SuperTEF (ícones oficiais dos sites como provisório):
  [null, 'https://www.google.com/s2/favicons?domain=getnet.com.br&sz=128',      'parceiro-getnet.png'],
  [null, 'https://www.google.com/s2/favicons?domain=userede.com.br&sz=128',     'parceiro-rede.png'],
  [null, 'https://www.google.com/s2/favicons?domain=mercadopago.com.br&sz=128', 'parceiro-mercadopago.png'],
  [null, 'https://www.google.com/s2/favicons?domain=picpay.com&sz=128',         'parceiro-picpay.png'],
  [null, 'https://www.google.com/s2/favicons?domain=caixa.gov.br&sz=128',       'parceiro-caixa.png'],
  [null, 'https://www.google.com/s2/favicons?domain=sicredi.com.br&sz=128',     'parceiro-sicredi.png'],
  [null, 'https://www.google.com/s2/favicons?domain=safrapay.com.br&sz=128',    'parceiro-safrapay.png'],
  [null, 'https://www.google.com/s2/favicons?domain=sipag.com.br&sz=128',       'parceiro-sipag.png'],
  [null, 'https://www.google.com/s2/favicons?domain=clover.com&sz=128',         'parceiro-clover.png'],
  [null, 'https://www.google.com/s2/favicons?domain=portalbin.com.br&sz=128',   'parceiro-bin.png'],
  [null, 'https://www.google.com/s2/favicons?domain=pago.ifood.com.br&sz=128',  'parceiro-ifoodpago.png'],
  [null, 'https://www.google.com/s2/favicons?domain=redeflex.com.br&sz=128',    'parceiro-redeflex.png'],
];

function baixar($url) {
  if (!$url) return false;
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MageSistemas-Setup)',
    ]);
    $dados = curl_exec($ch);
    $http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($dados !== false && $http === 200 && strlen($dados) > 500) return $dados;
    return false;
  }
  $ctx = stream_context_create(['http' => ['timeout' => 30, 'follow_location' => 1,
    'header' => "User-Agent: Mozilla/5.0 (compatible; MageSistemas-Setup)\r\n"]]);
  $dados = @file_get_contents($url, false, $ctx);
  return ($dados !== false && strlen($dados) > 500) ? $dados : false;
}

echo "<html><body style='font-family:sans-serif;background:#0A0718;color:#F3F0FC;padding:40px'>";
echo "<h2>Baixando logotipos dos parceiros…</h2><ul style='line-height:2'>";

$ok = 0; $falhas = [];
foreach ($fontes as [$principal, $alternativa, $arquivo]) {
  $destino = "$dir/$arquivo";
  if (file_exists($destino) && filesize($destino) > 500) {
    echo "<li>✔️ $arquivo já existe — mantido.</li>"; $ok++; continue;
  }
  $dados = baixar($principal) ?: baixar($alternativa);
  if ($dados) {
    file_put_contents($destino, $dados);
    echo "<li>✅ $arquivo salvo (" . round(strlen($dados)/1024) . " KB)</li>"; $ok++;
  } else {
    echo "<li>⚠️ $arquivo — não foi possível baixar (envie manualmente para a pasta img/)</li>";
    $falhas[] = $arquivo;
  }
}
echo "</ul>";

if ($ok > 0) echo "<p><b>$ok logotipo(s) prontos.</b> O carrossel do site já vai exibi-los automaticamente.</p>";
if ($falhas) echo "<p>Pendentes: " . implode(', ', $falhas) . "</p>";

// Autodestruição do script por segurança
if (@unlink(__FILE__)) {
  echo "<p style='color:#35C98B'>🧹 Este script se apagou sozinho por segurança. Pode fechar esta página.</p>";
} else {
  echo "<p style='color:#FF8FCB'>Importante: apague o arquivo <b>baixar-logos.php</b> do servidor agora.</p>";
}
echo "<p><a href='/' style='color:#A78BFF'>→ Ver o site</a></p></body></html>";
