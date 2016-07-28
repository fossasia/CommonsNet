
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
       .controller('HomeCtrl', function ($scope, $http) {
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
                   if(typeof title !== "undefined")  {
                      results.push({'title': title, 'img': img, 'url': url});
              }

            }
          $scope.results = results;
          console.log(results )
        }
});

     
    })
   
         .controller('GenerateWiFiCtrl', function ($scope) {
        $scope.title = 'Generate WiFi';
        $scope.body = 'This is the about page body';
      })



        .controller('ContactCtrl', function ($scope) {
        $scope.title = 'Have questions? Contact us';
        $scope.body = '';

    })

    
        
        .controller('WizardController', function ($scope, $http) {
         // contrller function - different steps in Wizard Form. Defining steps, different names and template which is used
         var vm = this;
   
        $scope.countries = [
          {name:'France' },
          {name:'Poland' },
          {name:'Germany' },
          {name:'USA' },
          {name:'Russia' }
        ];

         // getting data from JSON file on ng-change select 
    $scope.update = function() {
         var country = vm.countries.name;
         console.log(country);
      
       var table = [];
         $http.get('restrictions.txt').success(function(data) {
           table=data
           for (var i=0; i<table.length; i++) {
              console.log(table[i].country);
                    if (country === table[i].country) {
                      vm.legalrestrictions = table[i].restrictions;
                    }
                 }
         });
            
        
      }
       
        //Wizard Model
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

        vm.makeTextFile = function (text) {
          var textFile = null
              var data = new Blob([text], {type: 'text/plain'});

              // If we are replacing a previously generated file we need to
              // manually revoke the object URL to avoid memory leaks.
              if (textFile !== null) {
                window.URL.revokeObjectURL(textFile);
              }

              textFile = window.URL.createObjectURL(data);

              return textFile;
            };
              
        
        // function save to replacing values in fodt file based on different wizard form fields. 
        vm.save = function() {
               $http({
            method: 'GET',
            url: 'https://commonsnet.herokuapp.com/generatefile.fodt',
           
        }).success(function(data){

            // With the data succesfully returnd, call our callback
          

            var result = data.replace("INPUTS", vm.ssid);
            var result  = result.replace("INPUTP", vm.password);
            var result  = result.replace("INPUTA", vm.securitytypes);
             var result  = result.replace("863", vm.capacity); 
              var result  = result.replace("INPUTT", vm.wifistandards);

              if (vm.paymentfieldyes ==='yes') {
             var result  = result.replace("PAIDFIELD", "YES" );
              }
              else {
           var result  = result.replace("PAIDFIELD", " - " );

              } 
              if (vm.paymentfieldyes ==='yes') {
             var result  = result.replace("HOWFIELD", vm.paymentfield);
              }
              else {
              var result  = result.replace("HOWFIELD", " - ");

              }
            if (vm.timelimityes ==='yes') {
             var result  = result.replace("800", "YES");
              }
              else {
            var result  = result.replace("800", " - ");

              }
 
            if (vm.timelimityes ==='yes') {
             var result  = result.replace("HOWTIME", vm.timelimitfield);
              }
              else {
            var result  = result.replace("HOWTIME", " - ");

              }

            if (vm.specialdevices ==='yes') {
                var result  = result.replace("452", 'Special devices');
                  }
            else {
               var result  = result.replace("452", " ");

                  } 
           
            if (vm.specialsettings ==='yes') {
                var result  = result.replace("163", 'Special settings');
                  }
            else {
               var result  = result.replace("163", " ");

                  } 
            
            if (vm.acceptterms ==='yes') {
                var result  = result.replace("365", 'Accepting terms of use');
                  }
            else {
               var result  = result.replace("365", " ");

                  } 
            if (vm.socialprofile ==='yes') {
                var result  = result.replace("186", 'Liking social profile');
                  }
            else {
               var result  = result.replace("186", " ");


                  } 
                  if (vm.downloading ==='yes') {
                var result  = result.replace("236", 'Downloading pdf file');
                  }
            else {
               var result  = result.replace("236", " ");

                  } 
         
          var result = result.replace("888", vm.countires);
          var result = result.replace("456", vm.legalrestrictions);
         console.log(result)



          var link = document.getElementById('downloadlink');
          link.href = vm.makeTextFile(result);


        }).error(function(){
            alert("error");
        });
   }


 
    })
          

     



 


 
