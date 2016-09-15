
// angular routes definded
var app = angular.module('website', ['ngRoute', 'summernote', 'pascalprecht.translate']);
    app.config(function ($routeProvider) {
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
            .when('/contact', {
                templateUrl : 'partials/contact.html',
                controller  : 'ContactCtrl'
            })
               .when('/generate-wifi/confirmation', {
                templateUrl : 'partials/confirmation.html',
                controller  : 'ContactCtrl'
            })
           .when('/file', {
              templateUrl : 'partials/file_structure.html',
              controller: 'FileCtrl'
           })
          .when('/users/login', {
              templateUrl : 'partials/login.html',
              controller: 'LoginCtrl'
          })
          .when('/users/register', {
            templateUrl : 'partials/register.html'
            // controller: 'RegisterCtrl'
          })
          .otherwise ({redirectTo: '/'});


    })
    .config(function($translateProvider) {
      $translateProvider.useStaticFilesLoader({
        prefix: 'i18n/locale-',
        suffix: '.json'
      });

      $translateProvider.preferredLanguage('en');
    })






























