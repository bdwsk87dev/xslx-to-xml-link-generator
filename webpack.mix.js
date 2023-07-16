const mix = require('laravel-mix');

mix.js('resources/js/app.jsx', 'public/js')
    .react()
    .disableSuccessNotifications();

mix.webpackConfig({
    output: {
        chunkFilename: 'js/[name].js?id=[chunkhash]',
    },
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
        },
    },
});

mix.disableNotifications();
