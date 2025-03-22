const Encore = require('@symfony/webpack-encore');

Encore
    // répertoire où Webpack va stocker les fichiers compilés
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // entrée de l'application
    .addEntry('app', './assets/app.js')

    // autres configurations...
;

module.exports = Encore.getWebpackConfig();
