<?php
require __DIR__ . '/vendor/autoload.php';

$mongo = new MongoDB\Client(getenv('MONGODB_URI'));
$db = $mongo->selectDatabase('vite_gourmand');

$collections = $db->listCollections();

foreach ($collections as $collection) {
    echo $collection->getName() . PHP_EOL;
}
