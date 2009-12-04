delete from tbl_list_values

where

    field_id in
       (select f.field_id
        from tbl_fields f, tbl_states s, tbl_templates t
        where f.state_id = s.state_id and s.template_id = t.template_id and t.project_id = %1)
