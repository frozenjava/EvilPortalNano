registerController("EvilPortalController", ['$api', '$scope', function ($api, $scope) {

    // status information about the module
    $scope.evilPortal = {"throbber": false, "sdAvailable": false, "running": false, "startOnBoot": false};

    // controls that belong in the Controls pane
    $scope.controls = [
        { "title": "Captive Portal", "visible": true, "throbber": false, "status": "Start"},
        {"title": "Start On Boot", "visible": true, "throbber": false, "status": "Enable"}
    ];

    // messages to be displayed in the Messages pane
    $scope.messages = [];

    // all of the portals that could be found
    $scope.portals = [];

    // a model of a new portal to create
    $scope.newPortal = {"type": "basic", "name": ""};

    // deleting portal stuff
    $scope.portalToDelete = null;
    $scope.portalDeleteValidation = null;

    /**
     * Push a message to the Evil Portal Messages Pane
     * @param t: The Title of the message
     * @param m: The message body
     */
    $scope.sendMessage = function (t, m) {
        // Add a new message to the top of the list
        $scope.messages.unshift({title: t, msg: m});

        // if there are 4 items in the list remove the 4th item
        if ($scope.messages.length === 4) {
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

    /**
     * Preform an action for a given control
     * This can be starting the captive portal or toggle on boot.
     * @param control: The control to handle
     */
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
     * Validates the information in the newPortal model and then makes an API request to create a new portal.
     * @param storage: The storage medium to create the portal on (internal or sd)
     */
    $scope.createNewPortal = function(storage) {
        $api.request({
            module: "EvilPortal",
            action: "createNewPortal",
            name: $scope.newPortal.name,
            type: $scope.newPortal.type,
            storage: storage
        }, function(response) {
            if (!response.success) {
                $scope.sendMessage('Error Creating Portal', response.message);
                return;
            }
            $scope.newPortal = {"type": "basic", "name": ""};
            getPortals();
        });
    };

    /**
     * Move a given portal between storage mediums if an SD card is present.
     * @param portal: The portal to move
     */
    $scope.movePortal = function(portal) {
        if (!$scope.evilPortal.sdAvailable) {
            $scope.sendMessage("No SD Card.", "An SD card must be present to preform this action.");
            return;
        }

        $api.request({
            module: "EvilPortal",
            action: "movePortal",
            name: portal.title,
            storage: portal.storage
        }, function(response) {
            if (response.success) {
                getPortals();
                $scope.sendMessage("Moved Portal", response.message);
            } else {
                $scope.sendMessage("Error Moving " + portal.title, response.message);
            }
        });
    };

    /**
     * Delete a portal from the wifi pineapple
     * @param verified: Has the delete request been verified? If so then make the API request otherwise setup
     * @param portal: The portal to delete
     */
    $scope.deletePortal = function(verified, portal) {
        if (!verified) {  // if the request has not been verified then setup the shits
            $scope.portalToDelete = portal;
            return;
        }

        if ($scope.portalToDelete === null || $scope.portalToDelete.fullPath === null) {
            $scope.sendMessage("Unable To Delete Portal", "No portal was set for deletion.");
            return;
        }
        deleteFileOrDirectory($scope.portalToDelete.fullPath, function (response) {
            if (!response.success) {
                $scope.sendMessage("Error Deleting Portal", response.message);  // push an error if deletion failed
            } else {
                $scope.sendMessage("Deleted Portal", "Successfully deleted " + $scope.portalToDelete.title.toUpperCase() + ".");
                $scope.portalToDelete = null;
                $scope.portalDeleteValidation = null;
                getPortals();  // refresh the library
            }
        });
    };

    /**
     * Activate a portal
     * @param portal: The portal to activate
     */
    $scope.activatePortal = function(portal) {
        $api.request({
            module: "EvilPortal",
            action: "activatePortal",
            name: portal.title,
            storage: portal.storage
        }, function(response) {
            if (response.success) {
                getPortals();
                $scope.sendMessage("Activated Portal", portal.title + " has been activated successfully.");
            } else {
                $scope.sendMessage("Error Activating " + portal.title, response.message);
            }
        });
    };

    /**
     * Deactivate a given portal if its active
     * @param portal: The portal to deactivate
     */
    $scope.deactivatePortal = function(portal) {
        $api.request({
            module: "EvilPortal",
            action: "deactivatePortal",
            name: portal.title,
            storage: portal.storage
        }, function(response) {
            if (response.success) {
                getPortals();
                $scope.sendMessage("Deactivated Portal", portal.title + " has been deactivated successfully.");
            } else {
                $scope.sendMessage("Error Deactivating " + portal.title, response.message);
            }
        });
    };

    /**
     * Delete a file or directory from the pineapples filesystem.
     * This is intended to be used for only deleting portals and portal related files but anything can be delete.
     * @param fileOrDirectory: The path to the file to delete
     * @param callback: The callback function to handle the API response
     */
    function deleteFileOrDirectory(fileOrDirectory, callback) {
        $api.request({
            module: "EvilPortal",
            action: "deleteFile",
            filePath: fileOrDirectory
        }, function(response) {
            callback(response);
        });
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
            for (var key in response) {
                if (response.hasOwnProperty(key) && $scope.evilPortal.hasOwnProperty(key)) {
                    $scope.evilPortal[key] = response[key];
                }
            }
            $scope.evilPortal.throbber = false;
            updateControls();
        });
    }

    /**
     * Get all of the portals on the Pineapple
     */
    function getPortals() {
        $scope.evilPortal.throbber = true;
        $api.request({
            module: "EvilPortal",
            action: "listAvailablePortals"
        }, function(response) {
            if (!response.success) {
                $scope.sendMessage("Error Listing Portals", "An error occurred while trying to get list of portals.");
                return;
            }
            $scope.portals = [];
            response.portals.forEach(function(item, index) {
                $scope.portals.unshift({
                    title: item.title,
                    storage: item.storage,
                    active: item.active,
                    type: item.portalType,
                    fullPath: item.location
                });
            });
        });
    }

    // The status for the Evil Portal module as well as current portals should be retrieved when the controller loads.
    getStatus();
    getPortals();


}]);