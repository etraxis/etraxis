select

    template_id,
    template_name,
    template_prefix,
    critical_age,
    frozen_time,
    description,
    is_locked

from tbl_templates
where project_id = %1
order by %2
