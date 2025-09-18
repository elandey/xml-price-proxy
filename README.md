# XML Price Proxy (Vercel + GitHub)
Serverless функція, яка забирає джерельний XML, робить націнку за діапазонами `tiers` і повертає готовий XML.

## Швидкий старт
1) Розпакуй в папку.
2) Ініціалізуй git і залий у GitHub:
   git init
   git add .
   git commit -m "initial"
   git branch -M master
   git remote add origin https://github.com/<USER>/xml-price-proxy.git
   git push -u origin master
3) На vercel.com → New Project → обери репозиторій → Deploy.

## Виклики
- Перевірка: https://<project>.vercel.app/health
- XML: https://<project>.vercel.app/tiers.xml?src=...&price_tag=price&ttl_minutes=15&tiers=0-49:60,50-99:45

Додатково: download=1, filename=myfeed.xml. Якщо не передати tiers — беруться з tiers.txt.
