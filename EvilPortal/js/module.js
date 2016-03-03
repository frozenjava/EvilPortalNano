registerController("EvilPortalController", ['$api', '$scope', function($api, $scope) {

	getControls();
	getPortals();

	$scope.portals = [];
	$scope.messages = [];
	$scope.throbber = true;
	$scope.running = false;
	$scope.whiteList = '';
	$scope.whiteListInput = '';
	$scope.accessList = '';
	$scope.accessListInput = '';
	$scope.workshopPortal = {name: "", code: "", storage: "internal"};

	$scope.handleControl = function(control) {
		control.throbber = true;
		switch (control.title) {

			case "CaptivePortal":
				$api.request({
					module: "EvilPortal",
					action: "startStop"
				}, function(response) {
					getControls();
					control.throbber = false;
					$scope.sendMessage(control.title, response.control_message);
					$scope.refreshLivePreview()
				});
				break;

			case "Auto Start":
				$api.request({
					module: "EvilPortal",
					action: "enableDisable"
				}, function(response) {
					getControls();
					control.throbber = false;
					$scope.sendMessage(control.title, response.control_message);
				});
				break;
		}
	}

	$scope.sendMessage = function(t, m) {
		// Add a new message to the top of the list
		$scope.messages.unshift({title: t, msg: m});

		// if there are 4 items in the list remove the 4th item
		if ($scope.messages.length == 4) {
			$scope.dismissMessage(3);
		}
	}

	$scope.dismissMessage = function($index) {
		//var index = $scope.messages.indexOf(message);
		$scope.messages.splice($index, 1);
	}

	function getControls() {
		$scope.throbber = true;
		$api.request({
			module: "EvilPortal",
			action: "getControlValues"
		}, function(response) {
			updateControls(response);
		});
	}

	function getPortals() {
		$api.request({
			module: "EvilPortal",
			action: "portalList"
		}, function(response) {
			$scope.portals = [];
			for (var i = 0; i < response.length; i++) {
				$scope.portals.unshift({title: response[i].title, storage: response[i].location});
				console.log({title: response[i].title, storage: response[i].location});
			}
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
		$scope.throbber = false;
	}

	$scope.createNewPortal = function() {
		console.log($scope.workshopPortal.name);
		console.log($scope.workshopPortal.code);
		$api.request({
			module: "EvilPortal",
			action: "submitPortalCode",
			portalCode: $scope.workshopPortal.code,
			storage: $scope.workshopPortal.storage,
			name: $scope.workshopPortal.name
		}, function(response) {
			$scope.sendMessage("Create New Portal", response.message);
			getPortals();
		});
	}

	$scope.deletePortal = function(portal) {
		console.log(portal.storage);
		console.log(portal.title);
		$api.request({
			module: "EvilPortal",
			action: "deletePortal",
			storage: portal.storage,
			name: portal.title
		}, function(response) {
			$scope.sendMessage("Delete Portal", response.message);
			getPortals();
		});
	}

	$scope.activatePortal = function(portal) {
		$api.request({
			module: "EvilPortal",
			action: "activatePortal",
			storage: portal.storage,
			name: portal.title
		}, function(response) {
			$scope.sendMessage("Activate Portal", response.message);
		});
	}

	$scope.editPortal = function(portal) {
		$api.request({
			module: "EvilPortal",
			action: "getPortalCode",
			storage: portal.storage,
			name: portal.title
		}, function(response) {
			$scope.sendMessage("Edit Portal", response.message);
			$scope.workshopPortal.code = response.code;
			$scope.workshopPortal.name = portal.title;
			$scope.workshopPortal.storage = portal.storage;
		});
	}

	$scope.refreshLivePreview = function() {
		window.frames['livePreviewIframe'].src = "http://172.16.42.1:2050";
	}

	$scope.getList = function(listToGet) {
		$api.request({
			module: "EvilPortal",
			action: "getList",
			listName: listToGet
		}, function(response) {
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
	}

	$scope.addWhiteListClient = function() {
		$api.request({
			module: "EvilPortal",
			action: "addToList",
			listName: "whiteList",
			clientIP: $scope.whiteListInput
		}, function(response) {
			if (response.add_success) {
				$scope.whiteListInput = '';
				$scope.getList("whiteList");
			} else {
				$scope.sendMessage("White List", response.add_message);
				console.log(response);
			}
		});
	}

	$scope.removeWhiteListClient = function() {
		$api.request({
			module: "EvilPortal",
			action: "removeFromList",
			listName: "whiteList",
			clientIP: $scope.whiteListInput
		}, function(response) {
			if (response.remove_success) {
				$scope.whiteListInput = '';
				$scope.getList("whiteList");
			} else {
				$scope.sendMessage("White List", response.remove_message);
				console.log(response);
			}
		});
	}

	$scope.authorizeClient = function() {
		$api.request({
			module: "EvilPortal",
			action: "addToList",
			listName: "accessList",
			clientIP: $scope.accessListInput
		}, function(response) {
			if (response.add_success) {
				$scope.accessListInput = '';
				$scope.getList("accessList");
			} else {
				$scope.sendMessage("Access List", response.add_message);
				console.log(response);
			}
		});
	}

	$scope.revokeClient = function() {
		$api.request({
			module: "EvilPortal",
			action: "removeFromList",
			listName: "accessList",
			clientIP: $scope.accessListInput
		}, function(response) {
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