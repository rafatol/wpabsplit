<input type="hidden" name="wpabsplit_subject_nonce" value="<?=wp_create_nonce(WPAB_NONCE_KEY)?>">
<table class="form-table">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>Page Title</th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach($pages as $page):
?>
        <tr>
            <td><input type="checkbox" value="<?=$page->ID?>" name="selected_pages[]"<?=((in_array($page->ID, $selected_pages))?' checked':'')?>></td>
            <td><?=$page->post_title?></td>
        </tr>
<?php
    endforeach;
?>
    </tbody>
</table>