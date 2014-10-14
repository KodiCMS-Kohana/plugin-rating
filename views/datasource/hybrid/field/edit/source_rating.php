<div class="form-group">
	<label class="control-label col-md-3" for="from_ds"><?php echo __('Datasource'); ?></label>
	<div class="col-md-3">
		<?php echo Form::select('from_ds', $field->sections(), $field->from_ds, empty($field->id) ? NULL : array('disabled')); ?>
	</div>
</div>