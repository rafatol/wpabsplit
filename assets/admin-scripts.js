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
    },
    changeTriggerType: function(){
        let currentEl = jQuery(this);

        if(jQuery('[data-parent="' + currentEl.attr('name') + '"]').length){
            jQuery('[data-parent="' + currentEl.attr('name') + '"]').addClass('hidden');
            jQuery('[data-parent="' + currentEl.attr('name') + '"][data-value="' + currentEl.val() + '"]').removeClass('hidden');
        }
    }
}

jQuery(wpabsplit.initialize);