<style>
    span.color-preview.control-bkg{background-color:<?=$controlColor?>;}
    span.color-preview.challenger-bkg{background-color:<?=$hypotesisColor?>;}
</style>
<div id="wpab-report">
    <h1><?=$post->post_title?></h1>
    <div class="panel">
        <div class="panel-title">
            <h5><?=__('About')?></h5>
            <a href="<?=admin_url('post.php?post=' . $post->ID . '&action=edit')?>" class="panel-action"><?=__('Edit')?></a>
        </div>
        <div class="panel-content test-detail">
            <div class="row">
                <div class="col">
                    <p><?=__('This report shows the results of your A/B test. The test was conducted on the following pages:')?></p>
                    <ul>
                        <li><span class="color-preview control-bkg"></span> <strong><?=__('Control page')?></strong>: <?=sprintf('%s (%s)', $controlPageTitle, $controlPageUrl)?></li>
                        <li><span class="color-preview challenger-bkg"></span> <strong><?=__('Challenger page')?></strong>: <?=sprintf('%s (%s)', $challengerPageTitle, $challengerPageUrl)?></li>
                    </ul>
<?php
                if($totalRuns):
?>
                    <p><?=sprintf(__('The results of this report are based on a collection of %d (of %d) tests between <strong>%s</strong> and <strong>%s</strong>.'), $totalRuns, WPAB_get_test_quantity($post->ID), wp_date(get_option('date_format'), get_post_timestamp($post)), wp_date(get_option('date_format'), $lastRunDateResult->getTimestamp()))?></p>
<?php
                else:
?>
                    <p></p>
<?php
                endif;
?>
                </div>
                <div class="col">
                    <p><?=__('The test was triggered by:')?> <strong><?=ucfirst(WPAB_get_event($post->ID))?></strong></p>
                    <p><?=__('The test was triggered on the following selector:')?> <strong><?=WPAB_get_selector($post->ID)?></strong></p>
                </div>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-title">
            <h5><?=__('All users')?></h5>
        </div>
        <div class="panel-content<?=((isset($resultArray['all']['empty']))?' empty-result':'')?>">
            <div id="all-users"></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><?=__('Variation')?></th>
                            <th><?=__('Sample Size')?></th>
                            <th><?=__('Conversions')?></th>
                            <th><?=__('Conversion Rate')?></th>
                            <th><?=__('Uplift')?></th>
                            <th><?=__('Probability to Be Best')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr<?=(($resultArray['all']['control']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview control-bkg"></span> <strong><?=__('Control')?></strong></td>
                            <td><?=number_format_i18n($resultArray['all']['control']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['all']['control']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['all']['control']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell baseline"><?=$resultArray['all']['control']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['all']['control']['p2bb'], 2)?>%</td>
                        </tr>
                        <tr<?=(($resultArray['all']['challenge']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview challenger-bkg"></span> <strong><?=__('Challenger')?></strong></td>
                            <td><?=number_format_i18n($resultArray['all']['challenge']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['all']['challenge']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['all']['challenge']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell uplift-<?=((substr($resultArray['all']['challenge']['uplift'], 0, 1) == '+')?'positive':((substr($resultArray['all']['challenge']['uplift'], 0, 1) == '-')?'negative':'none'))?>"><?=$resultArray['all']['challenge']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['all']['challenge']['p2bb'], 2)?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-title">
            <h5><?=__('Mobile users')?></h5>
        </div>
        <div class="panel-content<?=((isset($resultArray['small']['empty']))?' empty-result':'')?>">
            <div id="small-users"></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><?=__('Variation')?></th>
                            <th><?=__('Sample Size')?></th>
                            <th><?=__('Conversions')?></th>
                            <th><?=__('Conversion Rate')?></th>
                            <th><?=__('Uplift')?></th>
                            <th><?=__('Probability to Be Best')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr<?=(($resultArray['small']['control']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview control-bkg"></span> <strong><?=__('Control')?></strong></td>
                            <td><?=number_format_i18n($resultArray['small']['control']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['small']['control']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['small']['control']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell baseline"><?=$resultArray['small']['control']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['small']['control']['p2bb'], 2)?>%</td>
                        </tr>
                        <tr<?=(($resultArray['small']['challenge']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview challenger-bkg"></span> <strong><?=__('Challenger')?></strong></td>
                            <td><?=number_format_i18n($resultArray['small']['challenge']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['small']['challenge']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['small']['challenge']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell uplift-<?=((substr($resultArray['small']['challenge']['uplift'], 0, 1) == '+')?'positive':((substr($resultArray['small']['challenge']['uplift'], 0, 1) == '-')?'negative':'none'))?>"><?=$resultArray['small']['challenge']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['small']['challenge']['p2bb'], 2)?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-title">
            <h5><?=__('Tablet users')?></h5>
        </div>
        <div class="panel-content<?=((isset($resultArray['medium']['empty']))?' empty-result':'')?>">
            <div id="medium-users"></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><?=__('Variation')?></th>
                            <th><?=__('Sample Size')?></th>
                            <th><?=__('Conversions')?></th>
                            <th><?=__('Conversion Rate')?></th>
                            <th><?=__('Uplift')?></th>
                            <th><?=__('Probability to Be Best')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr<?=(($resultArray['medium']['control']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview control-bkg"></span> <strong><?=__('Control')?></strong></td>
                            <td><?=number_format_i18n($resultArray['medium']['control']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['medium']['control']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['medium']['control']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell baseline"><?=$resultArray['medium']['control']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['medium']['control']['p2bb'], 2)?>%</td>
                        </tr>
                        <tr<?=(($resultArray['medium']['challenge']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview challenger-bkg"></span> <strong><?=__('Challenger')?></strong></td>
                            <td><?=number_format_i18n($resultArray['medium']['challenge']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['medium']['challenge']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['medium']['challenge']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell uplift-<?=((substr($resultArray['medium']['challenge']['uplift'], 0, 1) == '+')?'positive':((substr($resultArray['medium']['challenge']['uplift'], 0, 1) == '-')?'negative':'none'))?>"><?=$resultArray['medium']['challenge']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['medium']['challenge']['p2bb'], 2)?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-title">
            <h5><?=__('Desktop users')?></h5>
        </div>
        <div class="panel-content<?=((isset($resultArray['large']['empty']))?' empty-result':'')?>">
            <div id="large-users"></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><?=__('Variation')?></th>
                            <th><?=__('Sample Size')?></th>
                            <th><?=__('Conversions')?></th>
                            <th><?=__('Conversion Rate')?></th>
                            <th><?=__('Uplift')?></th>
                            <th><?=__('Probability to Be Best')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr<?=(($resultArray['large']['control']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview control-bkg"></span> <strong><?=__('Control')?></strong></td>
                            <td><?=number_format_i18n($resultArray['large']['control']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['large']['control']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['large']['control']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell baseline"><?=$resultArray['large']['control']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['large']['control']['p2bb'], 2)?>%</td>
                        </tr>
                        <tr<?=(($resultArray['large']['challenge']['winner'])?' class="winner-row"':'')?>>
                            <td><span class="color-preview challenger-bkg"></span> <strong><?=__('Challenger')?></strong></td>
                            <td><?=number_format_i18n($resultArray['large']['challenge']['sample'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['large']['challenge']['conversion'], 0)?></td>
                            <td><?=number_format_i18n($resultArray['large']['challenge']['conversion_rate'], 2)?>%</td>
                            <td class="uplift-cell uplift-<?=((substr($resultArray['large']['challenge']['uplift'], 0, 1) == '+')?'positive':((substr($resultArray['large']['challenge']['uplift'], 0, 1) == '-')?'negative':'none'))?>"><?=$resultArray['large']['challenge']['uplift']?></td>
                            <td><?=number_format_i18n($resultArray['large']['challenge']['p2bb'], 2)?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>