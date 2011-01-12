<!DOCTYPE html>
<html>
	<head>
		<title>Clepsydra</title>	
		<link rel="stylesheet" type="text/css" media="screen" href="{{ 'styles/css/master.css'|url }}" />
		<script type="text/javascript" src="{{ 'javascript/vendor/mootools-combined.js'|url }}"></script>
		<script type="text/javascript" src="{{ 'javascript/vendor/rosewood.compiled.js'|url }}"></script>
		<script type="text/javascript" src="{{ 'javascript/master.js'|url }}"></script>
	</head>
	<body id="login_body">
	
		<div class="container_12">
			<form method="post" action="{{ 'clepsydra:main/login'|url }}" id="login">
				<img src="{{ 'styles/img/logo.png'|url }}" alt="Clepsydra" id="logo" />
				<h1></h1>
				<input class="text-replace" default="Email" type="text" name="email" value="Email" id="email" />
				
				<input class="text-replace" default="Password" type="password" name="passwd" value="Password" id="password" />
				
				<input type="submit" value="Login" class="green" />
				
				<p>
					<strong>clep&bull;sy&bull;dra</strong>
					<span>noun. <i>klep-si-druh</i></span>
					1. An ancient device for measuring time by the regulated flow of water or mercury through a small aperture.	
				</p>
			</form>
		</div>
	
	</body>
</html>

