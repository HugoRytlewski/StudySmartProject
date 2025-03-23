const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Set the output path for the compiled files
    .setOutputPath('public/build/')
    // Set the public path for assets
    .setPublicPath('/build')
    // Define the entry point for the app.js file
    .addEntry('app', './assets/app.js')
    // Split the entry chunks for better cache management
    .splitEntryChunks()
    // Enable a single runtime chunk for better caching
    .enableSingleRuntimeChunk()
    // Clean the output directory before each build
    .cleanupOutputBeforeBuild()
    // Enable build notifications
    .enableBuildNotifications()
    // Enable source maps for non-production builds
    .enableSourceMaps(!Encore.isProduction())
    // Enable versioning for production builds to help with cache busting
    .enableVersioning(Encore.isProduction())
    // Configure Babel with additional settings
    .configureBabel((config) => {
        config.exclude = /node_modules\/(?!(.*fullcalendar.*)\/).*/;
    })
    // Configure Babel preset environment
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })
    // Configure the Webpack dev server
    .configureDevServerOptions((devServerOptions) => {
        const port = process.env.PORT || 8080; // Use dynamic port provided by Render or default to 8080
        devServerOptions.port = port;
        devServerOptions.host = '0.0.0.0'; // Allow connections from all interfaces
        devServerOptions.allowedHosts = 'all'; // Allow all hosts
        devServerOptions.open = true; // Open the browser automatically
        devServerOptions.hot = true; // Enable hot module replacement
        return devServerOptions;
    })
    // Optional: Enable additional optimizations or configurations for Symfony and Webpack integration
    .enableSassLoader() // If you use SCSS/SASS
    .enablePostCssLoader() // If you use PostCSS
;

module.exports = Encore.getWebpackConfig();
