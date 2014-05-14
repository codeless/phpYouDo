;<?php /*

[report]
template=empty
; For the selection-template
sql="SELECT 'post' AS type"

[selection]
sql="SELECT lorem AS id, ipsum AS text FROM lipsum"

[post_always]
sql="""
SELECT 'Query executed!' AS 'Always execute?'
UNION
SELECT ipsum FROM lipsum WHERE lorem=#lorem
"""

[post_required]
sql="SELECT * FROM lipsum WHERE lorem=#lorem*"

; */
