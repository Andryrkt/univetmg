import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

export default defineConfig({
    plugins: [symfonyPlugin()],
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
