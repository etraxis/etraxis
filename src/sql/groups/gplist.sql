select

    gp.perms as perms

from

    tbl_group_perms gp,
    tbl_membership  ms

where

    gp.template_id = %1 and
    ms.account_id  = %4 and
    ms.group_id    = gp.group_id

union

select author_perm as perms
from tbl_templates
where template_id = %1 and %2 = %4

union

select responsible_perm as perms
from tbl_templates
where template_id = %1 and %3 = %4

union

select registered_perm as perms
from tbl_templates
where template_id = %1 and %4 <> 0
