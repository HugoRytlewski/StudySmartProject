const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning()
    .configureBabel((config) => {
        config.exclude = /node_modules\/(?!(.*fullcalendar.*)\/).*/;
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })
    // Configure Webpack Dev Server options correctly using a callback
    .configureDevServerOptions((devServerOptions) => {
        const port = process.env.PORT || 8080;  // Use the Render-defined PORT environment variable, fallback to 8080 if not defined
        devServerOptions.port = port;  // Binding to the correct port
        devServerOptions.open = true;  // Open the browser automatically
        devServerOptions.hot = true;   // Enable Hot Module Replacement (HMR)
        return devServerOptions;
    });

module.exports = Encore.getWebpackConfig();
