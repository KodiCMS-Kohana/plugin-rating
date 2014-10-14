<div class="panel">
	<div class="panel-heading">
		<span class="panel-title"><?php echo $document->header; ?></span>
	</div>
	<div class="panel-body">
		<div class="rows">
			<div class="col-sm-6">
				<h4>
					<strong>Рейтинг</strong>&nbsp;&nbsp;&nbsp;
					<span class="text-warning"><?php echo $document->rating > 0 ? str_repeat(UI::icon('star'), $document->rating) : UI::icon('star-o'); ?></span>
					<br /><small>[<?php echo $datasource->min_rating; ?>&mdash;<?php echo $datasource->max_rating; ?>]</small>
				</h4>

				Всего голосов: <strong><?php echo $document->raters; ?></strong>
			</div>
			<div class="col-sm-6">
				<hr class="visible-xs"/>
				<div class="text-success">
					Реальный рейтинг: <strong><?php echo $rating['real_rating']; ?></strong> <br />
					Реальных голосов: <strong><?php echo $rating['real_votes']; ?></strong>
				</div>
				<div class="text-muted">
					Исскуственный рейтинг: <strong><?php echo $rating['fake_rating']; ?></strong> <br />
					Исскуственных голосов: <strong><?php echo $rating['fake_votes']; ?></strong>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-heading">
		<span class="panel-title">Голоса</span>
	</div>
	<?php echo $votes; ?>
</div>