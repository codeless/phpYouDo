;<?php /*

; Test for [Issue #9](https://github.com/codeless/phpYouDo/issues/9)
; To be called with a paramter like: &field_to_select=ipsum

[init]
sql="select :field_to_select as field"
saveRowFieldsToSession[0]=field

[exec]
; phpYouDo will fail to bind :company!!
sql="select $field*! as title from lipsum"

[exec2]
; this will work as expected:
sql="select :field_to_select as field, $field! as title from lipsum"

; */
