<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f6f6f6;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }
        .content {
            padding: 20px 0;
        }
        .greeting {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .intro-line {
            margin-bottom: 10px;
        }
        .action-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .outro {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                {{ $greeting }}
            </div>

            @foreach ($introLines as $line)
                <p class="intro-line">{{ $line }}</p>
            @endforeach

            @if (isset($actionText))
                <div style="text-align: center;">
                    <a href="{{ $actionUrl }}" class="action-button">
                        {{ $actionText }}
                    </a>
                </div>
            @endif

            <div class="outro">
                @foreach ($outroLines as $line)
                    <p>{{ $line }}</p>
                @endforeach
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
