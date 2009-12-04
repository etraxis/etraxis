select

    r.record_id

from

    tbl_children ch,
    tbl_states   s,
    tbl_records  r

where

    s.state_id       = r.state_id  and
    r.record_id      = ch.child_id and
    ch.parent_id     = %1          and
    ch.is_dependency = 1           and
    s.state_type     <> 3
