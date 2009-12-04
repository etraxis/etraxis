select

    s.template_id,
    r.creator_id,
    r.responsible_id,
    a.attachment_name,
    a.attachment_type,
    a.attachment_size,
    e.event_id,
    e.originator_id,
    a.is_removed

from

    tbl_states      s,
    tbl_records     r,
    tbl_events      e,
    tbl_attachments a

where

    s.state_id      = r.state_id  and
    r.record_id     = e.record_id and
    e.event_id      = a.event_id  and
    a.attachment_id = %1
