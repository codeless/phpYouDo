;<?php /*

[get_choose]
sql="""
SELECT	'<a href=?application=example_01_intro&report=get&lorem='
		|| lorem
		|| '>'
		|| ipsum
		|| ' &raquo;</a>' AS 'Please choose an record:'
	FROM lipsum
"""

[get_always]
; Always gets executed, even if there's no lorem-GET parameter set:
sql="""
SELECT 'Query executed!' AS 'Always execute?'
UNION
SELECT ipsum FROM lipsum WHERE lorem=:lorem
"""

[get_required]
; Only gets executed when the lorem parameter is set:
sql="SELECT * FROM lipsum WHERE lorem=:lorem*"

; */
