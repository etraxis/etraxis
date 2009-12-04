select count(*)

from

    tbl_templates t,
    tbl_states    s,
    tbl_records   r

where

    s.state_id    = r.state_id    and
    t.template_id = s.template_id and
    t.project_id  = %1
