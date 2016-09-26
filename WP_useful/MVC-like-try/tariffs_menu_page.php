<?php
add_menu_page("tariffs", "Tariffs", 'edit_posts', "tariffs", 'tariffs_menu_page_view');

function tariffs_menu_page_view() {
    $actions = array();
    $get = $_GET;
    if (isset($get['id'])){
        unset($get['id']);
    }
    $actions['List'] = '?' . http_build_query(array_merge($get,array('action'=>'list')));
    $tariff_count=Tariff::getCount();
    if ($tariff_count < Tariff::COUNT_LIMIT) {
        $actions['Create'] = '?' . http_build_query(array_merge($get,array('action'=>'create')));
    } ?>
    <div class="wrap">
        <h2>Tariffs</h2>
        <?php
        foreach ($actions as $name=>$href) {
            echo "<h3><a href='$href'>$name</a></h3>";
        }
        if (isset($_SESSION['tariff_page_message'])){
            echo "<p>{$_SESSION['tariff_page_message']}</p>";
            unset($_SESSION['tariff_page_message']);
        }
        if (isset($_SESSION['tariff_page_content'])){
            echo $_SESSION['tariff_page_content'];
            unset($_SESSION['tariff_page_content']);
        }
        ?>
    </div>
    <?php
}

function tariff_actionCreate() {
    if (isset($_POST['tariff'])) {
        if ($new_tariff_id = Tariff::create($_POST['tariff'])) {
            $json_str = json_encode(array('tariff_data'=>$_POST['tariff']));
            $json_str = str_replace('\\','\\\\',$json_str);
            $json_str = str_replace(array('\'','"'),array('\\\'','\"'),$json_str);
            UserTariffAction::create(array(
                'user_id'=>0,
                'tariff_id'=>$new_tariff_id,
                'action'=>'tariff_create',
                'date'=>date('Y-m-d H:i:s'),
                'comment'=>$json_str,
            ));
            $_SESSION['tariff_page_message'] = "Tariff #$new_tariff_id created";
        } else {
            $_SESSION['tariff_page_message'] = "Some error occured, tariff wasn't created";
        }
        wp_redirect($_SERVER['SCRIPT_NAME'] ."?page=tariffs");
        exit;
    } else {
    ?>
    <form method="post">
        <p>New tariff data:</p>
        <p><label><input name="tariff[name]"> name</label></p>
        <p><label><input name="tariff[payment]"> payment</label></p>
        <p><label><input name="tariff[period]"> period</label></p>
        <p><input type="submit" value="Create"></p>
    </form>
    <?php
    }
}

function tariff_actionList(){
    $tariffs = Tariff::findAll();
    if (isset($tariffs[0]) && is_array($tariffs[0])) {
        echo "<style>.highlight_hover_tr tr:hover { background-color: #E0CCFF; }"
                . ".highlight_hover_tr tr>*:first-child { width: 10%;}"
                . "</style>";
        echo "<table class='highlight_hover_tr' style='width:100%;' border=1>";
        show_table_row(array_keys(array('Actions'=>'')+$tariffs[0]),array('cell_tag'=>'th'));
        foreach ($tariffs as $tariff) {
            $edit_link = '?' . http_build_query(array_merge($_GET,array('action'=>'edit', 'id'=>$tariff['id'])));
            $delete_link = '?' . http_build_query(array_merge($_GET,array('action'=>'delete', 'id'=>$tariff['id'])));
            show_table_row(array("<a href='$edit_link'>Edit</a> <a href='$delete_link'>Delete</a>")+$tariff);
        }
        echo "</table>";
    }
}

function tariff_actionEdit($id){
    $tariff = Tariff::findById($id);
    if (empty($tariff)) {
        $_SESSION['tariff_page_message'] = "Tariff #$id not found";
        wp_redirect($_SERVER['SCRIPT_NAME'] ."?page=tariffs");
        exit;
    }
    if (isset($_POST['tariff_action']) && $_POST['tariff_action']=='update' && isset($_POST['tariff']) && !empty($_POST['tariff'])){
        $prev_tariff_data = $tariff;
        if (Tariff::update($tariff=$_POST['tariff'])){
            $json_str = json_encode(array('prev_tariff_data'=>$prev_tariff_data, 'new_tariff_data'=>$tariff));
            $json_str = str_replace('\\','\\\\',$json_str);
            $json_str = str_replace(array('\'','"'),array('\\\'','\"'),$json_str);
            UserTariffAction::create(array(
                'user_id'=>0,
                'tariff_id'=>$id,
                'action'=>'tariff_update',
                'date'=>date('Y-m-d H:i:s'),
                'comment'=>$json_str,
            ));
            echo "<p>Tariff updated</p>";
        } else {
            echo "<p>Some error occured, tariff wasn't updated</p>";
        }
    }
    ?>
    <form method="post">
        <p>Tariff data:</p>
        <input type="hidden" name="tariff_action" value="update">
        <p><label><input readonly name="tariff[id]" value="<?= $tariff['id']; ?>"> ID</label></p>
        <p><label><input name="tariff[name]" value="<?= $tariff['name']; ?>"> name</label></p>
        <p><label><input name="tariff[payment]" value="<?= $tariff['payment']; ?>"> payment</label></p>
        <p><label><input name="tariff[period]" value="<?= $tariff['period']; ?>"> period</label></p>
        <p><input type="submit" value="Save"></p>
    </form>
    <?php
}

function tariff_actionDelete($id){
    if (!($tariff=Tariff::findById($id))){
        $_SESSION['tariff_page_message'] = "Tariff #$id not found";
        wp_redirect($_SERVER['SCRIPT_NAME'] ."?page=tariffs");
        exit;
    }
    if (Tariff::delete($id)){
        $json_str = json_encode(array('tariff_data'=>$tariff));
        $json_str = str_replace('\\','\\\\',$json_str);
        $json_str = str_replace(array('\'','"'),array('\\\'','\"'),$json_str);
        UserTariffAction::create(array(
                'user_id'=>0,
                'tariff_id'=>$id,
                'action'=>'tariff_delete',
                'date'=>date('Y-m-d H:i:s'),
                'comment'=>$json_str,
            ));
        $_SESSION['tariff_page_message'] = "Tariff #$id deleted";
    } else {
        $_SESSION['tariff_page_message'] = "Some error occured, tariff wasn't deleted";
    }
    wp_redirect($_SERVER['SCRIPT_NAME'] ."?page=tariffs");
    exit;
}

function show_table_row($row,$args=array()){
    if (!is_array($row)) {
        return false;
    }
    $defaults = array(
        'cell_tag'=>'td',
    );
    $args = array_merge($defaults,$args);
    
    $td = $args["cell_tag"];
    echo "<tr><$td>".implode("</$td><$td>",$row)."</$td></tr>";
}

add_action('admin_init', 'tariffs_menu_page_controller');

function tariffs_menu_page_controller(){
    global $pagenow;
    if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page']=='tariffs'){
        ob_start();
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'create':
                    if ($tariff_count >= Tariff::COUNT_LIMIT) {
                        tariff_actionList();
                    } else {
                        tariff_actionCreate();
                    }
                    break;
                case 'list':
                    tariff_actionList();
                    break;
                case 'edit':
                    if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])){
                        tariff_actionList();
                    } else {
                        tariff_actionEdit($_GET['id']);
                    }
                    break;
                case 'delete':
                    if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])){
                        tariff_actionList();
                    } else {
                        tariff_actionDelete($_GET['id']);
                    }
                    break;
                default:
                    tariff_actionList();
            }
        } else {
            tariff_actionList();
        }
        $_SESSION['tariff_page_content'] = ob_get_contents();
        ob_end_clean();
    }
}