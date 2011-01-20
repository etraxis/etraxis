select

    subscribe_id,
    subscribe_name,
    carbon_copy,
    is_activated

from tbl_subscribes
where account_id = %1
order by %2
