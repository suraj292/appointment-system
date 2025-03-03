<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentBookingCancellationMail;
use App\Mail\AppointmentBookingMail;
use App\Models\Appointment;
use Carbon\Carbon;
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
        $timezone = 'Asia/Kolkata';

        $appointments = auth()->user()->appointments()
            ->with('guestInvitations')
            ->orderBy('date_time')
            ->get()->map(function ($appointment) use ($timezone) {
                $formattedDate = Carbon::parse($appointment->date_time)->format('d M Y');
                $formattedTime = Carbon::parse($appointment->date_time)->format('h:i A');
                $appointment->date = $formattedDate;
                $appointment->time = $formattedTime;

                $carbonDate = Carbon::createFromFormat('Y-m-d\TH:i', $appointment->timezone, $timezone);
                $appointment->timezone = $carbonDate->getOffsetString();

                return $appointment;
            });

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
                    'status' => 'Confirmed',
                ]);
                $emails[] = $guest['email'];
            }

            Mail::to($emails)->queue(new AppointmentBookingMail($guests, $appointment, $user));

            return Inertia::render('Appointment/Index', [
                'success' => 'Appointment created successfully.'
            ]);

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
        try {
            if ($request->status === 'cancelled'){
                $appointment = Appointment::with('guestInvitations')->find($id);
                $appointment->status = 'Cancelled';
                $appointment->save();

                $appointment->guestInvitations()->update([
                    'status' => 'Declined'
                ]);
            }

            $user = auth()->user();

            $emails = [];
            $emails[] = $user->email;
            if (isset($appointment->guestInvitations) && $appointment->guestInvitations->count() > 0) {
                foreach ($appointment->guestInvitations as $guest) {
                    $emails[] = $guest->email;
                }
            }

            Mail::to($emails)->queue(new AppointmentBookingCancellationMail(null, $appointment, $user));

            return Inertia::render('Appointment/Index', [
                'appointment' => $appointment,
            ]);

        }catch (Throwable $th) {
            Log::info(print_r($th->getMessage(), true));
            return redirect()->back()->with('error', 'Failed to cancel appointment.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
