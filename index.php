<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<title>Word Total Frequency</title>

	<script type="text/javascript" src="jquery-1.6.2.min.js"></script>

<script>

JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"'+obj+'"';
        return String(obj);
    }

    else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n]; t = typeof(v);
            if (t == "string") v = '"'+v+'"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};


function callback(json){
	var data = {};
	data['spell'] = json.query;
	data['frequency'] = counts[currentIndex];
	
	var arr_phonetic = [], arr_sound = [], arr_reference = [], arr_detail = [];
	var obj_entries = {};
	
	try{
	data['prototype'] = json.primaries[0].terms[0].text;
	
	for (var i=0; i<json.primaries.length; i++){
		// phonetic & sound file
		var terms = json.primaries[i].terms;
		for (var j=0; j<terms.length; j++){
			if (terms[j].type == 'phonetic')
				arr_phonetic.push(terms[j].text);
			if (terms[j].type == 'sound')
				arr_sound.push(terms[j].text);
		}
		
		// entries
		var entries = json.primaries[i].entries;
		for (var j=0; j<entries.length; j++){
			if (entries[j].type == 'meaning'){
				for (var k=0; k<entries[j].terms.length; k++){
					if (entries[j].terms[k].language == 'zh-Hans'){
						if (!obj_entries[TOP_MEANING]) obj_entries[TOP_MEANING] = [];
						obj_entries[TOP_MEANING].push(entries[j].terms[k].text);
					}
				}
			}
			else if (entries[j].type == 'related' && entries[j].labels[0].text == 'See also:'){
				arr_reference.push(entries[j].terms[0].reference);
			}
			else if (entries[j].type == 'container' && entries[j].labels[0].title == 'Part-of-Speech'){
				// new array for one word type
				var word_type = entries[j].labels[0].text;
				obj_entries[word_type] = [];
				
				var entries2nd = entries[j].entries;
				for (var k=0; k<entries2nd.length; k++){
					// meaning
					if (entries2nd[k].type == 'meaning'){
						for (var l=0; l<entries2nd[k].terms.length; l++){
							if (entries2nd[k].terms[l].language == 'zh-Hans')
								obj_entries[word_type].push(entries2nd[k].terms[l].text);
						}
					}
				}
			}
			else{
				var entries2nd = entries[j].entries;
				if (entries2nd && entries2nd.length>0){
					for (var k=0; k<entries2nd.length; k++){
						// meaning
						if (entries2nd[k].type == 'related'){
							arr_reference.push(entries2nd[k].terms[0].reference);
						}
					}
				}
			}
		}
	}
	}
	catch(msg){
		console.log("XXXX--: "+msg);
		pause(500);
	}
	
	data['phonetic'] = arr_phonetic.unique().join(' ');
	data['soundFile'] = arr_sound.unique().join(' ');
	data['reference'] = arr_reference.join(' ');
	
	for (var key in obj_entries){
		if (arr_detail.length > 0) arr_detail.push('\n');
		
		if (key != TOP_MEANING)
			arr_detail.push(key+'\n');
		
		for (var i=0; i<obj_entries[key].length; i++)
			arr_detail.push(obj_entries[key][i]+'\n');
	}
	data['translate'] = arr_detail.length>1 ? arr_detail[1] : arr_detail[0];
	data['detail'] = arr_detail.join('');
	data['json'] = JSON.stringify(json);

	$.ajax({
	   type:'POST',
	   url:'save.php',
	   data: data,
	   dataType: 'json',
	   contentType:'application/x-www-form-urlencoded;charset=utf-8',
	   timeout: 5000,
	   success: function(msg){
		 console.log('data saved for "' + msg.word + '"');
		 pause(700);
		 getNextWord();
	   },
	   error: function(msg){
		 // this block never get invoked
		 console.log('[ERROR]: saving for "' + msg.word + '"');
		 
		 currentIndex--;
		 pause(30000);
		 console.log('re-get word...');
		 getNextWord();
	   }
	});
}

function pause(millis) 
{
    var date = new Date();
    var curDate = null;

    do {curDate = new Date();} 
    while (curDate-date < millis)
}

var words = [], counts = [];
var currentIndex = 115;
var currentSection = 11;
var stopCrawling = false;
var TOP_MEANING = 'TOP_MEANING';

function getNextWord(){
	if (stopCrawling) return;
	
	currentIndex++;
	if (currentIndex >= words.length) return;

	if (Math.floor(currentIndex/10) > currentSection){
		currentSection = Math.floor(currentIndex/10);
		pause(12000);
		currentIndex--;
		getNextWord();
	}
	else{
		console.log('get word ['+currentIndex+']: '+words[currentIndex]+'...');
		var url = 'http://www.google.com/dictionary/json?callback=callback&sl=en&tl=zh&restrict=pr%2Cde&client=te&jsoncallback=?&q=' + words[currentIndex];
		$.getJSON(url, function(json){
			// do nothing
		});
	}
}

$(function(){
	$(document).ready(function(){
		$('#btn-count').click(function(){
			$.get("words.txt", function(data){
				console.log('===================== START READING WORD LIST ==========================');
				var re = /^([A-Za-z-']{1,})\s/gim;
				words = data.match(re);
				console.log('total words count: '+words.length);
				
				re = /[\d]+$/gim;
				counts = data.match(re);
				console.log('total counts count: '+counts.length);
				
				/* check invalid character
				var re1 = /^([A-Za-z-']*)\b/gim;
				var words1 = data.match(re1);
				console.log('total words1 count: '+words1.length);
				
				for (var i=0; i<Math.min(words.length, words1.length); i++){
					if (words[i].trim() != words1[i].trim()){
						console.log('['+i+']: '+words[i] + ', -- '+words1[i]);
						break;
					}
				}
				*/
			});
		});
		
		$('#btn-merge').click(function(){
			console.log('===================== START Merging WORD LIST ==========================');
			var o = {}, i, l = words.length;
			for (i=0; i<l; i+=1){
				if (o[words[i]]){
					o[words[i]] += parseInt(counts[i]);
				}
				else{
					o[words[i]] = parseInt(counts[i]);
				}
			}
			
			words = [], counts = [];
			for (i in o){
				words.push(i);
				counts.push(o[i]);
			}
			o = null;
			console.log('===== after merging: words:'+words.length+', counts:'+counts.length);
		});
		
		$('#btn-crawl').click(function(){
			console.log('===================== START Crawling ==========================');
			stopCrawling = false;
			getNextWord();
		});
		$('#btn-stop-crawl').click(function(){
			stopCrawling = true;
		});

		$('#btn-getpage').click(function(){
			$.getJSON('get.php?page=1', function(json){
				console.log('asdfasf');
			});
		});
	});

});

String.prototype.trim = function(){
    return this.replace(/(^\s*)|(\s*$)/g, "");
}

Array.prototype.unique = function() {
    var o = {}, i, l = this.length, r = [];
    for(i=0; i<l;i+=1) o[this[i]] = this[i];
    for(i in o) r.push(o[i]);
    return r;
};

</script>
</head>
<body>

	<button id="btn-count">Count Word List</button>
	<p></p>
	<button id="btn-merge">Merge Word List</button>
	<p></p>
	<button id="btn-crawl">Start Crawl</button>&nbsp;&nbsp;<button id="btn-stop-crawl">Stop</button>
	<p></p>
	<button id="btn-getpage">Get Page Json</button>
</body>
</html>