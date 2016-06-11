# gzippo [![Build Status](https://secure.travis-ci.org/tomgco/gzippo.png?branch=master)](https://secure.travis-ci.org/tomgco/gzippo)

gzippo pronounced `g-zippo` is a gzip middleware for Connect / expressjs using node-compress for better performace, in node 0.6 and up will be using the new zlib api.

gzippo currently only supports only gzipping static content files however a release is in progress to introduce streaming support.

## Notice

Please note that gzippo@0.0.X branch will only be tested for nodejs 0.4, where the soon to be released gzippo@0.1.X will work for node 0.6

## Installation

	$ npm install gzippo

### Usage
#### Static Gzip

In your express/connect server setup, use as follows:

	var gzippo = require('gzippo');

	//Replace the static provider with gzippo's
	//app.use(express.static(__dirname + '/public'));
	app.use(gzippo.staticGzip(__dirname + '/public'));

Options:

- `contentTypeMatch` - A regular expression tested against the Content-Type header to determine whether the response should be gzipped or not. The default value is `/text|javascript|json/`.
- `maxAge` - cache-control max-age directive, defaulting to 1 day
- `clientMaxAge` - browser cache-control max-age directive, defaulting to 1 week
- `prefix` - A url prefix. If you want all your static content in a root path such as /resource/. Any url paths not matching will be ignored

Currently the gzipped version is created and stored in memory. This is not final and was done to get a working version
up and about.

Gzippo now uses the native Zlib support found in node >= 0.6

#### Streaming Gzip

Starting in Connect 2.X Expressjs has the ability to use a streaming gzip module provided natively by connect. As this 2.X branch is not currently stable I have back ported the compress.js component into gzippo.

	app.use(gzippo.staticGzip(__dirname + '/public'));
	app.use(gzippo.compress());

This has no caching and is currently unsupported as it will be included in a future connect 1.X release, up until then compress.js will be included in gzippo. 

## License

(The MIT License)

Copyright (c) 2011 Tom Gallacher &lt;<http://www.tomg.co>&gt;

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
