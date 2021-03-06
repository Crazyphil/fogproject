#!/bin/bash
#
#  FOG is a computer imaging solution.
#  Copyright (C) 2007  Chuck Syperski & Jian Zhang
#
#   This program is free software: you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation, either version 3 of the License, or
#    any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
#

# Ubuntu Config Settings

# apt-get packages to install
packages="apache2 php5 php5-json php5-gd php5-cli php5-curl mysql-server mysql-client isc-dhcp-server tftpd-hpa tftp-hpa nfs-kernel-server vsftpd net-tools wget xinetd  sysv-rc-conf tar gzip build-essential cpp gcc g++ m4 htmldoc lftp openssh-server php-gettext php5-mcrypt php5-mysqlnd curl libc6 libcurl3 zlib1g php5-fpm"
storageNodePackages="apache2 php5 php5-json php5-cli php5-curl mysql-client nfs-kernel-server vsftpd net-tools wget xinetd sysv-rc-conf tar gzip build-essential cpp gcc g++ m4 lftp php-gettext php5-mysqlnd curl libc6 libcurl3 zlib1g php5-fpm"
langPackages="language-pack-it language-pack-en language-pack-es language-pack-zh-hans"
dhcpname="isc-dhcp-server"
olddhcpname="dhcp3-server"
# where do the init scripts go?
if [ "$systemctl" == "yes" ]; then
	initdpath="/lib/systemd/system";
	initdsrc="../packages/systemd";
    if [[ -e /lib/systemd/system/mariadb.service ]]; then
        ln -s /lib/systemd/system/mariadb.service /lib/systemd/system/mysql.service >/dev/null 2>&1
        ln -s /lib/systemd/system/mariadb.service /lib/systemd/system/mysqld.service >/dev/null 2>&1
        ln -s /lib/systemd/system/mariadb.service /etc/systemd/system/mysql.service >/dev/null 2>&1
        ln -s /lib/systemd/system/mariadb.service /etc/systemd/system/mysqld.service >/dev/null 2>&1
    elif [[ -e /usr/lib/systemd/system/mysqld.service ]]; then
        ln -s /usr/lib/systemd/system/mysqld.service /usr/lib/systemd/system/mysql.service >/dev/null 2>&1
        ln -s /usr/lib/systemd/system/mysqld.service /etc/systemd/system/mysql.service >/dev/null 2>&1
    fi
	initdMCfullname="FOGMulticastManager.service";
	initdIRfullname="FOGImageReplicator.service";
	initdSDfullname="FOGScheduler.service";
	initdSRfullname="FOGSnapinReplicator.service";
	initdPHfullname="FOGPingHosts.service";
else
	initdpath="/etc/init.d";
	initdsrc="../packages/init.d/ubuntu";
	initdMCfullname="FOGMulticastManager";
	initdIRfullname="FOGImageReplicator";
	initdSDfullname="FOGScheduler";
	initdSRfullname="FOGSnapinReplicator";
	initdPHfullname="FOGPingHosts";
fi

# where do the php files go?
if [ -z "$docroot" ]; then
    docroot="/var/www/html/"
    webdirdest="${docroot}fog"
elif [[ "$docroot" != *'fog'* ]]; then
    webdirdest="${docroot}fog"
else
    webdirdest="${docroot}"
fi
if [ "$docroot" == "/var/www/html/" -a ! -d "$docroot" ]; then
    docroot="/var/www/";
fi
webredirect="$docroot/index.php"
apacheuser="www-data"
apachelogdir="/var/log/apache2"
apacheerrlog="$apachelogdir/error.log"
apacheacclog="$apachelogdir/access.log"
etcconf="/etc/apache2/sites-available/001-fog.conf"
phpini="/etc/php5/apache2/php.ini"

# where do we store the image files?
storage="/images"
storageupload="/images/dev"

# DHCP config file location
dhcpconfig="/etc/dhcp/dhcpd.conf"
olddhcpconfig="/etc/dhcp3/dhcpd.conf"

# where do the tftp files go?
tftpdirdst="/tftpboot"

# where is the tftpd config file?
tftpconfig="/etc/xinetd.d/tftp"
tftpconfigupstartconf="/etc/init/tftpd-hpa.conf"
tftpconfigupstartdefaults="/etc/default/tftpd-hpa"

# where is the ftp server config file?
ftpconfig="/etc/vsftpd.conf"

# where do snapins go?
snapindir="/opt/fog/snapins"
packageinstaller="apt-get -yq install -o Dpkg::='--force-confdef' -o Dpkg::Options::='--force-confold'"
packagelist="apt-cache pkgnames | grep"
packageupdater="apt-get -yq upgrade -o Dpkg::='--force-confdef' -o Dpkg::Options::='--force-confold'"
packmanUpdate="apt-get update"
jsontest="php5-json php5-common"
if [ -e "/etc/init.d/${dhcpname}" ]; then
    dhcpd="${dhcpname}"
elif [ -e "/etc/init.d/${olddhcpname}" ]; then
    dhcpd="${olddhcpname}"
fi
