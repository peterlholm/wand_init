#
# Makefile for danwand configuration
#
# 220402	PLH	First version with config mode
#

CONFDIR=conf
#HOME=/home/danwand
BACKUPDIR=$(HOME)/backupconf
WEBSITEDIR=/var/www/danwand

default:
	@echo "install the wand basis services"
	@echo "make install\tinstall sw and set default config"
	#@echo "make raspbian-config\tConfigure raspbian for danwand and stop unused service"
	#@echo "make help\tDisplay alternative options"

help :
	@echo "make hostname\tset new wand hostname"	
	@echo 

	# @echo "Use the following commands:\n"
	# @echo "make install\tinstall all required basis sw"
	# @echo "make debug\tdebug users and console"
	# @echo "make console\tConfigure console for hdmi and keyboard for DK"
	# @echo "make ipv6-disable\t,Disable ipv6"
	# @echo "make website\tinstall website"
	# @echo "make camera-util\tInstall camera dt-blop"
	# @echo "make get-sw\tGet other sw to $HOME"
	# @echo "--"
	# @echo "make service\tinstall register service"
	# @echo "make python\tinstall Phython requirements"
	# @echo "make debugtools\tinstall debug sw"



# common

danwand-config-file:	/home/danwand
	@echo "create configuration files"
	test -f /etc/danwand.conf || touch /etc/danwand.conf
	chown danwand /etc/danwand.conf
	chmod a+rw /etc/danwand.conf

# set hostname to wand

hostname:
	@echo "Setting hostname to wand"
	hostnamectl set-hostname wand
	sed -i /etc/hosts -e '/127.0.1.1/s/127.0.1.1\t.*/127.0.1.1\twand/'
	@echo hostname changed after reboot
	#raspi-config nonint do_hostname wand

# standard services

danwand-services:	danwand-config-file
	@echo Installing danWand Services
	cp -r ./config_files/systemd/* /etc/systemd/system
	cp -r ./bin/local/* /usr/local/bin/
	systemctl enable danwand.service



#config web site

apache-config:
	cp ./config_files/apache/020_www-data /etc/sudoers.d/
	cp ./config_files/apache/passwords /etc/apache2/
	cp ./config_files/apache/groups /etc/apache2/
	a2dissite 000-default
	systemctl restart apache2

#website:	danwand-lib apache-config
website:	apache-config
	@echo "Installing config site"
	rm -fr /var/www/config
	cp -r ./config_site /var/www/config
	chgrp -R www-data /var/www/config
	cp ./config_files/apache/config.conf /etc/apache2/sites-available
	a2enmod authz_groupfile
	a2ensite config.conf
	systemctl reload apache2
	touch /var/log/apache2/config.err.log /var/log/apache2/config.log
	chmod o+r /var/log/apache2/config.err.log /var/log/apache2/config.log


raspi-boot-config:
	@echo "configure with raspi-config"
	raspi-config nonint do_legacy 0

# standard linux services

hostapd:
	@echo "Installing hotspot"
	rfkill unblock wlan
	apt install hostapd
	systemctl stop hostapd
	cp ./config_files/etc/hostapd.conf /etc/hostapd/hostapd.conf
	systemctl unmask hostapd
	systemctl disable hostapd

dnsmasq:
	@echo "Installing dnsmasq"
	apt -y install dnsmasq
	systemctl stop dnsmasq
	systemctl unmask dnsmasq
	systemctl disable dnsmasq
	cp ./config_files/etc/dnsmasq.conf /etc/dnsmasq.d/danwand.conf
	cp ./config_files/etc/hostapd.conf /etc/hostapd/hostapd.conf

apache:
	@echo "Installing Apache Webserver"
	apt -y install apache2 php libapache2-mod-php
	sed -i /etc/apache2/mods-available/mpm_prefork.conf -e "/[StartServers|MinSpareServers]/s/5/3/"
	# allow apache to use camera and exec sudo
	usermod -aG video www-data
	usermod -aG sudo www-data

system-access:
	mkdir -p /home/pi/.ssh
	cp ./config_files/user/authorized_keys.danwand /home/pi/.ssh/authorized_keys
	cp ./config_files/user/authorized_keys.danwand /etc/ssh/ssh_known_hosts

/var/lib/danwand/install-system: raspbian-config std-sw raspi-boot-config hostapd dnsmasq apache console system-access
	@echo standard systemfiles Installed
	mkdir -p /var/lib/danwand
	touch /var/lib/danwand/install-system

install-system:	/var/lib/danwand/install-system user-peter
	@echo System files Installed

# configuration of services

# apache-config:
# 	cp ./config_files/apache/020_www-data /etc/sudoers.d/
# 	cp ./config_files/apache/passwords /etc/apache2/
# 	cp ./config_files/apache/groups /etc/apache2/
# 	#test -f /etc/sudoers.d/020_www-data || echo "www-data ALL=(ALL) NOPASSWD: ALL" >/etc/sudoers.d/020_www-data
# 	#systemctl stop apache2
# 	a2dissite 000-default
# 	systemctl restart apache2

	

danwand-lib:  /home/danwand
	mkdir -p /var/lib/danwand 
	chown danwand:www-data /var/lib/danwand
	chmod ug+rw /var/lib/danwand

/home/danwand:
	@echo generating danwand user
	id danwand ||  useradd -m -u 600 -c "DanWand user" -G sudo -s /bin/bash danwand 
	test -f /etc/sudoers.d/020_danwand || echo "danwand ALL=(ALL) NOPASSWD: ALL" >/etc/sudoers.d/020_danwand
	sudo usermod -a -G gpio,video danwand
	sudo mkdir -p -m 700 /home/danwand/.ssh
	sudo cp ./config_files/user/authorized_keys.danwand /home/danwand/.ssh
	sudo chown -R danwand:danwand /home/danwand/.ssh
	sudo echo "danwand:   " | chpasswd -e

danwand-basis:	danwand-config-file /home/danwand


#
configmode:	danwand-config-file danwand-services python-req
	@echo "Installing Configmode files"
	apt install avahi-utils
	cp ./config_files/etc/dw_dhcpcd.conf /etc
	cp ./config_files/etc/avahi-danwand.service /etc/avahi/services
	cp ./config_files/etc/avahi.hosts /etc/avahi/hosts
	#systemctl disable --now avahi-alias@wand.local.service
	systemctl enable dw_init.service
	systemctl enable danwand.service
	systemctl restart  dw_init.service danwand.service

./bin/local/man/danwand.conf.5:	./config_files/man/danwand.conf.5.md
	pandoc ./config_files/man/danwand.conf.5.md -s -t man -o ./bin/local/man/danwand.conf.5

man:	./bin/local/man/danwand.conf.5	
	@echo man page generated
# normal mode

normalmode:
	systemctl enable danwand.service

# untestet

get-sw:
	@echo "Getting other sw from github"
	git clone https://github.com/peterlholm/dw $$HOME/dw
	git clone https://github.com/peterlholm/danbots-webserv $$HOME/webserv
	
# install: install-system camera-util website configmode python-req danwand-services debug
# 	@echo "All SW Installed"

#install: website 
install: 	danwand-services
	@echo "All SW Installed"
