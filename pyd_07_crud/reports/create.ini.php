;<?php /*

[create_status]
sql="""
INSERT INTO lipsum(ipsum, dolor, sit, amet)
	VALUES( -- Make SQL invalid when no new record is submitted:
		(SELECT :ipsum* WHERE :submit* = 'Save new'),
		:dolor,
		:sit,
		:amet)
"""

[update_status]
sql="""
UPDATE lipsum
	SET	ipsum = :ipsum*,
		dolor = :dolor,
		sit = :sit,
		amet = :amet
	WHERE	lorem = :id* AND
		-- Fire only when Update is pressed:
		:submit* = 'Update'
"""

[create_formular]
sql="""
-- Placeholders for new records
SELECT	NULL AS lorem,
	'Placeholder' AS ipsum,
	NULL AS dolor,
	NULL AS sit,
	NULL AS amet,
	'Save new' AS status
UNION

-- Record data when id is given:
SELECT	lorem,
	ipsum,
	dolor,
	sit,
	amet,
	'Update' AS status
	FROM lipsum
	WHERE lorem=IFNULL(:id,-1)

-- Get rid of placeholder-record
-- when id is set:
ORDER BY 1 DESC
LIMIT 1
"""

; */
