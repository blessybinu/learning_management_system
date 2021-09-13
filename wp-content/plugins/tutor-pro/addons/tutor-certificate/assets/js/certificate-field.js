jQuery(document).ready(function($){
    'use strict';

    $(document).on('click', '.tutor-certificate-template', function(){
        $('.tutor-certificate-template').removeClass('selected-template');
        $(this).addClass('selected-template');
    });
});