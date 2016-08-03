#!/bin/bash

####################################
#
# Apache virtualhosts, test and enable them
#
####################################

#exit on error
set -e 

cd $htdocs/build/templates/apache

echo "Creating HTTP 80 apache virtualhost, enabling it & restarting apache"

vhostDest=/etc/apache2/sites-available/$appid.conf
cp VirtualHost $vhostDest

sed -i "s@%APPLICATION_ENV%@$APPLICATION_ENV@" $vhostDest
sed -i "s@%HOSTNAME%@$hostname@" $vhostDest
sed -i "s@%HTDOCS%@$htdocs@" $vhostDest
sed -i "s@%DOCROOT%@$docroot@" $vhostDest

# revert certain vhost directives for apache 2.2
if [ "$apacheversion" = "2.2" ]; then
    sed -i "s@#apache2.4@Order allow,deny@" $vhostDest
    sed -i "s@Require all granted@allow from all@" $vhostDest        
fi

# enable the virtual host
a2ensite $appid.conf

# test the apache config
apachectl configtest

# check if apachectl configtest succeeded
apacheReturnCode=$?   
if [ $apacheReturnCode -ne 0 ];then
    echo "apachectl configtest failed"
    # disable the virtual host
    a2dissite $appid.conf
    exit 1;
fi

# setup SSL vhost
if [ "$letsencrypt" = "yes" ] || [ -f "$certpath.crt" ]; then 

    if [ -f "$certpath.crt" ]; then
        # use existing certificate
        cert="$certpath.crt"
        key="$certpath.key"
    else
        # generate a letsencrypt certificate
        cert="/etc/letsencrypt/live/$hostname/fullchain.pem"
        key="/etc/letsencrypt/live/$hostname/privkey.pem"

        if [ -f $cert ] ; then
            echo "letsencrypt certificate for $hostname and www.$hostname already exists at: $cert"
        else
            echo "Creating letsencrypt certificate for $hostname and www.$hostname"
            letsencrypt --authenticator webroot --installer apache certonly -w $docroot -d $hostname -d www.$hostname --agree-tos --email sysadmin@aerian.com
        fi
    fi

    echo "Creating HTTPS 443 apache virtualhost, enabling it & restarting apache"

    vhostDestSSL=/etc/apache2/sites-available/$appid-SSL.conf
    cp VirtualHostSSL $vhostDestSSL

    # integration_v2 needs to use it's internal IP for the vhost for SNI to work
    if [ "$ip" = "212.159.160.166" ] ; then 
        ip=10.0.1.33
    fi

    sed -i "s@%IP%@$ip@" $vhostDestSSL
    sed -i "s@%APPLICATION_ENV%@$APPLICATION_ENV@" $vhostDestSSL
    sed -i "s@%HOSTNAME%@$hostname@" $vhostDestSSL
    sed -i "s@%HTDOCS%@$htdocs@" $vhostDestSSL
    sed -i "s@%DOCROOT%@$docroot@" $vhostDestSSL
    sed -i "s@%CERT%@$cert@" $vhostDestSSL
    sed -i "s@%KEY%@$key@" $vhostDestSSL
            
    # revert certain vhost directives for apache 2.2
    if [ "$apacheversion" = "2.2" ]; then
        sed -i "s@#apache2.4@Order allow,deny@" $vhostDestSSL
        sed -i "s@Require all granted@allow from all@" $vhostDestSSL        
    fi

    # enable the virtual host
    a2ensite $appid-SSL.conf

    # test the apache config
    apachectl configtest

    # check if apachectl configtest succeeded
    apacheReturnCode=$?   
    if [ $apacheReturnCode -ne 0 ];then
        echo "apachectl configtest failed"
        # disable the virtual host
        a2dissite $appid-SSL.conf
        exit 1;
    fi
fi

# restart apache
apachectl graceful