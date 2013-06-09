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
/* For Profile 1.0 */

class msProfile1 extends msProfile {
  function msProfile1(&$msLoader) {
    $this->type = 'msProfile1';
    parent::msProfile($msLoader);
  }
  
  public function profileType() {
    return $this->type;
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

  /*
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
  */
  
  public function userName() {
    $regs = $this->regex('%<span class="nametext">(.*?)[\s]*?<.*?>%i', $this->html);
    
    /* We'll replace the single quotation mark HTML code for the actual thing */
    $name = html_entity_decode($regs[0][1], ENT_COMPAT, "UTF-8");
    
    /* We have to remove the double quotation marks, they mess up the javascript. */
    $name = preg_replace('%"%', '', $name);
    
    return trim($name);
  }

  public function userDefaultImage() {
    $regs = $this->regex('%<a.*?id=".*?DefaultImage".*?>[\s]*?<img.*?src="(.*?)" />%i', $this->html);
    return $regs[0][1];
  }

  public function userMsUrl() {
    /* We'll just append the custom URL name */
    return "http://www.myspace.com/" . $this->userMsUrlName();
  }
  
  public function userMsUrlName() {
    $regs = $this->regex('%<tr class="userProfileURL">[\s]*?.*?&nbsp;&nbsp;http://www\.myspace\.com/(.*?)&nbsp;%i', $this->html);
    if ($regs) {
      return $regs[0][1];
    } else {
      return $this->getId();
    }
  }

  public function userHeadline() {
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
    $regs = $this->regex('%Last Login:[\w\W\s]*?(\d{1,2}/\d{1,2}/\d{4})[\s]*?%i', $this->html);
    return $regs[0][1];
  }

  public function userInfo() {
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
  
  public function userGeneralInterests() {
  	return null;
  }

  public function userFriendCount() {
    $regs = $this->regex('%has <span class="redbtext">([\d]*?)</span> friends.%i', $this->html);
    $size = $regs[0][1];
    if ($size == "" || $size == null) {
      return 0;
    }
    return $size;
  }


  public function userDetails() {
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
      /* TODO: get rid of the hardcoded values */
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
  
  public function userPublic() {
    if (!preg_match('%<span.*?>This profile is set to private\. This user must add you as a friend to see his/her profile\.</span>%i', $this->html)) {
      return true;
    } else {
      return false;
    }
  }
  
  public function func3() {
    $regs = $this->regex('%%i', $this->html);
    $return = $regs[0];
    return ($return != null) ? $return : parent::func3();
  }
}
?>
