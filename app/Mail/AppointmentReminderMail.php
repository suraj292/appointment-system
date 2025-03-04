<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reminder;
    public $name;

    /**
     * Create a new message instance.
     */
    public function __construct($reminder, $name)
    {
        $this->reminder = $reminder;
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Reminder',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $formattedDate = Carbon::parse($this->reminder->date_time)->format('d M Y');
        $formattedTime = Carbon::parse($this->reminder->date_time)->format('h:i A');

        return new Content(
            view: 'emails.appointment-reminder',
            with: [
                'reminder' => $this->reminder,
                'name' => $this->name,
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
