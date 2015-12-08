<?php
  if(isset($_GET['c'])) {
      $content_url = $_GET['c'];
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!--	<meta http-equiv="refresh" content="2;URL=http://spirals">-->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="initial-scale=1, maximum-scale=1">
	<title>SmartPlate</title>
	<meta name="description" content="">
	<meta name="author" content="Tomo.Hagiwara">
<link href="css/reset.css" rel="stylesheet" type="text/css" />
<link href="css/common.css" rel="stylesheet" type="text/css" />
<link href="css/page.css" rel="stylesheet" type="text/css" />

<style>
</style>

<script>
	setTimeout("spload()",00);
	function spload() {
		document.spcontent.location.href="<?php echo $content_url; ?>";
		spheaderSplash.style.display="none";
		spheader.style.display="";
	}
	spcontent.document.write("<base target=\"_top\">");
</script>

</head>

<body style="overflow-y: hidden;">

<section id="spheader" style="display: block; background-color: white; color: #666;">
<p class="lt" id="lefttop"><a href="javascript:alert('\nスマホをかざすと何かが開く「スマートプレート」は、開くコンテンツをアプリからコントロールできるまったく新しいO2Oコミュニケーションツールです。（2014年12月発売予定）\n\nSmart Plate is a brand-new communication tool can be managed from the app. Will go on sale in Dec. 2014')">&gt; Smart Plate</a><!--&trade;--></p>
<p class="rt" id="righttop">いいねと思ったら[<a href="http://tap.cm/enquete3/question.php?q=5">ココ</a>] [<a href="http://tap.cm/enquete3/view.php?q=5">確認</a>]</p>
</section>

<section id="spheaderSplash" class="" style="display: none; background-color: white; color: black">
<!--<p class="center">めざせ100万カ所！</p>-->
<p class="center">かざせば、ひらく。</p>
</section>

<iframe src="" width="100%" height="100%" id="spmain" name="spcontent" scrolling="auto" target="_top">
</iframe>


</body>
</html>
