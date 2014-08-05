;<?php /*

[desc]
sql="""
SELECT 'Individual input filtering using a PHP template &mdash; only bold and italic formating is allowed!' AS Description
"""

[individual_filter]
sql="SELECT #html[FILTER_SANITIZE_MAGIC_QUOTES] AS input"
 
[html]
sql="""
SELECT	IFNULL($html, '<h1>Please edit and post!</h1>') AS filtered_html,
	'individual' AS report
"""

; */
