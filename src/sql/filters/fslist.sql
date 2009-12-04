select

    fs.state_id

from

    tbl_filter_states fs,
    tbl_states        s

where

    fs.filter_id  = %1 and
    s.template_id = %2 and
    s.state_id    = fs.state_id
