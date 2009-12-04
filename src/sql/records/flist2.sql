select distinct

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
    fv.value_id

from

    tbl_group_perms  gp,
    tbl_membership   ms,
    tbl_events       e,
    tbl_fields       f,
    tbl_field_perms  fp,
    tbl_field_values fv

where

    fv.event_id = e.event_id  and
    fv.field_id = f.field_id  and
    fp.field_id = f.field_id  and
    fp.group_id = gp.group_id and
    ms.group_id = gp.group_id and

    e.record_id   = %1 and
    fv.event_id   = %2 and
    f.state_id    = %3 and
    ms.account_id = %6 and
    fp.perms      = %7

union

select distinct

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
    fv.value_id

from

    tbl_events       e,
    tbl_fields       f,
    tbl_field_values fv

where

    fv.event_id = e.event_id and
    fv.field_id = f.field_id and

    e.record_id = %1 and
    fv.event_id = %2 and
    f.state_id  = %3 and

  ( f.author_perm      >= %7 and %6 = %4 or
    f.responsible_perm >= %7 and %6 = %5 or
    f.registered_perm  >= %7 and %6 <> 0 )

union

select distinct

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
    fv.value_id

from

    tbl_events       e,
    tbl_templates    t,
    tbl_states       s,
    tbl_fields       f,
    tbl_field_values fv

where

    fv.event_id   = e.event_id    and
    fv.field_id   = f.field_id    and
    f.state_id    = s.state_id    and
    s.template_id = t.template_id and

    e.record_id = %1 and
    fv.event_id = %2 and
    f.state_id  = %3 and

    t.guest_access = 1 and
    f.guest_access = 1

order by field_order
