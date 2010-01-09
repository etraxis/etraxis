select

    comment_body,
    is_confidential

from tbl_comments
where event_id = %1
