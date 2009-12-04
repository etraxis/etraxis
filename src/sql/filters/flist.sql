select distinct

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type

from

    tbl_group_perms gp,
    tbl_membership  ms,
    tbl_fields      f,
    tbl_field_perms fp

where

    fp.field_id = f.field_id  and
    fp.group_id = gp.group_id and
    ms.group_id = gp.group_id and

    f.state_id    = %1 and
    ms.account_id = %2 and
    fp.perms      = %3

order by field_order
