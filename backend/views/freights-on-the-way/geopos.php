<?php

/* @var $this yii\web\View */
/* @var $model common\models\MobileAppGeopos */

$this->title = 'Транспорт на карте | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Транспорт на карте';
?>
<div class="transport-on-the-map">
    <div class="form-group">
        <div id="googleMap" style="height: 600px; width: 100%;"></div>
    </div>
</div>
<?php
$urlGetGeopositions = \yii\helpers\Url::to(['/freights-on-the-way/get-mobile-apps-geopositions']);
//$urlSetGeoposition = \yii\helpers\Url::to(['/api/accept-geopos']);
//$.post("$urlSetGeoposition", {token: "dkEw9ujoj-XRuT5DgWORaaztXCoQ9KtE", "MobileAppGeopos[coord_lat]": "55.672008", "MobileAppGeopos[coord_long]": "37.277257"});

$this->registerJsFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyDdvRkgkcmWz-60NfeTCthFIeEq9Yy_liI', ['position' => \yii\web\View::POS_END]);

$this->registerJs(<<<JS
function initMap() {
    $.get("$urlGetGeopositions", function(markers) {
        if ($.isArray(markers)) {
            var map = new google.maps.Map(document.getElementById("googleMap"), {
                zoom: 10,
                center: {lat: 55.753960, lng: 37.620393}
            });
            $.each(markers, function (index, element) {
                var position = new google.maps.LatLng(markers[index].coord_lat, markers[index].coord_long);
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: markers[index].userProfileName
                });
            });
        }
    });
}

initMap();
setTimeout("window.location.reload()", 60000 * 5); // каждые 5 минут
JS
, \yii\web\View::POS_READY);
?>
