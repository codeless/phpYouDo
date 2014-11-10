;<?php /*

[sec1]
sql="""SELECT 'lipsum' AS 'testtable'"""
saveRowFieldsToSession[0]=testtable

[sec2]
sql="""
SELECT * FROM $testtable!
"""

; */
