<?php
/*
	Plugin Name: Address Bar Ads
	Plugin URI: http://www.addressbarads.com/
	Description: Integrate messages in your website's Address (URL) Bar and turn it into an Advertisement Box. You choose your text, pages/posts, and destination URL. All rights reserved Shamir Ozery.
	Version:1.0.0
	Author: Shamir Ozery
	Author URI: http://www.sivenso.com
	License: GPL2
*/
 /*  Copyright YEAR  Shamir  (email : shamir@sivenso.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//Table use for the save the content  of address bar 



class cbnetmpdl{
    function cbnetmpdl(){
        if(is_admin()){
            add_filter('plugin_row_meta',array(&$this,'cbnet_display_donate_link'),99,2);
        }
    }
	
	function cbnet_display_donate_link($links,$plugin_file){
		$donate_link_already_exists = 'false';
		$existing_links = $links;
		foreach ($existing_links as $link){
			if( $donate_link_already_exists == 'false' ) {
				$other_donate_link = ( strpos(strtolower(trim($link)),'donate') !== false ? 'true' : 'false' );
				if( $other_donate_link == 'true' ){
					$donate_link_already_exists = 'true';
				}
			}
		}
		if( $donate_link_already_exists == 'false' ) {
			$donate_uri = false;
			$readmeFile = WP_PLUGIN_DIR.'/'.dirname($plugin_file).'/readme.txt';
			if(file_exists($readmeFile)){
				$readme = file($readmeFile,FILE_SKIP_EMPTY_LINES);
				foreach ($readme as $line){ 
					$donate_link_exists = ( stripos($line , 'donate' ) !== false ? 'true' : 'false' );
					if( $donate_link_exists == 'true' ){ 
						$donate_uri=trim(substr($line,strpos($line,':')+1));
						$donate_link_text = 'Donate';
						break;
					} 
				}
			} 
			if ( $donate_uri !== false) {
			$donate_link = '<a href="' . $donate_uri  . '" target="_blank">' . $donate_link_text . '</a>';
			$links[] = $donate_link;
			}
		}
		return $links;
    }

}
new cbnetmpdl();
load_plugin_textdomain('your-unique-name', false, basename( dirname( __FILE__ ) ) . '/languages' );

define(WP_ADDRESS_BAR_ADS_TABLE,$wpdb->prefix."addressbarads_list");
define(WP_POSTS,$wpdb->prefix."posts");


$plugin_dir = PluginUrl();
define(WP_ADDRESS_BAR_ADS_PLUGIN_URL, $plugin_dir);



// function for the getting the current plugin folder path folder path
function PluginUrl() 
	{
	    //Try to use WP API if possible, introduced in WP 2.6
        if (function_exists('plugins_url')) return trailingslashit(plugins_url(basename(dirname(__FILE__))));
        //Try to find manually... can't work if wp-content was renamed or is redirected
        $path = dirname(__FILE__);
        $path = str_replace("\\","/",$path);
        $path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
        return $path;
    }

//Sript use for the add the header javascript and headder code 
add_action('wp_head', 'addressBarAds_script');
function addressBarAds_script() 
{		
		global $wpdb;	
		global $post;
		$c='';
		$thePostID = $post->ID;
		if($thePostID != '')
		{
			$c= $wpdb->get_row( "SELECT * FROM  ".WP_ADDRESS_BAR_ADS_TABLE." where uba_page_ids like '%".$thePostID."%' limit 1" );
		}
 		// cheching for the ads showing only for the given post and page ids//		
		if($c != '')
		 {
 	
 	
		?>
		<script type="text/javascript" src="<?php echo WP_ADDRESS_BAR_ADS_PLUGIN_URL ?>jquery1.6.min.js"></script>
        <script type="text/javascript" src="<?php echo WP_ADDRESS_BAR_ADS_PLUGIN_URL ?>jquery.dotimeout.min.js"></script>
        <script type="text/javascript" src="<?php echo WP_ADDRESS_BAR_ADS_PLUGIN_URL ?>urlhash.js"></script>
        <script type="text/javascript">		
		
			var press_hot_key = 'Enter';
			var ad_postion = '<?php echo $c->uba_key_pos; ?>';
			var new_win_open='<?php echo $c->uba_new_win; ?>';
			var hashes_str = new Array();
			hashes_str[0]= '<?php  echo stripslashes(urldecode($c->uba_plug_text)); ?>'; 
			
			initURLHash(hashes_str, '<?php  echo $c->uba_plug_link; ?>');
    	</script>
<?php
	 }
}


/**
 * First the enqueue function
 */
function itg_admin_css_all_page() {
    /**
     * Register the style handle
     */
    wp_register_style($handle = 'itg-admin-css-all', $src = plugins_url('addressbarads.css', __FILE__), $deps = array(), $ver = '1.0.0', $media = 'all');
 
    /**
     * Now enqueue it
     */
    wp_enqueue_style('itg-admin-css-all');
}
add_action('admin_print_styles', 'itg_admin_css_all_page'); 
/**
 * Finally hook the itg_admin_css_all_page to admin_print_styles
 * As it is done during the init of the admin page, so the enqueue gets executed.
 * This can also be attached to the admin_init, or admin_menu hook
 */
 
  
//Installation Event calling for the create the DB Related to the. 
if (isset($_GET['activate']) && $_GET['activate'] == 'true')
{
	add_action ('init', 'addressBarAdsPlugin_install');
}

//function exicuting while plugin installation.
function addressBarAdsPlugin_install()
{
	global $wpdb;   
	if($wpdb->get_var("show tables like '". WP_ADDRESS_BAR_ADS_TABLE . "'")!= WP_ADDRESS_BAR_ADS_TABLE) 
		{
				$sSql = "CREATE TABLE IF NOT EXISTS `". WP_ADDRESS_BAR_ADS_TABLE . "` (";
				$sSql = $sSql . "`uba_plug_id` INT NOT NULL AUTO_INCREMENT ,";
				$sSql = $sSql . "`uba_page_ids` text ,";
				$sSql = $sSql . "`uba_plug_link` VARCHAR( 150 ),";
				$sSql = $sSql . "`uba_plug_text` TEXT ,";
				$sSql = $sSql . "`uba_plug_status` VARCHAR( 10 ) NOT NULL ,";
				$sSql = $sSql . "`uba_key_pos` INT (2) NOT NULL  default 0,";
				$sSql = $sSql . "`uba_new_win` INT (2) NOT NULL  default 0,";
				$sSql = $sSql . "PRIMARY KEY ( `uba_plug_id` )";
			 	$sSql = $sSql . ")";
 				$wpdb->query($sSql);
				unset($_REQUEST);
		}
		
}

//funtion for the manage the plugin content
function addressbarads_admin_option()
{
	
	//Checking fot the validation for the blank field.
		if(isset($_REQUEST['sbt_ads']))
				{
	
					if($_REQUEST['remove_page']=="")
							$message_e="Please Select page/post.";	
					
					if($_REQUEST['add_banner_code']=="")
							$message_e="Please Enter Urls.";
						
					if(trim($_REQUEST['add_text'])=="")
						$message_e="Please enter Urls text.";	
					elseif($_REQUEST['add_text']!="")	
					{
						if(strpos($_REQUEST['add_text'],'%') !== false)
						{
							$message_e="% sign is not allowed in ad!";
						}
						else
						{
							 $remove = array("\n", "\r\n", "\r");
							$_REQUEST['add_text']=str_replace($remove,' ',$_REQUEST['add_text']);
						}
					}
				}
			 
		global $wpdb;
		
 	//Validation error.	
		if($message_e=="")
		{	
					
			//Adding the record if first time in the if content is blanks
			if(isset($_REQUEST['sbt_ads']) && $_REQUEST['sbt_ads']=="Save" && trim($_REQUEST['add_id'])=="" )
				{
						$ads_list = $wpdb->get_results( "SELECT uba_page_ids FROM  ".WP_ADDRESS_BAR_ADS_TABLE." where uba_page_ids IN ( select ID from ".WP_POSTS.")");
						
						if(count($ads_list) == '3')
						{
							$message_e="More than 3 ads are not allowed!";
							
						}
						else
						{
							$sSql = "INSERT INTO `". WP_ADDRESS_BAR_ADS_TABLE . "`  ( `uba_page_ids`,`uba_plug_link`,`uba_plug_text`,`uba_plug_status`,`uba_key_pos`,`uba_new_win`) value (";
											
							if($_REQUEST['remove_page']=="");
									$message="Please Select page/post.";	
							
							if($_REQUEST['uba_plug_link']=="");
									$message="Please Enter link Urls.";		
											
							if(is_array($_REQUEST['remove_page']))
							{
								$ads_id=implode(",",$_REQUEST['remove_page']);
								if(substr($ads_id, 0,1) == ',')
								{
									$ads_id=substr($ads_id, 1); 
								}
							}
							else
							{
								$ads_id=$_REQUEST['remove_page'];
							}
							$duplicate=0;
							foreach($_REQUEST['remove_page'] as $addpagedup)
							{
								if($addpagedup != '')
								{
									$dup = $wpdb->get_row( "SELECT uba_plug_id FROM  ".WP_ADDRESS_BAR_ADS_TABLE." where uba_page_ids like '%$addpagedup%' limit 1 ", ARRAY_A );
									
									
									if(isset($dup['uba_plug_id']) && $dup['uba_plug_id'] != '')
									{
										$duplicate=1;
									}
								}
							}
							
							if(trim($_REQUEST['before']) == '')
							{
								$_REQUEST['before']='0';
							}
							if(trim($_REQUEST['add_new_tab']) == 'on')
							{
								$_REQUEST['add_new_tab']='1';
							}
							else
							{
								$_REQUEST['add_new_tab']='0';
							}
							$sSql = $sSql . "'".$ads_id."',";
							$sSql = $sSql . "'".$_REQUEST['add_banner_code']."',";
							$sSql = $sSql . "'".rawurlencode($_REQUEST['add_text'])."',1,";
							$sSql = $sSql . "'".$_REQUEST['before']."',";
							$sSql = $sSql . "'".$_REQUEST['add_new_tab']."')";
							if($duplicate == 1)
							{
								$message= 'duplicate';
							}
							else
							{
								
								
								$wpdb->query($sSql);
								$message= 'success';
								unset($_REQUEST);
							}
						}
						
 					 
				} 
			else if(!trim($_REQUEST['add_id'])=="")//Updating the records if the content is available
				{
					
						if(is_array($_REQUEST['remove_page']))
						{
							$ads_id=implode(",",$_REQUEST['remove_page']);
							if(substr($ads_id, 0,1) == ',')
							{
								$ads_id=substr($ads_id, 1); 
							}
						}
						else
						{
							$ads_id=$_REQUEST['remove_page'];
						}
						$duplicate=0;
						foreach($_REQUEST['remove_page'] as $addpagedup)
						{
							if($addpagedup != '')
							{
								
								$dup = $wpdb->get_row( "SELECT uba_plug_id FROM  ".WP_ADDRESS_BAR_ADS_TABLE." where uba_page_ids like '%$addpagedup%' and uba_plug_id != '$_REQUEST[add_id]' limit 1 ", ARRAY_A );
								
								if(isset($dup['uba_plug_id']) && $dup['uba_plug_id'] != '')
								{
									$duplicate=1;
								}
							}
						}
						
						if(trim($_REQUEST['before']) == '')
						{
							$_REQUEST['before']='0';
						}
						if(trim($_REQUEST['add_new_tab']) == 'on')
						{
							$_REQUEST['add_new_tab']='1';
						}
						else
						{
							$_REQUEST['add_new_tab']='0';
						}
						$sSql = " UPDATE `". WP_ADDRESS_BAR_ADS_TABLE . "` SET ";						
				 		$sSql = $sSql . "`uba_page_ids` ='".$ads_id."',";
						$sSql = $sSql . "`uba_plug_link`='".$_REQUEST['add_banner_code']."',";
						$sSql = $sSql . "`uba_plug_text`='".rawurlencode($_REQUEST['add_text'])."',";
						$sSql = $sSql . "`uba_key_pos`='".$_REQUEST['before']."',";
						$sSql = $sSql . "`uba_new_win`='".$_REQUEST['add_new_tab']."'";
						$sSql = $sSql . " where `uba_plug_id`='".$_REQUEST['add_id']."'";
						
						
						if($duplicate == 1)
						{
							$message= 'duplicate';
						}
						else
						{
							$wpdb->query($sSql);
							$message= 'success';
							unset($_REQUEST);
							unset($_GET['ad_id']);
						}	
				}
			}
		
				$post_str='';
				$del_id=$_GET['del_id'];
				if(isset($del_id)  && $del_id != '')
				{
					 $del_Sql = "DELETE from `". WP_ADDRESS_BAR_ADS_TABLE. "` where uba_plug_id='".$del_id."'";
				 	 $wpdb->query($del_Sql);
				}
				$ad_id= $_GET['ad_id'];
				if(isset($ad_id) && $ad_id != '')
				{
					$post_str="where uba_plug_id='".$ad_id."'";	
					$c = $wpdb->get_row( "SELECT * FROM  ".WP_ADDRESS_BAR_ADS_TABLE." ".$post_str." limit 1 ", ARRAY_A );
					unset($_REQUEST);
				}
				//echo "SELECT * FROM  ".WP_ADDRESS_BAR_ADS_TABLE." where uba_page_ids IN ( select ID from ".WP_POSTS.")";
		 		$ads_list = $wpdb->get_results( "SELECT * FROM  ".WP_ADDRESS_BAR_ADS_TABLE." where uba_page_ids IN ( select ID from ".WP_POSTS.")");
				
				$pages_wp = $wpdb->get_results( "SELECT * FROM  ".WP_POSTS." where post_type in ('page','post')");
	 
			?>
<?php 
		 	 //cheking if the success massage is available
		 if (isset($message) && $message == 'success') { ?>

        <div class="greenMsg" id="message">
          <p>
            <?php echo _e( '<strong>Ad saved successfully !</strong>');?>
          </p>
        </div>
        <?php } 
		elseif(isset($message) && $message == 'duplicate')
		{
			?>

        <div class="redMsg" id="message">
          <p>
            <?php echo _e('<strong>Multiple ads on same page are not allowed !</strong>');?>
          </p>
        </div>
        <?php 
		}
		?>
<?php
	 //cheking if the error massage is available
	  if (isset($message_e)) { ?>
  <div class="redMsg" id="message">
  <p>
    <?php echo _e('<strong>'.$message_e.'</strong>');?>
    </p>
  </div>
  <?php } ?>
<BR>
<div>

<p>
<span class="title"><?php echo _e('Address Bar Ads'); ?></span>
<br />
<?php echo _e('Active Ads:'); ?></p>
	<!--ads list start here -->
	<table  width="800" cellspacing="5" cellpadding="5"  style="background-color:#F8F8F8;border-radius: 10px 10px 10px 10px; border-style: solid;border-width: 1px; border-color:#C4C4C4" id="" >
     <tr>
      <td class="lable1" width="150px" style="color:#21759B"><?php echo _e('Ad Text'); ?></td>
     <td class="lable1" style="color:#21759B"><?php echo _e('Pages/Posts'); ?></td>
     <td class="lable1" style="color:#21759B"><?php echo _e('Destination URL'); ?></td>
     <td class="lable1" style="color:#21759B"><?php echo _e('Link key'); ?></td>
     <td class="lable1" style="color:#21759B"><?php echo _e('Edit/Delete'); ?></td>
      </tr>
    <?php foreach($ads_list as $ad) { ?>
      <tr>
     
     <td width="150px" title="<?php echo stripslashes(urldecode($ad->uba_plug_text)); ?>"><?php echo _e(substr(stripslashes(urldecode($ad->uba_plug_text)),0,15)); ?>..</td>
     <td><?php
	 	$pages_list=explode(',',$ad->uba_page_ids);
		foreach($pages_list as $page_detail)
		{
			$page_name=explode('|',$page_detail);
		  echo _e($page_name[1]); ?>
          <br />
         <?php } ?>
    </td>
     <td width="150px" ><?php echo _e($ad->uba_plug_link); ?></td>
     <td ><?php echo _e('Enter'); ?></td>
     <td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=address-bar-ads/address_bar_ads.php&ad_id=<?php echo $ad->uba_plug_id; ?>"><?php  echo _e('Edit');?></a> / <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=address-bar-ads/address_bar_ads.php&del_id=<?php echo $ad->uba_plug_id; ?>"><?php echo _e('Delete'); ?></a></td>
      </tr>
      <?php } ?>
      <tr>
      <td>   <?php if(count($ads_list) < 3) { ?><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=address-bar-ads/address_bar_ads.php" class="addNew"><?php echo _e("Add New"); ?></a><?php } ?>
      </td>
      </tr>
      </table>
      <!-- ads list end here -->
       <br />
    <!-- widget-->
    <table  width="800" cellspacing="5" cellpadding="5"  style="background-color:#F8F8F8;border-radius: 10px 10px 10px 10px; border-style: solid;border-width: 1px; border-color:#C4C4C4" id="" >
    <tr>
    <td colspan="2" width="20%">
    <h3><?php echo _e("Help Us Perfect <br> Address Bar Ads"); ?></h3>
    </td>
     <td width="50%">
      <?php echo _e("Please consider donating if you found this plugin useful. Donations will be used to keep this plugin alive and up to date!"); ?>
       </td>
    <td width="30%">
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="9882WZK23JDWC">
        <input type="image" src="https://www.paypalobjects.com/en_US/IL/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" style="margin-left:8px; float:left">
        </form>
       </td>
      
       </tr>
       
	</table>
    <!-- end here>
      
   <!-- form starts here -->
   <br />
   <?php echo _e('New Ad:'); ?>
  <form name="frm_addressbarads" action="" method="post"  onSubmit="selectAllOptions('remove_page')";>
  <div id="edit_ad">
    <input type="hidden" name="add_id" value="<?php echo $c['uba_plug_id'];?>" />
    <table  width="800" cellspacing="5" cellpadding="5"  style="background-color:#f8f8f8;border-radius: 10px 10px 10px 10px; border:1px solid #c4c4c4; margin:20px 0 0 0; " id="test" >
      <tr>
        <td width="170px" align="right" ></td>
        <td width="150px" ></td>
        <td ></td>
      </tr>
     
      <tr>
        <td   align="right" ></td>
       
      </tr>
      <tr >
        <td height="32" width="170" class="lable" align="right" valign="top" ><a href="#" onmouseover="javascript: jQuery('#text_hidden').show();"; onmouseout="jQuery('#text_hidden').hide();"><?php echo _e('Text'); ?></a>
         </td>
        <td align="left" width="335"><textarea  cols="35" class="textArea"  rows="4"type="text" name="add_text" id="ad_text" ><?php if($_REQUEST['add_text'] != ''){ echo $_REQUEST['add_text']; } else { echo stripslashes(urldecode($c['uba_plug_text'])); } ?> </textarea></td>
         <td valign="top" align="left"> <div class="first"><span id="text_hidden" class="explBox"><?php echo _e('Write your message here'); ?></span></div></td>
      </tr>
       <tr>
        <td height="33" width="170" align="right" valign="top" class="lable"><a href="#" onmouseover="javascript: jQuery('#icon_hidden').show();" onmouseout="jQuery('#icon_hidden').hide();"><?php echo _e("Additonal Icons"); ?></a></td>
        <td valign="top" align="left" width="335"><table width="99%" border="0" cellspacing="0" cellpadding="0">
          <tr id="icons">
          	<td><a onclick="putIcon('&#9829;');" class="love"  name="love" id="love" ></a></td>
             <td><a onclick="putIcon('&#8226;');" class="circle"  name="circle" id="circle" ></a></td>
             <td><a onclick="putIcon('&#9786;');" name="smile" id="smile" class="smile"></a></td>
             <td><a onclick="putIcon('&#9787;');" class="blackSmiley"  name="blackSmiley" id="blackSmiley" ></a></td>
            <td><a onclick="putIcon('&#9733;');" class="star"  name="star" id="star" ></a></td>
            <td><a onclick="putIcon('&#8656;');" class="leftwards"  name="leftwards" id="leftwards" ></a></td>
            <td><a onclick="putIcon('&#8657;');" class="upwards"  name="upwards" id="upwards" ></a></td>
            <td><a onclick="putIcon('&#8658;');" class="rightwards"  name="rightwards" id="rightwards" ></a></td>
            <td><a onclick="putIcon('&#8659;');" class="downwards"  name="downwards" id="downwards" ></a></td>
            <td><a onclick="putIcon('&#8660;');" class="leftRight"  name="leftRight" id="leftRight" ></a></td>
            
          </tr>
          <tr id="icons">
            <td><a onclick="putIcon('&#9754;');" name="leftPointing" id="leftPointing" class="leftPointing"></a></td>
            <td><a onclick="putIcon('&#9755;');" class="rightPointing"  name="rightPointing" id="rightPointing" ></a></td>
            
            <td><a onclick="putIcon('&#9742;');" class="phoneService"  name="phoneService" id="phoneService" ></a></td>
            <td><a onclick="putIcon('&#9775;');" class="yinYang"  name="yinYang" id="yinYang" ></a></td>
            <td><a onclick="putIcon('&#9792;');" class="venus"  name="venus" id="venus" ></a></td>
            <td><a onclick="putIcon('&#9794;');" class="mars"  name="mars" id="mars" ></a></td>
            <td><a onclick="putIcon('&#9819;');" class="blackQueen"  name="blackQueen" id="blackQueen" ></a></td>
            <td><a onclick="putIcon('&#9836;');" class="musical"  name="musical" id="musical" ></a></td>
            <td><a onclick="putIcon('&#10004;');" class="checkMark"  name="checkMark" id="checkMark" ></a></td>
            <td><a onclick="putIcon('&#10008;');" class="ballotX"  name="ballotX" id="ballotX" ></a></td>
          </tr>
			</table>
     </td>
     <td valign="top" align="left"> <div class="first"><span id="icon_hidden" class="explBox"><?php echo _e('Optional- Choose some icons to make your ad more noticeable. NOTE: Some browsers might show boxes (&#9744;) instead of icons'); ?>
</span></div></td>

  
      </tr>
      <tr>
        <td height="32" width="170" class="lable"  align="right" valign="top" ><a href="#" onmouseover="javascript:jQuery('#ids_hidden').show();" onmouseout="jQuery('#ids_hidden').hide();"><?php echo  _e('Pages/Posts'); ?></a></td>
        <td align="left" width="335">
        <table width="99%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><?php 
                $page_ids_arr=explode(',',$c['uba_page_ids']);
                ?>
                <select name="add_page[]" id="add_page" style=" height: 80px; padding: 5px; width: 160px; margin-right:5px;" size=4 multiple>
                    <?php 
					$temp_sel=array();
                    $page_ids_temp=explode(',',$c['uba_page_ids']);
					foreach($page_ids_temp as $page_arr_temp)
                    {
                        $page_det_temp=explode('|',$page_arr_temp);
						$temp_sel[]=$page_det_temp[0];
					}
                    foreach($pages_wp as $page)
                    {
                        if($page->post_title !=''){ 
							if(!in_array($page->ID,$temp_sel)) {
								?>
								<option value="<?php echo $page->ID."|".$page->post_title; ?>" title="<?php echo _e($page->post_title); ?>"><?php echo _e($page->post_title); ?>&nbsp;&nbsp;[<?php echo _e($page->post_type); ?>]</option>
						<?php }
						}
					}
                    ?>
                   </select></td>
            <td><select name="remove_page[]" id="remove_page" style=" height:80px; padding: 5px; width: 160px;" size=4 multiple>
                    <?php
                    $page_ids_arr=explode(',',$c['uba_page_ids']);
                    foreach($page_ids_arr as $page_arr)
                    {
                        $page_det=explode('|',$page_arr);
                        ?>
                        <option value="<?php echo $page_arr; ?>" <?php echo $str_sel; ?> ><?php echo _e($page_det[1]); ?></option>
                <?php 	}
            ?>
                </select></td>
      </tr>
  <tr>
    <td height="23" align="left"> <a href="#" id="add"><?php echo _e('add');?> &gt;&gt;</a>  </td>
    <td align="left"> <a href="#" id="remove">&lt;&lt; <?php echo _e('remove');?> </a>  </td>
  </tr>
</table>

        </td>
        
          <td valign="top" align="left"> <div class="first"><span id="ids_hidden" class="explBox"><?php echo _e('Select the Pages and Posts on which you\'d like to activate your ad'); ?>  
</span></div></td>
      </tr>
      
       
      <tr >
        <td height="32" width="170" class="lable" align="right" ><a href="#" onmouseover="javascript: jQuery('#link_hidden').show();" onmouseout="jQuery('#link_hidden').hide();"><?php echo _e('Destination URL'); ?></a></td>
        <td  align="left" width="335"><input type="text" class="textFild" name="add_banner_code" id="add_banner_code" value="<?php if($_REQUEST['add_banner_code']!=''){ echo $_REQUEST['add_banner_code']; } else { echo $c['uba_plug_link']; } ?>" style=" height: auto; padding: 7px 5px; width: 325px;"></td>
        <td valign="top" align="left"> <div class="first"><span id="link_hidden" class="explBox"><?php echo _e('Choose your designated URL'); ?></span></div></td>
      </tr>
      <tr>
      	<td height="32" width="170" class="lable" align="right" ><a href="#" onmouseover="javascript: jQuery('#win_hidden').show();" onmouseout="jQuery('#win_hidden').hide();"><?php echo _e('Open link in a new tab/Window'); ?></a></td>
        <td align="left" width="346" ><input type="checkbox" name="add_new_tab" id="add_new_tab" <?php if($_REQUEST['add_new_tab'] !='' && $_REQUEST['add_new_tab']=='1'){ echo "checked=checked";} elseif($c['uba_new_win'] == '1') { echo "checked=checked";} ?>></td>
        <td valign="top"> <div class="first"><span id="win_hidden" class="explBox"><?php echo _e('Check to open ad in a new window'); ?></span></div></td>
      </tr>
      <tr >
        <td height="32" class="lable" align="right"><a href="#" onmouseover="javascript:jQuery('#key_hidden').show();" onmouseout="jQuery('#key_hidden').hide();"><?php echo _e('Link Key'); ?></a></td>
        <td align="left" width="335"><?php echo _e('Enter'); ?></td>
        <td valign="top" align="left"> <div class="first"><span id="key_hidden" class="explBox"><?php echo _e('Clicking \'Enter\' will lead to designated URL. Upcoming Address Bar Ads Pro version will enable customizable Link Keys'); ?></span></div></td>
      </tr>
       <tr>
      <td height="63" width="170" valign="top"> </td>
       <td align="left" width="335" valign="top"><table width="99%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="31"> <input type="radio" name="before" value="0" <?php if($_REQUEST['before'] !='' && $_REQUEST['before']=='0'){ echo "checked=checked"; }  elseif($c['uba_key_pos'] == '0') { echo "checked=checked"; } ?>/> <?php echo _e('Place Link Key before Ad Text'); ?></td>
  </tr>
  <tr>
    <td><input type="radio" name="before" value="1" <?php if($_REQUEST['before'] !='' && $_REQUEST['before']=='1'){ echo "checked=checked";} elseif($c['uba_key_pos'] == '1') { echo "checked=checked"; } ?>/>
     	<?php echo _e('Place Link Key after Ad Text'); ?></td>
      
  </tr>
  </table>

 		</td>
         <td align="left"></td>
      </tr>
    
      <tr >
       <td valign="top" >&nbsp;</td>
        <td align="left" width="335"> <table width="99%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="60%" align="left" ><input type="submit" name="sbt_ads" class="saveButton" value="Save"/></td>
  </tr>
</table>
</td>
        <td valign="top" align="left" >&nbsp;</td>
      </tr>
    </table>

    </div>
  </form>
  <script>
  
  jQuery(document).ready(function($){ 
		 jQuery('#add').click(function() { 
		  return !jQuery('#add_page option:selected').remove().appendTo('#remove_page');  
		 });  
		 jQuery('#remove').click(function() {  
		  return !jQuery('#remove_page option:selected').remove().appendTo('#add_page');  
		 });
		  jQuery('#add_page').dblclick(function() { 
		  return !jQuery('#add_page option:selected').remove().appendTo('#remove_page');  
		 });  
		 jQuery('#remove_page').dblclick(function() {  
		  return !jQuery('#remove_page option:selected').remove().appendTo('#add_page');  
		 });
		 
		jQuery('#ids_hidden').hide();
		jQuery('#text_hidden').hide();
		jQuery('#link_hidden').hide();
		jQuery('#key_hidden').hide();
		jQuery('#icon_hidden').hide();
		jQuery('#win_hidden').hide();
		
	});	
	
	
	function setCaretPosition(ctrl, pos)
	{
	
		if(ctrl.setSelectionRange)
		{
			ctrl.focus();
			ctrl.setSelectionRange(pos,pos);
		}
		else if (ctrl.createTextRange) {
			var range = ctrl.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	}


		function doGetCaretPosition (ctrl) {

			var CaretPos = 0;
			// IE Support
			if (document.selection) {
		
				ctrl.focus ();
				var Sel = document.selection.createRange ();
		
				Sel.moveStart ('character', -ctrl.value.length);
		
				CaretPos = Sel.text.length;
				}
				// Firefox support
				else if (ctrl.selectionStart || ctrl.selectionStart == '0')
					CaretPos = ctrl.selectionStart;
			
				return (CaretPos);
		
		}
	
		function putIcon(ele)
		{
			  var val1 = doGetCaretPosition(document.getElementById('ad_text'));
			 var text1=jQuery("#ad_text").val();
			 var text2 = text1.substr(0,val1);
			 var text4 = text1.substr(val1);
			 
			 text3=text2 + ele + text4;
			 
			 jQuery("#ad_text").val(text3);
			 setCaretPosition(document.getElementById('ad_text'),val1+1);
		}
		
		function selectAllOptions(selStr)
		{
		  var selObj = document.getElementById(selStr);
		  for (var i=0; i<selObj.options.length; i++) {
			selObj.options[i].selected = true;
			}
		}
		
		function getCaret(el) { 
		  if (el.selectionStart) { 
			return el.selectionStart; 
		  } else if (document.selection) { 
			el.focus(); 
		
			var r = document.selection.createRange(); 
			if (r == null) { 
			  return 0; 
			} 
		
			var re = el.createTextRange(), 
				rc = re.duplicate(); 
			re.moveToBookmark(r.getBookmark()); 
			rc.setEndPoint('EndToStart', re); 
		
			return rc.text.length; 
		  }  
		  return 0; 
		}

	
  </script>
    </div>
    <?php
}


//Function for the add the menu link at option table
function addressBarAdsPlugin_add_to_menu() 
	{
		add_options_page('Address Bar Ads','Address Bar Ads','manage_options',__FILE__,'addressbarads_admin_option');  
	}





	//function for the uninstall the plugins
if(function_exists('addressBarAdsPlugin_uninstall_hook') )
 	   register_uninstall_hook(__FILE__, 'addressBarAdsPlugin_uninstall_hook');
 
function addressBarAdsPlugin_uninstall_hook()
	{
		global $wpdb;
		if($wpdb->get_var("show tables like '". WP_ADDRESS_BAR_ADS_TABLE . "'")== WP_ADDRESS_BAR_ADS_TABLE) 
			{
				 $sSql = "DELETE TABLE `". WP_ADDRESS_BAR_ADS_TABLE. "`";
				 $wpdb->query($sSql);
			} 
	}
function myplugin_deactivation()
	{
		global $wpdb;
		if($wpdb->get_var("show tables like '". WP_ADDRESS_BAR_ADS_TABLE . "'")== WP_ADDRESS_BAR_ADS_TABLE) 
			{
				 $sSql = "DROP TABLE `". WP_ADDRESS_BAR_ADS_TABLE. "`";
				 $wpdb->query($sSql);
			} 
	}
//register_activation_hook(__FILE__, 'myplugin_install');
register_deactivation_hook(__FILE__, 'myplugin_deactivation');
add_action('admin_menu', 'addressBarAdsPlugin_add_to_menu'); 
?>
