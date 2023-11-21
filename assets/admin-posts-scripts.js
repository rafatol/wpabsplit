jQuery(function(){
    jQuery('.wpab-progress-bar').each(function(){
        let currentEl = jQuery(this);
        let currentVal = currentEl.data('percentage');

        currentEl.find('.progress').css('width', currentVal + '%');
    });

    if(typeof wpab_posts_list != 'undefined'){
        jQuery('li#menu-posts-wpab_test .wp-submenu > li, li#menu-posts-wpab_test .wp-submenu > li > a').removeClass('current');
        jQuery('li#menu-posts-wpab_test a[href~="' + wpab_posts_list.slug_to_activate + '"]').addClass('current').parent().addClass('current');
    }
});