module.exports = function(grunt) {

  //Initializing the configuration object
    grunt.initConfig({

    // Task configuration
    less: {
        development: {
            options: {
              compress: true,  //minifying the result
              // LESS source map
              // Enable source maps. (To enable, set sourceMap to true and update sourceMapRootpath based on your install)
              sourceMap: true,
              // Write the source map to a separate file with the given filename.
              sourceMapFilename: './public/dist/css/main.min.css.map', // where file is generated and located
              // Override the default URL that points to the source map from the compiled CSS file.
              sourceMapURL: '../../../public/dist/css/main.min.css.map', // (the complete url and filename put in the compiled css file.)
              // Adds this path onto the less file paths in the source map.
              sourceMapRootpath: '../../../app/'
            },
            files: {
              //compiling main.less into main.min.css
              "./public/dist/css/main.min.css":"./app/assets/less/main.less"
            }
        }
    },
    concat: {
      options: {
        separator: ';',
      },
      js_softcatala: {
        src: [
          './bower_components/jquery/jquery.js',
          './bower_components/bootstrap/dist/js/bootstrap.js',
          './bower_components/responsive-toolkit/dist/bootstrap-toolkit.min.js',
          './bower_components/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js',
          './bower_components/bootstrap-star-rating/js/star-rating.min.js',
          './bower_components/bootstrap-select/js/bootstrap-select.js',
          './bower_components/mega-site-navigation/js/main.js',
          './bower_components/expanding-search-bar/js/uisearch.js',
          './bower_components/expanding-search-bar/js/classie.js',
          './app/assets/js/main.js'
        ],
        dest: './public/dist/js/main.js',
      },
    },
    uglify: {
      options: {
        mangle: false  // Use if you want the names of your functions and variables unchanged
      },
      minifying: {
        files: {
          './public/dist/js/main.min.js': './public/dist/js/main.js',
        }
      },
    },
    watch: {
        js_softcatala: {
          files: [
            './app/assets/js/main.js',
            './bower_components/mega-site-navigation/js/main.js'
            ],
          tasks: ['concat:js_softcatala','uglify:minifying'], // tasks to run
          options: {
            livereload: true // reloads the browser
          }
        },
        less: {
          files: ['./bower_components/bootstrap/less/*.less',
                  './bower_components/mega-site-navigation/css/style.less',
                  './app/assets/less/components/*.less',
                  './app/assets/less/comuns/*.less',
                  './app/assets/less/layouts/*.less',
                  './app/assets/less/*.less'],
          tasks: ['less'],
          options: {
            livereload: true //reloads the browser
          }
        }
      }
    });

  // Plugin loading
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  // Task definition
  grunt.registerTask('default', ['less']);

};