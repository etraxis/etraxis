select

    ch.parent_id,
    t.template_prefix

from

    tbl_templates t,
    tbl_states    s,
    tbl_records   r,
    tbl_children  ch

where

    t.template_id = s.template_id and
    s.state_id    = r.state_id    and
    r.record_id   = ch.parent_id  and
    ch.parent_id  = %1            and
    ch.child_id   = %2
