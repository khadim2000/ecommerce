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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                military: {
                    50: '#f0f9f0',
                    100: '#dcf2dc',
                    200: '#b8e5b8',
                    300: '#8dd18d',
                    400: '#5bb85b',
                    500: '#3a9d3a',
                    600: '#2d7d2d',
                    700: '#256325',
                    800: '#1f4f1f',
                    900: '#1a3f1a',
                    950: '#0d1f0d',
                },
            },
            backgroundImage: {
                'military-gradient': 'var(--gradient-hero)',
                'military-dark': 'var(--gradient-dark)',
                'military-card': 'var(--gradient-card)',
                'military-button': 'var(--gradient-button)',
                'military-accent': 'var(--gradient-accent)',
            },
            boxShadow: {
                'military': '0 4px 14px 0 rgba(45, 125, 45, 0.15)',
                'military-lg': '0 10px 25px 0 rgba(45, 125, 45, 0.2)',
            },
        },
    },

    plugins: [forms],
};
