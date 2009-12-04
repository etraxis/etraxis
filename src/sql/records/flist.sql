select

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type,
    f.is_required,
    f.add_separator,
    f.regex_check,
    f.regex_search,
    f.regex_replace,
    f.param1,
    f.param2,
    fv.value_id,
    e.event_time

from

    tbl_groups       g,
    tbl_membership   ms,
    tbl_events       e,
    tbl_fields       f,
    tbl_field_perms  fp,
    tbl_field_values fv

where

    fp.field_id    = f.field_id and
    fp.group_id    = g.group_id and
    ms.group_id    = g.group_id and
    fv.event_id    = e.event_id and
    fv.field_id    = f.field_id and
    fv.is_latest   = 1          and
    f.removal_time = 0          and
    e.record_id    = %1         and
    f.state_id     = %2         and
    ms.account_id  = %5         and
    fp.perms       = %6

union

select

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type,
    f.is_required,
    f.add_separator,
    f.regex_check,
    f.regex_search,
    f.regex_replace,
    f.param1,
    f.param2,
    fv.value_id,
    e.event_time

from

    tbl_events       e,
    tbl_fields       f,
    tbl_field_values fv

where

    fv.event_id    = e.event_id and
    fv.field_id    = f.field_id and
    fv.is_latest   = 1          and
    f.removal_time = 0          and
    e.record_id    = %1         and
    f.state_id     = %2         and

  ( f.author_perm      >= %6 and %5 = %3 or
    f.responsible_perm >= %6 and %5 = %4 or
    f.registered_perm  >= %6 and %5 <> 0 )

order by field_order
