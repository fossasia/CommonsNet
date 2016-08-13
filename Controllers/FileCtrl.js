    app.controller('FileCtrl',['$scope', '$routeParams', function ($scope, $routeParams) {
            $scope.ssid = $routeParams.ssid;
            $scope.password = $routeParams.password;
            $scope.security = $routeParams.security;
            $scope.standard = $routeParams.standard;
            $scope.payment = $routeParams.payment;
            $scope.fee = $routeParams.fee;
            $scope.timelimit = $routeParams.timelimit;
            $scope.limit = $routeParams.limit;
            $scope.service = $routeParams.service;
            $scope.specialdevices = $routeParams.specialdevices;
            $scope.specialdevicesfield = $routeParams.specialdevicesfield;
            $scope.specialsettings= $routeParams.specialsettings;
            $scope.specialsettingsfield = $routeParams.specialsettingsfield;
            $scope.downloading = $routeParams.downloading;
            $scope.liking = $routeParams.liking;
            $scope.acceptterms = $routeParams.acceptterms;
            $scope.restrictions = $routeParams.restrictions;
            $scope.country = $routeParams.country;
            $scope.law = $routeParams.law;
            console.log($routeParams)

       }])
