<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentBookingMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Throwable;
use function Laravel\Prompts\error;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//        $appointments = auth()->user()->appointments()
//            ->with('guestInvitations')
//            ->orderBy('start_time')
//            ->get();

        return Inertia::render('Appointment/Index', [
            'appointments' => $appointments ?? [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Appointment/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'appointment_date' => 'required|date',
            ]);

            $appointment_date = date('l', strtotime($request->appointment_date));
            if (!in_array($appointment_date, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) {
                return Inertia::render('Appointment/Create', [
                    'error' => 'Appointment can only be scheduled on weekdays.'
                ]);
            }

            $appointment = auth()->user()->appointments()->create([
                'title' => $request->title,
                'description' => $request->description,
                'date_time' => $request->appointment_date,
                'timezone' => $request->appointment_date,
            ]);

            $user = auth()->user();

            $emails = [];
            $emails[] = $user->email;

            $guests = $request->guests;
            foreach ($guests as $guest) {
                $appointment->guestInvitations()->create([
                    'name' => $guest['name'],
                    'email' => $guest['email'],
                ]);
                $emails[] = $guest['email'];
            }

            Mail::to($emails)->queue(new AppointmentBookingMail($guests, $appointment, $user));

//            Mail::to()->queue(new AppointmentBookingMail($guests, $appointment, $user));

//            return redirect()->route('appointment.index')->with('success', 'Appointment created successfully.');

        }catch (Throwable $th) {

            Log::info(print_r($th->getMessage(), true));
            return redirect()->back()->with('error', 'Failed to create appointment.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
