<?php
    ini_set('display_errors', 'on');
	$id = $this->getVar("id");
    $template = $this->getVar("template");
    $mappings = $this->getVar("mappings");
    $table = $this->getVar("table");
    $type = $this->getVar("type");
    $label = $this->getVar("label");
    $errors = $this->getVar("errors");
    $user_id = $this->getVar("user_id");
    $timecode = $this->getVar("timecode");
    $parent_id = $this->getVar("parent_id");
    $data = $this->getVar("data");
	error_reporting(E_ERROR);
	?>
<!-- dependencies (jquery, handlebars and bootstrap) -->
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

<!-- ckeditor -->
<script src="<?php print __CA_URL_ROOT__;?>/app/plugins/Contribuer/assets/ckeditor/ckeditor.js" type="text/javascript"></script>
<script src="<?php print __CA_URL_ROOT__;?>/app/plugins/Contribuer/assets/ckeditor/config.js" type="text/javascript"></script>
<script src="<?php print __CA_URL_ROOT__;?>/app/plugins/Contribuer/assets/ckeditor/adapters/jquery.js" type="text/javascript"></script>

<!-- alpaca -->
<link type="text/css" href="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.css" rel="stylesheet"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.js"></script>


<td class="container" style="">
	<td class="row" style="padding-top:20px;">
        <h1 class="edit-form"><?php print $label; ?></h1>

<?php
    foreach($errors as $error) :
?>
        <div class="alert alert-warning alert-dismissible show" role="alert">
            <strong>Erreur</strong> <?php print $error; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<?php
    endforeach;
?>
<?php
	if($parent_id) {
		$vt_object = new ca_objects($parent_id);
		print $vt_object->getWithTemplate("<div><b>^ca_objects.preferred_labels.name <ifdef code='ca_objects.volume'>vol. ^ca_objects.volume </ifdef><ifdef code='ca_objects.issue'># ^ca_objects.issue</ifdef></b></div>");
	}
?>

<div id="form1" style="padding: 2px 2px 2px 2px"></div>
<style>
    h1 small {
        font-size:20px;
    }
</style>
<script type="text/javascript">
$("#form1").alpaca({
    "data": {
<?php
    print "\t\t\"_user_id\": \"".$user_id."\",\n";
    print "\t\t\"_timecode\": \"".$timecode."\",\n";
    print "\t\t\"_table\": \"".$table."\",\n";
    print "\t\t\"_type\": \"".$type."\",\n";
	print "\t\t\"_id\": \"".$id."\",\n";
    foreach($data as $key=>$value) {
        if($key == "content_blocs") {

            // SPECIFIC FOR PAGES : HERE IS WHERE THE JSON IS STORED

            $content = str_replace("\\\\n", '', $value);
			$content = str_replace("’", "&apos;", $content);
			$content = str_replace("'", "&apos;", $content);
			print "\t\t\"content_blocs\": ".json_encode($value).",\n";

        } else {
            // REGULAR CASES
            if((is_array($value)) && ($key != "content")) {
                // Reintroduce separator if array
                $value = "[\"".implode("\",\"", $value)."\"]";
            } else {
                // Protect if JSON
                $value = str_replace('"', '’’', $value);
                $value = str_replace('\n', '§', $value);
                $value = '"'.$value.'"';
            }
            print "\t\t\"$key\": ".str_replace("\n", "\\n", $value).",\n";
        }


    }
?>
    },
    "schema": {
        "type": "object",
        "properties": {
<?php foreach($mappings as $field=>$properties) :
print "\t\t\t'{$field}' : {\n";
foreach($properties as $name=>$property) {
	$property = '"'.$property.'"';
    if(is_string($property) && ($name != "mapping") && ($name != "placeholder")) print "\t\t\t\t\"{$name}\": {$property},\n";
}
print "\t\t\t},\n";
            endforeach; ?>
            "_user_id": {
                "type": "string"
            },
            "_timecode": {
                "type": "string"
            },
            "_type_id": {
                "type": "string"
            },
            "_type": {
                "type": "string"
            },
            "_table": {
                "type": "string"
            },
			"_id": {
				"type": "string"
			},
        }
    },
    "options": {
        "form": {
            "attributes": {
                "action": "<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Pages/Save/id/<?php print $id; ?>",
                "method": "post"
            }
            /*,
	            "buttons": {
                "submit": {
                    "title":"SEND"
                }
            }*/
        },
        "fields": {
            "_user_id": {
                "type": "hidden"
            },
            "_timecode": {
                "type": "hidden"
            },
            "_type_id": {
                "type": "hidden"
            },
            "_type": {
                "type": "hidden"
            },
            "_table": {
                "type": "hidden"
            },
			"_id": {
				"type": "hidden"
			},
<?php
            foreach($mappings as $field=>$properties) {
                print "\t\t\t\"".$field."\": {";
                if ($properties["dataSource"]) {
	            	print "\"placeholder\": \"".$properties["placeholder"]."\",";
                }
				if ($properties["dataSource"]) {
					print "\"dataSource\": \"".$properties["dataSource"]."\", \"type\":\"select\",";
	                if(!$properties["options"]) $properties["options"]=[];
	                if($properties["type"] == "array") {
	                	// Specific helper message for array
						$properties["options"] = array_merge($properties["options"], ["helper"=>"Type the first letters and use the keyboard arrows for fast selection. Keep CTRL pushed and click for multiple selection."]);
					} else {
	                	// Normal list helper message
						$properties["options"] = array_merge($properties["options"], ["helper"=>"Type the first letters and use the keyboard arrows for fast selection."]);
					}

	            }
                if (is_array($properties["options"])) {
	                foreach($properties["options"] as $option=>$value) {
			            print "\"".$option."\": \"".$value."\"";
	                }
	            }
                print "},\n";
            }
?>
        }
    },
    "view": "bootstrap-edit"
});

</script>
<table style="width: 100%;">
	<tr>
		<td style="width: 23%;">
			Available blocks
		</td>
        <td>
			Displayed blocks
		</td>
	</tr>
	<tr>
		<td id="dragItems" class="source col-md-3" style="vertical-align: top">
		    <div class="item paragraph" data-type="paragraph">Paragraph
		        <div class="contents">
			        <label>Content</label>
		            <textarea class="input content" name="content" data-subtype="content">content</textarea>
		        </div>
		    </div>
		    <div class="item large-image" data-type="large-image">Large image
		        <div class="contents">
			        <label>Image</label>
		            <input class="input image" name="image" data-subtype="image" value="image" />
			        <label>Caption</label>
		            <input class="input figcaption" name="figcaption" data-subtype="figcaption" value="figcaption" />
		        </div>
		    </div>
		    <div class="item lead-dropcap" data-type="lead-dropcap">Lead-dropcap
		        <div class="contents">
			        <label>Content</label>
		            <textarea class="input content" name="content" data-subtype="content">content</textarea>
		        </div>
		    </div>
		    <div class="item image-with-text" data-type="image-with-text">Image-with-text
		        <div class="contents">
			        <label>Image</label>
		            <input class="input image" name="image" data-subtype="image" value="image" />
			        <label>Content</label>
		            <textarea class="input content" name="content" data-subtype="content">content</textarea>
		        </div>
		    </div>
		    <div class="item image-is-fullsize" data-type="image-is-fullsize">Image fullsize
		        <div class="contents">
			        <label>Image</label>
		            <input class="input image" name="image" data-subtype="image" value="image" />
			        <label>Caption</label>
		            <input class="input figcaption" name="figcaption" data-subtype="figcaption" value="figcaption" />
		        </div>
		    </div>
		    <div class="item two-images" data-type="two-images">Two-images
		        <div class="contents">
			        <label>Image1</label>
		            <input class="input image1" name="image1" data-subtype="image1" value="image1" />
			        <label>Caption1</label>
		            <input class="input figcaption1" name="figcaption1" data-subtype="figcaption1" value="figcaption1" />
			        <label>Image2</label>
		            <input class="input image2" name="image2" data-subtype="image2" value="image2" />
			        <label>Caption2</label>
		            <input class="input figcaption2" name="figcaption2" data-subtype="figcaption2" value="figcaption2" />
		        </div>
		    </div>
		    <div class="item references" data-type="references">References
		        <div class="contents">
			        <label>Notes</label>
			        <textarea class="input content" name="content" data-subtype="content">Notes</textarea>
			        <label>Footnote 1</label>
		            <input class="input footnote1" name="footnote1" data-subtype="footnote1" value="footnote1" />
			        <label>Footnote 2</label>
		            <input class="input footnote2" name="footnote2" data-subtype="footnote2" value="footnote2" />
			        <label>Footnote 3</label>
		            <input class="input footnote3" name="footnote3" data-subtype="footnote3" value="footnote3" />
			        <label>Footnote 4</label>
		            <input class="input footnote4" name="footnote4" data-subtype="footnote4" value="footnote4" />
			        <label>Footnote 5</label>
		            <input class="input footnote5" name="footnote5" data-subtype="footnote5" value="footnote5" />
			        <label>Footnote 6</label>
		            <input class="input footnote6" name="footnote6" data-subtype="footnote6" value="footnote6" />
		        </div>
		    </div>
		</td>
		<td id="sortItems" class="target col-md-9">
		</td>
	</tr>
</table>
  <button id="serialize" class="is-primary alpaca-form-button-submit" style="float:right">
      ENREGISTRER
  </button>
<div id="json-container" style="display:none;">
  <textarea id="json">
  </textarea>
</div>
<div style="height: 120px;">&nbsp;</div>
<style>
.row {

}
.row .col-md-6 {
    width:calc(50% - 26px);
    padding:12px;
    margin:0;
    border:none;
    display: inline-block;
}
.row .col-md-9 {
    width:calc(75% - 26px);
    padding:12px;
    margin:0;
    border:none;
    display: inline-block;
}
.row .col-md-3 {
    width:calc(25% - 26px);
    padding:12px;
    margin:0;
    border:none;
    display: inline-block;
}
div[data-alpaca-field-path="/content_blocs"] textarea {
  /*display:none;*/
}
div[data-alpaca-field-path="/content_blocs"] label:before {
  content:"+";
  display:block;
  float:left;
  border:1px solid #efefef;
  padding:2px 6px;
  border-radius: 4px;
  margin-right:10px;
}

.source,
.target {
  min-height: 190px;
  border: 1px solid #dbdbdb;
  border-radius: 4px;
}

.item {
  min-height: 20px;
  margin: 5px;
  padding: 5px;
  border: 1px solid #f6f6f6;
  background-color: #fcfcfc;
  position: relative;
  border-radius: 4px;
}
#dragItems .item {
  background-color: #cd8;
}
.item .contents {
  display: none;
}
.closer,
.expander {
  float: right;
  width: 20px;
  height: 20px;
  border: 0;
  background-color: transparent;
}

.closer:hover,
.expander:hover {
  background-color: rgba(255,255,255,0.2);
  border: 0;
}

.empty {
  height: 30px;
  margin: 5px;
  background: #eee;
  border: 1px dashed #999;
}

.highlight {
  border: 1px solid red;
  background: #fff;
}

.highlight .item {
  opacity: 0.3;
}

.ui-draggable-dragging {
  z-index: 99;
  opacity: 1 !important;
  width: 100% !important;
}

#dragItems {
}
#sortItems label {
	font-size:0.6em;
	padding-left:4px;
	margin: 4px 0 -2px 0;
	display: block;
}

.ui-helper {
    width: 94% !important;
    height: auto !important;
}

.contents textarea {
  width: 100%;
  min-width: 340px;
  height: 140px;
}
#serialize-container {
  margin: 20px;
}

.ui-button-text-only .ui-button-text {
    padding:0 !important;
}

</style>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
<script>
var makeReadyBlock=function($it, content) {
  if ($it.find(".closer").length == 0) {
    var closeBtn = $("<span>", {
      class: "closer"
    });
    var expandBtn = $("<span>", {
      class: "expander"
    });
    $it.prepend(expandBtn);
    $it.prepend(closeBtn);
    expandBtn.button({
      icon: "ui-icon-triangle-1-s",
      label: "▶",
      showLabel: false
    }).click(function(ev) {
    	// expand button clicked
      console.log("[INFO]: Expanding ", $it);
      if($it.children(".contents").is(":visible")) {
        // Element is visible, so hide and carret down
        $it.children(".contents").hide();
        expandBtn.button({icon: "ui-icon-triangle-1-s"});
      } else {
        // Element is hidden, so show and carret up
        $it.children(".contents").show();
        expandBtn.button({icon: "ui-icon-triangle-1-n"});
      }
    });
    closeBtn.button({
      icon: "ui-icon-close",
      label: "✖︎",
      showLabel: false
    }).click(function(ev) {
    	// close button clicked
      console.log("[INFO]: Closing ", $it);
      $it.fadeTo(200, 0.0, function() {
        $it.remove();
        //$("#sortItems").sortable("refresh");
      });
    });
  }
  if((content) && ($it.length>0)) {
  	// No need for the type property
  	delete content.type;
    $.each(content, function(key,value) {
    	// target jquery block for the value
      let target = $it.find(".contents .input."+key);

			// Detect if .input.key is a textarea or an input tag
			if(target.prop("tagName") == "TEXTAREA") {
      	// put the value inside .text
        target.val(value);
        target.text(value);
      } else {
      	// put the value inside .val()
        target.val(value);
      }
    });
  }
}

$(function() {
  $("#sortItems").sortable({
    axis: "y",
    items: "> div",
    placeholder: "empty",
    dropOnEmpty: true,
    stop: function(e, ui) {
    	$it=ui.item;
      makeReadyBlock($it);
      }
  });

  $("#dragItems .item").draggable({
    connectToSortable: "#sortItems",
    revert: "invalid",
    start  : function(event, ui){
        $(ui.helper).addClass("ui-helper");
    },
    helper: 'clone'
  });

  // UPDATE THE PROPER TEXTAREA IN THE FORM
  $("#sortItems").disableSelection();
  $("#serialize").on("click", function() {
		var result={};
  	console.log("serialize clicked");
    let j=0;
  	$.each($("#sortItems").children(), function() {
	    j++;
      result[j]= {
        "type":$(this).data("type")
      }
    	$(this).find(".input").each(function() {
	      console.log($(this));
        let subtype = $(this).data("subtype");
        let value=$(this).text();
        value=$(this).val();
        result[j][subtype] = value;
      });
    });

    // IMPORTANT : the Alpaca textarea has for name "content". Double-check that it always the case.
    $(document).find('.alpaca-container-item textarea[name="content_blocs"]').val(JSON.stringify(result, null, 4));
    setTimeout(function(){
       $("#alpaca2").submit(); },
       700);
  });
});
<?php
    if($content) {
        print "var edit =".$content."; ";
    } else {
        print "var edit =[];";
    }
?>

$(document).ready(function() {
	$.each(edit, function(key, value) {
  	console.log(value.type);
    $it = $("#dragItems").find("."+value.type).clone().appendTo("#sortItems");
    makeReadyBlock($it, value);
  })
  $(document).on("click", "div[data-alpaca-field-path='/content.blocs']", function() {
	  console.log($(this));
	  $(this).find("textarea").show();
  })
  $("#sortItems textarea[name='content']").ckeditor();
});

</script>
