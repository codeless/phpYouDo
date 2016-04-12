;<?php /*

[get_number_of_records]
sql="select count(*) as record_count from lipsum"
saveRowFieldsToSession[0]=record_count

[list]
total_records="$record_count"
paginate=2
sql="select * from lipsum"

; */
