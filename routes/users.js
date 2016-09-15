var express = require('express');
var mongoose = require('mongoose');
var passport = require('passport');
var router = express.Router();
var localStrategy = require('passport-local').Strategy;

var User = require("../models/user");
var Location = require("../models/location")

router.post('/register', function (req,res){
    var username = req.body.username;
    var email = req.body.email;
    var password = req.body.password;
    var password2= req.body.password2;

    //Validation
    req.checkBody('username', 'Name is required').notEmpty();
    req.checkBody('email', 'Email is not valid').isEmail();
    req.checkBody('password', 'Password is required').notEmpty();
    req.checkBody('password2', 'Passwords do not match').equals(req.body.password)


    var errors = req.validationErrors();

    if (errors) {
        res.render('register', {
            errors: errors
        });
    }
    else {
        var newUser = new User({
            username: username,
            email: email,
            password: password
        });
        User.createUser(newUser, function (err, user){
            if (err) throw err;
            console.log(user);
        });

        req.flash("success_msg", "You are registered and can now login");

        res.redirect("/#/users/login");
    }

});

router.post('/insert', function (req, res) {
    var location = req.body.location;
    var newLocation = new Location({
        name: location
    })
    Location.createLocation(newLocation)
});



passport.use(new localStrategy(
    function(username, password, done) {
     User.getUserByUsername(username, function(err, user) {
         if (err) throw err;
            if (!user) {
                return done(null, false, {message: 'Unknown User'});
            }
            User.comparePassword(password, user.password, function(err, isMatch){
                if (err) throw err;
                if (isMatch) {
                    return done(null, user);
                } else {
                    return done(null, false, {message: 'Invalid Password'});
                }
            });
        });
    }
));
passport.serializeUser(function(user,done) {
    done(null, user.id);
});

passport.deserializeUser(function(id, done){
    User.getUserById(id, function(err,user) {
        done(err, user);
    })
});

router.post('/login',
    passport.authenticate('local', {successRedirect: '/', failureRedirect: 'users/login', failureFlash: true }),
    function(req, res) {
        res.redirect("/#/users/login");

    });
router.get('/logout', function (req, res) {
    req.logout();

    req.flash('success_msg', 'You are logged out');

    res.redirect('/#/users/login')
});

module.exports = router;
