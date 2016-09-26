<?php

add_filter('ajax_query_attachments_args','query_attachments_callback');
function query_attachments_callback($args) {
//    $new_args = array('post_type' => 'attachment',
//                  'orderby' => 'date',
//                  'order' => 'DESC',
//                  'posts_per_page' => 40,
//                  'paged' => $args['paged'],
//                  'post_status' => 'inherit,private',
//        );
//    $imgs = new WP_Query($new_args);
    $foldername = $_REQUEST['query']['media_category'] ? $_REQUEST['query']['media_category'] : '/';
    $page = $_REQUEST['query']['paged'] ? $_REQUEST['query']['paged'] : 1;
    //$lim = ((int)$page-1)*40;
    $lim = 0;
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."posts` WHERE `post_type`='attachment'"
            . " AND ((`post_status`='inherit' OR `post_status`='private'))"
            . " AND `guid` LIKE '%$foldername%'"
            . " LIMIT $lim, 80";
    $imgs = $wpdb->get_results($sql);
//    var_dump($imgs);
//    die();
    $posts_IDs = array();
    foreach($imgs as $img):
        $posts_IDs[] = $img->id;
    endforeach;
    
//    while ($imgs->have_posts()): $imgs->the_post();
//        if ($filename_start_pos=strpos(get_post()->guid,$foldername)):  // attachment inside folder
//            // and not in subfolder :
//            if (!strpos(substr(get_post()->guid,$filename_start_pos+strlen($foldername)+1),'/') || $foldername=='/'):
//                $args['post__in'][] = get_the_ID();
//            endif;
//        endif;
//    endwhile;
//    unset($imgs);
    if (empty($posts_IDs)) $posts_IDs = 'null';
    $args['post__in'] = $posts_IDs;
    return $args;
}

add_action( 'admin_enqueue_scripts', 'add_folder_select_dropdown');
function add_folder_select_dropdown() {
        global $pagenow;
	// Media editor
        
	if ( wp_script_is( 'media-editor' ) && ( ( 'upload.php' == $pagenow ) || ( 'post.php' == $pagenow ) || ( 'post-new.php' == $pagenow ) ) || ( 'edit-tags.php' == $pagenow ) ) {

            global $wpdb;
            $res = $wpdb->get_results(
                    "SELECT  ".
                    "DISTINCT LEFT(meta_value, CHAR_LENGTH(meta_value)-CHAR_LENGTH(SUBSTRING_INDEX(meta_value, '/', -1))) AS subdir ".
                    "FROM $wpdb->postmeta ".
                    "WHERE meta_key = '_wp_attached_file' ".
                    "AND meta_value LIKE '%/%' ".
                    "AND meta_value <> '.' AND meta_value <> '..' ".
                    "ORDER BY subdir ");

            $folders = '';
            foreach ($res as $subf) {
                $folders .= ',{"term_id":"'.$subf->subdir.'","term_name":"'.$subf->subdir.'"}';
            }
                
		echo '<script type="text/javascript">'."\n";
		echo '/* <![CDATA[ */'."\n";
		echo 'var wp_media_categories_taxonomies = {"' . 'media_category' . '":'."\n";
		echo     '{"list_title":"All folders",'."\n";
		echo       '"term_list":[' . $folders . ']}};'."\n";
		echo '/* ]]> */';
		echo '</script>';
                
		// Script
		wp_enqueue_script( 'realalanya_media-folders-views', get_template_directory_uri() . '/js/folders/media-views.js',array( 'media-views' ),'201510280001',true);
            // Styling
            wp_enqueue_style( 'realalanya_media-folders-styling', get_template_directory_uri() . '/css/folders/admin.css');
        }
}

?>