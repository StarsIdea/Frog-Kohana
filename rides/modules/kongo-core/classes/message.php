<?php

class Message {
	
	public static function success($message = NULL, $params = array())
	{
		self::add('success', $message, $params);
	}
	
	public static function warning($message = NULL, $params = array())
	{
		self::add('warning', $message, $params);
	}
	
	public static function error($message = NULL, $params = array())
	{
		self::add('error', $message, $params);
	}

	//@todo Should a file be required to make things consistent and allow for message cascading?
   protected static function add($type, $message = NULL, array $params = array())
	{
      $messages = Session::instance()->get('message');
      if(!is_array($messages)) {
         $messages = array();
      }

	  $path = explode('.', $message);
	  $file = array_shift($path); // get file from first item in path
	  $message_path = implode('.', $path);
	  $message = Kohana::find_file('messages', $file) ? Kohana::message($file, $message_path, $message_path) : $message;
      $messages[$type][] = __(Kohana::message($file, $message, $message), $params);
      Session::instance()->set('message', $messages);
   }

   public static function render() {
      $str = '';
      $messages = Session::instance()->get('message');
      Session::instance()->delete('message');

      if( ! empty($messages)) {
         foreach($messages as $type => $messages) {
            foreach($messages as $message) {
				$str .= "<div class=\"message $type\">$message</div>";
            }
         }
      }
      return $str;
   }

}