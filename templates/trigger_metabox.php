<div class="trigger-type">
    <label for="trigger_type"><?=__('Trigger type')?></label>
    <select name="trigger_type" id="trigger_type">
        <option value="data_attribute"<?=(($trigger_type == 'data_attribute')?' selected':'')?>><?=__('Data attributes')?></option>
        <option value="selectors"<?=(($trigger_type == 'selectors')?' selected':'')?>><?=__('CSS Selectors')?></option>
    </select>
</div>
<div class="hidden" data-parent="trigger_type" data-value="data_attribute">
    <h3><?=__('Instructions')?></h3>
    <p><?=__('It is possible to use data attributes to inform the plugin which elements will be monitored and how they will be monitored, as well as inform a friendly description that will be used in the A/B test reports.')?></p>
    <p><?=__('Use the <strong>data-wpab-trigger</strong> attribute to indicate to the plugin which action will trigger the record in the report.')?></p>
    <div class="more-info">
        <button type="button"><?=__('Supported triggers')?></button>
        <div class="hidden-content hidden">
            <ul>
                <li>
                    <strong>click</strong><br>
                    <p><?=__('Use this trigger to monitor when a page element is clicked.')?></p>
                </li>
                <li>
                    <strong>submit</strong><br>
                    <p><?=__('Use this trigger to monitor when a form is submitted')?></p>
                </li>
                <li>
                    <strong>mousein</strong><br>
                    <p><?=__('Use this trigger to monitor when the mouse cursor enters an element\'s area.')?></p>
                </li>
                <li>
                    <strong>mouseout</strong><br>
                    <p><?=__('Use this trigger to monitor when the mouse cursor leaves an element\'s area.')?></p>
                </li>
                <li>
                    <strong>visible</strong><br>
                    <p><?=__('Use this trigger to monitor when an element enters the browser\'s visible area.')?></p>
                </li>
            </ul>
        </div>
    </div>
    <hr>
    <p><?=__('Use the <strong>data-wpab-description</strong> attribute to user-friendly describe what the element represents. This information will be displayed on the final test report.')?></p>
    <h4><?=__('Example')?></h4>
    <div class="code-box">
        &lt;button class="fancy-button" <span class="highlight-me">data-wpab-trigger="click"</span> <span class="highlight-me">data-wpab-description="<?=__('Someone clicked here!')?>"</span>&gt;foobar&lt;/button&gt;
    </div>
</div>
<div class="hidden" data-parent="trigger_type" data-value="selectors">
    <h3><?=__('Instructions')?></h3>
    <p><?=__('It is possible to use CSS selectors to inform the plugin which elements will be monitored and how they will be monitored, as well as inform a friendly description that will be used in the A/B test reports.')?></p>
    <div class="more-info">
        <button type="button"><?=__('Supported triggers')?></button>
        <div class="hidden-content hidden">
            <ul>
                <li>
                    <strong>click</strong><br>
                    <p><?=__('Use this trigger to monitor when a page element is clicked.')?></p>
                </li>
                <li>
                    <strong>submit</strong><br>
                    <p><?=__('Use this trigger to monitor when a form is submitted')?></p>
                </li>
                <li>
                    <strong>mousein</strong><br>
                    <p><?=__('Use this trigger to monitor when the mouse cursor enters an element\'s area.')?></p>
                </li>
                <li>
                    <strong>mouseout</strong><br>
                    <p><?=__('Use this trigger to monitor when the mouse cursor leaves an element\'s area.')?></p>
                </li>
                <li>
                    <strong>visible</strong><br>
                    <p><?=__('Use this trigger to monitor when an element enters the browser\'s visible area.')?></p>
                </li>
            </ul>
        </div>
    </div>
    <hr>
    <table class="form-table">
        <thead>
            <tr>
                <th><?=__('Selector')?></th>
                <th><?=__('Trigger')?></th>
                <th><?=__('Description')?></th>
                <th><?=__('Actions')?></th>
            </tr>
        </thead>
        <tbody>
<?php
    if(is_array($triggers) && count($triggers)):
        foreach($triggers as $idx => $t):
?>
            <tr>
                <td><input type="text" value="<?=$t['selector']?>" name="trigger[<?=$idx?>][selector]" id="trigger-<?=$idx?>-selector" data-name="trigger[@][selector]" data-id="trigger-@-selector" data-invalid="<?=__('%s is not a valid selector')?>" required></td>
                <td>
                    <select name="trigger[<?=$idx?>][type]" id="trigger-<?=$idx?>-type" data-name="trigger[@][type]" data-id="trigger-@-type" required>
                        <option value="click"<?=(($t['type'] == 'click')?' selected':'')?>><?=__('Click')?></option>
                        <option value="submit"<?=(($t['type'] == 'submit')?' selected':'')?>><?=__('Submit')?></option>
                        <option value="mousein"<?=(($t['type'] == 'mousein')?' selected':'')?>><?=__('Mouse In')?></option>
                        <option value="mouseout"<?=(($t['type'] == 'mouseout')?' selected':'')?>><?=__('Mouse Out')?></option>
                        <option value="visible"<?=(($t['type'] == 'visible')?' selected':'')?>><?=__('Visible')?></option>
                    </select>
                </td>
                <td><input type="text" value="<?=$t['selector']?>" name="trigger[<?=$idx?>][description]" id="trigger-<?=$idx?>-description" data-name="trigger[@][description]" data-id="trigger-@-description" required></td>
                <td><button type="button" data-action="add"><?=__('Add')?></button> <button type="button" data-action="delete"><?=__('Remove')?></button></td>
            </tr>
<?php
        endforeach;
    else:
?>
            <tr>
                <td><input type="text" name="trigger[0][selector]" id="trigger-0-selector" data-name="trigger[@][selector]" data-id="trigger-@-selector" data-invalid="<?=__('%s is not a valid selector')?>" required></td>
                <td>
                    <select name="trigger[0][type]" id="trigger-0-type" data-name="trigger[@][type]" data-id="trigger-@-type" required>
                        <option value="click"><?=__('Click')?></option>
                        <option value="submit"><?=__('Submit')?></option>
                        <option value="mousein"><?=__('Mouse In')?></option>
                        <option value="mouseout"><?=__('Mouse Out')?></option>
                        <option value="visible"><?=__('Visible')?></option>
                    </select>
                </td>
                <td><input type="text" name="trigger[0][description]" id="trigger-0-description" data-name="trigger[@][description]" data-id="trigger-@-description" required></td>
                <td><button type="button" data-action="add"><?=__('Add')?></button> <button type="button" data-action="delete"><?=__('Remove')?></button></td>
            </tr>
<?php
    endif;
?>
        </tbody>
    </table>
</div>
