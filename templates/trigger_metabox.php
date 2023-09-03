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

</div>
