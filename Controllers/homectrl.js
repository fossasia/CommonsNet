       app.controller('HomeCtrl', function ($scope, $http) {
         $scope.title = 'We are CommonsNet';

        $http.get("https://public-api.wordpress.com/rest/v1.1/sites/commonsnetblog.wordpress.com/posts/")
         .then(function(response) {
          
          var dictionary = response.data;
          var results = [];
            for (item in dictionary) {
              for (subItem in dictionary[item]) {
                var title = (dictionary[item][subItem].title);
                var img = (dictionary[item][subItem].featured_image);
                var url = (dictionary[item][subItem].URL);
                
        
                 // console.log(title, img);
                   if(typeof title !== "undefined") {

                      results.push({'title': title, 'img': img, 'url': url});
                    
              }

            }
          $scope.results = results.slice(-8);
          console.log(results )
        }
});

     
    })