 app.controller('ContactCtrl', function ($scope, $http) {
        $scope.contactus = 'contact.contact_us';
        $scope.subtitle = 'contact.find_out_more';
        $scope.title2 = 'contact.mailing_address';
        $scope.subtitle2 = 'contact.not_office';
        $scope.address = 'contact.email';
        $scope.title3 = 'contact.report_bugs';
        $scope.subtitle3 = 'contact.use_form';
        $scope.title4 = 'contact.this_section';
        $scope.title5 = 'contact.get_involved';
        $scope.subtitle8 = 'contact.share_wifi';
        $scope.title9 = 'contact.faq';
        $scope.subtitle88 = 'contact.users';
        $scope.subtitletitle6 = 'contact.social';
        $scope.subtitletitle7 = 'contact.support';
        $scope.subtitle77 = 'contact.stay_alive';
        $scope.subtitle5 = 'contact.ways';
        $scope.text = 'contact.contribute';
        $scope.text1 = 'contact.github';
        $scope.faq = 'contact.sidemenu.faq';
        $scope.mail = 'contact.sidemenu.mail';
        $scope.get = 'contact.sidemenu.get_involved';
        $scope.report = 'contact.sidemenu.report_bugs';
        $scope.connect = 'contact.connectCommonsNet';
        $scope.signup = "contact.signup";
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
