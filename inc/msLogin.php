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
/* Class: MsLogin
   Desc:  Uses cURL to authenticate a Myspace user account and allow access
          to members only pages.
*/

class msLogin {
  var $userEmail;
  var $userPassword;
  var $ch;
  var $randnum;
  var $lastPage;
  var $page;
  var $myId;
  var $jarFile;
  var $jarLocation;
  var $jarFullPath;
  
  public function msLogin($jarId = '') {
    /* Load the site options */
    global $options;
    $this->options = $options;
    
    /* Set the cookie jar's directory path */
    $this->jarLocation = $this->options['cookieJar'];
    
    /* Check if the cookie jar exists, if not create a new session */
    if($jarId != '' && file_exists($this->jarLocation.'cookiejar-'.$jarId)) {
      $this->randnum = $jarId;
      $this->lastPage = 'http://home.myspace.com/index.cfm?fuseaction=user';
    } else {
      $this->randnum = rand(1, 9999999);
      $this->lastPage = 'http://www.myspace.com/';
    }
    $this->jarFullPath = $this->jarLocation.'cookiejar-'.$this->randnum;
    $this->setupCurl();
  }
  
  private function setupCurl() {
    $this->jarFile = $this->jarFullPath;
    $this->ch = curl_init();
    //
    // setup and configure
    //
    curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->jarFile);
    curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->jarFile);
    curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5');
    curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
    @curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->ch, CURLOPT_POST, 0);
  }
  
  public function myspaceCreds($user, $pass) {
    $this->userEmail = $user;
    $this->userPassword = $pass;
    $this->doLogin();
  }
  
  private function doLogin() {
    if($this->userEmail == null || $this->userPassword == null) {
      die('User and password have not been set!');
    }
    
    /* get homepage for login page token */
    curl_setopt($this->ch, CURLOPT_URL, 'http://www.myspace.com');
    $this->page = curl_exec($this->ch);

    /* Find the token */
    preg_match('/MyToken=([^"]+)"/', $this->page, $token);
    $this->token = @$token[1];
    
    /* Do login */
    curl_setopt($this->ch, CURLOPT_URL, 'http://secure.myspace.com/index.cfm?fuseaction=login.process&MyToken={$this->token}');
    curl_setopt($this->ch, CURLOPT_REFERER, 'http://www.myspace.com');
    curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, Array ('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($this->ch, CURLOPT_POST, 1);
    $postfields = '__VIEWSTATE=/wEPDwUJODA4MTQ2MTc0ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkTWFpbiRjcE1haW4kU3BsYXNoRGlzcGxheSRjdGwwMSRSZW1lbWJlcl9DaGVja2JveAU9Y3RsMDAkY3RsMDAkTWFpbiRjcE1haW4kU3BsYXNoRGlzcGxheSRjdGwwMSRMb2dpbl9JbWFnZUJ1dHRvbg==';
    $postfields .= '&ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24Email_Textbox=' . urlencode($this->userEmail);
    $postfields .= '&ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24Password_Textbox=' . urlencode($this->userPassword);
    $postfields .= '&ctl00_ctl00_Main_cpMain_SplashDisplay_ctl01_Remember_Checkbox=';
    $postfields .= '&ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24Login_ImageButton=';
    $postfields .= '&ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24SingleSignOnHash=';
    $postfields .= '&ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24SingleSignOnRequestUri=';
    $postfields .= '&ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24nexturl=';
    $postfields .= '&=ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24apikey';
    $postfields . '&=ctl00%24ctl00%24Main%24cpMain%24SplashDisplay%24ctl01%24ContainerPage';
    $postfields .= '&NextPage=';
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postfields);
    $this->page = curl_exec($this->ch);

    /* Prepare the redirection URL */
    $redirpage = 'http://home.myspace.com/index.cfm?fuseaction=user&MyToken={$this->token}';
    
    /* Do the redirection */
    curl_setopt($this->ch, CURLOPT_REFERER, 'http://secure.myspace.com/index.cfm?fuseaction=login.process&MyToken={$this->token}');
    curl_setopt($this->ch, CURLOPT_URL, $redirpage);
    curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($this->ch, CURLOPT_POST, 0);
    $this->page = curl_exec($this->ch);
    $this->lastPage = 'http://secure.myspace.com/index.cfm?fuseaction=login.process&MyToken={$this->token}';
    
    /* Check if there was a login error */
    if (strpos($this->page, 'You Must Be Logged-In to do That!') !== false) {
      // login error
      return 2;
    }
    
    /* Go to the homepage
       This might no longer be required, waste of bandwith.
    curl_setopt($this->ch, CURLOPT_REFERER, $this->lastPage);
    curl_setopt($this->ch, CURLOPT_URL, 'http://home.myspace.com/index.cfm?fuseaction=user');
    curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($this->ch, CURLOPT_POST, 0);
    $this->page = curl_exec($this->ch);
    */
    
    $this->lastPage = 'http://home.myspace.com/index.cfm?fuseaction=user';
    $this->getMyId();
  }
  
  private function getMyId() {
    preg_match('/friendID=(.*?)\'/i', $this->page, $myid);
    $this->myId = @$myid[1];
    return $this->myId;
  }
  
  public function newLocation($url) {
    curl_setopt($this->ch, CURLOPT_REFERER, $this->lastPage);
    curl_setopt($this->ch, CURLOPT_URL, $url);
    curl_setopt($this->ch, CURLOPT_POST, 0);
    curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
    $this->page = curl_exec($this->ch);
    $this->LastPage = $url;
  }
  
  public function doPost($url, $postData) {
    curl_setopt($this->ch, CURLOPT_URL, $url);
    curl_setopt($this->ch, CURLOPT_POST, 1);
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData); 
    curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
    $this->page = curl_exec($this->ch);
  }
  
  public function newLocationTest($url) {
    $this->newLocation($url);
    $photoPage = $this->returnLocation();
    if (preg_match('/HashMashter="(.*?)"/i', $photoPage, $regs)) {
      $hash = $regs[1];
    } else {
      $hash = "";
    }
    $maxPages = 5;
    for($pageNumber = 1; $pageNumber <= $maxPages; $pageNumber++) {
      $url = 'http://www.myspace.com/Modules/PageEditor/Handlers/Profiles/Module.ashx';
      $postContent = 'PageNo='.$pageNumber.'&typeid=54&isGrouped=true&Hash='.$hash;
      /*curl_setopt($this->ch, CURLOPT_REFERER, $this->lastPage);*/
      curl_setopt($this->ch, CURLOPT_URL, $url);
      curl_setopt($this->ch, CURLOPT_POST, 1);
      curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postContent); 
      curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
      $this->page = curl_exec($this->ch);
      echo $this->page;
      $this->LastPage = $url;
    }
  }
  
  public function returnLocation() {
    return $this->page;
  }
  
  function close() {
    curl_close($this->ch);
    
    /* TODO: Erase the cookiejar when no longer needed */
    //@ unlink($this->jarFile);
    /**/
  }
}
/*
$ms = new msLogin("FB");
$ms->myspaceCreds($options['msUser'], $options['msPass']);
$ms->newLocation("http://www.myspace.com/tilatequila/photos/page/1?typeid=54&isGrouped=true");
$photoPage = $ms->returnLocation();
if (preg_match('/HashMashter="(.*?)"/i', $photoPage, $regs)) {
  $hash = $regs[1];
} else {
  $hash = "";
}
$maxPages = 5;
for($pageNumber = 1; $pageNumber <= $maxPages; $pageNumber++) {
  $url = 'http://www.myspace.com/Modules/PageEditor/Handlers/Profiles/Module.ashx';
  $postData = 'PageNo='.$pageNumber.'&typeid=54&isGrouped=true&Hash='.$hash;
  $ms->doPost($url, $postData);
  echo $ms->returnLocation();
}
$ms->close();
*/
?>