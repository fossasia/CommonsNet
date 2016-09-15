var express = require('express');
var app = express();
const execFile = require('child_process').execFile;

var cookieParser = require('cookie-parser');
var bodyParser = require('body-parser');
var exphbs = require('express-handlebars');
var expressValidator = require('express-validator');
var flash = require('connect-flash');
var session = require('express-session');
var passport = require('passport');
var assert = require('assert');

var mongoose = require('mongoose');
mongoose.connect(process.env.PROD_MONGODB);
var db = mongoose.connection;

var routes = require('./routes/app');
var users = require('./routes/users');

app.set('port', (process.env.PORT || 5000));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
app.use(cookieParser());

app.use(express.static(__dirname + '/'));

// views is directory for all template files
// app.set('views', __dirname + '/views');
// app.set('view engine', 'ejs');

app.get('/', function(request, response) {
  response.render('pages/index');
});



app.listen(app.get('port'), function() {
  console.log('Node app is running on port', app.get('port'));
});

app.get('/path_to_pdf', function(request, response) {
  const child = execFile('unoconv', ['-f', 'pdf', 'generate-wifi.odt'], function(error, stdout, stderr) {
    if (error) {
      throw error;
    }
    response.json({ message: stdout });
  });

});

// //lets require/import the mongodb native drivers.
// var mongodb = require('mongodb');
//
// //We need to work with "MongoClient" interface in order to connect to a mongodb server.
// var MongoClient = mongodb.MongoClient;

// Connection URL. This is where your mongodb server is running.


// // Use connect method to connect to the Server
// MongoClient.connect(url, function (err, db) {
//   if (err) {
//     console.log('Unable to connect to the mongoDB server. Error:', err);
//   } else {
//     //HURRAY!! We are connected. :)
//     console.log('Connection established to', url);
//     insertUsers(db)
//
//     // do some work here with the database.
//
//     //Close connection
//     // db.close();
//   }
// });

// var insertUsers = function(db) {
//   // Get the documents collection
//   var collection = db.collection('users');
//   // Insert some documents
//   collection.insert({
//     email: 'aga.ta@gmail.com',
//     password: 'test1',
//
//   })
// };

app.use(session({
  secret: 'secret',
  saveUninitialized: true,
  resave: true
}));

app.use(passport.initialize());
app.use(passport.session());

app.use(expressValidator({
  errorFormatter: function(param, msg, value) {
    var namespace = param.split('.')
      , root = namespace.shift()
      , formParam = root;

    while(namespace.length) {
      formParam += '[' + namespace.shift() + ']';
    }
    return {
      param : formParam,
      msg : msg,
      value : value
    };
  }
}));

app.use(flash());

app.use(function (req, res, next) {
  res.locals.success_msg = req.flash('success_msg');
  res.locals.error_msg = req.flash('error_msg');
  res.locals.error = req.flash('error');
  res.locals.user = req.user || null;
  next();

});

app.use('/', routes);
app.use('/users', users);









