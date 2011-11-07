select distinct

    s.state_id,
    s.state_name,
    min(e.event_time) as first_time,
    max(e.event_time) as event_time

from

    tbl_states s,
    tbl_events e

where

    s.state_id = e.event_param and
    (e.event_type = 1 or e.event_type = 4 or e.event_type = 14) and
    e.record_id = %1

group by

    s.state_id,
    s.state_name

order by

    first_time asc
