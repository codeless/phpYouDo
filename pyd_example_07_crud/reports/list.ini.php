;<?php /*

[list]
sql="""
SELECT	lorem,
	ipsum,
	dolor,
	sit,
	amet,
	'<a href=?application=07_crud&report=edit&id='
		|| lorem
		|| '><i class=\"fa fa-pencil-square-o\"></i></a>' AS Edit,
	'<a href=?application=07_crud&report=delete&id='
		|| lorem
		|| ' class=confirm title=delete'
		|| '><i class=\"fa fa-trash-o\"></i></a>' AS Trash
	FROM lipsum
	ORDER BY lorem DESC
	LIMIT 40
"""

; */
