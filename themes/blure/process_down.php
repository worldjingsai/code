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
<div>
	<p class="alert alert-info">
		<strong>注意</strong> 批量下载可能时间较长，请耐心等等，下载完成后点击返回按钮。
	</p>
	<p>
	<a href="javascritp:;"  class="btn btn-sm btn-primary"  onclick="location.history.back();">返回列表</a>
	</p>
</div>

</div>
</div>

</div>
</div>
<?php $this->load->view ('footer');?>

</body></html>
