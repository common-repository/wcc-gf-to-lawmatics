jQuery(document).delegate(".wcc_more_detail","click",function(){

  jQuery(".wcc-data-response-data").html(jQuery(this).parents("tr").find(".wcc_response_data").html());
  jQuery(".wcc-data-sent-data").html(jQuery(this).parents("tr").find(".wcc_sent_data").html());
  jQuery(".wcc-data-more-data").html(jQuery(this).parents("tr").find(".wcc_more_data").html());
  wcc_model_open("viewDataModal")
})


var wcc_model_open = function(id){
  document.querySelector('#'+id).classList.toggle('open');jQuery('body').append('<div class=\'modal-backdrop fade show\'></div>').addClass('modal-open');
}
var wcc_model_close = function(){
    if(jQuery(".wcc-modal.fade.open").length){
      jQuery('.wcc-modal').removeClass('open');
      jQuery(".modal-backdrop").remove();
      jQuery("body").removeClass("modal-open");
    }
}
jQuery(document).ready(function(){
  jQuery(".close_model").click(function(){
    wcc_model_close();
  });
});
window.onclick = function(event) {
  if (event.target.matches('.wcc-modal')) {
    wcc_model_close();
  }
  if (!event.target.matches('.wcc-dropbtn')) {
    var dropdowns = document.getElementsByClassName("wcc-dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('wcc-show')) {
        openDropdown.classList.remove('wcc-show');
      }
    }
  }
}


jQuery(document).ready(function(){

  jQuery("#doexport").click(function(e){
    e.preventDefault();
    jQuery("#wcc_gf_lawmatics_log_export_data").val("1");
    jQuery("#search_form").submit();
    jQuery("#wcc_gf_lawmatics_log_export_data").val("");
  });
    
  jQuery(".wcc-date-picker").datepicker({
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    yearRange: "-100:+10",
    dateFormat: 'yy-mm-dd'
  });
})
jQuery(document).delegate("select[name='filter_time']","change",function(e){
  console.log(jQuery(this).val())
  if(jQuery(this).val() == "custom"){
    jQuery(".custom_range_fields").addClass("wcc-inline-block");
  }else{
    jQuery("input[name='filter_start_date']").val("");
    jQuery("input[name='filter_end_date']").val("");
    jQuery(".custom_range_fields").removeClass("wcc-inline-block");
  }
})
jQuery(document).delegate(".wcc_more_detail","click",function(e){

})
jQuery(document).delegate(".starred_btn","click",function(e){
    e.preventDefault();
    $this = jQuery(this);
    var favorite = 0;
    if(!jQuery(this).hasClass("starred")){
      favorite = 1;
    }
    jQuery.ajax({
      url: ajaxurl,
      data:{
        action : "wcc_gf_lawmatics_favorite",
        id:$this.data("id"),
        favorite:favorite,
        nonce: ajax.nonce, 
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