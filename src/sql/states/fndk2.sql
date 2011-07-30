select

    p.project_id,
    s.state_id

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.template_id = %1            and
    lower(s.state_name) = '%2'
