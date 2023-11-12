jQuery(function(){
    jQuery('.wpab-progress-bar').each(function(){
        let currentEl = jQuery(this);
        let currentVal = currentEl.data('percentage');

        currentEl.find('.progress').css('width', currentVal + '%');
    });
});