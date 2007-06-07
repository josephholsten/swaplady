<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Db_Statement
 */
require_once 'Zend/Db/Statement.php';

/**
 * Extends for Oracle.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Statement_Oracle extends Zend_Db_Statement
{

    /**
     * The connection_stmt object.
     */
    protected $_stmt;

    /**
     * Column names.
     */
    protected $_keys;

    /**
     * Fetched result values.
     */
    protected $_values;

    /**
     * closes the cursor, allowing the statement to be executed again
     *
     * @return boolean True if cursor has been closed.
     */
    public function closeCursor()
    {
        if (!$this->_stmt) {
            return false;
        }

        oci_free_statement($this->_stmt);
        $this->_stmt = false;
        return true;
    }


    /**
     * Returns the number of columns in the result set.
     *
     * @return mixed Integer number of fields, or false.
     */
    public function columnCount()
    {
        if (!$this->_stmt) {
            return false;
        }

        return oci_num_fields($this->_stmt);
    }


    /**
     * Retrieves an error code, if any, from the statement.
     *
     * @return mixed Error code, or false.
     */
    public function errorCode()
    {
        if (!$this->_stmt) {
            return false;
        }

        $error = oci_error($this->_stmt);

        if (!$error) {
            return false;
        }

        return $error['code'];
    }


    /**
     * Retrieves an array of error information, if any, from the statement.
     *
     * @return mixed Structured information about the error, or false.
     */
    public function errorInfo()
    {
        if (!$this->_stmt) {
            return false;
        }

        $error = oci_error($this->_stmt);
        if (!$error) {
            return false;
        }

        if (isset($error['sqltext'])) {
            return array(
                $error['code'],
                $error['message'],
                $error['offset'],
                $error['sqltext'],
            );
        } else {
            return array(
                $error['code'],
                $error['message'],
            );
        }
    }


    /**
     * Executes a prepared statement.
     *
     * @param array $params
     * @return void
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    public function execute(array $params = array())
    {
        $connection = $this->_connection->getConnection();
        if (!$this->_stmt) {
            $sql = $this->_joinSql();
            $this->_stmt = oci_parse($connection, $sql);
        }

        if (! $this->_stmt) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($connection));
        }

        if ($params) {
            $error = false;
            foreach (array_keys($params) as $name) {
                if (!oci_bind_by_name($this->_stmt, $name, $params[$name], -1)) {
                    $error = true;
                    break;
                }
            }
            if ($error) {
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
            }
        }

        if (!oci_execute($this->_stmt, $this->_connection->_getExecuteMode())) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }

        $this->_keys = Array();
        if ($field_num = oci_num_fields($this->_stmt)) {
            for ($i = 1; $i <= $field_num; $i++) {
                $name = oci_field_name($this->_stmt, $i);
                $this->_keys[] = $name;
            }
        }

        $this->_values = Array();
        if ($this->_keys) {
            $this->_values = array_fill(0, count($this->_keys), null);
        }
    }

    /**
     * Binds a PHP variable to a parameter in the prepared statement.
     *
     * @param mixed   $parameter
     * @param string  $variable
     * @param string  $type OPTIONAL
     * @param integer $length OPTIONAL
     * @param array   $options OPTIONAL
     * @return void
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        if (is_integer($parameter)) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception("bind by position is not supported by Oracle adapter");
        } else if (is_string($parameter)) {
            // bind by name. make sure it has a colon on it.
            if ($parameter[0] != ':') {
                $parameter = ":$parameter";
            }

            // default value
            if ($type === NULL) {
                $type = SQLT_CHR;
            }

            // default value
            if ($length === NULL) {
                $length = -1;
            }

            if (!oci_bind_by_name($this->_stmt, $parameter, $variable, $length, $type)) {
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
            }
        } else {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception('invalid $parameter value');
        }
    }

    /**
     * Fetches a row from the result set.
     *
     * @param string  $style OPTIONAL
     * @param string  $cursor OPTIONAL
     * @param integer $offset OPTIONAL
     * @return mixed
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if (!$this->_stmt) {
            return false;
        }

        // make sure we have a fetch mode
        if ($style === null) {
            $style = $this->_fetchMode;
        }

        switch ($style) {
            case Zend_Db::FETCH_NUM:
                $fetch_function = "oci_fetch_row";
                break;
            case Zend_Db::FETCH_ASSOC:
                $fetch_function = "oci_fetch_assoc";
                break;
            case Zend_Db::FETCH_BOTH:
                $fetch_function = "oci_fetch_array";
                break;
            case Zend_Db::FETCH_OBJ:
                $fetch_function = "oci_fetch_object";
                break;
            default:
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception("invalid fetch mode specified");
                break;
        }

        // fetch the next result
        $row = $fetch_function($this->_stmt);
        if (! $row && $error = oci_error($this->_stmt)) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception($error);
        }

        return $row;
    }

    /**
     * Returns the number of rows that were affected by the execution of an SQL statement.
     *
     * @return mixed Integer number of rows, or false.
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    public function rowCount()
    {
        if (!$this->_stmt) {
            return false;
        }

        $num_rows = oci_num_rows($this->_stmt);

        if ($num_rows === false) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }

        return $num_rows;
    }

    /**
     * Prepares statement handle
     *
     * @param string $sql
     * @return void
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    protected function _prepSql($sql)
    {
        $connection = $this->_connection->getConnection();
        $this->_stmt = oci_parse($connection, $sql);
        if (!$this->_stmt) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($connection));
        }
    }

    /**
     * Fetches an array containing all of the rows from a result set
     *
     * @param string $style
     * @param integer $col
     * @return mixed Result set structured per $style, or false.
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    public function fetchAll($style = null, $col = 0)
    {
        if (!$this->_stmt) {
            return false;
        }

        // make sure we have a fetch mode
        if ($style === null) {
            $style = $this->_fetchMode;
        }

        $flags = 0;

        switch ($style) {
            case Zend_Db::FETCH_BOTH:
                $flags |= OCI_NUM;
                $flags |= OCI_ASSOC;
                break;
            case Zend_Db::FETCH_NUM:
                $flags |= OCI_NUM;
                break;
            case Zend_Db::FETCH_ASSOC:
                $flags |= OCI_ASSOC;
                break;
            case Zend_Db::FETCH_OBJ:
                break;
            case Zend_Db::FETCH_COLUMN:
                $flags |= OCI_NUM;
                break;
            default:
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception("invalid fetch mode specified");
                break;
        }

        /* @todo XXX how to handle $col ? */

        $result = Array();
        if ($flags) { /* not Zend_Db::FETCH_OBJ */
            if (! ($rows = oci_fetch_all($this->_stmt, $result, 0, -1, $flags) )) {
                if ($error = oci_error($this->_stmt)) {
                    require_once 'Zend/Db/Statement/Oracle/Exception.php';
                    throw new Zend_Db_Statement_Oracle_Exception($error);
                }
                if (!$rows) {
                    return array();
                }
            }
            if ($style == Zend_Db::FETCH_COLUMN) {
                $result = $result[$col];
            }
        } else {
            while (($row = oci_fetch_object($this->_stmt)) !== false) {
                $result [] = $row;
            }
            if ($error = oci_error($this->_stmt)) {
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception($error);
            }
        }

        return $result;
    }


    /**
     * Returns the data from a single column in a result set.
     *
     * @param integer $col
     * @return mixed Structured result set, or false.
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    public function fetchColumn($col = 0)
    {
        if (!$this->_stmt) {
            return false;
        }

        if (!oci_fetch($this->_stmt)) {
            /* TODO ERROR */
        }

        $data = oci_result($this->_stmt, $col+1); //1-based
        if ($data === false) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }
        return $data;
    }


    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class
     * @param array $config
     * @return mixed Result set as object, or false.
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        if (!$this->_stmt) {
            return false;
        }

        $obj = oci_fetch_object($this->_stmt);

        if ($obj === false) {
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }

        /* @todo XXX handle parameters */

        return $obj;
    }
}

/* vim: set et fdm=syntax syn=php ft=php: */

