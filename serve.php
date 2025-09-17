
<?php
$config = require __DIR__.'/config.php';
$file = $config['OUT_FILE'];
if (!is_file($file)) { header('HTTP/1.1 503 Service Unavailable'); header('Content-Type: text/plain; charset=utf-8'); echo "Нет готового фида. Подождите генерации."; exit; }

$download = (isset($_GET['download']) && $_GET['download']==='1');
$filename = isset($_GET['filename']) ? preg_replace('/[^A-Za-z0-9_.-]/','_',$_GET['filename']) : 'feed.xml';

$xml = file_get_contents($file);
$changed = 0; if (preg_match('/X-Changed:\s*(\d+)/',$xml,$m)) $changed=(int)$m[1];

header('Content-Type: application/xml; charset=utf-8');
header('X-Changed: '.$changed);
if ($download) header('Content-Disposition: attachment; filename="'.$filename.'"');
echo $xml;
