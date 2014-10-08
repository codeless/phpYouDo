;<?php /*

[choose]
sql="""
SELECT	lorem,
	ipsum,
	'arrays_via_session' AS report
	FROM lipsum
	WHERE :show IS NULL LIMIT 3
"""

[show_details]
pre="require('pyd_example_11_input_arrays/library/store_selection_into_session.php')"
sql="SELECT * FROM lipsum WHERE lorem IN ($details[]*) AND :show*"

; */
