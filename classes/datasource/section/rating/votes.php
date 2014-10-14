<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * @package Datasource
 * @category Category
 */
class Datasource_Section_Rating_Votes extends Datasource_Section_Headline {

	/**
	 *
	 * @var DataSource_Rating_Document 
	 */
	protected $_document = NULL;
	
	/**
	 * 
	 * @param DataSource_Rating_Document $document
	 * @return Datasource_Section_Headline
	 */
	public function set_document(DataSource_Rating_Document $document)
	{
		$this->_document = $document;
		
		return $this;
	}

	public function fields()
	{
		return array(
			'author' => array(
				'name' => 'Author',
				'width' => NULL,
				'visible' => TRUE
			),
			'ip' => array(
				'name' => 'IP',
				'width' => 50,
				'class' => 'text-right',
				'visible' => TRUE
			),
			'rating' => array(
				'name' => 'Rating',
				'width' => 150,
				'class' => 'text-right',
				'visible' => TRUE
			),
			'created_on' => array(
				'name' => 'Date',
				'width' => 150,
				'class' => 'text-right',
				'visible' => TRUE
			),
		);
	}

	public function get(array $ids = NULL)
	{
		$documents = array();

		$results = array(
			'total' => 0,
			'documents' => array()
		);

		$pagination = $this->pagination($ids);

		$query = $this->query($ids)
			->select('l.id', 'l.is_active', 'l.is_fake', array('users.username', 'author'), 'l.created_on', 'l.rating', 'l.ip')
			->join('users', 'left')
				->on('users.id', '=', 'user_id')
			->order_by('created_on', 'desc');

		$result = $query
			->limit($this->limit())
			->offset($this->offset())
			->execute()
			->as_array('id');
		
		if(count($result) > 0)
		{
			$results['total'] = $pagination->total_items;
	
			foreach ( $result as $id => $row )
			{
				$data = array(
					'id' => $id,
					'published' => (bool) $row['is_active'],
					'author' => empty($row['author']) ? __('Anonyumous') : $row['author'],
					'created_on' => Date::format($row['created_on']),
					'rating' => (int) $row['rating'],
					'ip' => $row['ip']
				);

				$documents[$id] = (object) $data;
			}

			$results['documents'] = $documents;
		}
		
		return $results;
	}

	public function count_total(array $ids = NULL)
	{
		return $this->query($ids)
			->select(array(DB::expr('COUNT(*)'), 'total_docs'))
			->execute()
			->get('total_docs');
	}

	public function query(array $ids = NULL)
	{
		$query = DB::select()
			->from(array('dsrating_log', 'l'))
			->where('l.rating_id', '=', (int) $this->_document->id);
		
		if (!empty($ids))
		{
			$query->where('l.id', 'in', $ids);
		}
		
		return $query;
	}
	
	public function set_query_params()
	{
		
	}
	
	public function pagination(array $ids = NULL)
	{
		$pagination = parent::pagination($ids);
		
		$pagination->current_page = array(
			'source' => 'query_string',
			'key' => 'page',
			'uri' => Route::get('datasources')->uri(array('directory' => 'rating', 'controller' => 'document', 'action' => 'view'))
		);
		
		return $pagination;
	}
}