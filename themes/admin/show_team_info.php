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
<span class="chevron">&nbsp;›&nbsp;</span> <a href="<?php echo site_url('admin/contests/contest/'.$contest['contest_id']);?>"><?php echo sb_substr(strip_tags($contest['contest_name']), 19);?></a>
<span class="chevron">&nbsp;›&nbsp;</span> <a href="<?php echo site_url('admin/contests/team_list/'.$contest['contest_id']).'/'.$conf['session'];?>">届数<?php echo $conf['session'];?></a>
<span class="chevron">&nbsp;›&nbsp;</span> <?php echo $team['team_number']?>
</div>
<div class='cell'>
<?php if(!empty($conf)){?>

<div class="cell">
系统信息
</div>
<div align='center' class='inner'>
<table class="table table-bordered">

<tbody>

<?php if(!empty($team['team_number'])) {?>
<tr>
<td align="right" width="30%">
<span class="gray">参赛队号</span>
</td>
<td align="left">
<div class="pull-right"></div>
<?php echo $team['team_number'];?>
</td>
</tr>
<?php }?>

<?php if($conf['fee'] > 0) {?>
<tr>
<td align="right" width="30%">
<span class="gray">报名费用</span>
</td>
<td align="left">
<div class="pull-right"></div>
<span><?=$conf['fee']?>元 </span>&nbsp;&nbsp;
<?php if($team['is_fee']) {?>
<span class="green">已支付</span>
<?php } else {?>
<span class="red">未支付</span>
<?php }?>
</td>
</tr>
<?php }?>

</tbody></table>
</div>

<div class="cell">
团队信息
</div>
<div align='center' class='inner'>
<table class="table table-bordered">

<tbody>
<?php foreach($conf['t'] as $key=>$item) {?>
<?php if(!empty($item['2'])) {?>
<tr>
<td align="right" width="30%">
<span class="gray"><?=$item['0']?></span>
</td>
<td align="left">
<div class="pull-right"></div>
<?php echo $t[$key];?>
</td>
</tr>
<?php }?>
<?php }?>
</tbody></table>
</div>

<?php for($i=0; $i<count($m); $i++) {?>
<div class="cell">
队员<?php echo $i+1;?>信息
</div>

<div align='center' class='inner'>
<table class="table table-bordered">

<tbody>
<?php foreach($conf['m'] as $key=>$item) {?>
<?php if(!empty($item['2'])) {?>
<tr>
<td align="right"  width="30%">
<span class="gray"><?=$item['0']?></span>
</td>
<td align="left">
<div class="pull-right"></div>
<?php echo $m[$i][$key];?>
</td>
</tr>
<?php }?>
<?php }?>
</tbody></table>
</div>
<?php }?>
<?php } else{?>
暂无团队
<?php }?>
</div>

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
