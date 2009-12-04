select

    p.project_id,
    p.project_name,
    p.description as p_description,
    p.is_suspended,
    t.template_id,
    t.template_name,
    t.template_prefix,
    t.critical_age,
    t.frozen_time,
    t.description,
    t.is_locked,
    t.guest_access,
    t.registered_perm,
    t.author_perm,
    t.responsible_perm

from

    tbl_projects  p,
    tbl_templates t

where

    p.project_id  = t.project_id and
    t.template_id = %1
