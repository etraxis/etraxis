select

    a.attachment_id,
    a.attachment_name,
    a.attachment_type,
    a.attachment_size,
    e.originator_id,
    u.username,
    u.fullname,
    e.event_time

from

    tbl_attachments a,
    tbl_accounts    u,
    tbl_events      e

where

    e.event_id      = a.event_id   and
    e.originator_id = u.account_id and
    a.is_removed    = 0            and
    e.record_id     = %1

order by %2
