select

    p.project_id,
    p.project_name,
    e.event_id,
    e.record_id,
    e.originator_id,
    e.event_type,
    e.event_time,
    e.event_param

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s,
    tbl_records   r,
    tbl_events    e

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.state_id    = r.state_id    and
    r.record_id   = e.record_id   and

    e.record_id     = %1 and
    e.originator_id = %2 and
    e.event_type    = %3 and
    e.event_time    = %4 and
    e.event_param   = %5
