window.tutor_ajax_counter = 0;

window.jQuery(document).ajaxStart(function() {
    window.tutor_ajax_counter++;
});

window.jQuery(document).ajaxStop(function() {
    window.tutor_ajax_counter--;
});


window.jQuery(document).ready(function($) {
    
    var ajax_resolver = function() {
        setTimeout(function() {
            if(window.tutor_ajax_counter>0) {
                ajax_resolver();
                return;
            }

            var form = $('#tutor-course-topics');
            if(!form.find('[name="tutor_wpml_copy_source"]').length) {
                form.append('<input type="hidden" name="tutor_wpml_copy_source" value="1"/>');
            }    

            if($('#save-post').length) {
                $('#save-post').trigger('click');
            } else if($('#publish').length) {
                $('#publish').trigger('click');
            } else if($('.editor-post-save-draft').length) {

                $('.editor-post-save-draft').trigger('click');

                wp.data.subscribe(function () {
                  
                    var reloader = function () {
                        setTimeout(function () {

                            var isSavingPost = wp.data.select('core/editor').isSavingPost();
                            
                            if(isSavingPost) {
                                reloader();
                                return;
                            }

                            window.location.reload();
                            
                        }, 1000);
                    }
                    

                    var isSavingPost = wp.data.select('core/editor').isSavingPost();
                    var isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();
                    
                    if (isSavingPost && !isAutosavingPost) {
                        reloader();
                    }
                });
            }
            
        }, 1000);
    }

    var importer_button = $('#icl_div #icl_cfo').get(0);

    if(importer_button) {
        importer_button.addEventListener('click', function() {
            ajax_resolver();
        });
    }
});