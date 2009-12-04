select

    e.event_id,
    e.originator_id,
    e.event_type,
    e.event_time,
    a.username,
    a.fullname,
    s.state_id,
    s.state_name

from

    tbl_accounts a,
    tbl_states   s,
    tbl_events   e

where

    a.account_id  = e.originator_id and
    s.state_id    = e.event_param   and
    e.record_id   = %1              and
    (e.event_type = 1 or e.event_type = 4)

union

select

    e.event_id,
    e.originator_id,
    e.event_type,
    e.event_time,
    a.username,
    a.fullname,
    null as state_id,
    null as state_name

from

    tbl_accounts a,
    tbl_events   e

where

    a.account_id  = e.originator_id and
    e.record_id   = %1              and
    (e.event_type = 7 or e.event_type = 13)

order by

    event_time asc,
    event_type asc
