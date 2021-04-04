<?php

class Table_ERA extends Table {

	/**
	 * Row value id's are replaced by a link, so strip out "Id" from titles
	 * @return array
	 */
	protected function _generate_heading()
	{
		// down &#9660;
		// up	&#9650;

		foreach($this->column_titles as $key => $title)
		{
			if($pos = strpos($key, '_id'))
			{
				$title = '('.substr($title, 0, $pos).')';
			}

			$order = 'asc';
			$arrow = '';

			if(isset($_GET['sort']) AND $_GET['sort'] == $key AND isset($_GET['order']))
			{
				if($_GET['order'] == 'asc')
					$order = 'desc';

				$arrow = ($_GET['order'] == 'asc') ? '&#9660;' : '&#9650;';
				$arrow = '&nbsp;<span class="sort-arrow">'.$arrow.'</span';
			}

			$this->column_titles[$key] = Html::anchor(Request::initial()->uri() . Url::query(array('sort' => $key, 'order' => $order)), $title. $arrow) ;
		}
		return parent::_generate_heading();
	}

}