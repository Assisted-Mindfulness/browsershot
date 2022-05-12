# Browsershot

[![Tests](https://github.com/Assisted-Mindfulness/browsershot/actions/workflows/phpunit.yaml/badge.svg)](https://github.com/Assisted-Mindfulness/browsershot/actions/workflows/phpunit.yaml)

The package can convert a webpage to an image or pdf. The conversion is done behind the screens by Google Chrome. This package has been tested on MacOS and Ubuntu. If you use another OS your mileage may vary. Chrome should be installed on your system.

On a [Forge](https://forge.laravel.com) provisioned Ubuntu server you can install the latest stable version of Chrome like this:

```bash
sudo wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add -
sudo sh -c 'echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'
sudo apt-get update
sudo apt-get -f install
sudo apt-get install google-chrome-stable
```

## Installation

This package can be installed through Composer.

```bash
composer require assisted-mindfulness/browsershot
```

## Usage

In all examples it is assumed that you imported this namespace at the top of your file

```php
use AssistedMindfulness\Browsershot\Browsershot;
```

Here's the easiest way to create an image of a webpage:

```php
Browsershot::url('https://example.com')->save($pathToImage);
```

Browsershot will make an educated guess where Google Chrome is located. If Chrome can not be found on your system you can manually set its location:

```php
Browsershot::url('https://example.com')
   ->setChromePath($pathToChrome)
   ->save($pathToImage);
```

By default the screenshot will be a `png` and it's size will match the resolution you use for your desktop. Want another size of screenshot? No problem!

```php
Browsershot::url('https://example.com')
    ->windowSize(640, 480)
    ->save($pathToImage);
```

You can also set the size of the output image independently of the size of window. Here's how to resize a screenshot take with a resolution of 1920x1080 and scale that down to something that fits inside 200x200.

```php
Browsershot::url('https://example.com')
    ->windowSize(1920, 1080)
    ->fit(Manipulations::FIT_CONTAIN, 200, 200)
    ->save($pathToImage);
```

You can also capture the webpage at higher pixel densities by passing a device scale factor value of 2 or 3. This mimics how the webpage would be displayed on a retina/xhdpi display.

```php
Browsershot::url('https://example.com')
    ->deviceScaleFactor(2)
    ->save($pathToImage);
```

In fact, you can use all the methods [spatie/image](https://docs.spatie.be/image/v1) provides. Here's an example where we create a greyscale image:

```php
Browsershot::url('https://example.com')
    ->windowSize(640, 480)
    ->greyscale()
    ->save($pathToImage);
```

If, for some reason, you want to set the user agent Google Chrome should use when taking the screenshot you can do so:

```php
Browsershot::url('https://example.com')
    ->userAgent('My Special Snowflake Browser 1.0')
    ->save($pathToImage);
```

The default timeout of Browsershot is set to 60 seconds. Of course, you can modify this timeout:

```php
Browsershot::url('https://example.com')
    ->timeout(120)
    ->save($pathToImage);
```

Browsershot will save a pdf if the path passed to the `save` method has a `pdf` extension.

```php
// a pdf will be saved
Browsershot::url('https://example.com')->save('example.pdf');
```

Alternatively you can explicitly use the `savePdf` method:

```php
Browsershot::url('https://example.com')->savePdf('example.pdf');
```

Browsershot also can get the body of an html page after JavaScript has been executed:

```php
Browsershot::url('https://example.com')->bodyHtml(); // returns the html of the body
```


You can also use an arbitrary html input, simply replace the `url` method with `html`:

```php
Browsershot::html('<h1>Hello world!!</h1>')->save('example.pdf');
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
