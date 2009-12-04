select distinct

    a.account_id,
    a.username,
    a.fullname,
    a.email

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
    a.locale      = %2             and
    a.is_disabled = 0

order by

    fullname,
    username
