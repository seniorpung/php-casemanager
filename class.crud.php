<?php
class CRUD
{
    private $table;
    private $primary_key;
    private $unique_field;
    public $error = array();
    public $message = array();
    public $mysqli;
    public function __construct($table = '', $primary_key = '', $unique_field = '')
    {
        $this->mysqli = '';
        $this->error = array();
        if ($table)
            $this->table = $table;
        if ($primary_key)
            $this->primary_key = $primary_key;
        if ($unique_field)
            $this->unique_field = $unique_field;
    }
    public function setDBTable($table)
    {
        $this->table = $table;
    }
    public function setPrimaryKey($primary_key)
    {
        $this->primary_key = $primary_key;
    }
    public function save($data = array())
    {
        if (count($data) > 0) {
            $data = $this->trim_array_values($data);
            $data = $this->mysql_real_escape_data_array($data);
            $set_arr = array();

            if (empty($this->unique_field) || $this->isUnique($this->unique_field, $data[$this->unique_field], $data[$this->primary_key])) {
                foreach ($data as $k => $v) {
                    if ($k != $this->primary_key)
                        $set_arr[] = '' . $k . ' = \'' . $v . '\' ';
                }

                if (isset($data[$this->primary_key]) && !empty($data[$this->primary_key])) {
                    $set_value = implode(', ', $set_arr);
                    $update_stmt = 'UPDATE ' . $this->table . ' set ' . $set_value . ' WHERE ' . $this->primary_key . '=' . $data[$this->primary_key] . ' LIMIT 1';

                    if (mysqli_query($this->mysqli, $update_stmt)) {
                        $this->message[] = 'Updated Successfully !';
                        return $data[$this->primary_key];
                    } else {
                        $this->error[] = ucwords($set_value) . ' already exists !';
                        return false;
                    }
                } else {
                    $data[$this->primary_key] = '';
                    $set_value = implode(', ', $set_arr);
                    $insert_stmt = 'INSERT INTO ' . $this->table . ' set ' . $set_value . '';
                    if (mysqli_query($this->mysqli, $insert_stmt)) {
                        $this->message[] = 'Added Successfully !';
                        return mysqli_insert_id($this->mysqli);
                    } else {
                        $this->error[] = ucwords($set_value) . ' already exists !';
                        return false;
                    }
                }
            } else {
                $this->error[] = '"' . $data[$this->unique_field] . '" already exists !';
                return false;
            }
        }
    }
    public function replace($data = array())
    {
        if (count($data) > 0) {
            $data = $this->trim_array_values($data);
            $data = $this->mysql_real_escape_data_array($data);
            $set_arr = array();

            foreach ($data as $k => $v) {
                //if ($k != $this->primary_key)
                $set_arr[] = '' . $k . ' = \'' . $v . '\' ';
            }

            $set_value = implode(', ', $set_arr);

            $replace_stmt = 'REPLACE INTO ' . $this->table . ' set ' . $set_value;
            if (mysqli_query($this->mysqli, $replace_stmt)) {
                $this->message[] = 'Add Successfully !';
                return true;
            } else {
                $this->error[] = 'Error while insert. <br />' . mysqli_error($this->mysqli);
                return true;
            }
        }
    }
    public function checkReferenceInTableByFindInSet($foreign_tbl_keys = array(), $ref_value = '')
    {
        if (is_array($foreign_tbl_keys) && count($foreign_tbl_keys) && !empty($ref_value)) {
            foreach ($foreign_tbl_keys as $foreign_tbl => $foreign_key) {
                $sql = mysqli_query($this->mysqli, 'SELECT COUNT(1) AS num_ref FROM ' . $foreign_tbl . ' WHERE (' . $foreign_key . ' = \'' . $ref_value . '\') ');
                $res = mysqli_fetch_assoc($sql);
                if ($res['num_ref'] > 0)
                    return true;
            }
        }
        return false;
    }
    public function delete($ids = array())
    {
        if (is_array($ids) && count($ids) > 0)
            return mysqli_query($this->mysqli, 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primary_key . ' IN (' . implode(', ', $ids) . ')');
        else
            return mysqli_query($this->mysqli, 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primary_key . ' = ' . $ids);
    }
    public function mysql_real_escape_data_array($value_arr)
    {
        foreach ($value_arr as $k => $v) {
            if (is_array($v))
                $value_arr[$k] = $this->mysql_real_escape_data_array($v);
            else //if (!get_magic_quotes_gpc())
                $value_arr[$k] = mysqli_real_escape_string($this->mysqli, $v);
        }
        return $value_arr;
    }
    public function trim_array_values($value_arr)
    {
        foreach ($value_arr as $k => $v) {
            if (is_array($v))
                $value_arr[$k] = $this->trim_array_values($v);
            else
                $value_arr[$k] = trim($v);
        }
        return $value_arr;
    }
    public function isUnique($field_name, $value, $primary_key_value = '')
    {
        $where_str = ' WHERE ' . $field_name . ' = \'' . $value . '\' ';
        if ($primary_key_value > 0)
            $where_str .= ' AND ' . $this->primary_key . ' <> ' . $primary_key_value;

        $qry = mysqli_query($this->mysqli, 'SELECT ' . $this->primary_key . ' FROM ' . $this->table . $where_str . ' LIMIT 1 ');
        if (mysqli_num_rows($qry) > 0)
            return false;
        else
            return true;
    }
    public function fetch_records($sql = '')
    {
        if ($sql) {
            $res = mysqli_query($this->mysqli, $sql);
            if ($res) {
                if (mysqli_num_rows($res) > 0) {
                    $records = array();
                    while ($row = mysqli_fetch_assoc($res)) {
                        $records[] = $row;
                    }
                    return $records;
                }
            }
        }
        return false;
    }
    public function FindAll($table, $fetch_fields = array(), $conditions = array(), $lower_limit = 0, $limit = 0, $sort_by_data = array(), $debug = false)
    {
        $records = array();

        if (is_array($fetch_fields) && count($fetch_fields) > 0)
            $fields = implode(', ', $fetch_fields);
        else
            $fields = '*';

        $where_cond = '';
        if (is_array($conditions) && count($conditions) > 0)
            $where_cond = ' WHERE ' . implode(' AND ', $conditions);

        $order_str = '';
        if (is_array($sort_by_data) && count($sort_by_data) > 0) {
            $order_str = ' ORDER BY ';
            foreach ($sort_by_data as $v)
                $order_str .= $v[0] . ' ' . $v[1] . ', ';
            $order_str = substr($order_str, 0, strlen($order_str) - 2);
        }
        $stmt = 'SELECT ' . $fields . '  FROM ' . $table . $where_cond . ' ' . $order_str . ' ' . ($limit > 0 ? ' LIMIT ' . $lower_limit . ', ' . $limit : '');

        if ($debug)
            echo $stmt;

        $qry = mysqli_query($this->mysqli, $stmt);
        if ($qry) {
            if (mysqli_num_rows($qry) > 0) {
                while ($rows = mysqli_fetch_assoc($qry)) {
                    $records[] = $rows;
                }
            }
        }
        return $records;
    }
    public function FindRow($table, $fetch_fields = array(), $conditions = array(), $sort_by_data = array())
    {
        $records = false;
        $records = $this->FindAll($table, $fetch_fields, $conditions, 0, 1, $sort_by_data);
        if (is_array($records) && count($records) > 0)
            return $records[0];
        return $records;
    }
    public function isUniqueForReplace($field_name, $value, $primary_key_value = '', $table_name)
    {
        $where_str = ' WHERE ' . $field_name . ' = \'' . $value . '\' ';
        if ($primary_key_value > 0)
            $where_str .= ' AND ' . $this->primary_key . ' <> ' . $primary_key_value;

        $qry = mysqli_query($this->mysqli, 'SELECT * FROM ' . $table_name . $where_str . ' LIMIT 1 ');
        if (mysqli_num_rows($qry) > 0)
            return false;
        else
            return true;
    }
    public function run_sql_query($sql = '')
    {
        if ($sql)
            return mysqli_query($this->mysqli, $sql);
        return false;
    }
    public function get_record_count($sql = '')
    {
        if ($sql) {
            $res = mysqli_query($this->mysqli, $sql);
            if ($res)
                return mysqli_num_rows($res);
        }
        return 0;
    }
    public function check_require_fields($required_fields_arr = array())
    {
        $requires_arr = array();
        if (is_array($required_fields_arr) && count($required_fields_arr) > 0) {
            foreach ($required_fields_arr as $k => $v) {
                if (empty($v)) {
                    $requires_arr[] = $k;
                }
            }
            return $requires_arr;
        }
        return false;
    }
    public function FindRecordsCount($table, $conditions = array())
    {
        $records = array();
        $where_cond = '';
        if (is_array($conditions) && count($conditions) > 0)
            $where_cond = ' WHERE ' . implode(' AND ', $conditions);
        $qry = mysqli_query($this->mysqli, 'SELECT COUNT(1) AS total FROM ' . $table . $where_cond);
        $rows = mysqli_fetch_assoc($qry);
        return $rows['total'];
    }
    public function getTableColumnInfo($table_name)
    {
        $columns = array();
        $columns['DataType'] = array();
        $columns['Length'] = array();
        $columns['Values'] = array();
        $tbl_schema = $this->fetch_records('SHOW COLUMNS FROM ' . $table_name . ' ');
        if (count($tbl_schema) > 0) {
            foreach ($tbl_schema as $column_row) {
                preg_match('/[(]{1}(.*)[)]{1}/', $column_row['Type'], $temp_arr);

                $columns['DataType'][$column_row['Field']] = '';
                $columns['Length'][$column_row['Field']] = 0;

                if (is_numeric($temp_arr[1]))
                    $columns['Length'][$column_row['Field']] = $temp_arr[1];
                elseif (!empty($temp_arr[1]))
                    $columns['Values'][$column_row['Field']] = explode(',', $temp_arr[1]);
            }
        }
        return $columns;
    }
    public function validateDataLength($data = array(), $columns)
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $k => $v) {
                if ($columns['Length'][$k] > 0) {
                    if (strlen($v) > $columns['Length'][$k]) {
                        $this->error[] = 'Maximum ' . $columns['Length'][$k] . ' characters allowed for ' . $k . '.';
                        return false;
                    }
                } else if (is_array($columns['Values'][$k]) && count($columns['Values'][$k]) > 0) {
                    if (!in_array("'" . $v . "'", $columns['Values'][$k])) {
                        $this->error[] = '"' . $v . '" not allowed for ' . $k . '.';
                        return false;
                    }
                }
            }
        }
        return true;
    }
    public function deleteFromTable($table_name, $where_cond = array())
    {
        $this->run_sql_query('DELETE FROM ' . $table_name . ' WHERE ' . implode(', ', $where_cond));
    }
    public function checkReferenceInTableByKey($foreign_tbl_keys = array(), $ref_value = '')
    {
        if (is_array($foreign_tbl_keys) && count($foreign_tbl_keys) && !empty($ref_value)) {
            foreach ($foreign_tbl_keys as $foreign_tbl => $foreign_key) {
                $sql = mysqli_query($this->mysqli, 'SELECT COUNT(1) AS num_ref FROM ' . $foreign_tbl . ' WHERE ' . $foreign_key . ' = \'' . $ref_value . '\' ');
                $res = mysqli_fetch_assoc($sql);

                if ($res['num_ref'] > 0)
                    return true;
            }
        }
        return false;
    }
    public function FindVar($table, $fetch_field_name, $conditions = array(), $sort_by_data = array(), $debug = false)
    {
        $records = $this->FindRow($table, array($fetch_field_name), $conditions, $sort_by_data, $debug);
        if (is_array($records) && count($records) > 0) {
            return $records[$fetch_field_name];
        }
        return null;
    }
    public function getValueByField($make_id, $field_id, $field_name, $table)
    {
        $sqlStmt = "Select " . $field_name . " from " . $table . " where " . $field_id . "='" . $make_id . "' limit 1";
        $result = mysqli_query($this->mysqli, $sqlStmt);
        if ($result) {
            while ($query = mysqli_fetch_array($result)) {
                $value = $query[$field_name];
                return $value;
                break;
            }
        }
        return '';
    }
}
?>