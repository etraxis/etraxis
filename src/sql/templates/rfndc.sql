select count(*)

from

    tbl_states  s,
    tbl_records r

where

    s.state_id    = r.state_id and
    s.template_id = %1
