select

    subscribe_id,
    subscribe_name,
    is_activated

from tbl_subscribes
where account_id = %1
order by subscribe_name
