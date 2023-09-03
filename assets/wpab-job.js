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

        wpabJob.addTriggerEvents(wpab_vars.triggers);

        wpabJob.handshake();

        window.onbeforeunload = wpabJob.bye;
    },
    addTriggerEvents: function(availableTriggers){
        jQuery.each(availableTriggers, function(triggerIdx, triggerData){
            if(triggerData.action == 'visible'){
                /** @todo Checar se o elemento está visível no viewport do usuário */
                return;
            }

            jQuery(triggerData.trigger_selector).on(triggerData.js_event, function(){
                let currentEl = jQuery(this);

                let eventParams = {userAction: triggerData.action, selector: triggerData.trigger_selector};

                if(currentEl.attr('id')){
                    eventParams.id = currentEl.attr('id');
                }

                if(currentEl.data(triggerData.description_selector)){
                    eventParams.description = currentEl.data(triggerData.description_selector);
                }

                wpabJob.doPost(eventParams);
            });
        });
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