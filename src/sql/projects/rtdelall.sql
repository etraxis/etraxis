delete from tbl_role_trans

where

    (state_id_from in (select s.state_id from tbl_states s, tbl_templates t where s.template_id = t.template_id and t.project_id = %1)) or
    (state_id_to   in (select s.state_id from tbl_states s, tbl_templates t where s.template_id = t.template_id and t.project_id = %1))
