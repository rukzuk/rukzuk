/* webkit bugfix https://bugs.webkit.org/show_bug.cgi?id=53166 */
(function (global) {

    var isWebkit = /webkit/.test(navigator.userAgent.toLowerCase());
    var tableLayoutElements = [];

    function webkitFixTableLayout() {
        if (isWebkit) {
            for (var i = 0; i < tableLayoutElements.length; ++i) {
                var el = tableLayoutElements[i];
                // force repaint (http://stackoverflow.com/a/3485654)
                el.className = el.className + ' wkFixTableLayoutRun';
                /* jshint expr: true */
                el.offsetHeight;
                /* jshint expr: false */
                el.className = el.className.replace(/( wkFixTableLayoutRun)+$/, '');
            }
        }
    }

    if (isWebkit) {
        tableLayoutElements = document.getElementsByClassName('wkFixTableLayout');

        global.addEventListener('resize', function () {
            webkitFixTableLayout();
        }, false);
    }

    global.webkitFixTableLayout = webkitFixTableLayout;
}(window));
