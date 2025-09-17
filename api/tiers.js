
export default function handler(req, res) {
  const { src, tiers, ttl_minutes, price_tag } = req.query;

  if (!src || !tiers || !ttl_minutes || !price_tag) {
    return res.status(400).json({ error: 'Missing required parameters' });
  }

  // Логика обработки данных
  res.status(200).send(`Tiers XML for ${src} with ${price_tag}`);
}
