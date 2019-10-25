<?php

use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\MobileAppGeopos */
/* @var $urlGetGeopositions string */
/* @var $dpOutdated \yii\data\ActiveDataProvider */

$this->title = 'Транспорт на карте | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Транспорт на карте';
?>
<div class="transport-on-the-map">
    <div class="form-group">
        <div id="googleMap" style="height: 600px; width: 100%;"></div>
    </div>
</div>
<div class="form-group">
    <p class="lead">Координаты пользователей мобильного приложения, которые устарели.</p>
    <?= GridView::widget([
        'id' => 'gwOutdated',
        'dataProvider' => $dpOutdated,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'arrived_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'userProfileName',
            'ferrymanName',
            [
                'label' => 'Последнее местоположение',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\MobileAppGeopos */
                    /* @var $column \yii\grid\DataColumn */

                    return \yii\helpers\Html::a('Показать', '#', [
                        'class' => 'link-ajax',
                        'id' => 'showOutdatedGeopos' . $model->id,
                        'data-id' => $model->id,
                        'data-lat' => $model->coord_lat,
                        'data-lng' => $model->coord_long,
                    ]);
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modalTitle" class="modal-title">Самые поздние координаты</h4></div>
            <div id="modalBody" class="modal-body">
                <div id="googleMapModal" style="height: 300px; width: 100%;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php

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
                    title: markers[index].markerTitle,
                    label: markers[index].user_id
                });
            });
        }
    });
}

initMap();
setTimeout("window.location.reload()", 60000 * 5); // каждые 5 минут

// Обработчик щелчка по ссылкам для отображения устаревших местоположений пользователей.
//
function showOutdatedGeoposOnClick() {
    lat = parseFloat($(this).attr("data-lat"));
    lng = parseFloat($(this).attr("data-lng"));
    var map = new google.maps.Map(document.getElementById("googleMapModal"), {
        zoom: 14,
        center: {lat: lat, lng: lng}
    });
    var position = new google.maps.LatLng(lat, lng);
    marker = new google.maps.Marker({
        position: position,
        map: map
    });

    $("#modalWindow").modal("show");

    return false;
} // showOutdatedGeoposOnClick()

$(document).on("click", "a[id ^= 'showOutdatedGeopos']", showOutdatedGeoposOnClick);
JS
, \yii\web\View::POS_READY);
?>
