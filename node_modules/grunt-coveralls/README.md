Grunt-Coveralls
===============
> Grunt task to load coverage results and submit them to Coveralls.io

[![Build Status](https://travis-ci.org/pimterry/grunt-coveralls.png)](https://travis-ci.org/pimterry/grunt-coveralls) [![Coverage Status](https://coveralls.io/repos/pimterry/grunt-coveralls/badge.png?branch=master)](https://coveralls.io/r/pimterry/grunt-coveralls?branch=master) [![Dependency status](https://david-dm.org/pimterry/grunt-coveralls/status.png)](https://david-dm.org/pimterry/grunt-coveralls#info=dependencies&view=table) [![Dev Dependency Status](https://david-dm.org/pimterry/grunt-coveralls/dev-status.png)](https://david-dm.org/pimterry/grunt-coveralls#info=devDependencies&view=table)

## Getting Started
This plugin requires Grunt `~0.4.2`

If you haven't used [Grunt](http://gruntjs.com/) before, be sure to check out the [Getting Started](http://gruntjs.com/getting-started) guide, as it explains how to create a [Gruntfile](http://gruntjs.com/sample-gruntfile) as well as install and use Grunt plugins. Once you're familiar with that process, you may install this plugin with this command:

```shell
npm install grunt-coveralls --save-dev
```

Once the plugin has been installed, it may be enabled inside your Gruntfile with this line of JavaScript:

```js
grunt.loadNpmTasks('grunt-coveralls');
```

## The "coveralls" task

Grunt-coveralls takes one or more lcov files, and uploads them to [coveralls.io](https://coveralls.io).

Everything more specific than that is handled internally by [node-coveralls](https://github.com/cainus/node-coveralls), and the Coveralls service itself.


### Usage

The only required option is a 'src' parameter, which accepts all the standard grunt src formats (plain path, glob, array of paths) and attempts to parse the matched lcov files and upload them.

This grunt task will pass as long as at least one file is matched, and all matched files are uploaded successfully.

```js
grunt.initConfig({
  coveralls: {
    // Options relevant to all targets
    options: {
      // When true, grunt-coveralls will only print a warning rather than
      // an error, to prevent CI builds from failing unnecessarily (e.g. if
      // coveralls.io is down). Optional, defaults to false.
      force: false
    },
    
    your_target: {
      // LCOV coverage file (can be string, glob or array)
      src: 'coverage-results/extra-results-*.info',
      options: {
        // Any options for just this target
      }
    },
  },
});
```

This can then be run with `grunt coveralls` or `grunt coveralls:your_target`. The outcome will be printed to the console. For a practical example, look at this project, which uses itself to track its own coverage.

Note if you are new to grunt: You need to specify at least one target. Just specifying the options won't work.

## Contributing
In lieu of a formal styleguide, take care to maintain the existing coding style. Add unit tests for any new or changed functionality. To ensure your code runs correctly, run `npm test`.

## Release History
_(Nothing yet)_


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/pimterry/grunt-coveralls/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

