Imagecow
========

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>

What is Imagecow?
-----------------

It's a php library to manipulate images to web.

* Written in PHP 5.3
* Use GD2 or Imagick libraries (and can be extended with more)
* Has an optional client-side javascript to generate responsive images
* Very simple and easy to use. There is not a lot of features, just only the basics: crop, resize, resizeCrop, etc.
* Use the PSR-0 autoloader standard


How use it?
-----------

Create an instance of Imagecow\Libs\Gd or Imagecow\Libs\Imagick (it depends of the library you choose):

```php
$GDimage = new Imagecow\Libs\Gd();
```

Or you can also use the static function Imagecow\Image::create() to returns an instance:

```php
use Imagecow\Image;

$MyImagickImage = Image::create('Imagick'); //Returns an instance using the Imagick library

$MyImage = Image::create(); //Detects automatically the library to use (in order of preference: Imagick, GD2)
```

#### Load an image file:

```php
$MyImage->load('picture.jpg');
```

#### Crop the image

```php
$Image->crop(200, 300); //Crops the image to 200x300px
$Image->crop(200, 300, 'left', 'top'); //Crops the image to 200x300px starting from left-top
$Image->crop(200, 300, 20, '50%'); //Crops the image to 200x300px starting from 20px (x) / 50% (y)
$Image->crop(50%, 50%); //Crops the image to half size
```

#### Resize the image

```php
$Image->resize(200, 300); //Resizes the image to max size 200x300px (keeps the aspect ratio. If the image is lower, don't resize it)
$Image->resize(800, 600, 1); //Resizes the image to max size 800x600px (keeps the aspect ratio. If the image is lower enlarge it)
$Image->resize(800); //Resizes the image to 800px width and calculates the height maintaining the proportion.
```

#### Resize and Crop the image

```php
$Image->resizeCrop(200, 300); //Resizes and crops the image to this size.
```

#### Convert the image to other formats:

```php
$Image->format('png');
```

#### Save the image to a file

```php
$image->save('my-new-image.png');
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

$image->getImage(); //Returns the image resource (GD) or Imagik instance
$image->setImage(); //Sets manually a new image resource or Imagik instance

$image->setError('message'); //Sets an error manually
$image->getError(); //Returns an ImageException instance in case of error

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

$Image = Image::create();

$Image->load($_GET['img'])->transform($operations)->show();
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

Maintainers:
------------

* @oscarotero (creator)
* @eusonlito (contributor)
* @AndreasHeiberg (contributor)
