
$(document).ready(function () {
    
    function initMap() {
        var e_slug = $('#map').data('event');
        $.post(Routing.generate('get_event_map_position', {slug: e_slug}),
            function (data) {
                if (data.result) {
                    var myLatLng = new google.maps.LatLng(data.lat, data.lng);
                    var map = new google.maps.Map(document.getElementById('map'),
                        {
                            zoom: 17,
                            center: myLatLng,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        });
                    var marker = new google.maps.Marker(
                        {
                            position: myLatLng,
                            map: map
                        });
                } else {
                    console.log('Error:'+data.error);
                }
        });
    }

    if ($('#map').length > 0) {
        initMap();
    }
});