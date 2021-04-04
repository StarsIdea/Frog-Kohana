<?php

class Helper_Table {

	/**
	 * @param string $column The database column name to order by
	 * @param string $title The text to display in the table header
	 * @return string
	 */
	public static function get_sortable_header($column, $title=NULL)
	{
		if( ! $title) {
			$title = ucwords(str_replace('_', ' ', $column));
		}
		$direction = NULL;
		if(Arr::get($_GET,'sort') == $column AND isset($_GET['dir']))
		{
			$arrow = $_GET['dir'] == 'asc' ? '▲' : '▼';
			$direction = '&nbsp;<span class="arrow">'.$arrow.'</span>';
		}
		return Html::anchor(
			Request::initial()->uri().Url::query(array('sort'=>$column, 'dir'=>Arr::get($_GET,'dir') == 'desc' ? 'asc' : 'desc')),
			$title.$direction
		);
	}

}