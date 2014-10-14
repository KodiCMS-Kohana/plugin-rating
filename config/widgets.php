<?php defined('SYSPATH') or die('No direct access allowed.');

if(Plugins::is_activated('hybrid'))
{
	$categories['rating_handler'] = __('Rating handler');
}

return array(
	__('Rating') => $categories
);