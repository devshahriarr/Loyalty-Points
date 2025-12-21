<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Business Approved</title>
</head>
<body>

<p>Hello,</p>

<p>🎉 <strong>Your business has been approved!</strong></p>

<p>You can now log in to your dashboard using the details below:</p>

<ul>
    <li><strong>Name:</strong> {{ $businessName }}</li>
    <li><strong>Email:</strong> {{ $email }}</li>
    <li><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></li>
</ul>

<p>Please use the password you registered with.</p>

<p>If you face any issues, contact support.</p>

<br>

<p>Best regards,<br>
<strong>Loyalty Platform Admin</strong></p>

</body>
</html>
