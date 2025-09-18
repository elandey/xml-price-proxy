# XML Price Proxy (Vercel, без cron)
Serverless-функція: забирає джерельний XML, застосовує націнку за діапазонами, повертає XML. Крон не потрібен — CRM просто відкриває URL.

## Швидкий старт
1) Розпакуй у папку.
2) Завантаж у GitHub (main/master):
   git init
   git add .
   git commit -m "initial"
   git branch -M master
   git remote add origin https://github.com/<USER>/xml-price-proxy.git
   git push -u origin master
3) На vercel.com → New Project → обери репозиторій → Deploy.

## Виклики
- Health: https://<project>.vercel.app/health
- XML з націнкою:
  https://<project>.vercel.app/tiers.xml?src=URL_ДЖЕРЕЛА&price_tag=price&ttl_minutes=15&tiers=0-49:60,50-99:45,100-499:35,500-1499:30,1500-2499:28,2500-4999:20,5000-7499:16,7500-9999:14,10000-99999:8
- Скачування: додай &download=1&filename=feed.xml

Поради:
- Якщо використовуєш формат `400+`, у URL кодуй плюс: `400%2B:25`.
- ttl_minutes=0 вимикає CDN-кеш.
