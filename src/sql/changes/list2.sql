select

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type,
    f.state_id,
    ch.new_value_id

from

    tbl_groups      g,
    tbl_membership  ms,
    tbl_changes     ch,
    tbl_fields      f,
    tbl_field_perms fp

where

    ch.field_id   = f.field_id and
    ch.event_id   = %1         and
    fp.field_id   = f.field_id and
    fp.group_id   = g.group_id and
    ms.group_id   = g.group_id and
    ms.account_id = %4         and
    fp.perms      = 1

union

select

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type,
    f.state_id,
    ch.new_value_id

from

    tbl_changes ch,
    tbl_fields  f

where

    ch.field_id = f.field_id and
    ch.event_id = %1         and

  ( f.author_perm      >= 1 and %4 = %2 or
    f.responsible_perm >= 1 and %4 = %3 or
    f.registered_perm  >= 1 and %4 <> 0 )

union

select

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type,
    f.state_id,
    ch.new_value_id

from

    tbl_changes   ch,
    tbl_templates t,
    tbl_states    s,
    tbl_fields    f

where

    s.template_id = t.template_id and
    f.state_id    = s.state_id    and
    ch.field_id   = f.field_id    and
    ch.event_id   = %1            and

    t.guest_access = 1 and
    f.guest_access = 1

union

select

    null as field_id,
    null as field_name,
    0    as field_order,
    null as field_type,
    null as state_id,
    ch.new_value_id

from

    tbl_changes ch

where

    ch.field_id is null and
    ch.event_id = %1

order by state_id, field_order
