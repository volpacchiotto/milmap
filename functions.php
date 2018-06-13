<?
/**
 * возвращает дату по порядковому дню карты с учетом языка
 * использует постоянные карты START_DATE и MONTHS
 */
function formatDate ($date, $lang) {
    $date = getdate(strtotime($date));
    return $date['mday'] . ' ' . MONTHS[$date['mon']][$lang] . ' ' . $date['year'];
}

/**
 * выводит флажок с подписью по номеру войска
 * использует постоянную карты ARMIES
 */
function armyToFlag ($army, $lang) {
    echo '<img src="../icons/'
    . ARMIES[$army]['flag']. '_.gif" alt="'
    . ARMIES[$army]['name_' . $lang] . '" title="'
    . ARMIES[$army]['name_' . $lang] . '">';
}

/**
 * возвращает порядковый день карты по дате
 * использует постоянную карты START_DATE
*/
function dateToDay ($date) {
    $date = getdate(strtotime($date));
    $startDate = getdate(strtotime(START_DATE));
    $dateStamp = mktime(12, 0, 0, $date['mon'], $date['mday'], $date['year']);
    $startDateStamp = mktime(12, 0, 0, $startDate['mon'], $startDate['mday'], $startDate['year']);
    return round(($dateStamp - $startDateStamp) / 60 / 60 / 24) + 1;
} 
?>