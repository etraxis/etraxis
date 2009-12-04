select

    count(record_id) as amount,
    week

from

   (select

        record_id,
        %3((creation_time + 259200 + (%2)) / 604800) as week

    from

        tbl_templates t,
        tbl_states    s,
        tbl_records   r

    where

        r.state_id    = s.state_id    and
        s.template_id = t.template_id and
        t.project_id  = %1) r

group by week
order by week
