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
        },
        colors: {
            'sky-blue': '#40a2e3',
            'linen': '#FFF6E9',
            'teal': '#227B94',
            'crystal-blue': '#bbe2ec',
            'navy-blue': '#16325B',
            'honey-gold': '#FFDC7F',
            'light-crystal': '#bee9f4',
            'dark-navy': '#112643',
            'light-gray': '#f7f7f7',
            'color-white': '#ffffff',
            'dark-gray': '#E2E8F0',
            'gray': 'rgb(117 127 141)',
            'red-warning': '#cb2f2f',
            'green': '#25af15',
        }
    },

    plugins: [forms],
};
