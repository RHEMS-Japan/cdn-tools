<?php

class Model_CacheProgress extends Orm\Model {
    
    protected static $_table_name = 'cacheprogress';

    protected static $_properties = array(
        'id',
        'originalEstimatedSeconds',
        'progressUri',
        'purgeId',
        'supportId',
        'completionTime',
        'submittedBy',
        'purgeStatus',
        'submissionTime',
        'pingAfterSeconds',
        'create_at',
        'done',
    );

}
