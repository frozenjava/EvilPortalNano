registerController("EvilPortalController", ['$api', '$scope', function ($api, $scope) {

    getControls();
    getPortals();

    $scope.portals = [];
    $scope.portalToDelete = null;
    $scope.portalDeleteValidation = '';
    $scope.messages = [];
    $scope.newPortalName = '';
    $scope.newPortalType = 'basic';
    $scope.throbber = true;
    $scope.sdAvailable = false;
    $scope.running = false;
    $scope.library = true;
    $scope.whiteList = '';
    $scope.whiteListInput = '';
    $scope.accessList = '';
    $scope.accessListInput = '';
    $scope.workshopPortal = {name: "", files: [], storage: "internal"};
    $scope.editPortalFile = {portalName: "", storage: "", file: "", code: ""};
    $scope.deleteFile = {};
    $scope.portalRules = {};
    $scope.workingPortalRules = {};

    $scope.handleControl = function (control) {
        control.throbber = true;
        switch (control.title) {

            case "CaptivePortal":
                $api.request({
                    module: "EvilPortal",
                    action: "startStop"
                }, function (response) {
                    getControls();
                    control.throbber = false;
                    if (!response.control_success) {
                        $scope.sendMessage(control.title, response.control_message);
                    }
                    $scope.refreshLivePreview()
                });
                break;

            case "Auto Start":
                $api.request({
                    module: "EvilPortal",
                    action: "enableDisable"
                }, function (response) {
                    getControls();
                    control.throbber = false;
                    if (!response.control_success) {
                        $scope.sendMessage(control.title, response.control_message);
                    }
                });
                break;
        }
    };

    $scope.sendMessage = function (t, m) {
        // Add a new message to the top of the list
        $scope.messages.unshift({title: t, msg: m});

        // if there are 4 items in the list remove the 4th item
        if ($scope.messages.length == 4) {
            $scope.dismissMessage(3);
        }
    };

    $scope.dismissMessage = function ($index) {
        //var index = $scope.messages.indexOf(message);
        $scope.messages.splice($index, 1);
    };

    function getControls() {
        $scope.throbber = true;
        $api.request({
            module: "EvilPortal",
            action: "getControlValues"
        }, function (response) {
            updateControls(response);
        });
    }

    function updateControls(response) {
        var running;
        var autostart;
        if (response.running == false) {
            running = "Start";
            $scope.running = false;
        } else {
            running = "Stop";
            $scope.running = true;
        }
        if (response.autostart == false) {
            autostart = "Enable";
        } else {
            autostart = "Disable";
        }
        $scope.controls = [
            {
                title: "CaptivePortal",
                status: running,
                visible: true,
                throbber: false
            },
             {
             title: "Auto Start",
             status: autostart,
             visible: true,
             throbber: false
             }];
        $scope.sdAvailable = response.sdAvailable;
        $scope.throbber = false;
    }

    $scope.createNewPortal = function (storage) {
        $api.request({
            module: "EvilPortal",
            action: "createNewPortal",
            portalName: $scope.newPortalName.toLowerCase(),
            portalType: $scope.newPortalType,
            storage: storage
        }, function (response) {
            if (response.create_success) {
                getPortals();
                $scope.newPortalName = '';
                $scope.newPortalType = 'basic';
            } else {
                $scope.sendMessage("Error Creating Portal", response.create_message);
            }
        });
    };

    $scope.deletePortalRequest = function(portal) {
        $scope.portalToDelete = portal;
        console.log(portal);
    };

    $scope.deletePortal = function (portal) {
        console.log(portal.storage);
        console.log(portal.title);
        $scope.portalToDelete = null;
        $scope.portalDeleteValidation = null;
        $api.request({
            module: "EvilPortal",
            action: "deletePortal",
            storage: portal.storage,
            name: portal.title
        }, function (response) {
            $scope.sendMessage("Delete Portal", response.message);
            getPortals();
        });
    };

    $scope.requestDeleteFile = function(file, portal) {
        $scope.deleteFile = {name: file, portal: portal.title, storage: portal.storage};
    };

    $scope.sendDeleteFile = function() {
        $api.request({
            module: "EvilPortal",
            action: "deletePortalFile",
            portal: $scope.deleteFile.portal,
            storage: $scope.deleteFile.storage,
            name: $scope.deleteFile.name
        }, function(response) {
            $scope.sendMessage("Delete File", response.message);
            $scope.getPortalFiles($scope.workshopPortal);
        });
    };

    $scope.activatePortal = function (portal) {
        $api.request({
            module: "EvilPortal",
            action: "activatePortal",
            storage: portal.storage,
            name: portal.title
        }, function (response) {
            //$scope.sendMessage("Activate Portal", response.message);
            getPortals();
        });
    };

    $scope.deactivatePortal = function (portal) {
        $api.request({
            module: "EvilPortal",
            action: "deactivatePortal",
            storage: portal.storage,
            name: portal.title
        }, function (response) {
            //$scope.sendMessage("Deactivate Portal", response.message);
            getPortals();
        });
    };

    $scope.editPortal = function (portal, file) {
        $api.request({
            module: "EvilPortal",
            action: "getPortalCode",
            storage: portal.storage,
            name: portal.title,
            portalFile: file
        }, function (response) {
            $scope.editPortalFile.code = response.code;
            $scope.editPortalFile.file = file;
            $scope.editPortalFile.portalName = portal.title;
            $scope.editPortalFile.storage = portal.storage;
            $scope.editPortalFile.updating = true;
        });
    };

    $scope.savePortalCode = function (editFile) {
        $api.request({
            module: "EvilPortal",
            action: "submitPortalCode",
            storage: editFile.storage,
            portalCode: editFile.code,
            name: editFile.portalName,
            fileName: editFile.file
        }, function (response) {
            $scope.editPortalFile = {portalName: "", storage: "", file: "", code: ""};
            $scope.sendMessage("Edit File", response.message);
            $scope.getPortalFiles($scope.workshopPortal);
        });
    };

    $scope.getPortalFiles = function (portal) {
        $api.request({
            module: "EvilPortal",
            action: "portalFiles",
            storage: portal.storage,
            name: portal.title
        }, function (response) {
            console.log(response);
            $scope.workshopPortal.title = portal.title;
            $scope.workshopPortal.storage = portal.storage;
            $scope.workshopPortal.type = portal.type;
            $scope.workshopPortal.files = response.portalFiles;
            $scope.library = false;
        });
    };

    $scope.getPortalRules = function(portal) {
        $api.request({
            module: "EvilPortal",
            "action": "getPortalRules",
            storage: portal.storage,
            name: portal.title
        }, function(response) {
            console.log(response);
            if (response.success) {
                $scope.portalRules = response.data;
                $scope.workingPortalRules = {"rules": {}};

                // welcome to the realm of loops. I will be your guide
                // We have to turn each rule into a keyed set of rules with a rule index represented by var index
                // this is because we need a constant key for each rule when editing on the web interface
                // the index must be removed later before savign the results to the routes.json file
                // if you have a better way to do this you are my hero. Email me n3rdcav3@gmail.com or fork the repo :)

                // This first loop loops over each rule categories such as "mac", "ssid" and so on
                for (var key in response.data['rules']) {

                    // we then create the a object with that key name in our workingData object
                    $scope.workingPortalRules['rules'][key] = {};

                    // Now its time to loop over each category specifier such as "exact" and "regex"
                    for (var specifier in response.data['rules'][key]) {
                        var index = 0;

                        // We then create that specifier in our workingData
                        $scope.workingPortalRules['rules'][key][specifier] = {};

                        // finally we loop over the specific rules defined in the specifier
                        for (var r in response.data['rules'][key][specifier]) {
                            var obj = {};
                            obj['key'] = r;
                            obj['destination'] = response.data['rules'][key][specifier][r];
                            $scope.workingPortalRules['rules'][key][specifier][index] = obj;
                        }
                        // increment index
                        index++;
                    }
                }
                console.log($scope.workingPortalRules);
            } else {
                $scope.sendMessage("Error", response.message)
            }
        });
    };

    $scope.removePortalRule = function(rule, specifier, index) {
        delete $scope.workingPortalRules['rules'][rule][specifier][index];
    };

    $scope.newPortalRule = function(rule, specifier) {
        // make sure the specifier is set
        if ($scope.workingPortalRules['rules'][rule][specifier] == undefined) {
            $scope.workingPortalRules['rules'][rule][specifier] = {};
        }

        var highest = 0;

        // get the highest index
        for (var i in $scope.workingPortalRules['rules'][rule][specifier]) {
            if (parseInt(i) >= highest) {
                highest = i + 1;
            }
        }

        $scope.workingPortalRules['rules'][rule][specifier][highest] = {"": ""};

    };

    $scope.commitPortalRule = function (rule, specifier, index, key, value) {
        var obj = {};
        obj['key'] = key;
        obj['destination'] = value;
        $scope.workingPortalRules['rules'][rule][specifier][index] = obj;
    };

    $scope.savePortalRules = function(portal) {
        // build the rules
        for (var key in $scope.portalRules.rules) {
            for (var specifier in $scope.portalRules.rules[key]) {
                var obj = {};
                for (var i in $scope.workingPortalRules['rules'][key][specifier]) {
                    //for (var r in $scope.workingPortalRules['rules'][key][specifier][i]) {
                    //    obj[r] = $scope.workingPortalRules['rules'][key][specifier][i]['destination'];
                    //}
                    obj[$scope.workingPortalRules['rules'][key][specifier][i]['key']] = $scope.workingPortalRules['rules'][key][specifier][i]['destination'];
                }
                $scope.portalRules['rules'][key][specifier] = obj;
            }
        }

        console.log(JSON.stringify($scope.portalRules));

        $api.request({
            module: "EvilPortal",
            action: "savePortalRules",
            portal: portal.title,
            storage: portal.storage,
            rules: JSON.stringify($scope.portalRules)
        }, function(response) {
            if (!response.success) {
                $scope.sendMessage("Error", response.message);
            }
        });
    };

    $scope.isObjectEmpty = function(obj) {
        return (Object.keys(obj).length === 0);
    };

    function getPortals() {
        $api.request({
            module: "EvilPortal",
            action: "portalList"
        }, function (response) {
            $scope.portals = [];
            for (var i = 0; i < response.length; i++) {
                $scope.portals.unshift({
                    title: response[i].title,
                    storage: response[i].location,
                    active: response[i].active,
                    type: response[i].type
                });
                //console.log({title: response[i].title, storage: response[i].location, active: response[i].active});
            }
        });
    }

    $scope.refreshLivePreview = function () {
        window.frames['livePreviewIframe'].src = "http://172.16.42.1";
    };

    $scope.getList = function (listToGet) {
        $api.request({
            module: "EvilPortal",
            action: "getList",
            listName: listToGet
        }, function (response) {
            if (response.list_success) {
                if (listToGet == "whiteList") {
                    $scope.whiteList = response.list_contents;
                } else if (listToGet == "accessList") {
                    $scope.accessList = response.list_contents;
                }
            } else {
                $scope.sendMessage("List Data Error", response.list_message);
                console.log(response);
            }
        });
    };

    $scope.addWhiteListClient = function () {
        $api.request({
            module: "EvilPortal",
            action: "addToList",
            listName: "whiteList",
            clientIP: $scope.whiteListInput
        }, function (response) {
            if (response.add_success) {
                $scope.whiteListInput = '';
                $scope.getList("whiteList");
            } else {
                $scope.sendMessage("White List", response.add_message);
                console.log(response);
            }
        });
    };

    $scope.removeWhiteListClient = function () {
        $api.request({
            module: "EvilPortal",
            action: "removeFromList",
            listName: "whiteList",
            clientIP: $scope.whiteListInput
        }, function (response) {
            if (response.remove_success) {
                $scope.whiteListInput = '';
                $scope.getList("whiteList");
            } else {
                $scope.sendMessage("White List", response.remove_message);
                console.log(response);
            }
        });
    };

    $scope.authorizeClient = function () {
        $api.request({
            module: "EvilPortal",
            action: "addToList",
            listName: "accessList",
            clientIP: $scope.accessListInput
        }, function (response) {
            if (response.add_success) {
                $scope.accessListInput = '';
                $scope.getList("accessList");
            } else {
                $scope.sendMessage("Access List", response.add_message);
                console.log(response);
            }
        });
    };

    $scope.revokeClient = function () {
        $api.request({
            module: "EvilPortal",
            action: "removeFromList",
            listName: "accessList",
            clientIP: $scope.accessListInput
        }, function (response) {
            if (response.remove_success) {
                $scope.accessListInput = '';
                $scope.getList("accessList");
            } else {
                $scope.sendMessage("Access List", response.remove_message);
                console.log(response);
            }
        });
    }


}]);