<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-red-500 text-white text-center py-4">
                <h1 class="text-2xl font-bold">Error Occurred</h1>
            </div>
            <div class="p-6">
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Error!</p>
                    <p>{{ $message }}</p>
                </div>
                <div class="text-center">
                    <a href="{{ url()->previous() }}" class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                        Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
