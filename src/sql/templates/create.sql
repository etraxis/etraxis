insert into tbl_templates

   (project_id,
    template_name,
    template_prefix,
    critical_age,
    frozen_time,
    description,
    is_locked,
    guest_access,
    registered_perm,
    author_perm,
    responsible_perm)

values (%1, '%2', '%3', %4, %5, '%6', 1, %7, 0, 0, 0)
