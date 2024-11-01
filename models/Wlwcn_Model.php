<?php

namespace Models;

use Exception;

class Wlwcn_Model
{
    protected $mwpdb;
    protected $per_page = 25;
    public $prefix = WLWCN_SETTINGS_SLUG.'_';
    protected $table;
    protected $select = '';
    protected $where = [];
    protected $where_in = [];
    protected $or_where_in = [];
    protected $where_not_in = [];
    protected $or_where_not_in = [];
    protected $or_where = [];
    protected $where_not_null = [];
    protected $or_where_not_null = [];
    protected $with_trashed = false;
    protected $prepared_params = [];
    protected $data_format = [];
    protected $where_format = [];
    protected $orderby = false;
    protected $order = 'asc';
    protected $array = [];

    function __construct($table='')
    {
        global $wpdb;
        $this->mwpdb = $wpdb;
        $plugin_prefix = $wpdb->prefix.$this->prefix;
        $this->table = $table ? $wpdb->prefix.$table : $plugin_prefix.$this->table;
        self::select();
    }

    function getPerPage()
    {
        return $this->per_page;
    }

    function whereIn($col, $vals=[])
    {
        $this->where_in += is_array($col) ? $col : [$col => $vals];

        return $this;
    }

    function whereNotIn($col, $vals=[])
    {
        $this->where_not_in += is_array($col) ? $col : [$col => $vals];

        return $this;
    }

    function orWhereIn($col, $vals=[])
    {
        $this->or_where_in += is_array($col) ? $col : [$col => $vals];

        return $this;
    }

    function orWhereNotIn($col, $vals=[])
    {
        $this->or_where_not_in += is_array($col) ? $col : [$col => $val_str];

        return $this;
    }

    function whereNotNull($col)
    {
        if(is_array($col))
        {
            $this->where_not_null += $col;
        }
        else
        {
            $this->where_not_null[] = $col;
        }

        return $this;
    }

    function orWhereNotNull($col)
    {
        if(is_array($col))
        {
            $this->or_where_not_null += $col;
        }
        else
        {
            $this->or_where_not_null[] = $col;
        }

        return $this;
    }

    function firstOrFail()
    {
        $sql = $this->select;

        $where_sql = self::_whereSql();

        $sql = $this->select.$where_sql;

        $result = self::_getPrepared($sql);

        if(!empty($result) && !empty($result[0]))
        {
            return $result [0];
        }

        throw new \Exception("Resource not found.", 404);
    }

    function findOrFail($id)
    {
        $this->where['id'] = $id;

        $sql = $this->select;

        $where_sql = self::_whereSql();

        $sql = $this->select.$where_sql;

        $result = self::_getPrepared($sql);

        if(!empty($result) && !empty($result[0]))
        {
            return $result [0];
        }

        throw new \Exception("Resource not found.", 404);
    }

    function orWhere($where=[])
    {
        $this->or_where += $where;

        return $this;
    }

    function delete($id='')
    {
        if($id)
        {
            $this->where += ['id' => $id];
        }

        if(!count($this->where))
        {
            return false;
        }

        $result = self::update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $result;
    }

    function forceDelete($id='')
    {
        if($id)
        {
            $this->where += ['id' => $id];
        }

        if(!count($this->where))
        {
            return false;
        }

        self::_setWhereFormat();

        $result = $this->mwpdb->delete(
                $this->table,
                $this->where,
                $this->where_format
            );

        return $result;
    }

    private function _formatDataAppropriately($data)
    {
        if(!empty($this->array))
        {
            foreach($data as $col => $val)
            {
                if(in_array($col, $this->array))
                {
                    $data[$col] = json_encode($val);
                }
            }
        }

        return $data;
    }

    function store($data=[])
    {
        self::_setDataFormat($data);
        $data = self::_formatDataAppropriately($data);
        $result = $this->mwpdb->insert(
                $this->table,
                $data,
                $this->data_format
            );

        if($result)
        {
            $result = $this->mwpdb->insert_id;
        }

        return $result;
    }

    function update($data=[])
    {
        self::_setDataFormat($data);
        self::_setWhereFormat();

        // update( $table, $data, $where, $data_format = null, $where_format = null );
        $data = self::_formatDataAppropriately($data);
        if(empty($this->where))
        {
            throw new Exception("Set WHERE condition before running update query.", 1);
        }

        $result = $this->mwpdb->update(
                $this->table,
                $data,
                $this->where,
                $this->data_format,
                $this->where_format
            );

        return $result;
    }

    private function _setWhereFormat()
    {
        if(!empty($this->where))
        {
            foreach($this->where as $key => $val)
            {
                array_push($this->where_format, '%s');
            }
        }
    }

    private function _setDataFormat($data=[])
    {
        foreach($data as $key => $val)
        {
            array_push($this->data_format, '%s');
        }
    }

    function orderby($orderby, $order='asc')
    {
        $this->orderby = $orderby;
        $this->order = $order;

        return $this;
    }

    private function _getPageNum($default=1)
    {
        $return = $default;

        if(isset($_GET['pg']))
        {
            $page = $_GET['pg'];
            if(wlwcn_isInteger($page) && ($page > 0))
            {
                $return = (int) $page;
            }
        }

        return $return;
    }

    function withTrashed()
    {
        $this->with_trashed = true;

        return $this;
    }

    function where($where=[])
    {
        $this->where += $where;

        return $this;
    }

    private function _getWhereSql($where, $concat='and')
    {
        $sql = '';
        $i = 0;
        $where_count = count($where);
        if($concat == 'or')
        {
            $concat = 'OR';
            $not_null = $this->or_where_not_null;
            $in = $this->or_where_in;
            $not_in = $this->or_where_not_in;
        }
        else
        {
            $concat = 'AND';
            $not_null = $this->where_not_null;
            $in = $this->where_in;
            $not_in = $this->where_not_in;
        }

        foreach($where as $key => $val)
        {
            ++$i;
            if(is_array($val))
            {
                if(count($val) == 2)
                {
                    if($val[0] == 'like')
                    {
                        $operator = "LIKE";
                        $placeholder = '"%%%s%%"';
                    }
                    else
                    {
                        $operator = $val[0];
                        $placeholder = '%s';
                    }
                    $sql .= ' `'.$key.'` '.$operator.' '.$placeholder;
                    array_push($this->prepared_params, $val[1]);
                }
                else
                {
                    if(is_null($val[1]))
                    {
                        $sql .= ' `'.$key.'` IS NULL';
                    }
                    else
                    {
                        $sql .= ' `'.$key.'` = %s';
                        array_push($this->prepared_params, $val[1]);
                    }
                }
            }
            else
            {
                if(is_null($val))
                {
                    $sql .= ' `'.$key.'` IS NULL';
                }
                else
                {
                    $sql .= ' `'.$key.'` = %s';
                    array_push($this->prepared_params, $val);
                }
            }

            if($i < $where_count)
            {
                $sql .= ' '.$concat;
            }
        }

        $i = 0;
        $not_null_count = count($not_null);
        if($sql && ($not_null_count > 0))
        {
            $sql .= ' '.$concat;
        }
        foreach($not_null as $key => $val)
        {
            ++$i;
            $sql .= ' `'.$val.'` IS NOT NULL';

            if($i < $not_null_count)
            {
                $sql .= ' '.$concat;
            }
        }

        $in_sql = self::_getInSql($in, 'IN', $concat);
        if($sql && $in_sql)
        {
            $sql .= ' '.$concat;
        }
        $sql .= $in_sql;

        $not_in_sql = self::_getInSql($not_in, 'NOT IN', $concat);
        if($sql && $not_in_sql)
        {
            $sql .= ' '.$concat;
        }
        $sql .= $not_in_sql;

        return $sql;
    }

    private function _getInSql($in_arr, $in_type, $concat)
    {
        $i = 0;
        $in_count = count($in_arr);
        $sql = '';

        foreach($in_arr as $col => $row)
        {
            ++$i;
            $sql .= ' `'.$col.'` '.$in_type.' (';

            $val_count = count($row);
            $j = 0;
            foreach($row as $key => $val)
            {
                ++$j;
                $sql .= '%s';
                if($j < $val_count)
                {
                    $sql .= ', ';
                }
                array_push($this->prepared_params, $val);
            }
            $sql .= ')';

            if($i < $in_count)
            {
                $sql .= ' '.$concat;
            }
        }

        return $sql;
    }

    private function _whereSql()
    {
        $where_sql = self::_getWhereSql($this->where);
        $or_where_sql = self::_getWhereSql($this->or_where, 'or');
        $trash_sql = self::_trashSql();
        $sql = '';

        if($where_sql)
        {
            $sql = ' WHERE ';
            if($or_where_sql && $trash_sql)
            {
                $sql .= '( ('.$where_sql.') OR ('.$or_where_sql.') ) AND ('.$trash_sql.')';
            }
            else if($or_where_sql)
            {
                $sql .= '('.$where_sql.') OR ('.$or_where_sql.')';
            }
            else if($trash_sql)
            {
                $sql .= $where_sql.' AND '.$trash_sql;
            }
            else
            {
                $sql .= $where_sql;
            }

        }
        else if($or_where_sql)
        {
            $sql = ' WHERE ';
            if($trash_sql)
            {
                $sql .= '('.$or_where_sql.') AND '.$trash_sql;
            }
            else
            {
                $sql .= $or_where_sql;
            }
        }
        else if($trash_sql)
        {
            $sql = ' WHERE '.$trash_sql;
        }

        return $sql;
    }

    private function _trashSql()
    {
        $return = !$this->with_trashed ? '`deleted_at` IS NULL' : '';
        return $return;
    }

    function select($select='*')
    {
        $sql = 'SELECT';
        if(is_array($select))
        {
            $count = count($select);
            $i = 0;
            foreach($select as $key => $val)
            {
                ++$i;
                if($i == 1)
                {
                    $sql .= ' ';
                }
                $sql .= $val;

                if($i < $count)
                {
                    $sql .= ', ';
                }
            }
        }
        else if($select != '*')
        {
            $sql .= ' '.$select;
        }
        else
        {
            $sql .= ' *';
        }

        $this->select = $sql.' FROM `'.$this->table.'`';

        return $this;
    }

    private function _getPrepared($sql)
    {
        if(!empty($this->prepared_params))
        {
            $query = $this->mwpdb->prepare($sql, $this->prepared_params);
        }
        else
        {
            $query = $sql;
        }

        $result = $this->mwpdb->get_results($query);

        return $result;
    }

    function get()
    {
        $sql = $this->select;

        $where_sql = self::_whereSql();

        $sql .= $where_sql;

        if($this->orderby)
        {
            $sql .= ' ORDER BY `'.$this->orderby.'` '.$this->order;
        }

        $result = self::_getPrepared($sql);

        return $result;
    }

    function paginate($per_page='')
    {
        $sql = $this->select;

        $where_sql = self::_whereSql();

        $sql .= $where_sql;

        $count_sql = "SELECT COUNT(*) AS `total_rows` FROM `".$this->table."` ".$where_sql;

        $count_result = self::_getPrepared($count_sql);

        if($this->orderby)
        {
            $sql .= ' ORDER BY `'.$this->orderby.'` '.$this->order;
        }

        if(!$per_page)
        {
            $per_page = $this->per_page;
        }
        $sql .= ' LIMIT '.$per_page;

        $page = self::_getPageNum();
        if($page > 1)
        {
            $offset = ($page - 1) * $per_page;
            $sql .= ' OFFSET '.$offset;
        }

        $result = self::_getPrepared($sql);

        $data = [
                'total_rows' => $count_result[0]->total_rows,
                'cur_page' => $page,
                'per_page' => $per_page,
                'result' => $result,
            ];

        return $data;
    }
}
