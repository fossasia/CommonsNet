var express = require('express');
var mongoose = require('mongoose');
var passport = require('passport');
var router = express.Router();
var localStrategy = require('passport-local').Strategy;

var File = require("../models/details");
var User = require("../models/user");
router.post('/save', function (req, res) {
    var location = req.body.location;
    var isp = req.body.isp;
    var name = req.body.name;
    var username = req.body.username;
    var password = req.body.password;
    var paid = req.body.paid;
    var datarate = req.body.datarate;
    var standard = req.body.standard;
    var security = req.body.security;
    var service = req.body.service;
    var condition = req.body.condition;
    var restriction = req.body.restriction;
    var devices = req.body.devices;
    var control = req.body.control;

    var newFile = new File ({
        location: location,
        isp: isp,
        name: name,
        username: username,
        password: password,
        paid: paid,
        datarate: datarate,
        standard: standard,
        security: security,
        service: service,
        condition: condition,
        restriction: restriction,
        devices: devices,
        control: control
    });

    File.createFile(newFile)
    req.user.details.push(newFile)
});

// var location = [];
router.get('/details', function (req,res) {
    mongoose.model('File').find( function(err, Location) {
        res.send(JSON.stringify(Details));


    });

});


module.exports = router;