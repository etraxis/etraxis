update tbl_reminders

set reminder_name = '%2',
    subject_text  = '%3',
    state_id      = %4,
    group_id      = %5,
    group_flag    = %6

where reminder_id = %1
