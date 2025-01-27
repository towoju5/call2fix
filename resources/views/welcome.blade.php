<html lang="en">

<head>
	<title>Call 2 Fix</title>
	<script src="https://cdn.tailwindcss.com/3.4.3"></script>
	<script>
		var code = `tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {}
        }
    }`
	</script>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 dark:bg-gray-800">
	<div class="min-h-screen flex flex-col justify-center items-center">
		<img src="https://www.svgrepo.com/show/426192/cogs-settings.svg" alt="Logo" class="mb-8 h-40">
		<h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-center text-gray-700 dark:text-white mb-4">Site is under maintenance</h1>
		<p class="text-center text-gray-500 dark:text-gray-300 text-lg md:text-xl lg:text-2xl mb-8">We're working hard to improve the user experience. Stay tuned!</p>
		<div class="flex space-x-4">
			<a href="#" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded dark:bg-gray-700 dark:hover:bg-gray-600">Contact Us</a>
			<a href="#" class="border-2 border-gray-800 text-black font-bold py-3 px-6 rounded dark:text-white dark:border-white">Reload</a>
		</div>
	</div>
	<script>
		window.Echo.channel('chat')
			.listen('.message.sent', (event) => {
				console.log('New message:', event.message);
				document.getElementById('messages').innerHTML += `<p>${event.message}</p>`;
			});
	</script>

</body>

</html>