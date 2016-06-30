var FileAsset = module.exports = function FileAsset(name, options) {
  options = options || {};
  this._maxAge = options.maxAge || 86400000;
  this._mtime = options.mtime || new Date();

  this._fileName = name;
  this._ctime = +Date.now();
  this.fileContents = [];
  this.fileContentsLength = 0;
};

/**
 * Prototype.
 */

FileAsset.prototype = {
  set maxAge(maxAge) {
    this._maxAge = maxAge;
  },

  get maxAge() {
    return this._maxAge;
  },

  get mtime() {
    return this._mtime;
  },

  get isExpired() {
    return (this._ctime + this._maxAge) < +Date.now();
  },

  get name() {
    return this._fileName;
  },

  get content() {
    // var file = Buffer(this.fileContentsLength);
    // var pos = 0;
    // for (var i = 0; i < this.fileContents.length; i++) {
    //   // this.fileContents[i] = this.fileContents[i].toString();
    //   // buffer.copy(file, pos);
    //   // pos += buffer.length;
    // }

    return this.fileContents;
  },

  get length() {
    return this.fileContentsLength;
  },

  get data() {
    return {
      expires: this._expires,
      mtime: this._mtime,
      content: this.content,
      length: this.fileContentsLength
    };
  }
};
