<!DOCTYPE html><html><head><meta content='' name='description'>
<meta charset='UTF-8'>
<meta content='True' name='HandheldFriendly'>
<meta content='width=device-width, initial-scale=1.0' name='viewport'>
<title><?php echo $title?> - 我的竞赛 - <?php echo $settings['site_name']?></title>
<?php $this->load->view('header-meta');?>
<link  rel='stylesheet' type='text/css' href='/static/common/css/jquery-ui-1.10.4.custom.min.css' >
</head>
<body id="startbbs">
<?php $this->load->view('header');?>
<div id="wrap">
<div class="container" id="page-main">
<div class="row">
<?php $this->load->view('mycontest_left');?>
<div class='col-xs-12 col-sm-6 col-md-10'>

<div class='box'>
<div class='cell'>
<a href="<?php echo site_url('mycontest/index');?>">我的创建的竞赛</a>
<span class="chevron">&nbsp;›&nbsp;</span> <?php echo sb_substr(strip_tags($contest['contest_name']), 19)?>
</div>

<div class='cell'>
<?php if (empty($conf)) {?>
没有报名系统
<?php } else {?>
    <ul class="nav nav-tabs" style="margin-top:10px;">
    <li class="active"><a href="#">团队信息</a></li>
    <li><a href="<?php echo site_url('mycontest/my_team_list_delete/'.$contest['contest_id']);?>">取消报名团队</a></li>
    </ul>
<div class="cell">

<form method="get"  class="form-horizontal" action="<?php echo site_url('mycontest/my_team_list/'.$conf['contest_id'])?>">
  			<fieldset>


  			    <?php if($conf['is_checked']) {?>
  			    <div class="form-group">
      				<label class="col-sm-2 control-label" >是否审核通过</label>
      				<div class="col-sm-2">
       					<select name="is_checked" class="form-control" >
                        <option value="-1">全部</option>
                        <option value="1" <?php if(isset($gets['is_checked']) && $gets['is_checked'] == 1) {?>selected="selected"<?php }?>>已通过</option>
                        <option value="0" <?php if(isset($gets['is_checked']) && $gets['is_checked'] == 0) {?>selected="selected"<?php }?>>未通过</option>
                        </select>
      				</div>
      			</div>
                <?php }?>

  			    <div class="form-group">
  			    <?php if($conf['fee'] > 0) {?>
      				<label class="col-sm-2 control-label" >是否缴费</label>
      				<div class="col-sm-2">
       					<select name="is_fee" class="form-control" id="cid" >
                        <option value="-1">全部</option>
                        <option value="1" <?php if(isset($gets['is_fee']) && $gets['is_fee'] == 1) {?>selected="selected"<?php }?>>已缴费</option>
                        <option value="0" <?php if(isset($gets['is_fee']) && $gets['is_fee'] == 0) {?>selected="selected"<?php }?>>未缴费</option>
                        </select>
      				</div>

      				<label class="col-sm-2 control-label" >缴费图片</label>
      				<div class="col-sm-2">
       					<select name="fee_image" id="cid" class="form-control" >
                        <option  value="-1">全部</option>
                        <option value="1" <?php if(isset($gets['fee_image']) && $gets['fee_image'] == 1) {?>selected="selected"<?php }?>>已上传</option>
                        <option value="0" <?php if(isset($gets['fee_image']) && $gets['fee_image'] == 0) {?>selected="selected"<?php }?>>未上传</option>
                        </select>
      				</div>
                <?php }?>

                <?php if($conf['result_column']) {?>
      				<label class="col-sm-2 control-label" >上传作品</label>
      				<div class="col-sm-2">
       					<select name="is_result" id="cid" class="form-control" >
                        <option  value="-1">全部</option>
                        <option value="1" <?php if(isset($gets['is_result']) && $gets['is_result'] == 1) {?>selected="selected"<?php }?>>已上传</option>
                        <option value="0" <?php if(isset($gets['is_result']) && $gets['is_result'] == 0) {?>selected="selected"<?php }?>>未上传</option>
                        </select>
      				</div>
      			<?php }?>
    			</div>

    			<div class="form-group">
    			<label class="col-sm-2 control-label" >组队信息查询</label>
      				<div class="col-sm-4 control-div">
      				<select name="select"  class="form-control" >
      				<option value="team_number">队号</option>
      				<?php  foreach($conf['team_column'] as $k=>$v) {?>
                    <?php if($v[2] > 0) {?>
                    <option value="<?=$k?>" <?php if(isset($gets['select']) && $gets['select'] == $k) {?>selected="selected"<?php }?>><?=$v[0]?></option>
                    <?php }?>
                    <?php }?>
      				</select>
      				</div>

      				<div class="col-sm-6">
      					<input name="keywords" class="form-control" id="keywords" type="text" size="50" value="<?php echo isset($gets['keywords'])?$gets['keywords']:''?>">
      				</div>
    			</div>

                <div class="form-group">
    		    <div class="col-sm-12" align="right">
                <button type="submit" class="btn btn-info"  >查询</button>
                </div>
                </div>
    		</fieldset>

</form>

</div>


<?php if(!empty($rows)){?>
<form id="js_myform" name="myform" method="post" action="<?php echo site_url('mycontest/batch_process/'.$conf['contest_id'])?>">
<table class='topics table'>
<thead>
<tr>
<th align='left' class='auto'><input id="checkall" type="checkbox" ></th>
<th align='left' class='auto'>队号</th>

<?php foreach($field as $k=>$v) {?>
<th align='center' class='auto'><?php echo $v;?></th>
<?php }?>

<?php if($conf['fee'] > 0) {?>
<th align='right' class='auto'>缴费</th>
<?php }?>

<?php if($conf['is_checked']) {?>
<th align='right' class='auto'>审核</th>
<?php }?>

<th class='w100'>操作</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $k=>$v){ ?>
<tr class='highlight'>
<td class='auto'>
<input name="<?php echo $v['team_id'];?>"  value="<?php echo $v['team_id']?>" type="checkbox">
</td>

<td class='auto'>
<a href="<?php echo site_url('mycontest/team_info/'.$v['team_id']);?>"><?=$v['team_number']?></a>
</td>
<?php foreach($field as $rk=>$rv) {?>
<td class='auto' title="<?php echo isset($v[$rk]) ? $v[$rk] : '';?>">
<?php echo isset($v[$rk]) ? sb_substr($v[$rk], 10) : '';?>
</td>
<?php }?>

<?php if($conf['fee'] > 0) {?>
<th class='auto'>
<?php if($v['is_fee']) {?>是<?php }else {?>否<?php }?>
<?php if ($v['fee_image']) {?>
<a class="btn btn-primary btn-sm" href="/uploads/fee_images/<?php echo $v['fee_image'];?>" target="_blank">图</a>
<?php }?>
</th>
<?php }?>

<?php if($conf['is_checked']) {?>
<th class='auto'>
<?php if($v['is_valid']) {?>是<?php }else {?>否<?php }?>
</th>
<?php }?>

<td class='w100'>
<a target="_blank" href="<?php echo site_url('contest/user_apply/'.$v['contest_id'].'/'.$v['team_id']);?>" class="btn btn-primary btn-sm">编辑</a>
<?php if ($v['result_file']) {?><a href="<?php echo site_url('mycontest/result_file/'.$v['team_id']);?>" class="btn btn-primary btn-sm">作品</a><?php }?>
<!--  <a href="<?php echo site_url('forum/edit/'.$v['fid']);?>" class="btn btn-primary btn-sm">编辑</a>
<a href="<?php echo site_url('admin/topics/del/'.$v['fid'].'/'.$v['cid'].'/'.$v['uid']);?>" class="btn btn-sm btn-danger" data-confirm="真的要删除吗？" data-method="delete" rel="nofollow">删除</a>
<?php if($v['is_top']==0){?>
<a href="<?php echo site_url('admin/topics/set_top/'.$v['fid']).'/'.$v['is_top'];?>" class="btn btn-primary btn-sm">置顶</a>
<?php } else {?>
<a href="<?php echo site_url('admin/topics/set_top/'.$v['fid']).'/'.$v['is_top'];?>" class="btn btn-primary btn-sm">取消置顶</a>
<?php } ?>
<?php if($v['is_hidden']==1){?>
<a href="<?php echo site_url('admin/topics/approve/'.$v['fid']);?>" class="btn btn-primary btn-sm">审</a>
<?php } ?>
-->
</td>
</tr>
<?php } ?>


</tbody>
</table>

<div class='form-actions'>
<?php if($conf['fee'] > 0) {?>
<input class="btn btn-primary btn-info" name="batch_fee" type="submit" value="选中缴费" />
<input class="btn btn-primary btn-info" name="batch_unfee" type="submit" value="选中不缴费" />
<?php }?>
<?php if($conf['is_checked']) {?>
<input class="btn btn-primary btn-info" name="batch_check" type="submit" value="选中审核通过" />
<input class="btn btn-primary btn-warning" name="batch_uncheck" type="submit" value="选中审核不通过" />
<?php }?>

<!-- input class="btn btn-primary btn-danger" name="batch_del" type="submit" value="缴费" /-->
<a class="btn btn-primary"  href="?act=export&<?=$url_query?>">导出全部团队信息</a>
<a class="btn btn-primary"  href="?act=export&mem=1&<?=$url_query?>">导出全部团队和队员信息</a>
<?php if (!empty($conf['export_formate'])) {?>
<?php if (strpos(',', $conf['export_formate']) !== FALSE) {?>
	<a class="btn btn-primary"  href="?act=export&mem=1&<?=$url_query?>">导出全部团队和队员信息</a>
<?php } else {?>
	<a class="btn btn-success"  href="?act=export&formate=<?php echo $conf['export_formate'];?>&mem=1&<?=$url_query?>">按照格式导出数据</a>
<?php }?>
<?php }?>

<?php if (!empty($conf['can_down'])) {?>
<input id="btn_down" class="btn btn-primary btn-info" name="batch_down" type="submit" value="下载全部作品" />
<?php }?>
<?php if (!empty($conf['is_seal'])) {?>
<input id="seal_number" class="btn btn-info" name="seal_number" type="submit" value="生成密封号" />
<?php }?>
</div>

<div id="alertTest" class="alert alert-warning fade in" role="notice" style="display: none">
<button type="button" class="close" ><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
<span class="alert_message"></span>
</div>

</form>
<?php } else{?>
暂无团队信息
<?php }?>
<?php }?>

</div>
<div align='center' class='inner'>
<div>
<ul class='pagination'>
<?php echo isset($pagination) ? $pagination : '';?>
</ul>
</div>

</div>
</div>

</div>
</div></div></div>
<?php $this->load->view ('footer');?>
<script type="text/javascript" src="/static/common/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

	$(".alert").alert();
	$(".close").on('click', function() {$(".alert").hide()});

	$("#checkall").bind('click',function(){
		$("input:checkbox").prop("checked",$(this).prop("checked"));//全选
	});
	$("input:checkbox").on('change', function(){
		$("#down_notice").hide();
	});

	$("#btn_down").on('click', function(){
		$(".alert").show();
		$(".alert_message").html('批量下载可能时间较长请耐心等待');
	    return true;
	})

	$("#seal_number").on('click', function() {
		if ($('#promptMessage').length == 0) {
			var div = '<div class="" id="promptMessage" ></div>';
			$(document.body).append(div);
		} else {
			$('#promptMessage').dialog('open');
		}
		$('#promptMessage').html("<span class='red' >确定要生成密封号吗？!!</span><br/>新生成的密封号将会替换旧的。");
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
					start: function() {
						$('#promptMessage').html('密封号生成中请耐心等待...');
					},
					url: $("#js_myform").attr('action'),
					type: "POST",
					dataType:"json",
					data:{'seal_number':1},
					success: function(responseText) {
						if (responseText.code == 0) {
							if (responseText.message != '') {
								$('#promptMessage').html(responseText.message);
								$('#promptMessage').dialog("close");
								hideLi.hide('slow');
							} else {
								$('#promptMessage').dialog("close");
								$(".alert").show();
								$(".alert_message").html('密封号已经生成，点击上面两个导出按钮即可导出!');
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
				"取消" : function(){$( this ).dialog( "close" ); return false;}
			}
		});
		return false;
	})
});

</script>
</body></html>
