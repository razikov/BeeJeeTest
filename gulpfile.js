const { src, dest, parallel } = require('gulp');
//const babel = require('gulp-babel');
//const uglify = require('gulp-uglify');

function javascript(cb) {
    return src('src/Frontend/js/*.js')
            .pipe(src('node_modules/jquery/dist/jquery.min.js'))
            .pipe(src('node_modules/bootstrap/dist/js/bootstrap.min.js'))
            .pipe(dest('public/js'));
}

function css(cb) {
    return src('src/Frontend/css/*.css')
            .pipe(src('node_modules/bootstrap/dist/css/bootstrap.min.css'))
            .pipe(dest('public/css'));
}

function img(cb) {
    return src('src/Frontend/img/*.css')
            .pipe(src('node_modules/feather-icons/dist/icons/arrow-down.svg'))
            .pipe(src('node_modules/feather-icons/dist/icons/arrow-up.svg'))
            .pipe(src('node_modules/feather-icons/dist/icons/check.svg'))
            .pipe(src('node_modules/feather-icons/dist/icons/edit-3.svg'))
            .pipe(src('node_modules/feather-icons/dist/icons/edit.svg'))
            .pipe(dest('public/img'));
}

exports.default = parallel(javascript, css, img);