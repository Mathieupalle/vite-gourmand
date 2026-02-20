<?php

require_once __DIR__ . '/../vendor/autoload.php';

function mongo()
{
    static $client = null;

    if ($client === null) {
        $uri = "mongodb+srv://vitegourmand:ViteGourmand12345@cluster0.ptlv7eh.mongodb.net/?appName=Cluster0&authSource=admin";
        $client = new MongoDB\Client($uri);
    }

    return $client;
}
