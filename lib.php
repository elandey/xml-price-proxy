
<?php
function logLine($file,$s){@mkdir(dirname($file),0775,true);@file_put_contents($file,"[".date('c')."] ".$s."
",FILE_APPEND);}
function escapeXml($s){return strtr($s,['&'=>'&amp;','<'=>'&lt;','>'=>'&gt;','"'=>'&quot;',"'“"=>'&apos;']);}
function parseTiersSpec($spec){
  $parts=preg_split('/[,;
]+/',$spec,-1,PREG_SPLIT_NO_EMPTY); $rules=[];
  foreach($parts as $raw){$raw=trim($raw); if($raw==='')continue;
    if(strpos($raw,':')!==false){[$rng,$pct]=explode(':',$raw,2);} else {$rng='*-*';$pct=$raw;}
    $pct=rtrim(trim($pct),'%'); if(!is_numeric($pct)) throw new Exception("Некорректный процент: $raw"); $p=(float)$pct;
    $rng=trim($rng); if(substr($rng,-1)==='+'){ $lo=(float)substr($rng,0,-1); $hi=INF; }
    elseif(strpos($rng,'-')!==false){[$a,$b]=explode('-',$rng,2); $lo=($a===''||$a==='*')?0.0:(float)$a; $hi=($b===''||$b==='*')?INF:(float)$b;}
    else {$lo=(float)$rng; $hi=$lo;}
    if(!is_finite($lo)||!is_finite($hi)||$lo<0||$hi<$lo) throw new Exception("Неверный диапазон: $raw");
    $rules[]=['lo'=>$lo,'hi'=>$hi,'p'=>$p];
  }
  usort($rules,fn($A,$B)=>($A['lo']==$B['lo'])?($A['hi']<=>$B['hi']) : ($A['lo']<=>$B['lo']));
  return $rules;
}
function parseNum($s){ if($s===null)return null; $s=str_replace("Â ",' ',$s); $s=preg_replace('/\s+/','',$s); $s=str_replace(',', '.',$s);
  if(!preg_match('/^-?\d+(?:\.\d+)?/',$s,$m)) return null; return (float)$m[0]; }
function formatLike($orig,$val){
  $orig=trim((string)$orig); $comma=(strpos($orig,',')!==false)&&(strpos($orig,'.')===false);
  $frac=0; if(strpos($orig,',')!==false)$frac=strlen(substr(strrchr($orig,','),1));
  elseif(strpos($orig,'.')!==false)$frac=strlen(substr(strrchr($orig,'.'),1));
  if($frac>6)$frac=6; $out=$frac>0?number_format($val,$frac,'.',''): (string)round($val);
  return $comma?str_replace('.',',',$out):$out;
}
function detectXmlEncoding($xml){
  if(preg_match('/<\?xml[^>]*encoding=["']([^"']+)["']/i',$xml,$m)){ $enc=strtoupper(trim($m[1])); return $enc?:'UTF-8'; }
  return 'UTF-8';
}
function fetchUrl($url,$timeout=60){
  if(function_exists('curl_init')){ $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_USERAGENT=>'XML-Price-Proxy/1.0 (+php)',CURLOPT_CONNECTTIMEOUT=>$timeout,CURLOPT_TIMEOUT=>$timeout,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
    $res=curl_exec($ch); $ok=$res!==false && curl_getinfo($ch,CURLINFO_HTTP_CODE)<400; curl_close($ch); return $ok?$res:null;
  } else { $ctx=stream_context_create(['http'=>['timeout'=>$timeout,'header'=>"User-Agent: XML-Price-Proxy/1.0
"]]); $res=@file_get_contents($url,false,$ctx); return $res===false?null:$res; }
}
