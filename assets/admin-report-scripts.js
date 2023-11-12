let wpabChart = {
    loadData: function(){
        CanvasJS.addColorSet('wpabColorSet', [wpab_chart_colors.control_color, wpab_chart_colors.hypotesis_color]);

        jQuery.each(wpab_chart_data, function(idx, chartInfo){
            jQuery('#' + idx + '-users').addClass('chart');

            wpab_chart_data.chart = new CanvasJS.Chart(idx + '-users', {
                colorSet: 'wpabColorSet',
                data: [
                    {
                        type: "pie",
                        dataPoints: chartInfo.dataPoints
                    }
                ],
                toolTip: {
                    enabled: true,
                    animationEnabled: true,
                    content: "{indexLabel}: {y}%"
                }
            });

            wpab_chart_data.chart.render();
        });

        if(typeof wpab_sidebar !== 'undefined'){
            jQuery.each(wpab_sidebar.custom_menu, function(elI, elV){
                let newMenuItem = jQuery('<li' + ((typeof elV.current !== 'undefined' && elV.current === true)?' class="current"':'') + '><a href="' + elV.url + '"' + ((typeof elV.current !== 'undefined' && elV.current === true)?' class="current" aria-current="page"':'') + '>' + elV.label + '</a></li>');

                if(typeof elV.expand !== 'undefined'){
                    jQuery(elV.expand).attr('class', 'wp-has-submenu wp-has-current-submenu wp-menu-open menu-top menu-icon-wpab_test menu-top-last').children('a').attr('class', 'wp-has-submenu wp-has-current-submenu wp-menu-open menu-top menu-icon-wpab_test menu-top-last');
                }

                if(typeof elV.add_after !== 'undefined'){
                    jQuery(elV.add_after).after(newMenuItem);
                    return;
                }

                if(typeof elV.add_before !== 'undefined'){
                    jQuery(elV.add_before).after(newMenuItem);
                    return;
                }
            });

            if(typeof wpab_sidebar.custom_title !== 'undefined'){
                jQuery('title').text(wpab_sidebar.custom_title + ' ' + jQuery('title').text());
            }
        }
    }
};

jQuery(wpabChart.loadData);