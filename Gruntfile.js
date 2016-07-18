module.exports = function(grunt) {

	grunt.initConfig ({
		pkg: grunt.file.readJSON('package.json'),
		/* Sass */
		sass: {
			dev: {
				options: {
					style: 'expanded',
					sourcemap: 'none',
				},
				files: {
					'style-readable.css': 'sass/style.scss'
				}
			},
			dist: {
				options: {
					style: 'compressed',
					sourcemap: 'none',
				},
				files: {
					'style.css': 'sass/style.scss'
				}
			}
		},

		/* Watch */
		autoprefixer: {
			options: {
				browsers: ['last 2 versions']
			},
			multiple_files: {
				expand: true,
				flatten: true,
				src: '*.css',
				dest: ''
			}
		},

		/* Watch */
		watch: {
			options: {
			    livereload: true
			},
			css: {
				files: '**/*.scss',
				tasks: ['sass','autoprefixer']
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-autoprefixer');
	grunt.registerTask('default',['watch']);
}