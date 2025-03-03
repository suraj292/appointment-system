<!DOCTYPE html>
<html>
<head>
    <title>Appointment Cancellation</title>
</head>
<body>
<p><b>Dear {{ $user->name }},</b></p>
<p>The appointment titled "{{ $appointment->title }}" scheduled for [{{ $formattedDate }} and {{ $formattedTime }}] has been cancelled.</p>
<p>We regret any inconvenience caused.</p>
<p>Thank you!</p>
</body>
</html>
