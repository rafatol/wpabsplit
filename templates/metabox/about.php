<?php
    include WPAB_PLUGIN_PATH . 'templates/column/progress.php';
?>
<div class="wpab-report-shortcut">
	<a href="<?=$reportUrl?>" title="<?=__('Test Report')?>"<?=((!$isCompleted)?' class="disabled"':'')?>><?=__('Test Report')?></a>
</div>