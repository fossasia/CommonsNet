 app.controller('ContactCtrl', function ($scope, $http) {
        $scope.title = 'Contact us';
        $scope.subtitle = 'Many questions are answered in our FAQ and you can find out more about our CommonsNet network here. If you are unable to find your answer via our FAQ or website please contact us'
        $scope.title2 = 'Mailing Address'
        $scope.subtitle2 = 'Please note: we are a distributed team working all over the world. We don’t have an office, but if you need to send us an  email please use above form or send it to:'
        $scope.address = 'commonsnet.team@gmail.com '
        $scope.title3 = 'Reporting bugs with CommonsNet website'
        $scope.subtitle3 = 'You can write to us using the form above'       
        $scope.title4 = 'In this section'
        $scope.title5 = 'Get Involved' 
        $scope.subtitle8 = 'Share your Wifi details'
        $scope.title9 = 'Frequently asked questions'
        $scope.subtitle88 = 'Commonsnet would do little without enthusiastic use by Wifi providers. If you share your Wifi with other people make them transparent'
        $scope.subtitletitle6 = 'Social'
        $scope.subtitletitle7 = 'Support'
        $scope.subtitle77 = 'CommonsNet stays alive because of support from the community.'
        $scope.subtitle5 = 'There are numerous ways to connect with CommonsNet.'
        $scope.text = 'The best way to start interacting and contributing to the community is to join us on GitHub:'
        $scope.text1 = 'https://github.com/fossasia/CommonsNet'
        $scope.disableButtons = true;
        $scope.submit = function() {
          
           if ($scope.myForm.$valid) {
              
               
        }


        $scope.submitForm = function() {

            // check to make sure the form is completely valid
           if ($scope.myForm.$valid) {
                $scope.message = "Thank you! Your email has been delivered."
            }

        };

}
    })