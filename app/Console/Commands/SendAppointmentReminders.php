<?php

namespace App\Console\Commands;

use App\Mail\AppointmentBookingMail;
use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-appointment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $reminders = DB::table('appointment_reminder_v')
                ->whereBetween('reminder_time', [
                    Carbon::now()->subMinutes(5)->format('Y-m-d\TH:i'),
                    Carbon::now()->addMinutes(5)->format('Y-m-d\TH:i')
                ])
                ->get();

            foreach ($reminders as $reminder) {
                try {
                    $name = $reminder->guest_name ?? $reminder->user_name;
                    $email = $reminder->guest_email ?? $reminder->user_email;

                    Mail::to($email)->queue(new AppointmentReminderMail($reminder, $name));
                }catch (\Throwable $th){
                    Log::error(print_r([$th->getMessage(), $th->getFile(), $th->getLine()], 1));
                }
            }
        }catch (\Throwable $th){
            Log::error(print_r([$th->getMessage(), $th->getFile(), $th->getLine()], 1));
        }
    }
}
