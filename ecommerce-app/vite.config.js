import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    /*server: {
        host: '0.0.0.0',   // Permet d'accepter les connexions externes (Ngrok)
        port: 5173,        // Port par défaut de Vite
        cors: true,        // Active le CORS pour éviter les blocages
        hmr: {
            host: 'localhost', // Hot Module Reload
        },
    },*/
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        
    ],
});
