<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Bootstrap Theme Shortcodes. 
 *
*/

//var_dump (class_exists('user_shortcodes'));
if(!class_exists('user_shortcodes'))
{
  require_once(e_CORE."shortcodes/batch/user_shortcodes.php");
}
//var_dump (class_exists('user_shortcodes'));
//var_dump ($euser_pref);
class euser_shortcodes extends user_shortcodes
{
	function __construct()
	{
				$this->sql = e107::getDb(); 
				$this->tp = e107::getParser();
//        $this->template = e107::getTemplate('euser', 'whatsnew_menu');
        $this->euser_pref = e107::getPlugPref('euser');
        
    		$this->sql->select("euser", "*", "user_id='".$this->var['user_id']."' ");
    		$this->euser_data = $this->sql->fetch();
	}

  // Provis�rio, at� o pull ser aprovado....
	function sc_user_jump_link($parm) 
	{
		global $full_perms;
//		$sql = e107::getDb();
//		$tp = e107::getParser();
		
//      var_dump ($full_perms);
		if (!$full_perms) return;
		$url = e107::getUrl();
		if(!$userjump = e107::getRegistry('userjump'))
		{
		//  $sql->db_Select("user", "user_id, user_name", "`user_id` > ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id ASC LIMIT 1 ");
		  $this->sql->gen("SELECT user_id, user_name FROM `#user` FORCE INDEX (PRIMARY) WHERE `user_id` > ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id ASC LIMIT 1 ");
		  if ($row = $this->sql->fetch())
		  {
			$userjump['next']['id'] = $row['user_id'];
			$userjump['next']['name'] = $row['user_name'];
		  }
		//  $sql->db_Select("user", "user_id, user_name", "`user_id` < ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id DESC LIMIT 1 ");
		  $this->sql->gen("SELECT user_id, user_name FROM `#user` FORCE INDEX (PRIMARY) WHERE `user_id` < ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id DESC LIMIT 1 ");
		  if ($row = $this->sql->fetch())
		  {
			$userjump['prev']['id'] = $row['user_id'];
			$userjump['prev']['name'] = $row['user_name'];
		  }
		  e107::setRegistry('userjump', $userjump);
		}
		

	
		if($parm == 'prev' || isset($parm['prev']))
		{
      if (isset($userjump['prev']['id'])){
		    if(isset($parm['link']))return $url->create('user/profile/view', $userjump['prev']);
		    if(isset($parm['title']))return $userjump['prev']['name'];
		
			  $icon = (deftrue('BOOTSTRAP')) ? $this->tp->toGlyph('chevron-left') : '&lt;&lt;';			
    	  return "<a class='e-tip".($parm['class']?" ".$parm['class']."":"")."' href='".$url->create('user/profile/view', $userjump['prev']) ."' title=\"".$userjump['prev']['name']."\">".$icon." ".LAN_USER_40."</a>\n";
		  }
      return "&nbsp;"; 
			// return isset($userjump['prev']['id']) ? "&lt;&lt; ".LAN_USER_40." [ <a href='".$url->create('user/profile/view', $userjump['prev'])."'>".$userjump['prev']['name']."</a> ]" : "&nbsp;";
		
		}
		else
		{
      if (isset($userjump['next']['id'])){
  		  if(isset($parm['link']))return $url->create('user/profile/view', $userjump['next']);
	   	  if(isset($parm['title']))return $userjump['next']['name'];

			  $icon = (deftrue('BOOTSTRAP')) ? $this->tp->toGlyph('chevron-right') : '&gt;&gt;';
			  return "<a class='e-tip".($parm['class']?" ".$parm['class']."":"")."' href='".$url->create('user/profile/view', $userjump['next'])."' title=\"".$userjump['next']['name']."\">".LAN_USER_41." ".$icon."</a>\n";
      }
      return "&nbsp;"; 
      // return isset($userjump['next']['id']) ? "[ <a href='".$url->create('user/profile/view', $userjump['next'])."'>".$userjump['next']['name']."</a> ] ".LAN_USER_41." &gt;&gt;" : "&nbsp;";
		}
	}

  //redefino aqui a user_addons, porque n�o faz bem o que eu quero, mais por causa do template aplicado.......
	function sc_user_addons($parm='')
	{
  // � quase uma c�pia do USER_ADDONS, mas ao contr�rio, n�o precisa do ficheiro e_euser.php no plugin...
  //Primeiro copiamos o USER_addons, mas com umas pequenas altera��es...
  // Vamos ver de plugins que tenham o e_user....
      global $euser_template;
  		$data 		= e107::getAddonConfig('e_user',null,'profile',$this->var);
  	if(empty($data))
		{
			return;
		}
//--		$text = '';	
		foreach($data as $plugin=>$val)
		{
			foreach($val as $v)
			{
				$value = vartrue($v['url']) ? "<a href=\"".$v['url']."\">".$v['text']."</a>" : $v['text'];		
//        var_dump ($plugin);
				$array = array(
					'EUSER_ADDON_ICON' => constant(IMAGE_.$plugin),
					'EUSER_ADDON_LABEL' => $v['label'],
					'EUSER_ADDON_TEXT' => $value
				);
				$text .= $this->tp->parseTemplate($euser_template['plugins'], true, $array);
			}		
		}
//        var_dump ($text);

		return $text;			
  }

//public function user_avatar_shortcode($parm=null) //TODO new function $tp->toAvatar(); so full 
//public function user_picture_shortcode($parm=null) //TODO new function $tp->toAvatar(); so full arrays can be passed to it. 
//public function sc_user_picture($parm=null) //TODO new function $tp->toAvatar(); so full 
//public function sc_user_avatar($parm=null) //TODO new function $tp->toAvatar(); so full arrays arrays can be passed to it. 
	function sc_euser_online($parm='')
	{
//var_dump (e107::isInstalled("pm"));
//var_dump ($this->var['user_id'] > 0);
//    var_dump ($this->var['user_id']);
//    var_dump ($this->var['user_name']);
  	$on_name = "".$this->var['user_id'].".".$this->var['user_name']."";
  	$check = $this->sql-> db_Count("online","(*)","WHERE online_user_id='".$on_name."'");
		return (( $check > 0 )?IMAGE_online:IMAGE_offline);
  }

	function sc_euser_controls($parm='')
	{
    global $euser_template;
		return $this->tp->parseTemplate((USERID == $this->var['user_id']?$euser_template['controls_logged']:$euser_template['controls'].(ADMIN?"<br>".$euser_template['controls_logged']:"")), TRUE, $this);
  }

	function sc_euser_settings($parm='')
	{
//        var_dump ($parm['text']);

    			if (USERID == $this->var['user_id'] && ADMIN) {
//				$text .= "{USER_UPDATE_LINK}";
        $url=e_HTTP."usersettings.php";
        $title = LAN_USER_38;
        $text = LAN_EUSER_651;
//        return "<a href='".e_HTTP."usersettings.php' title='".LAN_USER_38."'".($parm['class']?" class=".$parm['class']."":"").">".($text?:IMAGE_settings."&nbsp;".LAN_USER_38)."</a>";
			} else {
				if (USERID == $this->var['user_id']) {
////					$text .= "<tr><td colspan='2' style='width:100%' class='forumheader'><center><a href='".e_BASE."usersettings.php'>".PROFILE_360."</a></center></td></tr>";
					$url = e_BASE."usersettings.php";
					$title = PROFILE_360;
					$text = PROFILE_360;
//					return "<a href='".e_BASE."usersettings.php' title='".PROFILE_360."'".($parm['class']?" class=".$parm['class']."":"").">".($text?:IMAGE_settings."&nbsp;".PROFILE_360)."</a>";
				} elseif (ADMIN && getperms("4")) {
////					$text .= "<tr><td colspan='2' style='width:100%' class='forumheader'><center><a href='euser_settings.php?uid=".$this->var['user_id']."'>".PROFILE_29."</a></center></td></tr>";
					$url = "euser_settings.php?page=settings&uid=".$this->var['user_id'];
//					$title = PROFILE_29;
          $title = LAN_USER_39;
          $text = LAN_EUSER_652;
//					return "<a href='euser_settings.php?page=settings&uid=".$this->var['user_id']."' title='".PROFILE_29."'".($parm['class']?" class=".$parm['class']."":"").">".($text?:IMAGE_settings."&nbsp;".PROFILE_29)."</a>";
				}
			}

    if (isset($parm['link'])){return $url;}
    if (isset($parm['title'])){return $title;}

        return "<a href='".$url."' title='".$title."'".($parm['class']?" class=".$parm['class']."":"").">".IMAGE_settings."&nbsp;".$text."</a>";

  }
  
/*
	function sc_euser_settingslink($parm='')
	{
    return $this->euser_settingslink().PROFILE_17."</a>";
  }
*/
	function sc_euser_bgimage($parm='')
	{
    // pref bgimage n�o est� no plugin.xml
	if ($this->euser_pref['bgimage'] == 'Yes') {
//		$sql->mySQLresult = @mysql_query("SELECT user_background FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
		$bg = $this->euser_data;
		if ($bg['user_background'] != '') {
			if (eregi('http', $bg['user_background'])) {
			return "<body background='".$bg['user_background']."'>";
			} else {
			return "<body bgcolor='".$bg['user_background']."'>";
			}
		}
	}
  }

	function sc_euser_avatarminw($parm='')
	{
//    global $euser_pref;
    return $this->euser_pref['avatar_width'];
  }

	function sc_euser_avatar($parm='')
	{

//  global $tp, $user_sc;
//  $doc = DOMDocument::loadHTML($tp->parseTemplate("{USER_AVATAR=".$this->var['user_id']."}", TRUE, $user_sc));
  $pref = e107::getPref();
  $doc = DOMDocument::loadHTML($this->tp->parseTemplate("{SETIMAGE: w=".varset($pref['im_width'], 120)."}{USER_AVATAR=".$this->var['user_id']."}", TRUE, $this));
//$image = $doc->getElementsByTagName('img');
foreach($doc->getElementsByTagName('img') as $image){
//    foreach(array('width', 'height') as $attribute_to_remove){
        if($image->hasAttribute('title')){
            $image->removeAttribute('title');
        }
//    }
}

//  return $this->euser_settingslink().$doc->saveHTML()."</a>";
  return $doc->saveHTML();
  }
  
  
  
  
	function sc_euser_addfriend($parm='')
	{
//    var_dump ($parm);
  	if ($this->euser_pref['friends']) {
// Porque se repete isto? � c�digo original...		
		if ($this->euser_pref['user_tracking'] == "session") {
			$ulang = $_SESSION['e107language_'.$this->euser_pref['cookie_name']];
		} else {
			$ulang = $_COOKIE['e107language_'.$this->euser_pref['cookie_name']];
		}
		if ($this->euser_pref['user_tracking'] == "session") {
			$ulang = $_SESSION['e107_language'];
		} else {
			$ulang = $_COOKIE['e107_language'];
		}
// Porque se repete isto? � c�digo original...		
/*
    $sql->mySQLresult = @mysql_query("SELECT user_friends, user_friends_request FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
		$settings = $sql->db_Fetch();
*/
//		$settings = $this->euser_data;

		$friendb = explode("|", $this->euser_data['user_friends']);
		$friendb1 = explode("|", $this->euser_data['user_friends_request']);
		if (USER && $this->var['user_id'] != USERID && !in_array(USERID, $friendb) && !in_array(USERID, $friendb1)) {
//			$text .= "<a href='euser.php?id=".$this->var['user_id']."&add' style=\"text-decoration: none;\" title='".PROFILE_16."'><img src='images/buttons/".e_LANGUAGE."_addfriend.png' border='0'></a>";
    // TEMPLATIZAR?
  			return "<a class='btn ".(empty($parm['class'])?'btn-sm btn-default':$parm['class'])."' href='euser.php?id=".$this->var['user_id']."&add' style=\"text-decoration: none;\" title='".PROFILE_16."'>".($parm['icon']?$this->tp->toGlyph($parm['icon']):IMAGE_addfriend)."&nbsp;".PROFILE_16."</a>";
		}
	}
  }

	function sc_euser_sendpm($parm='')
	{
    return $this->tp->parseTemplate("{SENDPM: user=" . $this->var['user_id'] . "&glyph=envelope}", true);
  }
    
	function sc_euser_warn($parm='')
	{
	 // O WARN desapareceu do mapa na v2.x, isto � o c�digo antigo que l� estava...
  if ($euser_pref['user_warn_support'] == "Yes" AND $sql->db_Select("user_extended", "*", "user_extended_id='$this->var['user_id']' AND user_warn!='null' AND user_warn!=''")) {
	$text .= "<TR>
		<td  {$main_colspan} colspan=2 style='width:100%' class='forumheader3'><span style='float:left'>".PROFILE_311.":&nbsp;&nbsp;</span><span style='float:right; text-align:right'>{USER_WARN}</span></td>
		</TR>";
	}
  }

	function sc_euser_plugins($parm='')
	{
  // � quase uma c�pia do USER_ADDONS, mas ao contr�rio, n�o precisa do ficheiro e_euser.php no plugin...
  //Primeiro copiamos o USER_addons, mas com umas pequenas altera��es...
      global $euser_template;

				$data = array(
					'news' => array ('label'=>LAN_EUSER_600,'table'=>'news','count_field'=>'news_author', 'count_data'=>$this->var['user_id']),
					'upload' => array ('label'=>LAN_EUSER_601,'table'=>'download','count_field'=>'download_author', 'count_data'=>$this->var['user_name']),
					'download' => array ('label'=>LAN_EUSER_602,'table'=>'download','count_field'=>'download_request_userid', 'count_data'=>$this->var['user_id']),
					'links' => array ('label'=>LAN_EUSER_603,'table'=>'links_page','count_field'=>'links_author', 'count_data'=>$this->var['user_id']),
				);

//--		$text = '';	
		foreach($data as $plugin=>$val)
		{
//var_dump ($val);
//			foreach($val as $v)
//			{
// Para sair? Com c�lculo do valor, mais percentagem...
//				$value = vartrue($v['url']) ? "<a href=\"".$v['url']."\">".$v['text']."</a>" : $v['text'];		
//var_dump ($v);
    if(($total = $this->sql->count($val['table']))>0)
    {
      $usercount = $this->sql->count($val['table'],"(*)","where ".$val['count_field']."=".$val['count_data']);
//			$text .= "<TR><td {$main_colspan} style='width:100%' class='forumheader3'><span style='float:left'><img src='images/news.png'>&nbsp;".PROFILE_38."&nbsp;&nbsp;</span><span style='float:right; text-align:right'>{$usernews} ( ".(($usernews!=0)?round(($usernews/$totalnews)*100,2):"0")."% )</td></TR>";
      //var_dump ($parm['ratio']);
//      if (isset($parm['percent'])){return (($usernews!=0)?round(($usernews/$totalnews)*100,2):"0");}


//      var_dump ($totalnews);
//      var_dump ($usernews);
			$value = ($usercount?:"0")."&nbsp;(&nbsp;".(($usercount!=0)?round(($usercount/$total)*100,2):"0")."%&nbsp;)";
 //   }
//    var_dump ($val['url']);
		$value = (vartrue($val['url']) ? "<a href=\"".$val['url']."\">".$value."</a>" : $value);		
    


//        var_dump ($plugin);
				$array = array(
					'EUSER_ADDON_ICON' => constant(IMAGE_.$plugin),
					'EUSER_ADDON_LABEL' => $val['label'],
					'EUSER_ADDON_TEXT' => $value
				);
				$text .= $this->tp->parseTemplate($euser_template['plugins'], true, $array);
			}		
		}
//        var_dump ($text);

		return $text;			
//************************************************************************
//FALTA O NUMERO DE CLASSIFICADOS
//************************************************************************
  }

	function sc_euser_news($parm='')
	{
//      var_dump ($parm['percent']);
    if(($totalnews = $this->sql->count("news"))>0)
    {
      $usernews = $this->sql->count("news","(*)","where news_author=".$this->var['user_id']);
//			$text .= "<TR><td {$main_colspan} style='width:100%' class='forumheader3'><span style='float:left'><img src='images/news.png'>&nbsp;".PROFILE_38."&nbsp;&nbsp;</span><span style='float:right; text-align:right'>{$usernews} ( ".(($usernews!=0)?round(($usernews/$totalnews)*100,2):"0")."% )</td></TR>";
      //var_dump ($parm['ratio']);
      if (isset($parm['percent'])){return (($usernews!=0)?round(($usernews/$totalnews)*100,2):"0");}

//      var_dump ($totalnews);
//      var_dump ($usernews);
			return ($usernews?:"0");
    }
  }

  
	function sc_euser_uploads($parm='')
	{
    if(($totaluploads = $this->sql->count("download"))>0)
    {
      $useruploads = $this->sql->count("download","(*)","where download_author='".$this->var['user_name']."'");
//			$text .= "<TR><td {$main_colspan} style='width:100%' class='forumheader3'><span style='float:left'><img src='images/news.png'>&nbsp;".PROFILE_38."&nbsp;&nbsp;</span><span style='float:right; text-align:right'>{$usernews} ( ".(($usernews!=0)?round(($usernews/$totalnews)*100,2):"0")."% )</td></TR>";
      if (isset($parm['percent'])){return (($useruploads!=0)?round(($useruploads/$totaluploads)*100,2):"0");}

  		return ($useruploads?:"0");
    }
  }

	function sc_euser_downloads($parm='')
	{
    if(($totaluploads = $this->sql->count("download_requests"))>0)
    {
      $userdownloads = $this->sql->count("download","(*)","download_requests","(*)","where download_request_userid=".$this->var['user_id']."'");
//			$text .= "<TR><td {$main_colspan} style='width:100%' class='forumheader3'><span style='float:left'><img src='images/news.png'>&nbsp;".PROFILE_38."&nbsp;&nbsp;</span><span style='float:right; text-align:right'>{$usernews} ( ".(($usernews!=0)?round(($usernews/$totalnews)*100,2):"0")."% )</td></TR>";
      if (isset($parm['percent'])){return (($userdownloads!=0)?round(($userdownloads/$totaldownloads)*100,2):"0");}

  		return ($userdownloads?:"0");
    }
  }

	function sc_euser_links($parm='')
	{
    if(($totallinks = $this->sql->count("links_page"))>0)
    {
      $userlinks = $this->sql->count("links_page","(*)","where link_author=".$this->var['user_id']."'");
//			$text .= "<TR><td {$main_colspan} style='width:100%' class='forumheader3'><span style='float:left'><img src='images/news.png'>&nbsp;".PROFILE_38."&nbsp;&nbsp;</span><span style='float:right; text-align:right'>{$usernews} ( ".(($usernews!=0)?round(($usernews/$totalnews)*100,2):"0")."% )</td></TR>";
      if (isset($parm['percent'])){return (($userlinks!=0)?round(($userlinks/$totallinks)*100,2):"0");}

  		return ($totallinks?:"0");
    }
  }


	function sc_euser_mp3($parm='')
	{
			if ($this->euser_data['user_mp3'] != "" && $euser_pref['mp3enabled'] && !isset($_GET['page'])) {
//--			$sql->mySQLresult = @mysql_query("SELECT user_mp3 FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
//--			$mp3 = $sql->db_Fetch();
//			if ($mp3['user_mp3'] != "" && $euser_pref['mp3enabled'] == "ON" && !isset($_GET['page'])) {
				$type = substr(strrchr($this->euser_data['user_mp3'], '.'), 1);
				if(strpos($this->euser_data['user_mp3'], "http://") === false && strpos($this->euser_data['user_mp3'], "https://") === false && strpos($this->euser_data['user_mp3'], "ftp://") === false) {
					$mp3file = "usermp3/".$this->var['user_id'].".".$type;
					$mp3display = str_replace("_", " ", $this->euser_data['user_mp3']);
				} else {
					$mp3file = $this->euser_data['user_mp3'];
					$mp3break = explode("/", $this->euser_data['user_mp3']);
					$mp3display = str_replace("_", " ", end($mp3break));
				}
				// Zene lejatszasa
/*
				if (!USERID == ADMIN || !USER) {
					$sql->mySQLresult = @mysql_query("SELECT user_friends, user_settings FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
					$settings = $sql->db_Fetch();
					$break = explode("|",$settings['user_settings']);
					$friendb = explode("|", $settings['user_friends']);
*/
					if ((!USER && $break[10] == 1) || ($break[10] == 1 && $this->var['user_id'] != USERID && !isset($_GET['add']))) {
						//----------- Only friends
    ap_onlyfriends(array('',''));
/*
						if (((!in_array(USERID, $friendb)) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "")) || !USER) {
							$text .= "</table>";
							$display = $tp->parseTemplate($text, TRUE, $user_sc);
							$ns->tablerender("",$display);
							require_once(FOOTERF);
							exit;
						} else if ($euser_pref['friends'] != "ON") {
							$text .= "</table>";
							$display = $tp->parseTemplate($text, TRUE, $user_sc);
							$ns->tablerender("",$display);
							require_once(FOOTERF);
							exit;
						}
*/
					}
//				}
				if ($this->euser_pref['mp3_autoplay']) {
					$profile_mp3_autoplay = "&autoplay=1";
				}
				if ($this->euser_pref['mp3_loop']) {
					$profile_mp3_loop = "&loop=1";
				}
				if ($this->euser_pref['mp3_volume']) {
					$profile_mp3_volume = $this->euser_pref['mp3_volume'];
					if ($profile_mp3_volume > 200) $profile_mp3_volume = 200;
					$profile_mp3_volume = "&volume=".$profile_mp3_volume."";
				}
				return "<object type='application/x-shockwave-flash' data='player_mp3_maxi.swf' width='150' height='16'>
					<param name='wmode' value='transparent' />
					<param name='movie' value='player_mp3_maxi.swf' />
					<param name='FlashVars' value='mp3=".$mp3file.$profile_mp3_autoplay.$profile_mp3_loop.$profile_mp3_volume."' />
					</object>";
			}
  } 
  
  	function sc_euser_comments($parm='')
	{
    // J� existe profile coments no core...
    // Depois tenho de por aqui algo para s� ser vis�vel conforme os parms...
		if((e107::getPref('profile_comments')) && (isset($parm['caption'])))
		{
        global $euser_template;

        $euser_template['comments_caption'] = $this->tp->lanVars($euser_template['comments_caption'], array('x'=>($comnumrows>0?$comnumrows:NULL)));
      return $this->tp->parseTemplate($euser_template['comments_caption'], TRUE, $this);
    }
//---      if (($_GET['page'] == comments) || (!$_GET['page'])){
			// Check member settings - NO Admin & NO Friends
//---			if (!USERID == ADMIN || !USER) {
/*
				$sql->mySQLresult = @mysql_query("SELECT user_friends, user_settings FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
				$settings = $sql->db_Fetch();
				$break = explode("|",$settings['user_settings']);
				$friendb = explode("|", $settings['user_friends']);
*/
//---				if ((!USER && $break[9] == 1) || ($break[9] == 1 && $this->var['user_id'] != USERID && !isset($_GET['add']))) {
					//----------- Only friends
//---    ap_onlyfriends(array(PROFILE_253,PROFILE_253a));
/*
					if (((!in_array(USERID, $friendb)) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "")) || !USER) {
						$text .= "<br/>".$username." ".PROFILE_253;
						$display = $tp->parseTemplate($text, TRUE, $user_sc);
						$ns->tablerender("",$display);
						require_once(FOOTERF);
						exit;
					} else if ($euser_pref['friends'] != "ON") {
						$text .= "<br/>".$username." ".PROFILE_253a;
						$display = $tp->parseTemplate($text, TRUE, $user_sc);
						$ns->tablerender("",$display);
						require_once(FOOTERF);
						exit;
					}
*/
//---				}
//---			}
//			if ($euser_pref['commentson'] == "ON" || $euser_pref['commentson'] == "") {
//---			if ($this->euser_pref['commentson']) {

//        var_dump ($parm['caption']);
//        global $euser_template;
//        var_dump ($euser_template);
        
//--                if (isset($parm['caption'])){return $this->tp->parseTemplate($euser_template['comments_caption'], TRUE, $this);}

//--$text .= "<div class='virtualpage4".(($_GET['page']==comments)?"":" hidepiece")."'>";

// Carrego o ficheiro para no futuro ter uma hip�tese de reoordenar como eu quiser isto...
//---         return require("includes/comments.php");
/*
				// MULTIPAGES INFO
				if ($euser_pref['apcomments'] != '') {
					$rowsPerPage = $euser_pref['apcomments'];
				} else {
					$rowsPerPage = 5;
				}
				$pageNum = 1;
				if(isset($_GET['pgnum'])) {
					$pageNum = intval($_GET['pgnum']);
				}
				$offset = ($pageNum - 1) * $rowsPerPage;
				if(isset($_GET['comment_order'])) {
					if($_GET['comment_order'] == "ASC" || $_GET['comment_order'] == "DESC") {
						$comment_order = $_GET['comment_order'];
					}
				}
				if (!$comment_order == ASC || !$comment_order == DESC) {
					$comment_order = "DESC";
				}
				$sql->mySQLresult = @mysql_query("SELECT com_id, com_message, com_date, com_by FROM ".MPREFIX."euser_com WHERE com_to='".$this->var['user_id']."' AND com_type='prof' ORDER BY com_date $comment_order LIMIT $offset,$rowsPerPage");
				$comm = $sql->db_Rows();
				$maxPage = ceil($comnumrows/$rowsPerPage);
				$self = $_SERVER['PHP_SELF'];
				$nav  = '';
				for($page = 1; $page <= $maxPage; $page++) {
					if ($page == $pageNum) {
						$nav .= "";
					} else {
						$nav .= " <a href=\"$self?id=".$this->var['user_id']."&page=comments&comment_order=".$comment_order."&pgnum=".$page."\">$page</a> ";
					}
				}
				if ($pageNum > 1) {
					$page  = $pageNum - 1;
					$prev  = " <a href=\"$self?id=".$this->var['user_id']."&page=comments&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_204."</a> ";
					$first = " <a href=\"$self?id=".$this->var['user_id']."&page=comments&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_205."</a> ";
				} else {
					$prev  = ''; // we're on page one, don't print previous link
					$first = '&nbsp;'; // nor the first page link
				}
				if ($pageNum < $maxPage) {
					$page = $pageNum + 1;
					$next = " <a href=\"$self?id=".$this->var['user_id']."&page=comments&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_202."</a> ";
					$last = " <a href=\"$self?id=".$this->var['user_id']."&page=comments&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_203."</a> ";
				} else {
					$next = ''; // we're on the last page, don't print next link
					$last = '&nbsp;'; // nor the last page link
				}
					if ($euser_pref['maxpcomment'] != '') {
						$maxpcomment = $euser_pref['maxpcomment'];
					} else {
						$maxpcomment = 100;
					}
				if ($comm == 0) {
//					$text .= "<br><table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/comments.png'><i>".PROFILE_32."</i></td></tr></table>";
					$text .= "<table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/comments.png' style='vertical-align:middle'>&nbsp;<i>".PROFILE_32."</i></td></tr>";
				} else {
					$text .= "<br><table width='100%' class='fborder'>

						<tr>
							<td style='width:20%; text-align:left' class='forumheader' colspan='2'><img src='images/comments.png'><i>".PROFILE_36a." (".$comnumrows."):</i></td>";
							if ($comment_order == DESC) {
							$text .= "<td style='width:80%; text-align:right' class='forumheader' colspan='2'>".PROFILE_256."&nbsp;&nbsp;<a href=\"$self?id=".$this->var['user_id']."&page=comments&comment_order=ASC\"><img src='images/order_down.png' title='".PROFILE_310."'></a></td>";
							} else {
							$text .= "<td style='width:80%; text-align:right' class='forumheader' colspan='2'>".PROFILE_256."&nbsp;&nbsp;<a href=\"$self?id=".$this->var['user_id']."&page=comments&comment_order=DESC\"><img src='images/order_up.png' title='".PROFILE_309."'></a></td>";
							}
							$text .= "</tr>
					</table>";


					$text .= "<br/>";					//Profil hozz�sz�l�sok list�ja indul
					for ($i = 0; $i < $comm; $i++) {
						$com = $sql->db_Fetch();
						$from = mysql_query("SELECT * FROM ".MPREFIX."user WHERE user_id=".$com['com_by']." ");
						$from = mysql_fetch_assoc($from);
						$date = date("Y-m-j. H:i", $com['com_date']);
						$comid = $com['com_id'];
						$user_name = $from['user_name'];
						$on_name = "".$com['com_by'].".".$user_name."";
						$checkonline = mysql_query("SELECT * FROM ".MPREFIX."online WHERE online_user_id='".$on_name."'");
						$checkonline = mysql_num_rows($checkonline);
						//e107_0.8 compatible
						if(file_exists(e_HANDLER."level_handler.php")){
							require_once(e_HANDLER."level_handler.php");
							$ldata = get_level($from['user_id'], $from['user_forums'], $from['user_comments'], $from['user_chats'], $from['user_visits'], $from['user_join'], $from['user_admin'], $from['user_perms'], $euser_pref);
						} else {
							//
						}
						if (strstr($ldata[0], "IMAGE_rank_main_admin_image")) {
							$from_level = "".PROFILE_276."<br/>$ldata[1]";
						}
						else if(strstr($ldata[0], "IMAGE")) {
							$from_level = "".PROFILE_277."<br/>$ldata[1]<br/>";
						} else {
							$from_level = $ldata[1];
						}
						$gen = new convert;
						$from_join = $gen->convert_date($from['user_join'], "forum");
						$from_signature = $from['user_signature'] ? $tp->toHTML($from['user_signature'], TRUE) : "";
						$fromext = mysql_query("SELECT * FROM ".MPREFIX."user_extended WHERE user_extended_id=".$com['com_by']." ");
						$fromext = mysql_fetch_assoc($fromext);

						if( $checkonline > 0 ) {
							$online = "<img src='images/online.gif' title='".PROFILE_96."' style='vertical-align: middle;' />";
						} else {
							$online = "";
						}
						unset($checkonline,$on_name);

						$text .= "<br><table width='100%' class='fborder'>
						<tr>
							<td style='width:20%; text-align:left' class='fcaption'>".PROFILE_268."".$from['user_name']."</td>
							<td style='width:60%; text-align:left' class='fcaption'>".PROFILE_269."</td>
							<td style='width:20%; text-align:right' class='fcaption'>id: #".$comid."</td>
						</tr>
							<td class='forumheader'>&nbsp;".$online."&nbsp;&nbsp;<a href='euser.php?id=".$com['com_by']."'><b>".$from['user_name']."</b></a></td>
							<td class='forumheader' style='vertical-align: middle;' /><img src='images/post.png'>&nbsp;".$date."</td>
							<td class='forumheader' style='vertical-align: middle; text-align:right' /><a href='".e_PLUGIN."pm/pm.php?send.".$com['com_by']."'><img src='".e_PLUGIN."/pm/images/pm.png' title='".PROFILE_138."'></a></td></tr>
						<tr>
							<td class='forumheader3' style='vertical-align: top; width='20%;' />";

						$text .= "<br><table width='100%' class='fborder'>
						<tr>
							<td style='width:20%; text-align:left' class='fcaption'>".PROFILE_268."".$from['user_name']."</td>
							<td style='width:60%; text-align:left' class='fcaption'>".PROFILE_269."</td>
							<td style='width:20%; text-align:right' class='fcaption'>id: #".$comid."</td>
						</tr>
  							<td class='forumheader'>&nbsp;<img src='images/".(( $check > 0 )?"green":"gray").".png' title='".(( $check > 0 )?PROFILE_96:PROFILE_97)."' style='vertical-align: bottom;' />&nbsp;&nbsp;<a href='euser.php?id=".$com['com_by']."'><b>".$from['user_name']."</b></a></td>
							<td class='forumheader' style='vertical-align: middle;' /><img src='images/post.png'>&nbsp;".$date."</td>
							<td class='forumheader' style='vertical-align: middle; text-align:right' /><a href='".e_PLUGIN."pm/pm.php?send.".$com['com_by']."'><img src='".e_PLUGIN."/pm/images/pm.png' title='".PROFILE_138."'></a></td></tr>
						<tr>
							<td class='forumheader3' style='vertical-align: top; width='20%;' />";
						unset($checkonline,$on_name);

						// GET COMMENTERS AVATAR
						if($from[user_image] == "") {
							$av = "".e_PLUGIN."euser/images/noavatar.png";
							$text .= "".$from['user_customtitle']."<br/><br/><a href='euser.php?id=".$com['com_by']."'><img src='".$av."' border='1' ".$avwidth." ".$avheight."  alt='' /></a>";
						} else {
							$av = $from[user_image];
							require_once(e_HANDLER."avatar_handler.php");
							$av = avatar($av);
							$text .= "".$from['user_customtitle']."<br/><br/><a href='euser.php?id=".$com['com_by']."'><img src='".$av."' border='1' ".$avwidth." ".$avheight."  alt='' /></a>";
						}
						if ($euser_pref['user_warn_support'] == "Yes" AND $fromext['user_warn'] !='null' AND $fromext['user_warn'] !='') {
							$text .= "<br/><img src=\"".THEME_ABS."images/warn/".$fromext['user_warn'].".png\">";
						}
						$text .= "<br/>$from_level<br/><div class='smallblacktext'>".PROFILE_270."$from_join<br/>".PROFILE_272.$fromext['user_location']."</div></td>";
						$message = $tp -> toHTML($com['com_message'], true, 'parse_sc, constants');
						$text .= "<td class='forumheader3' colspan='2' style='vertical-align: top;'>".$message."<hr width='80%' align='left' size='1' noshade ='noshade'>$from_signature</td></tr>";
						$text .= "<tr><td class='forumheader'><div class='smallblacktext'><a href='".e_SELF."?".e_QUERY."#header' onclick=\"window.scrollTo(0,0);\">".PROFILE_271."</a></div></td>";
						if (USER) {
							if ($comnumrows < $maxpcomment) {
								$text .= "<td colspan='2'  class='forumheader' style='vertical-align: middle; text-align:right' /><div class='smallblacktext'>| <a href='".e_SELF."?".e_QUERY."#newprofilecomment'>".PROFILE_414."</a> | <a href='".e_SELF."?".e_QUERY."&vtoname=".$from['user_name']."&vtodate=".$date."&vtoid=".$comid."#newprofilecomment'>".PROFILE_415."</a> |</td></div></tr></table><br/><br/>";
							} else {
								$text .= "<td colspan='2'  class='forumheader'></td></tr></table><br/><br/>";
							}
						} else {
							$text .= "<td colspan='2'  class='forumheader'></td></tr></table><br/><br/>";
						}
					}
				}
        if (($prev.$nav.$next)!='') {
				$text .= "<br/><center>".$prev.$nav.$next."</center><br/><br/>";
        }

          $text .="<tr><td>";
				// Hozz�sz�l�sok list�z�s�nak v�ge
				if (USER) {

					if ($comnumrows < $maxpcomment) {
						if (isset($_GET['vtoname']) && isset($_GET['vtodate']) && isset($_GET['vtoid'])) {
							$vtoname = $_GET['vtoname'];
							$vtodate = $_GET['vtodate'];
							$vtoid = $_GET['vtoid'];
							$vtomessage = "[blockquote]".PROFILE_279."".$vtoname." #".$vtoid."".PROFILE_280."[/blockquote]";
						}
						$cbox .= "<a name='newprofilecomment'></a>";
//						$text .= "<form method='post' action='formhandler.php'><table width='100%'><tr><td class='forumheader' style='vertical-align: middle;' /><img src='images/post1.png'>&nbsp;&nbsp;<b>".PROFILE_33."</b></td>";
						$cbox .= "<form method='post' action='formhandler.php' style='margin:5px' ><table width='100%'><tr><td style='vertical-align: middle;text-align:left' />";

								if ($euser_pref['buttontype'] == "Yes") {
									$cboxbut = "<input type='image' name='post_comment' onmouseover='this.src=\"images/buttons/".e_LANGUAGE."_comment_over.gif\"' onmouseout='this.src=\"images/buttons/".e_LANGUAGE."_comment.gif\"' src='images/buttons/".e_LANGUAGE."_comment.gif' title='".PROFILE_208."' >";
								} else {
//									$cboxbut= "<input type='submit' name='post_comment' value='".PROFILE_208."' class='button'>";
									$cboxbut = "<button style='height:25px;vertical-align:middle;' class='button' type='submit' value='submit' title='".PROFILE_208."' name='post_comment'><img style='vertical-align:middle;' src='images/post1.png'>&nbsp;&nbsp;<b>".PROFILE_33."</b></button>";
                }


//<button onclick="location.href='http://cat-philataelia.site90.net/e107_plugins/links_page/links.php?submit'" style="height:25px;vertical-align:middle;" class="button" type="submit" value="submit" name="post_comment"><img src='images/post1.png'>&nbsp;&nbsp;<b>".PROFILE_33."</b></button>




                
						if ($break[1] == 1 && $this->var['user_id'] != USERID) {
							if ((!in_array(USERID, $friendb)) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "")) {
								$cbox .= "<tr><td>".$username." ".PROFILE_105."</td></tr></form>";
							} else if ($euser_pref['friends'] != "ON") {
								$cbox .= "<tr><td>".$username." ".PROFILE_105a."</td></tr></form>";
							} else {
//								$text .= $cbox;
//								$text .= "<br/><input type='hidden' name='id' value='".$this->var['user_id']."'>";
								$cbox .= "<input type='hidden' name='id' value='".$this->var['user_id']."'>";

								if ($euser_pref['buttontype'] == "Yes") {
									$cbox .= "<input type='image' name='post_comment' onmouseover='this.src=\"images/buttons/".e_LANGUAGE."_comment_over.gif\"' onmouseout='this.src=\"images/buttons/".e_LANGUAGE."_comment.gif\"' src='images/buttons/".e_LANGUAGE."_comment.gif' >";
								} else {
									$cbox .= "<input type='submit' name='post_comment' value='".PROFILE_208."' class='button'>";
								}

                $cbox .=$cboxbut;
							}
						} else {
//							$text .= $cbox;
//							$text .= "<br/><input type='hidden' name='id' value='".$this->var['user_id']."'>";
							$cbox .= "<input type='hidden' name='id' value='".$this->var['user_id']."'>";

							if ($euser_pref['buttontype'] == "Yes") {
								$cbox .= "<input type='image' name='post_comment' onmouseover='this.src=\"images/buttons/".e_LANGUAGE."_comment_over.gif\"' onmouseout='this.src=\"images/buttons/".e_LANGUAGE."_comment.gif\"' src='images/buttons/".e_LANGUAGE."_comment.gif' >";
							} else {
								$cbox .= "<input type='submit' name='post_comment' value='".PROFILE_208."' class='button'>";
								
							}

                $cbox .=$cboxbut;

						}
          $cbox .="</td>";

						if (!e_WYSIWYG) {
							require_once(e_HANDLER."ren_help.php");
						}
//						$cbox = "<tr><td><textarea class='e-wysiwyg tbox' id='data' name='user_comment' cols='50' rows='10' style='width:100%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>$vtomessage</textarea></td></tr><tr><td>";
							$cbox .= "<td style='text-align:right'>".
						if (!e_WYSIWYG) {
							$cbox .= display_help("helpb", "body");
						}
//						$cbox .= "</td></tr>";
						$cbox .="</td></tr><tr><td colspan=2 style='text-align:center' >";
						$cbox .= "<textarea class='e-wysiwyg tbox' id='data' name='user_comment' cols='50' rows='5' style='width:100%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>$vtomessage</textarea>";


						// Check member settings
						if ($break[1] == 1 && $this->var['user_id'] != USERID) {
							if ((!in_array(USERID, $friendb)) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "")) {
								$text .= "<tr><td>".$username." ".PROFILE_105."</td></tr></form>";
							} else if ($euser_pref['friends'] != "ON") {
								$text .= "<tr><td>".$username." ".PROFILE_105a."</td></tr></form>";
							} else {
								$text .= $cbox;
								$text .= "</td></tr><tr><td><br/><br/><input type='hidden' name='id' value='".$this->var['user_id']."'>";
								if ($euser_pref['buttontype'] == "Yes") {
									$text .= "<input type='image' name='post_comment' onmouseover='this.src=\"images/buttons/".e_LANGUAGE."_comment_over.gif\"' onmouseout='this.src=\"images/buttons/".e_LANGUAGE."_comment.gif\"' src='images/buttons/".e_LANGUAGE."_comment.gif' >";
								} else {
									$text .= "<input type='submit' name='post_comment' value='".PROFILE_208."' class='button'>";
								}
							}
						} else {
							$text .= $cbox;
							$text .= "</td></tr><tr><td><br/><br/><input type='hidden' name='id' value='".$this->var['user_id']."'>";
							if ($euser_pref['buttontype'] == "Yes") {
								$text .= "<input type='image' name='post_comment' onmouseover='this.src=\"images/buttons/".e_LANGUAGE."_comment_over.gif\"' onmouseout='this.src=\"images/buttons/".e_LANGUAGE."_comment.gif\"' src='images/buttons/".e_LANGUAGE."_comment.gif' >";
							} else {
								$text .= "<input type='submit' name='post_comment' value='".PROFILE_208."' class='button'>";
							}
						}

						$cbox .= "</td></tr>";

					} else {
						$cbox .= "<table width='100%'><tr><td><div class='forumheader'>".PROFILE_237." ($maxpcomment".PROFILE_236.").</div>";
//						$text .= "<table width='100%'><tr><td><div class='forumheader'>".PROFILE_237." ($maxpcomment".PROFILE_236.").</div>";
					}
//					$text .= "</td></tr></table></form>";
					$cbox .= "</td></tr></table></form>";
				}
//			}

						$text .= $cbox;
					$text .= "</td></tr></table>";
*/
//$text .= "</div>";
//---			}
//---    }
  }

  	function sc_euser_friends($parm='')
	{

  // #####AMIGOS#####
    if (($_GET['page'] == friends) || (!$_GET['page'])){
			// Check member settings - NO Admin & NO Friends
			if (!USERID == ADMIN || !USER) {
/*
				$sql->mySQLresult = @mysql_query("SELECT user_friends, user_settings FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
				$settings = $sql->db_Fetch();
				$break = explode("|",$settings['user_settings']);
				$friendb = explode("|", $settings['user_friends']);
*/
				if ((!USER && $break[6] == 1) || ($break[6] == 1 && $this->var['user_id'] != USERID && !isset($_GET['add']))) {
					//----------- Only friends
/*
					if (!in_array(USERID, $friendb) || !USER) {
						$text .= "<br/>".$username." ".PROFILE_252;
						$display = $tp->parseTemplate($text, TRUE, $user_sc);
						$ns->tablerender("",$display);
						require_once(FOOTERF);
						exit;
					}
*/
    ap_onlyfriends(array(PROFILE_252));			
				}
			}

//			var_dump ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "");

//--			if ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "") {
			if ($this->euser_pref['friends']) {

//---        global $euser_template;
//        var_dump ($euser_template);
        
//---                if (isset($parm['caption'])){return $this->tp->parseTemplate($euser_template['friends_caption'], TRUE, $this);}

//---$text .= "<div class='virtualpage4".(($_GET['page'] == friends)?"":" hidepiece")."'>";
// Carrego o ficheiro para no futuro ter uma hip�tese de reoordenar como eu quiser isto...
//var_dump ("Inicio");
define(UPROF, "");
			return require("includes/friends.php");
//var_dump ("Fim");
//---$text .="</div>";

/*
				if ($euser_pref['frcol'] == '') {
					$frcolumn = '6';
				} elseif ($euser_pref['frcol'] > '8') {
					$frcolumn = '8';
				} else {
					$frcolumn = $euser_pref['frcol'];
				}

				$sql->mySQLresult = @mysql_query("SELECT user_id, user_friends, user_friends_request FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
				$list = $sql->db_Fetch();
				$friend = explode("|", $list['user_friends']);
				$num = count($friend) - 2;
				if ($list['user_friends'] == '' or $list['user_friends'] == '|') {
					$text .= "<br><table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/friends.png'>&nbsp;<i>".PROFILE_30."</i></td></tr></table>";
				} else {
					$text .= "<br><table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/friends.png'>&nbsp;<i>".$num." " .PROFILE_31." </i></td></tr></table>";
					$text .= "<table width='100%'>";
					$column = 1;
					foreach ($friend as $fr) {
						if ($column==1) {
						$text .="<tr>";
						}
						if ($fr == '') {
						// DO NOTHING
						} else {
							$sql->mySQLresult = @mysql_query("SELECT user_name, user_image FROM ".MPREFIX."user WHERE user_id='".$fr."' ");
							$fname = $sql->db_Fetch();
							$user_name = $fname['user_name'];
							$frnames[] = $user_name;
							array_multisort ($frnames, SORT_ASC);
						}
					}
					foreach ($frnames as $frname) {
						$sql->mySQLresult = @mysql_query("SELECT user_id, user_name, user_image FROM ".MPREFIX."user WHERE user_name='".$frname."' ");
						$name = $sql->db_Fetch();
						$user_name = $name['user_name'];
						$fr = $name['user_id'];
						$on_name = "".$fr.".".$user_name."";
						$check = $sql-> db_Count("online","(*)","WHERE online_user_id='".$on_name."'");
						if( $check > 0 ) {
							$online = "<img src='images/online.gif' title='".PROFILE_96."' style='vertical-align: top;' />";
						} else {
							$online = "";
						}
						unset($check,$on_name);
						$text .= "<td class='forumheader3' width = '10%'><div align='center'><a href='euser.php?id=".$fr."'>";
						if($name[user_image] == "") {
							$text .= "<img src='".e_PLUGIN."euser/images/noavatar.png' border='1' width='64' alt='' />";
						}else{
							$user_image = $name[user_image];
							require_once(e_HANDLER."avatar_handler.php");
							$user_image = avatar($user_image);
							$text .= "<img src='".$user_image."' border='1' width='64' alt='' />";
						}
						$text .= "<br/></a>".$online." ".$name['user_name']."</div></td>";
						$column++;
						if ($column == $frcolumn + 1) {
							$text .= "</tr>";
							$column = 1;
						}
					}
					$text .= "</table>";
					$text .= "<br/><table width='100%' ><tr><td class='forumheader' colspan='3' ><div class='smallblacktext'><a href='".e_SELF."?".e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".PROFILE_271."</a></div></td></tr></table>";
				}
*/
			}
}

  }
  
  	function sc_euser_images($parm='')
	{

  // #####IMAGENS#####
    if (($_GET['page'] == images) || (!$_GET['page'])){
			// Check member settings - NO Admin & NO Friends
			if (!USERID == ADMIN || !USER) {
/*
				$sql->mySQLresult = @mysql_query("SELECT user_friends, user_settings FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
				$settings = $sql->db_Fetch();
				$break = explode("|",$settings['user_settings']);
				$friendb = explode("|", $settings['user_friends']);
*/
				if ((!USER && $break[8] == 1) || ($break[8] == 1 && $this->var['user_id'] != USERID && !isset($_GET['add']))) {
					//----------- Only friends
    ap_onlyfriends(array(PROFILE_250,PROFILE_250a));
/*
					if (((!in_array(USERID, $friendb)) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "")) || !USER) {
						$text .= "<br/>".$username." ".PROFILE_250;
						$display = $tp->parseTemplate($text, TRUE, $user_sc);
						$ns->tablerender("",$display);
						require_once(FOOTERF);
						exit;
					} else	if ($euser_pref['friends'] != "ON") {
						$text .= "<br/>".$username." ".PROFILE_250a;
						$display = $tp->parseTemplate($text, TRUE, $user_sc);
						$ns->tablerender("",$display);
						require_once(FOOTERF);
						exit;
					}
*/
				}
			}
//			if ($euser_pref['pics'] == "ON" || $euser_pref['pics'] == "") {
			if ($this->euser_pref['pics']) {

//--$text.="<div class='virtualpage4".(($_GET['page'] == images)?"":" hidepiece")."'>";
// Carrego o ficheiro para no futuro ter uma hip�tese de reoordenar como eu quiser isto...
			return	require("includes/images.php");
//--$text .= "</div>";

/*
				$picdir = "userimages/".$this->var['user_id']."/";
				$picthumbdir = "userimages/".$this->var['user_id']."/thumbs";

				function countpicFiles($strDirName) {
					if ($hndDir = opendir($strDirName)){
						$intCount = 0;
						while (false !== ($strFilename = readdir($hndDir))){
							if ($strFilename != "." && $strFilename != ".."){
								$intCount++;
							}
						}
						closedir($hndDir);
					} else {
						$intCount = -1;
					}
					return $intCount;
				}

				$kepekszama = countpicFiles($picdir);
				if(file_exists($picthumbdir)){
					if ($kepekszama < 3) {
						$text .= "<br><table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/images.png'><i>".PROFILE_163."</i></td></tr></table>";
					} else {
						$text .= "<br><table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/images.png'><i>".PROFILE_14a."</i></td></tr></table><br>";
					}
				}
				if(!file_exists($picthumbdir)){
					if ($kepekszama < 2) {
						$text .= "<br><table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/images.png'><i>".PROFILE_163."</i></td></tr></table>";
					} else {
						$text .= "<br><table width='100%' class='fborder'><tr><td class='forumheader' colspan='4'><img src='images/images.png'><i>".PROFILE_14a."</i></td></tr></table><br>";
					}
				}
				if (isset($_GET['album']) && isset($_GET['pic'])) {
					if ($_GET['album'] != "root") {
						$dir = "userimages/".$this->var['user_id']."/".$_GET['album']."/";
					} else {
						$dir = "userimages/".$this->var['user_id']."/";
					}
//MOD_20120418
//								$dirHandle = opendir($dir);
					if ($handle = opendir($dir)) {
						$filenames = array();
						while (false !== ($filename = readdir($handle))) {
							$file_list[] = array('name' => $filename, 'size' => filesize($dir."/".$filename), 'mtime' => filemtime($dir."/".$filename));
						}
if ($euser_pref['userpic_order'] == 'ASC' || $euser_pref['userpic_order'] == '') {
						usort($file_list, create_function('$a, $b', "return strcmp(\$a['mtime'], \$b['mtime']);"));
} else {
						usort($file_list, create_function('$b, $a', "return strcmp(\$a['mtime'], \$b['mtime']);"));
}
						closedir($handle);
					}
$np =0;
foreach($file_list as $one_file) {
 $file = $one_file['name'];
 if (!is_dir($dir.$file)){
  if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != "only_friends" && $file != "thumbs" && substr(strrchr($file, '.'), 1) != "txt" && substr(strrchr($file, '.'), 1) != "htm" ) {
   if ($np == 1) {
     $next_pic = $file;
     break;
   }
   if ($file == $_GET['pic']) $np = 1;
   if (!$np ==1) $prev_pic = $file;
  }
 }
}



					$aof = 0;
					if (file_exists($dir."/only_friends")) $aof = 1;
					if ((in_array(USERID, $friendb) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "") && USER) || !file_exists("".$dir."/only_friends") || $this->var['user_id'] == USERID || (ADMIN && getperms("4"))) {
						$split = explode(".", $_GET['pic']);
						$counter=0;
						foreach($split as $string) {
							$counter++;
							if ($string == '') {
								$split_id = $split[$counter];
								$this->var['user_id'] = $split_id;
								$lnk=true;
								break;
							}
						}
						$kiterjesztes = $split[$counter - 1];
						$picname = str_replace(".".$split[$counter - 1]."", "", $_GET['pic']);
						$myFile = $dir.$picname.".txt";
*/
/*
						if ($_GET['album'] != "root") {
							$text .= "<a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."'><< ".PROFILE_34."</a><br/><a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."&album=".$_GET['album']."'><< ".PROFILE_34a." \"".str_replace("_", " ", $_GET['album'])."\"</a><br/><br/>";
						} else {
							$text .= "<a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."'><< ".PROFILE_34."</a><br/><br/>";
						}
*/
/*
						$kepmeret = getimagesize("".$dir.$_GET['pic']."");
						$kep_sz = $kepmeret[0]+30;
						$kep_m = $kepmeret[1]+30;
						if ($euser_pref['picviewsize'] == '') {
							$picviewsize = '600';
						} else {
							$picviewsize = $euser_pref['picviewsize'];
						}

if ($prev_pic) {
$prev .= "<a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."&album=".$_GET['album']."&pic=".$prev_pic."'><img style='border: 0px solid ; width: 32px; height: 32px;' alt='prev' title='".PROFILE_426."' src='images/prev.png'></a>";
}
if ($next_pic) {
$next .= "<a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."&album=".$_GET['album']."&pic=".$next_pic."'><img style='border: 0px solid ; width: 32px; height: 32px;' alt='next' title='".PROFILE_427."' src='images/next.png'></a>";
}

if ($_GET['album'] != "root") {
$up_pic .= "<a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."&album=".$_GET['album']."'><img style='border: 0px solid ; width: 32px; height: 32px;' alt='next' title='".PROFILE_34a." ".$_GET['album']."' src='images/up.png'></a>";
} else {
$up_pic .= "<a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."'><img style='border: 0px solid ; width: 32px; height: 32px;' alt='next' title='".PROFILE_34."' src='images/up.png'></a>";
}

$text .= '<table style="text-align: left; width: 100%; margin-left: auto; margin-right: auto;" border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr>
<td colspan="3" rowspan="1" style="vertical-align: top;"><br>
';




						if ($euser_pref['lightview'] == 'Yes' && $euser_pref['cl_widget_ver'] != ''){
							if ($kep_sz<$picviewsize+31) {
								$text .= "<center><img src='".$dir.$_GET['pic']."'><br>".str_replace("_", " ", $picname)."</center><br/><br/>";
							} else {
								$text .= "<center><a href='".$dir.$_GET['pic']."' class=\"lightview\" title='".$username.": ::".str_replace("_", " ", $picname)."'><img src='".$dir.$_GET['pic']."' width='$picviewsize'></a><br/>".str_replace("_", " ", $picname)."a</center>";
							}
						} else if ($euser_pref['lightwindowbox'] == 'Yes' && (file_exists(e_PLUGIN."lightwindow/js/lightwindow.js"))){
							if ($kep_sz<$picviewsize+31) {
								$text .= "<center><img src='".$dir.$_GET['pic']."'><br>".str_replace("_", " ", $picname)."</center><br/><br/>";
							} else {
								$text .= "<center><a href='".$dir.$_GET['pic']."' class=\"lightwindow\" title='".$_GET['pic']."'><img src='".$dir.$_GET['pic']."' width='$picviewsize'></a><br/>".str_replace("_", " ", $picname)."</center>";
							}
						} else if ($euser_pref['lightbox'] == 'Yes' && $euser_pref['lightb_enabled'] == '1'){
							if ($kep_sz<$picviewsize+31) {
								$text .= "<center><img src='".$dir.$_GET['pic']."'><br>".str_replace("_", " ", $picname)."</center><br/><br/>";
							} else {
								$text .= "<center><a href='".$dir.$_GET['pic']."' rel=\"lightbox[roadtrip]\" title='".$_GET['pic']."'><img src='".$dir.$_GET['pic']."' width='$picviewsize'></a><br/>".str_replace("_", " ", $picname)."</center>";
							}
						} else if ($euser_pref['clearbox'] == 'Yes'){
							echo '
								<script language="JavaScript" src="clearbox/js/clearbox.js" type="text/javascript" charset="iso-8859-2"></script>
								<link rel="stylesheet" href="clearbox/css/clearbox.css" rel="stylesheet" type="text/css"/>
							';





if ($kep_sz<$picviewsize+31) {
//	$text .= "<center><img src='".$dir.$_GET['pic']."'><br>".str_replace("_", " ", $picname)."</center><br/><br/>";
	$text .= "<center><a href='".$dir.$_GET['pic']."' rel=\"clearbox[gallery=gallery]\" title='".$_GET['pic']."'><img src='".$dir.$_GET['pic']."'></a><br/>".str_replace("_", " ", $picname)."</center>";

} else {
	$text .= "<center><a href='".$dir.$_GET['pic']."' rel=\"clearbox[gallery=gallery]\" title='".$_GET['pic']."'><img src='".$dir.$_GET['pic']."' width='$picviewsize'></a><br/>".str_replace("_", " ", $picname)."</center>";
}
foreach($file_list as $one_file) {
 $file = $one_file['name'];
 if (!is_dir($dir.$file)){
  if ($file != "." && $file != $_GET['pic'] && $file != ".." && $file != "Thumbs.db" && $file != "only_friends" && $file != "thumbs" && substr(strrchr($file, '.'), 1) != "txt" && substr(strrchr($file, '.'), 1) != "htm" ) {
//	$text .= '<a href="'.$dir.$file.'" rel="clearbox[gallery=gallery]"><img src="'.$dir.$file.'"></a>';
 if (is_file($dir."thumbs/".$file)) {
	$text .= '<a href="'.$dir.$file.'" rel="clearbox[gallery=gallery]" tnhref="'.$dir."thumbs/".$file.'"></a>';
} else {
	$text .= '<a href="'.$dir.$file.'" rel="clearbox[gallery=gallery]" tnhref="'.$dir.$file.'"></a>';
}
  }
 }
}


 

*/
/*
							if ($kep_sz<$picviewsize+31) {
								$text .= "<center><img src='".$dir.$_GET['pic']."'><br>".str_replace("_", " ", $picname)."</center><br/><br/>";
							} else {
								$text .= "<center><a href='".$dir.$_GET['pic']."' rel=\"clearbox\" title='".$_GET['pic']."'><img src='".$dir.$_GET['pic']."' width='$picviewsize'></a><br/>".str_replace("_", " ", $picname)."</center>";
							}
*/
/*
						} else {
							if ($kep_sz<$picviewsize+31) {
								$text .= "<center><img src='".$dir.$_GET['pic']."'><br>".str_replace("_", " ", $picname)."</center><br/><br/>";
							} else {
								$text .= "<center><a href='#' title='".PROFILE_167."' onClick=\"window.open('".$dir.$_GET['pic']."','','menubar=no,titlebar=no,resizable=no,scrollbars=yes,width=$kep_sz,height=$kep_m')\"><img src='".$dir.$_GET['pic']."' width='$picviewsize'></a><br/>".str_replace("_", " ", $picname)."</center>";
							}
						}

$text .= '</td>
</tr>
<tr>
<td style="vertical-align: top; text-align: center; width: 10%;">'.$prev.'<br>
</td>
<td style="vertical-align: top; text-align: center; width: 10%;"><br/><br/>'.$up_pic.'
</td>
<td style="vertical-align: top; text-align: center; width: 10%;">'.$next.'<br>
</td>
</tr>
</tbody>
</table>';







						$sql->mySQLresult = @mysql_query("SELECT com_id, com_message, com_date, com_by FROM ".MPREFIX."euser_com WHERE com_to='".$this->var['user_id']."' AND com_type='pics' AND com_extra='".mysql_real_escape_string($_GET['album'])."/".mysql_real_escape_string($_GET['pic'])."' ORDER BY com_date DESC");
						$piccomm = $sql->db_Rows();
						// K�p hozz�sz�l�sok list�z�sa
						$sql->mySQLresult = @mysql_query("SELECT com_id FROM ".MPREFIX."euser_com WHERE com_to='".$this->var['user_id']."' AND com_extra='".mysql_real_escape_string($_GET['album'])."/".mysql_real_escape_string($_GET['pic'])."'");
						$picnumrows = $sql->db_Rows();
						// MULTIPAGES INFO
						if ($euser_pref['apcomments'] != '') {
							$rowsPerPage = $euser_pref['apcomments'];
						} else {
							$rowsPerPage = 5;
						}
						$pageNum = 1;
						if(isset($_GET['pgnum'])) {
							$pageNum = intval($_GET['pgnum']);
						}
						$offset = ($pageNum - 1) * $rowsPerPage;
						if(isset($_GET['comment_order'])) {
							if($_GET['comment_order'] == "ASC" || $_GET['comment_order'] == "DESC") {
								$comment_order = $_GET['comment_order'];
							}
						}
						if (!$comment_order == ASC || !$comment_order == DESC) {
							$comment_order = "DESC";
						}
						$sql->mySQLresult = @mysql_query("SELECT com_id, com_message, com_date, com_by FROM ".MPREFIX."euser_com WHERE com_to='".$this->var['user_id']."' AND com_type='pics' AND com_extra='".mysql_real_escape_string($_GET['album'])."/".mysql_real_escape_string($_GET['pic'])."' ORDER BY com_date $comment_order LIMIT $offset,$rowsPerPage");
						$piccomm = $sql->db_Rows();
						$maxPage = ceil($picnumrows/$rowsPerPage);
						$self = $_SERVER['PHP_SELF'];
						$nav  = '';
						for($page = 1; $page <= $maxPage; $page++) {
							if ($page == $pageNum) {
								$nav .= ""; // no need to create a link to current page
							} else {
								$nav .= " <a href=\"$self?id=".$this->var['user_id']."&page=images&album=".$_GET['album']."&pic=".$_GET['pic']."&comment_order=".$comment_order."&pgnum=".$page."\">$page</a> ";
							}
						}
						if ($pageNum > 1) {
							$page  = $pageNum - 1;
							$prev  = " <a href=\"$self?id=".$this->var['user_id']."&page=images&album=".$_GET['album']."&pic=".$_GET['pic']."&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_204."</a> ";
							$first = " <a href=\"$self?id=".$this->var['user_id']."&page=images&album=".$_GET['album']."&pic=".$_GET['pic']."&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_205."</a> ";
						} else {
							$prev  = ''; // we're on page one, don't print previous link
							$first = '&nbsp;'; // nor the first page link
						}
						if ($pageNum < $maxPage) {
							$page = $pageNum + 1;
							$next = " <a href=\"$self?id=".$this->var['user_id']."&page=images&album=".$_GET['album']."&pic=".$_GET['pic']."&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_202."</a> ";
							$last = " <a href=\"$self?id=".$this->var['user_id']."&page=images&album=".$_GET['album']."&pic=".$_GET['pic']."&comment_order=".$comment_order."&pgnum=".$page."\">".PROFILE_203."</a> ";
						} else {
							$next = ''; // we're on the last page, don't print next link
							$last = '&nbsp;'; // nor the last page link
						}
						// END OF MULTIPAGES
	
						if ($euser_pref['maxpiccomment'] != '') {
							$maxpiccomment = $euser_pref['maxpiccomment'];
						} else {
							$maxpiccomment = 50;
						}
						if ($piccomm == 0) {
							$text .= "<br/><br/><i>".PROFILE_36."</i>";
						} else {
						$text .= "<br><table width='100%' class='fborder'>
								<tr>
									<td style='width:20%; text-align:left' class='forumheader' colspan='2'><img src='images/comments.png'><i>".PROFILE_36a." (".$picnumrows."):</i></td>";
									if ($comment_order == DESC) {
										$text .= "<td style='width:80%; text-align:right' class='forumheader' colspan='2'>".PROFILE_256."&nbsp;&nbsp;<a href=\"$self?id=".$this->var['user_id']."&page=images&album=".$_GET['album']."&pic=".$_GET['pic']."&comment_order=ASC\"><img src='images/order_down.png' title='".PROFILE_310."'></a></td>";
									} else {
										$text .= "<td style='width:80%; text-align:right' class='forumheader' colspan='2'>".PROFILE_256."&nbsp;&nbsp;<a href=\"$self?id=".$this->var['user_id']."&page=images&album=".$_GET['album']."&pic=".$_GET['pic']."&comment_order=DESC\"><img src='images/order_up.png' title='".PROFILE_309."'></a></td>";
									}
									$text .= "</tr>
							</table>";
							$text .= "<br/>";
							// K�p hozz�sz�l�sok indul
							for ($i = 0; $i < $piccomm; $i++) {
								$com = $sql->db_Fetch();
								$from = mysql_query("SELECT * FROM ".MPREFIX."user WHERE user_id=".$com['com_by']." ");
								$from = mysql_fetch_assoc($from);
								$date = date("Y-m-j. H:i", $com['com_date']);
								$comid = $com['com_id'];
								$user_name = $from['user_name'];
								$on_name = "".$com['com_by'].".".$user_name."";
								$checkonline = mysql_query("SELECT * FROM ".MPREFIX."online WHERE online_user_id='".$on_name."'");
								$checkonline = mysql_num_rows($checkonline);
								//e107_0.8 compatible
								if(file_exists(e_HANDLER."level_handler.php")){
									require_once(e_HANDLER."level_handler.php");
									$ldata = get_level($from['user_id'], $from['user_forums'], $from['user_comments'], $from['user_chats'], $from['user_visits'], $from['user_join'], $from['user_admin'], $from['user_perms'], $euser_pref);
								} else {
									//
								}
								if (strstr($ldata[0], "IMAGE_rank_main_admin_image")) {
									$from_level = "".PROFILE_276."<br/>$ldata[1]";
								}
								else if(strstr($ldata[0], "IMAGE")) {
									$from_level = "".PROFILE_277."<br/>$ldata[1]<br/>";
								} else {
									$from_level = $ldata[1];
								}
								$gen = new convert;
								$from_join = $gen->convert_date($from['user_join'], "forum");
								$from_signature = $from['user_signature'] ? $tp->toHTML($from['user_signature'], TRUE) : "";
								$fromext = mysql_query("SELECT * FROM ".MPREFIX."user_extended WHERE user_extended_id=".$com['com_by']." ");
								$fromext = mysql_fetch_assoc($fromext);
								if( $checkonline > 0 ) {
									$online = "<img src='images/online.gif' title='".PROFILE_96."' style='vertical-align: middle;' />";
								} else {
									$online = "";
								}
								unset($checkonline,$on_name);
								$text .= "<br><table width='100%' class='fborder'>
								<tr>
									<td style='width:20%; text-align:left' class='fcaption'>".PROFILE_268."".$from['user_name']."</td>
									<td style='width:60%; text-align:left' class='fcaption'>".PROFILE_269."</td>
									<td style='width:20%; text-align:right' class='fcaption'>id: #".$comid."</td>
								</tr>
									<td class='forumheader'>&nbsp;".$online."&nbsp;&nbsp;<a href='euser.php?id=".$com['com_by']."'><b>".$from['user_name']."</b></a></td>
									<td class='forumheader' style='vertical-align: middle;' /><img src='images/post.png'>&nbsp;".$date."</td>
									<td class='forumheader' style='vertical-align: middle; text-align:right' /><a href='".e_PLUGIN."pm/pm.php?send.".$com['com_by']."'><img src='".e_PLUGIN."/pm/images/pm.png' title='".PROFILE_138."'></a></td></tr>
								<tr>
									<td class='forumheader3' style='vertical-align: top; width='20%;' />";
								// GET COMMENTERS AVATAR
								if($from[user_image] == "") {
									$av = "".e_PLUGIN."euser/images/noavatar.png";
									$text .= "".$from['user_customtitle']."<br/><br/><a href='euser.php?id=".$com['com_by']."'><img src='".$av."' border='1' ".$avwidth." ".$avheight."  alt='' /></a>";
								} else {
									$av = $from[user_image];
									require_once(e_HANDLER."avatar_handler.php");
									$av = avatar($av);
									$text .= "".$from['user_customtitle']."<br/><br/><a href='euser.php?id=".$com['com_by']."'><img src='".$av."' border='1' ".$avwidth." ".$avheight."  alt='' /></a>";
								}
								if ($euser_pref['user_warn_support'] == "Yes" AND $fromext['user_warn'] !='null' AND $fromext['user_warn'] !='') {
									$text .= "<br/><img src=\"".THEME_ABS."images/warn/".$fromext['user_warn'].".png\">";
								}
								$text .= "<br/>$from_level<br/><div class='smallblacktext'>".PROFILE_270."$from_join<br/>".PROFILE_272.$fromext['user_location']."</div></td>";
								$message = $tp -> toHTML($com['com_message'], true, 'parse_sc, constants');
								$text .= "<td class='forumheader3' colspan='2' style='vertical-align: top;'>".$message."<hr width='80%' align='left' size='1' noshade ='noshade'>$from_signature</td></tr>";
								$text .= "<tr><td class='forumheader'><div class='smallblacktext'><a href='".e_SELF."?".e_QUERY."#header' onclick=\"window.scrollTo(0,0);\">".PROFILE_271."</a></div></td>";
								if (USER) {
									if ($picnumrows < $maxpiccomment) {
										$text .= "<td colspan='2'  class='forumheader' style='vertical-align: middle; text-align:right' /><div class='smallblacktext'>| <a href='".e_SELF."?".e_QUERY."#newprofilecomment'>".PROFILE_414."</a> | <a href='".e_SELF."?".e_QUERY."&vtoname=".$from['user_name']."&vtodate=".$date."&vtoid=".$comid."#newprofilecomment'>".PROFILE_415."</a> |</td></div></tr></table><br/><br/>";
									} else {
										$text .= "<td colspan='2'  class='forumheader'></td></tr></table><br/><br/>";
									}
								} else {
									$text .= "<td colspan='2'  class='forumheader'></td></tr></table><br/><br/>";
								}
							}
						}
						$text .= "<br/><center>".$prev.$nav.$next."</center><br/><br/>";
						// K�p hozz�sz�l�sok list�z�s�nak v�ge
						if (USER) {
							if ($picnumrows < $maxpiccomment) {
								if (isset($_GET['vtoname']) && isset($_GET['vtodate']) && isset($_GET['vtoid'])) {
									$vtoname = $_GET['vtoname'];
									$vtodate = $_GET['vtodate'];
									$vtoid = $_GET['vtoid'];
									$vtomessage = "[blockquote]".PROFILE_279."".$vtoname." #".$vtoid."".PROFILE_280."[/blockquote]";
								}
								$text .= "<a name='newprofilecomment'></a>";
								$text .= "<form method='post' action='formhandler.php'><table width='100%'><tr><td class='forumheader' style='vertical-align: middle;' /><img src='images/post1.png'>&nbsp;&nbsp;<b>".PROFILE_33."</b></td>";
								if (!e_WYSIWYG) {
									require_once(e_HANDLER."ren_help.php");
								}
								$cpbox = "<tr><td><input type='hidden' name='album' value='".$_GET['album']."'><textarea class='e-wysiwyg tbox' id='data' name='user_picture_comment' cols='50' rows='10' style='width:100%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>$vtomessage</textarea></td></tr><tr><td>";
								if (!e_WYSIWYG) {
									$cpbox .= display_help("helpb", "body");
								}
								$cpbox .= "</td></tr>";
								// Check member settings
								if ($break[2] == 1 && $this->var['user_id'] != USERID) {
									if ((!in_array(USERID, $friendb)) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "")) {
										$text .= "<tr><td>".$username." ".PROFILE_107b."</td></tr></table></form>";
									} else if ($euser_pref['friends'] != "ON") {
										$text .= "<tr><td>".$username." ".PROFILE_107c."</td></tr></table></form>";
									} else {
										$text .= $cpbox;
										///comment k�ld�se
										$text .= "</td></tr><tr><td><br/><br/><input type='hidden' name='id' value='".$this->var['user_id']."'><input type='hidden' name='pic' value='".$_GET['pic']."'><input type='hidden' name='picfull' value='".$_GET['album']."/".$_GET['pic']."'><input type='hidden' name='picname' value='".$picname[0]."'><input type='hidden' name='txtfile' value='".$data."'>";
										if ($euser_pref['buttontype'] == "Yes") {
											$text .= "<input type='image' name='post_comment' onmouseover='this.src=\"images/buttons/".e_LANGUAGE."_comment_over.gif\"' onmouseout='this.src=\"images/buttons/".e_LANGUAGE."_comment.gif\"' src='images/buttons/".e_LANGUAGE."_comment.gif' >";
										} else {
										$text .= "<input type='submit' name='post_comment' value='".PROFILE_208."' class='button'>";
										}
									}
								} else {
									$text .= $cpbox;
									///comment k�ld�se
									$text .= "</td></tr><tr><td><br/><br/><input type='hidden' name='id' value='".$this->var['user_id']."'><input type='hidden' name='pic' value='".$_GET['pic']."'><input type='hidden' name='picfull' value='".$_GET['album']."/".$_GET['pic']."'><input type='hidden' name='picname' value='".$picname[0]."'><input type='hidden' name='txtfile' value='".$data."'>";
									if ($euser_pref['buttontype'] == "Yes") {
										$text .= "<input type='image' name='post_comment' onmouseover='this.src=\"images/buttons/".e_LANGUAGE."_comment_over.gif\"' onmouseout='this.src=\"images/buttons/".e_LANGUAGE."_comment.gif\"' src='images/buttons/".e_LANGUAGE."_comment.gif' >";
									} else {
										$text .= "<input type='submit' name='post_comment' value='".PROFILE_208."' class='button'>";
									}
								}
							} else {
								$text .= "<table width='100%'><tr><td><div class='forumheader'>".PROFILE_238." ($maxpiccomment".PROFILE_236.").</div>";
							}
								$text .= "</td></tr></table></form>";
						}
					}
				} elseif (isset($_GET['album']) && !isset($_GET['pic'])) {
					$text .= "<a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."'><< ".PROFILE_34."</a><br/>";
					$dir = "userimages/".$this->var['user_id']."/".$_GET['album']."/";
					if ((in_array(USERID, $friendb) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "") && USER) || !file_exists("".$dir."/only_friends") || $this->var['user_id'] == USERID || (ADMIN && getperms("4"))) {
						if (file_exists($dir)) {
							// IF glob has been disabled by your host then uncomment the above function and comment out the next 2 lines.
							$empty = (count(glob("$dir/*")) === 0) ? 'TRUE' : 'FALSE';
							if ($empty == "TRUE") {
*/
								// Comment out until here - when uncommenting above, just remove the /* and */ from function to if.
/*
								$text .= "<br/><i>".PROFILE_123."</i>";
							} else {
								$column = 1;
								if ($euser_pref['piccol']) {
									$profile_piccol = $euser_pref['piccol'];
								} else {
									$profile_piccol = 3;
								}
								$profile_piccol_p = intval(100/$profile_piccol);
								$text .= "<br/><table width='100%'>";
//MOD_20120418
//								$dirHandle = opendir($dir);
					if ($handle = opendir($dir)) {
						$filenames = array();
						while (false !== ($filename = readdir($handle))) {
							$file_list[] = array('name' => $filename, 'size' => filesize($dir."/".$filename), 'mtime' => filemtime($dir."/".$filename));
						}
if ($euser_pref['userpic_order'] == 'ASC' || $euser_pref['userpic_order'] == '') {
						usort($file_list, create_function('$a, $b', "return strcmp(\$a['mtime'], \$b['mtime']);"));
} else {
						usort($file_list, create_function('$b, $a', "return strcmp(\$a['mtime'], \$b['mtime']);"));
}
						closedir($handle);
//								while ($file = readdir($dirHandle)) {

foreach($file_list as $one_file) {
$file = $one_file['name'];
		// Get the file size.
		$fs = $one_file['size'];
		
if (e_LANGUAGE == "English") {
		$ft = date ('F j, Y  H:i', $one_file['mtime']);
} else {
		$ft = date("Y. m. j. H:i", $one_file['mtime']);
}

									$pos = strrpos($file, '.');
									$str = substr($file, $pos, strlen($file));
									$filetypes = ".jpg|.gif|.png|.jpeg|.JPG|.GIF|.PNG|.JPEG";
									$filetypes = explode("|", $filetypes);
									if(!is_dir($file) && in_array($str, $filetypes)) {
										$split = explode(".", $file);
										$counter=0;
										foreach($split as $string) {
											$counter++;
											if ($string == '') {
												$split_id = $split[$counter];
												$this->var['user_id'] = $split_id;
												$lnk=true;
												break;
											}
										}
										$kiterjesztes = $split[$counter - 1];
										$name = str_replace(".".$split[$counter - 1]."", "", $file);
										$newname = wordwrap($name, 17, "<br />\n");
										if ($column==1) {
											$text .="<tr>";
										}
										//Album pictures:
										if (file_exists($dir."/thumbs/".$file)) {
											$text .= "<td width='".$profile_piccol_p."%'><center><a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."&album=".$_GET['album']."&pic=".$file."'><img src='".$dir."thumbs/".$file."'></a><br/>".str_replace("_", " ", $newname)."<br/>".$ft."<br/>(".$fs."kB)";
											$query = mysql_query("SELECT com_id FROM ".MPREFIX."euser_com WHERE com_type='pics' AND com_extra='".mysql_real_escape_string($_GET['album'])."/".mysql_real_escape_string($file)."' ");
											$pic_all = mysql_num_rows($query);
											if ($pic_all > 0) {
												$text .= "<br/>".$pic_all." ".($pic_all == 1 ? PROFILE_315 : PROFILE_315)."</center></td>";
											} else {
												$text .= "</center></td>";
											}
										} else {
											$text .= "<td width='".$profile_piccol_p."%'><center><a href='euser.php?id=".$this->var['user_id']."&page=".$_GET['page']."&album=".$_GET['album']."&pic=".$file."'><img src='".$dir.$file."' width='100'></a><br/>".str_replace("_", " ", $newname)."<br/>".$ft."<br/>(".$fs."kB)";
											$query = mysql_query("SELECT com_id FROM ".MPREFIX."euser_com WHERE com_type='pics' AND com_extra='".mysql_real_escape_string($_GET['album'])."/".mysql_real_escape_string($file)."' ");
											$pic_all = mysql_num_rows($query);
											if ($pic_all > 0) {
												$text .= "<br/>".$pic_all." ".($pic_all == 1 ? PROFILE_315 : PROFILE_315)."</center></td>";
											} else {
												$text .= "</center></td>";
											}
										}
										$column++;
										if ($column == $profile_piccol + 1) {
											$text .= "</tr><tr><td><br/></td></tr>";
											$column = 1;
										}
									}
								}
//								closedir($dirHandle);
}
								$text .= "</table>";
							$text .= "<br/><table width='100%' ><tr><td class='forumheader' colspan='3' ><div class='smallblacktext'><a href='".e_SELF."?".e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".PROFILE_271."</a></div></td></tr></table>";
							}
						} else {
							$text .= "<i>".PROFILE_123."</i>";
						}
					}

				} else {
					$dir = "userimages/".$this->var['user_id']."/";
//MOD_20120418
					if ($handle = opendir($dir)) {
						$filenames = array();
						while (false !== ($filename = readdir($handle))) {
							$file_list[] = array('name' => $filename, 'size' => filesize($dir."/".$filename), 'mtime' => filemtime($dir."/".$filename));
						}
if ($euser_pref['userpic_order'] == 'ASC' || $euser_pref['userpic_order'] == '') {
						usort($file_list, create_function('$a, $b', "return strcmp(\$a['mtime'], \$b['mtime']);"));
} else {
						usort($file_list, create_function('$b, $a', "return strcmp(\$a['mtime'], \$b['mtime']);"));
}
						closedir($handle);

//					}

					$text .= "<table width='100%'><tr>"; // <br/><br/>
//					if ($handle = opendir($dir)) {
						$col = 0;
						$piccol = 0;
						if ($euser_pref['piccol']) {
							$profile_piccol = $euser_pref['piccol'];
						} else {
							$profile_piccol = 3;
						}
						$profile_piccol_p = intval(100/$profile_piccol);
//						while (false !== ($file = readdir($handle))) {

foreach($file_list as $one_file) {
$file = $one_file['name'];
		// Get the file size.
		$fs = $one_file['size'];
		
		// Get the file's modification date.
if (e_LANGUAGE == "English") {
		$ft = date ('F j, Y  H:i', $one_file['mtime']);
} else {
		$ft = date("Y. m. j. H:i", $one_file['mtime']);
}

							if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != "thumbs" && substr(strrchr($file, '.'), 1) != "txt" && substr(strrchr($file, '.'), 1) != "htm" ) {
								if (substr(strrchr($file, '.'), 1) != "") {
									$split = explode(".", $file);
									$counter=0;
									foreach($split as $string) {
										$counter++;
										if ($string == '') {
											$split_id = $split[$counter];
											$this->var['user_id'] = $split_id;
											$lnk=true;
											break;
										}
									}
									$kiterjesztes = $split[$counter - 1];
									$name = str_replace(".".$split[$counter - 1]."", "", $file);
									$newname = wordwrap($name, 17, "<br />\n");
									//Pictures:
									if (file_exists($dir."thumbs/".$file)) {
										$pic .= "<td width='".$profile_piccol_p."%'><center><a href='euser.php?id=".$this->var['user_id']."&page=images&album=root&pic=".$file."'><img src='".$dir."thumbs/".$file."'></a><br/>".str_replace("_", " ", $newname)."<br/>".$ft."<br/>(".$fs."kB)";
										$query = mysql_query("SELECT com_id FROM ".MPREFIX."euser_com WHERE com_type='pics' AND com_extra='root/".mysql_real_escape_string($file)."' ");
										$pic_all = mysql_num_rows($query);
										if ($pic_all > 0) {
											$pic .= "<br/>".$pic_all." ".($pic_all == 1 ? PROFILE_315 : PROFILE_315)."</center></td>";
										} else {
											$pic .= "</center></td>";
										}
									} else {
										$pic .= "<td width='".$profile_piccol_p."%'><center><a href='euser.php?id=".$this->var['user_id']."&page=images&album=root&pic=".$file."'><img src='".$dir.$file."' width='100'></a><br/>".str_replace("_", " ", $newname)."<br/>".$ft."<br/>(".$fs."kB)";
										$query = mysql_query("SELECT com_id FROM ".MPREFIX."euser_com WHERE com_type='pics' AND com_extra='root/".mysql_real_escape_string($file)."' ");
										$pic_all = mysql_num_rows($query);
										if ($pic_all > 0) {
											$pic .= "<br/>".$pic_all." ".($pic_all == 1 ? PROFILE_315 : PROFILE_315)."</center></td>";
										} else {
											$pic .= "</center></td>";
										}
									}
									$piccol++;
									if ($piccol == $profile_piccol) {
										$pic .= "</tr><tr><td><br/></td></tr><tr>";
										$piccol = 0;
									}
								} else {
									$count = 0;
									$firstimage="";
									if ($subhandle = opendir($dir.$file)) {
										$aof = 0;
										while (false !== ($subfile = readdir($subhandle))) {
											if ($subfile=="only_friends") $aof = 1;
											if ($subfile != "only_friends" && $subfile != "." && $subfile != ".." && $subfile != "Thumbs.db" && $subfile != "thumbs"  && $subfile != "index.htm" ) {
												if ($firstimage == "") {
													$firstimage = $subfile;
												}
												$count = $count + 1;
											}
										}
									}
									if (file_exists($dir.$file."/thumbs/".$firstimage)) {
										$imageurl = "src='userimages/".$this->var['user_id']."/".$file."/thumbs/".$firstimage."' ";
									} else {
										$imageurl = "src='userimages/".$this->var['user_id']."/".$file."/".$firstimage."' width='100' ";
									}
									//Albums:
									if ((in_array(USERID, $friendb) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "") && USER) || $aof != 1 || $this->var['user_id'] == USERID || (ADMIN && getperms("4"))) {
										if ($count == 0) {
											$text .="<td width='".$profile_piccol_p."%'><center><a href='euser.php?id=".$this->var['user_id']."&page=images&album=".$file."'  style=\"text-decoration: none;\"><img src='images/folder.png' width='64' style='padding:5px;border-style:outset;border-width:1px'><br/>".str_replace("_", " ", $file)."</a><br/><br/>".$count." ".($count == 1 ? PROFILE_134 : PROFILE_135)."<br/><br/></center></td>";
										} else {
											$text .="<td width='".$profile_piccol_p."%'><center><a href='euser.php?id=".$this->var['user_id']."&page=images&album=".$file."'  style=\"text-decoration: none;\"><img ".$imageurl." style='padding:5px;border-style:outset;border-width:3px'><br/>".str_replace("_", " ", $file)."</a><br/><br/>".$count." ".($count == 1 ? PROFILE_134 : PROFILE_135)."<br/><br/></center></td>";
										}
									}



									$col++;
									if ($col == $profile_piccol) {
										$text .= "</tr><tr><td><br/></td></tr><tr>";
										$col = 0;
									}
								}
							}
						}
//						closedir($handle);
					}
					$text .= "</tr><tr>".$pic."</tr></table>";

					if ($kepekszama > 2) {
						$text .= "<br/><table width='100%' ><tr><td class='forumheader' colspan='3' ><div class='smallblacktext'><a href='".e_SELF."?".e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".PROFILE_271."</a></div></td></tr></table>";
					}
				}
*/
			}
		}


  }
  
  	function sc_euser_videos($parm='')
	{
// #####VIDEOS#####
    if (($_GET['page'] == videos) || (!$_GET['page'])){
			// Check member settings - NO Admin & NO Friends
			if (!USERID == ADMIN || !USER) {
/*
				$sql->mySQLresult = @mysql_query("SELECT user_friends, user_settings FROM ".MPREFIX."euser WHERE user_id='".$this->var['user_id']."' ");
				$settings = $sql->db_Fetch();
				$break = explode("|",$settings['user_settings']);
				$friendb = explode("|", $settings['user_friends']);
*/
				if ((!USER && $break[7] == 1) || ($break[7] == 1 && $this->var['user_id'] != USERID && !isset($_GET['add']))) {
					//----------- Only friends
    ap_onlyfriends(array(PROFILE_251,PROFILE_251a));			

/*
					if (((!in_array(USERID, $friendb)) && ($euser_pref['friends'] == "ON" || $euser_pref['friends'] == "")) || !USER) {
						$text .= "<br/>".$username." ".PROFILE_251;
						$display = $tp->parseTemplate($text, TRUE, $user_sc);
						$ns->tablerender("",$display);
						require_once(FOOTERF);
						exit;
					} else if ($euser_pref['friends'] != "ON") {
						$text .= "<br/>".$username." ".PROFILE_251a;
						$display = $tp->parseTemplate($text, TRUE, $user_sc);
						$ns->tablerender("",$display);
						require_once(FOOTERF);
						exit;
					}
*/
				}
			}
//--			if ($euser_pref['videos'] == "ON" || $euser_pref['videos'] == "") {
			if ($this->euser_pref['videos']) {

//--        global $euser_template;
//        var_dump ($euser_template);
        
//--                if (isset($parm['caption'])){return $this->tp->parseTemplate($euser_template['videos_caption'], TRUE, $this);}
//--$text .="<div class='virtualpage4".(($_GET['page'] == videos)?"":" hidepiece")."'>";
      return	require("includes/videos.php");
//--$text .="</div>";
			}
}



  }
  
  
}






{

//var_dump($parm);

	global $loop_uid;
	
		
	$tp 		= e107::getParser();
	$width 		= $tp->thumbWidth;
	$height 	= ($tp->thumbHeight !== 0) ? $tp->thumbHeight : "";
	
	if(intval($loop_uid) > 0 && trim($parm) == "")
	{
		$parm = $loop_uid;
	}
	
	if(is_numeric($parm))
	{
		if($parm == USERID)
		{
			$image = USERIMAGE;
		}
		else
		{
			$row = e107::user($parm);
			$image=$row['user_image'];
		}
	}
	elseif(!empty($parm))
	{
		$image=$parm;
	}
	elseif(USERIMAGE)
	{
		$image = USERIMAGE;
	}
	else
	{
		$image = "";	
	}
	
	$genericImg = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","w=".$width."&h=".$height,true);	
	
	if (vartrue($image)) 
	{
		
		if(strpos($image,"://")!==false) // Remove Image
		{
			$img = $image;	
			
			//$height 	= e107::getPref("im_height",100); // these prefs are too limiting for local images.  
			//$width 		= e107::getPref("im_width",100);
		}
		elseif(substr($image,0,8) == "-upload-")
		{
			
			$image = substr($image,8); // strip the -upload- from the beginning. 
			if(file_exists(e_AVATAR_UPLOAD.$image)) // Local Default Image
			{
				$img =	$tp->thumbUrl(e_AVATAR_UPLOAD.$image,"w=".$width."&h=".$height);	
			}	
			else 
			{
				
				$img = $genericImg;
			}	
		}
		elseif(file_exists(e_AVATAR_DEFAULT.$image))  // User-Uplaoded Image
		{
			$img =	$tp->thumbUrl(e_AVATAR_DEFAULT.$image,"w=".$width."&h=".$height);		
		}
		else // Image Missing. 
		{
			
			$img = $genericImg;
		}
	}
	else // No image provided - so send generic. 
	{
		$img = $genericImg;
	}
	
	$title = (ADMIN) ? $image : "";
	
	$text = "<img class='img-rounded user-avatar e-tip' title='".$title."' src='".$img."' alt='' style='width:".$width."px; height:".$height."px' />";
//	return $img;
	return $text;
}
?>
