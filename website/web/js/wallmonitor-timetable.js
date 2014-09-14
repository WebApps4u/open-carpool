/**
 * wallmonitor-timetable.js
 * 
 * jQuery plugin that displays offered lifts on the wallmonitor departures timetable.
 * 
 * Author: Patrick Hund <pahund@team.mobile.de>
 * Since: 2014-02-21
 */
(function ($) {

    var WallmonitorTimetable = function (element, options) {

        options = $.extend({}, $.fn.wallmonitorTimetable.defaults, options);
        this.$element = $(element);
        this.ajaxUrl = options.ajaxUrl;
        if (typeof this.ajaxUrl === 'undefined') {
            this.displayError(
                'Failed to initialize wallmonitor timetable plugin',
                'required option "url" is not set');
        }
        
        this.updateInterval = options.updateInterval;

        this.initTable();
        this.intervalHandle = window.setInterval($.proxy(this.initTable, this), this.updateInterval);
    };


    WallmonitorTimetable.prototype = {
        
        initTable: function () {
            var self = this;
            $.getJSON(this.ajaxUrl).done($.proxy(self.initTableSuccess, self)).fail(function (jqxhr, textStatus, error) {
                self.handleError(textStatus, error);
            });
        },
        
        initTableSuccess: function (data) {
            if (data.error !== null) {
                var message = typeof data.error.message === 'undefined' ? data.error : data.error.message;
                this.handleError("Ajax request successful, but shows error message", message);
            } else {
                if (data.result.length === 0) {
                    this.displayInfo("Currently no lifts are offered");
                } else {
                    this.$element.empty();
                    for (var rowIndex = 0; rowIndex < data.result.length; rowIndex++) {
                        var row = data.result[rowIndex];
                        this.$element.append(
                            '<div class="row">' +
                            '<div class="col-md-1">' + row.time + '</div>' +
                            '<div class="col-md-6">' + row.route + '</div>' +
                            '<div class="col-md-3">' +
                            '<img class="gravatar" src="' + row.gravatarUrl + '"> ' + row.driver + '</div>' +
                            '<div class="col-md-2">' + row.phone + '</div>' +
                            '</div>');
                    }
                }
            }            
        },

        handleError: function (textStatus, error) {
            // stop cyclic Ajax polling if error occurred
            if (typeof this.intervalHandle !== 'undefined') {
                window.clearInterval(this.intervalHandle);
            }
            this.displayError(textStatus, error);
        },
        
        displayError: function (textStatus, error) {
            this.$element.html(
                '<div class="alert alert-danger"><h2>Whoopsie! Error!</h2>' +
                '<p>status: ' + textStatus + '</p>' +
                '<p>error message: ' + error + '</p>');
        },
    
        displayInfo: function (message) {
            this.$element.html('<div class="alert alert-info"><h2>' + message + '</h2>');
        }
    }

    $.fn.wallmonitorTimetable = function (input) {
        return this.each(function () {
            var $this = $(this), data = $this.data('wallmonitorTimetable');

            // if the plugin script is not already attached to the selected node...
            if (!data) {
                // if options were passed to the plugin as an object, pass them on
                var options = typeof input == 'object' && input;

                // initialize the plugin
                $this.data('wallmonitorTimetable', (data = new WallmonitorTimetable(this, options)));
            }

            // if a simple string is passed to the plugin, it is interpreted as a command,
            // and the corresponding method of the plugin is executed
            if (typeof input == 'string') {
                return data[input]();
            }
        });
    };

    // default values for options if none are explicitly set through the plugin
    $.fn.wallmonitorTimetable.defaults = {
        updateInterval: 10000 // refresh table every 10 seconds
    };

} (jQuery));