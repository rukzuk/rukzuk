var system = require('system');

function renderPage(address, output) {
    var page = require('webpage').create();

    //Calc screenshot size
    page.viewportSize = { 'width': system.args[3], 'height': system.args[4] };
    page.clipRect = { 'top': 0, 'left': 0, 'width': system.args[3], 'height': system.args[4] };

    // Open the webpage and render it
    page.open(address, function (status) {
        if (status == 'success') {
            // Fix background color for jpg
            suffix = 'jpg';
            if (output.indexOf(suffix, output.length - suffix.length) !== -1) {
                if (getComputedStyle(document.body, null).backgroundColor === 'rgba(0, 0, 0, 0)') {
                    page.evaluate(function() {document.body.bgColor = 'white';});
                }
            }
            // Fix background color for jpeg
            suffix = 'jpeg';
            if (output.indexOf(suffix, output.length - suffix.length) !== -1) {
                if (getComputedStyle(document.body, null).backgroundColor === 'rgba(0, 0, 0, 0)') {
                    page.evaluate(function() {document.body.bgColor = 'white';});
                }
            }
            // Render output
            page.render(output);
            phantom.exit();
        } else {
            console.log('Unable to load the address: ', address, status );
            phantom.exit(1);
        }
    });
}

// Start rendering
renderPage(system.args[1], system.args[2]);