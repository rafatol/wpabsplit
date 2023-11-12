<div class="side-by-side">
    <select name="wpab_hypothesis_page" id="wpab_hypothesis_page" data-placeholder="<?=__('Select a challenger page')?>" required<?=((!$can_edit)?' readonly disabled':'')?>>
        <option value=""><?=__('Select a challenger page')?></option>
    </select>
    <input type="color" name="wpab_hypothesis_page_color" id="wpab_hypothesis_page_color" value="<?=$color?>" required<?=((!$can_edit)?' readonly disabled':'')?>>
</div>