insert into tbl_group_trans
(state_id_from, state_id_to, group_id)

    select distinct

        sdf.state_id,
        sdt.state_id,
        gt.group_id

    from

        tbl_groups      g,
        tbl_states      ssf,
        tbl_states      sst,
        tbl_states      sdf,
        tbl_states      sdt,
        tbl_group_trans gt

    where

        ssf.template_id = %1 and
        sst.template_id = %1 and
        sdf.template_id = %2 and
        sdt.template_id = %2 and

        gt.state_id_from = ssf.state_id   and
        gt.state_id_to   = sst.state_id   and
        ssf.state_name   = sdf.state_name and
        sst.state_name   = sdt.state_name and
        g.group_id       = gt.group_id    and

        g.project_id is null

    union

    select distinct

        sdf.state_id,
        sdt.state_id,
        gd.group_id

    from

        tbl_groups      gs,
        tbl_groups      gd,
        tbl_states      ssf,
        tbl_states      sst,
        tbl_states      sdf,
        tbl_states      sdt,
        tbl_group_trans gt

    where

        ssf.template_id = %1 and
        sst.template_id = %1 and
        sdf.template_id = %2 and
        sdt.template_id = %2 and
        gs.project_id   = %3 and
        gd.project_id   = %4 and

        gt.state_id_from = ssf.state_id   and
        gt.state_id_to   = sst.state_id   and
        ssf.state_name   = sdf.state_name and
        sst.state_name   = sdt.state_name and
        gs.group_name    = gd.group_name  and
        gs.group_id      = gt.group_id
