#
#  FOG is a computer imaging solution.
#  Copyright (C) 2007  Chuck Syperski & Jian Zhang
#
#   This program is free software: you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation, either version 3 of the License, or
#   any later version.
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
# Linux Account that is used for FTP transactions
username="fog";
# This is used for storage nodes
dbuser="root"
dbpass=""
dbhost="p:127.0.0.1"
# where are the php files from the download package?
webdirsrc="../packages/web";
# where are the tftp files from the download package?
tftpdirsrc="../packages/tftp";
# where are the udpcast files from the download package?
udpcastsrc="../packages/udpcast-20120424.tar.gz";
udpcasttmp="/tmp/udpcast.tar.gz";
udpcastout="udpcast-20120424";
# where are the service files from the download package?
servicesrc="../packages/service";
# where do the service files go?
servicedst="/opt/fog/service"
# where do the service log files go?
servicelogs="/opt/fog/log"
# where do the fog program files go?
fogprogramdir="/opt/fog"
# where do generic fog utils go?
fogutilsdir="${fogprogramdir}/utils";
# where do generic fog utils come from?
fogutilsdirsrc="../packages/utils";
# where is the nfs exports file?
nfsconfig="/etc/exports";
# what are the potential NFS service names
nfsservice="nfs nfs-server nfs-kernel-server";
# what version are we working with?
version="`awk -F\' /"define\('FOG_VERSION'[,](.*)"/'{print $4}' ../packages/web/lib/fog/System.class.php | tr -d '[[:space:]]'`";
sqlclientlist="mysql mariadb MariaDB-client"
sqlserverlist="mysql-server mariadb-server mariadb-galera-server MariaDB-server MariaDB-Galera-server";
# what is the schema version
schemaversion="181";
if [ "$systemctl" == "yes" ]; then
    initdsrc="../packages/systemd";
    initdMCfullname="FOGMulticastManager.service";
    initdIRfullname="FOGImageReplicator.service";
    initdSDfullname="FOGScheduler.service";
    initdSRfullname="FOGSnapinReplicator.service";
    if [[ "$linuxReleaseName" == +(*'Ubuntu'*|*'Debian'*) ]]; then
        initdpath="/lib/systemd/system";
    else
        initdpath="/usr/lib/systemd/system";
    fi
fi
