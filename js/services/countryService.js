app.service('countryService', function() {
  var countries = [
    { name: 'France' },
    { name: 'Poland' },
    { name: 'Germany' },
    { name: 'USA' },
    { name: 'Russia' },
  ];

  function getCountries() {
    return countries;
  }

  return {
    getCountries: getCountries
  }
});