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
$id = @$_GET['id'];
$jsFile  = $options['siteUrl'] . 'js/javascript.php';
$cssFile = $options['siteUrl'] . 'css/style.php';

if($id == null || !isset($id)) {
  $id = '';
}
?>
<html>
<head>
<link rel="stylesheet" href="<?=$cssFile;?>" type="text/css" media="screen" />
<script type="text/javascript" src="<?=$jsFile;?>"></script>
<script type="text/javascript">
  function resizeImage(url, size) {
    var image = url;
    image = image.replace('s_', size + '_');
    image = image.replace('m_', size + '_');
    image = image.replace('l_', size + '_');
    image = image.replace('_s', '_' + size);
    image = image.replace('_m', '_' + size);
    image = image.replace('_l', '_' + size);
    return image;
  }

  function cleanText(text) {
    if(text) {
      text  = text.replace(/</g, '&lt;');
      text  = text.replace(/>/g, '&gt;');
      text  = text.replace(/"/g, '');
    }
    return text;
  }

  function doJson(profile) {
    var html     = 'Profile type: ' + profile.profileType;
    var friendID = $('#friendid').val();
    var title    = (profile.name != '') ? profile.name : friendID;
    var url      = 'http://www.myspace.com/' + friendID + '/classic/';
    document.title = title + ' - HITS';
    
    html+= '<br /><a href="' + url + '">' + url + '</a><br />';
    
    html += "<div id=\"userName\">\n";
    if(profile.defaultImage != null) { 
      var name = profile.name
      html += '<strong>' + name + "</strong>\n";
    }
    html += "<div class=\"clear\"></div>\n";
    html += "</div>\n";
    
    
    html += "<div id=\"defaultImage\">\n";
    if(profile.defaultImage != null) {
      var url  = '<?=$options['siteUrl'];?>#' + profile.id;
      var img        = profile.defaultImage;
      var imageLarge = resizeImage(img, 'l');
      
      html += '<a href="'+imageLarge+'" target="_blank" class="lightbox" rel="defaultImage" title="Default Image">';
      html += ' <img src="'+img+'" alt="'+name+'" />';
      html += "</a>\n";
    }

    html += "<div class=\"clear\"></div>\n";
    html += "</div>\n";
    
    html += "<div id=\"tops\">\n";
    if(profile.tops != null) {
      for(var i = 0, j = 0; i < profile.tops.length; i++, j++) {
        var topFriend = profile.tops[i];
        var name = topFriend.name;
        var img  = topFriend.img;
        var imageLarge = resizeImage(img, 'l');
        var url  = '<?=$options['siteUrl'];?>#' + topFriend.id;
        var title = topFriend.id;

        html += '<div class="topFriend">';
        html += '<a href="'+imageLarge+'" target="_blank" class="lightbox" rel="topFriends" title="'+name+'"><img src="'+img+'" alt="'+name+'" /></a>';
        html += '<a href="'+url+'" class="profileLink" target="_blank" title="'+title+'">'+name+'</a></div>';
        html += "\n";
      }

      html += "<div class=\"clear\"></div>\n";
    }
    html += "</div>\n";

    html += "<div id=\"aboutMe\">\n";
    if(profile.aboutme != null) {
      html += '<strong>About Me</strong><br />'
      html += profile.aboutme;
      html += "<div class=\"clear\"></div>\n";
    }
    html += "</div>\n";

    html += "<div id=\"generalInterests\">\n";
    if(profile.generalinterests != null) {
      html += '<strong>General Interests</strong><br />'
      html += profile.generalinterests;
      html += "<div class=\"clear\"></div>\n";
    }
    html += "</div>\n";

    /* For the full image slideshow */
    if(profile.pictures != null) {
      var currentAlbum = '';
      var endDiv = false;
      html += '<div id="allPicturesLinks">';
      for(var i = 0, j = 0; i < profile.pictures.length; i++, j++) {
        /* img, caption, url */
        var image = profile.pictures[i];
        var album = image.albumName;
        
        var url = image.url;
        var img = resizeImage(image.img, 'l');
        var imgSmall = resizeImage(img, 's');
        var caption = cleanText(image.caption);

        if(j == 0) {
          html += '<a href="'+img+'" id="allImagesLink" title="'+caption+'" target="_blank" rel="allImages" class="lightbox" style="display:none;"></a>';
        } else {
          html += '<a href="'+img+'" title="'+caption+'" target="_blank" rel="allImages" class="lightbox" style="display: none;"></a>';
        }
        
      }

      html += '</div><div class="clear"></div>' + "\n";
    }


    if(profile.pictures != null) {
      var currentAlbum = '';
      var endDiv = false;
      for(var i = 0, j = 0; i < profile.pictures.length; i++, j++) {
        /* img, caption, url */
        var image = profile.pictures[i];
        var album = image.albumName;
        var albumId = image.albumId;
        var rel = 0;
        
        var url = image.url;
        var img = resizeImage(image.img, 'l');
        var imgSmall = resizeImage(img, 's');
        var caption = cleanText(image.caption);

        if(currentAlbum != album) {
          if(endDiv) {
            html += '</div>' + "\n";
            endDiv = false;
          }
          html += '<div class="clear"></div>' + "\n";
          html += '<div id="pictures' + albumId + '">' + "\n";
          html += '<div><strong>' + album + '</strong></div>' + "\n";
          html += '<div class="clear"></div>' + "\n";
          currentAlbum = album;
          endDiv = true;
        }
        html += '<div class="image">';
        html += '<a href="'+img+'" title="'+caption+'" target="_blank" rel="'+albumId+'" class="lightbox"><img src="'+imgSmall+'" /></a>';
        html += '<a class="msLink" href="'+url+'">ms</a>';
        html += ' </div>';
        html += "\n";
      }

      html += '</div><div class="clear"></div>' + "\n";
    }

    html += "<div id=\"comments\">\n";
    if(profile.comments != null) {
        var comment;
        var name;
        var img;
        var imageLarge;
        var text;
        var url;
        var title;
        var time;
      for(i = 0, j = 0; i < profile.comments.length; i++, j++) {
        comment = profile.comments[i];
        name = comment.name;
        img  = resizeImage(comment.img, 's');
        imageLarge = resizeImage(img, 'l');
        text = cleanText(comment.text);
        url  = '<?=$options['siteUrl'];?>' + comment.id;
        title = comment.id;
        time = new Date();

        time.setTime(comment.timestamp * 1000);
        time = time.toLocaleString();

        html += '<div class="comment">';
		html += '	<div class="commentTop">';
		html += '		<a href="'+imageLarge+'" target="_blank" class="lightbox" rel="comments" title="'+name+'"><img src="'+img+'" /></a>';
		html += '   <span class="commentInfo">';
    html += '		  <a href="'+url+'" class="profileLink" target="_blank" title="'+title+'">'+name+'</a>';
		html += '		  (<b>'+time+'</b>)';
    html += '   </span>';
		html += '	</div><div class="commentText">'
		html += 		text;
		html += '	</div>';
		html += '</div>';
        html += "<div class=\"clear\"></div>\n";
      }

      html += "<div class=\"clear\"></div>\n";
    }
    html += "</div>\n";

    /* Figure out max width and height */
    myWidth = Math.floor($(document).width() * .9);
    myHeight = Math.floor($(document).height() * .9);

    $('#main').height(myHeight).html(html);

    /* Prepare the colorboxes */
    $("a.lightbox").colorbox({maxWidth: myWidth, maxHeight: myHeight, slideshow: true, slideshowAuto: false});

    $("a.msLink").colorbox({width:"80%", height:"80%", iframe:true});

    //$(".example7").colorbox({width:"80%", height:"80%", iframe:true});
    $(".allImages").click(function(){ 
        //$.fn.colorbox({href:'http://www.google.com', open:true,width:"80%", height:"80%", iframe:true});
        //$("img").colorbox({href: tempUrl , maxWidth: myWidth, maxHeight: myHeight, slideshow: true, slideshowAuto: false, open: true});
        $("#allImagesLink").click();
        //alert('test');
        return false;
      });
    /*
    $(".pictureComments")
      .click(function (event) {
        event.preventDefault();
        $(this).children().children()
          .attr({id : 'currentComments'})
          .show();
      })
      .colorbox({
        width:"90%",
        inline:true,
        href: '#currentComments'
      })
      .bind("cbox_closed", function(){
        $(this).children().children()
          .attr({id : ''})
          .hide();
      });
      */


    /* Profile Link Click */
    $(".profileLink").click(function (event) {
      event.preventDefault();
      $('#friendid').val($(this).attr('title'));
      newURL();
    });
  }
  
  function checkURL(string, regex) {
    var match = regex.exec(string);
    if (match != null && match.length > 1) {
      return match[1];
    } else {
      return 0;
    }
  }
  
  
  function newURL() {
    var theid = $('#friendid').val();
    if(!theid && window.location.hash) {
      theid = window.location.hash.substr(1);
      $('#friendid').val(theid);
    }
    
    if(id = checkURL(theid, /^([\w\-\_\.]*?)$/i)) {
      /* Regular friendID */
      validated = true;
    } else if(id = checkURL(theid, /myspace\.com\/([\w\-\_\.]*?)$/i)) {
      /* www.myspace.com/friendid */
      validated = true;
    } else if(id = checkURL(theid, /friendid=(\d*)/i)) {
    /* friendid=12345 */
      validated = true;
    }
    if(id && validated) {
      $.getJSON('<?=$options['siteUrl'];?>l/' + id, function(json){
      window.location.hash = id;
      doJson(json);
    });
    }
  }
  
  function dump(arr,level) {
		var dumped_text = "";
		if(!level) level = 0;
	
		//The padding given at the beginning of the line.
		var level_padding = "";
		for(var j=0;j<level+1;j++) level_padding += "    ";
		
		if(typeof(arr) == 'object') { //Array/Hashes/Objects 
			for(var item in arr) {
				var value = arr[item];
			
				if(typeof(value) == 'object') { //If it is an array,
					dumped_text += level_padding + "'" + item + "' ...\n";
					dumped_text += dump(value,level+1);
				} else {
					dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
				}
			}
		} else { //Stings/Chars/Numbers etc.
			dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
		}
		return dumped_text;
	}	

  $(function() {
    /* Loading message */
    $().bind('ajaxSend', function(){
      $('#loading').show();
      $('#wrapper').hide();

    }).bind('ajaxComplete', function(){
      $('#loading').hide();
      $('#wrapper').show();
    });

    /* Load the profile */
    newURL();
    $('form').submit(function() {
      return false;
    });
  });

</script>
</head>
<body>
<div class="header">
  <div id="friendIDDiv">
    <form method="get" action="<?=$options['baseUrl'];?>" onsubmit="newURL()">
      FriendID: <input type="text" name="friendid" id="friendid" value="<?=$id;?>" />
      <input type="submit" value="Go!" onsubmit="newURL()" />
    </form>
  </div>
</div>
<a class='allImages' href="#">View All Images</a>
<div id="test"></div>
<div id="loading" style="display: none"><h1>Loading...</h1></div>
<div id="wrapper">
<div id="main"></div>
</div>
</body>
</html>