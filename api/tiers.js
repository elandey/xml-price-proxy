
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const DEFAULT_SRC = 'https://opt-drop.com/storage/xml/opt-drop-0.xml';
const DEFAULT_PRICE_TAG = 'price';

const DEFAULT_TIERS = (() => {
  try { return fs.readFileSync(path.join(__dirname, '..', 'tiers.txt'), 'utf8').trim(); }
  catch { return ''; }
})();

function parseTiersSpec(spec) {
  if (!spec || !spec.trim()) throw new Error('Передайте "tiers" або заповніть tiers.txt');
  const parts = spec.split(/[,;\r\n]+/).map(s => s.trim()).filter(Boolean);
  const rules = [];
  for (const raw of parts) {
    let rng = '*-*', pct = '';
    if (raw.includes(':')) [rng, pct] = raw.split(':', 2); else pct = raw;
    pct = pct.trim().replace(/%+$/, '');
    if (pct === '' || isNaN(pct)) throw new Error(`Некоректний відсоток: "${raw}"`);
    const p = parseFloat(pct);
    rng = rng.trim();
    let lo, hi;
    if (rng.endsWith('+')) { lo = parseFloat(rng.slice(0,-1)); hi = Infinity; }
    else if (rng.includes('-')) {
      const [a,b] = rng.split('-',2);
      lo = (a === '' || a === '*') ? 0 : parseFloat(a);
      hi = (b === '' || b === '*') ? Infinity : parseFloat(b);
    } else { lo = parseFloat(rng); hi = lo; }
    if (!(isFinite(lo) && isFinite(hi)) || lo < 0 || hi < lo) throw new Error(`Невірний діапазон: "${raw}"`);
    rules.push({lo,hi,p});
  }
  rules.sort((A,B)=>A.lo===B.lo?(A.hi-B.hi):(A.lo-B.lo));
  return rules;
}

function parseNumber(s){
  if (s==null) return null;
  let x = String(s).replace(/\u00A0/g,' ').replace(/\s+/g,'');
  const comma = x.includes(',') && !x.includes('.');
  if (comma) x = x.replace(',', '.');
  const m = x.match(/^-?\d+(?:\.\d+)?/);
  if (!m) return null;
  return parseFloat(m[0]);
}

function formatLike(orig, val){
  const s = String(orig).trim();
  const commaOnly = s.includes(',') && !s.includes('.');
  let frac = 0;
  if (s.includes(',')) frac = (s.split(',')[1]||'').length;
  else if (s.includes('.')) frac = (s.split('.')[1]||'').length;
  if (frac>6) frac=6;
  let out = frac>0 ? Number(val).toFixed(frac) : String(Math.round(val));
  if (commaOnly) out = out.replace('.',',');
  return out;
}

export default async function handler(req, res){
  const { src, tiers, ttl_minutes, price_tag, download, filename } = req.query;
  const sourceUrl = src || DEFAULT_SRC;
  const priceTag  = price_tag || DEFAULT_PRICE_TAG;
  const tiersSpec = tiers || DEFAULT_TIERS;
  const ttlMin    = Math.max(0, parseInt(ttl_minutes || '15',10)||0);

  if (!sourceUrl || !priceTag || !tiersSpec){
    return res.status(400).json({error:'Missing required parameters', need:['src','price_tag','tiers','ttl_minutes']});
  }

  let xml;
  try {
    const r = await fetch(sourceUrl, { headers: { 'User-Agent': 'xml-price-proxy/1.0 (+vercel)' } });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    xml = await r.text();
  } catch(e){
    return res.status(502).json({error:'Failed to fetch source XML', detail:e.message});
  }

  const rules = parseTiersSpec(tiersSpec);

  const reOffer = /<offer\b[^>]*>[\s\S]*?<\/offer>/gi;
  const reHeader = /^\s*<\?xml[^>]*\?>\s*/i;

  let changed = 0;
  const transformed = xml.replace(reOffer, offer => {
    const rePrice = new RegExp(`(<${priceTag}[^>]*>)([^<]+)(<\/${priceTag}>)`,'i');
    const m = offer.match(rePrice);
    if (!m) return offer;
    const cur = parseNumber(m[2]);
    if (cur==null) return offer;
    let pct = null;
    for (const r of rules){ if (cur>=r.lo && cur<=r.hi){ pct=r.p; break; } }
    if (pct==null) return offer;
    const upd = cur*(1+pct/100);
    const text = formatLike(m[2], upd);
    changed++;
    return offer.replace(rePrice, `$1${text}$3`);
  });

  const body = `<?xml version="1.0" encoding="UTF-8"?>\n<!-- X-Changed: ${changed} -->\n` + transformed.replace(reHeader,'');

  res.setHeader('Content-Type','application/xml; charset=utf-8');
  res.setHeader('X-Changed', String(changed));
  if (ttlMin>0){
    res.setHeader('Cache-Control', `s-maxage=${ttlMin*60}, stale-while-revalidate=60`);
  }
  if (download==='1'){
    const safe = String(filename||'feed.xml').replace(/[^A-Za-z0-9_.-]/g,'_');
    res.setHeader('Content-Disposition', `attachment; filename="${safe}"`);
  }
  res.status(200).send(body);
}
