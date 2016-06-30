/*!
 * Tom Gallacher
 *
 * MIT Licensed
 */

/**
 * Module dependencies.
 */

// Commented out as I think that connect is avalible from within express...
// try {
    // var staticMiddleware = require('connect').static;

// } catch (e) {
//  staticMiddleware = require('express').static;
// }

var fs = require('fs'),
    parse = require('url').parse,
    path = require('path'),
    zlib = require('zlib'),
    MemoryStore = require('./memory'),
    StoreStream = require('./storeStream'),
    FileAsset = require('./fileAsset'),
    send = require('send'),
    mime = send.mime
    ;

/**
 * Strip `Content-*` headers from `res`.
 *
 * @param {ServerResponse} res
 * @api public
 */

var removeContentHeaders = function(res){
    Object.keys(res._headers).forEach(function(field){
        if (0 === field.indexOf('content')) {
            res.removeHeader(field);
        }
    });
};

/**
 * Supported content-encoding methods.
 */

var methods = {
    gzip: zlib.createGzip,
    deflate: zlib.createDeflate
};

/**
 * Default filter function.
 */

exports.filter = function(req, res){
  var type = res.getHeader('Content-Type') || '';
  return type.match(/json|text|javascript/);
};

/**
 * Parse the `req` url with memoization.
 *
 * @param {ServerRequest} req
 * @return {Object}
 * @api private
 */

var parseUrl = function(req){
  var parsed = req._parsedUrl;
  if (parsed && parsed.href == req.url) {
    return parsed;
  } else {
    return req._parsedUrl = parse(req.url);
  }
};

/**
 * By default gzip's static's that match the given regular expression /text|javascript|json/
 * and then serves them with Connects static provider, denoted by the given `dirPath`.
 *
 * Options:
 *
 *  -   `maxAge` how long gzippo should cache gziped assets, defaulting to 1 day
 *  -   `clientMaxAge`  client cache-control max-age directive, defaulting to 0; 604800000 is one week.
 *  -   `contentTypeMatch` - A regular expression tested against the Content-Type header to determine whether the response
 *      should be gzipped or not. The default value is `/text|javascript|json/`.
 *  -   `prefix` - A url prefix. If you want all your static content in a root path such as /resource/. Any url paths not matching will be ignored
 *
 * Examples:
 *
 *     connect.createServer(
 *       connect.staticGzip(__dirname + '/public/');
 *     );
 *
 *     connect.createServer(
 *       connect.staticGzip(__dirname + '/public/', {maxAge: 86400000});
 *     );
 *
 * @param {String} path
 * @param {Object} options
 * @return {Function}
 * @api public
 */

exports = module.exports = function staticGzip(dirPath, options){
    options = options || {};

    var maxAge = options.maxAge || 86400000,
        contentTypeMatch = options.contentTypeMatch || /text|javascript|json/,
        clientMaxAge = options.clientMaxAge || 604800000,
        prefix = options.prefix || '',
        names = Object.keys(methods),
        compressionOptions = options.compression || {},
        store = options.store || new MemoryStore();

    if (!dirPath) throw new Error('You need to provide the directory to your static content.');
    if (!contentTypeMatch.test) throw new Error('contentTypeMatch: must be a regular expression.');

    dirPath = path.normalize(dirPath);

    return function(req, res, next) {
        var acceptEncoding = req.headers['accept-encoding'] || '',
            url,
            filename,
            contentType,
            charset,
            method;

        function pass(name) {
            send(req, url.substring(prefix.length))
                .maxage(clientMaxAge || 0)
                .root(dirPath)
                .pipe(res)
                ;
        }

        function setHeaders(stat, asset) {
            res.setHeader('Content-Type', contentType);
            res.setHeader('Content-Encoding', method);
            res.setHeader('Vary', 'Accept-Encoding');
            // if cache version is avalible then add this.
            if (asset) {
                // res.setHeader('Content-Length', asset.length);
                res.setHeader('ETag', '"' + asset.length + '-' + Number(asset.mtime) + '"');
                res.setHeader('Last-Modified', asset.mtime.toUTCString());
            }
            res.setHeader('Date', new Date().toUTCString());
            res.setHeader('Expires', new Date(Date.now() + clientMaxAge).toUTCString());
            res.setHeader('Cache-Control', 'public, max-age=' + (clientMaxAge / 1000));
        }

        // function gzipAndSend(filename, gzipName, mtime) {
        //     gzippo(filename, charset, function(gzippedData) {
        //         gzippoCache[gzipName] = {
        //             'ctime': Date.now(),
        //             'mtime': mtime,
        //             'content': gzippedData
        //         };
        //         sendGzipped(gzippoCache[gzipName]);
        //     });
        // }

        function forbidden(res) {
          var body = 'Forbidden';
          res.setHeader('Content-Type', 'text/plain');
          res.setHeader('Content-Length', body.length);
          res.statusCode = 403;
          res.end(body);
        }

        if (req.method !== 'GET' && req.method !== 'HEAD') {
            return next();
        }

        url = decodeURI(parseUrl(req).pathname);

        // Allow a url path prefix
        if (url.substring(0, prefix.length) !== prefix) {
            return next();
        }

        filename = path.normalize(path.join(dirPath, url.substring(prefix.length)));
        // malicious path
        if (0 != filename.indexOf(dirPath)){
          return forbidden(res);
        }

        // directory index file support
        if (filename.substr(-1) === '/') filename += 'index.html';


        contentType = mime.lookup(filename);
        charset = mime.charsets.lookup(contentType, 'UTF-8');
        contentType = contentType + (charset ? '; charset=' + charset : '');

        // default to gzip
        if ('*' == acceptEncoding.trim()) method = 'gzip';

        // compression method
        if (!method) {
            for (var i = 0, len = names.length; i < len; ++i) {
              if (~acceptEncoding.indexOf(names[i])) {
                method = names[i];
                break;
              }
            }
        }

        if (!method) return pass(filename);

        fs.stat(filename, function(err, stat) {

            if (err) {
                return next();
            }

            if (stat.isDirectory()) {
                return next();
            }

            if (!contentTypeMatch.test(contentType)) {
                return pass(filename);
            }

            // superceeded by if (!method) return;
            // if (!~acceptEncoding.indexOf('gzip')) {
            //     return pass(filename);
            // }

            var base = path.basename(filename),
                dir = path.dirname(filename),
                gzipName = path.join(dir, base + '.gz');

            var sendGzipped = function(filename) {
                var stream = fs.createReadStream(filename);

                req.on('close', stream.destroy.bind(stream));

                var storeStream = new StoreStream(store, filename, {
                    mtime: stat.mtime,
                    maxAge: options.maxAge
                });

                var compressionStream = methods[method](options.compression);

                stream.pipe(compressionStream).pipe(storeStream).pipe(res);

                stream.on('error', function(err){
                    if (res.headerSent) {
                        console.error(err.stack);
                        req.destroy();
                    } else {
                        next(err);
                    }
                });
            };

            store.get(decodeURI(filename), function(err, asset) {
                setHeaders(stat, asset);
                if (err) {
                    // handle error

                } else if (!asset) {
                    sendGzipped(decodeURI(filename));
                } else if ((asset.mtime < stat.mtime) || asset.isExpired) {
                    sendGzipped(decodeURI(filename));
                }
                else if (req.headers['if-modified-since'] && asset &&
                // Optimisation: new Date().getTime is 90% faster that Date.parse()
                +stat.mtime <= new Date(req.headers['if-modified-since']).getTime()) {
                    removeContentHeaders(res);
                    res.statusCode = 304;
                    return res.end();
                }
                else {
                    // StoreReadStream to pipe to res.
                    // console.log("hit: " + filename + "              length: " + asset.length);
                    for (var i = 0; i < asset.content.length; i++) {
                        res.write(asset.content[i], 'binary');
                    }
                    res.end();
                }
            });
        });
    };
};
