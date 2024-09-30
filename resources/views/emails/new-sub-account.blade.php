<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sub-Account Created</title>
</head>
<body>
    <h1>Welcome to Your New Sub-Account</h1>
    
    <p>Hello {{ $name }},</p>
    
    <p>Your new sub-account has been created successfully. Here are your account details:</p>
    
    <ul>
        <li><strong>Name:</strong> {{ $name }}</li>
        <li><strong>Email:</strong> {{ $email }}</li>
        <li><strong>Role:</strong> {{ $role }}</li>
        <li><strong>Temporary Password:</strong> {{ $password }}</li>
    </ul>
    
    <p>Please log in and change your password as soon as possible.</p>
    
    <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
    
    <p>Best regards,<br>The Call2Fix Team</p>
</body>
</html>
