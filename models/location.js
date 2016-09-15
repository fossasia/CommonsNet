var mongoose = require('mongoose');
var bcrypt = require('bcryptjs');

var LocationSchema = mongoose.Schema({
    name: {
        type: String
    }
});

var Location = module.exports = mongoose.model('Location', LocationSchema);

module.exports.createLocation = function(newLocation, callback) {
    newLocation.save(callback);
};