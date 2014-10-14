<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * @package Datasource
 * @category Category
 */
class DataSource_Section_Rating extends Datasource_Section {

	const DSR_DEVIATION = 0.49;
	/**
	 * 
	 * @return string
	 */
	public static function icon()
	{
		return 'star-half-o';
	}
	
	/**
	 *
	 * @var integer 
	 */
	public $min_rating = 0;
	
	/**
	 *
	 * @var integer 
	 */
	public $max_rating = 10;
	
	/**
	 * Таблица раздела
	 * 
	 * @var string
	 */
	protected $_ds_table = 'dsrating';
	
	
	/**
	 * Тип раздела
	 * 
	 * @var string
	 */
	protected $_type = 'rating';

	public function values(array $values = array())
	{		
		parent::values($values);
		
		$this->min_rating = (int) Arr::get($values, 'min_rating');
		$this->max_rating = (int) Arr::get($values, 'max_rating');
		
		if($this->max_rating <= $this->min_rating)
		{
			$this->max_rating = $this->min_rating + 1;
		}
	}
	
	public function update()
	{
		$status = parent::update();
		
		if($status)
		{
			$this->recalculate_all_rating();
		}
		
		return $status;
	}
	
	/**
	 * 
	 * @param integer $doc_id
	 * @param boolean $update_doc_field
	 * @return integer
	 */
	public function create_by_document_id($doc_id, $update_doc_field = TRUE) 
	{
		$ds = DataSource_Section::factory('hybrid');
		
		$select = DB::select(array(DB::expr($this->id()), 'ds_id'), 'id', 'created_on')
			->from($ds->table())
			->where('id', '=', (int) $doc_id);
		
		list($id, $num_rows) = DB::insert($this->table())
			->columns(array('ds_id', 'doc_id', 'created_on'))
			->select($select)
			->execute();
		
		if($id AND $update_doc_field === TRUE AND ($column = $this->get_document_column()) !== NULL)
		{
			DB::update(DataSource_Hybrid_Factory::PREFIX . $column['ds_id'])
				->set(array($column['name'] => $id))
				->where('id', '=', (int) $doc_id)
				->execute();
		}
		
		return $id;
	}
	
	public function has_access($acl_type = 'section.edit', $check_own = TRUE, $user_id = NULL)
	{
		if(in_array($acl_type, array(
			'document.create', 'document.edit'
		)))
		{
			return FALSE;
		}

		return parent::has_access($acl_type, $check_own, $user_id);
	}

	public function recalculate_all_rating()
	{
		$data = DB::select('d.id', 'd.doc_id')
			->select(array(DB::expr('SUM(l.votes)', 'raters')))
			->select(array(DB::expr('SUM(l.rating)', 'rating')))
			->from(array($this->table(), 'd'))
			->join(array('dsrating_log', 'l'), 'left')
				->on('l.rating_id', '=', 'd.id')
				->on('l.is_active', '>', DB::expr(0))
			->where('d.ds_id', '=', $this->id());

		foreach ($data->execute()->as_array('id') as $row)
		{
			$raters = (int) $row['raters'];
			
			$rating = $raters > 0 ? round($row['rating'] / $row['raters'], 4) : 0;
			DB::update($this->table())
				->set(array(
					'raters' => $raters,
					'rating' => $rating,
					'updated_on' => date('Y-m-d H:i:s')
				))
				->where('id', '=', $row['id'])
				->execute();
		}
	}

	/**
	 * 
	 * @param integer $id
	 * @param integer $rating
	 * @return boolean
	 */
	public function create_fake_rating($id, $rating) 
	{
		$doc = $this->get_document($id);

		if (!$doc->loaded())
		{
			return FALSE;
		}
		
		$data = $doc->calculate_rating_data();

		if($data['rating'] != $rating) 
		{
			$new_rating = $rating == $this->min_rating 
				? $this->min_rating + self::DSR_DEVIATION  
				: ($rating == $this->max_rating 
						? $this->max_rating - self::DSR_DEVIATION 
						: $rating);

			$query = DB::select(array(DB::expr('SUM(rating)'), 'rating'), array(DB::expr('COUNT(*)'), 'votes'))
				->from('dsrating_log')
				->where('rating_id', '=', $doc->id)
				->where('is_active', '>', 0)
				->group_by('rating_id')
				->execute()
				->current();
			 

			$sum = (int) $query['rating']; 
			$votes = (int) $query['votes']; 
			$avg_rating = $votes ? $sum / $votes : 0;
			$mark = $rating > $avg_rating ? $this->max_rating : $this->min_rating;
			$votes_to_add = max(ceil(($new_rating * $votes - $sum) / ($mark - $new_rating)), 1);

			if($votes_to_add > 0)
			{
				$data = array(
					'is_fake' => 1,
					'is_active' => 1,
					'rating' => $mark,
					'rating_id' => $doc->id,
					'created_on' => date('Y-m-d H:i:s')
				);
				
				$insert = DB::insert('dsrating_log')
					->columns(array_keys($data));
				
				for($i = 0; $i < $votes_to_add; ++$i)
				{
					$insert->values($data);
				}
				
				$insert->execute();
			}
			
			$doc->recalculate_rating();
			return TRUE;
		}

		return FALSE;
	}
	
	/**
	 * 
	 * @param integer $doc_id
	 * @param integer $rating
	 * @param integer $user_id
	 * @return boolean
	 */
	public function rate_document($doc_id, $rating, $user_id = NULL)
	{
		return $this->_add_user_rating('doc_id', (int) $doc_id, (int) $rating, $user_id);
	}
	
	/**
	 * 
	 * @param integer $doc_id
	 * @param integer $rating
	 * @param integer $user_id
	 * @return boolean
	 */
	public function rate_rating($rating_id, $rating, $user_id = NULL)
	{
		return $this->_add_user_rating('id', (int) $rating_id, (int) $rating, $user_id);
	}
	
	/**
	 * 
	 * @param string $field
	 * @param integer $id
	 * @param integer $rating
	 * @param integer $user_id
	 * @return boolean
	 */
	protected function _add_user_rating($field, $id, $rating, $user_id = NULL) 
	{
		$select = DB::select('id', (int) $user_id, 1, $this->get_valid_rating($rating), date('Y-m-d H:i:s'))
			->from($this->table())
			->where($field, '=', $id);

		$status = (bool) DB::insert('dsrating_log')
			->columns(array('rating_id', 'user_id', 'is_active', 'rating', 'created_on'))
			->select($select)
			->execute();
		
		if($status)
		{
			$this->recalculate_all_rating();
		}
		
		return $status;
	}
	
	/**
	 * 
	 * @param integer $rating_id
	 * @param array $user_ids
	 * @param boolean $state
	 * @return boolean
	 */
	public function set_votes_state($rating_id, array $user_ids, $state)
	{
		$result = TRUE;
		$doc = $this->get_document($rating_id);
		
		if(!$doc->loaded())
		{
			return FALSE;
		}

		if(!empty($user_ids)) 
		{
			$status = DB::update('dsrating_log')
				->set(array('is_active' => $state ? 1 : 0))
				->where('rating_id', '=', $rating_id)
				->where('user_id', 'in', $user_ids)
				->execute();

			if($status)
			{
				$doc->recalculate_rating();
			}
				
		}
		return $status;
	}
	
	/**
	 * 
	 * @param integer $rating_id
	 * @param array $ids
	 * @return boolean
	 */
	public function remove_votes($rating_id, $ids)
	{
		$result = TRUE;

		if(!empty($ids)) 
		{
			$result = (bool) DB::delete('dsrating_log')
				->where('rating_id', '=', (int) $rating_id)
				->where('id', 'in', $ids)
				->execute();

			if($result)
			{
				$doc = $this->get_document($rating_id);
				$doc->recalculate_rating();
			}
		}

		return $result;
	}
	
	/**
	 * 
	 * @param integer $rating
	 * @return integer
	 */
	public function get_valid_rating($rating) 
	{
		$rating = (int) $rating;

		return $rating > $this->max_rating 
			? $this->max_rating 
			: ($rating < $this->min_rating ? $this->min_rating : $rating);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_document_column() 
	{
		if ($this->_column = NULL)
		{
			$this->_column = DB::select('ds_id', 'name')
				->from('dshfields')
				->where('from_ds', '=', $this->id())
				->where('family', '=', 'rating')
				->limit(1)
				->execute()
				->current();
		}

		return $this->_column;
	}
}