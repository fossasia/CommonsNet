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


            .when('/contact', {
                templateUrl : 'partials/contact.html',
                controller  : 'ContactCtrl'
            })
               .when('/generate-wifi/confirmation', {
                templateUrl : 'partials/confirmation.html',
                controller  : 'ContactCtrl'
            })



            .otherwise ({redirectTo: '/'});
            

    }).controller('AboutCtrl', function ($scope) {
        // $scope.title = 'About Page';
        // $scope.body = 'This is the about page body';
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


        .controller('WizardController', function ($scope) {
         var vm = this;
        
        //Model
        vm.currentStep = 1;
        vm.steps = [
          {
            step: 1,
            name: 'wireless settings',
            template: "partials/1settings.html"
          },
          {
            step: 2,
            name: 'Payment',
          
            template: "partials/2payment.html"
          },   
          {
            step: 3,
            name: 'Conditions',
            template: "partials/3conditions.html"
          },  
           {
            step: 4,
            name: 'Legal restrictions',
            template: "partials/4legalissues.html"
          },    
            {
          step: 5,
              name:'Confirmation',
            template: "partials/confirmation.html"
          },  

        ];
        vm.user = {};

        vm.payment = {
          option: 'FREE'
        };

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


          doc.fromHTML(vm.ssid, 20, 55, {
          'width': 300,
          
             });
          
          doc.fromHTML(vm.password, 20, 60, {
          'width': 300,
           });


          doc.fromHTML(vm.securitytypes, 20, 65, {
          'width': 300,
           });
          
           doc.fromHTML(vm.capacity, 20, 70, {
          'width': 300,
           });

       
          doc.fromHTML(vm.wifistandards, 20, 75, {
          'width': 300,
           });
        
          doc.setFontSize(20);
          doc.text(20, 95, 'Payment');

          doc.fromHTML(vm.payment.option, 20, 100, {
          'width': 300,
           });

          doc.setFontSize(20);
          doc.text(20, 120, 'Conditions');

          doc.fromHTML(vm.wificonditions, 20, 125, {
          'width': 300,
           });

          doc.setFontSize(20);
          doc.text(20, 145, 'Legal Restrictions');
         
          doc.fromHTML(vm.legalrestrictions, 20, 150, {
          'width': 300,
           });

          
          var file = doc.output('save', 'wifi.pdf');



    }
    })

