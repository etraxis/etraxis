insert into tbl_subscribes

   (account_id,
    subscribe_name,
    carbon_copy,
    subscribe_type,
    subscribe_flags,
    subscribe_param,
    is_activated)

values (%1, '%2', '%3', %4, %5, %6, 1)
