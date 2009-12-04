insert into tbl_text_values

   (value_token,
    text_value)

values (:value_token, empty_clob())
returning text_value into :text_value
