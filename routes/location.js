var express = require('express');
var mongoose = require('mongoose');
var passport = require('passport');
var router = express.Router();
var localStrategy = require('passport-local').Strategy;

var Location = require("../models/location");

router.post('/insert', function (req, res) {
    var location = req.body.location;
    var newLocation = new Location({
        name: location
    })
    Location.createLocation(newLocation)
});

// var location = [];
router.get('/locations', function (req,res) {
    mongoose.model('Location').find( function(err, Location) {
        var send = res.send(JSON.stringify(Location));
        console.log(send);
        // location.push(send);
        // console.log(location);

    });

});


module.exports = router;