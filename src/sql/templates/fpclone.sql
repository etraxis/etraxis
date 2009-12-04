insert into tbl_field_perms
(field_id, group_id, perms)

    select distinct

        fd.field_id,
        fp.group_id,
        fp.perms

    from

        tbl_groups      g,
        tbl_states      ss,
        tbl_states      sd,
        tbl_fields      fs,
        tbl_fields      fd,
        tbl_field_perms fp

    where

        ss.template_id = %1            and
        sd.template_id = %2            and
        ss.state_id    = fs.state_id   and
        sd.state_id    = fd.state_id   and
        ss.state_name  = sd.state_name and
        fs.field_name  = fd.field_name and
        fp.field_id    = fs.field_id   and
        fp.group_id    = g.group_id    and

        g.project_id is null

    union

    select distinct

        fd.field_id,
        gd.group_id,
        fp.perms

    from

        tbl_groups      gs,
        tbl_groups      gd,
        tbl_states      ss,
        tbl_states      sd,
        tbl_fields      fs,
        tbl_fields      fd,
        tbl_field_perms fp

    where

        ss.template_id = %1            and
        sd.template_id = %2            and
        gs.project_id  = %3            and
        gd.project_id  = %4            and
        ss.state_id    = fs.state_id   and
        sd.state_id    = fd.state_id   and
        ss.state_name  = sd.state_name and
        gs.group_name  = gd.group_name and
        fs.field_name  = fd.field_name and
        fp.field_id    = fs.field_id   and
        fp.group_id    = gs.group_id
