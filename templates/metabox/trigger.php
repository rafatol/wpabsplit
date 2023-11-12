<div class="form-group">
    <label for="trigger_selector"><?=__('Element selector')?></label>
    <input type="text" name="trigger_selector" id="trigger_selector" value="<?=$trigger_selector?>" data-invalid="<?=__('%s is not a valid selector')?>" required<?=((!$can_edit)?' readonly disabled':'')?>>
    <p><?=__('Enter the CSS selector of the element you want to monitor.')?></p>
</div>
<div class="form-group">
    <label for="trigger_event"><?=__('Trigger Event')?></label>
    <div class="input-group">
        <select name="trigger_event" id="trigger_event" required<?=((!$can_edit)?' readonly disabled':'')?>>
            <option value="click"<?=(($trigger_event == 'click')?' selected':'')?>><?=__('Click')?></option>
            <option value="submit"<?=(($trigger_event == 'submit')?' selected':'')?>><?=__('Submit')?></option>
            <option value="mousein"<?=(($trigger_event == 'mousein')?' selected':'')?>><?=__('Mouse In')?></option>
            <option value="mouseout"<?=(($trigger_event == 'mouseout')?' selected':'')?>><?=__('Mouse Out')?></option>
            <option value="visible"<?=(($trigger_event == 'visible')?' selected':'')?>><?=__('Visible')?></option>
        </select>
        <div class="input-group-btn">
            <button type="button" data-action="toggle" data-target="#supported_events"><i class="dashicons dashicons-editor-help"></i> <?=__('Supported events')?></button>
        </div>
    </div>
    <div id="supported_events" class="more-info">
        <div class="hidden-content hidden">
            <ul>
                <li>
                    <strong>click</strong><br>
                    <p><?=__('Use this event to monitor when a page element is clicked.')?></p>
                </li>
                <li>
                    <strong>submit</strong><br>
                    <p><?=__('Use this event to monitor when a form is submitted')?></p>
                </li>
                <li>
                    <strong>mousein</strong><br>
                    <p><?=__('Use this event to monitor when the mouse cursor enters an element\'s area.')?></p>
                </li>
                <li>
                    <strong>mouseout</strong><br>
                    <p><?=__('Use this event to monitor when the mouse cursor leaves an element\'s area.')?></p>
                </li>
                <li>
                    <strong>visible</strong><br>
                    <p><?=__('Use this event to monitor when an element enters the browser\'s visible area.')?></p>
                </li>
            </ul>
        </div>
    </div>
</div>