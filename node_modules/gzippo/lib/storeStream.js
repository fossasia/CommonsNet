var util = require('util'),
    stream = require('stream'),
    FileAsset = require('./fileAsset');

/*
 * gzippo - StoreStream
 * Copyright(c) 2012 Tom Gallacher
 * MIT Licensed
 */

var StoreStream = module.exports = function StoreStream(store, fileName, options) {
  if (!(this instanceof StoreStream)) return new StoreStream(store, options);
  options = options || {};

  this._queue = [];
  this._processing = false;
  this._ended = false;
  this.readable = true;
  this.writable = true;

  this._asset = new FileAsset(fileName, options);
  this._store = store;
};

util.inherits(StoreStream, stream.Stream);

StoreStream.prototype.write = function write(chunk, cb) {
  if (this._ended) {
    return this.emit('error', new Error('Cannot write after end'));
  }

  if (arguments.length === 1 && typeof chunk === 'function') {
    cb = chunk;
    chunk = null;
  }

  if (!chunk) {
    chunk = null;
  } else if (typeof chunk === 'string') {
    chunk = new Buffer(chunk);
  } else if (!Buffer.isBuffer(chunk)) {
    return this.emit('error', new Error('Invalid argument'));
  }


  var empty = this._queue.length === 0;

  this._queue.push([chunk, cb]);
  this._process();
  if (!empty) {
    this._needDrain = true;
  }
  return empty;
};

StoreStream.prototype.flush = function flush(cb) {
  return this.write(cb);
};

StoreStream.prototype.end = function end(chunk, cb) {
  var self = this;
  this._ending = true;
  var ret = this.write(chunk, function() {
    self.emit('end');
    process.nextTick(function() {
      self._store.set(self._asset);
    });
    if (cb) cb();
  });
  this._ended = true;
  return ret;
};

StoreStream.prototype._process = function() {
  var self = this;
  if (this._processing || this._paused) return;

  if (this._queue.length === 0) {
    if (this._needDrain) {
      this._needDrain = false;
      this.emit('drain');
    }
    // nothing to do, waiting for more data at this point.
    return;
  }

  var req = this._queue.shift();
  var cb = req.pop();
  var chunk = req.pop();

  if (this._ending && this._queue.length === 0) {
    this._flush = true;
  }

  if (chunk !== null) {
    self.emit('data', chunk);
    this._asset.fileContents.push(chunk);
  }

  // finished with the chunk.
  self._processing = false;
  if (cb) cb();
  self._process();
};

StoreStream.prototype.destory = function() {
  this._paused = true;
  StoreStream.prototype.end.call(this);
};

StoreStream.prototype.pause = function() {
  this._paused = true;
  this.emit('pause');
};

StoreStream.prototype.resume = function() {
  this._paused = false;
  this._process();
};
