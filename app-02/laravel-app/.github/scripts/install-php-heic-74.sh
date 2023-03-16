#!/bin/bash
start_time="$(date -u +%s)"
go env -w GO111MODULE=off
sed -Ei 's/^# deb-src /deb-src /' /etc/apt/sources.list
apt-get update
apt-get -y install build-essential autoconf libtool git-core
apt-get -y build-dep imagemagick libmagickcore-dev libde265 libheif
cd /usr/src/ 
git clone https://github.com/strukturag/libde265.git  
git clone https://github.com/strukturag/libheif.git 
cd libde265/ 
./autogen.sh 
./configure 
make -j$(nproc)  
make install 
cd /usr/src/libheif/ 
./autogen.sh 
./configure 
make -j$(nproc)  
make install 
cd /usr/src/ 
wget https://imagemagick.org/archive/ImageMagick.tar.gz
tar xf ImageMagick.tar.gz 
cd ImageMagick-7*
./configure --with-heic=yes 
make -j$(nproc)  
make install  
ldconfig
apt-get -y install php-imagick
cd /usr/src/ 
wget http://pecl.php.net/get/imagick-3.5.1.tgz
tar -xvzf imagick-3.5.1.tgz
cd imagick-3.5.1/
apt-get -y install php7.4-dev 
phpize
./configure
make -j$(nproc)
make install
phpenmod imagick
systemctl restart apache2
php -r 'phpinfo();' | grep HEIC
end_time="$(date -u +%s)"
elapsed="$(($end_time-$start_time))"
echo "Total of $elapsed seconds elapsed for this script."