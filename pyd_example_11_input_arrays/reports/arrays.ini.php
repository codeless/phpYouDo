;<?php /*

[choose]
sql="SELECT lorem, ipsum FROM lipsum WHERE :show IS NULL LIMIT 3"

[show_details]
log=1
sql="SELECT * FROM lipsum WHERE lorem IN (:details[]*) AND :show*"

; */
