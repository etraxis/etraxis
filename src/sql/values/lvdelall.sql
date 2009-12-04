delete from tbl_list_values

where

    field_id = %1 and
    int_value not in

(select distinct

    lv.int_value

from

    tbl_field_values fv,
    tbl_list_values lv

where

    fv.field_id = %1 and
    lv.field_id = %1 and
    lv.int_value = fv.value_id)
