delete from tbl_group_trans

where

    (state_id_from in (select state_id from tbl_states where template_id = %1)) or
    (state_id_to   in (select state_id from tbl_states where template_id = %1))
