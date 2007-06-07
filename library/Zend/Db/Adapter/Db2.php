<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 *
 */

/** Zend_Db_Adapter_Abstract */
require_once 'Zend/Db/Adapter/Abstract.php';

/** Zend_Db_Adapter_Db2_Exception */
require_once 'Zend/Db/Adapter/Db2/Exception.php';

/** Zend_Db_Statement_Db2 */
require_once 'Zend/Db/Statement/Db2.php';


/**
 * @package    Zend_Db
 * @copyright  Copyright (c) 2005-2007 Zend Technologies Inc. (http://www.zend.com)
 * @license    Zend Framework License version 1.0
 * @author     Joscha Feth <jffeth@de.ibm.com>
 * @author     Salvador Ledezma <ledezma@us.ibm.com>
 */

class Zend_Db_Adapter_Db2 extends Zend_Db_Adapter_Abstract
{
    /**
     * User-provided configuration.
     *
     * Basic keys are:
     *
     * username   => (string)  Connect to the database as this username.
     * password   => (string)  Password associated with the username.
     * host       => (string)  What host to connect to (default 127.0.0.1)
     * dbname     => (string)  The name of the database to user
     * protocol   => (string)  Protocol to use, defaults to "TCPIP"
     * port       => (integer) Port number to use for TCP/IP if protocol is "TCPIP"
     * persistent => (boolean) Set TRUE to use a persistent connection (db2_pconnect)
     *
     * @var array
     */
    protected $_config = array(
        'dbname'       => null,
        'username'     => null,
        'password'     => null,
        'host'         => 'localhost',
        'port'         => '50000',
        'protocol'     => 'TCPIP',
        'persistent'   => false
    );

    /**
     * Execution mode
     *
     * @var int execution flag (DB2_AUTOCOMMIT_ON or DB2_AUTOCOMMIT_OFF)
     * @access protected
     */
    protected $_execute_mode = DB2_AUTOCOMMIT_ON;

    /**
     * Table name of the last accessed table for an insert operation
     * This is a DB2-Adapter-specific member variable with the utmost
     * probability you might not find it in other adapters...
     *
     * @var string
     * @access protected
     */
    protected $_lastInsertTable = null;

     /**
     * Constructor.
     *
     * $config is an array of key/value pairs containing configuration
     * options.  These options are common to most adapters:
     *
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     * port           => (string) The port of the database, defaults to 50000
     * persistent     => (boolean) Whether to use a persistent connection or not, defaults to false
     * protocol       => (string) The network protocol, defaults to TCPIP
     * options        => (array)  Other database options such as autocommit, case, and cursor options
     *
     * @param array $config An array of configuration keys.
     */
    public function __construct(array $config)
    {
        if (! array_key_exists('password', $config)) {
            throw new Zend_Db_Adapter_Db2_Exception("Configuration array must have a key for 'password' for login credentials.");
        }

        if (! array_key_exists('username', $config)) {
            throw new Zend_Db_Adapter_Db2_Exception("Configuration array must have a key for 'username' for login credentials.");
        }

        if (! array_key_exists('dbname', $config)) {
            throw new Zend_Db_Adapter_Db2_Exception("Configuration array must have a key for 'dbname' that names the database instance.");
        }

        // keep the config
        $this->_config = array_merge($this->_config, (array) $config);

        // create a profiler object
        $enabled = false;
        if (array_key_exists('profiler', $this->_config)) {
            $enabled = (bool) $this->_config['profiler'];
            unset($this->_config['profiler']);
        }

        $this->_profiler = new Zend_Db_Profiler($enabled);
    }

    /**
     * Creates a connection resource.
     *
     * @return void
     */
    protected function _connect()
    {
        if (is_resource($this->_connection)) {
            // connection already exists
            return;
        }

        if (!extension_loaded('ibm_db2')) {
            throw new Zend_DB_Adapter_Db2_Exception('The IBM DB2 extension is required for this adapter but not loaded');
        }

        if ($this->_config['persistent']) {
            // use persistent connection
            $conn_func_name = 'db2_pconnect';
        } else {
            // use "normal" connection
            $conn_func_name = 'db2_connect';
        }

        if (!isset($this->_config['options'])) {
            // config options were not set, so set it to an empty array
            $this->_config['options'] = array();
        }

        if (!isset($this->_config['options']['autocommit'])) {
            // set execution mode
            $this->_config['options']['autocommit'] = &$this->_execute_mode;
        }

        if ($this->_config['host'] !== 'localhost') {
            // if the host isn't localhost, use extended connection params
            $dbname = 'DRIVER={IBM DB2 ODBC DRIVER}' .
                     ';DATABASE=' . $this->_config['dbname'] .
                     ';HOSTNAME=' . $this->_config['host'] .
                     ';PORT='     . $this->_config['port'] .
                     ';PROTOCOL=' . $this->_config['protocol'] .
                     ';UID='      . $this->_config['username'] .
                     ';PWD='      . $this->_config['password'] .';';
            $this->_connection = $conn_func_name(
                $dbname,
                null,
                null,
                $this->_config['options']
            );
        } else {
            // host is localhost, so use standard connection params
            $this->_connection = $conn_func_name(
                $this->_config['dbname'],
                $this->_config['username'],
                $this->_config['password'],
                $this->_config['options']
            );
        }

        // check the connection
        if (!$this->_connection) {
            throw new Zend_Db_Adapter_Db2_Exception(db2_conn_errormsg(), db2_conn_error());
        }
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        db2_close($this->_connection);
        $this->_connection = null;
    }

    /**
     * Returns an SQL statement for preparation.
     *
     * @param string $sql The SQL statement with placeholders.
     * @return Zend_Db_Statement_Db2
     */
    public function prepare($sql)
    {
        $this->_connect();
        $stmt = new Zend_Db_Statement_Db2($this, $sql);
        $stmt->setFetchMode($this->_fetchMode);
        return $stmt;
    }

    /**
     * Gets the execution mode
     *
     * @return int the execution mode (DB2_AUTOCOMMIT_ON or DB2_AUTOCOMMIT_OFF)
     */
    public function _getExecuteMode()
    {
        return $this->_execute_mode;
    }

    /**
     * @param integer $mode
     * @return void
     */
    public function _setExecuteMode($mode)
    {
        switch ($mode) {
            case DB2_AUTOCOMMIT_OFF:
            case DB2_AUTOCOMMIT_ON:
                $this->_execute_mode = $mode;
                db2_autocommit($this->_connection, $mode);
                break;
            default:
                throw new Zend_Db_Adapter_Db2_Exception("execution mode not supported");
                break;
        }
    }

    /**
     * Quote a raw string.
     *
     * @param string $value     Raw string
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        /**
         * Some releases of the IBM DB2 extension appear
         * to be missing the db2_escape_string() method.
         * The method was added in ibm_db2.c revision 1.53
         * according to cvs.php.net.  But the function is
         * not present in my build of PHP 5.2.1.
         */
        if (function_exists('db2_escape_string')) {
            return db2_escape_string($value);
        }
        return parent::_quote($value);
    }

    /**
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        $info = db2_server_info($this->_connection);
        $identQuote = $info->IDENTIFIER_QUOTE_CHAR;
        return $identQuote;
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        if (!$this->_connection) {
            $this->_connect();
        }
        // take the most general case and assume no z/OS
        // since listTables() takes no parameters
        $stmt = db2_tables($this->_connection);

        $tables = array();

        while ($row = db2_fetch_assoc($stmt)) {
            $tables[] = $row['TABLE_NAME'];
        }

        return $tables;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     *
     * @todo Discover integer unsigned property.
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        $sql = "SELECT DISTINCT c.tabschema, c.tabname, c.colname, c.colno,
              c.typename, c.default, c.nulls, c.length, c.scale,
              c.identity, tc.type AS tabconsttype, k.colseq
            FROM syscat.columns c
              LEFT JOIN (syscat.keycoluse k JOIN syscat.tabconst tc
                ON (k.tabschema = tc.tabschema
                  AND k.tabname = tc.tabname
                  AND tc.type = 'P'))
              ON (c.tabschema = k.tabschema
                AND c.tabname = k.tabname
                AND c.colname = k.colname)
            WHERE c.tabname = ".$this->quote($tableName);
        if ($schemaName) {
            $sql .= " AND c.tabschema = ".$this->quote($schemaName);
        }
        $sql .= " ORDER BY c.colno";

        $desc = array();
        $stmt = $this->query($sql);
        $result = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        foreach ($result as $key => $row) {
            $desc[$row['COLNAME']] = array(
                'SCHEMA_NAME'      => $row['TABSCHEMA'],
                'TABLE_NAME'       => $row['TABNAME'],
                'COLUMN_NAME'      => $row['COLNAME'],
                'COLUMN_POSITION'  => $row['COLNO']+1,
                'DATA_TYPE'        => $row['TYPENAME'],
                'DEFAULT'          => $row['DEFAULT'],
                'NULLABLE'         => (bool) ($row['NULLS'] == 'Y'),
                'LENGTH'           => $row['LENGTH'],
                'SCALE'            => $row['SCALE'],
                'PRECISION'        => ($row['TYPENAME'] == 'DECIMAL' ? $row['LENGTH'] : 0),
                'UNSIGNED'         => null, // @todo
                'PRIMARY'          => (bool) ($row['TABCONSTTYPE'] == 'P' || $row['IDENTITY'] == 'Y'),
                'PRIMARY_POSITION' => $row['COLSEQ']
            );
        }

        return $desc;
    }

    /**
     * Return the most recent value from the specified sequence in the database.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return integer
     * @throws Zend_Db_Adapter_Db2_Exception
     */
    public function lastSequenceId($sequenceName)
    {
        $this->_connect();
        $sql = 'SELECT PREVVAL FOR '.$this->quoteIdentifier($sequenceName).' AS VAL FROM SYSIBM.SYSDUMMY1';
        $stmt = $this->query($sql);
        $result = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        if ($result) {
            return $result[0]['VAL'];
        } else {
            return null;
        }
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return integer
     * @throws Zend_Db_Adapter_Db2_Exception
     */
    public function nextSequenceId($sequenceName)
    {
        $this->_connect();
        $sql = 'SELECT NEXTVAL FOR '.$this->quoteIdentifier($sequenceName).' AS VAL FROM SYSIBM.SYSDUMMY1';
        $stmt = $this->query($sql);
        $result = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        if ($result) {
            return $result[0]['VAL'];
        } else {
            return null;
        }
    }

    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * The IDENTITY_VAL_LOCAL() function gives the last generated identity value
     * in the current process, even if it was for a GENERATED column.
     *
     * @param string $tableName OPTIONAL
     * @param string $primaryKey OPTIONAL
     * @return integer
     * @throws Zend_Db_Adapter_Db2_Exception
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        $this->_connect();

        if ($tableName !== null) {
            $sequenceName = $tableName;
            if ($primaryKey) {
                $sequenceName .= "_$primaryKey";
            }
            $sequenceName .= '_seq';
            return $this->lastSequenceId($sequenceName);
        }

        $sql = 'SELECT IDENTITY_VAL_LOCAL() AS VAL FROM SYSIBM.SYSDUMMY1';
        $stmt = $this->query($sql);
        $result = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        if ($result) {
            return $result[0]['VAL'];
        } else {
            return null;
        }
    }

    /**
     * Begin a transaction.
     *
     * @return void
     */
    protected function _beginTransaction()
    {
        $this->_setExecuteMode(DB2_AUTOCOMMIT_OFF);
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    protected function _commit()
    {
        if (!db2_commit($this->_connection)) {
            throw new Zend_Db_Adapter_Db2_Exception(
                db2_conn_errormsg($this->_connection),
                db2_conn_error($this->_connection));
        }

        $this->_setExecuteMode(DB2_AUTOCOMMIT_ON);
    }

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    protected function _rollBack()
    {
        if (!db2_rollback($this->_connection)) {
            throw new Zend_Db_Adapter_Db2_Exception(
                db2_conn_errormsg($this->_connection),
                db2_conn_error($this->_connection));
        }
        $this->_setExecuteMode(DB2_AUTOCOMMIT_ON);
    }

    /**
     * Set the fetch mode.
     *
     * @param integer $mode
     * @return void
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case Zend_Db::FETCH_NUM:   // seq array
            case Zend_Db::FETCH_ASSOC: // assoc array
            case Zend_Db::FETCH_BOTH:  // seq+assoc array
            case Zend_Db::FETCH_OBJ:   // object
                $this->_fetchMode = $mode;
                break;
            default:
                throw new Zend_Db_Adapter_Db2_Exception('Invalid fetch mode specified');
                break;
        }
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param integer $count
     * @param integer $offset OPTIONAL
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count <= 0) {
            throw new Zend_Db_Adapter_Db2_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new Zend_Db_Adapter_Db2_Exception("LIMIT argument offset=$offset is not valid");
        }

        /**
         * Oracle does not implement the LIMIT clause as some RDBMS do.
         * We have to simulate it with subqueries and ROWNUM.
         * Unfortunately because we use the column wildcard "*", 
         * this puts an extra column into the query result set.
         */
        $limit_sql = "SELECT z2.*
            FROM (
                SELECT ROW_NUMBER() OVER() AS zend_db_rownum, z1.*
                FROM (
                    " . $sql . "
                ) z1
            ) z2
            WHERE z2.zend_db_rownum BETWEEN " . ($offset+1) . " AND " . ($offset+$count);
        return $limit_sql;
    }

}
