select

    a.account_id,
    a.email,
    s.carbon_copy,
    s.subscribe_flags

from

    tbl_accounts   a,
    tbl_subscribes s

where

    s.is_activated <> 0 and

    (s.subscribe_type = 2 and s.subscribe_param = %1 or
     s.subscribe_type = 3 and s.subscribe_param = %2 or
     s.subscribe_type = 1) and

    a.account_id = s.account_id and
    a.account_id in

       (select distinct

            a.account_id

        from

            tbl_accounts    a,
            tbl_templates   t,
            tbl_group_perms gp,
            tbl_membership  ms

        where

            t.template_id = gp.template_id and
            ms.group_id   = gp.group_id    and
            ms.account_id = a.account_id   and
            t.project_id  = %1             and
            a.locale      = %3             and
            a.is_disabled = 0)
