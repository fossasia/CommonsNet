angular.module('website', ['ngRoute']).
    config(function ($routeProvider) {
        $routeProvider
            .when('/', {
                templateUrl : 'partials/home.html',
                controller  : 'HomeCtrl'
            })
            .when('/about-us', {
                templateUrl : 'partials/about.html',
                controller  : 'AboutCtrl'
             })
            .when('/generate-wifi', {
                templateUrl : 'partials/generate-wifi.html',
                controller  : 'GenerateWiFiCtrl'
             })
            .when('/blog', {
                templateUrl : 'partials/blog.html',
                controller  : 'BlogCtrl'
            })
            .when('/contact', {
                templateUrl : 'partials/contact.html',
                controller  : 'ContactCtrl'
            })
            .otherwise ({redirectTo: '/'});
            

    }).controller('AboutCtrl', function ($scope) {
        $scope.title = 'About Page';
        $scope.body = 'This is the about page body';
    })
       .controller('HomeCtrl', function ($scope) {
         $scope.title = 'We are CommonsNet';
     
    })
         .controller('GenerateWiFiCtrl', function ($scope) {
        $scope.title = 'Generate WiFi';
        $scope.body = 'This is the about page body';
      })

        .controller('ContactCtrl', function ($scope) {
        $scope.title = 'Contact';
        $scope.body = 'This is the about page body';

    })
        .controller('BlogCtrl', function ($scope) {
        $scope.title = 'Blog';
        $scope.body = 'This is the about page body';

    
    })
         

     