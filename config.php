
<?php
// Rедактируй под себя
return [
  'SRC'         => 'https://opt-drop.com/storage/xml/opt-drop-0.xml',
  'PRICE_TAG'   => 'price',          // если другая цена — поменяй
  'TIERS_FILE'  => __DIR__.'/tiers.txt', // редактируй диапазоны в этом файле
  'TTL_MINUTES' => 15,               // как часто обновлять
  'OUT_FILE'    => __DIR__.'/out/feed.xml',
  'LOG_FILE'    => __DIR__.'/logs/build.log',
  // защита build.php по URL (необязательно, но желательно)
  'BUILD_KEY'   => 'mysecret123',    // поставь свой; или '' чтобы отключить
];
