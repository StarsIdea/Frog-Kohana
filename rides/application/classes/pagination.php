<?php

class Pagination extends Kohana_Pagination {
	
	public function url($page = 1)
	{
		// Clean the page number
		$page = max(1, (int) $page);

		// No page number in URLs to first page
		if ($page === 1 AND ! $this->config['first_page_in_url'])
		{
			$page = NULL;
		}

		switch ($this->config['current_page']['source'])
		{
			case 'query_string':
				return URL::site(Request::initial()->uri()).URL::query(array($this->config['current_page']['key'] => $page));

			case 'route':
				return URL::site(Request::initial()->uri(array($this->config['current_page']['key'] => $page))).URL::query();
		}

		return '#';
	}
	
}