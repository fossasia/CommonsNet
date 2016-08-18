

	app.controller('AboutCtrl', function ($scope) {
             $scope.descriptions = [ 
            {
              who: 'We are a network of people who believe that network transparency and highest standards are possible. We wan to see the trustworthy global network, and feel secured, but not restricted. ',
              title: 'WHO ARE WE',

            },
            {
              who: 'We believe that a transparency is a key in building high standard and quality of wireless connection. Supporting Internet users to be provided with clear wifi details results in increasing standards.',
              title: 'WHY WE DO IT'
            },
            {
              
              who: 'We provide you with a great tool to make a wifi network transparent, become an important member of community and work with us for our common goal.',
              title:   'WHAT WE CAN DO FOR YOU'
            }
            ] 
            $scope.title = 'Across the world there are different legal settings and requirements for sharing of Internet connections. CommonsNet is a website that helps to make them transparent to users.'
        	$scope.title2 = 'We want to see the world of transparent, shared wifi which let all people over the world to enjoy Internet resources. We want people to be provided with clear data about wireless connection they want to use in below sections.'
    		$scope.sections = [
    		{
    			title: 'BASIC DETAILS',
    			content: 'We want people to know what are the basic settings of wireless connection like SSID, password, authentication and standard'

    		},
    		{
    			title: 'PAYMENT',
    			content: 'We believe people should get clear information if the wifi they want to use is paid or not. This section helps you to provide users with clear information about your fee. '
    		},
    		{
    			title: 'TIME LIMIT',
    			content: 'In comparison to payment example our form let you to inform your users if they have to keep the time limit, or the wireless connection is unlimited.'
    		},
    		{
    			title: 'SERVICE',
    			content: 'It helps you to inform your users if you provide a Wifi service. Thanks to that they can quickly know if they are secured and can get a help in an emergency'
    		},
    		{
    			title: 'CONDITIONS',
    			content: 'We want also to make people aware of conditions to use wireless connection and let them know what do they need to do in order to connect to offered wifi.'
    		},
    		{
    			title: 'LEGAL RESTRICTIONS',
    			content: 'We believe that another imporant information about wifi are legal restrictions. We don’t want them to be a real boundary or make some trouble for someone due to the lack of knowledge. We want people to be aware of things they are not allowed to do while connecting to Internet in a specific place and be safer as well.'
    		}
    		]

    })
    