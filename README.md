## Overview

CSS / Javascript Asset Management Library For Codeigniter
Inspired By: http://codeigniter.com/wiki/Carabiner/

The real magic of this library is it gives you the option to upload your assets to Amazon S3 or Rackspace Cloud Files. This library will check your javascript and css files for any changes if there are changes it will combine all your files into one big file (one for css and one for javascript). After your files are combined it will then minify your files. These files will be written to a cached directory. If you do not have Amazon or Rackspace configured the files will remain in that cache directory and a html tag for each will be return to be displayed in your views. If you have Amazon or Rackspace hooked up it will upload your newly combined files to their Cloud Storage solutions. 

This library also allows you to keep assets files that are not js / css uploaded to cloud storage providers. For example we often have an images folder that we would want to copy to our cloud storage providers. We can put all these files in a folder and then use the configuration option $config['combine']['folders'] to tell this library to upload the assets. If the asset changes this library will update it. If there is a new asset this library will upload it. This library will not delete assets at the cloud storage providers. Also folder assets are only synced up when a changed in css or javascript is detected.

One more note: You do not need to use a cloud storage provider to use this library. It is just a nice option.

## Requirements

1. PHP 5.1+
2. CodeIgniter 2.0.0+
3. CURL

## Cloud Storage Providers

- Rackspace Cloudfiles ::: [http://www.rackspace.com/cloud/cloud_hosting_products/files/](http://www.rackspace.com/cloud/cloud_hosting_products/files/)

- Amazon.com S3 ::: [http://aws.amazon.com/s3/](http://aws.amazon.com/s3/)

## Sparks

This library is intended to be installed via [http://getsparks.org/](http://getsparks.org/) the main reason for this is it depends on [cloudmanic-storage](http://getsparks.org/packages/cloudmanic-storage/versions/HEAD/show). We use cloudmanic-storage for uploading the assets to the cloud providers.

Once you have installed this spark be sure to configure the cloudmanic-storage configuration if you want to upload assets to a cloud providers.

## Usage 

```
$this->load->spark('cloudmanic-combine/X.X.X');
		
$this->combine->css('style.css');
$this->combine->css('site.css');

$this->combine->js('site.js');
$this->combine->js('blog.js');

echo $this->combine->build();
```