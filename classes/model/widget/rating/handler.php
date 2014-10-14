<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_Widget_Rating_Handler extends Model_Widget_Decorator_Handler {
	
	/**
	 * 
	 * @param array $data
	 */
	public function set_values(array $data) 
	{
		if (empty($data['ds_id']) OR ! $this->datasource_exists($data['ds_id']))
		{
			$data['ds_id'] = 0;
		}

		parent::set_values($data);

		return $this;
	}

}