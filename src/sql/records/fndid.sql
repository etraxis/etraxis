select

    r.record_id,
    p.project_id,
    p.project_name,
    p.is_suspended,
    t.template_id,
    t.template_prefix,
    t.template_name,
    t.critical_age,
    t.frozen_time,
    t.is_locked,
    s.state_id,
    s.state_name,
    s.next_state_id,
    s.responsible,
    r.subject,
    r.responsible_id,
    ar.username,
    ar.fullname,
    r.creator_id,
    ac.username as author_username,
    ac.fullname as author_fullname,
    r.creation_time,
    r.change_time,
    r.closure_time,
    (%2 - r.creation_time) as opened_age,
    (r.closure_time - r.creation_time) as closed_age,
    r.postpone_time

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s,
    tbl_records   r
        left outer join tbl_accounts ar on
            r.responsible_id = ar.account_id
        left outer join tbl_accounts ac on
            r.creator_id = ac.account_id
        left outer join (select record_id, event_time
                         from tbl_events) e on
            r.record_id = e.record_id

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.state_id    = r.state_id    and
    r.record_id   = %1
