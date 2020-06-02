# EvilPortal
Evil Portal is a captive portal module for the [Hak5](https://www.hak5.org) [Wifi Pineapple](https://www.wifipineapple.com/). 

This is a clone of the repository [EvilPortalNano](https://github.com/frozenjava/EvilPortalNano) for the [Wifi Pineapple Nano](http://hakshop.myshopify.com/products/wifi-pineapple?variant=81044992) and [Wifi Pineapple Tetra](http://hakshop.myshopify.com/products/wifi-pineapple?variant=11303845317). 

If you have a Wifi Pineapple MKV you can find the code for that version [here](https://github.com/frozenjava/evilportal).

## Overview
This version of the portal is able generate tokens and send those via mail (from template) and verifies those later.
This brings new abilities.

## Requires you to setup:

--> smtp settings on your pinapple. (smtp server infos needed)

--> edit details of sender and subject of the mail in the MyPortal.php. (The sender and Subject for the user token mail)

--> if needed replace the template.html (email template with your own | the template needs to have a string "TOKEN" !)

## Normal flow : 
User connects ---> enters email / pw ---> Internet

## New possible flow: 
User connects ---> enters email / pw ---> portal sends token ---> User enters Token ---> Internet

## Portals
### Basic Portals
Basic Portals allow you to create a simple captive portal page that is the same for everyone who visits it. This is useful if your needs don't involve different clients seeing different branded pages or pages with unique functionality to them.

### Targeted Portals
Targeted Portals allow you to create different portals to target a specific device or groups of devices based upon your pre-defined conditions. This is incredibly useful if you want all android devices to go to one android themed portal and all clients who are connected to "some-coffee-shop-wifi" go to a different portal all together. Targeted portals currently let you create targeting rules based on mac addresses, ssids, hostnames, and http useragents all on a per-client basis. You can either specify exact string matches or regex matches.

### Token Portals
Token Portals are Basic Portals with ability to generate tokens and send those via mail, those user based tokens will be request later to login. (sure will still ask for email and password !).

## Manual Installation

First clone the repo

```
git clone https://github.com/frozenjava/EvilPortalNano.git
```

Next change directory to EvilPortalNano

```
cd EvilPortalNano
```

Finally, with your Wifi Pineapple connected upload the EvilPortal directory to the Wifi Pineapple to the /pineapple/modules directory.

```
scp -r EvilPortal root@172.16.42.1:/pineapple/modules/
```

# Now you need to setup your SMTP Settings on your pinapple:
(you can ssh and edit the files or use the [Cabinet Module](https://github.com/hak5/wifipineapple-modules/tree/master/Cabinet)) 

```
edit the /etc/ssmtp/ssmtp.conf and change it with your configuration.
For example, configuration for GMAIL:
root@Pineapple:/etc/ssmtp# cat ssmtp.conf
#
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
#TLSCert=/etc/ssl/certs/ssmtp.pem
```
# Edit Email details matching your needs:
```
edit MyPortal.php and change:
$sub = "Google FI - Your WIFI-Token !\nContent-Type: text/html"; //Subject of the mail & html format info just replace "Google FI - Your WIFI-Token !"
$sender = "your_email@gmail.com or your_fake_sender_email@gmail.com"; //Sender of the mail
```


# Change Nginx Servers to support hotspot auto detection on all devices.
# EDIT to / Override  /etc/nginx/nginx.conf with :

```
user root root;
worker_processes  1;



events {
    worker_connections  1024;
}


http {
	include mime.types;
	index index.php index.html index.htm;
	default_type text/html;

	sendfile on;
	keepalive_timeout 65;
	gzip on;
	server_names_hash_bucket_size  64;
	gzip_min_length  1k;
	gzip_buffers     4 16k;
	gzip_http_version 1.0;
	gzip_comp_level 2;
	gzip_types       text/plain application/x-javascript text/css application/xml;
	gzip_vary on;
	server {
	    listen       80;		# Port, make sure it is not in conflict with another http daemon.
	    server_name  www;	# Change this, reference -> http://nginx.org/en/docs/http/server_names.html
		error_page 404 =200 /index.php;
		error_log /dev/null;
		access_log /dev/null;
		fastcgi_connect_timeout 300;
		fastcgi_send_timeout 300;
		fastcgi_read_timeout 300;
		fastcgi_buffer_size 32k;
		fastcgi_buffers 4 32k;
		fastcgi_busy_buffers_size 32k;
		fastcgi_temp_file_write_size 32k;
		client_body_timeout 10;
		client_header_timeout 10;
		send_timeout 60;		# 60 sec should be enough, if experiencing alof of timeouts, increase this.
		output_buffers 1 32k;
		postpone_output 1460;

		root   /www/;		# Your document root, where all public material is.

		location ~ \.php$ {
			fastcgi_index  index.php;
			include        fastcgi_params;
			fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;

			if (-f $request_filename) {
				# Only throw it at PHP-FPM if the file exists (prevents some PHP exploits)
				fastcgi_pass    unix:/var/run/php5-fpm.sock;     # The upstream determined above
			}
		}
		error_page 404 =200 /index.php;
	}

	server {
	    listen       80;		# Port, make sure it is not in conflict with another http daemon.
	    server_name  connectivitycheck.gstatic.com connectivitycheck.android.com clients3.google.com;
		error_page 404 =200 /index.php;
		error_log /dev/null;
		access_log /dev/null;
		fastcgi_connect_timeout 300;
		fastcgi_send_timeout 300;
		fastcgi_read_timeout 300;
		fastcgi_buffer_size 32k;
		fastcgi_buffers 4 32k;
		fastcgi_busy_buffers_size 32k;
		fastcgi_temp_file_write_size 32k;
		client_body_timeout 10;
		client_header_timeout 10;
		send_timeout 60;		# 60 sec should be enough, if experiencing alof of timeouts, increase this.
		output_buffers 1 32k;
		postpone_output 1460;

		root   /www/;		# Your document root, where all public material is.

		location ~ \.php$ {
			fastcgi_index  index.php;
			include        fastcgi_params;
			fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;

			if (-f $request_filename) {
				# Only throw it at PHP-FPM if the file exists (prevents some PHP exploits)
				fastcgi_pass    unix:/var/run/php5-fpm.sock;     # The upstream determined above
			}
		}
		error_page 404 =200 /index.php;
	}

	server {
	    listen       80;		# Port, make sure it is not in conflict with another http daemon.
	    server_name  captive.apple.com www.airport.us www.thinkdifferent.us;
		error_page 404 =200 /index.php;
		error_log /dev/null;
		access_log /dev/null;
		fastcgi_connect_timeout 800;
		fastcgi_send_timeout 600;
		fastcgi_read_timeout 600;
		fastcgi_buffer_size 32k;
		fastcgi_buffers 4 32k;
		fastcgi_busy_buffers_size 32k;
		fastcgi_temp_file_write_size 32k;
		client_body_timeout 15;
		client_header_timeout 15;
		send_timeout 120;		# 60 sec should be enough, if experiencing alof of timeouts, increase this.
		output_buffers 1 32k;
		postpone_output 1460;

		root   /www/;		# Your document root, where all public material is.

		location ~ \.php$ {
			fastcgi_index  index.php;
			include        fastcgi_params;
			fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;

			if (-f $request_filename) {
				# Only throw it at PHP-FPM if the file exists (prevents some PHP exploits)
				fastcgi_pass    unix:/var/run/php5-fpm.sock;     # The upstream determined above
			}
		}
		error_page 404 =200 /index.php;
	}

	server {
	    listen       80;		# Port, make sure it is not in conflict with another http daemon.
	    server_name  www.msftconnecttest.com msftconnecttest.com www.msftncsi.com;
		error_page 404 =200 /index.php;
		error_log /dev/null;
		access_log /dev/null;
		fastcgi_connect_timeout 300;
		fastcgi_send_timeout 300;
		fastcgi_read_timeout 300;
		fastcgi_buffer_size 32k;
		fastcgi_buffers 4 32k;
		fastcgi_busy_buffers_size 32k;
		fastcgi_temp_file_write_size 32k;
		client_body_timeout 10;
		client_header_timeout 10;
		send_timeout 60;		# 60 sec should be enough, if experiencing alof of timeouts, increase this.
		output_buffers 1 32k;
		postpone_output 1460;

		root   /www/;		# Your document root, where all public material is.

		location ~ \.php$ {
			fastcgi_index  index.php;
			include        fastcgi_params;
			fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;

			if (-f $request_filename) {
				# Only throw it at PHP-FPM if the file exists (prevents some PHP exploits)
				fastcgi_pass    unix:/var/run/php5-fpm.sock;     # The upstream determined above
			}
		}
		error_page 404 =200 /index.php;
	}

	server {
	    listen       80;		# Port, make sure it is not in conflict with another http daemon.
	    server_name  detectportal.firefox.com;
		error_page 404 =200 /index.php;
		error_log /dev/null;
		access_log /dev/null;
		fastcgi_connect_timeout 300;
		fastcgi_send_timeout 300;
		fastcgi_read_timeout 300;
		fastcgi_buffer_size 32k;
		fastcgi_buffers 4 32k;
		fastcgi_busy_buffers_size 32k;
		fastcgi_temp_file_write_size 32k;
		client_body_timeout 10;
		client_header_timeout 10;
		send_timeout 60;		# 60 sec should be enough, if experiencing alof of timeouts, increase this.
		output_buffers 1 32k;
		postpone_output 1460;

		root   /www/;		# Your document root, where all public material is.

		location ~ \.php$ {
			fastcgi_index  index.php;
			include        fastcgi_params;
			fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;

			if (-f $request_filename) {
				# Only throw it at PHP-FPM if the file exists (prevents some PHP exploits)
				fastcgi_pass    unix:/var/run/php5-fpm.sock;     # The upstream determined above
			}
		}
		error_page 404 =200 /index.php;
	}


	server {
	    listen       1471;		# Port, make sure it is not in conflict with another http daemon.
	    server_name  pineapple;	# Change this, reference -> http://nginx.org/en/docs/http/server_names.html
		error_page 404 =200 /index.php;
		error_log /dev/null;
		access_log /dev/null;
		fastcgi_connect_timeout 300;
		fastcgi_send_timeout 300;
		fastcgi_read_timeout 300;
		fastcgi_buffer_size 32k;
		fastcgi_buffers 4 32k;
		fastcgi_busy_buffers_size 32k;
		fastcgi_temp_file_write_size 32k;
		client_body_timeout 10;
		client_header_timeout 10;
		send_timeout 60;		# 60 sec should be enough, if experiencing alof of timeouts, increase this.
		output_buffers 1 32k;
		postpone_output 1460;

		root   /pineapple/;		# Your document root, where all public material is.
		add_header 'Cache-Control' 'no-cache, no-store, must-revalidate';

		location ~ \.php$ {
			fastcgi_index  index.php;
			include        fastcgi_params;
			fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;

			if (-f $request_filename) {
				# Only throw it at PHP-FPM if the file exists (prevents some PHP exploits)
				fastcgi_pass    unix:/var/run/php5-fpm.sock;     # The upstream determined above
			}
		}
	}
}
```

Head on over to the Wifi Pineapples Web Interface and go to the Evil Portal module. You're all done!

## Useful Links
[Official Hak5 Forum Thread](https://forums.hak5.org/index.php?/topic/37874-official-evilportal/)
[Official Youtube Playlist](https://www.youtube.com/playlist?list=PLW7RuuSaPPzDgrZINbNkt4ujR7RDTUCMB)
[My website: frozendevelopment.net](http://frozendevelopment.net/)

## Tasks for Upcoming Release

If you want to contribute to the project feel free to tackle one of these tasks!

### TODO
* Add ability to program commands to run when a portal is enabled/disabled

## Release History

### Version 4.B
* Added Sendmail and Token 
* Added Hotspot detection html 
* Added Tracking Script (Pinapple Dashboard Browser Stats (Still Beta))
* Https fix

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
