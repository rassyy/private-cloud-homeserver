import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                primary: '#3b82f6',
                'primary-container': '#dbeafe',
                'on-primary-container': '#1e3a8a',
                'on-primary': '#ffffff',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                geist: ['Geist', 'sans-serif'],
            },
            spacing: {
                'sidebar-width': '280px',
                'container-max': '1440px',
                'gutter': '24px',
            },
        },
    },

    plugins: [forms],
};
