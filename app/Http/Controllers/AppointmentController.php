<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentBookingCancellationMail;
use App\Mail\AppointmentBookingMail;
use App\Models\Appointment;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Stevebauman\Location\Facades\Location;
use Throwable;
use Torann\GeoIP\GeoIP;

class AppointmentController extends Controller
{
    public function index()
    {
        $ip = file_get_contents('https://api64.ipify.org'); // Get the client's IP
        $timezone = Location::get($ip);
        $appointments = auth()->user()->appointments()
            ->with('guestInvitations')
            ->orderBy('date_time')
            ->get()
            ->map(function ($appointment) use ($timezone) {
                $appointment->date = Carbon::parse($appointment->date_time)->format('d M Y');
                $appointment->time = Carbon::parse($appointment->date_time)->format('h:i A');
                $appointment->timezone = $timezone->timezone;
                return $appointment;
            });

        return Inertia::render('Appointment/Index', ['appointments' => $appointments ?? []]);
    }

    public function create()
    {
        return Inertia::render('Appointment/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'appointment_date' => 'required|date|after:today',
        ]);

        if (!$this->isWeekday($request->appointment_date)) {
            return Inertia::render('Appointment/Create', ['error' => 'Appointment can only be scheduled on weekdays.']);
        }

        try {
            $appointment = $this->createAppointment($request);
            $this->sendAppointmentMail($appointment, $request->guests);

            return Inertia::render('Appointment/Index', ['success' => 'Appointment created successfully.']);
        } catch (Throwable $th) {
            Log::info(print_r([$th->getMessage(), $th->getFile(), $th->getLine()], true));
            return redirect()->back()->with('error', 'Failed to create appointment.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $appointment = Appointment::with('guestInvitations')->find($id);

            if ($request->status === 'cancelled') {
                $currentDateTime = Carbon::now();
                $appointmentDateTime = Carbon::parse($appointment->date_time);

                if ($currentDateTime->diffInMinutes($appointmentDateTime, false) <= 30) {
                    $this->cancelAppointment($appointment);
                    $this->sendCancellationMail($appointment);

                    return response()->json(['message' => 'Appointment cancelled successfully.'], 200);
                } else {
                    return response()->json(['error' => 'Appointments can only be cancelled at least 30 minutes before the scheduled time.'], 400);
                }
            }

//            $this->sendCancellationMail($appointment);

//            return Inertia::render('Appointment/Index', ['appointment' => $appointment]);
            return response()->json(['message' => 'Appointment cancelled successfully.'], 200);
        } catch (Throwable $th) {
            Log::info(print_r([$th->getMessage(), $th->getFile(), $th->getLine()], true));
            return redirect()->back()->with('error', 'Failed to cancel appointment.');
        }
    }

    private function isWeekday($date)
    {
        $day = date('l', strtotime($date));
        return in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
    }

    private function createAppointment(Request $request)
    {

        $ip = file_get_contents('https://api64.ipify.org'); // Get the client's IP
        $timezone = Location::get($ip);

        $appointment = auth()->user()->appointments()->create([
            'title' => $request->title,
            'description' => $request->description,
            'date_time' => $request->appointment_date,
            'timezone' => $timezone->timezone,
        ]);

        foreach ($request->guests as $guest) {
            $appointment->guestInvitations()->create([
                'name' => $guest['name'],
                'email' => $guest['email'],
                'status' => 'Confirmed',
            ]);
        }

        return $appointment;
    }

    private function sendAppointmentMail($appointment, $guests)
    {
        $emails = array_merge([auth()->user()->email], array_column($guests, 'email'));
        Mail::to($emails)->queue(new AppointmentBookingMail($guests, $appointment, auth()->user()));
    }

    private function cancelAppointment($appointment)
    {
        $appointment->update(['status' => 'Cancelled']);
        $appointment->guestInvitations()->update(['status' => 'Declined']);
    }

    private function sendCancellationMail($appointment)
    {
        $emails = array_merge([auth()->user()->email], $appointment->guestInvitations->pluck('email')->toArray());
        Mail::to($emails)->queue(new AppointmentBookingCancellationMail(null, $appointment, auth()->user()));
    }

    public function showUserPendingTasks(Request $request)
    {
        $user = \App\Models\User::select('name', 'pending_tasks')->withCount('pendingTasks')->find($request->user_id);

    }
}
