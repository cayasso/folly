<?php echo Form::open($action, $attributes)?>
<?php foreach($elements as $element):?>
	<?php echo $element?>
<?php endforeach?>
	<p>
		<?php echo Form::submit('submit', 'Submit')?>
	</p>
<?php echo Form::close()?>