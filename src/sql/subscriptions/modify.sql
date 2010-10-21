update tbl_subscribes

set subscribe_name  = '%2',
    carbon_copy        = '%3',
    subscribe_flags = '%4'

where subscribe_id = %1
