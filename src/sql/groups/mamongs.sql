select

    a.account_id,
    a.username,
    a.fullname,
    a.is_ldapuser

from

    tbl_accounts   a,
    tbl_membership m

where

    m.group_id   = %1 and
    m.account_id = a.account_id

order by

    a.fullname,
    a.username
