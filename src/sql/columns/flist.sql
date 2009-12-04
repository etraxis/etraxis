select distinct

    s.state_name,
    f.field_name,
    f.field_type

from

    tbl_groups      g,
    tbl_membership  ms,
    tbl_states      s,
    tbl_fields      f,
    tbl_field_perms fp

where

    s.state_id     = f.state_id and
    ms.group_id    = g.group_id and
    ms.account_id  = %1         and
    f.removal_time = 0          and
    fp.field_id    = f.field_id and
    fp.group_id    = g.group_id and
    fp.perms       = 1

order by

    state_name,
    field_name,
    field_type
