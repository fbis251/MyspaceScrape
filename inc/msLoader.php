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
require_once('includes.php');
/* Class: msLoader
   Desc:  Loads all the profile-related pages and stores them to be parsed
          by other classes
*/

class msLoader {
  public $id;
  
  /* ADD A NEW CONSTRUCTOR THAT LOADS PROFILES BASED ON PREVIOUS SESSION.
     IT SHOULD CONTINUE USING cURL'S COOKIE JAR FILE!!!
  */
  function msLoader($id, $user, $pass, $jarId = '') {
    global $options;
    $this->options = $options;
    
    /* Find the numeric friendId */
    $this->setId($id);
    
    if($jarId == '') {
      /* Create a new page loader */
      $this->page = new msLogin();
      $this->page->myspaceCreds($user, $pass);
      
      /* Load all the pages */
      $this->albums      = $this->loadAlbums();
      $this->blogs       = $this->loadBlogs();
      $this->comments    = $this->loadComments();
      $this->html        = $this->loadHtml();
      $this->pics        = $this->loadPics();
      $this->stylesheets = $this->loadStylesheets();
      
      /* Set the numeric friendId */
      $this->id          = $this->getId();
      
      /* Log out */
      $this->page->close();
    } else {
      /* We have a cookie jar to work from, we only need to load the page */
      /* Create a new page loader */
      $this->page = new msLogin($jarId);
    }
  }
  
  public function getAlbums() {
    return $this->albums;
  }
  public function getBlogs() {
    return $this->blogs;
  }
  public function getComments() {
    return $this->comments;
  }
  public function getHtml() {
    return $this->html;
  }
  public function getId() {
    return $this->id;
  }
  public function getPics() {
    return $this->pics;
  }
  public function getStylesheets() {
    return $this->stylesheets;
  }
  
  private function loadAlbums() {
    $url = 'http://viewmorepics.myspace.com/index.cfm?fuseaction=user.viewAlbums&friendID=' . $this->id;
    $this->page->newLocation($url);
    $this->albums = $this->page->returnLocation();
    return $this->albums;
  }
  
  private function loadBlogs() {
    $url = 'http://blog.myspace.com/index.cfm?fuseaction=blog&friendID=' . $this->id;
    $this->page->newLocation($url);
    $this->blogs = $this->page->returnLocation();
    return $this->blogs;
  }
  
  private function loadBlogsRss() {
    $url = 'http://blog.myspace.com/blog/rss.cfm?friendID=' . $this->id;
    $this->page->newLocation($url);
    $this->blogs = $this->page->returnLocation();
    return $this->blogs;
  }
  
  private function loadComments() {
    $maxComments = $this->options['commentsPages'];
    
    $this->comments = '';
    /* TODO: Detect how many pages of comments there are, and do not surpass them!@#! */
    
    for($i = 1; $i <= $maxComments; $i++) {
      $url = 'http://comment.myspace.com/index.cfm?fuseaction=user.viewComments&friendID=' . $this->id . '&page=' . $i;
      $this->page->newLocation($url);
      $this->comments .= $this->page->returnLocation();
    }
    
    return $this->comments;
  }
  
  private function loadHtml() {
    /* Need to disable the Myspace Lite option */
    $url = 'http://www.myspace.com/' . $this->id . '?lite=0';
    $this->page->newLocation($url);
    $this->html = $this->page->returnLocation();
    return $this->html;
  }
  
  private function loadPics() {
  $hash   = '';
  $return = '';
	$maxPage = 1;
  
	$url = 'http://www.myspace.com/'. $this->id .'/photos/page/' . $maxPage . '?typeid=54&isGrouped=true';
    $this->page->newLocation($url);
	$this->pics = $this->page->returnLocation();
  
  /* We need to find out the hash so that we can load the pics using a POST request */
  if (preg_match('/HashMashter="(.*?)"/i', $this->pics, $regs)) {
    $hash = $regs[1];
  } else {
    $hash = '';
  }
    /* Look for all the photos pages and add all the html into one var */
    $regs = $this->regex('%photos/page/(\d+)%i', $this->pics);
    if($regs && $hash) {
      foreach($regs as $photoPage) {
        /* Find out the highest page so we can load it */
        if($photoPage[1] > $maxPage)
          $maxPage = $photoPage[1];
    }
    
    /* Now we can do POST requests to load the rest of the pics, should be quicker than
       loading the whole page of pics again */
	  if($maxPage > 1) {
      for($pageNumber = 2; $pageNumber <= $maxPage; $pageNumber++) {
        /* Load all the URLs and concatenate them into photos var */
        $url = 'http://www.myspace.com/Modules/PageEditor/Handlers/Profiles/Module.ashx';
        $postData = 'PageNo='.$pageNumber.'&typeid=54&isGrouped=true&Hash='.$hash;
        $this->page->doPost($url, $postData);
        $this->pics .= $this->page->returnLocation();
      }
	  }
	}
    return $this->pics;
  }
  
  private function loadStylesheets() {
    /* This variable will hold the raw stylesheet CSS */
    $this->stylesheets = "";
    
    /* Look for all stylesheets hosted on profileedit server, get the URLs */
    $regs = $this->regex('%<link.*?href="(http://profileedit\.myspace\.com/.*?)".*?>%i', $this->html);
    
    if($regs) {
      foreach($regs as $stylesheet) {
        /* Load all the URLs and concatenate them into stylesheets var */
        $url = $stylesheet[1];
        $this->page->newLocation($url);
        $this->stylesheets .= $this->page->returnLocation();
      }
    }
    
    return $this->stylesheets;
  }
  
  public function loadUrl($url) {
    $this->page->newLocation($url);
    return $this->page->returnLocation();
  }

  private function numId($stringId) {
    if (!is_numeric($stringId)) {
      $temp = @implode(@gzfile('http://www.myspace.com/'.$stringId));
      if(!$temp) // We encountered an error, exit cleanly.
        return 0;
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
  protected function regex($reg, $text) {
    if (preg_match_all($reg, $text, $regs, PREG_SET_ORDER)) {
      return $regs;
    } else {
      return null;
    }
  }
  private function setId($newId) {
    $this->id = $this->numId($newId);
  }

}
?>