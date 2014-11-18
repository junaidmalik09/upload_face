# Face Detection Module

## Introduction
https://prezi.com/1eytx-ie-zv7/lms-face-detection-tool/

## Installation and usage instructions for Linux
### Requirements

- Linux server 
- PHP/Apache
- GD Library
- OpenCV 
- PHP Facedetect extension 

#### First install OpenCV and then compile the PHP facedetect extension with PHP.
##### How to Install OpenCV

`yum install opencv`

OpenCV may also need the following dependencies to work properly and you will need to install them as well.

`yum install ibpng libpng-dev zlib libjpeg libjpeg-dev libtiff libjasper swig python pkgconfig gtk gtk-dev`

For more installation instructions on Linux see http://OpenCV.willowgarage.com/wiki/InstallGuide_Linux

OpenCV will be installed in/usr/local/share/OpenCV and all the important facedetection training files can be found in haarcascades folder in xml format (like haarcascade_frontalface_alt.xml)

##### How to test run OpenCV
In order to test run OpenCV you have to compile the samples folder. Go to

`cd /usr/local/share/OpenCV/samples/c/`

Run this command to compile to binary.

`./build_all.sh`

If you find large number of errors and variables not declared run
export PKG_CONFIG_PATH=/usr/local/lib/pkgconfig

`./build_all.sh`

and then it will compile.

Please see reference no [3] for Fedora specific installation instructions for OpenCV.

### Install PHP Facedetect Extension
Download the package from the provided link and compile it for PHP.

`wget https://www.dropbox.com/s/grzx6c43tszhl5a/PHP-Facedetect-master.tar.gz?m`
`tar zxf PHP-Facedetect-master.tar.gz `
`cd PHP-Facedetect-master`

then

`phpize && configure && make && make install`

If you see PHPize command not found error, install PHP developer libraries

`yum install PHP-devel`

OR

`yum install PHP5-devel`

then

`open PHP.ini` 

and make sure you add this line.

`extension=facedetect.so`

Create a new PHP file on the web server document root named test.php  with the following code:
```
<?PHP
	echo phpinfo();
?>
```

If facedetect has been successful, you will see this enabled in test.php.
