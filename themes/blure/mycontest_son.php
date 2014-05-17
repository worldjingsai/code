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
<a href="<?php echo site_url('mycontest/index');?>">我的创建的竞赛</a>
<span class="chevron">&nbsp;›&nbsp;</span> <a href="<?php echo site_url('mycontest/sons/'.$cinfo['contest_id']);?>" title="<?php echo strip_tags($cinfo['contest_name']).'的子竞赛';?>"><?php echo sb_substr(strip_tags($cinfo['contest_name']), 19)?></a>
<span class="chevron">&nbsp;›&nbsp;</span> 子竞赛
</div>
<div class="cell">
<div align='right' class='inner'>
<a href="<?php if (empty($parent_cid_str)) {echo site_url('mycontest/index');} else{ echo site_url('mycontest/sons/'.$parent_cid_str);}?>" class="btn btn-sm btn-primary">返回</a></div>
</div>
<div class='cell'>
<?php if(!empty($rows)){?>
<form name="myform" method="post" action="<?php echo site_url('mycontest/batch_process')?>">
<table class='topics table'>
<thead>
<tr>
<!-- th align='left' class='auto'><input id="checkall" type="checkbox" checked="1"></th> -->
<th align='left' class='auto'>竞赛名称</th>
<th align='left' class='auto'>竞赛类型</th>
<th align='left' class='auto'>竞赛级别</th>
<th align='right' class='auto'>创建日期</th>
<th align='right' class='auto'>报名队数</th>
<th align='right' class='auto'>子竞赛</th>


<!--  th class='w100'>操作</th>-->
</tr>
</thead>
<tbody>
<?php foreach($rows as $k=>$v){ ?>
<tr class='highlight'>
<!-- td class='auto'>
<input name="<?php echo $k?>" checked="1" value="<?php echo $v['contest_id']?>" type="checkbox">
</td> -->

<td class='auto'>
<a target="_blank" href="<?php echo site_url($v['contest_url']);?>"><?php echo sb_substr(strip_tags($v['contest_name']),20)?></a>
</td>
<td class='auto'>
<?php echo sb_substr($v['type_name'],20)?>
</td>
<td class='auto'>
<?php echo $v['level_name']?>
</td>
<td  class='auto'>
<small class='fade1'><?php echo substr($v['create_time'], 0, 10)?></small>
</td>
<td class='auto'>
<a href="<?php echo site_url('mycontest/sons_team_list/'.$current_cid_str.'_'.$v['contest_id']);?>" class="rabel profile_link btn btn-primary btn-sm" title="点击查看报名详情"><?php echo $v['enter_members']?></a>
</td>
<td  class='auto'>
<a href="<?php echo site_url('mycontest/sons/'.$current_cid_str.'_'.$v['contest_id']);?>" class="rabel profile_link btn btn-primary btn-sm" title="点击查看子竞赛"><?php echo $v['sons']?></a>
</td>

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
