insert into tbl_role_trans
(state_id_from, state_id_to, role)

    select distinct

        sdf.state_id,
        sdt.state_id,
        rt.role

    from

        tbl_states     ssf,
        tbl_states     sst,
        tbl_states     sdf,
        tbl_states     sdt,
        tbl_role_trans rt

    where

        ssf.template_id = %1 and
        sst.template_id = %1 and
        sdf.template_id = %2 and
        sdt.template_id = %2 and

        rt.state_id_from = ssf.state_id   and
        rt.state_id_to   = sst.state_id   and
        ssf.state_name   = sdf.state_name and
        sst.state_name   = sdt.state_name

    order by

        sdf.state_id,
        sdt.state_id,
        rt.role
