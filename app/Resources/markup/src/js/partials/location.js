
$(document).ready(function () {
    
    function initMap() {
        var myLatLng = new google.maps.LatLng(50.451144, 30.445346);
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

    if ($('#map').length > 0) {
        initMap();
    }
});