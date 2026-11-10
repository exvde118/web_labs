const mix = require('laravel-mix');
mix.setPublicPath('public')
    .js('resources/js/app.js', 'js')
    .sass('resources/scss/app.scss', 'css')
    .version()
    .disableNotifications();
mix.webpackConfig({ watchOptions: { ignored: /node_modules/ } });

// Пересборка также при изменении Blade-разметки.
const fs = require('fs');
const path = require('path');
mix.webpackConfig({
    plugins: [{
        apply(compiler) {
            compiler.hooks.afterCompile.tap('WatchBlade', compilation => {
                function watch(directory) {
                    compilation.contextDependencies.add(directory);
                    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
                        const file = path.join(directory, entry.name);
                        if (entry.isDirectory()) watch(file);
                        else if (file.endsWith('.blade.php')) compilation.fileDependencies.add(file);
                    }
                }
                watch(path.resolve(__dirname, 'resources/views'));
            });
        }
    }]
});
