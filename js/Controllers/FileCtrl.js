    app.controller('FileCtrl',['$scope', '$routeParams', function ($scope, $routeParams) {
            $scope.ssid = $routeParams.ssid;
            $scope.loginname = $routeParams.loginname;
            $scope.isp = $routeParams.isp;
            $scope.password = $routeParams.password;
            $scope.security = $routeParams.security;
            $scope.standard = $routeParams.standard;
            $scope.service = $routeParams.service;
            $scope.db = $routeParams.db;
            $scope.isplimits = $routeParams.isplimits;
            $scope.paid = $routeParams.paid;
            $scope.speed = $routeParams.speed;
            // $scope.limits = JSON.parse($routeParams.limits);
            $scope.captiveportal = $routeParams.captiveportal;
            $scope.specialdevices = $routeParams.specialdevices;
            $scope.specialdevicesfield = $routeParams.specialdevicesfield;
            $scope.specialsettings= $routeParams.specialsettings;
            $scope.specialsettingsfield = $routeParams.specialsettingsfield;
            $scope.downloading = $routeParams.downloading;
            $scope.liking = $routeParams.liking;
            $scope.acceptterms = $routeParams.acceptterms;
            $scope.register = $routeParams.register;
            $scope.newsletter = $routeParams.newsletter;
            $scope.mobilenumber = $routeParams.mobilenumber;
            $scope.emailaddress = $routeParams.emailaddress;
            $scope.personaldetails = $routeParams.personaldetails;
            $scope.autodisable = $routeParams.autodisable;
            $scope.rnumber = $routeParams.rnumber;
            $scope.nodf = $routeParams.nodf;
            // $scope.ownrestr = $routeParams.ownrestr;
            // $scope.ownrestfield = $routeParams.ownrestfield;
            // $scope.restrictions = $routeParams.restrictions;
            // $scope.country = $routeParams.country;
            // $scope.law = $routeParams.law;
            console.log($scope.limits)


       }])
