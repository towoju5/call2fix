/** @type {import('tailwindcss').Config} */
export default {
	content: [
		"./resources/**/*.blade.php",
		"./resources/**/*.js",
		"./resources/**/*.vue",
	],
	darkMode: 'class', // Enable class-based dark mode
	theme: {
		extend: {
			colors: {
				light: {
					primary: '#ffffff',
					secondary: '#f3f4f6',
					text: '#1f2937'
				},
				dark: {
					primary: '#1f2937',
					secondary: '#374151',
					text: '#f3f4f6'
				}
			}
		},
	},
	plugins: [
	],
}
