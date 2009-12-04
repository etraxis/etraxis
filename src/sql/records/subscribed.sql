select

    a.account_id,
    a.username,
    a.fullname

from

    tbl_accounts          a,
    tbl_record_subscribes rs

where

    rs.record_id     = %1  and
    rs.subscribed_by = %2  and
    rs.account_id    <> %2 and
    rs.account_id    = a.account_id

order by

    username,
    fullname
