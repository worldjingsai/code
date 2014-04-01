var selectSchool = function(){
//弹出窗口
	this.pop = function(){
		var province_id= arguments[0] || 1;
		var link= arguments[1] || false;
		//将窗口居中
		makeCenter();

		//初始化省份列表
		initProvince(link);

		//默认情况下, 给第一个省份添加choosen样式
		$('[province-id='+province_id+']').addClass('choosen');

		//初始化大学列表
		initSchool(province_id, link);
	};

	var initProvince =function(){
		var link= arguments[0] || false;
		//原先的省份列表清空
		$('#choose-a-province').html('');
		
		for(i=0;i<schoolList.length;i++){
			$('#choose-a-province').append('<a href="javascript:void(0);" class="province-item" province-id="'+schoolList[i].id+'">'+schoolList[i].name+'</a>');
		}
		
		//添加省份列表项的click事件
		$('.province-item').bind('click',function(){
			var item=$(this);
			var province = item.attr('province-id');
			var choosenItem = item.parent().find('.choosen');
			if(choosenItem)
			$(choosenItem).removeClass('choosen');
			item.addClass('choosen');
			
			//更新大学列表
			initSchool(province, link);
		});
	};

	var initSchool=function(provinceID, link){

		//原先的学校列表清空
		$('#choose-a-school').html('');
		var schools = schoolList[provinceID-1].school;
		for(i=0;i<schools.length;i++){
			if (link) {
				$('#choose-a-school').append('<a target="_blank" href="/'+schools[i].short_name+'" class="school_close" school-id="'+schools[i].id+'">'+schools[i].name+'</a>');
			} else {
				$('#choose-a-school').append('<a href="javascript:void(0);" class="school-item" school-id="'+schools[i].id+'">'+schools[i].name+'</a>');
			}
		}
		
		//添加大学列表项的click事件
		$('.school-item').bind('click', function(){
			var item=$(this);
			var school = item.attr('school-id');

			//更新选择大学文本框中的值
			$('#school_name').val(item.text());
			$('#univs_id').val(school);

			//关闭弹窗
			hide();
		});
		
		//添加大学列表项的click事件
		$('.school_close').bind('click', function(){
			//关闭弹窗
			hide();
		});
	};
	
	var hide = function(){
		$('#choose-box-wrapper').css("display","none");
	}

	var makeCenter =function(){
		$('#choose-box-wrapper').css("display","block");
		$('#choose-box-wrapper').css("position","absolute");
		$('#choose-box-wrapper').css("top", Math.max(0, (($(window).height() - $('#choose-box-wrapper').outerHeight()) / 2) + $(window).scrollTop()) + "px");
		$('#choose-box-wrapper').css("left", Math.max(0, (($(window).width() - $('#choose-box-wrapper').outerWidth()) / 2) + $(window).scrollLeft()) + "px");
	}

}

$(document).ready(function(){
	$("#school_name").on("blur", function(){
		if(this.value==''){this.value='请选择大学'}
	});
	
	$("#school_name").on("focus", function(){
		if(this.value=='请选择大学'){this.value=''};
	});

	var se = new selectSchool();
	$("#school_name").on("click",function(){
		se.pop();
		$("#js_schoole_select").css("display", "");
		});
	
	$(".js_morechool").on("click", function(){
		$("#js_schoole_select").css("display", "none");
		var item=$(this);
		var provid = item.attr('porvince_id');
		var link = true;
		se.pop(provid, link);
		});
	//隐藏窗口
	$('#closeSchoole').on("click",function(){
		$('#choose-box-wrapper').css("display","none");
	});
	//选择窗口
	$('#selectOtherSchoole').on("click",function(){
		$('#choose-box-wrapper').css("display","none");
		var sname = function(){
			return $('#otherSchoole').val();
		};
		$("#school_name").val(sname);
		$('#univs_id').val('');
	});
})
