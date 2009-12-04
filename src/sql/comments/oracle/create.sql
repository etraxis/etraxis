insert into tbl_comments

   (comment_body,
    event_id,
    is_confidential)

values (empty_clob(), :event_id, :is_confidential)
returning comment_body into :comment_body
