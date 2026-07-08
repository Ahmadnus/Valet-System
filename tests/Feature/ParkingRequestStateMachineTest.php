<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Car;
use App\Models\Cartype;
use App\Models\Customer;
use App\Models\Location;
use App\Models\LocationAgent;
use App\Models\ParkingSlot;
use App\Models\Request as UserRequest;
use App\Models\User;
use App\Services\Requests\ParkingRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParkingRequestStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $agent;
    private Location $location;
    private ParkingSlot $slot;
    private Car $car;

    protected function setUp(): void
    {
        parent::setUp();

        // The transitions emit domain events whose listeners hit Firebase;
        // faking events keeps these unit-style tests isolated.
        Event::fake();

        Role::firstOrCreate(['name' => 'valet_agent']);
        Role::firstOrCreate(['name' => 'valet_customer']);

        $this->agent = $this->makeUser(Agent::class, 'valet_agent', '+963911111111');
        $this->customer = $this->makeUser(Customer::class, 'valet_customer', '+963922222222');

        $this->location = Location::create([
            'latitude' => 33.5138,
            'longitude' => 36.2765,
            'cost' => 10,
            'host_id' => $this->agent->id,
        ]);

        $this->slot = ParkingSlot::create([
            'location_id' => $this->location->id,
            'slot_number' => 'A1',
            'status' => 'available',
        ]);

        $this->car = Car::create([
            'user_id' => $this->customer->id,
            'plate_number' => 'DAM-1234',
            'cartypeable_type' => Cartype::class,
            'cartypeable_id' => 1,
        ]);
    }

    private function makeUser(string $userableType, string $role, string $phone): User
    {
        $userable = $userableType::create([]);
        $user = User::create([
            'name' => class_basename($userableType),
            'phone_number' => $phone,
            'password' => bcrypt('secret123'),
            'userable_id' => $userable->id,
            'userable_type' => $userableType,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makeRequest(string $status, ?int $valetAgentId = null): UserRequest
    {
        return UserRequest::create([
            'user_id' => $this->customer->id,
            'car_id' => $this->car->id,
            'parking_slot_id' => $this->slot->id,
            'location_id' => $this->location->id,
            'status' => $status,
            'valet_agent_id' => $valetAgentId,
        ]);
    }

    private function setAgentAvailability(string $status): void
    {
        LocationAgent::updateOrCreate(
            ['agent_id' => $this->agent->id, 'location_id' => $this->location->id],
            ['status' => $status],
        );
    }

    private function service(): ParkingRequestService
    {
        return app(ParkingRequestService::class);
    }

    private function coords(): array
    {
        return ['latitude' => 33.5138, 'longitude' => 36.2765];
    }

    // --- Happy-path transitions -------------------------------------------

    public function test_available_agent_can_accept_a_pending_request(): void
    {
        $this->actingAs($this->agent);
        $this->setAgentAvailability('available');
        $request = $this->makeRequest('pending');

        $this->service()->changeParkingRequestStatus($request->id, 'accepted', $this->coords());

        $this->assertSame('accepted', $request->fresh()->status);
        $this->assertSame($this->agent->id, $request->fresh()->valet_agent_id);
        $this->assertSame('occupied', LocationAgent::where('agent_id', $this->agent->id)->value('status'));
    }

    public function test_agent_can_start_an_accepted_request_they_own(): void
    {
        $this->actingAs($this->agent);
        $this->setAgentAvailability('occupied');
        $request = $this->makeRequest('accepted', $this->agent->id);

        $this->service()->changeParkingRequestStatus($request->id, 'in_progress', []);

        $this->assertSame('in_progress', $request->fresh()->status);
    }

    public function test_agent_can_deliver_a_preparing_request(): void
    {
        $this->actingAs($this->agent);
        $this->setAgentAvailability('occupied');
        $request = $this->makeRequest('preparing', $this->agent->id);

        $this->service()->changeParkingRequestStatus($request->id, 'delivering', $this->coords());

        $this->assertSame('delivering', $request->fresh()->status);
    }

    public function test_agent_can_complete_a_delivering_request_with_matching_plate(): void
    {
        $this->actingAs($this->agent);
        $this->setAgentAvailability('in_way');
        $request = $this->makeRequest('delivering', $this->agent->id);

        $this->service()->changeParkingRequestStatus($request->id, 'completed', [
            'plate_number' => 'DAM-1234',
        ]);

        $this->assertSame('completed', $request->fresh()->status);
    }

    public function test_agent_can_cancel_a_pending_request(): void
    {
        $this->actingAs($this->agent);
        $request = $this->makeRequest('pending');

        $this->service()->changeParkingRequestStatus($request->id, 'canceled', ['reason' => 'no show']);

        $this->assertSame('canceled', $request->fresh()->status);
    }

    // --- Illegal transitions ----------------------------------------------

    public function test_cannot_start_a_request_that_is_not_accepted(): void
    {
        $this->actingAs($this->agent);
        $request = $this->makeRequest('pending', $this->agent->id);

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'in_progress', []);
    }

    public function test_cannot_deliver_a_request_that_is_not_preparing(): void
    {
        $this->actingAs($this->agent);
        $request = $this->makeRequest('accepted', $this->agent->id);

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'delivering', $this->coords());
    }

    public function test_cannot_complete_a_request_that_is_not_delivering(): void
    {
        $this->actingAs($this->agent);
        $request = $this->makeRequest('preparing', $this->agent->id);

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'completed', ['plate_number' => 'DAM-1234']);
    }

    public function test_cannot_complete_with_a_mismatched_plate(): void
    {
        $this->actingAs($this->agent);
        $request = $this->makeRequest('delivering', $this->agent->id);

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'completed', ['plate_number' => 'WRONG-0000']);
    }

    public function test_cannot_cancel_a_request_that_is_not_pending(): void
    {
        $this->actingAs($this->agent);
        $request = $this->makeRequest('accepted', $this->agent->id);

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'canceled', ['reason' => 'x']);
    }

    public function test_unknown_status_is_rejected(): void
    {
        $this->actingAs($this->agent);
        $request = $this->makeRequest('pending');

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'teleported', []);
    }

    // --- Authorization guards ---------------------------------------------

    public function test_a_customer_cannot_accept_a_request(): void
    {
        $this->actingAs($this->customer);
        $this->setAgentAvailability('available');
        $request = $this->makeRequest('pending');

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'accepted', $this->coords());
    }

    public function test_an_unavailable_agent_cannot_accept(): void
    {
        $this->actingAs($this->agent);
        $this->setAgentAvailability('occupied');
        $request = $this->makeRequest('pending');

        $this->expectException(\Exception::class);
        $this->service()->changeParkingRequestStatus($request->id, 'accepted', $this->coords());
    }
}
