
$(document).ready(function () {

    function setMapByCoords(lng, lat) {
        var myLatLng = new google.maps.LatLng(lat, lng);
        if (typeof google === 'undefined') {
            return;
        }
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
    }

    function initMap() {
        var map_element = $('#map');
        var e_slug = map_element.data('event');
        var lng = map_element.data('lng');
        var lat = map_element.data('lat');
        if (!lng || !lat) {
            $.ajax({
                type: 'post',
                url: Routing.generate('get_event_map_position', {slug: e_slug})
            }).done(
                function (data) {
                    if (data.result) {
                        setMapByCoords(data.lng, data.lat)
                    } else {
                        console.log('Error:' + data.error);
                    }
                }
            );
        } else {
            setMapByCoords(lng, lat)
        }
    }

    $(window).load(function() {
        if ($('#map').length > 0) {
            initMap();
        }
    })
});