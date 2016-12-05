app.controller('ContactCtrl', function($scope, markers) {
	$scope.map = { 
		center: { latitude: 0, longitude: 0 }, 
		zoom: 2
		 };
	$scope.markers = markers;
});

