insert into tbl_list_values
(field_id, int_value, str_value)

    select distinct

        fd.field_id,
        lv.int_value,
        lv.str_value

    from

        tbl_states      ss,
        tbl_states      sd,
        tbl_fields      fs,
        tbl_fields      fd,
        tbl_list_values lv

    where

        ss.template_id = %1            and
        sd.template_id = %2            and
        ss.state_id    = fs.state_id   and
        sd.state_id    = fd.state_id   and
        ss.state_name  = sd.state_name and
        fs.field_name  = fd.field_name and
        lv.field_id    = fs.field_id

    order by

        fd.field_id,
        lv.int_value
