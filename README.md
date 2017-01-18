Translatable Routing
=================
[![Build Status](https://travis-ci.org/webbuilders-group/silverstripe-translatablerouting.png)](https://travis-ci.org/webbuilders-group/silverstripe-translatablerouting)  ![helpfulrobot](https://helpfulrobot.io/webbuilders-group/silverstripe-translatablerouting/badge)

Extends SilverStripe Translatable module and replaces routing to enable multi-lingual urls

## Maintainer Contact
* Ed Chipman ([UndefinedOffset](https://github.com/UndefinedOffset))

## Requirements
* SilverStripe CMS 3.2.x+
* [SilverStripe Translatable](https://github.com/silverstripe/silverstripe-translatable/) 2.1+


## Installation
__Composer (recommended):__
```
composer require webbuilders-group/silverstripe-translatablerouting
```

If you prefer you may also install manually:
* Download the module from here https://github.com/webbuilders-group/silverstripe-translatablerouting/archive/master.zip
* Extract the downloaded archive into your site root so that the destination folder is called translatablerouting, opening the extracted folder should contain _config.php in the root along with other files/folders
* Run dev/build?flush=all to regenerate the manifest

After installing you must make some modifications to your Page class, [see here](docs/en/usage.md) for more information.


## Documentation
For full usage and configuration documentation see the [docs folder](docs/en/).


## Notes
Translatable Routing has support for the SilverStripe Google Sitemaps module for 3.1, which will add support for the multi-lingual site per [google's documentation](https://support.google.com/webmasters/answer/2620865?hl=en) on doing this.
