select distinct

    a.account_id,
    a.username,
    a.fullname

from

    tbl_accounts    a,
    tbl_membership  ms,
    tbl_group_perms gp,
    tbl_states      s,
    tbl_records     r

where

    ms.group_id    = gp.group_id   and
    ms.account_id  = a.account_id  and
    gp.template_id = s.template_id and
    s.state_id     = r.state_id    and
    r.record_id    = %1            and

    a.account_id not in

       (select a.account_id

        from

            tbl_accounts          a,
            tbl_record_subscribes rs

        where

            rs.record_id     = %1  and
            rs.subscribed_by = %2  and
            rs.account_id    <> %2 and
            rs.account_id    = a.account_id)

order by

    fullname,
    username
