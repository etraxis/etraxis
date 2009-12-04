select

    a.attachment_id,
    a.attachment_name,
    a.attachment_type,
    a.attachment_size

from

    tbl_attachments a,
    tbl_events      e

where

    e.event_id      = a.event_id and
    a.is_removed    = 0          and
    e.record_id     = %1         and
    e.originator_id = %2

order by a.attachment_name
