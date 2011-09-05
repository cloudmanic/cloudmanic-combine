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

class Cloudmixer
{
	private $_CI;
	private $_config;
	private $_js = array();
	private $_css = array();
	private $_production = TRUE;
	private $_js_last_modified = 0;
	private $_css_last_modified = 0;
	
	//
	// Constructor ….
	//
	function __construct()
	{
		$this->_CI =& get_instance();
		log_message('debug', 'Cloudmixer Library initialized.');
		
		// Load the configs 
		if($this->_CI->config->load('cloudmixer', TRUE, TRUE))
		{
			log_message('debug', 'Cloudmixer config loaded from config file.');
			
			$this->_config = $this->_CI->config->item('cloudmixer');
		}
		
		// Figure out what mode we are in. If in development we don't do
		// much heavly lifting.
		if(ENVIRONMENT == 'development')
		{
			$this->_production = FALSE;
		}
		
					$this->_production = TRUE;
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
	function build()
	{
		$build = $this->_build_css();		
		$build .= $this->_build_js();
		return $build;
	}
	
	// -------------- Private Helper Functions ------------------- //
	
	//
	// Build and return the css link tags to be displayed. If in 
	// production we make or grab the combined file.
	//
	private function _build_css()
	{
		$css = '';
		
		foreach($this->_css AS $key => $row)
		{
			// Production or not...
			if(! $this->_production)
			{
				$css .= $this->_tag('css', $this->_config['css_base_url'] . $row);
			} else 
			{
				$this->_js_last_modified = max($this->_js_last_modified, 
																		filemtime(realpath($this->_config['style_dir'] . $row)));
			
				$css .= $this->_minify('css', $this->_config['style_dir'] . $row);
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
		
		foreach($this->_js AS $key => $row)
		{
			// Production or not...
			if(! $this->_production)
			{
				$js .= $this->_tag('js', $this->_config['js_base_url'] . $row);
			} else 
			{
				$this->_css_last_modified = max($this->_css_last_modified, 
																		filemtime(realpath($this->_config['script_dir'] . $row)));
																				
				$js .= $this->_minify('js', $this->_config['script_dir'] . $row);
			}
		}
		
		return $js;
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
}