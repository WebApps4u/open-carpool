/**
 * wallmonitor-init.js
 * 
 * Initializes the wallmonitor departures page.
 * 
 * Author: Patrick Hund <pahund@team.mobile.de>
 * Since: 2014-02-21
 */
$(document).ready(function () {

    // enter full screen mode and hide fullscreen button when button is clicked
    $('#fullScreen').on('click', function() {
        var $button = $(this), docElement, request;

        docElement = document.documentElement;
        
        request = docElement.requestFullScreen 
                || docElement.webkitRequestFullScreen 
                || docElement.mozRequestFullScreen 
                || docElement.msRequestFullScreen;

        if (typeof request !== 'undefined' && request) {
            request.call(docElement);
        }
        
        $button.hide();
        
        // show button when fullscreen mode is exited by hitting escape key
        window.setTimeout(function () {
            var $document = $(document);
            $document.on('webkitfullscreenchange mozfullscreenchange fullscreenchange', function () {
                $button.show();
                $document.off();
            });
        }, 1000);
        
    });
    
    $('#offers').wallmonitorTimetable({
        ajaxUrl: '/ajax/ajax_wallmonitor.php'
    });
        
});