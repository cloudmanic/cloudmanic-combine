CSS / Javascript Asset Management Library For Codeigniter

Inspired By: http://codeigniter.com/wiki/Carabiner/

This library is still a work in progress. Will make a 1.0 branch when ready for production.  

## Usage 

```
$this->load->spark('cloudmanic-combine/X.X.X');
		
$this->combine->css('style.css');
$this->combine->css('site.css');

$this->combine->js('site.js');
$this->combine->js('blog.js');

echo $this->combine->build();
```