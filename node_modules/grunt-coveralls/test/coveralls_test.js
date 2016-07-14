'use strict';

var coveralls = require('coveralls');
var fs = require('fs');
var grunt = require('grunt');
var sinon = require('sinon');

function runGruntTask(taskName, callback) {
    var task = grunt.task._taskPlusArgs(taskName);
    task.task.fn.apply({
        nameArgs: task.nameArgs,
        name: task.task.name,
        args: task.args,
        flags: task.flags,
        async: function() { return callback; }
    }, task.args);
}

function givenCoverallsWillFail() {
    coveralls.handleInput.restore();
    sinon.stub(coveralls, 'handleInput').callsArgWith(1, 'Error');
}

function givenFileReadsWillFail() {
    fs.readFile.restore();
    sinon.stub(fs, 'readFile').callsArgWith(2, 'Error');
}

exports.coveralls = {
    setUp: function (callback) {
        sinon.stub(coveralls, 'handleInput').callsArgWith(1, null);
        sinon.spy(fs, 'readFile');
        callback();
    },

    tearDown: function (callback) {
        fs.readFile.restore();
        coveralls.handleInput.restore();
        callback();
    },

    submits_file_to_coveralls: function (test) {
        runGruntTask('coveralls:basic_test', function (result) {
            test.ok(result, 'Should be successful');

            test.ok(coveralls.handleInput.calledOnce);
            test.equal(coveralls.handleInput.getCall(0).args[0], 'lcov.info content', 'Should send lcov data');
            test.done();
        });
    },

    submits_nothing_if_the_file_is_missing: function (test) {
        runGruntTask('coveralls:missing_file_test', function (result) {
            test.ok(!result, 'Should fail');

            test.ok(!coveralls.handleInput.called);
            test.done();
        });
    },

    submits_multiple_files: function (test) {
        runGruntTask('coveralls:multiple_files_test', function (result) {
            test.ok(result, 'Should be successful');

            test.ok(coveralls.handleInput.calledTwice);
            sinon.assert.calledWith(coveralls.handleInput, 'lcov.info content');
            sinon.assert.calledWith(coveralls.handleInput, 'lcov2.info content');
            test.done();
        });
    },

    submits_present_files_only_if_some_are_missing: function (test) {
        runGruntTask('coveralls:some_missing_files_test', function (result) {
            test.ok(result, 'Should be successful');

            test.ok(coveralls.handleInput.calledOnce);
            sinon.assert.calledWith(coveralls.handleInput, 'lcov.info content');
            test.done();
        });
    },

    fails_if_multiple_files_listed_and_all_files_are_missing: function (test) {
        runGruntTask('coveralls:all_missing_files_test', function (result) {
            test.ok(!result, 'Should fail');

            test.ok(!coveralls.handleInput.called);
            test.done();
        });
    },

    fails_if_file_is_not_readable: function (test) {
        givenFileReadsWillFail();

        runGruntTask('coveralls:basic_test', function (result) {
            test.ok(!result, 'Should fail');

            test.ok(!coveralls.handleInput.called);
            test.done();
        });
    },

    fails_if_any_files_fail_to_upload: function (test) {
        givenCoverallsWillFail();

        runGruntTask('coveralls:basic_test', function (result) {
            test.ok(!result, 'Should fail');
            test.done();
        });
    },

    force_mode_doesnt_produce_grunt_errors: function (test) {
        givenCoverallsWillFail();

        runGruntTask('coveralls:basic_test_force', function (result) {
            test.ok(result, 'Should not fail when options.force === true');
            test.done();
        });
    }
};
