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
<a href="<?php echo site_url('mycontest/index');?>">我的竞赛</a> <span class="chevron">&nbsp;›&nbsp;</span> 我参加的竞赛
</div>
<div class='cell'>
<?php if(!empty($rows)){?>
<form name="myform" method="post" action="<?php echo site_url('mycontest/batch_process')?>">
<table class='topics table'>
<thead>
<tr>
<!-- th align='left' class='auto'><input id="checkall" type="checkbox" checked="1"></th> -->
<th align='left' class='auto'>队号</th>
<th align='left' class='auto'>竞赛名称</th>
<th align='right' class='auto'>报名时间</th>
<th align='right' class='auto'>备注</th>
<th class='w100'>操作</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $k=>$v){ ?>
<tr class='highlight'>

<td class='auto'>
<?php echo $v['team_number']?>
</td>
<td class='auto'>
<a target="_blank" href="<?php echo site_url($v['contest_url']);?>"><?php echo sb_substr(strip_tags($v['contest_name']),50)?></a>
</td>

<td  class='auto'>
<small class='fade1'><?php echo $v['create_time']?></small>
</td>
<td class='auto'>
<?php if($v['is_fee']) {
echo "已交费";
}?>
</td>
<td>
<a class="btn btn-primary btn-sm" href="<?php echo site_url('/mycontest/i_team/' . $v['team_id']);?>">详情</a>
</td>
<!--  td class='w100'>
<a href="<?php echo site_url('forum/edit/'.$v['fid']);?>" class="btn btn-primary btn-sm">编辑</a>
<a href="<?php echo site_url('admin/topics/del/'.$v['fid'].'/'.$v['cid'].'/'.$v['uid']);?>" class="btn btn-sm btn-danger" data-confirm="真的要删除吗？" data-method="delete" rel="nofollow">删除</a>
<?php if($v['is_top']==0){?>
<a href="<?php echo site_url('admin/topics/set_top/'.$v['fid']).'/'.$v['is_top'];?>" class="btn btn-primary btn-sm">置顶</a>
<?php } else {?>
<a href="<?php echo site_url('admin/topics/set_top/'.$v['fid']).'/'.$v['is_top'];?>" class="btn btn-primary btn-sm">取消置顶</a>
<?php } ?>
<?php if($v['is_hidden']==1){?>
<a href="<?php echo site_url('admin/topics/approve/'.$v['fid']);?>" class="btn btn-primary btn-sm">审</a>
<?php } ?>
</td>
-->
</tr>
<?php } ?>


</tbody>
</table>
<!-- div class='form-actions'>
<input class="btn btn-primary btn-danger" name="batch_del" type="submit" value="批量删除" />
<input class="btn btn-primary" name="batch_approve" type="submit" value="批量审核" />
</div>
 -->
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
