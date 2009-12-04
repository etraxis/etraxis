select

    a.attachment_id

from

    tbl_attachments a,
    tbl_events      e

where

    e.event_id        = a.event_id and
    a.is_removed      = 0          and
    e.record_id       = %1         and
    a.attachment_name = '%2'
