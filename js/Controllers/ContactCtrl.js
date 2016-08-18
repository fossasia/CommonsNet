 app.controller('ContactCtrl', function ($scope, $http) {
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