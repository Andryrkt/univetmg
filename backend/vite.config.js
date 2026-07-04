import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

export default defineConfig({
    plugins: [symfonyPlugin()],
    server: {
        host: "0.0.0.0",
        port: 5173,
        strictPort: true,
        // Le serveur écoute sur toutes les interfaces (nécessaire dans Docker),
        // mais les balises <script>/<link> générées doivent pointer vers une URL
        // atteignable depuis le navigateur de l'hôte, pas 0.0.0.0.
        origin: "http://localhost:5173",
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
