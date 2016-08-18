

	app.controller('AboutCtrl', function ($scope) {
			
             $scope.descriptions = [
              {
              content: 'We are a network of people who believe that network transparency and highest standards are possible. We wan to see the trustworthy global network, and feel secured, but not restricted. ',
              title: 'WHO ARE WE',
              icon: ''

            },
            {
              content: 'We believe that a transparency is a key in building high standard and quality of wireless connection. Supporting Internet users to be provided with clear wifi details results in increasing standards.',
              title: 'WHY WE DO IT',
              icon: ''
            },
            {
              
              content: 'We provide you with a great tool to make a wifi network transparent, become an important member of community and work with us for our common goal.',
              title:   'WHAT WE CAN DO FOR YOU',
              icon: ''
            }
            ]
         
              


                $scope.maintitle = 'Across the world there are different legal settings and requirements for sharing of Internet connections. CommonsNet is a website that helps to make them transparent to users.',
                $scope.maintitle2 = 'We want to see the world of transparent, shared wifi which let all people over the world to enjoy Internet resources. We want people to be provided with clear data about wireless connection they want to use in below sections.' 

            
            
            
    		    $scope.sections = [
    
            
          
        		{
        			title: 'BASIC DETAILS',
        			content: 'We want people to know what are the basic settings of wireless connection like SSID, password, authentication and standard etc.',
        			icon: ''

        		},
        		{
        			title: 'PAYMENT & TIME LIMIT',
        			content: 'We believe people should get clear information if the wifi they want to use is paid or limited. This section helps you to provide users with clear information about your fee and time limit.',
        			icon: ''
        		},
        		{
        			title: 'SERVICE',
        			content: 'We belive people should be also informed if Wifi service is provided. Thanks to that they can quickly know if they can get a help in an emergency',
        			icon: ''
        		},
        		{
        			title: 'CONDITIONS',
        			content: 'We want also to make people aware of conditions to use wireless connection and let them know what do they need to do in order to connect to offered wifi.',
        			icon: ''
        		},
        		{
        			title: 'LEGAL RESTRICTIONS',
        			content: 'Another imporant information about wifi are legal restrictions. We don’t want them to be a real boundary due to the lack of knowledge. We want people to be aware of things they are not allowed to do while connecting to Internet in a specific place and be safer as well.',
        			icon: ''
        		}
        		]

    })
    