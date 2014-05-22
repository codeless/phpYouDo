;<?php /*

[desc]
sql="""
SELECT 'FILTER_SANITIZE_NUMBER_INT removes all characters except digits,
plus and minus sign.<br>
Click the links below and see the results.' AS Description
"""
 
[email]
sql="SELECT :number[FILTER_SANITIZE_NUMBER_INT]* AS filtered_integer"

[links]
sql="""
SELECT	'<a href=?application=example_08_inputfilter&report=number&number=Ten>Ten</a>' AS link
UNION
SELECT	'<a href=?application=example_08_inputfilter&report=number&number=10>10</a>' AS link
UNION
SELECT	'<a href=?application=example_08_inputfilter&report=number&number=+10>+10</a>' AS link
UNION
SELECT	'<a href=?application=example_08_inputfilter&report=number&number=-10>-10</a>' AS link
UNION
SELECT	'<a href=?application=example_08_inputfilter&report=number&number=-10.3>-10.3</a>' AS link
"""

; */
