<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| URL Base 
|--------------------------------------------------------------------------
|
| This is the base url to the asset directories. We use this to build 
| the FQDN urls that get called in the css / js tags.
|
*/

$config['combine']['js_base_url'] = base_url() . 'assets/javascript/';
$config['combine']['css_base_url'] = base_url() . 'assets/css/';
$config['combine']['cache_base_url'] = base_url() . 'cache/';


/*
|--------------------------------------------------------------------------
| Script & Style Directory
|--------------------------------------------------------------------------
|
| Path to the script & style directory.  Relative to the CI front controller.
|
*/

$config['combine']['script_dir'] = 'assets/javascript/';
$config['combine']['style_dir'] = 'assets/css/';


/*
|--------------------------------------------------------------------------
| Cache Directory
|--------------------------------------------------------------------------
|
| Path to the cache directory. Must be writable. Relative to the CI 
| front controller.
|
*/

$config['combine']['cache_dir'] = 'cache/';


/*
|--------------------------------------------------------------------------
| Rackspace Cloud Files
|--------------------------------------------------------------------------
|
| If you setup the configs below your assets will be uploaded to Rackspace 
| CloudFiles. A css / js tag with a direct url to these files will be returned
| when in production mode. (please include a trailing slash on rs_url)
|
*/

$config['combine']['rs_container'] = '';
$config['combine']['rs_url'] = '';


/*
|--------------------------------------------------------------------------
| Cloud Storage Folders.
|--------------------------------------------------------------------------
|
| Sometimes you might have a folder you want to upload to cloud storage.
| As part of our automated process we want to be able to upload these 
| folders for you. An example would be an images folder. Your combined
| CSS is good to put in cloud storage but uploading the images that 
| make up your website is even better. 
|
| Below is an array of folder paths. This system will loop through the 
| different files in these folders and make sure they are uploaded
| to your cloud storage. So in the case of Racksapce we would create a 
| "folder" at rackspace named the same thing. http://cdnurl.com/foldername/file1.jpg.
|
| We do not delete files on rackspace but we will update or add. The reason we do not 
| delete is this is the web. If you upload one file someone could link to it. If you
| delete it you just broke thier link. If you update or add a file to the folders
| the file will make its way to your cloud storage provider.
|
*/

//$config['combine']['folders'][] = array('name' => 'images', 'path' => './assets/css/images');
//$config['combine']['folders'][] = array('name' => 'fonts', 'path' => './assets/css/fonts');


/* End File */