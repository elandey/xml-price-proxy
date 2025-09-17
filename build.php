
<?php
$config = require __DIR__.'/config.php';
require __DIR__.'/lib.php';
@mkdir(dirname($config['OUT_FILE']),0775,true);

$key = $_GET['key'] ?? '';
if (!empty($config['BUILD_KEY']) && $key !== $config['BUILD_KEY']) {
  header('HTTP/1.1 403 Forbidden'); echo "forbidden"; exit;
}

$tiersSpec = is_file($config['TIERS_FILE']) ? trim(file_get_contents($config['TIERS_FILE'])) : '';
if ($tiersSpec===''){ http_response_code(400); echo "tiers.txt пустой"; exit; }

$src   = $config['SRC']; $priceTag = $config['PRICE_TAG']; $ttl = (int)$config['TTL_MINUTES'];

$start = microtime(true);
$xml = fetchUrl($src, 60);
if ($xml === null) { logLine($config['LOG_FILE'],"FAIL download"); http_response_code(502); echo "download failed"; exit; }

try { $rules = parseTiersSpec($tiersSpec); }
catch (Throwable $e) { logLine($config['LOG_FILE'],"FAIL tiers: ".$e->getMessage()); http_response_code(400); echo $e->getMessage(); exit; }

$enc = detectXmlEncoding($xml);
$changed = 0;
$out = preg_replace_callback(
  '/<offer[^>]*>[\s\S]*?<\/offer>/i',
  function($m) use ($priceTag,$rules,&$changed){
    $offer = $m[0]; $re='/(<'.preg_quote($priceTag,'/').'[^>]*>)([^<]+)(<\/'.preg_quote($priceTag,'/').'>)/i';
    if(!preg_match($re,$offer,$mm)) return $offer;
    $orig=$mm[2]; $cur=parseNum($orig); if($cur===null) return $offer;
    $pct=null; foreach($rules as $r){ if($r['lo']<= $cur && $cur <= $r['hi']) { $pct=$r['p']; break; } }
    if($pct===null) return $offer;
    $upd=$cur*(1.0+$pct/100.0); $text=formatLike($orig,$upd); $changed++;
    return preg_replace($re,'${1}'.$text.'${3}',$offer,1);
  },
  $xml
);
$out = preg_replace('/^\s*<\?xml[^>]*\?>\s*/i','',$out);
$out = "<?xml version="1.0" encoding="$enc"?>
<!-- X-Changed: $changed -->
".$out;

file_put_contents($config['OUT_FILE'],$out);
$sec = round((microtime(true)-$start),3);
logLine($config['LOG_FILE'],"OK changed=$changed time={$sec}s size=".strlen($out));
header('Content-Type: text/plain; charset=utf-8');
echo "ok changed=$changed time={$sec}s
";
