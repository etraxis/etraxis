delete from tbl_comments
where event_id in (select event_id from tbl_events where record_id = %1)
