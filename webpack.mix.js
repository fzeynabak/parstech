const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/sales-show.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .css('resources/css/sales-show.css', 'public/css')
   .version();
