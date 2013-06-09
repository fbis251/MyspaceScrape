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
/* Class: msDetect
   Desc: Determines the type of profile passed in and returns the correct object
         for the type of profile.
*/
require_once('msProfile.php');

class msDetect {
  function msDetect($id, $user, $pass) {
    $this->msLoader = new msLoader($id, $user, $pass, '');
    $this->html = $this->msLoader->getHtml();
    return $this->detectProfileType();
  }
  
  /* Name: detectProfileType
     Desc: Detects the type of Myspace profile based on the loaded HTML, and
           returns the correct object.
  */
  public function detectProfileType() {
    $return = null;
	
    $this->msProfileBand = new msProfileBand($this->msLoader);
    $this->msProfile3    = new msProfile3($this->msLoader);
    $this->msProfile2    = new msProfile2($this->msLoader);
    $this->msProfile1    = new msProfile1($this->msLoader);
      
    $profiles = array($this->msProfileBand, $this->msProfile3, $this->msProfile2, $this->msProfile1);
    
    /* See which type has the max count, it is likely the correct one */
    $maxCount = 0;
    foreach($profiles as $profile) {
      $currentCount = $this->count($profile);
      
      /* If the current count is higher than the rest, it's the correct
         profile type */
      if($currentCount > $maxCount) {
        $maxCount = $currentCount;
        $return = $profile;
      }
    }
    
    /* We'll return msProfile1 by default */
    return ($return) ? $return : $this->msProfile1;
  }
  
  /* Name: count
     Desc: Counts how many methods for $msProfile are defined and not null.
	       Useful for determining how much information is available under the
		   parsed profile type.
  */
  public function count($msProfile) {
    $functions = array(
                        'userDefaultImage',
                        'userName',
                        'userStatus',
                        'userLastLogin',
                        'userHeadline'
                      );
    
    $count = 0;

    foreach($functions as $function) {
      /* Make sure we are not calling a nonexistent method first, then make sure
         that it does not return null.
      */
      if(method_exists($msProfile, $function) && $msProfile->$function() != null) {
        $count++;
      }
    }
    return $count;
  }
  
}
?>