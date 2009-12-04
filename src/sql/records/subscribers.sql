select

    a.fullname,
    a.email,
    a.locale

from

    tbl_accounts          a,
    tbl_record_subscribes rs

where

    rs.record_id     = %1           and
    rs.account_id    = %2           and
    rs.subscribed_by = a.account_id and
    a.account_id     <> %2          and
    a.locale         = %3
