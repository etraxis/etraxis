select

    a.username,
    a.fullname,
    a.email,
    e.event_id,
    e.event_time,
    e.event_type,
    e.event_param

from

    tbl_events e
        left outer join tbl_accounts a on
            e.originator_id = a.account_id

where e.record_id = %1 and e.event_type <> %2

order by %3
