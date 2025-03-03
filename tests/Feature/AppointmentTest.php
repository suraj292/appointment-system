<?php

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

it('can create an appointment', function () {
    $response = $this->post('/appointments', [
        'title' => 'Test Appointment',
        'description' => 'This is a test appointment.',
        'appointment_date' => now()->addDays(1)->toDateTimeString(),
        'guests' => [
            ['name' => 'Guest 1', 'email' => 'guest1@example.com'],
            ['name' => 'Guest 2', 'email' => 'guest2@example.com'],
        ],
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('appointments', ['title' => 'Test Appointment']);
});

it('can cancel an appointment', function () {
    $appointment = Appointment::factory()->create(['user_id' => $this->user->id]);

    $response = $this->put("/appointments/{$appointment->id}", [
        'status' => 'cancelled',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'Cancelled']);
});
