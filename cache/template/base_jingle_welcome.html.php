<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="<?php echo see_engine_request::host(false); ?>/statics/css/jingle.min.css" />
</head>
<body>
<section id="section_container" class="active">
	<header>
		<nav class="left"><a href=""><</a></nav>
		<h1 class="title"><a href='#' id="nav_center">Demo</a></h1>
		<nav class="right"><a href="#" id="nav_right">=</a></nav>
	</header>
	<article class="active">
		<ul class="list">
			<li><a href="">aaaaaaaaaaa</a></li>
			<li><a href="">bbbbbbbbbbbbbbb</a></li>
			<li><a href="">ccccccccccc</a></li>
		</ul>
	</article>
	<footer>
		<a href="">Menu</a>
		<a href="">Panel</a>
	</footer>
</section>
</body>

<script type="text/javascript" src="<?php echo see_engine_request::host(false); ?>/statics/js/zepto.js"></script>
<script type="text/javascript" src="<?php echo see_engine_request::host(false); ?>/statics/js/iscroll.js"></script>
<script type="text/javascript" src="<?php echo see_engine_request::host(false); ?>/statics/js/template.min.js"></script>
<script type="text/javascript" src="<?php echo see_engine_request::host(false); ?>/statics/js/jingle.debug.js"></script>

<script type="text/javascript" src="<?php echo see_engine_request::host(false); ?>/statics/js/zepto.touch2mouse.js"></script>
<script>
J.launch();
$('#nav_right').tap(function(){
	J.popup({html:'<ul class="active list"><li>aaaaaaa</li></ul>',pos:{top:'44px',left:'85%'},width:'15%',height:'300px',showCloseBtn:false});
});
$('#nav_center').tap(function(){
	J.popup({html:'<ul class="active list"><li>aaaaaaa</li></ul>',pos:{top:'44px',left:'38%'},width:'25%',height:'200px',arrowDirection:'top',showCloseBtn:false});
});
</script>
</html>