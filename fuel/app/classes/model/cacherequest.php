<?php

class Model_CacheRequest extends Orm\Model {

    protected static $_table_name = 'cacherequest';

    protected static $_properties = array(
        'id',
        'estimatedSeconds',
        'progressUri',
        'purgeId',
        'supportId',
        'httpStatus',
        'detail',
        'pingAfterSeconds',
        'create_at',
        'done',
    );

}
