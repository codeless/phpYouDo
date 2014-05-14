;<?php /*

[report]
template=empty
; For the selection-template
sql="SELECT 'session' AS type"

[selection]
sql="SELECT lorem AS id, ipsum AS text FROM lipsum"

[store_selection_into_session]
template=empty
sql="SELECT #lorem* AS selection"
saveRowFieldsToSession[0]=selection

[post_always]
sql="""
SELECT 'Query executed!' AS 'Always execute?'
UNION
SELECT ipsum FROM lipsum WHERE lorem=$selection
"""

[post_required]
sql="SELECT * FROM lipsum WHERE lorem=$selection*"

; */
