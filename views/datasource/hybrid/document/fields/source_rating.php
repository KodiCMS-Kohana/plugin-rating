<div class="form-group form-inline">
	<label class="<?php echo Arr::get($form, 'label_class'); ?>">
		<?php echo $field->header; ?>
	</label>
	<div class="<?php echo Arr::get($form, 'input_container_class'); ?>">
		<?php if($value['id'] > 0): ?>
		<div class="padding-xs-vr">
			<span class="text-warning"><?php echo ($value['rating'] > 0) ? str_repeat(UI::icon('star fa-lg'), $value['rating']) : UI::icon('star-o fa-lg'); ?></span>
			&nbsp;&nbsp;&nbsp;
			<span class="text-muted">[ <?php echo HTML::anchor($value['uri'], __('Rating: :num / Votes: :votes', array(
				':num' => $value['rating'],
				':votes' => $value['votes']
			)), array('target' => 'blank')); ?> ]</span>
		</div>
		
		<?php if($field->hint): ?>
		<p class="help-block"><?php echo $field->hint; ?></p>
		<?php endif; ?>
		<?php else: ?>
		<div class="alert alert-info alert-dark padding-xs-vr">
			<?php echo __('Section will be available after ReSave'); ?>
		</div>
		<?php endif; ?>
	</div>
</div>