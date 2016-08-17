app.directive('appInfo', function() { 
	  return { 
	    restrict: 'EA', 
	    scope: { 
	      info: '=' 
	    }, 
	    templateUrl: 'partials/appInfo.html' 
	  
	  }; 
});
// test