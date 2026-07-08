<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

echo "=== Payment System Diagnostic ===\n\n";

// Auth as super-admin
$user = User::where('email', 'superadmin@example.com')->first();
auth()->login($user);
echo "✓ Logged in as: {$user->name} ({$user->email})\n\n";

// Find a member who hasn't paid yet
// First, let's look at members who have NO payments at all
$membersWithPayments = Payment::select('member_id')->distinct()->pluck('member_id')->toArray();
echo "Members with any payment: " . count($membersWithPayments) . "\n";

$member = Member::with('club')
    ->whereNotIn('id', $membersWithPayments)
    ->first();

if (!$member) {
    // All members have payments, create a fresh member
    $member = Member::first();
    echo "All members have payments, using first member: {$member->name}\n";
} else {
    echo "Using member with no payments: {$member->name} (ID: {$member->id})\n";
}

$currentYear = (int) now()->year;
$testYear = $currentYear; // Use current year

// Clear any test payment for this member
Payment::where('member_id', $member->id)->where('year_paid', $testYear)->delete();

// Simulate the exact form submission by swapping the global request
echo "\n--- Test: Submit payment via form submission ---\n";
echo "Member ID: {$member->id}, Year: {$testYear}\n";

try {
    // Create the request and set it as the global request
    $request = new Request();
    $request->setMethod('POST');
    $request->merge([
        'member_id' => (string) $member->id,
        'year_paid' => (string) $testYear,
    ]);
    
    // Replace the global request
    $app->instance('request', $request);
    request()->setUserResolver(fn() => $user);
    
    $controller = $app->make(\App\Http\Controllers\Admin\PaymentController::class);
    $response = $controller->store($request);
    
    echo "✓ Controller returned successfully\n";
    
    // Verify payment was created
    $payment = Payment::where('member_id', $member->id)
        ->where('year_paid', $testYear)
        ->first();
    
    if ($payment) {
        echo "✓ Payment FOUND in database!\n";
        echo "  ID: {$payment->id}\n";
        echo "  Year: {$payment->year_paid}\n";
        echo "  Date: {$payment->date_paid}\n";
        echo "  Created at: {$payment->created_at}\n";
    } else {
        echo "✗ Payment NOT found in database after controller call!\n";
        
        // Debug: check all payments
        $allPayments = Payment::where('member_id', $member->id)->get();
        echo "  All payments for member {$member->id}: " . $allPayments->count() . "\n";
        foreach ($allPayments as $p) {
            echo "  - ID:{$p->id}, Year:{$p->year_paid}\n";
        }
    }
    
    // Test duplicate
    echo "\n--- Test: Submit duplicate (should fail) ---\n";
    $request2 = new Request();
    $request2->setMethod('POST');
    $request2->merge([
        'member_id' => (string) $member->id,
        'year_paid' => (string) $testYear,
    ]);
    $app->instance('request', $request2);
    request()->setUserResolver(fn() => $user);
    
    try {
        $controller->store($request2);
        echo "✗ BUG: Duplicate payment was accepted!\n";
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "✓ Correctly rejected: " . json_encode($e->errors()) . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ FAILED: " . get_class($e) . "\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (method_exists($e, 'errors')) {
        echo "  Validation errors: " . json_encode($e->errors()) . "\n";
    }
}

echo "\n=== Payment System Test Complete ===\n";
echo "If payment was created successfully above, the controller works.\n";
echo "If not, the issue is highlighted above.\n";
