var wpabsplit = {
    initialize: function(){
        let controlPageSelect = jQuery('select#wpab_control_page');
        controlPageSelect.select2();
        controlPageSelect.on('change.select2', wpabsplit.loadHypothesis);

        jQuery('select#wpab_hypothesis_page').select2();

        if(controlPageSelect.val()){
            controlPageSelect.trigger('change');
        }

        if(jQuery('.wpab-progress-bar').length){
            jQuery('.wpab-progress-bar').each(function(){
                let currentEl = jQuery(this);
                let currentVal = currentEl.data('percentage');

                currentEl.find('.progress').css('width', currentVal + '%');
            });
        }

        jQuery('button[data-action="toggle"]').on('click', function(e){
            e.preventDefault();

            let currentEl = jQuery(this);
            let currentWrapper = jQuery(currentEl.data('target')).closest('.more-info');

            if(currentWrapper.find('.hidden-content').hasClass('hidden')){
                currentWrapper.find('.hidden-content').removeClass('hidden');
                return;
            }

            currentWrapper.find('.hidden-content').addClass('hidden');
        });

        jQuery('.more-info button').on('click', function(e){
            e.preventDefault();

            let currentWrapper = jQuery(this).closest('.more-info');

            if(currentWrapper.find('.hidden-content').hasClass('hidden')){
                currentWrapper.find('.hidden-content').removeClass('hidden');
                return;
            }

            currentWrapper.find('.hidden-content').addClass('hidden');
        });

        jQuery('input[name="trigger_selector"]').on('change', function(e){
            let thisEl = jQuery(this);

            const isSelectorValid = ((dummyElement) =>
                (selector) => {
                    try { dummyElement.querySelector(selector) } catch { return false }
                    return true
                })(document.createDocumentFragment())

            if(thisEl.val() != '' && !isSelectorValid(thisEl.val())){
                wpabsplit.errorToast(thisEl.data('invalid').replace('%s', '"' + thisEl.val() + '"'));
                thisEl.val('').focus();
            }
        });

        if(typeof wpab_sidebar !== 'undefined'){
            jQuery.each(wpab_sidebar.custom_menu, function(elI, elV){
                let newMenuItem = jQuery('<li><a href="' + elV.url + '">' + elV.label + '</a></li>');

                if(typeof elV.add_after !== 'undefined'){
                    jQuery(elV.add_after).after(newMenuItem);
                    return;
                }

                if(typeof elV.add_before !== 'undefined'){
                    jQuery(elV.add_before).after(newMenuItem);
                    return;
                }
            });
        }
    },
    loadHypothesis: function(e){
        let currentEl = jQuery(this);
        let currentVal = currentEl.val();

        let targetSelect = jQuery('select#wpab_hypothesis_page');

        jQuery.get(currentEl.data('probe'), {page_id: currentVal, current_id: jQuery('#post_ID').val(), method: 'hypothesis', action: 'wpab_probe'}, function(data){
            targetSelect.html('');
            jQuery.each(data, function(elI, elV){
                targetSelect.append('<option value="' + ((null !== elV.id)?elV.id:'') + '"' + ((elV.selected)?' selected':'') + '>' + elV.label + '</option>');
            });

            targetSelect.trigger('change');
        });
    },
    updateFieldName: function(tbody){
        tbody.find('tr').each(function(idx, el){
            jQuery(el).find('[data-name]').each(function(idx2, el2){
                let currentEl = jQuery(el2);

                currentEl.attr('name', currentEl.data('name').replace('@', idx));
                currentEl.attr('id', currentEl.data('id').replace('@', idx));
            });
        });
    },
    errorToast: function(errorText, errorTitle){
        let toastOpt = {
            text: errorText,
            icon: 'error',
            loader: true
        };

        if(undefined !== errorTitle){
            toastOpt.heading = errorTitle;
        }

        jQuery.toast(toastOpt);
    }
}

jQuery(wpabsplit.initialize);