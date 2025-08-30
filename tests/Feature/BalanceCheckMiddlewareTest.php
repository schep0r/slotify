<?php

namespace Tests\Feature;

use App\Http\Middleware\BalanceCheckMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BalanceCheckMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private BalanceCheckMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new BalanceCheckMiddleware();
    }

    public function test_allows_request_when_sufficient_balance()
    {
        $user = User::factory()->create(['balance' => 100.00]);
        $this->actingAs($user);

        $request = Request::create('/api/v1/game/spin', 'POST', ['betAmount' => 10.00]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_request_when_insufficient_balance()
    {
        $user = User::factory()->create(['balance' => 5.00]);
        $this->actingAs($user);

        $request = Request::create('/api/v1/game/spin', 'POST', ['betAmount' => 10.00]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('INSUFFICIENT_BALANCE', $data['code']);
        $this->assertEquals(10.00, $data['required']);
        $this->assertEquals(5.00, $data['available']);
    }

    public function test_skips_check_for_non_game_routes()
    {
        $user = User::factory()->create(['balance' => 0.00]);
        $this->actingAs($user);

        $request = Request::create('/api/v1/user/profile', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_handles_different_bet_amount_field_names()
    {
        $user = User::factory()->create(['balance' => 5.00]);
        $this->actingAs($user);

        // Test bet_amount field
        $request = Request::create('/api/v1/game/spin', 'POST', ['bet_amount' => 10.00]);
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });
        $this->assertEquals(400, $response->getStatusCode());

        // Test amount field
        $request = Request::create('/api/v1/game/spin', 'POST', ['amount' => 10.00]);
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_handles_multiple_bets_array()
    {
        $user = User::factory()->create(['balance' => 15.00]);
        $this->actingAs($user);

        $request = Request::create('/api/v1/game/spin', 'POST', [
            'bets' => [
                ['amount' => 5.00],
                ['amount' => 10.00],
                ['amount' => 5.00] // Total: 20.00
            ]
        ]);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(20.00, $data['required']);
    }
}