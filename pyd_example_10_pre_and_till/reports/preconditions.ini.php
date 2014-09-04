;<?php /*

[clear_action_flag]
template=empty
sql="SELECT 0 AS action"
saveRowFieldsToSession[0]=action

[save_action_flag_when_given]
template=empty
sql="SELECT :action* AS action"
saveRowFieldsToSession[0]=action

[desc]
sql="""
SELECT 'By using the pre-key, it is possible to run a query only
when a special precondition is given. Through this feature
it is not required to include all parameters in the queries anymore
(for instance for verifications, asf.)' AS Description
"""

[confirm]
sql="""
SELECT \"<a href='?application=example_10_pre_and_till&report=preconditions&action=1'>
	Run the query</a>\" AS Command
UNION
SELECT \"<a href='?application=example_10_pre_and_till&report=preconditions'>
	Do not run the query</a>\" AS Command
"""
 
[pre]
pre="$action"
sql="SELECT 'Hi, you have run the query!' AS message"

; */
