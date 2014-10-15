<div class="panel-body">
	<div class="form-group">
		<label class="control-label col-md-3" for="ds_id"><?php echo __('Hybrid section'); ?></label>
		<div class="col-md-3">
			<?php echo Form::select('ds_id', Datasource_Data_Manager::get_all_as_options('rating'), $widget->ds_id, array(
				'id' => 'ds_id'
			)); ?>
		</div>
	</div>
</div>

<?php if (!$widget->ds_id): ?>
<div class="alert alert-warning alert-dark">
	<?php echo UI::icon('lightbulb-o fa-lg'); ?> <?php echo __('You need select rating section'); ?>
</div>
<?php else: ?>
<div class="panel-heading">
	<h4><?php echo __('Properties'); ?></h4>
</div>
<div class="panel-body">
	<div class="form-group form-inline">
		<label class="control-label col-md-3" for="doc_id_ctx"><?php echo __('Document ID (POST)'); ?></label>
		<div class="col-md-9">
			<?php echo Form::input('doc_id_ctx', $widget->doc_id_ctx, array(
				'class' => 'form-control', 'id' => 'doc_id_ctx'
			)); ?>
		</div>
	</div>
	
	<div class="form-group form-inline">
		<label class="control-label col-md-3" for="rating_value_ctx"><?php echo __('Rating ID (POST)'); ?></label>
		<div class="col-md-9">
			<?php echo Form::input('rating_value_ctx', $widget->rating_value_ctx, array(
				'class' => 'form-control', 'id' => 'rating_value_ctx'
			)); ?>
		</div>
	</div>
	
	<div class="form-group">
		<div class="col-md-offset-3 col-md-9">
			<div class="checkbox">
				<label><?php echo Form::checkbox('only_auth', 1, $widget->only_auth); ?> <?php echo __('Only authorized users can vote'); ?></label>

				<label><?php echo Form::checkbox('update_rating', 1, $widget->update_rating); ?> <?php echo __('User can update document rating'); ?></label>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>