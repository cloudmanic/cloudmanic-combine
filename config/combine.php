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

/* End File */