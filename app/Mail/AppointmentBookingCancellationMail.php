<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentBookingCancellationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $appointment;
    public $guests;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($guests, $appointment, $user)
    {
        $this->appointment = $appointment;
        $this->guests = $guests;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Booking Cancellation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $formattedDate = Carbon::parse($this->appointment->date_time)->format('d M Y');
        $formattedTime = Carbon::parse($this->appointment->date_time)->format('h:i A');

        return new Content(
            view: 'emails.appointment-cancellation',
            with: [
                'appointment' => $this->appointment,
                'guests' => $this->guests,
                'user' => $this->user,
                'formattedDate' => $formattedDate,
                'formattedTime' => $formattedTime,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
