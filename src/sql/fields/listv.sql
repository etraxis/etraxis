select

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type,
    f.is_required,
    f.add_separator,
    f.description,
    f.param1,
    f.param2,
    fv.value_id

from

    tbl_events       e,
    tbl_fields       f,
    tbl_field_values fv

where

    e.record_id    = %1         and
    f.state_id     = %2         and
    f.removal_time = 0          and
    fv.event_id    = e.event_id and
    fv.field_id    = f.field_id and
    fv.is_latest   = 1

order by field_order
