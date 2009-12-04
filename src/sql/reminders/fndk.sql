select reminder_id
from tbl_reminders
where account_id = %1 and lower(reminder_name) = '%2'
