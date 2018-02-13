var _ = require('lodash');
var webpack = require('webpack');
var path = require('path');
var glob = require('glob');
var MinifyPlugin = require("babel-minify-webpack-plugin");
var exports = [];

glob.sync('{gear/modules/**,gear/installer/**,gear/system/**,extensions/**,themes/**}/webpack.config.js', {
    ignore: '**/node_modules/*'
}).forEach(function(file) {
    var dir = path.join(__dirname, path.dirname(file));
    exports = exports.concat(require('./' + file).map(function(config) {
        return _.merge({
            context: dir,
            output: {
                path: dir
            },
            plugins: [
                new webpack.DefinePlugin({
                    'process.env': {
                        NODE_ENV: '"production"'
                    }
                }),
                new MinifyPlugin(),
                new webpack.LoaderOptionsPlugin({
                    minimize: true
                })
            ],
            module: {
                loaders: [
                    {
                        test: /\.vue$/,
                        loader: 'vue-loader',
                        options: {
                            transformToRequire: {
                                vector: 'src',
                                img: 'src',
                                image: 'xlink:href'
                            }
                        }
                    },
                    {
                        test: /\.svg$/,
                        loader: 'svg-inline-loader'
                    }
                ]
            }
        }, config);
    }));
});

module.exports = exports;
