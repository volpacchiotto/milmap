<?
session_start();

require_once('connections.php');
require_once('Ukraine_1939-1944/constants.php');

if (! empty($_COOKIE['lang'])) {$lang = $_COOKIE['lang'];}
if (! empty($_GET['lang'])) {$lang = $_GET['lang'];}
if (empty($lang)) {$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);}
//если языка нет в настройках страницы, берём по умолчанию первый из них
if (! in_array($lang, LANGS)) {$lang = LANGS[0];}
setcookie ('lang', $lang, time() + 3600 * 24 * 62, "/");

if (isset($_GET['date'])) {
    $date = explode('.', $_GET['date']);
    if ($date[1] >= 1 && $date[1] <= 12) {
        $day = (strtotime ($_GET['date']) - strtotime(START_DATE)) / 60 / 60 / 24 + 1;
    } else {
        $months = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII',
            'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii'];
        if (in_array($date[1], $months)) {
            $date[1] = array_search($date[1], $months) + 1;
            $date[1] > 12 ? $date[1] = $date[1] - 12 : null;
            $day = (strtotime(implode('.', $date))- strtotime(START_DATE)) / 60 / 60 / 24 + 1;
        } else {
            $day = MIN;
        }
    }
} else {
    $day = MIN;
}

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <title><?= MAP_NAME[$lang] ?></title>
    <meta charset="utf-8">
    <meta name="keywords" content="<?= TEXTS['meta'][$lang] ?>">
    <meta property="og:url" content="http://milmap.inf.ua/" />
    <meta property="og:description" content="<?= TEXTS['description'][$lang] ?>">
    <meta property="og:image" content="http://milmap.inf.ua/milmap.jpg" />
    <meta property="og:image:height" content="480">
    <meta property="og:image:width" content="525"> 
    <link href="icons/favicon.ico" type=image rel="shortcut icon">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/slider.css">
    <script src="js/slider.js"></script>
    <script src="js/functions.js"></script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-78440052-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());gtag('config', 'UA-78440052-1');
    </script>
</head>

<body>

<? require_once('languages_selector.php'); ?>

<h1><?= MAP_NAME[$lang] ?></h1>
<h2 id="day_at_head">
        <?= idate('d', strtotime(START_DATE)) ?>
        <?= MONTHS[idate('m', strtotime(START_DATE))][$lang] ?>
        <?= idate('Y', strtotime(START_DATE)) ?>
</h2>

<div>
    <div id="map"></div>
    <div id="rail-with-buttons">
        <p title="<?= TEXTS['beginning'][$lang] ?>">
            <?= idate('d', strtotime(START_DATE)) ?>
            <?= MONTHS[idate('m', strtotime(START_DATE))][$lang] ?>
            <?= idate('Y', strtotime(START_DATE)) ?>
        <p id="minus" class="button">◀<div id="rail"><div id="slider"></div></div><p id="plus" class="button">▶
        <p title="<?= TEXTS['end'][$lang] ?>">
            <?= idate('d', strtotime(END_DATE)) ?>
            <?= MONTHS[idate('m', strtotime(END_DATE))][$lang] ?>
            <?= idate('Y', strtotime(END_DATE)) ?>
        <input type="hidden" id="outerValue">
    </div>

    <form onchange="selectDate(day)">
        <select>
        <?
        for ($d = 1; $d <= 31; $d ++) {
            echo "<option value=$d";
            if ($d == idate('d', strtotime(START_DATE))) {echo " selected";}
            echo ">$d</option>";
        }
        ?>
        </select><select>
        <?
        foreach (MONTHS as $m => $month) {
            echo "<option value=$m";
            if ($m == idate('m', strtotime(START_DATE))) {echo " selected";}
            echo ">$month[$lang]</option>";
        }
        ?>
        </select><select>
        <?
        for ($year = idate('Y', strtotime(START_DATE));
            $year <= idate('Y', strtotime(END_DATE));
            $year ++) {
            echo "<option value=$year>$year</option>";
        }
        ?>
        </select>
    </form>

    <div id="legends">
        <div>
            <fieldset>
                <legend><?= TEXTS['legend'][$lang] ?></legend>
                <? foreach (ARMIES as $army) { ?>
                <p><img src="icons/<?= $army['flag'] ?>.gif" alt="<?= $army['name_' . $lang] ?>" title="<?= $army['name_' . $lang] ?>">
                    <img src="icons/<?= $army['color'] ?>3.gif" alt="<?= $army['name_' . $lang] ?>" title="<?= $army['name_' . $lang] ?>">
                    <? echo $army['name_' . $lang];
                    if ($army['note_' . $lang]) {
                        echo $army['note_' . $lang];
                    }
                } ?>
            </fieldset>
        </div>
        <div>
            <fieldset>
                <a href="towns"><?= TEXTS['points_list'][$lang] ?></a>
            </fieldset>
            <fieldset>
                <legend><?= TEXTS['markers_changer'][$lang] ?></legend>
                <input type="radio" id="circles" name="marks" 
                <?= (! $_GET['marks'] || $_GET['marks'] == 'circles') ? 'checked' : null ?>
                onClick="reformMarkers('circles'); generateLink('<?= $_GET['lang'] ?>')">
                <label for="circles"><?= TEXTS['circles'][$lang] ?></label>
                <input type="radio" id="flags" name="marks" 
                <?= $_GET['marks'] == 'flags' ? 'checked' : null ?>
                onClick="reformMarkers('flags'); generateLink('<?= $_GET['lang'] ?>')">
                <label for="flags"><?= TEXTS['flags'][$lang] ?></label>
            </fieldset>
        </div>
    </div>
    <div class="comment" id="link" style="display: none;">
        <p><?= TEXTS['link'][$lang] ?>:<p>
    </div>
</div><!--map_section-->

<div id="events_section">
    <h3 id="day">
        <?= idate('d', strtotime(START_DATE)) ?>
        <?= MONTHS[idate('m', strtotime(START_DATE))][$lang] ?>
        <?= idate('Y', strtotime(START_DATE)) ?>
    </h3>
    <h4 id="today"><?= TEXTS['today'][$lang] ?>:</h4>
    <div id="battle_begins" style="display: none;"><?= TEXTS['battle_begins'][$lang] ?></div>
    <div id="battle_continues" style="display: none;"><?= TEXTS['battle_continues'][$lang] ?></div>
    <div id="battle_ends" style="display: none;"><?= TEXTS['battle_ends'][$lang] ?></div>
    <ul id="event_list">
    </ul>
</div>
<div id="fb-root"></div>
<script>
(function(d,s,id){
    var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;
    js.src="//connect.facebook.net/<?= TEXTS['lang_country'][$lang] ?>/sdk.js#xfbml=1&version=v2.6";
    fjs.parentNode.insertBefore(js,fjs);}(document,'script','facebook-jssdk'));
</script> 
<div class="fb-like" data-href="http://milmap.inf.ua/" data-share="true" data-layout="button_count" data-action="like" data-show-faces="true"></div>

<script src="Ukraine_1939-1944/points.js"></script>
<script src="Ukraine_1939-1944/borders.js"></script>
<script src="Ukraine_1939-1944/battles.js"></script>
<script>
    var RAIL_WIDTH = 600, SLIDER_WIDTH = 8
    var MIN = 1, MAX = <?= (strtotime(END_DATE) - strtotime(START_DATE)) / 60 / 60 / 24 + 1 ?> 
    var START_DATE = new Date(<?= idate('Y', strtotime(START_DATE)) ?>, <?=
        idate('m', strtotime(START_DATE)) - 1 ?>, <?= idate('d', strtotime(START_DATE)) ?>, 12)
    var MONTHS = [<? foreach (MONTHS as $month) {echo "'$month[$lang]',";} ?>]
    var ARMIES = {
        <?
        foreach (ARMIES as $id => $army) {
            echo "$id: {";
            foreach ($army as $parameter => $value) {
                echo "$parameter: '$value', ";
            }
            echo '},
        ';
        } 
        ?>
    }
    var LINK_NAME = '<?= TEXTS['link'][$lang] ?>'
    var BORDER_APPEARING = '<?= TEXTS['border']['appearing'][$lang] ?>'
    var BORDER_DISAPPEARING = '<?= TEXTS['border']['disappearing'][$lang] ?>'

    var map,
        marks = '<?= in_array($_GET['marks'], ['circles', 'flags']) ? $_GET['marks'] : 'circles' ?>',
        markers = [],
        drawnBorders = [],
        day,
        infoWindows = [],
        mapDefaults = {
            zoom: 6,
            center: {
                latitude: <?= MAP_CENTER['latitude'] ?>,
                longitude: <?= MAP_CENTER['longitude'] ?>
            }
        },
        mapCenter = {}
        mapCenter.latitude = typeof(points[<?= $_GET['id'] ?? 'null' ?>])
            != 'undefined' ? points[<?= $_GET['id'] ?? 'null' ?>].latitude :
            <?= $_GET['lat'] ?? 'false' ?> ? <?= $_GET['lat'] ?? 'null' ?> :  mapDefaults.center.latitude
        mapCenter.longitude = typeof(points[<?= $_GET['id'] ?? 'null' ?>])
            != 'undefined' ? points[<?= $_GET['id'] ?? 'null' ?>].longitude :
            <?= $_GET['lon'] ?? 'false' ?> ? <?= $_GET['lon'] ?? 'null' ?> :  mapDefaults.center.longitude
        var zoom = typeof(points[<?= $_GET['id'] ?? 'null' ?>]) != 'undefined' ? 9 :
            <?= $_GET['z'] ?? 'false' ?> ? <?= $_GET['z'] ?? 'null' ?> : mapDefaults.zoom

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: zoom,
            center: {lat: mapCenter.latitude, lng: mapCenter.longitude},
            mapTypeId: 'terrain',
            tilt: 0,
            streetViewControl: false,
            mapTypeControlOptions: {mapTypeIds: [
                google.maps.MapTypeId.TERRAIN,
                google.maps.MapTypeId.ROADMAP,
                google.maps.MapTypeId.SATELLITE
            ]},
            keyboardShortcuts: false,
        })
        slider(day)//через whatToDoWhenSliderIsMoving() первый запуск функций, зависимых от ползунка
        <? if (isset($_GET['id'])) { ?>
        if (typeof(points[<?= $_GET['id'] ?>]) != 'undefined') {
            var infoWindow = new google.maps.InfoWindow()
            infoWindow.setContent(makeInfoWindow(<?= $_GET['id'] ?>,
                points[<?= $_GET['id'] ?>], '<?= $lang ?>'))
            infoWindow.open(map, markers[<?= $_GET['id'] ?>])    
            document.title = '<?= MAP_NAME[$lang] ?>' + ' — '
                + points[<?= $_GET['id'] ?>]['name_' + '<?= $lang ?>']   
        }
        <? } ?>      
        map.addListener('center_changed', function(){generateLink('<?= $_GET['lang'] ?>')});
        map.addListener('zoom_changed', function(){generateLink('<?= $_GET['lang'] ?>')});
    }

    //поле для передачи значений в slider.js
    var dayInput = document.getElementById('outerValue')
    //само значение - номер дня после начального
    day = + dayInput.value

    /**
     * главная функция при смене даты
     * @param integer номер дня
     */
    function whatToDoWhenSliderIsMoving (value) {
        var date = dayToDate(value)
        document.getElementById('day_at_head').innerText
            = date.day + ' ' + date.month + ' ' + date.year// + ' ' + day
        document.getElementById('day').innerText = date.day + ' ' + date.month + ' ' + date.year
        document.title = '<?= MAP_NAME[$lang] ?>' + date.day + ' ' + date.month + ' ' + date.year
        day = + dayInput.value
        setOnMap(day, previousDay, '<?= $lang ?>')
        getEvents (points, day, '<?= $lang ?>')
        previousDay = day
        changeDateSelectors(day)
        generateLink('<?= $_GET['lang'] ?>', '<?= $_GET['id'] ?>')
    }//whatToDoWhenSliderIsMoving

    document.getElementById('minus').addEventListener('click', function() {
        dayInput.value = -- day
        var event = document.createEvent('Event')
        event.initEvent('change', false, true) 
        dayInput.dispatchEvent(event)
    })

    document.getElementById('plus').addEventListener('click', function() {
        dayInput.value = ++ day
        var event = document.createEvent('Event')
        event.initEvent('change', false, true) 
        dayInput.dispatchEvent(event)
    })

    //закрывает окна меток
    document.addEventListener('keydown', function(event) {
        if (event.keyCode == 27) {//Escape
            if (infoWindows.length) {
                infoWindows.pop().close()
            }
        }
    })

    day = <?= $day ?> 
    var previousDay = day
    var showLink = <?= isset($_GET) && empty($_GET['id']) ? 'true' : 'false' ?>
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_API_KEY ?>&callback=initMap&language=<?= $lang ?>"
    async defer></script>
 
</body>
</html>