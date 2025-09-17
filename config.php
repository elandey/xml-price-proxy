
<?php
return [
  'SRC'         => 'https://opt-drop.com/storage/xml/opt-drop-0.xml',
  'PRICE_TAG'   => 'price',
  'TIERS_FILE'  => __DIR__.'/tiers.txt',
  'TTL_MINUTES' => 15,
  'OUT_FILE'    => __DIR__.'/out/feed.xml',
  'LOG_FILE'    => __DIR__.'/logs/build.log',
  'BUILD_KEY'   => 'mysecret123',
];
