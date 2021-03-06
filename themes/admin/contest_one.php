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
<a href="<?php echo site_url('admin/contests/my');?>">所有竞赛</a>
<span class="chevron">&nbsp;›&nbsp;</span> <?php echo sb_substr(strip_tags($contest['contest_name']), 19)?>
</div>

<div class='cell'>
<?php if(!empty($rows)){?>
<form name="myform" method="post" action="<?php echo site_url('mycontest/batch_process')?>">
<table class='topics table'>
<thead>
<tr>
<!-- th align='left' class='auto'><input id="checkall" type="checkbox" checked="1"></th> -->

<th align='left' class='auto'>竞赛届数</th>
<th align='left' class='auto'>报名人数</th>
<th align='right' class='auto'>创建时间</th>
<th align='right' class='auto'>创建人ID</th>
<th class='w100'>操作</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $k=>$v){ ?>
<tr class='highlight'>
<!-- td class='auto'>
<input name="<?php echo $k?>" checked="1" value="<?php echo $v['contest_id']?>" type="checkbox">
</td> -->
<td class='auto'>
<?php echo $v['session']?>
</td>
<td class='auto'>
<a class="btn btn-primary btn-sm" target="_blank" href="<?php echo site_url('admin/contests/team_list/'.$v['contest_id'].'/'.$v['session']);?>"><?php echo $v['enter_members']?></a>
</td>

<td  class='auto'>
<?php echo $v['create_time']?>
</td>
<td  class='auto'>
<a href="<?php echo site_url('admin/contests/my/');?>"><?php echo $v['create_user_id']?></a>
</td>
<td class='w100'>
<a href="<?php echo site_url('admin/contests/my/');?>" class="btn btn-primary btn-sm">详情</a>

</td>

</tr>
<?php } ?>


</tbody>
</table>
<!-- div class='form-actions'>

<input class="btn btn-primary btn-danger" name="batch_del" type="submit" value="批量删除" />
<input class="btn btn-primary" name="batch_approve" type="submit" value="批量审核" />
</div> -->
<?php } else{?>
暂无报名系统
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
