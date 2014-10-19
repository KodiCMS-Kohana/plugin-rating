<div class="panel">
	<div class="panel-heading">
		<span class="panel-title"><?php echo $document->header; ?></span>
	</div>
	<div class="panel-body">
		<div class="rows">
			<div class="col-sm-6">
				<h4>
					<strong><?php echo __('Rating'); ?></strong>&nbsp;&nbsp;&nbsp;
					<span class="text-warning"><?php echo $document->rating > 0 ? str_repeat(UI::icon('star'), $document->rating) : UI::icon('star-o'); ?></span>
					<br /><small>[<?php echo $datasource->min_rating; ?>&mdash;<?php echo $datasource->max_rating; ?>]</small>
				</h4>

				<?php echo __('Total votes'); ?>: <strong><?php echo $document->raters; ?></strong>
			</div>
			<div class="col-sm-6">
				<hr class="visible-xs"/>
				<div class="text-success">
					<?php echo __('Real rating'); ?>: <strong><?php echo $rating['real_rating']; ?></strong> <br />
					<?php echo __('Real votes'); ?>: <strong><?php echo $rating['real_votes']; ?></strong>
				</div>
				<div class="text-muted">
					<?php echo __('Fake rating'); ?>: <strong><?php echo $rating['fake_rating']; ?></strong> <br />
					<?php echo __('Fake votes'); ?>: <strong><?php echo $rating['fake_votes']; ?></strong>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-heading">
		<span class="panel-title"><?php echo __('Raters'); ?></span>
	</div>
	<?php echo $votes; ?>
</div>