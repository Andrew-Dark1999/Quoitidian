var gulp = require('gulp'),
    cssmin = require('gulp-clean-css'),
    watch = require('gulp-watch'),
    less = require('gulp-less'),
    //uglify = require('gulp-uglify'),
    concat = require('gulp-concat');

var config = {
    fileOfCss: 'crm.min.css',
    fileOfJs: 'scripts.min.js',
    fileOfJs2: 'scripts2.min.js',
    fileOfJs3: 'scripts3.min.js',
    watch: {
       scripts: './htdocs/static/js/**/*.js',
       css: './htdocs/static/css/**/*.css',
       less: './htdocs/static/css/**/*.less'
     },
    path:{
      temp: './htdocs/static/temp/',
      original:{
        less: './htdocs/static/css/crm-additional.less'
      },
      production:{
          output: './htdocs/static/min'
        }
      }
};

gulp.task('css', function () {
    return gulp.src([
                    './htdocs/static/js/jquery-ui/jquery-ui-1.10.1.custom.min.css',
                    './htdocs/static/css/bootstrap-reset.css',
                    './htdocs/static/font-awesome/css/font-awesome.css',
                    './htdocs/static/js/jvector-map/jquery-jvectormap-1.2.2.css',
                    './htdocs/static/css/clndr.css',
                    './htdocs/static/js/data-tables/DT_bootstrap.css',
                    './htdocs/static/fonts/lato.css',
                    './htdocs/static/css/bucket-ico-fonts.css',
                    './htdocs/static/css/style.css',
                    './htdocs/static/fonts/myriadpro.css',
                    './htdocs/static/css/bootstrap-select.css',
                    './htdocs/static/js/bootstrap-datepicker/css/datepicker.css',
                    './htdocs/static/js/bootstrap-timepicker/css/timepicker.css',
                    './htdocs/static/js/magnific-popup/magnific-popup.css',
                    './htdocs/static/js/bootstrap-daterangepicker/daterangepicker-bs3.css',
                    './htdocs/static/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.css',
                    './htdocs/static/js/jscrollpane/css/jquery.jscrollpane.css',
                    './htdocs/static/css/crm.css',
                    './htdocs/static/css/bootstrap-fullcalendar.css',
                    './htdocs/static/css/crm-additional.css'
                  ])
        .pipe(concat(config.fileOfCss))
        .pipe(cssmin()) //Сожмем
        .pipe(gulp.dest(config.path.production.output));
});

gulp.task('scripts', function () {
    return gulp.src([
                    './htdocs/static/js/model-global.js',
                    './htdocs/static/js/global.js',
                    // './htdocs/static/js/sip-phone.js',
                    // './htdocs/static/js/voximplant.min.js',
                    './htdocs/static/js/moment-2.18.1.js',
                    './htdocs/static/js/jquery-ui/jquery-ui-1.10.1.custom.min.js',
                    './htdocs/static/bs3/js/bootstrap.min.js',
                    './htdocs/static/js/jquery.dcjqaccordion.2.7.js',
                    './htdocs/static/js/jquery.scrollTo.min.js',
                    './htdocs/static/js/jQuery-slimScroll-1.3.0/jquery.slimscroll.js',
                    './htdocs/static/js/jquery.nicescroll.js',
                    './htdocs/static/js/bootstrap-select.js',
                    './htdocs/static/js/data-tables/jquery.dataTables.js',
                    './htdocs/static/js/data-tables/DT_bootstrap.js',
                    './htdocs/static/js/jquery.maskedinput.js',
                    './htdocs/static/js/drop-down-list.js',
                    './htdocs/static/js/history.js',
                    './htdocs/static/js/notifications.js',
                    './htdocs/static/js/date-time-block.js',
                    ])
        .pipe(concat(config.fileOfJs))
        //.pipe(uglify())
        .pipe(gulp.dest(config.path.production.output));
 });

gulp.task('scripts2', function () {
    return gulp.src(['./htdocs/static/js/easypiechart/jquery.easypiechart.js',
         './htdocs/static/js/sparkline/jquery.sparkline.js',
         './htdocs/static/js/morris-chart/morris.js',
         './htdocs/static/js/morris-chart/raphael-min.js',
         './htdocs/static/js/flot-chart/jquery.flot.js',
         './htdocs/static/js/flot-chart/jquery.flot.tooltip.min.js',
         //'./htdocs/static/js/flot-chart/jquery.flot.resize.js',
         //'./htdocs/static/js/flot-chart/jquery.flot.pie.resize.js',
         './htdocs/static/js/flot-chart/jquery.flot.animator.min.js'])
        .pipe(concat(config.fileOfJs2))
        //.pipe(uglify())
        .pipe(gulp.dest(config.path.production.output));
});

gulp.task('scripts3', function () {
    return gulp.src([
        './htdocs/static/js/flot-chart/jquery.flot.growraf.js',
        './htdocs/static/js/bootstrap-datepicker/js/bootstrap-datepicker.js',
        './htdocs/static/js/bootstrap-datepicker/js/datepicker-locales.js',
        './htdocs/static/js/bootstrap-timepicker/js/bootstrap-timepicker.js',
        './htdocs/static/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
        './htdocs/static/js/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.ru.js',
        './htdocs/static/js/jscrollpane/js/jquery.jscrollpane.min.js',
        './htdocs/static/js/jscrollpane/js/jquery.mousewheel.js',
        './htdocs/static/js/jscrollpane/js/mwheelIntent.js',
        './htdocs/static/js/bootstrap-daterangepicker/moment.js',
        './htdocs/static/js/bootstrap-daterangepicker/daterangepicker.js',
        './htdocs/static/js/jquery.dragtable/jquery.dragtable.js',
        './htdocs/static/js/scripts.js',
        './htdocs/static/js/local-storage.js',
        './htdocs/static/js/list-view-display.js',
        './htdocs/static/js/url.js',
        './htdocs/static/js/modal-dialog.js',
        './htdocs/static/js/profile.js',
        './htdocs/static/js/communications.js',
        './htdocs/static/js/calls.js',
        './htdocs/static/js/events.js',
        './htdocs/static/js/edit-view.js',
        './htdocs/static/js/table-column-resize.js',
        './htdocs/static/js/list-view.js',
        './htdocs/static/js/process-view.js',
        './htdocs/static/js/process-view-base.js',
        './htdocs/static/js/jquery.emojiarea.js',
        './htdocs/static/js/filter.js',
        './htdocs/static/js/pagination.js',
        './htdocs/static/js/sorting.js',
        './htdocs/static/js/search.js',
        './htdocs/static/js/tools.js',
        './htdocs/static/js/participant.js',
        // './htdocs/static/js/constructor.js',
        // './htdocs/static/js/inline-edit.js',
        './htdocs/static/js/preloader.js',
        './htdocs/static/js/process/process.general_v0.1.js',
        './htdocs/static/js/process/process.events.js',
        './htdocs/static/js/reports.general.js',
        './htdocs/static/js/nice-scroll.js'
        ])
        .pipe(concat(config.fileOfJs3))
        //.pipe(uglify())
        .pipe(gulp.dest(config.path.production.output));
});

gulp.task('less', function () {
  return gulp.src(config.path.original.less)
    .pipe(less())
    .pipe(gulp.dest('./htdocs/static/css/'));
});

gulp.task('build', ['less','css','scripts', 'scripts2', 'scripts3']);

gulp.task('watch', function(){
    watch([config.watch.less], function(event, cb) {
        gulp.start('less');
    });       
	  watch([config.watch.css], function(event, cb) {
        gulp.start('css');
    });
    watch([config.watch.scripts], function(event, cb) {
        gulp.start('scripts');
        gulp.start('scripts2');
        gulp.start('scripts3');
    });
});

gulp.task('default',['build','watch']);
