app.factory("markers", function(){
  var Markers = [
    {
      "id": "0",
      "coords": {
        "latitude": "45.5200",
        "longitude": "-122.6819"
      },
      "window": {
        "title": "Portland, OR"
      }
    },
    {
      "id": "1",
      "coords": {
        "latitude": "40.7903",
        "longitude": "-73.9597"
      },
      "window" : {
        "title": "Manhattan New York, NY"
      }
    },
    {
    "id": "2",
    "coords": {
       "latitude": "52.520007",
       "longitude": "13.404954"
     },
     "window" : {
        "title": "Berlin, Germany"
     }
   }
  ];
  return Markers;
});