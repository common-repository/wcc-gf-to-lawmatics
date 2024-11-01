jQuery(document).ready(function(){
    
    jQuery(document).delegate(".wcc-switch-status","click",function(e){
        $this = jQuery(this);
        var status = 0;
        if(jQuery(this).prop("checked")){
          status = 1;
        }
        jQuery.ajax({
          url: ajaxurl,
          data:{
            action : "wcc_gf_lawmatics_status",
            nonce: ajax.nonce, 
            id:$this.data("id"),
            status:status
          },
          type:"post",
          dataType:"json",
          success:function(json){
            if(json['error']){
              alert("Something Wrong Try Again");
            }
            if(json['success']){
              if(!$this.hasClass("starred")){
                $this.addClass("starred");
              }else{
                $this.removeClass("starred");
              }
            } 
          }
        })
    });
    jQuery(document).delegate(".form_value_selector","change",function (e) {
        if(jQuery(this).parents(".connector_setting_block").find(".maping_field_type").val() == "custom"){
            input = jQuery(this).parents(".connector_setting_block").find(".wcc_custom_value textarea");
            val = input.val();
            input.val(val+" {"+jQuery(this).val()+"}")
            jQuery(this).val("");
        }
    })
    jQuery(document).delegate(".maping_field_type","change",function (e) {
        if(jQuery(this).val() == "custom"){
            jQuery(this).parents(".connector_setting_block").find(".wcc_custom_value").show();
            jQuery(this).parents(".connector_setting_block").find(".form_value_selector").val("");
        }else{
            jQuery(this).parents(".connector_setting_block").find(".wcc_custom_value").hide();
        }
    })
    jQuery(document).delegate("#wcc_add_maping_val","click",function (e) {
        e.preventDefault()
        i = jQuery("#connector_fields").val();
        if(i){
            option = jQuery("#connector_fields option[value='"+i+"']");

            hidden_connector_setting = jQuery(".hidden_connector_setting").html();
            hidden_connector_setting = hidden_connector_setting.replace(/{{connector_field}}/g, i);
            html_var = jQuery(hidden_connector_setting);
            html_var.data("maping_key",i);
            html_var.find(".connector_field_name").html(option.text());
            html_var.find(".connector_field_name_info").html("Name: "+i+" , Type: "+option.data("type")+" , Max Length: 18");
            option.attr("disabled","disabled");
            jQuery("#connector_fields").val("");
            jQuery(".maping_fields").append(html_var[0].outerHTML);
        }
    })
    jQuery(document).delegate(".removeMaping","click",function (e) {
        connector_setting_block = jQuery(this).parents(".connector_setting_block");
        console.log(connector_setting_block.data("maping_key"))
        jQuery("#connector_fields option[value='"+connector_setting_block.data("maping_key")+"']").removeAttr("disabled")
        connector_setting_block.remove();
    })
    jQuery("#wcc_gf_lawmatics_module,#forms").change(function (e) {
        if(jQuery("#wcc_gf_lawmatics_module").val() && jQuery("#forms").val() && jQuery("#accounts").val()){
            var accounts = jQuery("#accounts").val()
            var forms = jQuery("#forms").val()
            var wcc_gf_lawmatics_module = jQuery("#wcc_gf_lawmatics_module").val()
            jQuery(".maping_fields").html("");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    accounts:accounts,
                    forms:forms,
                    nonce: ajax.nonce, 
                    wcc_gf_lawmatics_module:wcc_gf_lawmatics_module,
                    action:"wcc_gf_lawmatics_get_module_fields_and_form_field"
                },
                success: function (data) {
                    html = "<option value=''>Select Field</option>";
                    if(data['form_fields']){
                        jQuery.each(data['form_fields'],function(i,j){
                            html += "<option value='"+i+"''>"+j+"</option>";
                        })
                    }
                    console.log(html)
                    jQuery(".hidden_connector_setting .form_value_selector").html(html);
                    jQuery(".maping_fields").html("");
                    html = "<option value=''>Select Field</option>";
                    if(data['connector_fields']){
                        jQuery.each(data['connector_fields'],function(i,j){
                            disable = "";
                            if(j['required']){
                                disable = "disabled='disabled'";
                                hidden_connector_setting = jQuery(".hidden_connector_setting").html();
                                hidden_connector_setting = hidden_connector_setting.replace(/{{connector_field}}/g, i);
                                html_var = jQuery(hidden_connector_setting);
                                html_var.find(".removeMaping").remove();
                                html_var.find(".connector_field_name").html(j['label']+" <span class='wcc-text-danger'>(Required)</span>");
                                html_var.find(".connector_field_name_info").html("Name: "+i+" , Type: "+j['type']); 
                                jQuery(".maping_fields").append(html_var[0].outerHTML);
                            }
                            html += "<option data-type='"+j['type']+"' "+disable+" value='"+i+"''>"+j['label']+"</option>";
                        })
                    }
                    //console.log(html)
                    jQuery("#connector_fields").html(html);
                    jQuery(".connector_map_fields").show();
                }
            });
        }
    });

    
    var get_field = function(){
        var accounts = jQuery("#accounts").val()
        var wcc_gf_lawmatics_module = jQuery("#wcc_gf_lawmatics_module").val()
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {wcc_gf_lawmatics_module:wcc_gf_lawmatics_module,accounts:accounts,action:"wcc_gf_lawmatics_get_module_fields",nonce: ajax.nonce, },
            success: function (data) {
                jQuery(".field_info").html(data)
            }
        });            
    }
    jQuery(document).delegate("#wcc_gf_lawmatics_module,#accounts","change",function(){
        get_field();
    })
})