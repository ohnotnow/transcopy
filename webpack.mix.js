let cssImport = require('postcss-import')
let cssNext = require('postcss-cssnext')
let glob = require('glob-all')
let mix = require('laravel-mix')
let purgeCss = require('purgecss-webpack-plugin')
let tailwind = require('tailwindcss')

mix.js('resources/js/app.js', 'public/js')
  .postCss('resources/css/app.css', 'public/css', [
    cssImport(),
    tailwind('tailwind.js'),
    cssNext({ features: { autoprefixer: false }}),
  ]);

if (mix.inProduction()) {
  mix.webpackConfig({
    plugins: [
      new purgeCss({
        paths: glob.sync([
          path.join(__dirname, 'resources/views/**/*.blade.php'),
          path.join(__dirname, 'resources/js/**/*.vue'),
        ]),
        extractors: [
          {
            extractor: class {
              static extract(content) {
                return content.match(/[A-z0-9-:\/]+/g)
              }
            },
            extensions: ['html', 'js', 'php', 'vue']
          }
        ]
      })
    ]
  })
}

