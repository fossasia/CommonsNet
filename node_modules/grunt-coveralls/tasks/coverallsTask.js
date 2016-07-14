'use strict';

module.exports = function(grunt) {
    function Runner() {
        var done = this.async(),
            force = this.options().force || false;

        if (this.filesSrc.length === 0) {
            grunt.log.error('No src files could be found for grunt-coveralls');
            done(false);
        }

        var successful = true;
        var remainingSubmissions = this.filesSrc.length;

        function receiveResult(result) {
            successful = successful && result;
            remainingSubmissions--;

            if (remainingSubmissions === 0) {
                if (successful) {
                    grunt.log.ok("Successfully submitted coverage results to coveralls");
                    done(true);
                } else if (force) {
                    grunt.log.warn("WARNING: Failed to submit coverage results to coveralls");
                    done(true);
                } else {
                    grunt.log.error("Failed to submit coverage results to coveralls");
                    done(false);
                }
            }
        }

        for (var ii = 0; ii < this.filesSrc.length; ii++) {
            submitToCoveralls(this.filesSrc[ii], receiveResult);
        }
    }

    function submitToCoveralls(fileName, callback) {
        grunt.verbose.writeln("Submitting file to coveralls.io: " + fileName);

        var coveralls = require('coveralls');

        // Override coveralls option processing until it handles use as a library better (TODO)
        coveralls.getOptions = coveralls.getBaseOptions;

        var fs = require('fs');

        fs.readFile(fileName, 'utf8', function(err, fileContent) {
          if (err) {
            grunt.log.error("Failed to read file '" + fileName + "', with error: " + err);
            return callback(false);
          }

          coveralls.handleInput(fileContent, function(err) {
            if (err) {
              grunt.log.error("Failed to submit '" + fileName + "' to coveralls: " + err);
              return callback(false);
            }

            grunt.verbose.ok("Successfully submitted " + fileName + " to coveralls");
            callback(true);
          });
        });
    }

    grunt.registerMultiTask('coveralls', 'Grunt task to load coverage results and submit them to Coveralls.io', Runner);
};