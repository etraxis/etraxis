select

    int_value,
    str_value

from tbl_list_values
where field_id = %1
order by int_value
