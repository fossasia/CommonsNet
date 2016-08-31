app.controller('AboutCtrl', function ($scope) {
  $scope.descriptions = [
    {
      content: 'We are a network of people who believe that network transparency is possible to build and we want to have an important impact on doing that. That is why we have prepared a great tool to help everyone make his or her Wifi details open and clear to everyone.',
      title: 'What we do'
    },
    {
      content: 'We provide you with a great tool to make a wifi network transparent, become an important member of community and work with us for our common goal.',
      title: 'What we can do for you'
    }
  ];

  $scope.maintitle1 = 'We are a team of Wifi enthusiasts',
  $scope.maintitle2 = 'Across the world there are different legal settings and requirements for sharing of Internet connections. CommonsNet is a webiste which reflects these details and make them transparent to users.',
  $scope.maintitle3 = 'We want people to be provided with clear data about wireless connection they use'
  $scope.maintitle4 = 'Our system enables to collect all Wifi details in a easy-to-follow wizrd form, and then based on that details generate a human readable pdf file and a machine readeable link'

  $scope.sections = [
    {
      title: 'about_us.process.basic_details.title',
      content: 'about_us.process.basic_details.content',
    },
    {
      title: 'about_us.process.payment_and_limits.title',
      content: 'about_us.process.payment_and_limits.content',
    },
    {
      title: 'about_us.process.conditions.title',
      content: 'about_us.process.conditions.content',
    },
    {
      title: 'about_us.process.legal_restrictions.title',
      content: 'about_us.process.legal_restrictions.content',
    },
  ];
});
