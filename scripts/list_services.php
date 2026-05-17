<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Services;

$services = Services::with('office')->take(50)->get();
if ($services->isEmpty()) {
    echo "No services found.\n";
    exit(0);
}
foreach ($services as $s) {
    $officeUserId = $s->office ? ($s->office->user_id ?? 'N/A') : 'N/A';
    echo "id={$s->id} name={$s->name} office_id={$s->office_id} office_user_id={$officeUserId}\n";
}
