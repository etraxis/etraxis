select event_id

from tbl_events

where

    originator_id = %1 and
    event_type    = %2 and
    event_time    > %3 and
    event_param   = %4
