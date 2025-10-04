// webpack.front.config.js
const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    .setOutputPath('public/assets-front/')
    .setPublicPath('/assets-front')

    /*
     * ENTRY CONFIG
     */
    .addEntry('front', './assets-front/js/main.js')

    // CSS/SCSS support (CSS will be extracted automatically from JS imports)
    .enableSassLoader()
    .configureCssLoader(options => { options.url = false; })
    .disableSingleRuntimeChunk() // fewer files for simple integration
    // .splitEntryChunks()       // enable if you want a vendor chunk (adds 1 file)
    .configureFilenames({
        js: 'js/[name].[contenthash].js',
        css: 'css/[name].[contenthash].css',
    })

    // Make sure any `require('jquery')` in vendor files resolves to our local file
    .addAliases({
        jquery: path.resolve(__dirname, 'assets-front/vendor/jquery-3.7.1.min.js'),
    })

    // Copy static assets used by CSS/JS
    .copyFiles({ from: './assets-front/imgs',      to: 'imgs/[path][name].[ext]' })
    .copyFiles({ from: './assets-front/fonts',     to: 'fonts/[name].[ext]' })
    .copyFiles({ from: './assets-front/webfonts',  to: 'webfonts/[name].[ext]' })
    .copyFiles({ from: './assets-front/vendor',    to: 'vendor/[name].[ext]' })
    .copyFiles({ from: './assets-front/css',       to: 'css/[name].[ext]', pattern: /auth\.css$/ })

    /*
    * FEATURE CONFIG
    */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .configureCssMinimizerPlugin((options) => {
        // Exclude copied, non-standard CSS (contains SCSS syntax) from minification
        options.exclude = /css\/auth\.css$/;
    })
    .enableVersioning(Encore.isProduction());

module.exports = Encore.getWebpackConfig();