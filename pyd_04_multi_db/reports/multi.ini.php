;<?php /*

[delete_hit]
database=db2
sql="DELETE FROM hits WHERE ROWID=:delete_hit*"

[update_hits_in_db2]
database=db2
sql="INSERT INTO hits (lorem) VALUES (datetime('now'))"

[read_db1]
; db1 is the default one, thus doesn't need database-specification:
sql="SELECT * FROM lipsum"

[read_db2]
database=db2
sql="""
SELECT	lorem AS 'Last hits',
	'<a href=?application=04_multi_db&report=multi&delete_hit='
		|| ROWID
		|| '>Delete</a>' AS Action
	FROM hits
	ORDER BY 1 DESC
	LIMIT 10
"""

; */
