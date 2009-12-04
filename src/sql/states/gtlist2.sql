select

    s.state_name

from

    tbl_states      s,
    tbl_group_trans gt

where

    gt.state_id_from = %1         and
    gt.state_id_to   = s.state_id and
    gt.group_id      = %2
