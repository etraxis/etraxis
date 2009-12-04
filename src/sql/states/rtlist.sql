select

    state_id,
    state_name,
    state_type,
    1 as is_set

from

    tbl_states

where

    template_id = %1 and
    state_id in

       (select state_id_to as state_id
        from tbl_role_trans
        where state_id_from = %2 and role = %3)

union

select

    state_id,
    state_name,
    state_type,
    0 as is_set

from

    tbl_states

where

    template_id = %1 and
    state_id not in

       (select state_id_to as state_id
        from tbl_role_trans
        where state_id_from = %2 and role = %3)

order by

    state_type,
    state_name
