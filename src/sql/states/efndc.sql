select count(*)

from tbl_events

where

    (event_type = 1 or event_type = 4 or event_type = 14) and
    event_param = %1
