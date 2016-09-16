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
var location = require('./routes/location');
var details = require('./routes/details');


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
app.use('/location', location);
app.use('/details', details);









