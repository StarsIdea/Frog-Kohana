<?php defined('SYSPATH') or die('No direct script access.');

class Alphabet {

	protected $_alphabet;

	public static function factory($config)
	{
		return new self($config);
	}

    public function __construct(array $config)
	{
		$config = arr::extract($config, array('table', 'column'));

		if(in_array(NULL, $config))
		{
			throw new Exception('Missing parameter.');
		}

		extract($config);
		
		$this->_alphabet = DB::select(DB::expr('UCASE(SUBSTRING('.$column.',1,1)) as letter'))
			->distinct(TRUE)
			->from($table)
			->execute()
			->as_array(NULL, 'letter');

		$this->_alphabet = array_intersect($this->_alphabet, explode(',', 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z'));
		
		sort($this->_alphabet);
	}

	public function render()
	{
		$html = '<ul class="alphabet-pager">';
		foreach($this->_alphabet as $letter) {
			$html .= Arr::get($_GET, 'letter') == $letter
				? "<li>$letter</li>"
				: "<li>".Html::anchor(Request::initial()->uri().Url::query(array('letter' => $letter)), $letter)."</li>";
		}

		$html .= isset($_GET['letter']) 
			? '<li>'.Html::anchor(Request::initial()->uri(), 'All').'</li>'
			: '<li>All</li>';

		return $html;
	}

	public function __toString()
	{
		return $this->render();
	}

}

