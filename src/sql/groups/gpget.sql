select gp.perms
from tbl_group_perms gp
where gp.group_id = %1 and gp.template_id = %2
