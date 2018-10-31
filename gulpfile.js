var gulp = require('gulp');

gulp.task('init', function() {
    console.log('alert');
});

gulp.task('sass', function(){
    return gulp.src('source-files')
        .pipe(sass()) // Using gulp-sass
        .pipe(gulp.dest('destination'))
});