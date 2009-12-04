select

    p.project_id,
    f.field_id

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s,
    tbl_fields    f

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.state_id    = f.state_id    and
    f.state_id    = %1            and

    lower(f.field_name) = '%2'
