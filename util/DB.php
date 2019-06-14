<?php

namespace go1\rest\util;

use Doctrine\DBAL\Connection;
use PDO;

class DB
{
    const OBJ      = PDO::FETCH_OBJ;
    const ARR      = PDO::FETCH_ASSOC;
    const COL      = PDO::FETCH_COLUMN;
    const INTEGER  = PDO::PARAM_INT;
    const INTEGERS = Connection::PARAM_INT_ARRAY;
    const STRING   = PDO::PARAM_STR;
    const STRINGS  = Connection::PARAM_STR_ARRAY;
}
