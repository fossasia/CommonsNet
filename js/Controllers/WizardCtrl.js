 app.controller('WizardController', ['$scope', 'wizard', 'file', function($scope, wizard, file) { 
      var vm = this;       
      var table = [];
            wizard.success(function(data) { 
               $scope.table = data
               table=data
    
              })
              $scope.update = function() {
              var country = vm.countries.name;
              for (var i=0; i<table.length; i++) {
                            console.log(table[i].country);
                                  if (country === table[i].country) {
                                    vm.legalrestrictions = table[i].restrictions;
                                  }
                               }
                   }         


   
        $scope.countries = [
          {name:'France' },
          {name:'Poland' },
          {name:'Germany' },
          {name:'USA' },
          {name:'Russia' }
        ];

        $scope.add = function () {
          var paidrow =   angular.element(document.querySelector('#paidrow'));
               var childrow = $compile(paidrow)(paidrow);
               container.append(childrow);
             }

        vm.currentStep = 1;
        vm.steps = [
          {
            step: 1,
            name: 'DETAILS',
            template: "partials/1settings.html"
          },  
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

        vm.gotoStep = function(newStep) {
          vm.currentStep = newStep;
          if (vm.currentStep === 3) {

              var link = "http://commonsnet.herokuapp.com/#/file?ssid=" + vm.ssid + "&password=" + vm.password + "&security=" + vm.securitytypes + "&standard=" + vm.wifistandards + "&ban_control=" + vm.dualbandyes + "&payment=" + vm.paymentfieldyes + "&fee=" + vm.paymentfield + "&timelimit=" + vm.timelimityes + "&limit=" + vm.timelimitfield + "&service=" + vm.serviceyes +  "&specialdevices=" + vm.specialdevices + "&devices=" + vm.specialdevicesfield + "&specialsettings=" + vm.specialsettings + "&settings=" + vm.specialsettingsfield + "&acceptterms=" + vm.acceptterms + "&liking=" + vm.socialprofile + "&downloading=" + vm.downloading + "&restrictions=" + vm.country + "&country=" + vm.countries + "&law=" + vm.legalrestrictions             
              vm.code = '<a href="' + link + '">CommonsNet</a>'
          }
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
            file.success(function(data){
            var result = data.replace("INPUT_SSID", vm.ssid);
                result = result.replace("NETWORK_NAME", "The owner provides" + " "  + vm.ssid +  " " + "network connection")
              
              if ((vm.password !== "") && (typeof vm.password !== "undefined")) {
                  result = result.replace("INPUT_PASSWORD", "The owner informs that password is" + " " + vm.password);
                  }
              else  {
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
                  result = result.replace("STANDARD_WIFI", "The owner declares that the network uses" + " " + vm.wifistandards + "" + "standard")
             else {
                  result = result.replace('<text:p text:style-name="P149">STANDARD_WIFI</text:p>', '')
             }
              if ((vm.dualbandyes !== "") && (typeof vm.dualbandyes !== "undefined")) {
                  result = result.replace("BANDWIDTH_CONTROL", "The Owner declares to provide a bandwidth control");
              }
              else {
                  result= result.replace('<text:p text:style-name="P149">BANDWIDTH_CONTROL</text:p>', '')
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
                result  = result.replace(' <text:p text:style-name="P150">FEE_FIELD</text:p>' , 'The owner declares that he does not require any fee');

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
                result = result.replace(' <text:h text:style-name="P167" text:outline-level="3">TIME_LIMIT</text:h>', " ")
              }
 
            
            if (vm.serviceyes ==='yes') {
                result  = result.replace("SERVICE_FIELD", "The owner guarantees Wifi service" );
             // <text:h text:style-name="P132" text:outline-level="3">The service is provided &quot;as is&quot;, with no warranty or liability of whatsoever kind</text:h>

              }
            else {
                  result  = result.replace("SERVICE_FIELD", "The owner does not guarantee Wifi service");
              }

            if (vm.specialdevices === "yes" || vm.specialsettings === "yes" || vm.socialprofile === "yes" || vm.acceptterms === "yes" || vm.socialprofile === "yes" || vm.downloading === "yes" || vm.register === "yes" || vm.newsletter === "yes" || vm.mobilenumber === "yes" || vm.emailaddress === "yes" || vm.personaldetails === "yes" || vm.autoupdatedisabled === "yes" || vm.referencenumber === "yes" ) {
              result = result.replace("CONDITIONS", "The Owner declares that there are some requirments to use Wifi")
            }
            else {
              result = result. replace("CONDITIONS", "The Owner declares that there are not any requirmenets to use Wifi")
              result = result.replace('<text:h text:style-name="P164" text:outline-level="3">The Owner informs that to use Wifi the User is required to:</text:h>', ' ')
              result = result.replace('<text:list xml:id="list533319923915382650" text:style-name="L2"><text:list-item><text:p text:style-name="P155">SPECIAL_DEVICES</text:p></text:list-item><text:list-item><text:p text:style-name="P155">SPECIAL SETTINGS</text:p></text:list-item><text:list-item><text:p text:style-name="P155">ACCEPT_TERMS</text:p></text:list-item><text:list-item><text:p text:style-name="P155">LIKE</text:p></text:list-item><text:list-item><text:p text:style-name="P155">DOWNLOAD</text:p></text:list-item> <text:list-item><text:p text:style-name="P155">REGISTER</text:p></text:list-item><text:list-item><text:p text:style-name="P155">NEWSLETTER</text:p></text:list-item><text:list-item><text:p text:style-name="P155">MOBILE_NUMER</text:p></text:list-item> <text:list-item><text:p text:style-name="P155">EMAIL_ADDRESS</text:p></text:list-item><text:list-item><text:p text:style-name="P155">PERSONAL_DETAILS</text:p></text:list-item><text:list-item><text:p text:style-name="P155">DISABLE_FUNCTION</text:p></text:list-item><text:list-item><text:p text:style-name="P155">REFERENCE_NUMBER</text:p> </text:list-item></text:list>', '')
              }
            
            if (vm.specialdevices ==='yes') {
              result  = result.replace("SPECIAL_DEVICES", 'use special devices like' + ' ' + vm.specialdevicesfield );
                  }
            else {
              result  = result.replace('<text:p text:style-name="P155">SPECIAL_DEVICES</text:p>', ' ');

                  } 

             if (vm.specialsettings ==='yes') {
                result  = result.replace("SPECIAL_SETTINGS", 'run special settings like' + ' ' + vm.specialsettingsfield);
                  }
            else {
                result  = result.replace('<text:p text:style-name="P155">SPECIAL SETTINGS</text:p>', " ");

                  } 

             if (vm.acceptterms ==='yes') {
                result  = result.replace("ACCEPT_TERMS", 'accept terms of use');
                  }
            else {
                result  = result.replace('<text:p text:style-name="P155">ACCEPT_TERMS</text:p>', " ");

                  } 

      
            if (vm.socialprofile ==='yes') {
                result  = result.replace("LIKE", 'like social profile');
                  }
            else {
                result  = result.replace('<text:p text:style-name="P155">LIKE</text:p>', " ");


                  } 
            if (vm.downloading ==='yes') {
                result  = result.replace("DOWNLOAD", 'download terms of use pdf file');
                  }
            else {
                result  = result.replace('<text:p text:style-name="P155">DOWNLOAD</text:p>', " ");

                  } 
            if (vm.register === 'yes') {
                result = result.replace("REGISTER", 'register to website to use Wifi')
            }
            else {
                result = result.replace('<text:p text:style-name="P155">REGISTER</text:p>', ' ')
            }
            if (vm.newsletter === 'yes') {
                result = result.replace("NEWSLETTER", 'sign up to a newsletter')
            }
            else {
              result = result.replace('<text:p text:style-name="P155">NEWSLETTER</text:p>', '')
            }
            if (vm.mobilenumber === 'yes') {
              result = result.replace("MOBILE_NUMER", 'give a mobile number')
            }
            else  {
              result = result.replace('<text:p text:style-name="P155">MOBILE_NUMER</text:p>', '')
            }

            if (vm.emailaddress === 'yes') {
              result = result.replace("EMAIL_ADDRESS", 'give an email address')
            }
            else { 
              result = result.replace('<text:p text:style-name="P155">EMAIL_ADDRESS</text:p>', '') 
            }
             if (vm.personaldetails === 'yes') {
              result = result.replace("PERSONAL_DETAILS", 'provide personal details like age, gender etc.')
            }
            else { 
              result = result.replace('<text:p text:style-name="P155">PERSONAL_DETAILS</text:p>', '') 
            }
            if (vm.autoupdatedisabled === 'yes') {
              result = result.replace("DISABLE_FUNCTION", 'disable auto-update function')
            }
            else {
              result = result.replace('<text:p text:style-name="P155">DISABLE_FUNCTION</text:p>', ' ')
            }
            if (vm.referencenumber === 'yes') {
                result = result.replace("REFERENCE_NUMBER", 'have a reference number')
            }
            else {
              result = result.replace('<text:p text:style-name="P155">REFERENCE_NUMBER</text:p>', ' ')
            }

           if (vm.ownrestrictionyes === "yes") {
            result = result.replace("OWN_RESTRICTIONS", "The Owner informs that the Users are forbidden to" + vm.ownrestrictionsfield);
        
            }
            else {
              result = result.replace('<text:p text:style-name="P150">OWN_RESTRICTIONS</text:p>', ' ')
            
          }
            if (vm.limitdevicesyes === "yes") {
              result = result.replace("NUMBER_OF_DEVICES", "The Owner informs that the Users are forbidden to use more than" + ' ' + vm.limitdevicesfield + ' ' + 'devices');
        
            }
            else {
              result = result.replace('<text:p text:style-name="P161">NUMBER_OF_DEVICES</text:p>', ' ')
            
          }
          
          if (vm.country !== "yes") {
            result = result.replace("LEGAL_RESTRICTIONS", "The owner informs that there are not known to him any legal restriction to use Wifi connection, using Internet resources or taking actions in the network.");
            result = result.replace('<text:p text:style-name="P151">LEGAL_RESTRICTIONS</text:p>', ' ')
            result = result.replace('<text:p text:style-name="P152">FIELD_RESTRICTIONS</text:p>', ' ')
          }
          else {
            result = result.replace("LEGAL_RESTRICTIONS", "The owner declares that the law of" + " " + vm.countries + " " + "prohibits:")
            result = result.replace("FIELD_RESTRICTIONS", vm.legalrestrictions)
          }
          
          
          var link = document.getElementById('downloadlink');
          link.href = vm.makeTextFile(result);
        })
  
    }
}]);
