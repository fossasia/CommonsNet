
/*!
 * gzippo - MemoryStore
 *
 * MIT Licensed
 */

var Store = require('./store'),
    util = require('util');

/**
 * new `MemoryStore`.
 *
 * @api public
 */

var MemoryStore = module.exports = function MemoryStore() {
  Store.call(this);
  this.assets = {};
};

util.inherits(MemoryStore, Store);

/**
 * Attempt to fetch an asset by its filename - `file`.
 *
 * @param {String} fileName
 * @param {Function} cb
 * @api public
 */

MemoryStore.prototype.get = function(fileName, cb) {
  var that = this;
  process.nextTick(function(){
    var expires,
        asset = that.assets[fileName];
    if (asset) {
      // expires = (typeof asset.expires === 'string') ?
      //   +Date.parse(asset.expires) :
      //   asset.expires;
      // if (!expires || +Date.now() < expires) {
        cb(null, asset);
      // } else {
      //   that.purgeFile(file, cb);
      // }
    } else {
      cb();
    }
  });
};

/**
 *
 * @param {FileAsset} asset
 * @param {Function} cb
 * @api public
 */

MemoryStore.prototype.set = function(asset, cb) {
  var that = this;
  process.nextTick(function() {
    that.assets[asset.name] = asset.data;
    if(cb instanceof Function) cb();
  });
};

/**
 * purge the cache
 *
 * @param {Function} cb
 * @api public
 */

MemoryStore.prototype.purge = function(cb){
  this.assets = {};
  if(cb instanceof Function) cb();
};

/**
 * purge the an item from thecache
 *
 * @param {FileAsset} asset
 * @param {Function} cb
 * @api public
 */

MemoryStore.prototype.purgeFile = function(asset, cb){
  process.nextTick(function() {
    delete this.assets[asset.name];
    if(cb instanceof Function) cb();
  });
};

/**
 * Fetch number of cached files.
 *
 * @param {Function} fn
 * @api public
 */

MemoryStore.prototype.length = function(cb){
  cb(null, Object.keys(this.assets).length);
};