var filters = {} || filters;
(function() {

    filters.ColorChannel = {
        RED: 0,
        GREEN: 1,
        BLUE: 2,
        ALPHA: 3
    };

    filters.Point = function(x, y) {
        this.x = x || 0;
        this.y = y || 0;
    };

    filters.DisplacementMap = function(source, map, target, point, scaleX, scaleY, channelX, channelY) {
        
        this.source = source;
        this.map = map;
        this.target = target;

        this.targetCtx = this.target.getContext("2d");

        this.point = point || new filters.Point();
        this.scaleX = scaleX || 0;
        this.scaleY = scaleY || 0;
        this.channelX = channelX || filters.ColorChannel.RED;
        this.channelY = channelY || filters.ColorChannel.RED;
        if (this.channelX != 0 && this.channelX != 1 && this.channelX != 2 && this.channelX != 3) this.channelX = filters.ColorChannel.RED;
        if (this.channelY != 0 && this.channelY != 1 && this.channelY != 2 && this.channelY != 3) this.channelY = filters.ColorChannel.RED;
    };

    var p = filters.DisplacementMap.prototype;

    p.draw = function() {

        var sourceData = this.source;
        var mapData = this.map;


        // copy the data, don't reference:
        var copiedData = this.source.data.slice();
        var targetDataX = new ImageData(copiedData, this.source.width, this.source.height);
        var targetDataY = new ImageData(copiedData, this.source.width, this.source.height);

        var pixelsLength = mapData.data.length / 4;
        var colorValue,
            alphaValue,
            ratio,
            ratioWithAlpha,
            pixelShift,
            sourcePosition,
            targetPosition,
            x,
            y;
        var i = 0;
        while (i < pixelsLength) {
            x = ((i % this.map.width) + this.point.x) | 0;
            y = (((i / this.map.width) | 0) + this.point.y) | 0;
            colorValue = mapData.data[i * 4 + this.channelX];
            alphaValue = mapData.data[i * 4 + filters.ColorChannel.ALPHA];
            ratio = (colorValue / 0xFF * 2) - 1;
            ratioWithAlpha = ratio * (alphaValue / 0xFF);
            pixelShift = (ratioWithAlpha * this.scaleX | 0);
            sourcePosition = (this.source.width * y) + x;
            targetPosition = (this.target.width * y) + x + pixelShift;
            this.setPixels(targetDataX, targetPosition, sourceData, sourcePosition);
            i++;
        }
        i = 0;
        while (i < pixelsLength) {
            x = ((i % this.map.width) + this.point.x) | 0;
            y = (((i / this.map.width) | 0) + this.point.y) | 0;
            colorValue = mapData.data[i * 4 + this.channelY];
            alphaValue = mapData.data[i * 4 + filters.ColorChannel.ALPHA];
            ratio = (colorValue / 0xFF * 2) - 1;
            ratioWithAlpha = ratio * (alphaValue / 0xFF);
            pixelShift = (ratioWithAlpha * this.scaleY | 0);
            sourcePosition = (this.source.width * y) + x;
            targetPosition = (this.target.width * (y + pixelShift)) + x;
            this.setPixels(targetDataY, targetPosition, targetDataX, sourcePosition);
            i++;
        }
        this.targetCtx.putImageData(targetDataY, 0, 0);
      //  isAlreadyRunning = false;
    };

    p.setPixels = function(target, pos, source, i) {
        target.data[i * 4] = source.data[pos * 4];
        target.data[i * 4 + 1] = source.data[pos * 4 + 1];
        target.data[i * 4 + 2] = source.data[pos * 4 + 2];
        target.data[i * 4 + 3] = source.data[pos * 4 + 3];
    };

})();

var source;
var map;
var canvas;
var filter;
var isAlreadyRunning = false;


/*
function myCallback(timestamp) {
    distortX += 0.8;
    //distortY++;
    filter = new filters.DisplacementMap(source, map, canvas, { x: distortX, y: distortY }, 8, 2);
    filter.draw();
    requestAnimationFrame(myCallback);
}
*/

self.onmessage = function(e) {
  //  if(!isAlreadyRunning) {
     //   isAlreadyRunning = true;
 if(e.data.source) {
    source = e.data.source;
}
    if(e.data.map) {
    map = e.data.map;
}
    if(e.data.canvas) {
    canvas = e.data.canvas;
}


    filter = new filters.DisplacementMap(source, map, canvas, { x: e.data.x, y: e.data.y }, e.data.channelX, e.data.channelY);
    filter.draw();
 //   isAlreadyRunning = false;
//}
  //  requestAnimationFrame(myCallback);
};