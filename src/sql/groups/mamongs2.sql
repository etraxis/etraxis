select distinct

    a.username,
    a.fullname,
    a.email,
    a.description,
    a.is_ldapuser,
    a.is_admin,
    a.is_disabled,
    a.locale

from

    tbl_accounts   a,
    tbl_membership m

where

    m.account_id = a.account_id and
    m.group_id in (%1)

order by a.username
