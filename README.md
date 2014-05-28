Imagecow
========

[![Build Status](https://travis-ci.org/oscarotero/imageCow.svg?branch=master)](https://travis-ci.org/oscarotero/imageCow)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/oscarotero/imageCow/badges/quality-score.png?s=968f74e091c90ce0100cbfdce2ad925eb0b2ab20)](https://scrutinizer-ci.com/g/oscarotero/imageCow/)

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>


What is Imagecow?
-----------------

It's a php library to manipulate images to web.

* Written in PHP 5.3
* Use GD2 or Imagick libraries (and can be extended with more)
* Has an optional client-side javascript to generate responsive images
* Very simple and easy to use. There is not a lot of features, just only the basics: crop, resize, resizeCrop, etc.
* Use the PSR-0 autoloader standard


Notes on 1.x version
--------------------

The API in 1.x version changes a little bit (not much, only on create the instances).


How use it?
-----------

Use the static function Imagecow\Image::create() to load an image and returns an imageCow instance. This function has two arguments:

* image: The image file path or a binary string with the image data
* library: The library used (Gd or Imagick). If it's not provided, it's detected automatically (in order of preference: Imagick, Gd)

```php
use Imagecow\Image;

//Create an Imagick instance of "my-image.jpg" file:
$image = Image::create('my-image.jpg', 'Imagick');

//Create an instance detecting the library automatically
$image = Image::create('my-image.jpg');

//Create an instance from a binary file
$data = file_get_contents('my-image.jpg');

$image = Image::create($data);
```

#### Crop the image

```php
//Arguments: ($width, $height, $x = 'center', $y = 'middle');

$image->crop(200, 300); //Crops the image to 200x300px
$image->crop(200, 300, 'left', 'top'); //Crops the image to 200x300px starting from left-top
$image->crop(200, 300, 20, '50%'); //Crops the image to 200x300px starting from 20px (x) / 50% (y)
$image->crop('50%', '50%'); //Crops the image to half size
```

#### Resize the image

```php
//Arguments: ($width, $height = 0, $enlarge = false)

$image->resize(200, 300); //Resizes the image to max size 200x300px (keeps the aspect ratio. If the image is lower, don't resize it)
$image->resize(800, 600, 1); //Resizes the image to max size 800x600px (keeps the aspect ratio. If the image is lower enlarge it)
$image->resize(800); //Resizes the image to 800px width and calculates the height maintaining the proportion.
```

#### Resize and Crop the image

```php
//Arguments: ($width, $height, $x = 'center', $y = 'middle')

$image->resizeCrop(200, 300); //Resizes and crops the image to this size.
```

#### Rotate

```php
$image->rotate(90); //Rotates the image 90 degrees
$image->autoRotate(); //Rotates the image according its EXIF data.
```

#### Convert the image to other formats:

```php
$image->format('png');
```

#### Save the image to a file

```php
$image->save('my-new-image.png');

//Overwrite the image (only if has been loaded from a file)
$image->save();
```

#### Execute multiple functions (resize, crop, resizeCrop, format)

This is useful to get images transformed dinamically using get variables: image.php?transform=resize,200,300|format,png

```php
$image->transform('resize,200,300|format,png');
```

#### Show the image

```php
$image->show();
```

#### Other functions:

```php
$image->getWidth();
$image->getHeight();
$image->getMimeType();

$image->getString(); //Returns the image in a string

$image->setBackground(array(255, 255, 255)); //Set a default background used in some transformations (for example, convert a transparent png to jpg)
$image->getExifData();
$image->setCompressionQuality(80); //Define the image compression quality for jpg images
```


Responsive images
-----------------

Include the Imagecow.js library in the html page and execute the function Imagecow.init();

```html
<script src="Imagecow.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">
	Imagecow.init();
</script>
```

This function saves a cookie with the client information (width, height, connection speed).
You can configurate the cookie. The default values are:

```javascript
Imagecow.cookie_seconds = 3600*24;
Imagecow.cookie_name = 'Imagecow_detection';
Imagecow.cookie_path = '/';
```

In the server-side, use the cookie to generate the responsive operations:

```php
use Imagecow\Image;

$operations = Image::getResponsiveOperations($_COOKIE['Imagecow_detection'], $_GET['transform']);

$image = Image::create();

$image->load($_GET['img'])->transform($operations)->show();
```

Now you can transform the image according with the client dimmensions. The available options are:

* max-width
* min-width
* max-height
* min-height
* width
* height

You can use the same syntax than transform, but separate the "media-query" with ";".

```
img.php?img=my_picture.png&transform=resizeCrop,800,600;max-width=400:resize,400
```

Get me the image "my_picture.png" with resizeCrop to 800x600. If the max-width of the client side is 400, resize to 400.


Other utils
-----------

IconExtractor. Class to extract the images from an .ico file and convert to png. Only for Imagick:

```php
use Imagecow\Utils\IconExtractor;

$icon = new IconExtractor('favicon.ico');

//Gets the better image from the icon (quality = color_depth + (width * height))
$image = $icon->getBetterQuality();

//Do imagecow stuff
$image->resize(100)->save('my-image.png');
```


Maintainers:
------------

* @oscarotero (creator)
* @eusonlito (contributor)
* @AndreasHeiberg (contributor)
* @kevbaldwyn (contributor)
