/* admin javascript */
jQuery( document ).ready(function() {
    jQuery('#save_setting_btn').on('click', function(){

        jQuery(this).attr('disabled',true);
        var selected_value = []; // initialize empty array
        jQuery(".element:checked").each(function(){
            selected_value.push(jQuery(this).val());
        });

        jQuery.ajax({
            type:"POST",
            url: "admin-ajax.php",
            data: {action:'awst_settings_ajax', data: selected_value},
            success:function(data){
                console.log(data);
                jQuery('#awSuccess').fadeIn(2000).fadeOut(2000);

                jQuery('#save_setting_btn').delay(2000).removeAttr('disabled');
            }
        });
    });
    return false;
});