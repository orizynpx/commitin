import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['IBM Plex Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'surface': '#f5faff',
                'surface-dim': '#caddeb',
                'surface-bright': '#f5faff',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#eaf5ff',
                'surface-container': '#def0ff',
                'surface-container-high': '#d8ebfa',
                'surface-container-highest': '#d2e5f4',
                'on-surface': '#0b1d28',
                'on-surface-variant': '#404850',
                'inverse-surface': '#21323e',
                'inverse-on-surface': '#e4f3ff',
                'outline': '#707881',
                'outline-variant': '#bfc7d1',
                'surface-tint': '#006398',
                'primary': '#005d8f',
                'on-primary': '#ffffff',
                'primary-container': '#0077b5',
                'on-primary-container': '#f3f7ff',
                'inverse-primary': '#93ccff',
                'secondary': '#7c5800',
                'on-secondary': '#ffffff',
                'secondary-container': '#feb700',
                'on-secondary-container': '#6b4b00',
                'tertiary': '#005f85',
                'on-tertiary': '#ffffff',
                'tertiary-container': '#0079a8',
                'on-tertiary-container': '#f0f8ff',
                'error': '#ba1a1a',
                'on-error': '#ffffff',
                'error-container': '#ffdad6',
                'on-error-container': '#93000a',
                'primary-fixed': '#cde5ff',
                'primary-fixed-dim': '#93ccff',
                'on-primary-fixed': '#001d32',
                'on-primary-fixed-variant': '#004b74',
                'secondary-fixed': '#ffdea8',
                'secondary-fixed-dim': '#ffba20',
                'on-secondary-fixed': '#271900',
                'on-secondary-fixed-variant': '#5e4200',
                'tertiary-fixed': '#c6e7ff',
                'tertiary-fixed-dim': '#82cfff',
                'on-tertiary-fixed': '#001e2d',
                'on-tertiary-fixed-variant': '#004c6b',
                'background': '#f5faff',
                'on-background': '#0b1d28',
                'surface-variant': '#d2e5f4',
            },
            borderRadius: {
                'sm': '0.125rem',
                DEFAULT: '0.25rem',
                'md': '0.375rem',
                'lg': '0.5rem',
                'xl': '0.75rem',
                'full': '9999px',
            }
        },
    },

    plugins: [forms],
};
