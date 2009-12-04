select reminder_id
from tbl_reminders
where reminder_id <> %1 and account_id = %2 and lower(reminder_name) = '%3'
