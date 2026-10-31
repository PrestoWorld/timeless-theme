const postcss = require('postcss');
const tailwindcss = require('@tailwindcss/postcss');
const autoprefixer = require('autoprefixer');
const fs = require('fs');

fs.mkdirSync('./assets/css/dist', { recursive: true });

postcss([tailwindcss(), autoprefixer()])
  .process(fs.readFileSync('./assets/css/app.css'), {
    from: './assets/css/app.css',
    to: './assets/css/dist/tailwind.css'
  })
  .then((result) => {
    fs.writeFileSync('./assets/css/dist/tailwind.css', result.css);
    if (result.map) {
      fs.writeFileSync('./assets/css/dist/tailwind.css.map', result.map);
    }
    console.log('Built assets/css/dist/tailwind.css');
  })
  .catch((err) => {
    console.error(err);
    process.exit(1);
  });
