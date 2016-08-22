var express = require('express');
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




