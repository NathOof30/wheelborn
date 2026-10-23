import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                cosmos: {
                    deep: '#050a14',
                    DEFAULT: '#0a0e1a',
                    mid: '#0c1424',
                    surface: 'rgba(12, 17, 30, 0.55)',
                    card: 'rgba(15, 22, 40, 0.5)',
                },
                gold: {
                    DEFAULT: '#d4a843',
                    light: '#f0c96e',
                    pale: '#e8d5a3',
                    glow: 'rgba(212, 168, 67, 0.15)',
                },
                frost: {
                    DEFAULT: '#7dd3fc',
                    light: '#a5b4fc',
                },
            },
            spacing: {
                '18': '4.5rem',
                '22': '5.5rem',
                '26': '6.5rem',
                '30': '7.5rem',
                '34': '8.5rem',
            },
            borderRadius: {
                '3xl': '1.5rem',
                '4xl': '2rem',
            },
            backdropBlur: {
                'xs': '2px',
            },
        },
    },

    plugins: [forms],
};
