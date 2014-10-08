;<?php /*

[choose]
sql="""
SELECT	lorem,
	ipsum,
	'arrays' AS report
	FROM lipsum
	WHERE :show IS NULL LIMIT 3
"""

[show_details]
sql="SELECT * FROM lipsum WHERE lorem IN (:details[]*) AND :show*"

; */
