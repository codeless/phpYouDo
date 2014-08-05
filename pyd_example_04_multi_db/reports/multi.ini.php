;<?php /*

[delete_hit]
database=db2
sql="DELETE FROM hits WHERE ROWID=:delete_hit*"

[read_db1]
; db1 is the default one, thus doesn't need database-specification:
sql="SELECT * FROM lipsum"

[read_db2]
database=db2
sql="""
SELECT	lorem AS 'Last hits',
	'<a href=?application=example_04_multi_db&report=multi&delete_hit='
		|| ROWID
		|| '>Delete</a>' AS Action
	FROM hits
	ORDER BY 1 DESC
	LIMIT 10
"""

; */
