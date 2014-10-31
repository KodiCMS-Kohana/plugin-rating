<?php echo Form::open(Request::current()->uri(), array(
	'class' => 'form-horizontal panel'
)); ?>
	<?php echo Form::hidden('ds_id', $ds->id()); ?>
	
	<?php echo View::factory('datasource/section/information_form', array(
		'users' => $users,
		'ds' => $ds
	)); ?>

	<div class="panel-heading" data-icon="info">
		<span class="panel-title"><?php echo __('Datasource properties'); ?></span>
	</div>
	<div class="panel-body">
		<div class="form-group form-inline">
			<label class="control-label col-md-3"><?php echo __('Rating (min/max)'); ?></label>
			<div class="col-md-9">
				<div class="input-group">
					<?php echo Form::input('min_rating', $ds->min_rating, array('class' => 'form-control', 'size' => 2)); ?>
					<div class="input-group-addon">-</div>
					<?php echo Form::input('max_rating', $ds->max_rating, array('class' => 'form-control', 'size' => 2)); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="form-actions panel-footer">
		<?php echo UI::actions(NULL, Datasource_Section::uri()); ?>
	</div>
<?php echo Form::close(); ?>