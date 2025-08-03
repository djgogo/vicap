// webpack.config.js
const Encore = require('@symfony/webpack-encore');

// Always use /assets for both development and production
Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/scss/config/material/app.scss')
    .addEntry('bootstrap', './assets/scss/config/material/bootstrap.scss')
    .addEntry('icons', './assets/scss/icons.scss')
    .addEntry('custom', './assets/scss/config/material/custom.scss')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()
    .enableSassLoader()

    .configureFilenames({
        css: 'css/[name].min.css',
    })

    .copyFiles({
        from: './assets/fonts',
        to: 'fonts/[name].[ext]',
    })

    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]',
    })

    .copyFiles({
        from: './assets/js',
        to: 'js/[path][name].[ext]',
    })

    .copyFiles({
        from: './assets/lang',
        to: 'lang/[name].[ext]',
    })

    .copyFiles({
        from: './assets/libs',
        to: 'libs/[path][name].[ext]',
    })

    /*
     * FEATURE CONFIG
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

module.exports = Encore.getWebpackConfig();
