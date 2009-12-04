select event_id

from tbl_events

where

    record_id     = %1 and
    originator_id = %2 and
    event_type    = %3 and
    event_time    > %4
