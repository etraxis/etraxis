update tbl_field_values
set is_latest = 0
where field_id = %2 and event_id in

   (select event_id
    from tbl_events
    where record_id = %1)
