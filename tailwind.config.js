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
                sans: ['Crimson Pro', ...defaultTheme.fontFamily.serif],
                body: ['Crimson Pro', ...defaultTheme.fontFamily.serif],
                display: ['Cinzel', ...defaultTheme.fontFamily.serif],
                gothic: ['UnifrakturCook', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                parchment: {
                    bright: '#FBF6EA',
                    light: '#F5EDD8',
                    DEFAULT: '#EBE0C8',
                    shade: '#DCCEA9',
                    border: '#C4B186',
                },
                ink: {
                    DEFAULT: '#2A1D11',
                    sepia: '#52381F',
                    muted: '#5C4328',
                },
                brass: {
                    light: '#DFC06E',
                    DEFAULT: '#B8923A',
                    dark: '#6E4F0E',
                    metal: '#8A7048',
                    edge: '#433214',
                },
                gold: {
                    DEFAULT: '#B8923A',
                    light: '#DFC06E',
                    pale: '#E8D5A3',
                    dark: '#6E4F0E',
                },
                wax: {
                    light: '#B3261E',
                    DEFAULT: '#8A1A14',
                    dark: '#580E0A',
                },
                leather: {
                    light: '#5C2A12',
                    DEFAULT: '#3E1A09',
                    dark: '#230D04',
                },
                desk: {
                    deep: '#1E1408',
                    warm: '#2A1A0A',
                },
                forest: {
                    DEFAULT: '#24573A',
                    light: '#2E7A50',
                },
            },
        },
    },

    plugins: [forms],
};
