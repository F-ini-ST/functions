<?php
    require_once './../functions.php';
    
    function show_dbs_list($res){
        echo "<pre>Databases (".mysql_num_rows($res)."):\n";
        while ($row = mysql_fetch_assoc($res)){
            echo "<a href='?db_chosen=".$row['Database']."'>".$row['Database']."</a>"."\n";
//            pretty_var_dump($row);
        }
    }
    
    function show_tables_list($res){
        echo "<pre>Tables (".mysql_num_rows($res)."):\n";
        while ($row = mysql_fetch_assoc($res)){
            echo "<a href='?table=".$row['Tables_in_'.$_SESSION['db_chosen']]."'>".$row['Tables_in_'.$_SESSION['db_chosen']]."</a>"."\n";
//            pretty_var_dump($row);
        }
/*        echo "<pre>Tables (".count($res)."):\n";
        foreach ($res as $res_row):
            $table_name = array_values($res_row['TABLE_NAMES'])[0];
            echo "<a href='?table=".$table_name."'>".$table_name."</a>"."\n";
        endforeach;//*/
    }
    
    function describe_table($res){
        while ($res_row = mysql_fetch_assoc($res)):
            echo "<tr>\n<td><b><i>{$res_row['Field']}</i></b></td><td>{$res_row['Type']}</td><td>{$res_row['Null']}</td>"
                . "<td>{$res_row['Key']}</td><td>{$res_row['Default']}</td><td>{$res_row['Extra']}</td>\n</tr>\n";
//            pretty_var_dump($res_row);
        endwhile;
        
//            foreach ($res as $res_row):
//                echo "<tr>\n<td><b><i>{$res_row['COLUMNS']['Field']}</i></b></td><td>{$res_row['COLUMNS']['Type']}</td><td>{$res_row['COLUMNS']['Null']}</td>"
//                    . "<td>{$res_row['COLUMNS']['Key']}</td><td>{$res_row['COLUMNS']['Default']}</td><td>{$res_row['COLUMNS']['Extra']}</td>\n</tr>\n";
//            endforeach;
    }
    
function show_table_row($row,$is_header=false){
    if (!is_array($row)) {
        return false;
    }
    $td = $is_header ? 'th' : 'td';
    echo "<tr><$td>".implode("</$td><$td>",$row)."</$td></tr>";
}
    
    function show_table($res){
        $row = mysql_fetch_assoc($res);
        if ($row){
            echo "<style>.highlight_hover_tr tr:hover { background-color: #E0CCFF; }</style>";
            echo "<table class='highlight_hover_tr' style='width:100%;' border=1>";
            show_table_row(array_keys($row),true);
            show_table_row($row);

            while ($row = mysql_fetch_assoc($res)){
                show_table_row($row);
    //            echo "<a href='?table=".$row['Tables_in_wordpress']."'>".$row['Tables_in_wordpress']."</a>"."\n";
    //            pretty_var_dump($row);
            }
            echo "</table>";
        }
/*        if (isset($res[0])):
            echo "<table class='mytable'>\n<tr>\n";
                foreach ($res[0][$_REQUEST['table']] as $col=>$v) echo "<th>$col</th>";
            echo "</tr>\n";
            foreach ($res as $res_row):
                echo "<tr>\n";
                foreach ($res_row[$_REQUEST['table']] as $val):
                    if (is_numeric($val) && ((int)$val)>1000000000) $val = "<span title='$val'>".date('Y-m-d H:i:s',(int)$val)."</span>";
                    echo "<td>$val</td>";
                endforeach;
                echo "</tr>\n";
            endforeach;
            echo "</table>\n";
        endif;//*/
    }

    function my_admin($QueringObj=null) {
        if ($QueringObj==null){
            return;
        }
        $rows_per_page = 100;
        if (isset($_GET['db_chosen'])) {
            $_SESSION['db_chosen'] = $_GET['db_chosen'];
        }
        if (isset($_SESSION['db_chosen'])){
            $QueringObj->selectDB($_SESSION['db_chosen']);
        }
         
// for yor-mon.com:
//        $this->loadModel("Server");
//        $QueringObj = $this->Server;
         
        
        echo "<style>.mytable td {border: 1px #333 solid; padding: 3px}</style>";
        echo "additional GET params: 'add_sql'<br />\n";
        echo "<form method='post'><input name='query' size=100 value='".(isset($_POST['query'])?$_POST['query']:'')."'><input type='submit' value='run query'></form>";
        $add_sql = !empty($_REQUEST['add_sql']) ? (" ".$_REQUEST['add_sql']) : '';
        
        // execute SQL
        if (!empty($_POST['query'])):
            $res = $QueringObj->query($_POST['query']);
            while ($row=  mysql_fetch_assoc($res)){
                pretty_var_dump($row);
            }
        // show all from TABLE
        elseif (!empty($_REQUEST['table']) && !empty($_REQUEST['action']) && $_REQUEST['action']=='view'):
            $rows_count = mysql_result($QueringObj->query("SELECT COUNT(*) as count FROM ".$_REQUEST['table']),0,'count');
            echo "View table <b>{$_REQUEST['table']}</b>, ";
            $pages_count = ceil($rows_count/$rows_per_page);
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) && is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            echo (($page-1)*$rows_per_page+1)." - ".($page==$pages_count? $rows_count : $page*$rows_per_page)." from $rows_count rows <br />\n";
            echo "pages: ";
            for ($i=1; $i<=$pages_count; $i++){
                $get_params = $_GET;
                $get_params['page'] = $i;
                $get_params = http_build_query($get_params);
                echo ($i != $page) ? "<a href='?$get_params'>$i</a> " : "$i ";
            }
            echo "<br />\n";
            $sql = "SELECT * FROM ".$_REQUEST['table'].$add_sql." LIMIT ".($rows_per_page*($page-1)).", $rows_per_page";
            echo "SQL query: <span style='color:blue;'>$sql</span><br />\n";
            $res = $QueringObj->query($sql);
            show_table($res);
        // describe TABLE
        elseif (!empty($_REQUEST['table'])):
            echo "<pre>Describe ".$_REQUEST['table'].":\n";
            echo "<table class='mytable'>\n"
                    . "<tr>\n<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>\n</tr>";
            $res = $QueringObj->query("DESCRIBE ".$_REQUEST['table']);
            describe_table($res);
            echo "</table>\n<a href='?table={$_REQUEST['table']}&action=view'>View</a>\n";
        // show tables list
        elseif (isset($_SESSION['db_chosen'])):
            $res = $QueringObj->query("SHOW FULL TABLES");
            show_tables_list($res);
        else:
            $res = $QueringObj->query("SHOW DATABASES");
            show_dbs_list($res);
        endif;
        die("\nend");
    }
?>