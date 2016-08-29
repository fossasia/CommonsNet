
var express = require('express');
require('es6-promise').polyfill();
var app = express();
const execFile = require('child_process').execFile;


app.set('port', (process.env.PORT || 5000));

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

//lets require/import the mongodb native drivers.
var mongodb = require('mongodb');

//We need to work with "MongoClient" interface in order to connect to a mongodb server.
var MongoClient = mongodb.MongoClient;

// Connection URL. This is where your mongodb server is running.
var url = 'mongodb://localhost:27017/commonsnet';

// Use connect method to connect to the Server
MongoClient.connect(url, function (err, db) {
  if (err) {
    console.log('Unable to connect to the mongoDB server. Error:', err);
  } else {
    //HURRAY!! We are connected. :)
    console.log('Connection established to', url);
    insertUsers(db)

    // do some work here with the database.

    //Close connection
    // db.close();
  }
});

var insertUsers = function(db) {
  // Get the documents collection
  var collection = db.collection('users');
  // Insert some documents
  collection.insert({
   email: 'aga.ta@gmail.com', 
   password: 'test1',
  
})
}




