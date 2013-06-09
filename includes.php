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
ob_start('ob_gzhandler');
require_once('cfg/config.inc.php');
require_once($options['siteRoot'] . '/inc/msLogin.php');
require_once($options['siteRoot'] . '/inc/msLoader.php');
require_once($options['siteRoot'] . '/inc/msDetect.php');
require_once($options['siteRoot'] . '/inc/msProfile.php');
require_once($options['siteRoot'] . '/inc/msProfile1.php');
require_once($options['siteRoot'] . '/inc/msProfile2.php');
require_once($options['siteRoot'] . '/inc/msProfileBand.php');
require_once($options['siteRoot'] . '/inc/simple_html_dom.php');
/*
require_once('render.php');
require_once('common.php');
*/
?>