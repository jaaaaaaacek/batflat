const gulp = require('gulp');
const sass = require('gulp-sass');
const browserSync = require('browser-sync').create();


gulp.task('browserSync', function() {
    browserSync.init({
        server: {
            baseDir: 'app'
        },
    })
})

gulp.task('watch', function (){
    browserSync.init({
        proxy: 'localhost/batflat'
    })

    gulp.watch('themes/default/scss/**/*.scss', ['sass', browserSync.reload]);
    gulp.watch('themes/default/**/*.html', browserSync.reload);
    gulp.watch('themes/default/js/**/*.js', browserSync.reload);
});


gulp.task('sass', function() {
    return gulp.src('themes/default/scss/*.scss')
        .pipe(sass())
        .pipe(gulp.dest('themes/default/css'))
        .pipe(browserSync.reload({
            stream: true,
        }))
});

gulp.task('default', ['sass', 'watch']);