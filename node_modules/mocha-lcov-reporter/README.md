mocha-lcov-reporter
===================

LCOV reporter for Mocha.

LCOV format can be found in this [geninfo manpage](http://ltp.sourceforge.net/coverage/lcov/geninfo.1.php). This LCOV reporter was built after [Sonar Javascript Plugin LCOVParser class](https://github.com/SonarCommunity/sonar-javascript/blob/master/sonar-javascript-plugin/src/main/java/org/sonar/plugins/javascript/lcov/LCOVParser.java).

Usage
=====

The mocha-lcov-reporter is a reporter for mocha. In order to get coverage data, the same instructions are to be followed as for the `JSONCov` and `HTMLCov` reporters:

- Install [jscover](https://github.com/node-modules/jscover) or [node-jscoverage](https://github.com/visionmedia/node-jscoverage)
- Instrument your library with `jscover` (or `node-jscoverage`)
- Run your tests against your instrumented library and save the output

For example, the following script can be part of your build process (add `jscover`, `mocha`, and `mocha-lcov-reporter` as `devDependencies` to your `package.json` file and run `npm install`):

```
#!/usr/bin/env bash
rm -rf coverage
rm -rf lib-cov

mkdir coverage

node_modules/.bin/jscover lib lib-cov
mv lib lib-orig
mv lib-cov lib
node_modules/.bin/mocha -R mocha-lcov-reporter > coverage/coverage.lcov
rm -rf lib
mv lib-orig lib
```

This script instruments your sources (source 'lib', target 'lib-cov'), temporarily replaces your library by the instrumented version, run the tests against the instrumented version of your sources, and undoes the replacing of the original library by the instrumented library.

A safer and better approach is to instrument your library (target 'lib-cov'), and include that directory from your tests directly. Instead of doing a 'require("../lib")' do a 'require("../lib-cov")'. This saves the hassle of replacing directory 'lib' with directory 'lib-cov' and undoing it afterwards. You can use an environment variable to check if the instrumented library should be included or the normal version:

```
var lib = process.env.JSCOV ? require('../lib-cov') : require('../lib');
```

And to get the test-coverage, run mocha as follows:

```
JSCOV=1 mocha -R mocha-lcov-reporter > coverage/coverage.lcov
```

See the [SaXPath project](https://github.com/StevenLooman/saxpath) for an example on how to do this.

Incomplete paths in LCOV output
===============================

Unfortunately, when the code is instrumented using `jscover` or `node-jscoverage`, the output of the reporter will have incomplete paths for the covered files. A quick fix is to update the paths after running the tests with the mocha-lcov-reporter, like so:

```
# run the tests
JS_COV=1 ./node_modules/.bin/mocha -R mocha-lcov-reporter > coverage/coverage_temp.lcov

# fix the paths
sed 's,SF:,SF:lib/,' coverage/coverage_temp.lcov > coverage/coverage.lcov
```

The reason this is that `jscover` runs on the directory you specify (e.g., `lib/`) and regards that as the root for the project.

Blanket support
===============

[Blanket.js](http://blanketjs.org/) can be used as well. After the lcov file, be sure to fix the paths for the covered files. The path will be an URL, having `file:` as its protocol. Using the same manner as above, the path can be fixed using `sed`.

Example output
==============

What does LCOV output look like? LCOV is meant to be interpreted by other programs and not meant to be readable by humans. This is an example:

```
SF:base_unit.js
DA:1,1
DA:4,1
DA:5,155
DA:7,155
DA:8,140
DA:9,140
DA:12,155
DA:13,155
DA:16,1
DA:17,1
DA:20,1
DA:21,9
DA:24,1
DA:25,40
DA:28,1
DA:29,26
DA:32,1
DA:33,7
DA:36,1
DA:37,6
DA:40,1
DA:41,45
DA:44,1
DA:45,52
DA:51,1
DA:52,3
DA:55,1
end_of_record
```

If you are looking for something human readable, the `HTMLCov` reporter can be used.
