/** Tailwind build config — jalankan ulang setelah menambah class baru:
 *  npx tailwindcss@3.4.17 -c tailwind.config.js -i assets/tailwind.css -o public/css/app.css --minify
 */
module.exports = {
    content: [
        './views/**/*.php',
        './app/**/*.php',
        './public/**/*.js',
    ],
    safelist: [
        'lg:grid-cols-1', 'lg:grid-cols-2', 'lg:grid-cols-3',
        'lg:grid-cols-4', 'lg:grid-cols-5', 'lg:grid-cols-6',
    ],
    theme: {
        extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
                brand: {
                    50:  '#eef7f1',
                    100: '#d6ece0',
                    200: '#aedcc2',
                    300: '#7cc39f',
                    400: '#4c9d75',
                    500: '#2b7d55',
                    600: '#1e6347',
                    700: '#17503a',
                    800: '#113d2c',
                    900: '#0b2a1e',
                }
            }
        }
    }
};
