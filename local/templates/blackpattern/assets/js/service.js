(function () {
    var city = document.getElementById('service-city');
    if (!city) { return; }
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var ns = 'http://www.w3.org/2000/svg';
    var width = 1440;
    var height = 400;
    var seed = 11;
    var random = function () { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; };
    var rectangle = function (x, y, itemWidth, itemHeight, fill, cssClass) {
        var item = document.createElementNS(ns, 'rect');
        item.setAttribute('x', x);
        item.setAttribute('y', y);
        item.setAttribute('width', itemWidth);
        item.setAttribute('height', itemHeight);
        item.setAttribute('fill', fill);
        if (cssClass) { item.setAttribute('class', cssClass); }
        city.appendChild(item);
        return item;
    };
    for (var offset = -10; offset < width;) {
        var buildingWidth = 54 + random() * 70;
        var buildingHeight = 110 + random() * 220;
        var buildingY = height - buildingHeight;
        rectangle(offset, buildingY, buildingWidth - 6, buildingHeight, '#10161c');
        for (var x = offset + 9; x < offset + buildingWidth - 14; x += 19) {
            for (var y = buildingY + 9; y < height - 9; y += 23) {
                var lit = random();
                if (lit < 0.3) {
                    var color = lit < 0.03 ? '#E73101' : (lit < 0.16 ? '#ffd9a0' : '#fff3df');
                    var windowItem = rectangle(x, y, 11, 13, color, !reduce && random() < 0.5 ? 'service-city-window' : '');
                    windowItem.setAttribute('opacity', (0.35 + random() * 0.6).toFixed(2));
                    if (!reduce) {
                        windowItem.style.animationDuration = (2.5 + random() * 4).toFixed(1) + 's';
                        windowItem.style.animationDelay = (random() * 4).toFixed(1) + 's';
                    }
                } else {
                    rectangle(x, y, 11, 13, '#0b0f14');
                }
            }
        }
        offset += buildingWidth + 2;
    }
}());
