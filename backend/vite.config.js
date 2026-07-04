import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

export default defineConfig({
    plugins: [symfonyPlugin()],
    server: {
        host: "0.0.0.0",
        port: 5173,
        strictPort: true,
    },
    build: {
        outDir: "public/build", // les fichiers compilés iront ici
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: "./assets/app.js",
            },
        },
    },
});
