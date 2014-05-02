<!DOCTYPE html><html><head><meta content='' name='description'>
<meta charset='UTF-8'>
<meta content='True' name='HandheldFriendly'>
<meta content='width=device-width, initial-scale=1.0' name='viewport'>
<title><?php echo $title?> - 我的竞赛 - <?php echo $settings['site_name']?></title>
<?php $this->load->view('header-meta');?>
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
<a href="<?php echo site_url('mycontest/index');?>">我的竞赛</a> <span class="chevron">&nbsp;›&nbsp;</span> <a href="<?php echo site_url('mycontest/index');?>">我的创建的竞赛</a>
<span class="chevron">&nbsp;›&nbsp;</span> <?php echo sb_substr(strip_tags($contest['contest_name']), 19)?>
</div>
<div class='cell'>

<div class="cell">
<form method="get"  class="form-horizontal" action="<?php echo site_url('mycontest/my_team_list/'.$conf['contest_id'])?>">
  			<fieldset>

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
<form name="myform" method="post" action="<?php echo site_url('mycontest/batch_process/'.$conf['contest_id'])?>">
<table class='topics table'>
<thead>
<tr>
<th align='left' class='auto'><input id="checkall" type="checkbox" ></th>
<th align='left' class='auto'>队号</th>
<?php $i=1;?>
<?php  foreach($conf['team_column'] as $k=>$v) {?>

<?php if($i++ > 5) {break;}?>
<th align='right' class='auto'><?php echo $v[0];?></th>
<?php }?>

<?php if($conf['fee'] > 0) {?>
<th align='right' class='auto'>缴费</th>
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
<?=$v['team_number']?>
</td>
<?php for($j=1; $j<$i-1; $j++) {?>
<td class='auto'>
<?php echo $v["t$j"];?>
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
<td class='w100'>
<a href="<?php echo site_url('mycontest/team_info/'.$v['team_id']);?>" class="btn btn-primary btn-sm">详情</a>
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
<!-- input class="btn btn-primary btn-danger" name="batch_del" type="submit" value="缴费" /-->
<a class="btn btn-primary"  href="?act=export&<?=$url_query?>">导出全部团队信息</a>
<a class="btn btn-primary"  href="?act=export&mem=1&<?=$url_query?>">导出全部团队和队员信息</a>
<input id="btn_down" class="btn btn-primary btn-info" name="batch_down" type="submit" value="选中下载作品" />
<p id="down_notice" class="alert alert-warning red" style="display: none">批量下载可能时间较长请耐心等待</p>
</div>
</form>
<?php } else{?>
暂无团队信息
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
<script type="text/javascript">
$(document).ready(function(){
  $("#checkall").bind('click',function(){
  $("input:checkbox").prop("checked",$(this).prop("checked"));//全选
  });
  $("input:checkbox").on('change', function(){
	    //$("#btn_down").attr('disabled', false);
	    $("#down_notice").hide();
	  })
  $("#btn_down").on('click', function(){
	    //$("#btn_down").attr('disabled', true);
	    $("#down_notice").show();
	    return true;
	})
});
</script>
</body></html>
