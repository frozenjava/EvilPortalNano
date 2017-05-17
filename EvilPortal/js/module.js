registerController("EvilPortalController", ['$api', '$scope', function ($api, $scope) {

    // status information about the module
    $scope.evilPortal = {"throbber": false, "sdAvailable": false, "running": false, "startOnBoot": false, "debug": true};

    // controls that belong in the Controls pane
    $scope.controls = [
        { "title": "Captive Portal", "visible": true, "throbber": false, "status": "Start"},
        {"title": "Start On Boot", "visible": true, "throbber": false, "status": "Enable"}
    ];

    // messages to be displayed in the Messages pane
    $scope.messages = [];

    /**
     * Push a message to the Evil Portal Messages Pane
     * @param t: The Title of the message
     * @param m: The message body
     */
    $scope.sendMessage = function (t, m) {
        // Add a new message to the top of the list
        $scope.messages.unshift({title: t, msg: m});

        // if there are 4 items in the list remove the 4th item
        if ($scope.messages.length == 4) {
            $scope.dismissMessage(3);
        }
    };

    /**
     * Remove a message from the Evil Portal Messages pane
     * @param $index: The index of the message in the list to remove
     */
    $scope.dismissMessage = function ($index) {
        $scope.messages.splice($index, 1);
    };

    $scope.handleControl = function(control) {
        control.throbber = true;
        var actionToPreform = null;
        switch(control.title) {
            case "Captive Portal":
                actionToPreform = "toggleCaptivePortal";
                break;

            case "Start On Boot":
                actionToPreform = "toggleOnBoot";
                break;
        }

        if (actionToPreform !== null) {
            $api.request({
                module: "EvilPortal",
                action: actionToPreform
            }, function(response) {
                if (!response.success) {
                    $scope.sendMessage(control.title, response.message);
                }
                getStatus();
            });
        }

    };

    /**
     * Log a given message to the console if debugging is true
     * @param message: The message to log
     */
    function debug_log(message) {
        if ($scope.evilPortal.debug)
            console.log(message);
    }

    /**
     * Update the control models so they reflect the proper information
     */
    function updateControls() {
        $scope.controls = [
            {
                "title": "Captive Portal",
                "status": ($scope.evilPortal.running) ? "Stop" : "Start",
                "visible": true,
                "throbber": false
            },
            {
                "title": "Start On Boot",
                "status": ($scope.evilPortal.startOnBoot) ? "Disable": "Enable",
                "visible": true,
                "throbber": false
            }
        ];
        debug_log($scope.controls);
    }

    /**
     * Get the status's for the controls in the Controls pane and other various information
     */
    function getStatus() {
        $scope.evilPortal.throbber = true;
        $api.request({
            module: "EvilPortal",
            action: "status"
        }, function (response) {
            debug_log(response);
            for (var key in response) {
                if (response.hasOwnProperty(key) && $scope.evilPortal.hasOwnProperty(key)) {
                    $scope.evilPortal[key] = response[key];
                }
            }
            $scope.evilPortal.throbber = false;
            updateControls();
        });
    }

    getStatus();

}]);