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
require_once('../includes.php');
require_once('jsmin.php');

$javascript = '';

/* The javascript files */
$files[] = 'jquery-1.3.2.js';
$files[] = 'jquery.cookie.js';
$files[] = 'jquery.colorbox.js';

foreach($files as $file) {
  $javascript .= file_get_contents($options['siteRoot'] . '/js/' . $file);
}

if($options['minify']) {
  if(file_put_contents($options['siteRoot'] . '/js/javascript.js', JSMin::minify($javascript))) {
  
  } else
    echo 'Failed';
} else {
  file_put_contents($options['siteRoot'] . '/js/javascript.js', $javascript);
}

?>