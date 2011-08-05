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

	var data = {}, trans = [];

	data['spell'] = json.query;
	data['frequency'] = counts[currentIndex];

	try{

		$.each(json.primaries[0].terms, function(idx, term){

			if (term.type == 'sound' && !data['soundFile']){

				data['soundFile'] = term.text;

				//console.log('soundFile: '+term.text);

			}

			else if (term.type == 'phonetic' && !data['phonetic']){

				data['phonetic'] = term.text;

				//console.log('phonetic: '+term.text);

			}

		});

		

		// translate - detail

		$.each(json.primaries[0].entries, function(idx, entry){

			if (entry.type == 'meaning'){

				$.each(entry.terms, function(idx, term){

					if (term.type == 'text' && term.language == 'zh-Hans'){

						trans.push(term.text);

						//console.log('translate: '+term.text);

					}

				});

			}

		});

		

		var entries = json.primaries[0].entries[0].entries || json.primaries[0].entries[1].entries;

		$.each(entries, function(idx, entry){

			if (entry.type == 'meaning'){

				$.each(entry.terms, function(idx, term){

					if (term.type == 'text' && term.language == 'zh-Hans'){

						trans.push(term.text);

						//console.log('translate: '+term.text);

					}

				});

			}

		});

	}catch(msg){

		console.log(msg);
		pause(500);

	}

	if (trans.length > 0){

		data['translate'] = trans[0];

		data['detail'] = trans.join('\n');

	}

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

var currentIndex = -1;

var currentSection = 0;



function getNextWord(){

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
			getNextWord();
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
	<button id="btn-crawl">Start Crawl</button>
	<p></p>
	<button id="btn-getpage">Get Page Json</button>
</body>
</html>