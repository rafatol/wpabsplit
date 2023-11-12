<div class="option-group">
    <label for="test_quantity"><?=__('Number of tests')?></label>
    <input type="number" name="test_quantity" id="test_quantity" value="<?=$test_quantity?>" min="2" step="1" required>
    <p><?=__('Enter the number of tests you want to carry out with the selected pages. If the quantity entered is not a multiple of the number of pages selected, the system will run the tests until this value is reached.')?></p>
</div>