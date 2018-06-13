/**
 * горизонтальный ползунок
 * @param {number} initialValue начальное значение
 */
function slider(initialValue) {
    var value = initialValue
    var rail = document.getElementById('rail')
    var slider = document.getElementById('slider')
    rail.style.width = RAIL_WIDTH + 'px'
    slider.style.width = SLIDER_WIDTH + 'px'
    var rightEdge = RAIL_WIDTH - slider.offsetWidth - 2 //border × 2
    var railAbscissa = getAbscissa(rail)
    var outerValue = document.getElementById('outerValue')//для непосредственного ввода значения
    outerValue.value = value
    moveAndDo((value - MIN) / (MAX - MIN) * rightEdge)
    
    //перетаскивание ползунка
    slider.onmousedown = function (element) {
        var shiftX = element.pageX - getAbscissa(slider)
        document.onmousemove = function(element) {
            var newSliderAbscissa = element.pageX - shiftX - railAbscissa
            moveWithMouse(newSliderAbscissa)
        }
        document.onmouseup = function() {
            document.onmousemove = document.onmouseup = null
        }
        return false
    }

    //клацание по направляющей
    rail.onclick = function (element) {
        var newSliderAbscissa = element.pageX - railAbscissa
            - /*середина ползунка, а не левый край*/SLIDER_WIDTH / 2
        moveWithMouse(newSliderAbscissa)
    }

    //клавиши
    document.addEventListener('keydown', function(event) {
        switch(event.keyCode){
            case 37://←
                if (value > MIN) {
                    value --
                    moveAndDo((value - MIN) / (MAX - MIN) * rightEdge)
                }
                break
            case 39://→
                if (value < MAX) {
                    value ++
                    moveAndDo((value - MIN) / (MAX - MIN) * rightEdge)
                }
                break
        }
    })

    //обработка входящего значения
    outerValue.addEventListener('change', function() {
        value = + outerValue.value
        value > MAX ? value = MAX : null
        value < MIN ? value = MIN : null
        moveAndDo((value - MIN) / (MAX - MIN) * rightEdge)
    })

    /**
     * перемещение с помощью мыши
     * @param {number} newSliderAbscissa относительная координата мыши
     */
    function moveWithMouse (newSliderAbscissa) {
        newSliderAbscissa < 0 ? newSliderAbscissa = 0 : null
        newSliderAbscissa > rightEdge ? newSliderAbscissa = rightEdge : null
        value = Math.round(newSliderAbscissa / rightEdge * (MAX - MIN)) + MIN
        moveAndDo(newSliderAbscissa)
    }

    /**
     * координата x элемента
     * @param {object} element DOM
     * @returns {number}
     */
    function getAbscissa(element) {
        return element.getBoundingClientRect().left + pageXOffset
    }

    /**
     * перемещение ползунка и внешняя полезная функция
     * @param {number} newSliderAbscissa координата ползунка
     */
    function moveAndDo (newSliderAbscissa) {
        slider.style.left = newSliderAbscissa + 'px'//движение
        outerValue.value = value
        whatToDoWhenSliderIsMoving(value)//внешняя полезная функция
    }

}
