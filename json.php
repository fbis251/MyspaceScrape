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
/* TODO: Allow for different requests in order to return JSON for profiles,
         comments, pictures, etc
*/


/* Let the browser know this is a JSON file */
/* TODO: Enable this before depolying!
header('Content-Type: application/json');
*/
if($options['useLocalFile']) {
  die(file_get_contents($options['siteRoot'] . 'profile.json'));
}

$id = @$_REQUEST['id'];
$id = ($id != null && $id != '') ? $id : 'thisiseviltom';
$ms = new msDetect($id, $options['msUser'], $options['msPass']);
$ms = $ms->detectProfileType();

$type = $ms->profileType();

if($type != 'msProfileBand') {
	$array['profileType']      = $type;
	$array['name']             = $ms->userName();
	$array['defaultImage']     = $ms->userDefaultImage();
	$array['albums']           = $ms->userAlbums();
	$array['comments']         = $ms->userComments();
	$array['pictures']         = $ms->userPics();
	$array['tops']             = $ms->userTopFriends();
	$array['trackers']         = $ms->userTrackers();
	$array['aboutme']          = $ms->userAboutMe();
  $array['generalinterests'] = $ms->userGeneralInterests();
}

$json = json_encode($array);
echo $json;
?>
