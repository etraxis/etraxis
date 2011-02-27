select distinct

    a.account_id,
    a.username,
    a.fullname

from

    tbl_accounts        a,
    tbl_membership      ms,
    tbl_state_assignees sa

where

    a.account_id  = ms.account_id and
    sa.group_id   = ms.group_id   and
    sa.state_id   = %1            and
    a.is_disabled = 0

union

select distinct

    a.account_id,
    a.username,
    a.fullname

from

    tbl_accounts a

where

    a.account_id  = %2 and
    a.is_disabled = 0

order by

    fullname,
    username
