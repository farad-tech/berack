import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig({
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        lib: {
            entry: resolve(import.meta.dirname, 'src/main.js'),
            formats: ['iife'],
            name: 'BerackSdk',
            fileName: () => 'berack.js',
        },
    },
});
