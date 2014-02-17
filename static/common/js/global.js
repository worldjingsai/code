
// JavaScript Document
$(function(){
	// 得到今天的日期
	if($("span.mlr5") != undefined){
	$("span.mlr5").html(function(){
		var _date = new Date();
		var _week = _date.getDay();
		var weekArray = ['日', '一', '二', '三', '四', '五', '六'];
		var datestr = (_date.getMonth() + 1) + '月' + _date.getDate() + '日  星期' + weekArray[_date.getDay()];
		return datestr;
	});
	};
	
	$(".js_login").click(function(){
	    $(".index_img").hide();
		$(".login_img").show();
		$("#js_login").show("fast");
		$("#js_register").hide("fast");
	  });
	$(".js_register").click(function(){
	    $(".index_img").show();
		$(".login_img").hide();
		$("#js_login").hide("fast");
		$("#js_register").show("fast");
	  });
	
	if($("#register_form").length > 0) {
		// validate the comment form when it is submitted
		$("#register_form").validate({
			rules: {
				username: {
					required:true
				},
				email:{
					required:true,
					email:true
				},
				password:{
					required:true,
					regexPassword:true
				},
				password_c:{
					equalTo:'#password'
				},
				school_name:"required",
				tel:{
					required:true,
					digits:true,
					rangelength:[11,11]
				}
			},
			messages: {
				username: "用户名不能为空",
				email:{
					required:"邮箱不能为空",
					email:"邮箱格式不正确"
				},
				password:{
					required:"密码不能为空",
					regexPassword:"密码至少包一个字母，一个数字，长度至少6位"
				},
				password_c:{
					equalTo:'两次输入的密码不一样请重新输入'
				},
				school_name:"学校不能为空",
				tel:{
					required:"手机号不能为空",
					digits:"手机号格式不正确",
					rangelength:"手机号格式不正确"
				}
			}
		});
		
		jQuery.validator.addMethod("regexPassword", function(value, element) {  
		    return this.optional(element) || /^(?=^.{6,}$)((?=.*\d)|(?=.*\W+))(?=.*[A-Za-z]).*$/.test(value);  
		}, "一个字母，一个数字");
		

	    // bind form using 'ajaxForm' 
	    if($('#content-form').length > 0) {
		    var options = {
		    		beforeSubmit: "ajaxFormStart",  // pre-submit callback
		            success:      "ajaxFormSuccess", // post-submit callback 
		            dataType:     'json'
		    }; 
	        $('#content-form').ajaxForm(options);
	    }
	};

	//$('#reply_content').bind("blur focus keydown keypress keyup", function(){
	//	recount();
	//});
    $("#myform").submit(function(){
		//var submitData = $(this).serialize();
		var comment = $('#reply_content').val();
		var fid = $("#fid").val();
		var is_top = $("#is_top").val();
		if(comment==""){
			$("#msg").show().html("你总得说点什么吧.").fadeOut(2000);
			return false;
		}
		$('.counter').html('<img style="padding:8px 12px" src="Images/load.gif" alt="正在处理..." />');
		$.ajax({
		   type: "POST",
		   url: siteurl+"/comment/add_comment",
		   //data: submitData
		   data:"comment="+comment+"&fid="+fid+"&is_top="+is_top,
		   dataType: "html",
		   success: function(msg){
			  if(parseInt(msg)!=0){
				 $('#saywrap').prepend(msg);
				 $('#reply_content').val('');
				 recount();
				 window.location.reload(true);
			 }else{
				$("#msg").show().html("系统错误.").fadeOut(2000);
				return false;
			 }
		  }
	    });
		return false;
	});
});
                
function recount(){
	var maxlen=140;
	var current = maxlen-$('#reply_content').val().length;
	$('.counter').html(current);

	if(current<1 || current>maxlen){
		$('.counter').css('color','#D40D12');
		$('input.btn btn-small').attr('disabled','disabled');
	}
	else
		$('input.btn btn-small').removeAttr('disabled');

	if(current<10)
		$('.counter').css('color','#D40D12');

	else if(current<20)
		$('.counter').css('color','#5C0002');

	else
		$('.counter').css('color','#cccccc');

}

//	/*添加回复*/
//	$(".clickable").live('click',function(){
//		/*var name=$('a:first',$(this).parent()).text();*/
//		/*var data = $('.clickable').attr("data-mention");*/
//		var name =$('.clickable').attr('data-mention');
//		$('#reply_content').val('@'+name+' ').focus();
//		return false;
//	});
// reply a reply
function replyOne(username){
    replyContent = $("#reply_content");
	oldContent = replyContent.val();
	prefix = "@" + username + " ";
	newContent = ''
	if(oldContent.length > 0){
	    if (oldContent != prefix) {
	        newContent = oldContent + "\n" + prefix;
	    }
	} else {
	    newContent = prefix
	}
	replyContent.focus();
	replyContent.val(newContent);
	moveEnd(replyContent);
}