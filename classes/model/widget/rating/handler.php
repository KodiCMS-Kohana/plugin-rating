<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_Widget_Rating_Handler extends Model_Widget_Decorator_Handler {

	const ERROR_USER_IS_VOTED = 100;
	const ERROR_NO_AUTH = 200;

	protected $_data = array(
		'doc_id_ctx' => 'doc_id',
		'rating_value_ctx' => 'rating',
		'only_auth' => TRUE,
		'ds_id' => 0
	);
	
	protected $_as_json = TRUE;
	
	/**
	 *
	 * @var array 
	 */
	protected $_response = array(
		'status' => FALSE,
		'data' => array()
	);
	
	public function on_page_load() 
	{
		if ($this->ds_id < 1)
		{
			return;
		}
		
		$doc_id = (int) $this->_get_field_value($this->doc_id_ctx);
		$rating_value_ctx = (int) $this->_get_field_value($this->rating_value_ctx);
		
		$user_id = (int) Auth::get_id();
		
		$this->_response['data'] = array(
			'doc_id' => $doc_id,
			'rating' => $rating_value_ctx
		);

		if (!empty($doc_id))
		{
			if($this->only_auth AND empty($user_id))
			{
				$status = FALSE;
				$this->_response['code'] = self::CODE_NO_AUTH;
				$this->_response['message'] = 'User not auth';
			}
			else
			{
				$ds = Datasource_Data_Manager::load($this->ds_id);
				if($ds->user_is_voted($doc_id))
				{
					$status = FALSE;
					$this->_response['code'] = self::ERROR_USER_IS_VOTED;
					$this->_response['message'] = 'User is voted';
				}
				else
				{
					$exists = (bool) DB::select('id')
						->from($ds->table())
						->where('ds_id', '=', $ds->id())
						->where('doc_id', '=', $doc_id)
						->limit(1)
						->execute()
						->get('id');

					if(!$exists)
					{
						$ds->create_by_document_id($doc_id);
					}

					$status = $ds->rate_document($doc_id, $rating_value_ctx, $user_id);
				}
			}
		}
		
		$this->_response['status'] = $status;
		parent::on_page_load();
	}
	
	/**
	 * 
	 * @param array $data
	 */
	public function set_values(array $data) 
	{
		parent::set_values($data);
		
		$this->only_auth = (bool) Arr::get($data, 'only_auth');

		if (empty($data['ds_id']) OR ! Datasource_Data_Manager::exists($data['ds_id']))
		{
			$this->ds_id = 0;
		}

		return $this;
	}
	
	public function set_doc_id_ctx($value)
	{
		return URL::title($value, '_');
	}
	
	public function set_rating_id_ctx($value)
	{
		return URL::title($value, '_');
	}
	
	public function set_ds_id($ds_id)
	{
		return (int) $ds_id;
	}
	
	public function fetch_backend_content()
	{
		if($this->ds_id > 0 AND ! Datasource_Data_Manager::exists($this->ds_id))
		{
			$this->ds_id = 0;
			Widget_Manager::update($this);
		}
		
		return parent::fetch_backend_content();
	}
	
	protected function _get_field_value($field)
	{
		return Arr::get(Request::current()->query(), $field);
	}
}