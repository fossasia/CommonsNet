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
                    var date = (data[item][subItem].date);
                 // console.log(title, img);
                   if(typeof title !== "undefined") {
                       results.push({'title': title, 'img': img, 'url': url, 'date': date});
                    }
                 }
              }

          var sorted = results.sort(function(a, b){return new Date(b.date) - new Date(a.date) });
                console.log(sorted);
          results = sorted.slice(0, 8);
                console.log(results);

          $scope.results = results
          $scope.icons = [
          {
            icon: 'home.icons.inform.icon',
            title: 'home.icons.inform.title',
            description: 'home.icons.inform.description'
          },
          {
            icon: 'home.icons.share.icon',
            title: 'home.icons.share.title',
            description: 'home.icons.share.description '
          },
          {
            icon: 'home.icons.find.icon',
            title: 'home.icons.find.title',
            description: 'home.icons.find.description'
          },
          {
            icon: 'home.icons.connect.icon',
            title: 'home.icons.connect.title',
            description: 'home.icons.connect.description'
          }
          ]
           $scope.parts = [
           {
            title: 'home.parts.fill.title',
            description: 'home.parts.fill.description'
           },
            {
            title: 'home.parts.create.title',
            description: 'home.parts.create.description'
           },
            {
            title: 'home.parts.enjoy.title',
            description: 'home.parts.enjoy.description'
           },
           ]
        });
}]);
