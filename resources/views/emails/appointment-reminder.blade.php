<!DOCTYPE html>
<html>
<head>
    <title>Appointment Invitation</title>
</head>
<body>
<p><b>Dear {{ $name }},</b></p>
<p>This is a reminder for your upcoming appointment titled "{{ $reminder->title }}" scheduled for [{{ $formattedDate }} and {{ $formattedTime }}].</p>
<p>Thank you!</p>
</body>
</html>
