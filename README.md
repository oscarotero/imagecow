# Imagecow

[![Build Status](https://travis-ci.org/oscarotero/imagecow.svg?branch=master)](https://travis-ci.org/oscarotero/imagecow)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/oscarotero/imagecow/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/oscarotero/imagecow/?branch=master)

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>


## What is Imagecow?

It's a php library to manipulate images to web.

* PHP >= 5.5
* Use GD2 or Imagick libraries
* Very simple, fast and easy to use. There is not a lot of features, just the basics: crop, resize, resizeCrop, etc.

Simple usage example:

```php
use Imagecow\Image;

Image::fromFile('my-image.gif')
    ->autoRotate()
    ->resizeCrop(300, 400, 'center', 'middle')
    ->format('png')
    ->save('converted-image.png')
    ->show();
```


## How use it?

### Installation

This package is installable and autoloadable via Composer as [imagecow/imagecow](https://packagist.org/packages/imagecow/imagecow).

```php
$ composer require imagecow/imagecow
```

### Creating a Imagecow\Image instance:

```php
use Imagecow\Image;

//Using Imagick:
$image = Image::fromFile('my-image.jpg', Image::LIB_IMAGICK);

//Detect the available library automatically
//(in order of preference: Imagick, Gd)
$image = Image::fromFile('my-image.jpg');

//Create an instance from a string
$image = Image::fromString(file_get_contents('my-image.jpg'));
```

### resize

`Image::resize($width, $height = 0, $cover = false)`

Resizes the image keeping the aspect ratio.

**Note:** If the new image is bigger than the original, the image wont be resized

* `$width`: The new max-width of the image. You can use percentages or numbers (pixels). If it's `0`, it will be calculated automatically using the height
* `$height`: The new max-height of the image. As width, you can use percentages or numbers and it will be calculated automatically if it's `0`
* `$cover`: If it's `true`, the new dimensions will cover both width and height values. It's like css's `image-size: cover`.

```php
//Assuming the original image is 1000x500

$image->resize(200);                    // change to 200x100
$image->resize(0, 200);                 // change to 400x200
$image->resize(200, 300);               // change to 200x100
$image->resize(2000, 2000);             // keeps 1000x500
```

### crop

`Image::crop($width, $height, $x = 'center', $y = 'middle')`

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

Imagecow includes some code copied from the great library [stojg/crop](https://github.com/stojg/crop) to calculate the most important parts of the image to crop and resizeCrop automatically. The available methods are:

* `Image::CROP_ENTROPY` [more info](https://github.com/stojg/crop#cropentropy)
* `Image::CROP_BALANCED` [more info](https://github.com/stojg/crop#cropbalanced)

Note: **these methods are available only for Imagick**. If you use Gd, the methods fallback to "center", "middle" positions.

To use them:

```php
$image->crop(500, 200, Image::CROP_ENTROPY);  // crops to 500x200 using the Entropy method to calculate the center point
$image->crop(500, 200, Image::CROP_BALANCED); // The same as above but using the Balanced method
```

### resizeCrop

`Image::resizeCrop($width, $height, $x = 'center', $y = 'middle')`

Resizes and crops the image. See [resize](resize) and [crop](crop) for the arguments description.

```php
$image->resizeCrop(200, 300);                  //Resizes and crops to 200x300px.
$image->resizeCrop('50%', 300);                //Resizes and crops to half width and 300px height
$image->resizeCrop(200, 300, 'left', '100%'); //Resizes and crops to 200x300px from left and bottom
$image->resizeCrop(200, 300, Image::CROP_BALANCED); //Resizes and crops to 200x300px using the CROP_BALANCED method
```

### rotate

`Image::rotate($angle)`

Rotates the image

* `$angle`: Rotation angle in degrees (anticlockwise)

```php
$image->rotate(90); // rotates the image 90 degrees
```

### autoRotate

`Image::autoRotate()`

Autorotates the image according its EXIF data

```php
$image->autoRotate();
```

### opacity

`Image::opacity($value)`

Set the alpha channel of the image. The value must be between 0 (transparent) to 100 (opaque). Note that the image will be converted to png (if it's not already)

```php
$image->opacity(50);
```

### blur

`Image::blur($loops = 4)`

Applies the gaussian blur to the image. The more loops, the more the image blurs.

```php
$image->blur(8);
```

### watermark

`Image::watermark($image, $x = 'right', $y = 'bottom')`

Applies a image as a watermark. You can configure the position and opacity.

```php
$image = Image::fromFile('photo.jpg');
$logo = Image::fromFile('logo.png');

$logo->opacity(50);

$image->watermark($logo);
```

### format

`Image::format($format)`

Converts the image to other format.

* `$format`: The format name. It can be "jpg", "png" or "gif".

```php
$image->format('png'); // converts to png
```

### save

Save the image to a file.

* `$filename`: The filename for the saved image. If it's not defined, overwrite the file (only if has been loaded from a file).

```php
$image->save('my-new-image.png'); // save to this file
$image->save(); // overwrite file
```

### setBackground

`Image::setBackground(array $background)`

Set a default background used in some transformations: for example on convert a transparent png to jpg.

* `$background`: An array with the RGB value of the color

```php
$image->setBackground(array(255, 255, 255)); // set the background to white
```

### quality

`Image::quality($quality)`

Defines the image compression quality for jpg images

* `$quality`: An integer value between 0 and 100

```php
$image->quality(80); // change the quality to 80
```

### setClientHints

`Image::setClientHints(array $clientHints)`

Defines the client hints to fix the final size of the image and generate responsive images. The available client hints are:

* `dpr` Device pixel ratio
* `width` The final image width
* `viewport-width` The viewport width

```php
$image->setClientHints([
    'dpr' => 2,
    'width' => 300,
    'viewport-width' => 1024,
]);
```

More information about [client hints below](#responsive-images).

### Display the image

Send the HTTP header with the content-type, output the image data and die:

```php
$image->show(); // you should see this image in your browser
```

Insert the image as base64 url:

```php
echo '<img src="' . $image->base64() . '">';
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
$image->transform('resize,200,50%|format,png|crop,100,100,CROP_ENTROPY');

//This is the same than:
$image
	->resize(200, '50%')
	->format('png')
	->crop(100, 100, Image::CROP_ENTROPY);
```

### Responsive images

Imagecow has support for client hints, that allows to generate responsive images without using cookies or javascript code (like in 1.x version of imagecow). Client Hints is introduced by Google becoming a standard. [Here's a deep explain of how to use it](https://www.smashingmagazine.com/2016/01/leaner-responsive-images-client-hints/)

Note that currently this is supported only by [chrome and opera browsers](http://caniuse.com/#feat=client-hints-dpr-width-viewport).

Simple example:

In your webpage, add the following code:

```html
<!DOCTYPE html>
<html>
<head>
    <title>My webpage</title>
    <!-- Activate client hints -->
    <meta http-equiv="Accept-CH" content="DPR,Width,Viewport-Width"> 
</head>
<body>
    <!-- Insert a responsive image -->
    <img src="image.php?file=flower.jpg&amp;transform=resize,1000" sizes="25vw">
</body>
</html>
```

Now, in the server side:

```php
use Imagecow\Image;

$file = __DIR__.'/'.$_GET['file'];
$transform = isset($_GET['transform']) ? $_GET['transform'] : null;

//Create the image instance
$image = Image::fromFile($file);

//Set the client hints
$image->setClientHints([
    'dpr' => isset($_SERVER['HTTP_DPR']) ? $_SERVER['HTTP_DPR'] : null,
    'width' => isset($_SERVER['HTTP_WIDTH']) ? $_SERVER['HTTP_WIDTH'] : null,
    'viewport-width' => isset($_SERVER['HTTP_VIEWPORT_WIDTH']) ? $_SERVER['HTTP_VIEWPORT_WIDTH'] : null,
]);

//Transform the image and display the result:
$image->transform($transform)->show();
```

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
* @eusonlito (collaborator)
* [and more...](https://github.com/oscarotero/imagecow/graphs/contributors)

### Thanks to

Stig Lindqvist and Julien Deniau jdeniau for the [stojg/crop library](https://github.com/stojg/crop)
