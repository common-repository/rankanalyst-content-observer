<?php
/**
 * Plugin Name: RankAnalyst Content Observer
 * Plugin URI:  https://rankanalyst.com/
 * Description: Content Observer Plugin for RankAnalyst Lab
 * Version: 1.1
 * Author: Steven Broschart
 * Author URI: https://rankanalyst.de/
 * License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}





/* ---------------------------
Interface
---------------------------- */

add_action('admin_head','rankanalyst_css');
function rankanalyst_css(){
  echo '<style>
    .ratable {border-collapse:collapse}
    .ratable tr td { padding: 10px; font-size: 20px; border-bottom: 1px solid #ccc; }
    .ratable th {border-bottom: 1px solid #888;}
    .preset:hover{cursor: pointer}
  </style>';
}



add_action( 'admin_init', 'rankanalyst_settings_init' );

function rankanalyst_settings_init() {

       register_setting( 'rankanalyst', 'rankanalyst_options' );

       add_settings_section(
       'rankanalyst_section_developers',
       '',
       'rankanalyst_section_developers_cb',
       'rankanalyst'
       );


       add_settings_field(
       'rankanalyst_field_apikey',
       __( 'API Key', 'rankanalyst' ),
       'rankanalyst_field_apikey_cb',
       'rankanalyst',
       'rankanalyst_section_developers',
       [
       'label_for' => 'rankanalyst_field_apikey',
       'class' => 'rankanalyst_row',
       'rankanalyst_custom_data' => 'custom',
       ]
       );
  
  
       add_settings_field(
       'rankanalyst_field_email',
       __( 'E-Mail', 'rankanalyst' ),
       'rankanalyst_field_email_cb',
       'rankanalyst',
       'rankanalyst_section_developers',
       [
       'label_for' => 'rankanalyst_field_email',
       'class' => 'rankanalyst_row',
       'rankanalyst_custom_data' => 'custom',
       ]
       );
}
 


function rankanalyst_section_developers_cb(){
 return;
}








function rankanalyst_field_apikey_cb( $args ) {
 
 $options = get_option( 'rankanalyst_options' );

 ?>
 <input size="80" value="<?php echo esc_attr( $options[$args['label_for']] ); ?>" name="rankanalyst_options[<?php echo esc_attr( $args['label_for'] ); ?>]" id="<?php echo esc_attr( $args['label_for'] ); ?>" type='' />
 <p class="description"><?php esc_html_e( 'If set, REST API is active and "RankAnalyst Lab" is able to retrieve modification data.', 'rankanalyst' ); ?></p>
 <?php
}


function rankanalyst_field_email_cb( $args ) {

 $options = get_option( 'rankanalyst_options' );

 ?>
 <input size="80" value="<?php echo esc_attr( $options[$args['label_for']] ); ?>" name="rankanalyst_options[<?php echo esc_attr( $args['label_for'] ); ?>]" id="<?php echo esc_attr( $args['label_for'] ); ?>" type='' />
 <p class="description"><?php esc_html_e( 'As soon as a user modifies the content, we will inform you via this email address. You can also specify multiple, comma-separated addresses. Leave it blank to deactivate fuctionality.', 'rankanalyst' ); ?></p>
 <?php
}


/* ---------------------------
Page-Configuration
---------------------------- */ 
function rankanalyst_options_page() {

 add_menu_page(
 'RankAnalyst Content Observer',
 'RankAnalyst Content Observer',
 'manage_options',
 'rankanalyst_co',
 'rankanalyst_options_page_html'
 );
 
add_submenu_page( 'rankanalyst_co', 'RankAnalyst Content Observer', 'Recent Modifications','manage_options', 'rankanalyst_co','rankanalyst_options_page_html');
add_submenu_page( 'rankanalyst_co', 'Configuration', 'Configuration','manage_options', 'rankanalyst_co_conf','rankanalyst_co_conf');  
 
}
 

add_action( 'admin_menu', 'rankanalyst_options_page' );



/* ---------------------------
Overview Page
---------------------------- */ 
function rankanalyst_options_page_html() {

 if ( ! current_user_can( 'manage_options' ) ) {
  return;
 }
 

if(isset($_GET['query'])){$query = sanitize_text_field( wp_unslash( $_GET['query'] ) );} else {$query = "";} ?>
<h2>Rankanalyst Content Observer</h2><hr /><h1>Recent Modifications</h1>
<input placeholder="search for specific modifications" style='margin-right: 10px' id='modsearch' size="40" value="<?php echo $query ?>" /> <span class='preset'>CLEAR</span>

<script>


$('#modsearch').on('keydown', function(e) {
    if (e.which == 13) {
        e.preventDefault();
      var url = window.location+"&query="+$('#modsearch').val();
      window.open(url,'_self');
      //console.log(current_url);
    }
});
  
$('.preset').click(function(e){
  var nquery = $(this).text();
  if(nquery == "CLEAR"){nquery="";}
  //$('#modsearch').val(nquery);
  var url = window.location+"&query="+nquery;
  window.open(url,'_self');
})
  
  
  

  
  
$('#modsearch').focus();
 
</script>



<?php
  
 global $wpdb;
 $table_name = $wpdb->prefix . 'rankanalyst_changes'; 
  
 //$query = sanitize_text_field( wp_unslash( $_GET['query'] ) );
 if($query!==""){
   $results = $wpdb->get_results( "SELECT time,author,linkid,textmutation,textextension,headlinemodification,postid, permalink,notice FROM ".$table_name." WHERE info LIKE '%<ins>%".$query."%</ins>%' OR info LIKE '%<del>%".$query."%</del>%' ORDER BY time DESC LIMIT 100000" ); 
 } else {
   $results = $wpdb->get_results( "SELECT time,author,linkid,textmutation,textextension,headlinemodification,postid, permalink,notice FROM ".$table_name." ORDER BY time DESC" );   
 }

  
 echo "<hr /><table class='ratable'><tr><th>Source</th><th>Time</th><th>Mutation</th><th>Extension</th><th>Author</th><th>Notice</th><th></th></tr>"; 
  
 foreach($results as $row){
   if($row->headlinemodification!==""){$titlechange="<br /><small><em>Renamed from: ".$row->headlinemodification."</em></small>";} else {$titlechange="";}
   if($row->textextension>0){$textext="+".$row->textextension;} else {$textext=$row->textextension;}
   echo "<tr><td><strong><a href='".esc_url($row->permalink)."' target='_blank'>".get_the_title($row->postid)."</a></strong>".$titlechange."</td><td>".rankanalyst_timeAgo($row->time)."</td><td>".$row->textmutation."%</td><td>".$textext."%</td><td>".get_the_author_meta('display_name',$row->author)."</td><td>".$row->notice."</td><td><a class='button button-primary' target='_blank' href='".get_site_url()."/wp-admin/revision.php?revision=".$row->linkid."&gutenberg=true'>Examine</a></td></tr>";
 } 
  echo "</table>";
}







/* ---------------------------
Configuration Page
---------------------------- */ 

function rankanalyst_co_conf() {

 if ( ! current_user_can( 'manage_options' ) ) {
  return;
 }
 

 if ( isset( $_GET['settings-updated'] ) ) {
  add_settings_error( 'rankanalyst_messages', 'rankanalyst_message', __( 'Settings Saved', 'rankanalyst' ), 'updated' );
 }
 
 settings_errors( 'rankanalyst_messages' );
 ?>
 <h2>Rankanalyst Content Observer</h2><hr />
 <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 <form action="options.php" method="post">
 <?php
      echo '<em>RankAnalyst Content Observer</em> is a Performance Quality Management Plugin. It keeps you up to date about recent content changes.<br />It is also able to share these data with our Website Optimization Analytics System <em><a href="https://rankanalyst.com/lab" target="_blank">RankAnalyst Lab</a></em>. For further information please visit our <a href="https://rankanalyst.de/content-observer-wordpress-plugin/" target="_blank">plugin website</a>.';
 
 settings_fields( 'rankanalyst' );
 do_settings_sections( 'rankanalyst' );?><button class='button-secondary' id='recreate'>Recreate historical entries from DB</button><?php
 submit_button( 'Save Settings' );
 ?>
 </form>



 
<script>    
/* ---------------------------
Recreate from Database (JS)
---------------------------- */  

$('#recreate').click(function(e){
  if(confirm("Are you sure? This action may take some time ...")){
      var endpoint = "<?php echo get_rest_url(null, 'racontentobserver/v1/recreate'); ?>";
     
      var _nonce = "<?php echo wp_create_nonce( 'wp_rest' ); ?>";
      $.ajax({
          type: 'POST',
          url: endpoint,
          dataType: 'json',
          data: {
              // Your other data here
              "_nonce": _nonce
          },
          beforeSend: function ( xhr ) {
              xhr.setRequestHeader( 'X-WP-Nonce', _nonce );
          },
        success: function(e){
          alert('Finished!');
        }
      });
       
     }
})
</script>

<?php
  
}





/* ---------------------------
DB Init
---------------------------- */

register_activation_hook( __FILE__, 'rankanalyst_create_db' );

function rankanalyst_create_db() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'rankanalyst_changes';

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT CURRENT_TIMESTAMP,
		author mediumint(9) NOT NULL,
		linkid int,
    textmutation smallint(3),
    textextension smallint(3),
    headlinemodification text,
    postid int,
    permalink varchar(500),
    notice text,
    info text NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}





/* ---------------------------
Save Post Hook
---------------------------- */


add_action('save_post','rankanalyst_contentchange');

function rankanalyst_contentchange(){
  
 $id = sanitize_text_field( wp_unslash( $_GET['post'] ) );

  if($id && is_numeric($id)){
        $rev = wp_get_post_revisions($id);

         $attc = current($rev); 
         $pt2 = $attc->post_title; 
         $pc2 = $attc->post_content; 
         $pa2 = $attc->post_author;
         $guid = $attc->ID;

         $atto = next($rev);
         $pt1 = $atto->post_title; 
         $pc1 = $atto->post_content; 
         $pa1 = $atto->post_author; 
           
     $diff = wp_text_diff( $pc1, $pc2 );
   
     $delpattern = "/<del>(.*?)<\/del>/m";
     $inspattern = "/<ins>(.*?)<\/ins>/m";
     $lost = 0;
     $in = 0;
   
     $affected = "";
     $notice = array();
    
     $total1 = strlen($pc1);
     $total2 = strlen($pc2);
    
     preg_match_all($delpattern,$diff,$out1);

    $inner1 = current($out1);
    foreach($inner1 as $k){$lost+=(strlen(utf8_decode($k))-11); $affected.=$k;}
    
    preg_match_all($inspattern,$diff,$out2);
    $inner2 = current($out2);
    foreach($inner2 as $p){$in+=(strlen(utf8_decode($p))-11); $affected.=$p;}    
    
    $m1 = abs($in-$lost)+abs($total2-$total1);
    $mutation = round(($m1/($total1-$m1+$in))*100);
    
    $modification = abs($in-$lost);
    $mutation = round(($modification/($total1-$modification))*100);
    $extension = $total2-$total1;
    
    $permalink = esc_url(get_permalink($id));
    
    if(preg_match("<h1|h1>",$affected)){array_push($notice,"&lt;h1&gt; affected");}
    if(preg_match("<h2|h2>",$affected)){array_push($notice,"&lt;h2&gt; affected");}
    if(preg_match("<h3|h3>",$affected)){array_push($notice,"&lt;h3&gt; affected");}
    if(preg_match("<em|em>",$affected)){array_push($notice,"&lt;em&gt; affected");}
    if(preg_match("<strong|strong>",$affected)){array_push($notice,"&lt;strong&gt; affected");}
    
     $diffo = "Hello,<br /><br />you are observing content changes on ".get_site_url().".<br /><br />Concerning: <a href='".$permalink."'>".$permalink."</a><br /><br />";
     if(count($notice)>0){$diffo.="Notice: ".implode(" | ",$notice)."<br /><br />";}
     if($mutation>0){$diffo.= "We recognized content modifications of existing content by <strong>".$mutation."%</strong>.";}
     if($extension>0){$diffo.= "<br />Content has been <strong>extended</strong> by about <strong>".round(100*$extension/$total1)."%</strong>.";}
     if($extension<0){$diffo.= "<br />Content has been <strong>shortened</strong> by about <strong>".abs(round(100*$extension/$total1))."%</strong>.";}
  
    $tc = "<br /><strong>Attention: Headline was changed from '".esc_html($pt1)."' to '".esc_html($pt2)."'</strong>";

     if($pt1!==$pt2){$diffo.= $tc;}
    
     if($mutation>0){$diffo.= "<br /><br />The Following modifications were made:<hr />".$diff;}
    
    
        global $wpdb;
        if($pt1!==$pt2){$formerly=$pt1;} else {$formerly="";}
        $table_name = $wpdb->prefix . 'rankanalyst_changes';
        $wpdb->insert($table_name , array('notice' => implode(" | ",$notice), 'permalink' => $permalink,'postid' => $id, 'info' => $diff, 'linkid' => $guid, 'author' => $pa2, 'textmutation' => abs($mutation), 'textextension' => round(100*$extension/$total1), 'headlinemodification' => $formerly));
    
     $options = get_option( 'rankanalyst_options' );


    if($options['rankanalyst_field_email']!=="" && ($pc1!==$pc2 || $pt1!==$pt2)){
          $addressfield = preg_replace('/ /',"",$options['rankanalyst_field_email']);
          $addresses = explode(",",$addressfield);
          $receiver = [];
          foreach($addresses as $addr){
            if(is_email($addr)){array_push($receiver,$addr);}
          }

        if(count($receiver)>0){wp_mail( $receiver, 'Content Modification on '.get_site_url(), $diffo, array('Content-Type: text/html; charset=UTF-8') );}
        }    
  }


}


/* ---------------------------
Time-Operations
---------------------------- */

function rankanalyst_timeAgo($time_ago)
{
    $time_ago = strtotime($time_ago);
    $cur_time   = time();
    $time_elapsed   = $cur_time - $time_ago;
    $seconds    = $time_elapsed ;
    $minutes    = round($time_elapsed / 60 );
    $hours      = round($time_elapsed / 3600);
    $days       = round($time_elapsed / 86400 );
    $weeks      = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640 );
    $years      = round($time_elapsed / 31207680 );
    // Seconds
    if($seconds <= 60){
        return "just now";
    }
    //Minutes
    else if($minutes <=60){
        if($minutes==1){return "one minute ago";}
        else{return "$minutes minutes ago";}
    }
    //Hours
    else if($hours <=24){
        if($hours==1){return "an hour ago";}
        else{return "$hours hrs ago";}
    }
    //Days
    else if($days <= 7){
      if($days==1){return "yesterday";}
      else{return "$days days ago";}
    }
    //Weeks
    else if($weeks <= 4.3){
        if($weeks==1){return "a week ago";}
      else{return "$weeks weeks ago";}
    }
    //Months
    else if($months <=12){
        if($months==1){
            return "a month ago";
        }else{
            return "$months months ago";
        }
    }
    //Years
    else{
        if($years==1){
            return "one year ago";}else{return "$years years ago";
        }
    }
}







/* ---------------------------
REST API for RankAnalyst Lab
---------------------------- */

add_action('rest_api_init','rankanalyst_rest_route');
function rankanalyst_rest_route(){
  
  register_rest_route(
    'racontentobserver/v1',
    'overview/(?P<from>.*?)/(?P<to>.*?)/(?P<apikey>.*?)',
    array(
      array('methods' => 'GET',
           'callback' => 'rankanalyst_co_overview_callback'
           )
    )
  );
  
  register_rest_route(
    'racontentobserver/v1',
    'page/(?P<from>.*?)/(?P<to>.*?)/(?P<apikey>.*?)/(?P<postid>.*?)',
    array(
      array('methods' => 'GET',
           'callback' => 'rankanalyst_co_page_callback'
           )
    )
  );
  
  
  
    register_rest_route(
    'racontentobserver/v1',
    'item/(?P<apikey>.*?)/(?P<itemid>.*?)',
    array(
      array('methods' => 'GET',
           'callback' => 'rankanalyst_co_item_callback'
           )
    )
  );
  
  
    
      register_rest_route(
          'racontentobserver/v1',
          'search/(?P<from>.*?)/(?P<to>.*?)/(?P<apikey>.*?)/(?P<query>.*?)',
          array(
            array('methods' => 'GET',
                 'callback' => 'rankanalyst_co_search_callback'
                 )
          )
  );
  
  
  

        register_rest_route(
          'racontentobserver/v1',
          'recreate',

          array(
            array('methods' => 'POST',
                 'callback' => 'rankanalyst_co_recreate_callback',
                  'args' => [

                  '_nonce' => [
                      'validate_callback' => function($_nonce) {
                          return wp_verify_nonce($_nonce, 'wp_rest');
                      }
                  ]
              ]
                 )
          )
  );
  
}



function rankanalyst_co_overview_callback($data){

     $from = sanitize_text_field( wp_unslash( $data['from'] ) );
     $to = sanitize_text_field( wp_unslash( $data['to'] ) );
     $apikey = sanitize_text_field( wp_unslash( $data['apikey'] ) );
  
     $options = get_option( 'rankanalyst_options' );
     if($options['rankanalyst_field_apikey']!==""){
       if($options['rankanalyst_field_apikey']==$apikey){
             global $wpdb;
             $table_name = $wpdb->prefix . 'rankanalyst_changes'; 
             $results = $wpdb->get_results( "SELECT permalink, postid, COUNT(*)AS cnt FROM ".$table_name." WHERE time >= '".$from."' AND time <= '".$to."' GROUP BY permalink ORDER BY time DESC LIMIT 100000" ); 
             return rest_ensure_response($results);
       }
     } else {
       return;
     }
  
}





function rankanalyst_co_page_callback($data){
     $from = sanitize_text_field( wp_unslash( $data['from'] ) );
     $to = sanitize_text_field( wp_unslash( $data['to'] ) );
     $postid = sanitize_text_field( wp_unslash( $data['postid'] ) );
     $apikey = sanitize_text_field( wp_unslash( $data['apikey'] ) );
  
     $options = get_option( 'rankanalyst_options' );
     if($options['rankanalyst_field_apikey']!==""){
       if($options['rankanalyst_field_apikey']==$apikey){
             global $wpdb;
             $table_name = $wpdb->prefix . 'rankanalyst_changes'; 
             $results = $wpdb->get_results( "SELECT time,author,linkid,textmutation,textextension,headlinemodification,postid, permalink,notice FROM ".$table_name." WHERE time >= '".$from."' AND time <= '".$to."' AND postid = '".$postid."' ORDER BY time DESC LIMIT 100000" ); 
             return rest_ensure_response($results);
       }
     } else {
       return;
     }
  
}




function rankanalyst_co_item_callback($data){
     $itemid = sanitize_text_field( wp_unslash( $data['itemid'] ) );
     $apikey = sanitize_text_field( wp_unslash( $data['apikey'] ) );
  
     $options = get_option( 'rankanalyst_options' );
     if($options['rankanalyst_field_apikey']!==""){
       if($options['rankanalyst_field_apikey']==$apikey){
             global $wpdb;
             $table_name = $wpdb->prefix . 'rankanalyst_changes'; 
             $results = $wpdb->get_results( "SELECT info FROM ".$table_name." WHERE linkid = '".$itemid."' ORDER BY time DESC LIMIT 100000" ); 
             return rest_ensure_response($results);
       }
     } else {
       return;
     }
  
}





function rankanalyst_co_search_callback($data){
     $from = sanitize_text_field( wp_unslash( $data['from'] ) );
     $to = sanitize_text_field( wp_unslash( $data['to'] ) );
     $query = sanitize_text_field( wp_unslash( $data['query'] ) );
     $apikey = sanitize_text_field( wp_unslash( $data['apikey'] ) );
  
     $options = get_option( 'rankanalyst_options' );
     if($options['rankanalyst_field_apikey']!==""){
       if($options['rankanalyst_field_apikey']==$apikey){
             global $wpdb;
             $table_name = $wpdb->prefix . 'rankanalyst_changes'; 
             $results = $wpdb->get_results( "SELECT time,author,linkid,textmutation,textextension,headlinemodification,postid, permalink,notice FROM ".$table_name." WHERE time >= '".$from."' AND time <= '".$to."' AND (info LIKE '%<ins>%".$query."%</ins>%' OR info LIKE '%<del>%".$query."%</del>%') ORDER BY time DESC LIMIT 100000" ); 
             return rest_ensure_response($results);
       }
     } else {
       return;
     }
  
}






function rankanalyst_co_recreate_callback($data){


      
             global $wpdb;
             $table_from = $wpdb->prefix . 'posts';
             $table_to = $wpdb->prefix . 'rankanalyst_changes'; 
         
             $wpdb->query( "DELETE FROM ".$table_to );
  
  
         $results = $wpdb->get_results( "SELECT ID FROM ".$table_from );
         foreach($results as $r){
           $postid = $r->ID;
           
         

    
        $rev = wp_get_post_revisions($postid);

         if($rev){
            $amount = count($rev);
            $counter=0;
         do { 
                 $attc = current($rev);
                 if(isset($attc->post_title)){  

                 $pt2 = $attc->post_title; 
                 $pc2 = $attc->post_content; 
                 $pa2 = $attc->post_author;
                 $ptm2 = $attc->post_date;
                 $guid = $attc->ID;


                 $atto = next($rev);
                 if(isset($atto->post_title)){ 
                 $pt1 = $atto->post_title; 
                 $pc1 = $atto->post_content; 
                 $pa1 = $atto->post_author; 

                 $diff = wp_text_diff( $pc1, $pc2 );

                 $delpattern = "/<del>(.*?)<\/del>/m";
                 $inspattern = "/<ins>(.*?)<\/ins>/m";
                 $lost = 0;
                 $in = 0;

                 $affected = "";
                 $notice = array();

                 $total1 = strlen($pc1);
                 $total2 = strlen($pc2);

                 preg_match_all($delpattern,$diff,$out1);

                $inner1 = current($out1);
                foreach($inner1 as $k){$lost+=(strlen(utf8_decode($k))-11); $affected.=$k;}

                preg_match_all($inspattern,$diff,$out2);
                $inner2 = current($out2);
                foreach($inner2 as $p){$in+=(strlen(utf8_decode($p))-11); $affected.=$p;}    

                $m1 = abs($in-$lost)+abs($total2-$total1);
                if($total1-$m1+$in==0){$m1+=.1;}
                $mutation = round(($m1/($total1-$m1+$in))*100);

                $modification = abs($in-$lost);
                if($total1-$modification==0){$modification+=.1;}
                $mutation = round(($modification/($total1-$modification))*100);
                $extension = $total2-$total1;

                $permalink = esc_url(get_permalink($postid));

                if(preg_match("<h1|h1>",$affected)){array_push($notice,"&lt;h1&gt; affected");}
                if(preg_match("<h2|h2>",$affected)){array_push($notice,"&lt;h2&gt; affected");}
                if(preg_match("<h3|h3>",$affected)){array_push($notice,"&lt;h3&gt; affected");}
                if(preg_match("<em|em>",$affected)){array_push($notice,"&lt;em&gt; affected");}
                if(preg_match("<strong|strong>",$affected)){array_push($notice,"&lt;strong&gt; affected");}



                if($pt1!==$pt2){$formerly=$pt1;} else {$formerly="";}
                $table_name = $wpdb->prefix . 'rankanalyst_changes';
                
                if($total1==0){$total1=.1;}
                $textext=round(100*$extension/$total1);
                $wpdb->insert($table_name , array('notice' => implode(" | ",$notice), 'time' => $ptm2,'permalink' => $permalink,'postid' => $postid, 'info' => $diff, 'linkid' => $guid, 'author' => $pa2, 'textmutation' => abs($mutation), 'textextension' => $textext, 'headlinemodification' => $formerly));
                 }

                 }
         $counter++;  
         }  while($counter<$amount);
    
     }


 
  


           
           
           
           
           
           
         
           
           
           
         }
         

         
         
         
             return rest_ensure_response([]);
       

  
}
?>