<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Twig
{
	private $CI;
	private $_twig;
	private $_template_dir;
	private $_cache_dir;
        private $_globals = array();
        private $_data = array();
        private $_config = array();

	/**
	 * Constructor
	 *
	 */
	function __construct($debug = false)
	{
		$this->CI =& get_instance();
		$this->CI->config->load('twig');

		ini_set('include_path',
		ini_get('include_path') . PATH_SEPARATOR . APPPATH . 'libraries/Twig');
		require_once (string) "Autoloader" . EXT;

		log_message('debug', "Twig Autoloader Loaded");

		Twig_Autoloader::register();

		$this->_template_dir = $this->CI->config->item('template_dir');
		$this->_cache_dir = $this->CI->config->item('cache_dir');

		$loader = new Twig_Loader_Filesystem($this->_template_dir);

		$this->_twig = new Twig_Environment($loader, array(
                'cache' => $this->_cache_dir,
                'debug' => $debug,
		));
                $this->_config['title_separator'] = ' | ';
	        foreach(get_defined_functions() as $functions) {
            		foreach($functions as $function) {
                		$this->_twig->addFunction($function, new Twig_Function_Function($function));
            		}
        	}
	}

	public function add_function($name) 
	{
		$this->_twig->addFunction($name, new Twig_Function_Function($name));
	}
        
        public function add_functions($names) 
	{
            foreach ($names as $name)
                $this->_twig->addFunction($name, new Twig_Function_Function($name));
	}

	public function render($template, $data = array()) 
	{
		$template = $this->_twig->loadTemplate($template);
		return $template->render($data);
	}

	public function display($template, $data = array()) 
	{
                if ($template != 'sitemap')
                    $template = $this->_twig->loadTemplate($template.'.html.twig');
                else
                    $template = $this->_twig->loadTemplate($template.'.xml');
		/* elapsed_time and memory_usage */
		$data['elapsed_time'] = $this->CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end');
		$memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2) . 'MB';
		$data['memory_usage'] = $memory;
		$template->display($data);
	}
        public function title()
	{
		if(func_num_args() > 0)
		{
			$args = func_get_args();

			// If at least one parameter is passed in to this method, 
			// call append() to either set the title or append additional
			// string data to it.
			call_user_func_array(array($this, 'append'), $args);
		}

		return $this;
	}
        public function append()
	{
		$args = func_get_args();
		$title = implode($this->_config['title_separator'], $args);

		if(empty($this->_globals['title']))
		{
			$this->set('title', $title, TRUE);
		}
		else
		{
			$this->set('title', $this->_globals['title'] . $this->_config['title_separator'] . $title, TRUE);
		}

		return $this;
	}
        public function set($key, $value, $global = FALSE)
	{
		if(is_array($key))
		{
			foreach($key as $k => $v) $this->set($k, $v, $global);
		}
		else
		{
			if($global)
			{
				$this->_twig->addGlobal($key, $value);
				$this->_globals[$key] = $value;
			}
			else
			{
			 	$this->_data[$key] = $value;
			}	
		}

		return $this;
	}
}