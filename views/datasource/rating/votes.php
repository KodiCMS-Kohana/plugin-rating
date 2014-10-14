<?php if($data['total'] > 0): ?>
<table class="table table-striped">
	<colgroup>
		<col width="30px" />
		<?php foreach ($fields as $key => $field): ?>
		<?php if(Arr::get($field, 'visible') === FALSE) continue; ?>
		<col <?php if (Arr::get($field, 'width') !== NULL) echo 'width="' . (int) $field['width'] . '"px'; ?>/>
		<?php endforeach; ?>
	</colgroup>
	<thead>
		<tr>
			<th></th>
			<?php foreach ($fields as $key => $field): ?>
			<?php if(Arr::get($field, 'visible') === FALSE) continue; ?>
			<th class="<?php echo Arr::get($field, 'class'); ?>"><?php echo __(Arr::get($field, 'name')); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data['documents'] as $id => $document): ?>
		<tr data-id="<?php echo $id; ?>" class="<?php if($document->published === FALSE) echo 'unpublished'; ?>">
			<td class="row-checkbox">
				<?php echo Form::checkbox('doc[]', $id, NULL, array('class' => 'doc-checkbox')); ?>
			</td>
			<?php foreach ($fields as $key => $field): ?>
			<?php if(Arr::get($field, 'visible') === FALSE) continue; ?>

			<td class="row-<?php echo $key; ?> <?php echo Arr::get($field, 'class'); ?>"><?php echo $document->$key; ?></td>

			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<div class="panel-footer">
	<?php echo __('Total votes: :num', array(':num' => $data['total'])); ?>
	<br />
	<?php echo $pagination; ?>
</div>
<?php else: ?>
<h2><?php echo __('No votes'); ?></h2>
<?php endif; ?>
