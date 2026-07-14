<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\Currency::where('is_base', true)->first() ?? App\Models\Currency::first();
App\Models\PaymentMethod::firstOrCreate(
    ['description' => 'Nota de Crédito'], 
    ['code' => 'NC', 'currency_id' => $c->id, 'used_in_pos' => true, 'auto_declare' => true]
);
echo "Payment method created.\n";
