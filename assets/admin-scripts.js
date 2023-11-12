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
    changeTriggerType: function(){
        let currentEl = jQuery(this);

        if(jQuery('[data-parent="' + currentEl.attr('name') + '"]').length){
            jQuery('[data-parent="' + currentEl.attr('name') + '"]').addClass('hidden');
            jQuery('[data-parent="' + currentEl.attr('name') + '"] [name]').prop('required', false);

            jQuery('[data-parent="' + currentEl.attr('name') + '"][data-value="' + currentEl.val() + '"]').removeClass('hidden');
            jQuery('[data-parent="' + currentEl.attr('name') + '"][data-value="' + currentEl.val() + '"] [name]').prop('required', true);
        }
    },
    addTrigger: function(e){
        e.preventDefault();

        let tbody = jQuery(this).closest('tbody');

        jQuery('<tr>\n' +
            '                <td><input type="text" name="trigger[@][selector]" id="trigger-@-selector" data-name="trigger[@][selector]" data-id="trigger-@-selector" data-invalid="%s is not a valid selector" required></td>\n' +
            '                <td>\n' +
            '                    <select name="trigger[@][type]" id="trigger-@-type" data-name="trigger[@][type]" data-id="trigger-@-type" required>\n' +
            '                        <option value="click">Click</option>\n' +
            '                        <option value="submit">Enviar</option>\n' +
            '                        <option value="mousein">Mouse In</option>\n' +
            '                        <option value="mouseout">Mouse Out</option>\n' +
            '                        <option value="visible">Visível</option>\n' +
            '                    </select>\n' +
            '                </td>\n' +
            '                <td><input type="text" name="trigger[@][description]" id="trigger-@-description" data-name="trigger[@][description]" data-id="trigger-@-description" required></td>\n' +
            '                <td><button type="button" data-action="add">Adicionar</button> <button type="button" data-action="delete">Remover</button></td>\n' +
            '            </tr>').appendTo(tbody);

        wpabsplit.updateFieldName(tbody);
    },
    deleteTrigger: function(e){
        e.preventDefault();

        let currentRow = jQuery(this).closest('tr');
        let tbody = currentRow.closest('tbody');

        if(tbody.find('tr').length == 1){
            return;
        }

        currentRow.remove();

        wpabsplit.updateFieldName(tbody);
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