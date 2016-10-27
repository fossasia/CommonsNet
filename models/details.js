var mongoose = require('mongoose');
var bcrypt = require('bcryptjs');

var FileSchema = mongoose.Schema({
    location: {
        type: String,
        index: true
    },
    isp: {
        type: String
    },
    name: {
        type: String
    },
    username: {
        type: String
    },
    paid: {
        type: Boolean
    },
    password: {
        type: String
    },
    datarate: {
        type: Number
    },
    standard: {
        type: String, select: true
    },
    security: {
        type: String, select: true
    },
    restriction: {
        type: Boolean
    },
    devices: {
        type: Boolean
    },
    control: {
        type: Boolean
    }





});

var File = module.exports = mongoose.model('File', FileSchema);

module.exports.createFile = function(newFile, callback) {
    newFile.save(callback);
};


