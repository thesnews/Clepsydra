<!DOCTYPE html>
<html>
	<head>
		<title>Clepsydra</title>	
		<link rel="stylesheet" type="text/css" media="screen" href="{{ 'styles/css/master.css'|url }}" />
		<script type="text/javascript" src="{{ 'javascript/vendor.min.js'|url }}"></script>
		<script type="text/javascript" src="{{ 'javascript/master.js'|url }}"></script>
	</head>
	<body>
	
		<div id="header">
			{% include 'clepsydra/header.tpl' %}
		</div>
		
		<div class="container_12">
			{% block content %}{% endblock %}
		</div>

		<div class="grid_12">
			<br style="clear:both;" />
			<div  id="footer">
				&copy; Copyright 2011 The State News
			</div>
		</div>
	
	</body>
</html>

