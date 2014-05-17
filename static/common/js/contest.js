function lxfEndtime(){
	$("#lxftime").each(function(){
		
		var endtime = new Date($(this).attr("endtime").replace(/-/g,'/')).getTime();//取结束日期(毫秒值)
		var nowtime = new Date().getTime(); //今天的日期(毫秒值)
		var youtime = endtime-nowtime; //还有多久(毫秒值)
		var seconds = youtime/1000;
		var minutes = Math.floor(seconds/60);
		var hours = Math.floor(minutes/60);
		var days = Math.floor(hours/24);
		var CDay= days ;
		var CHour= hours % 24;
		var CMinute= minutes % 60;
		var CSecond= Math.floor(seconds%60); //"%"是取余运算，可以理解为60进一后取余数，然后只要余数。

		if(isNaN(days)) {
			$(this).html("竞赛时间未知");
		}else if(endtime <= nowtime){
			$(this).html("竞赛已开始") //如果结束日期小于当前日期就提示过期啦
		}else{
			if($(this).attr("showdetailtime")=="no"){
				$(this).html("距竞赛开始还有 <b>"+days+"</b> 天"); //输出没有天数的数据
			} else {
				$(this).html("距竞赛开始还有 <b>"+days+"</b> 天<b>"+CHour+"</b>时<b>"+CMinute+"</b>分<b>"+CSecond+"</b>秒"); //输出有天数的数据
				setTimeout("lxfEndtime()",1000);
			}
		}
	});
};

// 提交表单开始
function ajaxFormStart() {
	ajax_message('提交中...');
}

// 提交表单成功
function ajaxFormSuccess(responseText, statusText) {
	if (statusText == 'success') {
		if (responseText.code == 0) {
			if (responseText.message != '') {
				ajax_message(responseText.message);
				if (responseText.data.return_url != undefined) {
					
					setTimeout(function(){window.location.href=responseText.data.return_url}, responseText.data.show_time||1000);
				}
			}
		} else {
			if (responseText.message != '') {
				ajax_message(responseText.message);
			} else {
				ajax_message('程序错误，错误代码' + responseText.code);
			}
		}
		setTimeout(function(){$('#ajaxMessage').dialog('close')}, responseText.data.show_time||1000);
	} else {
		ajax_message('系统错误' + statusText);
	}
}

// 
function ajax_message($msg, width) {
	if ($('#ajaxMessage').length == 0) {
		var div = '<div class="" id="ajaxMessage" ></div>';
		$(document.body).append(div); 
	}
	$('#ajaxMessage').html($msg);
	$('#ajaxMessage').dialog({
	    autoOpen: true,//如果设置为true，则默认页面加载完毕后，就自动弹出对话框；相反则处理hidden状态。 
	    bgiframe: true, //解决ie6中遮罩层盖不住select的问题  
	    width: width||300,
	    modal:true,//这个就是遮罩效果   
	    resizable:false,
	    dialogClass: 'alert'
	});
}

$(document).ready(function(){

	if($("#content-form").length > 0) {
		// validate the comment form when it is submitted
		$("#content-form").validate({
			rules: {
				title: "required"
			},
			messages: {
				title: "标题不能为空"
			}
		});
		
	    var options = {
	    		beforeSubmit: ajaxFormStart,  // pre-submit callback
	            success:      ajaxFormSuccess, // post-submit callback 
	            dataType:     'json'
	    }; 

	    // bind form using 'ajaxForm' 
	    $('#content-form').ajaxForm(options); 
	};
	if ($("#lxftime").length > 0){
		lxfEndtime();
	};
	
	if ($(".js_del").length > 0) {
		$(".js_del").on('click', function(){
			
			var hideLi = $(this).parent().parent();
			var url = $(this).attr('url') || '';
			var notice = $(this).attr('notice') || ''
			
			if ($('#promptMessage').length == 0) {
				var div = '<div class="" id="promptMessage" ></div>';
				$(document.body).append(div); 
			} else {
				$('#promptMessage').dialog('open');
			}
			$('#promptMessage').html("确定要删除“" + notice + "”吗");
			$('#promptMessage').dialog({
			    autoOpen: true,
			    bgiframe: true, 
			    width: 400,
			    modal:true,
			    resizable:false,
			    dialogClass: 'prompt',
			    title:'提示信息',
			    buttons : {
			    	"确定" : function(){
			    		$.ajax({
							url: url,
							type: "POST",
							dataType:"json",
							success: function(responseText){
								if (responseText.code == 0) {
									if (responseText.message != '') {
										$('#promptMessage').html(responseText.message);
										$('#promptMessage').dialog("close");
										hideLi.hide('slow');
									}
								} else {
									$('#promptMessage').html('返回错误' + responseText.message);
								}
						    },
						    error : function() {
						    	$('#promptMessage').html('系统错误，请重试!');
						    }
						});
			    		},
			    	"取消" : function(){$( this ).dialog( "close" );return false;}
			    }
			});
			
		})
	};
	
	if ($("#create_apply").length > 0) {
		$("#create_apply").validate({
			rules: {
				session: {
					required:true,
					digits:true
				},
				base_number: {
					required: true,
					digits:true
				},
				min_member: {
					required: true,
					digits:true
				},
				max_member: {
					required: true,
					digits:true
				},
				fee: {
					required:false,
					digits:true
				}
			},
			messages: {
				session: {
					required:'届数不能为空',
					digits:'请填写数字'
				},
				base_number: {
					required: '基数不能为空',
					digits:'请填写数字'
				},
				min_member: {
					required: '最少参赛人数不能为空',
					digits:'请填写数字'
				},
				max_member: {
					required: '最多参赛人数不能为空',
					digits:'请填写数字'
				},
				fee: {
					digits:'请填写数字'
				}
			}
		});
		
		var options = {
	    		beforeSubmit: ajaxFormStart,  // pre-submit callback
	            success:      ajaxFormSuccess, // post-submit callback 
	            dataType:     'json'
	    }; 

	    // bind form using 'ajaxForm' 
	    $('#create_apply').ajaxForm(options); 
	};
	
	$('.js_reply').click(function(){
		if($(this).parents(".toolbar").next(".post_box").length>0) {
			$(this).parents(".toolbar").next(".post_box").remove();
		}else {
			var id = new Date().getTime();
			$(this).parents(".comment_box").append($("#post_box").html().replace(/js_comment/, 'js_comment js_'+id));
			// 重新注册一下事件
			$(".js_"+id).validate({});
			var options = {
		    		beforeSubmit: ajaxFormStart,  // pre-submit callback
		            success:      ajaxFormSuccess, // post-submit callback 
		            dataType:     'json'
		    }; 
		    // bind form using 'ajaxForm' 
		    $('.js_comment').ajaxForm(options); 
		}
	});

	$(".js_table  tr:nth-child(even)").css({"background-color":"#E4F5FF"});
	$(".js_table tr").mouseover(function(){  
        $(this).css({"background-color":"#87CEEB"});  
     }).mouseout(function(event){  
        var $index=$(this).index();  
        if($index%2==0){  
            $(this).css({"background-color":"#fff"});  
        }else{  
            $(this).css({"background-color":"#E4F5FF"});  
        }  
     }); 
	
	$('.js_add_data').click(function(){
		var max = $("#max_member").val();
		var num = $(".team_member > .js_mem_info").length;

		if (num >=max) {
			alert('已达到最多会员数量不能添加了');
		} else {
			var htm = $("#js_add_data").html().replace(/{i}/g, num+1)
			$(".team_member").append(htm);

		}
	});
	
	$('.js_del_data').click(function(){
		var min = $("#min_member").val();
		var num = $(".team_member > .js_mem_info").length;

		if (num <= min) {
			alert('已达到最少会员数量不能删除了');
		} else {
			 $(".team_member > .js_mem_info:last").remove();
		}
	});
	
	if ($("#js_user_apply").length > 0) {
		$("#js_user_apply").validate({
		});
		
		var options = {
	    		beforeSubmit: ajaxFormStart,  // pre-submit callback
	            success:      ajaxFormSuccess, // post-submit callback 
	            dataType:     'json'
	    }; 

	    // bind form using 'ajaxForm' 
	    $('#js_user_apply').ajaxForm(options); 
	};
	
	
	if($('.js_comment').length > 0) {
		$(".js_comment").validate({});
		
		var options = {
	    		beforeSubmit: ajaxFormStart,  // pre-submit callback
	            success:      ajaxFormSuccess, // post-submit callback 
	            dataType:     'json'
	    }; 
	    // bind form using 'ajaxForm' 
	    $('.js_comment').ajaxForm(options); 
	}
	
	if($('.js_branch').length > 0) {
		$('.js_branch').on('click', function(){
			$url = $('.js_branch').attr('uri');
			$.ajax({
				url: $url,
				type: 'GET',
				data:'',
				dataType: 'json',
				timeout: 1000,
				beforeSend:function(){
					ajax_message('查询中...');
				},
				error: function(){ajax_message('查询错误');},
				success: function(result){
					console.log(result);
					if(result.code==0)
						ajax_message(result.data.html, 900);
					}
				});
		});
		
		/*$.ajax({
			url: $("#selfPostForm").attr('action'),
			type: 'POST',
			data:selfFormData,
			dataType: 'json',
			timeout: 1000,
			error: function(){alert('Error loading PHP document');},
			success: function(result){alert(result.message); location.reload();}
			});
		$(".js_comment").validate({});
		
		var options = {
	    		beforeSubmit: ajaxFormStart,  // pre-submit callback
	            success:      ajaxFormSuccess, // post-submit callback 
	            dataType:     'json'
	    }; 
	    // bind form using 'ajaxForm' 
	    $('.js_comment').ajaxForm(options); */
	}
	
});