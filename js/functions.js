/**
 * расставляет или меняет метки и границы на карте
 * использует глобальные, записанные в отдельных файлах points и borders
 * @param {number} day текущий день
 * @param {number} previousDay день, в который стояли метки до этого
 * @param {string} lang каким яз{ыком} подписывать
 */
function setOnMap (day, previousDay, lang) {
    for (var id in points) {
        if (
            typeof(markers[id]) == 'undefined'//если метка не стояла
            //или стояла под другими войсками
            || getArmy(day, points[id].periods).mark != getArmy(previousDay, points[id].periods).mark
        ) {
            if (typeof(markers[id]) != 'undefined'){//если стояла
                markers[id].setMap(null)//убираем c карты
            }
            addMarker(id, points[id], lang)//ставим 
        }
    }
    for (var id in borders) {
        if (typeof(drawnBorders[id]) == 'undefined') {//если граница не нарисована
            if (borders[id].set < day && day < borders[id].disappeared) {
                addBorder(id, 'existing', lang)
            }
            if (day == borders[id].set) {
                addBorder(id, 'appearing', lang)
            }
            if (day == borders[id].disappeared) {
                addBorder(id, 'disappearing', lang)
            }
        } else {//если граница нарисована
            //если совсем пропадает
            if (day < borders[id].set || day > borders[id].disappeared) {
                drawnBorders[id].setMap(null)//стираем с карты
                delete(drawnBorders[id])//и с массива
            }
            if (day == borders[id].set) {
                drawnBorders[id].setOptions({
                    strokeOpacity: 1,
                    strokeWeight: 3,
                })
            }
            if (day == borders[id].disappeared) {
                drawnBorders[id].setOptions({
                    strokeOpacity: .5,
                    strokeWeight: 2,
                })
            }
            if (borders[id].set < day && day < borders[id].disappeared) {
                drawnBorders[id].setOptions({
                    strokeOpacity: 1,
                    strokeWeight: 2,
                })
            }
        }
    }
}

/**
 * ставит метку на карту
 * @param {number} id
 * @param {object} point
 * @param {string} lang каким яз(ыком) подписывать
 */
function addMarker (id, point, lang) {
    var army = getArmy(day, point.periods).mark
    var icon = formIcon(army, point.size)
    var marker = new google.maps.Marker ({
        id: id,
        map: map,
        position: new google.maps.LatLng(point.latitude, point.longitude),
        icon: icon.image,
        shape: icon.shape,
        title: point['name_' + lang],
        zIndex: point.size,
    })
    marker.addListener('click', function() {
        var pointInfo = new google.maps.InfoWindow({
            content: makeInfoWindow(id, point, lang)
        })
        pointInfo.open(map, marker)
        infoWindows.push(pointInfo)
    })
    markers[id] = marker//массив расставленных меток
}

/**
 * @param {number} id айди границы
 */
function addBorder (id, type, lang) {
    var border = new google.maps.Polyline({
        id: id,
        path: Object.values(borders[id].nodes),
        strokeColor: '#000',
    })
    var options
    switch (type) {
        case 'existing': options = {
            strokeOpacity: 1,
            strokeWeight: 2,
        }; break
        case 'appearing': options = {
            strokeOpacity: 1,
            strokeWeight: 3,
        }; break
        case 'disappearing': options = {
            strokeOpacity: .5,
            strokeWeight: 2,
        }; break
    }
    border.setOptions(options)
    border.setMap(map)

    border.addListener('click', function(event) {
        var borderInfo = new google.maps.InfoWindow({
            position: {lat: event.latLng.lat(), lng: event.latLng.lng()},
            content: makeBorderInfoWindow(id, lang),
        })
        borderInfo.open(map)
        infoWindows.push(borderInfo)
    })
    drawnBorders[id] = border
}

/**
* превращает порядковый день в дату
* использует глобальную постоянную START_DATE
* @param {number} day день
* @returns {object} массив даты
*/
function dayToDate (day) {
    var date = new Date(START_DATE.getTime() + (day - 1) * 24 * 60 * 60 * 1000)
    return {
        year: date.getFullYear(),
        month: MONTHS[date.getMonth()],
        m: (date.getMonth() + 1),
        day: date.getDate()
    }
}

/**
* при выборе даты select
* @param {number} day текущий день в системе карты
*/
function selectDate (day) {
    var selectedDate = new Date(document.forms[0][2].value,
        document.forms[0][1].value - 1, document.forms[0][0].value, 12)
    day = (selectedDate.getTime() - START_DATE.getTime()) / 1000 / 60 / 60 / 24 + 1
    day = Math.round(day)
    dayInput.value = day
    var event = document.createEvent('Event')
    event.initEvent('change', false, true) 
    dayInput.dispatchEvent(event)
}

/**
* меняет значение выбора даты
* @param {number} day текущий день в системе карты
*/
function changeDateSelectors (day) {
    var date = new Date(START_DATE.getTime() + (day - 1) * 24 * 60 * 60 * 1000)
    document.forms[0][0].value = date.getDate()
    document.forms[0][1].value = date.getMonth() + 1
    document.forms[0][2].value = date.getFullYear()
}

/**
 * какие войска в этот день
 * @param {number} day текущий день в системе карты
 * @param {object} periods дат точки
 * @returns {string} айди войска или '%'
 */
function getArmy (day, periods) {
    var army = {}
    army.id = periods[0].army
    for (var eventDay in periods) {
        if (day == 0) {break}
        if (day == eventDay) {
            army.mark = '%'
            army.id = periods[eventDay].army
            break
        }
        if (day > eventDay) {army.id = periods[eventDay].army}
        if (day < eventDay) {break}
    }
    if (typeof(army.mark) == 'undefined') {
        army.mark = army.id
    }
    return army
}

/**
 * форма метки
 * @param {number} army айди войска
 * @param {number} size размер пункта
 */
function formIcon (army, size) {
    var icon
    if (army == '%') {
        icon = {
            image: {
                url: 'icons/proelium.gif',
                size: new google.maps.Size(16, 20),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(8, 20)
            },
            shape: {coords: [0, 14, 4, 20, 15, 16, 15, 11, 8, 0], type: 'poly'}
        }
        return icon
    }
    //marks - глобальная
    if (marks == 'circles') {
        switch (size) {//радиус кружочков
            case 4: r = 7; break
            case 3: r = 6; break
            case 2: r = 5; break
            case 1: r = 4; break
        }
        icon = {
            image: {
                url: 'icons/' + ARMIES[army].color + size + '.gif',
                size: new google.maps.Size(r * 2, r * 2),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(r, r)
            },
            shape: {coords: [r, r, r], type: 'circle'}
        }
    } else if (marks == 'flags') {
        icon = {
            image: {
                url: 'icons/' + ARMIES[army].flag + '.gif',
                size: new google.maps.Size(19, 20),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(1, 20)
            },
            shape: {coords: [0, 0, 19, 12], type: 'rect'}
        }
    }
    return icon
}

/**
 * меняет все значки
 * @param {string} form circles / flags
 */
function reformMarkers (form) {
    marks = form
    markers.forEach(function(marker) {//убираем значки
        marker.setMap(null)
    })
    markers = []
    setOnMap()
}

/**
 * наполняет всплывающее окно метки
 * исползует глобальную LINK_NAME
 * @param {object} point
 * @param {string} lang каким яз{ыком} подписывать
 */
function makeInfoWindow (id, point, lang) {
    content = '<div class="infoWindow">'
        + '<h3>' + point['name_' + lang] + '</h3>'
    if (typeof(point['present_name_' + lang]) != 'undefined') {
        content += '<p class="present-name">' + point['present_name_' + lang]
    }
    for (var eventDay in point.periods) {
        if (eventDay != 0) {
            content += '<p>' + dayToDate(eventDay).day + ' '
            + dayToDate(eventDay).month + ' ' + dayToDate(eventDay).year
            if (typeof(point.periods[eventDay]['comment_' + lang]) != 'undefined') {
                content += '<span class="more" onclick="showMore(\''
                    + id + '-' + eventDay + '\')">*</span>'
                    + '<span class="comment" id="' + id + '-' + eventDay + '" hidden=true>'
                    + point.periods[eventDay]['comment_' + lang] + '</span>'
            }
        }
        content += '<p><img src="icons/'
            + ARMIES[point.periods[eventDay].army].flag + '_.gif" alt="'
            + ARMIES[point.periods[eventDay].army]['name_' + lang] + '" title="'
            + ARMIES[point.periods[eventDay].army]['name_' + lang] + '">'
    }
    content += '<p class="comment"><a href="?id=' + id + '">' + LINK_NAME + '</a></div>'
    return content
}

/**
 * наполняет всплывающее окно границы
 * исползует глобальные BORDER_APPEARING и BORDER_DISAPPEARING
 * @param {object} point
 * @param {string} lang каким яз{ыком} подписывать
 */
function makeBorderInfoWindow (id, lang) {
    content = '<div class="infoWindow">'
       + '<h3>' + borders[id]['name_' + lang] + '</h3>'
    if (typeof(borders[id]['set_' + lang]) != 'undefined') {
        content += '<p>' + BORDER_APPEARING + ': ' + dayToDate(borders[id].set).day + ' '
        + dayToDate(borders[id].set).month + ' ' + dayToDate(borders[id].set).year
        + '<p class="present-name">' + borders[id]['set_' + lang]
    }
    if (typeof(borders[id]['disappeared_' + lang]) != 'undefined') {
        content += '<p>' + BORDER_DISAPPEARING + ': ' + dayToDate(borders[id].disappeared).day + ' '
        + dayToDate(borders[id].disappeared).month + ' ' + dayToDate(borders[id].disappeared).year
        + '<p class="present-name">' + borders[id]['disappeared_' + lang]
    }
    content += '</div>'
    return content
}

/**
 * показывает элемент по айди и скрывает предидущий
 * типа "читать далее"
 * @param {string} elementId DOM, который нужно показать
 */
function showMore (elementId) {
    var element = document.getElementById(elementId)
    element.hidden = false
    element.previousElementSibling.hidden = true
}

/**
 * события в этот день
 * @param {Array} points точки
 * @param {number} day текущий день
 * @param {string} lang на каком яз{ыке} выдавать
 */
function getEvents (points, day, lang) {
    var eventList = document.getElementById('event_list')
    var hideLowerTitle = true//спрятать "в этот день"
    eventList.innerHTML = ''

    battles.forEach(function(battle){
        if (battle.beginning < day && day < battle.end) {
            hideLowerTitle = false//показать "в этот день"
            eventList.innerHTML += '<li>' + document.getElementById('battle_continues').innerText
                + ' ' + battle['name_' + lang]
        } else if (battle.beginning == day) {
            hideLowerTitle = false
            eventList.innerHTML += '<li>' + document.getElementById('battle_begins').innerText
                + ' ' + battle['name_' + lang]
        } else if (day == battle.end) {
            hideLowerTitle = false
            eventList.innerHTML += '<li>' + document.getElementById('battle_ends').innerText
                + ' ' + battle['name_' + lang]
        }
    })

    var bordersEvents = {}
    for (var id in borders) {
        if (borders[id].set == day && typeof(borders[id]['set_' + lang]) != 'undefined') {
            bordersEvents[borders[id]['set_' + lang]] = true
            hideLowerTitle = false
        }
        if (borders[id].disappeared == day && typeof(borders[id]['disappeared_' + lang]) != 'undefined') {
            bordersEvents[borders[id]['disappeared_' + lang]] = true
            hideLowerTitle = false
        }
    }
    Object.keys(bordersEvents).forEach(function(borderEvent){
        eventList.innerHTML += '<li>' + borderEvent
    })

    for (var id in points) {
        for (var eventDay in points[id].periods) {
           if (eventDay == day) {
                hideLowerTitle = false//показать "в этот день"
                var event = '<li><a href="?id=' + id + '">' + points[id]['name_' + lang] + '</a>'
                if (typeof(points[id]['present_name_' + lang]) != 'undefined') {
                    event += ' <span class="present-name">' + points[id]['present_name_' + lang] + '</span>'
                }
                var previousArmy = getArmy(day - 1, points[id].periods).id
                var nextArmy = getArmy(day + 1, points[id].periods).id
                event +=
                    '<img src="icons/damnum.png" style="background-image: url(\'icons/'
                    + ARMIES[previousArmy].flag
                    + '_.gif\');" alt="'
                    + ARMIES[previousArmy]['name_' + lang]
                    + '" title="'
                    + ARMIES[previousArmy]['name_' + lang]
                    + '"> → <img src="icons/'
                    + ARMIES[nextArmy].flag
                    + '_.gif" alt="'
                    + ARMIES[nextArmy]['name_' + lang]
                    + '" title="'
                    + ARMIES[nextArmy]['name_' + lang] + '">'
                if (typeof(points[id].periods[eventDay]['comment_' + lang]) != 'undefined') {
                    event += '<span class="more" onclick="showMore(\''
                        + id + '-' + eventDay + '\')">…</span><span class="comment" id="'
                        + id + '-' + eventDay + '" hidden=true>'
                        + points[id].periods[eventDay]['comment_' + lang] + '</span>'
                }
                eventList.innerHTML += event
           }
        }
    }

    document.getElementById('today').hidden = hideLowerTitle
    document.getElementById('day').hidden = hideLowerTitle
}

/**
 * показать URL отображаемой карты
 */
function generateLink (lang) {
    var linkParagraph = document.getElementById('link')
    var latitude = Math.round(map.getCenter().lat()*1000)/1000
    var longitude = Math.round(map.getCenter().lng()*1000)/1000
    var zoom = map.getZoom()
    if (showLink || marks == 'flags') {
        if (
            day != 1
            || lang != ''
            || mapDefaults.center.latitude != latitude
            || mapDefaults.center.longitude != longitude
            || mapDefaults.zoom != zoom
            || marks == 'flags'
        ) {
            var link = []
            if (day != 1) {
                var date = dayToDate(day)
                var months = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII']
                link.push('date=' + date.day + '.' + months[date.m - 1] + '.' + date.year)
            }
            if (mapDefaults.center.latitude != latitude || mapDefaults.center.longitude != longitude) {
                link.push('lat=' + latitude)
                link.push('lon=' + longitude)
            }
            if (mapDefaults.zoom != zoom) {
                link.push('z=' + zoom)
            }
            if (marks == 'flags') {
                link.push('marks=flags')
            }
            if (lang != '') {
                link.push('lang=' + lang)
            }
            link = 'http://milmap.inf.ua/?' + link.join('&')
            linkParagraph.lastChild.innerText = link
            linkParagraph.style.display = ''
        } else {//неравенства
            linkParagraph.style.display = 'none'
        }
    }//showLink
    showLink = true
}