select count(*)

from tbl_events

where

    record_id   = %1 and
    event_param = %2 and
    (event_type = 1 or event_type = 4)
