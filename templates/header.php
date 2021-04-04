<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?=isset($title) ? $title : 'Endurance Riders of Alberta' ?></title>
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
  <meta name="robots" content="index, follow" />
  <link rel="stylesheet" href="/public/themes/era/css/main.css" media="screen" type="text/css" />
  <link rel="stylesheet" href="/public/themes/era/js/fancybox/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />
  <script src="/public/themes/era/js/jquery.js" type="text/javascript"></script>
  <script src="/public/themes/era/js/fancybox/jquery.fancybox-1.3.1.pack.js" type="text/javascript"></script>
  <script src="/public/themes/era/js/fancybox/jquery.easing-1.3.pack.js" type="text/javascript"></script>
  <script src="/public/themes/era/js/cufon.js" type="text/javascript"></script>
  <script src="/public/themes/era/js/Gotham_500.font.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			Cufon.set('fontFamily', 'Gotham').replace('h2')('h3')('h4');
			Cufon.set('fontFamily', 'Gotham').replace('ul:has(ul) > li a', {
				hover: true,
				textShadow: '#ee810b 1px 1px'
			}); 
			Cufon.now();
		
			$('#splash img').css('opacity','0');
			
			$('#search input').focus(function() { 
				if(this.value == "Search") {
					this.value = "";
				}
			});
			
			$('#search input').blur(function() {
				if(this.value == "") {
					this.value = "Search";
				}
			});	
		});
		
		$(window).load(function()
		{
			$('#splash img').fadeTo('slow',1);	
		});
	</script>
</head>
<body>
<a name="top"></a>
<div id="wrap">
		<div id="header">	
			<div id="splash">
				<img src="/public/themes/era/splash/rotate.php" alt="A Random Image" height="160" />
			</div>
			<div id="title">
				<h1 id="logo"><a href="/">Endurance Riders of Alberta</a><a href="/recruits/getting-started">Get started</a></h1>
				<span id="classic">ERA<ul><li><a href="/">Home</a></li><li><a href="/contact-us">Contact Us</a></li></ul></span>
			</div>
			<ul id="nav">
				<li class="club"><a href="/our-club">Our Club</a>
					<ul>
						<li><a href="/our-club/club-directors"><em>&raquo;</em> Club Directors</a></li>
						<li><a href="/our-club/era-awards"><em>&raquo;</em> ERA Awards</a></li>
						<li><a href="/our-club/rules-regulations"><em>&raquo;</em> Rules & Regulations</a></li>
						<li><a href="/our-club/club-history"><em>&raquo;</em> Club History</a></li>
					</ul>
				
				
				
				</li>
				<li class="rides"><a href="/rides-events">Rides & Events</a>
					
				</li>
				<li class="buysell"><a href="/buy-sell/classifieds">Buy & Sell</a>
					<ul>
						<li><a href="/buy-sell/classifieds"><em>&raquo;</em> Classifieds</a></li>
						<li><a href="/buy-sell/submit-ad"><em>&raquo;</em> Submit Ad</a></li>
					</ul>
				</li>
				<li class="members"><a href="/members/memberships">Members</a>
					<ul>
						<li><a href="/members/memberships"><em>&raquo;</em> Memberships</a></li>
						<li><a href="/members/forms"><em>&raquo;</em> Forms</a></li>
					</ul>
				</li>
				<li class="recruits"><a href="/recruits/getting-started">Recruits</a>
					<ul>
						<li><a href="/recruits/getting-started"><em>&raquo;</em> Getting Started</a></li>
						<li><a href="/recruits/links"><em>&raquo;</em> Links</a></li>
					</ul>
				</li>
				<li class="gallery last"><a href="/gallery">Gallery</a></li>
			</ul>
		</div>