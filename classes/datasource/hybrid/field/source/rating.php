<?php defined('SYSPATH') or die('No direct access allowed.');

class DataSource_Hybrid_Field_Source_Rating extends DataSource_Hybrid_Field_Source {

	protected $_is_sortable = TRUE;
	
	public function rules()
	{
		$rules = parent::rules();
		$rules['from_ds'] = array(
			array('not_empty'),
			array('intval')
		);
		
		return $rules;
	}
	
	public function sections()
	{
		$used_sections = DB::select('from_ds')
			->from($this->table)
			->where('type', '=', 'source_rating')
			->execute()
			->as_array(NULL, 'from_ds');

		$sections = array(__('--- Not set ---'));
		foreach (Datasource_Data_Manager::get_all('rating') as $id => $section)
		{
			if(in_array($id, $used_sections) AND empty($this->id)) continue;

			$sections[$id] = $section->name;
		}
		
		return $sections;
	}
	
	public function convert_value($value)
	{
		$result = array(
			'id' => NULL, 
			'rating' => 0, 
			'votes' => 0,
			'uri' => NULL
		);

		if($rating = $this->get_rating_details($value))
		{
			$result = array(
				'id' => $value,
				'rating' => $rating['rating'],
				'votes' => $rating['raters'],
			);
			
			if(IS_BACKEND)
			{
				$result['uri'] =  Route::get('datasources')
					->uri(array(
						'directory' => 'rating', 'controller' => 'document', 'action' => 'view'
					)) . URL::query(array(
						'ds_id' => $this->from_ds,
						'id' => $value
					), FALSE);
			}
		}
		
		return $result;
	}

	public function get_type()
	{
		return 'INT(11) UNSIGNED';
	}
	
	public function onReadDocumentValue(array $data, DataSource_Hybrid_Document $document)
	{
		return $this;
	}
	
	public function onCreateDocument(DataSource_Hybrid_Document $doc)
	{
		$ds = Datasource_Data_Manager::load($this->from_ds);
		
		$id = $ds->create_by_document_id($doc->id, FALSE);
		$doc->set($this->name, $id);
	}
	
	public function onUpdateDocument(DataSource_Hybrid_Document $old = NULL, DataSource_Hybrid_Document $new)
	{
		$this->onCreateDocument($new);
	}
	
	public function onRemoveDocument(DataSource_Hybrid_Document $doc)
	{
		$ds = Datasource_Data_Manager::load($this->from_ds);
		$rating_id = $doc->get($this->name);

		if($rating_id > 0)
		{
			$ds->remove_documents(array($rating_id));
		}
	}
	
	public function get_rating_details($id) 
	{
		return DB::select('rating', 'raters')
			->from('dsrating')
			->where('ds_id', '=', $this->from_ds)
			->where('id', '=', (int) $id)
			->limit(1)
			->execute()
			->current();
	}
	
	public function remove()
	{
		$status = parent::remove();
		
		if($status)
		{
			$ds = DataSource_Section::factory('hybrid');
			$ids = DB::select('r.id')
				->from(array('dsrating', 'r'))
				->join(array($ds->table(), 'd'))
					->on('r.doc_id', '=', 'd.id')
				->where('r.ds_id', '=', $this->from_ds)
				->execute()
				->as_array(NULL, 'id');

			Datasource_Data_Manager::load($this->from_ds)->remove_documents($ids);
		}
	}
	
	public function fetch_headline_value($value, $document_id)
	{
		$ds = Datasource_Data_Manager::load($this->from_ds);

		$uri = Route::get('datasources')
			->uri(array(
				'directory' => 'rating', 'controller' => 'document', 'action' => 'view'
			)) . URL::query(array(
				'ds_id' => $this->from_ds,
				'id' => $value
			), FALSE);
	
		if($rating = $this->get_rating_details($value))
		{
			$value = $rating['rating'];
		}
		else
		{
			$value = 0;
		}
		
		return HTML::anchor($uri, $value, array('target' => 'blank'));
	}
	
	public function get_query_props(Database_Query $query, DataSource_Hybrid_Agent $agent)
	{
		parent::get_query_props($query, $agent);

		$query
			->join('dsrating', 'left')
				->on($this->table_column_key(), '=', 'dsrating.id')
				->on('dsrating.ds_id', '=', DB::expr($this->from_ds))
				->select(array('dsrating.rating', 'dsr' . $this->id));
	}
	
	public function sorting_condition(Database_Query $query, $dir)
	{
		$query->order_by('dsr' . $this->id, $dir);
	}
	
	public static function fetch_widget_field($widget, $field, $row, $fid, $recurse)
	{
		if($widget instanceof Model_Widget_Hybrid_Headline)
		{
			return (int) $row['dsr' . $fid];
		}
		else if($widget instanceof Model_Widget_Hybrid_Document)
		{
			return $field->convert_value($row[$fid]);
		}
		
		return (int) $row[$fid];
	}
}