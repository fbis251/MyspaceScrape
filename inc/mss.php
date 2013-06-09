<?php
# Copyright (C) 2007-2013 Fernando Barillas
#
# Licensed under the GPL, Version 3.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#      http://www.gnu.org/licenses/gpl.txt
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
?>
<?php
/* This doesn't work anymore, use the one I made (mslogin.php) */
//require "accessClass.php";
require_once("mslogin.php");
class mss {
  function mss($id, $user, $pass, $rid = "", $html = "", $pics = "", $blogs = "", $comments = "") {
    $this->user = $user;
    $this->pass = $pass;
    $this->setId($id);
    $this->selfId      = $rid;
    $this->html        = $html;
    $this->pics        = $pics;
    $this->blogs       = $blogs;
    $this->comments    = $comments;
    $this->stylesheets = "";
    
    $this->picsCount = 0;
    
    if ($this->html == "" || $this->html == null) {
      $this->page = new myspace("ms");
      $this->page->myspaceCreds($user, $pass);
      $this->selfId = $this->loadSelfId();
      $this->html = $this->loadHtml();
      $this->pics = $this->loadPics();
      $this->blogs = $this->loadBlogs();
      $this->comments = $this->loadComments();
      $this->stylesheets = $this->loadStylesheets();
    }
    
    if($this->getId() == 0) {
      // We encoutered some kind of error. Die.
      die("An error has been encountered, please make sure the friendID is correct.");
    }
  }

  private function numId($stringId) {
    if (!is_numeric($stringId)) {
      $temp = @implode(@gzfile("http://www.myspace.com/$stringId"));
      if(!$temp) // We encountered an error, exit cleanly.
        return 0;
      //$regs = $this->regex('%www\.myspace\.com/([\d]*)%i', $temp);
      $regs = $this->regex('%DisplayFriendId":([\d]*)%i', $temp);
      if ($regs) {
        $return = $regs[0][1];
      } else {
        $return = 0;
      }
    } else {
      $return = $stringId;
    }
    return $return;
  }

  private function setId($newId) {
    $this->id = $this->numId($newId);
  }

  public function getId() {
    return $this->id;
  }

  public function newId($newId) {
    $this->setId($newId);
  }

  public function loadSelfId() {
    return $this->page->getMyId();
  }

  public function getSelfId() {
    return $this->selfId;
  }

  public function getHtml() {
    return $this->html;
  }
  
  public function getComments() {
    return $this->comments;
  }

  public function loadHtml() {
    /* 07/07/09 Don't know why this no longer works... Using the short URL does. Go figure. */
    //$url = "http://profile.myspace.com/index.cfm?fuseaction=user.viewprofile&friendid=" . $this->id;
    $url = "http://www.myspace.com/" . $this->id;
    $this->page->newLocation($url);
    $this->html = $this->page->returnLocation();
    return $this->html;
  }

  public function loadBlogsRss() {
    $url = "http://blog.myspace.com/blog/rss.cfm?friendID=" . $this->id;
    $this->page->newLocation($url);
    $this->blogs = $this->page->returnLocation();
    return $this->blogs;
  }
  
  public function loadComments() {
    $url = "http://comment.myspace.com/index.cfm?fuseaction=user.viewComments&friendID=" . $this->id;
    $this->page->newLocation($url);
    $this->comments = $this->page->returnLocation();
    return $this->comments;
  }

  public function loadBlogs() {
    $url = "http://blog.myspace.com/index.cfm?fuseaction=blog&friendID=" . $this->id;
    $this->page->newLocation($url);
    $this->blogs = $this->page->returnLocation();
    return $this->blogs;
  }
  
  public function loadStylesheets() {
    $url[] = "http://profileedit.myspace.com/Modules/Profiles/Handlers/UserStyle.ashx?friendId=" . $this->id . "&styleid=62317";
    $url[] = "http://profileedit.myspace.com/Modules/Profiles/Handlers/UserStyle.ashx?friendId=" . $this->id . "&styleid=4379";
    $this->stylesheets = "";
    foreach($url as $stylesheet) {
      $this->page->newLocation($stylesheet);
      $this->stylesheets .= $this->page->returnLocation();
    }
    return $this->stylesheets;
  }

  public function getBlogs() {
    return $this->blogs;
  }
  
  public function getStylesheets() {
    return $this->stylesheets;
  }

  public function getBlogsCount() {
    $size = trim(sizeof($this->userBlogs()));
    if ($size == "" || $size == null) {
      return 0;
    }
    return $size;
  }

  public function userBlogs() {
    $return = null;
    $reg = '%<div class="blogTimeStamp">[\s]*?(.*?)[\s]*?</div>[\w\W\s]*?<div class="blogSubject">[\s]*?<label.*?>(.*?)</label>[\w\W\s]*?(?:Current mood:([\w\W\s]*?)[\s]*?)?(?:<br.*?>Category:</b>[\w\W\s]*?(.*?)[\s]*?</div>[\s]*?)?<!--- blog body --->[\s]*?<div.*?class="blogContent".*?>([\w\W\s]*?)</div>[\s]*?<!---[\w\W\s]*?<div.*?class="cmtcell".*?><a.*?>(.*?)</a>%i';
    $regs = $this->regex($reg, $this->getBlogs());
    if($regs) {
      for ($i = 0; $i < sizeof($regs); $i++) {
        $ts = trim($regs[$i][1]) . ', ' . trim($regs[$i][6]) . ' PST';
        $ts = preg_replace('%&nbsp;%i', '', $ts);
        $ts = preg_replace('%-%i', '', $ts);
        $content = trim($regs[$i][5]);
        $content = preg_replace('%<p>[\s]*?<table.*?class="blogContentInfo">[\w\W\s]*?</table>%i', '', $content);
        $blogs[$i]['timestamp'] = strtotime($ts);
        $blogs[$i]['title'] = trim($regs[$i][2]);
        $blogs[$i]['mood'] = trim($regs[$i][3]);
        $blogs[$i]['category'] = trim($regs[$i][4]);
        $blogs[$i]['content'] = trim($content);
      }
      $return = $blogs;
    }
    
    return $return;
  }

  public function userPics() {
    $pics = $this->pics;
    $pics = @ preg_replace("%><!\[CDATA\[%i", ">", $pics);
    $pics = @ preg_replace("%]]><%i", "<", $pics);
    $regs = $this->regex('%<photo><file>(.*?)</file><caption>(.*?)</caption><link>(.*?)</link></photo>%i', $pics);
    $return = array ();
    for ($i = 0; $i < sizeof($regs); $i++) {
      $return[$i]['img'] = $regs[$i][1];
      $return[$i]['caption'] = $regs[$i][2];
      $return[$i]['url'] = $regs[$i][3];

    }
    return $return;
  }

  public function loadPics() {
    //$this->html = implode(gzfile("http://www.myspace.com/" . $this->id));
    //$this->html = implode(file($this->id . ".html"));
    $url = "http://www.myspace.com/services/media/photosXML.ashx?friendid=" . $this->id;
    $this->page->newLocation($url);
    $this->pics = $this->page->returnLocation();
    return $this->pics;
  }

  public function getPics() {
    return $this->pics;
  }

  public function getPicsCount() {
    $size = trim(sizeof($this->userPics()));
    if ($size == "" || $size == null) {
      return 0;
    }
    return $size;
  }

  public function resizePics($url, $which) {
    $which = strtolower($which);
    $which = $which[0];
    $return = $url;
    if(!preg_match('%s|m|l%', $which))
      $which = 's';
    
    /* One of these will match, replace it for the correct one */
    $return = preg_replace("%_s%", '_'.$which, $return);
    $return = preg_replace("%_m%", '_'.$which, $return);
    $return = preg_replace("%_l%", '_'.$which, $return);
    $return = preg_replace("%s_%", $which.'_', $return);
    $return = preg_replace("%m_%", $which.'_', $return);
    $return = preg_replace("%l_%", $which.'_', $return);
    
    return $return;
  }

  public function hoverEnlarge($img, $url) {
    $return = "";
    $return = $return . '<span onclick="toggleImage(this);" onmouseover="enlargeImage(this);" onmouseout="shrinkImage(this)">';
    $return = $return . '<img id="smallImage" src="' . $this->resizePics($img, 'small') . '" alt="Image Thumbnail" />';
    $return = $return . '<span class="hoverImageSpan"></span></span>';
    /*
    $return = $return . '<span class="thumbnail">' . "\n";
    $return = $return . ' <img alt="Image Small" src="' . $this->resizePics($img, "small") . '"/>' . "\n";
    $return = $return . ' <span>' . "\n";
    $return = $return . '   <a href="' . $url . '"<img alt="Image Large" src="' . $this->resizePics($img, "large") . '"/></a>' . "\n";
    $return = $return . ' </span>' . "\n";
    $return = $return . '</span>' . "\n";
    */
    return $return;
  }

  public function logout() {
    @ $this->page->newLocation("http://collect.myspace.com/index.cfm?fuseaction=signout");
    @ $this->page->close();
  }

  /*
  private function msLogin($finalurl,$user, $pass) {
    $page = new myspace("ms");
    $page->myspaceCreds($user, $pass);
    $page->newLocation($finalurl);
    $html = $page->returnLocation();
    $page->newLocation("http://collect.myspace.com/index.cfm?fuseaction=signout");
    $page->close();
    return $html;
  }*/

  protected function regex($reg, $text) {
    if (preg_match_all($reg, $text, $regs, PREG_SET_ORDER)) {
      return $regs;
    } else {
      return null;
    }
  }

  public function userName() {
    /* This is the regex before the July 16 update 
    $regs = $this->regex('%<span class="nametext">(.*?)</span>%i', $this->html);
    */
    /* This one was being weird... 
    $regs = $this->regex('%<span.*?class="nametext".*?>(.*?)<br%i', $this->html);
    */
    $regs = $this->regex('%<span class="nametext">(.*?)[\s]*?<.*?>%i', $this->html);
    
    /* We'll replace the single quotation mark HTML code for the actual thing */
    $name = html_entity_decode($regs[0][1], ENT_COMPAT, "UTF-8");
    
    /* We have to remove the double quotation marks, they mess up the javascript. */
    $name = preg_replace('%"%', '', $name);
    
    return trim($name);
  }

  public function userDefaultImage() {
    /* This is the regex before the July 16 update
    $regs = $this->regex('%<a.*?id=".*?DefaultImage" href=".*?"><img border="0" alt="" src="(.*?)" />%i', $this->html);
    */
    $regs = $this->regex('%<a.*?id=".*?DefaultImage".*?>[\s]*?<img.*?src="(.*?)" />%i', $this->html);
    return $regs[0][1];
  }

  public function userMsUrl() {
    /*
    $regs = $this->regex('%<tr class="userProfileURL">[\s]*?<td><div align="left">&nbsp;&nbsp;(.*?)&nbsp;&nbsp;</div>%i', $this->html);
    if ($regs) {
      return $regs[0][1];
    } else {
      return "http://www.myspace.com/" . $this->getId();
    }
    */
    return "http://www.myspace.com/" . $this->userMsUrlName();
  }
  
  public function userMsUrlName() {
    //$regs = $this->regex('%<tr class="userProfileURL">[\s]*?<td><div align="left">&nbsp;&nbsp;http://www\.myspace\.com/(.*?)&nbsp;&nbsp;</div>%i', $this->html);
    $regs = $this->regex('%<tr class="userProfileURL">[\s]*?.*?&nbsp;&nbsp;http://www\.myspace\.com/(.*?)&nbsp;%i', $this->html);
    if ($regs) {
      return $regs[0][1];
    } else {
      return $this->getId();
    }
  }

  public function userHeadline() {
    /* This is the regex before the July 16 update
    $regs = $this->regex('%<td class="text" width="193" bgcolor="#ffffff" height="75" align="left">(.*?)<br>%', $this->html);
    */
    $regs = $this->regex('%<td class="text" width="193" bgcolor="#ffffff" height="75" align="left">([\w\W]*?)<br.*?>%', $this->html);
    return $regs[0][1];
  }

  public function userStatus() {
    $regs = $this->regex('%<span.*?id=".*?ctrlMessage.*?".*?>(.*?)</span>%', $this->html);
    return $regs[0][1];
  }

  public function userOnline() {
    $regs = $this->regex('%<span.*?class="msOnlineNow.*?".*?><img.*?src=".*?onlinenow.*?gif".*?>Online Now!</span>[\s]*?<br.*?>[\s]*?<br.*?>Last Login%i', $this->html);
    return ($regs) ? true : false;
  }

  public function userMood() {
    $regs = $this->regex('%<b>Mood:</b>[\s]*(.*?)[\s]*<img src="(.*?)" id=".*?moodImage" alt="Mood Image" />[\s]*?</td>%', $this->html);
    if ($regs) {
      return $regs[0][1] . ' <img src="' . $regs[0][2] . '" />';
    } else {
      return null;
    }
  }

  public function userLastLogin() {
    /* This is the regex before the July 16 update
    $regs = $this->regex('%<br>[\s]*?Last Login:[\s]*?(\d{1,2}/\d{1,2}/\d{4})[\s]*?<br>%i', $this->html);
    */
    $regs = $this->regex('%Last Login:[\w\W\s]*?(\d{1,2}/\d{1,2}/\d{4})[\s]*?%i', $this->html);
    return $regs[0][1];
  }

  public function userInfo() {
    /* This is the regex before the July 16 update
    $regs = $this->regex('%<td class="text" width="193" bgcolor="#ffffff" height="75" align="left">.*?<br>([\w\W\s]*?)<br />%', $this->html);
    */
    $regs = $this->regex('%<td.*?align="left">.*?<br.*?>([\w\W\s]*?)<br /><br />%i', $this->html);
    if(!$regs)
      $regs = $this->regex('%<td[^>]*?align="left">.*?([\w\W\s]*?)<br /><br />%i', $this->html);

    if ($regs != null) {
      $result = $regs[0][1];
      $result = preg_replace("%<br.*?>%", '', $result);
      $result = explode("\n", $result);

      for ($i = 0; $i < sizeof($result); $i++) {
        $r = trim($result[$i]);
        if (($r != null) && ($r != '')) {
          $info[] = "$r ";
        }
      }
      return trim(implode($info));
    } else {
      return null;
    }
  }

  public function userGender() {
    $return = "unknown";
    if (preg_match('%female%i', $this->userInfo())) {
      $return = "Female";
    } else if (preg_match('%male%i', $this->userInfo())) {
      $return = "Male";
    }
    return $return;
  }

  public function userAboutMe() {
    $regs = $this->regex('%<span class="orangetext15">[\s]*?About me:</span><br/>[\s]*?<span class="text">([\w\W]*?)</span>[\s]*?</td>[\s]*?</tr>[\s]*?<tr>[\s]*?<td valign="top"%i', $this->html);
    if ($regs) {
      $htmlAboutMe = $regs[0][1];
      $htmlAboutMe = preg_replace('%<style.*?>[\w\W]*?</style>%i', '', $htmlAboutMe);
      /*
      $htmlAboutMe = preg_replace('%<.*?>%i', '', $htmlAboutMe);
      $htmlAboutMe = preg_replace('%<%', '&lt;', $htmlAboutMe);
      $htmlAboutMe = preg_replace('%>%', '&gt;', $htmlAboutMe);
      */
      $htmlAboutMeArray = explode("\n", $htmlAboutMe);
      $return = array ();
      $i = 0;
      foreach ($htmlAboutMeArray as $line) {
        $line = trim($line);
        if ($line != null) {
          $line = $line . "<br />\n";
          $return[$i] = $line;
          $i++;
        }
      }
      return implode($return, "");
    } else {
      return "None";
    }
  }

  public function userWhoIdLikeToMeet() {
    $htmlArray = null;
    $regs = $this->regex('%Who I\'d like to meet:</span><br/>[\s]*?<span class="text">([\w\W]*?)[\s]*?</td>[\s]*?</tr>[\s]*?</table>[\s]*?</td>[\s]*?</tr>[\s]*?</table>[\s]*?<br>[\s]*?<table.*?class="friendSpace".*?>%i', $this->html);
    if ($regs) {
      $html = $regs[0][1];
      $html = preg_replace('%<style.*?>[\w\W]*?</style>%i', '', $html);
      /*
      $html = preg_replace('%<.*?>%i', '', $html);
      $html = preg_replace('%<%', '&lt;', $html);
      $html = preg_replace('%>%', '&gt;', $html);
      */
      $htmlArray = explode("\n", $html);
    }
    $return = array ();
    $i = 0;
    if ($htmlArray != null) {
      foreach ($htmlArray as $line) {
        $line = trim($line);
        if ($line != null) {
          $line = $line . "<br />\n";
          $return[$i] = $line;
          $i++;
        }
      }
      return implode($return, "");
    } else {
      return "None";
    }
  }

  public function userFriendCount() {
    $regs = $this->regex('%has <span class="redbtext">([\d]*?)</span> friends.%i', $this->html);
    $size = $regs[0][1];
    if ($size == "" || $size == null) {
      return 0;
    }
    return $size;
  }

  public function getCommentsTotal() {
    $regs = $this->regex('%<div.*?class="pagingLeft">Listing.*?(\d*?)[\s]*?</div>%i', $this->comments);
    $size = trim($regs[0][1]);
    if ($size == "" || $size == null) {
      return 0;
    }
    return $size;
  }

  public function getCommentsCount() {
    $size = trim(sizeof($this->userComments()));
    if ($size == "" || $size == null) {
      return 0;
    }
    return $size;
  }

  public function userDetails() {
    /*$regs = $this->regex('%<tr id=.*?:Row><td.*?><span class="lightbluetext8 label">(.*?)</span></td><td id="Profile.*?:".*?>(.*?)</td></tr>%i', $this->html);*/
    $regs = $this->regex('%<tr id=".*?:Row"><td.*?><span class="lightbluetext8 label">(.*?)</span></td><td id="Profile.*?:".*?>(.*?)</td></tr>%i', $this->html);
    if ($regs) {
      for ($i = 0; $i < sizeof($regs); $i++) {
        $info[] = $regs[$i][1] . " " . $regs[$i][2] . "<br />";
      }
      $info = preg_replace('%<a.*?href=".*?".*?>(.*?)</a>%i', '$1', $info);
      return implode($info);
    } else {
      return null;
    }
  }

  public function userMusicPlayer() {
    $regs = $this->regex('%<div[^>]*?>[\w\W\s]*?<param[^>]*?name="movie"[^>]*?value="(http://lads\.myspace\.com/videos/Main\.swf)"[^>]*?>[\w\W\s]*?<param[^>]*?name="flashvars"[^>]*?value="(.*?)"[^>]*?>%i', $this->html);
    if($regs) {
      $url       = $regs[0][1];
      $flashVars = $regs[0][2];
      
      /* The width for the music player is higher for band profiles */
      $width = preg_match('%profile_mp3Player%i', $regs[0][0]) ? 450 : 295;
        
      
      /* Turn off autoplay */
      $flashVars = preg_replace('%ap=1%i', 'ap=0', $flashVars);
      return '<embed src="'.$url.'" flashvars="'.$flashVars.'" wmode="transparent" width="'.$width.'" height="345" />';
    } else {
      return "";
    }
  }

  public function userTopFriends() {
    $return = null;
    /* Changed this on August 9, 2008
    $regs = $this->regex('%<a href="http://profile\.myspace\.com/index\.cfm\?fuseaction=user\.viewprofile&friendid=([\d]*?)" id="ctl00_Main_ctl00_UserFriends.*?">(.*?)</a>[\w\W\s]*?<a href=".*?" id=".*?friendImageLink"><img src="(.*?)".*?/></a><br>%', $this->html);
    */
    $regs = $this->regex('%<a href="(.*?)" id=".*?UserFriends.*?">(.*?)</a>[\w\W\s]*?id=".*?friendImageLink"><img src="(.*?)".*?/></a>%i', $this->html);
    
    if($regs) {
      for ($i = 0; $i < sizeof($regs); $i++) {
        $tops[$i]['name'] = $regs[$i][2];
        $tops[$i]['id'] = $this->getFriendId($regs[$i][1]);
        $tops[$i]['img'] = $regs[$i][3];
      }
      $return = $tops;
    }
    return $return;
  }

  /*
  Old Profile-based comments
  public function userComments() {
    $return = null;
    $reg = '%<td align.*>[\s]*<a href="(.*?)">[\s]*?(.*?)[\s]*?</a>[\w\W\s]*?<a href=".*?">[\s]*?<img src="(.*?)" border="0"/></a><br>[\w\W\s]*?<span class="blacktext10">[\s]*?(.*?)[\s]*?</span>[\s]*?<br>[\s]*?<br>[\s]*([\w\W\s]*?)[\s]*</td>%i';
    //$reg = '%<a href="http://profile\.myspace\.com/index\.cfm\?fuseaction=user\.viewprofile&friendid=([\d]*?)">[\s]*?(.*?)[\s]*?</a>[\w\W\s]*?<a href=".*?">[\s]*?<img src="(.*?)" border="0"/></a><br>[\w\W\s]*?<span class="blacktext10">[\s]*?(.*?)[\s]*?</span>[\s]*?<br>[\s]*?<br>[\s]*?(.*?)[\s]*?</td>%i';
    $regs = $this->regex($reg, $this->html);
    
    if($regs) {
      for ($i = 0; $i < sizeof($regs); $i++) {
        $comments[$i]['id'] = $this->getFriendId(trim($regs[$i][1]));
        $comments[$i]['name'] = trim($regs[$i][2]);
        $comments[$i]['img'] = trim($regs[$i][3]);
        $comments[$i]['timestamp'] = strtotime(trim($regs[$i][4]));
        $comments[$i]['text'] = trim($regs[$i][5]);
      }
      $return = $comments;
    }
    return $return;
  }
  */
  
  public function userComments() {
    $return = null;
    $reg = '%<tr.*?>[\w\W\s]*?<a.*?href="(.*?)".*?>(.*?)</a>[\w\W\s]*?<img.*?src="(.*?)".*?>(?:<span.*?class="pilRealName".*?>(.*?)</span>)?</a>[\s]*?</span>[\w\W\s]*?<h4>(.*?)</h4>[\s]*([\w\W\s]*?)[\s]*?</textarea>[\w\W\s]*?</tr>%i';
    $reg = '%<tr.*?>[\w\W\s]*?<a.*?href="(.*?)".*?><span.*?class="pilDisplayName">(.*?)</span>[\w\W\s]*?<img.*?src="(.*?)".*?>(?:<span.*?class="pilRealName".*?>(.*?)</span>)?</a>[\s]*?</span>[\w\W\s]*?<h4>(.*?)</h4>[\s]*([\w\W\s]*?)[\s]*?</textarea>[\w\W\s]*?</tr>%i';
    $regs = $this->regex($reg, $this->comments);
    
    if($regs) {
      for ($i = 0; $i < sizeof($regs); $i++) {
        $comments[$i]['id'] = $this->getFriendId(trim($regs[$i][1]));
        $comments[$i]['name'] = trim($regs[$i][2]);
        $comments[$i]['img'] = trim($regs[$i][3]);
        $comments[$i]['fullname'] = trim($regs[$i][4]);
        $comments[$i]['timestamp'] = strtotime(trim($regs[$i][5]));
        $comments[$i]['text'] = trim($regs[$i][6]);
      }
      $return = $comments;
    }
    return $return;
  }

  public function userPicComments() {
    $html = implode(file("pic.html"), "");
    $regs = $this->regex('%<td style="text-align: center;".*?>[\s]*?<a href=".*?">([\w\W]*?)</a>[\s\w\W]*?<a href=".*?friendid=(\d+).*?">[\s]*?<img src="(.*?)".*?/></a><br />[\w\W\s]*?<strong>([\s\w\W]*?)</strong>[\s]*?<br />([\w\W\s]*?)</td>%', $html);
    for ($i = 0; $i < sizeof($regs); $i++) {
      $comments[$i]['name'] = trim($regs[$i][1]);
      $comments[$i]['id'] = $this->getFriendId(trim($regs[$i][2]));
      $comments[$i]['img'] = trim($regs[$i][3]);
      $comments[$i]['timestamp'] = strtotime(trim($regs[$i][4]));
      $comments[$i]['text'] = trim($regs[$i][5]);
    }
    return $comments;
  }

  public function userTrackers() {
    $trackers = file("inc/trackers.txt");
    $d = 0;
    $return = array ();
    $check = $this->html . $this->stylesheets;
    
    for ($i = 0; $i < sizeof($trackers); $i++) {
      $track = trim($trackers[$i]);
      $find = preg_match("%$track%", $check);
      if ($find) {
        if ($track == "imaqeshack.us") {
          $return[] = "$track (Terikan's Tracker)";
        } else if ($track == "205.134.170.241") {
          $return[] = "$track (SpySpace)";
        } else if($track == "google-analytics.com") {
          /* Do nothing... */
        } else {
          $return[] = "$track";
        }
        $d++;
      }
    }
    return $return;
  }

  public function getTrackersCount() {
    return trim(sizeof($this->userTrackers()));
    if ($size == "" || $size == null) {
      return 0;
    }
    return $size;
  }

  public function getFriendId($urlString) {
    $id = 0;

    if($result = $this->regex('%^([\w_]*?)$%i', $urlString)) {
      $id = $result[0][1];
      /* Regular friendID */
    } else if($result = $this->regex('%myspace\.com\/([\w_]*?)$%i', $urlString)) {
      /* www.myspace.com/friendid */
      $id = $result[0][1];
    } else if($result = $this->regex('%friendid=(\d*)%i', $urlString)) {
      /* friendid=12345 */
      $id = $result[0][1];
    }

    return $id;
  }

  public function userFriends() {
    $page = new myspace("ms");
    $page->myspaceCreds($this->user, $this->pass);

    $url = "http://collect.myspace.com/index.cfm?myTopEight=0&fuseaction=user.editTop8&friendid=" . $this->id . "&page=0";
    $page->newLocation($url);
    $this->friends = $page->returnLocation();
    $pages = $this->regex("%of&nbsp;[\s]*?<a href=\"javascript:NextPage\('(\d*?)'\)\">%i", $this->friends);
    $pagesNum = $pages[0][1];
    //echo $pagesNum . " pages<br />\n";
    for ($i = 1; $i <= $pagesNum; $i++) {
      $url = "http://collect.myspace.com/index.cfm?myTopEight=0&fuseaction=user.editTop8&friendid=" . $this->id . "&page=$i";
      $page->newLocation($url);
      $this->friends = $this->friends . $page->returnLocation();
      if ($i > 3)
        break;
    }
    $page->newLocation("http://collect.myspace.com/index.cfm?fuseaction=signout");
    $page->close();

    $regs = $this->regex('%<li class="box" title="(\d*?)">(.*?)<br><img src="(.*?)" width="75".*?></li>%i', $this->friends);
    for ($i = 0; $i < sizeof($regs); $i++) {
      $friends[$i]['id'] = $this->getFriendId(trim($regs[$i][1]));
      $friends[$i]['name'] = trim($regs[$i][2]);
      $friends[$i]['img'] = trim($regs[$i][3]);
    }
    return $friends;
  }

  public function userPublic() {
    if (!preg_match('%<span.*?>This profile is set to private\. This user must add you as a friend to see his/her profile\.</span>%i', $this->html)) {
      return true;
    } else {
      return false;
    }
  }

  public function clean($text, $textMode = "low") {
    $text = $this->linkReplace($text);
    
    /* Trim whitespace */
    $text = preg_replace('%^[\s]+|[\s]+$%i', '', $text);
    
    /* The tagConverts are in there because of the replacements we made to
       sanitize the code */
    $find = array (
      '%<%',
      '%>%',
      '%<object.*?>[\w\W\s]*?</object>%i',
      $this->tagConvert('%<a.*?href="(http://\w*\.*)(\w*\.\w{2,3})(/[^"]*?)".*?>(.*?)</a>%'),
      $this->tagConvert('%<img.*?src="(http://(?:[\w\-_]*\.)*)([\w\-_]*\.\w{2,3})(/[^"]*?)".*?>%i'),
      $this->tagConvert('%[\s]*?<br[\s]*?/?>[\s]*?%i'),
      '%%i',
      '%%i',
      '%%i'
    );
    $replace = array (
      '&lt;',
      '&gt;',
      '',
      // 1 is the subdomain, 2 is the TLD, 3 is the rest of the URL, 4 is the text the link had
      'LINK: <a href="\1\2\3">[\2]</a><br />\4' . "\n",
      // 1 is the subdomain, 2 is the TLD, 3 is the rest of the URL
      'IMAGE: <a href="\1\2\3">[\2]</a><br />' . "\n",
      "<br />\n",
      '',
      '',
      ''
    );
    /* Remember to add this option to the control panel! */
    if ($textMode == "high") {
      $text = preg_replace($find, $replace, $text);
    } else {
      $text = preg_replace($this->tagConvert('%<.*?>%i'), '', $text);
      $text = preg_replace('%</td>%i', ' ', $text);
      $text = preg_replace('%</tr>%i', "<br />\n", $text);
      /* Remove superfluous breaks */
      $text = preg_replace('%(?:<br.*?>[\s]*+)+%i', "<br />\n", $text);
      $text = preg_replace('%<.*?>%i', '', $text);
      $text = preg_replace('%<%i', '&lt;', $text);
      $text = preg_replace('%>%i', '&gt;', $text);
      /* Remove whitespace */
      $text = preg_replace('%^(?:[\s])+%im', '', $text);
      /* Remove lines with 2 dots */
      $text = preg_replace('%[\s]*?\.\.[\s]*?%i', '', $text);
      
      /* Now add breaks for every line break that's alone */
      //$text = preg_replace('%((?:[^\s>][\s])?|<br />)$%im', '\1'."<br />", $text);
      $text = preg_replace('%^(.*?)[\s]*?(?:(<br[\s]*?/?>))*[\s]*?$%im', '\1'."<br />", $text);
      
      /* Find single lines with a break and remove the break */
      $text = preg_replace('%[\s]*?(.*?)(?:<br[\s]*?/?>)[\s]*?$%i', '\1', $text);
      
      /* Find lines with only line breaks and remove them */
      $text = preg_replace('%^[\s]*?<br[\s]*?/?>[\s]*?$%m', '', $text);
      
      /* Remove line breaks from beginning of strings */
      $text = preg_replace('%^[\s]*?<br[\s]*?/?>[\s]*?%i', '', $text);
    }
    return trim($text);
  }

  private function tagConvert($text) {
    $text = preg_replace('%<%', '&lt;', $text);
    $text = preg_replace('%>%', '&gt;', $text);
    return $text;
  }

  private function linkReplace($comment) {
    $comm = $comment;
    $reg  = '%"http://www\.msplinks\.com/([^"]+)"%i';
    /* mspblinks are encoded in an unknown format... We'll take them out for the
       time being... */
    $reg2 = '%<a.*?href=http://www.mspblinks.com/.*?>(.*?)</a>%i';
    $comment = preg_replace($reg2, '\1', $comment);
    preg_match_all($reg, $comment, $result, PREG_SET_ORDER);
    for ($i = 0; $i < sizeof($result); $i++) {
      $thelink = $result[$i][1];
      $thelink = base64_decode($thelink);
      $thelink = substr($thelink, 2);
      $thelink = "\"$thelink\"";
      $comment = preg_replace($reg,  $thelink, $comment);
    }
    //return $comment;
    return $comm;
  }

  public function userTitle() {
    $regs = $this->regex('%<title>[\s]*(.*?)[\s]*</title>%i', $this->html);
    $return = $regs[0][1];
    $return = trim(preg_replace("%MySpace.com -%", "", $return));
    return $return;
  }

  public function userInterests() {
    $regs = $this->regex('%<span.*?class="lightbluetext8">(.*?)</span>[\w\W\s]*?<td.*?id="Profile.*?">([\w\W\s]*?)</td></tr>%i', $this->html);
    $i = 0;
    // 1 is the title of the section
    // 2 is the text contained inside the section
    if ($regs) {
      foreach ($regs as $reg) {
        $return[$i][0] = $reg[1];
        $return[$i][1] = $reg[2];
        $i++;
      }
    } else {
      $return = null;
    }
    return $return;
  }
  
  public function echoBlank($string) {
    echo ($string != null) ? trim($string) : "&nbsp;";
  }
  
  public function func3() {
    $regs = $this->regex('%%i', $this->html);
    return $regs[0];
  }
}
?>
