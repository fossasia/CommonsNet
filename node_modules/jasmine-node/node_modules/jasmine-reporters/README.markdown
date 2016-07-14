This branch is for Jasmine 1.x.
[Switch to the 2.x branch.](https://github.com/larrymyers/jasmine-reporters)

# Jasmine Reporters

Jasmine Reporters is a collection of javascript jasmine.Reporter classes that can be used with
the [JasmineBDD testing framework](http://jasmine.github.io/).

Included reporters:

* ConsoleReporter - Report test results to the browser console.
* JUnitXmlReporter - Report test results to a file in JUnit XML Report format.
* NUnitXmlReporter - Report test results to a file in NUnit XML Report format.
* TapReporter - Test Anything Protocol, report tests results to console.
* TeamcityReporter - Basic reporter that outputs spec results to for the Teamcity build system.
* TerminalReporter - Logs to a terminal (including colors) with variable verbosity.

## Usage

Examples are included in the test directory that show how to use the reporters,
as well a basic runner scripts for Rhino + envjs, and a basic runner for
[PhantomJS](https://github.com/ariya/phantomjs). Either of these methods could
be used in a Continuous Integration project for running headless tests and
generating JUnit XML output.

### Rhino + EnvJS

Everything needed to run the tests in Rhino + EnvJS is included in this
repository inside the `ext` directory, specifically Rhino 1.7r2 and envjs 1.2
for Rhino.

### PhantomJS

Should work in most versions of PhantomJS > 1.4.1
I have used PhantomJS 1.4.1 through 1.9.6 on Mac OS X with no problems.

### Node.js

Most of these reporters also work in node.js by making use of the excellent
[jasmine-node project](https://github.com/mhevery/jasmine-node).

# Protractor

Protractor 1.6.0 or above allows you to use either Jasmine 1 or Jasmine 2.
If you are using Jasmine 1, make sure you install a 1.x-compatible version of jasmine-reporters:

    npm install --save-dev jasmine-reporters@^1.0.0

Then set everything up inside your protractor.conf:

    onPrepare: function() {
        // The require statement must be down here, since jasmine-reporters@1.0
        // expects jasmine to be in the global and protractor does not guarantee
        // this until inside the onPrepare function.
        require('jasmine-reporters');
        jasmine.getEnv().addReporter(
            new jasmine.JUnitXmlReporter('xmloutput', true, true)
        );
    }
