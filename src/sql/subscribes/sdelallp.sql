delete from tbl_subscribes

where

    (subscribe_param = %1 and subscribe_type = 2) or
    (subscribe_param in (select template_id from tbl_templates where project_id = %1) and subscribe_type = 3)
