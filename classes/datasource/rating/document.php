<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * @package Datasource
 * @category Hybrid
 */
class DataSource_Rating_Document extends Datasource_Document {
	
	protected $_system_fields = array(
		'id' => NULL,
		'ds_id' => 0,
		'doc_id' => 0,
		'raters' => 0,
		'rating' => 0,
	);

	public function filters()
	{
		$filters = parent::filters();
		
		$filters['doc_id'] = array(
			array('intval')
		);
		
		$filters['raters'] = array(
			array('intval')
		);
		
		$filters['rating'] = array(
			array('intval')
		);
				
		return $filters;
	}
	
	/**
	 * Загрузка документа по названию поля значению
	 * 
	 * @param string $field
	 * @param string $value
	 * @return \DataSource_Document
	 */
	public function load_by($field, $value)
	{
		$columns = array_map(function($v) {
			return 'd.'.$v;
		}, array_keys($this->system_fields()));

		$result = DB::select('h.header')
			->select_array($columns)
			->from(array($this->section()->table(), 'd'))
			->join(array('dshybrid', 'h'))
				->on('d.doc_id', '=', 'h.id')
			->where('d.ds_id', '=', (int) $this->section()->id())
			->where('d.' . $field, '=', $value)
			->limit(1)
			->execute()
			->current();

		if (empty($result))
		{
			return $this;
		}

		$this->_loaded = TRUE;

		foreach ($result as $field => $value)
		{
			$this->{$field} = $value;
		}

		return $this;
	}

	public function remove()
	{
		if(($status = parent::remove()) === TRUE)
		{
			DB::delete('dsrating_log')
				->where('rating_id', '=', $this->id)
				->execute();
		}
		
		return $status;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function recalculate_rating()
	{
		$data = DB::select()
			->select(array(DB::expr('COUNT(*)'), 'raters'))
			->select(array(DB::expr('SUM(l.rating)'), 'rating'))
			->from(array('dsrating_log', 'l'))
			->where('l.is_active', '>', 0)
			->where('l.rating_id', '=', $this->id)
			->group_by('l.rating_id');

		foreach ($data->execute() as $row)
		{
			$raters = (int) $row['raters'];
			
			$rating = $raters > 0 ? round($row['rating'] / $row['raters'], 4) : 0;
			DB::update($this->section()->table())
				->set(array(
					'raters' => $raters,
					'rating' => $rating,
					'updated_on' => date('Y-m-d H:i:s')
				))
				->where('id', '=', $this->id)
				->execute();
		}
		
		return TRUE;
	}

	/**
	 * 
	 * @param integer $rating_id
	 * @return array
	 */
	public function calculate_rating_data()
	{
		$ds = DataSource_Section::factory('hybrid');

		$result = DB::select('d.*', 'h.header')
			->from(array($this->section()->table(), 'd'))
			->join(array($ds->table(), 'h'))
				->on('h.id', '=', 'd.doc_id')
			->where('d.id', '=', $this->id)
			->limit(1)
			->execute()
			->current();
		
		if($result !== NULL)
		{
			$real_calculated = DB::select(array(DB::expr('SUM(rating)'), 'rating'), array(DB::expr('COUNT(*)'), 'votes'))
				->from('dsrating_log')
				->where('rating_id', '=', $result['id'])
				->where('is_fake', '=', 0)
				->where('is_active', '>', 0)
				->execute()
				->current();
			
			if($real_calculated !== NULL)
			{
				$result['real_rating'] = (int) ($real_calculated['votes'] ? (float) $real_calculated['rating'] / $real_calculated['votes'] : 0);
				$result['real_votes'] = (int) $real_calculated['votes'];
			}
			
			$fake_calculated = DB::select(array(DB::expr('SUM(rating)'), 'rating'), array(DB::expr('COUNT(*)'), 'votes'))
				->from('dsrating_log')
				->where('rating_id', '=', $result['id'])
				->where('is_fake', '>', 0)
				->where('is_active', '>', 0)
				->execute()
				->current();
			
			if($fake_calculated !== NULL)
			{
				$result['fake_rating'] = (int) ($fake_calculated['votes'] ? (int) $fake_calculated['rating'] / $fake_calculated['votes'] : 0);
				$result['fake_votes'] = (int) $fake_calculated['votes'];
			}
		}
			
		return $result;
	}
}