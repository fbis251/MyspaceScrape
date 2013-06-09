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
/* Compression gets enabled through includes.php */
require_once('../includes.php');

/* We don't always need to reminify the JS
  TODO: Only update JS once in a while, when needed
require_once('updateJS.php');
*/

if($options['minify']) {
  /* Set a date way in the future */
  header('Expires: Wed, 1 Jan 2020 00:00:00 GMT');
}
/* Let the browser know this is a JS file */
header('Content-Type: text/javascript');

/* Load the JS file */
include($options['siteRoot'] . '/js/javascript.js');

?>