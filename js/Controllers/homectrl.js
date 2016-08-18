app.controller('HomeCtrl', ['$scope', 'blog', function($scope, blog) { 
      $scope.title = 'Transparency is a key to a common success';
      blog.success(function(data) { 
            $scope.dictionary = data; 
            var results = [];
              for (item in data) {
                for (subItem in data[item]) {
                    var title = (data[item][subItem].title);
                    var img = (data[item][subItem].featured_image);
                      var url = (data[item][subItem].URL);
                 // console.log(title, img);
                   if(typeof title !== "undefined") {
                      results.push({'title': title, 'img': img, 'url': url});     
                    }
                 }
              }
     
          $scope.results = results
          $scope.icons = [
          {
            icon: 'info',
            title: 'INFORM',
            description: 'Collect your wifi details, put them in a form and provide a clear WiFi.'
          },
          {
            icon: 'share-alt',
            title: 'SHARE',
            description: 'Share your WiFi details in a transparent and easy-to understand way. '
          },
          {
            icon: 'home',
            title: 'FIND',
            description: 'Let your users to find WiFi generated file supported by CommonsNet at your place.'
          }, 
          {
            icon: 'wifi',
            title: 'CONNECT',
            description: 'Thanks to providing wifi details in a generated file let your users connect to trustworthy wifi and enjoy Internet resources.'
          }
          ]
           $scope.parts = [
           {
            title: 'FILL FORM',
            description: 'Collect all details about your Wifi connection and put them into a prepared an easy-to-follow form.'
           },
            {
            title: 'GENERATE',
            description: 'As you finish filling, click save button to generate file or code and share with your customers.'
           },
            {
            title: 'ENJOY',
            description: 'Enjoy providing your customers with a transparent and trustworthy wireless connection.'
           },
           ]
        });
}]);
