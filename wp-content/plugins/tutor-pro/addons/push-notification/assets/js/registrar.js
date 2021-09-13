'use strict';

window.jQuery(document).ready(function($){

    const { __, _x, _n, _nx } = wp.i18n;
    
    if(!('serviceWorker' in navigator))
    {
        console.warn('Service Worker not supported');
        return;
    }

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=" + window._tutorobject.base_path;
      }
      
      function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
            c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
          }
        }
        return "";
      }
      
    function urlBase64ToUint8Array(base64String) 
    {
        const padding = "=".repeat((4 - base64String.length % 4) % 4)
        // eslint-disable-next-line
        const base64 = (base64String + padding).replace(/\-/g, "+").replace(/_/g, "/")
    
        const rawData = window.atob(base64)
        const outputArray = new Uint8Array(rawData.length)
    
        for (let i = 0; i < rawData.length; ++i) 
        {
            outputArray[i] = rawData.charCodeAt(i)
        }
        return outputArray
    }
    
    function subscribeUser(convertedVapidKey, user_clicked, callback) 
    {
        navigator.serviceWorker.ready.then(function(registration) 
        {
            // Check if supports PN.
            if (!registration.pushManager)
            {
                setCookie('tutor_pn_dont_ask', 'yes', 365);
                alert(__('This browser does not support push notification', 'tutor-pro'));
                return;
            }
    
            // Try to get existing subscription.
            registration.pushManager.getSubscription().then(function(existedSubscription) 
            {
                if(existedSubscription === null) 
                {
                    // Create new subscription
                    registration.pushManager.subscribe
                    ({
                        applicationServerKey: convertedVapidKey,
                        userVisibleOnly: true,
                    }).then(function(newSubscription) 
                    {
                        setTimeout(function(){
                            if(navigator.userAgent.indexOf('Mac OS X') && user_clicked) {
                                alert( __( 'Thanks! Please make sure browser notification is enbled in notification settings.', 'tutor-pro' ) );
                            }
                        }, 1);

                        callback(newSubscription, registration, true);
                        
                    }).catch(function(e) 
                    {
                        console.warn(Notification.permission !== 'granted' ? 'PN Permission denied' : 'PN subscription error');
                    });
                } 
                else 
                {
                    // Use existing subscription
                    callback(existedSubscription, registration);
                }
            })
        }).catch(function(e) 
        {
            console.error('Service Worker error', e)
        });
    }

    function init_subscriber(user_clicked) {
        // Request push notification permission
        subscribeUser(urlBase64ToUint8Array(window._tutorobject.tutor_pn_vapid_key), user_clicked, function(subscription, registration, force_save) {
                    
            // Set the current user id for this browser
            registration.active.postMessage(JSON.stringify({
                client_id: window._tutorobject.tutor_pn_client_id, 
                browser_key: getCookie('tutor_pn_browser_key')
            }));

            if(window._tutorobject.tutor_pn_client_id==0 || (!force_save && window._tutorobject.tutor_pn_subscription_saved=='yes')) {
                return;
            }
            
            $.ajax({
                url: window._tutorobject.ajaxurl,
                type: 'POST',
                async: true,
                data: {
                    action:'tutor_pn_save_subscription', 
                    subscription: JSON.stringify(subscription)
                }
            });
        });
    }

    // Register service worker
    navigator.serviceWorker.register(window._tutorobject.home_url + '/tutor-push-notification.js').then(function(registration) {
        
        if(Notification.permission == 'denied') {
            return;
        }

        if(!window._tutorobject.tutor_pn_vapid_key) {
            console.warn('Vapid key could not be generated.');
            return;
        }
        
        if(Notification.permission == 'granted' ) {
            init_subscriber();
            return;
        }
        
        var container = $('#tutor-pn-permission');
        if( container.length && window._tutorobject.tutor_pn_client_id > 0 && !getCookie('tutor_pn_dont_ask')) {

            // Show the toast first
            container.show().css({'display':'block'}).animate({'bottom': '0px'}, 1000);

            // Enabale handler
            container.find('#tutor-pn-enable').click(function() {
                init_subscriber(true);
            });

            // Don't ask handler
            container.find('#tutor-pn-dont-ask').click(function() {
                setCookie('tutor_pn_dont_ask', 'yes', 365);
            });
            
            // Hide toast on all actions ultimately
            container.find('#tutor-pn-enable, #tutor-pn-close, #tutor-pn-dont-ask').click(function() {
                container.hide();
            });
        }
        
    }).catch(function(e) {
        console.warn('Tutor PN Service Worker registration failed', e);
    });
});