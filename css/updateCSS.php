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
require_once('cssmin.php');

$css = '';

/* The CSS files */
$files[] = 'main.css';
$files[] = 'colorbox.css';
if(preg_match('%msie%i', $_SERVER['HTTP_USER_AGENT']))
  $files[] = 'colorbox-ie.css';

foreach($files as $file) {
  $css .= file_get_contents($file);
}

if($options['minify']) {
  file_put_contents($options['siteRoot'] . '/css/style.css', cssmin::minify($css));
} else {
  file_put_contents($options['siteRoot'] . '/css/style.css', $css);
}

?>