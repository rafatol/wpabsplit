<select name="test_status" id="test_status">
	<option value=""><?=__('All tests')?></option>
	<option value="running"<?=((isset($_GET['test_status']) && $_GET['test_status'] == 'running')?' selected':'')?>><?=__('Running')?></option>
	<option value="paused"<?=((isset($_GET['test_status']) && $_GET['test_status'] == 'paused')?' selected':'')?>><?=__('Paused')?></option>
	<option value="completed"<?=((isset($_GET['test_status']) && $_GET['test_status'] == 'completed')?' selected':'')?>><?=__('Completed')?></option>
</select>