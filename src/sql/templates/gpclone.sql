insert into tbl_group_perms
(group_id, template_id, perms)

    select distinct gp.group_id, %2, gp.perms
    from tbl_groups g, tbl_group_perms gp
    where g.project_id is null and g.group_id = gp.group_id and gp.template_id = %1

    union

    select distinct gd.group_id, %2, gp.perms
    from tbl_groups gs, tbl_groups gd, tbl_group_perms gp
    where gp.template_id = %1
      and gs.project_id  = %3
      and gd.project_id  = %4
      and gs.group_id    = gp.group_id
      and gs.group_name  = gd.group_name
