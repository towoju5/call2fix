<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #007bff, #00c6ff);
            text-align: center;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .loading-icon {
            font-size: 50px;
            color: #007bff;
            margin: 20px 0;
            animation: spin 1s linear infinite;
        }

        h2 {
            font-size: 22px;
            color: #333;
        }

        p {
            font-size: 16px;
            color: #555;
            margin-top: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Processing Your Payment...</h2>
        <div class="loading-icon">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Please wait while we securely process your transaction.</p>
    </div>
</body>
</html>
