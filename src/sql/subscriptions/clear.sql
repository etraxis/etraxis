update tbl_subscribes
set is_activated = 0
where subscribe_id = %1 and account_id = %2
