;<?php /*

[get1]
sql="SELECT :get1 AS 'get1 success', :get1 AS get1"

[get2]
sql="SELECT :get2* AS 'get2 success', :get2* AS get2"

[get3]
sql="SELECT :get3* AS 'get3 success', :get3 AS get3"

[init_session]
sql="SELECT 1 AS s1, 1 AS s2, 1 AS s3"
saveRowFieldsToSession[0]=s1,s2,s3

[session1]
sql="SELECT $s1 AS 's1 success', $s1 AS s1"

[session2]
sql="SELECT $s2* AS 's2 success', $s2* AS s2"

[session3]
sql="SELECT $s3* AS 's3 success', $s3 AS s3"

; */
