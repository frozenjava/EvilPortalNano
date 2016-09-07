# EvilPortal
Evil Portal is a captive portal module for the [Hak5](https://www.hak5.org) [Wifi Pineapple](https://www.wifipineapple.com/). This is the repository for the [Wifi Pineapple Nano](http://hakshop.myshopify.com/products/wifi-pineapple?variant=81044992) and [Wifi Pineapple Tetra](http://hakshop.myshopify.com/products/wifi-pineapple?variant=11303845317). If you have a Wifi Pineapple MKV you can find the code for that version [here](https://github.com/frozenjava/evilportal).

## Upcoming Releases

### TODO
* Figure out how to redirect clients going to HTTPS sites
* Create portal templates for users to choose from
* Update the work bench so users can choose between targeted and non-targeted portals
* Create easy-to-use interface for creating targeting rules

### DONE
* Get SSID of connected client by IP address
* Add ability to route clients to different portals based upon some identifier [ssid, mac vendor, ip, etc...]

## Release History

### Version 2.1
* Removed un-needed verbosity
* Made tab key indent in the editor instead of change elements
* Added confirmation dialogue box when deleting a portal
* Created auto-start feature
* Various other quality of life updates

### Version 2.0
* Captive Portal is now purely iptables (because F*** NoDogSplash)

### Version 1.0
* Install/Remove NoDogSplash
* Start/Stop NoDogSplash
* Enable/Disable NoDogSplash
* Create/Edit/Delete/Active Portals
* Live Preview portals
* All panels collapse for a better mobile experience