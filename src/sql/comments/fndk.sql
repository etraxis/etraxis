select

    a.username,
    a.fullname,
    e.event_time,
    c.comment_body,
    c.is_confidential

from

    tbl_comments c,
    tbl_events   e
        left outer join tbl_accounts a on
            e.originator_id = a.account_id

where

    e.event_id = c.event_id and
    e.event_id = %1
