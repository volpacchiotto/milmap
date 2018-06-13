<?
session_start();

require_once('../connections.php');
require_once('../Ukraine_1939-1944/constants.php');
require_once('../functions.php');

if (! empty($_COOKIE['lang'])) {$lang = $_COOKIE['lang'];}
if (! empty($_GET['lang'])) {$lang = $_GET['lang'];}
if (empty($lang)) {$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);}
//если языка нет в настройках страницы, берём по умолчанию первый из них
if (! in_array($lang, LANGS)) {$lang = LANGS[0];}
setcookie ('lang', $lang, time() + 3600 * 24 * 62, "/");

$database = mysqli_connect(SERVER, USER, PASSWORD, DATABASE) or die('не подключилось к базе ☹');
mysqli_set_charset($database, 'utf8');
$query = 'SELECT *, `ukraine_1939-1944`.`id` AS `point_id` FROM `ukraine_1939-1944`
INNER JOIN `ukraine_1939-1944_periods` ON `ukraine_1939-1944`.`id` = `ukraine_1939-1944_periods`.`point_id`
ORDER BY `name_uk` COLLATE utf8_unicode_ci, `date`';
$data = mysqli_query($database, $query);
mysqli_close($database);

//группируем события в точки
while ($point = mysqli_fetch_array($data))  {
    if (! isset($points[$point['point_id']])) {
        $points[$point['point_id']] = [
            'name_uk' => $point['name_uk'],
            'name_ru' => $point['name_ru'],
            'present_name_uk' => $point['present_name_uk'],
            'present_name_ru' => $point['present_name_ru'],
            'latitude' => $point['latitude'],
            'longitude' => $point['longitude'],
            'size' => $point['size'],
        ];
    }
    $points[$point['point_id']]['periods'][$point['date']] = [
        'army' => $point['army_id'],
        'comment_uk' => $point['comment_uk'],
        'comment_ru' => $point['comment_ru'],
    ];
}

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <title><?= LIST_NAME[$lang] ?></title>
    <meta charset="utf-8">
    <link href="../icons/favicon.ico" rel="shortcut icon" type=image>
    <link href="../css/styles.css" rel="stylesheet">
    <script src="../js/functions.js"></script>
</head>

<body>

<? require_once('../languages_selector.php'); ?>

<h1><?= LIST_NAME[$lang] ?></h1>
<h2><?= TEXTS['list description'][$lang] ?></h2>
<div id="legends">
    <div>
        <fieldset>
        <legend><?= TEXTS['legend'][$lang]; ?></legend>
        <? foreach (ARMIES as $n => $army) { ?>
        <p><? armyToFlag($n, $lang) ?>
            <? echo $army['name_' . $lang];
            if ($army['note_' . $lang]) {
                echo $army['note_' . $lang];
            }
        } ?>
        </fieldset>
    </div>
</div>

<div id="events_section">
<? foreach ($points as $id => $point) { ?>
    <p><a href="../?id=<?= $id ?>"><?= $point['name_' . $lang] ?></a>
    <? foreach ($point['periods'] as $date => $period) {
        if (dateToDay($date)) {
            echo ' → ' . formatDate($date, $lang);
            if ($period['comment_' . $lang]) {
                echo "<span class=\"more\" onclick=\"showMore('$id-$date')\">*</span>"
                    . "<span class=\"comment\" id=\"$id-$date\" hidden=true>"
                    . $period['comment_' . $lang] . '</span>';
            }
            echo ' → ';
        }
        armyToFlag($period['army'], $lang);
    }
} ?>
</div>

</body>
</html>