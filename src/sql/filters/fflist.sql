select

    f.field_id,
    f.field_type,
    ff.param1,
    ff.param2

from

    tbl_fields        f,
    tbl_filter_fields ff

where

    ff.field_id  = f.field_id and
    ff.filter_id = %1
