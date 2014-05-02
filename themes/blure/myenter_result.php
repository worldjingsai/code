<!DOCTYPE html><html><head><meta content='' name='description'>
<meta charset='UTF-8'>
<meta content='True' name='HandheldFriendly'>
<meta content='width=device-width, initial-scale=1.0' name='viewport'>
<title><?php echo $title?> - 我的竞赛 - <?php echo $settings['site_name']?></title>
<?php $this->load->view('header-meta');?>
<link href="<?php echo base_url('static/common/css/jquery-ui-1.10.4.custom.min.css');?>" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo base_url('static/common/js/jquery-1.10.2.min.js');?>"></script>
<script type="text/javascript" src="<?php echo base_url('static/common/js/jquery-ui-1.10.4.custom.min.js');?>"></script>

</head>
<body id="startbbs">
<?php $this->load->view('header');?>
<div id="wrap">
<div class="container" id="page-main">
<div class="row">
<?php $this->load->view('mycontest_left');?>
<div class='col-xs-12 col-sm-6 col-md-10'>

<div class='box'>

<div class='cell' style="border-bottom-style: none;">
<a href="<?php echo site_url('mycontest/my');?>">我的竞赛</a> <span class="chevron">&nbsp;›&nbsp;</span> <a href="<?php echo site_url('myenter/enter');?>">我的参加的竞赛</a>
<span class="chevron">&nbsp;›&nbsp;</span> <?php echo $team['team_number']?>
    <ul class="nav nav-tabs" style="margin-top:10px;">
    <li ><a href="<?php echo site_url('myenter/team/'.$team['team_id']);?>">团队</a></li>
    <li><a href="<?php echo site_url('myenter/fee/'.$team['team_id']);?>">缴费</a></li>
    <li class="active"><a href="<?php echo site_url('myenter/result/'.$team['team_id']);?>">作品</a></li>
    </ul>
</div>

<div class='cell'>
<?php if(!empty($conf)){?>

<div class="inner">

		<form class="form-horizontal" enctype="multipart/form-data" action="<?php echo site_url('myenter/result/'.$team['team_id'])?>" method="post">
  			<fieldset>

    			<?php $r = $conf['r'];?>
    			<?php if (!empty($r['r1'][2])) {?>
    			<div class="form-group">
    			    <label class="col-sm-2 control-label"><?php echo $r['r1'][0];?></label>
    			    <div class="col-sm-10">
    			    <p class="alert alert-info">
    			    <?php if (strpos($r['r1'][1], '|') !== false) {?>
    			        <?php $opts = explode('|', $r['r1'][1]);?>
    			        <?php foreach ($opts as $pot) {?>
    			            <input type="radio" value="<?php echo $pot;?>" name="team_level" <?php if($pot == $team['team_level']) {?> checked="checked" <?php }?>/><?php echo $pot;?><span style="width:10px;">&nbsp;</span>
    			        <?php }?>
    			    <?php } else {?>
    			            <input type="text" value="<?php echo $team['team_level'];?>" name="team_level"/>
    			    <?php }?>
    			    </p>
    			    </div>
    			</div>
    			<?php }?>

    			<?php if (!empty($r['r2'][2])) {?>
    			<div class="form-group">
    			    <label class="col-sm-2 control-label"><?php echo $r['r2'][0];?></label>
    			    <div class="col-sm-10">
    			    <p class="alert alert-info">
    			    <?php if (strpos($r['r2'][1], '|') !== false) {?>
    			        <?php $opts = explode('|', $r['r2'][1]);?>
    			        <?php foreach ($opts as $pot) {?>
    			            <input type="radio" value="<?php echo $pot;?>" name="problem_number" <?php if($pot == $team['problem_number']) {?> checked="checked" <?php }?>/><?php echo $pot;?><span style="width:10px;">&nbsp;</span>
    			        <?php }?>
    			    <?php } else {?>
    			            <input type="text" value="<?php echo $team['problem_number'];?>" name="problem_number"/>
    			    <?php }?>
    			    </p>
    			    </div>
    			</div>
    			<?php }?>

    			<div class="form-group">
    			<label class="col-sm-2 control-label">上传作品</label>
      				<div class="col-sm-10">
      					<p class="alert alert-info">
      					<?php if ($team['result_file']){?>
      						<a href="<?php echo base_url('mycontest/result_file/'.$team['team_id']); ?>">作品下载</a>
      					<?php } else {?>
							还未上传
      					<?php }?>
      					</p>

       					<p class="alert alert-info">
       						<strong>注意</strong> 支持 5M 以内的 doc / docx / pdf / rar / zip  文件.
	      				</p>
      				</div>
    			</div>

    			<div class="form-group">
      				<label class="col-sm-2 control-label" for="avatar_file">选择作品</label>
      				<div class="col-sm-5">
       					<input type="file" id="file" name="userfile" />
      				</div>
      				<div id="progressbar" ><div class="progress-label"></div></div>
      				<?php if (!empty($qiniu['is_used'])) {?>
					<input type="hidden" id="token" name="token"  value="<?php  echo $qiniu['up_token'];?>">
					<input type="hidden" id="key" name="key"  value="<?php  echo $qiniu_key?>">
					<?php }?>
    			</div>

    			<div class="form-group">
	    			<div class="col-sm-offset-2 col-sm-9">
	    			<?php if (!empty($qiniu['is_used'])) {?>
	    			<input type="button" id="btn_upload" name="upload" class="btn btn-sm btn-primary" value="上传新作品"></input>
	    			<?php } else {?>
    				<button type="submit" name="upload" class="btn btn-sm btn-primary">上传新作品</button>
    				<?php }?>
    				</div>
    			</div>
    		</fieldset>
    	</form>
	</div>

<?php } else{?>
暂无团队
<?php }?>
</div>

</div>
</div>

</div>
</div></div>
<?php $this->load->view ('footer');?>
<script type="text/javascript">
$(document).ready(function(){
  $("#checkall").bind('click',function(){
  $("input:checkbox").prop("checked",$(this).prop("checked"));//全选
  });

});
</script>
<?php if (!empty($qiniu['is_used'])) {?>
<script type="text/javascript" src="<?php echo base_url('static/common/js/qiniuResult.js');?>"></script>
<?php }?>
</body></html>
