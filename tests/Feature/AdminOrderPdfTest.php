<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminOrderPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function getHeaders(): array
    {
        $token = JWTAuth::fromUser($this->admin);
        return ['Authorization' => "Bearer $token"];
    }

    public function test_admin_can_download_sales_report_pdf(): void
    {
        $response = $this->get('/api/admin/reports/sales?format=pdf', $this->getHeaders());
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename=sales-report.pdf', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_download_order_print_pdf(): void
    {
        $order = Order::factory()->create(['user_id' => $this->admin->id]);
        
        $response = $this->get("/api/admin/orders/{$order->id}/print-file?format=pdf", $this->getHeaders());
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename=order-'.$order->id.'.pdf', $response->headers->get('content-disposition'));
    }
}
