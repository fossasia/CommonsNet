/*
 * grunt-coveralls
 * https://github.com/pimterry/grunt-coveralls
 *
 * Copyright (c) 2013 Tim Perry
 * Licensed under the MIT license.
 */

'use strict';

module.exports = function(grunt) {

    grunt.initConfig({
        jshint: {
            all: [
                'Gruntfile.js',
                'tasks/*.js',
                '<%= nodeunit.tests %>'
            ],
            options: {
                jshintrc: '.jshintrc'
            }
        },

        coveralls: {
            basic_test: {
                src: 'test/fixtures/lcov.info'
            },
            basic_test_force: {
                src: 'test/fixtures/lcov.info',
                options: {
                    force: true
                }
            },
            multiple_files_test: {
                src: ['test/fixtures/lcov.info', 'test/fixtures/lcov2.info']
            },
            missing_file_test: {
                src: 'test/fixtures/nonexistent_lcov.info'
            },
            some_missing_files_test: {
                src: ['test/fixtures/lcov.info', 'test/fixtures/nonexistent_lcov.info']
            },
            all_missing_files_test: {
                src: ['test/fixtures/nonexistent_lcov1.info', 'test/fixtures/nonexistent_lcov2.info']
            },
            grunt_coveralls_real_coverage: {
                src: 'coverage/lcov.info'
            }
        },

        nodeunit: {
            tests: ['test/*_test.js']
        },

        watch: {
            options: {
                spawn: false
            },
            gruntfile: {
                files: 'Gruntfile.js',
                tasks: ['jshint']
            },
            lib: {
                files: 'tasks/*.js',
                tasks: ['test']
            },
            test: {
                files: 'test/*js',
                tasks: ['test']
            }
        }

    });

    grunt.loadTasks('tasks');

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-nodeunit');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-exec');

    grunt.registerTask('default', ['test']);
    grunt.registerTask('test', ['jshint', 'nodeunit']);
};
