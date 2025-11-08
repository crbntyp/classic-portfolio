module.exports = {
    proxy: "http://localhost:8080",
    files: [
        {
            match: ["dist/css/main.css"],
            fn: function (event, file) {
                console.log("🎨 CSS updated");
            }
        },
        "dist/css/*.css",
        "dist/js/*.js",
        "dist/portfolio/**/*.php",
        "dist/includes/**/*.php",
        "dist/*.php",
        "dist/*.html"
    ],
    watchOptions: {
        ignoreInitial: true,
        ignored: ['node_modules/**', 'src/**', 'dist/css/main.css.map'],
        awaitWriteFinish: {
            stabilityThreshold: 150,
            pollInterval: 50
        }
    },
    reloadDelay: 0,
    notify: false,
    open: true,
    startPath: "/",
    ui: false,
    ghostMode: false,
    logLevel: "silent",
    logPrefix: "Portfolio",
    port: 3000,
    injectChanges: true,
    codeSync: true
};
