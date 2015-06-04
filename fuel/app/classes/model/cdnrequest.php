<?php

namespace Model;

class CdnRequest extends \Orm\Model {

    protected static $_table_name = 'cdnrequest';
    protected static $_properties = array(
        'id',
        'cdnType',
        'accountName',
        'estimatedSeconds',
        'progressUri',
        'purgeId',
        'supportId',
        'httpStatus',
        'detail',
        'pingAfterSeconds',
        'done',
        'created_at',
        'updated_at',
    );

    /*
      CREATE TABLE cdnrequest(
      id integer primary key autoincrement,
      cdnType text,
      accountName text,
      estimatedSeconds text,
      progressUri text,
      purgeId text,
      supportId text,
      httpStatus int,
      detail text,
      pingAfterSeconds int,
      done int,
      created_at int,
      updated_at int
      );
     */
}
