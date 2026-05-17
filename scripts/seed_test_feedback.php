<?php
// One-off seeder: creates a test feedback for the first Completed request with no feedback
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ServiceRequests;
use App\Models\Feddback;

$req = ServiceRequests::where('status', 'Completed')
    ->whereDoesntHave('feedbacks')
    ->first();

if (! $req) {
    echo "No completed service request without feedback found.\n";
    exit(0);
}

$exists = Feddback::where('service_request_id', $req->id)->first();
if ($exists) {
    echo "Feedback already exists for request {$req->id}\n";
    exit(0);
}

$feedback = Feddback::create([
    'service_request_id' => $req->id,
    'citizen_id' => $req->citizen_id,
    'rating' => 5,
    'comment' => 'Automated test feedback created by developer script.'
]);

if ($feedback) {
    echo "Created feedback id={$feedback->id} for service_request_id={$req->id}\n";
} else {
    echo "Failed to create feedback.\n";
}
