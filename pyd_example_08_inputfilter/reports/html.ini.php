;<?php /*

[desc]
sql="""

SELECT 'By using FILTER_SANITIZE_MAGIC_QUOTES,
special characters used in HTML do not get stripped, but backslashed.<br>
Click the links below and see the results.' AS Description
"""
 
[html]
sql="""
SELECT IFNULL(	#html[FILTER_SANITIZE_MAGIC_QUOTES],
		'Lorem ipsum <u>dolor</u> <i>sit</i> <b>amet</b><h1>Please edit and post!</h1>')
	AS filtered_html
"""

; */
