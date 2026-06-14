import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', { weights: [400, 500, 600] }),
                // AKH design-theme font families (self-hosted, one set per switchable design)
                bunny('Space Grotesk', { weights: [400, 500, 600, 700] }),
                bunny('Inter', { weights: [400, 500, 600, 700] }),
                bunny('JetBrains Mono', { weights: [400, 500, 600] }),
                bunny('Sora', { weights: [400, 600, 700, 800] }),
                bunny('Outfit', { weights: [400, 500, 600, 700] }),
                bunny('Plus Jakarta Sans', { weights: [400, 500, 600, 700] }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
});
