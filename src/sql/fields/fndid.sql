select

    p.project_id,
    p.project_name,
    p.is_suspended,
    t.template_id,
    t.template_name,
    t.is_locked,
    s.state_id,
    s.state_name,
    f.field_name,
    f.field_order,
    f.field_type,
    f.is_required,
    f.add_separator,
    f.guest_access,
    f.registered_perm,
    f.author_perm,
    f.responsible_perm,
    f.description,
    f.show_in_emails,
    f.regex_check,
    f.regex_search,
    f.regex_replace,
    f.param1,
    f.param2,
    f.value_id

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s,
    tbl_fields    f

where

    p.project_id   = t.project_id  and
    t.template_id  = s.template_id and
    s.state_id     = f.state_id    and
    f.field_id     = %1            and
    f.removal_time = 0
