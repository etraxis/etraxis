delete from tbl_group_perms

where

    (group_id    in (select group_id    from tbl_groups    where project_id = %1)) or
    (template_id in (select template_id from tbl_templates where project_id = %1))
