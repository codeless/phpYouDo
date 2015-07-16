;<?php /*

[form]
pre="':trigger' == ''"
sql="select 1 from lipsum"

[form_submitted]
pre="':trigger' != ''"
sql="select 1 from lipsum"

; Conditionally include subreport:
[report subreport]
pre="':trigger' != ''"

; */
