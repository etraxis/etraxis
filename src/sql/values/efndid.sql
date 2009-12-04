select e.event_id

from

    tbl_events       e,
    tbl_field_values fv

where

    e.record_id  = %1 and
    fv.field_id  = %2 and
    fv.event_id  = e.event_id and
    fv.is_latest = 1
