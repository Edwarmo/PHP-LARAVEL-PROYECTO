import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { resolve } from 'node:path'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            ssr: 'resources/js/ssr.js',
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
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    optimizeDeps: {
        include: ['animejs'],
    },
    test: {
        environment: 'happy-dom',
        globals: true,
        setupFiles: ['./resources/js/tests/setup.js'],
        coverage: {
            provider: 'istanbul',
            reporter: ['text', 'json', 'html'],
            reportsDirectory: './coverage',
            include: ['resources/js/Components/ui/**/*.vue'],
            all: true,
            threshold: {
                branches: 97,
                functions: 97,
                lines: 97,
                statements: 97
            }
        }
    }
})
