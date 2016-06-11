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
        $scope.title = 'Have questions? Contact us';
        $scope.body = '';

    })
        .controller('BlogCtrl', function ($scope) {
        $scope.title = 'Blog';
        $scope.body = 'This is the about page body';

    
    })

        .controller('WizardController', function ($scope) {
         var vm = this;
        
        //Model
        vm.currentStep = 1;
        vm.steps = [
          {
            step: 1,
            name: "WIRELESS SETTINGS",
            template: "partials/1settings.html"
          },
          {
            step: 2,
            name: "PAYMENT",
            template: "partials/2payment.html"
          },   
          {
            step: 3,
            name: "CONDITIONS",
            template: "partials/3conditions.html"
          },  
           {
            step: 4,
            name: "LEGAL RESTRICTIONS",
            template: "partials/4legalissues.html"
          },                 
        ];
        vm.user = {};
        
        //Functions
        vm.gotoStep = function(newStep) {
          vm.currentStep = newStep;
        }
        
        vm.getStepTemplate = function(){
          for (var i = 0; i < vm.steps.length; i++) {
                if (vm.currentStep == vm.steps[i].step) {
                    return vm.steps[i].template;
                }
            }
        }
        
        vm.save = function() {
          alert(
            "Are you sure to save your file?"
            // "Name: " + vm.user.name + "\n" + 
            // "Email: " + vm.user.email + "\n" + 
            // "Age: " + vm.user.age
            );
        }

    
    })
         

     