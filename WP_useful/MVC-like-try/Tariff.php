<?php

class Tariff {

    const COUNT_LIMIT = 20;

    protected static $table = 'tariff';

    public static function create($array) {
        if (!is_array($array)) {
            return false;
        }
        $fields = array_keys($array);
        $values = array_values($array);
        $sql = 'INSERT INTO `' . static::$table . '` (`' . implode('`,`', $fields) . '`) VALUES ("' . implode('","', $values) . '")';
//        echo "<h4>$sql</h4>";
        global $wpdb;
        $result = $wpdb->query($sql);
        return $result ? $wpdb->insert_id : false;
    }

    public static function getCount() {
        global $wpdb;
        $result = $wpdb->get_results('SELECT COUNT(*) AS count FROM `' . static::$table . '`', ARRAY_A);
        return isset($result[0]['count']) ? $result[0]['count'] : false;
    }

    public static function findById($id) {
        global $wpdb;
        $result = $wpdb->get_results('SELECT * FROM `' . static::$table . '` WHERE `id`=' . $id, ARRAY_A);
        return isset($result[0]) ? $result[0] : false;
    }
    
    public static function findByAttributes($attributes){
        $fields = array_keys($attributes);
        $values = array_values($attributes);
        $sql_parts = array();
        foreach ($attributes as $field => $value) {
            $sql_parts[] = "$field='$value'";
        }
        $sql = 'SELECT * FROM `' . static::$table . '` WHERE ' . implode(', ', $sql_parts);
        global $wpdb;
        return $wpdb->get_results($sql,ARRAY_A);
    }

    public static function findAll() {
        global $wpdb;
        return $wpdb->get_results('SELECT * FROM `' . static::$table . '`', ARRAY_A);
    }

    public static function update($tariff) {
        if (empty($tariff) || !is_array($tariff) || !isset($tariff['id']) || !is_numeric($tariff['id'])) {
            return false;
        }
        $id = $tariff['id'];
        unset($tariff['id']);
        $fields = array_keys($tariff);
        $values = array_values($tariff);
        $sql_parts = array(); //`' . implode('`,`', $fields) . '`) VALUES ("' . implode('","', $values) . '")';
        foreach ($tariff as $field => $value) {
            $sql_parts[] = "$field='$value'";
        }
        $sql = 'UPDATE `' . static::$table . '` SET ' . implode(', ', $sql_parts) . " WHERE id=$id";
//        var_dump($sql);
        global $wpdb;
        return $wpdb->query($sql);
    }

    public static function delete($id) {
        $sql = 'DELETE FROM `' . static::$table . '` WHERE id=' . $id;
        global $wpdb;
        return $wpdb->query($sql);
    }

}

class UserTariffAction extends Tariff {

    protected static $table = 'users_tariffs_actions';

}

class UserTariffStatus extends Tariff {

    protected static $table = 'users_tariffs_statuses';

}
