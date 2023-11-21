<div class="wrap">
	<h1 class="wp-heading-inline"><?=__('All Reports')?></h1>
</div>
<table class="wp-list-table widefat fixed striped table-view-list posts">
	<thead>
		<tr>
			<th scope="col" class="manage-column"><?=__('Title')?></th>
			<th scope="col" class="manage-column"><?=__('Control')?></th>
			<th scope="col" class="manage-column"><?=__('Challenger')?></th>
			<th scope="col" class="manage-column"><?=__('Sample size')?></th>
			<th scope="col" class="manage-column"><?=__('Period')?></th>
		</tr>
	</thead>
	<tbody>
<?php
	/** @var $tests WP_Query */
	if($tests->have_posts()):
		while($tests->have_posts()):
			$tests->the_post();
			$post = $tests->post; /** @var $post WP_Post */
?>
			<tr>
				<td>
					<a href="<?=admin_url('post.php?page=wpab_report&post=' . $post->ID)?>" title="<?=sprintf('%s report', $post->post_title)?>"><strong><?=$post->post_title?></strong></a>
				</td>
				<td><?=get_post(WPAB_get_control($post->ID))->post_title?></td>
				<td><?=get_post(WPAB_get_hypothesis($post->ID))->post_title?></td>
				<td><?=WPAB_get_test_quantity($post->ID)?></td>
				<td><?=sprintf(__('%s to %s'), wp_date(get_option('date_format'), get_post_timestamp($post)), wp_date(get_option('date_format'), WPAB_get_test_last_run($post->ID)->getTimestamp()))?></td>
			</tr>
<?php
		endwhile;
	else:
?>
		<tr>
			<td colspan="5"><?=__('No reports found')?></td>
		</tr>
<?php
	endif;
?>
	</tbody>
</table>