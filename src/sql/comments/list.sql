select

    e.event_id,
    e.event_type,
    e.event_time,
    a.username,
    a.fullname,
    c.is_confidential,
    c.comment_body

from

    tbl_accounts a,
    tbl_comments c,
    tbl_events   e

where

    a.account_id  = e.originator_id and
    c.event_id    = e.event_id      and
    e.record_id   = %1              and
    (e.event_type = 7 or e.event_type = %2)

order by

    event_time asc,
    event_id   asc
