function lxfEndtime(){
	$("#lxftime").each(function(){
		var endtime = new Date($(this).attr("endtime")).getTime();//取结束日期(毫秒值)
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
		if(endtime <= nowtime){
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
$(document).ready(function(){
	if ($("#lxftime") != undefined){
		lxfEndtime();
	};
});