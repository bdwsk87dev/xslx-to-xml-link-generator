import { defineConfig } from 'vite';
import reactRefresh from '@vitejs/plugin-react-refresh';

export default defineConfig({
    plugins: [reactRefresh()],
    build: {
        outDir: 'public/js',
        rollupOptions: {
            input: 'resources/js/app.jsx',
            output: {
                entryFileNames: 'app.js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
    },
    server: {
        proxy: {
            '/api': {
                target: 'http://localhost:8000', // Указываем адрес и порт вашего бэкенда
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, '') // Удаление префикса /api из URL
            }
        }
    }
});
