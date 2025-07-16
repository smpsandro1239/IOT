/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.html",
    "./js/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'barrier-blue': '#2563eb',
        'barrier-green': '#16a34a',
        'barrier-red': '#dc2626',
        'barrier-yellow': '#ca8a04'
      },
      animation: {
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'bounce-slow': 'bounce 2s infinite',
        'spin-slow': 'spin 3s linear infinite'
      }
    },
  },
  plugins: [],
}
