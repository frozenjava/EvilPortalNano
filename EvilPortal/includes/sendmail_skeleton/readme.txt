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

