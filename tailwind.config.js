/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        orange: {
          100: '#ffedd5',
          800: '#9a3412',
        },
        green: {
          100: '#dcfce7',
          800: '#166534',
        },
        emerald: {
          100: '#d1fae5',
          800: '#065f46',
        },
        purple: {
          100: '#f3e8ff',
          800: '#6b21a8',
        },
        blue: {
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
        },
        yellow: {
          100: '#fef3c7',
          800: '#92400e',
        },
        red: {
          100: '#fee2e2',
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
        },
        gray: {
          600: '#4b5563',
          700: '#374151',
        },
      },
    },
  },
  plugins: [],
}