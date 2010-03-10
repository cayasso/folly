<?php echo Form::open($action, $attributes)?>
<?php foreach($fields as $field):?>
	<?php echo $field?>
<?php endforeach?>
	<p>
		<?php echo Form::submit('submit', 'Submit')?>
	</p>
<?php echo Form::close()?>