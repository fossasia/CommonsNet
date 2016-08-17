app.directive('appHome', function() { 
  return { 
    restrict: 'EA', 
    scope: { 
      info: '=' 
    }, 
    templateUrl: 'partials/apphome.html' 
  }; 
});