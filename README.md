# EvilPortal
Evil Portal is a captive portal module for the [Hak5](https://www.hak5.org) [Wifi Pineapple](https://www.wifipineapple.com/). This is the repository for the [Wifi Pineapple Nano](http://hakshop.myshopify.com/products/wifi-pineapple?variant=81044992) and [Wifi Pineapple Tetra](http://hakshop.myshopify.com/products/wifi-pineapple?variant=11303845317). If you have a Wifi Pineapple MKV you can find the code for that version [here](https://github.com/frozenjava/evilportal).

## Overview

### Basic Portals
Basic Portals allow you to create a simple captive portal page that is the same for everyone who visits it. This is useful if your needs don't involve different clients seeing different branded pages or pages with unique functionality to them.

### Targeted Portals
Targeted Portals allow you to create different portals to target a specific device or groups of devices based upon your pre-defined conditions. This is incredibly useful if you want all android devices to go to one android themed portal and all clients who are connected to "some-coffee-shop-wifi" go to a different portal all together. Targeted portals currently let you create targeting rules based on mac addresses, ssids, hostnames, and http useragents all on a per-client basis. You can either specify exact string matches or regex matches.

## Manual Installation

First clone the repo and checkout the development branch

```
git clone https://github.com/frozenjava/EvilPortalNano.git
git checkout -b development origin/development
git pull
```

Next change directory to EvilPortalNano

```
cd EvilPortalNano
```

Finally, with your Wifi Pineapple connected upload the EvilPortal directory to the Wifi Pineapple to the /pineapple/modules directory.

```
scp -r EvilPortal root@172.16.42.1:/pineapple/modules/
```

Head on over to the Wifi Pineapples Web Interface and go to the Evil Portal module. You're all done!

## Portal Sendmail function needs to be setup ! (optional)
You need to edit the /etc/ssmtp/ssmtp.conf and change it with your configuration.
For example, configuration for GMAIL(needs less secure apps enabled): 
```
# /etc/ssmtp.conf -- a config file for sSMTP sendmail.
#
# The person who gets all mail for userids < 1000
# Make this empty to disable rewriting.
root=your_email@gmail.com

# The place where the mail goes. The actual machine name is required
# no MX records are consulted. Commonly mailhosts are named mail.domain.com
# The example will fit if you are in domain.com and your mailhub is so named.
mailhub=smtp.gmail.com:465

# Where will the mail seem to come from?
rewriteDomain=gmail.com

# The full hostname
hostname=mail.gmail.com

# Set this to never rewrite the "From:" line (unless not given) and to
# use that address in the "from line" of the envelope.
FromLineOverride=YES

# Use SSL/TLS to send secure messages to server.
UseTLS=YES
#UseSTARTTLS=Yes

AuthUser=your_email@gmail.com
AuthPass=your_gmail_password

# Use SSL/TLS certificate to authenticate against smtp host.
#UseTLSCert=YES

# Use this RSA certificate.
#TLSCert=/etc/ssl/certs/ssmtp.pem﻿
```

Example for Protal with sendmail 

https://github.com/H4xl0r/evilportals/tree/master/portals/mailtoken


## Useful Links
[Official Hak5 Forum Thread](https://forums.hak5.org/index.php?/topic/37874-official-evilportal/)
[Official Youtube Playlist](https://www.youtube.com/playlist?list=PLW7RuuSaPPzDgrZINbNkt4ujR7RDTUCMB)
[My website: frozendevelopment.net](http://frozendevelopment.net/)

## Tasks for Upcoming Release
If you want to contribute to the project feel free to tackle one of these tasks!

### TODO
* Add ability to program commands to run when a portal is enabled/disabled
* writelog function refiew 
* documenatation for portals and functions / calls 

## Release History
* Figured out how to redirect clients going to HTTPS sites 
* Added Mail function via sendmail , Portals can send emails now !

### Version 3.1
* Added ability to write and view logs on a per-portal basis
* Created method <i>writeLog($message)</i> that writes to the portal log file
* Created method <i>notify($message)</i> that sends a notification to the web ui
* Added ability to download files
* Tab button in file editor will now insert four spaces
* Revamped the file editor modal
* Showing file sizes in the portal work bench
* Various quality of life improvements

### Version 3.0
* Add doc strings to all methods in module.php and functions in module.js
* Get SSID of connected client by IP address
* Add ability to route clients to different portals based upon some identifier [ssid, mac vendor, ip, etc...]
* Update the work bench so users can choose between targeted and non-targeted portals
* Create easy-to-use interface for creating targeting rules
* Create some consistency throughout the UI
* Add ability to create portals on an SD card and move between SD and Internal storage easily
* Make white listed and authorized clients IP addresses clickable like SSIDs in PineAP
* Write up some helpful information so people can just start using the module
* Consolidate all portal info into a single portal_name.json file
* Disable the button to move a portal while it is activated
* Fixed client redirection after authorization

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
