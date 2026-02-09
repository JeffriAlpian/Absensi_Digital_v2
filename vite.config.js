import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // host: '0.0.0.0', // Mengizinkan akses dari luar localhost
        // https: false,
        // hmr: {
        //     host: '192.168.1.6' // GANTI dengan IP Laptop/PC Anda yang Anda pakai tadi
        // },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
