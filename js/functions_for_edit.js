/**
 * случайный не светлый цвет
 * @returns {string} #RGB цвет
 */
function colorizer() {
    var flag = 0, r, g, b
    var hex = ['0', '1', '2', '3', '4', '5', '6', '7', '8', 'A', 'B', '9', 'C', 'D', 'E', 'F']
    r = hex[Math.floor(Math.random() * 16)]
    if (r == 'C' || r == 'D' || r == 'E' || r == 'F' || r == '9') {
        flag = 1
    }
    g = hex[Math.floor(Math.random() * 16)]
    if (r == 'C' || r == 'D' || r == 'E' || r == 'F' || r == '9') {
        flag += 1
    }
    if (flag < 2) {
        b = hex[Math.floor(Math.random() * 16)]
    } else {
        b = hex[Math.floor(Math.random() * 11)]
    }
    return '#' + r + g + b
}