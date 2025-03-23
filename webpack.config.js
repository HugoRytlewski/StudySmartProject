const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction()) // ✅ Correction ici
    .configureBabel((config) => {
        config.exclude = /node_modules\/(?!(.*fullcalendar.*)\/).*/;
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })
    .configureDevServerOptions((devServerOptions) => {
        const port = process.env.PORT || 8080;
        devServerOptions.port = port;
        devServerOptions.host = '0.0.0.0'; // ✅ Ajout
        devServerOptions.open = true;
        devServerOptions.hot = true;
        return devServerOptions;
    })
    

module.exports = Encore.getWebpackConfig();
