<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Request: {{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .info {
            background-color: #ffffff;
            border-left: 4px solid #3498db;
            padding: 10px;
            margin-bottom: 20px;
        }
        .message {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Support Request: {{ $subject }}</h1>
        <div class="info">
            <p><strong>From:</strong> {{ "$sender?->first_name $sender?->last_name" }}</p>
            <p><strong>Email:</strong> {{ $sender?->email }}</p>
        </div>
        <h2 style="margin-top: 3rem">Message:</h2>
        <div class="message">
            <p><?= $payload ?></p>
            <pre><?php var_dump($payload) ?></pre>
        </div>
    </div>
</body>
</html>