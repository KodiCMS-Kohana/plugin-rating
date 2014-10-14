<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * @package Datasource
 * @category Category
 */
class Datasource_Section_Rating_Headline extends Datasource_Section_Headline {

	public function fields()
	{
		return array(
			'header' => array(
				'name' => 'Header',
				'width' => NULL,
				'type' => 'link',
				'visible' => TRUE
			),
			'rating' => array(
				'name' => 'Rating',
				'width' => 150,
				'class' => 'text-right',
				'visible' => TRUE
			),
			'raters' => array(
				'name' => 'Raters',
				'width' => 50,
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
				->select('d.id', 'h.header', 'h.published', 'd.rating', 'd.raters', 'd.created_on', 'd.created_by_id')
				->order_by('rating', 'desc');

		$result = $query
			->limit($this->limit())
			->offset($this->offset())
			->execute()
			->as_array('id');
		
		if(count($result) > 0)
		{
			$results['total'] = $pagination->total_items;
			$ratio = 100 / ($this->_section->max_rating - $this->_section->min_rating);
			
			foreach ( $result as $id => $row )
			{
				$data = array(
					'id' => $id,
					'published' => (bool) $row['published'],
					'header' => $row['header'],
					'created_on' => Date::format($row['created_on']),
					'created_by_id' => $row['created_by_id'],
					'rating' => (int) $row['rating'],
					'raters' => (int) $row['raters']
				);
				
				$document = new DataSource_Rating_Document($this->_section);
				$document->id = $id;
				$documents[$id] = $document
					->read_values($data)
					->set_read_only();
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
			->from(array($this->_section->table(), 'd'))
			->join(array('dshybrid', 'h'))
				->on('d.doc_id', '=', 'h.id')
			->where('d.ds_id', '=', $this->_section->id());
		
		if (!empty($ids))
		{
			$query->where('d.id', 'in', $ids);
		}
		
		return $query;
	}
}