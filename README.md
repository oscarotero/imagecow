# Imagecow

[![Build Status](https://travis-ci.org/oscarotero/imagecow.svg?branch=master)](https://travis-ci.org/oscarotero/imagecow)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/oscarotero/imagecow/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/oscarotero/imagecow/?branch=master)

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>


## What is Imagecow?

It's a php library to manipulate images to web.

* Written in PHP 5.3
* Use GD2 or Imagick libraries (and can be extended with more)
* Has an optional client-side javascript to generate responsive images
* Very simple, fast and easy to use. There is not a lot of features, just the basics: crop, resize, resizeCrop, etc.
* Use the PSR-4 autoloader standard

Simple usage example:

```php
use Imagecow\Image;

Image::create('my-image.gif')
    ->autoRotate()
    ->resizeCrop(300, 400, 'center', 'middle')
    ->format('png')
    ->save('converted-image.png')
    ->show();
```

## How use it?

Use the static function Imagecow\Image::create() to load an image and returns an imageCow instance. This function has two arguments:

* image: The image file path or a binary string with the image data
* library: The library used (Gd or Imagick). If it's not provided, it's detected automatically (in order of preference: `Image::LIB_IMAGICK`, `Image::LIB_GD`)

```php
use Imagecow\Image;

//Create an Imagick instance of "my-image.jpg" file:
$image = Image::create('my-image.jpg', Image::LIB_IMAGICK);

//Create an instance detecting the library automatically
$image = Image::create('my-image.jpg');

//Create an instance from a binary file
$data = file_get_contents('my-image.jpg');

$image = Image::create($data);

//You can use also the direct functions:
$image = Image::createFromString($data);
$image = Image::createFromFile($file);
```

### Resize

`Image::resize($width, $height = 0, $enlarge = false, $cover = false)`

Resizes the image keeping the aspect ratio.

* `$width`: The new max-width of the image. You can use percentages or numbers (pixels). If it's `0`, it will be calculated automatically using the height
* `$height`: The new max-height of the image. As width, you can use percentages or numbers and it will be calculated automatically if it's `0`
* `$enlarge`: By default is `false`. This means that the image won't be scaled if the new value is bigger.
* `$cover`: If it's `true`, the new dimensions will cover both width and height values. It's like css's `image-size: cover`.

```php
//Assuming the original image is 1000x500

$image->resize(200);                    // change to 200x100
$image->resize(0, 200);                 // change to 400x200
$image->resize(200, 300);               // change to 200x100
$image->resize(2000, 2000);             // keeps 1000x500
$image->resize(2000, 2000, true);       // enlarge to 2000x1000
$image->resize(2000, 2000, true, true); // enlarge to 4000x2000
```

### Crop

`Image::crop($width, $height = 0, $x = 'center', $y = 'middle')`

Crops the image:

* `$width`: The width of the cropped image. It can be number (pixels) or percentage
* `$height`: The height of the cropped image. It can be number (pixels) or percentage
* `$x`: The horizontal offset of the crop. It can be a number (for pixels) or percentage. You can also use the keywords `left`, `center` and `right`. If it's not defined, used the value by default (`center`).
* `$y`: The vertical offset of the crop. As with $x, it can be a number or percentage. You can also use the keywords `top`, `middle` and `bottom`. If it's not defined, used the value by default (`middle`).

```php
$image->crop(200, 300);                 // crops to 200x300px
$image->crop(200, 300, 'left', 'top');  // crops to 200x300px from left and top
$image->crop(200, 300, 20, '50%');      // crops to 200x300px from 20px left and 50% top
$image->crop('50%', '50%');             // crops to half size
```

#### Automatic cropping

Stylecow includes some code copied from the great library [stojg/crop](https://github.com/stojg/crop) to calculate the most important parts of the image to crop and resizeCrop automatically. The available methods are:

* `Image::CROP_ENTROPY` [more info](https://github.com/stojg/crop#cropentropy)
* `Image::CROP_BALANCED` [more info](https://github.com/stojg/crop#cropbalanced)

Note: **these methods are available only for Imagick**. If you use Gd, the methods fallback to "center", "middle" positions.

To use them:

```php
$image->crop(500, 200, Image::CROP_ENTROPY);  // crops to 500x200 using the Entropy method to calculate the center point
$image->crop(500, 200, Image::CROP_BALANCED); // The same as above but using the Balanced method
```

### ResizeCrop

`Image::resizeCrop($width, $height = 0, $x = 'center', $y = 'middle', $enlarge = false)`

Resizes and crops the image. See [resize](resize) and [crop](crop) for the arguments description.

```php
$image->resizeCrop(200, 300);                  //Resizes and crops to 200x300px.
$image->resizeCrop('50%', 300);                //Resizes and crops to half width and 300px height
$image->resizeCrop(200, 300, 'left', '100%'); //Resizes and crops to 200x300px from left and bottom
```

### Rotate

`Image::rotate($angle)`

Rotates the image

* `$angle`: Rotation angle in degrees (anticlockwise)

```php
$image->rotate(90); // rotates the image 90 degrees
```

### AutoRotate

`Image::autoRotate()`

Autorotates the image according its EXIF data

```php
$image->autoRotate();
```

### Format

`Image::format($format)`

Converts the image to other format.

* `$format`: The format name. It can be "jpg", "png" or "gif".

```php
$image->format('png'); // converts to png
```

### Save

Save the image to a file.

* `$filename`: The filename for the saved image. If it's not defined, overwrite the file (only if has been loaded from a file).

```php
$image->save('my-new-image.png'); // save to this file
$image->save(); // overwrite file
```

### SetBackground

`Image::setBackground(array $background)`

Set a default background used in some transformations: for example on convert a transparent png to jpg.

* `$background`: An array with the RGB value of the color

```php
$image->setBackground(array(255, 255, 255)); // set the background to white
```

### SetCompressionQuality

`Image::setCompressionQuality($quality)`

Defines the image compression quality for jpg images

* `$quality`: An integer value between 0 and 100

```php
$image->setCompressionQuality(80); // change the quality to 80
```

### Show

Send the HTTP header with the content-type, output the image data and die.

```php
$image->show(); // you should see this image in your browser
```

### Get image info:

There are other functions to returns image info:

* `$image->getWidth()`: Returns the image width in pixels
* `$image->getHeight()`: Returns the image height in pixels
* `$image->getMimeType()`: Returns the image mime-type
* `$image->getExifData()`: Returns the EXIF data of the image
* `$image->getString()`: Returns a string with the image content


#### Execute multiple functions

You can execute some of these functions defined as a string. This is useful to get images transformed dinamically using variables, for example: `image.php?transform=resize,200,300|format,png`. All operations are separated by `|` and use commas for the arguments:

```php
$image->transform('resize,200,300|format,png');

//This is the same than:
$image->resize(200, 300)->format('png');
```

### Responsive images

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

Image::create($_GET['img'])->transform($operations)->show();
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


### Other utils

#### IconExtractor.

**Only for Imagick**. Class to extract the images from an .ico file and convert to png.

```php
use Imagecow\Utils\IconExtractor;

$icon = new IconExtractor('favicon.ico');

//Gets the better image from the icon (quality = color_depth + (width * height))
$image = $icon->getBetterQuality();

//Do imagecow stuff
$image->resize(100)->save('my-image.png');
```

#### SvgExtractor.

**Only for Imagick** This class allows generate images from a svg file (usefull for browsers that don't support svg format):

```php
use Imagecow\Utils\SvgExtractor;

$svg = new SvgExtractor('image.svg');

//Gets the image
$image = $svg->get();

//Now you can execute the imagecow methods:
$image->resize(200)->format('jpg')->save('image.jpg');
```


### Maintainers:

* @oscarotero (creator)
* @eusonlito (contributor)
* @AndreasHeiberg (contributor)
* @kevbaldwyn (contributor)

### Thanks to

Stig Lindqvist and Julien Deniau jdeniau for the [stojg/crop library](https://github.com/stojg/crop)
