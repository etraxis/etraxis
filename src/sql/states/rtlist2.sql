select

    s.state_name

from

    tbl_states     s,
    tbl_role_trans rt

where

    rt.state_id_from = %1         and
    rt.state_id_to   = s.state_id and
    rt.role          = %2
