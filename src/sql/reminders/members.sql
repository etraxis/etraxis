select

    a.email

from

    tbl_accounts   a,
    tbl_membership m

where

    m.group_id    = %1 and
    a.locale      = %2 and
    a.is_disabled = 0  and
    a.account_id  = m.account_id

order by

    a.fullname,
    a.username
