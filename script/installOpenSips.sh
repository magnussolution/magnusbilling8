#!/bin/bash
clear
echo
echo
echo
echo "=======================WWW.MAGNUSSOLUTION.COM===========================";
echo "_      _                               ______ _ _ _ _                  ";
echo "|\    /|                               | ___ (_) | (_)                 ";
echo "| \  / | ___  ____  _ __  _   _  _____ | |_/ /_| | |_ _ __   ____      ";
echo "|  \/  |/   \/  _ \| '_ \| | | \| ___| | ___ \ | | | | '_ \ /  _ \     ";
echo "| |\/| |  | |  (_| | | | | |_| ||____  | |_/ / | | | | | | |  (_| |    ";
echo "|_|  |_|\___|\___  |_| | |_____|_____|  \___/|_|_|_|_|_| |_|\___  |    ";
echo "                _/ |                                           _/ |    ";
echo "               |__/                                           |__/     ";
echo "                                                                       ";
echo "======================= VOIP SYSTEM FOR LINUX =========================";
echo

unset HISTFILE
if [ -z "$1" ]; then
  echo "Using ./installOpenSips.sh LOCAL_IP MBILLING_IP LOCANET(optinal)";
  exit
fi

if [ -z "$2" ]; then
  echo "Using ./installOpenSips.sh LOCAL_IP MBILLING_IP LOCANET(optinal)";
  exit
fi

sleep 3

proxyip=$1
ipMbilling=$2

if [ -z "$3" ]; then
  localIP=$1
else
  localIP=$3
fi

echo "TRIS SCRIPT WILL INSTALL OPENSIPS IN THIS SERVER PUBLIC IP $proxyip LOCALNET $localIP TO CONNECT WITH MAGNUSBILLING IP $ipMbilling"


sed 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config > borra && mv -f borra /etc/selinux/config
yum -y install kernel-devel.`uname -m` epel-release
yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum -y install yum-utils gcc gcc-c++ bison flex zlib-devel openssl-devel  subversion pcre-devel
yum-config-manager --enable remi-php71
yum -y install  unixODBC unixODBC-devel libtool-ltdl libtool-ltdl-devel  php-mbstring php-mcrypt flex screen php php-gd php-pear
yum -y install gcc-c++ bison lynx subversion flex curl-devel libxslt libxml2-devel libxml2 pcre-devel wget make php-mysql  wget make rsyslog sqlite*

yum install -y mariadb-server  mariadb-devel mariadb php-mysql

systemctl enable httpd.service && systemctl enable mariadb


MAXSIZE=10

array1=( 
w e r t y u p a s d f h j k z x c v b m Q W E R T Y U P A D 
F H J K L Z X C V B N M 2 3 4 7 8
) 


MODNUM=${#array1[*]} 

pwd_len=0 

while [ $pwd_len -lt $MAXSIZE ] 
do 
  index=$(($RANDOM%$MODNUM)) 
  password="${password}${array1[$index]}" 
  ((pwd_len++)) 
done 

if [ -e "/root/passwordMysql.log" ] && [ ! -z "/root/passwordMysql.log" ]
then
    password=$(awk '{print $1}' /root/passwordMysql.log)
fi


touch /root/passwordMysql.log
echo "$password" > /root/passwordMysql.log

chmod -R 777 /tmp

systemctl start  mariadb
mysqladmin -u root password $password


cd /usr/src
yum install -y epel-release
yum install -y https://yum.opensips.org/2.4/releases/el/7/x86_64/opensips-yum-releases-2.4-6.el7.noarch.rpm
yum install -y opensips opensips-db_mysql

touch /var/log/opensips.log
chown opensips:opensips /var/log/opensips.log

echo "DBENGINE=MYSQL
DBHOST=localhost
DBNAME=opensips
DBRWUSER=opensips
DBRWPW=$password
" > /etc/opensips/opensipsctlrc



sed -i "s/NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES/MYSQL40/g" /etc/my.cnf
systemctl restart  mariadb

clear
echo ""
echo ""
echo "----------------USE this password $password--------------------"
echo ""
echo ""

PW=$password
export PW
yes | opensipsdbctl create
chkconfig opensips on
systemctl enable opensips

systemctl restart opensips 

mysql -u root -p$(awk '{print $1}' /root/passwordMysql.log) opensips -e "ALTER TABLE subscriber ADD accountcode VARCHAR( 50 ) NOT NULL"
mysql -u root -p$(awk '{print $1}' /root/passwordMysql.log) opensips -e "ALTER TABLE subscriber ADD trace TINYINT(1) NOT NULL DEFAULT '0'"
mysql -u root -p$(awk '{print $1}' /root/passwordMysql.log) opensips -e "ALTER TABLE subscriber ADD  cpslimit INT( 11 ) NOT NULL DEFAULT  '-1'"
mysql -u root -p$(awk '{print $1}' /root/passwordMysql.log) opensips -e "ALTER TABLE address CHANGE context_info  context_info CHAR( 70 ) NULL DEFAULT NULL ;"
mysql -u root -p$(awk '{print $1}' /root/passwordMysql.log) opensips -e "ALTER TABLE subscriber CHANGE password  password CHAR( 70 )  NOT NULL DEFAULT '';"

echo "
* * * * * /usr/sbin/opensipsctl fifo ds_reload
* * * * * /usr/sbin/opensipsctl address reload
" > /var/spool/cron/root


cd /etc/opensips/
mv opensips.cfg opensips.cfg_old
wget https://raw.githubusercontent.com/magnussolution/magnusbilling7/source/script/opensips.cfg

sed -i "s/MYSQLUSER:MYSQLPASS/root:$password/g" /etc/opensips/opensips.cfg
sed -i "s/MYIP/$proxyip/g" /etc/opensips/opensips.cfg
sed -i "s/LOCALIP/$localIP/g" /etc/opensips/opensips.cfg


MAXSIZE=10

array1=( 
w e r t y u p a s d f h j k z x c v b m Q W E R T Y U P A D 
F H J K L Z X C V B N M 2 3 4 7 8
) 


MODNUM=${#array1[*]} 

pwd_len=0 

while [ $pwd_len -lt $MAXSIZE ] 
do 
  index=$(($RANDOM%$MODNUM)) 
  password2="${password2}${array1[$index]}" 
  ((pwd_len++)) 
done 

if [ -e "/root/.passwordMysqlToMbilling" ] && [ ! -z "/root/.passwordMysqlToMbilling" ]
then
    password2=$(awk '{print $1}' /root/.passwordMysqlToMbilling)
fi

touch /root/.passwordMysqlToMbilling
echo "$password2" > /root/.passwordMysqlToMbilling


# now create mysql user to allow MagnusBilling write in database
mysql -uroot -p${password} -e "CREATE USER 'proxy_magnus'@'$ipMbilling' IDENTIFIED BY '${password2}';"
mysql -uroot -p${password} -e "GRANT ALL PRIVILEGES ON \`opensips\` . * TO 'proxy_magnus'@'$ipMbilling' WITH GRANT OPTION;FLUSH PRIVILEGES;"    

echo "INSTALL MONITOR TOLLS"
cd /usr/local/sbin
wget magnusbilling.com/download/sip
wget magnusbilling.com/download/config
chmod 777 /usr/local/sbin/*
yum install -y ngrep htop


yum -y install libpcap-devel autoconf automake git ncurses-devel
cd /usr/src
git clone https://github.com/irontec/sngrep.git
cd sngrep
./bootstrap.sh
./configure
make && make install 


echo 'set filter.methods INVITE
set cl.column0 method
set cl.column1 sipfromuser
set cl.column1.width 15
set cl.column2 siptouser
set cl.column3 src
set cl.column4 dst
set cl.column4.width 22
set cl.column5 starting
set cl.column6 warning
set cl.column6.width 0' > /usr/local/etc/sngreprc


echo '
module(load="imuxsock") # provides support for local system logging
module(load="imklog")   # provides kernel logging support
$ActionFileDefaultTemplate RSYSLOG_TraditionalFileFormat
$FileOwner root
$FileGroup adm
$FileCreateMode 0640
$DirCreateMode 0755
$Umask 0022
$WorkDirectory /var/spool/rsyslog
$IncludeConfig /etc/rsyslog.d/*.conf
auth,authpriv.*     /var/log/auth.log
*.*;auth,authpriv.none    -/var/log/syslog
#cron.*       /var/log/cron.log
daemon.*      -/var/log/daemon.log
kern.*        -/var/log/kern.log
lpr.*       -/var/log/lpr.log
mail.*        -/var/log/mail.log
user.*        -/var/log/user.log
mail.info     -/var/log/mail.info
mail.warn     -/var/log/mail.warn
mail.err      /var/log/mail.err
*.=debug;\
  auth,authpriv.none;\
  mail.none   -/var/log/debug
*.=info;*.=notice;*.=warn;\
  auth,authpriv.none;\
  cron,daemon.none;\
  mail.none   -/var/log/messages
*.emerg       :omusrmsg:*
local0.*       /var/log/opensips.log
' > /etc/rsyslog.conf


systemctl restart rsyslog
systemctl restart opensips 

yum install -y unzip


apachectl restart


echo
echo "Installing Fail2ban & Iptables"
echo
yum install firewalld -y
systemctl start firewalld
systemctl enable firewalld
firewall-cmd --zone=public --add-port=19639/tcp --permanent
firewall-cmd --zone=public --add-port=22/tcp --permanent
firewall-cmd --zone=public --add-port=5060/udp --permanent
firewall-cmd --zone=public --add-port=35000-65535/udp --permanent
firewall-cmd --zone=public --add-port=80/tcp --permanent
firewall-cmd --zone=public --add-rich-rule="
  rule family=\"ipv4\"
  source address=\"$ipMbilling/32\"
  port protocol=\"tcp\" port=\"3306\" accept" --permanent
firewall-cmd --reload
firewall-cmd --zone=public --list-all


yum install -y fail2ban fail2ban-systemd
systemctl enable fail2ban

echo "
[DEFAULT]
ignoreip = 127.0.0.1
bantime  = 600
findtime  = 600
maxretry = 3
backend = auto
usedns = warn


[ssh-iptables]
enabled  = true
filter   = sshd
action   = iptables-allports[name=SSH, port=all, protocol=all]
logpath  = /var/log/secure
maxretry = 3
bantime = 600

[opensips-iptables]
enabled  = true
filter   = opensips
action   = iptables-allports[name=OPENSIPS, protocol=all]
logpath  = /var/log/opensips.log
maxretry = 10
bantime  = 1800

 " >> /etc/fail2ban/jail.local



echo $'
[INCLUDES]
before = common.conf
[Definition]
_daemon = sshd
failregex = ^%(__prefix_line)s(?:error: PAM: )?Authentication failure for .* from <HOST>\s*$
            ^%(__prefix_line)s(?:error: PAM: )?User not known to the underlying authentication module for .* from <HOST>\s*$
            ^%(__prefix_line)sFailed (?:password|publickey) for .* from <HOST>(?: port \d*)?(?: ssh\d*)?$
            ^%(__prefix_line)sFailed (?:password|publickey) for .* from <HOST>(?: port \d*)?(?: ssh\d*)?.*$
            ^%(__prefix_line)sFailed (?:password|publickey) for .* from <HOST>(?: port \d*)?(?: ssh2\d*)?.*$
            ^%(__prefix_line)sFailed (?:password|publickey) for .* from <HOST>(?: port \d*)?(?: ssh2\d*)?$
            ^%(__prefix_line)sROOT LOGIN REFUSED.* FROM <HOST>\s*$
            ^%(__prefix_line)s[iI](?:llegal|nvalid) user .* from <HOST>\s*$
            ^%(__prefix_line)sUser \S+ from <HOST> not allowed because not listed in AllowUsers$
            ^%(__prefix_line)sauthentication failure; logname=\S* uid=\S* euid=\S* tty=\S* ruser=\S* rhost=<HOST>(?:\s+user=.*)?\s*$
            ^%(__prefix_line)srefused connect from \S+ \(<HOST>\)\s*$
            ^%(__prefix_line)sAddress <HOST> .* POSSIBLE BREAK-IN ATTEMPT!*\s*$
            ^%(__prefix_line)sUser \S+ from <HOST> not allowed because none of user\'s groups are listed in AllowGroups$

ignoreregex =
' > /etc/fail2ban/filter.d/sshd.conf



echo $'
[Definition]
failregex = Blocking traffic from <HOST>
' > /etc/fail2ban/filter.d/opensips.conf




systemctl restart fail2ban 

iptables -L -v
clear



cd /usr/src
wget https://www.kamailio.org/pub/mirrors/rtpproxy/rtpproxy-1.2.1.tar.gz
tar xzvf rtpproxy-1.2.1.tar.gz
cd rtpproxy-1.2.1
./configure
make
make install

groupadd rtpproxy
useradd -d /var/run/rtpproxy -s /bin/true -g rtpproxy rtpproxy
mkdir /var/log/rtpproxy
chown -R rtpproxy.rtpproxy /var/log/rtpproxy
chown -R rtpproxy.rtpproxy /var/run/rtpproxy

echo $'#!/bin/bash
#
# Startup script for rtpproxy
#
# chkconfig: 345 85 15
# description: A symmetric RTP proxy
#
# processname: rtpproxy
# pidfile: /var/run/rtpproxy.pid

# Source function library.
. /etc/rc.d/init.d/functions


prog=rtpproxy
rtpproxy=/usr/local/bin/$prog
user=rtpproxy
USELOG=0
lockfile=/var/lock/subsys/$prog
pidfile=/var/run/$prog.pid

RETVAL=0
OPTIONS=" -l 192.168.0.166 -s udp:127.0.0.1:7890 -u $user"

start() {
        echo -n $"Starting $prog: "
        if [ "${USELOG}" = "0" ]; then
          daemon $rtpproxy $OPTIONS
        else
          daemon $rtpproxy $OPTIONS -F -f -d DBUG 2&> /dev/null
        fi

        RETVAL=$?
        echo
        [ $RETVAL = 0 ] && touch /var/lock/subsys/rtpproxy
        return $RETVAL
}

stop() {
  echo -n $"Stopping $prog: "
  killproc $rtpproxy
  RETVAL=$?
  echo
  [ $RETVAL = 0 ] && rm -f /var/lock/subsys/rtpproxy /var/run/rtpproxy.pid
}

reload() {
  echo -n $"Reloading $prog: "
  killproc $rtpproxy -HUP
  RETVAL=$?
  echo
}

# See how we were called.
case "$1" in
  start)
  start
  ;;
  stop)
  stop
  ;;
  status)
        status $rtpproxy
  RETVAL=$?
  ;;
  restart)
  stop
  start
  ;;
  condrestart)
  if [ -f /var/run/rtpproxy.pid ] ; then
    stop
    start
  fi
  ;;
  *)
  echo $"Usage: $prog {start|stop|restart|condrestart|status|help}"
  exit 1
esac

exit $RETVAL
' > /etc/rc.d/init.d/rtpproxy

chmod +x /etc/rc.d/init.d/rtpproxy
systemctl daemon-reload
systemctl start rtpproxy
chkconfig rtpproxy on

chmod -R 7777 /tmp

echo 500000 > /proc/sys/fs/file-max
echo "fs.file-max=500000">>/etc/sysctl.conf

echo '
* soft nofile 500000
* hard nofile 500000
* soft core unlimited
* hard core unlimited
* soft data unlimited
* hard data unlimited
* soft fsize unlimited
* hard fsize unlimited
* soft memlock unlimited
* hard memlock unlimited
* soft cpu unlimited
* hard cpu unlimited
* soft nproc unlimited
* hard nproc unlimited
* soft locks unlimited
* hard locks unlimited
* soft sigpending unlimited
* hard sigpending unlimited' >> /etc/security/limits.conf


ulimit -c unlimited # The maximum size of core files created.
ulimit -d unlimited # The maximum size of a process's data segment.
ulimit -f unlimited # The maximum size of files created by the shell (default option)
ulimit -i unlimited # The maximum number of pending signals
ulimit -n 999999    # The maximum number of open file descriptors.
ulimit -q unlimited # The maximum POSIX message queue size
ulimit -u unlimited # The maximum number of processes available to a single user.
ulimit -v unlimited # The maximum amount of virtual memory available to the process.
ulimit -x unlimited # ???
ulimit -s 240         # The maximum stack size
ulimit -l unlimited # The maximum size that may be locked into memory.
ulimit -a           # All current limits are reported.

yum install -y monit
password=$(awk '{print $1}' /root/passwordMysql.log)

echo "
set daemon  60
set log syslog
set httpd port 2813 and
  allow 0.0.0.0/0.0.0.0
    allow admin:$password

include /etc/monit.d/*
" > /etc/monitrc


echo "check process opensips with pidfile /var/run/opensips.pid 
  start program = \"/usr/sbin/opensipsctl start\"
  stop program = \"/usr/sbin/opensipsctl stop\"
  if 6 restarts within 6 cycles then timeout
  if cpu > 90% for 5 cycles then restart
  " > /etc/monit.d/opensips

echo "check process mariadb with pidfile /var/run/mariadb/mariadb.pid
    start program = \"/bin/systemctl start mariadb\"
    stop program = \"/bin/systemctl stop mariadb\"
" > /etc/monit.d/mariadb

firewall-cmd --zone=public --add-port=2813/tcp --permanent
firewall-cmd --reload

systemctl restart monit
systemctl enable monit


sed -i -e 's/STARTOPTIONS/STARTOPTIONS -m 2048/g' /usr/sbin/opensipsctl


echo 'SystemMaxUse=10M' >> /etc/systemd/journald.conf

systemctl restart systemd-journald


clear
echo ""
echo ""
echo "----------------GO TO TO YOUR MAGNUSBILLING PANEL, MENU SERVERS AND ADD A NEW SERVER WITH THESE DATA--------------------"
echo ""
echo "Host=$proxyip"
echo "Username=proxy_magnus"
echo "Password=$password2"
echo "Port=3306"
echo "Type=SipProxy"
echo "Status=Active"
echo ""
echo "Is necessary execute this script in your MagnusBilling server [php /var/www/html/mbilling/cron.php sipproxyaccounts] you can add it on Crontab"



history -c