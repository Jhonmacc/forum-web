import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import plugin from 'tailwindcss/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/**/*.{html,js,vue}'
    ],

    theme: {
        extend: {
            fontFamily: {
                nunito: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        forms,
        typography,
        plugin(function ({ addBase }) {
            addBase({
                '*::-webkit-scrollbar': {
                    width: '8px',
                },
                '*::-webkit-scrollbar-track': {
                    'border-radius': '9999px',
                    'background-color': 'rgba(229, 231, 235, 1)', // bg-gray-100
                },
                '*::-webkit-scrollbar-thumb': {
                    'border-radius': '9999px',
                    'background-color': 'rgba(209, 213, 219, 1)', // bg-gray-300
                },
                '*:hover::-webkit-scrollbar-thumb': {
                    'background-color': 'rgba(156, 163, 175, 1)', // bg-gray-400 on hover
                },
                '@media (prefers-color-scheme: dark)': {
                    '*::-webkit-scrollbar-track': {
                        'background-color': 'rgba(55, 65, 81, 1)', // bg-neutral-700
                    },
                    '*::-webkit-scrollbar-thumb': {
                        'background-color': 'rgba(107, 114, 128, 1)', // bg-neutral-500
                    },
                },
            });
        }),
    ],
};
