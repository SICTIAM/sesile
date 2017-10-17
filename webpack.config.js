// webpack.config.js
var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build/')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/build')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('app', './app/Resources/assets/js/Main.jsx')

    // will output as web/build/global.css
    .addStyleEntry('global', './app/Resources/assets/css/global.scss')

    .addLoader({
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /node_modules\/(?!(foundation-sites)\/).*/ 
    })

    .createSharedEntry('vendor', [
        'jquery',
        'what-input',
        'foundation-sites'
    ])

    // allow sass/scss files to be processed
    .enableSassLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    .enableReactPreset()

// create hashed filenames (e.g. app.abc123.css)
// .enableVersioning();

// export the final configuration
module.exports = Encore.getWebpackConfig();