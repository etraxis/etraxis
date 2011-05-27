select

    f.field_id,
    f.field_name,
    f.field_order,
    f.field_type,
    f.regex_search,
    f.regex_replace,
    fv.value_id

from

    tbl_events       e,
    tbl_fields       f,
    tbl_field_values fv

where

    fv.event_id      = e.event_id and
    fv.field_id      = f.field_id and
    fv.is_latest     = 1          and
    f.removal_time   = 0          and
    f.show_in_emails = 1          and
    e.record_id      = %1         and
    f.state_id       = %2

order by field_order
