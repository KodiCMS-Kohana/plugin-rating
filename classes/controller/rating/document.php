<?php defined( 'SYSPATH' ) or die( 'No direct access allowed.' );

class Controller_Rating_Document extends Controller_System_Datasource_Document {
	
	protected function _load_template($doc) 
	{
		parent::_load_template($doc);
		
		$votes = new Datasource_Section_Rating_Votes;
		
		$this->template->content->set(array(
			'rating' => $doc->calculate_rating_data(),
			'votes' => $votes
				->set_document($doc)
				->render('datasource/rating/votes')
		));
	}

}