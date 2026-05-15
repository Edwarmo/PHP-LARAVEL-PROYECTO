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
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/vue') || id.includes('node_modules/@inertiajs')) {
                        return 'vendor'
                    }
                    if (id.includes('node_modules/gsap')) {
                        return 'gsap'
                    }
                    if (id.includes('node_modules/clsx') || id.includes('node_modules/tailwind-merge') || id.includes('node_modules/tw-animate-css')) {
                        return 'ui'
                    }
                },
            },
        },
        chunkSizeWarningLimit: 250,
        cssCodeSplit: true,
        sourcemap: false,
        minify: 'esbuild',
    },
    optimizeDeps: {
        include: ['gsap'],
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
        },
    },
})
