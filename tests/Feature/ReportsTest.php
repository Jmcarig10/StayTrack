<?php

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Reports;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeReportFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $tenantUser = User::factory()->create(['role' => 'tenant', 'status' => 'approved']);

    $tenant = Tenant::create([
        'user_id' => $tenantUser->id,
        'first_name' => 'Report',
        'last_name' => 'Tenant',
        'phone' => '09170000000',
        'email' => 'report.tenant@example.test',
        'status' => 'active',
    ]);

    $apartment = Apartment::create([
        'unit_number' => 'RPT-101',
        'unit_type' => '2-Bedroom',
        'building_name' => 'Report Hall',
        'property_address' => '1 Test Street',
        'floor' => 1,
        'bedrooms' => 2,
        'bathrooms' => 1,
        'monthly_rent' => 12000,
        'security_deposit' => 24000,
        'status' => 'occupied',
    ]);

    $lease = Lease::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonths(11),
        'monthly_rent' => 12000,
        'security_deposit' => 24000,
        'downpayment' => 24000,
        'status' => 'active',
    ]);

    $bill = Bill::create([
        'lease_id' => $lease->id,
        'billing_month' => now()->startOfMonth(),
        'due_date' => now()->addDays(5),
        'amount' => 12000,
        'late_fee' => 0,
        'status' => 'partially_paid',
    ]);

    Payment::create([
        'bill_id' => $bill->id,
        'amount_paid' => 6000,
        'payment_date' => now(),
        'payment_method' => 'gcash',
        'status' => 'verified',
        'received_by' => $admin->id,
    ]);

    MaintenanceRequest::create([
        'tenant_id' => $tenant->id,
        'apartment_id' => $apartment->id,
        'title' => 'Leaking faucet',
        'category' => 'plumbing',
        'priority' => 'high',
        'description' => 'Kitchen sink drips continuously.',
        'status' => 'pending',
    ]);

    return compact('admin', 'tenantUser', 'tenant', 'apartment', 'lease', 'bill');
}

test('admin can open the reports page', function () {
    ['admin' => $admin] = makeReportFixture();

    $this->actingAs($admin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Reports')
        ->assertSee('Payments &amp; revenue', false);
});

test('tenants cannot open reports or exports', function () {
    ['tenantUser' => $tenantUser] = makeReportFixture();

    $this->actingAs($tenantUser)->get(route('reports.index'))->assertForbidden();
    $this->actingAs($tenantUser)->get(route('reports.export', ['report' => 'payments']))->assertForbidden();
});

test('the reports page renders filtered rows', function () {
    ['admin' => $admin] = makeReportFixture();

    $this->actingAs($admin)
        ->get(route('reports.index', [
            'report' => 'bills',
            'building' => 'Report Hall',
            'status' => 'partially_paid',
        ]))
        ->assertOk()
        ->assertSee('Report Tenant')
        ->assertSee('RPT-101')
        ->assertSee('Partially paid');
});

test('every report type downloads a real xlsx file', function () {
    ['admin' => $admin] = makeReportFixture();

    foreach (array_keys(Reports::TYPES) as $report) {
        $filename = 'staytrack-'.$report.'-'.now()->format('Y-m-d').'.xlsx';

        $response = $this->actingAs($admin)->get(route('reports.export', ['report' => $report]));

        $response->assertOk()->assertDownload($filename);

        expect($response->headers->get('content-type'))
            ->toContain('spreadsheetml');
    }
});

test('every report type downloads a real pdf file', function () {
    ['admin' => $admin] = makeReportFixture();

    foreach (array_keys(Reports::TYPES) as $report) {
        $filename = 'staytrack-'.$report.'-'.now()->format('Y-m-d').'.pdf';

        $response = $this->actingAs($admin)->get(route('reports.export', [
            'report' => $report,
            'format' => 'pdf',
        ]));

        $response->assertOk()->assertDownload($filename);

        expect($response->headers->get('content-type'))
            ->toContain('application/pdf');
    }
});

test('unknown report types return not found', function () {
    ['admin' => $admin] = makeReportFixture();

    $this->actingAs($admin)
        ->get(route('reports.export', ['report' => 'unknown']))
        ->assertNotFound();
});

test('list pages expose pdf and excel exports through the shared chooser', function () {
    ['admin' => $admin] = makeReportFixture();

    $this->actingAs($admin)
        ->get(route('payments.index'))
        ->assertOk()
        ->assertSee('data-export-pdf="'.route('reports.export', ['report' => 'payments', 'format' => 'pdf']).'"', false)
        ->assertSee('data-export-excel="'.route('reports.export', ['report' => 'payments', 'format' => 'excel']).'"', false)
        ->assertSee('PDF document')
        ->assertSee('Excel workbook');

    $this->actingAs($admin)
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertSee('data-export-pdf="'.route('reports.export', ['report' => 'tenants', 'format' => 'pdf']).'"', false)
        ->assertSee('data-export-excel="'.route('reports.export', ['report' => 'tenants', 'format' => 'excel']).'"', false);
});
