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
/* For Profile 2.0 Compatability */

class msProfile3 extends msProfile2 {
  function msProfile3(&$msLoader) {
    $this->type = 'msProfile3';
    parent::msProfile($msLoader);
  }
  
  public function profileType() {
    return $this->type;
  }
  
  public function userName() {
    $regs = $this->regex('%<h1><a class="userLink".*?>(.*?)</a></h1>%i', $this->html);
    /* We'll replace the single quotation mark HTML code for the actual thing */
    $name = html_entity_decode($regs[0][1], ENT_COMPAT, "UTF-8");
    
    /* We have to remove the double quotation marks, they mess up the javascript. */
    $return = preg_replace('%"%', '', $name);
    
    //return ($return != null) ? $return : parent::userName();
    return $return;
  }

  public function userDefaultImage() {
    $regs = $this->regex('%<img.*?class="profilePic".*?src="(.*?)".*?/>%i', $this->html);
    $return = $regs[0][1];
    
    //return ($return != null) ? $return : parent::userDefaultImage();
	return $return;
  }

  public function userHeadline() {
    /* This is the regex before the July 16 update
    $regs = $this->regex('%<td class="text" width="193" bgcolor="#ffffff" height="75" align="left">(.*?)<br>%', $this->html);
    */
    $regs = $this->regex('%<td class="text" width="193" bgcolor="#ffffff" height="75" align="left">([\w\W]*?)<br.*?>%', $this->html);
    return $regs[0][1];
  }

  public function userMsUrlName() {
    $regs = $this->regex('%<span class="urlLink"><a href="http://www\.myspace\.com/(.*?)"%i', $this->html);
    if ($regs) {
      $return = $regs[0][1];
    } else {
      $return = null;
    }
    
    
    return $return;
	//return ($return != null) ? $return : parent::userMsUrlName();
  }

  public function userStatus() {
    $regs = $this->regex('%<span class="status">(.*?)</span>%', $this->html);
    $return = $regs[0][1];
    
    
    return $return;
	//return ($return != null) ? $return : parent::userStatus();
  }

  public function userOnline() {
    $regs = $this->regex('%<li>[\s]*?<span.*?class="msOnlineNow.*?".*?><img src=".*?onlinenow2\.gif".*?>Online Now!?</span>%i', $this->html);
    
    return ($regs != null);// ? true : parent::userOnline();
  }

  public function userMood() {
    $regs = $this->regex('%<span class="mood"><strong>Mood:</strong>[\s]*?(.*?)[\s]*?<img.*?src="(.*?)"%i', $this->html);
    if ($regs) {
      $return = $regs[0][1] . ' <img src="' . $regs[0][2] . '" />';
    } else {
      $return = null;
    }
    
    
    return $return;
	//return ($return != null) ? $return : parent::userMood();
  }

  public function userLastLogin() {
    $regs = $this->regex('%Last Login:[\w\W\s]*?(\d{1,2}/\d{1,2}/\d{4})[\s]*?%i', $this->html);
    $return = $regs[0][1];
    
    
    return $return;
	//return ($return != null) ? $return : parent::userLastLogin();
  }

  public function userInfo() {
    $regs = $this->regex('%<ul class="profileUserInfo">[\w\W\s]*?<span class="age">(.*?)</span>[\w\W\s]*?<span class="locality">(.*?)</span>[\w\W\s]*?<span class="region">(.*?)</span>[\w\W\s]*?<span class="country-name">(.*?)</span>%i', $this->html);

    if ($regs != null) {
      $return = "{$regs[0][1]} years old, {$regs[0][2]}, {$regs[0][3]}, {$regs[0][4]}";
    } else {
      $return = null;
    }
    
    
    return $return;
	//return ($return != null) ? $return : parent::userInfo();
  }

  public function userGender() {
    $regs = $this->regex('%<span class="gender">(.*?)</span>%i', $this->html);
    $return = $regs[0][1];
    
    
    return $return;
	//return ($return != null) ? $return : parent::userGender();
  }

  public function userAboutMe() {
    $regs = $this->regex('%<div class="autoResize blurbAboutMe">([\w\W\s]*?)<div class="moduleBodyEnd">%i', $this->html);
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
      $return = implode($return, "");
    } else {
      $return = null;
    }
    
    //return ($return != null) ? $return : parent::userAboutMe();
    return $return;
  }
  
  public function userGeneralInterests() {
    $regs = $this->regex('%<li class="interestGeneral.*?>([\w\W\s]*?)</li>[\s]*?</ul>[\s]*?<div class="moduleBodyEnd">%i', $this->html);
    if ($regs) {
      $htmlGeneralInterests = $regs[0][1];
      $htmlGeneralInterests = preg_replace('%<style.*?>[\w\W]*?</style>%i', '', $htmlGeneralInterests);
      $htmlGeneralInterestsArray = explode("\n", $htmlGeneralInterests);
      $return = array ();
      $i = 0;
      foreach ($htmlGeneralInterestsArray as $line) {
        $line = trim($line);
        if ($line != null) {
          $line = $line . "<br />\n";
          $return[$i] = $line;
          $i++;
        }
      }
      $return = implode($return, "");
    } else {
      $return = null;
    }
    
    return $return;
  }

  public function userWhoIdLikeToMeet() {
    $htmlArray = null;
    $regs = $this->regex('%<div class="autoResize blurbLikeToMeet">[\s]*?<h4>Who I\'d like to meet:</h4>[\s]*?([\w\W\s]*?)[\s]*?</div>[\s]*?<div class="moduleBodyEnd">%i', $this->html);
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
      $return = implode($return, "");
    } else {
      $return = null;
    }
    
    
    return $return;
	//return ($return != null) ? $return : parent::userWhoIdLikeToMeet();
  }

  public function userFriendCount() {
    $regs = $this->regex('%has <span class="count.*?">([\d]*?)</span> friends.%i', $this->html);
    $return = $regs[0][1];
    
    
    return $return;
	//return ($return != null) ? $return : parent::userFriendCount();
  }

  public function getCommentsTotal() {
    $regs = $this->regex('%of <span class="count">(\d*?)</span> comments%i', $this->html);
    $return = trim($regs[0][1]);
    
    
    return $return;
	//return ($return != null) ? $return : parent::getCommentsTotal();
  }

  public function userDetails() {
    $regs = $this->regex('%<h3 class="moduleHead"><span><span>Details[\w\W\s]*?<div class="moduleBodyEnd">%i', $this->html);
    if($regs != null) {
      $details = $regs[0][0];
      $regs = $this->regex('%<strong>(.*?)</strong>[\s]*?<span>(.*?)</span>%i', $this->html);
      if ($regs) {
        for ($i = 0; $i < sizeof($regs); $i++) {
          $info[] = $regs[$i][1] . " " . $regs[$i][2] . "<br />";
        }
        $info = preg_replace('%<a.*?>(.*?)</a>%i', '$1', $info);
        return implode($info);
      } else {
        $return = null;
      }
    } else {
      $return = null;
    }
    
    return $return;
	//return ($return != null) ? $return : parent::userDetails();
  }

  public function userTopFriends() {
    $return = null;
    
    $regs = $this->regex('%<li.*?>[\s]*?<span.*?class="msProfileLink".*?><a.*?href="http://www\.myspace\.com/(.*?)"><span.*?class="pilDisplayName".*?>(.*?)</span><img.*?src="(.*?)".*?class="profileimagelink".*?/>%i', $this->html);
    
    if($regs) {
      for ($i = 0; $i < sizeof($regs); $i++) {
        $tops[$i]['id'] = $this->getFriendId($regs[$i][1]);
        $tops[$i]['name'] = $regs[$i][2];
        $tops[$i]['img'] = $regs[$i][3];
      }
      
      $return = $tops;
    }
    
    
    return $return;
	//return ($return != null) ? $return : parent::userTopFriends();
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
    $return = $comments;
    
    
    return $return;
	//return ($return != null) ? $return : parent::userPicComments();
  }

  public function userPublic() {
    /* If the parent class' username is null, we know this is profile 2.0 */
    if(parent::userName() == null) {
      if (!preg_match('%<div id="privateProfile">%i', $this->html)) {
        $return = true;
      } else {
        $return = false;
      }
    } else {
      $return = parent::userPublic();
    }
    return $return;
  }

  public function userTitle() {
    $regs = $this->regex('%<title>[\s]*(.*?)[\s]*</title>%i', $this->html);
    $return = $regs[0][1];
    $return = trim(preg_replace("%MySpace.com -%", "", $return));
    
    return $return;
	//return ($return != null) ? $return : parent::userTitle();
  }
  
  public function userInterests() {
    $regs = $this->regex('%<li.*?class="interest.*?><h4>(.*?)</h4><div.*?class="autoResize".*?>([\w\W\s]*?)</div></li>%i', $this->html);
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
	//return ($return != null) ? $return : parent::userInterests();
  }
  
  public function func3() {
    $regs = $this->regex('%%i', $this->html);
    $return = $regs[0];
    return ($return != null) ? $return : parent::func3();
  }
}
?>
