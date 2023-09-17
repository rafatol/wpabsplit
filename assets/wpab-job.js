let wpabJob = {
    testId: null,
    subjectId: null,
    userId: null,
    probeUrl: null,
    initialize: function(){
        wpabJob.testId = parseInt(wpab_vars.test_id);
        wpabJob.subjectId = parseInt(wpab_vars.subject_id);
        wpabJob.userId = wpab_vars.id;
        wpabJob.probeUrl = wpab_vars.probe_url;

        jQuery.fn.isInViewport = function() {
            var elementTop = jQuery(this).offset().top;
            var elementBottom = elementTop + jQuery(this).outerHeight();

            var viewportTop = jQuery(window).scrollTop();
            var viewportBottom = viewportTop + jQuery(window).height();

            return elementBottom > viewportTop && elementTop < viewportBottom;
        };

        wpabJob.addTriggerEvents(wpab_vars.triggers);

        wpabJob.handshake();

        window.onbeforeunload = wpabJob.bye;
    },
    addTriggerEvents: function(availableTriggers){
        jQuery.each(availableTriggers, function(triggerIdx, triggerData){
            if(triggerData.action == 'visible'){
                jQuery(window).on('resize scroll', triggerData.trigger_selector, function(){
                    if(jQuery(this).isInViewport()){
                        wpabJob.registerTriggeredEvent(jQuery(this), triggerData);
                    }
                });

                return;
            }

            jQuery(triggerData.trigger_selector).on(triggerData.js_event, function(){
                wpabJob.registerTriggeredEvent(jQuery(this), triggerData);
            });
        });
    },
    registerTriggeredEvent: function(currentEl, triggerData){
        let eventParams = {userAction: triggerData.action, selector: triggerData.trigger_selector};

        if(currentEl.attr('id')){
            eventParams.id = currentEl.attr('id');
        }

        if(undefined !== triggerData.description_selector){
            if(currentEl.data(triggerData.description_selector)){
                eventParams.description = currentEl.data(triggerData.description_selector);
            }
        }

        if(undefined !== triggerData.description){
            eventParams.description = triggerData.description;
        }

        wpabJob.doPost(eventParams);
    },
    handshake: function(){
        console.log('Hello there');
        wpabJob.doPost({userAction: 'handshake'});
    },
    getParams: function(actions){
        let requestParams = {user_id: wpabJob.userId, subjectId: wpabJob.subjectId, testId: wpabJob.testId, action: 'wpab_probe'};

        jQuery.extend(requestParams, actions);

        return requestParams;
    },
    bye: function(){
        console.log('May the Force be with you');
        wpabJob.doPost({userAction: 'bye'});
    },
    doPost: function(params){
        jQuery.post(wpabJob.probeUrl, wpabJob.getParams(params));
    }
};

jQuery(wpabJob.initialize);