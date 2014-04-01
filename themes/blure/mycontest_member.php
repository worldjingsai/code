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
    
<label for="category">筛选</label>
<select name="cid" id="cid" width="20%">
<option selected="selected" value="">请选择分类</option>
<option value="1">已缴费队伍</option>
<option value="1">未上传缴费证明队伍</option>
<option value="1">缴费证明尚未审核队伍</option>
</select>

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
<?php if($v['is_fee']) {?>
<?php echo '是';?>
<?php }else {if ($v['fee_image']) {?><a class="btn btn-primary btn-sm" href="/uploads/fee_images/<?php echo $v['fee_image'];?>" target="_blank">图</a><?php } else {?>
    否
<?php }}?>
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
<?php }?>
<!-- input class="btn btn-primary btn-danger" name="batch_del" type="submit" value="缴费" /-->
<a class="btn btn-primary"  href="?act=export">全部导出</a>


</div>

<?php } else{?>
暂无竞赛
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
});
</script>
</body></html>
