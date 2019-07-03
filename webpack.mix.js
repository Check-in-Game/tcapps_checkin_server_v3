const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js(['resources/js/app.js',
  'node_modules/bootstrap/dist/js/bootstrap.bundle.js'
], 'public/js/app.js')

mix.sass('resources/sass/app.scss', 'public/css');

mix.styles(['resources/css/stisla/style.css',
  'resources/css/stisla/components.css',
  'resources/css/stisla/custom.css'
], 'public/css/stisla.css');

mix.js(['resources/js/stisla/stisla.js',
  'resources/js/stisla/scripts.js',
  'resources/js/stisla/custom.js'
], 'public/js/stisla.js');
