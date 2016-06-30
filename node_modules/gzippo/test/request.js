var http = require('http')
  ;

var port;
// basic request mocking function
module.exports = function (options) {
  port = options.port || 32123;
  return request;
};

var request = function(path, headers, callback) {
  var options = {
    host: '127.0.0.1',
    port: port,
    path: path,
    headers: headers || {},
    method: 'GET'
  };

  var req = http.request(options, function(res) {
    var buffers = []
      , total = 0;

    res.on('data', function(buf) {
      buffers.push(buf);
      total += buf.length;
    });

    res.on('end', function() {
      var data = new Buffer(total)
        , offset = 0;

      for (var i = 0; i < buffers.length; i++) {
        buffers[i].copy(data, offset);
        offset += buffers[i].length;
      }

      callback(null, res, data);
    });

    res.on('error', function(err) {
      callback(err);
    });
  });

  req.on('error', function(err) {
    callback(err);
  });

  req.end();
};