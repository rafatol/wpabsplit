var wpabsplit = {
    initialize: function(){
        jQuery('select[name="trigger_type"]').on('change', wpabsplit.changeTriggerType);
        jQuery('select[name="trigger_type"]').trigger('change');

        jQuery('.more-info button').on('click', function(e){
            e.preventDefault();

            let currentWrapper = jQuery(this).closest('.more-info');

            if(currentWrapper.find('.hidden-content').hasClass('hidden')){
                currentWrapper.find('.hidden-content').removeClass('hidden');
                return;
            }

            currentWrapper.find('.hidden-content').addClass('hidden');
        });

        jQuery('#ab_test_triggers').on('click', 'button[data-action]', function(e){
            let thisEl = jQuery(this);

            switch (thisEl.data('action')) {
                case 'delete':
                        wpabsplit.deleteTrigger.call(this, e);
                    break;
                case 'add':
                        wpabsplit.addTrigger.call(this, e);
                    break;
            }
        });

        jQuery('#ab_test_triggers').on('change', 'input[data-name="trigger[@][selector]"]', function(e){
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
    },
    changeTriggerType: function(){
        let currentEl = jQuery(this);

        if(jQuery('[data-parent="' + currentEl.attr('name') + '"]').length){
            jQuery('[data-parent="' + currentEl.attr('name') + '"]').addClass('hidden');
            jQuery('[data-parent="' + currentEl.attr('name') + '"][data-value="' + currentEl.val() + '"]').removeClass('hidden');
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