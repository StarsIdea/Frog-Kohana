<?php

class Kongo {

	public static $content_dir;

	public static function get_resource_names()
	{
		$resources = array();
		// Get a recursive array of every model
		$models = new RecursiveArrayIterator(Kohana::list_files('classes/model'));

		// Loop through each model recursively
		foreach (new RecursiveIteratorIterator($models) as $model => $path)
		{
			// Clean up the model name, and make it relative to the model folder
			$model = trim(str_replace(array('classes/model', EXT), '', $model), DIRECTORY_SEPARATOR);
			$model = str_replace(DIRECTORY_SEPARATOR, '_', $model);

			// Make sure model extends Model_Admin
			$reflector = new ReflectionClass('Model_'.$model);

			if($reflector->isSubclassOf('ORM') AND ! in_array($model, array('publishable')))
			{
				$resources[] = Inflector::plural($model);
			}
		}
		return $resources;
	}

}
