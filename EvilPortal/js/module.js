registerController("EvilPortalTabController", ['$api', '$scope', function($api, $scope) {

	$scope.tabs = [{
		title: "Evil Portal",
		url: "evilportal.controller.html"
	}, {
		title: "Configuration",
		url: "evilportal.config"
	}, {
		title: "Changes",
		url: "evilportal.change"
	}];

	$scope.currentTab = "evilportal.controller.html";

	$scope.onClickTab = function(tab) {
		$scope.currentTab = tab.url;
	}

	$scope.isActiveTab = function(tabUrl) {
		return tabUrl == $scope.currentTab;
	}

}]);

registerController("EvilPortalController", ['$api', '$scope', function($api, $scope) {

	$api.request({
		module: "EvilPortal",
		action: "getControlValues"
	}, function(response) {
		getControls(response);
	});

	$scope.portals = [
	{
		title: "Portal1"
	},
	{
		title: "Portal2"
	},
	{
		title: "Portal3"
	},
	{
		title: "Portal4"
	}];

	$scope.handleDependencies = function() {
		$api.request({
			module: "EvilPortal",
			action: "handleDepends"
		}, function(response) {
			alert(response.error);
		});
	}

	function getControls(response) {
		var deps;
		var running;
		var autostart;
		if (response.dependencies == false) {
			deps = "Install";
		} else {
			deps = "Uninstall";
		}
		if (response.running == false) {
			running = "Start";
		} else {
			running = "Stop";
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
			visible: true
		},
		{
			title: "NoDogSplash",
			status: running,
			visible: response.dependencies
		},
		{
			title: "Auto Start",
			status: autostart,
			visible: response.dependencies
		}];
	}

}]);