{% extends 'clepsydra/base.tpl' %}

{% block content %}
	{% helper userHelper as user %}

	<div class="grid_6">
		<h1>Welcome Back, {{ user.name }}.</h1>
		<em>Status:</em>
		{% if user.tracked %}
			Clocked {% if user.in %}In{% else %}Out{% endif %}
		{% else %}
			Your time isn't tracked.
		{% endif %}
		&nbsp;&nbsp;&nbsp;&nbsp;
		<em>Currently:</em> <span id="container_currentTime">00:00:00</span>
	</div>
	<div class="grid_6">
		<div id="timer" class="dark round">
			<em>Today</em>
			<span>02:44:13</span>
		</div>
	</div>


{% endblock %}