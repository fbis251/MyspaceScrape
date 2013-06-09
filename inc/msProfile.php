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
require_once('msProfile1.php');
require_once('msProfile2.php');
require_once('msProfile3.php');
require_once('msProfileBand.php');
require_once('includes.php');

/* Class: msProfile
   Desc:  Parent class that determines which methods should be overwritten
          by the more complete and detailed children classes tailored
          for each profile type. Additionally, it provides some methods
          that are common to all profile types.
*/
abstract class msProfile {
  abstract public function userName();
  /* TODO: Think this through...
    Possibly won't need these abstract functions. Can write null-returning
    functions for band profiles to meet the abstraction, but it will just add
	needless code.
  abstract public function userDefaultImage();
  abstract public function userMsUrl();
  abstract public function userMsUrlName();
  abstract public function userHeadline();
  abstract public function userStatus();
  abstract public function userOnline();
  abstract public function userMood();
  abstract public function userLastLogin();
  abstract public function userInfo();
  abstract public function userGender();
  abstract public function userAboutMe();
  abstract public function userWhoIdLikeToMeet();
  abstract public function userFriendCount();
  abstract public function userDetails();
  abstract public function userMusicPlayer();
  abstract public function userTopFriends();
  abstract public function userPicComments();
  abstract public function userFriends();
  abstract public function userPublic();
  abstract public function userTitle();
  abstract public function userInterests();
  */
  
  /* Constructor */
  function msProfile(&$loader) {
    /* Load all the pages */
    $this->albums      = $loader->getAlbums();
    $this->blogs       = $loader->getBlogs();
    $this->comments    = $loader->getComments();
    $this->html        = $loader->getHtml();
    $this->pics        = $loader->getPics();
    $this->stylesheets = $loader->getStylesheets();
    
    /* Parse the albums */
    $this->albumsArray = $this->userAlbums();
  }
  
  /* Common functions */
  public function userAlbums() {
    $return = null;
    
    $reg = '%<a[^>]*?href="[^>]*?albumId=(\d*?)"[^>]*?>(.*?)</a>%i';
    $regs = $this->regex($reg, $this->albums);
    
    if(@$regs) {
      for ($i = 0; $i < sizeof($regs); $i++) {
        $albums[$i]['id'] = trim($regs[$i][1]);
        $albums[$i]['name'] = trim($regs[$i][2]);
      }
      $return =  $albums;
    }
    return $return;
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
  
  public function userComments() {
    $return = null;
    
    $reg = '%<td.*?>[\s]*?<span.*?><a.*?href="(.*?)".*?><span.*?class="pilDisplayName">(.*?)</span>[\w\W\s]*?<img.*?src="(.*?)".*?>(?:<span.*?class="pilRealName".*?>(.*?)</span>)?</a>[\s]*?</span>[\w\W\s]*?<h4>(.*?)</h4>[\s]*([\w\W\s]*?)[\s]*?</textarea>[\w\W\s]*?</tr>%i';
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
  
  public function userPics() {
    $return = array();
    $i = 0;
    
    $html = new simple_html_dom();
    $html->load($this->pics);
    
    /* This is the main element (an ordered list) containing the images inside links */
    foreach($html->find('ol#photoList') as $photo) {
      /* We are now looking at the links themselves */
      foreach($photo->find('a') as $imgLink) {
        /* We can access the URL that the href points to */
        $return[$i]['url']     = 'http://www.myspace.com' . $imgLink->href;
        foreach($imgLink->find('img') as $image) {
          /* Now we're analzying the img tags themselves and can get the src and alt */
          $return[$i]['img']     = $image->src;
          $return[$i]['caption'] = $image->alt;
        }
      $i++;
      }
    }
    /* Deallocate memory used for analzying the HTML */
    $html->clear();
    return $return;
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
  
  public function getAlbumName($id) {
    $return = '';
    for($i = 0; $i < sizeof($this->albumsArray); $i++) {
      $album = $this->albumsArray[$i];
      if($id == $album['id'])
        $return = $album['name'];
    }
    return $return;
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

  protected function regex($reg, $text) {
    if (preg_match_all($reg, $text, $regs, PREG_SET_ORDER)) {
      return $regs;
    } else {
      return null;
    }
  }
  
}

?>