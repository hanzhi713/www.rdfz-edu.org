<?php
declare(strict_types=1);

//require '../config/config.php';
require dirname(__DIR__) . '/config/db.php';
/**
 * create connection with MySQL database, using the mysqli driver
 * @var mysqli $mysqli
 * */
$mysqli = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['name'], $db_config['port']);
$mysqli->set_charset('utf8');

/**
 * get result of an executed sql
 * @param mysqli_stmt $sql
 * @return mixed
 * if this statement has a result, return an array
 * if this statement has no result, return true
 * */
function getResults(mysqli_stmt $sql)
{
    $meta = $sql->result_metadata();
    if ($meta) {
        $params = array();
        $row = array();
        while ($field = $meta->fetch_field())
            $params[] = &$row[$field->name];
        call_user_func_array(array($sql, 'bind_result'), $params);
        $results = array();
        $row_copy = array();
        while ($sql->fetch()) {
            foreach ($row as $key => $val)
                $row_copy[$key] = $val;
            $results[] = $row_copy;
        }
        return $results;
    } else
        return true;
}

/**
 * execute a prepared statement
 * @param string $sql
 * a sql statement
 * @param array $params
 * all parameters needed to bind to the statement
 * @return mixed
 * returns the result set array
 * */
function executeSQL(string $sql, array $params = array())
{
    global $mysqli;
    $sql = $mysqli->prepare($sql);
    $result = false;
    if ($sql) {
        $ref_params = array();
        foreach ($params as $k => $v)
            $ref_params[] = &$params[$k];
        call_user_func_array(array($sql, 'bind_param'), $ref_params);
        if ($sql->execute())
            $result = getResults($sql);
    }
    $sql->close();
    return $result;
}

/**
 * generate the salted password for a user, given its email and registration time
 * @param string $pass
 * the password of user after md5 encryption
 * @param string $email
 * the user's email address
 * @param int $regtime
 * the user's registration time
 * @return string
 * returns the salted password for this user
 * */
function saltpass(string $pass, string $email, int $regtime): string
{
    $timestr = strval($regtime * 713);
    $saltstr = md5($email . $pass);
    $salt = '';
    $len = strlen($timestr) - 1;
    for ($i = 1; $i < $len; $i++) {
        $v = intval($timestr[$i]) * intval($timestr[$i - 1]) + intval($timestr[$i + 1]);
        if ($v % 3 === 0)
            $salt .= $saltstr[intval($timestr[$i]) - 1];
        else
            $salt .= $saltstr[intval($timestr[$i]) * ($v % 3) + 5];
    }
    return sha1($pass . $salt);
}

/**
 * get the basic information of the user in an associated array
 * include id, headshot, email, nickname
 * @param mysqli_stmt $user_sql
 * @param int $uid
 * user's id
 * @return array
 * */
function getUserInfo(mysqli_stmt $user_sql, int $uid): array
{
    $user = array();
    $user_sql->bind_param('i', $uid);
    $user_sql->execute();
    $user_sql->bind_result($user['id'], $user['headshot'], $user['email'], $user['nickname']);
    $user_sql->fetch();
    $user_sql->free_result();
    return $user;
}

/**
 * display user's nickname, if it's set
 * otherwise return user's email address
 * @param array $user
 * @return string
 * */
function displayName(array $user): string
{
    if (empty($user['nickname']))
        return $user['email'];
    else
        return $user['nickname'];
}