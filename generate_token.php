<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$casilla = \App\Models\Casilla::first();
$token = $casilla->createToken('dev')->plainTextToken;
file_put_contents(__DIR__.'/dev_token.txt', $token);
echo $token;
