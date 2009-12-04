select

    attachment_id,
    attachment_name,
    attachment_size,
    event_id,
    is_removed

from tbl_attachments
where event_id = %1
