<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Users;
use App\Models\Government_Offices;
use App\Models\Services;
use App\Models\ServiceRequests;
use App\Models\Feddback;
use App\Models\Municipality;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

// Create or find an office manager user
$officeUser = Users::first();
if (! $officeUser) {
    $officeUser = new Users();
    $officeUser->username = 'office_manager';
    $officeUser->email = 'office@example.test';
    $officeUser->password = bcrypt('password');
    $officeUser->role = 'office_user';
    $officeUser->save();
    echo "Created office user id={$officeUser->id}\n";
} else {
    echo "Using existing user id={$officeUser->id}\n";
}

// Ensure a municipality exists (required by government_offices table)
$municipality = Municipality::first();
if (! $municipality) {
    $municipality = Municipality::create(['name' => 'Test Municipality', 'city' => 'Test City']);
    echo "Created municipality id={$municipality->id}\n";
} else {
    echo "Using existing municipality id={$municipality->id}\n";
}

// Create a government office (avoid columns that may not exist in schema)
$office = Government_Offices::create([
    'name' => 'Test Office',
    'address' => '123 Test St',
    'municipality_id' => $municipality->id,
    'contact_info' => null,
    'latitude' => 0.0000000,
    'longitude' => 0.0000000,
    'user_id' => $officeUser->id,
]);

echo "Created office id={$office->id}\n";

// Ensure a service category exists
$category = ServiceCategory::first();
if (! $category) {
    $category = ServiceCategory::create(['name' => 'General', 'description' => 'Test category']);
    echo "Created service category id={$category->id}\n";
} else {
    echo "Using existing service category id={$category->id}\n";
}

// Create a service
$service = Services::create([
    'office_id' => $office->id,
    'name' => 'Test Service',
    'category_id' => $category->id,
    'price' => 0,
    'duration' => 0,
    'required_documents' => json_encode([]),
]);

echo "Created service id={$service->id}\n";

// Create a citizen user
$citizen = Users::where('email', 'citizen@example.test')->first();
if (! $citizen) {
    $citizen = new Users();
    $citizen->username = 'test_citizen';
    $citizen->email = 'citizen@example.test';
    $citizen->password = bcrypt('password');
    $citizen->role = 'citizen';
    $citizen->save();
    echo "Created citizen id={$citizen->id}\n";
} else {
    echo "Using existing citizen id={$citizen->id}\n";
}

// Create a completed service request
$serviceRequest = ServiceRequests::create([
    'citizen_id' => $citizen->id,
    'service_id' => $service->id,
    'status' => 'Completed',
    'qr_code' => (string) Str::uuid(),
    'appointment_id' => null,
]);

echo "Created service_request id={$serviceRequest->id}\n";

// Create feedback
$feedback = Feddback::create([
    'service_request_id' => $serviceRequest->id,
    'citizen_id' => $citizen->id,
    'rating' => 4,
    'comment' => 'Automated sample feedback for testing.'
]);

if ($feedback) {
    echo "Created feedback id={$feedback->id} for request {$serviceRequest->id}\n";
} else {
    echo "Failed to create feedback.\n";
}
