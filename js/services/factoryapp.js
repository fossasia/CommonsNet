app.factory('blog', ['$http', function($http) { 
  return $http.get('https://public-api.wordpress.com/rest/v1.1/sites/commonsnetblog.wordpress.com/posts/') 
        .success(function(data) { 
            return data; 
             
        })
            .error(function(err) { 
              return err; 
            }); 
}]);

