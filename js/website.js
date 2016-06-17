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
       
        var doc = new jsPDF();
         
          
          doc.setFontSize(30);
          doc.text(80, 30, 'WiFi');

          doc.setFontSize(20);
          doc.text(20, 50, 'Wireless details');


          doc.fromHTML(vm.ssid, 20, 60, {
          'width': 300,
          
             });
          
          doc.fromHTML(vm.password, 20, 70, {
          'width': 300,
           });


          doc.fromHTML(vm.securitytypes, 20, 80, {
          'width': 300,
           });
          
           doc.fromHTML(vm.capacity, 20, 90, {
          'width': 300,
           });

       
          doc.fromHTML(vm.wifistandards, 20, 100, {
          'width': 300,
           });
        

          doc.setFontSize(20);
          doc.text(20, 120, 'Conditions');

          doc.fromHTML(vm.wificonditions, 20, 130, {
          'width': 300,
           });

          doc.setFontSize(20);
          doc.text(20, 150, 'Legal Restrictions');
         
          doc.fromHTML(vm.legalrestrictions, 20, 160, {
          'width': 300,
           });

          var file = doc.output('save', 'wifi.pdf');



    }
    })
         

     