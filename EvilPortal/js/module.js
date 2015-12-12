registerController("EvilPortalTabController", ['$api', '$scope', function($api, $scope) {

	$scope.tabs = [{
		title: "Portals",
		url: "evilportal.portals.html"
	}, {
		title: "Configuration",
		url: "evilportal.config.html"
	}, {
		title: "Changes",
		url: "evilportal.change.html"
	}];

	$scope.currentTab = "evilportal.portals.html";

	$scope.onClickTab = function(tab) {
		$scope.currentTab = tab.url;
	}

	$scope.isActiveTab = function(tabUrl) {
		return tabUrl == $scope.currentTab;
	}

}]);

registerController("EvilPortalController", ['$api', '$scope', function($api, $scope) {

	getControls();
	getPortals();

	$scope.portals = [];
	$scope.messages = [];
	$scope.throbber = true;
	$scope.dependencies = false;
	$scope.running = false;
	$scope.activePortalCode = "";
	$scope.landingPage = '';

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
			//alert(response[0].title);
			//$scope.portals = response;
			for (var i = 0; i < response.length; i++) {
				$scope.portals.unshift({title: response[i].title, location: response[i].location});
				console.log({title: response[i].title, location: response[i].location});
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

	$scope.getActivePortalCode = function() {
		$api.request({
			module: "EvilPortal",
			action: "activePortalCode"
		}, function(response){
			//$scope.activePortalCode = response.portalCode;
		});
	}

	$scope.updateActivePortal = function() {
		console.log($scope.landingPage);
		/*$api.request({
			module: "EvilPortal",
			action: "updateActivePortal",
			portalCode: $scope.activePortalCode
		}, function(response){
			console.log($scope.activePortalCode);
			$scope.sendMessage("Saving Active Portal", response.message);
		});*/
	}

}]);