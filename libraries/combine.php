<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//
// By: Spicer Matthews
// Company: Cloudmanic Labs, LLC
// Modified From: Carabiner // Asset Management Library
//
// Description: This library provides a way to maintain 
//							javascript and CSS files for development 
//							and production. It uses the CI 2.0 defined('ENVIRONMENT')
//							to determine if we are in production or development mode.
//							When in development modes the files are just served normally.
//							When in production the files are minimized and conbined into
//							one js file and one css file. We also have the option 
//							to push the file to different CDNs (ie. Rackspace cloudfiles).
//							This library only combines and minimizes if the development
//							files change.
//

class Combine
{
	private $_CI;
	private $_config;
	private $_js = array();
	private $_css = array();
	private $_production = TRUE;
	private $_build_file_name = 'last_build.txt';
	private $_build_file;
	private $_last_build_config = array();
	private $_css_last_modified = 0;
	private $_js_last_modified = 0;
	private $_folder_run = FALSE;
	
	//
	// Constructor ….
	//
	function __construct()
	{
		$this->_CI =& get_instance();
		$this->_config = $this->_CI->config->item('combine');
		
		// Figure out what mode we are in. If in development we don't do
		// much heavly lifting.
		if(ENVIRONMENT == 'development')
		{
			$this->_production = FALSE;
		}

		// Make sure the config folder is writeable.
		$this->_check_cache_dir();
		
		// We store a text file with a time stamp of the last modified.
		// When in production we look for any files that have a modify
		// timestamp newer than this value. This is how we know to rebuild.
		$this->_build_file = $this->_config['cache_dir'] . $this->_build_file_name;
		$this->_load_last_build();
					
		log_message('debug', 'Combine Library initialized.');
	}
	
	//
	// Add a Javascript file to the list. We add different javascript files
	// and then when we run the build() function we figure out what to do with 
	// these javascript files. So this function just stores the asset path 
	// into memory.
	//
	function js($path)
	{
		$this->_js[] = $path;
	}
	
	//
	// Add a CSS file to the list. We add different CSS files
	// and then when we run the build() function we figure out what to do with 
	// these CSS files. So this function just stores the asset path 
	// into memory.
	//
	function css($path)
	{
		$this->_css[] = $path;
	}
	
	//
	// This is where all the magic starts. When we call this function 
	// we figure out if we are in development mode or production mode.
	// If in production we see if we have already created a minimized / 
	// combined version of our js & css assets or if the development files 
	// have changed. If they have changed or we have not created the development
	// versions we create them. If we are in development mode we just deliver 
	// the development files with script and link tags.
	//
	function build($type = 'all')
	{
		$build = ''; 
		
		switch($type)
		{
			case 'css':
				$build = $this->_build_css();
			break;

			case 'js':
				$build = $this->_build_js();
			break;
		
			default:
				$build = $this->_build_css();		
				$build .= $this->_build_js();
			break;
		}
		
		return $build;
	}
	
	// -------------- Private Helper Functions ------------------- //
	
	// 
	// Check or error if cache dir is not writeable or present.
	//
	private function _check_cache_dir()
	{
		if(is_dir($this->_config['cache_dir']) && is_writable($this->_config['cache_dir']))
		{
			return 1;
		}

		$msg = 'Cache directory is not present or not writeable. Please create directory and set it with 777 permissions.';
		log_message('debug', $msg);
		die($msg);
	}
	
	//
	// Build and return the css link tags to be displayed. If in 
	// production we make or grab the combined file.
	//
	private function _build_css()
	{
		$css = '';
		$files = array();
		
		foreach($this->_css AS $key => $row)
		{
			// Production or not...
			if(! $this->_production)
			{
				$css .= $this->_tag('css', $this->_config['css_base_url'] . $row);
			} else 
			{
				$files[] = $this->_config['style_dir'] . $row;
				$this->_css_last_modified = max($this->_css_last_modified, 
																		filemtime(realpath($this->_config['style_dir'] . $row)));
			}
		}
		
		// Now if we are in production and we have a modified file we update the 
		// cached css file. 
		if($this->_production)
		{
			if(($this->_css_last_modified > $this->_last_build_config['css_last_build']) ||
					($this->_last_build_config['css_file_count'] != count($files)))
			{
				$mini = '';
				
				foreach($files AS $key => $row)
				{ 
					$mini .= $this->_minify('css', $row);
				}
				
				// Write out new minified file.
				$this->_new_css_last_build = time();
				$name =  md5($this->_new_css_last_build) . '.css';
				$new = $this->_config['cache_dir'] . $name;
				file_put_contents($new, trim($mini));
				
				$this->_write_last_build('css_last_build', $this->_new_css_last_build);
				$this->_write_last_build('css_file_count', count($files));
				
				// See if we should send this to Rackspace Cloud Files.
				if(! $url = $this->_rackspace_upload($new, $name, 'text/css'))
				{
					$url = $this->_config['cache_base_url'] . $name;
				}
				
				// See if we should send this to Amazon S3.
				if(! $url = $this->_amazon_upload($new, $name, 'text/css'))
				{
					$url = $this->_config['cache_base_url'] . $name;
				}
				
				$css = $this->_tag('css', $url);
			} else 
			{
				$cf = md5($this->_last_build_config['css_last_build']) . '.css';
				
				// Check to see if one of the cloud storage providers was set.
				if((! empty($this->_config['rs_container'])) && (! empty($this->_config['rs_url'])))
				{
					$css = $this->_tag('css', $this->_config['rs_url'] . $cf);
				} else if((! empty($this->_config['az_bucket'])) && (! empty($this->_config['az_url'])))
				{ 
					$css = $this->_tag('css', $this->_config['az_url'] . $cf);				
				} else 
				{
					$css = $this->_tag('css', $this->_config['cache_base_url'] . $cf);
				}
			}
		}
		
		return $css;
	}
	
	//
	// Build and return the javascript script tags to be displayed. If in 
	// production we make or grab the combined file.
	//
	private function _build_js()
	{
		$js = '';
		$files = array();
		
		foreach($this->_js AS $key => $row)
		{
			// Production or not...
			if(! $this->_production)
			{
				$js .= $this->_tag('js', $this->_config['js_base_url'] . $row);
			} else 
			{
				$files[] = $this->_config['script_dir'] . $row;
				$this->_js_last_modified = max($this->_js_last_modified, 
																		filemtime(realpath($this->_config['script_dir'] . $row)));
			}
		}
		
		// Now if we are in production and we have a modified file we update the 
		// cached javascript file. 
		if($this->_production)
		{
			if(($this->_js_last_modified > $this->_last_build_config['js_last_build']) ||
					($this->_last_build_config['js_file_count'] != count($files)))
			{
				$mini = '';
				
				foreach($files AS $key => $row)
				{
					$mini .= $this->_minify('js', $row);
				}
				
				// Write out minified file.
				$this->_new_js_last_build = time();
				$name = md5($this->_new_js_last_build) . '.js';
				$new = $this->_config['cache_dir'] . $name;
				file_put_contents($new, trim($mini));
				
				$this->_write_last_build('js_last_build', $this->_new_js_last_build);
				$this->_write_last_build('js_file_count', count($files));
				
				// See if we should send this to Rackspace Cloud Files.
				if(! $url = $this->_rackspace_upload($new, $name, 'application/x-javascript'))
				{
					$url = $this->_config['cache_base_url'] . $name;
				} 

				// See if we should send this to Amazon S3.				
				if(! $url = $this->_amazon_upload($new, $name, 'application/x-javascript'))
				{
					$url = $this->_config['cache_base_url'] . $name;
				}
				
				$js = $this->_tag('js', $url);
			} else 
			{
				$cf = md5($this->_last_build_config['js_last_build'])  . '.js';
				
				// Check to see if one of the cloud storage providers was set.
				if((! empty($this->_config['rs_container'])) && (! empty($this->_config['rs_url'])))
				{
					$js = $this->_tag('js', $this->_config['rs_url'] . $cf);
				} else if((! empty($this->_config['az_bucket'])) && (! empty($this->_config['az_url'])))
				{
					$js = $this->_tag('js', $this->_config['az_url'] . $cf);				
				} else 
				{
					$js = $this->_tag('js', $this->_config['cache_base_url'] . $cf);
				}
			}
		}
		
		return $js;
	}
	
	//
	// Write Log: We pass in a key value pair that is then updated
	// In the last build log file. We keep these configs on hand
	// to safe extra file IO.
	//
	private function _write_last_build($key, $val)
	{		
		$this->_load_last_build();
		$this->_last_build_config[$key] = $val;
		file_put_contents($this->_build_file, json_encode($this->_last_build_config));
		chmod($this->_build_file, 0600);
	}
	
	//
	// Load last build config. Read from file and load
	// an array that is shared throughout.
	//
	private function _load_last_build()
	{
		// Is the file even present?
		if(! is_file($this->_build_file))
		{
			touch($this->_build_file);
		} 
		
		// Get current config and make a php object
		$str = file_get_contents($this->_build_file);
		$this->_last_build_config = json_decode($str, TRUE);
		
		// Set some default values.
		$a = array('js_last_build', 'css_last_build', 'css_file_count', 'js_file_count');
		foreach($a AS $key => $row)
		{
			if(! isset($this->_last_build_config[$row])) 
			{	 
				$this->_last_build_config[$row] = 0; 
			}
		} 
	}
	
	//
	// Minifying Assets. We pass in a type (js or css), and a file path.
	// We then run the file path through the minifers (support libaries).
	// This function returns a string that is a minifed version of the 
	// file we passed in.
	//
	private function _minify($type, $path)
	{	
		switch($type)
		{
			case 'css':
				$this->_CI->load->library('cssmin');
				$contents = file_get_contents($path);
				return $this->_CI->cssmin->minify($contents);
			break;
			
			case 'js':
				$this->_CI->load->library('jsmin');
				$contents = file_get_contents($path);
				return $this->_CI->jsmin->minify($contents);
			break;
		}
		
		return '';
	}
	
	//
	// Build a css or javscript html tag that will be returned and printed
	// to the screen with build().
	//
	private function _tag($type, $url, $media = 'screen')
	{
		switch($type)
		{
			case 'css':
				return '<link type="text/css" rel="stylesheet" href="' . $url . '" media="' . $media . '" />' . "\r\n";
			break;

			case 'js':
				return '<script type="text/javascript" src="' . $url . '"></script>' . "\r\n";
			break;
		}
	}
	
	// ----------- Cloud Storage Providers -------------- //
	
	//
	// If we have enabled racksace cloud files this function 
	// will upload any newly combined files to Rackspace 
	// Cloud Files.
	//
	private function _rackspace_upload($path, $name, $type) 
	{
		if((! empty($this->_config['rs_container'])) && (! empty($this->_config['rs_url'])))
		{
		  $this->_CI->load->spark('cloudmanic-storage/1.0.2');
		  $this->_CI->storage->load_driver('rackspace-cf');
		  $this->_CI->storage->upload_file($this->_config['rs_container'], $path, $name, $type);
		  
		  // Check to see if we need to update any folders.
		  if(! $this->_folder_run)
		  {
				$this->_cloud_folder_sync($this->_config['rs_container']);
		  }
		  		  
		  return $this->_config['rs_url'] . $name;
		} 
		
		return FALSE;
	}

	//
	// If we have enabled Amazon S3 this function 
	// will upload any newly combined files to Amazon.
	//
	private function _amazon_upload($path, $name, $type) 
	{
		if((! empty($this->_config['az_bucket'])) && (! empty($this->_config['az_url'])))
		{
		  $this->_CI->load->spark('cloudmanic-storage/1.0.2');
		  $this->_CI->storage->load_driver('amazon-s3');
		  $this->_CI->storage->upload_file($this->_config['az_bucket'], $path, $name, $type, 'public');
		  
		  // Check to see if we need to update any folders.
		  if(! $this->_folder_run)
		  {
				$this->_cloud_folder_sync($this->_config['az_bucket']);
		  }
		  		  
		  return $this->_config['az_url'] . $name;
		} 
		
		return FALSE;
	}
	
	//
	// Here we check to see if we need to sync up assets that live in
	// folders to our cloud provider. We only check this if CSS or JS files
	// have been changed and re-uploaded to cloudfiles.
	//
	private function _cloud_folder_sync($cont)
	{		  
		if(isset($this->_config['folders']) && is_array($this->_config['folders']))
		{
			foreach($this->_config['folders'] AS $key => $row)
			{
				if(! is_dir($row['path']))
				{
					show_error('Combine - Could not open directory: ' . $row['path']);
				} 
						
				// Make a "hash table" for looking up if the file is already at Rackspace
				$rs = $this->_CI->storage->list_files($cont, $row['name']);
				$rs_to_hash = array();
				$rs_to_name = array();
				foreach($rs AS $key2 => $row2)
				{
					$rs_to_hash[$row2['hash']] = TRUE;
					$rs_to_name[$row2['name']] = TRUE;					
				}
				
				// Loop through the different files and compare the files
				// that are at rackspace. We check md5 hashes.
				$files = glob($row['path'] . '/*');
				foreach($files AS $key2 => $row2)
				{
					// Just files. Maybe some day we will support many layers of folders.
					if(! is_file($row2)) { continue; }
					
					$hash = md5_file($row2);
					$name = basename($row2);
					
					if((! isset($rs_to_hash[$hash])) || (! isset($rs_to_name[$row['name'] . '/' . $name])))
					{
						$this->_CI->storage->upload_file($cont, $row2, $row['name'] . '/' . $name, NULL, 'public');
					}
				}
			}
			
			$this->_folder_run = TRUE;
		}		
	}
}