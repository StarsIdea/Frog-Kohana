<?php defined('SYSPATH') OR die('No direct access allowed.');

class Nav implements ArrayAccess, Countable, Iterator {

	public $items = array();
	public $attrs = array();
	public $active_path_set = FALSE;
	protected $_current_item = 0;
	public static $current_uri;
	public static $default = 'primary';
	protected static $_instances = array();

	public static function current_uri($uri = NULL)
	{
		if (!isset(self::$current_uri))
		{
			self::$current_uri = is_null($uri) ? Request::initial()->uri() : $uri;
		}
	}

	public static function instance($name = NULL, array $items = array())
	{
		self::current_uri();
		if ($name === NULL)
		{
			$name = Nav::$default; // Use the default instance name
		}
		if (!isset(self::$_instances[$name]))
		{
			self::$_instances[$name] = new Nav($items);
		}
		return self::$_instances[$name];
	}

	public static function factory(array $items = array())
	{
		return new Nav($items);
	}

	protected function __construct(array $items = array())
	{
		foreach ($items as $item)
		{
			$title = $item['title'];
			$url = isset($item['url']) ? $item['url'] : NULL;
			$nav = isset($item['nav']) ? $item['nav'] : NULL;
			$this->items[] = new Nav_Item($title, $url, $nav);
		}
	}

	public function add($title, $url, Nav $nav = NULL)
	{
		$this->items[] = new Nav_Item($title, $url, $nav);
		return $this;
	}

	public function set_active_path(array & $items = NULL, & $previous_item = NULL)
	{
		$items = is_null($items) ? $this->items : $items;

		foreach ($items as & $item)
		{
			if (isset($item->nav) AND !empty($item->nav->items))
			{
				$this->set_active_path($item->nav->items, $item);
			}
			// only 1 item can be current
			$item->current AND $previous_item AND $previous_item->current AND $previous_item->current = FALSE;
			$item->current AND $previous_item AND $previous_item->active = TRUE;
			$item->active  AND $previous_item AND $previous_item->active = TRUE;
		}
		return $this;
	}

	public function render(array $attrs = NULL, array $items = NULL)
	{
		static $i;
		// recursive, don't want this running many times
		if (!$this->active_path_set)
		{
			$this->set_active_path();
			$this->active_path_set = TRUE;
		}

		$items = empty($items) ? $this->items : $items;
		$attrs = empty($attrs) ? $this->attrs : $attrs;

		$i++;

		if ($i !== 1)
		{
			$attrs = array();
		}

		$attrs['class'] = empty($attrs['class']) ? 'level-' . $i : $attrs['class'] . ' level-' . $i;

		$menu = '<ul'.HTML::attributes($attrs).'>';

		foreach ($items as $key => $item)
		{
			$has_children = isset($item->nav);
			$classes = NULL;
			$has_children AND $classes[] = 'parent';
			$item->active AND $classes[] = 'active';
			$item->current AND $classes[] = 'current';

			if (!empty($classes))
			{
				$classes = HTML::attributes(array('class' => implode(' ', $classes)));
				//$classes = ' class="' . implode(' ', $classes) . '"';
			}

			$menu .= '<li' . $classes . '>';
			//$menu .= is_null($item->url) ? $item->title : "<a href=\"".Url::base().$item->url."\">$item->title</a>";
			$menu .= is_null($item->url) ? $item->title : HTML::anchor($item->url, $item->title);

			if ($has_children)
			{
				$menu .= $this->render(NULL, $item->nav->items);
			}
			$menu .= '</li>';
		}

		$menu .= '</ul>';

		$i--;

		return $menu;
	}
	
	public function breadcrumbs(array $items = NULL)
	{
		static $i;
		// recursive, don't want this running many times
		if ( ! $this->active_path_set) {
			$this->set_active_path();
			$this->active_path_set = TRUE;
		}

		$items = empty($items) ? $this->items : $items;

		$i++;
		
		$menu = '';
		if($i == 1)
		{
			$menu = '<ul>';
		}

		foreach ($items as $key => $item)
		{
			if($item->active)
			{
				$menu .= '<li>';
				$menu .= (is_null($item->url) OR $item->current)
					? $item->title 
					: HTML::anchor($item->url, $item->title);

				if (isset($item->nav))
				{
					$menu .= $this->breadcrumbs($item->nav->items);
				}
				$menu .= '</li>';
			}
		}

		if($i == 1)
		{
			$menu .= '</ul>';
		}

		$i--;

		return $menu;
	}
	
	public function __toString()
	{
		return $this->render();
	}

	public static function reset()
	{
		self::$_instances = array();
	}

	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->items[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->items[$offset]) ? $this->items[$offset] : NULL;
	}

	public function count()
	{
		return count($this->items);
	}

	public function current()
	{
		return $this->valid() ? $this->items[$this->_current_item] : NULL;
	}

	public function next()
	{
		++$this->_current_item;
		return $this;
	}

	public function key()
	{
		return $this->_current_item;
	}

	public function valid()
	{
		return $this->offsetExists($this->_current_item);
	}

	public function rewind()
	{
		$this->_current_item = 0;
		return $this;
	}

}

class Nav_Item implements Countable {

	public $title;
	public $url;
	public $current = FALSE;
	public $active = FALSE;
	public $nav;

	public function __construct($title, $url, $nav = NULL)
	{
		$this->title = $title;
		$this->url = $url;
		if ($nav instanceof Nav)
		{
			$this->nav = $nav;
		}
		elseif (is_array($nav))
		{
			$this->nav = Nav::factory($nav);
		}
		if ($this->url === Nav::$current_uri
				OR preg_replace('~/?index/?$~', '', Nav::$current_uri) === $this->url
				OR preg_replace('~/?list/?$~', '', Nav::$current_uri) === $this->url
				OR preg_replace('~/?edit/[0-9]+?$~', '', Nav::$current_uri) === $this->url
				OR preg_replace('~/?view/[0-9]+?$~', '', Nav::$current_uri) === $this->url
				OR preg_replace('~/?delete/[0-9]+?$~', '', Nav::$current_uri) === $this->url)
		{
			$this->current = TRUE;
			$this->active = TRUE;
		}
	}

	public function count()
	{
		return count($this->nav);
	}

}