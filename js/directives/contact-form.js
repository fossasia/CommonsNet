app.directive('contactForm', function() {
  return {
    restrict: 'E',
    templateUrl: 'partials/contact-form.html',
    scope: {
    },
    controller: ['$scope', function($scope) {
      $scope.model = {
        name: '',
        emailAddress: '',
        subject: '',
        message: '',
      };
      
      $scope.send = function() {
        if ($scope.form.$invalid) {
          return;
        }

        // TODO: send message
        console.log("Sending message from " + $scope.model.name + " (" + $scope.model.emailAddress + "):");
        console.log("Subject: " + $scope.model.subject + "\n" + $scope.model.message);

        $scope.message = "Thank you! Your email has been delivered."
      };
    }]
  };
});
