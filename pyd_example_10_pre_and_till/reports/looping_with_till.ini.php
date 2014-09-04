;<?php /*

[initialize_times]
template=empty
sql="SELECT 0 AS times"
saveRowFieldsToSession[0]=times

[save_times_when_given]
template=empty
sql="SELECT :times* AS times"
saveRowFieldsToSession[0]=times

[desc]
sql="""
SELECT 'By using the till-key, it is possible to run a query as
long as a special condition is met. Through this feature, a query
can be run multiple times.' AS Description
"""

[confirm]
sql="""
SELECT \"<a href='?application=example_10_pre_and_till&report=looping_with_till&times=5'>
	Run the query 5 times</a>\" AS Command
UNION
SELECT \"<a href='?application=example_10_pre_and_till&report=looping_with_till&times=3'>
	Run the query 3 times</a>\" AS Command
UNION
SELECT \"<a href='?application=example_10_pre_and_till&report=looping_with_till&times=1'>
	Run the query once</a>\" AS Command
UNION
SELECT \"<a href='?application=example_10_pre_and_till&report=looping_with_till&times=0'>
	Do not run the query</a>\" AS Command
"""
 
[pre$times]
pre="$times"
till="$times"
sql="""
SELECT	'Hi, the query is run!' AS message,
	$times*-1 AS times
"""
saveRowFieldsToSession[0]=times

; */
