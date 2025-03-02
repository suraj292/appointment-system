<!DOCTYPE html>
<html>
<head>
    <title>Appointment Invitation</title>
</head>
<body>
<p><b>Dear {{ $user->name }},</b></p>
<p>Your appointment titled "{{ $appointment->title }}" has been successfully booked for [{{ $formattedDate }} at {{ $formattedTime }}].</p>
<p>Description: {{ $appointment->description }}</p>
<b>Guests:</b>
<br>
@foreach($guests as $guest)
    <p>{{ $guest['name'] }} - {{ $guest['email'] }}</p>
@endforeach
<p>
Looking forward to seeing you!
<br>
Thank you!
</p>
</body>
</html>
