var assert = require('assert')
  , http = require('http')
  , fs = require('fs')
  , connect = require('connect')
  , join = require('path').join
  , gzippo = require('../')
  ;
var fixtures = join(__dirname, 'fixtures')
  , port = 32124
  , app
  , request
  ;

// read a fixture file synchronously
function file(name) {
  return fs.readFileSync(join(fixtures, name));
}

describe('gzippo.statisGzip (with prefix)', function() {

  it('should successfully serve a .json file with a path prefix', function(done) {
    var app = connect.createServer();
    app.use(gzippo.staticGzip(fixtures, { prefix: '/foo' }));
    request = require('./request')({ port: port + 5 });

    app.listen(port + 5, function() {
      request('/foo/user.json', { 'Accept-Encoding': 'gzip' },
        function(err, res, data) {
          if (err) throw err;
          assert.equal(res.statusCode, 200);

          assert.equal(res.headers['content-type'], 'application/json; charset=UTF-8');
          assert.equal(data.length, '69');
          assert.equal(res.headers['content-encoding'], 'gzip');

          assert.deepEqual(data, file('user.gzip'));

          done();
        }
      );
    });
  });

  it('should serve files as expected with a / prefix', function(done) {
    var app = connect.createServer();
    app.use(gzippo.staticGzip(fixtures, { prefix: '/' }));
    request = require('./request')({ port: port + 6});

    app.listen(port + 6, function() {
      request('/user.json', { 'Accept-Encoding': 'gzip' },
        function(err, res, data) {
          if (err) throw err;
          assert.equal(res.statusCode, 200);

          assert.equal(res.headers['content-type'], 'application/json; charset=UTF-8');
          assert.equal(data.length, '69');
          assert.equal(res.headers['content-encoding'], 'gzip');

          assert.deepEqual(data, file('user.gzip'));

          done();
        }
      );
    });
  });

});
