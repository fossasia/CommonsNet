 app.controller('WizardController', ['$scope', 'wizard', 'file', function($scope, wizard, file) {
      var vm = this;
      // vm.legalformatted = ''
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


      $scope.choices = [{'id':'choice1', "payment": '', "timelimit": '', "datalimit": ''}];

      $scope.addNewChoice = function() {
        var newItemNo = $scope.choices.length+1;
        $scope.choices.push({'id':'choice'+newItemNo});
        console.log($scope.choices)
      };

      $scope.removeChoice = function() {
        var lastItem = $scope.choices.length-1;
        $scope.choices.splice(lastItem);
      };
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
              var table = []
               for (var i=0; i<$scope.choices.length; i++) {
                if ($scope.choices[i].payment || $scope.choices[i].timelimit || $scope.choices[i].datalimit) {
                    table.push({payment: $scope.choices[i].payment, timelimit: $scope.choices[i].timelimit, datalimit: $scope.choices[i].datalimit});
                     console.log(table)
                    }
                  }
                var link = "http://commonsnet.herokuapp.com/#/file?ssid=" + vm.ssid + "&password=" + vm.password + "&loginname=" + vm.loginname + "&paid=" + vm.paidfield +  "&speed=" + vm.datarate + "&standard=" + vm.wifistandards + "&security=" + vm.securitytypes + "&service=" + vm.servicefield + "&captiveportal=" + vm.captiveportal + "&specialdevices=" + vm.specialdevices + "&devices=" + vm.specialdevicesfield + "&specialsettings=" + vm.specialsettings + "&settings=" + vm.specialsettingsfield + "&acceptterms=" + vm.acceptterms + "&liking=" + vm.socialprofile + "&downloading=" + vm.downloading + "&register=" + vm.register + "&newsletter=" +vm.newsletter + "&mobilenumber=" + vm.mobilenumber + "&emailaddress=" + vm.emailaddress + "&personaldetails=" + vm.personaldetails + "&autodisable=" + vm.autoupdatedisabled + "&rnumber=" +vm.referencenumber
              // var link = "http://commonsnet.herokuapp.com/#/file?ssid=" + vm.ssid + "&password=" + vm.password + "&security=" + vm.securitytypes + "&standard=" + vm.wifistandards + "&datarate=" + vm.datarate + "&ban_control=" +  vm.dualbandyes + "&service=" + vm.serviceyes +  "&limits=" + JSON.stringify(table) + "&captiveportal=" + vm.captiveportal + "&specialdevices=" + vm.specialdevices + "&devices=" + vm.specialdevicesfield + "&specialsettings=" + vm.specialsettings + "&settings=" + vm.specialsettingsfield + "&acceptterms=" + vm.acceptterms + "&liking=" + vm.socialprofile + "&downloading=" + vm.downloading + "&register=" + vm.register + "&newsletter=" +vm.newsletter + "&mobilenumber=" + vm.mobilenumber + "&emailaddress=" + vm.emailaddress + "&personaldetails=" + vm.personaldetails + "&autodisable=" + vm.autoupdatedisabled + "&rnumber=" +vm.referencenumber + "&ownrestr=" + vm.ownrestrictionyes + "&ownrestfield=" + vm.ownrestrictionsfield + "&limitdevices=" + vm.limitdevicesyes + "&howmany=" + vm.limitdevicesfield+ "&restrictions=" + vm.country + "&country=" + vm.countries + "&law=" + vm.legalrestrictions
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
              

            if(vm.countries) {
              var current_country_name = vm.countries.name
                for (var i=0; i < $scope.table.length; i++){
                  if (current_country_name === $scope.table[i].country){
                    for(var j =0; j < $scope.table[i].restrictions.length; j++){
                      vm.legalformatted += '<text:p text:style-name="P187">' + $scope.table[i].restrictions[j] + '</text:p>'

                      }

              }
            }
          }
          // NETWORK NAME

            var result = data.replace("INPUT_SSID", vm.ssid);
                result = result.replace("NETWORK_NAME", "The network name is:" + " "  + vm.ssid)
            // PASSWORD

              if ((vm.password !== "") && (typeof vm.password !== "undefined")) {
                  result = result.replace("PASSWORD", "The password is:" + " " + vm.password);
                  }
              else  {
                  result = result.replace('<text:p text:style-name="P209">PASSWORD</text:p>', " ")
              }
              // LOGIN NAME
               if ((vm.loginname !== "") && (typeof vm.loginname !== "undefined")) {
                  result = result.replace("LOGIN_NAME", "The login is:" + " " + vm.loginname);
                  }
              else  {
                  result = result.replace(' <text:p text:style-name="P209">LOGIN_NAME</text:p>', " ")
              }

              // SPEED
                if ((vm.datarate !== "") && (typeof vm.datarate !== "undefined")) {
                  result = result.replace("SPEED", "The speed is:" + " " + vm.datarate);
                  }
              else  {
                  result = result.replace(' <text:p text:style-name="P209">SPEED</text:p>', ' ')
              }
              // ISP 
               if ((vm.isp !== "") && (typeof vm.isp !== "undefined")) {
                  result = result.replace("ISP", "The Internet Service Provider is:" + " " + vm.isp);
                  }
              else  {
                  result = result.replace('<text:p text:style-name="P209">ISP</text:p>', " ")
              }
              if (vm.paidyes === "") {
                  result = result.replace("PAID", "The Wifi is paid" + " " + vm.paidfield);
                  }
              else  {
                  result = result.replace('<text:p text:style-name="P211">PAID</text:p>', " ")
              }


              // SECURITY
              if ((vm.securitytypes !== "") && (typeof vm.securitytypes !== "undefined") && (vm.securitytypes !== "OPEN")) {
                  result  = result.replace("SECURITY_TYPE", "The network is secured under" + " " + vm.securitytypes);
              }
              if((vm.securitytypes === "OPEN") && (vm.securitytypes !== "") && (typeof vm.securitytypes !== "undefined") )
                  result = result.replace("SECURITY_TYPE", "The Network is open")
             else {
                  result = result.replace("SECURITY_TYPE", "The Network is unsecured")
             }
             // WIFI STANDARDS 
              if((vm.wifistandards !== "") && (typeof vm.wifistandards !== "undefined") )
                  result = result.replace("STANDARD_WIFI", "The network uses" + " " + vm.wifistandards + " " + "standard")
             else {
                  result = result.replace('<text:p text:style-name="P210">STANDARD_WIFI</text:p>', '')
             }
             


          // console.log($scope.choices)
          //     var line = ''

          //     for (var i=0; i<$scope.choices.length; i++) {
          //       if ($scope.choices[i].payment || $scope.choices[i].timelimit || $scope.choices[i].datalimit) {
          //           line += '<text:p text:style-name="P183">'+ "Paid:" +  " " + $scope.choices[i].payment +  " " + " " + "Time Limit: " +  " " + $scope.choices[i].timelimit + " " + " " + "Data Usage Limit:" + " " +  $scope.choices[i].datalimit + '</text:p>'

          //       }
          //     }
          //     result = result.replace('<text:p text:style-name="P193">PAID_FIELD</text:p>', line);


           // CONDITIONS

            if (vm.specialdevices === "yes" || vm.specialsettings === "yes" || vm.socialprofile === "yes" || vm.acceptterms === "yes" || vm.socialprofile === "yes" || vm.downloading === "yes" || vm.register === "yes" || vm.newsletter === "yes" || vm.mobilenumber === "yes" || vm.emailaddress === "yes" || vm.personaldetails === "yes" || vm.autoupdatedisabled === "yes" || vm.referencenumber === "yes" ) {
              result = result.replace("CONDITIONS", "Conditions to use Wifi:")
            }
            else {
              result = result. replace("CONDITIONS", 'There are not any conditions to use  Wifi ')
              result = result.replace('<text:p text:style-name="P212">SPECIAL_DEVICES</text:p>', ' ')
              result = result.replace('<text:p text:style-name="P212">SPECIAL SETTINGS</text:p>', ' ')
              result = result.replace('<text:p text:style-name="P212">ACCEPT_TERMS</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">LIKE</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">DOWNLOAD</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">REGISTER</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">NEWSLETTER</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">MOBILE_NUMER</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">EMAIL_ADDRESS</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">PERSONAL_DETAILS</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">DISABLE_FUNCTION</text:p>', '')
              result = result.replace('<text:p text:style-name="P212">REFERENCE_NUMBER</text:p>', '')

              }

            if (vm.specialdevices === 'yes') {
                  result  = result.replace("SPECIAL_DEVICES", 'use special devices like' + ' ' + vm.specialdevicesfield);
                  }
            else {
              result  = result.replace('<text:p text:style-name="P212">SPECIAL_DEVICES</text:p>', ' ');
                  }

             if (vm.specialsettings === 'yes') {
                result  = result.replace("SPECIAL_SETTINGS", 'run special settings like' + ' ' + vm.specialsettingsfield);
                  }
            else {
                result  = result.replace('<text:p text:style-name="P212">SPECIAL SETTINGS</text:p>', ' ');
                  }

             if (vm.acceptterms === 'yes') {
                result  = result.replace("ACCEPT_TERMS", 'accept terms of use');
                  }
            else {
                result  = result.replace('<text:p text:style-name="P212">ACCEPT_TERMS</text:p>', ' ');

                  }


            if (vm.socialprofile === 'yes') {
                result  = result.replace("LIKE", 'like social profile');
                  }
            else {
                result  = result.replace('<text:p text:style-name="P212">LIKE</text:p>', ' ');


                  }
            if (vm.downloading === 'yes') {
                result  = result.replace("DOWNLOAD", 'download terms of use pdf file');
                  }
            else {
                result  = result.replace('<text:p text:style-name="P212">DOWNLOAD</text:p>', ' ');
                  }
            if (vm.register === 'yes') {
                result = result.replace("REGISTER", 'register to website to use Wifi')
            }
            else  {
                result = result.replace('<text:p text:style-name="P212">REGISTER</text:p>', ' ')
            }
            if (vm.newsletter === 'yes') {
                result = result.replace("NEWSLETTER", 'sign up to a newsletter')
            }
            else {
              result = result.replace('<text:p text:style-name="P212">NEWSLETTER</text:p>', ' ')
            }
            if (vm.mobilenumber === 'yes') {
              result = result.replace("MOBILE_NUMER", 'give a mobile number')
            }
            else  {
              result = result.replace('<text:p text:style-name="P212">MOBILE_NUMER</text:p>', ' ')
            }

            if (vm.emailaddress === 'yes') {
              result = result.replace("EMAIL_ADDRESS", 'give an email address')
            }
            else {
              result = result.replace('<text:p text:style-name="P212">EMAIL_ADDRESS</text:p>', ' ')
            }
             if (vm.personaldetails === 'yes') {
              result = result.replace("PERSONAL_DETAILS", 'provide personal details like age, gender etc.')
            }
            else {
              result = result.replace('<text:p text:style-name="P212">PERSONAL_DETAILS</text:p>', ' ')
            }
            if (vm.autoupdatedisabled === 'yes') {
              result = result.replace("DISABLE_FUNCTION", 'disable auto-update function')
            }
            else {
              result = result.replace('<text:p text:style-name="P212">DISABLE_FUNCTION</text:p>', ' ')
            }
            if (vm.referencenumber === 'yes') {
                result = result.replace("REFERENCE_NUMBER", 'have a reference number')
            }
            else {
              result = result.replace('<text:p text:style-name="P212">REFERENCE_NUMBER</text:p>', ' ')
            }

           if (vm.isprestrictionyes === "yes") {
            result = result.replace("BLOCKED_WEBSITES", vm.isprestrictionsfield);

            }
            else {
              result = result.replace(' <text:p text:style-name="P87"><text:span text:style-name="T35">ISP blocked websites like: </text:span><text:span text:style-name="T37">BLOCKED_WEBSITES</text:span></text:p>', ' ')

          }
            if (vm.limitdevicesyes === "yes") {
              result = result.replace("NUMBER_OF_DEVICES", "Only" + ' ' + vm.limitdevicesfield + ' ' + 'devices are allowed');

            }
            else {
              result = result.replace('<text:p text:style-name="P87">NUMBER_OF_DEVICES</text:p>', ' ')

          }

           if (vm.dualbandyes === "yes")  {
                  result = result.replace("BANDWIDTH_CONTROL", "The Network uses a bandwidth control");
              }
              else {
                  result= result.replace('<text:p text:style-name="P87">BANDWIDTH_CONTROL</text:p>', '')
              }

           if (vm.serviceyes ==='yes') {
                result  = result.replace("SERVICE_FIELD", "If you have any questions or troubles related to Wifi please contact" + " " + vm.servicefield);
             // <text:h text:style-name="P132" text:outline-level="3">The service is provided &quot;as is&quot;, with no warranty or liability of whatsoever kind</text:h>

              }
            else {
                  result  = result.replace("SERVICE_FIELD", "The Serivce is not guaranteed");
              }


          // if (vm.country !== "yes") {
          //   result = result.replace("LEGAL_RESTRICTIONS", "The owner informs that there are not known to him any legal restriction to use Wifi connection, using Internet resources or taking actions in the network.");
          //   result = result.replace('<text:p text:style-name="P186">LEGAL_RESTRICTIONS</text:p>', ' ')
          //   result = result.replace('<text:p text:style-name="P187">FIELD_RESTRICTIONS</text:p>', ' ')
          // }
          // else {
          //   result = result.replace("LEGAL_RESTRICTIONS", "The owner declares that the law prohibits:")
          //   result = result.replace('<text:p text:style-name="P187">FIELD_RESTRICTIONS</text:p>', vm.legalformatted)
          // }


          var link = document.getElementById('downloadlink');
          link.href = vm.makeTextFile(result);
        })

    }
}]);
