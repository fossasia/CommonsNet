
// angular routes definded
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
            

    })
    //different controllers build
    .controller('AboutCtrl', function ($scope) {
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
         // contrller function - different steps in Wizard Form. Defining steps, different names and template which is used
         var vm = this;
        
        //Model
        vm.currentStep = 1;
        vm.steps = [
          {
            step: 1,
            name: 'Settings',
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
            name: 'Restrictions',
            template: "partials/4legalissues.html"
          },    
            {
          step: 5,
              name:'Confirm',
            template: "partials/confirmation.html"
          },  

        ];
        vm.user = {};

        vm.payment = {
          option: 'FREE'
        };

        //Function
        vm.gotoStep = function(newStep) {
          vm.currentStep = newStep;
        }
        
        // function to display different templates
        vm.getStepTemplate = function(){
          for (var i = 0; i < vm.steps.length; i++) {
                if (vm.currentStep == vm.steps[i].step) {
                    return vm.steps[i].template;
                }
            }
        }
        
        // function save to generate pdf based on different wizard form fields. 
        vm.save = function() {
           var docDefinition = {
               content: [
                 {
                  text: 'Wifi Details', style: 'header'
                   // text: 'by CommonsNet', style: 'header' 
                 },
                   {   text: 'Wireless Settings', style: 'anotherStyle' 

                  },
                 
                 {   
                  text:vm.ssid,  style: 'anotherStyle' 

                  },
                      {   
                  text:vm.password,  style: 'anotherStyle' 

                  },
                 {   
                  text:vm.securitytypes,  style: 'anotherStyle' 

                  },
                   {   
                  text:vm.capacity,  style: 'anotherStyle' 

                  },
                {   
                  text:vm.wifistandards,  style: 'anotherStyle' 

                  },
               ],

               styles: {
                 header: {
                   fontSize: 22,
                   bold: true,
                   alignment: 'center'
                   

                 },
                 anotherStyle: {
       italic: true,
       alignment: 'left'
     },
                 
               }
             };
                      
          

         
          pdfMake.createPdf(docDefinition).download('wifi.pdf');
        
    //     var doc = new jsPDF();
         
          
    //       doc.setFontSize(30);
    //       doc.text(80, 30, 'WiFi');

    //       doc.setFontSize(20);
    //       doc.text(20, 50, 'Wireless details');


    //       doc.fromHTML(vm.ssid, 20, 55, {
    //       'width': 300,
          
    //          });
          
    //       doc.fromHTML(vm.password, 20, 60, {
    //       'width': 300,
    //        });


    //       doc.fromHTML(vm.securitytypes, 20, 65, {
    //       'width': 300,
    //        });
          
    //        doc.fromHTML(vm.capacity, 20, 70, {
    //       'width': 300,
    //        });

       
    //       doc.fromHTML(vm.wifistandards, 20, 75, {
    //       'width': 300,
    //        });
        
    //       doc.setFontSize(20);
    //       doc.text(20, 95, 'Payment');

    //       doc.fromHTML(vm.payment.option, 20, 100, {
    //       'width': 300,
    //        });

    //       doc.setFontSize(20);
    //       doc.text(20, 120, 'Conditions');

    //       doc.fromHTML(vm.wificonditions, 20, 125, {
    //       'width': 300,
    //        });

    //       doc.setFontSize(20);
    //       doc.text(20, 145, 'Legal Restrictions');
         
    //       doc.fromHTML(vm.legalrestrictions, 20, 150, {
    //       'width': 300,
    //        });

          
    //       var file = doc.output('save', 'wifi.pdf');



    }
    })



