<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ServiceRequests;

$requests = ServiceRequests::with(['service.office'])->where('status', 'Completed')->orderBy('id', 'desc')->take(20)->get();

if ($requests->isEmpty()) {
    echo "No completed requests found.\n";
    exit(0);
}

foreach ($requests as $r) {
    $officeUserId = $r->service && $r->service->office ? ($r->service->office->user_id ?? 'N/A') : 'N/A';
    $serviceName = $r->service->name ?? 'N/A';
    echo "id={$r->id} citizen_id={$r->citizen_id} service_id={$r->service_id} service={$serviceName} office_user_id={$officeUserId} created_at={$r->created_at}\n";
}
