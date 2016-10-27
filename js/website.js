
// angular routes definded
var app = angular.module('website', ['ngRoute', 'summernote', 'pascalprecht.translate']);
    app.config(function ($routeProvider) {
        $routeProvider
            .when('/', {
                templateUrl : 'partials/home.html',
                controller  : 'HomeCtrl'
            })
            .when('/about-us', {
                templateUrl : 'partials/about.html',
                controller  : 'AboutCtrl'
             })
            .when('/generate-wifi', {
                templateUrl : 'partials/generate-wifi.html',
                controller  : 'GenerateWiFiCtrl'
             })
            .when('/contact', {
                templateUrl : 'partials/contact.html',
                controller  : 'ContactCtrl'
            })
               .when('/generate-wifi/confirmation', {
                templateUrl : 'partials/confirmation.html',
                controller  : 'ContactCtrl'
            })
           .when('/file', {
              templateUrl : 'partials/file_structure.html',
              controller: 'FileCtrl'
           })
          .when('/users/login', {
              templateUrl : 'partials/login.html',
              controller: 'LoginCtrl'
          })
          .when('/users/register', {
            templateUrl : 'partials/register.html'
            // controller: 'RegisterCtrl'
          })
            .when('/users/admin', {
                templateUrl : 'partials/admin_panel.html'
                // controller: 'RegisterCtrl'
            })
          .otherwise ({redirectTo: '/'});


    })
    .config(function($translateProvider) {
      $translateProvider.useStaticFilesLoader({
        prefix: 'i18n/locale-',
        suffix: '.json'
      });

      $translateProvider.preferredLanguage('en');
    });

 function init_map() {
     var myOptions = {
         zoom:14,
         center:new google.maps.LatLng(52.55121,13.404289999999946),
         mapTypeId: google.maps.MapTypeId.ROADMAP
     };
        map = new google.maps.Map(document.getElementById("gmap_canvas"), myOptions);
        marker = new google.maps.Marker({map: map,position: new google.maps.LatLng(52.55121, 13.404289999999946)});
        infowindow = new google.maps.InfoWindow({
            content:"<b>CommonsNet</b><br/>Malmoer Strasse 5<br/> Berlin"
     });
     google.maps.event.addListener(marker, "click", function(){
         infowindow.open(map,marker);
     });
        infowindow.open(map,marker);
 }
    google.maps.event.addDomListener(window, 'load', init_map);






























