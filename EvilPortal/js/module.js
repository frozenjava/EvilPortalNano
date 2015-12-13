registerController("EvilPortalController", ['$api', '$scope', function($api, $scope) {

	getControls();
	getPortals();

	$scope.portals = [];
	$scope.messages = [];
	$scope.throbber = true;
	$scope.dependencies = false;
	$scope.running = false;
	$scope.workshopPortal = {name: "", code: "", storage: "internal"};

	$scope.handleControl = function(control) {
		control.throbber = true;
		switch (control.title) {
			case "Dependencies":
				$api.request({
					module: "EvilPortal",
					action: "handleDepends"
				}, function(response) {
					getControls();
					control.throbber = false;
					$scope.sendMessage(control.title, response.control_message);
				});
				break;

			case "NoDogSplash":
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
		var deps;
		var running;
		var autostart;
		if (response.dependencies == false) {
			deps = "Install";
			$scope.sendMessage("Missing Dependencies", "NoDogSplash must first be installed before you can use Evil Portal");
			$scope.dependencies = false;
		} else {
			deps = "Uninstall";
			$scope.dependencies = true;
		}
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
		//alert(deps);
		$scope.controls = [
		{
			title: "Dependencies",
			status: deps,
			visible: true,
			throbber: false
		},
		{
			title: "NoDogSplash",
			status: running,
			visible: response.dependencies,
			throbber: false
		},
		{
			title: "Auto Start",
			status: autostart,
			visible: response.dependencies,
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

}]);