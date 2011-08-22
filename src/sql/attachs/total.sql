select sum(attachment_size)
from tbl_attachments
where is_removed = 0
