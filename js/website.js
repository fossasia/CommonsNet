
// angular routes definded
angular.module('website', ['ngRoute', 'summernote']).
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
     
        $scope.options = {
            
              toolbar: [
                    
                      ['style', ['bold', 'italic' ]],
                      ['alignment', ['ul', 'ol' ]],
                   
                  ]
            };
      
   

       
    
      })



        .controller('ContactCtrl', function ($scope, $http) {
        $scope.title = 'Have questions? Contact us';
        $scope.disableButtons = true;
        $scope.submit = function() {
          
           if ($scope.myForm.$valid) {
                $scope.disableButtons = false;
               
        }


        $scope.submitForm = function() {

            // check to make sure the form is completely valid
           if ($scope.myForm.$valid) {
                $scope.message = "Thank you! Your email has been delivered."
            }

        };

}
    })
  
       
       


    
        
        .controller('WizardController', function ($scope, $http) {
         // contrller function - different steps in Wizard Form. Defining steps, different names and template which is used

   
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
     
  var vm = this;  

       
        //Wizard Model
        vm.currentStep = 1;
        vm.steps = [
          {
            step: 1,
            name: 'DETAILS',
            template: "partials/1settings.html"
          },
          // {
          //   step: 2,
          //   name: 'Payment',
          //   template: "partials/2payment.html"
          // },   
          // {
          //   step: 3,
          //   name: 'Conditions',
          //   template: "partials/3conditions.html"
          // },  
           {
            step: 2,
            name: 'CONDITIONS',
            template: "partials/4legalissues.html"
          },    
            {
          step: 3,
              name:'CONFIRMATION',
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
          var table = [];
               $http({
            method: 'GET',
            url: 'http://commonsnet.herokuapp.com/generatefile.fodt',
           
        }).success(function(data){
          console.log(vm.securitytypes);
          console.log(vm.conditions);


            var result = data.replace("INPUT_SSID", vm.ssid);
            result = result.replace("NETWORK_NAME", vm.ssid)
              
            if ((vm.password !== "") && (typeof vm.password !== "undefined")) {
             result = result.replace("INPUT_PASSWORD", "The owner informs that password is" + " " + vm.password);
           
              }
             else {
              result = result.replace("INPUT_PASSWORD", "The owner declars that there is no password")
             }

              if ((vm.securitytypes !== "") && (typeof vm.securitytypes !== "undefined") && (vm.securitytypes !== "OPEN")) {
              result  = result.replace("SECURITY_TYPE", "The owner informs that network is secured under" + " " + vm.securitytypes);
           
              }
              if((vm.securitytypes === "OPEN") && (vm.securitytypes !== "") && (typeof vm.securitytypes !== "undefined") )
                result = result.replace("SECURITY_TYPE", "The owner declares that the network is" + " " + vm.securitytypes)
             else {
               result = result.replace("SECURITY_TYPE", "The owner declars that the network is unsecured")
             }

              if((vm.wifistandards !== "") && (typeof vm.wifistandards !== "undefined") )
                result = result.replace("STANDARD_WIFI", "The owner declares that the network uses" + " " + vm.wifistandards)
             else {
               result = result.replace('<text:p text:style-name="P115">STANDARD_WIFI</text:p>', '')
             }
           
           
       
             
          if (vm.paymentfieldyes ==='yes') {
              result  = result.replace("PAYMENT_FIELD", "The owner declares that Wifi network is paid");
              } 
              else {
           result  = result.replace("PAYMENT_FIELD", "The owner declares that Wifi connection is completely free of any charge.");

              } 
              if (vm.paymentfieldyes ==='yes'  && (typeof vm.paymentfied !== "undefned" || (vm.paymentfield !== ''))) {
             result  = result.replace("FEE_FIELD", "The fee is" + " " + vm.paymentfield);
              }
              else {
             result  = result.replace('<text:p text:style-name="P116">FEE_FIELD</text:p>' , '');

              }
            if (vm.timelimityes ==='yes') {
             result  = result.replace("LIMIT_FIELD", "The owner informs that the access to the network is limited");
              }
             else {
              result = result.replace("LIMIT_FIELD", "The owner declares that the access to the network is unlimited")
             }

              if (vm.timelimityes ==='yes' && (typeof vm.timelimitfield !== "undefned" || (vm.timelimitfield !== '')) ){
                 result  = result.replace("TIME_LIMIT", "Users are allowed to use wifi" + " " + vm.timelimitfield );

              }
              else {
                result = result.replace('<text:h text:style-name="P140" text:outline-level="3"><text:span text:style-name="T108">TIME_LIMIT</text:span></text:h>', " ")
              }
 
            
            if (vm.serviceyes ==='yes') {
             result  = result.replace("SERVICE_FIELD", "The owner guarantees Wifi service" );
             // <text:h text:style-name="P132" text:outline-level="3">The service is provided &quot;as is&quot;, with no warranty or liability of whatsoever kind</text:h>

              }
              else {
            result  = result.replace("SERVICE_FIELD", "The owner does not guarantee Wifi service");

              }

            if (vm.specialdevices === "yes" || vm.specialsettings === "yes" || vm.socialprofile === "yes" || vm.acceptterms === "yes" || vm.socialprofile === "yes" || vm.downloading === "yes") {
              result = result.replace("CONDITIONS", "The owner declares that there are some requirments to use Wifi")
            }
            else {
              result = result. replace("CONDITIONS", "The owner declares that there is no requirments to use Wifi")
              result = result.replace(' <text:h text:style-name="P135" text:outline-level="3">To use Wifi you are required to SPECIAL_DEVICES SPECIAL_SETTINGS ACCEPT_TERMS LIKE DOWNLOAD</text:h>', ' ' )
              }
            
            if (vm.specialdevices ==='yes') {
                var result  = result.replace("SPECIAL_DEVICES", 'use special devices like' + ' ' + vm.specialdevicesfield+",");
                  }
            else {
               var result  = result.replace("SPECIAL_DEVICES", " ");

                  } 

             if (vm.specialsettings ==='yes') {
                var result  = result.replace("SPECIAL_SETTINGS", 'run special settings like' + ' ' + vm.specialsettingsfield+ ",");
                  }
            else {
               var result  = result.replace("SPECIAL_SETTINGS", " ");

                  } 

             if (vm.acceptterms ==='yes') {
                var result  = result.replace("ACCEPT_TERMS", 'accept terms of use' + ",");
                  }
            else {
               var result  = result.replace("ACCEPT_TERMS", " ");

                  } 
            

            if (vm.socialprofile ==='yes') {
                var result  = result.replace("LIKE", 'like social profile' + ",");
                  }
            else {
               var result  = result.replace("LIKE", " ");


                  } 
            if (vm.downloading ==='yes') {
                var result  = result.replace("DOWNLOAD", ', downloading pdf file'+ ".");
                  }
            else {
               var result  = result.replace("DOWNLOAD", " ");

                  } 
         
          if (vm.country !== "yes") {
            result = result.replace("LEGAL_RESTRICTIONS", "The owner informs that there are not known to him any legal restriction to use Wifi connection, using Internet resources or taking actions in the network in"+ " " + vm.countries);
            result = result.replace('<text:p text:style-name="P118">FIELD_RESTRICTIONS</text:p>', ' ')
          }
          else {
            result = result.replace("LEGAL_RESTRICTIONS", "The owner declares that the law of" + " " + vm.countries + " " + "prohibits:")
            result = result.replace("FIELD_RESTRICTIONS", "vm.legalrestrictions")
          }
          
          



          var link = document.getElementById('downloadlink');
          link.href = vm.makeTextFile(result);


        }).error(function(){
            alert("error");
        });
   


      
 
    }
  })


     



 


 
