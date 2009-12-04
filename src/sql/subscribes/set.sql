update tbl_subscribes
set is_activated = 1
where subscribe_id = %1 and account_id = %2
