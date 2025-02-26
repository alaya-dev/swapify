const Encore = require('@symfony/webpack-encore');

Encore
    // Répertoire de sortie pour les fichiers compilés
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    // Point d'entrée pour le fichier JavaScript principal
    .addEntry('app', './assets/app.js')
    // Activer le support pour les fichiers Sass
    .enableSassLoader()
    .enablePostCssLoader()
    // Générer des fichiers avec des hash pour le cache-busting
    .enableVersioning()
;

module.exports = Encore.getWebpackConfig();
