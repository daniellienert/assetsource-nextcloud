# Nextcloud Asset Source for Neos

[![Latest Stable Version](https://poser.pugx.org/dl/assetsource-nextcloud/v/stable)](https://packagist.org/packages/dl/assetsource-nextcloud) [![Total Downloads](https://poser.pugx.org/dl/assetsource-nextcloud/downloads)](https://packagist.org/packages/dl/assetsource-nextcloud) [![License](https://poser.pugx.org/dl/assetsource-nextcloud/license)](https://packagist.org/packages/dl/assetsource-nextcloud)

![Nextcloud](Resources/Public/Nextcloud_Logo.svg)

This package provides a direct access from the Neos media module to assets stored in your [Nextcloud](https://nextcloud.com/).

![Nextcloud](Resources/Public/AssetSource_Screenshot.png)

## Installation
Install the package via composer 

`composer require dl/assetsource-nextcloud`

## Configuration
I recommend to add a new user to your Nextcloud to be used only by this asset source and share everything that should be accessible from within Neos with this user.

1. Configure the server and user credentials.
2. Enable the preview generation capabilities according to your Nextcloud settings.

## Limitations

The Nextcloud / WebDav API has some limitations which needed some workaround to make it suitable as Neos asset source.

- No possibility to get the amount of files available / the files included in a search result. Therefore a maximum of files to retrieve needs to be set in settings, which defaults to 200:

	`assetResultLimit: 200`

## Development Resources

* Webdav Search: [https://docs.nextcloud.com/server/15/developer_manual/client_apis/WebDAV/search.html]()
* OCS Api: https://docs.nextcloud.com/server/15/developer_manual/client_apis/OCS/index.html
* Nextcloud Gallery API: https://github.com/nextcloud/gallery/wiki/RESTful-API
