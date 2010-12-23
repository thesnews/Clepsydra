<!DOCTYPE html>
<html>
	<head>
		<title>Clepsydra</title>	
		<link rel="stylesheet" type="text/css" media="screen" href="{{ 'styles/css/master.css'|url }}" />
		{#<script type="text/javascript" src="js/mootools-core-1.3.js"></script>
		<script type="text/javascript" src="js/js-class.js"></script>
		<script type="text/javascript" src="js/bluff-min.js"></script>
		<script type="text/javascript" src="js/master.js"></script>#}
	</head>
	<body>
	
		<div class="container_12">
			<form method="post" action="{{ 'clepsydra:main/login'|url }}">
				<input type="text" name="email" />
				
				<input type="password" name="passwd" />
				
				<input type="submit" value="login" />
			</form>
		</div>
	
	</body>
</html>

