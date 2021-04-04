<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Media extends Controller {
	
	public function action_file()
	{
		$file = $this->request->param('file');

		$ext = pathinfo($file, PATHINFO_EXTENSION);

		$file = substr($file, 0, -(strlen($ext) + 1));

		if ($file = Kohana::find_file('media', $file, $ext))
		{
			$this->response->body(file_get_contents($file));
		}
		else
		{
			Kohana::$log->add(Kohana::ERROR, 'Admin media controller error while loading file, '.$file);
			$this->response->status(404);
		}

		$this->response->headers('Content-Type', File::mime_by_ext($ext));
	}

}