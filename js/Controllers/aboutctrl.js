app.controller('AboutCtrl', function ($scope) {
  $scope.descriptions = [
    {
      title: 'about_us.activity.title',
      content: 'about_us.activity.content',
    },
    {
      title: 'about_us.offer.title',
      content: 'about_us.offer.content',
    },
  ];

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
