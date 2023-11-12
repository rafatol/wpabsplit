<input type="hidden" name="wpabsplit_subject_nonce" value="<?=wp_create_nonce(WPAB_NONCE_KEY)?>">
<div class="side-by-side">
    <select name="wpab_control_page" id="wpab_control_page" data-placeholder="<?=__('Select a control page')?>" data-probe="<?=admin_url('admin-ajax.php')?>" required<?=((!$can_edit)?' readonly disabled':'')?>>
        <option value=""><?=__('Select a control page')?></option>
<?php
        foreach($pages as $page):
?>
        <option value="<?=$page->ID?>"<?=(($page->ID == $selected_page)?' selected':'')?>><?=$page->post_title?></option>
<?php
        endforeach;
?>
    </select>
    <input type="color" name="wpab_control_page_color" id="wpab_control_page_color" value="<?=$color?>" required<?=((!$can_edit)?' readonly disabled':'')?>>
</div>
